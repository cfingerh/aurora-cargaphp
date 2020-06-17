<?php 
session_start();
include_once('logica/buscar_carpeta.php');
$depas=getDepartamentos('');
$usuarios=getUsuarios('');
getTags();
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

/***Nube de tags***/

function insertTag(tag, tipo){
	//alert(tag);
	//alert(tipo);
	if(tipo=='mat'){
		document.getElementById('txtMateria').value+=tag+' ';
	}
	if(tipo=='obs'){
		document.getElementById('txtNomDoc').value+=tag+' ';
	}
}


var word_list = [
<?php 
$cont2=0;
foreach($_SESSION['tags'] as $tagO => $cont){
$cont2++;
$tag=explode('**',$tagO);

?>
        {text: "<?php echo $tag[0];?>", weight: <?php echo $cont+$cont2;?>, link: "javascript:void(0)", handlers: { click: function() { click: insertTag('<?php echo $tag[0];?>','<?php echo $tag[1];?>') } }},
<?php 

}
?>        {text: "", weight: 0, link: "javascript:void(0)", handlers: { click: function() { click: insertTag('','mat') } }}
      ];
      $(function() {
        $("#cloudtag").jQCloud(word_list);
      });

/*********Fin nube*********/

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
	//busqueda en carpetas
	if(!$('#checkboxCarp').prop('checked') && !$('#checkboxDoc').prop('checked')){
		alert('Debe seleccionar al menos opción de búsqueda');
		$('#checkboxCarp').focus();
		return false;
	}
	if($('#checkboxCarp').prop('checked')){
		show('tablaCarp');
		show('carp');
		$.ajax({
			url: 'logica/buscar_carpeta.php',  
			type: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(){
				$('#titB').html('Carpetas: (ejecutando b&uacute;squeda...)');	
			},
			success: function(data){
				//alert(data);
				$('#tablaCarp').html(data);
				if(data!=''){
					formData = null;
					//var botonExp = '<input name="btnExp" type="button" id="btnExp" value="Exportar" onclick="exportToExcel(\'tablaCarp\');"/>';
					$('#tablaCarp').html(data);
					var nFilas = document.getElementById('resultadoC').rows.length -1;
					$('#titB').html('Carpetas: '+nFilas+' resultados');
				}else{
					$('#titB').html('Carpetas: no se encontraron resultados');
				}
			},
			error: function(){
				alert('Carp: Error en la operación');
			}
		});
		$('#checkboxCarp').prop('checked', false);
		var check=true;
	}else{
		$('#carp').slideUp(200);
	}
	
	var formData = new FormData($("#formBuscar")[0]);
	//busqueda en documentos
	if($('#checkboxDoc').prop('checked')){
		show('tablaDoc');
		show('doc');
		$.ajax({
			url: 'logica/buscar_carpeta.php',  
			type: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(){
				$('#titBDoc').html('Documentos: (ejecutando b&uacute;squeda...)');	
			},
			success: function(data2){
				//alert(data);
				if(data2!=''){
					formData = null;
					//var botonExp = '<input name="btnExp" type="button" id="btnExp" value="Exportar" onclick="exportToExcel(\'tablaDoc\');"/>';				
					$('#tablaDoc').html(data2);
					var nFilas = document.getElementById('resultadoD').rows.length -1;
					$('#titBDoc').html('Documentos: '+nFilas+' resultados');
				}else{
					$('#titBDoc').html('Documentos: no se encontraron resultados');
					//alert('no se encontraron resultados ');
				}
			},
			error: function(){
				alert('Doc: Error en la operación');
			}
		});
			
	}else{
		$('#doc').slideUp(200);
	}
	if(check){
			$('#checkboxCarp').prop('checked', true);
		}
	//alert('ok2');
}
function showDet(nCarpeta){
	//alert('ini');
	$('#detalle').slideUp(100);
	$('#detCarp').slideUp(150);
	$.post("logica/buscar_carpeta.php", { func: "getHisto", nCarp: nCarpeta},
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
	$.post("logica/buscar_carpeta.php", { func: "getDet", idPad: dato1, idHijo: dato2},
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
          <li class="selec"><a href="index.php">Buscar carpetas</a></li>
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
          <h2>Buscador SGDOC </h2>
          <p>&nbsp;</p>
          <form id="formBuscar"  enctype="multipart/form-data" method="post" action="logica/buscar_carpeta.php">
            <table border="0" cellspacing="1" cellpadding="0">
              <tr>
                <td width="124" bgcolor="#E6E6E6">Materia:</td>
                <td colspan="2" bgcolor="#E6E6E6"><input name="txtMateria" type="text" id="txtMateria" size="40"/></td>
                <td width="638"><div align="center"><h3>Nube de tags</h3></div></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">N° de carpeta: </td>
                <td colspan="2" bgcolor="#E6E6E6"><input name="txtNCarpeta" type="text" id="txtNCarpeta" size="40"/></td>
                <td width="638" rowspan="8"><div align="center"><div id="cloudtag" style="width: 500px; height: 300px; border: 1px solid #ccc;"></div>
                </div></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">N° de documento:</td>
                <td colspan="2" bgcolor="#E6E6E6"><input name="txtNDoc" type="text" size="40" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Nombre de documento: </td>
                <td colspan="2" bgcolor="#E6E6E6"><input name="txtNomDoc" type="text" id="txtNomDoc" size="40" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Unidad/División:</td>
                <td colspan="2" bgcolor="#E6E6E6"><select name="cbxDiv" id="cbxDiv">
                    <option value="" selected="selected"></option>
                    <?php
						foreach($depas as $val){
							echo '<option value="'.$val[0].'">'.$val[1].'</option>';
						}			
					?>
                  </select>                </td>
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
                <td bgcolor="#E6E6E6">De:</td>
                <td colspan="2" bgcolor="#E6E6E6"><select name="cbxDe" id="cbxDe">
                    <option value="" selected="selected"></option>
                    <?php
						foreach($usuarios as $val){
							echo '<option value="'.$val[0].'">'.$val[1].'</option>';
						}			
					?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">A:</td>
                <td colspan="2" bgcolor="#E6E6E6"><select name="cbxA" id="cbxA">
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
                <td colspan="2" bgcolor="#E6E6E6"><input type="text" name="from" id="from" /></td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">Fecha de fin:</td>
                <td colspan="2" bgcolor="#E6E6E6"><input type="text" name="to" id="to" /></td>
              </tr>
              <tr>
                <td bgcolor="#85C7E0">Buscar en:</td>
                <td width="80" bgcolor="#85C7E0"><input name="checkboxCarp" type="checkbox" id="checkboxCarp" value="carp" checked="checked" />
                  Carpetas</td>
                <td width="102" bgcolor="#85C7E0"><input name="checkboxDoc" type="checkbox" id="checkboxDoc" value="doc" />
                  Documentos </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td bgcolor="#E6E6E6">&nbsp;</td>
                <td colspan="2" bgcolor="#E6E6E6"><input name="btnBuscar" type="button" id="btnBuscar" value="Buscar" onclick="buscar();"/>                  </td>
                <td>&nbsp;</td>
              </tr>
            </table>
            <input name="tokenB" type="hidden" value="" />
          </form>
          <div id="carp" style="display:none">
            <h4 class="style1 style3" id="titB">Resultados Carpetas </h4>
            <form id="formBCarp"  enctype="multipart/form-data" method="post" action="logica/buscar_carpeta.php">
              <div id="tablaCarp" style="max-height:400px; overflow:auto;"> </div>
              <input name="btnExpC" type="submit" id="btnExpC" value="Exportar" />
            </form>
          </div>
 
          <div id="doc" style="display:none">
            <h4 class="style1 style3" id="titBDoc">Resultados Carpetas </h4>
            <form id="formBDoc"  enctype="multipart/form-data" method="post" action="logica/buscar_carpeta.php">
              <div id="tablaDoc" style="max-height:400px; overflow:auto;"> </div>
              <input name="btnExpD" type="submit" id="btnExpD" value="Exportar"/>
            </form>
          </div>
          <div id="detC">
            <h4 class="style1 style3" id="titDetCarp"></h4>
            <div id="detCarp" style="max-height:700px; max-width:1200px; overflow:auto; display:"> </div>
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
