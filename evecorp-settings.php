<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Plugin settings page in wp-admin
 *
 * @package evecorp
 */

require_once dirname( __FILE__ ) . '/admin-functions.php';

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
	global $wp_settings_sections, $wp_settings_fields;
	// Add our settings to the settings whitelist
	register_setting( 'evecorp', 'evecorp_options', 'evecorp_validate_settings' );

	// Eve Online Corporate API Key Section
	$section_id = 'section_corpkey';
	$section_title = 'Corporation API Key';
	$section_callback = 'corpkey_section_html';
	add_settings_section( $section_id, $section_title, $section_callback, 'evecorp_settings' );

	$field_id = 'corpkey_ID';
	$field_title = 'Corporation Key ID';
	$field_callback = 'corpkey_ID_formfield';
	add_settings_field( $field_id, $field_title, $field_callback, 'evecorp_settings', $section_id );

	$field_id = 'corpkey_vcode';
	$field_title = 'Corporation Key Verification Code';
	$field_callback = 'corpkey_vcode_formfield';
	add_settings_field( $field_id, $field_title, $field_callback, 'evecorp_settings', $section_id );

	// Eve Online API Server and Cache Section
	$section_id = 'section_API';
	$section_title = 'Eve Online API Settings';
	$section_callback = 'eveapi_section_html';
	add_settings_section( $section_id, $section_title, $section_callback, 'evecorp_settings' );

	$field_id = 'cache_API';
	$field_title = 'Cache data from Eve Online';
	$field_callback = 'cache_API_formfield';
	add_settings_field( $field_id, $field_title, $field_callback, 'evecorp_settings', $section_id );

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
	echo '<p>Provide Key ID and verification code of your Eve Online corporate API key</p>';
	echo '<p>You can create your corporate key';
	echo '<a href="http://support.eveonline.com/api/Key/CreatePredefined/2048/' . $_SERVER["HTTP_EVE_CHARID"] . '/true" target="_BLANK">here</a>.';
	echo '<strong>Note</strong>: Only the CEO and directors can create corporation keys.';
	echo '</p>';
}

function corpkey_ID_formfield()
{
	$options = evecorp_get_option( 'corpkey_ID' );
	echo "<input id='corpkey_ID' name='evecorp_options[corpkey_ID]' type='text' value='{$corpkey_ID}' />";
	echo '<p class="description">The ID number of your corporate API key.</p>';
}

function corpkey_vcode_formfield()
{
	$options = get_option( 'evecorp_options' );
	echo "<textarea id='corpkey_vcode' name='evecorp_options[corpkey_vcode]' cols=32 rows=2 value='{$options['corpkey_vcode']}'></textarea>";
	echo '<p class="description">The verification code for your corporate API key (usually 64 characters long).</p>';
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
	$options = get_option( 'evecorp_options' );
	echo "<input id='cache_API' name='evecorp_options[cache_API]' type='checkbox' checked='{$options[cache_API]}' class='code' /> ";
	echo '<p class="description">Cache Eve Online API response (recommended).</p>';
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
 * @return array Sanitized values from the input form fields
 */
function evecorp_validate_settings( $input )
{
	$output = $input;
	return $output;
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
	global $wp_settings_sections, $wp_settings_fields;
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
				<input type="submit" name="Submit" value="Save changes" />
			</p>
		</form>
	</div>
	<?php
}

// Contextual Help for Eve Online settings
function evecorp_settings_help()
{

	// Help text
	include_once(dirname( __FILE__ ) . '/help.php');

	// Hook to screen from add_options_page()
	global $evecorp_settings_page_hook;
	$screen = get_current_screen();

	/*
	 * Check if current screen is My Admin Page
	 * Don't add help tab if it's not
	 */
	if ( $screen->id != $evecorp_settings_page_hook )
		return;

	// Remove the admin notice about visting this page.
	remove_action( 'admin_notices', 'evecorp_config_notifiy' );

	// Add my_help_tab if current screen is My Admin Page
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_overview',
		'title' => __( 'Overview' ),
		'content' => $evecorp_options_help_overview
			)
	);
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_corpkey',
		'title' => __( 'Corporate API Key' ),
		'content' => $evecorp_options_help_corpkey
			)
	);
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_auth',
		'title' => __( 'User Authentication' ),
		'content' => $evecorp_options_help_authentication
			)
	);
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_userkey',
		'title' => __( 'User API Key' ),
		'content' => $evecorp_options_help_userkey
			)
	);
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_risk',
		'title' => __( 'Risks' ),
		'content' => $evecorp_options_help_risks
			)
	);
	$screen->add_help_tab( array(
		'id' => 'evecorp_help_igb',
		'title' => __( 'In-Game Browser' ),
		'content' => $evecorp_options_help_igb
			)
	);

	$screen->set_help_sidebar( '

		<p><strong>For more information:</strong></p>

		<p><a href="http://wiki.eveonline.com/en/wiki/In_game_browser"
			title="Evelopedia on the In-Game Browser"
			target="_blank">In-Game Browser (IGB)</a></p>

		<p><a href="http://community.eveonline.com/devblog.asp?a=blog&nbid=1920"
			title="Eve DevBLog on API Keys"
			target="_blank">Eve Online API Keys</a></p>

		<p><a href="https://forums.eveonline.com/default.aspx?g=topics&f=263"
			title="Eve 3rd-Party Developer Forum"
			target="_blank">Eve Technology Lab</a></p>
			' );
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