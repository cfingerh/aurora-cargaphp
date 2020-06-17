<?php 

include_once('logica/l_get_info.php');

$nodos=getNodos();
$relaciones=getRelaciones('');
$hechos=getHechos('');
//$listaNodos=getListaNodos();
//$listaHechos=getListaHechos();

?>
<!doctype html>
<html><head>
    <title>Reporte Gestión SGDP</title>

    <style type="text/css">
        #mynetwork {
            width: 900px;
            height: 800px;
            border: 1px solid lightgray;
        }
		
		
/* Style the tab2 */
.tab2 {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons inside the tab2 */
.tab2 button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
}

/* Change background color of buttons on hover */
.tab2 button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab2 button.active {
    background-color: #ccc;
}

/* Style the tab2 content */
.tabcontent2 {
    display: none;
    padding: 6px 12px;
    -webkit-animation: fadeEffect 1s;
    animation: fadeEffect 1s;
}

/* Fade in tabs */
@-webkit-keyframes fadeEffect {
    from {opacity: 0;}
    to {opacity: 1;}
}

@keyframes fadeEffect {
    from {opacity: 0;}
    to {opacity: 1;}
}
		
		
    </style>

    
    <link href="dist/vis-network.min.css" rel="stylesheet" type="text/css"/>	
  	<link href="dist/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  	<!--<link href="assets/css/bootstrap.css" rel="stylesheet">-->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
	
	<script type="text/javascript" src="dist/vis.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.1/moment-with-locales.min.js"></script>
  	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
 
    <script type="text/javascript">
		var nodos, network, edgest;
        function draw() {
            // create some nodes
            var nodes = [                
				<?php
				for($i=0;$i<count($nodos);$i++){
				if($i!=0){
						echo ',
						';
					}
					echo '{id: '.$nodos[$i][0].', "label": "'.$nodos[$i][2].'", "group": '.$nodos[$i][3].', "title": "'.$nodos[$i][2].'" }';				
				}
				?>
            ];
            // create some edges
            edgest = [
				<?php
				for($i=0;$i<count($relaciones);$i++){
					if($i!=0){
						echo ',
						';
					}
					echo '{ id: \''.$relaciones[$i][4].'\', "from": "'.$relaciones[$i][0].'", "to": '.$relaciones[$i][1].', "title": "<label onClick=\"upInfoHecho('.$relaciones[$i][4].', \'titulo\')\">'.$relaciones[$i][2].'</label>"}';
				}
				?>
				];
				
			
			nodos = new vis.DataSet(nodes);

            // create a network
            var container = document.getElementById('mynetwork');
            var data = {
                nodes: nodos,
                edges: edgest
            };
            var opciones = {
                nodes: {
                    shape: 'dot',
                    size: 16
                },
				edges: {
					hoverWidth: function (width) {return width+5;}
				},
				locale: 'es',
				interaction:{
					dragNodes:true,
					dragView: true,
					hideEdgesOnDrag: false,
					hideNodesOnDrag: false,
					hover: true,
					hoverConnectedEdges: true,
					selectable: true,
					selectConnectedEdges: true,
					tooltipDelay: 100,
					zoomView: true
				  },

                physics: {
                    forceAtlas2Based: {
                        gravitationalConstant: -26,
                        centralGravity: 0.005,
                        springLength: 230,
                        springConstant: 0.18
                    },
                    maxVelocity: 146,
                    solver: 'forceAtlas2Based',
                    timestep: 0.35,
                    stabilization: {iterations: 150}
                }
            };
			
			//generar la red de nodos
            network = new vis.Network(container, data, opciones);
			
			network.on("selectNode", function (params) {
				console.log('selectNode Event:', params);
				setMenu(params.nodes[0]);
			});
			
			network.on("doubleClick", function (params) {
				console.log('doubleClick Event:', params);
				try{
					if( params.nodes[0]==null){
						//alert(this.getEdgeAt(params.pointer.DOM));
						var idEdge = params.edges[0].split('_');
						upInfoHecho(idEdge[0], '');
					}
				}catch{
					return;
				}
			});
			getTimeLineM();
        }
		
		function setMenu(_params){
			//alert(_params);
			var dato=_params;
			 $.post("logica/l_get_info.php", { func: "getMenu", idNodo: dato},
				function(data){
					//alert(data);
					$('#detalleNodo').html(data);
					//var newColor = '#' + Math.floor((Math.random() * 255 * 255 * 255)).toString(16);
				});	
		}
		
		function selectNodo(_idNodo){
			network.selectNodes([_idNodo]);
		}
		
		function getTimeLine(_idNodo){
		
			$('#timeline').html('');
			
			var dato=_idNodo;
		 	$.post("logica/l_get_info.php", { func: "getHechoNodo", idNodo: dato},
				function(data){
					
					var itemes = data.split("//");
					var items = new vis.DataSet([]);
					for(var i=0; i<itemes.length;i++){
						var val = itemes[i].split("||");
						items.add([
							{id: val[0], content: '<label onClick="upInfoHecho('+val[0]+', \'titulo\')">'+val[1]+'</label>', start: val[2]}
						])
					}
					/*
					items.add([
						{id: 1, content: '<label onClick="selectNodo(\'1\')">item 1</label><br>start', start: '2014-01-23'}
					])
					
						var items = new vis.DataSet([
						{id: 'A', content: 'Period A', start: '2014-01-16', end: '2014-01-22', type: 'background'},
						{id: 'B', content: 'Period B', start: '2014-01-25', end: '2014-01-30', type: 'background', className: 'negative'},
						{id: 1, content: '<label onClick="selectNodo(\'1\')">item 1</label><br>start', start: '2014-01-23'},
						{id: 2, content: '<label onClick="selectNodo(\'2\')">item 2</label>', start: '2014-01-18'},
						{id: 3, content: 'item 3', start: '2014-01-21'},
						{id: 4, content: 'item 4', start: '2014-01-19', end: '2014-01-24'},
						{id: 5, content: 'item 5', start: '2014-01-28', type:'point'},
						{id: 6, content: 'item 6', start: '2014-01-26'}
					  ]);
					  items.add(data)*/
					var container = document.getElementById('timeline');
				  var options = {
					/*start: '2014-01-10',
					end: '2014-02-10',*/
					showCurrentTime: true,
					editable: false,
					type: 'point',
					locale: 'es'
				  };
				  var timeline = new vis.Timeline(container, items, options);
			});	
			
		}
		
		function getTimeLineM(){
			$('#timelineMacro').html('');
					  
		 	 var items = new vis.DataSet([
			<?php
				for($i=0;$i<count($hechos);$i++){
					if($i!=0){
						echo ',
						';
					}
					echo '{id: '.$hechos[$i][0].', "content": "<label onClick=\"upInfoHecho('.$hechos[$i][0].')\">'.$hechos[$i][1].'</label>", "start": "'.$hechos[$i][2].'" , type:\'point\'}';				
				}
				?>
			]);
		
		  var container = document.getElementById('timelineMacro');
		  var options = {
			/*start: '2014-01-10',
			end: '2014-02-10',*/
			showCurrentTime: true,
			editable: false,
			locale: 'es',
			type: 'point',
   			showMajorLabels: false
		  };
		
		  var timeline = new vis.Timeline(container, items, options);
		}
		
		function upInfoHecho(_idHecho, _titulo){
			var dato=_idHecho;
		 	$.post("logica/l_get_info.php", { func: "getInfoHecho", idHecho: dato},
				function(data){
					$('#detalleHecho').html(data);	
									
					$.post("logica/l_get_info.php", { func: "getDetInfoHecho", idHecho: dato},
						function(data){
							$('#detalleHecho').html($('#detalleHecho').html()+data);
							$( "#detalleHecho" ).dialog( "open" );
							
					});	
			});	
		}
		
		function updateTimeline(){
		
		
		}
		
    </script>	
	<script>
		function openTab(evt, cityName) {
			var i, tabcontent, tablinks;
			tabcontent = document.getElementsByClassName("tabcontent2");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablinks2");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active", "");
			}
			document.getElementById(cityName).style.display = "block";
			evt.currentTarget.className += " active";
		}
		$( function() {
				$( "#detalleHecho" ).dialog({
				  autoOpen: false,
				  height: "auto",
      			  width: "auto",
				  modal: false,
				  show: {
					effect: "blind",
					duration: 100
				  },
				  hide: {
					effect: "blind",
					duration: 100
				  }
				});
  			} );
		$(document).ready(function() {
			var table = $('#example').DataTable(	
						{
						"order": [[ 0, "desc" ]],
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
			
			//tabla hechos
			var table2 = $('#example2').DataTable({
						"order": [[ 0, "desc" ]],
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
			$('#example2 tfoot th').each( function () {
				var title = $(this).text();
				$(this).html( '<input type="text" placeholder="Buscar '+title+'" />' );
			} );
			
			 $('#example2 tbody').on( 'click', 'tr', function () {
				$(this).toggleClass('selected');
			} );
			
			// DataTable
			
			// Apply the search
			table2.columns().every( function () {
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
			table2.columns().every( function () {
				var that = this;

				$( 'select', this.footer() ).on( 'change', function () {
					if ( that.search() !== this.value ) {
						that
							.search( this.value )
							.draw();
					}
				} );
			} );
			
		} );
</script>
    
</head>

<body onLoad="draw()">
<h1>
    Reporte de gesti&oacute;n SGDP
</h1>
<h2>
    Resumen por Divisi&oacute;n/Unidad </h2>
<table border="1">
  <tr>
    <td width="50%">
      <div id="mynetwork" ></div>
       </td>
	  <td width="50%"><div id="detalleNodo" style="width:auto; height:100%; overflow:scroll; word-wrap: break-word;"></div></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</body>
</html>
