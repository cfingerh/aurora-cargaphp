<?php 

$file = fopen("file.txt", "r") or exit("Unable to open file!");
while(!feof($file)){
	
	$linea = fgets($file);
	$aux = explode(';', $linea);
	$sql='INSERT INTO sgdp."SGDP_AUTORES"("A_NOMBRE_AUTOR") VALUES ( \''.utf8_decode(utf8_encode(trim($aux[2]))).'\');</br>';
	echo $sql;
}


?>