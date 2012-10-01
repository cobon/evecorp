<?php

/*
  Plugin Name: Eve Online Plugin for WordPress
  Plugin URI: http://fisr.dnsd.info/
  Description: Secure and easy websites for Eve Online Player Corporations.
  Version: 0.1
  Author: Mitome Cobon-Han
  Author URI: https://gate.eveonline.com/Profile/Mitome%20Cobon-Han
  License: GPL3

 * Copyright (c) 2012 Mitome Cobon-Han  (mitome.ch@gmail.com)
 *
 * This file is part of evecorp.
 *
 *  evecorp is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  evecorp is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * evecorp.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This program incorporates work covered by the following copyright and
 * permission notices:
 *
 * Pheal class library Copyright (c) 2010-2012, Peter Petermann, Daniel Hoffend
 * licensed under the MIT License.
 *
 * EveApiRoles class Copyright (c) 2008, Michael Cummings licensed under the
 * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 License.
 *
 * Eve Online Copyright (c) 1997-2012, CCP hf, Reykjavík, Iceland
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

// Shortcodes
add_shortcode( 'eve-char', 'evecorp_char' );
add_shortcode( 'eve-corp', 'evecorp_corp' );

/**
 * Shortcode handler
 *
 * Converts a Eve Online identifier into links either for Eve Online clients or
 * normal browsers.
 *
 * Usage examples: <br>
 * 	[eve name="Mitome Cobon-Han"] <br>
 * 	[eve corp="Federation Interstellar Resources"] <br>
 *  [eve id=123456789]
 *
 */
function evecorp_eve( $atts )
{
	extract( shortcode_atts( array(
				'name'	 => '',
				'corp'	 => '',
				'id'	 => '',
					), $atts ) );

	switch ( $atts ) {
		case $value:


			break;

		default:
			break;
	}

	if ( evecorp_is_eve() ) {

		// Access from Eve IGB
		// Lookup character ID
		$id		 = evecorp_get_id( $name );
		$html	 = '<a onmouseover="this.style.cursor = \'pointer\'"
			onclick="CCPEVE.showInfo(1377, ' . $id . ')"
			title="Show Character Info">' . $name . '</a>';
	} else {

		// Access from external browser
		$html = '<a href="' . evecorp_get_option( 'char_url' ) . $name . '"
			title="Show ' . $name . ' on ' . evecorp_get_option( 'char_url_label' ) . '">' . $name . '</a>';
	}
	return $html;
}

//[eve-char name="Mitome Cobon-Han"]
function evecorp_char( $atts )
{
	extract( shortcode_atts( array(
				'name'	 => '',
				'id'	 => '',
					), $atts ) );

	if ( evecorp_is_eve() ) {

		// Access from Eve IGB
		// Lookup character ID
		$id		 = evecorp_get_id( $name );
		$html	 = '<a onmouseover="this.style.cursor = \'pointer\'"
			onclick="CCPEVE.showInfo(1377, ' . $id . ')"
			title="Show Character Info">' . $name . '</a>';
	} else {

		// Access from external browser
		$html = '<a href="' . evecorp_get_option( 'char_url' ) . $name . '"
			title="Show ' . $name . ' on ' . evecorp_get_option( 'char_url_label' ) . '">' . $name . '</a>';
	}
	return $html;
}

//[eve-corp name="Federation Interstellar Resources"]
function evecorp_corp( $atts )
{
	extract( shortcode_atts( array(
				'name'	 => '',
				'id'	 => '',
					), $atts ) );

	if ( evecorp_is_eve() ) {

		// Access from Eve IGB
		// Lookup corporation ID
		$id		 = evecorp_get_id( $name );
		$html	 = '<a onmouseover="this.style.cursor = \'pointer\'"
			onclick="CCPEVE.showInfo(2, ' . $id . ')"
			title="Show Corporation Info">' . $name . '</a>';
	} else {

		// Access from external browser
		$html = '<a href="' . evecorp_get_option( 'corp_url' ) . $name . '"
			title="Show ' . $name . ' on ' . evecorp_get_option( 'corp_url_label' ) . '">' . $name . '</a>';
	}
	return $html;
}

/**
 * Hook evecorp into the WordPress authentication flow.
 *
 * Allow Site administrators can still login with user name/password,
 * all others need either a valid WP session cookies or supply Eve Online API
 * key information.
 */
remove_all_filters( 'authenticate' );
add_filter( 'authenticate', 'evecorp_auth_admin', 1, 3 );
add_filter( 'authenticate', 'evecorp_authenticate_user', 20, 3 );
add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );

/**
 * Tests if the supplied credentials could belong to the WP admin account.
 * @param type $user
 * @param type $login
 * @param type $password
 */
function evecorp_auth_admin( $user, $login, $password )
{
	unset( $user );
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