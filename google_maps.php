<?php
/**
 * Generation of Google Maps elements
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2010 Marko Markovic <marko@ultimate.in.rs>
 */
class GoogleMapsHelper extends AppHelper {

	/**
	 * Url for Maps JS
	 */
	var $url = 'http://maps.google.com/maps/api/js?sensor=false';

	/**
	 * Url used for Static maps
	 */
	var $staticUrl = 'http://maps.google.com/maps/api/staticmap?sensor=false&';

	// Other helpers used by GoogleMapsHelper
	var $helpers = array('Html');


	/**
	 * Creates a Map Canvas DIV tag
	 *
	 * ### Usage
	 *
	 * Create a simple map centered on Belgrade, Serbia:
	 *
	 * `echo $this->GoogleMaps->map(44.788414, 20.469589, array('zoom' => 12));`
	 *
	 * Create a map with more options:
	 *
	 * `echo $this->GoogleMaps->map(
	 *    44.788414,
	 *    20.469589,
	 *    array(
	 *       'zoom' => 12,
	 *       'draggable' => false,
	 *       'mapTypeId' => 'google.maps.MapTypeId.HYBRID',
	 *       'backgroundColor' => "'#ff0000'", // Make sure the strings are quoted
	 *       'mapTypeControlOptions' => "{mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE], position: google.maps.ControlPosition.BOTTOM_RIGHT, style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}" // Pass complex options as JSON
	 *    )
	 * );`
	 *
	 * @param $lat float Latitude of the map center
	 * @param $lng float Longitude of the map center
	 * @param optional $options array Options for the google.maps.Map constructor. Make sure the strings are quoted; Pass complex options as JSON;
	 * @param optional $htmlAttributes array Additional HTML attributes of the DIV tag
	 * @returns a Map Canvas DIV tag with the loader scripts
	 * @access public
	 */
	function map($lat, $lng, $options = array(), $htmlAttributes = array()) {
		$this->__addApiJs();

		// Validating the coordinates
		if (!$this->__isValidLat($lat)) {
			$lat = 44.788414;
		}
		if (!$this->__isValidLng($lng)) {
			$lng = 20.469589;
		}

		// Default options
		$options = array_merge(array(
			'zoom' => 5,
			'mapTypeId' => 'google.maps.MapTypeId.ROADMAP'
		), $options);

		// Default htmlAttributes
		$htmlAttributes = array_merge(array(
			'style' => 'height: 400px',
			'class' => 'GoogleMap'
		), $htmlAttributes);

		$options = $this->__transformBooleans($options);

		if (!isset($htmlAttributes['id'])) {
			$htmlAttributes['id'] = 'GoogleMapCanvas';
		}
		$jsId = isset($options['id']) ? $options['id'] : $htmlAttributes['id'];
		unset($options['id']);

		$script = "
{$jsId}Opt = {};
{$jsId}Opt.center = new google.maps.LatLng({$lat}, {$lng});
";
		foreach ($options as $k => $v) {
			$script .= "{$jsId}Opt.{$k} = $v;\n";
		}

$script .= "var {$jsId} = new google.maps.Map(document.getElementById('{$htmlAttributes['id']}'), {$jsId}Opt);
";

		return $this->Html->tag('div', $this->Html->scriptBlock($script), $htmlAttributes);
	}


	/**
	 * Adds the markers to the map
	 *
	 * ### Usage
	 *
	 * `echo $this->GoogleMaps->addMarkers(
	 *     array(
	 *        array(
	 *           'lat' => 44.788414,
	 *           'lng' => 20.469589,
	 *           'title' => "'First Marker Title'",
	 *           'infoWindow' => array(
	 *              'content' => "'Content of the First Marker InfoWindow'" // Make sure the strings are quoted
	 *           )
	 *        ),
	 *        array(
	 *           'lat' => 12.3456,
	 *           'lng' => 78.90123,
	 *           'title' => "'Second Marker'"
	 *        ),
	 *        array(
	 *           'lat' => 98.7654,
	 *           'lng' => 32.10987,
	 *           'title' => "Third Marker"
	 *        )
	 *    ),
	 *    array(
	 *       'map' => 'GoogleMapCanvas'
	 *    )
	 * );`
	 *
	 * @param $markers array The array of all the markers to be created. Each marker should have the following structure:
	 * array(
	 *    'lat' => float Latitude
	 *    'lon' => float Longitude
	 *    'title' => optional string Title
	 *    'infoWindow' => optional array(
	 *       options for the google.maps.InfoWindow constructor
	 *    )
	 * )
	 * @requires the Map to already be created
	 * @returns the script to create the markers in the map canvas
	 */
	function addMarkers($markers, $options = array()) {

		// Default options
		$options = array_merge(array(
			'map' => 'GoogleMapCanvas'
		), $options);

		$options = $this->__transformBooleans($options);

		$script = '';
		foreach ($markers as $k => $marker) {
			if ($this->__isValidLat($marker['lat']) && $this->__isValidLng($marker['lng'])) {
				$script .= "
marker{$k}Opt = {};
marker{$k}Opt.position = new google.maps.LatLng({$marker['lat']}, {$marker['lng']});
";
				// Options for all markers
				foreach ($options as $kk => $v) {
					$script .= "marker{$k}Opt.{$kk} = $v;\n";
				}


				// Options for each marker
				if (isset($marker['infoWindow'])) {
					$infoWindow = $marker['infoWindow'];
					$lat = $marker['lat'];
					$lng = $marker['lng'];
				} else {
					unset($infoWindow);
				}
				unset($marker['lat'], $marker['lng'], $marker['infoWindow']);
				$marker = $this->__transformBooleans($marker);
				foreach ($marker as $kk => $v) {
					$script .= "marker{$k}Opt.{$kk} = $v;\n";
				}
				$script .= "var marker{$k} = new google.maps.Marker(marker{$k}Opt);\n";

				// InfoWindow
				if (isset($infoWindow)) {
					$script .= "marker{$k}IWOpt = {};
marker{$k}IWOpt.position = new google.maps.LatLng({$lat}, {$lng});
";
					$infoWindow = $this->__transformBooleans($infoWindow);
					foreach ($infoWindow as $kk => $v) {
						$script .= "marker{$k}IWOpt.{$kk} = $v;\n";
					}
					$script .= "marker{$k}IW = new google.maps.InfoWindow(marker{$k}IWOpt);
google.maps.event.addListener(marker{$k}, 'click', function() {
	marker{$k}IW.open({$options['map']}, marker{$k});
});
";
				}
			}
		}
		return $this->Html->scriptBlock($script);
	}


	/**
	 * Creates a draggable Marker that updates the Latitude and Longitude fields
	 *
	 * @param string $latField a field to update with the markers Latitude
	 * @param string $lngField a field to update with the markers Longitude
	 * @param optional $mapCanvasId string ID of the Map Canvas
	 * @param optional $markerOptions array google.maps.MarkerOptions
	 * @returns the script to create the draggable marker in the map canvas
	 */
	function addDraggableMarker($latField, $lngField, $mapCanvasId = 'GoogleMapCanvas', $markerOptions = array()) {

		$latField = $this->Html->domId($latField);
		$lngField = $this->Html->domId($lngField);

		$markerOptions = $this->__transformBooleans($markerOptions);

		$script = "
lat = document.getElementById('{$latField}').value;
lng = document.getElementById('{$lngField}').value;
markerOpt = {};
markerOpt.position = new google.maps.LatLng(lat, lng);
markerOpt.map = {$mapCanvasId};
markerOpt.draggable = true;
markerOpt.cursor = 'move';
";
		foreach ($markerOptions as $k => $v) {
			$script .= "markerOpt.{$k} = $v;\n";
		}

		$script .= "
if (isNaN(markerOpt.position.lat()) || isNaN(markerOpt.position.lng()) || lat == '' || lng == '') {
	markerOpt.position = new google.maps.LatLng(44.788414, 20.469589);
	{$mapCanvasId}.setZoom(5);
}
";

		$script .= "var marker = new google.maps.Marker(markerOpt);
var {$mapCanvasId}Drag = function (e) {
	lat = document.getElementById('{$latField}');
	lng = document.getElementById('{$lngField}');
	if (lat) {
		lat.value = e.latLng.lat();
	}
	if (lng) {
		lng.value = e.latLng.lng();
	}
}
google.maps.event.addListener(marker, 'drag', {$mapCanvasId}Drag, true);
{$mapCanvasId}.panTo(markerOpt.position);
";
		return $this->Html->scriptBlock($script);
	}


	function staticUrl($lat, $lng, $zoom = 5, $size = '256x256', $maptype = 'roadmap', $markers = array()) {
		// center=Brooklyn+Bridge,New+York,NY&zoom=14&size=512x512&maptype=roadmap&markers=color:blue|label:S|40.702147,-74.015794&markers=color:green|label:G|40.711614,-74.012318&markers=color:red|color:red|label:C|40.718217,-73.998284&sensor=false

		// Validating the coordinates
		if (!$this->__isValidLat($lat)) {
			$lat = 44.788414;
		}
		if (!$this->__isValidLng($lng)) {
			$lng = 20.469589;
		}

		$opts = '';
		$opts .= sprintf('center=%s,%s', $lat, $lng);
		$opts .= sprintf('&zoom=%d', $zoom);
		$opts .= sprintf('&size=%s', $size);
		$opts .= sprintf('&maptype=%s', $maptype);
		foreach ($markers as $marker) {
			$opts .= sprintf('&markers=%s', implode('|', $marker));
		}

		return $this->staticUrl . $opts;
	}

	/**
	 * Adds the required scripts to the layout
	 *
	 * @access private
	 */
	function __addApiJs() {
		$out = $this->Html->script($this->url, array('once' => true));
		$view =& ClassRegistry::getObject('view');
		$view->addScript($out);
	}

	/**
	 * Transforms the Boolean values in order to use them in JS
	 *
	 * @param $data array Data to transform
	 * @returns array Transformed data
	 * @access private
	 */
	function __transformBooleans($data) {
		foreach ($data as $k => $v) {
			if ($v === false) $data[$k] = 'false';
			if ($v === true) $data[$k] = 'true';
		}
		return $data;
	}

	/**
	 * Validates the Latitude
	 *
	 * @returns boolean
	 * @access private
	 */
	function __isValidLat($lat) {
		return preg_match('/\A[+-]?(?:90(?:\.0{1,6})?|\d(?(?<=9)|\d?)\.\d{1,12})\z/x', $lat);
	}

	/**
	 * Validates the Longitude
	 *
	 * @returns boolean
	 * @access private
	 */
	function __isValidLng($lng) {
		return preg_match('/\A[+-]?(?:180(?:\.0{1,6})?|(?:1[0-7]\d|\d{1,2})\.\d{1,12})\z/x', $lng);
	}

}

