<?php
session_start();

if(isset($_POST['txtExp'])){
	//$_SESSION['resultado']=saveUsers($_POST['asignados'], $_POST['rolProc']);
	echo getInfo($_POST['txtExp']);
	//header("Location: ../asig_user.php");
	exit;
	
}

function getInfo($_expediente){
	include('connect.php');
	$expediente= pg_escape_string($_expediente);
	
	$sql='SELECT 
			   "SGDP_PROCESOS"."A_NOMBRE_PROCESO", 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_INICIO", 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."D_FECHA_FIN", 
			  "SGDP_UNIDADES"."A_CODIGO_UNIDAD", 
			  "SGDP_ESTADOS_DE_PROCESOS"."A_NOMBRE_ESTADO_DE_PROCESO", 
			  "SGDP_PROCESOS"."A_CODIGO_PROCESO", 
			  "SGDP_PROCESOS"."B_VIGENTE", 
			  "SGDP_PROCESOS"."ID_PROCESO"
			FROM 
			  sgdp."SGDP_INSTANCIAS_DE_PROCESOS", 
			  sgdp."SGDP_PROCESOS", 
			  sgdp."SGDP_UNIDADES", 
			  sgdp."SGDP_ESTADOS_DE_PROCESOS"
			WHERE 
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = "SGDP_PROCESOS"."ID_PROCESO" AND
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" = "SGDP_ESTADOS_DE_PROCESOS"."ID_ESTADO_DE_PROCESO" AND
			  "SGDP_PROCESOS"."ID_UNIDAD" = "SGDP_UNIDADES"."ID_UNIDAD" AND
			  "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE" = \''.$expediente.'\';
			';
	$result=pg_query($dbconn, $sql);
	$row = pg_fetch_array($result, 0);
	
	$vigente=' (Vigente)';
	if($row['B_VIGENTE']=='f'){
		$vigente=' (No vigente)';
	}
	$finalizado=null;
	if($row['D_FECHA_FIN']!=''){
		$finalizado='<tr>
				<td><span style="font-weight: bold">Fecha fin expediente:</span></td>
				<td>'.$row['D_FECHA_FIN'].'</td>
			</tr>';
	}
	$estadoProc=$row['A_NOMBRE_ESTADO_DE_PROCESO'];
	if($estadoProc=='ASIGNADO'){
		$estadoProc='EN CURSO';
	}
	
	
	
	$info='<h3>Informaci&oacute;n del expediente '.$expediente.'</h3>
			<table border="1">
			<tr>
				<td width="220"><span style="font-weight: bold">ID Mapa de Procesos:</span></td>
				<td>'.$row['A_CODIGO_PROCESO'].'</td>
			</tr>
			<tr>
				<td width="220"><span style="font-weight: bold">ID Subproceso:</span></td>
				<td>'.$row['ID_PROCESO'].$vigente.'</td>
			</tr>
			<tr>
				<td width="220"><span style="font-weight: bold">Subproceso:</span></td>
				<td>'.$row['A_NOMBRE_PROCESO'].'</td>
			</tr>
			<tr>
				<td><span style="font-weight: bold">Divisi&oacute;n/Unidad Responsable:</span></td>
				<td>'.$row['A_CODIGO_UNIDAD'].'</td>
			</tr>
			<tr>
				<td><span style="font-weight: bold">Fecha creaci&oacute;n expediente:</span></td>
				<td>'.$row['D_FECHA_INICIO'].'</td>
			</tr>
			'.$finalizado.'
			<tr>
				<td><span style="font-weight: bold">Estado proceso:</span></td>
				<td>'.$estadoProc.'</td>
			</tr>
			</table>';
	
	$info.='<br><a href="http://sgdocb/proceso/bpm/this_task.php?nomExp='.$expediente.'" target="_blank">Ver diagrama expediente '.$expediente.'</a>
	<br>
	<br><h3>Detalle de roles del subproceso</h3>
		<table border="1">
			<tr>
				<th>Rol</th>
				<th>Divisi&oacute;n/Unidad Usuario</th>
				<th>Usuario</th>
			</tr>';
	$sql='SELECT distinct
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD", 
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO", 
			  "SGDP_UNIDADES"."A_CODIGO_UNIDAD",
			  "SGDP_USUARIOS_ROLES"."B_FUERA_DE_OFICINA"
			FROM 
			  sgdp."SGDP_RESPONSABILIDAD", 
			  sgdp."SGDP_PROCESOS", 
			  sgdp."SGDP_RESPONSABILIDAD_TAREA", 
			  sgdp."SGDP_TAREAS", 
			  sgdp."SGDP_USUARIO_RESPONSABILIDAD", 
			  sgdp."SGDP_USUARIOS_ROLES", 
			  sgdp."SGDP_UNIDADES",
			  sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
			WHERE 
			  "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD_TAREA"."ID_RESPONSABILIDAD" AND
			  "SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" = "SGDP_PROCESOS"."ID_PROCESO" AND
			  "SGDP_TAREAS"."ID_PROCESO" = "SGDP_PROCESOS"."ID_PROCESO" AND
			  "SGDP_TAREAS"."ID_TAREA" = "SGDP_RESPONSABILIDAD_TAREA"."ID_TAREA" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" = "SGDP_USUARIOS_ROLES"."ID_USUARIO" AND
			  "SGDP_USUARIOS_ROLES"."ID_UNIDAD" = "SGDP_UNIDADES"."ID_UNIDAD" AND
			   "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE" = \''.$expediente.'\'
			ORDER BY
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD" ASC,
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" ASC ;
		
		';

		$result=pg_query($dbconn, $sql);
		
		for($i=0;$i<pg_num_rows($result);$i++){	
			$vigente='S&iacute;';
			$fueraOf=$fondo=null;
			$row = pg_fetch_array($result, $i);
			$info.='<tr>';
			if($row['B_VIGENTE']=='f'){
				$vigente='No';
			}
			if($row['B_FUERA_DE_OFICINA']=='t'){
				$fondo=' bgcolor="#ffd100"';
				$fueraOf=' (Fuera de Oficina)';
			}
	
			//$info.='<td>'.$row['A_CODIGO_PROCESO'].'</td>';
			//$info.='<td>'.$row['ID_PROCESO'].'</td>';
			//$info.='<td>'.$vigente.'</td>';
			//$info.='<td>'.$row['A_NOMBRE_PROCESO'].'</td>';
			$info.='<td>'.$row['A_NOMBRE_RESPONSABILIDAD'].'</td>';
			$info.='<td>'.$row['A_CODIGO_UNIDAD'].'</td>';
			$info.='<td '.$fondo.'>'.$row['ID_USUARIO'].$fueraOf.'</td>';
			$info.='</tr>';
			/*$roles.='<td><select name="rolAsig[]" id="rolAsig[]" style="width:250px" require>
          				'.$usuarios.'<input name="saveUser'.$row['ID_RESPONSABILIDAD'].'" type="button" value="Asignar"/></td>';
			$roles.='<td></td>';	*/
			/*$roles.='<td>'..'</td>';
			$roles.='<td>'..'</td>';*/
		}
		return $info.'</table>';
}

?>