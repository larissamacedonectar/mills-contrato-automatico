/* Máscaras JS */
function mascara(o,f) {

    o.value=f(o);
    //$('[name=cpf_cnpj_c]').trigger('change');
    $(o).trigger('change');
}

function mascara_cnpj(obj) {

    v = obj.value;
    //alert('1:O valor v é ' + v);
    //console.log('1:O valor v é ' + v);
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito
    //alert('2:O valor v é ' + v);
    //console.log('2:O valor v é ' + v);

    if (obj.name == 'cnpj_c') {
        
        v = v.replace(/^(\d{2})(\d{3})?(\d{3})?(\d{4})?(\d{2})?/, "$1.$2.$3/$4-$5");
        v = v.substring(0, 18);
    }  
    return v;
}
function mascara_cep(obj) {

    v = obj.value;
    //alert('3:O valor v é ' + v);
    //console.log('3:O valor v é ' + v);
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito
    //alert('4:O valor v é ' + v);
    //console.log('4:O valor v é ' + v);

    if (obj.name == 'cep_c') {

        v = v.replace(/^(\d{5})(\d{3})?/, "$1-$2");
        v = v.substring(0, 9);
    }
    return v;
}
function mascara_fone(obj) {

    v = obj.value;
    //alert('5:O valor v é ' + v);
    //console.log('5:O valor v é ' + v);
    v = v.replace(/\D/g,""); //Remove tudo o que não é dígito
    //alert('6:O valor v é ' + v);
    //console.log('5:O valor v é ' + v);

    if (obj.name == 'phone_c' || obj.name == 'phone_work') {
        console.log('masquiane');
        v = v.replace(/^(\d{2})(\d{4})?(\d{4})?/, "($1) $2-$3");
        v = v.substring(0, 14);
    }
    return v;
}

    