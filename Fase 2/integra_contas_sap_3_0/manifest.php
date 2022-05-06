<?php

$manifest = array(
	'key' => 'integra_conta_sap_20220413',
	'name' => '[Integra SAP] - Hooks Conta - SAP',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'Logic Hooks para Integração Contas com SAP.',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date('Y-m-d h:i:s'),
	'type' => 'module',
	'version' => '3.1',
);

$installdefs = array(
	'id' => 'integra_conta_sap_20220413',
	'hookdefs' => array(
		array(
			'from' => '<basepath>/defintegra_contas_sap.php',
			'to_module' => 'Accounts',
		),
	),
	'copy' => array(
		0 => array(
			'from' => '<basepath>/integra_contas_sap.php',
			'to' => 'custom/modules/Accounts/integra_contas_sap.php',
		),
	),

	
);


