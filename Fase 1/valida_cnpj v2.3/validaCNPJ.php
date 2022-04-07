<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/SugarQuery/SugarQuery.php');
require_once('include/api/SugarApiException.php');
include_once('modules/ACLRoles/ACLRole.php');
require_once('include/SugarLogger/LoggerManager.php'); 
    
class ValidaCNPJ {

    function validadorCNPJ(&$bean, $event, $arguments) {

        if($bean->cnpj_c != $bean->fetched_row['cnpj_c']){
            
            $GLOBALS["log"]->fatal("--------- início Valida CNPJ ---------");
            
            if ($bean->cnpj_c != null && $bean->cnpj_c != ''){

                $GLOBALS["log"]->fatal("CNPJ: " . $bean->cnpj_c);
				
				/*
				* TK-76-000071: Duplicidade de CNPJ (Samuel Shin Kim - 19/10/2020)
				* O CNPJ está sendo salvo com máscara no banco de dados, porém o usuário pode inserir qualquer coisa no campo.
				* Portanto, é necessário formatar o dado inserido para que esteja com a máscara adequada antes de comparar com
				* o banco de dados para verificar duplicidade.
				*/
				
				$cnpj_formatado = preg_replace( '/[^0-9]/is', '', $bean->cnpj_c); // Remove todos os caracteres que não sejam números.
				$cnpj = $this->mask($cnpj_formatado, '##.###.###/####-##'); // Aplica a máscara padrão de CNPJ.
				
				/*
				 * TK-076-000208: O Sugar está permitindo criar CNPJ Duplicados (Samuel Shin Kim - 11/11/2021)
				 * O problema ocorre porque consultas usando SugarQuery levam em consideração as permissões do usuário. Caso o usuário não tenha permissão para enxergar
				 * algum cadastro já existente na base, a query não retorna um resultado, permitindo a criação de um duplicado. Query ajustada para ser feita por SQL.
				 */
				 
				/* 
                $Query = new SugarQuery();
                $Query->from(BeanFactory::getBean('Accounts'));
                $Query->where()->equals('cnpj_c', $cnpj);
                $Query->where()->equals('deleted', 0);
                
                // Para o caso de edição (update) da conta
                if($bean->fetched_row['cnpj_c'] != ''){ 
                    $GLOBALS["log"]->fatal("Update conta");
                    $Query->where()->notEquals('id',$bean->id);
                }

                $results = $Query->execute();
				 */
				 
				$conn = $GLOBALS['db']->getConnection();
				$sql = 'SELECT id, team_id
						FROM accounts
						INNER JOIN accounts_cstm
							ON id = id_c
						WHERE cnpj_c = ?
						AND deleted = 0';
				$stmt = $conn->executeQuery($sql, array($cnpj));
				$results = $stmt->fetch();
    
                if($results != null && $bean->flag == false){
					$sql = 'SELECT name
							FROM teams
							WHERE id = ?';
					$stmt = $conn->executeQuery($sql, array($results['team_id']));
					$team_name = $stmt->fetchColumn();
					
                    $GLOBALS["log"]->fatal("CNPJ duplicado");
                    throw new SugarApiExceptionInvalidParameter("Já existe uma conta da equipe " . $team_name . " para este CNPJ.");
                }
                else{
                    
                    $GLOBALS["log"]->fatal("CNPJ sem formatação: ".$cnpj_formatado);

                    //Valida quantidade de dígitos do CNPJ
                    if(strlen($cnpj_formatado) == 14){
                            
                        if (preg_match('/(\d)\1{13}/', $cnpj_formatado)) {
                            $GLOBALS["log"]->fatal("CNPJ com todos os números iguais");
                            throw new SugarApiExceptionInvalidParameter("Dados inválidos de CNPJ. Favor corrigir e salvar novamente.");
                        }

                        $cnpj_original = $cnpj_formatado;

                        // Captura os primeiros 12 números do CNPJ
                        $primeiros_numeros_cnpj = substr( $cnpj_original, 0, 12 );
                        
                        /**
                         * Multiplicação do CNPJ
                         *
                         * @param string $cnpj Os digitos do CNPJ
                         * @param int $posicoes A posição que vai iniciar a regressão
                         * @return int O
                         *
                         */
                        if ( ! function_exists('multiplica_cnpj') ) {
                            function multiplica_cnpj( $cnpj, $posicao = 5 ) {
                                // Variável para o cálculo
                                $calculo = 0;
                                
                                // Laço para percorrer os item do cnpj
                                for ( $i = 0; $i < strlen( $cnpj ); $i++ ) {
                                    // Cálculo mais posição do CNPJ * a posição
                                    $calculo = $calculo + ( $cnpj[$i] * $posicao );
                                    
                                    // Decrementa a posição a cada volta do laço
                                    $posicao--;
                                    
                                    // Se a posição for menor que 2, ela se torna 9
                                    if ( $posicao < 2 ) {
                                        $posicao = 9;
                                    }
                                }
                                // Retorna o cálculo
                                return $calculo;
                            }
                        }
                        
                        // Faz o primeiro cálculo
                        $primeiro_calculo = multiplica_cnpj( $primeiros_numeros_cnpj );
                        
                        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
                        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
                        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 :  11 - ( $primeiro_calculo % 11 );
                        
                        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
                        // Agora temos 13 números aqui
                        $primeiros_numeros_cnpj .= $primeiro_digito;
                    
                        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
                        $segundo_calculo = multiplica_cnpj( $primeiros_numeros_cnpj, 6 );
                        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 :  11 - ( $segundo_calculo % 11 );
                        
                        // Concatena o segundo dígito ao CNPJ
                        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;
                        
                        // Verifica se o CNPJ gerado é idêntico ao enviado
                        if ( $cnpj === $cnpj_original) {

                            if ( $bean->integracao_sucesso_c != "SUCESSO" ){

                                $GLOBALS["log"]->fatal("CNPJ válido");
								
								$consumerKey = $GLOBALS['app_list_strings']['VALORES_DE_SISTEMA']['serpro_consumer_key'];
								$consumerSecret = $GLOBALS['app_list_strings']['VALORES_DE_SISTEMA']['serpro_consumer_secret'];
								$auth_key = base64_encode($consumerKey . ':' . $consumerSecret);

                                //chamada para obter o token de autenticacao da integracao
                                $curl_token = curl_init();

                                $url_token = $GLOBALS['app_list_strings']['VALORES_DE_SISTEMA']['serpro_url_token'];

                                curl_setopt_array($curl_token, array(
                                CURLOPT_URL => $url_token,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => "grant_type=client_credentials",
                                CURLOPT_HTTPHEADER => array(
                                    "Authorization: Basic " . $auth_key,
                                    "Content-Type: application/x-www-form-urlencoded"
                                ),
                                ));

                                $response = curl_exec($curl_token);

                                curl_close($curl_token);

                                $auth = json_decode($response);

                                $GLOBALS["log"]->fatal("Token: " . $auth->access_token );

                                //chamada da integração
                                $curl = curl_init();

                                $url = $GLOBALS['app_list_strings']['VALORES_DE_SISTEMA']['serpro_url_cnpj'] . $cnpj_formatado;
                                
                                $GLOBALS["log"]->fatal($url);
                                
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => $url,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_HEADER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => array(
                                        "Authorization: Bearer " . $auth->access_token
                                    ),
                                ));

                                $output = curl_exec($curl);  
                                
                                $output_decoded = json_decode($output);

                                $json_data = mb_substr($output, curl_getinfo($curl, CURLINFO_HEADER_SIZE));  
                                $data = json_decode($json_data);

                                //pega o código de status da integração, 200 para OK, qualquer outro código é erro
                                $code = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));

                                curl_close($curl);

                                // existem dados do cliente
                                switch($code) {
                                    case 200:
                                        $GLOBALS["log"]->fatal("Empresa: " . $data->nomeEmpresarial);

                                        // valores da situacao cadastral
                                        $codigo_situacao = intval($data->situacaoCadastral->codigo);

                                        switch ($codigo_situacao) {
                                            case 1:
                                                $situacao = "Nula";
                                                break;
                                            case 2:
                                                $situacao = "Ativa";
                                                break;
                                            case 3:
                                                $situacao = "Suspensa";
                                                break;
                                            case 4:
                                                $situacao = "Inapta";
                                                break;
                                            case 8:
                                                $situacao = "Baixada";
                                                break;
                                            default:
                                                $situacao = "-";
                                        }

                                        // valores do tipo de estabelecimento
                                        $codigo_estabelecimento = intval($data->tipoEstabelecimento);

                                        switch ($codigo_estabelecimento) {
                                            case 1:
                                                $estabelecimento = "Matriz";
                                                break;
                                            case 2:
                                                $estabelecimento = "Filial";
                                                break;
                                            default:
                                                $estabelecimento = "-";
                                        }

                                        // valores do tipo de estabelecimento
                                        $codigo_porte = intval($data->porte);

                                        switch ($codigo_porte) {
                                            case 0:
                                                $porte = "NAO_INFORMADO";
                                                break;
                                            case 1:
                                                $porte = "ME";
                                                break;
                                            case 3:
                                                $porte = "EPP";
                                                break;
                                            case 5:
                                                $porte = "DEMAIS";
                                                break;
                                            default:
                                                $porte = "-";
                                        }

                                        $bean->integracao_sucesso_c = "SUCESSO";
                                        $bean->name = $data->nomeEmpresarial . " - " . $porte;
                                        $bean->nome_fantasia_c = $data->nomeEmpresarial;
                                        $bean->cnae_c = $data->cnaePrincipal->codigo . " - " . $data->cnaePrincipal->descricao;
                                        $bean->natureza_juridica_c = $data->naturezaJuridica->codigo . " - " . $data->naturezaJuridica->descricao;
                                        $bean->data_abertura_c = date_format(date_create($data->dataAbertura),"d/m/Y");
                                        $bean->situacao_especial_c = $data->situacaoEspecial;
                                        $bean->codigo_situacao_cadastral_c = $situacao;
                                        $bean->data_situacao_cadastral_c = date_format(date_create($data->situacaoCadastral->data),"d/m/Y");
                                        $bean->motivo_situacao_cadastral_c = $data->situacaoCadastral->motivo;
                                        $bean->tipo_estabelecimento_c = $estabelecimento;
                                        $bean->capital_social_c = "R$ ". number_format(($data->capitalSocial/100),2,",",".");
                                        $bean->porte_receita_c = $porte;
                                        //$bean->phone_office = "(" . $data->telefones->ddd . ") " . $data->telefones->numero;
                                        $bean->billing_address_street = $data->endereco->logradouro;
                                        $bean->billing_address_number_c = $data->endereco->numero;
                                        $bean->billing_address_add_c = $data->endereco->complemento;
                                        $bean->billing_address_postalcode = $data->endereco->cep;
                                        $bean->billing_address_quarter_c = $data->endereco->bairro;
                                        
                                        
                                        // Busca Estado
                                        $SQEstado = new SugarQuery();
                                        $SQEstado->from(BeanFactory::newBean('iT4_estados'));
                                        $SQEstado->where()->equals('name', $data->endereco->uf);
                                        $ResultsEstado = $SQEstado->execute();
                                        
                                        if($ResultsEstado[0]['id'] != ""){
                                            
                                            // Atribui estado
                                            $bean->it4_estados_id_c = $ResultsEstado[0]['id'];
                                            
                                            // Busca cidade
                                            $SQCidade = new SugarQuery();
                                            $SQCidade->from(BeanFactory::newBean('iT4_cidades'));
                                            $SQCidade->where()->equals('name', $data->endereco->municipio->descricao);
                                            $SQCidade->where()->equals('it4_estados_id_c', $ResultsEstado[0]['id']);
                                            $ResultsCidade = $SQCidade->execute();
                                            
                                            if($ResultsCidade[0]['id'] != ""){
                                                $bean->it4_cidades_id_c = $ResultsCidade[0]['id'];
                                            } else {
                                                // Se nao achou, cria a Cidade
                                                $Cidade = BeanFactory::newBean("iT4_cidades");
                                                $Cidade->it4_estados_id_c = $ResultsEstado[0]['id'];
                                                $Cidade->name = $data->endereco->municipio->descricao;
                                                $Cidade->save();
                                                $bean->it4_cidades_id_c = $Cidade->id;
                                            }
                                        }

                                        $GLOBALS["log"]->fatal("Fim da integração com sucesso");

                                        break;
                                    case 400:
                                        $GLOBALS["log"]->fatal("400: O número de inscrição do CNPJ informado não é válido.");
                                        throw new SugarApiExceptionInvalidParameter("O número de inscrição do CNPJ informado não é válido.");
                                        break;
                                    case 500:
                                        $GLOBALS["log"]->fatal("500: Ocorreu algum erro interno.");
                                        throw new SugarApiExceptionInvalidParameter("Ocorreu algum erro interno.");
                                        break;
                                    case 503:
                                        $GLOBALS["log"]->fatal("503: A consulta de CNPJ está indisponível no momento.");
                                        throw new SugarApiExceptionInvalidParameter("A consulta de CNPJ está indisponível no momento.");
                                        break;
                                    default:
                                        $GLOBALS["log"]->fatal("Integração não retornou. Erro: $code");
                                        $GLOBALS["log"]->fatal(print_r($output_decoded,true));
                                        throw new SugarApiExceptionInvalidParameter("Integração não retornou. Erro: $code");
                                        break;
                                }
                            }
                        }
                        else {
                            $GLOBALS["log"]->fatal("CNPJ com digitos invalidos");
                            throw new SugarApiExceptionInvalidParameter("Dados inválidos de CNPJ. Favor corrigir e salvar novamente.");
                        } 
                    }
                    else {
                        $GLOBALS["log"]->fatal("CNPJ não possui 14 digitos");
                        throw new SugarApiExceptionInvalidParameter("CNPJ inválido, digite um CNPJ com 14 dígitos.");
                    }
                }
            }

            $GLOBALS["log"]->fatal("--------- fim Valida CNPJ ---------");
        }    
    }

    function validadorIE(&$bean, $event, $arguments) {

        $bean->flag = false;

        if ($bean->im_c != $bean->fetched_row['im_c']) {
            
            $GLOBALS["log"]->fatal("--------- início Valida IE ---------");
            
            if ($bean->im_c != null && $bean->im_c != ''){

                $cnpj_formatado = preg_replace( '/[^0-9]/is', '', $bean->cnpj_c); // Remove todos os caracteres que não sejam números.
				$cnpj = $this->mask($cnpj_formatado, '##.###.###/####-##'); // Aplica a máscara padrão de CNPJ.
                
                $ie = $bean->im_c;

                $GLOBALS["log"]->fatal("CNPJ: " . $cnpj);
                $GLOBALS["log"]->fatal("IE: " . $ie);

                $conn = $GLOBALS['db']->getConnection();
				$sql = 'SELECT id, name, team_id, cnpj_c, im_c
						FROM accounts
						INNER JOIN accounts_cstm
							ON id = id_c
						WHERE cnpj_c = ? AND im_c = ?
						AND deleted = 0';
				$stmt = $conn->executeQuery($sql, array($cnpj, $ie));
				$results_ie = $stmt->fetch();

                if($results_ie != null) //se houver já uma conta sob o CNPJ e a IE inseridas
                {
                    throw new SugarApiExceptionInvalidParameter("Já existe uma conta cadastrada com este CNPJ e com esta IE. Tente um outro CNPJ e/ou uma outra IE.");
                }
                else //se não houver
                {
                    $conn = $GLOBALS['db']->getConnection();
                    $sql = 'SELECT id, name, team_id, cnpj_c, im_c
						FROM accounts
						INNER JOIN accounts_cstm
							ON id = id_c
						WHERE cnpj_c = ?
						AND deleted = 0';
                    $stmt = $conn->executeQuery($sql, array($cnpj));
                    $results_cnpj = $stmt->fetch();

                    $temacesso = false;
                    global $current_user;

                    require_once('modules/ACLRoles/ACLRole.php');
                    
                    $currentUserRoles = ACLRole::getUserRoleNames($current_user->id);
                    $validRoles = $GLOBALS['app_list_strings']['BACKOFFICE_ROLES'];
                    $matchingRoles = array_intersect($currentUserRoles, $validRoles);

                    if($current_user->isAdmin() || count($matchingRoles) > 0)
                    {
                        $temacesso = true;
                    }

                    if($results_cnpj != null) //há conta com o mesmo CNPJ, mas com uma outra IE cadastrada (pode ser vazia)
                    {
                        $conta = BeanFactory::retrieveBean('Accounts', $results_cnpj['id']); // a tal conta
                        $bean->flag = true;

                        if(!$temacesso)
                        {
                            throw new SugarApiExceptionInvalidParameter("Somente a equipe de Backoffice ou administradores podem cadastrar uma conta com CNPJ já existente, mas IE diferente.");
                        }

                        /*if($conta->im_c != null && $conta->im_c != '')
                        {

                        }*/
                    }
                    else //não há nenhuma conta já existente com o CNPJ inserido; não faz sentido validar a IE
                    {
                    }
                }
            }
        }
    }
	
	// Função copiada de "./custom/modules/Accounts/accounts_sis.php"
	function mask($val, $mask) {
		$maskared = '';
		$k = 0;
		for ($i = 0; $i <= strlen($mask) - 1; $i++) {
			if ($mask[$i] == '#') {
				if (isset($val[$k]))
					$maskared .= $val[$k++];
			} else {
				if (isset($mask[$i]))
					$maskared .= $mask[$i];
			}
		}

		return $maskared;
	}
}