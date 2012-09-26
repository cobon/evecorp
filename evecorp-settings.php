<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Plugin settings page in wp-admin
 *
 * @package evecorp
 */

require_once dirname( __FILE__ ) . '/admin-functions.php';

// Help text
include_once(dirname( __FILE__ ) . '/settings-help.php');

/** Adds a notification message  to the top of admin pages.
 *
 */
function evecorp_config_notifiy()
{
	echo '<div class="error"><p>' . sprintf( 'Please adjust <a href="%s">subspace signal modulation</a>', admin_url( 'options-general.php?page=eve_options' ) ) . ' to connect with your station.</p></div>';
}

/**
 * Initialize form content for the plugin settings page
 *
 * Defines a group of option settings for whitelisting and validation function
 * Defines sections for breaking settings in to thematic groups on page and a
 *  function which will generate the ouptupt.
 * Defines each individual setting, along with name, description and a function for output
 */
function evecorp_admin_init()
{
	// Add our settings to the settings whitelist
	register_setting( 'evecorp', 'evecorp_options', 'evecorp_validate_settings' );

	// Eve Online Corporate API Key Section
	if ( array_key_exists( 'corpkey_keyinfo', get_option( 'evecorp_options' ) ) ) {
		$keyinfo = evecorp_get_option( 'corpkey_keyinfo' );
		add_settings_section( 'section_corpkey', 'API Key Information', 'corpkey_section_html', 'evecorp_settings' );
		add_settings_field( 'corpkey_ID', 'Key ID', 'corpkey_ID_formfield', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_type', 'Key type', 'evecorp_apikey_type', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_corpname', 'Created for', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $keyinfo['characters'][0]['corporationName'] );
		add_settings_field( 'corpkey_issuer', 'Created by', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $keyinfo['characters'][0]['characterName'] );
		add_settings_field( 'corpkey_expires', 'Valid until', 'evecorp_apikey_expiry', 'evecorp_settings', 'section_corpkey', $keyinfo['expires'] );
		add_settings_field( 'corpkey_access', 'Access Permissions', 'evecorp_corpkey_access', 'evecorp_settings', 'section_corpkey' );
	} else {
		add_settings_section( 'section_corpkey', 'API Key Registration', 'corpkey_section_html', 'evecorp_settings' );
		add_settings_field( 'corpkey_ID', 'Key ID', 'corpkey_ID_formfield', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_vcode', 'Verification Code', 'corpkey_vcode_formfield', 'evecorp_settings', 'section_corpkey' );
	}

	// Eve Online API Server and Cache Section
	add_settings_section( 'section_API', 'Eve Online API Settings', 'eveapi_section_html', 'evecorp_settings' );
	add_settings_field( 'cache_API', 'Enable API Cache', 'cache_API_formfield', 'evecorp_settings', 'section_API' );


//	// Out-of-game browser section
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
		echo '<a href="https://support.eveonline.com/api/Key/CreatePredefined/5244936/' . $evecorp_IGB_data['charid'] . '/true" target="_BLANK">Eve Online Support website</a>.</p>';
	} else {
		echo '<a href="https://support.eveonline.com/api/" target="_BLANK">Eve Online Support website</a>.</p>';
	}
}

function corpkey_ID_formfield()
{
	$corpkey_ID = evecorp_get_option( 'corpkey_ID' );
	if ( array_key_exists( 'corpkey_keyinfo', get_option( 'evecorp_options' ) ) ) {
		echo '<a href="https://support.eveonline.com/api/Key/Update/' . $corpkey_ID . '" title="Update this API Key at Eve Online Support" target="_BLANK">' . $corpkey_ID . '</a> ' . evecorp_icon( 'yes' );
	} else {
		echo "<input id='corpkey_ID' name='evecorp_options[corpkey_ID]' type='text' value='{$corpkey_ID}'";
		if ( defined( 'EVECORP_CORPKEY_ID' ) )
			echo ' disabled="disabled" class="regular-text code disabled"';
		echo ' >';
		echo '<p class="description">The ID number of your corporate API key.</p>';
	}
}

function corpkey_vcode_formfield()
{
	$corpkey_vcode = evecorp_get_option( 'corpkey_vcode' );
	echo "<textarea id='corpkey_vcode' name='evecorp_options[corpkey_vcode]' cols='32' rows='2'";
	if ( defined( 'EVECORP_CORPKEY_VCODE' ) )
		echo ' disabled="disabled" class="regular-text readonly"';
	echo " >{$corpkey_vcode}</textarea>";
	echo '<p class="description">The verification code for your corporate API key (usually 64 characters long).</p>';
}

function evecorp_print( $str )
{
	echo ($str);
}

function evecorp_corpkey_access()
{
	$key = array(
		'key_ID' => evecorp_get_option( 'corpkey_ID' ),
		'vcode' => evecorp_get_option( 'corpkey_vcode' )
	);
	$keyinfo = evecorp_get_option( 'corpkey_keyinfo' );
	extract( $keyinfo );

	$test_cases = array(
		'WalletJournal',
		'Titles',
		'MemberTracking',
		'CorporationSheet'
	);
	foreach ( $test_cases as $api_name ) {
		echo $api_name . ' ';
		$result = evecorp_is_valid_key( $key, $type, 'corp', $api_name, $accessMask );
		if ( is_wp_error( $result ) ) {
			echo evecorp_icon( 'no' );
		} else {
			echo evecorp_icon( 'no' );
		}
		echo '<br />';
	}
}

function evecorp_apikey_type()
{
	$keyinfo = evecorp_get_option( 'corpkey_keyinfo' );
	$key_type = $keyinfo['type'];
	echo ($key_type) . ' ';
	if ( 'Corporation' === $key_type ) {
		echo evecorp_icon( 'yes' );
	} else {
		echo evecorp_icon( 'no' );
	}
}

function evecorp_apikey_expiry( $timestr )
{
	$unixtime = strtotime( $timestr );
	echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $unixtime );
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

function cache_API_formfield()
{
	$cache_API = evecorp_get_option( 'cache_API' );
	echo "<input id='cache_API' name='evecorp_options[cache_API]' type='checkbox' ";
	if ( $cache_API )
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
 * @param array $input The values from the input form fields
 *
 * @return array Sanitized values from the input form fields
 */
function evecorp_validate_settings( $input )
{

	// Debugging helper code: Emergency remove of keyinfo from options.
//	unset( $options['corpkey_keyinfo'] );
//	$options['corpkey_verified'] = false;
//	return $options;


	// Note that in this special case, we don't use evecorp_get_options() or
	// evecorp_init_options().
	$options = get_option( 'evecorp_options' );

	// Get API key information from options if there are any.
	if ( !array_key_exists( 'corpkey_keyinfo', $options ) ) {

		// Sanitize User input fields
		$input['corpkey_ID'] = sanitize_text_field( $input['corpkey_ID'] );
		$input['corpkey_vcode'] = sanitize_text_field( $input['corpkey_vcode'] );

		if ( $input['corpkey_ID'] <> '' && $input['corpkey_vcode'] <> '' ) {

			// We have key ID and vcode from form submission.
			$key = array(
				'key_ID' => $input['corpkey_ID'],
				'vcode' => $input['corpkey_vcode']
			);
			$options['corpkey_ID'] = $input['corpkey_ID'];
			$options['corpkey_vcode'] = $input['corpkey_vcode'];

			} elseif ( ( evecorp_get_option( 'corpkey_ID' ) <> '' && evecorp_get_option( 'corpkey_vcode' ) <> '' ) ) {

			// We have key ID and vcode from previously saved options or constant.
			$key = array(
				'key_ID' => evecorp_get_option( 'corpkey_ID' ),
				'vcode' => evecorp_get_option( 'corpkey_vcode' )
			);
		} else {

			// We don't have key ID and vcode.
			add_settings_error( 'evecorp_settings', 'section_corpkey', 'Please supply API key and verification code.', 'error' );
			unset( $options['corpkey_keyinfo'] );
			$options['corpkey_verified'] = false;
			return $options;
		}
	}

	// Get (or refresh) API key information
	$key = array(
		'key_ID' => evecorp_get_option( 'corpkey_ID' ),
		'vcode' => evecorp_get_option( 'corpkey_vcode' )
	);
	$keyinfo = evecorp_get_keyinfo( $key );
	if ( is_wp_error( $keyinfo ) ) {

		// Failed to fetch keyinfo
		add_settings_error( 'evecorp_settings', 'section_corpkey', $keyinfo->get_error_message(), 'error' );

		// Key fails, remove keyinfo from options
		unset( $options['corpkey_keyinfo'] );
		$options['corpkey_verified'] = false;
		return $options;
	} else {

		// Store keyinfo in options
		$options['corpkey_keyinfo'] = $keyinfo;
	}

	// Check if key and vcode are usable for our requests
	$access_mask = $options['corpkey_keyinfo']['accessMask'];
	$test_cases = array(
		'WalletJournal',
		'Titles',
		'MemberTracking',
		'CorporationSheet'
	);
	foreach ( $test_cases as $api_name ) {
		$result = evecorp_is_valid_key( $key, 'Corporation', 'corp', $api_name, $access_mask );
		if ( is_wp_error( $result ) ) {
			//add_settings_error( 'evecorp_settings', 'section_corpkey', $result->get_error_message(), 'error' );
			$options['corpkey_verified'] = false;
		}
	}

	if ( ! true === $options['corpkey_verified'] ) {

		$options['corpkey_verified'] = true;
		add_settings_error( 'evecorp_settings', 'section_corpkey', 'There are problems with your API key.', 'error' );

	} else {

		// If we reach here, out API key has passed all the API query tests.
		$options['corpkey_ID'] = $input['corpkey_ID'];
		$options['corpkey_vcode'] = $input['corpkey_vcode'];
		$options['corpkey_keyinfo'] = $keyinfo;
		$options['corpkey_verified'] = true;
		add_settings_error( 'evecorp_settings', 'section_corpkey', 'Your API key has been verified. Happy blogging!', 'updated' );
	}
	return $options;
}

// Add config menu entry for Eve Online
function evecorp_add_settings_menu()
{

	// Hook to screen for this page, used for contextual help.
	global $evecorp_settings_page_hook;

	$page_title = 'Eve Online Settings';
	$menu_title = 'Eve Online';
	$capability = 'read';

	// Save the page hook for use by contextual help later
	$evecorp_settings_page_hook = add_options_page( $page_title, $menu_title, $capability, 'evecorp_settings', 'evecorp_settings_page' );

	// Add a contextual help tab to the admin page
	add_action( 'load-' . $evecorp_settings_page_hook, 'evecorp_settings_help' );
}

// Create the admin page for Eve Online settings
function evecorp_settings_page()
{
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2>Eve Online Settings</h2>
		<form method="post" action="options.php">

			<!-- Output nonce, action, and option_page fields for a settings page. -->

	<?php settings_fields( 'evecorp' ); ?>

			<!-- Print out all settings sections added to a particular settings page.  -->

	<?php do_settings_sections( 'evecorp_settings' ); ?>

			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Verify Key" />
			</p>
		</form>
	</div>
	<?php
}

// Configuration admin interface
function OLDwp_evecorp_adminpage()
{
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2>Eve Online Settings</h2>
		<p>

	<?php
// TODO: Needs work
	if ( !evecorp_igb_access() )
		echo "It is strongly recommended to access the configuration settings with the In-Game browser and have this server set as trusted.";
	elseif ( !wp_evecorp_igb_trusted() )
		echo "It is strongly recommended to set this server as trusted before accessing the configuration settings.";
	else {
		echo "Welcome " . $_SERVER["HTTP_EVE_CHARNAME"] . ". ";
	}
	?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'wp_evecorp' ); ?>

			<?php do_settings_sections( 'wp_evecorp_corp_section' ); ?>
			<h3>Corporation Settings</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="wp_evecorp_corp_name">Corporation Name</label>
					</th>
					<td>
						<input name="wp_evecorp_corp_name" id="wp_evecorp_corp_name"
							   type="text"
							   value="<?php echo get_option( 'wp_evecorp_corp_name' ); ?>"
							   class="regular-text" />
						<p class="description">The name of your Eve Online player corporation.</p>
					</td>
				</tr>
			</table>

	<?php do_settings_sections( 'wp_evecorp_corpkey_section' ); ?>
			<h3>Corporate API Key</h3>
			<p>Optional. If you supply a corporate API key, additional features and
				information can be provided to your corporation members.</p>

			<p>You can create this key
				<a href="http://support.eveonline.com/api/Key/CreatePredefined/2048/<?php echo $_SERVER["HTTP_EVE_CHARID"]; ?>/true" target="_BLANK">here</a>.
				<strong>Note</strong>: Only the CEO and directors can create corporation keys.
			</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="wp_evecorp_corp_apikey_id">Corporation Key ID</label>
					</th>
					<td>
						<input name="wp_evecorp_corp_apikey_id"
							   type="text" id="wp_evecorp_corp_apikey_id"
							   value="<?php echo get_option( 'wp_evecorp_corp_apikey_id' ); ?>" />
						<p class="description">The ID number of your corporate API key.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="wp_evecorp_corp_apikey_code">Corporation Key Veryfication Code</label>
					</th>
					<td>
						<textarea name="wp_evecorp_corp_apikey_code" cols=32 rows=2
								  id="wp_evecorp_corp_apikey_code"
								  value="<?php echo get_option( 'wp_evecorp_corp_apikey_code' ); ?>"></textarea>
						<p class="description">The verfication code for your corporate API key (usually 64 characters long).</p>
					</td>
				</tr>
			</table>

			<h3>External Browser Links</h3>
			<p>In-game characters, corporations and other items are interactible if your website is accessed with the In-Game webbrowser.</p>
			<p>With the following URLs and labels, you can define alternative weblinks for out-of-game browsers.</p>
			<h4>Character Profiles</h4>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="wp_evecorp_char_url">URL</label></th>
					<td>
						<input name="wp_evecorp_char_url" id="wp_evecorp_char_url"
							   type="text"
							   value="<?php echo get_option( 'wp_evecorp_char_url' ); ?>"
							   class="regular-text" />
						<p class="description">Address of the website for player characters (the character's name will be appended).</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="wp_evecorp_char_url_label">Label</label></th>
					<td>
						<input name="wp_evecorp_char_url_label" id="wp_evecorp_char_url_label"
							   type="text"
							   value="<?php echo get_option( 'wp_evecorp_char_url_label' ); ?>"
							   class="regular-text" />
						<p class="description">Name of website (displayed alongside the character name when the mouse hovers over the link).</p>
					</td>
				</tr>
			</table>
			<h4>Corporations</h4>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="wp_evecorp_corp_url">URL</label></th>
					<td>
						<input name="wp_evecorp_corp_url" id="wp_evecorp_corp_url"
							   type="text"
							   value="<?php echo get_option( 'wp_evecorp_corp_url' ); ?>"
							   class="regular-text" />
						<p class="description">Address of website for player corporations (the corporation name will be appended).</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="wp_evecorp_corp_url_label">Label</label></th>
					<td>
						<input name="wp_evecorp_corp_url_label" id="wp_evecorp_corp_url_label"
							   type="text"
							   value="<?php echo get_option( 'wp_evecorp_corp_url_label' ); ?>"
							   class="regular-text"/>
						<p class="description">Name of the website (displayed alongside the corporation name when the mouse hovers over the link).</p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="Submit" value="Save changes" />
			</p>
		</form>
	</div>
	<?php
}