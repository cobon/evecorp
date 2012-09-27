<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Plugin settings page in wp-admin
 *
 * @package evecorp
 */

require_once dirname( __FILE__ ) . '/admin-functions.php';

// Contextual Help text for settings page
include_once(dirname( __FILE__ ) . '/settings-help.php');

/**
 * Adds a notification message  to the top of admin pages.
 *
 */
function evecorp_config_notifiy()
{
	echo '<div class="error"><p>' . sprintf( 'Please adjust <a href="%s">subspace signal modulation</a>', admin_url( 'options-general.php?page=eve_options' ) ) . ' to connect with your station.</p></div>';
}

/**
 * Initialize form content for the plugin settings page
 *
 * @global array $evecorp_options
 */
function evecorp_admin_init()
{
	global $evecorp_options;

	// Add our settings to the settings whitelist
	register_setting( 'evecorp', 'evecorp_options', 'evecorp_validate_settings' );

	// Eve Online Corporate API Key Section
	add_settings_section( 'section_corpkey', 'API Key Information', 'corpkey_section_html', 'evecorp_settings' );
	add_settings_field( 'corpkey_ID', 'Key ID', 'corpkey_ID_formfield', 'evecorp_settings', 'section_corpkey' );

	// Do we have a API key?
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {

		add_settings_field( 'corpkey_vcode', 'Verification Code', 'corpkey_vcode_formfield', 'evecorp_settings', 'section_corpkey' );
	} else {

		add_settings_field( 'corpkey_type', 'Key type', 'evecorp_apikey_type', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_corpname', 'Created for', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $evecorp_options['corpkey_corporation_name'] );
		add_settings_field( 'corpkey_issuer', 'Created by', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $evecorp_options['corpkey_character_name'] );
		add_settings_field( 'corpkey_expires', 'Valid until', 'evecorp_apikey_expiry', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_access', 'Permissions', 'evecorp_corpkey_access', 'evecorp_settings', 'section_corpkey' );
	}

	// Eve Online API Server and Cache Section
	add_settings_section( 'section_API', 'Eve Online API Settings', 'eveapi_section_html', 'evecorp_settings' );
	add_settings_field( 'cache_API', 'Enable API Cache', 'cache_API_formfield', 'evecorp_settings', 'section_API' );

	// Out-of-game browser section
//	add_settings_section( 'section_OGB', 'Out-of-Game Browser Settings', 'OGB_section_html', 'evecorp_settings' );
//	add_settings_field( 'char_URL', 'Character Profiles URL', 'char_URL_formfield', 'evecorp_settings', 'section_OGB' );
//	add_settings_field( 'char_label', 'Character Profiles Label', 'char_label_formfield', 'evecorp_settings', 'section_OGB' );
//	add_settings_field( 'corp_URL', 'Corporation Profiles URL', 'corp_url_formfield', 'evecorp_settings', 'section_OGB' );
//	add_settings_field( 'corp_label', 'Corporation Profiles Label', 'corp_label_formfield', 'evecorp_settings', 'section_OGB' );
}

/**
 * Output description for the Eve Online Corporate API Key Section
 * @todo Provide more information about API keys and access rights, provide
 *  link to create key
 */
function corpkey_section_html()
{
	global $evecorp_IGB_data;
	echo 'CEOs or directors can create corporation keys at the ';
	if ( evecorp_is_trusted() ) {
		echo '<a href="https://support.eveonline.com/api/Key/CreatePredefined/5244936/' . $evecorp_IGB_data['charid'] . '/true"';
	} else {
		echo '<a href="https://support.eveonline.com/api/"';
	}
	echo ' target="_BLANK">Eve Online Support website</a>.</p>';
}

/**
 * Output the the formfield for the API key ID
 *
 * @global array $evecorp_options
 */
function corpkey_ID_formfield()
{
	global $evecorp_options;

	// Do we have a API key?
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {
		echo "<input id='corpkey_ID' name='evecorp_options[corpkey_ID]' type='text' value='{$evecorp_options['corpkey_ID']}'";

		// Make it read-only if defined as constant in wp-config.php
		if ( defined( 'EVECORP_CORPKEY_ID' ) )
			echo ' disabled="disabled" class="regular-text code disabled"';
		echo " >\r\n";
	} else {
		?>
		<a href='https://support.eveonline.com/api/Key/Update/<?php echo $evecorp_options['corpkey_ID']; ?>'
		   title='Update this API Key at Eve Online Support' target='_BLANK'>
			<?php echo $evecorp_options['corpkey_ID']; ?></a>
		<?php
		echo evecorp_icon( 'yes' );
	}
}

/**
 * Output the the formfield for the API key verification code
 *
 * @global array $evecorp_options
 */
function corpkey_vcode_formfield()
{
	global $evecorp_options;

	echo "<textarea id='corpkey_vcode' name='evecorp_options[corpkey_vcode]' cols='32' rows='2'";

	// Make it read-only if defined as constant in wp-config.php
	if ( defined( 'EVECORP_CORPKEY_VCODE' ) )
		echo ' disabled="disabled" class="regular-text readonly"';
	echo " >{$evecorp_options['corpkey_vcode']}</textarea>";
	echo '<p class="description">Verification codes usually have 64 characters.</p>';
}

/**
 * Output the given string.
 *
 * This functions exists only because echo() and print() can not be registred as
 * callback functions.
 *
 * @param string $str String to output
 */
function evecorp_print( $str )
{
	echo ($str);
}

function evecorp_corpkey_access()
{
	global $evecorp_options;

	$key = array(
		'key_ID'		 => $evecorp_options['corpkey_ID'],
		'vcode'			 => $evecorp_options['corpkey_vcode'],
	);
	$access_tests	 = array(
		'WalletJournal',
		'Titles',
		'MemberTracking',
		'CorporationSheet'
	);
	foreach ( $access_tests as $api_name ) {
		echo $api_name . ' ';
		$result = evecorp_is_valid_key( $key, $evecorp_options['corpkey_type'], 'corp', $api_name, $evecorp_options['corpkey_access_mask'] );
		if ( is_wp_error( $result ) ) {
			echo evecorp_icon( 'no' );
			//add_settings_error( 'evecorp_settings', 'section_corpkey', $result->get_error_message(), 'error' );
			var_dump( $result );
		} else {
			echo evecorp_icon( 'yes' );
		}
		echo '<br />';
	}
}

/**
 * Output the type of API key
 *
 * @global array $evecorp_options
 */
function evecorp_apikey_type()
{
	global $evecorp_options;

	echo $evecorp_options['corpkey_type'] . ' ';
	if ( 'Corporation' === $evecorp_options['corpkey_type'] ) {
		echo evecorp_icon( 'yes' );
	} else {
		echo evecorp_icon( 'no' );
	}
}

/**
 * Output the expiry date of API key
 *
 * @global array $evecorp_options
 */
function evecorp_apikey_expiry()
{
	global $evecorp_options;
	$unixtime = strtotime( $evecorp_options['corpkey_expires'] );
	echo date_i18n( get_option( 'date_format' ), $unixtime );
	if ( $unixtime > time() ) {
		echo ' (' . human_time_diff( time(), $unixtime ) . ' from now). ' . evecorp_icon( 'yes' );
	} else {
		echo ' (expired ' . human_time_diff( $unixtime, time() ) . ' ago). ' . evecorp_icon( 'no' );
	}
}

/**
 * Output description for the Eve Online API section
 *
 */
function eveapi_section_html()
{
	echo '<p>Eve Online API Section Description.</p>';
}

/**
 * Output form field for the cache API setting
 *
 * @global array $evecorp_options
 */
function cache_API_formfield()
{
	global $evecorp_options;
	echo "<input id='cache_API' name='evecorp_options[cache_API]' type='checkbox' ";
	if ( $evecorp_options['cache_API'] )
		echo "checked='checked'";
	echo "class='code' /> ";
	echo '<span class="description">Store API data for repeated requests (recommended).</span>';
}

/**
 * Output description for the out-of-game browser section
 *
 */
function ogb_section_html()
{
	echo '<p>Eve Online Out-of-Game Browser Section Description.</p>';
}

/**
 * Validates the input fields of the settings form
 *
 * @global array $evecorp_options
 * @param array $input The user submitted values from the input form fields
 * @return array Validated options
 */
function evecorp_validate_settings( $input )
{

	global $evecorp_options;

	// Do we have a API key in options?
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {

		// Sanitize User input fields
		$input['corpkey_ID']	 = sanitize_text_field( $input['corpkey_ID'] );
		$input['corpkey_vcode']	 = sanitize_text_field( $input['corpkey_vcode'] );

		// Do we have a user submitted API key?
		if ( !empty( $input['corpkey_ID'] ) && !empty( $input['corpkey_vcode'] ) ) {

			// We have key ID and vcode from form submission.
			$key = array(
				'key_ID' => $input['corpkey_ID'],
				'vcode'	 => $input['corpkey_vcode']
			);

			$evecorp_options['corpkey_ID']		 = $input['corpkey_ID'];
			$evecorp_options['corpkey_vcode']	 = $input['corpkey_vcode'];
		} else {

			// We don't have key ID and vcode.
			add_settings_error( 'evecorp_settings', 'section_corpkey', 'Please supply API key and verification code.', 'error' );
			return $evecorp_options;
		}
	} else {

		$key = array(
			'key_ID' => $evecorp_options['corpkey_ID'],
			'vcode'	 => $evecorp_options['corpkey_vcode']
		);
	}

	$keyinfo = evecorp_get_keyinfo( $key );
	if ( is_wp_error( $keyinfo ) ) {

		// Failed to fetch keyinfo
		/**
		 * @todo Add handling for different kind of failures (e.g connection problems).
		 */
		add_settings_error( 'evecorp_settings', 'section_corpkey', $keyinfo->get_error_message(), 'error' );

		// Key fails, remove it
		unset( $evecorp_options['corpkey_ID'] );
		unset( $evecorp_options['corpkey_vcode'] );
		return $evecorp_options;
	} else {

		// Store key information in options
		$evecorp_options['corpkey_type']			 = $keyinfo['type'];
		$evecorp_options['corpkey_access_mask']		 = $keyinfo['accessMask'];
		$evecorp_options['corpkey_expires']			 = $keyinfo['expires'];
		$evecorp_options['corpkey_character_name']	 = $keyinfo['characters'][0]['characterName'];
		$evecorp_options['corpkey_corporation_name'] = $keyinfo['characters'][0]['corporationName'];
	}

	// Check if key and vcode are usable for our requests
	$access_tests = array(
		'WalletJournal',
		'Titles',
		'MemberTracking',
		'CorporationSheet'
	);
	$access_errors = 0;
	foreach ( $access_tests as $api_name ) {
		$result = evecorp_is_valid_key( $key, $evecorp_options['corpkey_type'], 'corp', $api_name, $evecorp_options['corpkey_access_mask'] );
		if ( is_wp_error( $result ) ) {
			$access_errors++;
			//add_settings_error( 'evecorp_settings', 'section_corpkey', $result->get_error_message(), 'error' );
		}
	}

	if ( $access_errors ) {
		add_settings_error( 'evecorp_settings', 'section_corpkey', 'There are problems with this API key.', 'error' );
	} else {

		// If we reach here, out API key has passed all the API query tests.
		add_settings_error( 'evecorp_settings', 'section_corpkey', 'Your API key has been verified.', 'updated' );
	}
	return $evecorp_options;
}

/**
 * Add admin menu entry for Eve Online
 *
 * @global type $evecorp_settings_page_hook
 */
function evecorp_add_settings_menu()
{

	// Hook to screen for this page, used for contextual help.
	global $evecorp_settings_page_hook;

	$page_title	 = 'Eve Online Settings';
	$menu_title	 = 'Eve Online';
	$capability	 = 'read';

	// Save the page hook for use by contextual help later
	$evecorp_settings_page_hook = add_options_page( $page_title, $menu_title, $capability, 'evecorp_settings', 'evecorp_settings_page' );

	// Add a contextual help tab to the admin page
	add_action( 'load-' . $evecorp_settings_page_hook, 'evecorp_settings_help' );
}

/**
 * Create the admin page for Eve Online settings
 */
function evecorp_settings_page()
{
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2>Eve Online Settings</h2>
		<form method="post" action="options.php">

			<!-- Nonce, action, and option_page fields for a settings page. -->

			<?php settings_fields( 'evecorp' ); ?>

			<!-- Settings Sections Start  -->

			<?php do_settings_sections( 'evecorp_settings' ); ?>

			<!-- Settings Sections End  -->

			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Verify Key" />
			</p>
		</form>
	</div>
	<?php
}
