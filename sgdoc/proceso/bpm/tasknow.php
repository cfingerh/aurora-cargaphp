<?php 
if(!isset($_GET['numTarea'])){
	die('Sin info');
}

$numTarea=$_GET['numTarea'];

include('connect.php');
$sql='SELECT
sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA",
sgdp."SGDP_TAREAS"."A_TIPO_DE_BIFURCACION"
FROM
sgdp."SGDP_INSTANCIAS_DE_TAREAS"
INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
WHERE
sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" =  '.$numTarea.';';
	//echo $sql; 
	//return;

	$result=pg_query($dbconn, $sql);
	$row = pg_fetch_array($result, 0);
	$nombreTarea=	$row['A_NOMBRE_TAREA'];
	$tipoB='false';
	if($row['A_NOMBRE_TAREA']=='OR'){
		$tipoB='true';
	}
	//determinar tareas pasadas
	$sql='SELECT distinct
	sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA"
	FROM
	sgdp."SGDP_INSTANCIAS_DE_TAREAS"
	INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
	INNER JOIN sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DE_ORIGEN"
	INNER JOIN sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS" ON sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" = sgdp."SGDP_ACCIONES_HIST_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA"
	WHERE
	sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_INSTANCIA_DE_TAREA_DESTINO" = '.$numTarea.' AND
	sgdp."SGDP_HISTORICO_DE_INST_DE_TAREAS"."ID_ACCION_HISTORICO_INST_DE_TAREA" <> 1 ;';
		
	$tPast=$result=null;
	$result=pg_query($dbconn, $sql);
	for($i=0;$i<pg_num_rows($result);$i++){	
		$row = pg_fetch_array($result, $i);
		$tPast.='<li class="children__item"><div class="node">
						<div class="node__text">'.$row['A_NOMBRE_TAREA'].'</div>
						<input type="text" class="node__input">
					</div></li>';
	}
	
	//determinar tareas futuras
	$sql2='SELECT distinct
sgdp."SGDP_TAREAS"."A_NOMBRE_TAREA"
FROM
sgdp."SGDP_TAREAS"
INNER JOIN sgdp."SGDP_REFERENCIAS_DE_TAREAS" ON sgdp."SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA_SIGUIENTE" = sgdp."SGDP_TAREAS"."ID_TAREA"
WHERE
sgdp."SGDP_REFERENCIAS_DE_TAREAS"."ID_TAREA" = (SELECT
sgdp."SGDP_TAREAS"."ID_TAREA"
FROM
sgdp."SGDP_INSTANCIAS_DE_TAREAS"
INNER JOIN sgdp."SGDP_TAREAS" ON sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_TAREA" = sgdp."SGDP_TAREAS"."ID_TAREA"
WHERE
sgdp."SGDP_INSTANCIAS_DE_TAREAS"."ID_INSTANCIA_DE_TAREA" = '.$numTarea.');';
//echo $sql2;
		
	$result2=pg_query($dbconn, $sql2);
	$tFut=null;
	for($j=0;$j<pg_num_rows($result2);$j++){	
		$row2 = pg_fetch_array($result2, $j);
		$tFut.='<li class="children__item">
					<div class="node">
						<div class="node__text"><div>'.$row2['A_NOMBRE_TAREA'].'</div></div>
						<input type="text" class="node__input">
					</div>
				</li>';
		
	}

?>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title>Mindmap</title>
		<link rel="stylesheet" href="css/mindmap.css">
	</head>
	<body>
		<div class="mindmap">
			<ol class="children children_leftbranch">
				<?php echo $tPast;?>
			</ol>
			<div class="node node_root">
				<div class="node__text" style="position: relative;
	display: inline-block;">Tu tarea<?php //echo $nombreTarea; ?></div>
				<input type="text" class="node__input">
			</div>
			<ol class="children children_rightbranch">
				<?php echo $tFut;?>
			</ol>
		</div>
		<iframe src="http://sgdocb/proceso/bpm/this_task.php?idTask=Task_0woyx1t&idProc=429&idInsProc=4136" width="300px"></iframe>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="jquery/mindmap.js"></script> 
		<script>
			
			$(function(){
				$('.mindmap').mindmap();
			});
			
		</script>
	</body>
</html>