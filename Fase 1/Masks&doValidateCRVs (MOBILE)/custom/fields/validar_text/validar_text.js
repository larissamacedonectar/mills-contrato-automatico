const customization = require('%app.core%/customization');
const TextField = require('%app.fields%/text-field');

function _doValidateCNPJ(fields, errors, callback) {

	if (!_.isEmpty(this.model.get('cnpj_c')))
	{
		if (!validador_cnpj(this.model.get('cnpj_c')))
		{
			errors['cnpj_c'] = errors['cnpj_c'] || {};
			errors['cnpj_c'].cnpj_invalido = true;
		}
	}

	callback(null, fields, errors);
}

function validador_cnpj(cnpj) {

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
}

function _doValidateCEP(fields, errors, callback) {

	if (!_.isEmpty(this.model.get('billing_address_postalcode')))
	{
		if (!validador_cep(this.model.get('billing_address_postalcode')))
		{
			errors['billing_address_postalcode'] = errors['billing_address_postalcode'] || {};
			errors['billing_address_postalcode'].cep_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('shipping_address_postalcode')))
	{
		if (!validador_cep(this.model.get('shipping_address_postalcode')))
		{
			errors['shipping_address_postalcode'] = errors['shipping_address_postalcode'] || {};
			errors['shipping_address_postalcode'].cep_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('primary_address_postalcode')))
	{
		if (!validador_cep(this.model.get('primary_address_postalcode')))
		{
			errors['primary_address_postalcode'] = errors['primary_address_postalcode'] || {};
			errors['primary_address_postalcode'].cep_invalido = true;
		}
	}

	if (!_.isEmpty(this.model.get('alt_address_postalcode')))
	{
		if (!validador_cep(this.model.get('alt_address_postalcode')))
		{
			errors['alt_address_postalcode'] = errors['alt_address_postalcode'] || {};
			errors['alt_address_postalcode'].cep_invalido = true;
		}
	}

	callback(null, fields, errors);
}

function validador_cep(cep) {

	var cep_sem_mascara = cep.replace(/\D/g,'');

	if(cep_sem_mascara.length == 8)
		return true;
	return false;
}

let ValidarTextField = customization.extend(TextField, {
	initialize(options) {

		app.error.errorName2Keys['cnpj_invalido'] = 'O CNPJ inserido possui menos de 14 dígitos e/ou é inválido.';
		app.error.errorName2Keys['cep_invalido'] = 'O CEP deve possuir 8 dígitos.';
		
		this._super(options);

		if (this.name === 'cnpj_c') {
			this.model.addValidationTask('valida_cnpj', _doValidateCNPJ.bind(this));
		}

		if (this.name === 'billing_address_postalcode' || this.name === 'shipping_address_postalcode' ||
			 this.name === 'primary_address_postalcode' || this.name === 'alt_address_postalcode') {
			this.model.addValidationTask('valida_cep', _doValidateCEP.bind(this));
		}
	},
	handleValidationError(error) {
		this._super(error);
	},
});

customization.register(ValidarTextField, {
	metadataType: 'text',
	module: 'Accounts',
});

customization.register(ValidarTextField, {
	metadataType: 'text',
	module: 'Contacts',
});