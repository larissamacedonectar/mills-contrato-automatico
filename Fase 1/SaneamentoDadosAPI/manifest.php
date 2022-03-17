<?php

$manifest = array(
	'key' => 'APISaneamento',
	'name' => 'API Saneamento',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'API Saneamento',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date("Y-m-d"),
	'type' => 'module',
	'version' => '1.0',
);

$installdefs = array(
	'id' => 'APISaneamento1',
	'copy' => array(
		0 =>
        array(
            'from' => '<basepath>/SaneamentoDadosAPI.php',
            'to' => 'custom/clients/base/api/SaneamentoDadosAPI.php',
		),

    ),
);
?>