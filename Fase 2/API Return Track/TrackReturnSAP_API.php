<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

date_default_timezone_set('America/Sao_Paulo');

//You need to inherit the SugarApi Class to
class TrackReturnSAP_API extends SugarApi
{
    public function registerApiRest()
    {
        return array(

            'TrackReturnSAP_API' => array(

                //Array of the acceptable kinds of requests for this method
                'reqType' => array('GET','POST','PUT','DELETE'),

                //If true, anyone can access. If false, only authenticated users.
                'noLoginRequired' => true,

                //Here is the path to access the endpoint, in this case: Describe/Request
                'path' => array('TrackReturnSAP_API'),

                //Specify an empty string for the path variables
                'pathVars' => array('', ''),

                //method to call
                'method' => 'TrackReturnSAP_API_method',

                //A small description, displayed in rest/v10/help page
                'shortHelp' => 'API to receive return track SAP Integration',

                //Further help, displayed when drilling down into the help page
                'longHelp' => 'custom/clients/base/api/help/TrackReturnSAP_APIHelp.html',
            ),
        );

    }
        
    public function TrackReturnSAP_API_method($api, $args)
        {

            global $db;

            // Inicio: valida JSON
            if (array_key_exists("status", $args) == false) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <status> ');
            } else if (array_key_exists("tipo", $args) == false) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <tipo>');
            } else if (array_key_exists("mensagem", $args) == false ) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <mensagem>');
            } else if (array_key_exists("id_sugar", $args) == false) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <id_sugar>');
            } else if (array_key_exists("cod_sap", $args) == false) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <cod_sap>');
            } else if (array_key_exists("data_criacao_sap", $args) == false) { 
                return $return_sugar = array('status' => false, 'msg' => 'É obrigatório enviar o parâmetro <data_criacao_sap>');
            } 
            // Fim: valida JSON

            // Inicio: Valida Tipo
            if ($args['tipo'] == 1) {
                $module = 'Accounts';
            } else if ($args['tipo'] == 2) {
                $module = 'Contacts'; 
            } else if ($args['tipo'] == 3) {
                $module = 'a1_MarketWatch';
            } else if ($args['tipo'] == 4) {
                $module = 'Quotes';
            } else {
                return $return_sugar = array('status' => false, 'msg' => 'Tipo inválido');
            }
            // Fim: Valida Tipo

            // Inicio: prepara varíaveis
            $module_cstm = strtolower($module) . '_cstm';
            $module_audit = strtolower($module) . '_audit';

            $status = $args['status'];
            $msg = $args['mensagem'];

            $date = $args['data_criacao_sap'];
            $nDate = DateTime::createFromFormat('Y-m-d H:i:s' , $date, new DateTimeZone('PST'));
            $nDate->setTimezone(new DateTimeZone('EST'));
            $dataIntegracao = $nDate->format('Y-m-d H:i:s');

            $id = $args['id_sugar'];
            $cod_sap = $args['cod_sap'];

            $inscricao_estadual = $args['inscricao_estadual'];
            $inscricao_municipal = $args['inscricao_municipal'];
            $contribuinte = $args['contribuinte'];
            $forma_pagamento = $args['forma_pagamento'];
            $data_hora_integracao = $args['data_hora_integracao'];
             // Fim: prepara varíaveis

             // Busca registro
            $bean = BeanFactory::retrieveBean($module, $id, array('disable_row_level_security' => true));

            // Se o registro for encontrado
            if($bean) {

                // Inicio: Verifica Código SAP
                if (array_key_exists("codsap_c", $bean)) {
                    if (!empty($bean->codsap_c && $bean->codsap_c <> $cod_sap)) {
                        return array('status' => false, 'msg' => 'Código SAP enviado não corresponde a Código SAP já cadastrado no Sugar para esse registro.');
                    } else {

                        if($args['tipo'] == 1) {
                            if ($bean->codsap_c) {
                                $update = "UPDATE $module_cstm 
                                SET data_integracao_sap_c = '$dataIntegracao',
                                    msg_interacao_sap_c = '$msg',
                                    status_integracao_sap_c = '$status'
                                WHERE id_c = '$id'";
                            } else {
                                $update = "UPDATE $module_cstm 
                                SET data_integracao_sap_c = '$dataIntegracao',
                                    msg_interacao_sap_c = '$msg',
                                    status_integracao_sap_c = '$status',
                                    imunicipal_c = '$inscricao_municipal',
                                    im_c = '$inscricao_estadual',
                                    contribuinte_c = '$contribuinte',
                                    forma_pagamento_c = '$forma_pagamento',
                                    data_integr_sap_sugar_cred_c = '$data_hora_integracao',
                                    codsap_c = '$cod_sap'
                                WHERE id_c = '$id'";
                            }
                        } else {
                            if ($bean->codsap_c) {
                                $update = "UPDATE $module_cstm 
                                SET data_integracao_sap_c = '$dataIntegracao',
                                    msg_interacao_sap_c = '$msg',
                                    status_integracao_sap_c = '$status'
                                WHERE id_c = '$id'";
                            } else {
                                $update = "UPDATE $module_cstm 
                                SET data_integracao_sap_c = '$dataIntegracao',
                                    msg_interacao_sap_c = '$msg',
                                    status_integracao_sap_c = '$status',
                                    codsap_c = '$cod_sap'
                                WHERE id_c = '$id'";
                            }
                        }
                    }
                } else if (array_key_exists("cod_sap_c", $bean)) {
                    if (!empty($bean->cod_sap_c && $bean->cod_sap_c <> $cod_sap)) {
                        return array('status' => false, 'msg' => 'Código SAP enviado não corresponde a Código SAP já cadastrado no Sugar para esse registro.');
                    } else {

                        if ($bean->cod_sap_c) {
                            $update = "UPDATE $module_cstm 
                            SET data_integracao_sap_c = '$dataIntegracao',
                                msg_interacao_sap_c = '$msg',
                                status_integracao_sap_c = '$status'
                            WHERE id_c = '$id'";
                        } else {
                            $update = "UPDATE $module_cstm 
                            SET data_integracao_sap_c = '$dataIntegracao',
                                msg_interacao_sap_c = '$msg',
                                status_integracao_sap_c = '$status',
                                cod_sap_c = '$cod_sap'
                            WHERE id_c = '$id'";
                        }

                    }
                }
                // Fim: Verifica Código SAP

                // Inicio: Update nos campos de rastreabilidade - A montagem do update está acima, aqui é só execução

                $db->query($update);
                //$GLOBALS['log']->fatal($update);

                    // Se houver alguma falha no update
                    if ($db->query($update) == false) {
                        return array('status' => false, 'msg' => 'Falha ao salvar os dados da rastrebilidade no registro. Contate o administrador do sistema.');
                    }

                // Fim: Update nos campos de rastreabilidade

                // Inicio: Inserts na tabela de audit
                $dataAudit = date("Y-m-d H:i:s");
                $nDateAudit = DateTime::createFromFormat('Y-m-d H:i:s' , $dataAudit, new DateTimeZone('PST'));
                $nDateAudit->setTimezone(new DateTimeZone('EST'));
                $data_integracao_audit = $nDateAudit->format('Y-m-d H:i:s');
         
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

                    if ($res == false) {
                        $error_sql_audit = true;
                    }
                }

                if ($error_sql_audit == true) {
                    return array('status' => false, 'msg' => 'Falha ao inserir os registros de rastreabilidade. Contate o administrador do sistema.');
                }
                // Fim: Inserts na tabela de audit

            } else {
                // Se não encontrar o registro
                return array('status' => false, 'msg' => 'Registro não encontrado. Verifique o ID e o Tipo.');
            }
            
            // Retorno sucesso
            return array('status' => true, 'msg' => 'Retorno cadastrado com sucesso');
        }

    }
