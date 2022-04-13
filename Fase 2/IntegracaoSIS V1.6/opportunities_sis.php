<?php

    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    class Opportunities_SIS
    {

        function postSIS(&$bean, $event, $arguments)
        {
        	$GLOBALS['log']->fatal('postSIS :' . $bean->id );
        	if (($bean->myasp_proposal_id_c == "")) {
        	    //$GLOBALS['log']->fatal('if ignore update');
				$GLOBALS['log']->fatal('Teste linha 13 ');
        		global $db;
        		//global $current_user;
                $notify_user = ($bean->modified_user_id != "") ? $bean->modified_user_id : "1";

        		$current_user = new User();
        		$current_user->retrieve($bean->assigned_user_id);
                $fromMass = strpos($_REQUEST["__sugar_url"], "MassUpdate");

        		$strop = $bean->id;

        		$sql = "SELECT ao.account_id, oc.contact_id FROM accounts_opportunities ao LEFT JOIN opportunities_contacts oc ON (oc.opportunity_id = ao.opportunity_id AND oc.deleted = 0) WHERE ao.opportunity_id = '$strop' AND ao.deleted = 0 LIMIT 1";
        	    //$GLOBALS['log']->fatal('sql: ' . $sql);
        		$res = $db->query($sql);
        		$row = $db->fetchByAssoc($res);

				$GLOBALS['log']->fatal('Teste linha 29 ');
        		$account_id = $row["account_id"];
        		$contact_id = $row["contact_id"];
        		$opportunity_id = $strop;
                $GLOBALS['log']->fatal($fromMass);
                if ($fromMass === false && $account_id != null) {
                    $GLOBALS['log']->fatal("ENTROU: ID CONTA: " . $account_id . " ID CONTATO " . $contact_id);
        		    $account = BeanFactory::getBean("Accounts", $account_id);
                    $account->nome_comercial_c = (trim($account->nome_comercial_c) != "") ? $account->nome_comercial_c : "Não informado";
                    $email = $account->emailAddress->getPrimaryAddress($account);
                    $account->email1 = ($email != "") ? $email : "sememail@sememail.com.br";
                    $account->save();

        		    $contact = BeanFactory::getBean("Contacts", $contact_id);
                    $contact->save();

        		    $opportunity = $bean;
        		    $bean->debug_c .= 'Account: ' . $account_id . '; ';
        		    $bean->debug_c .= 'Opportunity: ' . $opportunity_id . '; ';
        		    $bean->debug_c .= 'Contact: ' . $contact_id . '; ';
        		    $bean->debug_c .= 'User: ' . $current_user->myasp_user_id_c . '; ';
                }

                    $GLOBALS['log']->fatal($account_id . " -- " . $opportunity_id . " -- " . $contact_id . " -- " .$current_user->myasp_user_id_c );
                    $GLOBALS['log']->fatal($bean->flagsis_c . " -- " . $fromMass);
        	        $GLOBALS['log']->fatal('first debug ');
        		    if ($account_id != "" && $opportunity_id != "" && $contact_id != "" && $current_user->myasp_user_id_c != "" && ($bean->flagsis_c != 1) && ($fromMass === false))
        		    {
        			    $GLOBALS['log']->fatal('if integrate ');
                        $GLOBALS['log']->fatal('Opportunity WS Run: ' . $_REQUEST["__sugar_url"]);

        			    // SIS API Connect
						if (strpos($GLOBALS['sugar_config']['site_url'], 'solarisbrasil') > -1) {
							$wsURLBase = "http://sys.solarisbrasil.com.br/SIS/webservice/wsSIS.asmx?WSDL";
						} else {
							$wsURLBase = "https://sis-qas.mills.com.br/webservice/wsSIS.asmx?WSDL";
						}

        			    $client = new SoapClient($wsURLBase, array(
        					"trace" => 1,
        					"exceptions" => 1,
        					"soap_version" => SOAP_1_1,
        			    ));
                        $GLOBALS['log']->fatal("SOAPCLIENT");
        			    // Make token
        			    $user = "wsSisUsuario";
                        $hash = MD5("Qez@s4G(Eu");
                        
                        $pToken =  ["Usuario" => $user, "SenhaHash" => $hash];

                        $GLOBALS['log']->fatal("REQUISIÇÃO:");
                        $GLOBALS['log']->fatal(print_r($pToken, true));

                        $result = $client->Token($pToken);
                        
                        $GLOBALS['log']->fatal("RESULTADO COMPLETO TOKEN");


        			    $res = json_decode($result->TokenResult);
        			    $token = $res->result[0]->token;
                        $GLOBALS['log']->fatal($token);
        			    //$this->postAccount($account);
        	    		//$this->postContact($contact);

        		    	$myasp_tipo = array(
        					"LocacaoJLG" => array('name' => 'LocacaoJLGMills', 'tipo' => '2', 'sub' => '2', 'subsub' => '1'),
        					"LocacaoGradall" => array('name' => 'LocacaoGradallMills', 'tipo' => '2', 'sub' => '2', 'subsub' => '3'),
        					"LocacaoGerador" => array('name' => 'LocacaoGeradorMills', 'tipo' => '2', 'sub' => '2', 'subsub' => '4'),
        					"LocacaoMovimentoTerra" => array('name' => 'LocacaoMovimentoTerra', 'tipo' => '2', 'sub' => '2', 'subsub' => '20'),
        					"LocacaoTorreIluminacao" => array('name' => 'LocacaoTorreIluminacao', 'tipo' => '2', 'sub' => '2', 'subsub' => '21'),
        					"LocacaoCompressor" => array('name' => 'LocacaoCompressor', 'tipo' => '2', 'sub' => '2', 'subsub' => '22'),
        					"VendaSeminovaMovimentoTerra" => array('name' => 'VendaSeminovaMovimentoTerra', 'tipo' => '2', 'sub' => '2', 'subsub' => '23'),
        					"VendaSeminovaCompressor" => array('name' => 'VendaSeminovaCompressor', 'tipo' => '2', 'sub' => '4', 'subsub' => '25'),
        					"VendaSeminovaTorreIluminacao" => array('name' => 'VendaSeminovaTorreIluminacao', 'tipo' => '2', 'sub' => '2', 'subsub' => '25'),
        					"VendaNovaJLG" => array('name' => 'VendaNovaJLGMills', 'tipo' => '2', 'sub' => '3', 'subsub' => '7'),
        					"VendaNovaGradall" => array('name' => 'VendaNovaGradallMills', 'tipo' => '2', 'sub' => '3', 'subsub' => '9'),
        					"VendaNovaGerador" => array('name' => 'VendaNovaGeradorMills', 'tipo' => '2', 'sub' => '3', 'subsub' => '10'),
        					"VendaSeminovaPlataforma" => array('name' => 'VendaSeminovaPlataforma', 'tipo' => '2', 'sub' => '4', 'subsub' => '13'),
        					"VendaSeminovaManipulador" => array('name' => 'VendaSeminovaManipulador', 'tipo' => '2', 'sub' => '4', 'subsub' => '15'),
        					"VendaSeminovaGerador" => array('name' => 'VendaSeminovaGerador', 'tipo' => '2', 'sub' => '4', 'subsub' => '16'),
							"VendaNovaTorreIluminacao" => array('name' => 'VendaNovaTorreIluminacao', 'tipo' => '2', 'sub' => '2', 'subsub' => '21'),
                            "VendaNovaCompressor" => array('name' => 'VendaNovaCompressor', 'tipo' => '2', 'sub' => '3', 'subsub' => '27'),
        			);

        			// Set origem_c
                       /*         $orig["Filial BA"] = 1;
                                $orig["Filial RJ"] = 2;
                                $orig["Filial SP"] = 3;
                                $orig["Filial MG"] = 4;
                                $orig["Filial RS"] = 5;
                                $orig["Filial PR"] = 6;
                                $orig["Filial ES"] = 9;
								$orig["Filial MS"] = 173;
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
                                $orig["Filial UBL"] = 170;
                                $orig["Nacional"] = 3;
                                $orig["Global"] = 3;
 */
                global $db;
                $sql = "SELECT id, description FROM teams WHERE name LIKE '" . substr($bean->equipe_c, 0, 3) . "%' ORDER BY description DESC";
                $res = $db->query($sql);
                $row = $db->fetchByAssoc($res);
                $GLOBALS['log']->fatal("fez a query");
                if ($row["id"] != "") {
                  // $bean->team_id = $row["id"];
                  // $bean->team_set_id = $row["id"];
                  // $sql = "UPDATE opportunities SET team_id = '{$row["id"]}', team_set_id = '{$row["id"]}' WHERE id = '{$bean->id}'";
                  // $db->query($sql);
                }

                if(is_numeric($row["description"]) == true){
                    $origem_c = $row["description"];
                }else{
                    $origem_c = 3;
                }
                $GLOBALS['log']->fatal("DEBUG AQUI");
                $bean->origem_c = $origem_c;
                //$GLOBALS['log']->fatal("Contato : " . print_r($contact));
        			$proposta = new StdClass;
        			$proposta->ID_TipoProposta = $myasp_tipo[$bean->proposal_type_c]["tipo"];
        			$proposta->ID_SubTipoProposta = $myasp_tipo[$bean->proposal_type_c]["sub"];
        			$proposta->ID_SubSubTipoProposta = $myasp_tipo[$bean->proposal_type_c]["subsub"];
        			$proposta->ID_Funcionario = $current_user->myasp_user_id_c;
        			$proposta->ID_Filial = $origem_c;
        			$proposta->RazaoSocialDominio = "SOLARIS BRASIL";
        			$proposta->Dominio = "3";
        			$proposta->ID_Dominio = 3;
        			$proposta->ID_Cliente = $account->myasp_account_id_c;
        			$proposta->ID_ContatoCliente = $contact->myasp_contact_id_c;
                    $proposta->ID_ContatoObra = $contact->myasp_contact_id_c;
                    $proposta->ID_Contato = $contact->myasp_contact_id_c;
        			$proposta->FlagSugar = true;
        			$proposta->RazaoSocialCliente = $account->name;
        			$proposta->CNPJCliente = str_replace("-", "", str_replace(".", "", str_replace("/", "", $account->cnpj_c)));
        			$proposta->FlagFreteIncluso = false;
        			$proposta->EmailFaturaEletronica = ($account->email1 == "") ? "sememail@sememail.com.br" : $account->email1;
                    $proposta->Observacao = $bean->description . "{quote: $bean->nr_proposta_sis_c}";
                    //$proposta->CNPJInterveniente = ""; //$bean->cnpj_interveniente_c;
                    //$proposta->quote_num = $bean->nr_proposta_sis_c;

        			//$bean->account_id_c = $account_id;
        			//$bean->contact_id_c = $contact_id;
        			//$bean->op_id_c = $opportunity_id;
               
		            $GLOBALS['log']->fatal("ws proposta: " . json_encode($proposta));

        			if (($bean->myasp_proposal_id_c == "") && ($bean->empresa_c == "Solaris")) {
        				$pProposta =  array("Token" => $token, "Values" => $proposta);
						$GLOBALS['log']->fatal('teste linha 154 ');
						$bean->debug_c = $bean->debug_c . $pProposta ;

        				$result = $client->InserirProposta($pProposta);
        				$res = json_decode($result->InserirPropostaResult);
						$GLOBALS['log']->fatal('Proposta inserida ');

        				$id_proposta = $res->result[0]->ID_Proposta;
        				$bean->myasp_proposal_id_c = $id_proposta;
        				$bean->proposal_myasp_id_c = $id_proposta;
        				$res_debug = $result->InserirPropostaResult;

        			    $debug = json_encode($proposta);
        			    $bean->debug_c = $res_debug . " - " . $debug;

        			    $sql = "INSERT INTO ssis_proposta (id, name, myasp_id, proposal_type) VALUES ('" . $bean->id . "', '" .
        			      $bean->name . "', '" . $id_proposta . "', '" . $bean->proposal_type_c . "')";
        			    $db->query($sql);

        			    $sql = "INSERT INTO ssis_proposta_cstm (id_c, op_id_c) VALUES ('" . $bean->id . "', '" .
        			    		$bean->id . "')";
        			    $db->query($sql);

        			    $bean->flagsis_c = 0;
        		        $bean->save();
        			}/*elseif (($bean->myasp_proposal_id_c != "") && ($bean->empresa_c == "Solaris")) {
                        $pProposta =  array("Token" => $token, "Values" => $proposta);
						$GLOBALS['log']->fatal('teste linha 154 ');
						$bean->debug_c = $bean->debug_c . $pProposta ;

        				$result = $client->InserirProposta($pProposta);
        				$res = json_decode($result->InserirPropostaResult);
						$GLOBALS['log']->fatal('######### Proposta Atualizada ########## ');

        				$id_proposta = $res->result[0]->ID_Proposta;
        				$bean->myasp_proposal_id_c = $id_proposta;
        				$bean->proposal_myasp_id_c = $id_proposta;
        				$res_debug = $result->InserirPropostaResult;

        			    $debug = json_encode($proposta);
                        $bean->debug_c = $res_debug . " - " . $debug;
                        $bean->flagsis_c = 0;
        		        $bean->save();
                    } */else {
        				$pProposta =  array("Token" => $token, "ID_Proposta" => $bean->myasp_id, "Values" => $proposta);
        				//$result = $client->AtualizarProposta($pProposta);
        				//$res = json_decode($result->AtualizarPropostaResult);

        				//$id_proposta = $bean->myasp_id;
        				//$res_debug = $result->AtualizarPropostaResult;
        			}
        		}

        	}
        }

        function postAccount(&$bean)
        {
            global $db;
            //global $current_user;

            $current_user = new User();
            $current_user->retrieve($bean->assigned_user_id);

            if (($current_user->myasp_user_id_c != "") && ($bean->emailAddress->getPrimaryAddress($bean) != "") && ($bean->flagsis_c != 1)) {


                // Set origem_c
                $orig["Filial BA"] = 9;
                $orig["Filial PLN"] = 33;
                $orig["Filial ES"] = 3;
                $orig["Filial GO"] = 36;
                $orig["Filial MA"] = 32;
                $orig["Filial Maca�"] = 139;
                $orig["Filial MG"] = 6;
                $orig["Filial PA"] = 141;
                $orig["Filial PE"] = 35;
                $orig["Filial PR"] = 7;
                $orig["Filial RJ"] = 18;
                $orig["Filial RO"] = 142;
                $orig["Filial RS"] = 34;
                $orig["Filial SP"] = 8;
                $orig["Nacional"] = 37;
                $orig["Global"] = 37;

                $grupo["Construcao_Comercial"] = 48;
                $grupo["Construcao_Residencial"] = 47;
                $grupo["Construcao_Industrial"] = 49;
                $grupo["Construcao_Infraestrutura"] = 46;
                $grupo["Manutencao_Industrial"] = 49;
                $grupo["Industria2"] = 49;
                $grupo["Geracao_energia"] = 49;

                $sql = "SELECT name FROM teams WHERE id = '" . $bean->team_id . "'";
                $res = $db->query($sql);
                $row = $db->fetchByAssoc($res);

                if (isset($orig[$row["name"]]))
                  $origem_c = $orig[$row["name"]];
                else
                  $origem_c = 37;

                $bean->origem_c = $origem_c;

                // SIS API Connect
				if (strpos($GLOBALS['sugar_config']['site_url'], 'https://solarisbrasil.sugarondemand.com') > -1) {
					$wsURLBase = "http://sys.solarisbrasil.com.br/SIS/webservice/wsSIS.asmx?WSDL";
				} else {
					$wsURLBase = "http://sisqa.solarisbrasil.com.br/sis/webservice/wsSIS.asmx?WSDL";
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

                // Get Cliente
                $dominio = 3;
                $cnpj = str_replace("-", "", str_replace(".", "", str_replace("/", "", $bean->cnpj_c)));
                $pCliente =  array("Token" => $token, "CNPJ" => $cnpj, "ID_Dominio" => $dominio);

                $result = $client->GetCliente($pCliente);
                $res = json_decode($result->GetClienteResult);

                $debug = json_encode($res->result[0]);
                $bean->debug_c = $bean->debug_c . $debug;

                $id_cliente = $res->result[0]->ID_Cliente;

                $cliente = new StdClass;
                $cliente->DataCadastro = date("Y-m-d H:i:s");
                $cliente->DataInclusao = date("Y-m-d H:i:s");
                $cliente->RazaoSocial = $bean->name;
                $cliente->CNPJ = $cnpj;
                $cliente->InscricaoMunicipal = "000";
                $cliente->ID_GrupoCliente = $grupo[$bean->grupo_c];
                $cliente->ID_SubGrupoCliente = $bean->subgrupo_c;
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

                $endereco = new StdClass;
                $endereco->ID_Cliente = "$id_cliente";
                $endereco->Endereco = $bean->billing_address_street;
                $endereco->Bairro = $bean->billing_address_quarter_c;
                $endereco->CEP = "$bean->billing_address_postalcode";
                $endereco->ID_Estado = $bean->it4_estados_id_c;
                $endereco->ID_Municipio = $bean->it4_cidades_id_c;
                $endereco->Numero = $bean->billing_address_number_c;
                $endereco->Complemento = $bean->billing_address_add_c;
                $endereco->Telefone = $bean->phone_office;
                $endereco->DDDTelefone = "11";
                $endereco->Fax = $bean->phone_fax;
                $endereco->DDDFax = "";
                $endereco->ID_FuncionarioInclusao = $current_user->myasp_user_id_c;
                $endereco->DataInclusao = date("Y-m-d H:i:s");
                $endereco->ID_FuncionarioAlteracao = $current_user->myasp_user_id_c;
                $endereco->DataAlteracao = date("Y-m-d H:i:s");
                $endereco->FlagAtivo = 1;
                $endereco->ID_TipoEndereco = 4;

                if ($id_cliente != "") {
/*
                    $bean->myasp_account_id_c = $id_cliente;

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
                    $pCliente =  array("Token" => $token, "Values" => $cliente);

                    $result = $client->InserirCliente($pCliente);
                    $res = json_decode($result->InserirClienteResult);

                    $id_cliente = $res->result[0]->ID_Cliente;
                    $bean->myasp_account_id_c = $id_cliente;

                    $debug = json_encode($res->result[0]);
                    $bean->debug_c = $bean->debug_c . $debug;
                    $endereco->ID_Cliente = "$id_cliente";
                }

                if (trim($bean->myasp_address_id_c) != "") {
                    $endereco->DataAlteracao = $bean->date_modified;
                    $endereco->DataAtualizacao = $bean->date_modified;
                    $pEndereco =  array("Token" => $token, "ID_Endereco" => $bean->myasp_address_id_c, "Values" => $endereco);

                    $result = $client->AtualizarEndereco($pEndereco);
                    $res = json_decode($result->AtualizarEnderecoResult);

                    $debug = json_encode($res->result[0]);
                    $bean->debug_c = "update " . $bean->debug_c . $debug;

                    $mensagem = $res->result[0]->mensagem;
                    if ($mensagem != "Sucesso") {
                        throw new SugarApiException("Erro na Atualiza��o do SIS");
                        return;
                    }
                } else {
                    $pEndereco =  array("Token" => $token, "Values" => $endereco);

                    $result = $client->InserirEndereco($pEndereco);
                    $res = json_decode($result->InserirEnderecoResult);

                    $id_endereco = $res->result[0]->ID_Endereco;
                    $bean->myasp_address_id_c = $id_endereco;

                    $debug = json_encode($res->result[0]);
                    $bean->debug_c = "insert " . $bean->debug_c . $debug;
                }
            }
            $bean->flagsis_c = 0;
            $bean->save();
        }

        function postContact(&$bean)
        {
            global $db;
            //global $current_user;

            $current_user = new User();
            $current_user->retrieve($bean->assigned_user_id);

            if (($current_user->myasp_user_id_c != "") && ($bean->emailAddress->getPrimaryAddress($bean) != "") && ($bean->flagsis_c != 1)) {

                $relatedAccounts = $bean->accounts->getBeans();
                $parentAccount = false;
                if (!empty($relatedAccounts)) {
                    reset($relatedAccounts);
                    $parentAccount = current($relatedAccounts);
                }

                if ($parentAccount !== false && is_object($parentAccount))
                {
                    $area["comercial"] = 4;
                    $area["tecnica"] = 5;
                    $area["administracao"] = 7;
                    $area["cobranca"] = 3;
                    $area[""] = 0;

                    // SIS API Connect
					if (strpos($GLOBALS['sugar_config']['site_url'], 'solarisbrasil') > -1) {
						$wsURLBase = "http://sys.solarisbrasil.com.br/SIS/webservice/wsSIS.asmx?WSDL";
					} else {
						$wsURLBase = "http://sisqa.solarisbrasil.com.br/sis/webservice/wsSIS.asmx?WSDL";
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

                    // Get Contato
                    if ($bean->myasp_contact_id_c != "") {
                        $pContato =  array("Token" => $token, "ID_Contato" => $bean->myasp_contact_id_c);

                        $result = $client->GetContato($pContato);
                        $res = json_decode($result->GetContatoResult);

                        $id_contato = $res->result[0]->ID_Contato;

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "Get - " . $id_contato . " - " . $bean->debug_c . " - " . $debug;
                    } else
                        $id_contato = "";

                    $contato = new StdClass;
                    $contato->ID_Cliente = $parentAccount->myasp_account_id_c;
                    $contato->ID_Area = $area[$bean->area_c]; // Obrigat�rio
                    $contato->Nome = $bean->first_name . " " . $bean->last_name;
                    $contato->Email = $bean->emailAddress->getPrimaryAddress($bean);
                    $contato->Telefone = $bean->phone_work;
                    $contato->Celular = $bean->phone_mobile;
                    $contato->Observacao = $bean->description;
                    $contato->ID_CargoContato = 22; // Obrigat�rio
                    $contato->ID_FuncionarioInclusao = $current_user->myasp_user_id_c;
                    $contato->DataInclusao = date("Y-m-d H:i:s");
                    $contato->ID_FuncionarioAlteracao = $current_user->myasp_user_id_c;
                    $contato->DataAlteracao = date("Y-m-d H:i:s");
                    $contato->ID_OrigemCliente = $parentAccount->origem_c;
                    $contato->ID_SubOrigemCliente = 147;
                    $contato->ID_Saudacao = "";
                    $contato->ID_DepartamentoContato = 1; // Obrigat�rio
                    $contato->DataNascimento = "";
                    $contato->DDDTelefone = "11";
                    $contato->DDDCelular = "11";
                    $contato->FlagAtivo = 1;
                    $contato->ID_TipoContato = 2;

                    $bean->debug_c = json_encode($contato) . "-" . $parentAccount->myasp_account_id_c . " - " . $bean->debug_c;

                    if ($id_contato != "") {
                        $bean->myasp_contact_id_c = $id_contato;

                        $contato->DataAlteracao = $bean->date_modified;
                        $contato->DataAtualizacao = $bean->date_modified;
                        $pContato =  array("Token" => $token, "ID_Contato" => $id_contato, "Values" => $contato);

                        $result = $client->AtualizarContato($pContato);
                        $res = json_decode($result->AtualizarContatoResult);

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "Update - " . $parentAccount->myasp_account_id_c . " - " . $bean->debug_c . $debug;

                        $mensagem = $res->result[0]->mensagem;
                        if ($mensagem != "Sucesso") {
                            throw new SugarApiException("Erro na Atualiza��o do SIS");
                            return;
                        }
                    } else {
                        $pContato =  array("Token" => $token, "Values" => $contato);

                        $result = $client->InserirContato($pContato);
                        $res = json_decode($result->InserirContatoResult);

                        $id_contato = $res->result[0]->ID_Contato;
                        $bean->myasp_contact_id_c = $id_contato;

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "Insert - " . $bean->debug_c . $debug;
                    }
                }
            }
            $bean->flagsis_c = 0;
            $bean->save();
        }
    }

?>