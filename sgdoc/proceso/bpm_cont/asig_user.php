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
	<style>
	body{
	width:600px;margin:0 auto;overflow-x:hiden; 
	
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#febbbb+0,fe9090+45,fe9090+45,ff5c5c+100&0.47+0,1+96;Red+3D+%231 */
background: -moz-linear-gradient(top, rgba(254,187,187,0.47) 0%, rgba(254,144,144,0.72) 45%, rgba(255,96,96,1) 96%, rgba(255,92,92,1) 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(top, rgba(254,187,187,0.47) 0%,rgba(254,144,144,0.72) 45%,rgba(255,96,96,1) 96%,rgba(255,92,92,1) 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(to bottom, rgba(254,187,187,0.47) 0%,rgba(254,144,144,0.72) 45%,rgba(255,96,96,1) 96%,rgba(255,92,92,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#78febbbb', endColorstr='#ff5c5c',GradientType=0 ); /* IE6-9 */
	
	
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
	<h1 align="center" class="style1">Asignar usuarios a proceso historico </h1>
	<form action="logica/l_asig_users.php" method="post" id="formulario" enctype="multipart/form-data">
	<table cellspacing="1" id="tablaDatos" >
		<tr>
			<td><span class="style1">Seleccione subproceso:</span></td>
			<td ><select name="idProc" id="idProc" style="width:350px" require>
				<option selected>Seleccione un proceso...</option>
					<?php for($i=0; $i<count($procesos);$i++){ 
				echo '<option value="'.$procesos[$i]['id'].'">'.$procesos[$i]['nombre'].'</option>';
				} ?>
			  </select>			</td>
		    <td ><a href="http://sgdocb/proceso/bpm_cont/asig_user.php" target="_blank" class="style1" id="linkVerFlujo" style="display:none">ver flujo</a></td>
		</tr>
		<tr>
			<td><span class="style1">Seleccione rol:</span></td>
			<td><select name="rolProc" id="rolProc" style="width:350px" require>
				<option selected>Seleccione un proceso...</option>
				<?php for($i=0; $i<count($roles);$i++){ 
				echo '<option value="'.$roles[$i]['id'].'">'.$roles[$i]['nombre'].'</option>';
				} ?>
			  </select></td>
		    <td>&nbsp;</td>
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
				<?php for($i=0; $i<count($users_asig);$i++){ 
				echo '<option value="'.$users_asig[$i]['user'].'">'.$users_asig[$i]['user'].'</option>';
				} ?>
		  </select>
		</div>
		<p class="clear"><input type="submit" class="submit" value="Guardar Asignación"></p>
		<?php if(isset($_SESSION['resultado'])){
			//echo $_SESSION['resultado'];
			//unset($_SESSION['resultado']);
		
		}?>
	</form>
	<div id="divResult" style="width:600px" >	</div>
	<p>
	  <script type="text/javascript">
		var options2 = $("#rolProc").html();
		$("#rolProc option").remove();
		$(document).on('change', '#idProc', function() {
			$('#rolProc').html(options2);
			
			var aux = $( "#idProc" ).val();
			$("#rolProc option").not(" option[value|='"+aux+"']").remove();
			$('#rolProc').append('<option selected="selected">Seleccione un rol...</option>');
			if(aux!=""){
				$("#linkVerFlujo").css("display", ""); 
				$("#linkVerFlujo").attr("href","http://sgdocb/proceso/indexp.php?idproc="+aux);
				$("a").text("ver flujo");
			}else{
				$("#linkVerFlujo").css("display", "none"); 
			}
			
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
		
			//function loadDoc() {
			  var xhttp = new XMLHttpRequest();
			  xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
				  document.getElementById("demo").innerHTML = this.responseText;
				}
			  };
			  
			  /*xhttp.open("POST", "logica/l_asig_users.php", false);
			  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			  xhttp.send("func=getRolesFree&rol=105-32");
			  alert(xhttp.responseText);*/
			  
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
			  
			//}
		
			return false;
		});
		
		
		
		
		
	}); 
	</script>
</p>
	<p>&nbsp;</p>
	<p>&nbsp;    </p>
</body></html>