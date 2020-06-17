<?php 


function getHistorial($id){
	include("connect.php");
	include_once("security.php");
	
	$idProceso=$id;
	
	$sql='SELECT 
			  "SGDP_TAREAS"."ID_TAREA", 
			  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
			  "SGDP_PROCESOS"."A_NOMBRE_PROCESO", 
			  "SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA_SIGUIENTE", 
			  "SGDP_TAREAS"."N_DIAS_HABILES_MAX_DURACION"
			FROM 
			  sgdp."SGDP_TAREAS", 
			  sgdp."SGDP_PROCESOS", 
			  sgdp."SGDP_REFERENCIAS_DE_TAREAS"
			WHERE 
			  "SGDP_PROCESOS"."ID_PROCESO" = "SGDP_TAREAS"."ID_PROCESO" AND
			  "SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA" = "SGDP_TAREAS"."ID_TAREA" AND
			  "SGDP_TAREAS"."ID_PROCESO" ='.$idProceso.' AND 
			  "SGDP_TAREAS"."N_ORDEN" = 1 	  
			ORDER BY
			  "SGDP_TAREAS"."ID_TAREA" ASC;';
	
	/*
	$sql2='SELECT 
		  "SGDP_TAREAS"."ID_TAREA", 
		  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
		  "SGDP_TAREAS"."ID_PROCESO", 
		  "SGDP_TAREAS"."N_DIAS_HABILES_MAX_DURACION", 
		  "SGDP_TAREAS"."B_OBLIGATORIA", 
		  "SGDP_TAREAS"."B_PUEDE_APLICAR_FEA", 
		  "SGDP_TAREAS"."B_PUEDE_VISAR_DOCUMENTOS", 
		  "SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA_SIGUIENTE"
		FROM 
		  sgdp."SGDP_TAREAS", 
		  sgdp."SGDP_REFERENCIAS_DE_TAREAS"
		WHERE 
		  "SGDP_TAREAS"."ID_TAREA" = "SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA" AND
		  "SGDP_TAREAS"."ID_PROCESO" = '.$idProceso.' AND 
		  "SGDP_TAREAS"."N_ORDEN" = 1 	  
		ORDER BY
		  "SGDP_TAREAS"."ID_TAREA" ;';*/
		  
	//	  
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	//print_r($row);
	//exit;
	$idTarea=$row['ID_TAREA'];
	$nombreTarea=$row['A_NOMBRE_TAREA'];
	$tareaSig=$row['ID_TAREA_SIGUIENTE'];
	$nombreProceso=$row['A_NOMBRE_PROCESO'];
	$rol=getRolResponsable($idTarea);
	$documento=getDocRequerido($idTarea);
	$plazo=$row['N_DIAS_HABILES_MAX_DURACION'];
	
	if($idTarea==''){
		return 'Sin datos';
	}
	//print_r($row);
	//exit;
	//$idCarpeta='29242';
	$raiz='<h1>'.$nombreProceso.'</h1><div class="mindmap">
			<div class="node node_root">
				<div class="node__text" onclick="verDetalle('.$idTarea.')">'
				.$nombreTarea.'</br>
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
						<td>Plazo:</td>
						<td>'.$plazo.' d&iacute;as</td>
					  </tr>
					</table>				
				</span>
				</div>
		  </div>';
	$raiz.='
	<ol class="children children_rightbranch">';
	$raiz.=getHijos($tareaSig);
	$raiz.='</ol>
			</div>';
	/*$file = fopen("malla.txt", "w");
	fwrite($file, $raiz);
	fclose($file);*/
	return $raiz;
}

function getHijos($idTarea){
	if($idTarea==''){
		return '';
	}
	
	include("connect.php");
	$sql='SELECT 
		  "SGDP_TAREAS"."ID_TAREA", 
		  "SGDP_TAREAS"."A_NOMBRE_TAREA", 
		  "SGDP_TAREAS"."ID_PROCESO", 
		  "SGDP_TAREAS"."N_DIAS_HABILES_MAX_DURACION", 
		  "SGDP_TAREAS"."B_OBLIGATORIA", 
		  "SGDP_TAREAS"."B_PUEDE_APLICAR_FEA", 
		  "SGDP_TAREAS"."B_PUEDE_VISAR_DOCUMENTOS"
		FROM 
		 "SGDP_TAREAS"
		WHERE 
		  "SGDP_TAREAS"."ID_TAREA" = '.$idTarea.'; 	';
		  //return $sql;
	//	  
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	
	$nombreTarea=$row['A_NOMBRE_TAREA'];
	$rol=getRolResponsable($idTarea);
	$documento=getDocRequerido($idTarea);
	$plazo=$row['N_DIAS_HABILES_MAX_DURACION'];
	$bakgr=$tipo=$contTipo='';
	$contDoc='';
	if($documento!=''){
		$contDoc='<tr>
					<td>Documentos:</td>
					<td>'.$documento.'</td>
				</tr>';
	}
	
	if($row['B_PUEDE_APLICAR_FEA']=='t'){
		$bakgr='style="border-color: #8DDA69; border-width: 2px;';
		$tipo='FIRMA AVANZADA ';
	}
	if($row['B_PUEDE_VISAR_DOCUMENTOS']=='t'){
		$bakgr='style="border-color: #C2E2EF; border-width: 2px;';
		
		$tipo.='VISACI&Oacute;N';
	}
	
		
	$sql='select 
	t."ID_TAREA" as id_tarea
	, t."A_NOMBRE_TAREA" as nombre_tarea
	, r."ID_TAREA_SIGUIENTE" as id_tarea_siguiente  
	from sgdp."SGDP_REFERENCIAS_DE_TAREAS" r
				right join sgdp."SGDP_TAREAS" t on r."ID_TAREA" = t."ID_TAREA"
	where t."N_ORDEN" != 1 AND t."ID_TAREA" = '.$idTarea;
	//return $sql;
	$result = pg_query($dbconn, $sql);
	$i=0;	
	$cont=pg_num_rows($result);
	
	$hijos='<ol class="children">';
	$hijosdet='';
	for($i=0;$i<$cont;$i++){
		$row = pg_fetch_array ($result,$i);
	//while( $row = pg_fetch_array ($result,$i )) {
		$idTareaSiguiente=$row['id_tarea_siguiente'];
		$hijosdet.=getHijos($idTareaSiguiente);
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
						<td>Plazo:</td>
						<td>'.$plazo.' d&iacute;as</td>
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