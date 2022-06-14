<?php
/*
Ajustes de colunas exibidas para 
 */
$viewdefs['Accounts']['base']['view']['dupecheck-list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'link' => true,
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'contribuinte_c',
                    'label' => 'LBL_CONTRIBUINTE_C',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'im_c',
                    'label' => 'LBL_IM',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'billing_address_postalcode',
                    'label' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'billing_address_street',
                    'label' => 'LBL_BILLING_ADDRESS_STREET',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'id' => 'ASSIGNED_USER_ID',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => false,
                    'readonly' => true,
                ),
            ),
        ),
    ),
);
