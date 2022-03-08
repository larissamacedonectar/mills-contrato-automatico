<?php

$manifest = array(
	'key' => 'validadorDados',
	'name' => 'Classe Validadora de Dados',
	'acceptable_sugar_flavors' => array('PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
	'regex_matches' => array('11\\.[0-9]\\.[0-9]'),
	),
	'author' => 'Nectar Consulting',
	'description' => 'Classe Validadora de Dados',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => date("Y-m-d");,
	'type' => 'module',
	'version' => '1.0',
);

$installdefs = array(
	'id' => 'classeValidadoraDados',
	'copy' => array(
        array(
            'from' => '<basepath>/validadorDados.php',
            'to' => 'custom/ValidadorDados/validadorDados.php'
        )
    )	
);

