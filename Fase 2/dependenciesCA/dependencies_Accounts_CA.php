<?php

$dependencies['Accounts']['dependencies_integracao_CA'] = array(
    'hooks' => array("all"),
    'triggerField' => array('status_integracao_sap_c'),
    'trigger' => 'true',
    'onload' => true,
    'actions' => array(
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'im_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'imunicipal_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'forma_de_pagamento_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'cnpj_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'cnae_c ',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'contribuinte_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'porte_receita_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'grupo_clientes_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'data_integr_int_ii_c',
                'value' => 'equal($status_integracao_sap_c,true)',
            ),
        ),
    ),
);


