<?php

/*
  Plugin Name: Eve Online Plugin for WordPress
  Plugin URI: http://fisr.dnsd.info/
  Description: Secure and easy websites for Eve Online Player Corporations.
  Version: 0.1
  Author: Mitome Cobon-Han
  Author URI: https://gate.eveonline.com/Profile/Mitome%20Cobon-Han
  License: GPLv2 or later

 * Copyright (c) 2012 Mitome Cobon-Han (mitome.ch@gmail.com)
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
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2 or later
 * @version 0.1
 */
/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/* Who am I? */
define( 'EVECORP', 'Eve Online Player Corporation Plugin for WordPress' );
define( 'EVECORP_VERSION', 0.1 );
define( 'EVECORP_MIN_WP_VERSION', '3.3' );
define( 'EVECORP_MIN_PHP_VERSION', '5.2.4' );

/* Load common libraries */
require_once dirname( __FILE__ ) . "/functions.php";
require_once dirname( __FILE__ ) . "/api-functions.php";

/**
 * Global enqueues, actions, and filters.
 *
 */
/* Initialize plugin options */
add_action( 'init', 'evecorp_init_options' );

/* Use Eve Online portraits instead of Gravatar */
add_filter( 'get_avatar', 'evecorp_get_avatar', 10, 3 );

/* Allow empty mail address for Eve Online users */
add_filter( 'get_the_author_user_email', 'evecorp_author_mail', 10, 2 );

/* Add some Eve Online related links to the admin-bar */
add_action( 'admin_bar_menu', 'evecorp_toolbar_links', 999 );

if ( is_admin() ) {
	/**
	 * Admin enqueues, actions, and filters.
	 *
	 */
	register_activation_hook( __FILE__, 'evecorp_activate' );
	register_deactivation_hook( __FILE__, 'evecorp_deactivate' );

	/* Load admin libraries */
	require_once dirname( __FILE__ ) . '/admin-functions.php';
	require_once dirname( __FILE__ ) . "/evecorp-settings.php";

	/* Load our online help text contents */
	include_once dirname( __FILE__ ) . '/userprofile-help.php';

	/* Add a menu entry to administration menu */
	add_action( 'admin_menu', 'evecorp_add_settings_menu' );

	/* Define options page sections and allowed options for admin pages */
	add_action( 'admin_init', 'evecorp_admin_init' );

	/* Notify administrator if corporate API key is missing or invalid. */
	if ( !evecorp_corpkey_check() )
		add_action( 'admin_notices', 'evecorp_config_notifiy' );

	/* Don't show password fields in user settings for Eve Online characters. */
	add_filter( 'show_password_fields', 'evecorp_show_password_fields', 10, 2 );

	/* Allow user profile updates without e-mail address */
	add_action( 'user_profile_update_errors', 'evecorp_eveuser_mail', 10, 3 );

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
} else {
	/**
	 * Non-admin enqueues, actions, and filters
	 *
	 */
	/* Load login libraries */
	require_once dirname( __FILE__ ) . "/login-functions.php";

	/* Register shortcode handler */
	add_shortcode( 'eve', 'evecorp_shortcode' );

	/* jQuery context menu for Eve Online stuff */
	add_action( 'wp_enqueue_scripts', 'evecorp_menu_scripts' );

	/**
	 * Hook evecorp into the WordPress authentication flow.
	 *
	 * Ensure Site administrators can still login with user name/password,
	 * all others need either a valid WP session cookies or supply Eve Online API
	 * key information.
	 */
//	remove_all_filters( 'authenticate' );
//	add_filter( 'authenticate', 'evecorp_auth_admin', 1, 3 );
	add_filter( 'authenticate', 'evecorp_authenticate_user', 20, 3 );
//	add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );
	add_filter( 'login_form_defaults', 'evecorp_login_form_labels' );
}

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
 * @todo Themes should be able to supply a custom context menu CSS.
 *
 * @param string $name The name of the character to be linked.
 *
 * @return string HTML code to display on page.
 */
function evecorp_char( $name )
{
	$classes = 'evecorp-char';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '" ' . $name . '</a>';
	$html	 = '<a href="https://gate.eveonline.com/Profile/' . $name .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $name ) .
			'" title="Pilot Information">' . $name . '</a>';
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

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $corp_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '" ' . $corp_name . '</a>';
	$html	 = '<a href="https://gate.eveonline.com/Corporation/' . $corp_name .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $corp_name ) .
			'" title="Corporation Information">' . $corp_name . '</a>';
	return $html;
}