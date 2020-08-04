<?php 
session_start();
//$archivo='proceso2.bpmn.xml'; //archivo bizagi
//$archivo='proceso22.xml';  //archivo activiti
//$archivo='proceso3.bpmn20.xml'; //archivo process modeler
//$archivo='DMN-Account-Verification2.bpmn';



if (isset($_FILES['fileFoto']['tmp_name'])) {
	//paso: guardar imagen del proceso
	
	$dir_subida = 'diagramas/';
	$exten = pathinfo($_FILES['fileFoto']['name'], PATHINFO_EXTENSION);
	$fichero_subido = $dir_subida . basename($_FILES['fileFoto']['name']);
	
	if (move_uploaded_file($_FILES['fileFoto']['tmp_name'], $fichero_subido)) {
		$_SESSION['foto']=$fichero_subido;
		//echo 'revisar archivo subido '.$fichero_subido;
	} else {
		//echo 'falla en la subida del archivo'.$fichero_subido;
	}
}
	
//if (file_exists($archivo)) {
if (isset($_FILES['fileBPMN']['tmp_name'])) {

	//$loadArchivoXml = file_get_contents($archivo);
	$loadArchivoXml = file_get_contents($_FILES['fileBPMN']['tmp_name']);
	$xmlProceso=$loadArchivoXml;
	$loadArchivoXml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$3", $loadArchivoXml);
	$loadArchivoXml = simplexml_load_string($loadArchivoXml);
	
	$json_string = json_encode($loadArchivoXml);
	$procesoXML = json_decode($json_string, TRUE);
	//echo 'Procesando archivo '.$archivo.'</br>';
	procesarArchivo($procesoXML, $xmlProceso);
	header('Location: index.php');
	exit;
	/*echo '<pre>';
    print_r($procesoXML);
	exit;*/
} else {
    //exit('Error al subir archivo '.$_FILES['fileBPMN']['name']);
}


function procesarArchivo($procesoXML, $_xmlProceso){
	
	if(!isset($_POST['txtSubProc']) || $_POST['txtSubProc']==''){
		if(array_key_exists('0', $procesoXML['process'])){
			$nombre_proceso=$procesoXML['process'][0]['@attributes']['name'];
		}elseif(array_key_exists('name', $procesoXML['process']['@attributes'])){
			$nombre_proceso=$procesoXML['process']['@attributes']['name'];
	
		}else{
			$nombre_proceso=$procesoXML['process']['@attributes']['id'];
		}
	
		if($nombre_proceso==''){
			$nombre_proceso=$procesoXML['process']['laneSet']['@attributes']['name'];
		}	
	}else{
		$nombre_proceso=$_POST['txtSubProc'];
	}
	
	
	$idProceso=$_POST['cbxProc'];
	$isPriv='FALSE';
	if(isset($_POST['chkPriv'])){
		$isPriv=$_POST['chkPriv'];
	}
	
	$duracion=$_POST['txtDur'];
	//$duracion=0;
	$divResponsable=$_POST['cbxDiv'];
	
	//echo $nombre_proceso.'</br>';
	$codProc=$_POST['txtCodProc'];

	//extraer evento inicio
	$inicio=null;
	if(array_key_exists('0', $procesoXML['process'])){
		$inicio= $procesoXML['process'][1]['startEvent']['@attributes'];	
	}else{
		$inicio= $procesoXML['process']['startEvent']['@attributes'];
	}


	//extraer eventos fin
	$fin=null;
	foreach ($procesoXML['process'] as $key => $value){
		if(strpos(strtolower($key), 'endevent')===false){
			continue;
		}else{
			if(array_key_exists('0', $procesoXML['process'][$key])){
				for($i=0;$i<count($procesoXML['process'][$key]); $i++) {
					$fin[$i]=$procesoXML['process'][$key][$i]['@attributes'];
				}		
			}else{
				$fin[]=$procesoXML['process'][$key]['@attributes'];
			}
		}
	}/**/
	//print_r($fin);
	
	//extraer nombre de los roles y sus elementos
	$roles=null;
	for($i=0;$i<count($procesoXML['process']['laneSet']['lane']); $i++) {
		$roles[$i]['name']=$procesoXML['process']['laneSet']['lane'][$i]['@attributes']['name'];
		
		if(array_key_exists('0', $procesoXML['process']['laneSet']['lane'][$i]['flowNodeRef'])){
			$roles[$i]['elementos']=$procesoXML['process']['laneSet']['lane'][$i]['flowNodeRef'];	
		}else{
			$roles[$i]['elementos'][0]=$procesoXML['process']['laneSet']['lane'][$i]['flowNodeRef'];
		}
		
		$roles[$i]['idBD']='r';
		//echo $roles[$i]['name'].'</br>';
	}
	//print_r($roles);
	//exit;
	
	//extraer tareas y asociacion de docs
	$tareas=null;
	$asoc_docs=null;
	$j=0;
	foreach ($procesoXML['process'] as $key => $value){
		if(strpos(strtolower($key), 'task')===false){
			//echo $key.' - no detecta</br>';
			continue;
		}else{
			if(array_key_exists('0', $procesoXML['process'][$key])){
				//echo count($procesoXML['process'][$key]).'</br>';
				for($i=0;$i<count($procesoXML['process'][$key]); $i++) {
					$tareas[$j]=$procesoXML['process'][$key][$i]['@attributes'];
					$tareas[$j]['idBD']='t';
					$tareas[$j]['tipo']=$key;
					$tareas[$j]['docs']=getRelDocs($procesoXML['process'][$key][$i]['dataOutputAssociation']);
					$tareas[$j]['prop']=getPropiedadesTarea($procesoXML['process'][$key][$i]);
					//$tareas[$j]['params']=getParamsTarea($procesoXML['process'][$key][$i]);
					$tareas[$j]['salidasNoConformes']=getSalidasNoConformes($procesoXML['process'][$key][$i]);
					//$duracion+=$tareas[$j]['prop']['plazo'];				
					$j++;
				}		
			}else{
				$tareas[$j]=$procesoXML['process'][$key]['@attributes'];
				$tareas[$j]['idBD']='t';
				$tareas[$j]['tipo']=$key;
				$tareas[$j]['docs']=getRelDocs($procesoXML['process'][$key]['dataOutputAssociation']);	
				$tareas[$j]['prop']=getPropiedadesTarea($procesoXML['process'][$key]);
				//$tareas[$j]['params']=getParamsTarea($procesoXML['process'][$key]);
				$tareas[$j]['salidasNoConformes']=getSalidasNoConformes($procesoXML['process'][$key][$i]);
				//$duracion+=$tareas[$j]['prop']['plazo'];
				$j++;
			}
		}
	}/*
	echo '<pre>';
	print_r($tareas);
	exit;*/
	
	//extraer documentos
	$docs=null;
	$j=0;
	foreach ($procesoXML['process'] as $key => $value){
		if(strpos(strtolower($key), 'dataobjectreference')===false){
			continue;
		}else{
			if(array_key_exists('0', $procesoXML['process'][$key])){
			//if(count($procesoXML['process'][$key])>1){
				for($i=0;$i<count($procesoXML['process'][$key]); $i++) {
					$docs[$j]=$procesoXML['process'][$key][$i]['@attributes'];
					$docs[$j]['idBD']='d';
					$docs[$j]['prop']=getPropiedadesDoc($procesoXML['process'][$key][$i]);
					$j++;
				}		
			}else{
				$docs[$j]=$procesoXML['process'][$key]['@attributes'];
				$docs[$j]['idBD']='d';
				$docs[$j]['prop']=getPropiedadesDoc($procesoXML['process'][$key]);
				$j++;
			}
		}
	}/**/
	//print_r($docs);

	//extraer compuertas
	$compuertas=null;
	$k=0;
	foreach ($procesoXML['process'] as $key => $value){
		if(strpos(strtolower($key), 'gateway')===false){
			continue;
		}else{
			if(array_key_exists('0', $procesoXML['process'][$key])){
			//if(count($procesoXML['process'][$key])>1){
				for($i=0;$i<count($procesoXML['process'][$key]); $i++) {
					$compuertas[$k]=$procesoXML['process'][$key][$i]['@attributes'];
					$compuertas[$k]['tipo']=$key;
					$compuertas[$k]['idBD']='';
					$k++;
				}		
			}else{
				$compuertas[$k]=$procesoXML['process'][$key]['@attributes'];
				$compuertas[$k]['tipo']=$key;
				$compuertas[$k]['idBD']='';
				$k++;
			}
		}
	}/**/
	//print_r($compuertas);

	//extraer relaciones
	$relaciones=null;
	foreach ($procesoXML['process']['sequenceFlow'] as $key =>$flujo){
		$relaciones[$key]['de']=$flujo['@attributes']['sourceRef'];
		$relaciones[$key]['a']=$flujo['@attributes']['targetRef'];	
	}/**/
	//print_r($relaciones);
	
	$proceso['nombre']=$nombre_proceso;
	$proceso['duracion']=$duracion;
	$proceso['privado']=$isPriv;
	$proceso['idProc']=$idProceso;
	$proceso['div_resp']=$divResponsable;
	$proceso['inicio']=$inicio;
	$proceso['fin']=$fin;
	$proceso['tareas']=$tareas;
	$proceso['docs']=$docs;
	$proceso['roles']=$roles;
	$proceso['compuertas']=$compuertas;
	$proceso['relaciones']=$relaciones;
	$proceso['xml']=$_xmlProceso;
	$proceso['codProc']=$codProc;
	$_SESSION['proceso']=$proceso;
	tablaProceso($proceso);	
}

function getPropiedadesTarea($_tarea){
	$prop=null;
	$propiedades=$_tarea['extensionElements']['properties']['property'];
	if(array_key_exists('0', $propiedades)){
		foreach($propiedades as $val){
			$prop[$val['@attributes']['name']]=$val['@attributes']['value'];		
		}
	}else{
		$prop[$propiedades['@attributes']['name']]=$propiedades['@attributes']['value'];
	}/**/
	return $prop;

}

function getSalidasNoConformes($_tarea) {
	$salidasNoConformesValue=null;
	$salidasNoConformes=$_tarea['extensionElements']['inputOutput']['outputParameter']['list']['value'];	
	return $salidasNoConformes;
}

function getParamsTarea($_tarea){
	$prop=$nomPar=$map=null;
	$propiedades=$_tarea['extensionElements']['inputOutput']['outputParameter'];
	if(array_key_exists('0', $propiedades)){
		foreach($propiedades as $val){
			/*$nomPar=$val['@attributes']['name'];
			$prop[$nomPar]=$val['map']['entry'];*/	
			$lineParam = preg_split('/\r\n|\r|\n/', $val);
			$prop[]=$val;			
		}
	}else{
		//$prop[$propiedades['@attributes']['name']]=$propiedades['map']['entry'];
		$lineParam = preg_split('/\r\n|\r|\n/', $propiedades);
		$prop[]=$propiedades;
		//$prop[$propiedades['@attributes']['name']]=$propiedades;
		
	}/**/
	return $prop;
}

function getPropiedadesDoc($_doc){
	$prop=null;
	if(!isset($_doc['extensionElements']['properties']['property'])){
		//print_r($propiedades);
		return;
	}
	
	$propiedades=$_doc['extensionElements']['properties']['property'];
	//print_r($propiedades);
	//exit;
	if(array_key_exists('0', $propiedades)){
		foreach($propiedades as $val){
			$prop[$val['@attributes']['name']]=$val['@attributes']['value'];		
		}
	}else{
		$prop[$propiedades['@attributes']['name']]=$propiedades['@attributes']['value'];
	}/**/
	return $prop;

}


function tablaProceso($proceso){
	$contAdv=0;
	$mensaje='<h3>Por favor verifique la información del proceso antes de grabar</h3>';
	$tabla='<h4>Nombre del proceso</h4>'.$proceso['nombre'].'</br></br>
	<h4>Duración</h4>'.$proceso['duracion'].' días</br></br>
	<h4>Privacidad</h4>'.$proceso['privado'].'</br></br>
	<h4>Detalle del proceso</h4><table border="1">
		<tr>
		<td>ID Tarea</td>
		<td>Nombre Tarea</td>
		<td>Roles</td>
		<td>Documentos requeridos</td>
		<td>Tipo de Tarea</td>
		<td>Etapa</td>
		<td>Destinos</td>
		<td>Visa</td>
		<td>FEA</td>
		<td>Numera</td>
		<td>Espera</td>
		<td>Duración</td>
		<td>Par&aacute;metros</td>
		<td>Tipo Reset</td>
		<td>D&iacute;as reset</td>
		<td>Alertas</td>	
		</tr>
		';
	for($i=0; $i<count($proceso['tareas']);$i++){
		$visa=$fea=$adv=$plazo=null;
		$visa=$proceso['tareas'][$i]['prop']['visa'];
		$fea=$proceso['tareas'][$i]['prop']['fea'];
		$numera=$proceso['tareas'][$i]['prop']['num'];
		$espera=$proceso['tareas'][$i]['prop']['esperar'];
		$plazo=$proceso['tareas'][$i]['prop']['plazo'];
		$etapa=$proceso['tareas'][$i]['prop']['etapa'];
		$tiporesteo=$proceso['tareas'][$i]['prop']['tiporesteo'];
		$diasresteo=$proceso['tareas'][$i]['prop']['diasresteo'];
		$tiporesteo=$proceso['tareas'][$i]['prop']['tiporesteo'];
		$diasresteo=$proceso['tareas'][$i]['prop']['diasresteo'];
		$roles=getRol($proceso['tareas'][$i]['id'],$proceso['roles'] );
		$tipo=getTipoTarea($proceso['tareas'][$i]['id'],$proceso);
		//$parametrosT=$proceso['tareas'][$i]['params'];
		$salNoConf=$proceso['tareas'][$i]['salidasNoConformes'];
		//echo '<pre>';
		//print_r($parametrosT);
		//exit;
		if($plazo==''){
			$adv.='La tarea no tiene plazo asociado</br>';
			$contAdv++;
		}
		
		if($proceso['tareas'][$i]['name']==''){
			$adv.='La tarea no tiene nombre</br>';
			$contAdv++;
		}
		
		if($roles==''){
			$adv.='La tarea no tiene roles asociados</br>';
			$contAdv++;
		}
		
		if($etapa==''){
			$adv.='La tarea no tiene una etapa asociada</br>';
			$contAdv++;
		}

		if($fea!=''){
			$verifica=verifDocFEA($proceso['tareas'][$i]['docs'], $proceso['docs']);
			if($verifica!=''){
				$adv.=$verifica;
				$contAdv++;
			}
		}

		//print_r($salNoConf);
		
		/*if(array_key_exists('0', $salNoConf)){
			foreach($salNoConf as $val){
				print_r($val);
			}
		}*/

		
		$tabla.='
			<tr>
			<td>'.$proceso['tareas'][$i]['id'].'</td>
			<td>'.$proceso['tareas'][$i]['name'].'</td>
			<td>'.$roles.'</td>
			<td>'.getDocsSalida($proceso['tareas'][$i]['docs'],$proceso['docs']).'</td>
			<td>'.$tipo.'</td>
			<td>'.$etapa.'</td>
			<td>'.getDestinos($proceso['tareas'][$i]['id'],$proceso).'</td>
			<td>'.$visa.'</td>
			<td>'.$fea.'</td>
			<td>'.$numera.'</td>
			<td>'.$espera.'</td>
			<td>'.$plazo.'</td>
			<td>'.$salNoConf.'</td>
			<td>'.$tiporesteo.'</td>
            <td>'.$diasresteo.'</td>
			<td>'.$adv.'</td>			
			</tr>';
	
	}
	$tabla.='</table>';
	$formSend='</br></br><form action="guardar_bpmn.php" method="post" enctype="multipart/form-data" onsubmit="return grabar();"><input name="btnGuardar" type="submit" id="btnGuardar" value="Grabar Proceso"></form>';
	if($contAdv>0){
		$mensaje='</br></br><h3>Debe corregir la(s) '.$contAdv.' observacion(es) del cuadro de alertas antes de guardar el proceso.</h3>';
		$formSend='';
	}
	
	//echo $tabla;
	$_SESSION['tabla']=$mensaje.$tabla.$formSend;
	return;
	//echo 'Fin de procesamiento';
}

function transformParam($_param){
	foreach($_param as $val){
		$nomP.=preg_replace('/\r\n|\r|\n/','<br>', $val).' ';
		$nomP.='<br>';
	}
	if($nomP==': <br>'){
		return '';
	}
	return $nomP;
}

function getRol($_idTarea, $_arrayRol){
	$role='';
	foreach($_arrayRol as $rol){
		foreach($rol['elementos'] as $part){
			if(trim($_idTarea)==trim($part)){
				return $rol['name'];
			}else{
				$role.=$rol['name'].' - ';
			}	
		}
	}/**/
	return $role;
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

function verifDocFEA($_docsTarea, $_arrayDocs){
	$verif=null;
	$contFEA=0;
	//al menos 1 documento debe estar marcado como FEA para que la verificacion este ok
	foreach($_arrayDocs as $docs){
		foreach($_docsTarea as $docsT){
			if($docsT==$docs['id']){

				$fea=strtolower(trim($docs['prop']['fea']));
				if($fea==''){
					$verif.='Documento no marcado como FEA</br>';
				}else{
					$contFEA++;
				}
				/*$numAutoD=strtolower(trim($docs['prop']['numauto']));
				if($numAutoD==''){
					$verif.='Documento no marcado como numero automatico';
				}*/
				
			}
		}
	}/**/

	if($contFEA>0){
		return null;
	}
	return $verif;
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
				$destinos.=$nombreT.' ('.$rel['a'].')</br>';
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