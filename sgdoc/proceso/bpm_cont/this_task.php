<?php 
/*$archivo='57 02 Visacion y pago facturas_v1.1.svg'; //81 Difusión de estadísticas operacionales.svg
	$archivo='81 Difusion de estadisticas operacionales.svg'; //
	$archivo='sshi_v1.1_RES.svg'; //*/
	
	$idTarea=$_GET['idTask'];
	$idProc=$_GET['idProc'];
	$tamano=$_GET['t'];
	$archivo='diagramas/'.$idProc.'.svg'; //
	$loadArchivoXml = file_get_contents($archivo);
	
	if($idTarea=='' || $idTarea==null){
		echo $loadArchivoXml;
		return;
	}
	$pos = strpos($loadArchivoXml, $idTarea); 
	
	$pos2 = strpos($loadArchivoXml, 'black;',$pos);
	$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(67, 160, 71);',$pos2,6);
	
	$pos3 = strpos($loadArchivoXml, 'white;',$pos);
	$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(200, 230, 201);',$pos3,6);
	
	if($tamano!='s'){
		$loadArchivoXml=preg_replace('/width="[0-9]+"/i', 'width="100%"', $loadArchivoXml, 1);
		$loadArchivoXml=preg_replace('/height="[0-9]+"/i', 'height="100%"', $loadArchivoXml, 1);
	}
	
	
	echo $loadArchivoXml;
	
?>