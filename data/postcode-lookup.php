<?php



$postcode    = $_REQUEST['postcode'];
$search_code = urlencode( $postcode );


if(isset($_REQUEST['latlng'])){
    $url  = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $search_code . '&sensor=false';
} else {
    $url  = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $search_code . '&sensor=false';
}
$json = json_decode( file_get_contents( $url ) );

if ( isset( $_REQUEST['checkup'] ) ) {

	$result_count = count( $json->results );

	if ( $result_count > 1 ) {
		$i = 0;
		foreach ( $json->results as $location ) {
			$locations[] = $location->address_components[0]->long_name.', '.$location->address_components[1]->long_name.', '. $location->address_components[3]->long_name;
			$i++;
		}

		echo json_encode( array('locations' => $locations ));

	}


} else {
	$lat = $json->results[0]->geometry->location->lat;
	$lng = $json->results[0]->geometry->location->lng;

// Now build the lookup:
	$address_url  = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false';
	$address_json = json_decode( file_get_contents( $address_url ) );
	$address_data = $address_json->results[0]->address_components;

	$street = str_replace( 'Dr', 'Drive', $address_data[1]->long_name );
	$local  = $address_data[2]->long_name;
	$town   = $address_data[3]->long_name;
	$county = $address_data[4]->long_name;

	$town = str_replace( '"', "", $town );


	$array = array( 'local' => $local, 'town' => $town, 'county' => $county );


	echo json_encode( $array );
}
