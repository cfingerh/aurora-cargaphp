<?php 

echo "Descargar archivo resumen SGDOC en el siguiente link:</br></br>";
echo ''; 

?>
<a href="logica/archivo.csv" download="<?php echo 'datos_'.date('Ymd').'_SGDOC.csv'; ?>">Archivo</a>