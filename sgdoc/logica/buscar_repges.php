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
	$desde=limpiar_variable($_POST["from"]);
	if(!valida_string($desde) && $desde!=''){
		return 'Error en fecha desde';
	}
	$hasta=limpiar_variable($_POST["to"]);
	if(!valida_string($hasta) && $hasta!=''){
		return 'Error en fecha hasta';
	}
	
	if($nCarp!=''){
		$where.="AND expedientes.numero_expediente ~* '$nCarp' ";
	}
	if($remit!=''){
		$where.="AND documentos.emisor  ~* '$remit' ";
	}
	$whereB=null;
	if($div!=''){
		$whereB="unidades.id = $div AND";
	}
	if($de!=''){
		//$where.="AND expedientes.id_emisor = $de ";
		$where2=" expedientes.id_emisor_historico = $de OR ";
	}
	
	/*if($estado!=''){
		if($estado=='a'){
		
		}
		$where.="AND expedientes.fecha_despacho_historico <= '$hasta' ";
	}*/
	$_SESSION['paramB']['fechaini']=$desde;
	$_SESSION['paramB']['fechafin']=$hasta;
	
	$sql="SELECT 
		  personas.id AS idper, 
		  personas.nombres, 
		  personas.apellido_paterno, 
		  personas.usuario, 
		  unidades.id, 
		  unidades.descripcion
		FROM 
		  public.cargos, 
		  public.personas, 
		  public.unidades
		WHERE 
		  personas.id_cargo = cargos.id AND
		  unidades.id = cargos.id_unidad_organizacional AND
		  $whereB 
		  personas.vigente = true AND
		  id_cargo != 1 
		  order by unidades.descripcion, personas.apellido_paterno ASC;";
		  
	//return $sql;
	
	$result = pg_query($dbconn, $sql);
	$i=0;
	$carpetas='<table id="resultadoC"><tr>
                  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                  <th align="left" bgcolor="#E6E6E6">Persona</th>
                  <th align="left" bgcolor="#E6E6E6">Unidad / Divisi&oacute;n</th>
                  <th align="left" bgcolor="#E6E6E6">Cantidad en bandeja</th>
				  <th align="left" >&nbsp;</th>
                </tr>
	';
	unset($repl);
	$_SESSION['usuario']=null;
	$contTot =$contUn=0;
	$unidad=$contTotUn='';
	//while( $row = pg_fetch_array ($result,$i )) {
	for($i=0; $i<pg_num_rows($result);$i++){
		
		$row = pg_fetch_array ($result,$i );
		if($row['idper']!=$de && $de!=''){
			continue;
		}
		$_SESSION['usuario'][count($_SESSION['usuario'])][0]=$row['idper'];
		$_SESSION['usuario'][count($_SESSION['usuario'])-1][1]=$row['usuario'];
		
		//revisa los archivados		
		if($i==0){
			$unidad=$row['descripcion'];
		}
		if($unidad!=$row['descripcion'] && $de==''){
			
			$contTotUn=$contUn;
			$carpetas.='<tr><td align="left" >&nbsp;</td>
                  <td align="left" >&nbsp;</td>
                  <td align="left" bgcolor="#E6E6E6">Pendientes '.$unidad.':</td>
                  <td align="left" bgcolor="#E6E6E6">'.$contUn.'</td>
				  <td align="left" >&nbsp;</td></tr>';	
			$unidad=$row['descripcion'];
			$contUn=0;	
		}
		$contPend =0;
		//echo $row['idper'];
		$contPend = getContPend($row['idper'],$desde, $hasta);
		$contTot+=$contPend;
		$contUn+=$contPend;
		$nombre=$row['nombres'].' '.$row['apellido_paterno'];
		$repl[]='<a href="javascript:void(0)" onclick="getCarpetas(\''.$row['idper'].'\', \''.$nombre.'\')"><input type="button" name="btnVer'.$i.'" value="Ver pendientes" /></a>';
		$carpetas.= '<tr style="background-color: #A6E289;"><td><a href="javascript:void(0)" onclick="getCarpetas(\''.$row['idper'].'\', \''.$nombre.'\')"><input type="button" name="btnVer'.$i.'" value="Ver pendientes" /></a></td><td><span class="style2">'
			.$nombre.'</span></td><td><span class="style2">'
			.$row['descripcion'].'</span></td><td><span class="style2">'
			.$contPend.'</span></td>'
			.'</tr>';
		//$i++;
	}
	if($div=='' && $de==''){
			$contTotUn=$contUn;
			$contUn=0;	
			$carpetas.='<tr><td align="left" >&nbsp;</td>
                  <td align="left" >&nbsp;</td>
                  <td align="left" bgcolor="#E6E6E6">Pendientes '.$unidad.':</td>
                  <td align="left" bgcolor="#E6E6E6">'.$contTotUn.'</td>
				  <td align="left" >&nbsp;</td></tr>';		
	}
	$carpetas.='<tr><td align="left" >&nbsp;</td>
                  <td align="left" >&nbsp;</td>
                  <td align="left" bgcolor="#E6E6E6">Total pendientes :</td>
                  <td align="left" bgcolor="#E6E6E6">'.$contTot.'</td>
				  <td align="left" >&nbsp;</td></tr>';
	if($i==0){
		$carpetas='';
	}else{
		$carpetas.='</table>';
		$_SESSION['carpB']=str_replace($repl,'',$carpetas);
	}
	$_SESSION['carpB']=$carpetas;
	return $carpetas;
	
}

function getContPend($idUser, $fechaIni, $fechaFin){
	/*$sql="SELECT 
		 	count(DISTINCT expedientes.id)
		 FROM 
  			public.expedientes
		WHERE expedientes.id_emisor =$idUser AND 
			expedientes.archivado IS NULL AND 
			expedientes.copia = false AND 
			expedientes.id_emisor_historico IS NOT NULL AND
			expedientes.numero_expediente IS NOT NULL 
			AND expedientes.fecha_despacho_historico >= '2016-01-01'";
	if($fechaIni!=''){
		$sql.="
 				AND expedientes.fecha_despacho_historico >= '$fechaIni' ";
	}
	if($fechaFin!=''){
		$sql.="
 				AND expedientes.fecha_despacho_historico <= '$fechaFin' ";
	}
	$sql.="
 			AND expedientes.id NOT IN (SELECT distinct
				  expedientes.id_padre
				FROM 
				  public.expedientes
				WHERE 
				  expedientes.id_emisor_historico = $idUser OR
				  expedientes.id_emisor_historico = 303 OR
				  expedientes.id_emisor_historico = 750 OR
				  expedientes.id_emisor_historico = 1354);";*/
				  
	$sql="
			SELECT 
			count(DISTINCT tab1.id) 
			FROM 
			public.expedientes  as tab1
			WHERE 
			tab1.id_emisor =$idUser	
			AND tab1.archivado IS NULL 
			AND tab1.copia = false 
			AND tab1.id_emisor_historico IS NOT NULL 
			AND tab1.numero_expediente IS NOT NULL 
			AND tab1.fecha_despacho_historico >= '2016-01-01' 
			";
			
	if($fechaIni!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico >= '$fechaIni' ";
	}
	if($fechaFin!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico <= '$fechaFin' ";
	}
	
	$sql.="	AND NOT EXISTS (SELECT distinct expedientes.id_padre 
			FROM 
			public.expedientes 
			WHERE tab1.id  = expedientes.id_padre
			order by expedientes.id_padre ASC
			)
			AND EXISTS (SELECT id_expediente FROM documentos_expedientes WHERE documentos_expedientes.id_expediente=tab1.id);";
	//return $sql;
	include("connect.php");
	$result = pg_query($dbconn, $sql);
	$row = pg_fetch_array ($result,0);
	return $row['count'];
}

function getReportePend(){
	$detCarp='<table id="resultadoDetC"><tr>
                  <th align="left" >N&deg; Carpeta</th>
                  <th align="left" >Materia</th>
                  <th align="left" >Remitente</th>
				  <th align="left" >Usuario</th>
				  <th align="left" >&nbsp;</th>
                </tr>';
	//for($i=0;i<count($_SESSION['usuario']);$i++){
	for($i=0;$i<count($_SESSION['usuario']);$i++){
		$detCarp.=getDetalleCarpTotal($_SESSION['usuario'][$i][0], $_SESSION['usuario'][$i][1]);
	}

	$detCarp.='</table>';
	return $detCarp;

}

function getDetalleCarpTotal($idUser, $nomUser){
	$desde=$_SESSION['paramB']['fechaini'];
	$hasta=$_SESSION['paramB']['fechafin'];
	$detCarp=null;
	
	
	$sql="SELECT DISTINCT
			tab1.id,
		 	tab1.numero_expediente
		 FROM 
  			public.expedientes as tab1
		WHERE tab1.id_emisor =$idUser AND 
			tab1.archivado IS NULL AND 
			tab1.copia = false AND 
			tab1.id_emisor_historico IS NOT NULL AND
			tab1.numero_expediente IS NOT NULL 
			AND tab1.fecha_despacho_historico >= '2016-01-01'";
	if($desde!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico >= '$desde' ";
	}
	if($hasta!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico <= '$hasta' ";
	}
	$sql.="AND NOT EXISTS (SELECT distinct expedientes.id_padre 
			FROM 
			public.expedientes 
			WHERE tab1.id  = expedientes.id_padre
			order by expedientes.id_padre ASC
			) 
			AND EXISTS (SELECT id_expediente FROM documentos_expedientes WHERE documentos_expedientes.id_expediente=tab1.id) 
			order by numero_expediente ASC;";

	//return $sql;
	include("connect.php");
	$result = pg_query($dbconn, $sql);
	
	for($i=0;$i<pg_num_rows($result);$i++){
		$row = pg_fetch_array ($result,$i);
		$ncarp= $row['numero_expediente'];
		/********busca el detalle de la carpeta*********/
		$sql2="SELECT distinct documentos.materia, documentos.id, documentos.emisor		
				FROM public.documentos,  public.documentos_expedientes, public.expedientes 
				WHERE 
					expedientes.id = documentos_expedientes.id_expediente AND 
				  documentos_expedientes.id_documento = documentos.id AND
				 expedientes.numero_expediente = '$ncarp' ORDER BY id ASC LIMIT 2";
		$result2 = pg_query($dbconn, $sql2);
		$mat=$remit='';
		if(pg_num_rows($result2)>0){
			$row2 = pg_fetch_array ($result2,0);
			$mat=$row2['materia'];
			$remit=$row2['emisor'];
			if($mat==''){
				$row2 = pg_fetch_array ($result2,1);
				$mat=$row2['materia'];
				$remit=$row2['emisor'];
			}
		}
		
		/****************/
		
		
		$detCarp.='<tr >
                  <td ><span class="style2">'.$row['numero_expediente'].'</span></td>
				  <td width=60%><span class="style2">'.$mat.'</span></td>
				  <td ><span class="style2">'.$remit.'</span></td>
				  <td ><span class="style2">'.$nomUser.'</span></td>
                </tr>';
	}
	
	return $detCarp;
}

function getDetalleCarp($idUser){
	$desde=$_SESSION['paramB']['fechaini'];
	$hasta=$_SESSION['paramB']['fechafin'];
	
	
	$sql="SELECT DISTINCT
			tab1.id,
		 	tab1.numero_expediente
		 FROM 
  			public.expedientes as tab1
		WHERE tab1.id_emisor =$idUser AND 
			tab1.archivado IS NULL AND 
			tab1.copia = false AND 
			tab1.id_emisor_historico IS NOT NULL AND
			tab1.numero_expediente IS NOT NULL 
			AND tab1.fecha_despacho_historico >= '2016-01-01'";
	if($desde!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico >= '$desde' ";
	}
	if($hasta!=''){
		$sql.="
 				AND tab1.fecha_despacho_historico <= '$hasta' ";
	}
	$sql.="AND NOT EXISTS (SELECT distinct expedientes.id_padre 
			FROM 
			public.expedientes 
			WHERE tab1.id  = expedientes.id_padre
			order by expedientes.id_padre ASC
			) order by numero_expediente ASC;";

	
	/*
	$sql="SELECT DISTINCT
			expedientes.id,
		 	expedientes.numero_expediente
		 FROM 
  			public.expedientes
		WHERE expedientes.id_emisor =$idUser AND 
			expedientes.archivado IS NULL AND 
			expedientes.copia = false AND 
			expedientes.id_emisor_historico IS NOT NULL AND
			expedientes.numero_expediente IS NOT NULL 
			AND expedientes.fecha_despacho_historico >= '2016-01-01'";
	if($desde!=''){
		$sql.="
 				AND expedientes.fecha_despacho_historico >= '$desde' ";
	}
	if($hasta!=''){
		$sql.="
 				AND expedientes.fecha_despacho_historico <= '$hasta' ";
	}
	$sql.="
 			AND expedientes.id NOT IN (SELECT distinct
				  expedientes.id_padre
				FROM 
				  public.expedientes
				WHERE 
				  expedientes.id_emisor_historico = $idUser OR
				  expedientes.id_emisor_historico = 303 OR
				  expedientes.id_emisor_historico = 750 OR
				  expedientes.id_emisor_historico = 1354) order by numero_expediente ASC;";
				  */
	

	//return $sql;
	include("connect.php");
	$result = pg_query($dbconn, $sql);
	$detCarp='<table id="resultadoDetC"><tr>
                  <th align="left" bgcolor="#E6E6E6">&nbsp;</th>
                  <th align="left" bgcolor="#E6E6E6">N&deg; Carpeta</th>
                  <th align="left" bgcolor="#E6E6E6">Materia</th>
                  <th align="left" bgcolor="#E6E6E6">Remitente</th>
				  <th align="left" >&nbsp;</th>
                </tr>';
	for($i=0;$i<pg_num_rows($result);$i++){
		$row = pg_fetch_array ($result,$i);
		$ncarp= $row['numero_expediente'];
		/********busca el detalle de la carpeta*********/
		$sql2="SELECT distinct documentos.materia, documentos.id, documentos.emisor		
				FROM public.documentos,  public.documentos_expedientes, public.expedientes 
				WHERE 
					expedientes.id = documentos_expedientes.id_expediente AND 
				  documentos_expedientes.id_documento = documentos.id AND
				 expedientes.numero_expediente = '$ncarp' ORDER BY id ASC LIMIT 2";
		$result2 = pg_query($dbconn, $sql2);
		$row2 = pg_fetch_array ($result2,0);
		$mat=$row2['materia'];
		$remit=$row2['emisor'];
		if($mat==''){
			$row2 = pg_fetch_array ($result2,1);
			$mat=$row2['materia'];
			$remit=$row2['emisor'];
		}
		/****************/
		
		
		$detCarp.='<tr style="background-color: #A6E289;">
					<td ><a href="javascript:void(0)" onclick="showDet(\''.$ncarp.'\')"><input type="button" name="btnVer'.$i.'" value="Ver Carpeta" /></a></td>
                  <td ><span class="style2">'.$row['numero_expediente'].'</span></td>
				  <td width=60%><span class="style2">'.$mat.'</span></td>
				  <td ><span class="style2">'.$remit.'</span></td>
                </tr>';
	}
	if($i==0){
		$detCarp='';
	}else{
		$detCarp.='</table>';
	}/**/
	$_SESSION['carpU']=$detCarp;
	return $detCarp;
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
	$sql = "SELECT id, nombres, apellido_paterno FROM personas WHERE usuario != 'admin' AND vigente=true ORDER BY nombres ASC";
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
		$remitentes[$row['id']]= array($row['id'],utf8_decode($row['descripcion']));
		$i++;
	}
	return $remitentes;
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
	//$i=0;
	//while( $row = pg_fetch_array ($result,$i )) {
	for($i=0;$i<pg_num_rows($result);$i++){
		$row = pg_fetch_array ($result,$i);
		$idCarpeta=$row['id'];
		$bakgr=$copia='';
		//$bakgr='aa123';
		if($idAbu==$row['id_destinatario_historico'] || $idAbu==-1){
			$bakgr='style="border-color: #C2E2EF; border-width: 2px;';
		}
		if($row['copia']=='t'){
			$bakgr='style="border-color: #A6E289; border-width: 2px;';
			$copia='COPIA';
		}
		if($row['archivado']!=''){
			$bakgr='style="background-color: #C2E2EF; ';
			$copia='ARCHIVADO';
		}
		
		$hijos=getHijos($idCarpeta, $row['id_emisor_historico']);
		if($hijos=='' && $copia==''){
			$bakgr='style="background-color: #FFFF82; ';
			$copia='PENDIENTE';
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
		
		/*if($hijos=='' && $copia==''){
			$nodo=str_replace('aa123','style="background-color: #FFFF00;"',$nodo);
			echo $copia;
		}else{
			$nodo=str_replace('aa123','',$nodo);
		}*/
		$nodo.=$hijos;
		$nodo.='</li>';
		//$i++;
	}
	unset($result);
	if($i==0){
		$nodo='';
	}else{
		$nodo.='</ol>';
	}
	
	return $nodo;
}

function getDetalleNodo(){
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
	require_once("Alfresco/Service/Repository.php");
	require_once("Alfresco/Service/Session.php");
	require_once("Alfresco/Service/SpacesStore.php");

	// Specify the connection details
	//test
	$repositoryUrl = "http://172.16.10.136:8080/alfresco/api";
	
	//prod
	//$repositoryUrl = "http://172.16.10.160:8080/alfresco/api";
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
	//while( $row = pg_fetch_array ($result,$i )) {
	for( $i=0; $i<pg_num_rows($result);$i++) {
		$row = pg_fetch_array ($result,$i );
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
		$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.136', $link);
		
		//prod
		//$url_archivo=str_replace ( 'scjsgdoc.local' , '172.16.10.160', $link);
		$docs.='<li><a href="'.$url_archivo.'" target="_blank">'
		.$row['nombre_archivo'].'</a> (Agregado '.$to.' '.$_SESSION['usuarios'][$row['id_emisor']][1]
		.' en ID '
		.$row['id'].'  '.$row['id_archivo'].'.'.$extension.')</li>';
		//$i++;
	}
	unset($result);
	$docs.='</ul>';
	return $docs;
}

if(isset($_POST["tokenB"])){
		//echo $_POST["checkboxCarp"];
		echo buscar();
		
}

if(isset($_POST["func"]) && $_POST["func"]=='getHisto'){
	echo getHisorial();
		
}

if(isset($_POST["func"]) && $_POST["func"]=='getDetCarp'){
	echo getDetalleCarp($_POST["idUs"]);
		
}
if(isset($_POST["func"]) && $_POST["func"]=='getDet'){
	echo getDetalleNodo();
		
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
if(isset($_POST["btnExpUser"])){
	$nombreArchivo = date('Ymdhis').'_Carpetas_usuario.xls';
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=$nombreArchivo");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo utf8_decode($_SESSION['carpU']);
		
}
if(isset($_POST["btnExpUserTot"])){
	$nombreArchivo = date('Ymdhis').'_Carpetas_usuario.xls';
	header("Content-type: application/vnd.ms-excel; name='excel'");
	header("Content-Disposition: filename=$nombreArchivo");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo utf8_decode(getReportePend());
		
}
?>
