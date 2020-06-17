<?php 
session_start();
function getHistoriaExe($idTareaExe){
	include("connect.php");
	$sql='SELECT 
		  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO",
		  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"
		FROM 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" =  '.$idTareaExe;
		  
	//	  
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	//print_r($row);
	//exit;
	unset($_SESSION['stack']);
	$idProceso=$row['ID_INSTANCIA_DE_PROCESO'];
	//echo $idProceso;
	if($idProceso==''){
		return 'Sin datos';
	}
	return getHistorial($idProceso);

}

function getHistorial($id){
	include("connect.php");
	include_once("security.php");

	$idProceso=$id;
	$idPrimeraTarea=getPrimerHijo($id);
	
	
	$sql='SELECT 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA", 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_USUARIO_QUE_ASIGNA", 
		  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO", 
		  "SGDP_ESTADOS_DE_TAREAS"."A_NOMBRE_ESTADO_DE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA", 
		  "SGDP_TAREAS"."ID_TAREA", 
  			"SGDP_HISTORICO_DE_INST_DE_TAREAS"."A_COMENTARIO",
			 "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA"
		FROM 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS", 
		  sgdp."SGDP_ESTADOS_DE_TAREAS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_ESTADO_DE_TAREA" = "SGDP_ESTADOS_DE_TAREAS"."ID_ESTADO_DE_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = '.$idPrimeraTarea.' AND 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" != 1';		  
	//	  
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	//print_r($row);
	//exit;
	$idTarea=$row['ID_TAREA'];
	$idInsTarea=$row['ID_INSTANCIA_DE_TAREA'];
	$nombreTarea=$row['A_NOMBRE_TAREA'];
	$tareaSig=$row['ID_INSTANCIA_DE_TAREA_DESTINO'];
	$comentario=$row['A_COMENTARIO'];
	$nombreProceso='';
	$rol=getRolResponsable($idTarea);
	$documento=getDocRequerido($idTarea);
	$fecha=$row['D_FECHA_MOVIMIENTO'];
	$_SESSION['stack'][]=$row['ID_HISTORICO_DE_INST_DE_TAREA'];
	//print_r($row);
	//exit;
	//$idCarpeta='29242';
	$raiz='<h1>'.$nombreProceso.'</h1><div class="mindmap">
			<div class="node node_root">
				<div class="node__text " ALIGN=center><span style="white-space: pre-wrap;">'.$nombreTarea.'</span></br>
				<span class="style4" style="line-height: 10px;">
					 <table> 
						<tr>
						<td>Responsable:</td>
						<td>'.$rol.'</td>
					  </tr>
					  <tr>
						<td>Documento:</td>
						<td style="white-space: pre-wrap">'.$documento.'</td>
					  </tr>
					  <tr>
						<td>Fecha:</td>
						<td style="white-space: pre-wrap">'.$fecha.'</td>
					  </tr>
					  <tr>
						<td>Acci&oacute;n realizada:</td>
						<td>INICIO</td>
					  </tr>
					   <tr>
						<td>Comentario:</td>
						<td style="white-space: pre-wrap">'.$comentario.'</td>
					  </tr>
					</table>				
				</span>
				</div>
		  </div>';
		  
	if($tareaSig!=''){
		$raiz.='
		<ol class="children children_rightbranch">';
		
		$raiz.=getHijos($tareaSig, $fecha, $row['ID_HISTORICO_DE_INST_DE_TAREA']);
	}
	
	$raiz.='</ol>
			</div>';
	/*$file = fopen("malla.txt", "w");
	fwrite($file, $raiz);
	fclose($file);*/
	//print_r($_SESSION['stack']);
	
	return $raiz;
}

function getHijos($idHijo, $fecha, $idHisto){
	if($idHijo==''){
		return '';
	}
	
	$_SESSION['cont']++;
	
	include("connect.php");
	$sql='SELECT 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN", 
		  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO", 
		  "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."A_NOMBRE_ACCION", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."A_COMENTARIO", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN", 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
		FROM 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS", 
		  sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = '.$idHijo.' AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" > '.$idHisto.' 
		  order by  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" ASC';
		  //print_r( $sql);
	
	$result = pg_query($dbconn, $sql);
	
	$cont=pg_num_rows($result);
	if($cont==0){
		//return 'sin hijos';
		$sql='SELECT 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN", 
		  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO", 
		  "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."A_NOMBRE_ACCION", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."A_COMENTARIO", 
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_USUARIO_ORIGEN", 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
		FROM 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS", 
		  sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
		  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = '.$idHijo.'
		  order by  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" ASC';	
		$result = pg_query($dbconn, $sql);
	}
	
	
	$row = pg_fetch_array ($result,0);
	
	//$idHijo=$row['ID_TAREA'];
	$idInsTarea=$row['ID_INSTANCIA_DE_TAREA'];
	$nombreTarea=$row['A_NOMBRE_TAREA'];
	$rol=getRolResponsable($idHijo);
	$documento=getDocRequerido($idHijo);
	$plazo=$fecha;
	$comentario=$row['A_COMENTARIO'];
	
	$bakgr=$tipo=$contTipo='';
	$contDoc='';
	
	if($documento!=''){
		$contDoc='<tr>
					<td>Documentos:</td>
					<td>'.$documento.'</td>
				</tr>';
	}
	
	$hijos='<ol class="children">';
	$hijosdet='';

	for($i=0;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
		$stack=$row['ID_HISTORICO_DE_INST_DE_TAREA'];
		
		if (!in_array($stack, $_SESSION['stack']) && $row['A_NOMBRE_ACCION']!='CREA') {
		
			$_SESSION['stack'][]=$stack;
			$idTareaSiguiente=$row['ID_INSTANCIA_DE_TAREA_DESTINO'];
			$hijosdet.=getHijos($idTareaSiguiente,$row['D_FECHA_MOVIMIENTO'], $row['ID_HISTORICO_DE_INST_DE_TAREA'] );
		}else{
			//echo $stack.' / ';
			//echo 'repetido: '.$stack.' '.$idHijo.' - ';
		}
		
		
		
	}
	if($hijosdet==''){
		$hijos='';
		$bakgr='style="background-color: #C2E2EF; ';
		$tipo.=' TAREA FINAL';
	}else{
		$hijos.=$hijosdet.'	</ol>';
	}
	if($tipo!=''){
		$contTipo='<tr>
					<td>tipo:</td>
					<td>'.$tipo.'</td>
				</tr>';
	}
	$bakgr.='"';
		
	$nodo='<li class="children__item">
				<div  class="node"  '.$bakgr.'">
					<div class="node__text">
					<table wborder="0">
					  <tr>
						<td colspan="2"><div align="center" class="style3">'.$nombreTarea.'</div></td>
					  </tr>
					  <tr>
						<td>Responsable:</td>
						<td style="white-space: pre-wrap">'.$rol.'</td>
					  </tr>
					  '.$contDoc.'
					  <tr>
						<td>Fecha recepci&oacute;n:</td>
						<td style="white-space: pre-wrap">'.$plazo.'</td>
					  </tr>
					  <tr>
						<td>Acci&oacute;n realizada:</td>
						<td style="white-space: pre-wrap">'.(($row['A_NOMBRE_ACCION']!='CREA')?$row['A_NOMBRE_ACCION']:'ASIGNADA').'</td>
					  </tr>
					   <tr>
						<td>Comentario:</td>
						<td style="white-space: pre-wrap">'.$comentario.'</td>
					  </tr>
					  '.$contTipo.'
					</table>
					</div>
				</div>
				';
	
	$nodo.=$hijos.'
	</li>';
	unset($result);	
	return $nodo;
}

function getPrimerHijo($idProceso){
	include("connect.php");
	$sql='SELECT 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA"
		FROM 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_INSTANCIAS_DE_TAREAS"
		WHERE 
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" =  '.$idProceso.' AND 
		  "SGDP_TAREAS"."N_ORDEN" = 1;	';
		  //return $sql;
	//	  
	$documentos='';
	$result = pg_query($dbconn, $sql);
	if(!$result){
		return -1;
	}
	
	$row = pg_fetch_array ($result,0);
	return $row['ID_INSTANCIA_DE_TAREA'];
	
}

function getDocRequerido($idTarea){
	include("connect.php");
	$sql='SELECT 
		  "SGDP_TIPOS_DE_DOCUMENTOS"."A_NOMBRE_DE_TIPO_DE_DOCUMENTO"
		FROM 
		  sgdp."SGDP_TIPOS_DE_DOCUMENTOS", 
		  sgdp."SGDP_DOCUMENTOS_DE_SALIDA_DE_TAREAS"
		WHERE 
		  "SGDP_DOCUMENTOS_DE_SALIDA_DE_TAREAS"."ID_TIPO_DE_DOCUMENTO" = "SGDP_TIPOS_DE_DOCUMENTOS"."ID_TIPO_DE_DOCUMENTO" AND
		  "SGDP_DOCUMENTOS_DE_SALIDA_DE_TAREAS"."ID_TAREA" =  '.$idTarea.'; 	';
		  //return $sql;
	//	  
	$documentos='';
	$result = pg_query($dbconn, $sql);
	if(!$result){
		return;
	}
	$cont=pg_num_rows($result);
	
	for($i=0;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
		$documentos.=$row['A_NOMBRE_DE_TIPO_DE_DOCUMENTO'].'</br>';
		
	}
	return $documentos;
}

function getRolResponsable($idTarea){
	include("connect.php");
	$sql='SELECT "ID_INSTANCIA_DE_TAREA_DE_ORIGEN", 
       "D_FECHA_MOVIMIENTO", "ID_ACCION_HISTORICO_INST_DE_TAREA", "ID_USUARIO_ORIGEN", 
       "ID_INSTANCIA_DE_TAREA_DESTINO", "A_COMENTARIO"
  FROM sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"
		WHERE 
		  "SGDP_RESPONSABILIDAD_TAREA"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" AND
		  "SGDP_RESPONSABILIDAD_TAREA"."ID_TAREA" ='.$idTarea.'; 	';
		  //return $sql;
	//	  
	$roles='';
	$result = pg_query($dbconn, $sql);
	if(!$result){
		return;
	}
	$cont=pg_num_rows($result);
	
	for($i=0;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
		$roles.=$row['A_NOMBRE_RESPONSABILIDAD'].'</br>';
		
	}
	return $roles;
}



?>