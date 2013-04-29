<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Login functions library
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

function evecorp_login_form_labels( $defaults )
{
	$defaults['label_username']	 = 'API Key ID';
	$defaults['label_password']	 = 'Verification Code';
	return $defaults;
}

/**
 * Tests if the supplied credentials could belong to the WP admin account.
 *
 * @param type $user
 * @param type $login
 * @param type $password
 */
function evecorp_auth_admin( $user, $login, $password )
{
	if ( is_wp_error( $user ) )
		return $user;

	if ( !empty( $login ) && !empty( $password ) ) {
		$super_admins = get_super_admins();

		foreach ( $super_admins as $super_admin ) {
			if ( strtolower( $login ) == strtolower( $super_admin ) ) {
				add_filter( 'authenticate', 'wp_authenticate_username_password', 10, 3 );
			}
		}
	}
}

function evecorp_user_login( $user, $key_ID, $vcode )
{
	/* If a previous called authentication was valid, just pass it along. */
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	if ( !evecorp_corpkey_check() )
		return new WP_Error( 'missing_corpkey', '<strong>ERROR:</strong> Login with Eve Online API keys is currently disabled! ' .
				sprintf( 'Please ask your WordPress administrator to visit the <a href="%s">Eve Online settings page</a>.', admin_url( 'options-general.php?page=evecorp_settings' ) )
		);

	/* If the form has not been submitted yet. */
	if ( !isset( $_POST['wp-submit'] ) )
		return $user;

	/* Do we have a user submitted API key? */
	if ( empty( $key_ID ) || empty( $vcode ) ) {
		$error = new WP_Error();

		if ( empty( $key_ID ) )
			$error->add( 'empty_key_ID', '<strong>ERROR</strong>: You have to
				supply a API key ID.' );

		if ( empty( $vcode ) )
			$error->add( 'empty_vcode', '<strong>ERROR</strong>: You have to
				supply the verification code for your API key.' );

		return $error;
	}

	/* Authenticate the user using Eve Online API */
	$user = evecorp_authenticate_user( $user, $key_ID, $vcode );
	if ( is_wp_error( $user ) )
		return $user;

	/* Assign WP roles and capabilities from Eve Online roles and titles */
	evecorp_authorize_user( $user );
	return $user;
}

/**
 * Authenticate the user using Eve Online API.
 *
 * If this is the first time we've seen this user (based on the character name),
 * a new account will be created.
 *
 * If this is the first time we've seen this API Key (bssed on the key ID), a
 * new validation code will be created.
 *
 * Known users will have their profile data updated based on the Eve Online
 * data present.
 *
 * @return mixed WP_User object or WP_Error on failure to authenticate.
 */
function evecorp_authenticate_user( $user, $key_ID, $vcode )
{
	/* Test the submitted credentials */
	$key	 = array(
		'key_ID' => $key_ID,
		'vcode'	 => $vcode
	);
	$keyinfo = evecorp_get_keyinfo( $key );

	/**
	 * Failed to fetch keyinfo
	 *
	 * @todo Add handling for different kind of failures (e.g connection problems).
	 */
	if ( is_wp_error( $keyinfo ) )
		return $keyinfo;

	/* Is the key type for characters (and not a corp key or account key)? */
	if ( 'Character' <> $keyinfo['type'] )
		return new WP_Error( 'not_char_key', '<strong>ERROR</strong>: This API key is not a character
							key.' );

	/* Get a sanitized user login name from API key's character name */
	$user_login = sanitize_user( $keyinfo['characters'][0]['characterName'] );

	/* Is the character a member of our corporation? */
	if ( evecorp_get_option( 'corpkey_corporation_name' ) <> $keyinfo['characters'][0]['corporationName'] )
		return new WP_Error( 'not_corp_member', '<strong>ERROR</strong>: ' .
				$user_login . ' is not a member of ' .
				evecorp_get_option( 'corpkey_corporation_name' ) . '.' );

	/* Lookup account in WP users table */
	$user = get_user_by( 'login', $user_login );

	/* Create account if this is a new user */
	if ( false === $user )
		$user = evecorp_create_new_user( $user_login, $keyinfo );

	/* Account creation failed */
	if ( is_wp_error( $user ) )
		return $user;

	/* Get saved API key ID's for this character from WP users meta table */
	if ( is_a( $user, 'WP_User' ) )
		$evecorp_userkeys = get_user_meta( $user->ID, 'evecorp_userkeys', true );

	/* Are there any stored API key ID's? */
	if ( !empty( $evecorp_userkeys ) ) {

		/* Previously saved API key ID's found */
		foreach ( $evecorp_userkeys as $index => $value ) {

			/* Is there a API Key with this ID? */
			if ( $key_ID === (string) $index ) {

				/* Has it been validated? */
				if ( 'Yes' === $value['validated'] )

				/* We return a successful login */
					return $user;

				/* Get validation code hash for this key */
				$validation_hash = $value['validation_hash'];

				/* Try to validate it */
				if ( evecorp_userkey_validate( $user_login, $validation_hash ) ) {

					/* We have a successful login */
					$evecorp_userkeys[$key_ID]['validated']			 = 'Yes';
					$evecorp_userkeys[$key_ID]['expires']			 = $keyinfo['expires'];
					$evecorp_userkeys[$key_ID]['accessMask']		 = $keyinfo['accessMask'];
					$evecorp_userkeys[$key_ID]['characterName']		 = $keyinfo['characters'][0]['characterName'];
					$evecorp_userkeys[$key_ID]['characterID']		 = $keyinfo['characters'][0]['characterID'];
					$evecorp_userkeys[$key_ID]['corporationName']	 = $keyinfo['characters'][0]['corporationName'];
					update_user_meta( $user->ID, 'evecorp_userkeys', $evecorp_userkeys );

					/* Update existing account */
					evecorp_update_user( $user->ID, $keyinfo['characters'][0]['characterID'] );

					/* We return the successful login */
					return $user;
				}

				/* Key does not validate (yet) */
				$evecorp_userkeys[$key_ID]['validated']			 = 'No';
				$evecorp_userkeys[$key_ID]['expires']			 = $keyinfo['expires'];
				$evecorp_userkeys[$key_ID]['accessMask']		 = $keyinfo['accessMask'];
				$evecorp_userkeys[$key_ID]['characterName']		 = $keyinfo['characters'][0]['characterName'];
				$evecorp_userkeys[$key_ID]['characterID']		 = $keyinfo['characters'][0]['characterID'];
				$evecorp_userkeys[$key_ID]['corporationName']	 = $keyinfo['characters'][0]['corporationName'];
				update_user_meta( $user->ID, 'evecorp_userkeys', $evecorp_userkeys );
				return new WP_Error( 'awaiting_validation', 'Welcome back ' . $user_login . '.<br />This API
								key	is waiting for identity verification.<br />
								Please allow up	to 30 minutes for processing
								after payment has been made.<br />Thank you.' );
			}
		}
	}

	/* This API key ID has not been seen before */
	$validation_code = evecorp_userkey_add( $user->ID, $key_ID, $keyinfo );
	return new WP_Error( 'new_validation', 'Welcome ' . $user_login . '.<br /> As you never used this
						API key with us before, we need to verify your identity. <br />
						Please send 0.10 ISK to ' .
			evecorp_corp( evecorp_get_option( 'corpkey_corporation_name' ) ) .
			' and write the following in the reason field:<br />
					<strong><pre>Validate:' . $validation_code . '</pre></strong><br />
					Be aware that this validation code can not be displayed
					again and please allow up to 30 minutes for processing,
					after you made the payment. Thank you.' );
}

/**
 * Assign WP roles and capabilities from Eve Online roles and titles.
 *
 * @param WP_User object $user
 */
function evecorp_authorize_user( $user )
{
	/* Update this site WP roles with defined corporation titles */
	evecorp_update_title_roles();

	$character_info	 = get_user_meta( $user->ID, 'evecorp_character_info', true );
	$character_ID	 = $character_info['characterID'];

	/* Get roles for this character from Eve Online API */
	$eve_roles = evecorp_get_char_roles( $character_ID );
	if ( is_wp_error( $eve_roles ) )
		return $eve_roles;

	/* Get titles for this character from Eve Online API */
	$eve_titles = evecorp_get_char_titles( $character_ID );
	if ( is_wp_error( $eve_titles ) )
		return $eve_titles;

	/* Remove all previously assigned roles from this user */
	$user->set_role( '' );

	/* Remove all previously assigned capabilites from this user */
	$user->remove_all_caps();

	/* Are there any Roles or Titles in Eve Online for this character? */
	if ( $eve_roles > 0 || $eve_titles > 0 ) {

		/* Assign corresponding WP roles for each of his Eve Online roles */
		foreach ( $eve_roles as $eve_role ) {
			$wp_role = EVECORP_EVE_ROLE_PREFIX . substr( $eve_role, 4 );
			$user->add_role( $wp_role );
		}

		/* Assign corresponding WP roles for each of his Eve Online titles */
		foreach ( $eve_titles as $eve_title ) {
			$wp_role = EVECORP_EVE_TITLE_PREFIX . $eve_title;
			$user->add_role( $wp_role );
		}
	} else {

		/* User has no Roles or Titles in Eve Online */
		$default_role = get_option( 'default_role' );
		if ( !current_user_can( $default_role ) ) {

			/* Assign the "New User Default Role" */
			$user->set_role( $default_role );
		}
	}
}

/**
 * Create a new WordPress user account based on the Eve Online character.
 *
 * @param string $user_login login-name for the new user.
 * @param array $keyinfo Eve Online API key information.
 * @return mixed WP_User class object on success or WP_Error on failure.
 */
function evecorp_create_new_user( $user_login, $keyinfo )
{
	if ( empty( $user_login ) )
		return null;

	$character_ID	 = $keyinfo['characters'][0]['characterID'];
	$full_name		 = $keyinfo['characters'][0]['characterName'];
	$split_name		 = evecorp_split_name( $full_name );
	$user_nicename	 = sanitize_title( $user_login );

	/* Make a user URL */
	$user_url = trailingslashit( evecorp_get_option( 'char_url' ) ) .
			rawurlencode( $full_name );

	$user_data = array(
		'user_login'	 => $user_login,
		'user_pass'		 => '',
		'user_nicename'	 => $user_nicename,
		'first_name'	 => $split_name['first_name'],
		'last_name'		 => $split_name['last_name'],
		'display_name'	 => $full_name,
		'user_url'		 => $user_url,
	);

	/* Create account */
	$user_id = wp_insert_user( $user_data );
	if ( is_wp_error( $user_id ) )
		return $user_id;

	/* Get the new user object from WP users db. */
	$user = new WP_User( $user_id );

	/* Update user meta */
	update_user_meta( $user->ID, 'evecorp_character_ID', $character_ID );

	return $user;
}

/**
 * Update the user data in the WP user table with the data retrieved from the
 * Eve Online character information.
 *
 * @param string $user_ID WordPress user id.
 * @param array $keyinfo Eve Online API key informartion.
 */
function evecorp_update_user( $user_ID, $character_ID )
{

	/* Get current API character information */
	$evecorp_char_info = evecorp_get_char_info( $character_ID );
	if ( !is_wp_error( $evecorp_char_info ) ) {

		/* Update the WP users meta table */
		update_user_meta( $user_ID, 'evecorp_character_info', $evecorp_char_info );
	}
}

/**
 * Split a full name in first [middle] and last names.
 *
 * @param string $full_name
 * @param string $prefix
 * @return array
 */
function evecorp_split_name( $full_name, $prefix = '' )
{
	$pos = strrpos( $full_name, ' ' );

	if ( $pos === false ) {
		return array(
			$prefix . 'first_name'	 => $full_name,
			$prefix . 'last_name'	 => ''
		);
	}

	$first_name	 = substr( $full_name, 0, $pos + 1 );
	$last_name	 = substr( $full_name, $pos );

	return array(
		$prefix . 'first_name'	 => $first_name,
		$prefix . 'last_name'	 => $last_name
	);
}

/**
 * Add a Eve Online API key ID to a users meta data for later validation by ISK
 * payment.
 *
 * @param string $user_id WordPress user id
 * @param string $key_ID Eve Online API key ID
 * @return string Validation code for the user to use as payment-reason
 */
function evecorp_userkey_add( $user_ID, $key_ID, $keyinfo )
{
	/* Create validation code */
	$validation_code = wp_generate_password( 12, false );
	$validation_hash = wp_hash_password( $validation_code );

	/* Get saved API key ID's for this character from WP users meta table */
	$evecorp_userkeys = get_user_meta( $user_ID, 'evecorp_userkeys', true );

	/* Update the WP users meta table */
	$evecorp_userkeys[$key_ID]['validation_hash']	 = $validation_hash;
	$evecorp_userkeys[$key_ID]['validated']			 = 'No';
	$evecorp_userkeys[$key_ID]['expires']			 = $keyinfo['expires'];
	$evecorp_userkeys[$key_ID]['accessMask']		 = $keyinfo['accessMask'];
	$evecorp_userkeys[$key_ID]['characterName']		 = $keyinfo['characters'][0]['characterName'];
	$evecorp_userkeys[$key_ID]['characterID']		 = $keyinfo['characters'][0]['characterID'];
	$evecorp_userkeys[$key_ID]['corporationName']	 = $keyinfo['characters'][0]['corporationName'];
	update_user_meta( $user_ID, 'evecorp_userkeys', $evecorp_userkeys );
	return $validation_code;
}

/**
 * Test if there has been made a payment with a valid validation code from user.
 *
 * @param string $user_login Eve Online character name.
 * @param string $validation_hash Hash of the validation code.
 * @return boolean True if payment found and validation code matches. False otherwise.
 */
function evecorp_userkey_validate( $user_login, $validation_hash )
{

	/* Get corporation wallet journal */
	$journal = evecorp_corp_journal();
	foreach ( $journal as $transaction ) {

		/* Player donation? */
		if ( '10' === $transaction['refTypeID'] ) {

			/* From current WP user logging on? */
			if ( $user_login === sanitize_user( $transaction['ownerName1'] ) ) {

				/* Is there a validation code? */
				$validation_code = '';
				if ( 1 === sscanf( $transaction['reason'], 'DESC: Validate:%s', $validation_code ) ) {
					$validation_code = trim( $validation_code );

					/* Validation code match? */
					if ( wp_check_password( $validation_code, $validation_hash ) ) {
						return true;
					}
				}
			}
		}
	}
	return false;
}

/**
 * Update roles in WordPress site options with defined corporation titles.
 *
 * Eve Online roles are fixed and defined on plug-in activation.
 *
 * Eve Online titles are player defined sets of roles and are therefore
 * redefined of every login.
 *
 */
function evecorp_update_title_roles()
{
	global $wp_roles;

	/* Get all WordPress roles from site options */
	$wp_roles_list = $wp_roles->get_names();

	/* Remove all our WordPress roles defined from Eve Online titles */
	foreach ( $wp_roles_list as $wp_role => $wp_role_display_name ) {

		/* Check if its one of our title-derived roles */
		if ( EVECORP_EVE_TITLE_PREFIX === substr( $wp_role, strlen( EVECORP_EVE_TITLE_PREFIX ) ) ) {

			/* Remove this role from WordPress system */
			remove_role( $wp_role );
		}
	}

	/* Get all defined titles of our corporation */
	$corp_titles = evecorp_get_corp_titles();

	foreach ( $corp_titles as $corp_title ) {
		//var_dump( $corp_title );

		/* Does this Eve Online title have Eve Online Roles assigned? */
		if ( sizeof( $corp_title['roles'] ) > 0 ) {

			/* Init a list of WordPress capabilities */
			$title_capabilities = array( );

			/* Iterate trough those roles */
			foreach ( $corp_title['roles'] as $eve_role_of_title ) {

				/* Construct the WordPress Role ID from this Eve Online Role */
				$wp_role_ID = EVECORP_EVE_ROLE_PREFIX . substr( $eve_role_of_title['roleName'], 4 );

				/* Get the corresponding WordPress role object */
				$wp_role = get_role( $wp_role_ID );
				if ( !is_null( $wp_role ) ) {

					/* Add the capabilties of that WP role */
					array_push( $title_capabilities, $wp_role->capabilities );
				} else {

					/* Somehow no WP role found */
					print_r( 'no WP roles for ' . $wp_role_ID . ' found!' );
				}

				/* Did we find any capabilities? */
				if ( count( $title_capabilities ) > 0 ) {

					/* Construct a WordPress role ID for this title */
					$wp_role_ID = EVECORP_EVE_TITLE_PREFIX . $corp_title['titleID'];

					/* Add new WP role with all the collected capabilities */
					add_role( $wp_role_ID, $corp_title['titleName'], $title_capabilities );
				}
			}
		}
	}
}