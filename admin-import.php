<?php

/**
 * Creates Admin import page
 */
function deedfax_admin_import_callback() {
	callback_start();
	/*
	parse_deedfax_csv( DEEDFAX_PLUGIN_PATH.'/testdata.csv' );
	*/
	global $import_columns;
	$parish_options = array();
	$parishes = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->queryAll();
	foreach ($parishes as $parish) {
		$parish_options[] = array('value' => $parish->getSlug(), 'label' => $parish->getName());
	}
	?>
	<h1>Import Data</h1>
	<?php if (isset($_GET['settings-updated']) && !!$_GET['settings-updated']) { ?><div id="message" class="updated notice is-dismissible"><p>File imported successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div><?php } ?>
	<form method="post" enctype="multipart/form-data" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="import_file">File to Import</label></th>
					<td>
						<input type="file" name="import_file" id="import_file" accept=".csv" required>
						<p class="description" id="tagline-description">File format must be CSV.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Is the first row a header row?</th>
					<td>
						<label for="has_header">
							<input name="has_header" type="checkbox" id="has_header" value="1" checked>
							Yes
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="parish">Add properties to Parish</label></th>
					<td>
						<select name="parish" id="parish" required>
							<?php echo get_select_options( $parish_options, false, true ); ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<h2 class="title">Data Columns</h2>
		<p>Set the columns corresponding to the data you wish to upload.</p>
		<table class="form-table">
			<tbody>
				<?php
				$column_inputs = array('subdivision', 'square', 'lots', 'size', 'house', 'number', 'street', 'code', 'price', 'purchaser', 'entry', 'seller', 'other', 'other', 'other', 'date', 'pubyear', 'pubmonth', 'remarks');
				for ($i=0; $i < count($column_inputs); $i++) : ?>
				<tr>
					<th scope="row"><label for="col_<?php echo $i; ?>">Column <?php echo $i+1; ?></label></th>
					<td>
						<select name="col_<?php echo $i; ?>" id="col_<?php echo $i; ?>">
							<?php echo get_select_options( $import_columns, $column_inputs[$i] ); ?>
						</select>
					</td>
				</tr>
				<?php endfor; ?>
			</tbody>
		</table>
		<input type="hidden" name="action" value="deedfax_import">
		<?php wp_nonce_field( 'deedfax_import', 'deedfax_import_nonce'); ?>
		<?php submit_button('Import Data'); ?>
	</form>
	<?php
	callback_end();
}

function deedfax_import_submit() {
	if ( !isset( $_REQUEST['_wp_http_referer'] ) ) {
		$url = wp_login_url();
	} else {
		$url = $_REQUEST['_wp_http_referer'];
	}

	if ( wp_verify_nonce($_POST['deedfax_import_nonce'], 'deedfax_import') ) {
		$file = $_FILES['import_file'];
		if (isset($_POST['has_header']) && !!$_POST['has_header']) {
			$has_header = true;
		} else {
			$has_header = false;
		}
		
		$column_order = array();
		for ($i=0; $i <= 18; $i++) {
			$col_val = $_POST['col_'.$i];
			$column_order[$col_val] = $i;
		}
		
		$parish_slug = $_POST['parish'];
		$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($parish_slug);
		$rows_added = parse_deedfax_csv( $file['tmp_name'], $parish->getId(), $column_order, $has_header );
		if ($rows_added !== false) {
			$saved = 1;
			$message = 'Your data was imported successfully. '.$rows_added.' '.($rows_added === 1 ? 'property was' : 'properties were').'  added.';
		} else {
			$saved = 0;
			$message = 'Your data was not imported. Please check your data and try again.';	
		}
	} else {
		$saved = 0;
		$message = 'There was an error importing this data. Please try again later.';
	}

	$url = add_query_arg('updated', $saved, $url);
	$url = add_query_arg('message', rawurlencode($message), $url);
	wp_safe_redirect($url);
	exit;
}
add_action('admin_post_deedfax_import', 'deedfax_import_submit');