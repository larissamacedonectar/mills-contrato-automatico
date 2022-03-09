<?php

$manifest = array(
	'key' => 'EntryPointSaneamento',
	'name' => 'EntryPoint Saneamento',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'EntryPoint Saneamento',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date("Y-m-d"),
	'type' => 'module',
	'version' => '1.0',
);

$installdefs = array(
	'id' => 'EntryPointSaneamento1',
	'copy' => array(
		0 =>
        array(
            'from' => '<basepath>/EntryPointRegistry/EPSaneamentoRegistry.php',
            'to' => 'custom/Extension/application/Ext/EntryPointRegistry/EPSaneamentoRegistry.php',
		),
		1 =>
        array(
            'from' => '<basepath>/EPSaneamento.php',
            'to' => 'custom/Saneamento/EntryPoint/EPSaneamento.php',
		),

    ),
);
?>