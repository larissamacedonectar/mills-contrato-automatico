({
    extendsFrom: 'RecordView',

    initialize: function (options) {
		app.error.errorName2Keys['fone_invalido'] = 'O número de telefone deve possuir 10 ou 11 dígitos (DDD incluso).';
        app.error.errorName2Keys['cep_invalido'] = 'O CEP deve possuir 8 dígitos.';
        this._super('initialize', [options]);

		//this.model.addValidationTask('Validate_phone', _.bind(this._doValidatePhone, this)); //Retirado no projeto do Contrato Automático Março/Abril - 2022
		this.model.addValidationTask('valida_fone', _.bind(this._doValidateFone, this));
        this.model.addValidationTask('valida_cep', _.bind(this._doValidateCEP, this));
	},

	_doValidateFone: function(fields, errors, callback) {
        //validate type requirements

        if (!_.isEmpty(this.model.get('phone_work')))
        {
            if (!this.validador_fone(this.model.get('phone_work')))
            {
                errors['phone_work'] = errors['phone_work'] || {};
                errors['phone_work'].fone_invalido = true;
            }
        }

        if (!_.isEmpty(this.model.get('phone_mobile')))
        {
            if (!this.validador_fone(this.model.get('phone_mobile')))
            {
                errors['phone_mobile'] = errors['phone_mobile'] || {};
                errors['phone_mobile'].fone_invalido = true;
            }
        }

        callback(null, fields, errors);
    },

    _doValidateCEP: function(fields, errors, callback) {
        //validate type requirements
        
        if (!_.isEmpty(this.model.get('primary_address_postalcode')))
        {
            if (!this.validador_cep(this.model.get('primary_address_postalcode')))
            {
                errors['primary_address_postalcode'] = errors['primary_address_postalcode'] || {};
                errors['primary_address_postalcode'].cep_invalido = true;
            }
        }

        if (!_.isEmpty(this.model.get('alt_address_postalcode')))
        {
            if (!this.validador_cep(this.model.get('alt_address_postalcode')))
            {
                errors['alt_address_postalcode'] = errors['alt_address_postalcode'] || {};
                errors['alt_address_postalcode'].cep_invalido = true;
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

	_buildGridsFromPanelsMetadata:function(panels){
        var noEditFields = new Array();
        this._super('_buildGridsFromPanelsMetadata',[panels]);
        if(this.model.get('codsap_c') != '') {
            for(field in this.model.fields) {
				if (field == 'categoria_contato_c') {
					noEditFields.push(field);
				}
                
            }
            this.noEditFields=noEditFields;
        }
	},
	
    _doValidatePhone: function(fields, errors, callback) {
        var phone = this.model.get('phone_work');       
        var phone2 = this.model.get('phone_mobile');
		
		phone = phone.replace(")","");
		phone = phone.replace("(","");
		phone = phone.replace("-","");
		phone = phone.replace(" ","");
		
		phone2 = phone2.replace(")","");
		phone2 = phone2.replace("(","");
		phone2 = phone2.replace("-","");
		phone2 = phone2.replace(" ","");
		
        if( (isNaN(phone)) || (isNaN(phone2)) ) {
		  errors['phone_work'] = errors['phone_work'] || {};
          errors['phone_work'].required = true;
		  errors['phone_mobile'] = errors['phone_mobile'] || {};
          errors['phone_mobile'].required = true;
		  
          App.alert.show("1", {messages:"Formato do telefone n\u00E3o permitido.",level:"error",autoClose: 0} );			
		}		
		
		if((phone.length > 11) || (phone.length < 8) || (phone2.length > 11) || (phone2.length < 8) ){
		  errors['phone_work'] = errors['phone_work'] || {};
          errors['phone_work'].required = true;
		  errors['phone_mobile'] = errors['phone_mobile'] || {};
          errors['phone_mobile'].required = true;
          App.alert.show("1", {messages:"Formato do telefone n\u00E3o permitido.",level:"error",autoClose: 0} );			
		}		
		
		if((phone2.length == 9) && (phone2.substring(0,0) != '9')){
		 errors['phone_mobile'] = errors['phone_mobile'] || {};
         errors['phone_mobile'].required = true;
		 App.alert.show("1", {messages:"Numero de celular n\u00E3o com 9, favor corrigir.",level:"error",autoClose: 0} );
		}

        callback(null, fields, errors);
	},

	_renderHtml: function(options) {
		this._super("_renderHtml", [options]);
	 },
	
})
