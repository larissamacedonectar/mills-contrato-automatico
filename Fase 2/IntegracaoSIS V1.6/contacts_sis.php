<?php

    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    class Contacts_SIS
    {

        function postSIS(&$bean, $event, $arguments)
        {
            global $db;
            global $log;
            global $current_user;
            $notify_user = ($bean->modified_user_id != "") ? $bean->modified_user_id : "1";

            $fromLeads = strpos($_REQUEST["__sugar_url"], "Leads/");
            $fromMass = strpos($_REQUEST["__sugar_url"], "MassUpdate");
            $bean->phone_mobile = preg_replace( '/[^0-9]/is', '', $bean->phone_mobile);
            $bean->phone_work = preg_replace( '/[^0-9]/is', '', $bean->phone_work);
            $bean->phone_mobile = $this->mask_tel($bean->phone_mobile);
            $bean->phone_work = $this->mask_tel($bean->phone_work);

            // $current_user = new User();
            // $current_user->retrieve($bean->assigned_user_id);
            $GLOBALS['log']->fatal('CONTATO COM SIS' . $_REQUEST["__sugar_url"]);
            $GLOBALS['log']->fatal($current_user->myasp_user_id_c . " -- " . $bean->flagsis_c . " -- " . $fromLeads . " -- " . $fromMass);
            
            if (($current_user->myasp_user_id_c != "") && ($bean->flagsis_c != 1) && ($fromLeads === false) && ($fromMass === false)) {
                $GLOBALS['log']->fatal('Contact WS Run: ' . $_REQUEST["__sugar_url"]);
                $relatedAccounts = $bean->accounts->getBeans();
                $parentAccount = false;
                if (!empty($relatedAccounts)) {
                    reset($relatedAccounts);
                    $parentAccount = current($relatedAccounts);
                }
                $GLOBALS['log']->fatal('parentAccount:' . json_encode($parentAccount));
                if ($parentAccount !== false && is_object($parentAccount))
                {
                    $area["comercial"] = 4;
                    $area["tecnica"] = 5;
                    $area["administracao"] = 7;
                    $area["cobranca"] = 3;
                    $area[""] = 0;

                    // SIS API Connect
					/*if (strpos($GLOBALS['sugar_config']['site_url'], 'https://solarisbrasil.sugarondemand.com') > -1) {
						$wsURLBase = "http://sys.solarisbrasil.com.br/SIS/webservice/wsSIS.asmx?WSDL";
					} else {
						$wsURLBase = "http://sisqa.solarisbrasil.com.br/sis/webservice/wsSIS.asmx?WSDL";
					}*/
                    //TK-076-000248 - Apontamento de integração para o Sis - Pegando URL via lista suspensa - Maria Mattos 22/02/2022
                    $wsURLBase = $GLOBALS['app_list_strings']['MIDDLEWARE']['urlSIS'];
					
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
                    $GLOBALS['log']->fatal('myasp_contact_id_c:' . $bean->myasp_contact_id_c);
                    if ($bean->myasp_contact_id_c != "") {
                        $pContato =  array("Token" => $token, "ID_Contato" => $bean->myasp_contact_id_c);
                        $log->fatal("pContato");
                        $log->fatal($pContato);
                        $log->fatal(json_encode($pContato));

                        $result = $client->GetContato($pContato);
                        $log->fatal(json_encode($pContato));
                        $res = json_decode($result->GetContatoResult);

                        $id_contato = $res->result[0]->ID_Contato;

                        $debug = json_encode($res->result[0]);
                        $GLOBALS['log']->fatal('id_contato:' . $id_contato);
                        $bean->debug_c = "Get - " . $id_contato . " - " . $bean->debug_c . " - " . $debug;
                    } else
                        $id_contato = "";

                    $contato = new StdClass;
                    $contato->ID_Cliente = $parentAccount->myasp_account_id_c;
                    $contato->ID_Area = ($area[$bean->area_c] != "") ? $area[$bean->area_c] : "4"; // Obrigat�rio
                    $contato->Nome = $bean->first_name . " " . $bean->last_name;
                    $email = $bean->emailAddress->getPrimaryAddress($bean);
                    $contato->Email = ($email != "") ? $email : "sememail@sememail.com.br"; //
                    $contato->Telefone = ($bean->phone_work != "") ? $bean->phone_work : "1"; //
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
                    $contato->ID_TipoContato = 2; //
                    $GLOBALS['log']->fatal('CONTATO CRIADO: ' . json_encode($contato));
                    $bean->debug_c = json_encode($contato) . "-" . $parentAccount->myasp_account_id_c . " - " . $bean->debug_c;

                    if ($id_contato != "") {
                        $bean->myasp_contact_id_c = $id_contato;

                        $contato->DataAlteracao = $bean->date_modified;
                        $contato->DataAtualizacao = $bean->date_modified;
                        $pContato =  array("Token" => $token, "ID_Contato" => $id_contato, "Values" => $contato);

                        $log->fatal("contact = ");
                        $log->fatal($pContato);

                        $result = $client->AtualizarContato($pContato);
                        $res = json_decode($result->AtualizarContatoResult);

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "Update - " . $parentAccount->myasp_account_id_c . " - " . $bean->debug_c . $debug;

                        $log->fatal("id = " . $bean->id);
                        $log->fatal("debug_c = " . $debug);

                        $mensagem = $res->result[0]->mensagem;
                        if ($mensagem != "Sucesso" && $mensagem != "") {
                            $notifyBean = BeanFactory::newBean('Notifications');
                            $notifyBean->assigned_user_id = $current_user->id;
                            $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                            $notifyBean->parent_type = $bean->module_name;
                            $notifyBean->parent_id = $bean->id;
                            $notifyBean->created_by = $current_user->id;
                            $notifyBean->name = "Erro na Atualização do SIS: " . $mensagem;
                            $notifyBean->save();
                        }
                    } else {
                        
                        $pContato =  array("Token" => $token, "Values" => $contato);
                        $GLOBALS['log']->fatal('INSERINDO CONTATO: ' . json_encode($pContato));

                        $result = $client->InserirContato($pContato);
                        $res = json_decode($result->InserirContatoResult);
                        $GLOBALS['log']->fatal('RESPOSTA: ' . json_encode($res));
                        $id_contato = $res->result[0]->ID_Contato;
                        $bean->myasp_contact_id_c = $id_contato;

                        $debug = json_encode($res->result[0]);
                        $bean->debug_c = "Insert - " . $bean->debug_c . $debug;

                        $mensagem = $res->result[0]->mensagem;
                        if ($mensagem != "Sucesso" && $mensagem != "") {
                            $notifyBean = BeanFactory::newBean('Notifications');
                            $notifyBean->assigned_user_id = $current_user->id;
                            $notifyBean->severity = 'warning'; // Check notifications_severity_list for available option
                            $notifyBean->parent_type = $bean->module_name;
                            $notifyBean->parent_id = $bean->id;
                            $notifyBean->created_by = $current_user->id;
                            $notifyBean->name = "Erro na Atualização do SIS: " . $mensagem;
                            $notifyBean->save();
                        }
                    }
                }
            }
            $bean->flagsis_c = 0;
        }

        function mask_tel($TEL) {
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
