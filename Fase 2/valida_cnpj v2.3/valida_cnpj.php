<?php

    $hook_version = 1;  

    $hook_array['before_save'][] = Array(
        //Processing index. For sorting the array.
        2,
    
        //Label. A string value to identify the hook.
        'Valida CNPJ',
    
        //The PHP file where your class is located.
        'custom/Extension/modules/Accounts/Ext/validaCNPJ.php',
    
        //The class the method is in.
        'ValidaCNPJ',
    
        //The method to call.
        'validadorCNPJ'
    );
    $hook_array['before_save'][] = Array(
        //Processing index. For sorting the array.
        1,
    
        //Label. A string value to identify the hook.
        'Valida IE',
    
        //The PHP file where your class is located.
        'custom/Extension/modules/Accounts/Ext/validaCNPJ.php',
    
        //The class the method is in.
        'ValidaCNPJ',
    
        //The method to call.
        'validadorIE'
    );
