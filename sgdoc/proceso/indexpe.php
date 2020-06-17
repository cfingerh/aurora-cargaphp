<?php 
include_once("logica/getDrawProcessExe.php");
//include_once("logica/getDrawProcessExe2.php");
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Historial - SGDP</title>
<link rel="stylesheet" href="css/mindmap.css">
<style type="text/css">
#menu_principal #b_homologacion a {
	background-position:0 -23px;
}
.style1 {color: #309fca}
.style2 {color: #000000}
.style3 {font-size: 12pt; font-weight: bold; white-space: pre-wrap}
.style4 {font-size: 9pt}
body { font-family:"Arial Narrow", Helvetica, sans-serif; color:#666666; font-size:13px; margin:0;	padding:0; }
td {
	/*white-space: pre-wrap;*/
}
</style>
<link rel="stylesheet" type="text/css" href="css/jqcloud.css" />
<!-- JS 
<script src="homologacion_archivos/tamano_texto.js" type="text/javascript"></script>-->
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
</head>
<body>



<?php 

if(isset($_GET["idtar"]) ){
	echo getHistoriaExe($_GET["idtar"]);
}


?>
</body>
</html>