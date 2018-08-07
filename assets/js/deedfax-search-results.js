var defaultCenter = {lat: 30.1, lng: -90.3};
var defaultZoom = 8;
var map;
var infowindow;
var markers = [];


(function($){
	function initMap() {
		map = new google.maps.Map(document.getElementById("search-results-map"), {center: defaultCenter, zoom: defaultZoom});

		infowindow = new google.maps.InfoWindow({
			maxWidth: 800,
		});

		google.maps.event.addListener(map, "click", function(){
			infowindow.close();
		});
	}

	function setNewMarkers(marker_group) {
		if (!map)
			initMap();

		infowindow.close();
		deleteMarkers();
		var bounds = new google.maps.LatLngBounds();
		for (var i = marker_group.length - 1; i >= 0; i--) {
			addMarker(marker_group[i]);
			bounds.extend(marker_group[i]['coordinates']);
		}
		if (bounds.isEmpty()) {
			map.setCenter(defaultCenter);
		} else {
			map.fitBounds(bounds);
		}
	}

	function addMarker(marker) {
		var marker = new google.maps.Marker({
			position: marker["coordinates"],
			id: marker["id"],
			address: marker["address"],
			price: marker["price"],
			map: map,
			title: "Property",
		});
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.setContent('<div class="poi-info-window"><div class="title">'+marker.address+'</div><div class="address">'+marker.price+'</div><div class="view-link"><a href="#id-'+marker.id+'" onclick="scrollToProperty(\'#id-'+marker.id+'\')"><span>Go to property</span></a></div></div>');
			infowindow.open(map,marker);
		});
		markers.push(marker);
	}

	function deleteMarkers() {
		for (var i = markers.length - 1; i >= 0; i--) {
			markers[i].setMap(null);
		}
		markers = [];
	}

	$( document ).ready(function() {
		if (typeof search_results_markers !== 'undefined') {
			setNewMarkers(search_results_markers);
		}
	});

	$("#count").change(function() {
		window.location.href = URL_add_parameter(window.location.href, 'count', $(this).val());
	});



}(jQuery));

function scrollToProperty(target) {
	var offsetHeight = jQuery('#phantom').height();
	jQuery('html, body').animate({
		scrollTop: jQuery(target).offset().top - offsetHeight
	}, 1000, function() {
		jQuery(target).addClass('highlighted');
	});
	return false;
}

function URL_add_parameter(url, param, value){
	var hash       = {};
	var parser     = document.createElement('a');

	parser.href    = url;

	var parameters = parser.search.split(/\?|&/);

	for(var i=0; i < parameters.length; i++) {
		if(!parameters[i])
			continue;

		var ary      = parameters[i].split('=');
		hash[ary[0]] = ary[1];
	}

	hash[param] = value;

	var list = [];  
	Object.keys(hash).forEach(function (key) {
		list.push(key + '=' + hash[key]);
	});

	parser.search = '?' + list.join('&');
	return parser.href;
}