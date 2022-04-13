<?php

    $hook_version = 1; 
	
	$hook_array['before_save'][] = Array(
    2, 
    'Cria um stored_fetched_row_c', 
    'custom/modules/Contacts/integra_contato_sap.php', 
    'integra_contato_sap', 
    'before_save_method'
);

	
	$hook_array['after_save'][] = Array(
		20,'Connect with SAP sending Json.',
		'custom/modules/Contacts/integra_contato_sap.php',
		'integra_contato_sap',
		'afterSave'
	);
	