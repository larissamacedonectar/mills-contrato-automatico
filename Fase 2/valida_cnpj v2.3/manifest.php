<?php

$manifest = array(
	'key' => 'MILLS20200622',
	'name' => 'Valida CNPJ',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nathalia Vieira / Samuel Kim',
	'description' => 'Hook para validar CNPJ e puxar dados da conta.',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => '2020-05-18',
	'type' => 'module',
	'version' => '2.3',
);

$installdefs = array(
	'id' => 'MILLS20200622',
	'hookdefs' => array(
		array(
			'from' => '<basepath>/valida_cnpj.php',
			'to_module' => 'Accounts',
		),
	),
	'copy' => array(
		0 => array(
			'from' => '<basepath>/validaCNPJ.php',
			'to' => 'custom/Extension/modules/Accounts/Ext/validaCNPJ.php',
		),
	),

	
);


