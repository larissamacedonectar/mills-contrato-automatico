<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once 'custom/EnviaEmailSistema/EnviaEmailSistema.php';

class SaneamentoDadosAPI extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            //GET & POST
            'QuotesInfoEndpoint' => array(
                //request type
                'reqType' => array('POST'),

                //set authentication
                'noLoginRequired' => true,

                //endpoint path
                'path' => array('SaneamentoDadosAPI'),

                //endpoint variables
                'pathVars' => array('', ''),

                //method to call
                'method' => 'SaneamentoDadosAPI_method',

                //short help string to be displayed in the help documentation
                'shortHelp' => 'Endpoint to get info after quote was created',

                //long help to be displayed in the help documentation
                'longHelp' => 'custom/clients/base/api/help/SaneamentoDadosAPI_help.html',
            ),
        );
    }

	public function SaneamentoDadosAPI_method($api, $args)
	{
		require_once('include/upload_file.php');
		require_once('include/utils.php');
		global $db;

		$module = $args["module"];
		$field = $args["field"];
		$limit = $args["limit"];
		$email = $args["email"];
		$page = $args["page"];
		$type = $args["type"];
		//$nb_elem_per_page = $args["numElement"];


		$GLOBALS['log']->fatal("Module" . $module);

		$connect_url = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['connect_url'];
		$login = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['login'];
		$password = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['password'];
		$pasta = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['pasta_saneamento'];

		//$arquivo = "saneamento_contacts.csv";
		//$source = $pasta ."/saneamento_contacts.csv";
		//$arquivo =  $pasta ."/saneamento_contacts.csv";

		$typeModule = strpos($module, "accounts");
		if ($typeModule === false) {
			$arquivo = "saneamento_contacts.csv";
			$source = $pasta ."/saneamento_contacts.csv";
			$arquivo =  $pasta ."/saneamento_contacts.csv";
		} else {
			$arquivo = "saneamento_accounts.csv";
			$source = $pasta ."/saneamento_accounts.csv";
			$arquivo =  $pasta ."/saneamento_accounts.csv";
		}

		$conn = ftp_connect($connect_url) or die("Could not connect");

		ftp_login($conn,$login,$password);
		ftp_set_option($conn, FTP_USEPASVADDRESS, false);
		ftp_pasv($conn, true) or die("Cannot switch to passive mode");
		ftp_get($conn,$target,$source,FTP_ASCII);
		
		$contents = ftp_nlist($conn, $pasta);
		
		$GLOBALS['log']->fatal("Fez Login FTP para Saneamento");
		
		foreach ($contents as $nomeArquivo) {
			if ($nomeArquivo == $arquivo) {
						$source = $arquivo;

						$typeModule = strpos($module, "accounts");
						if ($typeModule === false) {
							$target = "saneamento_contacts";
						} else {
							$target = "saneamento_accounts.csv";
						}
						break;
				
				} 
		}

		ftp_set_option($conn, FTP_USEPASVADDRESS, false);
		ftp_get($conn,$target,$source,FTP_ASCII);

		$fieldseparator = ";";
		$lineseparator = "\n";
		$addauto = 0;

		$uploadFile = new UploadFile();
		
		if($uploadFile->temp_file_location = $target)
		{
			$GLOBALS['log']->fatal("Carregou temp_file");
		}
		else{
			$GLOBALS['log']->fatal("Deu erro ao carregar temp_file");
		}
		$file = $uploadFile->get_file_contents();

		$GLOBALS['log']->fatal("get_file_contents:" . print_r($uploadFile, True));

		if(!$file) {
			$GLOBALS['log']->fatal("Erro ao Abrir o Arquivo");
			exit;
		}

		$cstm = strpos($module, "_cstm");
		if ($cstm === false) {
			$type_query = $db->query("SELECT id, " . $field . " FROM " . $module . " WHERE deleted != 1");
		} else {
			$type_query = $db->query("SELECT id_c AS id, " . $field . " FROM " . $module . " WHERE deleted != 1");
		}

		while($row = $db->fetchByAssoc($type_query) )
		{
			if ($cstm === false) {
				$types[$row['id']] = $row[$field];
			} else {
				$types[$row['id_c']] = $row[$field];
			}
			
		}

		$linearray = array();
		$head = [];
		
		$file = rtrim($file);
		$lines = explode($lineseparator,$file);
		$first_line = $this->getArrayFromLine($lines[0], $fieldseparator);
		$GLOBALS['log']->fatal("FIRST LINE ARRAY" . print_r($first_line, true));

		$idposition = array_search("ID", $first_line);
		$name_position = array_search($field, $first_line);
		$log = "";

		$GLOBALS['log']->fatal("COMEÇOU IMPORTAÇÃO");

		array_shift($lines);
		foreach (array_slice($lines, $page*$limit, $limit) as $line) {
		//foreach($lines as $line) {

			$linearray = $this->getArrayFromLine($line, $fieldseparator);
			
			$id = $linearray[$idposition];
			$dado = $linearray[$name_position];

			$GLOBALS['log']->fatal("dado:" . print_r($dado, True));

			if($type == "cnpj") {
				$resultadoValidacao = $this->sanearCNPJ($dado);
			} else if ($type == "cep") {
				$resultadoValidacao = $this->sanearCEP($dado);
			} else if ($type == "telefone") {
				$resultadoValidacao = $this->sanearTelefone($dado);
			} else if ($type == "email") {
				$resultadoValidacao = $this->sanearEmail($dado);
			} else if ($type == "cidade") {
				$resultadoValidacao = $this->sanearCidade($dado);
			} else if ($type == "estado") {
				$resultadoValidacao = $this->sanearEstado($dado);
			} else {
				$resultadoValidacao = array(
					'status' => true,
					'resultado' => $dado
				);
			}

			$GLOBALS['log']->fatal("resultadoValidacao:" . print_r($resultadoValidacao, True));

			if ($resultadoValidacao["status"] == true) {
				if($cstm == false) {
					$GLOBALS['log']->fatal("UPDATE " . $module . " SET " . $field . " = '" . $dado . "' WHERE id = '$id'");
					$db->query("UPDATE " . $module . " SET " . $field . " = '" . $dado . "' WHERE id = '$id'");
				} else {
					$GLOBALS['log']->fatal("UPDATE " . $module . " SET " . $field . " = '" . $dado . "' WHERE id_c = '$id'");
					$db->query("UPDATE " . $module. " SET " . $field . " = '" . $dado . "' WHERE id_c = '$id'");
				}
			} else {
				$log .= $id . " - " . $resultadoValidacao["resultado"];
				$log .= "<br>";
			}
			
		}

		if(!empty($log)){
			
			$result = $this->EnviaEmail_Mailer(array($email), $log, "Status Saneamento");
			$GLOBALS['log']->fatal("Envio Email: " . $result);
		}
		
		$GLOBALS['log']->fatal("TERMNOU IMPORTAÇÃO");
		return $log; 																							  

	}

	function getArrayFromLine($line, $fieldseparator){

		$line = trim($line," \t");
		$line = str_replace("\r","",$line);


		$line = str_replace("'","\'",$line);

		return explode($fieldseparator,$line);
	}

	function sanearCNPJ($cnpj){

		require_once 'custom/Saneamento/ValidadorDados/validadorDados.php';

		$saneamentoCNPJ = new validadorDados;
		return $saneamentoCNPJ->cnpjValida($cnpj);
	}

	function sanearCEP($cep){

		require_once 'custom/Saneamento/ValidadorDados/validadorDados.php';

		$saneamentoCEP = new validadorDados;
		return $saneamentoCEP->cepValida($cep);

	}

	function sanearTelefone($telefone){
		
		require_once 'custom/Saneamento/ValidadorDados/validadorDados.php';

		$saneamentoTelefone = new validadorDados;
		return $saneamentoTelefone->telefoneValida($telefone);

	}

	function sanearEmail($email){

		require_once 'custom/Saneamento/ValidadorDados/validadorDados.php';
		
		$saneamentoEmail = new validadorDados;
		return $saneamentoEmail->emailValida($email);

	}

	function sanearCidade($idCidade){
		$GLOBALS['log']->fatal('Fatal level message 1');

		global $db;
		$GLOBALS['log']->fatal('Fatal level message 2');
		$select = "SELECT count(*) AS ct FROM it4_cidades WHERE id = '$idCidade' AND deleted = 0";
		$GLOBALS['log']->fatal('Fatal level message 3' . $select);
		$res = $db->query($select);
		$GLOBALS['log']->fatal('Fatal level message 4');
		$row = $db->fetchByAssoc($res);
		$GLOBALS['log']->fatal('Fatal level message 5');
		
		if($row["ct"] == 0) {
			return array(
				'status' => false,
				'resultado' => "ID de Cidade não cadastrado."
			);
		}  else {
			return array(
				'status' => true,
				'resultado' => $idCidade
			);
		}
	}

	function sanearEstado($idEstado){


		global $db;

		$select = "SELECT count(*) AS ct FROM it4_estados WHERE id = '$idEstado' AND deleted = 0";
		$res = $db->query($select);
		$row = $db->fetchByAssoc($res);
		
		if($row["ct"] == 0) {
			return array(
				'status' => false,
				'resultado' => "ID de Estado não cadastrado."
			);
		}  else {
			return array(
				'status' => true,
				'resultado' => $idEstado
			);
		}

	}

	public function EnviaEmail_Mailer($enderecos, $body, $assunto){

        try {
            $mailer = MailerFactory::getSystemDefaultMailer();
            $mailTransmissionProtocol = $mailer->getMailTransmissionProtocol();
            $mailer->setSubject($assunto);
            $mailer->setHtmlBody($body);
            
            
            $mailer->clearRecipients();
            foreach ($enderecos as $endereco) {
                $mailer->addRecipientsTo(new \EmailIdentity($endereco, ''));
            }
            $result = $mailer->send();
            if ($result) {
                return $result;
            } else {
                return "Falha no envio";
            }
        } catch (MailerException $me) {
            $message = $me->getMessage();
            switch ($me->getCode()) {
                case \MailerException::FailedToConnectToRemoteServer:
                    $GLOBALS["log"]->fatal("BeanUpdatesMailer :: error sending email, system smtp server is not set");
                    break;
                default:
                    $GLOBALS["log"]->fatal("BeanUpdatesMailer :: error sending e-mail (method: {$mailTransmissionProtocol}), (error: {$message})");
                    break;
            }
        }
    }
}