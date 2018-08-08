<?php

function parse_deedfax_csv( $csv_location, $parish_id, $column_order, $has_header = true ) {
	$row = $properties_added = 0;
	if (($handle = fopen($csv_location, "r")) !== FALSE) {
		$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->load($parish_id);
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if (!$row && $has_header) { // first row
			} else {
				$property = new DeedfaxProperty;

				$column_inputs = array('subdivision', 'square', 'lots', 'size', 'house', 'number', 'street', 'cd', 'price', 'purchaser', 'reference', 'seller', 'other', 'other', 'other', 'date', 'pubyear', 'pubmonth', 'remarks');

				$property->setParishId($parish_id);


				if ( df_import_col_exists('square', $column_order, $data) ) {
					$property->setSquare( import_encode($data[$column_order['square']]) );
				}
				if ( df_import_col_exists('lots', $column_order, $data) ) {
					$property->setLot( import_encode($data[$column_order['lots']]) );
				}
				if ( df_import_col_exists('size', $column_order, $data) ) {
					$property->setSize( import_encode($data[$column_order['size']]) );
				}
				if ( df_import_col_exists('house', $column_order, $data) ) {
					$property->setHouse( import_encode($data[$column_order['house']]) );
				} elseif ( df_import_col_exists('number', $column_order, $data) ) {
					$property->setHouse( import_encode($data[$column_order['number']]) );
				}
				if ( df_import_col_exists('code', $column_order, $data) ) {
					$property->setCode( import_encode($data[$column_order['code']]) );
				}
				if ( df_import_col_exists('purchaser', $column_order, $data) ) {
					$property->setPurchaser( import_encode($data[$column_order['purchaser']]) );
				}
				if ( df_import_col_exists('entry', $column_order, $data) ) {
					$property->setEntry( import_encode($data[$column_order['entry']]) );
				}
				if ( df_import_col_exists('seller', $column_order, $data) ) {
					$property->setSeller( import_encode($data[$column_order['seller']]) );
				}
				if ( df_import_col_exists('remarks', $column_order, $data) ) {
					$property->setRemarks( import_encode($data[$column_order['remarks']]) );
				}
				if ( df_import_col_exists('price', $column_order, $data) ) {
					$property->setPrettyPrice( import_encode($data[$column_order['price']]) );
				}
				if ( df_import_col_exists('sell_date', $column_order, $data) ) {
					$property->setSellDate( import_encode($data[$column_order['sell_date']]), 'm/d/Y' );
				}
				if ( df_import_col_exists('pubmonth', $column_order, $data) && df_import_col_exists('pubyear', $column_order, $data) ) {
					$property->setPublicationDate( import_encode($data[$column_order['pubmonth']]).'-01-'.import_encode($data[$column_order['pubyear']]), 'm-d-Y' );
				}
				if ( df_import_col_exists('subdivision', $column_order, $data) ) {
					$subdivision_id = find_deedfax_subdivision( import_encode($data[$column_order['subdivision']]), $parish_id );
					$property->setSubdivisionId( $subdivision_id );
				} else {
					$property->setSubdivisionId(1);
				}
				if ( df_import_col_exists('district', $column_order, $data) ) {
					$district_id = find_deedfax_district( import_encode($data[$column_order['district']]), $parish_id );
					$property->setDistrictId( $district_id );
				} else {
					$property->setDistrictId(1);
				}
				if ( df_import_col_exists('street', $column_order, $data) ) {
					$street = array(
						'number' => import_encode($data[$column_order['number']]),
						'name' => import_encode($data[$column_order['street']]),
					);
					$google_results = google_places_street_search( $street, $parish );
					$street_id = find_deedfax_street( import_encode($data[$column_order['street']]), $parish_id );
					$property->setStreetId( $street_id );
					$property->setLatitude( $google_results['latitude'] );
					$property->setLongitude( $google_results['longitude'] );
				} else {
					$property->setStreetId(1);
				}
				if ( df_import_col_exists('township', $column_order, $data) ) {
					$property->setTownship( import_encode($data[$column_order['township']]) );
				} else {
					$property->setTownship( 0 );
				}

				$property->setDateAddedNow();

				DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->insert($property);
				$properties_added++;
			}
			$row++;
		}
		fclose($handle);
		return $properties_added;
	}
	return false;
}

function old_data_import() {
	$row_num = $properties_added = 0;
	$csv_location = DEEDFAX_PLUGIN_PATH . 'old-data.csv';
	$csv_location2 = DEEDFAX_PLUGIN_PATH . 'old-data2.csv';
	if (($handle = fopen($csv_location, "r")) !== FALSE) {
		$handle2 = fopen($csv_location2, "w");
		while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if (!$row_num) { // first row
			} else if (!strlen($row[0])) {
				$parish_id = $row[1];

				$property = new DeedfaxProperty;

				$property->setParishId($parish_id);

				if ( !!strlen($row[2]) ) {
					$property->setOldId( (int)import_encode($row[2]) );
				}
				if ( !!strlen($row[6]) || !!strlen($row[7]) ) {
					$property->setSquare( import_encode($row[6]).import_encode($row[7]) );
				}
				if ( !!strlen($row[8]) || !!strlen($row[9]) ) {
					$property->setLot( import_encode($row[8]).import_encode($row[9]) );
				}
				if ( !!strlen($row[10]) ) {
					$property->setSize( import_encode($row[10]) );
				}
				if ( !!strlen($row[11]) || !!strlen($row[12]) ) {
					$property->setHouse( import_encode($row[11]).import_encode($row[12]) );
				}
				if ( !!strlen($row[15]) ) {
					$property->setCode( import_encode($row[15]) );
				}
				if ( !!strlen($row[18]) ) {
					$property->setPurchaser( import_encode($row[18]) );
				}
				if ( !!strlen($row[19]) ) {
					$property->setEntry( import_encode($row[19]) );
				}
				if ( !!strlen($row[20]) ) {
					$property->setSeller( import_encode($row[20]) );
				}
				if ( !!strlen($row[23]) ) {
					$property->setSellDate( import_encode($row[23]), 'm/d/Y' );
				}
				if ( !!strlen($row[24]) ) {
					$property->setRemarks( import_encode($row[24]) );
				}
				if ( !!strlen($row[16]) ) {
					$property->setPrettyPrice( import_encode($row[16]) );
				}
				if ( !!strlen($row[17]) ) {
					$property->setPriceText( import_encode($row[17]) );
				}
				if ( !!strlen($row[25]) && !!strlen($row[26]) ) {
					$property->setPublicationDate( import_encode($row[26]).'-01-'.import_encode($row[25]) , 'm-d-Y' );
				}
				if ( !!strlen($row[5]) ) {
					$subdivision_id = find_deedfax_subdivision( import_encode($row[5]), $parish_id );
					$property->setSubdivisionId( $subdivision_id );
				} else {
					$property->setSubdivisionId(1);
				}
				if ( !!strlen($row[28]) ) {
					$district_id = find_deedfax_district( import_encode($row[28]), $parish_id );
					$property->setDistrictId( $district_id );
				} else {
					$property->setDistrictId(1);
				}
				if ( !!strlen($row[14]) ) {
					$street_id = find_deedfax_street( import_encode($row[14]), $parish_id );
					$property->setStreetId( $street_id );
				} else {
					$property->setStreetId(1);
				}
				if ( !!strlen($row[3]) && $row[3] == 'Y' ) {
					$property->setTownship( 1 );
				}
				$id = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->insert($property);
				if (!!$id) {
					$row[0] = 'Y';
					$properties_added++;
				}
			}
			fputcsv($handle2, $row);
			$row_num++;
		}
		fclose($handle);
		fclose($handle2);
		unlink($csv_location);
		rename ($csv_location2, $csv_location);
	}
}

function import_encode($value) {
	$value = utf8_encode($value);
	return $value;
}

function find_deedfax_street( $name, $parish_id ) {
	if (!$name)
		return 1;

	$slug = sanitize_title($name);

	$street = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->loadBySlugAndParish($slug, $parish_id);
	if (!!$street)
		return $street->getId();

	$street = new DeedfaxStreet;
	$street->setName($name);
	$street->setSlug($slug);
	$street->setParishId($parish_id);
	$street_id = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->insert($street);
	return $street_id;
}

function find_deedfax_subdivision( $name, $parish_id ) {
	if (!$name)
		return 1;

	$slug = sanitize_title($name);

	$subdivision = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->loadBySlugAndParish($slug, $parish_id);
	if (!!$subdivision)
		return $subdivision->getId();

	$subdivision = new DeedfaxSubdivision;
	$subdivision->setName($name);
	$subdivision->setSlug($slug);
	$subdivision->setParishId($parish_id);
	$subdivision_id = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->insert($subdivision);
	return $subdivision_id;
}

function find_deedfax_district( $name, $parish_id ) {
	if (!$name)
		return 0;

	$slug = sanitize_title($name);

	$district = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->loadBySlugAndParish($slug, $parish_id);
	if (!!$district)
		return $district->getId();

	$district = new DeedfaxDistrict;
	$district->setName($name);
	$district->setSlug($slug);
	$district->setParishId($parish_id);
	$subdivision_id = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->insert($district);
	return $subdivision_id;
}

function google_places_street_search( $street, $parish ) {
	global $google_places_api_key;
	$latitude = $longitude = null;

	$attrs = array(
		'key' => $google_places_api_key,
		'address' => $street['number'].'+'.$street['name'].'+'.$parish->getName().'+Parish+LA',
	);

	$json = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query($attrs)));

	if (isset($json->results[0]->geometry->location)) {
		$latitude = $json->results[0]->geometry->location->lat;
		$longitude = $json->results[0]->geometry->location->lng;
	}

	if (isset($json->results[0]->address_components)) {
		foreach( $json->results[0]->address_components as $address_component ) {
			if (in_array('route', $address_component->types)) {
				$street_name = $address_component->short_name;
			}
		}
	}

	return array(
		'street_name' => $street_name,
		'latitude' => $latitude,
		'longitude' => $longitude,
	);
}

function df_import_col_exists ($key, $column_order, $data) {
	return (array_key_exists($key, $column_order) && array_key_exists($column_order[$key], $data) && !!strlen($data[$column_order[$key]]));
}