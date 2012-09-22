<?php
/*
  Plugin Name: Eve Online Plugin for WordPress
  Plugin URI: http://fisr.dnsd.info/
  Description: Secure and easy websites for Eve Online Player Corporations.
  Version: 0.1
  Author: Mitome Cobon-Han
  Author URI: https://gate.eveonline.com/Profile/Mitome%20Cobon-Han
  License: GPL3

  Copyright © 2012 Mitome Cobon-Han  (mitome.ch@gmail.com)

    This file is part of evecorp.

    evecorp is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    evecorp is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with evecorp.  If not, see <http://www.gnu.org/licenses/>.

 */

/**
 * @package evecorp
 * @author Mitome Cobon-Han <mitome.ch@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE Version 3
 * @version 0.1
  */


define( "EVECORP", "Eve Corporation Wordpress Plugin" );
define( "EVECORP_VERSION", 0.1 );

require_once dirname( __FILE__ ) . "/functions.php";

// admin actions
if ( is_admin() ) {
	require_once dirname( __FILE__ ) . "/options.php";

	register_activation_hook( __FILE__, 'evecorp_activate' );
	register_deactivation_hook( __FILE__, 'evecorp_deactivate' );

	// Add entry to administration menu
	add_action( 'admin_menu', 'evecorp_options' );

	// Define sections and allowed options for admin pages
	add_action( ' admin_init', 'evecorp_admin_init' );

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
		$html = '<a href="' . get_option( 'wp_evecorp_char_url' ) . $name . '"
			title="Show ' . $name . ' on ' . get_option( 'wp_evecorp_char_url_label' ) . '">' . $name . '</a>';
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
		$html = '<a href="' . get_option( 'wp_evecorp_corp_url' ) . $name . '"
			title="Show ' . $name . ' on ' . get_option( 'wp_evecorp_corp_url_label' ) . '">' . $name . '</a>';
	}
	return $html;
}