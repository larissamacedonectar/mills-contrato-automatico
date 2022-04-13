<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class requestAPI_SAP_class
{
    public function sendDataAPI_method($params, $type, $id)
    {
		
		global $GLOBALS;

        $GLOBALS['log']->fatal('JSON enviado SAP: ' . $params);

        if ($type == "PROP") {
            $url = $GLOBALS["app_list_strings"]['MIDDLEWARE']['urlPROP'];
        } else {
            $url = $GLOBALS["app_list_strings"]['MIDDLEWARE']['urlDADOSMESTRES'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer pXlVzxYOAWPg4E4myuvL5HX7uQQCuWFUJFMUEeuSFKyEdfD1XIB_QJ1Tw7A6oL_PU0IVa89gLZxdl-7cd2vBshkpVbdU88qB2AUHudPP2b6FNV-N_Si3EUKVY9r_CWpq_DGC0lcAbu82JNgRTZzCQh4JoTp5fPOcyfRRGS0',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        switch (true) {
            case  ($http_status >= 100 && $http_status <= 199):
                $GLOBALS['log']->fatal('Curl success: Respostas de informação: ' . $http_status);
                $GLOBALS['log']->fatal('Curl success: JSON: ' . $response);
                $status = true;
                break;
            case  ($http_status >= 200 && $http_status <= 299):
                $GLOBALS['log']->fatal('Curl success: Respostas de sucesso: ' . $http_status);
                $GLOBALS['log']->fatal('Curl success: JSON: ' . $response);
                $status = true;
                break;
            case  ($http_status >= 300 && $http_status <= 399):
                $GLOBALS['log']->fatal('Curl alert: Redirecionamentos: ' . $http_status);
                $GLOBALS['log']->fatal('Curl alert: JSON: ' . $response);
                $status = true;
                break;
            case  ($http_status >= 400 && $http_status <= 499 ):
                $GLOBALS['log']->fatal('Curl error:  Erros do cliente: ' . $http_status);
                $GLOBALS['log']->fatal('Curl error: JSON: ' . $response);
                $status = false;
                $this->errorConnectionAPI($id, $type);
                break;
            case  ($http_status >= 500 && $http_status <= 599):
                $GLOBALS['log']->fatal('Curl error:  Erros do servidor: ' . $http_status);
                $GLOBALS['log']->fatal('Curl error: JSON: ' . $response);
                $status = false;
                $this->errorConnectionAPI($id, $type);
                break;
          }

        curl_close($curl);

        return array('status' => $status , 'json' => json_decode($response));
       
    }

    public function errorConnectionAPI($id, $type) {

        $GLOBALS['log']->fatal('Criar registro de auditoria');

        global $db;

        if ($type == 'ZPJU') {
            $module_cstm = 'accounts_cstm';
            $module_audit = 'accounts_audit';
        } else if ($type == 'ZCOB' || $type == 'ZCON') {
            $module_cstm = 'contacts_cstm';
            $module_audit = 'contacts_audit';
        } else if ($type == 'ZLEN') {
            $module_cstm = 'a1_marketwatch_cstm';
            $module_audit = 'a1_marketwatch_audit';
        } else if ($type == 'PROP') {
            $module_cstm = 'quotes_cstm';
            $module_audit = 'quotes_audit';
        }

        $dataTentativa = date("Y-m-d H:i:s");
        //$nDateAudit = DateTime::createFromFormat('Y-m-d H:i:s' , $dataAudit, new DateTimeZone('PST'));
        //$nDateAudit->setTimezone(new DateTimeZone('EST'));
        //$dataTentativa = $nDateAudit->format('Y-m-d H:i:s');
        
        $msg = 'Falha na comunicação com o Middlware/SAP. Informe a TI e tente mais tarde.';

        $update = "UPDATE $module_cstm 
        SET data_integracao_sap_c = '$dataTentativa',
            msg_interacao_sap_c = '$msg',
            status_integracao_sap_c = false
        WHERE id_c = '$id'";

        //$GLOBALS['log']->fatal('UPDT: ' .  $update);

        $db->query($update);

        // Array dos campos da rastreabilidade
        $listFieldsEdit = [
            [
                "id_audit" => create_guid(),
                "field_name" => "data_integracao_sap_c", 
                "data_type" => "datetime", 
                "before_value_string" => "", 
                "after_value_string" => $dataTentativa, 
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
                "after_value_string" => false, 
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
                               '$dataTentativa', 
                               '1', 
                               '$dataTentativa', 
                               '$field_name', 
                               '$data_type', 
                               '$before_value_string', 
                               '$after_value_string', 
                               '$before_value_text',
                               '$after_value_text'
                               );
                        ";
            $res = $db->query($insert_audit);

            //$GLOBALS['log']->fatal('INST: ' .  $insert_audit);

            if ($res == false) {
                $error_sql_audit = true;
            }
        }

        return true;
    }
}

