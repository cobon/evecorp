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
add_action('init','evecorp_init_options');

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
				'name' => '',
				'corp' => '',
				'id' => '',
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
		$id = evecorp_get_id( $name );
		$html = '<a onmouseover="this.style.cursor = \'pointer\'"
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
				'name' => '',
				'id' => '',
					), $atts ) );

	if ( evecorp_is_eve() ) {

		// Access from Eve IGB
		// Lookup character ID
		$id = evecorp_get_id( $name );
		$html = '<a onmouseover="this.style.cursor = \'pointer\'"
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
				'name' => '',
				'id' => '',
					), $atts ) );

	if ( evecorp_is_eve() ) {

		// Access from Eve IGB
		// Lookup corporation ID
		$id = evecorp_get_id( $name );
		$html = '<a onmouseover="this.style.cursor = \'pointer\'"
			onclick="CCPEVE.showInfo(2, ' . $id . ')"
			title="Show Corporation Info">' . $name . '</a>';
	} else {

		// Access from external browser
		$html = '<a href="' . evecorp_get_option( 'corp_url' ) . $name . '"
			title="Show ' . $name . ' on ' . evecorp_get_option( 'corp_url_label' ) . '">' . $name . '</a>';
	}
	return $html;
}