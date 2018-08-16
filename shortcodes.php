<?

/**
 * Creates Deedfax Search Tool
 */
function deedfax_search_tool($atts = []) {
	// Parishes
	$parish_select = '<select class="shipping_method" name="parish" id="parish">';
	$user_parishes = get_user_parishes();
	if (count($user_parishes)) {
		$default = $user_parishes[0]->getId();
	} else {
		$default = 0;
	}
	foreach ($user_parishes as $parish) {
		$parish_select .= '<option value="'.$parish->getSlug().'" '.selected($parish->getId(), $default, false).'>'.$parish->getName().'</option>';
	}
	$parish_select .= '</select>';

	// Subidvision
	$subdivisions = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->queryByParish($default, 'name');
	$subdivisions_array = array();
	foreach ($subdivisions as $subdivision) {
		$subdivisions_array[] = array('value' => $subdivision->getSlug(), 'label' => $subdivision->getName());
	}
	$subdivision_select = '<select class="shipping_method select2" name="subdivision[]" id="subdivision" multiple="multiple">'.get_select_options( $subdivisions_array, false, false ).'</select>';

	// Street
	$streets = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->queryByParish($default, 'name');
	$streets_array = array();
	foreach ($streets as $street) {
		$streets_array[] = array('value' => $street->getSlug(), 'label' => $street->getName());
	}
	$street_select = '<select class="shipping_method select2" name="street[]" id="street" multiple="multiple">'.get_select_options( $streets_array, false, false ).'</select>';

	// District
	$districts = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->queryByParish($default, 'name');
	$districts_array = array();
	foreach ($districts as $district) {
		$districts_array[] = array('value' => $district->getSlug(), 'label' => $district->getName());
	}
	$district_select = '<select class="shipping_method select2" name="district[]" id="district" multiple="multiple">'.get_select_options( $districts_array, false, false ).'</select>';

	// Price
	$min_price = '<input class="input-text" type="text" name="min_price" id="min_price" maxlength="10" size="10" value="" placeholder="minimum">';
	$max_price = '<input class="input-text" type="text" name="max_price" id="max_price" maxlength="10" size="10" value="" placeholder="maximum">';

	// Date
	$start_date = '<input class="input-text" type="date" name="start_date" id="start_date" maxlength="15" size="10" value="">';
	$end_date = '<input class="input-text" type="date" name="end_date" id="end_date" maxlength="15" size="10" value="">';

	$text = '
<form id="search-data-form" class="woocommerce-checkout" action="'.get_site_url(null, 'search-results').'">
	<div id="customer_details">
		<p class="form-row form-row-wide">
			<label for="parish" class="">Parish</label>
			'.$parish_select.'
		</p>
		<p class="form-row form-row-wide">
			<label for="subdivision[]" class="">Subdivision</label>
			'.$subdivision_select.'
		</p>
		<p class="form-row form-row-wide">
			<label for="street[]" class="">Street</label>
			'.$street_select.'
		</p>
		<p class="form-row form-row-wide">
			<label for="district[]" class="">District</label>
			'.$district_select.'
		</p>
		<p class="form-row form-row-first">
			<label for="min_price" class="">Minimum Price</label>
			'.$min_price.'
		</p>
		<p class="form-row form-row-last">
			<label for="max_price" class="">Maximum Price</label>
			'.$max_price.'
		</p>
		<p class="form-row form-row-first">
			<label for="start_date" class="">Start Date</label>
			'.$start_date.'
		</p>
		<p class="form-row form-row-last">
			<label for="end_date" class="">End Date</label>
			'.$end_date.'
		</p>
		<input type="hidden" name="action" value="search_data">
		'.wp_nonce_field( 'search_data', 'search_data_nonce', false ).'
		<input type="submit" value="Search">
	</div>
</form>
<div id="search-results-map" class="hidden"></div>
<div id="search-results"></div>';

	$text .='<script type="text/javascript">var ajax_nonce = "'.wp_create_nonce( "get_parish_data" ).'"</script>';

	return $text;
}
add_shortcode('deedfax_search_tool', 'deedfax_search_tool');