<?php

function getRootURL() {
	 return "/osmhh/";
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

function getTagsFromOrt($name) {
$filename_suburb="cache/suburb.json";
 $boundarys=json_decode(getFromOverpassWithCache($filename_suburb,$url_suburb));

$bezirke=array();
$stadteile=array();
foreach ($boundarys->{'elements'} as $ele) {
	if ($ele->{'tags'}->{'name'} == $name) {
	  return $ele->{'tags'};
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
