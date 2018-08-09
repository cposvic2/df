<?php

$subscriptions = array(
	'orleans' => array(
		'orleans',
	),
	'jefferson' => array(
		'jefferson',
	),
	'st-bernard' => array(
		'st-bernard',
	),
	'st-tammany' => array(
		'st-tammany',
	),
	'st-charles' => array(
		'st-charles',
	),
	'st-john' => array(
		'st-john',
	),
	'plaquemines' => array(
		'plaquemines',
	),
	'east-baton-rouge' => array(
		'east-baton-rouge',
	),
	'west-baton-rouge' => array(
		'west-baton-rouge',
	),
	'livingston' => array(
		'livingston',
	),
	'tangipahoa' => array(
		'tangipahoa',
	),
	'ascension' => array(
		'ascension',
	),
	'iberville' => array(
		'iberville',
	),
	'lafourche' => array(
		'lafourche',
	),
	'terrebonne' => array(
		'terrebonne',
	),
	'st-mary' => array(
		'st-mary',
	),
	'iberia' => array(
		'iberia',
	),
	'washington' => array(
		'washington',
	),
	'st-james' => array(
		'st-james',
	),
	'assumption' => array(
		'assumption',
	),
	'east-feliciana' => array(
		'east-feliciana',
	),
	'west-feliciana' => array(
		'west-feliciana',
	),
);

function get_select_options( $arrays, $selected = false, $include_select = true, $disabled = false ) {
	if (!count($arrays))
		return '<option value="">None available</option>';

	$return = '';
	if ($include_select)
		$return .= '<option value="" '.selected( false, $selected, false ).( $disabled ? ' disabled' : '').'>Select</option>';
	foreach ( $arrays as $array ) {
		$return .= '<option value="'.$array['value'].'"'.selected( $array['value'], $selected, false ).'>'.$array['label'].'</option>';
	}
	return $return;
}

function get_user_parishes($user = null) {
	if (is_null($user)) {
		$user = wp_get_current_user();
	}

	$parishes = array();

	global $subscriptions;

	foreach ($subscriptions as $subscription => $subscription_parishes) {
		if ( user_is_subscribed($user, $subscription) ) {
			foreach ($subscription_parishes as $subscription_parish) {
				$parishes[$subscription_parish] = true;
			}
		}
	}

	$user_parishes = array();
	foreach ($parishes as $key => $value) {
		if (!!$value) {
			$parish = DeedfaxDAOFactory::getDAO('DeedfaxParishDAO')->loadBySlug($key);
			if (!!$parish) {
				$user_parishes[] = $parish;
			}
		}
	}

	return $user_parishes;
}

function user_is_subscribed( $user, $subscription ) {
	return (stripos(get_user_meta($user->ID, $subscription, true), 'online') !== false || stripos(get_user_meta($user->ID, $subscription, true), 'both') !== false);
}

function redirect_from_search_tool() {
	global $post;
	if( is_singular() ) {
		if( is_user_logged_in() ) {
			if ( $post->post_name === 'signup' ) {
				wp_safe_redirect( get_site_url(null, '/manage-account/') );
				exit;
			}
		} else {
			if ( has_shortcode($post->post_content, 'deedfax_search_tool') || $post->post_name === 'manage-account' || $post->post_name === 'search-results' ) {
				auth_redirect();
				exit;
			}
		}
	}
}
add_action( 'wp', 'redirect_from_search_tool' );


function deedfax_login_logo() { ?>
	<style type="text/css">
		#login h1 a, .login h1 a {
			background-image: url(<?php echo plugin_dir_url(__FILE__); ?>/assets/img/logo.jpg);
		height:53px;
		width:300px;
		background-size: 300px 53px;
		background-repeat: no-repeat;
			padding-bottom: 30px;
		}
	</style>
<?php }
add_action( 'login_enqueue_scripts', 'deedfax_login_logo' );

add_filter('wp_nav_menu_items','add_login_link', 10, 2);
function add_login_link( $items, $args ) {
	if( $args->theme_location == 'top')  {
		if (is_user_logged_in()) {
			$items .= '
<li class="menu-item menu-item-type-post_type menu-item-object-page first">
	<a href="'.get_site_url(null, 'search').'" data-level="1">
		<span class="menu-item-text">
			<span class="menu-text">Search</span>
		</span>
	</a>
</li>';
		} else {
			$items .= '
<li class="menu-item menu-item-type-post_type menu-item-object-page first">
	<a href="'.wp_login_url( get_permalink() ).'" data-level="1">
		<span class="menu-item-text">
			<span class="menu-text">Login</span>
		</span>
	</a>
</li>';
		}

	}
	return $items;
}

function deedfax_send_email( $recipient ) {
	$boundary = uniqid('np');
	$headers = 'From: Deedfax Online <admin@deedfaxonline.com>' . "\r\n";
	$headers .= 'Content-Type: multipart/alternative;boundary="' . $boundary . '"' . "\r\n";

	$content = '
	<p>Dear Valued Deedfax client,</p>
	<p>Your current subscription has expired.  Please click the link below, fill out the online form to confirm your account, and continue your membership.  Future renewals will be automated, and your credit card will be automatically billed on the renewal date to ensure your membership stays active.</p>
	<p><a href="'.get_site_url(null, 'manage-account').'">'.get_site_url(null, 'manage-account').'</a></p>
	<p>Thank you for your assistance. We appreciate your continued patronage.</p>
	<p>Deedfax Online</p>';

	//here is the content body
	$message = "This is a MIME encoded message.";
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";

	//Plain text body
	$message .= wp_strip_all_tags($content) . "\n";
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";

	//Html body
	$message .= '<html>
		<head>
		<title>Your subscription has expired</title> 
		</head>
		<body>';
	$message .= wpautop($content);
	$message .= '</body>
		</html>';
	
	$message .= "\r\n\r\n--" . $boundary . "--";

	return mail( $recipient, 'Your subscription has expired', $message, $headers );
}

function deedfax_schedule_user_emails() {
	$users = get_users(array(
		'meta_key' => 'renew_date',
	));
	foreach ($users as $user) {
		$renew_date = get_user_meta($user->ID, 'renew_date', TRUE);
		$renew_datetime = DateTime::createFromFormat( 'Y-m-d', $renew_date );	
		$timestamp = $renew_datetime->getTimestamp();
		$user_info = get_userdata($user->ID);
		$email = $user_info->user_email;
		wp_schedule_single_event( $timestamp, 'deedfax_send_email', array($email) );
	}
}

function deedfax_schedule_user_email_test() {
	$users = get_users(array(
		'role'         => 'administrator',
	));
	foreach ($users as $user) {
		$renew_date = get_user_meta($user->ID, 'renew_date', TRUE);
		var_dump($renew_date);
		$renew_datetime = DateTime::createFromFormat( 'Y-m-d', $renew_date );	
		$timestamp = $renew_datetime->getTimestamp();
		$user_info = get_userdata($user->ID);
		$email = $user_info->user_email;
		wp_schedule_single_event( $timestamp, 'deedfax_send_email', array($email) );
	}
}

/**
 * Disable Admin Notification of User Password Change
 *
 */
if ( ! function_exists( 'wp_password_change_notification' ) ) {
	function wp_password_change_notification( $user ) {
		return;
	}
}

/**
 * Disable New User Notifications
 *
 */
if ( ! function_exists( 'wp_new_user_notification' ) ) {
	function wp_new_user_notification( $user ) {
		return;
	}
}
