<?php
require 'functions.php';
$url=getRootURL();
$arr=getBezirkStadtteile();
//var_dump($arr);
$bezirke=$arr['bezirke'];
//               stadtteile
$stadtteile=$arr['stadtteile'];
sort($stadtteile);
sort($bezirke);
header('Content-Type: text/html; charset=utf-8');
?>
<html>
<head>
 <link rel="stylesheet" href="<?php echo $url ?>leaflet/leaflet.css" />
 <link rel="stylesheet" href="<?php echo $url ?>css/main.css" />
 <script>
   var params='<?php echo htmlentities($_GET['p']); ?>';
 </script>
 <script src="<?php echo $url ?>leaflet/leaflet.js"></script>
 <script src="<?php echo $url ?>js/jquery-1.11.2.min.js"></script>
 <script src="<?php echo $url ?>js/osmtogeojson.js"></script>
 <script src="<?php echo $url ?>js/main.js"></script>
</head>
<body>
<div id='sidebar'>
<?php 
if (startsWith($_GET['p'],"Bezirk/")) {
$bezirk=str_replace('Bezirk/','',$_GET['p']);
?>
<h2>Bezirk <?php print $bezirk;?></h2>

<a href="<? echo $url;?>">zurück</a>
<?php
} elseif (startsWith($_GET['p'],"Stadtteil/")) {
$stadtteil=str_replace('Stadtteil/','',$_GET['p']);
?>
<h2>Stadtteil <?php print $stadtteil;?></h2>

<a href="<? echo $url;?>">zurück</a>
<?php
} else {

?>
<h2>Bezirke</h2><?php
foreach ($bezirke as $bezirk) {
	?><li><a href="<?php echo $url ?>Bezirk/<?php print $bezirk ?>"><?php print $bezirk ?></a></li><?php
}

?><h2>Stadtteile</h2><?php
foreach ($stadtteile as $key => $stadtteil) {
	?><li><a href="<?php 
	echo $url . 'Stadtteil/' . $stadtteil; 
	?>"><?php 
	print $stadtteil; 
	?></a></li><?php
}}
?>
</div>
<div id='map'>
</div>
</body>
</html>
