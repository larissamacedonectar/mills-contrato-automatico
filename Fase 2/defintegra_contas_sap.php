<?php

$hook_array['before_save'][] = Array(
    2, 
    'Cria um stored_fetched_row_c', 
    'custom/modules/Accounts/integra_contas_sap.php', 
    'account_sap_class', 
    'before_save_method'
);



$hook_array['after_save'][] = Array(
    2, 
    'Integra Contas com SAP', 
    'custom/modules/Accounts/integra_contas_sap.php', 
    'account_sap_class', 
    'account_sap_method'
);

?>