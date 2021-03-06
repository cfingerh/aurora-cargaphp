<?php
session_start();

if(isset($_POST['asignados'])){
	//$_SESSION['resultado']=saveUsers($_POST['asignados'], $_POST['rolProc']);
	echo saveUsers($_POST['asignados'], $_SESSION['idProceso']);
	//header("Location: ../asig_user.php");
	exit;
	
}

if(isset($_POST['func']) && $_POST['func']=='getUsersCargo'){
	echo getUsersCargo($_POST['cargo']);
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
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO"
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
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC';
		  //return $sql;
	$result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al extraer usuarios sin asignar';
	}
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$resp.='<option value="'.$row['ID_USUARIO'].'">'.$row['ID_USUARIO'].'</option>';
	}
	return $resp;
		
}


function getNomProc($_idProc){
	
	include('connect.php');
	$sql='SELECT 
				  "SGDP_PROCESOS"."A_NOMBRE_PROCESO", 
					"SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE",
					"SGDP_INSTANCIAS_DE_PROCESOS"."ID_INSTANCIA_DE_PROCESO"
				FROM 
				  sgdp."SGDP_PROCESOS", 
				  sgdp."SGDP_INSTANCIAS_DE_PROCESOS"
				WHERE 
				  "SGDP_PROCESOS"."ID_PROCESO" = "SGDP_INSTANCIAS_DE_PROCESOS"."ID_PROCESO" AND
				  "SGDP_INSTANCIAS_DE_PROCESOS"."A_NOMBRE_EXPEDIENTE" = \''.$_idProc.'\'';	
	
	$result=pg_query($dbconn, $sql);
	if(pg_num_rows($result)<1){
		echo "No existe el expediente indicado";
		exit;
	}
	$row = pg_fetch_array($result, 0);
	$_SESSION['idProceso']=$row['ID_INSTANCIA_DE_PROCESO'];
	return array($row['A_NOMBRE_PROCESO'], $row['A_NOMBRE_EXPEDIENTE']);
}

function saveUsers($_notificados, $_idProc){
	include('connect.php');
	$resp=$sql='';
	$idProc=pg_escape_string($_idProc);
	//print_r($_notificados);
	//return;
	pg_query($dbconn, 'BEGIN;');
	//incorporar a los usuarios al seguimiento
	
	for($i=0; $i<count($_notificados); $i++){
		$notif='';
		$aux=explode('-',pg_escape_string($_notificados[$i]));
		if (count($aux)<2) {
			$notif=$_notificados[$i];
		}else{
			$notif=$aux[1];
		}
		
		
		$sql.='INSERT INTO sgdp."SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"(
            "ID_INSTANCIA_PROCESO", "ID_USUARIO")
				VALUES ('.$idProc.', \''.$notif.'\');
			';
		$resp.=($i+1).'.- ' .$sql.'</br>';
	}
	$result=pg_query($dbconn, $sql);
	if(!$result){
	
		
		pg_query($dbconn, 'ROLLBACK');
		$cuerpo="[ROLLBACK][".date('Y-m-d H:i:s')."]: ".$sql;
		$file = fopen("LOG_SGDP.txt", "a");
		fwrite($file, $cuerpo . PHP_EOL);
		fclose($file);
		
		return '<h2>Algo fall&oacute; en la notificaci&oacute;n</h2></br><h3>Log de la operaci&oacute;n:</h3></br></br></br>';
		
	}/**/
	$notificados=null;
	$asunto = '[SGDP] Notificacion de proceso '.utf8_decode($_SESSION['nomProc'][0]).' ('.$_SESSION['nomProc'][1].')';
	$mensaje = 'Estimado usuario:</br></br>

			Se le ha notificado el siguiente subproceso: </br></br>
			<ul>
			<li>Proceso: <strong>'.utf8_decode($_SESSION['nomProc'][0]).'</strong></li>
			<li>Expediente: <strong>'.$_SESSION['nomProc'][1].'</strong></li>
			</ul>
			
			Para revisarla, por favor dir?jase a la <strong>Bandeja de Notificaciones y Seguimiento</strong> del sistema 
			
			<a href="http://sistemas.scj.cl/sgdp/"> Sistema de gesti?n documental y de procesos</a></br></br>
			
			Saludos cordiales,
';
	for($i=0; $i<count($_notificados); $i++){
		$notif=$notificados='';
		$aux=explode('-',pg_escape_string($_notificados[$i]));
		if (count($aux)<2) {
			$notif=$_notificados[$i];
		}else{
			$notif=$aux[1];
		}
		$notificados=$notif.'@scj.gob.cl; ';
		sendMail('SISTEMA SGDP', $notificados, $asunto, $mensaje);
		$resp.=$i.'.- OK - '.$notificados.'</br>';
	}
	
	pg_query($dbconn, 'commit');
	$cuerpo="[COMMIT][".date('Y-m-d H:i:s')."]: ".$resp;
	$file = fopen("LOG_SGDP.txt", "a");
	fwrite($file, $cuerpo . PHP_EOL);
	fclose($file);
	//echo $sql;
	//return '<h2 class="style1">Usuarios notificados exitosamente!</h2></br><h3>Log de la operaci&oacute;n:</h3></br></br></br>'.$resp;
	return '<h2>Usuarios notificados exitosamente!</h2>';
	
}

function sendMail($_remitente, $_destin, $_asunto, $_mensaje){
	
	$destinatario = $headers = '';
	$cuerpo = $_mensaje;
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8r\n";
	$asunto =$_asunto;
	
	//direcci?n del remitente
	$headers .= "From:".$_remitente." <sgdp@scj.gob.cl\r\n";
	
	$destinatario = $_destin;
	mail($destinatario,$asunto,$cuerpo,$headers);
	
}

function getUsersCargo($_cargo){
	include('connect.php');
	$aux=explode('-',$_cargo);
	$resp='';
	$cargoUn='';
	$users=null;
	switch ($aux[1]) {
		case 'prof':
			$cargoUn=' AND ("SGDP_USUARIOS_ROLES"."ID_ROL" = 3 OR "SGDP_USUARIOS_ROLES"."ID_ROL" = 6) ';
			break;
		case 'jefe':
			$cargoUn=' AND ("SGDP_USUARIOS_ROLES"."ID_ROL" = 2 OR "SGDP_USUARIOS_ROLES"."ID_ROL" = 4) ';
			break;
		case 'admin':
			$cargoUn=' AND ("SGDP_USUARIOS_ROLES"."ID_ROL" = 1 OR "SGDP_USUARIOS_ROLES"."ID_ROL" = 5)';
			break;
		case 'coord':
			$cargoUn=' AND ("SGDP_USUARIOS_ROLES"."ID_ROL" = 8 OR "SGDP_USUARIOS_ROLES"."ID_ROL" = 9)';
			break;
	}
	
	$sql='SELECT distinct
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO"
		FROM 
		  sgdp."SGDP_USUARIOS_ROLES", 
		  sgdp."SGDP_UNIDADES"
		WHERE 
		  "SGDP_UNIDADES"."ID_UNIDAD" = "SGDP_USUARIOS_ROLES"."ID_UNIDAD" AND
		  "SGDP_USUARIOS_ROLES"."B_ACTIVO" = true AND 
		  "SGDP_UNIDADES"."A_CODIGO_UNIDAD" = \''.$aux[0].'\' 
		  '.$cargoUn.'
		ORDER BY
		  "SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC;';
	//return $sql;	
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$users[]=$row['ID_USUARIO'];
		
	}
	//print_r($users);
	
	$sql='SELECT distinct
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_USUARIO"
				FROM 
				  sgdp."SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"
				WHERE 
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_INSTANCIA_PROCESO" = '.$_SESSION['idProceso'].' 
				ORDER BY
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_USUARIO" ASC;';
		
	$result=pg_query($dbconn, $sql);	
	
	for($j=0;$j<count($users);$j++){
		$disab='';
		$select='selected';
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			if($users[$j]==$row['ID_USUARIO']){
				$disab='disabled';
				$select='';
			}	
		}		
		$resp.='<option value="'.$users[$j].'" '.$disab.' '.$select.'>'.$users[$j].'</option>';		
	}

	return $resp;

}

function getUnidades(){
	include('connect.php');
	$unidades=null;
	$sql='SELECT "ID_UNIDAD", "A_CODIGO_UNIDAD", "A_NOMBRE_COMPLETO_UNIDAD"
  				FROM sgdp."SGDP_UNIDADES"
		ORDER BY
		  "SGDP_UNIDADES"."A_CODIGO_UNIDAD" ASC;
		';
		//return $sql;
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar nombre de proceso';
		}
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			$unidades[$i]['id']=$row['A_CODIGO_UNIDAD'];
			$unidades[$i]['nombre']=$row['A_NOMBRE_COMPLETO_UNIDAD'];
		}
		return $unidades;

}

function getUsers($_idProc){
	include('connect.php');
	$users=null;
	$sql='SELECT distinct
			  "SGDP_UNIDADES"."A_CODIGO_UNIDAD", 
			  "SGDP_USUARIOS_ROLES"."ID_UNIDAD",
			  "SGDP_USUARIOS_ROLES"."ID_USUARIO"
			FROM 
			  sgdp."SGDP_UNIDADES", 
			  sgdp."SGDP_USUARIOS_ROLES"
			WHERE 
			  "SGDP_UNIDADES"."ID_UNIDAD" = "SGDP_USUARIOS_ROLES"."ID_UNIDAD" AND
			  "SGDP_USUARIOS_ROLES"."B_ACTIVO" = true 
			ORDER BY
			  "SGDP_UNIDADES"."A_CODIGO_UNIDAD" ASC, 
			  "SGDP_USUARIOS_ROLES"."ID_USUARIO" ASC;
		';
		//return $sql;
		$result=pg_query($dbconn, $sql);
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			
			$color='';
			if ($row['ID_UNIDAD']%2==0){
				$color='style="background:#CAE4FF;"';
			}
			
			$users[$i]['id']=$row['A_CODIGO_UNIDAD'].'-'.$row['ID_USUARIO'];
			$users[$i]['nombre']=$row['ID_USUARIO'];
			$users[$i]['disab']='';
			$users[$i]['color']=$color;
			$users[$i]['select']='selected';
		}
		
		$sql='SELECT distinct
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_USUARIO"
				FROM 
				  sgdp."SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"
				WHERE 
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_INSTANCIA_PROCESO" = '.$_idProc.' 
				ORDER BY
				  "SGDP_SEGUIMIENTO_INTANCIA_PROCESOS"."ID_USUARIO" ASC;';
		//return $sql;
		$result=pg_query($dbconn, $sql);
		
		for($i=0;$i<pg_num_rows($result);$i++){	
			$row = pg_fetch_array($result, $i);
			for($j=0;$j<count($users);$j++){
				if($users[$j]['nombre']==$row['ID_USUARIO']){
					$users[$j]['disab']='disabled';
					$users[$j]['select']='';
				}			
			}
		}
		return $users;
}

?>