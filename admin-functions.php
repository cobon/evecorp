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
 * @todo Test if we can use filesystem for saving cached API query results and
 *  create the directory for cache files.
 * @todo Security - Make sure cached Eve Online XML files can't be accessed directly by browser
 * @todo Check if required/recommendet plugins are installed.
 * @todo Add ovverides for some config settings via constants defined in wp-config.php
 */
function evecorp_activate()
{
	global $evecorp_options;
	evecorp_init_options();
	add_option( 'evecorp_options', $evecorp_options );
}

/**
 * Initialize Filesystem object
 *
 * @param str $form_url - URL of the page to display request form
 * @param str $method - connection method
 * @param str $context - destination folder
 * @param array $fields - fileds of $_POST array that should be preserved between screens
 * @return bool/str - false on failure, stored text on success
 * */
function filesystem_init( $form_url, $method = false, $context = WP_CONTENT_DIR, $fields = null )
{
//	global $wp_filesystem;

	if ( false === ( $fs_credentials = request_filesystem_credentials( $form_url, $method, $context, false, $fields ) ) ) {

		/**
		 * if we comes here - we don't have credentials
		 * so the request for them is displaying
		 * no need for further processing
		 * */
		return false;
	}

	// Now we got some credentials - try to use them
	if ( ! WP_Filesystem( $fs_credentials ) ) {

		// Incorrect connection data - ask for credentials again, now with error message
		request_filesystem_credentials( $form_url, $method, $context, true, $context, $fields );
		return false;
	}

	// Filesystem object has been successfully initiated
	return true;
}

/**
 * Create API cache directory for Pheal
 * Creates a subdirectory eve_api in the WP_CONTENTS/cache folder.
 *
 * Uses WordPress file system API
 *
 * @param $dirname. Directory name for the cache
 * @return mixed. WP_Error on failure, True on success
 */
function evecorp_init_cache( $form_url, $dirname = 'eve_api' )
{
	global $wp_filesystem;

	/**
	 * @param str $form_url - URL of the page to display request form
	 * @param str $context - destination folder
	 * @param array $fields - fileds of $_POST array that should be preserved between screens
	 * @return bool/str - false on failure, stored text on success
	 */
	filesystem_init( $form_url );
	if ( !is_object( $wp_filesystem ) )
		return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.' ) );

	if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() )
		return new WP_Error( 'fs_error', __( 'Filesystem error.' ), $wp_filesystem->errors );

	//Get the base wp_content folder
	$content_dir = $wp_filesystem->wp_content_dir();
	if ( empty( $content_dir ) )
		return new WP_Error( 'fs_no_content_dir', __( 'Unable to locate WordPress contents directory.' ) );

	$cache_dir = trailingslashit( $content_dir ) . 'cache/' . $dirname;
	$errors = array( );
	$created = $wp_filesystem->mkdir( $cache_dir );
	if ( !$created )
		$errors[] = $cache_dir;

	if ( !empty( $errors ) )
		return new WP_Error( 'could_not_create_cachedir', sprintf( __( 'Could not create the cache directory %s.' ), implode( ', ', $errors ) ) );
	return true;
}

/**
 * Remove directories and files of the Eve Online API cache.
 * Expects a subdirectory eve_api in the WP_CONTENTS/cache/ folder.
 *
 * Uses WordPress file system API
 *
 * @param array $dirname Name of the cache directory
 * @return mixed. WP_Error on failure, True on success
 */
function evecorp_clear_cache( $form_url, $dirname = 'eve_api' )
{
	global $wp_filesystem;
	filesystem_init( $form_url );
	if ( !is_object( $wp_filesystem ) )
		return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.' ) );

	if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() )
		return new WP_Error( 'fs_error', __( 'Filesystem error.' ), $wp_filesystem->errors );

	//Get the base wp_content folder
	$cache_dir = $wp_filesystem->wp_content_dir();
	if ( empty( $content_dir ) )
		return new WP_Error( 'fs_no_content_dir', __( 'Unable to locate WordPress contents directory.' ) );

	$cache_dir = trailingslashit( $content_dir ) . 'cache/' . $dirname;
	$errors = array( );
	$deleted = $wp_filesystem->rmdir( $cache_dir, true );
	if ( !$deleted )
		$errors[] = $cache_dir;

	if ( !empty( $errors ) )
		return new WP_Error( 'could_not_remove_cachedir', sprintf( __( 'Could not fully remove the cache directory %s.' ), implode( ', ', $errors ) ) );
	return true;
}