/**
 * CashWay Points of Payment locator service.
 * Based on Google Maps API for now.
 *
 * @copyright 2015 Epayment Solution - CashWay (http://www.cashway.fr/)
 * @license   Apache License 2.0
 * @author    hupstream <mailbox@hupstream.com>
*/

"use strict";

console.log(window.ENV);

var CW_GEO_SERVICE_URL = (window.ENV == 'development') ?
						 'http://maps.cashway.dev:9876' :
						 'https://maps.cashway.fr',
	CW_ICON_URL = 'http://www.cashway.fr/wp-content/plugins/wp-store-locator/img/markers/map_flag_cw2.png'; // 'https://assets.cashway.fr/i/icons/map_flag.png';

var map,
	geocoder,
	pos;

function initialize() {
	geocoder = new google.maps.Geocoder();

	var mapOptions = {
		zoom: 14,
		center: new google.maps.LatLng(48.8534100, 2.3488000)
	};

	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	map.data.setStyle({
		clickable: true,
		cursor: 'pointer',
		icon: CW_ICON_URL
	});

	var infowindow = new google.maps.InfoWindow();
	map.data.addListener('click', function (event) {
		infowindow.setContent(event.feature.getProperty('name') +
			'<br>' +
			event.feature.getProperty('address') +
			' à ' + event.feature.getProperty('city') +
			' (' + event.feature.getProperty('zipcode') + ')');

		infowindow.setPosition(event.feature.getGeometry().get());
		infowindow.setOptions({pixelOffset: new google.maps.Size(0,-30)});
		infowindow.open(map);
	});

	codeLocation();
}

function codeLocation()
{
	var address = document.getElementById("address").value;
	geocoder.geocode({"address": address, "region": "FR"}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			map.setCenter(results[0].geometry.location);
			var marker = new google.maps.Marker({
				map: map,
				position: results[0].geometry.location
			});
			pos = results[0].geometry.location.lat() + ',' + results[0].geometry.location.lng();
			map.data.loadGeoJson(CW_GEO_SERVICE_URL + '/pops.json?p=' + pos);

		} else {
			console.log("Failed to geocode for the following reason: " + status);
			console.log("Trying browser geolocation API...");
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (position) {
					initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					map.setCenter(initialLocation);
					pos = position.coords.latitude + ',' + position.coords.longitude;
					map.data.loadGeoJson(CW_GEO_SERVICE_URL + '/pops.json?p=' + pos);
				});
			}
		}
	});
}

function loadScript() {
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyDP_mKp8OkkJpDNl4pulTiln7HyeOzqyoM' +
		'&signed_in=true&callback=initialize';
	document.body.appendChild(script);
}
window.onload = loadScript;
