<?php 
include_once('logica/l_roles_users.php');
$datos=getRolesTotal();

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Roles de usuarios en subprocesos del SGDP</title>
<!--<link rel="shortcut icon" type="image/png" href="/media/images/favicon.png">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.datatables.net/rss.xml">
	<link rel="stylesheet" type="text/css" href="/media/css/site-examples.css?_=6e5593ad4c5375eef5d919cfc10a0a54">-->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
<link href="css/bootstrap.css" rel="stylesheet">
<style type="text/css" class="init">
	
	tfoot input {
		width: 100%;
		padding: 3px;
		box-sizing: border-box;
	}
	body{
	margin:10px;
	overflow-x:hiden; 
	font-family: Verdana, Arial, Helvetica, sans-serif;
	padding-top: 70px;}

	</style>
<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<!--<script type="text/javascript" language="javascript" src="jquery/jquery.dataTables.min.js"></script>-->

<script type="text/javascript" class="init">
	
$(document).ready(function() {
	var table = $('#example').DataTable(	
				{
				"order": [[ 0, "asc" ], [ 3, "asc" ], [ 5, "asc" ]],
				dom: 'B<"clear">lfrtip',
				"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
				 buttons: {
					buttons: [ 'excel' ]
				},
				fixedHeader: true,
				"language": {
						"lengthMenu": "Mostrar _MENU_ registros por página",
						"zeroRecords": "Ningún resultado",
						"info": "Mostrando pág _PAGE_ de _PAGES_",
						"infoEmpty": "Sin datos disponibles",
						"infoFiltered": "(Filtrado de _MAX_ registros totales)",
						"oPaginate" : {
								"sFirst" : "Primero",
								"sLast" : "\u00faltimo",
								"sNext" : "Siguiente",
								"sPrevious" : "Anterior"
						  },
						  "buttons" : {
									"excel" : 'Exportar a Excel'
						}

					}
				}
				);
	
	// Setup - add a text input to each footer cell
	$('#example tfoot th').each( function () {
		var title = $(this).text();
		$(this).html( '<input type="text" placeholder="Buscar '+title+'" />' );
	} );
	
	$('#example tfoot th.sel').each( function () {
		var title = $(this).text();
		$(this).html( '<select><option value="">Todos</option><option value="S&iacute;">Sí</option><option value="No">No</option></select>' );
	} );
	
	 $('#example tbody').on( 'click', 'tr', function () {
        $(this).toggleClass('selected');
    } );

	// DataTable
	

	// Apply the search
	table.columns().every( function () {
		var that = this;

		$( 'input', this.footer() ).on( 'keyup change', function () {
			if ( that.search() !== this.value ) {
				that
					.search( this.value )
					.draw();
			}
		} );
	} );
	
	// Apply the search
	table.columns().every( function () {
		var that = this;

		$( 'select', this.footer() ).on( 'change', function () {
			if ( that.search() !== this.value ) {
				that
					.search( this.value )
					.draw();
			}
		} );
	} );
	
/**/	
} );


</script>
</head>
<body class="wide comments example">
<div class="fw-container">
  <div class="fw-body">
    <div class="content">
      <h1 align="center" class="page_title">Roles de usuario en subprocesos del SGDP </h1>
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
        <li><a href="asig_user.php">Asignar usuarios a roles</a></li>
        <li class="active"><a href="roles_user.php">Reporte de roles</a></li>
		<li ><a href="info_proceso_exp.php">Información por expediente</a></li>
		<li><a href="phpgen/SGDP_AUTORES.php">Mantenedor Autores</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </nav>
  <div id="tablaDatos">
	  <table id="example" class="display" cellspacing="0" width="100%">
        <thead>
          <tr>
		    <th>ID Mapa de Procesos</th>
		    <th>ID Subproceso</th>
			<th>¿Vigente?</th>
			<th>Subproceso</th>
            <th>División/Unidad Responsable</th>
            <th>Rol</th>
            <th>Usuario</th>
            
          </tr>
        </thead>
        <tfoot>
          <tr>
		    <th>ID Mapa de Procesos</th>
		    <th>ID Subproceso</th>
			<th class="sel">¿Vigente?</th>
			<th>Subproceso</th>
            <th>División/Unidad Responsable</th>
            <th>Rol</th>
            <th>Usuario</th>
          </tr>
        </tfoot>
        <tbody>
          <?php echo $datos?>
	    </tbody>
	  </table><br><br>
      </div>
    </div>
  </div>
  <div class="fw-footer"></div>
</div>
</body>
</html>