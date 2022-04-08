
const customization = require('%app.core%/customization');
const dialog = require('%app.core%/dialog');
const EditView = require('%app.views.edit%/edit-view');
const app = SUGAR.App;

const customEditView = customization.extend(EditView, {initialize,onHeaderSaveClick});
customization.register(customEditView, { module: 'Accounts' });
 
module.exports = customEditView;

function initialize(options) {
    this._super(options);
    //this.model.on("change:cnpj_c", cnpjmask, this);
}    

        

function cnpjmask(){

    if(this.module == 'Accounts'){
        if(this.model.get('cnpj_c') != undefined){
            
            var v=this.model.get('cnpj_c');

            v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
            
            //Coloca ponto entre o segundo e o terceiro dígitos
            v=v.replace(/^(\d{2})(\d)/,"$1.$2")

            //Coloca ponto entre o quinto e o sexto dígitos
            v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3")

            //Coloca uma barra entre o oitavo e o nono dígitos
            v=v.replace(/\.(\d{3})(\d)/,".$1/$2")

            //Coloca um hífen depois do bloco de quatro dígitos
            v=v.replace(/(\d{4})(\d)/,"$1-$2")
                
            // v=v.replace(/^(\d{2})(\d{3})?(\d{3})?(\d{4})?(\d{2})?/, "$1.$2.$3/$4-$5")
            v=v.substring(0, 18);
            
            this.model.set('cnpj_c',v)
        }
    }    
}

function onHeaderSaveClick() {

    if(this.action == "create"){
        let exists; 

        let filtro = {
            "filter": [{
                "$and": [
                    //{"id": [{"$not": this.model.get('id')}]},
                    {"cnpj_c": this.model.get('cnpj_c')}],
                
            }]
        }; 
        let url = app.api.buildURL("Accounts", "filter", null, filtro);
        app.api.call('read', url, null, {
            success: _.bind(function(data) {
                exists = data;
            },this),
            error: function(error) {
                console.log("error loading forecast "+error);
            }.bind(this),
        }, {async:false});

        if (exists.records.length != 0) {
            dialog.showAlert('Impossivel salvar, CNPJ ja existe na base de dados !', {
                buttonLabels: 'Ok'
            });
        }
        else{
            this._super();
        }
    }
    else this._super();
}


