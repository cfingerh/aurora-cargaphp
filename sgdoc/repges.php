<?php 
session_start();
include_once('logica/buscar_repges.php');
$depas=getDepartamentos('');
$usuarios=getUsuarios('');
$remit=getRemitente('');
//getIsArchivado(86161);
//return;
$_SESSION['usuarios']=$usuarios;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
<!-- META TAGS -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10"/>
<meta name="keywords" content="">
<meta name="description" content="">
<!-- CSS -->
<link rel="shortcut icon" href="http://www.scj.cl/resources/images/favico.ico">
<link href="homologacion_archivos/screen.css" rel="stylesheet" type="text/css" media="screen">
<link rel="stylesheet" href="css/mindmap.css">
<style type="text/css">
#menu_principal #b_homologacion a {
	background-position:0 -23px;
}
.style1 {color: #309fca}
.style2 {color: #000000}
.style3 {font-size: 12pt}
.style4 {font-size: 9pt}
</style>
<link rel="stylesheet" type="text/css" href="css/jqcloud.css" />
<!-- JS -->
<script src="homologacion_archivos/tamano_texto.js" type="text/javascript"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="javascript/html2canvas.js"></script>
<script type="text/javascript" src="javascript/jqcloud-1.0.4.js"></script>
<script type="text/javascript">

$(function() {
    $( "#from" ).datepicker({
      //defaultDate: new Date("Noviembre 13, 2014"),
      changeMonth: true,
      numberOfMonths: 1,
	  dateFormat: 'dd-mm-yy',
      onClose: function( selectedDate ) {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#to" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
	  dateFormat: 'dd-mm-yy',
      onClose: function( selectedDate ) {
        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });
  
function show(valor){
	$('#'+valor).slideDown(200);
}

function buscar(){
	var formData = new FormData($("#formBuscar")[0]);
	$('#detalle').slideUp(100);
	$('#carp').slideUp(200);
	$('#detCarp').slideUp(200);
	$('#detUser').slideUp(200);
	$('#detC').slideUp(150);
	//busqueda en carpetas
	
		show('tablaCarp');
		show('carp');
		$.ajax({
			url: 'logica/buscar_repges.php',  
			type: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(){
				$('#titB').html('Pendientes: (ejecutando b&uacute;squeda...)');
				$('#tablaCarp').html('');	
			},
			success: function(data){
				//alert(data);
				$('#tablaCarp').html(data);
				if(data!=''){
					formData = null;
					//var botonExp = '<input name="btnExp" type="button" id="btnExp" value="Exportar" onclick="exportToExcel(\'tablaCarp\');"/>';
					$('#tablaCarp').html(data);
					var nFilas = document.getElementById('resultadoC').rows.length -1;
					//$('#titB').html('Pendientes: '+nFilas+' resultados');
					$('#titB').html('Pendientes: resultados');
				}else{
					$('#titB').html('Pendientes: no se encontraron resultados');
				}
			},
			error: function(){
				alert('Carp: Error en la operación');
			}
		});
	
	//alert('ok2');
}

function getCarpetas(idUser, nomUser){
	$('#detUser').slideUp(150);
	$('#detC').slideUp(150);
	//alert(idUser);
	$.post("logica/buscar_repges.php", { func: "getDetCarp", idUs: idUser},
	function(data){
		//alert(data);
		if(data==''){
			alert('sin carpetas pendientes');
			return false;
		}
		nCarpeta=idUser;
		$('#detUser').slideDown(200);
		var titulo ='Carpetas pendientes de usuario '+nomUser+ ' (click en carpetas para ver historia)';
		$('#titDetUser').html(titulo);
		$('#tablaDetUser').html(data);				
	});		

}

function showDet(nCarpeta){
	//alert('ini');
	$('#detalle').slideUp(100);
	$('#detCarp').slideUp(150);
	$('#detC').slideUp(150);
	$.post("logica/buscar_repges.php", { func: "getHisto", nCarp: nCarpeta},
		function(data){
			//alert(data);
			if(data==''){
				alert('sin historial');
				return false;
			}
			$('#detCarp').slideDown(200);
			var titulo ='Historia carpeta: '+nCarpeta+ ' (click en globos para ver detalle)';
			$('#titDetCarp').html(titulo);
			$('#detCarp').html(data);
			$('#detC').slideDown(200);				
		});		
	//alert('fin');
}

function verDetalle(idCarp, idPadre){
	//alert(idCarp + ' '+idPadre);
	$( "#detalle" ).slideUp(10);
	var dato1=idPadre;
	var dato2=idCarp;
	/*if(idPadre==-1){
		//dato1=idCarp;
	}*/
	$.post("logica/buscar_repges.php", { func: "getDet", idPad: dato1, idHijo: dato2},
		function(data){
			//alert(data);
			if(data==''){
				alert('sin historial');
				return false;
			}
			$( "#detalle" ).html(data);
			$( "#detalle" ).slideDown(150);
			$('#detalle').focus();			
		});
}

function exportToExcel(idTabla){
	window.open('data:application/vnd.ms-excel,' + encodeURIComponent($('#'+idTabla).html()));
}

function linkBack(){
	history.back();
	return false;
	
};

</script>
<title>SGDOCB - Buscador Sistema de Gestion Documental</title>
</head>
<body>
<!-- contenedor -->
<div id="contenedor">
  <div id="top" class="bg_menu_azul">
    <!-- menu principal -->
    <div class="logo"><img src="homologacion_archivos/logo.gif" alt="Superintendencia de Casinos de Juego" title="Superintendencia de Casinos de Juego"></div>
    <!-- // menu principal -->
  </div>
  <!-- cabecera -->
  <div id="cabecera_int">
    <h1 ></h1>
  </div>
  <!-- // cabecera -->
  <!-- contenido interiores -->
  <div id="contenido_int" class="azul">
    <!-- lado izquierdo -->
    <div id="lado_izquierdo">
      <!-- meenu lateral -->
      <div id="menu_lateral">
        <ul>
          <li><a href="index.php">Buscar carpetas</a></li>
		  <li class="selec"><a href="repges.php">Carpetas pendientes</a></li>
          <li ><a href="javascript:void(0)" onclick="linkBack()">Volver</a></li>
        </ul>
      </div>
      <!-- // fin menu lateral -->
    </div>
    <!-- // fin lado izquierdo -->
    <!-- lado derecho -->
    <div id="lado_derecho">
      <!-- tamaño texto -->
      <div class="control_texto"> <img src="homologacion_archivos/txt_tamano_texto.gif" alt="Tamaño del Texto"> <a onclick="javascript:dzIncreaseFontSize('contenido_int');" onfocus="blur();" title="Aumentar el tamaño del texto" href="#;"><img src="homologacion_archivos/ico_aumentar_txt.gif" alt="Aumentar el tamaño del texto"></a>&nbsp;<a onclick="javascript:dzDecreaseFontSize('contenido_int');" onfocus="blur();" title="Disminuir el tamaño del texto" href="#;"><img src="homologacion_archivos/ico_achicar_texto.gif" alt="Disminuir el tamaño del texto"></a></div>
      <!-- // fin tamaño texto -->
      <!-- textos -->
      <div id="textos">
        <div class="clear"> </div>
        <p><br>
        </p>
        <div class="clear"></div>
        <div id="listado" class="listado" style="width:1000px">
          <h2>Pendientes SGDOC </h2>
          <p>&nbsp;</p>
          <form id="formBuscar"  enctype="multipart/form-data" method="post" action="logica/buscar_carpeta.php">
<table border="0" cellspacing="1" cellpadding="0">
              <tr>
                <td width="130" bgcolor="#E6E6E6">Emisor:</td>
                <td width="376" bgcolor="#E6E6E6"><select name="cbxRem" id="cbxRem">
                    <option value="" selected="selected"></option>
                    <?php
						foreach($remit as $val){
							echo '<option value="'.$val[1].'">'.$val[1].'</option>';
						}			
					?>
                  </select></td>
              </tr>
              <tr>
                <td width="130" bgcolor="#E6E6E6">Materia:</td>
                <td bgcolor="#E6E6E6"><input name="txtMateria" type="text" id="txtMateria" size="40"/></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">N° de carpeta: </td>
                <td bgcolor="#E6E6E6"><input name="txtNCarpeta" type="text" id="txtNCarpeta" size="40"/></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">N° de documento:</td>
                <td bgcolor="#E6E6E6"><input name="txtNDoc" type="text" size="40" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Unidad/División:</td>
                <td bgcolor="#E6E6E6"><select name="cbxDiv" id="cbxDiv">
                    <option value="" selected="selected">Todos</option>
                    <?php
						foreach($depas as $val){
							echo '<option value="'.$val[0].'">'.$val[1].'</option>';
						}			
					?>
                  </select>
                </td>
              </tr>
              <!-- <tr>
                <td bgcolor="#E6E6E6">Creador:</td>
                <td colspan="2" bgcolor="#E6E6E6"><select name="cbxCreador" id="cbxCreador">
                    <option value="" selected="selected"></option>
                    <?php
						foreach($usuarios as $val){
							//echo '<option value="'.$val[0].'">'.$val[1].'</option>';
						}			
					?>
                  </select></td>
              </tr>-->
              <tr>
                <td bgcolor="#E6E6E6">Usuario:</td>
                <td bgcolor="#E6E6E6"><select name="cbxDe" id="cbxDe">
                    <option value="" selected="selected"></option>
                    <?php
						foreach($usuarios as $val){
							echo '<option value="'.$val[0].'">'.$val[1].'</option>';
						}			
					?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Fecha de inicio:</td>
                <td bgcolor="#E6E6E6"><input type="text" name="from" id="from" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Fecha de fin:</td>
                <td bgcolor="#E6E6E6"><input type="text" name="to" id="to" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">&nbsp;</td>
                <td bgcolor="#E6E6E6"><input name="btnBuscar" type="button" id="btnBuscar" value="Buscar" onclick="buscar();"/>
                </td>
              </tr>
            </table>
<input name="tokenB" type="hidden" value="" />
</form>
          <div id="carp" style="display:none">
            <h4 class="style1 style3" id="titB">Resultados</h4>
            <div id="tablaCarp" style="max-height:400px; overflow:auto;"> </div>
            <table width="200" border="0">
              <tr>
                <td><form id="formBCarp"  enctype="multipart/form-data" method="post" action="logica/buscar_repges.php">
                    <input name="btnExpC" type="submit" id="btnExpC" value="Exportar" />
                  </form></td>
                <td><form id="formBCarp"  enctype="multipart/form-data" method="post" action="logica/buscar_repges.php">
                    <input name="btnExpUserTot" type="submit" id="btnExpUserTot" value="Exportar detalle" />
                  </form></td>
              </tr>
            </table>
          </div>
		  
		   <div id="detUser" style="display:none">
            <h4 class="style1 style3" id="titDetUser">Pendientes de usuario </h4>
            <form id="formDetUser"  enctype="multipart/form-data" method="post" action="logica/buscar_repges.php" target="_blank">
              <div id="tablaDetUser" style="max-height:400px; overflow:auto;"> </div>
              <input name="btnExpUser" type="submit" id="btnExpUser" value="Exportar" />
            </form>
          </div>
 
          <div id="detC">
            <h4 class="style1 style3" id="titDetCarp"></h4>
            <div id="detCarp" style="max-height:700px; max-width:100%; overflow:auto; display:"> </div>
            <div id="detalle" class="style4"></div>
          </div>
            </div>
        <!-- paginador -->
        <div class="center"></div>
        <!-- // fin paginador -->
      </div>
      <!-- // fin textos -->
      <div class="clear"></div>
      <!-- herramientas de navegacion -->
      <!-- // fin herramientas -->
    </div>
    <!-- // fin contenido int -->
    <div class="clear">&nbsp;</div>
  </div>
  <!-- fin lado derecho -->
  <div id="foot_contenido_int">&nbsp;</div>
</div>
<!-- // contenedor -->
<!-- pie de pagina -->
<div id="pie">
  <div class="centrado"><span class="descarga"></span></div>
</div>
<!-- // pie de pagina -->
</body>
</html>
