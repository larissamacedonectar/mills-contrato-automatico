<?php

   if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

   require_once "custom/IntegraSAP/requestAPI_SAP.php";

   class account_sap_class 
   {
	  function before_save_method($bean, $event, $arguments)
	  {
		global $db;  
		  
		//store as a new bean property
		$bean->stored_fetched_row_c = $bean->fetched_row;
		
		$cidade_bean = BeanFactory::retrieveBean("iT4_cidades", $bean->it4_cidades_id_c);
		$bean->cidade_old = $cidade_bean->name;
		
		$bean->estado_old = $this->checkEstado($bean->it4_estados_id_c);
	  } 
	   
	   
      function account_sap_method($bean, $event, $arguments)
      {
		global $db;  
		global $current_user;
		
		$urlAccounts = strpos($_REQUEST["__sugar_url"], "Accounts");

		if ($urlAccounts  > 0 ) {  
		
			$currentUserRoles = ACLRole::getUserRoleNames($current_user->id);
		
			$validRoles = $GLOBALS['app_list_strings']['BACKOFFICE_ROLES']; 
		
			if(count(array_intersect($currentUserRoles,$validRoles)) > 0 || is_admin($GLOBALS['current_user']))
			{			
				//Checa se modificaram e-mails na trilha de auditoria
			
				$checkbox_email = false;
				$id = $bean->id;
				$date_modified = $bean->date_modified;
				$date_modified_1 = date('Y-m-d H:i:s',strtotime('-5 seconds',strtotime($date_modified)));
				$audit_table = "SELECT COUNT(id) AS total FROM accounts_audit WHERE parent_id = '$id' AND field_name = 'email' AND date_created > '$date_modified_1'";
				$result = $db->query($audit_table);
				$row = $db->fetchByAssoc($result);
				$total_no_of_audit = $row['total'];	
				//$count_audit = $db->query($audit_table);
				$GLOBALS['log']->fatal("linha 46 integra com audit quantos id?   "   .$total_no_of_audit);
				//$count_audit_value = $count_audit['COUNT(id)'];

				if ($total_no_of_audit > 0)
				{
					$GLOBALS['log']->fatal("Entrou no if da auditoria ");
					$checkbox_email = true;
					
				}
				
				//Fim de checagem de auditoria do e-mail
			
				$cidade_bean = BeanFactory::retrieveBean("iT4_cidades", $bean->it4_cidades_id_c);
				$cidade = $cidade_bean->name;
				$estado = $this->checkEstado($bean->it4_estados_id_c);
				
			
				//verifica se não tem código SAP e se foi assinalado para integrar -->> Integrar
				//OU
				//Verifica que tem código SAP e se algum campo dos dados mestres mudou -->> Integrar
			
				if((
					$bean->integrar_sap_c == "sim" AND 
					($bean->codsap_c == "" OR empty($bean->codsap_c))
					 ) 
					OR 
					(
					($bean->codsap_c != "" OR !empty($bean->codsap_c) ) AND
					(
					$bean->name != $bean->stored_fetched_row_c['name'] OR
					$checkbox_email == true OR
					$bean->billing_address_street != $bean->stored_fetched_row_c['billing_address_street'] OR
					$bean->billing_address_number_c != $bean->stored_fetched_row_c['billing_address_number_c'] OR
					$bean->billing_address_add_c != $bean->stored_fetched_row_c['billing_address_add_c'] OR
					$bean->billing_address_quarter_c != $bean->stored_fetched_row_c['billing_address_quarter_c'] OR
					$cidade != $bean->cidade_old OR 
					$estado != $bean->estado_old OR 
					$bean->billing_address_postalcode != $bean->stored_fetched_row_c['billing_address_postalcode'] OR
					$bean->phone_office != $bean->stored_fetched_row_c['phone_office'] OR
					$bean->phone_alternate != $bean->stored_fetched_row_c['phone_alternate'] OR
					$bean->segmentacao_gtm_c != $bean->stored_fetched_row_c['segmentacao_gtm_c']//volta do phone_alternate;
					//retirada do campo Outros Telefones, inscrições estadual e municipal entre outros do disparo de
					//atualização - Silvio - 13/04/2022 - CA
					)
					)
				)		
				{
				  $GLOBALS['log']->fatal("Entrou no IF de integrar " );
				  
					//Retorna ao valor vazio o campo integrar, mesmo se falhar a integração 
					if( $bean->integrar_sap_c == "sim")
					{
						$id = $bean->id;
						$update = "UPDATE accounts_cstm 
						SET integrar_sap_c = 'nao'
						WHERE id_c = '$id'";
						$db->query($update);
					}
				
				
					// Checagem para não ocorrer loop com os hooks de Oportunidades
					if (array_key_exists('date_modified', $arguments['dataChanges'])) {

						//$GLOBALS['log']->fatal("Data de modificação feita "  );

						if (!array_key_exists('last_opp_date_c', $arguments['dataChanges'])) {

							//$GLOBALS['log']->fatal("Não atualizou a data de proposta "  );
							$this->integrar($bean);
							
						} else {
				
							$GLOBALS['log']->fatal("Não integrou Contas SAP - Ok Hooks de Oportunidades");
				
						}

					} 
					
					else if (!array_key_exists('last_opp_date_c', $arguments['dataChanges']))
					{
						$this->integrar($bean);
				 
					}
				}
			}
		}
	}
	

      function checkCNPJ($cnpjBean) {

         $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpjBean);

         if (strlen($cnpj) != 14)
            return false;

         return $cnpj;

      }

      function listEmails($emailsBean) {

         $listaEmails = array();

         foreach ($emailsBean as $emailAddress) {

           if ($emailAddress['invalid_email'] == false && $emailAddress['primary_address'] == true) {
               $listaEmails[0] = $emailAddress['email_address'];
           } 

         }

         foreach ($emailsBean as $emailAddressSecundary) {

            if ($emailAddressSecundary['invalid_email'] == false && $emailAddressSecundary['primary_address'] == false) {
               $listaEmails[] = $emailAddressSecundary['email_address'];
            }
            
         }

         return $listaEmails;

      } 

      function checkEstado($codEstado) {

         switch ($codEstado) {
            case 1:
                return "BA";
            case 3:
                return "RJ";
            case 4:
                return "RS";
            case 5:
                return "SP";
            case 6:
                return "PR";
            case 7:
                return "DF";
            case 8:
                return "ES";
            case 9:
                return "PE";
            case 10:
                return "SC";
            case 11:
                return "MG";
            case 14:
                return "CE";
            case 15:
                return "GO";
            case 16:
                return "MT";
            case 17:
                return "TO";
            case 18:
                return "RR";
            case 19:
                return "AM";
            case 20:
                return "PB";
            case 21:
                return "AP";
            case 23:
                return "MS";
            case 24:
                return "PA";
            case 25:
                return "RN";
            case 26:
                return "SE";
            case 27:
                return "PI";
            case 28:
                return "RO";
            case 29:
                return "AL";
            case 31:
                return "AC";
            case 32:
                return "MA";
            case 34:
               return "EX";
            case "dd834566-7565-11e9-beea-029395602a62":
               return "EX";
            case "8bbbe8fa-5fd4-11ea-8bb1-06e41dba421a" || null || "":
               return null;

        }
      }
	  
	  function replace($string)
	  {
		$replace = [
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ç' => 'C', 'È' => 'E',
			'É' => 'E', 'Ê' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ò' => 'O', 
			'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',	'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 
			'Ü' => 'U', 'à' => 'a', 'á' => 'a',	'â' => 'a', 'ã' => 'a', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e',	'ì' => 'i', 'í' => 'i', 'î' => 'i', 
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ù' => 'u', 'ú' => 'u', 
			'û' => 'u', 'ü' => 'u'
			]; 
		 
		$string = str_replace(array_keys($replace), $replace, $string);   
		  
		return $string;
	  }
	  
	  
	  function integrar($bean)
	  {
			$emails = $this->listEmails($bean->emailAddress->addresses);
			$cnpj = $this->checkCNPJ($bean->cnpj_c);
			$cep = preg_replace('/[^0-9]/', '', (string) $bean->billing_address_postalcode);


			if ($bean->billing_address_county == "" || empty($bean->billing_address_county ) || strtoupper($bean->billing_address_county) == 'BRASIL') {
				$pais = "BR";
			} else {
				$pais = $bean->billing_address_county;
			}

			if ( $bean->imunicipal_c == "" ||  empty($bean->imunicipal_c)) {
				$insc_municipal = "PENDENTE";  
			} else {
				$insc_municipal = preg_replace('/[^0-9]/', '', (string)  $bean->imunicipal_c);
			}

		
			if ( $bean->im_c == "" || empty($bean->im_c)) {
				//Retirada o valor pendente, horas antes da entrada em produção. Sem tempo para testes em QAS.
				$insc_estadual = "";
			} else {
				$insc_estadual = preg_replace('/[^0-9]/', '', (string) $bean->im_c);
			}
			
			$cidade_bean = BeanFactory::retrieveBean("iT4_cidades", $bean->it4_cidades_id_c);
			$cidade = $cidade_bean->name;
			$estado = $this->checkEstado($bean->it4_estados_id_c);
			
			//o campo "outros telefones"[nome_do_projeto_c] é usado como telefone alternativo ao invés de phone_alternate
			//CA - Silvio Antunes - 12/4/2022 - phone_alternate volta a ser utilizado e teremos um array para primeira
			//integração no SAP (criação) e outro para integrações subsequentes (modificação) - interface I.

			$grupo_cliente_SAP = $GLOBALS['app_list_strings']['grupo_cliente_SAP_list']; //Faz o DE/PARA entre grupo_cliente_SAP_list
			//e segmentacao_gtm_list.


			if($bean->codsap_c != "" OR !empty($bean->codsap_c))
			{
				$account = array(
					"nome_1"=> $bean->name,
					"cnpj"=> $cnpj, // Necessário enviar para o SAP
					"emails"=> $emails,
					"rua" => $this->replace($bean->billing_address_street),
					"numero" => $bean->billing_address_number_c,
					"suplemento" => $this->replace($bean->billing_address_add_c),
					"bairro" => $this->replace($bean->billing_address_quarter_c),
					"cidade" => $cidade,
					"cep" => $cep,
					"estado" => $estado,
					"pais" => $pais, 
					"telefone" => $bean->phone_office,
					"telefone_alternativo" => $bean->phone_alternate,
					"codigo_sap" => $bean->codsap_c,
					"id_sugar" => $bean->id,
					"grupo_cliente" => $grupo_cliente_SAP[$bean->segmentacao_gtm_c],

					"inscricao_municipal" => $insc_municipal, // Retirar após o SAP subir a próxima versão
					"setorindustrial" => "0001", // Retirar após o SAP subir a próxima versão
					"cod_ind" => "9999", // Retirar após o SAP subir a próxima versão

					"grupo_contas" => "ZPJU"
				);
			}
			else
			{
				$account = array(
					"nome_1"=> $bean->name, 
					"cnpj"=> $cnpj,
					"emails"=> $emails,
					"rua" => $this->replace($bean->billing_address_street),
					"numero" => $bean->billing_address_number_c,
					"suplemento" => $this->replace($bean->billing_address_add_c),
					"bairro" => $this->replace($bean->billing_address_quarter_c),
					"cidade" => $cidade,
					"cep" => $cep,
					"estado" => $estado,
					"pais" => $pais, 
					"telefone" => $bean->phone_office,
					"telefone_alternativo" => $bean->phone_alternate,
					"inscricao_estadual" => $insc_estadual,
					"inscricao_municipal" => $insc_municipal,
					"codigo_sap" => $bean->codsap_c,
					"id_sugar" => $bean->id,
					"forma_de_pagamento" => $bean->forma_de_pagamento_c,
					"cnae" => $bean->cnae_c,
					"contribuinte" => $bean->contribuinte_c,
					"porte_receita" => $bean->porte_receita_c,
					"grupo_cliente" => $grupo_cliente_SAP[$bean->segmentacao_gtm_c],

					"setorindustrial" => "0001", // Retirar após o SAP subir a próxima versão
					"cod_ind" => "9999", // Retirar após o SAP subir a próxima versão

					"grupo_contas" => "ZPJU"
				);
			}
			
			$params =  json_encode($account);

			$requestSAP = new requestAPI_SAP_class;
			$res = $requestSAP->sendDataAPI_method($params, 'ZPJU', $bean->id) ;

			$duplicateMessage = "Falha na comunicação com o middleware. Informe a TI e tente mais tarde.";

			if ($res['status'] == false) {
				throw new SugarApiExceptionInvalidParameter($duplicateMessage);
			}
			  
		return;
	  }
	  
   
   }

?>