<?php 

function getPerspectiva(){
	include('connect.php');
	
	$sql='SELECT "ID_PERSPECTIVA", "A_NOMBRE_PERSPECTIVA", "A_DESCRIPCION_PERSPECTIVA" FROM sgdp."SGDP_PERSPECTIVAS" order by "A_NOMBRE_PERSPECTIVA" asc;';
	
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al extraer perspectiva';
	}
	$pers=null;
	for($i=0; $i<pg_num_rows($result); $i++){
		$row = pg_fetch_array($result,$i);
		$pers[$i]['id'] =$row['ID_PERSPECTIVA'];
		$pers[$i]['nombre'] =$row['A_NOMBRE_PERSPECTIVA'];
	}
	
	return $pers;
}

function getProceso(){
	include('connect.php');
	
	$sql='SELECT "ID_MACRO_PROCESO", "A_NOMBRE_MACRO_PROCESO", "A_DESCRIPCION_MACRO_PROCESO", "ID_PERSPECTIVA" FROM sgdp."SGDP_MACRO_PROCESOS" order by "A_NOMBRE_MACRO_PROCESO" ASC;';
	
	$result=pg_query($dbconn, $sql);
	echo pg_last_error($dbconn);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		echo "hola";
		// echo $r;
		return 'Error al extraer proceso';
	}
	$proc=null;
	for($i=0; $i<pg_num_rows($result); $i++){
		$row = pg_fetch_array($result,$i);
		$proc[$i]['id'] =$row['ID_MACRO_PROCESO'];
		$proc[$i]['nombre'] =$row['A_NOMBRE_MACRO_PROCESO'];
	}
	
	return $proc;

}

function getDivision(){
	include('connect.php');
	
	$sql='SELECT "ID_UNIDAD", "A_CODIGO_UNIDAD", "A_NOMBRE_COMPLETO_UNIDAD" FROM sgdp."SGDP_UNIDADES" order by "A_NOMBRE_COMPLETO_UNIDAD" asc;';
	
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al extraer proceso';
	}
	$proc=null;
	for($i=0; $i<pg_num_rows($result); $i++){
		$row = pg_fetch_array($result,$i);
		$proc[$i]['id'] =$row['ID_UNIDAD'];
		$proc[$i]['nombre'] =$row['A_NOMBRE_COMPLETO_UNIDAD'];
	}
	
	return $proc;
	
	
}

?>