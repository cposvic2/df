<?php

function add_search_results( $content ) {
	if( is_page('search-results') ) {
		$rowCount = 5;
		$rowCountOptions = array(5,10,20,50,100);
		$properties = $response = $subdivision_ids = $subdivision_slugs = $street_ids = $street_slugs = $district_ids = $district_slugs = array();
		$parish = $start_date = $end_date = $min_price = $max_price = null;
		$total_properties = 0;
		$page = 1;

		if (isset($_REQUEST['count'])) {
			$rowCount = (int)$_REQUEST['count'];
		} else {
			$rowCount = 10;
		}

		if (isset($_REQUEST['parish'])) {
			$parish_slug = sanitize_title($_REQUEST['parish']);
			$user_parishes = get_user_parishes();
			foreach ($user_parishes as $user_parish) {
				if ($user_parish->getSlug() === $parish_slug) {
					$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($parish_slug);
					break;
				}
			}
		}
		
		if (isset($_REQUEST['subdivision'])) {
			if (is_array($_REQUEST['subdivision']) && count($_REQUEST['subdivision'])) {
				$requested_subdivisions = $_REQUEST['subdivision'];
			} else {
				$requested_subdivisions = explode("+", $_REQUEST['subdivision']);
			}
			foreach ($requested_subdivisions as $subdivison_slug) {
				if (!!$subdivison_slug) {
					$subdivision = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->loadBySlug($subdivison_slug);
					if (!!$subdivision) {
						$subdivision_ids[] = $subdivision->getId();
						$subdivision_slugs[] = $subdivision->getSlug();
					}
				}

			}
		}

		if (isset($_REQUEST['street']) && is_array($_REQUEST['street']) && count($_REQUEST['street'])) {
			foreach ($_REQUEST['street'] as $street_slug) {
				if (!!$street_slug) {
					$street = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->loadBySlug($street_slug);
					if (!!$street) {
						$street_ids[] = $street->getId();
						$street_slugs[] = $street->getSlug();
					}
				}
			}
		}

		if (isset($_REQUEST['district']) && is_array($_REQUEST['district']) && count($_REQUEST['district'])) {
			foreach ($_REQUEST['district'] as $district_slug) {
				if (!!$district_slug) {
					$district = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->loadBySlug($district_slug);
					if (!!$district) {
						$district_ids[] = $district->getId();
						$district_slugs[] = $district->getSlug();
					}
				}
			}
		}

		if (isset($_REQUEST['start_date']) && !!$_REQUEST['end_date']) {
			$start_date = $_REQUEST['start_date'].' 00:00:00';
		}

		if (isset($_REQUEST['end_date']) && !!$_REQUEST['end_date']) {
			$end_date = $_REQUEST['end_date'].' 00:00:00';
		}

		if (isset($_REQUEST['min_price'])) {
			$min_price = preg_replace("/([^0-9\\.])/i", "", $_REQUEST['min_price']);
		}

		if (isset($_REQUEST['max_price'])) {
			$max_price = preg_replace("/([^0-9\\.])/i", "", $_REQUEST['max_price']);
		}

		if (isset($_REQUEST['pg']) && (int)$_REQUEST['pg']) {
			$page = (int)$_REQUEST['pg'];
		}

		$rowStart = ($page * $rowCount) - $rowCount;

		if (!!$parish) {
			$properties = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->searchQuery($parish->getId(), $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date, $rowStart, $rowCount);
			$total_properties = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->searchQueryCount($parish->getId(), $subdivision_ids, $street_ids, $district_ids, $min_price, $max_price, $start_date, $end_date);
		}

		$firstRow = $rowStart + 1;
		if ($total_properties < $firstRow + $rowCount) {
			$lastRow = $total_properties;
		} else {
			$lastRow = $firstRow + $rowCount;
		}


		$response['markers'] = array();
		$response['html'] ='
		<p><a href="'.get_site_url(null, '/search').'" class="default-btn-shortcode dt-btn dt-btn-m btn-inline-left"><span>Start New Search</span></a></p>
		<div id="search-results">
		<p>Found '.$total_properties.' result'.($total_properties===1 ? '' : 's').'. Showing results '.$firstRow.' to '.$lastRow.'...</p>';

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
					<th>Map</th>
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
					<td>'.( !!$property->getLatitude() && !!$property->getLongitude() ? '<a href="https://www.google.com/maps/?q='.$property->getLatitude().','.$property->getLongitude().'" target="_blank">Link</a>' : '').'</td>
				</tr>';
				if (!!$property->getRemarks()) {
					$response['html'] .= '
				<tr>
	                <td colspan="13">Notes: '.$property->getRemarks().'</td>
	    		</tr>';
				}
				if ( !!$property->getLatitude() && !!$property->getLongitude() ) {
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

		

		if ($total_properties > $rowCount + $page || ($total_properties > 0 && $page > 1)) {
			$query_args = array(
				'parish' => $parish_slug,
				'min_price' => $min_price,
				'max_price' => $max_price,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'subdivision[]' => implode('+',$subdivision_slugs),
				'street[]' => implode('+',$street_slugs),
				'district[]' => implode('+',$district_slugs),
				'count' => $rowCount,
			);
			$starting_link = add_query_arg($query_args, get_permalink());

			$response['html'] .= '<p>';
			if ($total_properties > 0 && $page > 1) {
				$response['html'] .= '<a href="'.add_query_arg('pg', $page-1, $starting_link).'">Back</a>&nbsp;';
			}

			if ($total_properties > $rowCount + $page) {
				$response['html'] .= '<a href="'.add_query_arg('pg', $page+1, $starting_link).'">Next</a>&nbsp;';
			}
			$response['html'] .= '&nbsp;Number of results:&nbsp;<select id="count" name="count" style="width:80px;">';
			foreach ($rowCountOptions as $rowCountOption) {
				$response['html'] .= '<option value="'.$rowCountOption.'" '.selected($rowCountOption, $rowCount, false).'>'.$rowCountOption.'</option>';
			}
				
			$response['html'] .= '</select>
			</p>';
		}

		$response['html'] .= '
		<p><a href="'.get_site_url(null, '/search').'" class="default-btn-shortcode dt-btn dt-btn-m btn-inline-left"><span>Start New Search</span></a></p>
		</div>';

		/*
		<script>
			var search_results_markers = '.json_encode($response['markers']).';
		</script>
		*/

		$content .= $response['html'];

	}
	return $content;
}
add_filter('the_content', 'add_search_results');