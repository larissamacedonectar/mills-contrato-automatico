<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/SugarQuery/SugarQuery.php');
require_once('include/api/SugarApiException.php');
include_once('modules/ACLRoles/ACLRole.php');
require_once('include/SugarLogger/LoggerManager.php'); 
require_once('custom/IntegraSAP/requestAPI_SAP.php');
	
class integra_contato_sap {
	
	function before_save_method($bean, $event, $arguments)
	  {
		global $db;  
		  
		//store as a new bean property
		$bean->stored_fetched_row_c = $bean->fetched_row;
	  }
	
	function afterSave($bean, $event, $arguments){
		global $db;
		global $current_user;
		global $log;

		// Tratamento para o Mobile que não tem esse campo no layout
		if ($arguments['api']->platform == "mobile" || empty($bean->categoria_contato_c)) {
			$bean->categoria_contato_c = 'comercial';

			$update = "UPDATE contacts_cstm SET categoria_contato_c = 'comercial' WHERE id_c = '$bean->id'";
			$db->query($update);

		}

		// Verificar URL se contém Contacts
		$urlQuotes = strpos($_REQUEST["__sugar_url"], "Quotes");

		if ($urlQuotes == 0) {
			
			$currentUserRoles = ACLRole::getUserRoleNames($current_user->id);
		
			$validRoles = $GLOBALS['app_list_strings']['BACKOFFICE_ROLES']; 
			
			if(count(array_intersect($currentUserRoles,$validRoles)) > 0 || is_admin($GLOBALS['current_user']))
			{
			
				
				
				$account_bean = BeanFactory::getBean("Accounts", $bean->account_id);
				
				$GLOBALS['log']->fatal("Entrou no começo de integrar SAP e procurou conta vinculada " );
				
				if ($account_bean->codsap_c != "" OR !empty($account_bean->codsap_c))
				{
					
					//Checa se modificaram e-mails na trilha de auditoria
				
					$checkbox_email = false;
					$id = $bean->id;
					$date_modified = $bean->date_modified;
					$date_modified_1 = date('Y-m-d H:i:s',strtotime('-5 seconds',strtotime($date_modified)));
					$audit_table = "SELECT COUNT(id) AS total FROM contacts_audit WHERE parent_id = '$id' AND field_name = 'email' AND date_created > '$date_modified_1'";
					$result = $db->query($audit_table);
					$row = $db->fetchByAssoc($result);
					$total_no_of_audit = $row['total'];	
					//$count_audit = $db->query($audit_table);
					$GLOBALS['log']->fatal("linha 66 - contacts integra com audit quantos id pegos?   "   .$total_no_of_audit);
					//$count_audit_value = $count_audit['COUNT(id)'];

					if ($total_no_of_audit > 0)
					{
						$checkbox_email = true;
						
					}
					
					//Fim de checagem de auditoria do e-mail
					
					
					if(  (
						$bean->integrar_sap_c == "sim" AND 
						($bean->codsap_c == "" OR empty($bean->codsap_c))
						 ) 
						OR 
						(
						($bean->codsap_c != "" OR !empty($bean->codsap_c) ) AND
						(
						$bean->first_name != $bean->stored_fetched_row_c['first_name'] OR
						$bean->last_name != $bean->stored_fetched_row_c['last_name'] OR
						$checkbox_email == true OR
						$bean->phone_work != $bean->stored_fetched_row_c['phone_work'] OR
						$bean->phone_mobile != $bean->stored_fetched_row_c['phone_mobile'] OR
						$bean->primary_address_street != $bean->stored_fetched_row_c['primary_address_street'] OR
						$bean->primary_address_postalcode != $bean->stored_fetched_row_c['primary_address_postalcode'] OR
						$bean->primary_address_quarter_c != $bean->stored_fetched_row_c['primary_address_quarter_c'] OR
						$bean->primary_address_state != $bean->stored_fetched_row_c['primary_address_state'] OR
						$bean->primary_address_city != $bean->stored_fetched_row_c['primary_address_city'] OR
						$bean->primary_address_number_c != $bean->stored_fetched_row_c['primary_address_number_c'] OR
						$bean->primary_address_add_c != $bean->stored_fetched_row_c['primary_address_add_c'] OR
						$bean->primary_address_country != $bean->stored_fetched_row_c['primary_address_country'] OR
						$bean->preferred_language != $bean->stored_fetched_row_c['preferred_language'] 
						)
						)
					)		
					{
					  $GLOBALS['log']->fatal("Entrou no IF de integrar " );
					  
						//Retorna ao valor vazio o campo integrar, mesmo se falhar a integração 
						if( $bean->integrar_sap_c == "sim")
						{
							$id = $bean->id;
							$update = "UPDATE contacts_cstm 
							SET integrar_sap_c = 'nao'
							WHERE id_c = '$id'";
							$db->query($update);
						}

						$GLOBALS['log']->fatal('entrando na integração Sap-Contacts');
							
						$emails = $this->listEmails($bean->emailAddress->addresses);
						
						//	Enviar somente números
						if ($bean->categoria_contato_c == "faturamento")
						{
							$tipo = "ZCOB";
						}
						else if ($bean->categoria_contato_c == "comercial")
						{
							$tipo = "ZCON";
						}

						/// Refernciando Areas antigas com Departamento do SAP
						$departamento = $bean->area_c;
					   
						if ($departamento == "administracao")
						{
							$departamento = "0005"; // ou 0011?
						}
						else if ($departamento == "cobranca")
						{
							$departamento = "0017";
						}
						else if ($departamento == "comercial")
						{
							$departamento = "0013";
						}   
						else if ($departamento == "tecnica")
						{
							$departamento = "0020";
						}
						
						if ($bean->primary_address_country == "" || empty($bean->primary_address_country ) || strtoupper($bean->primary_address_country) == 'BRASIL' ) {
							$pais = "BR";
						} else {
							$pais = $bean->primary_address_country;
						}

						if ($bean->phone_work == "" || empty($bean->phone_work)) {
							$telefone = $bean->phone_mobile;
						} else {
							$telefone = $bean->phone_work; 
						}

						if($bean->preferred_language == 'pt_BR' || $bean->preferred_language == '' ||
						   empty($bean->preferred_language))
						   {
							   $idioma = 'PT';
						   }
						   else
						   {
							   $idioma = $bean->preferred_language;
						   }
		 
						
						//o ID da Contato Sugar, Código Contato SAP (preenchido ou vazio), Código Conta SAP (obrigatório) e demais os dados (campos) do Contatos que estão disponíveis para consulta na Planilha de DE/PARA. Para esse cenário, 

						if($tipo == "ZCON")
						{
							$arrayJson = array(
								"nome_1" => $this->replace($bean->first_name),
								"nome_2" => $this->replace($bean->last_name),
								"telefone" => $telefone,
								"telefone_alternativo" => $bean->phone_mobile,
								"departamento" => $departamento,
								"emails" => $emails,
								"id_sugar" => $bean->id,
								"codigo_sap_contato" => $bean->codsap_c,
								"idioma" => $idioma,
								//Dados da Conta
								"id_sugar_conta" => $bean->account_id,
								"codigo_sap" => $account_bean->codsap_c,
								//Tipo
								"grupo_contas" => $tipo,
							);
						}
						else if($tipo == "ZCOB")
						{
							//Enviar CEP como apenas números
							$postal_code = preg_replace('/[^0-9]/', '', (string) $bean->primary_address_postalcode);
							
							//Prepara JSON
							$arrayJson = array(
								"nome_1" => $this->replace($bean->first_name),
								"nome_2" => $this->replace($bean->last_name),
								"telefone" => $telefone,
								"telefone_alternativo" => $bean->phone_mobile,
								"emails" => $emails,
								"id_sugar" => $bean->id,
								"codigo_sap" => $bean->codsap_c,

								//endereço
								"rua" => $this->replace($bean->primary_address_street),
								"cep" => $postal_code,
								"bairro" => $this->replace($bean->primary_address_quarter_c),
								"estado" => $this->replace($bean->primary_address_state),
								"pais" => $this->replace($pais),
								"cidade" => $this->replace($bean->primary_address_city),
								"numero" => $bean->primary_address_number_c,
								"suplemento" => $this->replace($bean->primary_address_add_c),

								//Dados da Conta
								//"id_sugar_conta" => $bean->account_id,
								//"codigo_sap" => $account_bean->codsap_c,
								//Tipo
								"grupo_contas" => $tipo,
							);
						}
						
						
						$json = json_encode($arrayJson);
						//$tipojson = json_encode($tipo);
						
						$requestSAP = new requestAPI_SAP_class;
						$res = $requestSAP->sendDataAPI_method($json, $tipo, $bean->id);
						
						$duplicateMessage = "Falha na comunicação com o middleware. Informe a TI e tente mais tarde.";

						if ($res['status'] == false) {
						throw new SugarApiExceptionInvalidParameter($duplicateMessage);
						}
					}	
				}	
				else 
				{
						
						$GLOBALS['log']->fatal("Entrou no ELSE pos não há codsap na conta " );
						
						if( $bean->integrar_sap_c == "sim")
						{
							$id = $bean->id;
							$update = "UPDATE contacts_cstm 
							SET integrar_sap_c = 'nao'
							WHERE id_c = '$id'";
							$db->query($update);
						}
						
						$id = $bean->id;
						$module = 'Contacts'; 
						$module_cstm = strtolower($module) . '_cstm';
						$module_audit = strtolower($module) . '_audit';
						
						$dataIntegracao = date("Y-m-d H:i:s");

										
						$msg  = "A Conta vinculada não possui Código SAP";
						$status = false;
						
						$update = "UPDATE $module_cstm 
						SET data_integracao_sap_c = '$dataIntegracao',
						msg_interacao_sap_c = '$msg',
						status_integracao_sap_c = '$status'
						WHERE id_c = '$id'";
									
						$db->query($update);
						
						// Inicio: Inserts na tabela de audit
						$data_integracao_audit = date("Y-m-d H:i:s");	
						
						
						// Array dos campos da rastreabilidade
						$listFieldsEdit = [
							[
								"id_audit" => create_guid(),
								"field_name" => "data_integracao_sap_c", 
								"data_type" => "datetime", 
								"before_value_string" => "", 
								"after_value_string" => $dataIntegracao, 
								"before_value_text" => "", 
								"after_value_text" => ""
							],

							[
								"id_audit" => create_guid(),
								"field_name" => "msg_interacao_sap_c", 
								"data_type" => "text", 
								"before_value_string" => "", 
								"after_value_string" => "", 
								"before_value_text" => "", 
								"after_value_text" => $msg
							],

							[
								"id_audit" => create_guid(),
								"field_name" => "status_integracao_sap_c", 
								"data_type" => "bool", 
								"before_value_string" => "", 
								"after_value_string" => $status, 
								"before_value_text" => "", 
								"after_value_text" => ""
							],

						];

					foreach ($listFieldsEdit as $field) {

						$id_audit = $field['id_audit'];
						$field_name = $field['field_name'];
						$data_type = $field['data_type'];
						$before_value_string = $field['before_value_string'];
						$after_value_string = $field['after_value_string'];
						$before_value_text = $field['before_value_text'];
						$after_value_text = $field['after_value_text'];

						$insert_audit = "INSERT INTO $module_audit
												 (id, 
												  parent_id, 
												  event_id, 
												  date_created, 
												  created_by, 
												  date_updated, 
												  field_name, 
												  data_type, 
												  before_value_string, 
												  after_value_string, 
												  before_value_text, 
												  after_value_text)
									VALUES(
										   '$id_audit', 
										   '$id', 
										   '', 
										   '$data_integracao_audit', 
										   '1', 
										   '$data_integracao_audit', 
										   '$field_name', 
										   '$data_type', 
										   '$before_value_string', 
										   '$after_value_string', 
										   '$before_value_text',
										   '$after_value_text'
										   );
									";
						$res = $db->query($insert_audit);
					
					}
				}

			}
		} else {
	
			$GLOBALS['log']->fatal("Não integrou Contato SAP - Ok Hooks de Oportunidades");

		}

		
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



}



