({
    extendsFrom: 'RecordView',
    req: false,

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        app.error.errorName2Keys['cnpj_invalido'] = 'O CNPJ inserido possui menos de 14 dígitos e/ou é inválido.';
        app.error.errorName2Keys['fone_invalido'] = 'O número de telefone deve possuir 10 ou 11 dígitos (DDD incluso).';
        app.error.errorName2Keys['cep_invalido'] = 'O CEP deve possuir 8 dígitos.';
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary']);
        this._super('initialize', [options]);
        this.model.fields.latitude_c.readonly = true;
        this.model.fields.longitude_c.readonly = true;

        this.model.on('change:estado_c', this.getLocalization, this);
        this.model.on('change:cidade_c', this.getLocalization, this);
        this.model.on('change:billing_address_street', this.getLocalization, this);
        this.model.on('change:billing_address_number_c', this.getLocalization, this);
        this.model.on('data:sync:complete', this.getLocalization, this);
        this.req = true;
        
        console.log("passou aqui.");
        this.model.on('change:oxigena_carteira_c', this.getOxigenacao, this);
        this.model.addValidationTask('valida_cnpj', _.bind(this._doValidateCNPJ, this));
        this.model.addValidationTask('valida_fone', _.bind(this._doValidateFone, this));
        this.model.addValidationTask('valida_cep', _.bind(this._doValidateCEP, this));
    },

    _doValidateCNPJ: function(fields, errors, callback) {
        //validate type requirements

        if (!_.isEmpty(this.model.get('cnpj_c')))
        {
            if (!this.validador_cnpj(this.model.get('cnpj_c')))
            {
                errors['cnpj_c'] = errors['cnpj_c'] || {};
                errors['cnpj_c'].cnpj_invalido = true;
            }
        }

        callback(null, fields, errors);
    },

    _doValidateFone: function(fields, errors, callback) {
        //validate type requirements

        if (!_.isEmpty(this.model.get('phone_office')))
        {
            if (!this.validador_fone(this.model.get('phone_office')))
            {
                errors['phone_office'] = errors['phone_office'] || {};
                errors['phone_office'].fone_invalido = true;
            }
        }

        if (!_.isEmpty(this.model.get('phone_alternate')))
        {
            if (!this.validador_fone(this.model.get('phone_alternate')))
            {
                errors['phone_alternate'] = errors['phone_alternate'] || {};
                errors['phone_alternate'].fone_invalido = true;
            }
        }

        callback(null, fields, errors);
    },

    _doValidateCEP: function(fields, errors, callback) {
        //validate type requirements

        if (!_.isEmpty(this.model.get('billing_address_postalcode')))
        {
            if (!this.validador_cep(this.model.get('billing_address_postalcode')))
            {
                errors['billing_address_postalcode'] = errors['billing_address_postalcode'] || {};
                errors['billing_address_postalcode'].cep_invalido = true;
            }
        }

        if (!_.isEmpty(this.model.get('shipping_address_postalcode')))
        {
            if (!this.validador_cep(this.model.get('shipping_address_postalcode')))
            {
                errors['shipping_address_postalcode'] = errors['shipping_address_postalcode'] || {};
                errors['shipping_address_postalcode'].cep_invalido = true;
            }
        }

        callback(null, fields, errors);
    },

    validador_fone: function (fone) {
        
        var fone_sem_mascara = fone.replace(/\D/g,'');

        if(fone_sem_mascara.length == 10 || fone_sem_mascara.length == 11)
            return true;
        return false;
    },

    validador_cep: function (cep) {
        
        var cep_sem_mascara = cep.replace(/\D/g,'');

        if(cep_sem_mascara.length == 8)
            return true;
        return false;
    },

    validador_cnpj: function (cnpj) {

        var valor_cnpj = cnpj.replace(/\D/g,'');
        console.log('O valor_cnpj é ' + valor_cnpj);
    
        if(valor_cnpj.length != 14)
            return false;
    
        if(valor_cnpj == '')
            return false;
    
        // Elimina CNPJs inválidos
    
       if(valor_cnpj == "00000000000000" || valor_cnpj == "11111111111111" ||
          valor_cnpj == "22222222222222" || valor_cnpj == "33333333333333" ||
          valor_cnpj == "44444444444444" || valor_cnpj == "55555555555555" ||
          valor_cnpj == "66666666666666" || valor_cnpj == "77777777777777" ||
          valor_cnpj == "88888888888888" || valor_cnpj == "99999999999999")
          return false;
    
       // Valida os dígitos de verificação (DVs)
    
       var tamanho = valor_cnpj.length - 2;
       var numeros = valor_cnpj.substring(0,tamanho);
       var digitos = valor_cnpj.substring(tamanho);
       var soma = 0;
       var pos = tamanho - 7;
    
       for(var i = tamanho; i >= 1; i--) {
           soma += numeros.charAt(tamanho - i) * pos--;
    
           if(pos < 2)
               pos = 9;
       }
    
       var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    
       if(resultado != digitos.charAt(0))
           return false;
    
       tamanho++;
       numeros = valor_cnpj.substring(0,tamanho);
       soma = 0;
       pos = tamanho - 7;
    
       for(var i = tamanho; i >= 1; i--) {
           soma += numeros.charAt(tamanho - i) * pos--;
    
           if(pos < 2)
               pos = 9;
       }
    
       resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    
       if(resultado != digitos.charAt(1))
           return false;
    
       return true;
    },

    getLocalization: function (model) {
        if(this.req == true) {
            var that = this;
            var location = '';
            var state = this.model.get('estado_c');
            var city = this.model.get('cidade_c');
            var street = this.model.get('billing_address_street');
            var billing_address_number_c = this.model.get('billing_address_number_c');
            var data = {};

            if((typeof street !== undefined && typeof billing_address_number_c !== undefined) && (typeof state !== undefined || typeof city !== undefined)) {
                if(typeof city !== undefined) {
                    location = city + ' ' + street + ' ' + billing_address_number_c;
                } else {
                    location = state + ' ' + street + ' ' + billing_address_number_c;
                }

                if(typeof location !== undefined) {
                    location = location.replace(/ /g, "+");

                    $.get("https://nominatim.openstreetmap.org/?addressdetails=1&q=" + location + "&format=json&limit=1", function (data) {
                        if(typeof data[0] != 'undefined') {
                            that.model.set('latitude_c', data[0].lat);
                            that.model.set('longitude_c', data[0].lon);
                        }
                    }, "json");
                }
            }
        }
    },
    
    getOxigenacao: function () {
        
        var that = this;
        var oxigenacao = that.model.get('oxigena_carteira_c');
        console.log(oxigenacao);
        
        
        if(oxigenacao == 'Cold') {
            console.log("Oxigenacao e COLD");
            that.model.set('passar_para_cold_vdd_c', 'Yes');
            
        } else if (oxigenacao == 'Hot') {
            that.model.set('passar_para_cold_vdd_c', 'No');
            console.log("Oxigenacao e HOT");
        }
        
    },
    
})