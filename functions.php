<?php

function osmTagName($val) {
  return "<b>Name:</b> ".$val.'<br/>';
}
function osmTagWikipedia($val) {
 preg_match('/^(.*):(.*)$/', $val, $treffer);

 return "<b>Wikipedia Seite:</b><a href='http://".$treffer[1].".wikipedia.org/wiki/$treffer[2]' target='_blank'>".$val."</a></br>";
}
function osmTagIsIn($val) {
 return "<b>Ist in:</b> ".$val.'<br/>';
}
function osmTagIsInCity($val) {
 return "<b>Ist in Stadt:</b> ".$val.'<br/>';
}

function osmAdminLevel($val) {
 $adminLevel = [
   "2"=>'2 Landesgrenze',
   "4"=>'4 Bundesland-Grenze',
   "5"=>'5 Regierungsbezirks-Grenze',
   "6"=>'6 Kreisgrenze',
   "7"=>'7 Verwaltungsgemeinschaft',
   "8"=>'8 Stadt/Gemeinde',
   "9"=>'9 Stadtbezirk',
   "10"=>'10 Stadtteil'
 ];
 if (isset($adminLevel[$val])) {
   return "<b>Grenzebene:</b> ".$adminLevel[$val].'<br/>';
 }
 return "<b>Admin-Level:</b> ".$val.' (Vermutlich nicht richtig eingetragen)<br/>';
}

function osmNamePrefix($val) {
  return "<b>Vorsatzwort:</b> ".$val."<br/>";
}

function osmNote($val) {
 return '<b>Notiz für den OSM Mitwirkenden:</b> <span style="font-size:75%">'.$val."</span><br/>";
}
function osmTagIgnore($val) {
 return "";
}

function osmType($val) {
 if ($val == "boundary") {
  return "<b>Typ:</b> Grenze<br/>";
 }
 return "<b>Typ:</b> ".$val."<br/>";
}

function osmBoundary($val) {

 $boundary = [
   "administrative"=>'Verwaltungsgrenze',
   "maritime"=>'Seegrenze',
   "national_park"=>'Nationalpark',
   "political"=>'Wahlkreis',
   "postal_code"=>'PLZ Gebiet',
   "protected_area"=>'Schutzgebiet',
 ];
 if (isset($boundary[$val])) {
   return "<b>Grenztyp:</b> ".$boundary[$val].'<br/>';
 }
 return "<b>Grenztyp:</b> ".$val.' (evtl. nicht richtig eingetragen)<br/>';


}
function displayTag($key, $value) {
  $osmTag = [
    "name" => "osmTagName",
    "wikipedia" => "osmTagWikipedia",
    "is_in" => "osmTagIsIn",
    "is_in:city" => "osmTagIsInCity",
    "admin_level" => "osmAdminLevel",
    "boundary" => "osmBoundary",
    "name:prefix" => "osmNamePrefix",
    "type" => "osmType",
    "note" => "osmNote",
    "boundary_type" => "osmTagIgnore",
    "TMC:cid_58:tabcd_1:Class" => "osmTagIgnore",
    "TMC:cid_58:tabcd_1:LCLversion" => "osmTagIgnore",
    "TMC:cid_58:tabcd_1:LocationCode" => "osmTagIgnore",
  ];

   if (isset($osmTag[$key])) {
     $func=$osmTag[$key];
     return $osmTag[$key]($value);
   } else {
     return "<i>".$key.":".$value."</i><br/>";
   }
}
function getRootURL() {
	 return "/osmhh/";
}


function getPathInfo($path) {

$tags=[];
$rtn="";
if (startsWith($path,"Bezirk/")) {
$bezirk=str_replace('Bezirk/','',$path);
$rtn="<h2>Bezirk ".$bezirk."</h2>";
$osm=getOsmFromOrt($bezirk,'9');
}

if (startsWith($path,"Stadtteil/")) {
$ort=str_replace('Stadtteil/','',$path);
$rtn="<h2>Stadtteil ".$ort."</h2>";
$osm=getOsmFromOrt($ort,'10');
}

if ($rtn != "") {
$rtn=$rtn.'Folgende Daten sind in OpenStreetMap zu ';
$objName=$osm['type'];
if ($osm['type']=="relation") {
   $objName='der Relation';
}
if ($osm['type']=="node") {
   $objName='der Knotenpunkt';
}
if ($osm['type']=="way") {
   $objName='der Linie';
}
$objName=$objName.' '.$osm['id'];
$rtn=$rtn.'<a href="https://www.openstreetmap.org/'.$osm['type']."/".$osm['id'].'" target="_blank">'.$objName.'</a>  gespeichert:<br/>';
foreach($osm['tags'] as $key => $value) {
	$rtn=$rtn.displayTag($key, $value);
}
}
$rtn=$rtn.'Sie können diese <a href="https://www.openstreetmap.org/edit?'.$osm['type']."=".$osm['id'].'" target="_blank">selbst korrigieren oder ergänzen.</a>';
return $rtn;
}


function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}

function getFromOverpassWithCache($filename,$url) {
 $reload=0;

 if (file_exists($filename)) {
    $stat=stat($filename);
   
// Anfrage alle 12h
    if ((time()-$stat['mtime'])>60*60*12) {
       $reload=1;
    }
 } else {
   $reload=1;
 }
 if ($reload==0) {
  return file_get_contents($filename);
 }
 $data = file_get_contents($url);
 $myfile = fopen($filename, "w") or die("Unable to open file $filename!");
 fwrite($myfile, $data);
 fclose($myfile);
 return $data;
}

function getOsmFromOrt($name,$alevel) {
$filename_suburb="cache/suburb.json";
 $boundarys=json_decode(getFromOverpassWithCache($filename_suburb,$url_suburb));

$bezirke=array();
$stadteile=array();
foreach ($boundarys->{'elements'} as $ele) {
	if (($ele->{'tags'}->{'name'} == $name) && ($ele->{'tags'}->{'admin_level'} == $alevel)) {
	  return [
 	  'tags'=>$ele->{'tags'},
	  'id'=>$ele->{'id'},
	  'type'=>$ele->{'type'}
         ];
	}
}
}

function getBezirkStadtteile() {

$filename_suburb="cache/suburb.json";
$url_suburb="http://overpass-api.de/api/interpreter?data=%2F*%0AThis%20has%20been%20generated%20by%20the%20overpass-turbo%20wizard.%0AThe%20original%20search%20was%3A%0A%E2%80%9Cboundary%3Dadminstrative%20in%20Hamburg%E2%80%9D%0A*%2F%0A%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3B%0A%2F%2F%20fetch%20area%20%E2%80%9CHamburg%E2%80%9D%20to%20search%20in%0Aarea%283602618040%29-%3E.searchArea%3B%0A%2F%2F%20gather%20results%0A%28%0A%20%20%2F%2F%20query%20part%20for%3A%20%E2%80%9Cboundary%3Dadminstrative%E2%80%9D%0A%20%20relation%5B%22boundary%22%3D%22administrative%22%5D%28area.searchArea%29%3B%0A%29%3B%0A%2F%2F%20print%20results%0Aout%20body%3B%0A%3E%3B%0Aout%20skel%20qt%3B";



$boundarys=json_decode(getFromOverpassWithCache($filename_suburb,$url_suburb));

$bezirke=array();
$stadteile=array();
foreach ($boundarys->{'elements'} as $ele) {
	if ($ele->{'type'}=='relation') {
	   if ($ele->{'tags'}->{'admin_level'}==10) {
	   	$stadtteile[]=$ele->{'tags'}->{'name'};
	   } 
	   if ($ele->{'tags'}->{'admin_level'}==9) {
	   	$bezirke[]=$ele->{'tags'}->{'name'};
	   } 
	}
}
sort($stadtteile);
sort($bezirke);
return ['bezirke'=>$bezirke,
       'stadtteile'=>$stadtteile];
}
