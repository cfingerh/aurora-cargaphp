<?php
session_start();


if(isset($_POST['func']) && $_POST['func']=='getRoles'){
	echo getRoles($_POST['user'], null);
	exit;
}

if(isset($_POST['func']) && $_POST['func']=='getRolesAsig'){
	echo getUsersAsig($_POST['rol']);
	exit;
}

if(isset($_POST["btnExpUserRol"])){
	$nombreArchivo = date('Ymdhis').'_roles_usuario.xls';
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=$nombreArchivo");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo utf8_decode(getRoles($_SESSION['idUSer'], null));
		
}

function getUsers($_idUser=null, $_idRol=null){
	$resp=null;
	include('connect.php');
	//obtener usuarios sin asignar
	$sql='SELECT distinct
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO",
		  "SGDP_USUARIOS_ROLES"."B_ACTIVO"
		FROM 
		  sgdp."SGDP_USUARIOS_ROLES"
		ORDER BY
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC';
		  //return $sql;
	$result=pg_query($dbconn, $sql);
	if(!$result){
		return 'Error al extraer usuarios';
	}
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$activo=$sel=$val='';
		if($row['B_ACTIVO']=='f'){
			$activo=' (no vigente)';
		}
		if($_idUser!=null && $_idUser==$row['ID_USUARIO']){
			$sel=' selected';
		}
		if($_idRol!=null){
			$val=$_idRol.'-';
		}
		
		$resp.='<option value="'.$val.$row['ID_USUARIO'].'" '.$sel.'>'.$row['ID_USUARIO'].$activo.'</option>';
	}
	
	return $resp;
	
	
}


function saveUsers($_asignados,$_idRol){
	include('connect.php');
	$rol=explode('-',$_idRol);
	pg_query($dbconn, 'BEGIN;');
	//1 eliminar los usuarios asignados
	
	$sql='DELETE FROM sgdp."SGDP_USUARIO_RESPONSABILIDAD" WHERE "ID_RESPONSABILIDAD" = '.$rol[1].'; ';
	$resp = $sql.'</br>';
	//echo $sql;
	//2 incorporar a los nuevo usuarios
	for($i=0; $i<count($_asignados); $i++){
		$sql.='INSERT INTO sgdp."SGDP_USUARIO_RESPONSABILIDAD"(
            "ID_USUARIO", 
			"ID_RESPONSABILIDAD", 
            "N_ORDEN"
			)
    		VALUES (
				\''.$_asignados[$i].'\', 
				'.$rol[1].', 
            	'.($i+1).'
				); ';
		$resp .= 'INSERT INTO sgdp."SGDP_USUARIO_RESPONSABILIDAD"(
            "ID_USUARIO", 
			"ID_RESPONSABILIDAD", 
            "N_ORDEN"
			)
    		VALUES (
				\''.$_asignados[$i].'\', 
				'.$rol[1].', 
            	'.($i+1).'
				); '.'</br>';
	}
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		$cuerpo="[ROLLBACK][".date('Y-m-d H:i:s')."]: ".$sql;
		$file = fopen("LOG_SGDP.txt", "a");
		fwrite($file, $cuerpo . PHP_EOL);
		fclose($file);
		echo 'mal</br>';
		
	}
	pg_query($dbconn, 'COMMIT');
	$cuerpo="[COMMIT][".date('Y-m-d H:i:s')."]: ".$sql;
	$file = fopen("LOG_SGDP.txt", "a");
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
	//echo $sql;
	return '<h2>Usuarios asignados exitosamente!</h2></br><h3>Log de la operaci&oacute;n:</h3></br></br></br>'.$resp;
	
}


function getRoles($_idUser, $_vigProc){
	include('connect.php');
	$roles=null;
	$_SESSION['idUSer']=$_idUser;
	$sql='SELECT distinct
			  "SGDP_PROCESOS"."ID_PROCESO", 
			  "SGDP_PROCESOS"."A_NOMBRE_PROCESO", 
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD", 
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO", 
			  "SGDP_PROCESOS"."B_VIGENTE", 
  			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD"
			FROM 
			  sgdp."SGDP_RESPONSABILIDAD", 
			  sgdp."SGDP_PROCESOS", 
			  sgdp."SGDP_RESPONSABILIDAD_TAREA", 
			  sgdp."SGDP_TAREAS", 
			  sgdp."SGDP_USUARIO_RESPONSABILIDAD"
			WHERE 
			  "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD_TAREA"."ID_RESPONSABILIDAD" AND
			  "SGDP_TAREAS"."ID_TAREA" = "SGDP_RESPONSABILIDAD_TAREA"."ID_TAREA" AND
			  "SGDP_TAREAS"."ID_PROCESO" = "SGDP_PROCESOS"."ID_PROCESO" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" = \''.$_idUser.'\'
			
		
		';
		if($_vigProc!=''){
			$sql.='"SGDP_PROCESOS"."B_VIGENTE" = true ';
		}
		$sql.=' ORDER BY
			  "SGDP_PROCESOS"."A_NOMBRE_PROCESO" ASC, 
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD" ASC;';
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar nombre de proceso';
		}
		$vigente='';
		/*$roles='<table border="1">
		<tr>
		<td>ID Proceso</td>
        <td width= "320px">Nombre Proceso</td>
        <td width= "320px">Rol asignado</td>
        <td >Asignar usuario a rol</td>
        <td width= "50px">&nbsp;</td>
      </tr>
	  ';*/
	  $roles='<table border="1">
		<tr>
		<td>ID Proceso</td>
        <td width= "320px">Nombre Proceso</td>
        <td width= "320px">Rol asignado</td>
      </tr>
	  ';
		for($i=0;$i<pg_num_rows($result);$i++){	
			$vigente='';
			$row = pg_fetch_array($result, $i);
			$roles.='<tr>';
			if($row['B_VIGENTE']=='f'){
				$vigente=' (no vigente) ';
			}
			$usuarios=getUsers($row['ID_USUARIO'],$row['ID_RESPONSABILIDAD'] );
			
			$roles.='<td>'.$row['ID_PROCESO'].$vigente.'</td>';
			$roles.='<td>'.$row['A_NOMBRE_PROCESO'].$vigente.'</td>';
			$roles.='<td>'.$row['A_NOMBRE_RESPONSABILIDAD'].'</td>';
			/*$roles.='<td><select name="rolAsig[]" id="rolAsig[]" style="width:250px" require>
          				'.$usuarios.'<input name="saveUser'.$row['ID_RESPONSABILIDAD'].'" type="button" value="Asignar"/></td>';
			$roles.='<td></td>';	*/
			/*$roles.='<td>'..'</td>';
			$roles.='<td>'..'</td>';*/
		}
		$roles.="</table>";
		return $roles;
}

function getRolesTotal(){
	include('connect.php');
	$roles=null;
	$sql='SELECT distinct
			  "SGDP_PROCESOS"."ID_PROCESO", 
			  "SGDP_PROCESOS"."A_NOMBRE_PROCESO", 
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD", 
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO", 
			  "SGDP_PROCESOS"."B_VIGENTE", 
			  "SGDP_UNIDADES"."A_CODIGO_UNIDAD"
			FROM 
			  sgdp."SGDP_RESPONSABILIDAD", 
			  sgdp."SGDP_PROCESOS", 
			  sgdp."SGDP_RESPONSABILIDAD_TAREA", 
			  sgdp."SGDP_TAREAS", 
			  sgdp."SGDP_USUARIO_RESPONSABILIDAD", 
			  sgdp."SGDP_USUARIOS_ROLES", 
			  sgdp."SGDP_UNIDADES"
			WHERE 
			  "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD_TAREA"."ID_RESPONSABILIDAD" AND
			  "SGDP_TAREAS"."ID_PROCESO" = "SGDP_PROCESOS"."ID_PROCESO" AND
			  "SGDP_TAREAS"."ID_TAREA" = "SGDP_RESPONSABILIDAD_TAREA"."ID_TAREA" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" AND
			  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" = "SGDP_USUARIOS_ROLES"."ID_USUARIO" AND
			  "SGDP_USUARIOS_ROLES"."ID_UNIDAD" = "SGDP_UNIDADES"."ID_UNIDAD"
			ORDER BY
			  "SGDP_PROCESOS"."A_NOMBRE_PROCESO" ASC, 
			  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD" ASC;
		
		';

		$result=pg_query($dbconn, $sql);
		
		for($i=0;$i<pg_num_rows($result);$i++){	
			$vigente='S&iacute;';
			$row = pg_fetch_array($result, $i);
			$roles.='<tr>';
			if($row['B_VIGENTE']=='f'){
				$vigente='No';
			}
	
			
			$roles.='<td>'.$row['ID_PROCESO'].'</td>';
			$roles.='<td>'.$row['A_NOMBRE_PROCESO'].'</td>';
			$roles.='<td>'.$row['A_CODIGO_UNIDAD'].'</td>';
			$roles.='<td></td>';
			$roles.='<td>'.$row['A_NOMBRE_RESPONSABILIDAD'].'</td>';
			$roles.='<td>'.$row['ID_USUARIO'].'</td>';
			$roles.='<td>'.$vigente.'</td>';
			/*$roles.='<td><select name="rolAsig[]" id="rolAsig[]" style="width:250px" require>
          				'.$usuarios.'<input name="saveUser'.$row['ID_RESPONSABILIDAD'].'" type="button" value="Asignar"/></td>';
			$roles.='<td></td>';	*/
			/*$roles.='<td>'..'</td>';
			$roles.='<td>'..'</td>';*/
		}
		return $roles;
}

function getUsersRol($_idRol, $_idUser){
include('connect.php');


$usuarios='<option selected>Seleccione un usuario...</option>';


return $usuarios;


}

?>