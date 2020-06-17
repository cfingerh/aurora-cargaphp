<?php 
include_once('logica/l_asig_users.php');
//$users_free=getUsers('0', 33);
//$users_asig=getUsers('1', 33);

$procesos=getProcesos();
$roles=getRoles('');

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/bootstrap.css" rel="stylesheet">
<title>Información de subproceso</title>
<script src="jquery/jquery.min.js.descarga"></script>
<style>
	body{width:1000px;
		margin: auto;
		overflow-x:hiden; 
		padding-top: 70px; 
		text-align:center}
	.clear{clear:both;text-align:center}
	div{float:center;text-align:left}
	input {margin:10px ;border:1px solid #ccc;padding:10px;}
	.izq{border-radius:10px 0 0 10px;}
	.der{border-radius:0 10px 10px 0;}
	.style1 {font-family: Verdana, Arial, Helvetica, sans-serif;}
    </style>
</head>
<body>
<h1 class="style1">Información por expediente </h1>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex6-collapse"> <span class="sr-only">Desplegar navegación</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
    <a class="navbar-brand" href="#">SGDP</a> </div>
  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse navbar-ex6-collapse">
    <ul class="nav navbar-nav">
      <li ><a href="index.php">Carga de procesos</a></li>
      <li><a href="asig_user.php">Asignar usuarios a roles</a></li>
      <li ><a href="roles_user.php">Reporte roles por usuario</a></li>
	  <li class="active"><a href="info_proceso_exp.php">Información por expediente</a></li>
	  <li><a href="phpgen/SGDP_AUTORES.php">Mantenedor Autores</a></li>
    </ul>
  </div>
  <!-- /.navbar-collapse -->
</nav>
<div align="center">
<form action="logica/l_asig_users.php" method="post" id="formulario" enctype="multipart/form-data">
  <table cellspacing="1">
    <tr>
      <td><span class="style1">Ingrese N° de Expediente :</span></td>
      <td ><input name="txtExp" type="text" id="txtExp"></td>
      <td ><span class="clear">
        <input name="btnGetInfo" type="submit" class="submit" id="btnGetInfo" value="Ver información">
      </span></td>
    </tr>
  </table>
  <p class="clear">&nbsp;</p>
</form>
</div>
<div id="divResult" style="width:900px" ></div>
<p>
  <script type="text/javascript">
		
	
	$().ready(function() 
	{
		$('.submit').click(function() { 
							
			  /*xhttp.open("POST", "logica/l_asig_users.php", false);
			  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			  xhttp.send("func=getRolesFree&rol=105-32");
			  alert(xhttp.responseText);*/
			  if($("#txtExp").val()==''){
			  	alert('Debe ingresar un número de expediente');
				return false;
			  }
			  
			  var url = "logica/l_info_proceso_exp.php";
				$.ajax({                        
				   type: "POST",                 
				   url: url,                     
				   data: $("#formulario").serialize(), 
				   success: function(data)             
				   {
					 $('#divResult').html(data);               
				   }
			   });
			  
			//}
		
			return false;
		});
		
	
		
	}); 
	</script>
</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
</body>
</html>
