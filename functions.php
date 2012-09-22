<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Copyright © 2012 Mitome Cobon-Han  (mitome.ch@gmail.com)
 *
 *	This file is part of evecorp.
 *
 *	evecorp is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	evecorp is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	You should have received a copy of the GNU General Public License
 *	along with evecorp.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package evecorp
 */

/**
 * Tests if the current browser is a Eve Online in-game browser
 *
 * @uses evecorp_IGB_data()
 * @return boolean true if client is Eve Online in-game browser
 * @todo Detection doesn't work logically
 */
function evecorp_is_eve() {

    global $evecorp_igb_data;

    if ( ! isset( $evecorp_igb_data ) )
        $evecorp_igb_data = evecorp_IGB_data();

    return $evecorp_igb_data['is_igb'];
}

/**
 * Tests if the Eve Online in-game browser is trusting us
 *
 * @uses evecorp_igb_data()
 * @return TRUE|FALSE
 */
function evecorp_is_trusted() {

    global $evecorp_igb_data;

    if ( ! isset( $evecorp_igb_data ) )
        $evecorp_igb_data = evecorp_IGB_data();

    return $evecorp_igb_data['trusted'];
}

/**
 * Returns the Eve Online in game browser data, if any
 *
 * @uses $_SERVER superglobal array
 * @return array
 * @todo fix detection logic
 */
function evecorp_IGB_data() {

    global $evecorp_igb_data;

    $evecorp_igb_data = array(
        'is_igb' => true,
        'trusted' => false,
        'values' => array()
    );

    foreach ($_SERVER as $key => $value) {

        // Skip on non-Eve headers
        if (strpos('HTTP_EVE', $key) === false)
            continue;

        // IGB browser detected
        $evecorp_igb_data['is_igb'] = true;

        // Remove the HTTP_EVE_ prefix
        $key = str_replace('HTTP_EVE_', '', $key);

        // Make it lower case
        $key = strtolower($key);

        // Set the trusted value to true if the header has been sent.
        if ($key === 'trusted')
            $evecorp_igb_data['trusted'] = true;

        // Store key and value in array
        $evecorp_igb_data['values'][$key] = $value;
    }

    return($evecorp_igb_data);
}

/**
 * Load and configure Pheal
 *
 * @uses Pheal object class
 * @return Pheal
 */
function load_pheal() {
    // Do this only once
    if ( ! class_exists( 'Pheal', FALSE ) ) {

        // Load the stuff
        require_once dirname( __FILE__ ) . "/pheal/Pheal.php";

        // register the class loader
        spl_autoload_register( "Pheal::classload" );

        // Set the cache and tell it were to save its contents
        PhealConfig::getInstance()->cache = new PhealFileCache( WP_CONTENT_DIR . '/cache/pheal/' );

        // Enable access detection
        PhealConfig::getInstance()->access = new PhealCheckAccess();

        // Identify ourself
        PhealConfig::getInstance()->http_user_agent = WP_EVECORP . ' ' . WP_EVECORP_VERSION;
    }
}

/**
 * Tests if Eve Online API key is valid and has the proper access rights.
 *
 * Needs key for API obviously
 *
 * @uses loadPheal()
 * @return TRUE|FALSE.
 * @param array $key. Eve Online API key authorization credentials. (ID, vCode).
 * @param string $type. Optional.The access level of the API key (Account,
 *   Character, Corporation)
 * @param string $accessMask. Optional. The bitwise number of the calls the API
 *   key can query
 */
function evecorp_is_valid_key( $key, $keyType = '', $accessMask = '' ) {

    // Load pheal
    load_pheal();

    // Create the Pheal object
    $request = new Pheal( $key['keyID'], $key['vCode'], 'account' );

    // Detect access
    $request->detectAccess();

    try {

        // Call the APIKeyInfo function
        $result = $request->APIKeyInfo( $key );
    } catch (PhealAccessException $e) {

        // API Access Error
        echo '<div id="error" class="error"><p>Eve API access error: ', $e->getMessage() . '.</p></div>';
    } catch ( PhealAPIException $e ) {

        // API Error
        echo '<div id="error" class="error"><p>Eve API error (' . $e->code . '): ', $e->getMessage() . '.</p></div>';
    } catch (PhealException $e ) {

        // Some other kind of error
        echo '<div id="error" class="error"><p>Eve API error: ', $e->getMessage() . '.</p></div>';
    }

    if ( ! $keyType == '' ) {
        if ( ! $keyType == $result->key->type )
            return false;
    }

    if ( ! $accessMask == '' ) {
        if ( ! $accessMask == $result->key->accessMask )
            return false;
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
function evecorp_get_char_name( $character_ID ) {

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
 * Doesn't need API key authorization
 *
 * @uses loadPheal()
 * @return string. Name of the corporation
 * @param string $corporationID. ID number of the corporation to lookup.
 */
function evecorp_get_corp_name( $corporation_ID ) {

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
function evecorp_get_ID( $name ) {

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
        $result = $request->CharacterID($arguments);
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
function evecorp_is_name( $name ) {

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
function evecorp_corpsheet( $corporation_ID ) {

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
function evecorp_is_member( $character_ID, $corporation_ID ) {

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
function evecorp_is_CEO($character_ID, $corporation_ID) {

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
    } catch (PhealAPIException $e) {

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
function evecorp_is_director($character_ID, $corporation_key) {

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
function evecorp_trust_button() {
    $html = '<button type="button" onclick="CCPEVE.requestTrust(\'' . home_url() . '\');">Set ' . home_url() . ' as trusted</button>';
    return $html;
}

// Icons
function evecorp_icon( $icon ) {
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