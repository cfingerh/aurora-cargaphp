<?php 
session_start();
function buscar(){
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
	$remit=limpiar_variable($_POST["cbxRem"]);
	if(!valida_string($remit) && $remit!=''){
		return 'Error en remitente';
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
	$estado=limpiar_variable($_POST["radioEstado"]);
	if(!valida_string($estado) && $estado!=''){
		return 'Error en estado';
	}
	
	$select="SELECT DISTINCT expedientes.numero_expediente ";
	/*$select="SELECT DISTINCT 
				expedientes.numero_expediente ";*/
	$from="FROM 
			  public.documentos, 
			  public.documentos_expedientes, 
			  public.expedientes ";
		  
	$where="WHERE 
			  documentos.id = documentos_expedientes.id_documento AND
			  expedientes.id = documentos_expedientes.id_expediente AND expedientes.numero_expediente != 'E8087/2015'";
	if($nCarp!=''){
		$where.="AND expedientes.numero_expediente ~* '$nCarp' ";
	}
	if($nDoc!=''){
		$where.="AND documentos.numero_documento  ~* '$nDoc' ";
	}
	if($nomDoc!=''){
		$where.="AND documentos.observacion  ~* '$nomDoc' ";
	}
	if($remit!=''){
		$where.="AND documentos.emisor  ~* '$remit' ";
	}
	$mat='';
	if($materia!=''){
		$mat=explode(' ',$materia);
		foreach($mat as $val){
			$where.="AND documentos.materia  ~* '$val' ";
		}
		
	}
	if($div!=''){
		$from.=",public.personas, public.cargos ";
		$where.="AND personas.id = documentos.id_autor AND
				  cargos.id = personas.id_cargo AND
				  cargos.id_unidad_organizacional = $div ";
	}
	if($de!=''){
		$where.="AND expedientes.id_emisor_historico = $de ";
	}
	if($a!=''){
		$where.="AND expedientes.id_destinatario_historico = $a ";
	}
	if($desde!=''){
		$where.="AND expedientes.fecha_despacho_historico >= '$desde' ";
	}
	if($hasta!=''){
		$where.="AND expedientes.fecha_despacho_historico <= '$hasta' ";
	}

	$criterio ='ORDER BY numero_expediente ASC LIMIT 200';
	$sql="SELECT DISTINCT 
				expedientes.id, 
				expedientes.fecha_despacho, 
				expedientes.id_emisor, 
				expedientes.copia,
				expedientes.numero_expediente
			FROM 
				public.documentos, public.documentos_expedientes, public.expedientes 
			WHERE 
				documentos.id = documentos_expedientes.id_documento AND 
				expedientes.id = documentos_expedientes.id_expediente AND 
				expedientes.id_padre IS NULL  AND
				expedientes.numero_expediente IN (".$select.$from.$where.$criterio.')
			ORDER BY fecha_despacho DESC';
	$result = pg_query($dbconn, $sql);
	$i=0;
	$carpetas='<table id="resultadoC"><tr>
                  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                  <th align="left" bgcolor="#E6E6E6">N&deg; Carpeta </th>
                  <th align="left" bgcolor="#E6E6E6">Materia</th>
                  <th align="left" bgcolor="#E6E6E6">Fecha despacho </th>
                  <th align="left" bgcolor="#E6E6E6">Remitente SCJ</th>
				  <th align="left" bgcolor="#E6E6E6">Emisor</th>
				  <th align="left" bgcolor="#E6E6E6">Usuario(s) actual(es)</th>
                  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                </tr>
	';
	unset($repl);
	while( $row = pg_fetch_array ($result,$i )) {
		$idCarpeta=$row['id'];
		
		$esArch=null;
		//revisa los archivados
		if($estado!='t'){
			$esArch=substr(getIsArchivado($idCarpeta), 0, -3);
			if($estado=='a'){
				if(strlen($esArch)>0){
					$i++;
					continue;
				}
			}
			if($estado=='o'){
				if(strlen($esArch)<=2){
					$i++;
					continue;
				}
			}
		}
		
		$sql2="SELECT documentos.materia, documentos.id	, documentos.emisor	
				FROM public.documentos,  public.documentos_expedientes
				WHERE 
				  documentos_expedientes.id_documento = documentos.id AND
				 documentos_expedientes.id_expediente = $idCarpeta ORDER BY id ASC LIMIT 1";
		$result2 = pg_query($dbconn, $sql2);
		$row2 = pg_fetch_array ($result2,0);
		$mat=$row2['materia'];
		$tipoC='DIRECTO';
		$remit=$row2['emisor'];
		if(!$row['copia']) $tipoC='COPIA';
		$id_em=$row['id_emisor'];
		$repl[]='<a href="javascript:void(0)" onclick="showDet(\''.$row['numero_expediente'].'\')"><input type="button" name="btnVer'.$i.'" value="Ver Carpeta" /></a>';
		$emisor=$_SESSION['usuarios'][$id_em][1];
		$carpetas.= '<tr style="background-color: #8DDA69;"><td><a href="javascript:void(0)" onclick="showDet(\''.$row['numero_expediente'].'\')"><input type="button" name="btnVer'.$i.'" value="Ver Carpeta" /></a></td><td><span class="style2">'
			.$row['numero_expediente'].'</span></td><td><span class="style2">'
			.$mat.'</span></td><td><span class="style2">'
			.$row['fecha_despacho'].'</span></td><td><span class="style2">'
			.$emisor.'</span></td><td><span class="style2">'
			.$remit.'</span></td><td><span class="style2">'
			.$esArch.'</span></td><td><span class="style2">'
			.'</span></td></tr>';
		$i++;
	}
	if($i==0){
		$carpetas='';
	}else{
		$carpetas.='</table>';
		$_SESSION['carpB']=str_replace($repl,'',$carpetas);
	}
	return $carpetas;
	
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
			  archivos.cms_id,			  
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

	$criterio ='ORDER BY numero_expediente ASC LIMIT 200';
	$sql=$select.$from.$where.$criterio;
	//return $sql;
	$result = pg_query($dbconn, $sql);
	$i=0;
	
	require_once "Alfresco/Service/Repository.php";
	require_once "Alfresco/Service/Session.php";
	require_once "Alfresco/Service/SpacesStore.php";

	// Specify the connection details
	$repositoryUrl = "http://172.16.10.136:8080/alfresco/api";
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
		$nodes = $session->query($spacesStore, "@cm\\:name:\"".$row['archivo_id'].".$extension\"");
		
		$contentNode = $nodes[0]; 
		$contentData = $contentNode->cm_content;
		if ($contentData != null)
		{
			$link= $contentData->getUrl();
		}else{
			$link= "ss";
			
		}
		//test
		//$url_archivo=str_replace ( 'scjsgdoc-2.local' , '172.16.10.240', $link);
		
		//prod
		
		$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.136', $link);
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
		$depas[]= array($row['id'],$row['descripcion']);
		$i++;
	}
	return $depas;
}

function getUsuarios($idUser){
	include("connect.php");
	$sql = "SELECT id, nombres, apellido_paterno FROM personas WHERE usuario != 'admin' ORDER BY nombres ASC";
	if($idUser!=''){
		$sql = "SELECT id, nombres, apellido_paterno FROM personas WHERE usuario != 'admin' AND id=$idUser ORDER BY nombres ASC";
	}
	$result = pg_query($dbconn, $sql);
	$i=0;
	$usuarios='';
	while( $row = pg_fetch_array ($result,$i )) {
		$usuarios[$row['id']]= array($row['id'],$row['nombres'].' '.$row['apellido_paterno']);
		$i++;
	}
	$_SESSION['usuarios']=$usuarios;
	session_write_close();
	return $usuarios;
}

function getRemitente($idRem){
	include("connect.php");
	$sql = "SELECT id, descripcion FROM dependencias_externas ORDER BY descripcion ASC";
	if($idRem!=''){
		$sql = "SELECT id, descripcion FROM dependencias_externas WHERE id=$idRem ORDER BY descripcion ASC";
	}
	$result = pg_query($dbconn, $sql);
	$i=0;
	$remitentes='';
	while( $row = pg_fetch_array ($result,$i )) {
		$remitentes[$row['id']]= array($row['id'],$row['descripcion']);
		$i++;
	}
	return $remitentes;
}

function getIsArchivado($idCarp){
	include("connect.php");
	$sql = "SELECT id, copia FROM expedientes WHERE id_padre = $idCarp AND copia=false";
	$result = pg_query($dbconn, $sql);
	$valores='';
	if(pg_num_rows($result)!=0){
		$i=0;
		//echo $idCarp.' es padre</br>';
		//if(!$row['copia']){
			while( $row = pg_fetch_array ($result,$i )) {
				//echo 'entra a hijo '.$row['id'].'</br>';
				$valores.=getIsArchivado($row['id']);
				$i++;
			}
		//}
		
	}else{
		//echo 'revisando hijo '.$idCarp.'</br>';
		$sql2 = "SELECT archivado, id_emisor FROM expedientes WHERE id = $idCarp";
		$result2 = pg_query($dbconn, $sql2);
		$row2 = pg_fetch_array ($result2,0);
		//echo 'es copia '.$idCarp;
		if($row2['copia']==false){
			if($row2['archivado']==''){
			//echo $idCarp.' no archivado</br>';
				return $_SESSION['usuarios'][$row2['id_emisor']][1].' - ';
			}else{
				//echo $idCarp.' archivado</br>';
				return;
			}
		}else{
			return;
		}
		
	}
	//echo 'hijos de '.$idCarp.' no archivados: '.$valores.'</br>';
	return $valores;
	
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
	$archivos=$copia='';
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
		  archivos.cms_id,
		  documentos.numero_documento,
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
	$repositoryUrl = "http://172.16.10.136:8080/alfresco/api";
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
	$link=null;
	$docs='<ul>';
	while( $row = pg_fetch_array ($result,$i )) {
		$trozos = explode(".", $row['nombre_archivo']); 
		$extension = end($trozos); 
		$queryAlf='@cm\\:name:"*'.$row['id_archivo'].'*"';
		
		//$queryAlf='@cm\\:name:"*102330*"';
		
		//$queryAlf='@cm\\:name:"'.trim($row['id_archivo']).".$extension\"";
		
		$nodes = $session->query($spacesStore, $queryAlf);
		//$nodes = $session->query($spacesStore, "@cm\\:name:\"64296.PDF\"");
		$contentNode = $nodes[0]; 
		$contentData = $contentNode->cm_content;
		$fp = fopen("/../../LOG_SGDOC.txt", "a");
		fputs($fp, 'Busqueda de archivo ID '.$row['id_archivo'].$extension. PHP_EOL);
		fclose($fp);/**/
		if ($contentData != null)
		{
			$link= $contentData->getUrl();
		}else{	
			$repositoryUrl2 = "http://172.16.10.116:8080/alfresco/api";
			$userName = "sgdoc";
			$password = "21382138"; 
			
			// Authenticate the user and create a session
			$repository2 = new Repository($repositoryUrl2);
			$ticket2 = $repository2->authenticate($userName, $password);
			$session2 = $repository2->createSession($ticket2);
			// Create a reference to the 'SpacesStore'
			$spacesStore2 = new SpacesStore($session2);
			
			$nodes2 = $session2->query($spacesStore, $queryAlf);
			//$nodes = $session->query($spacesStore, "@cm\\:name:\"64296.PDF\"");
			$contentNode2 = $nodes2[0]; 
			$contentData2 = $contentNode2->cm_content;
			//$link= $contentData2->getUrl();/**/
			
		}
		$fp = fopen("../../LOG_SGDOC.txt", "a");
		fputs($fp, 'Link de archivo ID '.$row['id_archivo'].$extension.' [LINK]: '.$link.' [getDocsAdjuntos] ['.date('Y-m-d H:i:s').']'. PHP_EOL);
		fclose($fp);/**/
		//test
		//$url_archivo=str_replace ( 'scjsgdoc-2.local' , '172.16.10.240', $link);
		
		//prod
		$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.136', $link);
		$url_archivo=str_replace ( 'scjsgdoc-2.local' , '172.16.10.116', $link);
		$docs.='<li><a href="'.$url_archivo.'" target="_blank">'
		.$row['nombre_archivo'].'</a> (Agregado '.$to.' '.$_SESSION['usuarios'][$row['id_emisor']][1]
		.' en ID '
		.$row['id'].')</li>';
		$i++;
	}
	unset($result);
	$docs.='</ul>';
	return $docs;
}

function getTags(){
	include("connect.php");
	$tags= array();
	$sql="SELECT DISTINCT
		  documentos.id, 
		  documentos.observacion, 
		  documentos.materia
		FROM 
		  public.documentos
		WHERE 
		  documentos.observacion ~* '#[a-zA-Z]+' OR
		  documentos.materia ~* '#[a-zA-Z]+'";
	$result = pg_query($dbconn, $sql);
	$i=0;
	unset($_SESSION['tags']);
	while( $row = pg_fetch_array ($result,$i )) {
		$aux = explode(' ', $row['materia']);
		for($j=0;$j<count($aux);$j++){
			$pos = strpos($aux[$j], '#');
			if($pos !== false){
				$tags[]=preg_replace('([^#A-Za-z0-9])', '',trim($aux[$j])).'**mat';
			}
		}
		
		$aux = explode(' ', $row['observacion']);
		for($j=0;$j<count($aux);$j++){
			$pos = strpos($aux[$j], '#');
			if($pos !== false){
				$tags[]=preg_replace('([^#A-Za-z0-9])', '',trim($aux[$j])).'**obs';
			}
		}
		$i++;
	}
	$_SESSION['tags'] = array_count_values($tags);

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