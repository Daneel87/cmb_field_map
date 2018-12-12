(function( $ ) {

	'use strict';



	var maps = [];



	$( '.cmb-type-pw-map' ).each( function() {

		initializeMap( $( this ) );

	});



	function initializeMap( mapInstance ) {

		var searchInput = mapInstance.find( '.pw-map-search' );

		var mapCanvas = mapInstance.find( '.pw-map' );

		var lat = mapInstance.find( '.pw-map-lat' );

		var lng = mapInstance.find( '.pw-map-lng' );

		var utcOffset = mapInstance.find( '.pw-map-utc_offset' );

		var formattedAddress = mapInstance.find( '.pw-map-formatted_address' );

		var placeName = mapInstance.find( '.pw-map-place_name' );

		var streetNumber = mapInstance.find( '.pw-map-street_number' );

		var route = mapInstance.find( '.pw-map-route' );

		var locality = mapInstance.find( '.pw-map-locality' );

		var state = mapInstance.find( '.pw-map-administrative_area_level_1' );

		var postalCode = mapInstance.find( '.pw-map-postal_code' );

		var country = mapInstance.find( '.pw-map-country' );

		var activateDrawings = mapInstance.find( '.pw-map-drawing_manager' );

		activateDrawings.val('YES');



		var latLng = new google.maps.LatLng( 54.800685, -4.130859 );

		var zoom = 5;



		// If we have saved values, let's set the position and zoom level

		if ( lat.val().length > 0 && lng.val().length > 0 ) {

			latLng = new google.maps.LatLng( lat.val(), lng.val() );

			zoom = 17;

		}



		// Map

		var mapOptions = {

			center: latLng,

			zoom: zoom

		};

		var map = new google.maps.Map( mapCanvas[0], mapOptions );



		// Marker

		var markerOptions = {

			map: map,

			draggable: true,

			title: 'Drag to set the exact location'

		};

		var marker = new google.maps.Marker( markerOptions );



		if ( lat.val().length > 0 && lng.val().length > 0 ) {

			marker.setPosition( latLng );

		}



		if ( activateDrawings.val() === 'YES' ) {

			// Polyline Options

			var polylineOptions = {

				map: map,

				draggable: true,

				strokeColor: '#ff0000',

				strokeWeight: 5,

				clickable: true,

				editable: true,

				zIndex: 1

			};

			var osPolyline = new google.maps.Polyline(polylineOptions);

			// Circle Options

			var circleOptions = {

				map: map,

				draggable: true,

				fillColor: '#ffff00',

				fillOpacity: 0.5,

				strokeWeight: 3,

				clickable: true,

				editable: true,

				zIndex: 1

			};

			var osCircle = new google.maps.Circle(circleOptions);

			// Rectangle Options

			var rectangleOptions = {

				map: map,

				draggable: true,

				fillColor: '#ff0000',

				fillOpacity: 0.5,

				strokeWeight: 3,

				clickable: true,

				editable: true,

				zIndex: 1

			};

			var osRectangle = new google.maps.Rectangle(rectangleOptions);

			// Polygon Options

			var polygonOptions = {

				map: map,

				draggable: true,

				fillColor: '#BCDCF9',

				fillOpacity: 0.5,

				strokeWeight: 3,

				strokeColor: '#57ACF9',

				clickable: true,

				editable: true,

				zIndex: 1

			};

			var osPolygon = new google.maps.Polygon(polygonOptions);



			// Drawing Manager

			var drawingManager = new google.maps.drawing.DrawingManager({

				drawingMode: null,

				drawingControl: true,

				drawingControlOptions: {

					position: google.maps.ControlPosition.TOP_CENTER,

					drawingModes: [

					//google.maps.drawing.OverlayType.MARKER,

					google.maps.drawing.OverlayType.CIRCLE,

					google.maps.drawing.OverlayType.POLYGON,

					google.maps.drawing.OverlayType.POLYLINE,

					google.maps.drawing.OverlayType.RECTANGLE]

				},

			});

			//console.log(drawingManager)

			drawingManager.setMap(map)



			google.maps.event.addListener(drawingManager, 'polylinecomplete', function (polyline) {

				//alert ("This is a polyline!");

				var polylinePath = polyline.getPath();

				polyline.setMap(null);

				osPolyline.setPath(polylinePath);

				osPolyline.setMap(map);

			});



			google.maps.event.addListener(osPolyline, 'rightclick', function () {

				osPolyline.setMap(null);

			});



			google.maps.event.addListener(drawingManager, 'circlecomplete', function (circle) {

				//alert ("This is a circle!");

				var circleCenter = circle.getCenter();

				var circleRadius = circle.getRadius();

				circle.setMap(null);

				osCircle.setCenter(circleCenter);

				osCircle.setRadius(circleRadius);

				osCircle.setMap(map);

			});



			google.maps.event.addListener(osCircle, 'rightclick', function () {

				osCircle.setMap(null);

			});



			google.maps.event.addListener(drawingManager, 'rectanglecomplete', function (rectangle) {

				//alert ("This is a rectangle!");

				var rectangleBounds = rectangle.getBounds();

				var rectangleNE = rectangle.getBounds().getNorthEast();

				var rectangleSW = rectangle.getBounds().getSouthWest();

				rectangle.setMap(null);

				osRectangle.setBounds(rectangleBounds);

				osRectangle.setMap(map);

			});



			google.maps.event.addListener(osRectangle, 'rightclick', function () {

				osRectangle.setMap(null);

			});



			google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) {

				//alert ("This is a polygon!");

				var polygonPaths = polygon.getPaths();

				polygon.setMap(null);

				osPolygon.setPaths(polygonPaths);

				osPolygon.setMap(map);

			});



			google.maps.event.addListener(osPolygon, 'rightclick', function () {

				osPolygon.setMap(null);

			});

		}



		// Search

		var autocomplete = new google.maps.places.Autocomplete( searchInput[0] );

		autocomplete.bindTo( 'bounds', map );



		google.maps.event.addListener( autocomplete, 'place_changed', function() {

			var place = autocomplete.getPlace();

			if ( ! place.geometry ) {

				return;

			}



			if ( place.geometry.viewport ) {

				map.fitBounds( place.geometry.viewport );

			} else {

				map.setCenter( place.geometry.location );

				map.setZoom( 17 );

			}



			marker.setPosition( place.geometry.location );



			lat.val( place.geometry.location.lat() );

			lng.val( place.geometry.location.lng() );

			formattedAddress.val( place.formatted_address );

			utcOffset.val( place.utc_offset );

			function extractFromAdress(components, type) {

				for (var i = 0; i < components.length; i++)

				for (var j = 0; j < components[i].types.length; j++)

				if (components[i].types[j] == type) return components[i].long_name;

				return "";

			}

			placeName.val( place.name );

			streetNumber.val( extractFromAdress(place.address_components, 'street_number') );

			route.val( extractFromAdress(place.address_components, 'route') );

			locality.val( extractFromAdress(place.address_components, 'locality') );

			state.val( extractFromAdress(place.address_components, 'administrative_area_level_1') );

			postalCode.val( extractFromAdress(place.address_components, 'postal_code') );

			country.val( extractFromAdress(place.address_components, 'country') );

		});



		$( searchInput ).keypress( function( event ) {

			if ( 13 === event.keyCode ) {

				event.preventDefault();

			}

		});



		// Allow marker to be repositioned

		google.maps.event.addListener( marker, 'drag', function() {

			lat.val( marker.getPosition().lat() );

			lng.val( marker.getPosition().lng() );

		});



		maps.push( map );

	}



	// Resize map when meta box is opened

	if ( typeof postboxes !== 'undefined' ) {

		postboxes.pbshow = function () {

			var arrayLength = maps.length;

			for (var i = 0; i < arrayLength; i++) {

				var mapCenter = maps[i].getCenter();

				google.maps.event.trigger(maps[i], 'resize');

				maps[i].setCenter(mapCenter);

			}

		};

	}



	// When a new row is added, reinitialize Google Maps

	$( '.cmb-repeatable-group' ).on( 'cmb2_add_row', function( event, newRow ) {

		var groupWrap = $( newRow ).closest( '.cmb-repeatable-group' );

		groupWrap.find( '.cmb-type-pw-map' ).each( function() {

			initializeMap( $( this ) );

		});

	});



})( jQuery );
