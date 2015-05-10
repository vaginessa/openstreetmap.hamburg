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
<title>OpenStreetMap Hamburg Startseite</title>
 <link rel="stylesheet" href="<?php echo $url ?>leaflet/leaflet.css" />
 <link rel="stylesheet" href="<?php echo $url ?>css/main.css" />
 <script>
   var params='<?php echo htmlentities($_GET['p']); ?>';
   var baseurl='<?php echo $url; ?>';
 </script>
 <script src="<?php echo $url ?>leaflet/leaflet.js"></script>
 <script src="<?php echo $url ?>js/jquery-1.11.2.min.js"></script>
 <script src="<?php echo $url ?>js/leaflet-osm.js"></script>
 <script src="<?php echo $url ?>js/osmtogeojson.js"></script>
 <script src="<?php echo $url ?>js/main.js"></script>
</head>
<body>
<div id='sidebarmain'>
<div id='sidebar'>
<div id='objInfo'>
<?php 
print getPathInfo($_GET['p']);
?>
</div>
<h2>Bezirke</h2>
<ul>
<?php
foreach ($bezirke as $bezirk) {
	?><li><a href="<?php echo $url;
	?>Bezirk/<?php print $bezirk; 
	?>" onclick="return loadBezirk('<?php print $bezirk ?>');"><?php print $bezirk ?></a></li><?php
}

?></ul>
<h2>Stadtteile</h2>
<ul><?php
foreach ($stadtteile as $key => $stadtteil) {
	?><li><a href="<?php 
	echo $url . 'Stadtteil/' . $stadtteil; 
	?>" onclick="return loadStadtteil('<?php print $stadtteil ?>');"><?php 
	print $stadtteil; 
	?></a></li><?php
}
?>
</ul>
<h2>Infos</h2>
<ul>
<li>Besuchen Sie den <a href="https://wiki.openstreetmap.org/wiki/Hamburger_Mappertreffen" target="_blank">Hamburger OSM-Stammstisch</a>!</li>
<li><a href="https://wiki.openstreetmap.org/wiki/Openstreetmap.hamburg" traget="_blank">Über diese Karte.</a></li>
</ul>
</div>
<div id='drag'>
</div>
</div>
<div id='map'>
</div>
</body>
</html>
