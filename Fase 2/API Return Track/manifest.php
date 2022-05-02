<?php

$manifest = array(
	'key' => '20210119APITRACKRETURNSAP',
	'name' => '[Integra SAP] - API Track Return SAP',
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$'),
    ),
	'author' => 'Nectar Consulting',
	'description' => 'API de Rastreabilidade Retorno do SAP',
	'icon' => '',
	'is_uninstallable' => true,
    'published_date' => '2021-01-19 09:50:00',
	'type' => 'module',
	'version' => '1.2',
);

$installdefs = array(
	'id' => '20210119APITRACKRETURNSAP',
	'copy' => array(
		0 => array(
			'from' => '<basepath>/TrackReturnSAP_API.php',
			'to' => 'custom/clients/base/api/TrackReturnSAP_API.php',
		),
	),	
);


