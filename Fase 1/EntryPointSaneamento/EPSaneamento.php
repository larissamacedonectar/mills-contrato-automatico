<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$params = array(
    "module" => $_GET["table"],
    "field" => $_GET["field"],
	"limit" => $_GET["limit"],
	"email" => $_GET["email"],
	"type" => $_GET["type"]
);

saneamentoMaster($params);

function saneamentoMaster($params)
{
    require_once('include/upload_file.php');
    require_once('include/utils.php');
    global $db;

	$module = $params["module"];
	$field = $params["field"];
	$limit = $params["limit"];
	$email = $params["email"];
	$type = $params["type"];

	$connect_url = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['connect_url'];
	$login = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['login'];
	$password = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['password'];
	$pasta = $GLOBALS['app_list_strings']['IMPORTACAO_FTP']['pasta_saneamento'];
	$arquivo = "saneamento_contacts.csv";
	
	$source = $pasta ."/saneamento_contacts.csv";
	$arquivo =  $pasta ."/saneamento_contacts.csv";
	
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
					$target = "saneamento_contacts.csv";
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
	$first_line = getArrayFromLine($lines[0], $fieldseparator);
    $GLOBALS['log']->fatal("FIRST LINE ARRAY" . print_r($first_line, true));

    $idposition = array_search("ID", $first_line);
    $name_position = array_search($field, $first_line);
	$log = "";

    $GLOBALS['log']->fatal("COMEÇOU IMPORTAÇÃO");

	array_shift($lines);
    foreach($lines as $line) {

        $linearray = getArrayFromLine($line, $fieldseparator);
        
        $id = $linearray[$idposition];
        $dado = $linearray[$name_position];

		$GLOBALS['log']->fatal("dado:" . print_r($dado, True));

		if($type == "cnpj") {
			$resultadoValidacao = sanearCNPJ($dado);
		} else if ($type == "cep") {
			$resultadoValidacao = sanearCEP($dado);
		} else if ($type == "telefone") {
			$resultadoValidacao = sanearTelefone($dado);
		} else if ($type == "email") {
			$resultadoValidacao = sanearEmail($dado);
		} else if ($type == "cidade") {
			$resultadoValidacao = sanearCidade($dado);
		} else if ($type == "estado") {
			$resultadoValidacao = sanearEstado($dado);
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
	echo '<div>';
	echo $log;
	echo '</div>';
	
	$GLOBALS['log']->fatal("TERMNOU IMPORTAÇÃO");
	return true; // Necessário para que o job não dê erro de execução (Samuel Shin Kim - 26/11/2020)																							  

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