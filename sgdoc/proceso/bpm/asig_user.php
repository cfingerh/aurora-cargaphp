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

	<title>Asignar usuarios a procesos</title>
	<script src="jquery/jquery.min.js.descarga"></script>
	<link href="css/bootstrap.css" rel="stylesheet">
  
	<style>
	body{
	
	width:600px;margin:0 auto;overflow-x:hiden; 
	padding-top: 70px;
	
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#feffff+0,d2ebf9+100;Blue+3D+%2312 */
background: rgb(254,255,255); /* Old browsers */
background: -moz-linear-gradient(top, rgba(254,255,255,1) 0%, rgba(210,235,249,1) 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(top, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(to bottom, rgba(254,255,255,1) 0%,rgba(210,235,249,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#d2ebf9',GradientType=0 ); /* IE6-9 */
	
	}
	select{width:180px;margin:0 0 10px 0;border:1px solid #ccc;padding:10px;border-radius:10px 0 0 10px;}
	.clear{clear:both;text-align:center}
	div{float:left;width:200px;text-align:center}
	input {margin:25px 1px 0 1px;border:1px solid #ccc;padding:10px;}
	.izq{border-radius:10px 0 0 10px;}
	.der{border-radius:0 10px 10px 0;}
	.style1 {font-family: Verdana, Arial, Helvetica, sans-serif}
    </style>
</head>
<body>
	<h1 align="center" class="style1">Asignar usuarios a subproceso vigente</h1>
	
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
        <li ><a href="index.php">Carga de procesos</a></li>
        <li class="active"><a href="asig_user.php">Asignar usuarios a roles</a></li>
        <li ><a href="roles_user.php">Reporte roles por usuario</a></li>
		<li ><a href="info_proceso_exp.php">Información por expediente</a></li>
		<li><a href="phpgen/SGDP_AUTORES.php">Mantenedor Autores</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </nav>
	<a href="http://sgdocb/proceso/bpm_cont/asig_user.php" target="_blank" class="style1">Ir a procesos historicos</a>
	<form action="logica/l_asig_users.php" method="post" id="formulario" enctype="multipart/form-data">
	<table cellspacing="1" id="tablaDatos" >
		<tr>
			<td><span class="style1">Seleccione un subproceso:</span></td>
			<td ><select name="idProc" id="idProc" style="width:350px">
				<option selected>Seleccione un subproceso...</option>
					<?php for($i=0; $i<count($procesos);$i++){ 
				echo '<option value="'.$procesos[$i]['id'].'">'.$procesos[$i]['nombre'].'</option>';
				} ?>
			  </select>			</td>
		</tr>
		<tr>
			<td><span class="style1">Seleccione rol:</span></td>
			<td><select name="rolProc" id="rolProc" style="width:350px">
				<option selected>Seleccione un rol...</option>
				<?php for($i=0; $i<count($roles);$i++){ 
				echo '<option value="'.$roles[$i]['id'].'">'.$roles[$i]['nombre'].'</option>';
				} ?>
			  </select></td>
		</tr>
</table>
	
	
		<div>
			<select name="libres[]" id="libres" multiple="multiple" size="15">
			</select>
		</div>
		<div>
			<input type="button" class="pasar izq" value="Pasar »"><input type="button" class="quitar der" value="« Quitar"><br>
			<input type="button" class="pasartodos izq" value="Todos »"><input type="button" class="quitartodos der" value="« Todos">
		</div>
		<div class="">
			<select name="asignados[]" id="asignados" multiple="multiple" size="15">
			</select>
		</div>
		<p class="clear"><input type="submit" class="submit" value="Guardar Asignación"></p>
		<?php if(isset($_SESSION['resultado'])){
			echo $_SESSION['resultado'];
			unset($_SESSION['resultado']);
		
		}?>
	</form>
	<div id="divResult" style="width:600px" >	</div>
	<script type="text/javascript">
		var options2 = $("#rolProc").html();
		$("#rolProc option").remove();
		$(document).on('change', '#idProc', function() {
			$('#rolProc').html(options2);
			
			var aux = $( "#idProc" ).val();
			$("#rolProc option").not(" option[value|='"+aux+"']").remove();
			$('#rolProc').append('<option selected="selected">Seleccione un rol...</option>');
	   });
	   
	   //cargar usuarios segun roles
	   $(document).on('change', '#rolProc', function() {
	   
			var dato = $( "#rolProc" ).val();
			//carga los datos de los usuarios sin asignar en el rol seleccionado
			 $.post("logica/l_asig_users.php", { func: "getRolesFree", rol: dato},
				function(data){
					//alert(data);
					$('#libres').html(data);
				});	
				
			//carga los datos de los usuarios seleccionados
			$.post("logica/l_asig_users.php", { func: "getRolesAsig", rol: dato},
				function(data){
					//alert(data);
					$('#asignados').html(data);
				});	
	   });
	  
	
	$().ready(function() 
	{
		$('.pasar').click(function() { return !$('#libres option:selected').remove().appendTo('#asignados'); });  
		$('.quitar').click(function() { return !$('#asignados option:selected').remove().appendTo('#libres'); });
		$('.pasartodos').click(function() { $('#libres option').each(function() { $(this).remove().appendTo('#asignados'); }); });
		$('.quitartodos').click(function() { $('#asignados option').each(function() { $(this).remove().appendTo('#libres'); }); });
		
		$('.submit').click(function() { 
			var conf = confirm('Se asignarán los usuarios. ¿Desea continuar?');
			if(!conf){
				return false;	
			}
			
			var cont= $('#asignados option').length;
			if(cont<1){
				var conf = confirm('No has seleccionado ningún usuario. ¿Desea continuar?');
				if(!conf){
					return false;	
				}
			}
			$('#asignados option').prop('selected', 'selected'); 
			$('#libres option').prop('selected', ''); 
			
			 var url = "logica/l_asig_users.php";
				$.ajax({                        
				   type: "POST",                 
				   url: url,                     
				   data: $("#formulario").serialize(), 
				   success: function(data)             
				   {
					 $('#divResult').html(data);               
				   }
			   });
		
			return false;
		});
		
		
		
		
		
	}); 
	</script>
		
	
</p>
	<p>&nbsp;</p>
	<p>&nbsp;    </p>
</body></html>