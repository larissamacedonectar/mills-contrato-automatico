const customization = require('%app.core%/customization');
const PhoneField = require('%app.fields%/phone/phone');

function _doValidateFone(fields, errors, callback) {

	if (!_.isEmpty(this.model.get('phone_office')))
	{
		if (!validador_fone(this.model.get('phone_office')))
		{
			errors['phone_office'] = errors['phone_office'] || {};
			errors['phone_office'].fone_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('phone_alternate')))
	{
		if (!validador_fone(this.model.get('phone_alternate')))
		{
			errors['phone_alternate'] = errors['phone_alternate'] || {};
			errors['phone_alternate'].fone_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('phone_work')))
	{
		if (!validador_fone(this.model.get('phone_work')))
		{
			errors['phone_work'] = errors['phone_work'] || {};
			errors['phone_work'].fone_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('phone_mobile')))
	{
		if (!validador_fone(this.model.get('phone_mobile')))
		{
			errors['phone_mobile'] = errors['phone_mobile'] || {};
			errors['phone_mobile'].fone_invalido = true;
		}
	}

	callback(null, fields, errors);
}

function validador_fone(fone) {

	var fone_sem_mascara = fone.replace(/\D/g,'');

	if(fone_sem_mascara.length == 10 || fone_sem_mascara.length == 11)
		return true;

	return false;
}

let ValidarPhoneField = customization.extend(PhoneField, {
	initialize(options) {
		app.error.errorName2Keys['fone_invalido'] = 'O número de telefone deve possuir 10 ou 11 dígitos (DDD incluso).';
		this._super(options);
		if (this.name === 'phone_office'    || this.name === 'phone_alternate' ||
			this.name === 'phone_work'      || this.name === 'phone_mobile') {
			this.model.addValidationTask('valida_fone', _doValidateFone.bind(this));
		}
	},
	handleValidationError(error) {
		this._super(error);
	},
});

customization.register(ValidarPhoneField, {
	metadataType: 'phone',
	module: 'Accounts',
});

customization.register(ValidarPhoneField, {
	metadataType: 'phone',
	module: 'Contacts',
});