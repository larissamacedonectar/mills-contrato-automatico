<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array('1','workflow','include/workflow/WorkFlowHandler.php','WorkFlowHandler','WorkFlowHandler',);

$hook_array['before_save'][] = Array(98,'Logic Hook Accounts Limit Date','custom/modules/Accounts/accounts_limitDate_lh.php','Accounts_limitDate_lh','limitDate',);
$hook_array['before_save'][] = Array(98,'Limit Date Accounts','custom/modules/Accounts/accounts_limitDate_lh.php','Accounts_limitDate_lh','limitDate',);
$hook_array['before_save'][] = Array(102,'Logic hook to set latitude and longitude','custom/modules/Accounts/hkSetLocalization.php','HkSetLocalization','hkSetLocalization',);
$hook_array['before_save'][] = Array(1,'Example Logic Hook - Logs account name','custom/modules/Accounts/ClearDataHot.php','ClearDataHot','clearData',);
$hook_array['after_save'] = Array(); 
// Mudança para before save, no caso a integração com o SIS executa antes da integração com o SAP
$hook_array['before_save'][] = Array(110,'Logic Hook Accounts - SIS','custom/modules/Accounts/accounts_sis.php','Accounts_SIS','postSIS',);



?>