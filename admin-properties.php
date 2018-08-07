<?php 

/**
 * Creates Admin properties page
 */
function deedfax_admin_properties_callback() {
	callback_start();
	if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && (int)$_GET['id']) {
		display_property_edit();
	} else {
		?><h1>Properties</h1><?php
		$exampleListTable = new Deedfax_Table;
		$exampleListTable->set_type('property');
		$exampleListTable->prepare_items();
		$exampleListTable->display();
	}
	callback_end();
}

function display_property_edit() {
	$id = (int)$_GET['id'];
	$entry = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->load($id);

	$delete_query = array(
		'id' => $entry->getId(),
		'action' => 'deedfax_property_delete',
		'wpnonce' => wp_create_nonce( 'deedfax_property_delete' )
	);

	$parish_options = array();
	$parishes = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->queryAll();
	foreach ($parishes as $parish) {
		$parish_options[] = array('value' => $parish->getId(), 'label' => $parish->getName());
	}
	?>
		<h1>Edit Property</h1>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="latitude">Latitude</label></th>
						<td>
							<input type="text" name="latitude" id="latitude" value="<?php echo $entry->getLatitude()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="longitude">Longitude</label></th>
						<td>
							<input type="text" name="longitude" id="longitude" value="<?php echo $entry->getLongitude()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="old_id">Old ID</label></th>
						<td>
							<input type="text" name="old_id" id="old_id" value="<?php echo $entry->getOldId()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="parish_id">Parish</label></th>
						<td>
							<select name="parish_id" id="parish_id" required>
								<?php echo get_select_options( $parish_options, $entry->getParishId(), false ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="township">Township</label></th>
						<td>
							<select name="township" id="township" required="">
								<option value="1" <?php selected($entry->getTownship(), 1); ?>>Yes</option>
								<option value="0" <?php selected($entry->getTownship(), 0); ?>>No</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="district_id">District ID</label></th>
						<td>
							<input type="text" name="district_id" id="district_id" value="<?php echo $entry->getDistrictId()?>" class="regular-text">
							<span class="description">Get District ID from <a href="<?php menu_page_url( 'deedfax-districts' ); ?>">Deedfax District page</a></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="subdivision_id">Subdivision ID</label></th>
						<td>
							<input type="text" name="subdivision_id" id="subdivision_id" value="<?php echo $entry->getSubdivisionId()?>" class="regular-text">
							<span class="description">Get Subdivision ID from <a href="<?php menu_page_url( 'deedfax-subdivisions' ); ?>">Deedfax Subdivisions page</a></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="street_id">Street ID</label></th>
						<td>
							<input type="text" name="street_id" id="street_id" value="<?php echo $entry->getStreetId()?>" class="regular-text">
							<span class="description">Get Street ID from <a href="<?php menu_page_url( 'deedfax-streets' ); ?>">Deedfax Streets page</a></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="square">Square</label></th>
						<td>
							<input type="text" name="square" id="square" value="<?php echo $entry->getSquare()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lot">Lot</label></th>
						<td>
							<input type="text" name="lot" id="lot" value="<?php echo $entry->getLot()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="size">Size</label></th>
						<td>
							<input type="text" name="size" id="size" value="<?php echo $entry->getSize()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="house">House</label></th>
						<td>
							<input type="text" name="house" id="house" value="<?php echo $entry->getHouse()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="code">Code</label></th>
						<td>
							<input type="text" name="code" id="code" value="<?php echo $entry->getCode()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="price">Price</label></th>
						<td>
							<input type="text" name="price" id="price" value="<?php echo $entry->getPrice()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="price_text">Price Text</label></th>
						<td>
							<input type="text" name="price_text" id="price_text" value="<?php echo $entry->getPriceText()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="purchaser">Purchaser</label></th>
						<td>
							<input type="text" name="purchaser" id="purchaser" value="<?php echo $entry->getPurchaser()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="entry">Entry</label></th>
						<td>
							<input type="text" name="entry" id="entry" value="<?php echo $entry->getEntry()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="seller">Seller</label></th>
						<td>
							<input type="text" name="seller" id="seller" value="<?php echo $entry->getSeller()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sell_date">Sell Date</label></th>
						<td>
							<input type="date" name="sell_date" id="sell_date" value="<?php echo $entry->getSellDate('Y-m-d')?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="publication_date">Publication Date</label></th>
						<td>
							<input type="date" name="publication_date" id="publication_date" value="<?php echo $entry->getPublicationDate('Y-m-d')?>" class="regular-text">
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="action" value="deedfax_property_edit">
			<input type="hidden" name="id" value="<?php echo $entry->getId()?>">
			<?php wp_nonce_field( 'deedfax_property_edit', 'deedfax_property_edit_nonce'); ?>
			<?php submit_button('Update'); ?>
			<a class="submitdelete deletion" href="<?php echo esc_html(admin_url('admin-post.php?'.http_build_query($delete_query))); ?>" onclick="return confirm('Are you sure you want to permanently delete this entry?')">Delete</a>
		</form>
	<?php
}

function deedfax_property_edit_callback() {
	if ( !isset( $_REQUEST['_wp_http_referer'] ) ) {
		$url = wp_login_url();
	} else {
		$url = $_REQUEST['_wp_http_referer'];
	}

	if ( wp_verify_nonce($_REQUEST['deedfax_property_edit_nonce'], 'deedfax_property_edit') ) {
		$id = (int)$_REQUEST['id'];

		$latitude = (float)$_REQUEST['latitude'];
		$longitude = (float)$_REQUEST['longitude'];
		$parish_id = (int)$_REQUEST['parish_id'];
		$old_id = (int)$_REQUEST['old_id'];
		$district_id = (int)$_REQUEST['district_id'];
		$subdivision_id = (int)$_REQUEST['subdivision_id'];
		$street_id = (int)$_REQUEST['street_id'];

		$township = sanitize_text_field($_REQUEST['township']);
		$square = sanitize_text_field($_REQUEST['square']);
		$lot = sanitize_text_field($_REQUEST['lot']);
		$size = sanitize_text_field($_REQUEST['size']);
		$house = sanitize_text_field($_REQUEST['house']);
		$code = sanitize_text_field($_REQUEST['code']);
		$price = sanitize_text_field($_REQUEST['price']);
		$price_text = sanitize_text_field($_REQUEST['price_text']);
		$purchaser = sanitize_text_field($_REQUEST['purchaser']);
		$entry_field = sanitize_text_field($_REQUEST['entry']);
		$seller = sanitize_text_field($_REQUEST['seller']);
		$sell_date = sanitize_text_field($_REQUEST['sell_date']);
		$remarks = sanitize_text_field($_REQUEST['remarks']);
		$publication_date = sanitize_text_field($_REQUEST['publication_date']);

		$entry = DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->load($id);
		$entry->setLatitude( $latitude );
		$entry->setLongitude( $longitude );
		$entry->setParishId( $parish_id );
		$entry->setDistrictId( $district_id );
		$entry->setSubdivisionId( $subdivision_id );
		$entry->setStreetId( $street_id );
		$entry->setTownship( $township );
		$entry->setSquare( $square );
		$entry->setLot( $lot );
		$entry->setSize( $size );
		$entry->setHouse( $house );
		$entry->setCode( $code );
		$entry->setPrice( $price );
		$entry->setPriceText( $price_text );
		$entry->setPurchaser( $purchaser );
		$entry->setEntry( $entry_field );
		$entry->setSeller( $seller );
		$entry->setSellDate( $sell_date, 'Y-m-d' );
		$entry->setRemarks( $remarks );
		$entry->setPublicationDate( $publication_date, 'Y-m-d');

		DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->update($entry);
		$saved = 1;
		$message = 'This entry was updated successfully.';
	} else {
		$saved = 0;
		$message = 'There was an error updating this entry. Please try again later.';
	}

	if ( !isset( $_REQUEST['_wp_http_referer'] ) ) {
		$_REQUEST['_wp_http_referer'] = wp_login_url();
	}

	$url = add_query_arg('updated', $saved, $url);
	$url = add_query_arg('message', rawurlencode($message), $url);
	wp_safe_redirect($url);
	exit;
}
add_action('admin_post_deedfax_property_edit', 'deedfax_property_edit_callback');

function deedfax_property_delete_callback() {
	if ( wp_verify_nonce($_REQUEST['wpnonce'], 'deedfax_property_delete') ) {
		$id = (int)$_REQUEST['id'];
		DeedfaxDAOFactory::getDAO('DeedfaxPropertyDAO')->delete($id);
		$url = admin_url('admin.php?page=deedfax-properties');
		$deleted = 1;
		$message = 'This entry was deleted successfully.';
	} else {
		$deleted = 0;
		$message = 'There was an error deleting this entry. Please try again later.';
	}

	$url = add_query_arg('deleted', $deleted, $url);
	$url = add_query_arg('message', rawurlencode($message), $url);
	wp_safe_redirect($url);
	exit;
}
add_action('admin_post_deedfax_property_delete', 'deedfax_property_delete_callback');