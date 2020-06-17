<?php
session_start();

if(isset($_POST['asignados'])){
	//$_SESSION['resultado']=saveUsers($_POST['asignados'], $_POST['rolProc']);
	echo saveUsers($_POST['asignados'], $_POST['rolProc']);
	//header("Location: ../asig_user.php");
	exit;
	
}

if(isset($_POST['func']) && $_POST['func']=='getRolesFree'){
	echo getUsersFree($_POST['rol']);
	exit;
}

if(isset($_POST['func']) && $_POST['func']=='getRolesAsig'){
	echo getUsersAsig($_POST['rol']);
	exit;
}


function getUsersFree($_rol){
	$resp=null;
	include('connect.php');
	$rol=explode('-',$_rol);
	//obtener usuarios sin asignar
	$sql='SELECT distinct
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO",
		  "SGDP_USUARIOS_ROLES"."ID_UNIDAD"
		FROM 
		  sgdp."SGDP_USUARIOS_ROLES"
		WHERE 
		  "SGDP_USUARIOS_ROLES"."B_ACTIVO" = true AND 
		"SGDP_USUARIOS_ROLES"."ID_USUARIO" not in (SELECT distinct
				  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO"
				FROM 
				  sgdp."SGDP_USUARIOS_ROLES", 
				  sgdp."SGDP_USUARIO_RESPONSABILIDAD"
				WHERE 
				  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" = "SGDP_USUARIOS_ROLES"."ID_USUARIO" AND
				  "SGDP_USUARIOS_ROLES"."B_ACTIVO" = true AND 
				  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = '.$rol[1].')
		ORDER BY
		  "SGDP_USUARIOS_ROLES"."ID_UNIDAD", "SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC';
		  
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al guardar nombre de proceso';
	}
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$color='';
		if ($row['ID_UNIDAD']%2==0){
			$color='style="background:#CAE4FF;"';
		}
		$resp.='<option '.$color.' value="'.$row['ID_USUARIO'].'">'.$row['ID_USUARIO'].'</option>';
	}
	
	return $resp;
	
	
}


function getUsersAsig($_rol){
	$resp=null;
	$rol=explode('-',$_rol);
	include('connect.php');
	
	//obtener usuarios asignados
	$sql='SELECT distinct
		  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO"
		FROM 
		  sgdp."SGDP_USUARIOS_ROLES", 
		  sgdp."SGDP_USUARIO_RESPONSABILIDAD"
		WHERE 
		  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" = "SGDP_USUARIOS_ROLES"."ID_USUARIO" AND
		  "SGDP_USUARIOS_ROLES"."B_ACTIVO" = true AND 
		  "SGDP_USUARIO_RESPONSABILIDAD"."ID_RESPONSABILIDAD" = '.$rol[1].'
		ORDER BY
		  "SGDP_USUARIO_RESPONSABILIDAD"."ID_USUARIO" ASC';

	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al guardar nombre de proceso';
	}
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$resp.='<option value="'.$row['ID_USUARIO'].'">'.$row['ID_USUARIO'].'</option>';
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
				); 
				'.'</br>';
	}
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		$cuerpo="[ROLLBACK][".date('Y-m-d H:i:s')."]: ".$sql;
		$file = fopen("LOG_SGDP.txt", "a");
		fwrite($file, $cuerpo . PHP_EOL);
		fclose($file);
		return 'Error al excribir los datos</br></br><h3>Log de la operaci&oacute;n:</h3></br></br></br></br>'.$resp;
		
	}
	pg_query($dbconn, 'COMMIT');
	$cuerpo="[COMMIT][".date('Y-m-d H:i:s')."]: ".$sql;
	$file = fopen("LOG_SGDP.txt", "a");
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
	//echo $sql;
	return '<h2>Usuarios asignados exitosamente!</h2></br></br><h3>Log de la operaci&oacute;n:</h3></br></br></br></br>'.$resp;
	
}


function getProcesos(){
	include('connect.php');
	$procesos=null;
	$sql='SELECT 
		  "SGDP_PROCESOS"."ID_PROCESO", 
		  "SGDP_PROCESOS"."A_NOMBRE_PROCESO"
		FROM 
		  sgdp."SGDP_PROCESOS"
		WHERE 
		  "SGDP_PROCESOS"."B_VIGENTE" = true
		ORDER BY
		  "SGDP_PROCESOS"."A_NOMBRE_PROCESO" ASC;
		';
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar nombre de proceso';
		}
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			$procesos[$i]['id']=$row['ID_PROCESO'];
			$procesos[$i]['nombre']=$row['A_NOMBRE_PROCESO'];
		}
		return $procesos;

}

function getRoles($_idProc){
	include('connect.php');
	$roles=null;
	$sql='SELECT distinct
		  "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD",
		  "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD", 
 		  "SGDP_TAREAS"."ID_PROCESO"
		FROM 
		  sgdp."SGDP_RESPONSABILIDAD", 
		  sgdp."SGDP_RESPONSABILIDAD_TAREA", 
		  sgdp."SGDP_TAREAS"
		WHERE 
		  "SGDP_RESPONSABILIDAD_TAREA"."ID_RESPONSABILIDAD" = "SGDP_RESPONSABILIDAD"."ID_RESPONSABILIDAD" AND
		  "SGDP_TAREAS"."ID_TAREA" = "SGDP_RESPONSABILIDAD_TAREA"."ID_TAREA" 
		
		';
		if($_idProc!=''){
			$sql.=' AND "SGDP_TAREAS"."ID_PROCESO" = '.$_idProc;
		}
		$sql.=' ORDER BY "SGDP_RESPONSABILIDAD"."A_NOMBRE_RESPONSABILIDAD" ASC;';
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar nombre de proceso';
		}
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			$roles[$i]['id']=$row['ID_PROCESO'].'-'.$row['ID_RESPONSABILIDAD'];
			$roles[$i]['nombre']=$row['A_NOMBRE_RESPONSABILIDAD'];
		}
		return $roles;
}

?>