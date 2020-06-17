<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!--
<link href="/bpm/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
<link href="/bpm/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
<link href="/bpm/css/bootstrap.css.map" rel="stylesheet" type="text/css">-->
<link href="css/bootstrap.css" rel="stylesheet">
<!--<link href="css/main.css" rel="stylesheet">-->
<title>Carga de proceso BPMN - SGDP</title>
<script type="text/javascript">

function grabar(){
	var ok = confirm('Está seguro de grabar este proceso en el sistema?');
	if(!ok){
		return false;
	}
}

</script>
<style type="text/css">
<!--
.style1 {font-size: 36px}
body{ padding-top: 70px;}

 td,  th {
    border: 1px solid #ddd;
    padding: 8px;
}

 tr:nth-child(even){background-color: #f2f2f2;}

 tr:hover {background-color: #ddd;}

th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
-->
</style>
</head>
<body style="margin-left:55px; margin-right:55px; margin-bottom:20px; margin-top:15px">
<h1 align="center" class="style1 style1">Carga de subprocesos a SGDP</h1>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex6-collapse">
        <span class="sr-only">Desplegar navegación</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">SGDP</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse navbar-ex6-collapse">
      <ul class="nav navbar-nav">
        <li class="active"><a href="index.php">Carga de procesos</a></li>
        <li><a href="asig_user.php">Asignar usuarios a roles</a></li>
        <li ><a href="roles_user.php">Reporte roles por usuario</a></li>
		<li ><a href="info_proceso_exp.php">Información por expediente</a></li>
		<li><a href="phpgen/SGDP_AUTORES.php">Mantenedor Autores</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
</nav>
<?php  session_start();
	include_once('get_param.php');
	$persp=getPerspectiva();
	$proc=getProceso();
	$div=getDivision();
	
 if(!isset($_FILES['fileBPMN'])) {   ?>
<form action="procesar_bpmn.php" method="post" enctype="multipart/form-data">

	<table  border="0">
	 <!-- <tr>
	    <td>Perspectiva: </td>
	    <td>
	      <select name="cbxPers" id="cbxPers" required>
		  	<option value="">Seleccione...</option>
		  <?php  foreach($persp as $pers){?>
	        <option value="<?php echo $pers['id'] ?>"><?php echo $pers['nombre'] ?></option>
		<?php  }?>
          </select></td>
      </tr>-->
	  <tr>
	    <td>Macroproceso:</td>
	    <td><select name="cbxProc" id="cbxProc" required>
		<option value="">Seleccione...</option>
		<?php  foreach($proc as $pro){?>
	        <option value="<?php echo $pro['id'] ?>"><?php echo $pro['nombre'] ?></option>
		<?php  }?>
                </select></td>
      </tr>
	  <tr>
	    <td>Nombre subproceso:</td>
	    <td>
	      <input name="txtSubProc" type="text" id="txtSubProc" >	    </td>
      </tr>
	  <tr>
	    <td>C&oacute;digo subproceso:</td>
	    <td>
	      <input name="txtCodProc" type="text" id="txtCodProc" >	    </td>
      </tr>
	  <tr>
	    <td>Duración (días hábiles):</td>
	    <td><input name="txtDur" type="text" id="txtDur" required></td>
      </tr><!---->
	  <tr>
	    <td>División/Unidad responsable: </td>
	    <td><select name="cbxDiv" id="cbxDiv" required>
		<option value="">Seleccione...</option>
		<?php  foreach($div as $di){?>
	        <option value="<?php echo $di['id'] ?>"><?php echo $di['nombre'] ?></option>
		<?php  }?>
                </select></td>
      </tr>
	  <tr>
	    <td>Privado:</td>
	    <td><label><input name="chkPriv" type="checkbox" id="chkPriv" value="TRUE" >
	    </label></td>
	  </tr>
	  <tr>
		<td>Seleccione el archivo bpmn (extension xml, bpmn)</td>
		<td><label>
		  <input name="fileBPMN" type="file" id="fileBPMN"  accept=".xml, .bpmn" required>
		</label></td>
	  </tr>
	  <tr>
		<td>Seleccione el diagrama (svg, png, jpg)</td>
		<td><label>
		  <input name="fileFoto" type="file" id="fileFoto" accept=".svg,.png,.jpg" required>
		</label></td>
	  </tr>
	  <tr>
		<td>&nbsp;</td>
		<td><input name="btnProcesar" type="submit" id="btnProcesar" value="Procesar"></td>
	  </tr>
	  <tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	  </tr>
  </table>
</form>

<?php    }   ?>
<?php

if(isset($_SESSION['tabla'])){
	echo $_SESSION['tabla'];
	unset($_SESSION['tabla']);
}

if(isset($_SESSION['resultado'])){
	echo '<h3>'.$_SESSION['resultado'].'</h4></br></br>';
	echo '<h4>Log de la operación:</h4></br>';
	print_r($_SESSION['sql']);
	
	unset($_SESSION['resultado']);
}

?>

</body>
</html>
