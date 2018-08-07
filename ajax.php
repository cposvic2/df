<?php 

function deedfax_hook_javascript() {
	echo '
	<script type="text/javascript">
		var ajaxurl = "'. admin_url('admin-ajax.php') .'";
	</script>';
}
add_action('wp_head','deedfax_hook_javascript');

function deedfax_get_parish_data () {
	if ( !check_ajax_referer('get_parish_data', 'security', false) ) { // Nonce check
		$response['status'] = 'REQUEST_DENIED';
		$response['reason'] = 'Did not pass security check.';
		echo json_encode($response);
		wp_die();
	}

	$parish_slug = $_POST['parish'];

	$valid_parish = false;
	$user_parishes = get_user_parishes();
	foreach ($user_parishes as $user_parish) {
		if ($user_parish->getSlug() === $parish_slug) {
			$valid_parish = true;
			break;
		}
	}

	if ( !$valid_parish ) {
		$response['status'] = 'REQUEST_DENIED';
		$response['reason'] = 'The parish you sent was invalid. Please contact an administrator.';
		echo json_encode($response);
		wp_die();
	}

	$response['status'] = 'OK';
	$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($parish_slug);
	$streets = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->queryByParish($parish->getId(), 'name');
	$subdivisions = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->queryByParish($parish->getId(), 'name');
	$districts = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->queryByParish($parish->getId(), 'name');

	$streets_array = array();
	foreach ($streets as $street) {
		$streets_array[] = array('value' => $street->getSlug(), 'label' => $street->getName());
	}
	$response['streets'] = get_select_options( $streets_array, false, false );

	$subdivisions_array = array();
	foreach ($subdivisions as $subdivision) {
		$subdivisions_array[] = array('value' => $subdivision->getSlug(), 'label' => $subdivision->getName());
	}
	$response['subdivisions'] = get_select_options( $subdivisions_array, false, false );

	$districts_array = array();
	foreach ($districts as $district) {
		$districts_array[] = array('value' => $district->getSlug(), 'label' => $district->getName());
	}
	$response['districts'] = get_select_options( $districts_array, false, false );

	echo json_encode($response);
	wp_die();
}
add_action( 'wp_ajax_get_parish_data', 'deedfax_get_parish_data' );
add_action( 'wp_ajax_nopriv_get_parish_data', 'deedfax_get_parish_data' );

function deedfax_search_data () {
	if ( !wp_verify_nonce($_POST['search_data_nonce'], 'search_data') ) { // Nonce check
		$response['status'] = 'REQUEST_DENIED';
		$response['reason'] = 'Did not pass security check';
		echo json_encode($response);
		wp_die();
	}

	$parish_slug = $_POST['parish'];

	$valid_parish = false;
	$user_parishes = get_user_parishes();
	foreach ($user_parishes as $user_parish) {
		if ($user_parish->getSlug() === $parish_slug) {
			$valid_parish = true;
			break;
		}
	}

	if ( !$valid_parish ) {
		$response['status'] = 'REQUEST_DENIED';
		$response['reason'] = 'The parish you sent was invalid. Please contact an administrator.';
		echo json_encode($response);
		wp_die();
	}

	$response['status'] = 'OK';
	$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($parish_slug);

	$street_ids = $subdivision_ids = $district_ids = array();

	if (is_array($_POST['subdivision']) && count($_POST['subdivision'])) {
		$subdivision_ids = array();
		foreach ($_POST['subdivision'] as $subdivison_slug) {
			if (!!$subdivison_slug) {
				$subdivision = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->loadBySlug($subdivison_slug);
				$subdivision_ids[] = $subdivision->getId();
			}

		}
	}

	if (is_array($_POST['street']) && count($_POST['street'])) {
		foreach ($_POST['street'] as $street_slug) {
			if (!!$street_slug) {
				$street = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->loadBySlug($street_slug);
				$street_ids[] = $street->getId();
			}
		}
	}

	if (is_array($_POST['district']) && count($_POST['district'])) {
		foreach ($_POST['district'] as $district_slug) {
			if (!!$district_slug) {
				$district = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->loadBySlug($district_slug);
				$district_ids[] = $district->getId();
			}
		}
	}

	$min_price = preg_replace("/([^0-9\\.])/i", "", $_POST['min_price']);
	$max_price = preg_replace("/([^0-9\\.])/i", "", $_POST['max_price']);

	if ($_POST['start_date']) $start_date = $_POST['start_date'].' 00:00:00';
	if ($_POST['end_date']) $end_date = $_POST['end_date'].' 00:00:00';

	$response['post'] = $_POST;
	$response['query'] = array(
		'parish_slug' => $parish_slug,
		'subdivisions' => $subdivision_ids,
		'streets' => $street_ids,
		'districts' => $district_ids,
		'min_price' => $min_price,
		'max_price' => $max_price,
		'start_date' => $start_date,
		'end_date' => $end_date,
	);

	$properties = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->searchQuery($parish->getId(), $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date, $page);
	$total_properties = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->searchQueryCount($parish->getId(), $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date);
	$response['markers'] = array();
	$response['html'] = '<p>Found '.count($properties).' result'.(count($properties)===1 ? '' : 's').'...</p>';

	if (count($properties)) {
		$response['html'] .='
		<table style="width:100%">
			<tr>
				<th>Front Street</th>
				<th>House #</th>
				<th>Subdivision</th>
				<th>Square</th>
				<th>Lot</th>
				<th>Size</th>
				<th>Code</th>
				<th>Price</th>
				<th>Purchaser</th>
				<th>Entry #</th>
				<th>Seller</th>
				<th>Date</th>
			</tr>';

		foreach ($properties as $property) {
			$response['html'] .= '
			<tr class="property-row" id="id-'.$property->getId().'">
				<td>'.$property->getStreet()->getName().'</td>
				<td>'.$property->getHouse().'</td>
				<td>'.$property->getSubdivision()->getName().'</td>
				<td>'.$property->getSquare().'</td>
				<td>'.$property->getLot().'</td>
				<td>'.$property->getSize().'</td>
				<td>'.$property->getCode().'</td>
				<td>'.$property->getPrettyPrice().'</td>
				<td>'.$property->getPurchaser().'</td>
				<td>'.$property->getEntry().'</td>
				<td>'.$property->getSeller().'</td>
				<td>'.$property->getSellDate('n/j/Y').'</td>
			</tr>';
			if (!!$property->getRemarks()) {
				$response['html'] .= '
			<tr>
                <td colspan="12">Notes: '.$property->getRemarks().'</td>
    		</tr>';
			}
			if (!!$property->getLatitude() && $property->getLongitude()) {
				$response['markers'][] = array(
					"id" => $property->getId(),
					"address" => $property->getStreet()->getName(),
					"price" => $property->getPrettyPrice(),
					"coordinates" => array(
						"lat" => $property->getLatitude(),
						"lng" => $property->getLongitude()
					),
				);
			}

		}
		$response['html'] .= '</table>';
	}

	$response['post'] = $_POST;

	echo json_encode($response);
	wp_die();
}
add_action( 'wp_ajax_search_data', 'deedfax_search_data' );
add_action( 'wp_ajax_nopriv_search_data', 'deedfax_search_data' );