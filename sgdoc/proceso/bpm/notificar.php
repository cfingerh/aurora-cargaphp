<?php 
include_once('logica/l_notificar.php');

if(!isset($_GET['idproc'])){
	echo "Error en el proceso";
	return;
}

$idProceso=pg_escape_string($_GET['idproc']);
$ncarp=$idProceso;
$nomProceso=getNomProc($idProceso);
//$_SESSION['idProceso']=$idProceso;
$users_free=getUsers($_SESSION['idProceso']);

$_SESSION['nomProc']=$nomProceso;

$unidades=getUnidades();

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="css/bootstrap.css" />
	<title>Notificar proceso <?php echo $ncarp;?></title>
	<script src="jquery/jquery.min.js.descarga"></script>
	<style>
	body{width:600px;margin:0 auto;overflow-x:hiden; }
	select{width:180px;margin:0 0 10px 0;border:1px solid #ccc;padding:10px;border-radius:10px 0 0 10px;}
	.clear{clear:both;text-align:center}
	div{float:left;width:200px;text-align:center}
	input {margin:25px 1px 0 1px;border:1px solid #ccc;padding:10px;}
	.izq{border-radius:10px 0 0 10px;}
	.der{border-radius:0 10px 10px 0;}
	.style1 {font-family: Verdana, Arial, Helvetica, sans-serif}
    </style>
</head>
<body style="text-align:center" >
	<h1 class="style1">Notificar subproceso &quot;<?php echo $nomProceso[0];?>&quot; </h1>
	<h3 class="style1">Expediente <?php echo $nomProceso[1];?> </h3>
	<table cellspacing="1" bgcolor="#D9EEFF" id="tablaDatos" >
		<tr>
			<td><span class="style1">Seleccione Unidad / División</span></td>
			<td >:<select name="division" id="division" style="width:250px" require>
					<option value="alld" selected>Todos</option>
					<?php for($i=0; $i<count($unidades);$i++){ 
				echo '<option value="'.$unidades[$i]['id'].'">'.$unidades[$i]['nombre'].'</option>';
				} ?>
			  </select>			</td>
		</tr>
		<tr>
			<td><p class="style1">Seleccione cargo</p>
		    </td>
			<td>:<select name="cargo" id="cargo" style="width:250px" require>
				<option value="allp" selected>Todos</option>
				<option value="super">Superintendenta</option>
				<option value="DFIS-jefe">Jefe DFIS</option>
				<option value="DAUT-jefe">Jefe DAUT</option>
				<option value="DJUR-jefe">Jefe DJUR</option>
				<option value="UAUD">Auditoría</option>
				<option value="UAYC-jefe">Jefe UAYC</option>
				<option value="UAYF-jefe">Jefe UAYF</option>
				<option value="UTDP-jefe">Jefe UTDP</option>
				<option value="GAB-prof">Profesional Gabinete</option>
				<option value="DFIS-prof">Profesional DFIS</option>
				<option value="DAUT-prof">Profesional DAUT</option>
				<option value="DJUR-prof">Profesional DJUR</option>
				<option value="UAYC-prof">Profesional UAYC</option>
				<option value="UAYF-prof">Profesional UAYF</option>
				<option value="UTDP-prof">Profesional UTDP</option>
				<option value="GAB-admin">Administrativo Gabinete</option>
				<option value="DFIS-admin">Administrativo DFIS</option>
				<option value="DAUT-admin">Administrativo DAUT</option>
				<option value="DJUR-admin">Administrativo DJUR</option>
				<option value="UAYC-admin">Administrativo UAYC</option>
				<option value="UAYF-admin">Administrativo UAYF</option>
				<option value="UTDP-admin">Administrativo UTDP</option>
				<option value="GAB-coord">Coordinador Gabinete</option>
				<option value="DFIS-coord">Coordinador DFIS</option>
				<option value="DAUT-coord">Coordinador DAUT</option>
				<option value="DJUR-coord">Coordinador DJUR</option>
				<option value="UAYC-coord">Coordinador UAYC</option>
				<option value="UAYF-coord">Coordinador UAYF</option>
				<option value="UTDP-coord">Coordinador UTDP</option>
			  </select></td>
		</tr>
</table>
	<form action="logica/l_notificar.php" method="post" id="formulario" enctype="multipart/form-data">
	  <div>  <p class="style1">Seleccione usuarios</p>
			<select name="libres[]" id="libres" multiple="multiple" size="15">
				<?php 
					
					for($i=0; $i<count($users_free);$i++){
						
						echo '<option value="'.$users_free[$i]['id'].'" '.$users_free[$i]['select'].' '.$users_free[$i]['color'].' '.$users_free[$i]['disab'].'>'.$users_free[$i]['nombre'].'</option>';
					}
				?>
			</select>
	  </div>
		<div></br></br>
		  <input type="button" class="pasar izq" value="Pasar »"><input type="button" class="quitar der" value="« Quitar"><br>
			<input type="button" class="pasartodos izq" value="Todos »"><input type="button" class="quitartodos der" value="« Todos">
		</div>
		<div class="style1">
		  <p>Usuarios a notificar		  </p>
	
		    <select name="asignados[]" id="asignados" multiple="multiple" size="15">
	        </select>
	          
		</div>
		<div id="divResult" style="width:600px" ></div>
		<input type="submit" class="submit" value="Notificar">
	</form>
	
	  <script type="text/javascript">	
	  
	  var options2 = $("#cargo").html();
	  var allc = $("#libres").html();
		
		$(document).on('change', '#division', function() {
		$("#cargo option").remove();
			$('#cargo').html(options2);
			
			var aux = $( "#division" ).val();
			$("#cargo option").not(" option[value|='"+aux+"']").remove();
			$('#cargo').append('<option selected="selected" value="allc" >Todos</option>');
	   });
	   
	/*$('#libres option').dblclick(function() {
		alert($('#libres option:selected').val());
		
	});*/
	
	//cargar usuarios segun roles
	   $(document).on('change', '#cargo', function() {
	   
			var dato = $( "#cargo" ).val();
			//alert(dato);
			//carga los datos de los usuarios sin asignar en el rol seleccionado
			 $.post("logica/l_notificar.php", { func: "getUsersCargo", cargo: dato},
				function(data){
					//alert(data);
					$('#libres').html(data);
				});	
	   });
	   
	   $(document).on('change', '#division', function() {
			var dato = $( "#division" ).val();
			//carga los datos de los usuarios sin asignar en el rol seleccionado
			if(dato=='alld'){
				$('#libres').html(allc);
			}
	   });
	
	
	$().ready(function() 
	{
		$('.pasar').click(function() { return !$('#libres option:selected').remove().appendTo('#asignados'); });  
		$('.quitar').click(function() { return !$('#asignados option:selected').remove().appendTo('#libres'); });
		$('.pasartodos').click(function() { $('#libres option').each(function() { $(this).remove().appendTo('#asignados'); }); });
		$('.quitartodos').click(function() { $('#asignados option').each(function() { $(this).remove().appendTo('#libres'); }); });
		
		$('.submit').click(function() { 
			var conf = confirm('Se notificarán a los usuarios seleccionados. ¿Desea continuar?');
			if(!conf){
				return false;	
			}
			
			var cont= $('#asignados option').length;
			if(cont<1){
				alert('No has seleccionado ningún usuario');
				return false;	
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
			  			  
			  var url = "logica/l_notificar.php";
				$.ajax({                        
				   type: "POST",                 
				   url: url,                     
				   data: $("#formulario").serialize(), 
				   success: function(data)             
				   {
				   	//alert(data);
					 $('#divResult').html(data);               
				   }
			   });
			  
			//}
		
			return false;
		});
		
	}); 
	</script>
</body></html>