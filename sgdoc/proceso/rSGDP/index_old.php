<?php
include_once('logica/l_get_info.php');
$datos = getDatos('');
?>
<!DOCTYPE HTML>
<!--
	Phantom by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
<head>
<title>Phantom by HTML5 UP</title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
<link rel="stylesheet" href="assets/css/main.css" />
<noscript>
<link rel="stylesheet" href="assets/css/noscript22.css" />
</noscript>
<style type="text/css">
.jqstooltip { 
position: absolute;
left: 0px;
top: 0px;
visibility: hidden;
background: rgb(0, 0, 0) transparent;
background-color: rgba(0,0,0,0.6);
filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)
-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";
color: white;
font: 10px arial, san serif;
text-align: left;
white-space: nowrap;
padding: 5px;
border: 1px solid white;
width:150px;
height:50px}
.jqsfield { color: white;font: 10px arial, san serif;text-align: left;}
</style>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="jquery.sparkline.js"></script>
 <script type="text/javascript">
    $(function() {
        /** This code runs when everything has been loaded on the page */
        /* Inline sparklines take their values from the contents of the tag */
        $('.inlinesparkline').sparkline(); 

        /* Sparklines can also take their values from the first argument 
        passed to the sparkline() function */
        var myvalues = [10,8,5,7,4,4,1];
        $('.dynamicsparkline').sparkline(myvalues);

        /* The second argument gives options such as chart type */
        $('.dynamicbar').sparkline('html', {
			type: 'bar',
			barColor: 'green', 
			barWidth: '15px',
			barHeigth: '15px'
			} );

        /* Use 'html' instead of an array of values to pass options 
        to a sparkline with data in the tag */
        $('.inlinebar').sparkline('html', {type: 'bar', barColor: 'red'} );
    });
    </script>
</head>
<body class="is-preload">
<!-- Wrapper -->
<div id="wrapper">
  <!-- Header -->
  <header id="header">
    <div class="inner">
      <!-- Logo -->
      <a href="index.html" class="logo"> <span class="symbol"><img src="images/logo.svg" alt="" /></span><span class="title">SGDP</span> </a>
      <!-- Nav -->
      <nav>
        <ul>
          <li><a href="#menu">Menu</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <!-- Menu 
  <nav id="menu">
    <h2>Menu</h2>
    <ul>
      <li><a href="index.html">Home</a></li>
      <li><a href="generic.html">Ipsum veroeros</a></li>
      <li><a href="generic.html">Tempus etiam</a></li>
      <li><a href="generic.html">Consequat dolor</a></li>
      <li><a href="elements.html">Elements</a></li>
    </ul>
  </nav>-->
  <!-- Main -->
  <div id="main">
    <div class="inner">
      <header>
        <h1>Reporte de tareas por División/Unidad </h1>
      </header>
      <section class="tiles">
	  	<?php echo $datos; ?>
       <table border="1">
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

      </section>
    </div>
  </div>
  <!-- Footer -->
  <footer id="footer">
    <div class="inner">
      <section>
        <h2>Get in touch</h2>
      </section>
      <ul class="copyright">
        <li>&copy; Untitled. All rights reserved</li>
        <li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
      </ul>
    </div>
  </footer>
</div>
<!-- Scripts -->
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
