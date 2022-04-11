<?php

$manifest = array(
	'key' => 'RetornoContaSAP_API',
	'name' => 'API de Retorno de Conta SAP',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'API de Retorno de Conta SAP',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date("Y-m-d"),
	'type' => 'module',
	'version' => '1.0',
);

$installdefs = array(
	'id' => 'RetornoContaSAP_API',
	'copy' => array(
		0 =>
        array(
            'from' => '<basepath>/RetornoContaSAP_API.php',
            'to' => 'custom/clients/base/api/RetornoContaSAP_API.php',
		),

    ),
);
?>