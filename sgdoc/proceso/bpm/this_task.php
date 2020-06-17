<?php 
session_start();
$diagrama=null;
$_SESSION['estadoP']=null;
if(isset($_GET['idInsProc']) || isset($_GET['idTask'])){
	$diagrama=getDiagramInfo();
	//exit;
}

if(isset($_GET['nomExp'])){
	$diagrama=getDiagramExp();
	//exit;
}
	
	
	
	
function getDiagramExp(){
	$tam=false;
	$nomExp=pg_escape_string($_GET['nomExp']);
	if(isset($_GET['t']) && $_GET['t']=='s'){
		$tam=true;
	}
	include('connect.php');
	
	//busca los datos del flujo segun el nombre del expediente
	$sql='SELECT 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO", 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO", 
			  "SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO", 
			  "SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO"
			FROM 
			  sgdp."SGDP_INSTANCIAS_DE_PROCESOS", 
			  sgdp."SGDP_ESTADOS_DE_PROCESOS"
			WHERE 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = "SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" AND
			  "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE" = \''.$nomExp.'\';';
	//echo $sql; return;
	
	$result=pg_query($dbconn, $sql);
	if(pg_num_rows($result)==0){
		return $loadArchivoXml;
	}
	$row = pg_fetch_array($result, 0);	
	$idProc=$row['ID_PROCESO'];
	$idInsProc=$row['ID_INSTANCIA_DE_PROCESO'];
	$idEstadoProc=$row['ID_ESTADO_DE_PROCESO'];
	$estadoProc=$row['A_NOMBRE_ESTADO_DE_PROCESO'];
	//echo $idProc;
	if($idEstadoProc==2){
		$estadoProc='EN CURSO';
	}
	if($estadoProc!=''){
		$_SESSION['estadoP']='ESTADO: '.$estadoProc;
	}
	
	$archivo='diagramas/'.$idProc.'.svg'; //
	$loadArchivoXml = file_get_contents($archivo);
	
	
		
	
	/****busca las tareas pasadas del flujo***/
	$sql='SELECT 
		  "SGDP_TAREAS"."ID_DIAGRAMA", 
		  count( "SGDP_TAREAS"."ID_DIAGRAMA") as veces
		FROM 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = '.$idInsProc.' AND (
"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 3 OR 
"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 4 OR 
"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 6 )
		  group by "SGDP_TAREAS"."ID_DIAGRAMA"
		  order by veces ASC;';
	//echo $sql; return;
	$result=pg_query($dbconn, $sql);
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		//determina posicion de tarea en el esquema
			$pos = strpos($loadArchivoXml, $row['ID_DIAGRAMA']); 
		
		if($row['ID_DIAGRAMA']!=$idTarea && $pos !== false){
			
			//pinta borde de color
			$pos2 = strpos($loadArchivoXml, 'black;',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(247, 220, 111);',$pos2,6);
			
			//pinta relleno de color
			$pos3 = strpos($loadArchivoXml, 'white;',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(247, 220, 111);',$pos3,6);
			
			//pinta relleno de color
			$pos4 = strpos($loadArchivoXml, '</text>',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<tspan> ('.$row['veces'].')</tspan></text>',$pos4,0);
			/*
			$pos5 = strpos($loadArchivoXml, '<text class',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<text onClick=getInfo() ',$pos5,6);*/
			
		}elseif($pos !== false){
			//pinta relleno de color
			$pos4 = strpos($loadArchivoXml, '</text>',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<tspan> ('.$row['veces'].')</tspan></text>',$pos4,0);
			$pos5 = strpos($loadArchivoXml, '<text class',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<text onClick=getInfo() ',$pos5,6);
		}	

	}
	
	/**************FIN BUSCA TAREAS ANTERIORES****************/
	
	
	/***************INICIO BUSCAR TAREAS ACTUALES*******************/
	
	$sql='SELECT 
		  "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE", 
		  "SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_USUARIO", 
		  "SGDP_TAREAS"."ID_DIAGRAMA"
		FROM 
		  sgdp."SGDP_USUARIOS_ASIGNADOS_A_TAREAS", 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
		WHERE 
		  "SGDP_USUARIOS_ASIGNADOS_A_TAREAS"."ID_INSTANCIA_DE_TAREA" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = "SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO" AND
		  "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE" = \''.$nomExp.'\';	';
	
	
	$result=pg_query($dbconn, $sql);
	
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$idTarea=$row['ID_DIAGRAMA'];
		$idUser=$row['ID_USUARIO'];
		//determina posicion de tarea en el esquema
		
		$pos = strpos($loadArchivoXml, $idTarea); 
		
		if($pos !== false){
			//pinta borde de color
			$pos2 = strpos($loadArchivoXml, 'black;',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(67, 160, 71);',$pos2,6);
			//pinta relleno de color
			$pos3 = strpos($loadArchivoXml, 'white;',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(200, 230, 201); ',$pos3,6);
			
			$pos4 = strpos($loadArchivoXml, '</text>',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<tspan id="ah" x="25" dy="10" fill="red" alignment-baseline="text-before-edge" > ('.$idUser.')</tspan></text>',$pos4,0);
			
			/*
			$pos5 = strpos($loadArchivoXml, '<text class',$pos);
			$loadArchivoXml =substr_replace($loadArchivoXml,'<text onClick=getInfo() ',$pos5,6);*/
			
		}
	}
	
	/*********************************************/
		
	
	// Establece el tamaño del diagrama, si se debe ajustar o no al contenedor
	if($tam){
		$loadArchivoXml=preg_replace('/width="[0-9]+"/i', 'width="100%"', $loadArchivoXml, 1);
		$loadArchivoXml=preg_replace('/height="[0-9]+"/i', 'height="100%"', $loadArchivoXml, 1);
	}
	
	$pos = strpos($loadArchivoXml, '<svg'); 
	$loadArchivoXml =substr_replace($loadArchivoXml,'<svg id="svg1"',$pos,5);
	
	
	$browser = getenv("HTTP_USER_AGENT");
	if (preg_match("/MSIE/i", "$browser"))
	{
		return $loadArchivoXml;
		//echo "Por favor utilice otro navegador para ver el diagrama";
	}else{
		return $loadArchivoXml;
	}
}

function getDiagramInfo(){
	$tam=false;
	$idTarea=pg_escape_string($_GET['idTask']);
	$idProc=pg_escape_string($_GET['idProc']);
	
	$idInsProc=pg_escape_string($_GET['idInsProc']);
	if(isset($_GET['t']) && $_GET['t']=='s'){
		$tam=true;
	}
	
	$archivo='diagramas/'.$idProc.'.svg'; //
	$loadArchivoXml = file_get_contents($archivo);
	
	if($idTarea=='' || $idTarea==null){
		return $loadArchivoXml;
	}
	if(isset($_GET['idInsProc'])){
		include('connect.php');
		//busca las tareas pasadas del flujo
		$sql='SELECT 
			  "SGDP_TAREAS"."ID_DIAGRAMA", 
			  count( "SGDP_TAREAS"."ID_DIAGRAMA") as veces
			FROM 
			  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
			  sgdp."SGDP_TAREAS", 
			  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
			WHERE 
			  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
			  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
			  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = '.$idInsProc.' AND (
	"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 3 OR 
	"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 4 OR 
	"SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = 6 )
			  group by "SGDP_TAREAS"."ID_DIAGRAMA"
			  order by veces ASC;';
		//echo $sql; return;
		$result=pg_query($dbconn, $sql);
		
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			//determina posicion de tarea en el esquema
				$pos = strpos($loadArchivoXml, $row['ID_DIAGRAMA']); 
			
			if($row['ID_DIAGRAMA']!=$idTarea && $pos !== false){
				
				//pinta borde de color
				$pos2 = strpos($loadArchivoXml, 'black;',$pos);
				$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(247, 220, 111);',$pos2,6);
				
				//pinta relleno de color
				$pos3 = strpos($loadArchivoXml, 'white;',$pos);
				$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(247, 220, 111);',$pos3,6);
				
				//pinta relleno de color
				$pos4 = strpos($loadArchivoXml, '</text>',$pos);
				$loadArchivoXml =substr_replace($loadArchivoXml,'<tspan > ('.$row['veces'].')</tspan></text>',$pos4,0);
			}elseif($pos !== false){
				//pinta relleno de color
				$pos4 = strpos($loadArchivoXml, '</text>',$pos);
				$loadArchivoXml =substr_replace($loadArchivoXml,'<tspan> ('.$row['veces'].')</tspan></text>',$pos4,0);
			}		
		}
	}
	//determina posicion de tarea en el esquema
	$pos = strpos($loadArchivoXml, $idTarea); 
	
	if($pos !== false){
		//pinta borde de color
		$pos2 = strpos($loadArchivoXml, 'black;',$pos);
		$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(67, 160, 71);',$pos2,6);
		//pinta relleno de color
		$pos3 = strpos($loadArchivoXml, 'white;',$pos);
		$loadArchivoXml =substr_replace($loadArchivoXml,'rgb(200, 230, 201);',$pos3,6);
	}
	
	// Establece el tamaño del diagrama, si se debe ajustar o no al contenedor
	if($tam){
		$loadArchivoXml=preg_replace('/width="[0-9]+"/i', 'width="100%"', $loadArchivoXml, 1);
		$loadArchivoXml=preg_replace('/height="[0-9]+"/i', 'height="100%"', $loadArchivoXml, 1);
	}
	
	$browser = getenv("HTTP_USER_AGENT");
	/*if (preg_match("/MSIE/i", "$browser"))
	{
	
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>

			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			</head>
			 
			<body>
			<div>'.$loadArchivoXml.'</div>
			</body>
			</html>';
		//echo "Por favor utilice otro navegador para ver el diagrama";
	}else{*/
	$pos = strpos($loadArchivoXml, '<svg'); 
	$loadArchivoXml =substr_replace($loadArchivoXml,'<svg id="svg1"',$pos,5);
		return $loadArchivoXml;
	//}

}
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script type="text/javascript" src="jquery/jquery-1.js"></script><!---->

  <script>
  function getInfo(){
/*
  //window.scrollTo(749, 1065);
	
  $( "#cuadroInfo" ).dialog({
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
        }
      }
    });*/
	}

  </script>
  <script type="text/javascript">//<![CDATA[
function goto(){
	<?php
		if(isset($_GET['idTask'])){	
	?>
		$("g[data-element-id='<?php echo $_GET['idTask']?>']")[0].scrollIntoView();
		//window.scrollBy(270,-20);
	<?php		
			}
	?>
	
}

</script>
<style type="text/css" class="init">

	body{
	overflow-x:hiden; 
	font-family: Verdana, Arial, Helvetica, sans-serif;
	}

	</style>

</head>
 
<body onload="goto()">
<h1 align="center"><?php echo $_SESSION['estadoP'] ?></h1>
<div align="center"><?php echo $diagrama ?></div>
<div id="cuadroInfo" title="Info Tarea" style="display:none">
  <p>Sin información (pronto)</p>
</div>
</body>
</html>