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
	return getHistorial($idProceso);

}

function getHistorial($id){
	include("connect.php");
	include_once("security.php");
	echo $id;
	$idProceso=$id;
	$idPrimeraTarea=getPrimerHijo($id);
	
	
	$sql='SELECT 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA", 
  "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA",
  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
  "SGDP_INSTANCIAS_DE_TAREAS"."D_FECHA_ASIGNACION", 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."D_FECHA_MOVIMIENTO", 
  "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."A_NOMBRE_ACCION", 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."A_COMENTARIO", 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN", 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO"
FROM 
  sgdp."SGDP_INSTANCIAS_DE_TAREAS", 
  sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS", 
  sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS", 
  sgdp."SGDP_TAREAS"
WHERE 
  "SGDP_ACCIONES_HIST_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" AND
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" AND
  "SGDP_TAREAS"."ID_TAREA" = "SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" AND
  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_PROCESO" = '.$idProceso.'AND 
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" != 1
ORDER BY
  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" ASC';		  
	//	  
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
//print_r($sql);
	$idTarea=$row['ID_TAREA'];
	$idInsTarea=$row['ID_INSTANCIA_DE_TAREA_DE_ORIGEN'];
	$nombreTarea=$row['A_NOMBRE_TAREA'];
	$tareaSig=$row['ID_INSTANCIA_DE_TAREA_DESTINO'];
	$comentario=$row['A_COMENTARIO'];
	$nombreProceso='';
	$rol=getRolResponsable($idTarea);
	$documento=getDocRequerido($idTarea);
	$fecha=$row['D_FECHA_MOVIMIENTO'];
	
	$contDoc='';
	if($documento!=''){
		$contDoc='<tr>
					<td>Documentos:</td>
					<td>'.$documento.'</td>
				</tr>';
	}
	
	$raiz='<h1>'.$nombreProceso.'</h1><div class="mindmap">
			<div class="node node_root">
				<div class="node__text" >'.$nombreTarea.'</br>
				<span class="style4" style="line-height: 10px;">
					 <table> 
						<tr>
						<td>Responsable:</td>
						<td>'.$rol.'</td>
					  </tr>
					  <tr>
						<td>Documento:</td>
						<td>'.$documento.'</td>
					  </tr>
					  <tr>
						<td>Fecha:</td>
						<td>'.$fecha.'</td>
					  </tr>
					  <tr>
						<td>Acci&oacute;n realizada:</td>
						<td>INICIO</td>
					  </tr>
					   <tr>
						<td>Comentario:</td>
						<td>'.$comentario.'</td>
					  </tr>
					</table>				
				</span>
				</div>
		  </div>';
	
	$cont=pg_num_rows($result);
	if($cont==0){
		return 'sin hijos';
	}
	
	if($tareaSig!=''){
		$raiz.='
		<ol class="children children_rightbranch">';
		
		//$raiz.=getHijos($tareaSig);
	}
	
	for($i=1;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
		$idTarea=$row['ID_TAREA'];
		$idInsTarea=$row['ID_INSTANCIA_DE_TAREA_DE_ORIGEN'];
		$nombreTarea=$row['A_NOMBRE_TAREA'];
		$tareaSig=$row['ID_INSTANCIA_DE_TAREA_DESTINO'];
		$comentario=$row['A_COMENTARIO'];
		echo $i;
		$raiz.='<li class="children__item">
				<div  class="node" ">
					<div class="node__text">
					<table wborder="0">
					  <tr>
						<td colspan="2"><div align="center" class="style3">'.$nombreTarea.'</div></td>
					  </tr>
					  <tr>
						<td>Responsable:</td>
						<td>'.$rol.'</td>
					  </tr>
					  '.$contDoc.'
					  <tr>
						<td>Fecha recepci&oacute;n:</td>
						<td>'.$plazo.'</td>
					  </tr>
					  <tr>
						<td>Acci&oacute;n realizada:</td>
						<td>'.$row['A_NOMBRE_ACCION'].'</td>
					  </tr>
					   <tr>
						<td>Comentario:</td>
						<td>'.$comentario.'</td>
					  </tr>
					  '.$contTipo.'
					</table>
					</div>
				</div></li>
				';
		
		
		
		/*
		
		if (!in_array($stack, $_SESSION['stack']) && $row['A_NOMBRE_ACCION']!='CREA') {		
		
			//$hijosdet.=getHijos($idTareaSiguiente,$row['D_FECHA_MOVIMIENTO'] );
		}else{
			//echo $stack.' / ';
			//echo 'repetido: '.$stack.' '.$idHijo.' - ';
		}
		*/
		
		
	}
	
	$raiz.='</ol>
			</div>';
	
	return $raiz;
}

function getNodo($idTarea){
	/*include("connect.php");
	$sql='';
		  //return $sql;
	//	  
	$result = pg_query($dbconn, $sql);
	
	$cont=pg_num_rows($result);
	if($cont==0){
		return 'sin hijos';
	}
	
	
	
	$row = pg_fetch_array ($result,0);*/

}

function getHijos($idHijo){
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
		  "SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = '.$idHijo.' order by  "SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_HISTORICO_DE_INST_DE_TAREA" ASC';
		  //return $sql;
	//	  
	$result = pg_query($dbconn, $sql);
	
	$cont=pg_num_rows($result);
	if($cont==0){
		return 'sin hijos';
	}
	
	
	
	$row = pg_fetch_array ($result,0);
	
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
	//return $cont;
	//return $sql;
	for($i=0;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
		$stack=$row['ID_HISTORICO_DE_INST_DE_TAREA'];
		
		if (!in_array($stack, $_SESSION['stack']) && $row['A_NOMBRE_ACCION']!='CREA') {
		
			$_SESSION['stack'][]=$stack;
			$idTareaSiguiente=$row['ID_INSTANCIA_DE_TAREA_DESTINO'];
			$hijosdet.=getHijos($idTareaSiguiente,$row['D_FECHA_MOVIMIENTO'] );
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
						<td>'.$rol.'</td>
					  </tr>
					  '.$contDoc.'
					  <tr>
						<td>Fecha recepci&oacute;n:</td>
						<td>'.$plazo.'</td>
					  </tr>
					  <tr>
						<td>Acci&oacute;n realizada:</td>
						<td>'.$row['A_NOMBRE_ACCION'].'</td>
					  </tr>
					   <tr>
						<td>Comentario:</td>
						<td>'.$comentario.'</td>
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
	$sql='SELECT 
		  "SGDP_ROLES"."A_NOMBRE_ROL"
		FROM 
		  sgdp."SGDP_ROLES", 
		  sgdp."SGDP_TAREAS_ROLES"
		WHERE 
		  "SGDP_TAREAS_ROLES"."ID_ROL" = "SGDP_ROLES"."ID_ROL" AND
		  "SGDP_TAREAS_ROLES"."ID_TAREA" =  '.$idTarea.'; 	';
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
		$roles.=$row['A_NOMBRE_ROL'].'</br>';
		
	}
	return $roles;
}



?>