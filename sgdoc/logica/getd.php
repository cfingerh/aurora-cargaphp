<?php 
	$carpetas='ID expediente;ID padre;Fecha creacion; No Carpeta; Remitente; Tipo; Fecha recepcion bandeja;Materia; Observacion; Dias en bandeja; Fecha Archivado;Archivos adjuntos;';
	$file = fopen("/var/www/html/sgdoc/logica/archivo.csv", "a");
	fwrite($file, $carpetas . PHP_EOL);
	fclose($file);

?>