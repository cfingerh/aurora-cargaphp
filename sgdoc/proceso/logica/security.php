<?php
session_start();
/***************FUNCIONES DE COMPROBACION DE VARIABLES DE SESION*****************/
function verif_sesion($user_agent, $perfil){
	//comprobacion user-agent
	if(!isset($_SESSION['userAgent']) || $user_agent!=$_SESSION['userAgent']){
		print "<meta http-equiv=Refresh content=\"0 ; url=../logica/exit.php\">";
		exit;
	}
	//comprobacion autenticado
	if(!isset($_SESSION['autenticado']) || $_SESSION['autenticado']!='SI'){
		print "<meta http-equiv=Refresh content=\"0 ; url=../logica/exit.php\">";
		exit;
	}
	
	//comprobacion perfil
	if(!isset($_SESSION['perfil']) || $_SESSION['perfil']!=$perfil){
		print "<meta http-equiv=Refresh content=\"0 ; url=../logica/exit.php\">";
		exit;
	}
	//comprobación de tiempo transcurrido en la sesion
	$timeOnline = $_SERVER['REQUEST_TIME'] - $_SESSION['LastActivity'];
	if($timeOnline > 3600){
		print "<meta http-equiv=Refresh content=\"0 ; url=../logica/exit.php\">";
		exit;
	}
}

function verif_token($token){
	//comprobacion token
	$aux=limpiar_variable($token);
	if(isset($_SESSION['token']) && $_SESSION['token']==$token){
		$_SESSION['LastActivity'] = $_SERVER['REQUEST_TIME'];
		return true;
	}else{
		return false;
	}
	
}

function gen_token(){
	//generacion de token
	$token = sha1 ( uniqid ( rand (), TRUE )); 
	$_SESSION ['token'] = $token ;
	return $token;	
}

/***************FIN FUNCIONES DE COMPROBACION*****************/

/*********FUNCIONES DE VALIDACION*********/
function valida_numero($num){
	if(strlen($num)==0){
		return true;
	}
	if(is_numeric($num)){
		return true;
	}elseif(is_float($num)){
		return true;
	}else{
		return false;
	}
}

function valida_string($string){
	//return true;
	if($string!=''){
		if(is_string($string)){
			return true;
		}else{
			return false;
		}
		
	}else{
		return false;
	}
}

function valida_mail($mail){
	if(filter_var($mail, FILTER_VALIDATE_EMAIL)){
		return true;
	}else{
		return filter_var($mail, FILTER_VALIDATE_EMAIL);
	}
}
/*********FIN VALIDACION*********/

/***************FUNCIONES DE LIMPIEZA DE VARIABLES*****************/
function limpiar_variable($var){
	$aux = trim(esc_SQL($var));
	$aux = esc_HTML($aux);
	$aux = esc_comillas($aux);
	$clean_var = $aux;
	return $clean_var;
}

function esc_comillas($var){
	$aux = addslashes($var);
	return $aux;
}

function esc_HTML($var){
	$aux = strip_tags($var);
	return $aux;
}

function esc_SQL($var){
	$valor = str_ireplace("SELECT","",$var);
	$valor = str_ireplace("COPY","",$valor);
	$valor = str_ireplace("DELETE","",$valor);
	$valor = str_ireplace("DROP","",$valor);
	$valor = str_ireplace("DUMP","",$valor);
	$valor = str_ireplace(" OR ","",$valor);
	$valor = str_ireplace("%","",$valor);
	$valor = str_ireplace("LIKE","",$valor);
	$valor = str_ireplace("--","",$valor);
	$valor = str_ireplace("^","",$valor);
	$valor = str_ireplace("[","",$valor);
	$valor = str_ireplace("]","",$valor);
	$valor = str_ireplace("\\","",$valor);
	//$valor = str_ireplace("!","",$valor);
	$valor = str_ireplace("¡","",$valor);
	//$valor = str_ireplace("?","",$valor);
	$valor = str_ireplace("=","",$valor);
	$valor = str_ireplace("!=","",$valor);
	$valor = str_ireplace("&","",$valor);
	return $valor;
}

function clean_mail($var){
	return filter_var($var, FILTER_SANITIZE_EMAIL);
}

function clean_string($var){
	return filter_var($var, FILTER_SANITIZE_STRING);
}

function clean_num($var){
	return filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT);
}
/***************FIN FUNCIONES DE LIMPIEZA*****************/
?>