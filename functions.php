<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Common functions library
 *
 * @package evecorp
 */

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

	// Newly introduced options should be added here
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

	// Get options from WP options API, if any. Use defaults if none.
	/* @var $saved_options array */
	$saved_options = get_option( 'evecorp_options', $default_options );

	// If options are there but one or more are missing
	$evecorp_options = array_merge( $default_options, $saved_options );

	// Save the options in WP optins DB
	update_option( 'evecorp_options', $evecorp_options );

	/**
	 * You can override any option with a constant defined in wp-config.php
	 * Will not be saved to options DB
	 *
	 */
	foreach ( $evecorp_options as $key => &$value ) {
		if ( defined( 'EVECORP_' . strtoupper( $key ) ) )
			$value = constant( 'EVECORP_' . strtoupper( $key ) );
	}
	unset( $value ); // break the reference with the last element;
}

/**
 * Returns the configuration option value of $key from WP options API.
 *
 * May be overriden by constants defined in wp-config.php
 *
 * @param string $key The name of the option requested.
 * @return string
 */
function evecorp_get_option( $key )
{
	$evecorp_options		 = get_option( 'evecorp_options' );
	if ( defined( 'EVECORP_' . strtoupper( $key ) ) )
		$evecorp_options[$key]	 = constant( 'EVECORP_' . strtoupper( $key ) );
	return $evecorp_options[$key];
}

/**
 * Tests if the current browser is a Eve Online in-game browser
 *
 * @return boolean true if client is Eve Online in-game browser
 */
function evecorp_is_eve()
{

	global $evecorp_IGB_data;

	if ( !isset( $evecorp_IGB_data ) )
		$evecorp_IGB_data = evecorp_IGB_data();

	return $evecorp_IGB_data['is_igb'];
}

/**
 * Tests if the Eve Online in-game browser is trusting us
 *
 * @uses evecorp_igb_data()
 * @return TRUE|FALSE
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

/**
 * Load and configure Pheal
 *
 * @uses Pheal object class
 * @return Pheal
 */
function load_wp_pheal()
{
	/* Do this only once */
	if ( !class_exists( 'WP_Pheal', FALSE ) ) {

		/* Load the stuff */
		require_once dirname( __FILE__ ) . "/pheal/Pheal.php";
		require_once dirname( __FILE__ ) . "/class-wp-pheal.php";

		/* Register the class loader */
		spl_autoload_register( "Pheal::classload" );

		/* HTTP request method */
		PhealConfig::getInstance()->http_method = 'curl';

		/* Turn on cacheing of requests */
		PhealConfig::getInstance()->cache = new WP_Transients_Pheal();

		/* Enable access detection */
		PhealConfig::getInstance()->access = new PhealCheckAccess();

		/* Identify ourself */
		PhealConfig::getInstance()->http_user_agent = EVECORP . ' ' . EVECORP_VERSION;
	}
}

/**
 * Generic API request function
 *
 * @param string $scope
 * @param string $API
 * @param array $arguments
 * @param array $key
 *
 * @return mixed WP_Pheal object on success, WP_Error object on failure.
 */
function evecorp_api( $scope, $API, $arguments = '', $key = '', $key_type = '', $access_mask = '' )
{
	/* Load pheal */
	load_wp_pheal();

	if ( empty( $key ) ) {
		$key['key_ID']	 = null;
		$key['vcode']	 = null;
	}

	/* Create the Pheal object */
	$request = new WP_Pheal( $key['key_ID'], $key['vcode'], $scope );

	/* Detect access */
	if ( !empty( $key_type ) && !empty( $access_mask ) ) {
		$request->setAccess( $key_type, $access_mask );
	} else {
		$access = evecorp_test_access( $request );
		if ( is_wp_error( $access ) )
			return $access;
	}

	try {

		/* Call the requested API function */
		$result = $request->$API( $arguments );
	} catch ( PhealAccessException $e ) {

		/* API Access Error (Pheal refused to exec, as API-key is not allowed */
		return new WP_Error( 'PhealAccessException', $e->getMessage() );
	} catch ( PhealAPIException $e ) {

		/* API Error (Eve Online servers with API error) */
		return new WP_Error( 'PhealAPIException', $e->getMessage(), $e->code );
	} catch ( PhealHTTPException $e ) {

		/* Eve Online API servers answer with HTTP Error (503, 404, etc.) */
		return new WP_Error( 'PhealHTTPException', $e->getMessage(), $e->code );
	} catch ( PhealException $e ) {

		/* Other Error (network/server connection, etc.) */
		return new WP_Error( 'PhealException', $e->getMessage(), $e->code );
	}

	/* Return the result as object of class WP_Pheal */
	return $result;
}

/**
 * Tests if a API request is allowed with the supplied API key.
 *
 * @param type $request
 * @return mixed true on success WP_Error obect on failure
 */
function evecorp_test_access( $request )
{
	try {

		$request->detectAccess();
	} catch ( PhealAccessException $e ) {

		/* API Access Error (Pheal refused to exec, cause the API-key would not allow this request anyway) */
		return new WP_Error( 'PhealAccessException', $e->getMessage() );
	} catch ( PhealAPIException $e ) {

		/* API Error (Eve Online servers with API error) */
		return new WP_Error( 'PhealAPIException', $e->getMessage(), $e->code );
	} catch ( PhealHTTPException $e ) {

		/* Eve Online API servers answer with HTTP Error (503, 404, etc.) */
		return new WP_Error( 'PhealHTTPException', $e->getMessage(), $e->code );
	} catch ( PhealException $e ) {

		/* Other Error (network/server connection, etc.) */
		return new WP_Error( 'PhealException', $e->getMessage(), $e->code );
	}

	return true;
}

/**
 * Returns information about the supplied API key.
 *
 * @param array $key. Eve Online API key authorization credentials. (key_ID, vcode).
 *
 * @return mixed. Array on success, WP_Error object on failure.
 */
function evecorp_get_keyinfo( $key )
{
	$result = evecorp_api( 'account', 'APIKeyInfo', '', $key );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	return $result->key->toArray();
}

/**
 * Tests if Eve Online API key is valid and has the proper access rights to perform a certain request
 * If $access_mask is not provided, it will be requested from API servers.
 * Needs key for API obviously.
 *
 * @param array $key. Eve Online API key authorization credentials. (key_ID, vcode).
 * @param string $key_type. The access level of the API key (Account, Character, Corporation).
 * @param string $scope. The scope of the request to test.
 * @param string $api_name Name of API request to check.
 * @param string $access_mask. Optional. The bitwise number of the calls the API key can query.
 *
 * @return mixed. true if $key is valid, WP_Error object on failure.
 */
function evecorp_is_valid_key( $key, $key_type, $scope, $API, $access_mask = '' )
{
	$result = evecorp_api( $scope, $API, '', $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return $result;
	return true;
}

function evecorp_char_sheet( $character_ID )
{
	$result = evecorp_api( 'eve', 'CharacterInfo', $character_ID );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$array = $result->toArray();
	return $array['result'];
}

/**
 * Returns a character name looked up by its ID from Eve Online API
 * Doesn't need API key authorization.
 *
 * @param string $characterID. ID number of the character to lookup.
 *
 * @return string. Name of the character.
 */
function evecorp_get_char_name( $character_ID )
{
	$result = evecorp_api( 'eve', 'CharacterInfo', $character_ID );
	if ( is_wp_error( $result ) )
		return $result->get_error_message();

	/* Get the result as string in a PHP variable */
	return $result['characterName'];
}

/**
 * Returns a corporation name looked up by its ID from Eve Online API
 * Doesn't need API key authorization
 *
 * @param string $corporationID. ID number of the corporation to lookup.
 *
 * @return string. Name of the corporation
 */
function evecorp_get_corp_name( $corporation_ID )
{
	$corpsheet = evecorp_get_corpsheet( $corporation_ID );
	return $corpsheet ['corporationName'];
}

/**
 * Returns a character or corporation ID by its name from Eve Online API
 * Doesn't need API key authorization
 *
 * @param string $name. Name of the character or coporation to lookup.
 *
 * @return string. ID number of the character or corporation.
 */
function evecorp_get_ID( $name )
{
	// Prepare the arguments
	$arguments = array(
		'names' => $name
	);

	$result = evecorp_api( 'eve', 'CharacterID', $arguments );
	if ( is_wp_error( $result ) )
		return $result;

	/* Get the result as string in a PHP variable */
	return $result->characters[0]->characterID;
}

/**
 * Tests if character or corporation name is valid/existing by looking it up
 *  with Eve Online API
 * Doesn't need API key authorization
 *
 * @param string $characterName. Name of the character or coporation to lookup.
 *
 * @return boolean true on success, false on failure.
 */
function evecorp_is_name( $name )
{
	$result = evecorp_api( 'eve', 'CharacterID', $name );
	if ( is_wp_error( $result ) )
		return false;
	return true;
}

/**
 * Returns the publicly available information about a corporation by looking it up
 *  with Eve Online API
 * Doesn't need API key authorization
 *
 * @param string $corporationID. ID number of the coporation.
 *
 * @return mixed array on success, WP_Error object on failure.
 */
function evecorp_get_corpsheet( $corporation_ID )
{
	/* Prepare the arguments */
	$arguments = array(
		'corporationID' => $corporation_ID
	);

	$result = evecorp_api( 'corp', 'CorporationSheet', $arguments );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$array = $result->toArray();
	return $array['result'];
}

function evecorp_get_corp_url( $corporation_ID )
{
	$corpsheet = evecorp_get_corpsheet( $corporation_ID );
	return $corpsheet ['url'];
}

/**
 * Tests if a character is a member of a specific corp.
 * Doesn't need API key authorization.
 *
 * @param string $characterID. ID number of the character.
 * @param string $corporationID. ID number of the coporation.
 *
 * @return boolean true on success, false on failure.
 */
function evecorp_is_member( $character_ID, $corporation_ID )
{
	$result = evecorp_api( 'eve', 'CharacterInfo', $character_ID );
	if ( is_wp_error( $result ) )
		return false;

	/* Compare the result with the supplied corpID */
	if ( $corporation_ID === $result->corporationID )
		return true;
	return false;
}

/**
 * Returns an array with all members and there roles and titles.
 * Needs a corporation key with access granted to the
 * "Corporation Members/Member Security" API.
 *
 * @return mixed array on success, WP_Error object on failure.
 */
function evecorp_member_security()
{

	/* Get our corp API key from options */
	$key = array(
		'key_ID'	 => evecorp_get_option( 'corpkey_ID' ),
		'vcode'		 => evecorp_get_option( 'corpkey_vcode' )
	);
	$key_type	 = evecorp_get_option( 'corpkey_type' );
	$access_mask = evecorp_get_option( 'corpkey_access_mask' );

	/* API Request */
	$result = evecorp_api( 'corp', 'MemberSecurity', null, $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$members = $result->members->toArray();
	return $members;
}

/**
 * Returns an array with all roles of the specified character
 * @param string $character_ID
 *
 * @return mixed array on success, WP_Error object on failure.
 */
function evecorp_get_roles( $character_ID )
{
	$roles = array( );
	$members = evecorp_member_security();
	if ( is_wp_error( $members ) )
		return $members;
	foreach ( $members as $member ) {
		if ( $character_ID === $member['characterID'] ) {
			foreach ( ($member['roles'] ) as $role ) {
				$roles[] = $role['roleName'];
			}
		}
	}
	return $roles;
}

/**
 * Returns an array with all titles of the specified character
 * @param string $character_ID
 *
 * @return mixed array on success, WP_Error object on failure.
 */
function evecorp_get_titles( $character_ID )
{
	$titles = array( );
	$members = evecorp_member_security();
	if ( is_wp_error( $members ) )
		return $members;
	foreach ( $members as $member ) {
		if ( $character_ID === $member['characterID'] ) {
			foreach ( ($member['titles'] ) as $title ) {
				$titles[] = $title['titleName'];
			}
		}
	}
	return $titles;
}

/**
 * Tests if a character is the CEO of a specific corp.
 * Doesn't need API key authorization.
 *
 * @param string $characterID. ID number of the character.
 * @param string $corporationID. ID number of the coporation.
 *
 * @return boolean true on success, false on failure.
 */
function evecorp_is_CEO( $character_ID, $corporation_ID )
{
	$result = evecorp_get_corpsheet( $corporation_ID );
	if ( is_wp_error( $result ) )
		return false;

	/* Compare the result with the supplied characterID */
	if ( $character_ID === $result['ceoID'] )
		return true;
	return false;
}

/**
 * Tests if a character is a director of a specific corp.
 * Needs a corporation key with access granted to the
 * "Corporation Members/Member Security" API.
 *
 * @param string $characterID. ID number of the character.
 * @param array $corpKey. Corporation API key authorization credentials.
 *
 * @return boolean true if character is director
 */
function evecorp_is_director( $character_ID )
{
	$key = array(
		'key_ID'	 => evecorp_get_option( 'corpkey_ID' ),
		'vcode'		 => evecorp_get_option( 'corpkey_vcode' )
	);
	$key_type	 = evecorp_get_option( 'corpkey_key_type' );
	$access_mask = evecorp_get_option( 'access_mask' );

	/* Prepare the arguments */
	$arguments = array(
		'characterID' => $character_ID
	);

	$result = evecorp_api( 'corp', 'MemberSecurity', $arguments, $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return false;

	/* Compare the result with the RoleID for directors */
	if ( $result->CharacterID->RoleID == 1 )
		return true;
	return false;
}

/**
 * Get the wallet journal entries as array.
 * Needs a corporation key with access granted to the
 * "Account and Market/Wallet Journal" API.
 *
 * @param string $account_key
 * @param string $from_ID
 * @param string $row_count
 *
 * @return mixed array on success WP_Error object on failure.
 */
function evecorp_corp_journal( $account_key = '1000', $from_ID = '', $row_count = '' )
{
	$key = array(
		'key_ID'	 => evecorp_get_option( 'corpkey_ID' ),
		'vcode'		 => evecorp_get_option( 'corpkey_vcode' )
	);
	$key_type	 = evecorp_get_option( 'corpkey_type' );
	$access_mask = evecorp_get_option( 'corpkey_access_mask' );

	/* Prepare the arguments */
	$arguments = array(
		'accountKey' => $account_key
	);

	if ( '' != $from_ID ) {
		$arguments['fromID'] = $from_ID;
	}

	if ( '' != $row_count ) {
		$arguments['rowCount']	 = $row_count;
	}
	$result					 = evecorp_api( 'corp', 'WalletJournal', $arguments, $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$journal = $result->entries->toArray();
	return $journal;
}

/**
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

	if ( is_ssl() ) {
		$protocol = 'https://';
	} else {
		$protocol		 = 'http://';
	}
	$host			 = 'image.eveonline.com';
	$server_path	 = 'Character';
	$eve_size		 = evecorp_avatar_size( $size );
	$suffix			 = 'jpg';
	$eve_avatar_url	 = $protocol . trailingslashit( $host ) . trailingslashit( $server_path ) . $character_ID . '_' . $eve_size . '.' . $suffix;
	$eve_avatar		 = "<img alt='{$alt}' src='{$eve_avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
	return $eve_avatar;
}

function evecorp_author_mail( $value, $user_id )
{
	if ( empty( $value ) )
		$value = $user_id;
	return $value;
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
	$eve_image_sizes = array( 30, 32, 64, 128, 200, 256, 512 );
	if ( in_array( $requested_size, $eve_image_sizes ) )
		return $requested_size;
	foreach ( $eve_image_sizes as $size ) {

		/* Return the next bigger available */
		if ( (int) $requested_size < $size )
			return (string) $size;
	}

	/* Return the biggets available */
	return '512';
}

/**
 * Request Trust
 * @return string
 */
function evecorp_trust_button()
{
	$html = '<button type="button" onclick="CCPEVE.requestTrust(\'' . home_url() . '\');">Set ' . home_url() . ' as trusted</button>';
	return $html;
}

/**
 * Icons
 * @return string
 */
function evecorp_icon( $icon )
{
	switch ( $icon ) {
		case "yes":
			$html	 = '<img src="' . admin_url() . 'images/yes.png" width="16" height="16" alt="Yes">';
			break;
		case "no":
			$html	 = '<img src="' . admin_url() . 'images/no.png" width="16" height="16" alt="No">';
			break;
		case "maybe":
			$html	 = '<p><img src="' . includes_url() . 'images/smilies/icon_question.gif" width="16" height="16" alt="Maybe">';
			break;
	}
	return $html;
}