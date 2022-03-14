<?php

$manifest = array(
    'acceptable_sugar_flavors' => array('PRO','ENT','ULT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('11\\..*$'),
    ),
    'author' => 'Néctar Consulting - Lucas Albero & Silvio Antunes',
    'name' => 'MasksContactsWeb',
    'description' => 'Formatação de campos de Telefone na tela de edição de registro de Contatos (Contacts).',
    'is_uninstallable' => true,
    'type' => 'module',
    'version' => '1.0',
    'key' => 'MasksContactsWeb',
);

$installdefs = array(
    'id' => 'MasksContactsWeb',
    'copy' => array(
        array(
            'from' => '<basepath>/edit.hbs',
            'to' => 'custom/modules/Contacts/clients/base/fields/text/edit.hbs',
        ),
        array(
            'from' => '<basepath>/mask.js',
            'to' => 'custom/valmer/mask.js',
        ),
    ),
);

?>