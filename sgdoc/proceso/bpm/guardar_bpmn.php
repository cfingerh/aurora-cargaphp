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
			"ID_UNIDAD",
			"A_CODIGO_PROCESO")
			 VALUES (\''.$_proceso['nombre'].'\',
			 \''.$_proceso['nombre'].'\', 
			 '.$_proceso['idProc'].', 
			 TRUE, 
			 '.$_proceso['privado'].',
			 '.$_proceso['duracion'].', 
			 \''.$_proceso['xml'].'\', 
			 '.$_proceso['div_resp'].',
			 \''.$_proceso['codProc'].'\') 
			 RETURNING "ID_PROCESO"; ';
	$_SESSION['sql'].=$sql.'</br></br>';
    $result=pg_query($dbconn, $sql);
	if(!$result){
		$error=pg_last_error($dbconn);
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al guardar nombre de proceso '.$error;
	}
	$row = pg_fetch_array($result, 0);
	$id_proceso=$row['ID_PROCESO'];

	//paso 1.0.1: deshabilitar el proceso antiguo vigente
	$sql = 'UPDATE sgdp."SGDP_PROCESOS" SET 
			"B_VIGENTE"=FALSE			   
			WHERE "ID_PROCESO"!='.$id_proceso.' AND "A_CODIGO_PROCESO"=\''.$_proceso['codProc'].'\'' ;

	$_SESSION['sql'].=$sql.'</br></br>';
	$result=pg_query($dbconn, $sql);
	if(!$result){
		$error=pg_last_error($dbconn);
		pg_query($dbconn, 'ROLLBACK');
		return 'Error al actualizar vigencia de proceso antiguo - '.$error;
	}

	//1.1 renombrar foto
	$fichero_subido=$_SESSION['foto'];
	$ext = pathinfo($fichero_subido, PATHINFO_EXTENSION);
	rename($fichero_subido, "diagramas/$id_proceso.$ext");
	
	
	//$id_proceso=66;
	$sql='';
	
//paso2: guardar tareas del proceso
	for($i=0;$i<count($_proceso['tareas']);$i++){
		
		$nombreT=$_proceso['tareas'][$i]['name'];
		$nombreT=ereg_replace('[^ A-Za-z0-9_-ñÑ]', '', $nombreT);
		$idT=$_proceso['tareas'][$i]['id'];
		
		
		$caract=getCaractTarea($_proceso['tareas'][$i]['id'], $_proceso);
		
		if($caract['fin']=='FALSE'){
			$caract['fin']=getTipoFinTarea($_proceso['tareas'][$i]['id'], $_proceso);
		}
		
		$visa=strtolower(trim($_proceso['tareas'][$i]['prop']['visa']));
		$fea=strtolower(trim($_proceso['tareas'][$i]['prop']['fea']));
		$plazo=strtolower(trim($_proceso['tareas'][$i]['prop']['plazo']));
		$etapa=strtolower(trim($_proceso['tareas'][$i]['prop']['etapa']));
		$asignaNum=strtolower(trim($_proceso['tareas'][$i]['prop']['num']));
		$esperar=strtolower(trim($_proceso['tareas'][$i]['prop']['esperar']));
		$orden=$_SESSION['ordenT'][$_proceso['tareas'][$i]['id']];
		$destinos_tarea[$_proceso['tareas'][$i]['id']]=getDestinos($_proceso['tareas'][$i]['id'], $_proceso);
		$parametrosT=$_proceso['tareas'][$i]['params'];
		$tiporesteo=strtolower(trim($_proceso['tareas'][$i]['prop']['tiporesteo']));
        $diasresteo=strtolower(trim($_proceso['tareas'][$i]['prop']['diasresteo']));	
		$confExp=strtolower(trim($_proceso['tareas'][$i]['prop']['expediente']));	
		$distribuir=strtolower(trim($_proceso['tareas'][$i]['prop']['distribuir']));
		$numAuto=strtolower(trim($_proceso['tareas'][$i]['prop']['numauto']));
		//print_r($caract);
		
		if($visa=='si' || $visa=='sí' || $visa=='true'){
			$visa='TRUE';
		}else{
			$visa='FALSE';
		}
		
		if($fea=='si' || $fea=='sí' || $fea=='true'){
			$fea='TRUE';
		}else{
			$fea='FALSE';
		}
		if($asignaNum=='si' || $asignaNum=='sí' || $asignaNum=='true'){
			$asignaNum='TRUE';
		}else{
			$asignaNum='FALSE';
		}
		
		if($esperar=='si' || $esperar=='sí' || $esperar=='true'){
			$esperar='TRUE';
		}else{
			$esperar='FALSE';
		}
		
		if($confExp=='si' || $confExp=='sí' || $confExp=='true'){
			$confExp='TRUE';
		}else{
			$confExp='FALSE';
		}

		if($distribuir=='si' || $distribuir=='sí' || $distribuir=='true'){
			$distribuir='TRUE';
		}else{
			$distribuir='FALSE';
		}
		/**********NUMERACION AUTOMATICA DE DOCUMENTOS*********/
		if($numAuto=='si' || $numAuto=='sí' || $numAuto=='true'){
			$numAuto='TRUE';
		}else{
			$numAuto='FALSE';
		}
		if($plazo<1){
			$plazo=1;
		}	
		
		if($diasresteo==null){
			$diasresteo=0;
		}
		
		if($caract['inicio']=='TRUE'){
			$orden=1;
		}/**/	

		if($orden=='' || $orden==null){
			$orden=22;
		}
		
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
			"B_PUEDE_APLICAR_FEA",
			"B_ASIGNA_NUM_DOC",
			"B_ESPERAR_RESP",
			"A_TIPO_RESETEO",
			"N_DIAS_RESETEO",
			"B_CONFORMA_EXPEDIENTE",
			"B_DISTRIBUYE",
			"B_NUMERACION_AUTO")
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
			'.$fea.',
			'.$asignaNum.',
			'.$esperar.',
			\''.$tiporesteo.'\',
            '.$diasresteo.',
			\''.$confExp.'\',
			\''.$distribuir.'\', 
			\''.$numAuto.'\')					   
			RETURNING "ID_TAREA";';
		
		$_SESSION['sql'].=$sql.'</br>';
		
		$result=pg_query($dbconn, $sql);
		if(!$result){
			$error = pg_last_error($dbconn);
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar tarea '.$nombreT.' - '.$error;
		}
		$row = pg_fetch_array($result, 0);
		$idTareaA=$row['ID_TAREA'];
		$_SESSION['proceso']['tareas'][$i]['idBD']=$row['ID_TAREA'];
		//$_SESSION['proceso']['tareas'][$i]['idBD']=$i;
		
		/**********Guardar los parametros de las tareas**********/		
		/**/
		foreach($parametrosT as $key=>$val){
			$lineParam = preg_split('/\r\n|\r|\n/', $val);
			$pTitulo=null;
			for($p=0;$p<count($lineParam);$p++){
				$aux= substr($lineParam[$p], 0, 2); 
				
				//0 preguntar ID de tipo de parametro
				$idTipoP=$nomParam=$posN=null;
				switch($aux){
					case 'o)':
						$idTipoP='1';
						$posN=strpos($lineParam[$p], ':');
						$nomParam=trim(substr($lineParam[$p], 2, $posN)); 
						break;
					case 's)':
						$idTipoP='5';
						$posN=strpos($lineParam[$p], ':');
						$nomParam=trim(substr($lineParam[$p], 2, $posN));
						break;
					case 'c)':
						$idTipoP='1';
						$posN=strpos($lineParam[$p], ':');
						$nomParam=trim(substr($lineParam[$p], 2, $posN));
						break;
					case 't)':
						$idTipoP='1';
						$posN=strpos($lineParam[$p], ':');
						$nomParam=trim(substr($lineParam[$p], 2, $posN));
						break;
					default:
						$pTitulo=$lineParam[$p];
						$nomParam=null;
						break;
				}
					
				//si es titulo, salta a la siguiente linea para guardar parametros
				if($nomParam==null){
					//$_SESSION['sql'].=$lineParam[$p]. ' - nomParam Nulo</br>';
					continue;
				}
				
				//1 guardar parametros de tarea
				
				$sql2='INSERT INTO sgdp."SGDP_PARAMETRO_DE_TAREA" ( 
				"A_NOMBRE_PARAM_TAREA", 
				"ID_TIPO_PARAMETRO_DE_TAREA",
				"A_TITULO" )
					VALUES
						( \''.$nomParam.'\', 
						'.$idTipoP.',
						\''.$pTitulo.'\') RETURNING "ID_PARAM_TAREA";
									';
				$_SESSION['sql'].=$sql2.'</br>';
				
				$result2=pg_query($dbconn, $sql2);
				if(!$result2){
					$error=pg_last_error($dbconn) ;
					pg_query($dbconn, 'ROLLBACK');
					return 'Error al guardar parametro '.$nomParam.' '.'" ['.$error.']' ;
				}
				$row2 = pg_fetch_array($result2, 0);
				$idParam=$row2['ID_PARAM_TAREA'];
				
				//2 guardar relacion param-tarea				
				$sql2='INSERT INTO sgdp."SGDP_PARAMETRO_RELACION_TAREA"(
									"ID_TAREA", "ID_PARAM_TAREA")
							VALUES ('.$idTareaA.', '.$idParam.');
									';
				$_SESSION['sql'].=$sql2.'</br>';
				
				$result2=pg_query($dbconn, $sql2);
				if(!$result2){
					$error=pg_last_error($dbconn) ;
					pg_query($dbconn, 'ROLLBACK');
					return 'Error al guardar relacion tarea-parametro '.$nomParam.'" ['.$error.']';
				}
				//3 guardar valores de param
				$auxValParam=explode(';',substr($lineParam[$p], $posN+1)); 
				
				for($p2=0;$p2<count($auxValParam);$p2++){
					$sql2='INSERT INTO sgdp."SGDP_TEXTO_PARAMETRO_DE_TAREA"(
									"ID_PARAM_TAREA", 
									"A_TEXTO")
							VALUES ( 
							'.$idParam.', 
							\''.$auxValParam[$p2].'\');
						';
					$_SESSION['sql'].=$sql2.'</br>';
					
					$result2=pg_query($dbconn, $sql2);
					if(!$result2){
						$error=pg_last_error($dbconn) ;
						pg_query($dbconn, 'ROLLBACK');
						return 'Error al guardar valor del parametro "'.$auxValParam[$p].'" ['.$error.']' ;
					}
				}		
			}//fin for
		}
		/**********************************/
	}
	//return $sql;
	//print_r($_SESSION['proceso']['tareas']);
	$_SESSION['sql'].='</br></br>';
	$sql='';

	
	//paso 3: guardar documentos del proceso
	for($i=0;$i<count($_proceso['docs']);$i++){
		
		$nombreD=trim($_proceso['docs'][$i]['name']);
		$nombreD=ereg_replace('[^ A-Za-z0-9_-ñÑ]', '', $nombreD);
		//$idDoc = getIdBdDoc($nombreD);
		
		$caract=getCaractTarea($_proceso['docs'][$i]['id'], $_proceso);
		$visa=strtolower(trim($_proceso['docs'][$i]['prop']['visa']));
		$fea=strtolower(trim($_proceso['docs'][$i]['prop']['fea']));
		$expediente=strtolower(trim($_proceso['docs'][$i]['prop']['expediente']));
		$conductor=strtolower(trim($_proceso['docs'][$i]['prop']['conductor']));
		$numAutoD=strtolower(trim($_proceso['docs'][$i]['prop']['numauto']));
		$codTipoDoc=strtolower(trim($_proceso['docs'][$i]['prop']['codtipodoc']));
		
		$visaP=$feaP=$expedienteP=$conductorP='';

		if($visa=='si' || $visa=='sí' || $visa=='true'){
			$visa='TRUE';
		}else{
			$visa='FALSE';
		}
		
		if($fea=='si' || $fea=='sí' || $fea=='true'){
			$fea='TRUE';
		}else{
			$fea='FALSE';
		}
	
		if($expediente=='si' || $expediente=='sí' || $expediente=='true'){
			$expediente='TRUE';
		}else{
			$expediente='FALSE';
		}
		
		if($conductor=='si' || $conductor=='sí' || $conductor=='true'){
			$conductor='TRUE';
		}else{
			$conductor='FALSE';
		}

		if($numAutoD=='si' || $numAutoD=='sí' || $numAutoD=='true'){
			$numAutoD='TRUE';
		}else{
			$numAutoD='FALSE';
		}
	
	
		$sql='INSERT INTO sgdp."SGDP_TIPOS_DE_DOCUMENTOS"(
		   "A_NOMBRE_DE_TIPO_DE_DOCUMENTO"
		   , "B_CONFORMA_EXPEDIENTE"
		   , "B_APLICA_VISACION"
		   , "B_APLICA_FEA"
		   , "B_ES_DOCUMENTO_CONDUCTOR"
		   , "A_COD_TIPO_DOC"
		   , "B_NUMERACION_AUTO")
			VALUES (
			\''.$nombreD.'\'
			,'.$expediente.'
			, '.$visa.'
			, '.$fea.'
			, '.$conductor.'
			, \''.$codTipoDoc.'\'
			, '.$numAutoD.'
			) RETURNING "ID_TIPO_DE_DOCUMENTO"; ';

		//si no existe el archivo, lo inserta en la BD	
		/*if($idDoc=='d'){
			
			if($visa=='si' || $visa=='sí' || $visa=='true'){
				$visa='TRUE';
			}else{
				$visa='FALSE';
			}
			
			if($fea=='si' || $fea=='sí' || $fea=='true'){
				$fea='TRUE';
			}else{
				$fea='FALSE';
			}
		
			if($expediente=='si' || $expediente=='sí' || $expediente=='true'){
				$expediente='TRUE';
			}else{
				$expediente='FALSE';
			}
			
			if($conductor=='si' || $conductor=='sí' || $conductor=='true'){
				$conductor='TRUE';
			}else{
				$conductor='FALSE';
			}

			if($numAutoD=='si' || $numAutoD=='sí' || $numAutoD=='true'){
				$numAutoD='TRUE';
			}else{
				$numAutoD='FALSE';
			}
		
		
			$sql='INSERT INTO sgdp."SGDP_TIPOS_DE_DOCUMENTOS"(
			   "A_NOMBRE_DE_TIPO_DE_DOCUMENTO"
			   , "B_CONFORMA_EXPEDIENTE"
			   , "B_APLICA_VISACION"
			   , "B_APLICA_FEA"
			   , "B_ES_DOCUMENTO_CONDUCTOR"
			   , "A_COD_TIPO_DOC"
			   , "B_NUMERACION_AUTO")
				VALUES (
				\''.$nombreD.'\'
				,'.$expediente.'
				, '.$visa.'
				, '.$fea.'
				, '.$conductor.'
				, \''.$codTipoDoc.'\'
				, '.$numAutoD.'
				) RETURNING "ID_TIPO_DE_DOCUMENTO"; ';
			
		}else{
			//si existe, actualiza sus campos
			if($visa=='si' || $visa=='sí' || $visa=='true'){
				$visaP=' "B_APLICA_VISACION"=TRUE, ';
			}
			
			if($fea=='si' || $fea=='sí' || $fea=='true'){
				$feaP=' "B_APLICA_FEA"=TRUE, ';
			}
		
			if($expediente=='si' || $expediente=='sí' || $expediente=='true'){
				$expedienteP=' "B_CONFORMA_EXPEDIENTE"=TRUE, ';
			}
			
			if($conductor=='si' || $conductor=='sí' || $conductor=='true'){
				$conductorP='"B_ES_DOCUMENTO_CONDUCTOR"=TRUE ';
			}else{
				$conductorP='"B_ES_DOCUMENTO_CONDUCTOR"=FALSE ';
			}
			/**********NUMERACION AUTOMATICA DE DOCUMENTOS*********/
			/*
			if($numAutoD=='si' || $numAutoD=='sí' || $numAutoD=='true'){
				$numAutoDP=', "B_NUMERACION_AUTO"=TRUE ';
			}else{
				//$numAutoDP=', "B_NUMERACION_AUTO"=FALSE ';
			}

			if($codTipoDoc!=''){
				$codTipoDocP=', "A_COD_TIPO_DOC"= \''.$codTipoDoc.'\'';
			}
			
			$sql='UPDATE sgdp."SGDP_TIPOS_DE_DOCUMENTOS" SET 
			   '.$expedienteP.'
			   '.$visaP.'
			   '.$feaP.' 
			   '.$conductorP.' 
			   '.$numAutoDP.' 
			   '.$codTipoDocP.' 			   
			   WHERE "ID_TIPO_DE_DOCUMENTO"='.$idDoc.' RETURNING "ID_TIPO_DE_DOCUMENTO"; ';	
			   
			   	
		}*/
		
		$_SESSION['sql'].=$sql.'</br>';
		
		$result=pg_query($dbconn, $sql);
		if(!$result){
			$error= pg_last_error($dbconn);
			$fp = fopen(date('Ymd_His')."_LOG.txt", "w");
			//$log=str_replace('</br>',"\n",$_SESSION['sql']);
			fputs($fp, $error.' SQL: '.$sql);
			fclose($fp);
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
			$error=pg_last_error($dbconn);
			pg_query($dbconn, 'ROLLBACK');
			return 'Error al guardar rol nombre '.$nombreR.$error;
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
				if($docs['id']==$docst && $docs['id']!=''){
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
	return 'Operación realizada exitosamente!';
	
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

function getTipoFinTarea($_idTarea, $_proceso){
	$tipo='FALSE';
	$arrFin=$_proceso['fin'];
	$arrComp=$_proceso['compuertas'];
	foreach($arrFin as $fin){
		foreach($arrComp as $comp){
			foreach($_proceso['relaciones'] as $rel){
				if($rel['de']==$comp['id'] && $rel['a']==$fin['id']){
					$compFin[]=$comp['id'];
					//echo $comp['id'].'</br>';
				}
			}
		}
	}
	foreach($_proceso['relaciones'] as $rel){
		foreach($compFin as $comp){
			if($rel['a']==$comp && $rel['de']==$_idTarea){
				$tipo='TRUE';
			}
		}
	}
	return $tipo;
}

function getCaractTarea($_idTarea, $_proceso){
	$tipo=null;
	$tipo['fin']='FALSE';
	$tipo['inicio']='FALSE';
	foreach($_proceso['relaciones'] as $rel){
		//tarea de inicio
		
		$idInicio=$_proceso['inicio']['id'];
		if($rel['de']==$idInicio && $rel['a']==$_idTarea){
			$tipo['inicio']='TRUE';
		}
		//tarea de fin
		
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
			
			//echo 'tarea de inicio: '.$rel['a'].' orden '.$_SESSION['ordenT'][$rel['a']].'</br>';
			//funcion recursiva de orden para cada hijo
			setOrdenRecursivo($rel['a'],$_proceso, $orden);
			break;
		}
	}
	
}

function setOrdenRecursivo($_idTarea,$_proceso, $_orden){
	//echo 'nuevo ciclo recursivo de tarea '.$_idTarea.' orden '.$_orden.'</br>';
	//$idTarea=$_idTarea;
	if(isset($_SESSION['ordenT'][$_idTarea]) && $_SESSION['ordenT'][$_idTarea]!=1){
		//echo ' fin con tarea '.$_idTarea.'</br>';
		//return;
	}
	$idTarea=$_idTarea;
	$orden=$_orden;
	$aux=null;
	for($i=0;$i<count($_proceso['relaciones']);$i++){
		//echo $i.' asigna relacion. orden actual '.$orden.'</br>';
		$rel=$_proceso['relaciones'][$i];
		
		if($rel['de']==$idTarea && $_SESSION['ordenT'][$rel['a']]==null){
			//echo 'Encuentra tarea origen: '.$idTarea.'</br>';
			//comprueba si el elemento siguiente es una compuerta
			$arrComp=$_proceso['compuertas'];
			foreach($arrComp as $comp){
				//si es compuerta, cambia id de busqueda y comienza la busqueda otra vez
				if($rel['a']==$comp['id']){
					$i=-1;
					$idTarea=$comp['id'];
					//echo 'Encuentra compuerta. Nueva tarea siguiente: '.$idTarea.'</br>';
				}
			}	
			
			if($i==-1){
				//echo 'continua por compuerta</br>';
				continue;
			}
			
			//si no es compuerta, busca la siguiente tarea
			$nombreT=getNombreTarea($rel['a'], $_proceso);
			//echo 'nombre tarea siguiente: '.$nombreT.'</br>';
			if($nombreT!==null){
				$orden=$_orden+1;
				$_SESSION['ordenT'][$rel['a']]=$orden;
				//echo 'Encuentra nombre tarea siguiente: '.$nombreT.' - ID: '.$idTarea.' orden '.$_SESSION['ordenT'][$rel['a']].'</br></br>';
				$_proceso['relaciones'][$i]=null;
				setOrdenRecursivo($rel['a'],$_proceso, $orden);
			}else{
				//echo 'Tarea siguiente sin nombre: '.$idTarea.' vs '.$_idTarea.'</br>';
			}
		}/**/
	}	
	//echo  'fin orden '.$_orden.'</br></br>';
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