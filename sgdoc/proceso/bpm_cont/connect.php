<?php
// connect.php 

$host = "172.16.10.61";
$data = "alfresco";
$user = "alfresco"; //usuario de postgres
$pass = "S4cc84cJ"; //password de usuario de postgres

$conn_string = "host=".$host." dbname=".$data." user=".$user." password=".$pass;

$dbconn = pg_connect($conn_string);
// validar la conexión
if(!$dbconn) {
    echo "Error al conectar a la Base de datos\n";
	$cuerpo="[ERROR][".date('Y-m-d H:i:s')."]: No ha sido posible conectarse a la Base de datos de SGDP. Por favor, verificar el sistema para garantizar su continuidad operativa.";	
	$destin= 'fmolins'; 
	//$cuerpo = "El usuario ha enviado el programa de trabajo de fiscalización ID $idFisc, con el resultado de su ejecución para su revisión.";
	$asunto = "[SGDP][ERROR] Error de conexion BD";
	$headers = "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: text/plain\n";
	//dirección del remitente
	$headers .= "From: SGDP <sgdp@scj.gob.cl\r\n";
	$destinatario = $destin."@scj.gob.cl";
	mail($destinatario,$asunto,$cuerpo,$headers);
	$file = fopen("../LOG_SGDP.txt", "a");
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
    exit;
}else{
	$file = fopen("../LOG_SGDP.txt", "a");
	$cuerpo="[CONN][".date('Y-m-d H:i:s')."]: Conexion correcta a BD SGDP  ";
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
    //exit;
}

?>
