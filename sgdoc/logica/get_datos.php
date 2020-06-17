<?php 
session_start();
getUsuarios('');
unlink('/var/www/html/sgdoc/logica/archivo.csv');
//unlink('archivo.csv');
$nombreArchivo = date('Ymdhis').'REGISTRO_CARPETAS_SGDOC_2015.xls';
echo 'Generando archivo</br></br>';
/*header("Content-type: application/vnd.ms-excel; name='excel'");
header("Content-Disposition: filename=$nombreArchivo");
header("Pragma: no-cache");
header("Expires: 0");*/

echo buscar();

function buscar(){
	include("connect.php");
	include("security.php");
	
	
	//$desde=limpiar_variable($_POST["from"]);
	$desde='2014-01-01';
	if(!valida_string($desde) && $desde!=''){
		return 'Error en fecha desde';
	}
	//$hasta=limpiar_variable($_POST["to"]);
	$hasta=date('Y-m-d');
	if(!valida_string($hasta) && $hasta!=''){
		return 'Error en fecha hasta';
	}
	

	$sql="SELECT DISTINCT 
				expedientes.id, 
				expedientes.fecha_despacho_historico, 
				expedientes.fecha_acuse_recibo,
				expedientes.fecha_despacho, 
				expedientes.fecha_creacion, 
				expedientes.id_emisor, 
				expedientes.id_emisor_historico, 
				expedientes.copia,
				expedientes.id_padre,
				expedientes.archivado,
				expedientes.numero_expediente
			FROM 
				public.expedientes 
			WHERE 
				
				 (expedientes.fecha_despacho_historico >= '$desde' AND
				 expedientes.fecha_despacho_historico <= '$hasta' ) OR  
				(expedientes.fecha_despacho >= '$desde' AND
				 expedientes.fecha_despacho <= '$hasta')
				
			ORDER BY numero_expediente, id, fecha_despacho_historico DESC ";
	//return $sql;
	$result = pg_query($dbconn, $sql);
	$i=0;
	
	$carpetas='ID expediente;ID padre;Fecha creacion; No. Carpeta; Emisor; Div Emisor; Receptor; Div Receptor; Tipo; Fecha recepcion bandeja;Materia; No. documento; Tipo Doc.; Observacion; Dias en bandeja; Fecha Archivado;Archivos adjuntos;';
	//$file = fopen("/var/www/html/sgdoc/logica/archivo.csv", "a");
	$file = fopen("/var/www/html/sgdoc/logica/archivo.csv", "a");
	fwrite($file, $carpetas . PHP_EOL);
	fclose($file);
	unset($repl);
	//$file = fopen("/var/www/html/sgdoc/logica/archivo.csv", "a");
	
	for($i=0;$i<pg_num_rows($result);$i++){	
	//while( $row = pg_fetch_array ($result,$i )) {
		$row = pg_fetch_array ($result,$i );
		$idCarpeta=$row['id'];
		$sql2="SELECT 
		documentos.materia
		, documentos.id	
		, documentos.observacion
		, documentos.numero_documento
		, tipos_documentos.descripcion
		, tipos_documentos.electronico	
		FROM 
			public.documentos
			,  public.documentos_expedientes
			, public.tipos_documentos
		WHERE 
				  documentos_expedientes.id_documento = documentos.id AND
				  documentos.id_tipo_documento = tipos_documentos.id AND
				 documentos_expedientes.id_expediente = $idCarpeta ORDER BY id ASC LIMIT 3";
				 
		$result2 = pg_query($dbconn, $sql2);
		$row2 = pg_fetch_array ($result2,0);
		$mat=$row2['materia'];
		$obs=$ndoc='';
		
		$tipoDoc=$row2['descripcion'];
		if(pg_num_rows($result2)>1){
			$row2 = pg_fetch_array ($result2,1);
			$obs=$row2['observacion'];
		}
		for($j=0;$j<pg_num_rows($result2);$j++){	
			$row2 = pg_fetch_array ($result2,$j);
			if($row2['numero_documento']!=''){
				$ndoc.=$row2['numero_documento'].' | ';
			}
		}
		$ndoc=substr($ndoc,0,-2);
		$sql2="SELECT expedientes.fecha_despacho_historico FROM public.expedientes
				WHERE id_padre = $idCarpeta ORDER BY fecha_despacho_historico DESC LIMIT 1";
		$result2 = pg_query($dbconn, $sql2);
		
		$fecha_desp='';
		if($row['archivado']!=''){
			$fecha_desp=$row['archivado'];
		}
		
		if(pg_num_rows($result2)>0){
			$row2 = pg_fetch_array ($result2,0);
			$fecha_desp=$row2['fecha_despacho_historico'];
		}
		if($fecha_desp=='') $fecha_desp=$row['fecha_despacho'];
		if($fecha_desp=='') $fecha_desp=date('Y-m-d');
		
		$tipoC='DIRECTO';
		if($row['copia']=='t') $tipoC='COPIA';
		if($row['copia']==null) $tipoC='INICIO';
		$id_em=$row['id_emisor'];
		$id_em_hist=$row['id_emisor_historico'];
		$repl[]='';
		
		$fechaCrea=$row['fecha_creacion'];
		$fechaBand=$row['fecha_despacho_historico'];
		if($fechaBand=='') $fechaBand=$row['fecha_despacho'];
		if($fechaCrea=='') $fechaCrea=$row['fecha_acuse_recibo'];
		if($fechaCrea=='') $fechaCrea=$row['fecha_despacho'];
		/**calculo de dias**/
		$datetime1 = date_create($fechaBand);
		$datetime2 = date_create($fecha_desp);
		$dias=date_diff($datetime1, $datetime2);
		$day=$dias->format('%d');
		$archivos = getDocsAdjuntos2($idCarpeta, '');
		$emisor=$_SESSION['usuarios'][$id_em_hist][1];
		$div_emisor=$_SESSION['usuarios'][$id_em_hist][2];
		$receptor=$_SESSION['usuarios'][$id_em][1];
		$div_receptor=$_SESSION['usuarios'][$id_em][2];
		/*$carpetas.= '<tr><td>'
			.$idCarpeta.'</td><td>'
			.$row['id_padre'].'</td><td>'
			.$row['fecha_creacion'].'</td><td>'
			.$row['numero_expediente'].'</td><td>'
			.$emisor.'</td><td>'
			.$tipoC.'</td><td >'
			.$row['fecha_despacho_historico'].'</td><td WIDTH="350">'
			.utf8_decode($mat).'</td><td WIDTH="350">'
			.utf8_decode($obs).'</td><td>'			
			.$dias->format('%a').'</td><td>'
			.$row['archivado'].'</td><td WIDTH="350">'
			.utf8_decode($archivos).'</td><td>'
			.'</td></tr>';*/
			
		$carpetas= $idCarpeta.'; '
			.$row['id_padre'].'; '
			.$fechaCrea.'; '
			.$row['numero_expediente'].'; '
			.$emisor.';'
			.$div_emisor.';'
			.$receptor.';'
			.$div_receptor.';'
			.$tipoC.'; '
			.$fechaBand.'; '
			. preg_replace("/\r\n+|\r+|\n+|\t+/i", " ",str_replace(';','.',utf8_decode($mat))).'; '
			.$ndoc.'; '
			.$tipoDoc.'; '
			. preg_replace("/\r\n+|\r+|\n+|\t+/i", " ",str_replace(';','.',utf8_decode($obs))).'; '			
			.$day.'; '
			.$row['archivado'].'; '
			.str_replace(';','.',trim(utf8_decode($archivos))).'; ';
		$file = fopen("/var/www/html/sgdoc/logica/archivo.csv", "a");
		fwrite($file, $carpetas . PHP_EOL);
		fclose($file);
		echo '.';
		//$i++;
	}
	
	unset($result);
	if($i==0){
		$carpetas='';
	}else{
		$carpetas.='</table>';
	}
	echo '</br></br></br>Archivo generado correctamente</br><a href="archivo.csv" download="20150101_'.date('Ymd').'_SGDOC.csv">Dercargar Archivo</a>';
	//return $carpetas;
	
}

function buscarDocumentos(){
	include("connect.php");
	include("security.php");
	
	$materia=limpiar_variable($_POST["txtMateria"]);
	if(!valida_string($materia) && $materia!=''){
		return 'Error en campo "Materia"';
	}
	$nCarp=limpiar_variable($_POST["txtNCarpeta"]);
	if(!valida_string($nCarp) && $nCarp!=''){
		return 'Error en n° de carpeta';
	}
	$nDoc=limpiar_variable($_POST["txtNDoc"]);
	if(!valida_string($nDoc) && $nDoc!=''){
		return 'Error en n° de documento';
	}
	$nomDoc=limpiar_variable($_POST["txtNomDoc"]);
	if(!valida_string($nomDoc) && $nomDoc!=''){
		return 'Error en n° de documento';
	}
	$div=limpiar_variable($_POST["cbxDiv"]);
	if(!valida_numero($div) && $div!=''){
		return 'Error en division';
	}
	/*$creador=limpiar_variable($_POST["cbxCreador"]);
	if(!valida_numero($creador) && $creador!=''){
		return 'Error en creador';
	}*/
	$de=limpiar_variable($_POST["cbxDe"]);
	if(!valida_numero($de) && $de!=''){
		return 'Error en De';
	}
	$a=limpiar_variable($_POST["cbxA"]);
	if(!valida_string($a) && $a!=''){
		return 'Error en A';
	}
	$desde=limpiar_variable($_POST["from"]);
	if(!valida_string($desde) && $desde!=''){
		return 'Error en fecha desde';
	}
	$hasta=limpiar_variable($_POST["to"]);
	if(!valida_string($hasta) && $hasta!=''){
		return 'Error en fecha hasta';
	}
	$select="SELECT DISTINCT
			  documentos.numero_documento, 
			  documentos.materia, 
			  documentos.fecha_creacion, 
			  documentos.id_autor, 
			  documentos.id_archivo, 
			  formatos_documentos.descripcion, 
			  documentos.emisor, 
			  documentos.titulo, 
			  documentos.destinatarios,
			  expedientes.numero_expediente,
			  archivos.nombre_archivo ";
	$from="FROM 
			  public.documentos, 
			  public.documentos_expedientes, 
			  public.expedientes,
			  public.formatos_documentos,
			  public.archivos ";
		  
	$where="WHERE 
			  documentos.id = documentos_expedientes.id_documento AND
			  expedientes.id = documentos_expedientes.id_expediente AND
			  formatos_documentos.id = documentos.id_formato_documento AND
			  documentos.id_archivo = archivos.id ";
	if($nCarp!=''){
		$where.="AND expedientes.numero_expediente ~* '$nCarp' ";
	}
	if($nDoc!=''){
		$where.="AND documentos.numero_documento  ~* '$nDoc' ";
	}
	if($nomDoc!=''){
		$where.="AND documentos.observacion  ~* '$nomDoc' ";
	}
	if($materia!=''){
		$where.="AND documentos.materia  ~* '$materia' ";
	}
	if($div!=''){
		$from.=",public.personas, public.cargos ";
		$where.="AND personas.id = documentos.id_autor AND
				  cargos.id = personas.id_cargo AND
				  cargos.id_unidad_organizacional = $div ";
	}
	if($de!=''){
		$where.="AND documentos.id_autor = $de ";
	}
	if($a!=''){
		$where.="AND documentos.destinatarios ~* '".$_SESSION['usuarios'][$a][1]."' ";
	}
	if($desde!=''){
		$where.="AND documentos.fecha_creacion >= '$desde' ";
	}
	if($hasta!=''){
		$where.="AND documentos.fecha_creacion <= '$hasta' ";
	}

	$criterio ='ORDER BY numero_expediente ASC';
	$sql=$select.$from.$where.$criterio;
	//return $sql;
	$result = pg_query($dbconn, $sql);
	$i=0;
	
	require_once "Alfresco/Service/Repository.php";
	require_once "Alfresco/Service/Session.php";
	require_once "Alfresco/Service/SpacesStore.php";

	// Specify the connection details
	$repositoryUrl = "http://172.16.10.160:8080/alfresco/api";
	$userName = "sgdoc";
	$password = "21382138"; 
	
	// Authenticate the user and create a session
	$repository = new Repository($repositoryUrl);
	$ticket = $repository->authenticate($userName, $password);
	$session = $repository->createSession($ticket);
	
	// Create a reference to the 'SpacesStore'
	$spacesStore = new SpacesStore($session);
	
	// Execute a Luecene query to find the content node we are after.  See http://wiki.alfresco.com/wiki/Search 
	// for some more information about Lucene queries.
	//$nodes = $session->query($spacesStore, "PATH:\"app:company_home/app:guest_home/cm:curso_JBoss_JavaEE_Parte2.pdf\"");
	
	
	$docs='<table id="resultadoD"><tr>
                  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                  <th align="left" bgcolor="#E6E6E6">N&ordm; Documento </th>
                  <th align="left" bgcolor="#E6E6E6">Fecha Creaci&oacute;n </th>
                  <th align="left" bgcolor="#E6E6E6">N&ordm; Carpeta </th>
                  <th align="left" bgcolor="#E6E6E6">Tipo Documento </th>
                  <th align="left" bgcolor="#E6E6E6">Materia</th>
                  <th align="left" bgcolor="#E6E6E6">Emisor</th>
         		  <th align="left" bgcolor="#E6E6E6">Destinatario</th>
				  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                </tr>';
	unset($repl);
	while( $row = pg_fetch_array ($result,$i )) {
		$trozos = explode(".", $row['nombre_archivo']); 
		$extension = end($trozos); 
		$nodes = $session->query($spacesStore, "@cm\\:name:\"".$row['id_archivo'].".$extension\"");
		$contentNode = $nodes[0]; 
		$contentData = $contentNode->cm_content;
		if ($contentData != null)
		{
			$link= $contentData->getUrl();
		}
		//test
		//$url_archivo=str_replace ( 'scjsgdoc-2.local' , '172.16.10.240', $link);
		
		//prod
		
		$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.160', $link);
		$id_aut=$row['id_autor'];
		$emisor=$_SESSION['usuarios'][$id_aut][1];
		$repl[]='<a href="javascript:void(0)" onclick="showDet(\''.$row['numero_expediente'].'\')"><input type="button" name="btnVer'.$i.'" value="Ver carpeta" /></a>';
		$docs.= '<tr style="background-color: #8DDA69;"><td><a href="javascript:void(0)" onclick="showDet(\''.$row['numero_expediente'].'\')"><input type="button" name="btnVer'.$i.'" value="Ver carpeta" /></a></td><td><span class="style2">'
			.$row['numero_documento'].'</span></td><td><span class="style2">'
			.$row['fecha_creacion'].'</span></td><td><span class="style2">'
			.$row['numero_expediente'].'</span></td><td><span class="style2">'
			.$row['descripcion'].'</span></td><td><span class="style2">'
			.$row['materia'].'</span></td><td><span class="style2">'
			.$emisor.'</span></td><td><span class="style2">'
			.$row['destinatarios'].'</span></td><td><span class="style2"><a href="'.$url_archivo.'" target="_blank"><input type="button" name="btnVer'.$i.'" value="Ver Doc" /></a>'
			.'</span></td></tr>';
		$i++;
	}
	if($i==0){
		$docs='';
	}else{
		$docs.='</table>';
		$_SESSION['docB']=str_replace($repl,'',$docs);
	}
	return $docs;

}

function getDepartamentos($idDep){
	include("connect.php");
	$sql = "SELECT id, descripcion FROM unidades WHERE descripcion != 'SGDOC' ORDER BY descripcion ASC";
	if($idDep!=''){
		$sql = "SELECT id, descripcion FROM unidades WHERE descripcion != 'SGDOC' AND id=idDep ORDER BY descripcion ASC";
	}
	$result = pg_query($dbconn, $sql);
	$i=0;
	$depas='';
	while( $row = pg_fetch_array ($result,$i )) {
		$depas[]= array($row['id_division'],$row['descripcion']);
		$i++;
	}
	return $depas;
}

function getUsuarios(){
	
	include("connect.php");
	$sql="SELECT 
		  personas.id, 
		  personas.nombres, 
		  personas.apellido_paterno, 
		  unidades.descripcion
		FROM 
		  public.cargos, 
		  public.personas, 
		  public.unidades
		WHERE 
		  personas.id_cargo = cargos.id AND
		  unidades.id = cargos.id_unidad_organizacional AND
		  id_cargo != 1 
		ORDER BY apellido_paterno ASC";
	
	$result = pg_query($dbconn, $sql);
	$i=0;
	$usuarios='';
	for($i=0;$i<pg_num_rows($result);$i++){
		$row = pg_fetch_array ($result,$i);
		$usuarios[$row['id']]= array($row['id'],$row['nombres'].' '.$row['apellido_paterno'], $row['descripcion']);
	}
	
	$_SESSION['usuarios']=$usuarios;
	session_write_close();
	//return $usuarios;
}

function getHisorial(){
	include("connect.php");
	include("security.php");
	$nCarp=limpiar_variable($_POST["nCarp"]);
	if(!valida_string($nCarp) && $nCarp!=''){
		return 'Error en num. de carpeta';
	}
	//$nCarp='E2274/2014';
	/**/$sql="SELECT 
		  expedientes.id, 
		  expedientes.numero_expediente,  
		  expedientes.fecha_despacho, 
		  expedientes.archivado, 
		  expedientes.id_emisor
		FROM 
		  public.expedientes
		WHERE 
		  expedientes.numero_expediente =  '$nCarp' AND id_padre IS NULL ORDER BY id ASC";
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	
	$idCarpeta=$row['id'];
	//$idCarpeta='29242';
	$raiz='<div class="mindmap"><div class="node node_root">
			<div class="node__text" onclick="verDetalle('.$idCarpeta.', -1)">'
			.$nCarp.'</br><span class="style4" style="line-height: 20px;">'
			.$idCarpeta.'</br>'
			.$_SESSION['usuarios'][$row['id_emisor']][1].'</br>'
			.$row['fecha_despacho'].'</span></div>
		  </div>';
	$raiz.='<ol class="children children_rightbranch">';
	$raiz.=getHijos($idCarpeta,'-1');
	$raiz.='</ol></div>';
	
	return $raiz;
		
}

function getHijos($idCarp, $idAbu){
	if($idCarp==''){
		return 'SIN DATO CARPETA';
	}
	$nodo='<ol class="children">';
	include("connect.php");
	$sql="SELECT 
		  expedientes.id, 
		  expedientes.numero_expediente, 
		  expedientes.fecha_creacion, 
		  expedientes.fecha_despacho, 
		  expedientes.fecha_acuse_recibo, 
		  expedientes.archivado, 
		  expedientes.id_padre, 
		  expedientes.copia, 
		  expedientes.archivado,
		  expedientes.id_destinatario_historico,
		  expedientes.id_emisor_historico
		FROM 
		  public.expedientes
		WHERE 
		  expedientes.id_padre =  $idCarp ORDER BY id ASC";
	$result = pg_query($dbconn, $sql);
	$i=0;
	while( $row = pg_fetch_array ($result,$i )) {
		$idCarpeta=$row['id'];
		$bakgr=$copia='';
		if($idAbu==$row['id_destinatario_historico'] || $idAbu==-1){
			$bakgr.='style="border-color: #C2E2EF; border-width: 2px;';
		}
		if($row['copia']=='t'){
			$bakgr='style="border-color: #8DDA69; border-width: 2px;';
			$copia='COPIA';
		}
		if($row['archivado']!=''){
			$bakgr='style="background-color: #C2E2EF; ';
			$copia='ARCHIVADO';
		}
		
		$bakgr.='"';
		$nodo.='<li class="children__item">
				<div  class="node" '.$bakgr.' onclick="verDetalle('.$idCarpeta.', '.$row['id_padre'].')">
					<div class="node__text">'
					.$row['id']
					.'</br>'.$_SESSION['usuarios'][$row['id_destinatario_historico']][1]
					.'</br>'.$row['fecha_acuse_recibo']
					//.'</br>'.$row['id_destinatario_historico']
					.'</br>'.$copia
					.'</div>
				</div>';
		$nodo.=getHijos($idCarpeta, $row['id_emisor_historico']);
		$nodo.='</li>';
		$i++;
	}
	unset($result);
	if($i==0){
		$nodo='';
	}else{
		$nodo.='</ol>';
	}
	
	return $nodo;
}

function getDetalleCarp(){
	include("connect.php");
	include("security.php");
	$idCarp=limpiar_variable($_POST["idPad"]);
	if(!valida_numero($idCarp) && $idCarp!=''){
		return 'Error en num. de carpeta';
	}
	$idCarpH=limpiar_variable($_POST["idHijo"]);
	if(!valida_numero($idCarpH) && $idCarpH!=''){
		return 'Error en num. de carpeta';
	}
	$archivos='';
	if($idCarp!='-1'){
		$sql="SELECT 
			  documentos.id, 
			  documentos.materia, 
			  documentos.observacion, 
			  expedientes.id, 
			  expedientes.copia, 
			  expedientes.fecha_acuse_recibo,
			  expedientes.id_destinatario_historico, 
			  expedientes.id_emisor_historico, 
			  documentos_expedientes.id_documento_referencia
			FROM 
			  public.documentos, 
			  public.documentos_expedientes, 
			  public.expedientes
			WHERE 
			  documentos_expedientes.id_documento = documentos.id AND
			  expedientes.id = documentos_expedientes.id_expediente AND
			  documentos_expedientes.id_documento_referencia = $idCarp
			ORDER BY
			  documentos.id ASC";
		$result = pg_query($dbconn, $sql);
		$row = pg_fetch_array ($result,0);
		$sql2="SELECT 
			  expedientes.fecha_acuse_recibo,
			  expedientes.copia,
			  expedientes.archivado,
			  expedientes.id_destinatario_historico, 
			  expedientes.id_emisor_historico
			FROM 
			  public.expedientes
			WHERE 
			  expedientes.id = $idCarpH";
		$result2 = pg_query($dbconn, $sql2);
		$row2 = pg_fetch_array ($result2,0);
		$emisor=$row2['id_emisor_historico'];
		$receptor=$row2['id_destinatario_historico'];
		if($emisor==''){
			$emisor=$row['id_emisor_historico'];
			$receptor=$row['id_destinatario_historico'];
		}
		if($row2['copia']=='t'){
			$copia='(COPIA)';
		}
		if($row2['archivado']!=''){
			$copia.='(Archivado el '.$row2['archivado'].')';
		}
		$det_1='<tr>
                <td>A:</td>
                <td>'.$_SESSION['usuarios'][$receptor][1].'</td>
              </tr>
              <tr>
                <td>Fecha recepci&oacute;n:</td>
                <td>'.$row2['fecha_acuse_recibo'].'</td>
              </tr>';
		$archivos=getDocsAdjuntos($idCarp, '');
	}else{
		$sql="SELECT 
		  expedientes.id, 
		  documentos.materia, 
		  documentos.observacion, 
		  expedientes.id_emisor,
		  expedientes.fecha_despacho,
		  expedientes.numero_expediente
		FROM 
		  public.documentos, 
		  public.documentos_expedientes, 
		  public.expedientes
		WHERE 
		  documentos_expedientes.id_documento = documentos.id AND
		  expedientes.id = documentos_expedientes.id_expediente AND
		  documentos_expedientes.id_documento_referencia = -1 AND
		  expedientes.id = $idCarpH
		ORDER BY
		  documentos.id ASC";
		$result = pg_query($dbconn, $sql);
		$row = pg_fetch_array ($result,0);
		$emisor=$row['id_emisor'];
		$copia.='(Creado el '.$row['fecha_despacho'].')';
		$det_1='<tr>
                <td>Fecha creaci&oacute;n:</td>
                <td>'.$row['fecha_despacho'].'</td>
              </tr>';
		$archivos=getDocsAdjuntos('', $row['numero_expediente']);
		
	}
	//unset($result);
	$detalle='<h4 class="style1 style3" >Detalle ID '.$idCarpH.'</h4>
			<table border="0" cellspacing="1" cellpadding="0">
			  <tr>
                <td>ID:</td>
                <td>'.$idCarpH.' '.$copia.'</td>
              </tr>
              <tr>
                <td>De:</td>
                <td>'.$_SESSION['usuarios'][$emisor][1].'</td>
              </tr>
			  '.$det_1.'
              <tr>
                <td>Materia:</td>
                <td>'.$row['materia'].'</td>
              </tr>
              <tr>
                <td>Observaciones:</td>
                <td>'.$row['observacion'].'</td>
              </tr>
			  <tr>
                <td>Documentos adjuntos:</td>
                <td>'.$archivos.'</td>
              </tr>
            </table>';
	return $detalle;

}

function getDocsAdjuntos($idCarp, $nCarp){
	include("connect.php");
	$cond=" expedientes.id=$idCarp AND";
	if($nCarp!=''){
		$cond=" expedientes.numero_expediente = '$nCarp' AND";
	}
	$sql="SELECT distinct
		  documentos.id_archivo,
		  archivos.nombre_archivo,
		  documentos.numero_documento,
		  documentos.fecha_creacion,
		  expedientes.id,
		  expedientes.id_emisor
		FROM 
		  public.documentos, 
		  public.expedientes, 
		  public.documentos_expedientes, 
		  public.archivos
		WHERE 
		  documentos.id_archivo = archivos.id AND
		  expedientes.id = documentos_expedientes.id_expediente AND
		  documentos_expedientes.id_documento = documentos.id AND
		  $cond
		  archivos.nombre_archivo IS NOT NULL";
	$result = pg_query($dbconn, $sql);
	$i=0;
	$nRows = pg_num_rows($result);
	//return $idCarp;
	if($nRows=='0'){
		return 'Sin archivos';
	}
	$docs='<ul>';
	require_once "Alfresco/Service/Repository.php";
	require_once "Alfresco/Service/Session.php";
	require_once "Alfresco/Service/SpacesStore.php";

	// Specify the connection details
	//test
	//$repositoryUrl = "http://172.16.10.240:8080/alfresco/api";
	
	//prod
	$repositoryUrl = "http://172.16.10.160:8080/alfresco/api";
	$userName = "sgdoc";
	$password = "21382138"; 
	
	// Authenticate the user and create a session
	$repository = new Repository($repositoryUrl);
	$ticket = $repository->authenticate($userName, $password);
	$session = $repository->createSession($ticket);
	
	// Create a reference to the 'SpacesStore'
	$spacesStore = new SpacesStore($session);
	$to='por';
	if($nCarp!=''){
		$to='para';
	}
	$docs='<ul>';
	
	for($i=0;$i<pg_num_rows($result);$i++){	
	//while( $row = pg_fetch_array ($result,$i )) {
		$row = pg_fetch_array ($result,$i );
		$trozos = explode(".", $row['nombre_archivo']); 
		$extension = end($trozos); 
		$nodes = $session->query($spacesStore, "@cm\\:name:\"".$row['id_archivo'].".$extension\"");
		$contentNode = $nodes[0]; 
		$contentData = $contentNode->cm_content;
		if ($contentData != null)
		{
			$link= $contentData->getUrl();
		}/**/
		//test
		//$url_archivo=str_replace ( 'scjsgdoc-2.local' , '172.16.10.240', $link);
		
		//prod
		$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.160', $link);
		$docs.='<li><a href="'.$url_archivo.'" target="_blank">'
		.$row['nombre_archivo'].'</a> (Agregado '.$to.' '.$_SESSION['usuarios'][$row['id_emisor']][1]
		.' en ID '
		.$row['id'].' el '.$row['fecha_creacion'].')</li>';
		//$i++;
	}

	unset($result);
	$docs.='</ul>';
	return $docs;
}

function getDocsAdjuntos2($idCarp, $nCarp){
	include("connect.php");
	$cond=" expedientes.id=$idCarp AND";
	if($nCarp!=''){
		$cond=" expedientes.numero_expediente = '$nCarp' AND";
	}
	$sql="SELECT distinct
		  documentos.id_archivo,
		  archivos.nombre_archivo,
		  documentos.numero_documento,
		  documentos.fecha_creacion,
		  expedientes.id,
		  expedientes.id_emisor
		FROM 
		  public.documentos, 
		  public.expedientes, 
		  public.documentos_expedientes, 
		  public.archivos
		WHERE 
		  documentos.id_archivo = archivos.id AND
		  expedientes.id = documentos_expedientes.id_expediente AND
		  documentos_expedientes.id_documento = documentos.id AND
		  $cond
		  archivos.nombre_archivo IS NOT NULL";
	$result = pg_query($dbconn, $sql);
	$i=0;
	$nRows = pg_num_rows($result);
	//return $idCarp;
	if($nRows=='0'){
		return 'Sin archivos';
	}
	$docs=' - ';	

	$to='por';
	if($nCarp!=''){
		$to='para';
	}
	$docs=' - ';
	
	for($i=0;$i<pg_num_rows($result);$i++){	
	//while( $row = pg_fetch_array ($result,$i )) {
		$row = pg_fetch_array ($result,$i );
		$trozos = explode(".", $row['nombre_archivo']); 
		$extension = end($trozos); 
		$nodes ='';
		$link= '';
	
		$docs.=' / '.$row['nombre_archivo'].'(Agregado '.$to.' '.$_SESSION['usuarios'][$row['id_emisor']][1].' en ID '.$row['id'].' el '.$row['fecha_creacion'].')';
		//$i++;
	}

	unset($result);
	$docs.=' - ';
	return $docs;
}

if(isset($_POST["tokenB"])){
	if($_POST["checkboxCarp"]=='carp'){
		//echo $_POST["checkboxCarp"];
		echo buscar();
	}elseif($_POST["checkboxDoc"]=='doc'){
		echo buscarDocumentos();
	}
	
}
if(isset($_POST["func"]) && $_POST["func"]=='getHisto'){
	echo getHisorial();
		
}
if(isset($_POST["func"]) && $_POST["func"]=='getDet'){
	echo getDetalleCarp();
		
}
if(isset($_POST["btnExpC"])){
	$nombreArchivo = date('Ymdhis').'_Carpetas.xls';
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=$nombreArchivo");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo utf8_decode($_SESSION['carpB']);
		
}
if(isset($_POST["btnExpD"])){
	$nombreArchivo = date('Ymdhis').'_Documentos.xls';
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=$nombreArchivo");
	header("Pragma: no-cache");
	header("Expires: 0");	
	echo utf8_decode($_SESSION['docB']);
}
?>