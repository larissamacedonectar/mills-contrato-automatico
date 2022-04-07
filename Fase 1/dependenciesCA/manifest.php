<?php

$manifest = array(
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$'),
    ),
    'author' => 'Néctar Consulting - Silvio Antunes',
    'name' => 'dependenciesCA',
    'description' => 'Bloqueia alguns campos de Contas (Accounts) após a integração com o SAP',
    'is_uninstallable' => true,
    'type' => 'module',
    'version' => '1.0',
    'key' => 'dependenciesCA',
);

$installdefs = array(
    'id' => 'dependenciesCA',
    'dependencies' => array(
        array(
            'from' => '<basepath>/dependencies_Accounts_CA.php',
            'to_module' => 'Accounts',
        )
    )
);

?>