<?php 

	include_once('logica/l_get_info.php');
	$lista = getDatosProc('');
?>
<!doctype html>
<html><head>
    <title>Reporte de procesos SGDP</title>

    
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
<link href="assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css" />
<style type="text/css" class="init">
	
	tfoot input {
		width: 100%;
		padding: 3px;
		box-sizing: border-box;
	}
	body{
	margin:10px;
	overflow-x:hiden; 
	padding-top: 80px;}
	</style>

    
    <script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script> 
	<script>
	$(document).ready(function() {
	var table = $('#example').DataTable(	
				{
				"order": [[ 0, "asc" ],[ 4, "desc" ]],
				dom: 'B<"clear">lfrtip',
				"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
				 buttons: {
					buttons: [ 'excel' ]
				},
				fixedHeader: true,
				"language": {
						"lengthMenu": "Mostrar _MENU_ registros por p&aacute;gina",
						"zeroRecords": "Ning&uacute;n resultado",
						"info": "Mostrando p&aacute;g _PAGE_ de _PAGES_",
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
      <h1 align="center" class="page_title">Reporte de procesos SGDP</h1>
	  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex6-collapse">
        <span class="sr-only">Desplegar navegación</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>      </button>
      <a class="navbar-brand" href="#" target="_blank">Reporte de tareas SGDP</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div  class="collapse navbar-collapse navbar-ex6-collapse">
      <ul class="nav navbar-nav">
			<li ><a href="det_nivel1.php?idDiv=&tip=f" >Resumen tareas finalizadas</a></li>
			<li ><a href="det_nivel1.php?idDiv=&tip=p" >Resumen tareas pendientes</a></li>          
      </ul>
    </div><!-- /.navbar-collapse -->
  </nav>	  <br><br>
	<div id="tabla">
	 <?php 
		echo $lista;
	?>	
      </div>
    </div>
  </div>
  <div class="fw-footer"></div>
</div>
</body>
</html>
