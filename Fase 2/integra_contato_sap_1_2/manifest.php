<?php

$manifest = array(
	'key' => 'integra_contato_sap_20220413',
	'name' => '[Integra SAP] - Hooks Integração Contato SAP',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Brunno Silotto',
	'description' => 'Hooks para Integração com SAP.',
	'icon' => '',
	'is_uninstallable' => true,
	'published_date' => date('Y-m-d h:i:s'),
	'type' => 'module',
	'version' => '1.2',
);

$installdefs = array(
	'id' => 'integra_contato_sap_20220413',
	'hookdefs' => array(
		array(
			'from' => '<basepath>/defintegra_contato_sap.php',
			'to_module' => 'Contacts',
		),
	),
	'copy' => array(
		0 => array(
			'from' => '<basepath>/integra_contato_sap.php',
			'to' => 'custom/modules/Contacts/integra_contato_sap.php',
		),
	),

	
);


