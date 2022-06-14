<?php

$manifest = array(
	'key' => 'dupechecklistCA',
	'name' => '[Vardefs Contas] - dupecheck-list',
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$','12\\..*$'),
    ),
	'author' => 'Nectar Consulting',
	'description' => 'Ajuste de colunas para verificação de Duplicados sugar',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => '2022-06-09 00:00:00',
	'type' => 'module',
	'version' => '1.0',
);

$installdefs = array(
	'id' => 'dupechecklistCA',
	'copy' => array(
		0 => array(
			'from' => '<basepath>/dupecheck-list.php',
			'to' => 'custom/Extension/modules/Accounts/Ext/clients/base/views/dupecheck-list/dupecheck-list.php',
		),
	),	
);


