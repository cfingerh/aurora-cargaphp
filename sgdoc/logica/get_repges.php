<?php
//session_start();

unlink('/var/www/html/sgdoc/logica/pendientes.csv');

$nombreArchivo = date('Ymdhis').'PENDIENTES_CARPETAS_SGDOC.xls';
echo 'Generando archivo</br></br>';


$users=getUsuarios();
$carpetas='Funcionario; ID Carpeta;No. Carpeta; Fecha recepcion bandeja; ; ';
$file = fopen("/var/www/html/sgdoc/logica/pendientes.csv", "a");
fwrite($file, $carpetas . PHP_EOL);
fclose($file);
$k=0;
//foreach($users as $user){
//echo $user[0].' '.$user[1].'</br>';
	
	/*if($k>2){
		break;
	}else{
		$k++;
	}*/
	//getDetalleCarp($user[0], $users);
	
//}
//print_r( $_SESSION['usuarios']);
getDetalleCarp('', $users);


echo '</br></br></br>Archivo generado correctamente</br><a href="pendientes.csv" download="20160101_'.date('Ymd').'_SGDOC_PENDIENTES.csv">Dercargar Archivo</a>';


function getDetalleCarp($idUser, $usuarios){
	
	/*$sql="SELECT
	exp1.id,
	exp1.numero_expediente
 FROM 
	public.expedientes as exp1
WHERE exp1.id_emisor =$idUser AND 
	exp1.archivado IS NULL AND 
	exp1.copia = false AND 
	exp1.numero_expediente IS NOT NULL AND 
	exp1.id_emisor_historico IS NOT NULL
	AND fecha_acuse_recibo > '2016-01-01'
	AND (SELECT	
	count(expedientes.id) 
		 FROM 
			public.expedientes
		WHERE expedientes.id_padre =exp1.id)<1 order by numero_expediente ASC;";
		
	$sql="SELECT 
		 	expedientes.id,
		 	expedientes.numero_expediente,
			expedientes.fecha_creacion,
			expedientes.fecha_despacho_historico,
			expedientes.fecha_acuse_recibo
		 FROM 
  			public.expedientes
		WHERE expedientes.id_emisor =$idUser AND 
			expedientes.archivado IS NULL AND 
			expedientes.copia = false AND 
			expedientes.id_emisor_historico IS NOT NULL AND
expedientes.numero_expediente IS NOT NULL 
AND expedientes.fecha_despacho_historico >= '2016-01-01'
			AND expedientes.id NOT IN (SELECT distinct
				  expedientes.id_padre
				FROM 
				  public.expedientes
				WHERE 
				  expedientes.id_emisor_historico = $idUser OR
				  expedientes.id_emisor_historico = 303 OR
				  expedientes.id_emisor_historico = 750 OR
				  expedientes.id_emisor_historico = 1354);";*/
				  
$sql="SELECT 
		 	expedientes.id,
		 	expedientes.numero_expediente,
			expedientes.fecha_creacion,
			expedientes.fecha_despacho_historico,
			expedientes.fecha_acuse_recibo,
			expedientes.id_emisor
		 FROM 
  			public.expedientes
		WHERE expedientes.id_emisor IN (
			SELECT id
			FROM personas 
			WHERE usuario != 'admin' AND 
			vigente=true
		)
			AND 
			expedientes.archivado IS NULL AND 
			expedientes.copia = false AND 
			expedientes.id_emisor_historico IS NOT NULL AND
expedientes.numero_expediente IS NOT NULL 
AND expedientes.fecha_despacho_historico >= '2016-01-01'
			AND expedientes.id NOT IN (SELECT distinct
				  expedientes.id_padre
				FROM 
				  public.expedientes
				WHERE 
				  expedientes.id_emisor_historico IN (
					SELECT id
					FROM personas 
					WHERE usuario != 'admin' AND
					id != 303 AND 
					id != 750 AND 
					id != 1354 AND 
					vigente=true
				  ) OR
				  expedientes.id_emisor_historico = 303 OR
				  expedientes.id_emisor_historico = 750 OR
				  expedientes.id_emisor_historico = 1354);

";

	//return $sql;
	include("connect.php");
	$result = pg_query($dbconn, $sql);
	$carpetas='';
	for($i=0;$i<pg_num_rows($result);$i++){
		$row = pg_fetch_array ($result,$i);
		$ncarp= $row['numero_expediente'];
		/********busca el detalle de la carpeta*********/
		$sql2="SELECT documentos.id, documentos.materia, documentos.emisor		
				FROM public.documentos,  public.documentos_expedientes, public.expedientes 
				WHERE 
					expedientes.id = documentos_expedientes.id_expediente AND 
				  documentos_expedientes.id_documento = documentos.id AND
				 expedientes.numero_expediente = '$ncarp' ORDER BY id ASC LIMIT 1";
		//$result2 = pg_query($dbconn, $sql2);
		//$row2 = pg_fetch_array ($result2,0);
		$mat= preg_replace("/\r\n+|\r+|\n+|\t+/i", " ",str_replace(';','.',$row2['materia']));
		$fechaCrea=$row['fecha_creacion'];
		$fechaBand=$row['fecha_despacho_historico'];
		if($fechaBand=='') $fechaBand=$row['fecha_despacho'];
		if($fechaCrea=='') $fechaCrea=$row['fecha_acuse_recibo'];
		if($fechaCrea=='') $fechaCrea=$row['fecha_despacho'];
		//$remit=$row2['emisor'];
		
		/****************/
		
		$carpetas= $usuarios[$row['id_emisor']][1].';'.$row['id'].';'.$row['numero_expediente'].';'.$fechaBand.';'.$mat.';'.$remit;
		$file = fopen("/var/www/html/sgdoc/logica/pendientes.csv", "a");
		fwrite($file, $carpetas . PHP_EOL);
		fclose($file);
		
	}
	$result = null;
	pg_close($dbconn);
}

function getTieneHijos($idExp){
	include("connect.php");
	$sql = "SELECT	count(expedientes.id)
		 FROM 
  			public.expedientes
		WHERE expedientes.id_padre =$idExp";
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0 );
	if($row['count']!='0'){
		return true;
	}
	return false;
}

function getUsuarios($idUser){
	include("connect.php");
	$sql = "SELECT id, nombres, apellido_paterno FROM personas WHERE usuario != 'admin' AND vigente=true ORDER BY nombres ASC";
	if($idUser!=''){
		$sql = "SELECT id, nombres, apellido_paterno FROM personas WHERE usuario != 'admin' AND id=$idUser ORDER BY nombres ASC";
	}
	$result = pg_query($dbconn, $sql);
	$i=0;
	$usuarios='';
	while( $row = pg_fetch_array ($result,$i )) {
		$usuarios[$row['id']]= array($row['id'],$row['nombres'].' '.$row['apellido_paterno']);
		$i++;
	}
	//$_SESSION['usuarios']=$usuarios;
	session_write_close();
	return $usuarios;
}

?>