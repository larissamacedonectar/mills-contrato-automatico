/* Máscaras JS */

function mascara(o,f) {
    var url = window.location.href.split('/');
    //ajsute para não impactar no filtro de CNPJ - não aplica a mascara quando esta na listagem
    console.log(url.length);
    
    if(url.length != 4){
        o.value=f(o);
        $(o).trigger('change');
    }
    
}
function mascara_cnpj(obj) {

    v = obj.value;
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito

    if (obj.name == 'cnpj_c') {
        
        v = v.replace(/^(\d{2})(\d)/,"$1.$2"); // Coloca um ponto entre o segundo e o terceiro dígitos
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3"); // Coloca um ponto entre o quinto e o sexto dígitos
        v = v.replace(/\.(\d{3})(\d)/,".$1/$2"); // Coloca uma barra entre o oitavo e o nono dígitos
        v = v.replace(/(\d{4})(\d)/,"$1-$2"); // Coloca um hífen depois do bloco de quatro dígitos
        v = v.substring(0, 18);
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
        v = v.substring(0, 9);
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
				
        if(v.length <= 13) {
            v = v.replace(/(\d{4})(\d)/g,"$1-$2");//Número com 8 dígitos. Formato: (99) 9999-9999
            v = v.substring(0, 14);
        }
        else {
            v = v.replace(/(\d{5})(\d)/g,"$1-$2");//Número com 9 dígitos. Formato: (99) 99999-9999
            v = v.substring(0, 15);
        }  
    }
    return v;
}
function mascara_name_contatos(obj) {

    v = obj.value;
    v_sem_especial = v.replace(/[^a-zA-Z ]/g, '');

    return v_sem_especial;
}
