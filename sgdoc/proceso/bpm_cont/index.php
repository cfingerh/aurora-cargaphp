<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10"/>
<!--
<link href="/bpm/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
<link href="/bpm/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
<link href="/bpm/css/bootstrap.css.map" rel="stylesheet" type="text/css">
<link href="/bpm/css/bootstrap.css" rel="stylesheet" type="text/css">-->

<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="css/main.css" />
<title>Carga de proceso BPMN - SGDP</title>
<script type="text/javascript">

function grabar(){
	var ok = confirm('Está seguro de grabar este proceso en el sistema?');
	if(!ok){
		return false;
	}
}

</script>
</head>
<body style="margin-left:55px; margin-right:55px; margin-bottom:20px; margin-top:15px">
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
	    <td>Proceso:</td>
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
	  <!--<tr>
	    <td>Duración (días hábiles):</td>
	    <td><input name="txtDur" type="text" id="txtDur" required></td>
      </tr>-->
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
	    <td><label><input name="chkPriv" type="checkbox" id="chkPriv" value="TRUE">
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
