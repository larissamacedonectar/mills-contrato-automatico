<?php

$manifest = array(
	'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$'),
    ),
    'author' => 'Vitor Nakazawa / Samuel Shin Kim',
    'name' => 'Logic Hooks for SIS - Nectar.',
    'description' => 'Arquivos accounts_sis, contacts_sis e opportunities_sis.',
    'is_uninstallable' => true,
    'published_date' => date('Y-m-d h:i:s'),
    'type' => 'module',
    'version' => '1.6',
	'key' => 'IntegracaoSIS20210413',
);
$installdefs = array(
    //You should use a unique value here for each package
    'id' => 'IntegracaoSIS202100413',
    'beans' => array(),
    'layoutdefs' => array(),
    'relationships' => array(),
    'copy' => array(
        array(
            'from' => '<basepath>/opportunities_sis.php',	
            'to' => 'custom/modules/Opportunities/opportunities_sis.php',
        ),
        array(
            'from' => '<basepath>/contacts_sis.php',	
            'to' => 'custom/modules/Contacts/contacts_sis.php',
        ),
        array(
            'from' => '<basepath>/accounts_sis.php',	
            'to' => 'custom/modules/Accounts/accounts_sis.php',
        ),
        array(
            'from' => '<basepath>/logic_hooks.php',	
            'to' => 'custom/modules/Accounts/logic_hooks.php',
        ),
    ),
);



