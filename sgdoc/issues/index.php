<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
<!-- META TAGS -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10"/>
<meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
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
<!-- JS -->
<script src="homologacion_archivos/tamano_texto.js" type="text/javascript"></script>
<script type="text/javascript">

function buscar(){
	var busc= document.getElementById('txtSis').value.toLowerCase();
	showHideAll('');
	if(busc.length<1){
		return;
	}
	for(var i=1; i<21;i++){
		var val = document.getElementById(i).value.toLowerCase();
		if(val.search(busc)==-1){
			//alert(i);
			document.getElementById(i).style.display='none';	
		}
	}
}

function showHideAll(op){
	for(var i=1; i<21;i++){
		document.getElementById(i).style.display=op;
	}
}
  
function reportar(url){
	window.open(url, target="issue"); 
	return false;
}

function popitup(url) {
	newwindow=window.open(url,'Información','height=400,width=400');
	if (window.focus) {newwindow.focus()}
	return false;
}
</script>
<title>Reporte de incidencias</title>
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
  </div>
  <!-- // cabecera -->
  <!-- contenido interiores -->
  <div id="contenido_int" class="azul">
    <!-- lado izquierdo -->
    <div id="lado_izquierdo">
      <!-- meenu lateral -->
      <div id="menu_lateral"> </div>
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
          <h2>Registro de solicitudes de sistemas informáticos </h2>
          <p>&nbsp;</p>
          <p>Descripción de este sistema. Los tipos de solicitudes son tres:</p>
          <ul>
            <li>Errores</li>
            <li>Soporte</li>
            <li>Cambios</li>
          </ul>
          <p>Para más detalle, vaya a <a href="info.html" onclick="return popitup('info.html')"
	>éste enlace</a>.</p>
          <p>&nbsp;</p>
          <p>Busca el sistema: </p>
          <table border="0" cellspacing="1" cellpadding="0">
            <tr>
              <td bgcolor="#E6E6E6">Sistema:</td>
              <td bgcolor="#E6E6E6"><input name="txtSis" type="text" id="txtSis" onkeyup="buscar();" />
                <input name="btnBuscar" type="button" id="btnBuscar" value="Buscar" onclick="buscar();"/></td>
            </tr>
          </table>
          <p>&nbsp;</p>
          <h4>Sistemas generales </h4>
          <p>
            <input name="4" type="button" id="4" value="iGestión" onclick="reportar('http://172.16.10.142/redmine/projects/igestion/issues/new');"/>
            <input name="6" type="button" id="6" value="Intranet" onclick="reportar('http://172.16.10.142/redmine/projects/intranet/issues/new');"/>
            <input name="9" type="button" id="9" value="SGDOC" onclick="reportar('http://172.16.10.142/redmine/projects/sgdoc/issues/new');"/>
            <input name="19" type="button" id="19" value="Sitio Web" onclick="reportar('http://172.16.10.142/redmine/projects/sitio-web/issues/new');"/>
            <input name="12" type="button" id="12" value="Sistema de Activo Fijo" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-activo-fijo/issues/new');"/>
            <input name="14" type="button" id="14" value="Sistema de Materiales" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-materiales/issues/new');"/>
            <input name="15" type="button" id="15" value="Sistema de personal de casinos" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-personal-de-casinos/issues/new');"/>
            <input name="16" type="button" id="16" value="Sistema de RRHH" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-rrhh/issues/new');"/>
            <input name="18" type="button" id="18" value="Sistema de viáticos y reembolsos" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-viaticos-y-reembolsos/issues/new');"/>
          </p>
          <h4>Computadores y otros periféricos </h4>
          <p>
            <input name="3" type="button" id="3" value="Equipos de escritorio" onclick="reportar('http://172.16.10.142/redmine/projects/equipos-de-escritorio/issues/new');"/>
            <input name="5" type="button" id="5" value="Impresoras" onclick="reportar('http://172.16.10.142/redmine/projects/impresoras/issues/new');"/>
            <input name="8" type="button" id="8" value="Notebooks" onclick="reportar('http://172.16.10.142/redmine/projects/notebooks/issues/new');"/>
            <input name="20" type="button" id="20" value="Teléfonos y accesorios" onclick="reportar('http://172.16.10.142/redmine/projects/telefonos-y-accesorios/issues/new');"/>
          </p>
          <h4>Sistemas de negocio </h4>
          <p>
            <input name="13" type="button" id="13" value="Sistema de Fiscalización" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-fiscalizacion/issues/new');"/>
            <input name="10" type="button" id="10" value="SGSC" onclick="reportar('http://172.16.10.142/redmine/projects/sgsc/issues/new');"/>
            <input name="11" type="button" id="11" value="SIOC" onclick="reportar('http://172.16.10.142/redmine/projects/sioc/issues/new');"/>
            <input name="7" type="button" id="7" value="iWeb SSHI" onclick="reportar('http://172.16.10.142/redmine/projects/modulo-web-iweb-sshi/issues/new');"/>
            <input name="17" type="button" id="17" value="SSHI" onclick="reportar('http://172.16.10.142/redmine/projects/sistema-de-solicitudes-de-homologacion-sshi/issues/new');"/>
          </p>
          <h4>Otros</h4>
          <p>
            <input name="1" type="button" id="1" value="Control de acceso " onclick="reportar('http://172.16.10.142/redmine/projects/control-de-acceso/issues/new');"/>
            <input name="2" type="button" id="2" value="Control de asistencia" onclick="reportar('http://172.16.10.142/redmine/projects/control-de-asistencia/issues/new');"/>
          </p>
          <p>&nbsp; </p>
          <div><!----><!----><!----><!---->
          </div>
          </br>
		  </br>
		  <iframe name="issue" width="100%" height="700px">
		  
		  </iframe>
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
