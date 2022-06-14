<?php

$manifest = array(
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('12\\..*$'),
    ),
    'author' => 'Néctar Consulting - Lucas Lopes',
    'name' => 'dependenciesCA_cred',
    'description' => 'Bloqueia os campos de Credito para que somente a integração SAP preencha',
    'is_uninstallable' => true,
    'type' => 'module',
    'version' => '1.0',
    'key' => 'dependenciesCA_cred',
);

$installdefs = array(
    'id' => 'dependenciesCA_cred',
    'dependencies' => array(
        array(
            'from' => '<basepath>/dependencies_Accounts_CA_cred.php',
            'to_module' => 'Accounts',
        )
    )
);

?>