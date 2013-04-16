<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Common functions library
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Initialiaize configuration options array
 * Define sane defaults
 * Load from WP options API / WP DB if exist
 * Add missing from defined defaults
 *
 * @global array $evecorp_options
 */
function evecorp_init_options()
{
	global $evecorp_options;

	/* Newly introduced options should be added here */
	$default_options = array(
		'plugin_version'			 => EVECORP_VERSION,
		'corpkey_ID'				 => '',
		'corpkey_vcode'				 => '',
		'corpkey_type'				 => '',
		'corpkey_access_mask'		 => '',
		'corpkey_expires'			 => '',
		'corpkey_character_name'	 => '',
		'corpkey_corporation_name'	 => '',
		'corpkey_verified'			 => false,
		'API_base_url'				 => 'https://api.eveonline.com/',
		'cache_API'					 => false,
		'char_url'					 => 'https://gate.eveonline.com/Profile/',
		'char_url_label'			 => 'EVE Gate',
		'corp_url'					 => 'https://gate.eveonline.com/Corporation/',
		'corp_url_label'			 => 'EVE Gate',
	);

	/* Get options from WP options API, if any. Use defaults if none. */
	$saved_options = get_option( 'evecorp_options', $default_options );

	/* If options are there but one or more are missing */
	$evecorp_options = array_merge( $default_options, $saved_options );

	/* Save the options in WP optins DB */
	update_option( 'evecorp_options', $evecorp_options );

	/**
	 * You can override any option with a constant defined in wp-config.php
	 * Thee will not be saved to options DB
	 */
	foreach ( $evecorp_options as $key => &$value ) {
		if ( defined( 'EVECORP_' . strtoupper( $key ) ) )
			$value = constant( 'EVECORP_' . strtoupper( $key ) );
	}
	unset( $value ); // break the reference with the last element;
}

/**
 * Returns the configuration option value of $key from WP options API.
 * May be overriden by constants defined in wp-config.php
 *
 * @param string $key The name of the option requested.
 * @return string The requested option value.
 */
function evecorp_get_option( $key )
{
	$evecorp_options		 = get_option( 'evecorp_options' );
	if ( defined( 'EVECORP_' . strtoupper( $key ) ) )
		$evecorp_options[$key]	 = constant( 'EVECORP_' . strtoupper( $key ) );
	return $evecorp_options[$key];
}

/**
 * Tests if there is a valid corporation key for the plugin to work.
 *
 * @return boolean True if key is validated.
 */
function evecorp_corpkey_check()
{
	return evecorp_get_option( 'corpkey_verified' );
}

/**
 * Tests if the current browser is a Eve Online in-game browser.
 *
 * @return boolean true if client is Eve Online in-game browser.
 */
function evecorp_is_eve()
{

	global $evecorp_IGB_data;

	if ( !isset( $evecorp_IGB_data ) )
		$evecorp_IGB_data = evecorp_IGB_data();

	return $evecorp_IGB_data['is_igb'];
}

/**
 * Tests if the Eve Online in-game browser is trusting us.
 *
 * @uses evecorp_igb_data()
 * @return boolean True if client is trusting us.
 */
function evecorp_is_trusted()
{

	global $evecorp_IGB_data;

	if ( !isset( $evecorp_IGB_data ) )
		$evecorp_IGB_data = evecorp_IGB_data();

	return $evecorp_IGB_data['trusted'];
}

/**
 * Returns the Eve Online in game browser data, if any
 *
 * @return array Browser data.
 */
function evecorp_IGB_data()
{

	global $evecorp_IGB_data;

	$evecorp_IGB_data = array(
		'is_igb'	 => false,
		'trusted'	 => false,
		'values'	 => array( )
	);

	foreach ( $_SERVER as $key => $value ) {

// Skip on non-Eve headers
		if ( strpos( $key, 'HTTP_EVE_' ) === 0 ) {

// IGB browser detected
			$evecorp_IGB_data['is_igb'] = true;

// Remove the HTTP_EVE_ prefix and make it lowercase
			$key = strtolower( str_replace( 'HTTP_EVE_', '', $key ) );

// Set the trusted value to true if the header has been sent.
			if ( $key === 'trusted' && 'Yes' === $value )
				$evecorp_IGB_data['trusted'] = true;

// Store key and value in array
			$evecorp_IGB_data['values'][$key] = $value;
		}
	}
	return($evecorp_IGB_data);
}

function evecorp_get_portrait( $character_ID, $size, $alt = '' )
{
	if ( is_ssl() ) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}
	if ( empty( $alt ) ) {
		$alt		 = 'Portrait of a Pilot';
	}
	$host		 = 'image.eveonline.com';
	$server_path = 'Character';
	$eve_size	 = evecorp_avatar_size( $size );
	$suffix		 = 'jpg';
	$image_url	 = $protocol . trailingslashit( $host ) . trailingslashit( $server_path ) . $character_ID . '_' . $eve_size . '.' . $suffix;
	$html		 = "<img alt='{$alt}' src='{$image_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
	return $html;
}

/**
 * Returns Eve Online character portrait image (replacing the Gravatar image).
 *
 * @fixme Still ugly, in dare need of refactoring.
 * @global type $comment
 * @param type $avatar
 * @param type $id_or_email
 * @param type $size
 * @return type
 */
function evecorp_get_avatar( $avatar, $id_or_email, $size )
{
	global $comment;

	/* Check if we are in a comment */
	if ( !is_null( $comment ) && !empty( $comment->user_id ) ) {
		$user_id = $comment->user_id;
	}

	if ( is_a( $id_or_email, 'WP_User' ) )
		$user_id = $id_or_email->ID;

	/* Pass it along if its a mail address */
	if ( !is_object( $id_or_email ) ) {
		if ( is_email( $id_or_email ) )
			return $avatar;

		/* Dunno how to handle non numeric id-numbers */
		if ( is_numeric( $id_or_email ) )
			$user_id = (int) $id_or_email;
	}

	/* The user who installed WP, is unlikely a Eve Online character */
	if ( 1 === $user_id )
		return $avatar;

	/* Lets see if we got a working user-id at last */
	$user = get_userdata( $user_id );
	if ( !is_a( $user, 'WP_User' ) )
		return $avatar;

	$alt			 = $user->user_nicename;
	$character_ID	 = get_user_meta( $user_id, 'evecorp_character_ID', true );
	$eve_avatar		 = evecorp_get_portrait( $character_ID, $size, $alt );
	return $eve_avatar;
}

/**
 * Ugly hack to fill-in the WP user ID where there is mail address missing.
 *
 * @param string $value Usually contains the mail address of a WP user.
 * @param string $user_id The WP user ID.
 * @return string
 */
function evecorp_author_mail( $value, $user_id )
{
	if ( empty( $value ) )
		$value = $user_id;
	return $value;
}

/**
 * Return a GMT offset in hours from user options if found.
 *
 * @param string $site_gmt_offset GTM offset in hours.
 * @return string GTM offset in hours.
 */
function evecorp_gmt_offset( $site_gmt_offset )
{
	if ( isset( $_REQUEST['user_id'] ) ) {
		$user_gmt_offset = get_user_option( 'evecorp_gmt_offset', $_REQUEST['user_id'] );
	} else {
		$user_gmt_offset = get_user_option( 'evecorp_gmt_offset' );
	}
	if ( $user_gmt_offset )
		return $user_gmt_offset;
	return $site_gmt_offset;
}

/**
 * Return a timezone location string from user options if found.
 *
 * @param string $site_timezone_string Timezone string.
 * @return string Timezone string.
 */
function evecorp_timezone_string( $site_timezone_string )
{
	if ( isset( $_REQUEST['user_id'] ) ) {
		$user_timezone_string = get_user_option( 'evecorp_timezone_string', $_REQUEST['user_id'] );
	} else {
		$user_timezone_string = get_user_option( 'evecorp_timezone_string' );
	}
	if ( $user_timezone_string )
		return $user_timezone_string;
	return $site_timezone_string;
}

/**
 * Returns the neaerest image size available for Eve Online character portraits.
 *
 * Valid Eve Online image sizes:
 *  30, 32, 64, 128, 200, 256, 512
 * (and 1024 for Incusrions 1.4 or later characters only).
 *
 * Valid Gravatar image sizes:
 *  Default 80, everything from 1px to 2048px
 *
 * @param string $requested_size The image size requested
 * @return string The image size available.
 */
function evecorp_avatar_size( $requested_size )
{
	$eve_image_sizes = array( 30, 32, 64, 128, 200, 256, 512, 1024 );
	if ( in_array( $requested_size, $eve_image_sizes ) )
		return $requested_size;
	foreach ( $eve_image_sizes as $size ) {

		/* Return the next bigger available */
		if ( (int) $requested_size < $size )
			return (string) $size;
	}

	/* Return the biggets available */
	return '1024';
}

/**
 * Return array of all known alternative characters of a character.
 *
 * @param string $character_name the name of the character.
 * @return array Array containing the found alts, if any.
 */
function evecorp_get_alts( $character_name )
{
	$alts = array( );
	/* Is the character a registred WP user? */
	$user = get_user_by( 'login', sanitize_user( $character_name ) );
	if ( $user ) {

		/* Are there any Eve Online API key stored with this user? */
		$userkeys = get_user_meta( $user->ID, 'evecorp_userkeys', true );
		if ( is_array( $userkeys ) ) {

			/* Our corporation name */
			$site_corp_name = evecorp_get_option( 'corpkey_corporation_name' );

			/* Work trough the stored API keys */
			foreach ( $userkeys as $userkey ) {

				/* Skip any key that belongs to the user itself,
				 * we only want his alts. */
				if ( $character_name !== $userkey['characterName'] ) {

					/* He may have changed corporation since this API key was
					 * stored. */
					$alt_corp_name = evecorp_get_char_corp( $userkey['characterID'] );
					if ( $site_corp_name != $alt_corp_name ) {

						/* @todo Update stored userkey with new corporation. */
						$userkey['corporationName']	 = $alt_corp_name;
					}
					$alts[]						 = array(
						'character_name'	 => $userkey['characterName'],
						'character_ID'		 => $userkey['characterID'],
						'corporation_name'	 => $userkey['corporationName']
					);
				}
			}
		}
	}
	return $alts;
}

/**
 * Split a camelcase string in properly spaced words.
 *
 * @param string $camelcase_str
 * @return string
 */
function camelcase_split( $camelcase_str )
{
	$regex	 = '/# Match position between camelCase "words".
    (?<=[a-z])  # Position is after a lowercase,
    (?=[A-Z])   # and before an uppercase letter.
    /x';
	$str	= '';
	$array	 = preg_split( $regex, $camelcase_str );
	$count	 = count( $array );
	for ( $i = 0; $i < $count; ++$i ) {
		$str .= $array[$i] . ' ';
	}
	return substr( $str, 0, -1 );
}

/**
 * Returns HTML code for a request trust button for Eve Online in-game browsers.
 *
 * @return string The HTML code for the button.
 */
function evecorp_trust_button()
{
	$html = '<button type="button" onclick="CCPEVE.requestTrust(\'' . home_url() . '\');">Set ' . home_url() . ' as trusted</button>';
	return $html;
}

/**
 * Returns HTML code for small "Yes" or "No" icons.
 *
 * @return string
 */
function evecorp_icon( $icon )
{
	strtolower( $icon );
	switch ( $icon ) {
		case "yes":
			$html	 = '<img src="' . admin_url() . 'images/yes.png" width="16" height="16" alt="Yes">';
			break;
		case "no":
			$html	 = '<img src="' . admin_url() . 'images/no.png" width="16" height="16" alt="No">';
			break;
		case "maybe":
			$html	 = '<img src="' . includes_url() . 'images/smilies/icon_question.gif" width="16" height="16" alt="Maybe">';
			break;
	}
	return $html;
}

/**
 * Add Eve Online links to the WP Toolbar.
 *
 * @param type $wp_admin_bar
 */
function evecorp_toolbar_links( $wp_admin_bar )
{
	$eve = array(
		'id'		 => 'eve',
		'title'		 => 'Eve Universe',
	);
	$eve_gate	 = array(
		'id'		 => 'eve_gate',
		'title'		 => 'Eve Gate',
		'href'		 => 'https://gate.eveonline.com/Home',
		'parent'	 => 'eve'
	);
	$eve_forums	 = array(
		'id'			 => 'eve_forums',
		'title'			 => 'Eve Forums',
		'href'			 => 'https://forums.eveonline.com/',
		'parent'		 => 'eve'
	);
	$eve_community	 = array(
		'id'		 => 'eve_community',
		'title'		 => 'Eve Community',
		'href'		 => 'http://community.eveonline.com/',
		'parent'	 => 'eve'
	);
	$evelopedia	 = array(
		'id'		 => 'evelopedia',
		'title'		 => 'Evelopedia',
		'href'		 => 'http://wiki.eveonline.com/',
		'parent'	 => 'eve'
	);
	$petitions	 = array(
		'id'		 => 'petitions',
		'title'		 => 'Petitions',
		'href'		 => 'https://support.eveonline.com/Pages/Petitions/MyPetitions.aspx',
		'parent'	 => 'eve'
	);
	$API_keys	 = array(
		'id'	 => 'API_keys',
		'title'	 => 'API Keys',
		'href'	 => 'https://support.eveonline.com/api',
		'parent' => 'eve'
	);
	$wp_admin_bar->add_node( $eve );
	$wp_admin_bar->add_node( $eve_gate );
	$wp_admin_bar->add_node( $eve_forums );
	$wp_admin_bar->add_node( $eve_community );
	$wp_admin_bar->add_node( $evelopedia );
	$wp_admin_bar->add_node( $petitions );
	$wp_admin_bar->add_node( $API_keys );
}

/**
 * We need to add/override get_current_screen() outside admin-pages for the
 * WP_TableClass to work correctly, as its original design is for admin-pages
 * only.
 *
 */
if ( !is_admin() ) {
	if ( !function_exists( 'get_current_screen' ) ) {

		function get_current_screen()
		{
			return null;
		}

	}
}

/**
 * Add our auto-generated members-list and member-pages to the parseable
 * WP query vars array.
 *
 * @param array $query_vars
 * @return array
 */
function evecorp_query_vars( $query_vars )
{
	$query_vars[]	 = 'members_list';
	$query_vars[]	 = 'member';
	return $query_vars;
}

/**
 * Check if current request is for our auto-generated members-list or a
 * member-page. Process request with corresponding template and bail out of
 * further request processing afterwards if that is the case.
 *
 * @param class $wp The WP request object.
 */
function evecorp_parse_request( &$wp )
{
	/* Do we have a request for a individual members page? */
	if ( array_key_exists( 'member', $wp->query_vars ) ) {
		$template		 = 'member.php';

		/* Look for the template in the active theme. */
		$template_file	 = locate_template( $template );
		if ( !$template_file ) {
			$template_file = EVECORP_PLUGIN_DIR . $template;
		}
		require_once $template_file;
		exit;
	}

	/* Do we have a request for the list of all members? */
	if ( array_key_exists( 'members_list', $wp->query_vars ) ) {
		$template = 'members-list.php';

		/* Look for the template in the active theme. */
		$template_file = locate_template( $template );
		if ( !$template_file ) {
			$template_file = EVECORP_PLUGIN_DIR . $template;
		}
		require_once $template_file;
		exit;
	}
}

/**
 * Add classes to the HTML body of our page.
 *
 * @param array $classes
 * @return array
 */
function evecorp_singular_body( $classes )
{
//	print '<pre>';
//	var_dump( $classes );
//	print '</pre>';
//	die;
//	$classes[]	 = 'singular';
//	$classes[]	 = 'one-column';
	return $classes;
}

/**
 * Determines the difference between two timestamps.
 *
 * The difference is returned in a human readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return string Human readable time difference.
 */
function evecorp_human_time_diff( $from, $to = '' )
{
	/* Units */
	$one_second	 = 1; // 1
	$one_minute	 = 60 * $one_second; // 60
	$one_hour	 = 60 * $one_minute; // 3600
	$one_day	 = 24 * $one_hour;   //
	$one_week	 = 7 * $one_day;
	$one_month	 = 30 * $one_day;
	$one_year	 = 365 * $one_day;

	if ( empty( $to ) )
		$to		 = time();
	$diff	 = (int) abs( $to - $from );

	if ( $one_second <= $diff && $one_minute > $diff ) {
		$seconds = round( $diff / $one_second );
		if ( $seconds <= 1 )
			$seconds = 1;
		$since	 = sprintf( _n( '%s second', '%s seconds', $seconds ), $seconds );
	} else if ( $one_minute <= $diff && $one_hour > $diff ) {
		$minutes = round( $diff / $one_minute );
		if ( $minutes <= 1 )
			$minutes = 1;
		$since	 = sprintf( _n( '%s minute', '%s minutes', $minutes ), $minutes );
	} else if ( $one_hour <= $diff && $one_day > $diff ) {
		$hours	 = round( $diff / $one_hour );
		if ( $hours <= 1 )
			$hours	 = 1;
		$since	 = sprintf( _n( '%s hour', '%s hours', $hours ), $hours );
	} else if ( $one_day <= $diff && $one_week > $diff ) {
		$days	 = round( $diff / $one_day );
		if ( $days <= 1 )
			$days	 = 1;
		$since	 = sprintf( _n( '%s day', '%s days', $days ), $days );
	} else if ( $one_week <= $diff && $one_month > $diff ) {
		$weeks	 = round( $diff / $one_week );
		if ( $weeks <= 1 )
			$weeks	 = 1;
		$since	 = sprintf( _n( '%s week', '%s weeks', $weeks ), $weeks );
	} else if ( $one_month <= $diff && $one_year > $diff ) {
		$months	 = round( $diff / $one_month );
		if ( $months <= 1 )
			$months	 = 1;
		$since	 = sprintf( _n( '%s month', '%s months', $months ), $months );
	} else if ( $one_year <= $diff ) {
		$years	 = round( $diff / $one_year );
		if ( $years <= 1 )
			$years	 = 1;
		$since	 = sprintf( _n( '%s year', '%s years', $years ), $years );
	}

	return $since;
}