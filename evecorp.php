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
/* Who am I? */
define( "EVECORP", "Eve Online Player Corporation Plugin for WordPress" );
define( "EVECORP_VERSION", 0.1 );

/* Make sure we don't expose anything if called directly */
if ( !function_exists( 'add_action' ) ) {
	die(); /* Silence is golden. */
}

/* Load common functions library */
require_once dirname( __FILE__ ) . "/functions.php";
//require_once dirname( __FILE__ ) . "/login-functions.php";

/* Initialize plugin options */
add_action( 'init', 'evecorp_init_options' );

/* admin actions */
if ( is_admin() ) {
	require_once dirname( __FILE__ ) . "/evecorp-settings.php";

	register_activation_hook( __FILE__, 'evecorp_activate' );
	register_deactivation_hook( __FILE__, 'evecorp_deactivate' );

	/* Add a menu entry to administration menu */
	add_action( 'admin_menu', 'evecorp_add_settings_menu' );

	/* Define options page sections and allowed options for admin pages */
	add_action( 'admin_init', 'evecorp_admin_init' );

	/* Notify administrator if active but unconfigured. */
	$evecorp_corpkey_ID		 = evecorp_get_option( 'corpkey_ID' );
	$evecorp_corpkey_vcode	 = evecorp_get_option( 'corpkey_vcode' );
	if ( empty( $evecorp_corpkey_ID ) || empty( $evecorp_corpkey_vcode ) )
		add_action( 'admin_notices', 'evecorp_config_notifiy' );

	/* Allow user profile updates without e-mail address */
	add_action( 'user_profile_update_errors', 'evecorp_eveuser_mail', 10, 3 );
} else {
	/* non-admin enqueues, actions, and filters */

	/* login functions, enqueues, actions, and filters */
	require_once dirname( __FILE__ ) . "/login-functions.php";
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

/* Eve Online avatar filter */
add_filter( 'get_avatar', 'evecorp_get_avatar', 10, 3 );
add_filter( 'get_the_author_user_email', 'evecorp_author_mail', 10, 2 );

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

