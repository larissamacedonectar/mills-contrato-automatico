<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class RetornoContaSAP_API extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            //GET & POST
            'AccountsInfoEndpoint' => array(
                //request type
                'reqType' => array('POST'),

                //set authentication
                'noLoginRequired' => false,

                //endpoint path
                'path' => array('RetornoContaSAP_API'),

                //endpoint variables
                'pathVars' => array('', ''),

                //method to call
                'method' => 'RetornoContaSAP_API_method',

                //short help string to be displayed in the help documentation
                'shortHelp' => 'Endpoint to get info after quote was created',

                //long help to be displayed in the help documentation
                'longHelp' => 'custom/clients/base/api/help/RetornoContaSAP_API_help.html',
            ),
            'AccountsCreditoInfoEndpoint' => array(
                'reqType' => array('POST'),
                'noLoginRequired' => false,

                //This time, we'll include a third element with a ?
                //So now the path is Describe/Request/{dataFromURL}
                'path' => array('RetornoContaSAP_API','Credito'),

                //Here, we specify the key for accessing that data within the function
                'pathVars' => array('', ''),
                
                //method to call
                'method' => 'RetornoContaCreditoSAP_API_method',
                
                //short help string to be displayed in the help documentation
                'shortHelp' => 'Endpoint to get info after quote was created',

                //long help to be displayed in the help documentation
                'longHelp' => 'custom/clients/base/api/help/RetornoContaSAP_API_help.html',
            ),
        );
    }

	public function RetornoContaSAP_API_method($api, $args) {

		require_once 'custom/Saneamento/ValidadorDados/validadorDados.php';
        
        if(!empty($args["IDConta"])) {
            global $db, $log;

            // Accounts
            $id = $args["IDConta"];
            $id = strtolower($id);
            $accountBean = BeanFactory::retrieveBean("Accounts",$id);
            if(empty($accountBean)){
                $api->getResponse()->setStatus("422");
                return new SugarApiExceptionInvalidParameter('Id conta SugarCRM n??o encontrado');
            }

            $billing_address_street = $args["rua"];
            $billing_address_postalcode = $args["cep"];
            $billing_address_country = $args["pais"];
            $phone_office = $args["telefone"];
            $phone_alternate = $args["telefone_alternativo"];
    
            $emails = $args["emails"];
    
            // Accounts_cstm
            $cod_sap_c = $args["cod_sap"];
            $billing_address_number_c = $args["numero"];
            $billing_address_add_c = $args["complemento"];
            $billing_address_quarter_c = $args["bairro"];
            $nomeCidade = $args["cidade"];
            $nomeEstado = $args["estado"];

            $forma_pagamento_SAP = $GLOBALS['app_list_strings']['forma_pagamento_sap_list'];
            $forma_pagamento = '';
            foreach($forma_pagamento_SAP as $key=>$value){
                if($value == $args['forma_pagamento']){
                    $forma_pagamento = $key;
                    $GLOBALS['log']->fatal("ARGS restorno Track Sap - foreach". $forma_pagamento);
                }
            }
            
            $im_c = $args["inscricao_estadual"];
            $imunicipal_c = $args["inscricao_municipal"];
            $cnae_c = $args["cnae"];
            $contribuinte_c = $args["contribuinte"];
            $data_integr_int_ii_c = $args["sap_integrou_sugar"];

            // Passando pelo saneaemento
            
            $logSaneamento = array();
            $saneamentoCEP = new validadorDados;

            if (!empty($billing_address_postalcode)) {
                $resultadoValidacaoCEP = $saneamentoCEP->cepValida($billing_address_postalcode);
                if ($resultadoValidacaoCEP["status"] == false) {
                    $logSaneamento["CEP"] = $resultadoValidacaoCEP["resultado"];
                } else {
                    $billing_address_postalcode = $resultadoValidacaoCEP["resultado"];
                }
            }
            
            if (!empty($nomeCidade)) {
                $resultadoValidacaoCidade = $this->buscarCidadePorSemelhanca($nomeCidade);  
                if ($resultadoValidacaoCidade["status"] == false) {
                    $logSaneamento["Cidade"] = $resultadoValidacaoCidade["resultado"];
                } else {
                    $it4_cidades_id_c = $resultadoValidacaoCidade["resultado"];
                }
            }

            if (!empty($nomeEstado)) {
                $resultadoValidacaoEstado = $this->buscarEstadoPorSemelhanca($nomeEstado);  
                if ($resultadoValidacaoEstado["status"] == false) {
                    $logSaneamento["Estado"] = $resultadoValidacaoEstado["resultado"];
                } else {
                    $it4_estados_id_c = $resultadoValidacaoEstado["resultado"];
                }
            }

            if (!empty($phone_office)) {
                $saneamentoTelefone = new validadorDados;
                $resultadoValidacaoTelefone = $saneamentoTelefone->telefoneValida($phone_office);  
                if ($resultadoValidacaoTelefone["status"] == false) {
                    $logSaneamento["Telefone"] = $resultadoValidacaoTelefone["resultado"];
                } else {
                    $phone_office = $resultadoValidacaoTelefone["resultado"];
                }
            }

            if (!empty($phone_alternate)) {
                $saneamentoTelefoneAlternativo = new validadorDados;
                $resultadoValidacaoTelefoneAlternativo = $saneamentoTelefoneAlternativo->telefoneValida($phone_alternate); 
                if ($resultadoValidacaoTelefoneAlternativo["status"] == false ) {
                    $logSaneamento["TelefoneAlternativo"] = $resultadoValidacaoTelefoneAlternativo["resultado"];
                } else {
                    $phone_alternate = $resultadoValidacaoTelefoneAlternativo["resultado"];
                }
            }

            //return array("arg" => $args, "CEP" => $resultadoValidacaoCEP, "telefone" => $resultadoValidacaoTelefone);

            if (!empty($logSaneamento)) {
                $api->getResponse()->setStatus("404");
                return $logSaneamento;
            }

            if(($contribuinte_c == 1 || $contribuinte_c == 2) && empty($im_c)) {
                $api->getResponse()->setStatus("422");
                return new SugarApiExceptionMissingParameter('Para esse tipo de Contribuinte a Inscri????o Estadual ?? obrigat??rio!');
            }
            
            //ajuste para cen??rio onde SAP atualiza conta sem codsap_c no sugar
            $insert_cod_sap = '';
            if(empty($accountBean->codsap_c)){
                $insert_cod_sap = "codsap_c = '$cod_sap_c', "; 
            }

            try {
                $update_contas = "UPDATE accounts
                                  SET      
                                    billing_address_street = '$billing_address_street',
                                    billing_address_postalcode = '$billing_address_postalcode',
                                    billing_address_country = '$billing_address_country',
                                    phone_office = '$phone_office',
                                    phone_alternate = '$phone_alternate'
                                 WHERE id = '$id'";
                $db->query($update_contas);
    
    
                $update_contas_cstm = "UPDATE accounts_cstm
                                       SET   ".$insert_cod_sap."       
                                            billing_address_number_c = '$billing_address_number_c',
                                            billing_address_add_c = '$billing_address_add_c',
                                            billing_address_quarter_c = '$billing_address_quarter_c',
                                            it4_cidades_id_c = '$it4_cidades_id_c',
                                            it4_estados_id_c = '$it4_estados_id_c',
                                            forma_de_pagamento_c = '$forma_pagamento',
                                            im_c = '$im_c',
                                            imunicipal_c = '$imunicipal_c',
                                            cnae_c = '$cnae_c',
                                            contribuinte_c = '$contribuinte_c',
                                            data_integr_int_ii_c = '$data_integr_int_ii_c'
                                       WHERE id_c = '$id'";
                $log->fatal($update_contas_cstm);
                $db->query($update_contas_cstm);
    
                $emailContato = new SugarEmailAddress;
                $i = 0;
                foreach ($emails as $emailObject) {
                    if($i == 0) {
                        $emailContato->addAddress($emailObject, true);
                    } else {
                        $emailContato->addAddress($emailObject, false);
                    }
                    $i++;
                }
                $emailContato->save($id, "Accounts");

                return array("status" => true, "mensagem" => "Conta atualizada com sucesso!" );
                //return array("status" => true, "mensagem" => "Conta atualizada com sucesso!", "update_contas" => $update_contas, "update_contas_cstm" => $update_contas_cstm );
            }
            catch(Exception $e) {
    
                return array("status" => false, "mensagem" => "Ocorreu um erro ao atualizar a conta. " . $e);
            
            }
    
        } else {
            $api->getResponse()->setStatus("422");
            return new SugarApiExceptionMissingParameter('O par??metro IDConta est?? vazio!');

        }

	}
	

    public function RetornoContaCreditoSAP_API_method($api, $args) {

        if(!empty($args["IDConta"])) {
            global $db;

            // Accounts
            $id = $args["IDConta"];
            $id = strtolower($id);
            $accountBean = BeanFactory::retrieveBean("Accounts",$id);
            if(empty($accountBean)){
                $api->getResponse()->setStatus("422");
                return new SugarApiExceptionInvalidParameter('Id conta SugarCRM n??o encontrado');
            }

            $rating = $args["rating"];
    
            $emails = array($args["emails"]);

            // Accounts_cstm
            $cod_sap_c = $args["codSap"];

            $data_integr_sap_sugar_cred_c = $args["dt_integracao_cred_sap"];
            $conta_credito_c = $args["conta_credito"];
            $data_ult_verif_cred_cli_c = $args["dt_ultima_verificacao"];
            $limite_total_c = $args["limit_total_credito"];
            $limite_disponivel_c = $args["credito_disponivel"];
            $compromisso_total_c = $args["credito_compromissado"];
            $a_receber_c = $args["a_receber"];
            $a_vencer_c = $args["a_vencer"];
            $vencido_abaixo_c = $args["vencido_cobranca"];
            $vencido_acima_c = $args["vencido_credito"];
            $vencido_total_c = $args["total_vencido"];
            $inad_cred_c = $args["inadimplente_credito"];
            $inad_cobr_c = $args["inadimplente_cobranca"];
    
            if(($contribuinte_c == 1 || $contribuinte_c == 2) && empty($im_c)) {
                
                return new SugarApiExceptionMissingParameter('Para esse tipo de Contribuinte a Inscri????o Estadual ?? obrigat??rio!');
            }
    
            try {
                $update_contas = "UPDATE accounts
                                  SET        
                                    rating = '$rating'
                                 WHERE id = '$id'";
                $db->query($update_contas);
    
    
                $update_contas_cstm = "UPDATE accounts_cstm
                                       SET 
                                            data_integr_sap_sugar_cred_c =  DATE_ADD('$data_integr_sap_sugar_cred_c', INTERVAL 3 HOUR),
                                            conta_credito_c = '$conta_credito_c',
                                            data_ult_verif_cred_cli_c = '$data_ult_verif_cred_cli_c ',
                                            limite_total_c = ' $limite_total_c ',
                                            limite_disponivel_c = '$limite_disponivel_c ',
                                            compromisso_total_c = '$compromisso_total_c',
                                            a_receber_c = '$a_receber_c',
                                            a_vencer_c = '$a_vencer_c ',
                                            vencido_abaixo_c = '$vencido_abaixo_c ',
                                            vencido_acima_c = '$vencido_acima_c',
                                            vencido_total_c ='$vencido_total_c',
                                            inad_cred_c = '$inad_cred_c',
                                            inad_cobr_c = '$inad_cobr_c '
                                       WHERE id_c = '$id'";
                $db->query($update_contas_cstm);

                return array("status" => true, "mensagem" => "Conta atualizada com sucesso!" );
                //return array("status" => true, "mensagem" => "Conta atualizada com sucesso!", "update_contas" => $update_contas, "update_contas_cstm" => $update_contas_cstm );
            }
            catch(Exception $e) {
    
                return array("status" => false, "mensagem" => "Ocorreu um erro ao atualizar a conta. " . $e);
            
            }
    
        } else {
            $api->getResponse()->setStatus("422");
            return new SugarApiExceptionMissingParameter('O par??metro IDConta est?? vazio!');

        }

	}

    function buscarCidadePorSemelhanca($cidade){

		global $db;
		$select = "SELECT id, name FROM it4_cidades WHERE name = '$cidade'AND deleted = 0 limit 1";
		$res = $db->query($select);
		$row = $db->fetchByAssoc($res);
		
		if(empty($row["id"])) {
			return array(
				'status' => false,
				'resultado' => "Cidade n??o encontrada."
			);
		}  else {
			return array(
				'status' => true,
				'resultado' => $row["id"]
			);
		}
	}

	function buscarEstadoPorSemelhanca($estado){


		global $db;

		$select = "SELECT  id, name FROM it4_estados WHERE name = '$estado' AND deleted = 0 limit 1";
		$res = $db->query($select);
		$row = $db->fetchByAssoc($res);
		
		if(empty($row["id"])) {
			return array(
				'status' => false,
				'resultado' => "Cidade n??o encontrada."
			);
		}  else {
			return array(
				'status' => true,
				'resultado' => $row["id"]
			);
		}

	}
}