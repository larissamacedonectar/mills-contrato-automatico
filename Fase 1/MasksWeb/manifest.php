<?php

$manifest = array(
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$'),
    ),
    'author' => 'Néctar Consulting - Lucas Albero & Silvio Antunes',
    'name' => 'MasksWeb',
    'description' => 'Formatação de campos de CNPJ, CEP e Telefone na tela de edição de registro de Contas (Accounts) e Contatos (Contacts).',
    'is_uninstallable' => true,
    'type' => 'module',
    'version' => '1.0',
    'key' => 'MasksWeb',
);

$installdefs = array(
    'id' => 'MasksWeb',
    'copy' => array(
        array(
            'from' => '<basepath>/text/edit.hbs',
            'to' => 'custom/modules/Contacts/clients/base/fields/text/edit.hbs',
        ),
        array(
            'from' => '<basepath>/phone/edit.hbs',
            'to' => 'custom/modules/Contacts/clients/base/fields/phone/edit.hbs',
        ),
        array(
            'from' => '<basepath>/text/edit.hbs',
            'to' => 'custom/modules/Accounts/clients/base/fields/text/edit.hbs',
        ),
        array(
            'from' => '<basepath>/phone/edit.hbs',
            'to' => 'custom/modules/Accounts/clients/base/fields/phone/edit.hbs',
        ),
        array(
            'from' => '<basepath>/name/edit.hbs',
            'to' => 'custom/modules/Accounts/clients/base/fields/name/edit.hbs',
        ),
        array(
            'from' => '<basepath>/mask.js',
            'to' => 'custom/masks3/mask.js',
        ),
    ),
);

?>