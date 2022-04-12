({
    extendsFrom: 'CreateView',

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        app.error.errorName2Keys['fone_invalido'] = 'O número de telefone deve possuir 10 ou 11 dígitos (DDD incluso).';
        app.error.errorName2Keys['cep_invalido'] = 'O CEP deve possuir 8 dígitos.';
        this._super('initialize', [options]);

        this.model.on('change:account_id', this.getAddress, this);
        this.model.on('change:primary_address_street', this.getAddress, this);
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

    getAddress: function () {
         var that = this;
        // var location = '';
         var account_id = this.model.get('account_id');
         this.retrieveBean("Accounts", account_id).then( accountBean => {
            this.model.set('primary_address_city', accountBean.get('cidade_c'));
            this.model.set('primary_address_state', accountBean.get('estado_c'));
            this.model.set('primary_address_country',"Brasil");

            this.model.set('primary_address_quarter_c', accountBean.get('billing_address_quarter_c'));
            this.model.set('primary_address_add_c', accountBean.get('billing_address_add_c'));
            this.model.set('primary_address_number_c', accountBean.get('billing_address_number_c'));

            this.model.set('alt_address_city', accountBean.get('cidade_c'));
            this.model.set('alt_address_state', accountBean.get('estado_c'));
            this.model.set('alt_address_country', accountBean.get('estado_c'));
            this.model.set('alt_address_country',"Brasil");
         });
        
    },

    retrieveBean: function(module, id) {
		let bean = app.data.createBean(module, {id: id});
		return new Promise((resolve, reject) => {
			bean.fetch({
				success: resolve,
				error: (bean, httpErrror) => reject( new Error(httpErrror.message) )
			});
		});
	},
})