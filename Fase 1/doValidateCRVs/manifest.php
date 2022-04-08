<?php

$manifest = array(
'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
'acceptable_sugar_versions' => array(
'exact_matches' => array(),
'regex_matches' => array('11\\..*$'),
),
'author' => 'Néctar Consulting - Silvio Antunes',
'name' => 'doValidateCRVs',
'description' => 'Novas versões de Create e Record Views (CRVs) para o módulo de Contas (Accounts) com validação de CNPJ, CEP e Telefone.',
'is_uninstallable' => true,
'published_date' => date('Y-m-d h:i:s'),
'type' => 'module',
'version' => '1.0',
'key' => 'doValidateCRVs',
);

$installdefs = array(
    'id' => 'doValidateCRVs',
    'copy' => array(
        array(
            'from' => '<basepath>/pt_br.cnpj_invalido.php',
            'to' => 'custom/Extension/application/Ext/Language/pt_br.cnpj_invalido.php',
        ),
        array(
            'from' => '<basepath>/pt_br.fone_invalido.php',
            'to' => 'custom/Extension/application/Ext/Language/pt_br.fone_invalido.php',
        ),
        array(
            'from' => '<basepath>/pt_br.cep_invalido.php',
            'to' => 'custom/Extension/application/Ext/Language/pt_br.cep_invalido.php',
        ),
        array(
            'from' => '<basepath>/Accounts/create.js',
            'to' => 'custom/modules/Accounts/clients/base/views/create/create.js',
        ),
        array(
            'from' => '<basepath>/Accounts/record.js',
            'to' => 'custom/modules/Accounts/clients/base/views/record/record.js',
        ),
        array(
            'from' => '<basepath>/Contacts/create.js',
            'to' => 'custom/modules/Contacts/clients/base/views/create/create.js',
        ),
        array(
            'from' => '<basepath>/Contacts/record.js',
            'to' => 'custom/modules/Contacts/clients/base/views/record/record.js',
        ),
    ),
);



?>