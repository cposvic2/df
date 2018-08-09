<?php

add_action( 'show_user_profile', 'deedfax_user_profile_fields' );
add_action( 'edit_user_profile', 'deedfax_user_profile_fields' );
function deedfax_user_profile_fields( $user ) {
	if ( current_user_can('edit_users') ) {
	?>
		<h3>Contact Information</h3>
		<table class="form-table">
			<tr class="user-phone-wrap">
				<th><label for="phone">Phone Number</label></th>
				<td><input type="tel" name="phone" id="phone" value="<?php echo esc_html( get_user_meta($user->ID, 'phone', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="address">Street Address</label></th>
				<td><input type="text" name="address" id="address" value="<?php echo esc_html( get_user_meta($user->ID, 'address', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
			<tr class="user-address2-wrap">
				<th><label for="address2">Address Line 2</label></th>
				<td><input type="text" name="address2" id="address2" value="<?php echo esc_html( get_user_meta($user->ID, 'address2', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
			<tr class="user-city-wrap">
				<th><label for="city">City</label></th>
				<td><input type="text" name="city" id="city" value="<?php echo esc_html( get_user_meta($user->ID, 'city', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
			<tr class="user-state-wrap">
				<th><label for="state">State</label></th>
				<td><input type="text" name="state" id="state" value="<?php echo esc_html( get_user_meta($user->ID, 'state', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
			<tr class="user-zip-wrap">
				<th><label for="zip">Zip</label></th>
				<td><input type="text" name="zip" id="zip" value="<?php echo esc_html( get_user_meta($user->ID, 'zip', TRUE) ); ?>" class="regular-text code"></td>
			</tr>
		</table>
		<?php		
		if (in_array('subscriber', (array)$user->roles) || in_array('administrator', (array)$user->roles)) {
			global $subscriptions;
			$pcode = get_user_meta($user->ID, 'pcode', TRUE);
			$child_user_link = get_site_url().'/signup?pcode='.$pcode;
			?>
			<h3>Deedfax Subscription Information</h3>
			<table class="form-table">
				<tr class="user-renew_date-wrap">
					<th><label for="renew_date">Renewal Date</label></th>
					<td><input type="date" name="renew_date" id="renew_date" value="<?php echo esc_html( get_user_meta($user->ID, 'renew_date', TRUE) ); ?>" class="regular-text code"></td>
				</tr>
				<tr class="user-renew_date-wrap">
					<th><label for="renew_date">Number of accounts</label></th>
					<td><input type="text" name="total_accounts" id="total_accounts" value="<?php echo esc_html( get_user_meta($user->ID, 'total_accounts', TRUE) ); ?>" class="regular-text code"></td>
				</tr>
				<tr class="user-renew_date-wrap">
					<th><label for="renew_date">Child User Link</label></th>
					<td><a href="<?php echo $child_user_link; ?>"><?php echo $child_user_link; ?></a></td>
				</tr>

				<?php foreach ($subscriptions as $subscription => $subscription_parishes) : ?>
					<tr class="show-admin-bar user-admin-bar-front-wrap">
						<th scope="row"><label for="<?php echo $subscription; ?>"><?php echo $subscription; ?></label></th>
						<td>
							<fieldset>
								<label><input type="radio" name="<?php echo $subscription; ?>" value="<?php echo $subscription; ?>-both" <?php checked( (stripos(get_user_meta($user->ID, $subscription, true), 'both')  !== false), true ); ?>> Both</label><br>
								<label><input type="radio" name="<?php echo $subscription; ?>" value="<?php echo $subscription; ?>-online" <?php checked( (stripos(get_user_meta($user->ID, $subscription, true), 'online')  !== false), true ); ?>> Online</label><br>
								<label><input type="radio" name="<?php echo $subscription; ?>" value="<?php echo $subscription; ?>-print" <?php checked( stripos(get_user_meta($user->ID, $subscription, true), 'print')  !== false ); ?>> Print</label><br>
								<label><input type="radio" name="<?php echo $subscription; ?>" value="0" <?php checked( !get_user_meta($user->ID, $subscription, true) ); ?>> None</label>
							</fieldset>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php
		}

		if (in_array('child_subscriber', (array)$user->roles)) {
			$parent_user_id = get_user_meta($user->ID, 'parent_user_id', true);
			$parent_user = get_userdata( $parent_user_id );
		?>
		<h3>Deedfax Child Account Information</h3>
		<table class="form-table">
			<tr class="user-parent_user_id-wrap">
				<th><label for="parent_user_id">Parent Account</label></th>
				<td><?php if (true) : ?><a href="<?php echo get_edit_user_link( $parent_user_id ); ?>"><?php echo esc_html($parent_user->user_login); ?></a><?php else: ?>No user set<?php endif; ?></td>
			</tr>
		</table>
		<?php
		}
	}

}

add_action( 'personal_options_update', 'deedfax_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'deedfax_save_user_profile_fields' );
function deedfax_save_user_profile_fields( $user_id ) {
	if ( current_user_can('edit_users') ) {
		global $subscriptions;
		foreach ($subscriptions as $subscription => $subscription_parishes) {
			$value = sanitize_text_field( $_POST[$subscription] );
			update_user_meta( $user_id, $subscription, $value );
		}
		$phone = sanitize_text_field( $_POST['phone'] );
		update_user_meta( $user_id, 'phone', $phone );
		$address = sanitize_text_field( $_POST['address'] );
		update_user_meta( $user_id, 'address', $address );
		$address2 = sanitize_text_field( $_POST['address2'] );
		update_user_meta( $user_id, 'address2', $address2 );
		$city = sanitize_text_field( $_POST['city'] );
		update_user_meta( $user_id, 'city', $city );
		$state = sanitize_text_field( $_POST['state'] );
		update_user_meta( $user_id, 'state', $state );
		$zip = sanitize_text_field( $_POST['zip'] );
		update_user_meta( $user_id, 'zip', $zip );
		$renew_date = sanitize_text_field( $_POST['renew_date'] );
		update_user_meta( $user_id, 'renew_date', $renew_date );

	}
	return true;
}