<?php
// connect.php 
/*
$data = "sgdoc";
$user = "sgdoc"; //usuario de postgres
$pass = "21382138"; //password de usuario de postgres
//test
//$conn_string = "host=172.16.10.240 dbname=".$data." user=".$user." password=".$pass;

//prod
$conn_string = "host=scjpsqlprod.supercasino.cl dbname=".$data." user=".$user." password=".$pass;*/

//nueva conexion EDB
$host="scjedb.supercasino.cl";
$port='5444';
$data = "sgdoc";
$user = "usuario_postgres"; //usuario de postgres
$pass = "postgresSCJ"; //password de usuario de postgres


$conn_string = "host=$host port=$port dbname=".$data." user=".$user." password=".$pass;/**/

$dbconn = pg_connect($conn_string);
// validar la conexi�n
if(!$dbconn) {
    echo "Error al conectar a la Base de datos\n";
	$cuerpo="[ERROR][scjedb.supercasino.cl][".date('Y-m-d H:i:s')."]: No ha sido posible conectarse a la Base de datos de SGDOC del buscador. Por favor, verificar el sistema para garantizar su continuidad operativa.";	
	$destin= 'fmolins';
	
	$asunto = "[SGDOC][BUSCADOR][ERROR] Error de conexion BD";
	$headers = "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: text/plain\n";
	//direcci�n del remitente
	$headers .= "From: SGDOC <sgdoc@scj.gob.cl\r\n";
	$destinatario = $destin."@scj.gob.cl";
	mail($destinatario,$asunto,$cuerpo,$headers);
	$file = fopen("../LOG_SGDOC.txt", "a");
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
    exit;
}else{
	/*$file = fopen("../LOG_SGDOC.txt", "a");
	$cuerpo="[CONN][".date('Y-m-d H:i:s')."]: Conexion correcta a BD SGDOC por usuario ";
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);*/
    //exit;
}

?>
