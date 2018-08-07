<?php

/**
 * Creates Admin districts page
 */
function deedfax_admin_districts_callback() {
	callback_start();
	if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && (int)$_GET['id']) {
		display_district_edit();
	} else {
		?><h1>Districts</h1><?php
		$exampleListTable = new Deedfax_Table;
		$exampleListTable->set_type('district');
		$exampleListTable->prepare_items();
		$exampleListTable->display();
	}
	callback_end();
}

function display_district_edit() {
	$id = (int)$_GET['id'];
	$entry = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->load($id);

	$delete_query = array(
		'id' => $entry->getId(),
		'action' => 'deedfax_district_delete',
		'wpnonce' => wp_create_nonce( 'deedfax_district_delete' )
	);

	$parish_options = array();
	$parishes = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->queryAll();
	foreach ($parishes as $parish) {
		$parish_options[] = array('value' => $parish->getId(), 'label' => $parish->getName());
	}
	?>
		<h1>Edit district</h1>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="name">Name</label></th>
						<td>
							<input type="text" name="name" id="name" value="<?php echo $entry->getName()?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="slug">Slug</label></th>
						<td>
							<input type="text" name="slug" id="slug" value="<?php echo $entry->getSlug()?>" class="regular-text">
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
				</tbody>
			</table>
			<input type="hidden" name="action" value="deedfax_district_edit">
			<input type="hidden" name="id" value="<?php echo $entry->getId()?>">
			<?php wp_nonce_field( 'deedfax_district_edit', 'deedfax_district_edit_nonce'); ?>
			<?php submit_button('Update'); ?>
			<a class="submitdelete deletion" href="<?php echo esc_html(admin_url('admin-post.php?'.http_build_query($delete_query))); ?>" onclick="return confirm('Are you sure you want to permanently delete this entry?')">Delete</a>
		</form>

	<?php
}

function deedfax_district_edit_callback() {
	if ( !isset( $_REQUEST['_wp_http_referer'] ) ) {
		$url = wp_login_url();
	} else {
		$url = $_REQUEST['_wp_http_referer'];
	}

	if ( wp_verify_nonce($_REQUEST['deedfax_district_edit_nonce'], 'deedfax_district_edit') ) {
		$id = (int)$_REQUEST['id'];
		$parish_id = (int)$_REQUEST['parish_id'];
		$name = sanitize_text_field($_REQUEST['name']);
		$slug = sanitize_title($_REQUEST['slug']);
		$entry = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->load($id);
		$entry->setName($name);
		$entry->setSlug($slug);
		$entry->setParishId($parish_id);
		DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->update($entry);
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
add_action('admin_post_deedfax_district_edit', 'deedfax_district_edit_callback');

function deedfax_district_delete_callback() {
	if ( wp_verify_nonce($_REQUEST['wpnonce'], 'deedfax_district_delete') ) {
		$id = (int)$_REQUEST['id'];
		DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->delete($id);
		$url = admin_url('admin.php?page=deedfax-districts');
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
add_action('admin_post_deedfax_district_delete', 'deedfax_district_delete_callback');