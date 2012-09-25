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
	/* @var $evecorp_options array */
	global $evecorp_options;

	// Newly introduced options should be added here
	$default_options = array(
		'plugin_version' => EVECORP_VERSION,
		'corpkey_ID' => '',
		'corpkey_vcode' => '',
		'corpkey_verified' => false,
		'API_base_url' => 'https://api.eveonline.com/',
		'cache_API' => false,
		'char_url' => 'https://gate.eveonline.com/Profile/',
		'char_url_label' => 'EVE Gate',
		'corp_url' => 'https://gate.eveonline.com/Corporation/',
		'corp_url_label' => 'EVE Gate',
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
	$evecorp_options = get_option( 'evecorp_options' );
	if ( defined( 'EVECORP_' . strtoupper( $key ) ) )
		$evecorp_options[$key] = constant( 'EVECORP_' . strtoupper( $key ) );
	return $evecorp_options[$key];
}

/**
 * Tests if the current browser is a Eve Online in-game browser
 *
 * @uses evecorp_IGB_data()
 * @return boolean true if client is Eve Online in-game browser
 * @todo Detection doesn't work logically
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
 * @uses $_SERVER superglobal array
 * @return array
 * @todo fix detection logic
 */
function evecorp_IGB_data()
{

	global $evecorp_IGB_data;

	$evecorp_IGB_data = array(
		'is_igb' => true,
		'trusted' => false,
		'values' => array( )
	);

	foreach ( $_SERVER as $key => $value ) {

		// Skip on non-Eve headers
		if ( strpos( 'HTTP_EVE', $key ) === false )
			continue;

		// IGB browser detected
		$evecorp_IGB_data['is_igb'] = true;

		// Remove the HTTP_EVE_ prefix and make it lowercase
		$key = strtolower( str_replace( 'HTTP_EVE_', '', $key ) );

		// Set the trusted value to true if the header has been sent.
		if ( $key === 'trusted' )
			$evecorp_IGB_data['trusted'] = true;

		// Store key and value in array
		$evecorp_IGB_data['values'][$key] = $value;
	}

	return($evecorp_IGB_data);
}

/**
 * Load and configure Pheal
 *
 * @uses Pheal object class
 * @return Pheal
 */
function load_pheal()
{
	// Do this only once
	if ( !class_exists( 'Pheal', FALSE ) ) {

		// Load the stuff
		require_once dirname( __FILE__ ) . "/pheal/Pheal.php";

		// register the class loader
		spl_autoload_register( "Pheal::classload" );

		// Set the cache and tell it were to save its contents
		if ( true === evecorp_get_option( 'cache_API' ) )
			PhealConfig::getInstance()->cache = new PhealFileCache( WP_CONTENT_DIR . '/cache/pheal/' );

		// Enable access detection
		PhealConfig::getInstance()->access = new PhealCheckAccess();

		// Identify ourself
		PhealConfig::getInstance()->http_user_agent = EVECORP . ' ' . EVECORP_VERSION;
	}
}

/**
 * Tests if Eve Online API key is valid and has the proper access rights to perform a certain request
 *
 * If $access_mask is not provided, it will be requested from API servers.
 *
 * Needs key for API obviously
 *
 * @param array $key. Eve Online API key authorization credentials. (key_ID, vcode).
 * @param string $key_type. The access level of the API key (Account, Character, Corporation).
 * @param string $scope. The scope of the request to test.
 * @param string $api_name Name of API request to check.
 * @param string $access_mask. Optional. The bitwise number of the calls the API key can query.
 *
 * @return mixed. true if $key is valid, WP_Error object on failure.
 */
function evecorp_is_valid_key( $key, $key_type, $scope, $api_name, $access_mask = '' )
{

	// Load pheal
	load_pheal();

	// Create the Pheal object
	$request = new Pheal( $key['key_ID'], $key['vcode'], $scope );

	if ( $access_mask === '' ) {

		try {

			// Request acccess mask from API servers.
			$request->detectAccess();
		} catch ( PhealAccessException $e ) {

			// API Access Error (Pheal refused to exec, cause the API-key would not allow this request anyway)
			return new WP_Error( 'PhealAccessException', $e->getMessage() );
		} catch ( PhealAPIException $e ) {

			// API Error (Eve Online servers with API error)
			return new WP_Error( 'PhealAPIException', $e->getMessage() );
		} catch ( PhealHTTPException $e ) {

			// Eve Online API servers answer with HTTP Error (503, 404, etc.)
			return new WP_Error( 'PhealHTTPException', $e->getMessage(), $e->code );
		} catch ( PhealException $e ) {

			// Other Error (network/server connection, etc.)
			return new WP_Error( 'PhealException', $e->getMessage(), $e->code );
		}
	} else {
		$request->setAccess( $key_type, $access_mask );
	}

	try {

		// Call the approbiate API function
		$request->$api_name( $key );
//		$result = $request->APIKeyInfo( $key );
	} catch ( PhealAccessException $e ) {

		// Access Error (Pheal refused to exec, cause accessMask not valid for this key)
		return new WP_Error( 'PhealAccessException', $e->getMessage() );
	} catch ( PhealAPIException $e ) {

		// API Error (Eve Online servers sent back a error)
		return new WP_Error( 'PhealAPIException', $e->getMessage() );
	} catch ( PhealHTTPException $e ) {

		// Eve Online API servers answer with HTTP Error (503, 404, etc.)
		return new WP_Error( 'PhealHTTPException', $e->getMessage() );
	} catch ( PhealException $e ) {

		// Other Error (network/server connection, etc.)
		return new WP_Error( 'PhealException', $e->getMessage() );
	}
	return true;
}

/**
 * Returns a character name looked up by its ID from Eve Online API
 * Doesn't need API key authorization
 *
 * @uses loadPheal()
 * @return string. Name of the character
 * @param string $characterID. ID number of the character to lookup.
 */
function evecorp_get_char_name( $character_ID )
{

	// Load pheal
	load_pheal();

	// Create the Pheal object
	$request = new Pheal( null, null, 'eve' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CharacterInfo function
		$result = $request->CharacterInfo( $character_ID );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Get the result as string in a PHP variable
	return $result->CharacterInfo->characterName;
}

/**
 * Returns a corporation name looked up by its ID from Eve Online API
 *
 * Doesn't need API key authorization
 *
 * @return string. Name of the corporation
 *
 * @param string $corporationID. ID number of the corporation to lookup.
 */
function evecorp_get_corp_name( $corporation_ID )
{

	// Load pheal
	load_pheal();

	// Create the Pheal object
	$request = new Pheal( null, null, 'corp' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CorporationSheet function
		$result = $request->CorporationSheet( $corporation_ID );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Get the result as string in a PHP variable
	return $result->corporationName;
}

/**
 * Returns a character or corporation ID looked up by its name from Eve
 *  Online API
 * Doesn't need API key authorization
 *
 * @uses loadPheal()
 * @return string. ID number of the character or corporation.
 * @param string $name. Name of the character or coporation to lookup.
 */
function evecorp_get_ID( $name )
{

	// Load pheal
	load_pheal();

	// Prepare the arguments
	$arguments = array(
		'names' => $name
	);

	// Create the Pheal object
	$request = new Pheal( null, null, 'eve' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CharacterID function
		$result = $request->CharacterID( $arguments );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Get the result as string in a PHP variable
	return $result->characters[0]->characterID;
}

/**
 * Tests if character or corporation name is valid/existing by looking it up
 *  with Eve Online API
 * Doesn't need API key authorization
 *
 * @uses evecorp_getID()
 * @return TRUE|FALSE.
 * @param string $characterName. Name of the character or coporation to lookup.
 */
function evecorp_is_name( $name )
{

	if ( eve_corp_get_ID( $name ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Returns the publicly available information about a corporation by looking it up
 *  with Eve Online API
 *
 * Doesn't need API key authorization
 * @uses loadPheal()
 * @return array.
 * @param string $corporationID. ID number of the coporation.
 */
function evecorp_corpsheet( $corporation_ID )
{

	// Load pheal
	load_pheal();

	// Prepare the arguments
	$arguments = array(
		'corporationID' => $corporation_ID
	);

	// Create the Pheal object
	$request = new Pheal( null, null, 'corp' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CorporationSheet function
		$result = $request->CorporationSheet( $arguments );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Convert API result object to a PHP array variable
	return $result->toArray();
}

/**
 * Tests if a character is a member of a specific corp.
 *
 * Doesn't need API key authorization
 * @uses loadPheal()
 * @return boolean
 * @param string $characterID. ID number of the character.
 * @param string $corporationID. ID number of the coporation.
 */
function evecorp_is_member( $character_ID, $corporation_ID )
{

	// Load pheal
	load_pheal();

	// Prepare the arguments
	$arguments = array(
		'characterID' => $character_ID
	);

	// Create the Pheal object
	$request = new Pheal( null, null, 'eve' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CharacterInfo function
		$result = $request->CharacterInfo( $arguments );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Compare the result with the supplied corpID
	if ( $result->corporationID == $corporation_ID ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Tests if a character is the CEO of a specific corp.
 *
 * Doesn't need API key authorization
 * @uses loadPheal()
 * @return TRUE|FALSE.
 * @param string $characterID. ID number of the character.
 * @param string $corporationID. ID number of the coporation.
 */
function evecorp_is_CEO( $character_ID, $corporation_ID )
{

	// Load pheal
	load_pheal();

	// Prepare the arguments
	$arguments = array(
		'corporationID' => $corporation_ID
	);

	// Create the Pheal object
	$request = new Pheal( null, null, 'corp' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the CorporationSheet function
		$result = $request->CorporationSheet( $arguments );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Compare the result with the supplied characterID
	if ( $result->ceoID == $character_ID ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Tests if a character is a director of a specific corp.
 *
 * Needs corporation key with access to "Corporation Members/Member Security"
 *  for API
 *
 * @uses loadPheal()
 * @return boolean true if character is director
 * @param string $characterID. ID number of the character.
 * @param array $corpKey. Corporation API key authorization credentials.
 */
function evecorp_is_director( $character_ID, $corporation_key )
{

	// Load pheal
	load_pheal();

	// Prepare the arguments
	$arguments = array(
		'characterID' => $character_ID
	);

	// Create the Pheal object
	$request = new Pheal( $corporation_key['keyID'], $corporation_key['vCode'], 'corp' );

	// Detect access
	$request->detectAccess();

	try {

		// Call the MemberSecurity function
		$result = $request->MemberSecurity( $arguments );
	} catch ( PhealAccessException $e ) {

		// API Access Error
		echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
	} catch ( PhealAPIException $e ) {

		// API Error
		echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
	} catch ( PhealException $e ) {

		// Some other kind of error
		echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
	}

	// Compare the result with the RoleID for directors
	if ( $result->CharacterID->RoleID == 1 ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Check if character is communication officer of corp
// Check if character is personnel manager of corp
// Check if character is security officer of corp
// Check if character has any role in corp
// Request Trust
function evecorp_trust_button()
{
	$html = '<button type="button" onclick="CCPEVE.requestTrust(\'' . home_url() . '\');">Set ' . home_url() . ' as trusted</button>';
	return $html;
}

// Icons
function evecorp_icon( $icon )
{
	switch ( $icon ) {
		case "yes":
			$html = '<img src="' . admin_url() . 'images/yes.png" width="16" height="16" alt="Yes">';
			break;
		case "no":
			$html = '<img src="' . admin_url() . 'images/no.png" width="16" height="16" alt="No">';
			break;
		case "maybe":
			$html = '<p><img src="' . includes_url() . 'images/smilies/icon_question.gif" width="16" height="16" alt="Maybe">';
			break;
	}
	return $html;
}