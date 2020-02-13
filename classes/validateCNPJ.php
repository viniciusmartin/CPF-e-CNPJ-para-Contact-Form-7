<?php

defined('ABSPATH') or die();

function cf7vm_validate_cnpj($cnpj = null) {

	if( empty($cnpj) ){
		return false;
	}

	if ($cnpj == '00.000.000/0000-00' || $cnpj == '11.111.111/1111-11' || $cnpj == '22.222.222/2222-22' || $cnpj == '33.333.333/3333-33' || $cnpj == '44.444.444/4444-44' || $cnpj == '55.555.555/5555-55' || $cnpj == '66.666.666/6666-66' || $cnpj == '77.777.777/7777-77' || $cnpj == '88.888.888/8888-88' || $cnpj == '99.999.999/9999-99') {
		return false;
	}

	$cnpj = preg_replace( '/[^0-9]/', '', $cnpj );
	
	$cnpj = (string)$cnpj;
	
	$cnpj_original = $cnpj;
	
	$primeiros_numeros_cnpj = substr( $cnpj, 0, 12 );

	if ( ! function_exists('multiplica_cnpj') ) {
		function multiplica_cnpj( $cnpj, $posicao = 5 ) {

			$calculo = 0;
			
			for ( $i = 0; $i < strlen( $cnpj ); $i++ ) {

				$calculo = $calculo + ( $cnpj[$i] * $posicao );
				
				$posicao--;

				if ( $posicao < 2 ) {
					$posicao = 9;
				}
			}

			return $calculo;
		}
	}

	$primeiro_calculo = multiplica_cnpj( $primeiros_numeros_cnpj );

	$primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 :  11 - ( $primeiro_calculo % 11 );
	
	$primeiros_numeros_cnpj .= $primeiro_digito;

	$segundo_calculo = multiplica_cnpj( $primeiros_numeros_cnpj, 6 );
	$segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 :  11 - ( $segundo_calculo % 11 );

	$cnpj = $primeiros_numeros_cnpj . $segundo_digito;

	if ( $cnpj === $cnpj_original ) {
		return true;
	}

}
