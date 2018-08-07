<?php
/*
Plugin Name: Deedfax Plugin
Description: This plugin adds required functionality for the Deedfax website.
Version: 1.0.1
Author: PUSH Design Group
Author URI: http://www.pushdesigngroup.com
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Set constants and globals
 */
global $wpdb;
define('DEEDFAX_PARISH_TABLE', $wpdb->prefix."parish" );
define('DEEDFAX_SUBDIVISION_TABLE', $wpdb->prefix."subdivision" );
define('DEEDFAX_STREET_TABLE', $wpdb->prefix."street" );
define('DEEDFAX_DISTRICT_TABLE', $wpdb->prefix."district" );
define('DEEDFAX_PROPERTY_TABLE', $wpdb->prefix."property" );
define('DEEDFAX_PLUGIN_NAME', 'deedfax_plugin' );
define('DEEDFAX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define('DEEDFAX_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
$google_api_key = 'AIzaSyAdVUFwzJU4JR3N1pHzWdwkY6tDLYnSIa4';
$google_places_api_key = 'AIzaSyAdVUFwzJU4JR3N1pHzWdwkY6tDLYnSIa4';
// given: AIzaSyCMxI8of7EVND8jTX3WikYWFOhc-QCx6sw

/**
 * Setup Plugin
 */
include( DEEDFAX_PLUGIN_PATH . '/classes/class-deedfax-base-dao.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-parish.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-street.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-subdivision.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-district.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-property.php');
include( DEEDFAX_PLUGIN_PATH . '/classes/class-dao-factory.php');
include( DEEDFAX_PLUGIN_PATH . '/functions.php');
include( DEEDFAX_PLUGIN_PATH . '/shortcodes.php');
include( DEEDFAX_PLUGIN_PATH . '/search-results.php');
include( DEEDFAX_PLUGIN_PATH . '/ajax.php');

if ( is_admin() ) {
	require_once DEEDFAX_PLUGIN_PATH . '/admin-user.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-properties.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-subdivisions.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-districts.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-parishes.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-streets.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin-import.php';
	require_once DEEDFAX_PLUGIN_PATH . '/admin.php';
	require_once DEEDFAX_PLUGIN_PATH . '/import_users.php';
}

/**
 * Register scripts
 */
function deedfax_enqueue_scripts() {
	global $google_api_key;
	wp_register_script('google_maps_api', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key='.$google_api_key, array(), '1.0.0', true);
	wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js');
	
	wp_register_script('deedfax_search', DEEDFAX_PLUGIN_URL.'/assets/js/deedfax-search.js', array(), '1.0.0', true);
	wp_register_script('deedfax_search_results', DEEDFAX_PLUGIN_URL.'/assets/js/deedfax-search-results.js', array(), '1.0.0', true);

	global $post;
	if ( is_singular() && has_shortcode($post->post_content, 'deedfax_search_tool') ) {
		wp_enqueue_script('select2');
		wp_enqueue_script('google_maps_api');
		wp_enqueue_script('deedfax_search');
		wp_enqueue_style('deedfax-style',  DEEDFAX_PLUGIN_URL.'/assets/css/deedfax-style.css');
		wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css');
	}

	if ( is_singular() && is_page('search-results') ) {
		wp_enqueue_script('google_maps_api');
		wp_enqueue_script('deedfax_search_results');
		wp_enqueue_style('deedfax-style',  DEEDFAX_PLUGIN_URL.'/assets/css/deedfax-style.css');
	}
}
add_action( 'wp_enqueue_scripts', 'deedfax_enqueue_scripts' );

/**
 * Activation hook. Creates custom tables.
 */
function deedfax_plugin_activation() {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE ".DEEDFAX_PARISH_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		slug varchar(55) DEFAULT '' NOT NULL,
		name text NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".DEEDFAX_SUBDIVISION_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		slug varchar(55) DEFAULT '' NOT NULL,
		name text NOT NULL,
		parish_id mediumint(9) NOT NULL,
		date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".DEEDFAX_STREET_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		slug varchar(55) DEFAULT '' NOT NULL,
		name text NOT NULL,
		parish_id mediumint(9) NOT NULL,
		date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".DEEDFAX_PROPERTY_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		old_id mediumint(9),
		latitude DOUBLE,
		longitude DOUBLE,
		parish_id mediumint(9) NOT NULL,
		street_id mediumint(9) DEFAULT 1 NOT NULL,
		subdivision_id mediumint(9) DEFAULT 1 NOT NULL,
		district_id mediumint(9) DEFAULT 1 NOT NULL,
		township boolean DEFAULT 0 NOT NULL,
		square varchar(55) DEFAULT '' NOT NULL,
		lot varchar(55) DEFAULT '' NOT NULL,
		size varchar(55) DEFAULT '' NOT NULL,
		house varchar(55) DEFAULT '' NOT NULL,
		code varchar(55) DEFAULT '' NOT NULL,
		price DECIMAL(10, 2) DEFAULT 0 NOT NULL,
		price_text varchar(55) DEFAULT '' NOT NULL,
		purchaser text DEFAULT '' NOT NULL,
		entry varchar(55) DEFAULT '' NOT NULL,
		seller text DEFAULT '' NOT NULL,
		sell_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		remarks longtext DEFAULT '' NOT NULL,
		publication_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		date_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

	$parishes = array(
		'Ascension',
		'East Baton Rouge',
		'Iberville',
		'Livingston',
		'Tangipahoa',
		'West Baton Rouge',
		'Jefferson',
		'Orleans',
		'Plaquemines',
		'St. Bernard',
		'St. Charles',
		'St. John',
		'St. Tammany',
		'East Feliciana',
		'West Feliciana',
	);

	foreach ($parishes as $name) {
		$slug = sanitize_title( $name );
		$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($slug);
		if (!$parish) {
			$parish = new DeedfaxParish;
			$parish->setSlug($slug);
			$parish->setName($name);
			DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->insert($parish);
		}
	}

	$district = DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->loadBySlugAndParish('none', 0);
	if (!$district) {
		$district = new DeedfaxDistrict;
		$district->setSlug('none');
		$district->setName('None');
		$district->setParishId(0);
		DeedfaxDAOFactory::getDAO('DeedfaxDistrictDAO')->insert($district);
	}

	$subdivision = DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->loadBySlugAndParish('none', 0);
	if (!$subdivision) {
		$subdivision = new DeedfaxSubdivision;
		$subdivision->setSlug('none');
		$subdivision->setName('None');
		$subdivision->setParishId(0);
		DeedfaxDAOFactory::getDAO('DeedfaxSubdivisionDAO')->insert($subdivision);
	}

	$street = DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->loadBySlugAndParish('none', 0);
	if (!$street) {
		$street = new DeedfaxStreet;
		$street->setSlug('none');
		$street->setName('None');
		$street->setParishId(0);
		DeedfaxDAOFactory::getDAO('DeedfaxStreetDAO')->insert($street);
	}
}
register_activation_hook( __FILE__, 'deedfax_plugin_activation' );