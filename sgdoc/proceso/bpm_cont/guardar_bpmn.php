<?php 
	
session_start();

if(isset($_SESSION['proceso'])){
	unset($_SESSION['ordenT']);
	unset($_SESSION['excluidos']);
	unset($_SESSION['sql']);
	setOrden($_SESSION['proceso']);
	//print_r($_SESSION['ordenT']);
	$_SESSION['resultado']=  guardarProceso($_SESSION['proceso']);
	unset($_SESSION['proceso']);
	header('Location: index.php');
	exit;
	
}



function guardarProceso($_proceso){
	include('connect.php');
	pg_query($dbconn, 'BEGIN');
	
//paso1: guardar nombre de proceso
	
	$sql = 'INSERT INTO sgdp."SGDP_PROCESOS"(
            "A_NOMBRE_PROCESO", 
			"A_DESCRIPCION_PROCESO", 
			"ID_MACRO_PROCESO", 
            "B_VIGENTE", 
			"B_CONFIDENCIAL" ,
			"N_DIAS_HABILES_MAX_DURACION", 
			"X_BPMN",
			"ID_UNIDAD")
			 VALUES (\''.$_proceso['nombre'].'\',
			 \''.$_proceso['nombre'].'\', 
			 '.$_proceso['idProc'].', 
			 TRUE, 
			 '.$_proceso['privado'].',
			 '.$_proceso['duracion'].', 
			 \''.$_proceso['xml'].'\', 
			 '.$_proceso['div_resp'].') 
			 RETURNING "ID_PROCESO"; ';
	$_SESSION['sql'].=$sql.'</br></br>';
    $result=pg_query($dbconn, $sql);
	if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al guardar nombre de proceso</br>';
	}
	$row = pg_fetch_array($result, 0);
	$id_proceso=$row['ID_PROCESO'];
	
	//1.1 renombrar foto
	$fichero_subido=$_SESSION['foto'];
	$ext = pathinfo($fichero_subido, PATHINFO_EXTENSION);
	rename($fichero_subido, "diagramas/$id_proceso.$ext");
	
	
	//$id_proceso=66;
	$sql='';
	
//paso2: guardar tareas del proceso
	for($i=0;$i<count($_proceso['tareas']);$i++){
		
		$nombreT=$_proceso['tareas'][$i]['name'];
		$idT=$_proceso['tareas'][$i]['id'];
		$caract=getCaractTarea($_proceso['tareas'][$i]['id'], $_proceso);
		$visa=strtolower(trim($_proceso['tareas'][$i]['prop']['visa']));
		$fea=strtolower(trim($_proceso['tareas'][$i]['prop']['fea']));
		$plazo=strtolower(trim($_proceso['tareas'][$i]['prop']['plazo']));
		$etapa=strtolower(trim($_proceso['tareas'][$i]['prop']['etapa']));
		$orden=$_SESSION['ordenT'][$_proceso['tareas'][$i]['id']];
		$destinos_tarea[$_proceso['tareas'][$i]['id']]=getDestinos($_proceso['tareas'][$i]['id'], $_proceso);
		
		//print_r($caract);
		
		if($visa=='si' || $visa=='s??' || $visa=='true'){
			$visa='TRUE';
		}else{
			$visa='FALSE';
		}
		
		if($fea=='si' || $fea=='s??' || $fea=='true'){
			$fea='TRUE';
		}else{
			$fea='FALSE';
		}
		
		if($plazo<1){
			$plazo=1;
		}	
		/*
		if($caract['inicio']=='TRUE'){
			$orden='1';
		}*/	
		
		$sql='INSERT INTO sgdp."SGDP_TAREAS"(
			"A_NOMBRE_TAREA", 
			"ID_DIAGRAMA", 
			"A_DESCRIPCION_TAREA", 
			"ID_PROCESO", 
            "N_DIAS_HABILES_MAX_DURACION", 
			"N_ORDEN", 
			"B_VIGENTE", 
			"B_SOLO_INFORMAR", 
            "ID_ETAPA", 
			"B_OBLIGATORIA", 
			"B_ES_ULTIMA_TAREA", 
			"A_TIPO_DE_BIFURCACION", 
            "B_PUEDE_VISAR_DOCUMENTOS", 
			"B_PUEDE_APLICAR_FEA")
    	VALUES (
			\''.$nombreT.'\',
			\''.$idT.'\',  
			NULL, 
			'.$id_proceso.', 
			'.$plazo.', 
            '.$orden.',
			TRUE, 
			FALSE, 
			'.$etapa.', 
            TRUE, 
			'.$caract['fin'].', 
			\''.$caract['tipoBif'].'\', 
            '.$visa.', 
			'.$fea.')
			RETURNING "ID_TAREA";';
		
		$_SESSION['sql'].=$sql.'</br>';
		
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar tarea nombre '.$nombreT;
		}
		$row = pg_fetch_array($result, 0);
		$_SESSION['proceso']['tareas'][$i]['idBD']=$row['ID_TAREA'];
		//$_SESSION['proceso']['tareas'][$i]['idBD']=$i;
		
	}
	//return $sql;
	//print_r($_SESSION['proceso']['tareas']);
	
	
	/*if(!$result){
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al guardar nombre de proceso';
	}*/
	$_SESSION['sql'].='</br></br>';

	$sql='';
	
//paso 3: guardar documentos del proceso
	for($i=0;$i<count($_proceso['docs']);$i++){
		
		$nombreD=trim($_proceso['docs'][$i]['name']);
		
		$idDoc = getIdBdDoc($nombreD);
		
		$caract=getCaractTarea($_proceso['docs'][$i]['id'], $_proceso);
		$visa=strtolower(trim($_proceso['docs'][$i]['prop']['visa']));
		$fea=strtolower(trim($_proceso['docs'][$i]['prop']['fea']));
		$expediente=strtolower(trim($_proceso['docs'][$i]['prop']['expediente']));
		$conductor=strtolower(trim($_proceso['docs'][$i]['prop']['conductor']));
		
		$visaP=$feaP=$expedienteP=$conductorP='';
		//si no existe el archivo, lo inserta en la BD	
		if($idDoc=='d'){
			
			if($visa=='si' || $visa=='s??' || $visa=='true'){
				$visa='TRUE';
			}else{
				$visa='FALSE';
			}
			
			if($fea=='si' || $fea=='s??' || $fea=='true'){
				$fea='TRUE';
			}else{
				$fea='FALSE';
			}
		
			if($expediente=='si' || $expediente=='s??' || $expediente=='true'){
				$expediente='TRUE';
			}else{
				$expediente='FALSE';
			}
			
			if($conductor=='si' || $conductor=='s??' || $conductor=='true'){
				$conductor='TRUE';
			}else{
				$conductor='FALSE';
			}
		
		
			$sql='INSERT INTO sgdp."SGDP_TIPOS_DE_DOCUMENTOS"(
			   "A_NOMBRE_DE_TIPO_DE_DOCUMENTO"
			   , "B_CONFORMA_EXPEDIENTE"
			   , "B_APLICA_VISACION"
			   , "B_APLICA_FEA"
			   , "B_ES_DOCUMENTO_CONDUCTOR")
				VALUES (
				\''.$nombreD.'\'
				,'.$expediente.'
				, '.$visa.'
				, '.$fea.'
				, '.$conductor.') RETURNING "ID_TIPO_DE_DOCUMENTO"; ';
			
		}else{
			//si existe, actualiza sus campos
			if($visa=='si' || $visa=='s??' || $visa=='true'){
				$visaP=' "B_APLICA_VISACION"=TRUE, ';
			}
			
			if($fea=='si' || $fea=='s??' || $fea=='true'){
				$feaP=' "B_APLICA_FEA"=TRUE, ';
			}
		
			if($expediente=='si' || $expediente=='s??' || $expediente=='true'){
				$expedienteP=' "B_CONFORMA_EXPEDIENTE"=TRUE, ';
			}
			
			if($conductor=='si' || $conductor=='s??' || $conductor=='true'){
				$conductorP='"B_ES_DOCUMENTO_CONDUCTOR"=TRUE ';
			}else{
				$conductorP='"B_ES_DOCUMENTO_CONDUCTOR"=FALSE ';
			}
			
			$sql='UPDATE sgdp."SGDP_TIPOS_DE_DOCUMENTOS" SET 
			   '.$expedienteP.'
			   '.$visaP.'
			   '.$feaP.' 
			   '.$conductorP.' WHERE "ID_TIPO_DE_DOCUMENTO"='.$idDoc.' RETURNING "ID_TIPO_DE_DOCUMENTO"; ';	
			   	
		}
		
		$_SESSION['sql'].=$sql.'</br>';
		
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar tipo documento '.$nombreD;
		}
		$row = pg_fetch_array($result, 0);
		$_SESSION['proceso']['docs'][$i]['idBD']=$row['ID_TIPO_DE_DOCUMENTO'];
		//$_SESSION['proceso']['docs'][$i]['idBD']=$i;
		
	
	}
	$_SESSION['sql'].='</br></br>';
	$sql='';
//paso 4: guardar roles del proceso
	for($i=0;$i<count($_proceso['roles']);$i++){
		
		$nombreR=$_proceso['roles'][$i]['name'];	
		
		$sql='INSERT INTO sgdp."SGDP_RESPONSABILIDAD"(
             "A_NOMBRE_RESPONSABILIDAD")
   			 VALUES ( \''.$nombreR.'\') RETURNING "ID_RESPONSABILIDAD";';
		
		$result=pg_query($dbconn, $sql);
		if(!$result){
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar rol nombre '.$nombreR;
		}
		$row = pg_fetch_array($result, 0);
		$_SESSION['proceso']['roles'][$i]['idBD']=$row['ID_RESPONSABILIDAD'];
		//$_SESSION['proceso']['roles'][$i]['idBD']=$i;
		$_SESSION['sql'].=$sql.'</br>';
	
	}
	$_SESSION['sql'].='</br>';


//paso 5: guardar relacion entre tareas
	foreach($destinos_tarea as $key => $val){
		
		$de_aux=$key;
		for($i=0; $i<count($_SESSION['proceso']['tareas']);$i++){
			if($_SESSION['proceso']['tareas'][$i]['id']==$de_aux){
				$de=$_SESSION['proceso']['tareas'][$i]['idBD'];
			}
		}
		
		if($val!=null){
			foreach($val as $key2 => $val2){
				for($i=0; $i<count($_SESSION['proceso']['tareas']);$i++){
					if($_SESSION['proceso']['tareas'][$i]['id']==$val2){
						$a=$_SESSION['proceso']['tareas'][$i]['idBD'];
					}
				}	
				$sql='INSERT INTO sgdp."SGDP_REFERENCIAS_DE_TAREAS"(
				"ID_TAREA"
				, "ID_TAREA_SIGUIENTE")
				VALUES (
				'.$de.'
				, '.$a.');';
				
				$_SESSION['sql'].=$sql.'</br>';
				
				$result=pg_query($dbconn, $sql);
				if(!$result){
					pg_query($dbconn, 'ROLLBACK');
					return 'Error al guardar relacion de '.$de.' a '.$a;
				}						
			}
		}	
	}
	$_SESSION['sql'].='</br>';


//paso 6: guardar relacion tareas-documentos
	for($i=0; $i<count($_proceso['tareas']);$i++){
		$idTarea=$_SESSION['proceso']['tareas'][$i]['idBD'];
		//echo $i.' - id tarea '.$idTarea.'</br>';
		
		if($_proceso['tareas'][$i]['docs']==null){
			//echo $i.' sin documentos</br>';
			continue;
		}
		
		foreach($_proceso['tareas'][$i]['docs'] as $docst ){
			//echo $i.' - id docT '.$docst.'</br>';
			foreach($_SESSION['proceso']['docs'] as $docs){
				if($docs['id']==$docst){
					$idDoct=$docs['idBD'];
					
					$sql='INSERT INTO sgdp."SGDP_DOCUMENTOS_DE_SALIDA_DE_TAREAS"(
									"ID_TAREA"
									, "ID_TIPO_DE_DOCUMENTO"
									, "N_ORDEN")
							VALUES (
							'.$idTarea.'
							, '.$idDoct.'
							, 1);';
					$_SESSION['sql'].=$sql.'</br>';
					$result=pg_query($dbconn, $sql);
					if(!$result){
						pg_query($dbconn, 'ROLLBACK');
						return 'Error al guardar doc de salida tarea '.$idTarea.' doc '.$idDoct;
					}
					
				}
			}			
		}
	}
	$_SESSION['sql'].='</br>';



//paso 7: guardar relacion tareas-roles
	for($i=0; $i<count($_proceso['roles']);$i++){
		$idRol=$_SESSION['proceso']['roles'][$i]['idBD'];
		for($j=0; $j<count($_proceso['roles'][$i]['elementos']);$j++){
			for($k=0; $k<count($_proceso['tareas']);$k++){
				$idTarea=$_SESSION['proceso']['tareas'][$k]['idBD'];
				if($_proceso['roles'][$i]['elementos'][$j]==$_proceso['tareas'][$k]['id']){
					$sql='INSERT INTO sgdp."SGDP_RESPONSABILIDAD_TAREA"(
									"ID_RESPONSABILIDAD"
									, "ID_TAREA")
							VALUES (
							'.$idRol.'
							, '.$idTarea.');';
					$_SESSION['sql'].=$sql.'</br>';
					$result=pg_query($dbconn, $sql);
					if(!$result){
						pg_query($dbconn, 'ROLLBACK');
						return 'Error al guardar rol de tarea '.$idTarea.' rol '.$idRol;
					}
				}
			}
		}
	}
	$_SESSION['sql'].='</br>';
	$fp = fopen("log/".date('Ymd_His').".txt", "w");
	$log=str_replace('</br>',"\n",$_SESSION['sql']);
	fputs($fp, $log);
	fclose($fp);
	pg_query($dbconn, 'COMMIT');
	//print_r($id_proceso);
	//return $_SESSION['sql'];
	return 'Operaci??n realizada exitosamente!';
	
}

function getIdBdDoc($_nombreDoc){

	for($i=0;$i<count($_SESSION['proceso']['docs']);$i++){
		if(trim(strtolower($_SESSION['proceso']['docs'][$i]['name']))==strtolower($_nombreDoc)){
			if($_SESSION['proceso']['docs'][$i]['idBD']!='d'){
				return $_SESSION['proceso']['docs'][$i]['idBD'];
			}
		}
	}
	return 'd';
}

function getCaractTarea($_idTarea, $_proceso){
	$tipo=null;
	
	foreach($_proceso['relaciones'] as $rel){
		//tarea de inicio
		$tipo['inicio']='FALSE';
		$idInicio=$_proceso['inicio']['id'];
		if($rel['de']==$idInicio && $rel['a']==$_idTarea){
			$tipo['inicio']='TRUE';
		}
		//tarea de fin
		$tipo['fin']='FALSE';
		$arrFin=$_proceso['fin'];
		foreach($arrFin as $fin){
			if($rel['a']==$fin['id'] && $rel['de']==$_idTarea){
				$tipo['fin']='TRUE';
			}
		}
		//tarea and, or  
		$arrComp=$_proceso['compuertas'];
		$tipo['tipoBif']='';
		foreach($arrComp as $comp){
			if($rel['a']==$comp['id'] && $rel['de']==$_idTarea){
				$tipoC=null;
				//echo $_idTarea;
				if($comp['tipo']=='parallelGateway'){
					$tipoC='AND';
				}
				if($comp['tipo']=='exclusiveGateway'){
					$tipoC='OR';
				}
				$tipo['tipoBif']=$tipoC;
				//echo $tipo['tipoBif'];
				break;
			}
		}
		if($tipo['tipoBif']!=''){
			break;
		}
	}
	return $tipo;

}

function setOrden($_proceso){

	$idTarea=null;
	$orden=0;
	$aux=null;
	for($i=0;$i<count($_proceso['relaciones']);$i++){
		//echo $i.' asigna relacion. orden actual '.$orden.'</br>';
		$rel=$_proceso['relaciones'][$i];
		
		$idInicio=$_proceso['inicio']['id'];
		if($rel['de']==$idInicio && $orden<1){
			$orden++;
			$_SESSION['ordenT'][$rel['a']]=1;
			
			echo 'tarea de inicio: '.$rel['a'].' orden '.$_SESSION['ordenT'][$rel['a']].'</br>';
			//funcion recursiva de orden para cada hijo
			setOrdenRecursivo($rel['a'],$_proceso, $orden);
			break;
		}
	}	
}

function setOrdenRecursivo($_idTarea,$_proceso, $_orden){
	echo 'nuevo ciclo recursivo de tarea '.$_idTarea.' orden '.$_orden.'</br>';
	//$idTarea=$_idTarea;
	if(isset($_SESSION['ordenT'][$_idTarea]) && $_SESSION['ordenT'][$_idTarea]!=1){
		echo ' fin con tarea '.$_idTarea.'</br>';
		//return;
	}
	$idTarea=$_idTarea;
	$orden=$_orden;
	$aux=null;
	for($i=0;$i<count($_proceso['relaciones']);$i++){
		//echo $i.' asigna relacion. orden actual '.$orden.'</br>';
		$rel=$_proceso['relaciones'][$i];
		
		if($rel['de']==$idTarea && $_SESSION['ordenT'][$rel['a']]==null){
			echo 'Encuentra tarea origen: '.$idTarea.'</br>';
			//comprueba si el elemento siguiente es una compuerta
			$arrComp=$_proceso['compuertas'];
			foreach($arrComp as $comp){
				//si es compuerta, cambia id de busqueda y comienza la busqueda otra vez
				if($rel['a']==$comp['id']){
					$i=-1;
					$idTarea=$comp['id'];
					echo 'Encuentra compuerta. Nueva tarea siguiente: '.$idTarea.'</br>';
				}
			}	
			
			if($i==-1){
				echo 'continua por compuerta</br>';
				continue;
			}
			
			//si no es compuerta, busca la siguiente tarea
			$nombreT=getNombreTarea($rel['a'], $_proceso);
			echo 'nombre tarea siguiente: '.$nombreT.'</br>';
			if($nombreT!==null){
				$orden=$_orden+1;
				$_SESSION['ordenT'][$rel['a']]=$orden;
				echo 'Encuentra nombre tarea siguiente: '.$nombreT.' - ID: '.$idTarea.' orden '.$_SESSION['ordenT'][$rel['a']].'</br></br>';
				$_proceso['relaciones'][$i]=null;
				setOrdenRecursivo($rel['a'],$_proceso, $orden);
			}else{
				echo 'Tarea siguiente sin nombre: '.$idTarea.' vs '.$_idTarea.'</br>';
			}
		}/**/
	}	
	echo  'fin orden '.$_orden.'</br></br>';
}

function getRol($_idTarea, $_arrayRol){
	foreach($_arrayRol as $rol){
		foreach($rol['elementos'] as $part){
			if($_idTarea==$part){
				return $rol['name'];
			}	
		}
	}/**/
	return;
}

function getDocsSalida($_docsTarea, $_arrayDocs){
	$listaDocs=null;
	foreach($_arrayDocs as $docs){
		foreach($_docsTarea as $docsT){
			if($docsT==$docs['id']){
				$listaDocs.=$docs['name'].'</br>';
			}
		}
	}/**/
	return $listaDocs;
}

function getTipoTarea($_idTarea, $_proceso){
	$tipo=null;
	
	foreach($_proceso['relaciones'] as $rel){
		//tarea de inicio
		$idInicio=$_proceso['inicio']['id'];
		if($rel['de']==$idInicio && $rel['a']==$_idTarea){
			$tipo.='INICIO</br>';
		}
		//tarea de fin
		$arrFin=$_proceso['fin'];
		foreach($arrFin as $fin){
			if($rel['a']==$fin['id'] && $rel['de']==$_idTarea){
				$tipo.='FIN</br>';
			}
		}
		//tarea and, or  
		$arrComp=$_proceso['compuertas'];
		foreach($arrComp as $comp){
			if($rel['a']==$comp['id'] && $rel['de']==$_idTarea){
				$tipoC=null;
				if($comp['tipo']=='parallelGateway'){
					$tipoC='AND';
				}
				if($comp['tipo']=='exclusiveGateway'){
					$tipoC='OR';
				}
				$tipo.=$tipoC.'</br>';
			}
		}
	}
	return $tipo;
}

function getDestinos($_idTarea, $_proceso){
	$destinos=null;
	$idTarea=$_idTarea;
	$aux='inicio</br>';
	for($i=0;$i<count($_proceso['relaciones']);$i++){
		$aux.='asigna relacion: '.$i.'</br>';
		$rel=$_proceso['relaciones'][$i];
		
		if($rel['de']==$idTarea){
			$aux.='Encuentra tarea origen: '.$idTarea.'</br>';
			//comprueba si el elemento siguiente es una compuerta
			$arrComp=$_proceso['compuertas'];
			foreach($arrComp as $comp){
				//si es compuerta, cambia id de busqueda y comienza la busqueda otra vez
				if($rel['a']==$comp['id']){
					$i=-1;
					$idTarea=$comp['id'];
					$aux.='Encuentra compuerta. Nueva tarea siguiente: '.$idTarea.'</br>';
				}
			}	
			
			//si es compuerta, cambia de id y comienza la busqueda otra vez
			$nombreT=getNombreTarea($rel['a'], $_proceso);
			$aux.='nombre tarea siguiente: '.$nombreT.'</br>';
			if($nombreT!==null){
				$destinos[]=$rel['a'];
				$aux.='Encuentra nombre tarea siguiente: '.$nombreT.'</br>';
			}else{
				$aux.='Tarea siguiente sin nombre: '.$idTarea.' vs '.$_idTarea.'</br>';
			}
		}
	}
	//return $destinos.$aux;
	return $destinos;
}

//retorna el nombre de una tarea segun id
function getNombreTarea($_idTarea,$_proceso){
	foreach($_proceso['tareas'] as $tarea){
		if($tarea['id']==$_idTarea){
			return $tarea['name'];
		}
	}
	return null;
}


//funciones 
function getRelDocs($array_docs){
	$k=0;
	$aux=null;
	if(array_key_exists('0', $array_docs)){
		//echo count($procesoXML['process'][$key]).'</br>';
		for($i=0;$i<count($array_docs); $i++) {
			$aux[$k]=$array_docs[$i]['targetRef'];				
			$k++;
		}		
	}else{
		$aux[$k]=$array_docs['targetRef'];		
		$k++;
	}
	return $aux;
}



?>