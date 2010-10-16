# CakePHP GoogleMaps Helper

## Instalation

Checkout the code and copy the google_maps.php to your app/views/helpers directory

## Usage

Add the **GoogleMaps** to the $helpers you're using:

    ...
    $helpers = array('Html', 'GoogleMaps');
    ...

In your views, use the helper to generate DIV tag with the map:

    ...
    <?php
    echo $this->GoogleMaps->map(
       44.788414,
       20.469589,
       array(
          'zoom' => 12,
          'draggable' => false,
          'mapTypeId' => 'google.maps.MapTypeId.HYBRID',
          'backgroundColor' => "'#ff0000'", // Make sure the strings are quoted
          'mapTypeControlOptions' => "{mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE], position: google.maps.ControlPosition.BOTTOM_RIGHT, style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}" // Pass complex options as JSON
       )
    );
	?>
    ...

Read the source to learn the usage of other methods (currently: map, addMarkers and addDraggableMarker). I'll add other methods when I need them or when you send them to me (Fork the repo, make your changes and send me the Pull Request).

## Licence

Released under The MIT License

