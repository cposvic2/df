<?php

function parse_users_csv( $csv_location ) {
	if (($handle = fopen($csv_location, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$user_data = array(
				'user_name' => import_encode($data[0]),
				'password' => import_encode($data[1]),
				'email' => import_encode($data[8]),
				'phone' => import_encode($data[3]),
				'address' => import_encode($data[4]),
				'city' => import_encode($data[5]),
				'state' => import_encode($data[6]),
				'zip' => import_encode($data[7]),
				'first_name' => import_encode($data[2]),
				'renew_date' => import_encode($data[9]),

				'greater-new-orleans' => import_encode($data[10]),
				'orleans' => import_encode($data[11]),
				'st-tammany' => import_encode($data[12]),
				'jefferson' => import_encode($data[13]),
				'st-bernard' => import_encode($data[14]),
				'st-charles' => import_encode($data[15]),
				'st-john' => import_encode($data[16]),
				'plaquemines' => import_encode($data[17]),
				'capitol-region' => import_encode($data[18]),
				'ebr-wbr-living' => import_encode($data[19]),
				'livingston' => import_encode($data[20]),
				'tangipahoa' => import_encode($data[21]),
				'ascension' => import_encode($data[22]),
				'iberville' => import_encode($data[23]),
				'east-feliciana' => "",
				'west-feliciana' => "",
			);

			create_deedfax_user( $user_data );
		}

		fclose($handle);
	}
}

function create_deedfax_user( $user_data ) {
	$user_name = sanitize_user($user_data['user_name']);
	$password = $user_data['password'];
	$email = sanitize_email($user_data['email']);

	$phone = sanitize_text_field($user_data['phone']);
	$address = sanitize_text_field($user_data['address']);
	$city = sanitize_text_field($user_data['city']);
	$state = sanitize_text_field($user_data['state']);
	$zip = sanitize_text_field($user_data['zip']);
	$first_name = sanitize_text_field($user_data['first_name']);
	$renew_date = sanitize_text_field($user_data['renew_date']);

	$user_id = wp_create_user( $user_name, $password, $email );

	update_user_meta($user_id, 'phone', $phone);
	update_user_meta($user_id, 'address', $address);
	update_user_meta($user_id, 'city', $city);
	update_user_meta($user_id, 'state', $state);
	update_user_meta($user_id, 'zip', $zip);
	update_user_meta($user_id, 'first_name', $first_name);
	update_user_meta($user_id, 'renew_date', $renew_date);

	global $subscriptions;

	foreach ($subscriptions as $subscription => $subscription_parishes) {
		if ($user_data[$subscription] == "X") {
			update_user_meta($user_id, $subscription, 'online');
		}
	}
}