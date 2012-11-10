<?php

/*
 * Eve Online Plugin for WordPress
 *
 * EveOnline API functions library
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Load and configure Pheal
 *
 * @uses Pheal object class
 * @return Pheal
 */
function evecorp_init_pheal()
{
	/* Do this only once */
	if ( !class_exists( 'WP_Pheal', FALSE ) ) {

		/* Load the stuff */
		require_once dirname( __FILE__ ) . "/classes/pheal/Pheal.php";
		require_once dirname( __FILE__ ) . "/classes/class-wp-pheal.php";

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
 * @param string $scope Eve Online API request scope.
 * @param string $API Requested Eve Online API function name.
 * @param array $arguments Optional query arguments.
 * @param array $key Optional API key ID and verification code.
 * @param string $key_type Optional API key type (Account, Character or Corporate)
 * @param string $access_mask Optional API key access permisisons bitmask
 * @return mixed WP_Pheal object on success, WP_Error object on failure.
 */
function evecorp_api( $scope, $API, $arguments = '', $key = '', $key_type = '', $access_mask = '' )
{
	/* Load pheal */
	evecorp_init_pheal();

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
		$access = evecorp_test_access( $scope, $key, $key_type, $access_mask );
		if ( is_wp_error( $access ) )
			return $access;
	}

	try {

		/* Call the requested API function */
		$result = $request->$API( $arguments );
	} catch ( PhealAccessException $e ) {

		/* API Access Error (Pheal refused to exec, as API-key is not allowed */
		return new WP_Error( 'PhealAccessException', 'Eve Online key access error: ' . $e->getMessage() );
	} catch ( PhealAPIException $e ) {

		/* API Error (Eve Online servers with API error) */
		return new WP_Error( 'PhealAPIException', 'Eve Online API error: ' . $e->getMessage(), $e->code );
	} catch ( PhealHTTPException $e ) {

		/* Eve Online API servers answer with HTTP Error (503, 404, etc.) */
		return new WP_Error( 'PhealHTTPException', 'Eve Online server error: ' . $e->getMessage() );
	} catch ( PhealException $e ) {

		/* Other Error (network/server connection, etc.) */
		return new WP_Error( 'PhealException', 'Eve Online connection error: ' . $e->getMessage(), $e->code );
	}

	/* Return the result as object of class WP_Pheal */
	return $result;
}

/**
 * Tests if a API request is allowed with the supplied API key.
 *
 * @param string $scope Eve Online API scope (account, eve, corp, etc.).
 * @param array $key Array with API key ID and verification code (vcode),
 * @param string $key_type The type of this API key (account, character, corp).
 * @param string $access_mask The access mask of the supplied API key.
 * @return \WP_Error|boolean true on success WP_Error obect on failure.
 */
function evecorp_test_access( $scope, $key, $key_type, $access_mask )
{
	/* Load pheal */
	evecorp_init_pheal();

	if ( empty( $key ) ) {
		$key['key_ID']	 = null;
		$key['vcode']	 = null;
	}

	/* Create the Pheal object */
	$request = new WP_Pheal( $key['key_ID'], $key['vcode'], $scope );

	/* Detect access */
	if ( !empty( $key_type ) && !empty( $access_mask ) )
		$request->setAccess( $key_type, $access_mask );

	try {

		$request->detectAccess();
	} catch ( PhealAccessException $e ) {

		/* API Access Error (Pheal refused to exec, cause the API-key would not allow this request anyway) */
		return new WP_Error( 'PhealAccessException', 'Eve Online key access error: ' . $e->getMessage() );
	} catch ( PhealAPIException $e ) {

		/* API Error (Eve Online servers with API error) */
		return new WP_Error( 'PhealAPIException', 'Eve Online API error: ' . $e->getMessage(), $e->code );
	} catch ( PhealHTTPException $e ) {

		/* Eve Online API servers answer with HTTP Error (503, 404, etc.) */
		return new WP_Error( 'PhealHTTPException', 'Eve Online server error: ' . $e->getMessage() );
	} catch ( PhealException $e ) {

		/* Other Error (network/server connection, etc.) */
		return new WP_Error( 'PhealException', 'Eve Online connection error: ' . $e->getMessage(), $e->code );
	}

	return true;
}

/**
 * Returns information about the supplied API key.
 *
 * @param array $key. Eve Online API key authorization credentials. (key_ID, vcode).
 * @return \WP_Error|array Array on success, WP_Error object on failure.
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
 * Tests if Eve Online API key is valid and has the proper access rights to
 * perform a certain request.
 * If $access_mask is not provided, it will be requested from API servers first.
 *
 * @param array $key. Eve Online API key authorization credentials. (key_ID, vcode).
 * @param string $key_type. The access level of the API key (Account, Character, Corporation).
 * @param string $scope. The scope of the request to test.
 * @param string $api_name Name of API request to check.
 * @param string $access_mask. Optional. The bitwise number of the calls the API key can query.
 * @return \WP_Error|boolean. True if $key is valid, WP_Error object on failure.
 */
function evecorp_is_valid_key( $key, $key_type, $scope, $API, $access_mask = '' )
{
	$access	 = evecorp_test_access( $scope, $key, $key_type, $access_mask );
	if ( is_wp_error( $access ) )
		return $access;
	$result	 = evecorp_api( $scope, $API, '', $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return $result;
	return true;
}

/**
 * Returns basic public data about a character from Eve Online API.
 * Doesn't need API key authorization.
 *
 * @param type $character_ID The ID of the Eve Online character.
 * @return \WP_Error|array Array on success WP_Error object on failure.
 */
function evecorp_get_char_info( $character_ID )
{
	/* Prepare the arguments */
	$arguments = array(
		'characterID' => $character_ID
	);

	/* Query API */
	$result = evecorp_api( 'eve', 'CharacterInfo', $arguments );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$array = $result->toArray();
	return $array['result'];
}

/**
 * Returns a character name looked up by its ID from Eve Online API.
 * Doesn't need API key authorization.
 *
 * @param string $characterID. ID number of the character to lookup.
 * @return \WP_Error|string. Name of the character or WP_Error object on failure.
 */
function evecorp_get_char_name( $character_ID )
{
	$character_info = evecorp_get_char_info( $character_ID );
	if ( is_wp_error( $character_info ) )
		return $character_info;
	return $character_info['characterName'];
}

/**
 * Returns the name of the corporation of the Eve Online character.
 * @param string $character_ID ID number of the character to lookup.
 * @return \WP_Error|string Corporation name or WP_Error object on failure.
 */
function evecorp_get_char_corp( $character_ID )
{
	$character_info = evecorp_get_char_info( $character_ID );
	if ( is_wp_error( $character_info ) )
		return $character_info;
	return $character_info['corporation'];
}

/**
 * Returns the ID of a character or corporation by its name from Eve Online API.
 * Doesn't need API key authorization
 *
 * @param string $name. Name of the character or coporation to lookup.
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
 * @return boolean true on success, false if not or on failure.
 */
function evecorp_is_name( $name )
{
	$result = evecorp_get_ID( $name );
	if ( is_wp_error( $result ) )
		return false;
	return true;
}

/**
 * Returns the publicly available information about a corporation by looking it
 * up with Eve Online API.
 * Doesn't need API key authorization
 *
 * @param string $corporationID. ID number of the coporation.
 * @return \WP_Error|array Corporation sheet on success, WP_Error object on failure.
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

/**
 * Returns a corporation name looked up by its ID from Eve Online API
 * Doesn't need API key authorization
 *
 * @param string $corporationID. ID number of the corporation to lookup.
 * @return string. Name of the corporation
 */
function evecorp_get_corp_name( $corporation_ID )
{
	$corpsheet = evecorp_get_corpsheet( $corporation_ID );
	if ( is_array( $corpsheet ) )
		return $corpsheet['corporationName'];
}

function evecorp_get_corp_url( $corporation_ID )
{
	$corpsheet = evecorp_get_corpsheet( $corporation_ID );
	if ( is_array( $corpsheet ) )
		return $corpsheet['url'];
}

/**
 * Tests if a character is a member of a specific corp.
 * Doesn't need API key authorization.
 *
 * @param string $characterID. ID number of the character.
 * @param string $corporationID. Optional. ID number of the coporation.
 * @return boolean true if its a member, false if not or on failure.
 */
function evecorp_is_member( $character_ID, $corporation_ID = '' )
{
	if ( empty( $corporation_ID ) )
		$corporation_ID	 = evecorp_get_option( 'corpkey_corporation_id' );
	$character_info = evecorp_get_char_info( $character_ID );
	if ( is_wp_error( $character_info ) )
		return false;

	/* Compare the result with the supplied corpID */
	if ( $corporation_ID === $character_info->corporationID )
		return true;
	return false;
}

/**
 * Get all current members of the corporation.
 * Needs a corporation key with access granted to the
 * "Corporation Members/MemberTracking" API.
 *
 * @return \WP_Error|array on success, WP_Error object on failure.
 */
function evecorp_get_members()
{
	/* Get our corp API key from options */
	$key = array(
		'key_ID'	 => evecorp_get_option( 'corpkey_ID' ),
		'vcode'		 => evecorp_get_option( 'corpkey_vcode' )
	);
	$key_type	 = evecorp_get_option( 'corpkey_type' );
	$access_mask = evecorp_get_option( 'corpkey_access_mask' );

	/* API Request */
	$result = evecorp_api( 'corp', 'MemberTracking', null, $key, $key_type, $access_mask );
	if ( is_wp_error( $result ) )
		return $result;

	/* Convert API result object to a PHP array variable */
	$members = $result->members->toArray();
	return $members;
}

/**
 * Returns an array with all members and there roles and titles.
 * Needs a corporation key with access granted to the
 * "Corporation Members/Member Security" API.
 *
 * @return \WP_Error|array Roles and titles on success, WP_Error object on failure.
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
 * Returns an array with all roles of the specified character.
 *
 * @param string $character_ID
 * @return \WP_Error|array Roles on success, WP_Error object on failure.
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
 * Returns an array with all titles of the specified character.
 *
 * @param string $character_ID
 * @return \WP_Error|array Titles on success, WP_Error object on failure.
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
 * @param string $corporationID. Optional. ID number of the coporation.
 * @return boolean true on success, false on failure.
 */
function evecorp_is_CEO( $character_ID, $corporation_ID = '' )
{
	if ( empty( $corporation_ID ) )
		$corporation_ID	 = evecorp_get_option( 'corpkey_corporation_id' );
	$result			 = evecorp_get_corpsheet( $corporation_ID );
	if ( is_wp_error( $result ) )
		return false;

	/* Compare the result with the supplied characterID */
	if ( $character_ID === $result['ceoID'] )
		return true;
	return false;
}

/**
 * Tests if a character is a director of the corporation.
 * Needs a corporation key with access granted to the
 * "Corporation Members/Member Security" API.
 *
 * @param string $characterID. ID number of the character.
 * @param array $corpKey. Corporation API key authorization credentials.
 * @return boolean true if character is director, false if not.
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
 * @param string $account_key Optional, defaults to 1000. The account number.
 * @param string $from_ID Optional. The journal entry number to start with.
 * @param string $row_count Optional. The number of journal entries to get.
 * @return \WP_Error|array Journal-data on success WP_Error object on failure.
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