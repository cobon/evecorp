<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Admin functions library
 *
 * @package evecorp
 */

/**
 * Plugin activation function.
 * Adds the options settings used by this plugin.
 *
 * @todo Test if we can connect with Eve Online API servers.
 * @todo Check if required/recommendet plugins are installed.
 */
function evecorp_activate()
{
	global $evecorp_options;
	evecorp_init_options();
	add_option( 'evecorp_options', $evecorp_options );
}