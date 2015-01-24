'use strict';

var map, restaurant_icon; 
var poi_markers = new Array();
var overpass_result= '[out:json][timeout:25];'+
// gather results
'('+
  // query part for: “restaurant”
'node["amenity"="restaurant"]({{bbox}});'+
'way["amenity"="restaurant"]({{bbox}});'+
'relation["amenity"="restaurant"]({{bbox}});'+
');'+
// print results
'out body center;';

var objLayer;

var overpass_boundary='[out:xml][timeout:25];'+
    'area(3602618040)->.searchArea;'+
    '('+
    '    relation["boundary"="administrative"]["admin_level"="{{alevel}}"]["name"="{{name}}"](area.searchArea);'+
    ');'+
    'out body;'+
    '    >;'+
    'out skel qt;';
function openSideBar(e) {
    var text;
    var tags=e.target.osmtags;
    var poi_type=e.target.poi_type;
    var osmlink=e.target.osmlink;
    if(tags.name == undefined) {
        text = "<strong>"+ poi_type +"</strong>";
    } else {
        text = "<strong>" + tags.name + " ("+ poi_type +")</strong>";
    }
    if (tags['addr:street'] != undefined) {
        text += '<div>'+tags['addr:street']+'</div>';
    }
    if (tags.wheelchair === 'no') {
        text += '<div>Nicht Rollstuhl geignet</div>';
    }
    if (tags.wheelchair === 'yes') {
        text += '<div>Rollstuhl geignet</div>';
    }
	text += "<div class='more_on_osm'><a href='"+osmlink+"'>Mehr auf OpenStreetMap.org</a></div>";
    text +="<div><a href='javascript:$(\"#sidebar\").css({ display: \"none\" })'>hide</a></div>";
    $( "#sidebar" ).html(text).css({ display: "block" });
}

function setPoiMarker(poi_type, icon, lat, lon, tags, osmid, osmtype) {
	var mrk = L.marker([lat, lon], {icon: icon});
	var osmlink = "https://www.openstreetmap.org/"+osmtype+"/"+osmid;


    mrk.osmlink=osmlink;
    mrk.osmtags=tags;
    mrk.poi_type=poi_type;
//	mrk.bindPopup(popup_content);
    mrk.on('click', openSideBar);
	poi_markers.push(mrk);
	mrk.addTo(map);
    }


function element_to_map(data) {
    $.each(poi_markers, function(_, mrk) {
	map.removeLayer(mrk);
    });

    $.each(data.elements, function(_, el) {
	if(el.lat == undefined) {
	    el.lat = el.center.lat;
	    el.lon = el.center.lon;
	}

	if(el.tags != undefined && el.tags.entrance != "yes") {
	    var mrk, popup_content;

	    if(el.tags.amenity == "restaurant" ) {
		setPoiMarker("Restaurant", restaurant_icon, el.lat, el.lon, el.tags, el.id, el.type);
	    }
	}
    });
}


function get_op_elements() {
    if(map.getZoom() < 12) {
	return null;
    }

    var bbox = map.getBounds().getSouth() + "," + map.getBounds().getWest() + "," + map.getBounds().getNorth() +  "," + map.getBounds().getEast();

    localStorage.setItem("pos_lat", map.getCenter().lat)
    localStorage.setItem("pos_lon", map.getCenter().lng)
    var qury=overpass_result.replace(/{{bbox}}/g,bbox);
    $.ajax({
	url: "https://overpass-api.de/api/interpreter",
	data: {
	    "data": qury
	},
	success: element_to_map
    });
}

function put_stadteil_to_map(xml) {
    if (objLayer) {
	map.removeLayer(objLayer);
    }
    objLayer=new L.OSM.DataLayer(xml).addTo(map);
    map.fitBounds(objLayer.getBounds());

}
function loadBoundary(name,alevel) {
    var place_overpass=overpass_boundary.replace(/{{alevel}}/g,alevel).replace(/{{name}}/g,name);

    $.ajax({
        url: "https://overpass-api.de/api/interpreter",
        data: {
            "data": place_overpass
        },
        success: put_stadteil_to_map
    });

}
function displayPathContent(content) {
    $('#objInfo').html(content);
    console.log(content);
}
function loadPathContent(path) {
    $.ajax({
        url: baseurl+"getPathContent.php",
        data: {
	    "path": path
	},
	success: displayPathContent
    });
}
function loadBezirk(name) {
    document.getElementsByTagName('title')[0].innerHTML = "OpenStreetMap Hamburg Bezirk "+name;
    window.history.pushState( {} , baseurl+'Bezirk/'+name ,baseurl+ 'Bezirk/'+name );
    loadPathContent('Bezirk/'+name);
    loadBoundary(name,'9');
    return false;
}
function loadStadtteil(name) {
    document.getElementsByTagName('title')[0].innerHTML = "OpenStreetMap Hamburg Stadtteil "+name;
    window.history.pushState( {} , baseurl+'Stadtteil/'+name , baseurl+'Stadtteil/'+name );
    loadPathContent('Stadtteil/'+name);
    loadBoundary(name,'10');
    return false;
}
$(function() {

//    restaurant_icon = L.icon({
//	iconUrl: '/img/restaurant.png',
//	iconSize: [30, 30],
//	iconAnchor: [15, 15],
//	popupAnchor: [0, -15]
 // /  });

    map = L.map('map',{
	'zoomControl': false,
    }).setView([53.505, 9.95], 10);
    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
	attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
	maxZoom: 18,
    }).addTo(map);


    if ((params!= undefined) && ((params.indexOf('Stadtteil/')==0) || (params.indexOf('Bezirk/')==0))) {
	var alevel='10';
	if (params.indexOf('Bezirk/')==0) {
	    alevel='9';
	}
	var name=params.replace(/^Stadtteil\//,'').replace(/^Bezirk\//,'');
	loadBoundary(name,alevel);
    }

    L.control.zoom({ position : 'topright'}).addTo(map);
    // poi reload on map move
//    map.on('moveend', get_op_elements);

    // initial poi load
//    get_op_elements();

});
