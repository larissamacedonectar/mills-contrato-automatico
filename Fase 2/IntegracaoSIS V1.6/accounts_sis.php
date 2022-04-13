<?php

    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    class Accounts_SIS
    {
        function postSISx(&$bean, $event, $arguments) {}

        function postSIS(&$bean, $event, $arguments)
        {
            global $db;
            $GLOBALS['log']->fatal('Inicio da Integração');        
            if($bean->postSIS == true)
                return;
            $bean->postSIS = true;
            // global $current_user;
            $notify_user = ($GLOBALS['current_user']->id != "") ? $GLOBALS['current_user']->id : "1";
            $bean->cnpj_c = preg_replace( '/[^0-9]/is', '', $bean->cnpj_c);
            $bean->cnpj_c = $this->mask($bean->cnpj_c, '##.###.###/####-##');
            $bean->phone_office = preg_replace( '/[^0-9]/is', '', $bean->phone_office);
            $bean->phone_office = $this->masc_tel($bean->phone_office);

            $fromLeads = strpos($_REQUEST["__sugar_url"], "Leads/");
            $fromMass = strpos($_REQUEST["__sugar_url"], "MassUpdate");
            $GLOBALS['log']->fatal('Mass update ' . $fromMass);

			/*
			 * TK-076-000224 - Integração de contas com o SIS (Samuel Shin Kim - 29/12/2021)
			 * Em alguns casos, devido a um bug, o assigned_user_id do bean da Conta fica vazio no before_save.
			 * Foi feito um ajuste para usar o current_user por padrão, e o assigned_user_id apenas se tiver valor.
			 */

			$current_user;
			if (!empty($bean->assigned_user_id)) {
				$current_user = new User();
				$current_user->retrieve($bean->assigned_user_id);
			} else {
				$current_user = clone $GLOBALS['current_user'];
			}

            if (($current_user->myasp_user_id_c != "") && ($bean->flagsis_c != 1) && ($fromLeads === false) && ($fromMass === false)) {
                $GLOBALS['log']->fatal('Account WS Run: ' . $_REQUEST["__sugar_url"]);
                // Set origem_c
                /*$orig["Filial BA"] = 1;
                $orig["Filial RJ"] = 2;
                $orig["Filial SP"] = 3;
                $orig["Filial MG"] = 4;
                $orig["Filial UBL"] = 4;
                $orig["Filial RS"] = 5;
                $orig["Filial PR"] = 6;
                $orig["Filial ES"] = 9;
                $orig["Filial MA"] = 126;
                $orig["Filial PE"] = 127;
                $orig["Filial GO"] = 128;
                $orig["Filial PLN"] = 130;
                $orig["Filial MAC"] = 139;
                $orig["Filial PA"] = 141;
                $orig["Filial RO"] = 142;
                $orig["Filial CE"] = 150;
                $orig["Filial DF"] = 157;
                $orig["Filial CRV"] = 158;
                $orig["Filial SC"] = 159;
                $orig["Filial VPB"] = 168;
                $orig["Nacional"] = 3;
                $orig["Global"] = 3;
                */
                $grupo["Construcao_Comercial"] = 48;
                $grupo["Construcao_Residencial"] = 47;
                $grupo["Construcao_Industrial"] = 49;
                $grupo["Construcao_Infraestrutura"] = 46;
                $grupo["Manutencao_Industrial"] = 49;
                $grupo["Industria2"] = 49;
                $grupo["Geracao_energia"] = 49;

                $sql = "SELECT description FROM teams WHERE id = '" . $bean->team_id . "'";
                $res = $db->query($sql);
                $row = $db->fetchByAssoc($res);

                if(is_numeric($row["description"]) == true){
                $origem_c = $row["description"];
                }else{
                  $origem_c = 3;
                    }
                $bean->origem_c = $origem_c;

                // SIS API Connect
                $GLOBALS['log']->fatal('Começando a conexão com o SIS');
				if (strpos($GLOBALS['sugar_config']['site_url'], 'https://solarisbrasil.sugarondemand.com') > -1) {
					$wsURLBase = "http://sys.solarisbrasil.com.br/SIS/webservice/wsSIS.asmx?WSDL";
				} else {
					$wsURLBase = "https://sis-qas.mills.com.br/webservice/wsSIS.asmx?WSDL";
				}
				
                $client = new SoapClient($wsURLBase, array(
                    "trace" => 1,
                    "exceptions" => 1,
                    "soap_version" => SOAP_1_1,
                ));

                // Make token
                $user = "wsSisUsuario";
                $hash = MD5("Qez@s4G(Eu");
            

                $pToken =  array("Usuario" => $user, "SenhaHash" => $hash);
                $result = $client->Token($pToken);

                $res = json_decode($result->TokenResult);
                $token = $res->result[0]->token;

                $GLOBALS['log']->fatal('Token Retornado:' . $token);
                $GLOBALS['log']->fatal('Iniciou o Get Cliente');
                // Get Cliente
                $dominio = 3;
                $cnpj = str_replace("-", "", str_replace(".", "", str_replace("/", "", $bean->cnpj_c)));
                $pCliente =  array("Token" => $token, "CNPJ" => $cnpj, "ID_Dominio" => $dominio);

                //try {
                    $GLOBALS['log']->fatal('Iniciou o Try do Get Cliente');
                    $result = $client->GetCliente($pCliente);
                    $res = json_decode($result->GetClienteResult);

                    $debug = json_encode($res->result[0]);
                    $bean->debug_c = $bean->debug_c . $debug;

                    $GLOBALS['log']->fatal('Debug do Resultado:' . $debug);
                /*} catch (Exception $e) {
                    $notifyBean = BeanFactory::newBean('Notifications');
                    $notifyBean->assigned_user_id = $notify_user;
                    $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                    $notifyBean->parent_type = $bean->module_name;
                    $notifyBean->parent_id = $bean->id;
                    $notifyBean->created_by = $notify_user;
                    $notifyBean->name = "Erro comunicação com o SIS: " . $e;
                    $notifyBean->save();
                }*/

                $id_cliente = $res->result[0]->ID_Cliente;
                $GLOBALS['log']->fatal('Iniciou o Populate de dados da $cliente');
                $cliente = new StdClass;
                $cliente->DataCadastro = date("Y-m-d H:i:s");
                $cliente->DataInclusao = date("Y-m-d H:i:s");
                $cliente->RazaoSocial = substr($bean->name, 0, 59);
                $cliente->CNPJ = $cnpj;
                $cliente->InscricaoMunicipal = "000";
                $cliente->ID_GrupoCliente = $grupo[$bean->grupo_c]; //
                $cliente->ID_SubGrupoCliente = $bean->subgrupo_c; //
                $cliente->Observacao = $bean->add_description_c;
                $cliente->HomePage = $bean->website;
                $cliente->ID_FuncionarioInclusao = $current_user->myasp_user_id_c;
                $cliente->ID_OrigemCliente = $bean->origem_c;
                $cliente->ID_SubOrigemCliente = "";
                $cliente->NomeComercial = ($bean->nome_comercial_c != "") ? $bean->nome_comercial_c : "Não informado";
                $cliente->ID_Dominio = $dominio;
                $cliente->ID_Pessoa = "2";
                $cliente->ID_Situacao = "2";
                $cliente->ID_StatusCliente = "2";
                $email = $bean->emailAddress->getPrimaryAddress($bean);
                $cliente->EmailFaturaEletronica = ($email != "") ? $email : "sememail@sememail.com.br";
                $cliente->ID_FuncionarioAlteracao = $current_user->myasp_user_id_c;
                $cliente->DataAlteracao = date("Y-m-d H:i:s");
                $cliente->DataAtualizacao = date("Y-m-d H:i:s");
                $GLOBALS['log']->fatal('Iniciou o populate do $endereço');
                $endereco = new StdClass;
                $endereco->ID_Cliente = "$id_cliente";
                $endereco->Endereco = ($bean->billing_address_street != "") ? $bean->billing_address_street : "Não informado";
                $endereco->Bairro = ($bean->billing_address_quarter_c != "") ? $bean->billing_address_quarter_c : "Não informado";
                $endereco->CEP = ($bean->billing_address_postalcode != "") ? "$bean->billing_address_postalcode" : "00000-000"; //
                $endereco->CEP = str_replace("-", "", $endereco->CEP);
                //$endereco->CEP = intval($endereco->CEP);
                $endereco->CEP = str_pad($endereco->CEP, 8);
                $endereco->CEP = substr($endereco->CEP, 0, 5) . "-" . substr($endereco->CEP, 5, 3);

                $endereco->ID_Estado = $bean->it4_estados_id_c; //
                $endereco->ID_Municipio = $bean->it4_cidades_id_c; //
                $endereco->Numero = ($bean->billing_address_number_c != "") ? $bean->billing_address_number_c : "0"; //
                $endereco->Complemento = $bean->billing_address_add_c;
                $endereco->Telefone = ($bean->phone_office != "") ? $bean->phone_office : "1";
                $endereco->Telefone = preg_replace('/\D/', '', $endereco->Telefone);
                if (strlen($endereco->Telefone) >= 10) {
                  $endereco->DDDTelefone = substr($endereco->Telefone, 0, 2);
                  $endereco->Telefone = substr($endereco->Telefone, 2);
                } else
                  $endereco->DDDTelefone = "11";

                $endereco->Fax = $bean->phone_fax;
                $endereco->Fax = preg_replace('/\D/', '', $endereco->Fax);
                if (strlen($endereco->Fax) >= 10) {
                  $endereco->DDDFax = substr($endereco->Fax, 0, 2);
                  $endereco->Fax = substr($endereco->Fax, 2);
                } else
                  $endereco->DDDFax = "11";

                $endereco->ID_FuncionarioInclusao = $current_user->myasp_user_id_c;
                $endereco->DataInclusao = date("Y-m-d H:i:s");
                $endereco->ID_FuncionarioAlteracao = $current_user->myasp_user_id_c;
                $endereco->DataAlteracao = date("Y-m-d H:i:s");
                $endereco->FlagAtivo = 1;
                $endereco->ID_TipoEndereco = 4;

                if ($id_cliente != "") {
                    $GLOBALS['log']->fatal('ID DO Cliente é diferente de Vazio');
                    $bean->myasp_account_id_c = $id_cliente;

                    /*
                    $cliente->DataAlteracao = $bean->date_modified;
                    $cliente->DataAtualizacao = $bean->date_modified;
                    $pCliente =  array("Token" => $token, "ID_Cliente" => $id_cliente, "Values" => $cliente);

                    $result = $client->AtualizarCliente($pCliente);
                    $res = json_decode($result->AtualizarClienteResult);

                    $debug = json_encode($res->result[0]);
                    $bean->debug_c = $bean->debug_c . $debug;

                    $mensagem = $res->result[0]->mensagem;
                    if ($mensagem != "Sucesso") {
                        throw new SugarApiException("Erro na Atualiza��o do SIS");
                        return;
                    }
                    */
                } else {
                    $GLOBALS['log']->fatal('ID DO Cliente é  Vazio');
                    $pCliente =  array("Token" => $token, "Values" => $cliente);
					$GLOBALS['log']->fatal('Tentando inserir ...'); 
					$resultCliente = sugar_upgrade_var_dump($cliente);
					$GLOBALS['log']->fatal($resultCliente);

                    
					
                    //try {
                        $GLOBALS['log']->fatal('Inicia Inserir Cliente');
                         $GLOBALS['log']->fatal($pCliente);
                        $result = $client->InserirCliente($pCliente);
                        $res = json_decode($result->InserirClienteResult);
                        $GLOBALS['log']->fatal("Cliente:");
                       

                        $id_cliente = $res->result[0]->ID_Cliente;
                        $bean->myasp_account_id_c = $id_cliente;
                        $GLOBALS['log']->fatal('Resultado do id do Inserir Cliente:' . $id_cliente);

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = $bean->debug_c . $debug;

                        $GLOBALS['log']->fatal('DEBUG:' . $debug);
						
                        $GLOBALS['log']->fatal('Teste linha 207');
                        $mensagem = $res->result[0]->mensagem;
                        if ($mensagem != "Sucesso" && $mensagem != "") {
                            $notifyBean = BeanFactory::newBean('Notifications');
                            $notifyBean->assigned_user_id = $notify_user;
                            $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                            $notifyBean->parent_type = $bean->module_name;
                            $notifyBean->parent_id = $bean->id;
                            $notifyBean->created_by = $notify_user;
                            $notifyBean->name = "Erro na Atualização do SIS: " . $mensagem;
                            $notifyBean->save();
							$GLOBALS['log']->fatal('Notificação Gerada');
                        }
                    /*} catch (Exception $e) {
                          $notifyBean = BeanFactory::newBean('Notifications');
                          $notifyBean->assigned_user_id = $notify_user;
                          $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                          $notifyBean->parent_type = $bean->module_name;
                          $notifyBean->parent_id = $bean->id;
                          $notifyBean->created_by = $notify_user;
                          $notifyBean->name = "Erro comunicação com o SIS: " . $e;
                          $notifyBean->save();
                    }*/

                    $endereco->ID_Cliente = "$id_cliente";
                }

                if (trim($bean->myasp_address_id_c) != "") {
                    $endereco->DataAlteracao = $bean->date_modified;
                    $endereco->DataAtualizacao = $bean->date_modified;
                    $pEndereco =  array("Token" => $token, "ID_Endereco" => $bean->myasp_address_id_c, "Values" => $endereco);

                    //try {
                        $GLOBALS['log']->fatal('Começou o Atualizar Endereço');
                        $result = $client->AtualizarEndereco($pEndereco);
                        $res = json_decode($result->AtualizarEnderecoResult);

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "update " . $bean->debug_c . $debug;

                        $GLOBALS['log']->fatal('DEBUG:' . $debug);

                        $mensagem = $res->result[0]->mensagem;

                        $GLOBALS['log']->fatal('Resultado do AtualizarEndereco:'. $mensagem);
                        if ($mensagem != "Sucesso" && $mensagem != "") {
                            $notifyBean = BeanFactory::newBean('Notifications');
                            $notifyBean->assigned_user_id = $notify_user;
                            $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                            $notifyBean->parent_type = $bean->module_name;
                            $notifyBean->parent_id = $bean->id;
                            $notifyBean->created_by = $notify_user;
                            $notifyBean->name = "Erro na Atualização do SIS: " . $mensagem;
                            $notifyBean->save();
                        }
                    /*} catch (Exception $e) {
                          $notifyBean = BeanFactory::newBean('Notifications');
                          $notifyBean->assigned_user_id = $notify_user;
                          $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                          $notifyBean->parent_type = $bean->module_name;
                          $notifyBean->parent_id = $bean->id;
                          $notifyBean->created_by = $notify_user;
                          $notifyBean->name = "Erro comunicação com o SIS: " . $e;
                          $notifyBean->save();
                    }*/

                } else {
                    $pEndereco =  array("Token" => $token, "Values" => $endereco);
                     $GLOBALS['log']->fatal('tentando inserir endereço ... ');
                    //try {
                        $GLOBALS['log']->fatal('Inicia Inserir Endereço');
                        $result = $client->InserirEndereco($pEndereco);
                        $res = json_decode($result->InserirEnderecoResult);

                        $id_endereco = $res->result[0]->ID_Endereco;
                        $bean->myasp_address_id_c = $id_endereco;

                        $GLOBALS['log']->fatal('ID DO ENDEREÇO:' . $id_endereco);

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "insert " . $bean->debug_c . $debug;

                        $GLOBALS['log']->fatal('DEBUG:' . $debug);
                        
						$GLOBALS['log']->fatal('Teste linha 281');
                        $mensagem = $res->result[0]->mensagem;
						$GLOBALS['log']->fatal('InserirEndereço : ' . $mensagem );
						
                       /* if ($mensagem != "Sucesso" && $mensagem != "") {
                            $notifyBean = BeanFactory::newBean('Notifications');
                            $notifyBean->assigned_user_id = $notify_user;
                            $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                            $notifyBean->parent_type = $bean->module_name;
                            $notifyBean->parent_id = $bean->id;
                            $notifyBean->created_by = $notify_user;
                            $notifyBean->name = "Erro na Atualização do SIS: " . $mensagem;
                            $notifyBean->save();
                        }
                    /*} catch (Exception $e) {
                          $notifyBean = BeanFactory::newBean('Notifications');
                          $notifyBean->assigned_user_id = $notify_user;
                          $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                          $notifyBean->parent_type = $bean->module_name;
                          $notifyBean->parent_id = $bean->id;
                          $notifyBean->created_by = $notify_user;
                          $notifyBean->name = "Erro comunicação com o SIS: " . $e;
                          $notifyBean->save();
                    }*/
                }
            }
			$GLOBALS['log']->fatal('Teste linha 310');
            $bean->flagsis_c = 0;
            //$bean->save();
        }

        function mask($val, $mask) {
            $maskared = '';
            $k = 0;
            for ($i = 0; $i <= strlen($mask) - 1; $i++) {
                if ($mask[$i] == '#') {
                    if (isset($val[$k]))
                        $maskared .= $val[$k++];
                } else {
                    if (isset($mask[$i]))
                        $maskared .= $mask[$i];
                }
            }

            return $maskared;
        }

        function masc_tel($TEL) {
            $tam = strlen(preg_replace("/[^0-9]/", "", $TEL));
            if ($tam == 13) { // COM CÓDIGO DE ÁREA NACIONAL E DO PAIS e 9 dígitos
                return "+".substr($TEL,0,$tam-11)." (".substr($TEL,$tam-11,2).") ".substr($TEL,$tam-9,5)."-".substr($TEL,-4);
            }
            if ($tam == 12) { // COM CÓDIGO DE ÁREA NACIONAL E DO PAIS
                return "+".substr($TEL,0,$tam-10)." (".substr($TEL,$tam-10,2).") ".substr($TEL,$tam-8,4)."-".substr($TEL,-4);
            }
            if ($tam == 11) { // COM CÓDIGO DE ÁREA NACIONAL e 9 dígitos
                return "(".substr($TEL,0,2).") ".substr($TEL,2,5)."-".substr($TEL,7,11);
            }
            if ($tam == 10) { // COM CÓDIGO DE ÁREA NACIONAL
                return "(".substr($TEL,0,2).") ".substr($TEL,2,4)."-".substr($TEL,6,10);
            }
            if ($tam <= 9) { // SEM CÓDIGO DE ÁREA
                return substr($TEL,0,$tam-4)."-".substr($TEL,-4);
            }
        }

    }

?>