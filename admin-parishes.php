<?php 

/**
 * Creates Admin parishes page
 */
function deedfax_admin_parishes_callback() {
	callback_start();
	if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && (int)$_GET['id']) {
		display_parish_edit();
	} else {
		?><h1>Parishes</h1><?php
		$exampleListTable = new Deedfax_Table;
		$exampleListTable->set_type('parish');
		$exampleListTable->prepare_items();
		$exampleListTable->display();
	}
	callback_end();
}

function display_parish_edit() {
	$id = (int)$_GET['id'];
	$entry = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->load($id);

	$delete_query = array(
		'id' => $entry->getId(),
		'action' => 'deedfax_parish_delete',
		'wpnonce' => wp_create_nonce( 'deedfax_parish_delete' )
	);

	?>
		<h1>Edit Parish</h1>
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
				</tbody>
			</table>
			<input type="hidden" name="action" value="deedfax_parish_edit">
			<input type="hidden" name="id" value="<?php echo $entry->getId()?>">
			<?php wp_nonce_field( 'deedfax_parish_edit', 'deedfax_parish_edit_nonce'); ?>
			<?php submit_button('Update'); ?>
			<a class="submitdelete deletion" href="<?php echo esc_html(admin_url('admin-post.php?'.http_build_query($delete_query))); ?>" onclick="return confirm('Are you sure you want to permanently delete this entry?')">Delete</a>
		</form>

	<?php
}

function deedfax_parish_edit_callback() {
	if ( !isset( $_REQUEST['_wp_http_referer'] ) ) {
		$url = wp_login_url();
	} else {
		$url = $_REQUEST['_wp_http_referer'];
	}

	if ( wp_verify_nonce($_REQUEST['deedfax_parish_edit_nonce'], 'deedfax_parish_edit') ) {
		$id = (int)$_REQUEST['id'];
		$name = sanitize_text_field($_REQUEST['name']);
		$slug = sanitize_title($_REQUEST['slug']);
		$entry = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->load($id);
		$entry->setName($name);
		$entry->setSlug($slug);
		DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->update($entry);
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
add_action('admin_post_deedfax_parish_edit', 'deedfax_parish_edit_callback');

function deedfax_parish_delete_callback() {
	if ( wp_verify_nonce($_REQUEST['wpnonce'], 'deedfax_parish_delete') ) {
		$id = (int)$_REQUEST['id'];
		//DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->delete($id);
		$url = admin_url('admin.php?page=deedfax-parishes');
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
add_action('admin_post_deedfax_parish_delete', 'deedfax_parish_delete_callback');