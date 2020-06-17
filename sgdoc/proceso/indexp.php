<?php 

if(isset($_GET["idproc"]) ){
	//echo getHistorial($_GET["idproc"]);
	/*echo '<object class="scaling-svg" type="image/svg+xml" data="bpm/diagramas/'.$_GET["idproc"].'.svg" >
  Necesitas algun plugin para ver SVG. 
  </object>';*/
 	$url="bpm/diagramas/".$_GET["idproc"].".svg"; // url de la pagina que queremos obtener  
	$url_content = '';  
	$file = fopen($url, 'r');  
	if($file){  
		while(!feof($file)) {  
			$url_content.=fgets($file);  
		}  
		fclose ($file); 
		//$patron = '/width="[0-9]+"/i';
		//$url_content=preg_replace('/width="[0-9]+"/i', 'width="100%"', $url_content, 1);
		//$url_content=preg_replace('/height="[0-9]+"/i', 'height="100%"', $url_content, 1);
		echo $url_content;
	} 
}
?>