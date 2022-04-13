<?php

$manifest = array(
	'key' => '20220413',
	'name' => '[Integração SAP] - Function to Request Integra SAP',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'Função padrão para requisitar Integração SAP',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date('Y-m-d h:i:s'),
	'type' => 'module',
	'version' => '3',
);

$installdefs = array(
	'id' => '20220413FUNCTIONINTEGRASAP',
	'copy' => array(
		0 => array(
			'from' => '<basepath>/requestAPI_SAP.php',
			'to' => 'custom/IntegraSAP/requestAPI_SAP.php',
		),
	),	
);


