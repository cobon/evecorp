<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Plugin settings page in wp-admin
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/* Contextual Help text for settings page */
include_once(EVECORP_PLUGIN_DIR . '/settings-help.php');

/**
 * Initialize form content for the plugin settings page
 *
 * @global array $evecorp_options
 */
function evecorp_admin_init()
{
	global $evecorp_options;

	/* Add our settings to the settings whitelist */
	register_setting( 'evecorp', 'evecorp_options', 'evecorp_validate_settings' );

	/* Eve Online Corporate API Key Section */
	add_settings_section( 'section_corpkey', 'API Key Information', 'corpkey_section_html', 'evecorp_settings' );
	add_settings_field( 'corpkey_ID', 'Key ID', 'corpkey_ID_formfield', 'evecorp_settings', 'section_corpkey' );

	/* Do we have a API key? */
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {

		add_settings_field( 'corpkey_vcode', 'Verification Code', 'corpkey_vcode_formfield', 'evecorp_settings', 'section_corpkey' );
	} else {

		add_settings_field( 'corpkey_type', 'Key type', 'evecorp_apikey_type', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_corpname', 'Created for', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $evecorp_options['corpkey_corporation_name'] );
		add_settings_field( 'corpkey_issuer', 'Created by', 'evecorp_print', 'evecorp_settings', 'section_corpkey', $evecorp_options['corpkey_character_name'] );
		add_settings_field( 'corpkey_expires', 'Valid until', 'evecorp_apikey_expiry', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_access', 'Permissions', 'evecorp_corpkey_access', 'evecorp_settings', 'section_corpkey' );
		add_settings_field( 'corpkey_url', 'Site Address (URL)', 'evecorp_apikey_url', 'evecorp_settings', 'section_corpkey' );
	}
}

/**
 * Output description for the Eve Online Corporate API Key Section
 *
 * @global array $evecorp_IGB_data
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

	/* Do we have a API key? */
	$field_value = form_option( $evecorp_options['corpkey_ID'] );
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {
		echo "<input id='corpkey_ID' name='evecorp_options[corpkey_ID]' type='text' value='{$field_value}'";

		/* Make it read-only if defined as constant in wp-config.php */
		if ( defined( 'EVECORP_CORPKEY_ID' ) )
			echo ' disabled="disabled" class="regular-text code disabled"';
		echo " >\r\n";
	} else {
		?>
		<a href='https://support.eveonline.com/api/Key/Update/<?php echo $evecorp_options['corpkey_ID']; ?>'
		   title='Update this API Key at Eve Online Support' target='_BLANK'>
			<?php echo $evecorp_options['corpkey_ID']; ?></a>
		<?php
		echo evecorp_icon( 'yes' ) . PHP_EOL;
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

	$field_value = form_option( $evecorp_options['corpkey_vcode'] );
	echo "<textarea id='corpkey_vcode' name='evecorp_options[corpkey_vcode]' cols='32' rows='2'";

	/* Make it read-only if defined as constant in wp-config.php */
	if ( defined( 'EVECORP_CORPKEY_VCODE' ) )
		echo ' disabled="disabled" class="regular-text readonly"';
	echo " >{$field_value}</textarea>" . PHP_EOL;
	echo '<p class="description">Verification codes usually have 64 characters.</p>' . PHP_EOL;
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
	echo ($str) . PHP_EOL;
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
		'MemberSecurity',
		'CorporationSheet'
	);
	foreach ( $access_tests as $api_name ) {
		echo $api_name . ' ';
		$result = evecorp_is_valid_key( $key, $evecorp_options['corpkey_type'], 'corp', $api_name, $evecorp_options['corpkey_access_mask'] );
		if ( is_wp_error( $result ) ) {
			echo evecorp_icon( 'no' );
		} else {
			echo evecorp_icon( 'yes' );
		}
		echo '<br />' . PHP_EOL;
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
	echo PHP_EOL;
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
		echo ' (' . evecorp_human_time_diff( time(), $unixtime ) . ' from now). ' . evecorp_icon( 'yes' );
	} else {
		echo ' (expired ' . evecorp_human_time_diff( $unixtime, time() ) . ' ago). ' . evecorp_icon( 'no' );
	}
	echo PHP_EOL;
}

/**
 * Compare the current WP site url with the one published Eve Online corporation.
 *
 * @global array $evecorp_options
 */
function evecorp_apikey_url()
{
	global $evecorp_options;
	$corp_url = evecorp_get_corp_url( $evecorp_options['corpkey_corporation_id'] );
	if ( site_url() == untrailingslashit( $corp_url ) ) {
		echo 'This website address (' . site_url() . ') matches the coporation URL (' . $corp_url . ').' . evecorp_icon( 'yes' );
	} else {
		echo 'This website address (' . site_url() . ') does not match the coporation URL (' . $corp_url . ').' . evecorp_icon( 'no' );
	}
}

/**
 * Output HTML for a clickable button to remove API key in a HTML form.
 *
 */
function evecorp_apikey_clear_button()
{
	$other_attributes = array(
		'onclick' => "return confirm('Are you sure?')"
	);
	submit_button( 'Remove API Key', 'delete', 'evecorp_options[corpkey_remove]', false, $other_attributes );
	echo PHP_EOL;
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

	if ( !isset( $_POST ) )
		return;

	/* Do we have a API key in options? */
	if ( empty( $evecorp_options['corpkey_ID'] ) ) {

		/* Sanitize User input fields */
		$input['corpkey_ID']	 = sanitize_text_field( $input['corpkey_ID'] );
		$input['corpkey_vcode']	 = sanitize_text_field( $input['corpkey_vcode'] );

		/* Do we have a user submitted API key? */
		if ( !empty( $input['corpkey_ID'] ) && !empty( $input['corpkey_vcode'] ) ) {

			/* We have key ID and vcode from form submission. */
			$key = array(
				'key_ID' => $input['corpkey_ID'],
				'vcode'	 => $input['corpkey_vcode']
			);

			$evecorp_options['corpkey_ID']		 = $input['corpkey_ID'];
			$evecorp_options['corpkey_vcode']	 = $input['corpkey_vcode'];
		} else {

			/* We don't have key ID and vcode. */
			add_settings_error( 'evecorp_settings', 'section_corpkey', 'Please supply API key and verification code.', 'error' );
			return $evecorp_options;
		}
	} else {

		/* Was the Clear-Key button clicked? */
		if ( is_array( $input ) ) {
			if ( $input['corpkey_remove'] == 'Remove API Key' ) {

				/* Remove it */
				unset( $evecorp_options['corpkey_ID'] );
				unset( $evecorp_options['corpkey_vcode'] );
				$evecorp_options['corpkey_verified'] = false;
				add_settings_error( 'evecorp_settings', 'section_corpkey', 'API key information cleared.', 'updated' );
				return $evecorp_options;
			}
		}
		$key								 = array(
			'key_ID' => $evecorp_options['corpkey_ID'],
			'vcode'	 => $evecorp_options['corpkey_vcode']
		);
	}

	$keyinfo = evecorp_get_keyinfo( $key );
	if ( is_wp_error( $keyinfo ) ) {

		/**
		 * Failed to fetch keyinfo
		 *
		 * @todo Add handling for different kind of failures (e.g connection problems).
		 */
		add_settings_error( 'evecorp_settings', 'section_corpkey', $keyinfo->get_error_message(), 'error' );

		/* Key fails, remove it */
		unset( $evecorp_options['corpkey_ID'] );
		unset( $evecorp_options['corpkey_vcode'] );
		$evecorp_options['corpkey_verified'] = false;
		return $evecorp_options;
	} else {

		/* Store key information in options */
		$evecorp_options['corpkey_type']			 = $keyinfo['type'];
		$evecorp_options['corpkey_access_mask']		 = $keyinfo['accessMask'];
		$evecorp_options['corpkey_expires']			 = $keyinfo['expires'];
		$evecorp_options['corpkey_character_name']	 = $keyinfo['characters'][0]['characterName'];
		$evecorp_options['corpkey_corporation_name'] = $keyinfo['characters'][0]['corporationName'];
		$evecorp_options['corpkey_corporation_id']	 = $keyinfo['characters'][0]['corporationID'];
	}

	/* Check if key and vcode are usable for our requests */
	$access_tests = array(
		'WalletJournal',
		'Titles',
		'MemberTracking',
		'MemberSecurity',
		'CorporationSheet'
	);
	$access_errors = 0;
	foreach ( $access_tests as $api_name ) {
		$result = evecorp_is_valid_key( $key, $evecorp_options['corpkey_type'], 'corp', $api_name, $evecorp_options['corpkey_access_mask'] );
		if ( is_wp_error( $result ) ) {
			$access_errors++;
//			add_settings_error( 'evecorp_settings', 'section_corpkey', $result->get_error_message(), 'error' );
		}
	}

	if ( $access_errors ) {
		$evecorp_options['corpkey_verified'] = false;
		add_settings_error( 'evecorp_settings', 'section_corpkey', 'There are problems with this API key.', 'error' );
	} else {

		/* If we reach here, out API key has passed all the API query tests. */
		$evecorp_options['corpkey_verified'] = true;
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

	/* Hook to screen for this page, used for contextual help. */
	global $evecorp_settings_page_hook;

	$page_title	 = 'Eve Online Settings';
	$menu_title	 = 'Eve Online';
	$capability	 = 'manage_options';

	/* Save the page hook for use by contextual help later */
	$evecorp_settings_page_hook = add_options_page( $page_title, $menu_title, $capability, 'evecorp_settings', 'evecorp_settings_page' );

	/* Add a contextual help tab to the admin page */
	add_action( 'load-' . $evecorp_settings_page_hook, 'evecorp_settings_help' );
}

/**
 * Create the admin page for Eve Online settings
 *
 * @global array $evecorp_options
 */
function evecorp_settings_page()
{
	global $evecorp_options;
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
				<?php
				if ( empty( $evecorp_options['corpkey_ID'] ) ) {
					submit_button( 'Verify Key', 'primary', 'submit', false );
				} else {
					submit_button( 'Verify API Key', 'primary', 'submit', false );
					echo '&nbsp;' . PHP_EOL;
					evecorp_apikey_clear_button();
					echo PHP_EOL;
				}
				?>
			</p>
		</form>
	</div>
	<?php
}