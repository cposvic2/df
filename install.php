<?php

function deedfax_plugin_activate() {
	global $wp_roles;
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	$adm = $wp_roles->get_role('subscriber');
	$wp_roles->add_role('child_subscriber', 'Child Subscriber', $adm->capabilities);
}
register_activation_hook( 'deedfax-plugin', 'deedfax_plugin_activate' );