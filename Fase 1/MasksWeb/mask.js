/* Máscaras JS */
function mascara(o,f) {

    o.value=f(o);
    $(o).trigger('change');
}

function mascara_cnpj(obj) {

    v = obj.value;
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito

    if (obj.name == 'cnpj_c') {
        
        v = v.replace(/^(\d{2})(\d)/,"$1.$2"); // Coloca um ponto entre o segundo e o terceiro dígitos
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3"); // Coloca um ponto entre o quinto e o sexto dígitos
        v = v.replace(/\.(\d{3})(\d)/,".$1/$2"); // Coloca uma barra entre o oitavo e o nono dígitos
        v = v.replace(/(\d{4})(\d)/,"$1-$2"); // Coloca um hífen depois do bloco de quatro dígitos
        //v = v.replace(/^(\d{2})(\d{3})?(\d{3})?(\d{4})?(\d{2})?/, "$1.$2.$3/$4-$5");
        v = v.substring(0, 17);
    }  
    return v;
}
function mascara_cep(obj) {

    v = obj.value;
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito

    if (obj.name == 'billing_address_postalcode'  ||
        obj.name == 'shipping_address_postalcode' ||
        obj.name == 'primary_address_postalcode'  ||
        obj.name == 'alt_address_postalcode') {

        v = v.replace(/^(\d{5})(\d{3})?/, "$1-$2");
        v = v.substring(0, 8);
    }
    return v;
}
function mascara_fone(obj) {

    v = obj.value;
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito

    if (obj.name == 'phone_home'  || obj.name == 'phone_mobile' ||
        obj.name == 'phone_work'  || obj.name == 'phone_fax'    ||
        obj.name == 'phone_other' || obj.name == 'phone_office' ||
        obj.name == 'phone_alternate') {

        v = v.replace(/^(\d{2})(\d)/g,"($1) $2");//Coloca parênteses em volta dos dois primeiros dígitos
				
        if(v.length <= 12)
        {
            //console.log('1: O valor de v é ' + v + 'seu tamanho é ' + v.length);
            v = v.replace(/(\d{4})(\d)/g,"$1-$2");//Número com 8 dígitos. Formato: (99) 9999-9999
            //console.log('2: O valor de v é ' + v + 'seu tamanho é ' + v.length);
            v = v.substring(0, 13);
            //console.log('3: O valor de v é ' + v + 'seu tamanho é ' + v.length);
        }
        else
        {
            //console.log('4: O valor de v é ' + v + 'seu tamanho é ' + v.length);
            v = v.replace(/(\d{5})(\d)/g,"$1-$2");//Número com 9 dígitos. Formato: (99) 99999-9999
            //console.log('5: O valor de v é ' + v + 'seu tamanho é ' + v.length);
            v = v.substring(0, 14);
            //console.log('6: O valor de v é ' + v + 'seu tamanho é ' + v.length);
        }  
    }
    return v;
}