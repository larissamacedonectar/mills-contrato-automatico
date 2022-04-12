<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
// classe para validar dados quando necessário

class validadorDados
{    
    function semCaracterEspecial($dado) {

        return array(
            'status' => true,
            'resultado' => preg_replace('/[^a-zA-Z0-9 ]/', '', $dado )
        );
        
    }

    function mascara($valor, $mascara){

        $valorComMascara = '';
        $k = 0;

        for($i = 0; $i<=strlen($mascara)-1; $i++)
        {

            if($mascara[$i] == '#')
            {
                if(isset($valor[$k]))
                $valorComMascara .= $valor[$k++];
            }
            else
            {
            if(isset($mascara[$i]))
                $valorComMascara .= $mascara[$i];
            }
        }

        return $valorComMascara;

    }

    function cnpjValida($cnpj)
    {
        //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $j=0;
        for($i=0; $i<(strlen($cnpj)); $i++)
            {
                if(is_numeric($cnpj[$i]))
                    {
                        $num[$j]=$cnpj[$i];
                        $j++;
                    }
            }
        //Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
        if(count($num)!=14)
            {
                return array(
                    'status' => false,
                    'resultado' => "CNPJ Inválido - Diferente de 14 dígitos"
                );
            }
        //Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria um cnpj válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 && $num[8]==0 && $num[9]==0 && $num[10]==0 && $num[11]==0)
            {
                return array(
                    'status' => false,
                    'resultado' => "CNPJ Inválido - Igual à 00000000000"
                );
            }
        //Etapa 4: Calcula e compara o primeiro dígito verificador.
        else
            {
                $j=5;
                for($i=0; $i<4; $i++)
                    {
                        $multiplica[$i]=$num[$i]*$j;
                        $j--;
                    }
                $soma = array_sum($multiplica);
                $j=9;
                for($i=4; $i<12; $i++)
                    {
                        $multiplica[$i]=$num[$i]*$j;
                        $j--;
                    }
                $soma = array_sum($multiplica);	
                $resto = $soma%11;			
                if($resto<2)
                    {
                        $dg=0;
                    }
                else
                    {
                        $dg=11-$resto;
                    }
                if($dg!=$num[12])
                    {
                        $cnpjValido=false;
                    } 
            }
        //Etapa 5: Calcula e compara o segundo dígito verificador.
        if(!isset($cnpjValido))
            {
                $j=6;
                for($i=0; $i<5; $i++)
                    {
                        $multiplica[$i]=$num[$i]*$j;
                        $j--;
                    }
                $soma = array_sum($multiplica);
                $j=9;
                for($i=5; $i<13; $i++)
                    {
                        $multiplica[$i]=$num[$i]*$j;
                        $j--;
                    }
                $soma = array_sum($multiplica);	
                $resto = $soma%11;			
                if($resto<2)
                    {
                        $dg=0;
                    }
                else
                    {
                        $dg=11-$resto;
                    }
                if($dg!=$num[13])
                    {
                        return array(
                            'status' => false,
                            'resultado' => "CNPJ Inválido"
                        );
                    }
                else
                    {

                        $res = preg_replace('/[^\p{L}\p{N}\s]/', '', $cnpj );

                        return array(
                            'status' => true,
                            'resultado' => $this->mascara($res, "##.###.###/####-##") 
                        );
                    }
            }

            return $cnpjValido;			
    }

    function cepValida($cep)
    {
        //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cep em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $j=0;
        for($i=0; $i<(strlen($cep)); $i++)
            {
                if(is_numeric($cep[$i]))
                    {
                        $num[$j]=$cep[$i];
                        $j++;
                    }
            }
        //Etapa 2: Conta os dígitos, um cep válido possui 8 dígitos numéricos.
        if(count($num)!=8)
            {
                return array(
                    'status' => false,
                    'resultado' => "CEP Inválido - Diferente de 8 dígitos"
                );
            }


            //Etapa 3: O número 00000000 embora não seja um cep real resultaria um cep válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 )
        {
            return array(
                'status' => false,
                'resultado' => "CEP Inválido - Igual à 00000000"
            );
        }
            //Etapa 4: Aplica a máscara
            else
            {
                $res = preg_replace('/[^\p{L}\p{N}\s]/', '', $cep );

                return array(
                    'status' => true,
                    'resultado' => $this->mascara($res, "#####-###")
                );
            }
    }

    function telefoneValida($telefone)
    {

        //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o telefone em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
        $j=0;
        for($i=0; $i<(strlen($telefone)); $i++)
            {
                if(is_numeric($telefone[$i]))
                    {
                        $num[$j]=$telefone[$i];
                        $j++;
                    }
            }

        //Etapa 2: Conta os dígitos, um telefone válido possui 8 dígitos numéricos.
        if(count($num) != 10 && count($num) != 11)
            {
                return array(
                    'status' => false,
                    'resultado' => "Telefone Inválido - Diferente de 10 ou 11 dígitos"
                );
            }


            //Etapa 3: O número 0000000000 embora não seja um telefone real resultaria um cep válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
        if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 && $num[8]==0 && $num[9]==0 && $num[10]==0 && $num[11]==0 )
        {
            return array(
                'status' => false,
                'resultado' => "Telefone Inválido - Igual à 0000000000"
            );
        }
            //Etapa 4: Aplica a máscara
            else
            {
                $res = preg_replace('/[^\p{L}\p{N}\s]/', '', $telefone );

                if(strlen($res) == 10) {
                    return array(
                        'status' => true,
                        'resultado' => $this->mascara($res, "(##) ####-####")
                    );
                } else if (strlen($res) == 11) {
                    return array(
                        'status' => true,
                        'resultado' => $this->mascara($res, "(##) #####-####")
                    );
                }


            }
    }

    function emailValida($email) 
    {

        $conta = "/[a-zA-Z0-9\._-]+@";
        $domino = "[a-zA-Z0-9\._-]+.";
        $extensao = "([a-zA-Z]{2,4})$/";
        $pattern = $conta.$domino.$extensao;
    
        if (preg_match($pattern, $email))
            return array(
                'status' => true,
                'resultado' => $email
            );
        else
            return array(
                'status' => false,
                'resultado' => "E-mail Inválido"
            );
        }

    }

    function cidadeValida($idCidade){
        $GLOBALS['log']->fatal('Fatal level message 1');

        global $db;
        $GLOBALS['log']->fatal('Fatal level message 2');
        $select = "SELECT count(*) AS ct FROM it4_cidades WHERE id = '$idCidade' AND deleted = 0";
        $GLOBALS['log']->fatal('Fatal level message 3' . $select);
        $res = $db->query($select);
        $GLOBALS['log']->fatal('Fatal level message 4');
        $row = $db->fetchByAssoc($res);
        $GLOBALS['log']->fatal('Fatal level message 5');
        
        if($row["ct"] == 0) {
            return array(
                'status' => false,
                'resultado' => "ID de Cidade não cadastrado."
            );
        }  else {
            return array(
                'status' => true,
                'resultado' => $idCidade
            );
        }

    }

    function estadoValida($idEstado){

        global $db;

        $select = "SELECT count(*) AS ct FROM it4_estados WHERE id = '$idEstado' AND deleted = 0";
        $res = $db->query($select);
        $row = $db->fetchByAssoc($res);
        
        if($row["ct"] == 0) {
            return array(
                'status' => false,
                'resultado' => "ID de Estado não cadastrado."
            );
        }  else {
            return array(
                'status' => true,
                'resultado' => $idEstado
            );
        }

    }

?>