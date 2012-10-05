<?php

/*
  Plugin Name: Eve Online Plugin for WordPress
  Plugin URI: http://fisr.dnsd.info/
  Description: Secure and easy websites for Eve Online Player Corporations.
  Version: 0.1
  Author: Mitome Cobon-Han
  Author URI: https://gate.eveonline.com/Profile/Mitome%20Cobon-Han
  License: GPL3

 * Copyright (c) 2012 Mitome Cobon-Han (mitome.ch@gmail.com)
 *
 * This file is part of evecorp.
 *
 * evecorp is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
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
 * EveApiRoles class Copyright (c) 2008, Michael Cummings licensed under the
 * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 License.
 *
 * This program connects to and exchanges data with API servers of Eve Online.
 * by using this program you agree with the website terms of service of Eve
 * Online published under <http://community.eveonline.com/pnp/termsofuse.asp>.
 *
 * Eve Online is copyright (c) 1997-2012, CCP hf, Reykjavík, Iceland
 *
 * EVE Online and the EVE logo are the registered trademarks of CCP hf. All
 * rights are reserved worldwide.
 *
 */

/**
 * @package evecorp
 * @author Mitome Cobon-Han <mitome.ch@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE Version 3
 * @version 0.1
 */
// Who am I?
define( "EVECORP", "Eve Online Player Corporation Plugin for WordPress" );
define( "EVECORP_VERSION", 0.1 );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// Load common functions library
require_once dirname( __FILE__ ) . "/functions.php";

// Initialize plugin options
add_action( 'init', 'evecorp_init_options' );

// admin actions
if ( is_admin() ) {
	require_once dirname( __FILE__ ) . "/evecorp-settings.php";

	register_activation_hook( __FILE__, 'evecorp_activate' );
	register_deactivation_hook( __FILE__, 'evecorp_deactivate' );

	// Add a menu entry to administration menu
	add_action( 'admin_menu', 'evecorp_add_settings_menu' );

	// Define options page sections and allowed options for admin pages
	add_action( 'admin_init', 'evecorp_admin_init' );

	/* 	// Notify administrator if active but unconfigured.
	  $options = get_option( 'evecorp_options' );
	  if ( empty( $options['corpkey_id']) || empty( $options['corpkey_vcode'] ) )
	  add_action( 'admin_notices', 'evecorp_config_notifiy' );
	 */
} else {
	// non-admin enqueues, actions, and filters
}

// Register shortcode handler
add_shortcode( 'eve', 'evecorp_shortcode' );
add_action( 'wp_enqueue_scripts', 'evecorp_menu_scripts' );

function evecorp_menu_scripts()
{
	wp_register_style( 'evecorp-contextMenu', plugin_dir_url( __FILE__ ) . 'js/jquery.contextMenu.css' );
	wp_register_script( 'jquery.ui.position', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.position.js', array( 'jquery' ) );
	wp_register_script( 'jquery.contextMenu', plugin_dir_url( __FILE__ ) . 'js/jquery.contextMenu.js', array( 'jquery', 'jquery.ui.position' ) );
	wp_register_script( 'evecorp.contextMenu', plugin_dir_url( __FILE__ ) . 'js/evecorp.contextMenu.js', array( 'jquery.contextMenu' ) );
	wp_enqueue_script( 'evecorp.contextMenu' );
	wp_enqueue_style( 'evecorp-contextMenu' );
}

/**
 * Eve Online Shortcode handler
 *
 * Converts a Eve Online identifier into links either for Eve Online clients or
 * normal browsers.
 *
 * Usage examples: <br>
 * 	[eve name="Mitome Cobon-Han"] <br>
 * 	[eve corp="Federation Interstellar Resources"] <br>
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
		'name'	 => '',
		'corp'	 => '',
			), $shortcode );

	foreach ( $sc as $key => $value ) {
		if ( '' <> $value ) {
			switch ( $key ) {
				case 'name':
					$html	 = evecorp_char( $value );
					break;
				case 'corp':
					$html	 = evecorp_corp( $value );
					break;
				default:
					break;
			}
		}
	}
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online character name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $name The name of the character to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_char( $name )
{
	$classes = 'evecorp-char';

	// Access from Eve Online in-game browser?
	if ( evecorp_is_eve() ) {

		$classes .= ' igb';

		// Are in the browsers list of trusted sites?
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id = evecorp_get_id( $name );
	$html = '<a class="'. $classes .'" id="'.$id.'" name="'.$name.'" title="Click for menu">'.$name.'</a>';
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
	$classes = 'evecorp-corp';

	// Access from Eve Online in-game browser?
	if ( evecorp_is_eve() ) {

		$classes .= ' igb';

		// Are in the browsers list of trusted sites?
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id = evecorp_get_id( $corp_name );
	$html = '<a class="'. $classes .'" id="'.$id.'" name="'.$corp_name.'" title="Click for menu">'.$corp_name.'</a>';
	return $html;
}

/**
 * Hook evecorp into the WordPress authentication flow.
 *
 * Ensure Site administrators can still login with user name/password,
 * all others need either a valid WP session cookies or supply Eve Online API
 * key information.
 */
//remove_all_filters( 'authenticate' );
//add_filter( 'authenticate', 'evecorp_auth_admin', 1, 3 );
add_filter( 'authenticate', 'evecorp_authenticate_user', 20, 3 );
//add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );

/**
 * Tests if the supplied credentials could belong to the WP admin account.
 * @param type $user
 * @param type $login
 * @param type $password
 */
function evecorp_auth_admin( $user, $login, $password )
{
	if ( is_wp_error( $user ) )
		return $user;

	if ( !empty( $login ) && !empty( $password ) ) {
		$super_admins = get_super_admins();

		foreach ( $super_admins as $super_admin ) {
			if ( strtolower( $login ) == strtolower( $super_admin ) ) {
				add_filter( 'authenticate', 'wp_authenticate_username_password', 10, 3 );
			}
		}
	}
}

/**
 * Authenticate the user using Eve Online API.
 *
 * If this is the first time we've seen this user (based on the character name),
 * a new account will be created.
 *
 * Known users will have their profile data updated based on the Eve Online
 * data present.
 *
 * @return WP_User|WP_Error authenticated user or error if unable to authenticate
 */
function evecorp_authenticate_user( $user, $key_ID, $vcode )
{
	// If a previous called authentication was valid, just pass it along.
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	// If the form has not been submitted yet.
	if ( !isset( $_POST['wp-submit'] ) )
		return $user;

	// Do we have a user submitted API key?
	if ( empty( $key_ID ) || empty( $vcode ) ) {
		$error = new WP_Error();

		if ( empty( $key_ID ) )
			$error->add( 'empty_key_ID', __( '<strong>ERROR</strong>: You have to supply a API key ID.' ) );

		if ( empty( $vcode ) )
			$error->add( 'empty_vcode', __( '<strong>ERROR</strong>: You have to supply the verification code for your API key.' ) );

		return $error;
	}

	// Test the submitted credentials
	$key = array(
		'key_ID' => $key_ID,
		'vcode'	 => $vcode
	);
	$keyinfo = evecorp_get_keyinfo( $key );

	/**
	 * Failed to fetch keyinfo
	 *
	 * @todo Add handling for different kind of failures (e.g connection problems).
	 */
	if ( is_wp_error( $keyinfo ) )
		return $keyinfo;

	// Is the key type for characters (and not a corp key or account key)?
	if ( 'Character' <> $keyinfo['type'] )
		return new WP_Error( 'not_char_key', '<strong>ERROR</strong>: This API key is not a character key.' );

	// Get a sanitized user login name from API key's character name
	$user_login = sanitize_user( $keyinfo['characters'][0]['characterName'] );

	// Is the character a member of our corporation?
	if ( evecorp_get_option( 'corpkey_corporation_name' ) <> $keyinfo['characters'][0]['corporationName'] )
		return new WP_Error( 'not_corp_member', '<strong>ERROR</strong>: ' . $user_login . ' is not a member of ' . evecorp_get_option( 'corpkey_corporation_name' ) . '.' );

	// Lookup account in WP users table
	$user = get_user_by( 'login', $user_login );

	// Create account if this is a new user
	if ( false === $user )
		$user = evecorp_create_new_user( $user_login );

	// Update existing account
	if ( is_a( $user, 'WP_User' ) )
		evecorp_update_user( $user, $keyinfo );

	return $user;
}

/**
 * Create a new WordPress user account based on the Eve Online character.
 *
 * @param string $user_login login name for the new user
 * @return object WP_User object for newly created user
 */
function evecorp_create_new_user( $user_login )
{
	if ( empty( $user_login ) )
		return null;

	// Create account
	$user_id = wp_insert_user( array( 'user_login' => $user_login, 'user_pass'	 => '' ) );

	// Get the new user from WP users db.
	$user = new WP_User( $user_id );
	return $user;
}

/**
 * Update the user data for the specified user based on the Eve Online character.
 *
 * @param WP_User $user
 * @param array $keyinfo
 */
function evecorp_update_user( $user, $keyinfo )
{
	$full_name	 = $keyinfo['characters'][0]['characterName'];
	$split_name	 = evecorp_split_name( $full_name );

	$userdata = array(
		'ID'			 => $user->ID,
		'user_nicename'	 => $full_name,
		'first_name'	 => $split_name['first_name'],
		'last_name'		 => $split_name['last_name'],
		'display_name'	 => $full_name,
	);

	wp_update_user( $userdata );
}

/**
 * Split a full name in first [middle] and last names.
 *
 * @param string $full_name
 * @param string $prefix
 * @return array
 */
function evecorp_split_name( $full_name, $prefix = '' )
{
	$pos = strrpos( $full_name, ' ' );

	if ( $pos === false ) {
		return array(
			$prefix . 'first_name'	 => $full_name,
			$prefix . 'last_name'	 => ''
		);
	}

	$first_name	 = substr( $full_name, 0, $pos + 1 );
	$last_name	 = substr( $full_name, $pos );

	return array(
		$prefix . 'first_name'	 => $first_name,
		$prefix . 'last_name'	 => $last_name
	);
}