<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

print_r("Sanear CNPJ");
print_r("<br>");
print_r(sanearCNPJ("58.623.488/0001-93"));
print_r("<br>");
print_r(sanearCNPJ("58.623.488/0001-01"));

print_r("<br>");
print_r("<br>");
print_r("Sanear CEP");
print_r("<br>");
print_r(sanearCEP("12345-123"));
print_r("<br>");
print_r(sanearCEP("12345"));

print_r("<br>");
print_r("<br>");
print_r("Sanear Telefones");
print_r("<br>");
print_r(sanearTelefone("11977759239"));
print_r("<br>");
print_r(sanearTelefone("114040-4040"));
print_r("<br>");
print_r(sanearTelefone("00000000000"));

print_r("<br>");
print_r("<br>");
print_r("Sanear Email");
print_r("<br>");
print_r(sanearEmail("larissa.macedo@nectarconsulting.com.br"));
print_r("<br>");
print_r(sanearEmail("teste.teste.com"));

print_r("<br>");
print_r("<br>");
print_r("Sanear Cidade");
print_r("<br>");
print_r(sanearCidade("191c6dac-1648-11ec-b617-06e41dba421a"));
print_r("<br>");
print_r(sanearCidade("191c6dac-1648-11ec-b617-06e41dba421a-TESTEINVALIDO"));

print_r("<br>");
print_r("<br>");
print_r("Sanear Estado");
print_r("<br>");
print_r(sanearEstado("11"));
print_r("<br>");
print_r(sanearEstado("TESTE-INVALIDO"));

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