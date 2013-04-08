<?php
/*
  Plugin Name: Eve Online Player Corporation
  Plugin URI: http://fisr.dnsd.info/
  Description: Secure and easy websites for Eve Online Player Corporations.
  Version: 0.1
  Author: Mitome Cobon-Han
  Author URI: https://gate.eveonline.com/Profile/Mitome%20Cobon-Han
  License: GPLv2 or later

 * Copyright (c) 2012, 2013 Mitome Cobon-Han (mitome.ch@gmail.com)
 *
 * This file is part of evecorp.
 *
 * evecorp is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 2 of the License, or (at your option) any later
 * version. evecorp is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with evecorp. If not, see <http://www.gnu.org/licenses/>.
 *
 * This program incorporates work covered by the following copyright and
 * permission notices:
 *
 * Pheal class library Copyright (c) 2010-2012, Peter Petermann, Daniel Hoffend
 * licensed under the MIT License.
 *
 * jQuery contextMenu by Rodney Rehm, Addy Osmani licensed under MIT License and
 * GPL v3
 *
 * This program connects to and exchanges data with API servers of Eve Online.
 * by using this program you agree with the website terms of service of Eve
 * Online published under <http://community.eveonline.com/pnp/termsofuse.asp>.
 *
 * Eve Online is copyright (c) 1997-2013, CCP hf, Reykjavík, Iceland
 *
 * EVE Online and the EVE logo are the registered trademarks of CCP hf. All
 * rights are reserved worldwide.
 *
 */

/**
 * @package evecorp
 * @author Mitome Cobon-Han <mitome.ch@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2 or later
 * @version 0.1
 */
/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/* Who am I? */
define( 'EVECORP', 'Eve Online Player Corporation Plugin for WordPress' );
define( 'EVECORP_VERSION', '0.1alpha' );
define( 'EVECORP_MIN_WP_VERSION', '3.3' );
define( 'EVECORP_MIN_PHP_VERSION', '5.2.4' );
define( 'EVECORP_PLUGIN_FILE', __FILE__ );
define( 'EVECORP_PLUGIN_DIR', plugin_dir_path( EVECORP_PLUGIN_FILE ) );
define( 'EVECORP_PLUGIN_URL', plugin_dir_url( EVECORP_PLUGIN_FILE ) );

/**
 * Global includes, actions and filters.
 *
 */
/* Load global libraries */
require_once EVECORP_PLUGIN_DIR . "/functions.php";
require_once EVECORP_PLUGIN_DIR . "/api-functions.php";

/* Initialize plugin options */
add_action( 'init', 'evecorp_init_options' );

/* Use Eve Online portraits instead of Gravatar */
add_filter( 'get_avatar', 'evecorp_get_avatar', 10, 3 );

/* Allow empty mail address for Eve Online users */
add_filter( 'get_the_author_user_email', 'evecorp_author_mail', 10, 2 );

/* Use GMT offset from user options instead of site options */
add_filter( 'option_gmt_offset', 'evecorp_gmt_offset' );

/* Use timezone string from user options instead of site options */
add_filter( 'option_timezone_string', 'evecorp_timezone_string' );

/* Add some Eve Online related links to the admin-bar */
add_action( 'admin_bar_menu', 'evecorp_toolbar_links', 999 );

if ( is_admin() ) {
	/**
	 * Admin includes, hooks, actions and fliters.
	 *
	 */
	/* Load admin libraries */
	require_once EVECORP_PLUGIN_DIR . '/admin-functions.php';
	require_once EVECORP_PLUGIN_DIR . "/evecorp-settings.php";

	/* Load online help text */
	include_once EVECORP_PLUGIN_DIR . '/userprofile-help.php';

	/* Admin hooks */
	register_activation_hook( EVECORP_PLUGIN_FILE, 'evecorp_activate' );
	register_deactivation_hook( EVECORP_PLUGIN_FILE, 'evecorp_deactivate' );

	/* Add a menu entry to administration menu */
	add_action( 'admin_menu', 'evecorp_add_settings_menu' );

	/* Define options page sections and allowed options for admin pages */
	add_action( 'admin_init', 'evecorp_admin_init' );

	/* Allow user profile updates without e-mail address */
	add_action( 'user_profile_update_errors', 'evecorp_eveuser_mail', 10, 3 );

	/* Outputs a user timezone setting on user-edit page. */
	add_action( 'edit_user_profile', 'evecorp_user_TZ_form' );

	/* Outputs a user timezone seetting on userprofile page. */
	add_action( 'show_user_profile', 'evecorp_user_TZ_form' );

	/* Save the timezone setting to the current users meta data. */
	add_action( 'personal_options_update', 'evecorp_set_user_TZ' );

	/* Save the timezone setting to the a users meta data. */
	add_action( 'edit_user_profile_update', 'evecorp_set_user_TZ' );

	/* Outputs a section and table-listing with API keys on user-edit page. */
	add_action( 'edit_user_profile', 'evecorp_userkeys' );

	/* Outputs a section and table-listing with API keys on userprofile page. */
	add_action( 'show_user_profile', 'evecorp_userkeys' );

	/* Output HTML form for adding API key ID and vcode. */
	add_action( 'show_user_profile', 'evecorp_userkeys_form' );

	/* Add a Eve Online API key ID to a current users meta data. */
	add_action( 'personal_options_update', 'evecorp_altkey_add' );

	/* Add contextual help to the userprofile page */
	add_action( 'load-profile.php', 'evecorp_userprofile_help' );

	/* Add contextual help to the user-edit page */
	add_action( 'load-user-edit.php', 'evecorp_userprofile_help' );

	/* Add rewrite rules for our auto-generated Eve Online pages (members, et al) */
	add_action( 'generate_rewrite_rules', 'evecorp_add_rewrite_rules' );

	/* Add buttons to the tinyMCE editor for Eve Online shortcodes */
	add_action( 'init', 'evecorp_init_mce' );

	/* Notify administrator if corporate API key is missing or invalid. */
	if ( !evecorp_corpkey_check() )
		add_action( 'admin_notices', 'evecorp_config_notifiy' );

	/* Don't show password fields in user settings for Eve Online characters. */
	add_filter( 'show_password_fields', 'evecorp_show_password_fields', 10, 2 );
} else {
	/**
	 * Non-admin includes, actions and filters.
	 *
	 */
	/* Load login libraries */
	require_once EVECORP_PLUGIN_DIR . "/login-functions.php";

	/* jQuery context menu for Eve Online stuff */
	//add_action( 'wp_enqueue_scripts', 'evecorp_menu_scripts' );

	/* Eve Online page processing (members, et al) */
	add_action( 'parse_request', 'evecorp_parse_request' );

	/* Add query variables for our auto-generated Eve Online pages (members, et al) */
	add_filter( 'query_vars', 'evecorp_query_vars' );

	/* Register shortcode handler */
	add_shortcode( 'eve', 'evecorp_shortcode' );

	/**
	 * Hook us into the WordPress authentication flow.
	 *
	 * Ensure Site administrators can still login with user name/password,
	 * all others need either valid WP session cookies or supply Eve Online API
	 * key information.
	 */
	remove_all_filters( 'authenticate' );
	add_filter( 'authenticate', 'evecorp_auth_admin', 1, 3 );
	add_filter( 'authenticate', 'evecorp_authenticate_user', 20, 3 );
	add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );
	add_filter( 'login_form_defaults', 'evecorp_login_form_labels' );
}

/**
 * jQuery context menu for Eve Online stuff
 *
 */
function evecorp_menu_scripts()
{
	wp_register_style( 'evecorp-contextMenu', EVECORP_PLUGIN_URL . 'js/jquery.contextMenu.css' );
	wp_register_script( 'jquery.contextMenu', EVECORP_PLUGIN_URL . 'js/jquery.contextMenu.js', array( 'jquery', 'jquery-ui-position' ) );
	wp_register_script( 'evecorp.contextMenu', EVECORP_PLUGIN_URL . 'js/evecorp.contextMenu.js', array( 'jquery.contextMenu', 'jquery-ui-tooltip', 'jquery-effects-core' ) );
//	wp_register_script( 'evecorp.hover', EVECORP_PLUGIN_URL . 'js/evecorp.hover.js', array( 'jquery-ui' ) );
	wp_enqueue_script( 'evecorp.contextMenu' );
//	wp_enqueue_script( 'evecorp.hover' );
	wp_enqueue_style( 'evecorp-contextMenu' );
	wp_enqueue_style( 'jquery-ui-dialog' );
}

/**
 * Eve Online Shortcode handler
 *
 * Converts a Eve Online identifier into links either for Eve Online clients or
 * normal browsers.
 *
 * Usage examples: <br>
 * 	[eve char="Mitome Cobon-Han"] <br>
 * 	[eve corp="Federation Interstellar Resources"] <br>
 *  [eve alliance="Legionum Tenebrae"] <br>
 * 	[eve system="Misneden"] <br>
 * 	[eve item="Tritanium"] <br>
 *  [eve id=123456789]
 *
 * @param type $shortcode
 * @return string html code for output.
 */
function evecorp_shortcode( $shortcode )
{
	$sc = shortcode_atts( array(
		'char'			 => '',
		'corp'			 => '',
		'alliance'		 => '',
		'station'		 => '',
		'solarsystem'	 => '',
		'constellation'	 => '',
		'region'		 => '',
			), $shortcode );

	foreach ( $sc as $key => $value ) {
		if ( '' <> $value ) {
			switch ( $key ) {
				case 'char':
					$html	 = evecorp_char( $value );
					break;
				case 'corp':
					$html	 = evecorp_corp( $value );
					break;
				case 'alliance':
					$html	 = evecorp_alliance( $value );
					break;
				case 'station':
					$html	 = evecorp_station( $value );
					break;
				case 'solarsystem':
					$html	 = evecorp_solarsystem( $value );
					break;
				case 'constellation':
					$html	 = evecorp_constellation( $value );
					break;
				case 'region':
					$html	 = evecorp_region( $value );
					break;
				default:
					break;
			}
		}
	}
	if ( !isset( $html ) )
		$html = '[shortcode error]';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online character name
 * inlcuding CSS selectors for the jQuery context menu.
 * @todo Themes should be able to supply a custom context menu CSS.
 *
 * @param string $name The name of the character to be linked.
 *
 * @return string HTML code to display on page.
 */
function evecorp_char( $char_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-char';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Get the ID for this character */
	$character_ID = evecorp_get_id( $char_name );
	if ( is_wp_error( $character_ID ) )
		return '<a title="' . $character_ID->get_error_message() . '">' . $char_name . '</a>';

	/* Check if its a player character and not a NPC */
	$type_of_ID = evecorp_get_ID_type( $character_ID );
	if ( 'Character' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $char_name . '</a>';

	/* Get the public profile for this character */
	$character_info = evecorp_get_char_info( $character_ID );
	if ( is_wp_error( $character_info ) )
		return '<a title="' . $character_info->get_error_message() . '">' . $char_name . '</a>';

	/* Get the date of birth */
	$jobs		 = sizeof( $character_info['employmentHistory'] );
	$birthdate	 = $character_info['employmentHistory'][$jobs - 1]['startDate'];

	/* Calculate the age */
	$age = evecorp_human_time_diff( time(), strtotime( $birthdate ) );

	/* Get kills and losses for this character */
	$killz = evecorp_killz_stats( 'character', $character_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $char_name . '</a>';

	/* Render HTML */
	$html = '<a href="https://gate.eveonline.com/Profile/' . $char_name . '"' .
			' class="' . esc_attr( $classes ) . '"' .
			' id="' . esc_attr( $character_ID ) . '"' .
			' name="' . esc_attr( $char_name ) . '"' .
			' security-status="' . esc_attr( number_format( $character_info['securityStatus'], 2 ) ) . '"' .
			' age="' . esc_attr( $age ) . '"' .
			' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"' .
			' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"' .
			' title="Capsuleer"' .
			'>' .
			$char_name .
			'</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online corporation name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $corp_name The name of the corporation to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_corp( $corp_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-corp';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Lookup the ID */
	$corp_ID = evecorp_get_id( $corp_name );
	if ( is_wp_error( $corp_ID ) )
		return '<a title="' . $corp_ID->get_error_message() . '">' . $corp_name . '</a>';

	/* Check if its a player corporation and not a NPC corporation */
	$type_of_ID = evecorp_get_ID_type( $corp_ID );
	if ( 'Corporation' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $corp_name . '</a>';

	/* Get the public profile for this corporation */
	$corp_sheet = evecorp_get_corpsheet( $corp_ID );
	if ( is_wp_error( $corp_sheet ) )
		return '<a title="' . $corp_sheet->get_error_message() . '">' . $corp_name . '</a>';

	/* Get kills and losses for this corporation */
	$killz = evecorp_killz_stats( 'corporation', $corp_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $corp_name . '</a>';


	$html = '<a href="https://gate.eveonline.com/Corporation/' . $corp_name . '"' .
			' class="' . esc_attr( $classes ) . '"' .
			' id="' . esc_attr( $corp_ID ) . '"' .
			' name="' . esc_attr( $corp_name ) . '"' .
			//allianceName
			' member_count="' . esc_attr( number_format( $corp_sheet['memberCount'] ) ) . '"' .
			' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"' .
			' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"' .
			' title="Corporation Information">' . $corp_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online alliance name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $alliance_name The name of the alliance to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_alliance( $alliance_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-alliance';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Lookup the ID */
	$alliance_ID = evecorp_get_id( $alliance_name );
	if ( is_wp_error( $alliance_ID ) )
		return '<a title="' . $alliance_ID->get_error_message() . '">' . $alliance_name . '</a>';

	/* Check if its really a alliance */
	$type_of_ID = evecorp_get_ID_type( $alliance_ID );
	if ( 'Alliance' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $alliance_name . '</a>';

	/* Get some info on this alliance */
	$alliance_info = evecorp_get_alliance_info( $alliance_ID );
	if ( is_wp_error( $alliance_info ) )
		return '<a title="' . $alliance_info->get_error_message() . '">' . $alliance_name . '</a>';

	//var_dump( $alliance_info );

	/* Calculate the age */
	$age = evecorp_human_time_diff( strtotime( $alliance_info['startDate'], time() ) );

	/* Get kills and losses for this alliance */
	$killz = evecorp_killz_stats( 'alliance', $alliance_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $alliance_name . '</a>';

//	die();

	$html = '<a href="https://gate.eveonline.com/Alliance/' . $alliance_name .
			'" class="' . esc_attr( $classes )  . '"' .
			' id="' . esc_attr( $alliance_ID )  . '"' .
			' name="' . esc_attr( $alliance_name ) . '"' .
			' member_count="' . esc_attr( number_format( $alliance_info['memberCount'] ) ) . '"' .
			' age="' . esc_attr( $age ) . '"' .
			' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"' .
			' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"' .
			' title="Alliance Information">' . $alliance_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online station name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $station_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_station( $station_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-station';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $station_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $station_name . '</a>';
	$html	 = '<a href="http://evemaps.dotlan.net/station/' . $station_name .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $station_name ) .
			'" title="Station Information">' . $station_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online solar system name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $solarsystem_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_solarsystem( $solarsystem_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-solarsystem';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $solarsystem_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $solarsystem_name . '</a>';
	$html	 = '<a href="http://wiki.eveonline.com/en/wiki/' . $solarsystem_name .
			'_%28System%29' .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $solarsystem_name ) .
			'" title="Solar System Information">' . $solarsystem_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online constellation name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $constellation_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_constellation( $constellation_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-constellation';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $constellation_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $constellation_name . '</a>';
	$html	 = '<a href="http://wiki.eveonline.com/en/wiki/Category:' . $constellation_name .
			'_%28Constellation%29' .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $constellation_name ) .
			'" title="Constellation Information">' . $constellation_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online region name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $region_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_region( $region_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-region';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $region_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $region_name . '</a>';
	$html	 = '<a href="http://wiki.eveonline.com/en/wiki/Category:' . $region_name .
			'_%28Region%29' .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $region_name ) .
			'" title="Region Information">' . $region_name . '</a>';
	return $html;
}

/**
 * Output a list of corporation members as HTML table.
 *
 */
function evecorp_the_members()
{
	require_once EVECORP_PLUGIN_DIR . '/classes/class-members-table.php';

	/* Create an instance of the Members_Table class */
	$Members_Table = new evecorp_Members_Table();

	/* Fetch, prepare, sort, and filter our data */
	$Members_Table->prepare_items();

	/* Display */
	?>
	<!-- Begin members list table -->
	<!--<div class="entry-content">-->
	<?php $Members_Table->display() ?>
	<!--</div>-->
	<!-- End members list table -->
	<?php
}

function evecorp_the_member()
{
	global $wp;

	require_once EVECORP_PLUGIN_DIR . '/classes/class-member-profile.php';

	/* Get character name and ID */
	$character_ID = evecorp_get_ID( urldecode( $wp->query_vars['member'] ) );
	if ( is_wp_error( $character_ID ) ) {
		wp_die( var_dump( $character_ID ) );
	}

	/* Create an instance of the Member_Profile class */
	$Member_Profile = new evecorp_Member_Profile();

	/* Fetch and prepare our data */
	$Member_Profile->prepare_profile( $character_ID );

	/* Display */
	?>
	<!-- Begin member profile -->
	<div class="entry-content">
		<?php $Member_Profile->display() ?>
	</div>
	<!-- End member profile -->
	<?php
	/* Is the requested character valid and actual corporation member? */
//	if ( evecorp_is_member( $character_ID ) ) {
//	var_dump( $character_info );
//	} else {

	/* Make it a 404 */
//		global $wp_query;
//		$wp_query->set_404();
//			var_dump($wp_query);
//			var_dump( $wp );
//			die( 'single member' );
//		header( "HTTP/1.0 404 Not Found - Member not found" );
//		require TEMPLATEPATH . '/404.php';
//		exit;
//	}
}