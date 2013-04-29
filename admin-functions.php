<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Admin functions library
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Plugin activation function.
 * Adds the options settings used by this plugin.
 *
 * @todo Test if we can connect with Eve Online API servers.
 * @todo Check if required/recommended plugins are installed.
 *
 * @global type $wp_version
 * @global type $evecorp_options
 */
function evecorp_activate()
{
	global $wp_version, $evecorp_options;

	/* WordPress version check */
	$wp_version_error = EVECORP . ' requires WordPress ' . EVECORP_MIN_WP_VERSION .
			' or newer. This server is running WordPress version ' . $wp_version .
			'. <a href="http://codex.wordpress.org/Upgrading_WordPress">
			Please update!</a>';
	if ( version_compare( $wp_version, EVECORP_MIN_WP_VERSION, '<' ) ) {
		deactivate_plugins( EVECORP_PLUGIN_FILE );
		wp_die( $wp_version_error, '', array( 'response'	 => 200, 'back_link'	 => TRUE ) );
	}

	/* PHP version check */
	$php_version_error = EVECORP . ' requires PHP ' . EVECORP_MIN_PHP_VERSION .
			' or newer. This server is running PHP version ' . phpversion() .
			'. <a href="http://www.php.net/downloads.php">
			Please update!</a>';
	if ( version_compare( PHP_VERSION, EVECORP_MIN_PHP_VERSION, '<' ) ) {
		deactivate_plugins( EVECORP_PLUGIN_FILE );
		wp_die( $php_version_error, '', array( 'response'	 => 200, 'back_link'	 => TRUE ) );
	}

	/* Add some sane default options */
	evecorp_init_options();
	add_option( 'evecorp_options', $evecorp_options );

	/* Add rewrite rules for our auto-generated Eve Online specific pages */
	evecorp_add_rewrite_rules();

	/* Recreate rewrite rules */
	flush_rewrite_rules();

	/* Add our Eve Online specific roles to WordPress roles */
	evecorp_add_roles();
}

/**
 * Plugin de-activation function.
 *
 */
function evecorp_deactivate()
{
	/* Remove our rewrite rules */
	evecorp_remove_rewrite_rules();

	/* Remove out Eve Online specific WordPress roles */
	evecorp_remove_roles();
}

/**
 * Add Eve Online specific roles to WordPress roles
 *
 * See https://github.com/cobon/evecorp/wiki/Access-Rights-and-Capabilities
 *
 */
function evecorp_add_roles()
{
	$eve_roles = array(
		'Director'						 => 'Director',
		'Accountant'					 => 'Accountant',
		'Auditor'						 => 'Auditor',
		'ChatManager'					 => 'Communications Officer',
		'EquipmentConfig'				 => 'Config Equipment',
		'StarbaseConfig'				 => 'Starbase Equipment',
		'ContractManager'				 => 'Contract Manager',
		'Diplomat'						 => 'Diplomat',
		'FittingManager'				 => 'Fitting Manager',
		'JuniorAccountant'				 => 'Junior Accountant',
		'PersonnelManager'				 => 'Personnel Manager',
		'InfrastructureTacticalOfficer'	 => 'Starbase Defense Operator',
		'StarbaseCaretaker'				 => 'Starbase Fuel Technician',
		'FactoryManager'				 => 'Factory Manager',
		'CanRentFactorySlot'			 => 'Rent Factory Slot',
		'CanRentOffice'					 => 'Rent Office',
		'CanRentResearchSlot'			 => 'Rent Research Slot',
		'SecurityOfficer'				 => 'Security Officer',
		'StationManager'				 => 'Station Manager',
		'Trader'						 => 'Trader',
	);
	foreach ( $eve_roles as $role => $display_name ) {
		switch ( $role ) {
			case 'Director':

				/* Gets all capabilities of the WordPress Administrator role  */
				$wp_role = get_role( 'administrator' );
				$caps	 = $wp_role->capabilities;

				/* Add this role to WordPress */
				$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name, $caps );

				/* Add capability to edit our content ACL */
				$new_role->add_cap( 'evecorp_ACL', true );
				break;

			case 'ChatManager':

				/* Gets all capabilities of a WordPress Editor */
				$wp_role = get_role( 'editor' );
				$caps	 = $wp_role->capabilities;

				/* Add this role to WordPress */
				$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name, $caps );

				/* Add capability to edit our content ACL */
				$new_role->add_cap( 'evecorp_ACL', true );
				break;

			case 'Diplomat':

				/* Gets all capabilities of a WordPress Author */
				$wp_role = get_role( 'author' );
				$caps	 = $wp_role->capabilities;

				/* Add this role to WordPress */
				$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name, $caps );

				/* Add capability to edit our content ACL */
				$new_role->add_cap( 'evecorp_ACL', true );
				break;

			case 'SecurityOfficer':

				/* Add this role to WordPress */
				$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name, $caps );

				/* Add capability to edit our content ACL */
				$new_role->add_cap( 'evecorp_ACL', true );
				break;

			case 'PersonnelManager':

				/* Add this role to WordPress */
				$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name, $caps );

				/* Add capability to list and edit WordPress user profiles */
				$new_role->add_cap( 'list_users', true );
				$new_role->add_cap( 'edit_users', true );
				break;

			default:

				/* All others have no predefined capabilities */
				//$new_role = add_role( EVECORP_EVE_ROLE_PREFIX . $role, $display_name );
				break;
		}
	}
}

/**
 * Remove Eve Online specific WordPress roles.
 *
 * @global type $wp_user_roles
 */
function evecorp_remove_roles()
{
	global $wp_roles;
	$prefix = EVECORP_WP_ROLE_PREFIX;

	/* Get all roles from WP options */
	$wp_roles_list = $wp_roles->get_names();
	foreach ( $wp_roles_list as $role_key => $role_display_name ) {

		/* Check if its one of our roles */
		if ( $prefix === substr( $role_key, 0, strlen( $prefix ) ) ) {

			/* Remove role from WordPress system */
			remove_role( $role_key );
		}
	}
}

/**
 * Add rewrite rules for Eve online specific pages.
 *
 * Examples URLs:
 * 	www.example.com/members/
 * 	www.example.com/members/John_Doe/
 *
 * @global type $wp_rewrite
 */
function evecorp_add_rewrite_rules()
{
	global $wp_rewrite;

	add_rewrite_tag( '%member%', '([^&]+)' );
	$wp_rewrite->add_rule( '^members/([^/]*)/?', 'index.php?&member=$matches[1]', 'top' );
	$wp_rewrite->add_rule( '^members$', 'index.php?members_list=1', 'top' );
}

/**
 * Remove rewrite rules for Eve online specific pages.
 */
function evecorp_remove_rewrite_rules()
{
	/* Remove our rewrite rules */
	remove_action( 'generate_rewrite_rules', 'evecorp_add_rewrite_rules' );

	/* Recreate rewrite rules, without ours included */
	flush_rewrite_rules();
}

/**
 * Add a notification message  to the top of admin pages.
 *
 */
function evecorp_config_notifiy()
{
	echo '<div class="error"><p>' .
	'Login with Eve Online API keys is currently disabled! ' .
	sprintf( 'Please visit <a href="%s">the Eve Online settings</a>', admin_url( 'options-general.php?page=evecorp_settings' ) ) .
	' and check your corporate API key.</p></div>';
}

/**
 * Remove error condition if its because missing users mail adddress.
 *
 * @param WP_Error $errors
 * @param type $update
 * @param WP_User $user
 */
function evecorp_eveuser_mail( $errors, $update, $user )
{
	unset( $update ); // This is just to supress IDE warning about unused vars
	if ( get_user_meta( $user->ID, 'evecorp_character_ID', true ) )
		unset( $errors->errors['empty_email'] );
}

/**
 * Don't show password fields in user settings for Eve Online characters.
 *
 * @param boolean $show Current value.
 * @param WP_User $profileuser
 * @return boolean True to show, false to not show the password fields.
 */
function evecorp_show_password_fields( $show, $profileuser )
{
	if ( get_user_meta( $profileuser->ID, 'evecorp_character_ID', true ) )
		return false;
	return $show;
}

/**
 * Outputs a section and table-listing with API keys for this user.
 *
 * @param WP_User $user WordPress user profile to edit/display
 */
function evecorp_userkeys( $user )
{
	/* Is the displayed user-profile an Eve Online character? */
	if ( get_user_meta( $user->ID, 'evecorp_character_ID', true ) ) {
		require_once EVECORP_PLUGIN_DIR . '/classes/class-apikey-table.php';

		/* Create an instance of key_Table class */
		$APIKey_Table = new evecorp_APIKey_Table();

		/* Fetch, prepare, sort, and filter our data */
		$APIKey_Table->prepare_items( $user->ID );

		/* Any action going on? */
		$action = $APIKey_Table->current_action();
		if ( 'remove' === $action ) {
			$key_ID = $_REQUEST['key_ID'];
			evecorp_userkey_remove( $user->ID, $key_ID );
		}

		/* Display */
		?>
		<h3>Eve Online Characters and API Keys</h3>
		<!-- Begin API key list table -->
		<?php $APIKey_Table->display() ?>
		<!-- End API key list table -->
		<?php
	}
}

/**
 * Remove a API key from WordPress user meta data.
 *
 * @param type $user_ID ID number of WordPress user profile.
 * @param type $key_ID ID number of Eve Online API key.
 */
function evecorp_userkey_remove( $user_ID, $key_ID )
{
	/* Get saved API key ID's for this character from WP users meta table */
	$user_keys = get_user_meta( $user_ID, 'evecorp_userkeys', true );

	/* Remove */
	unset( $user_keys[$key_ID] );

	/* Save */
	update_user_meta( $user_ID, 'evecorp_userkeys', $user_keys );
}

/**
 * Output HTML form for setting the users time-zone.
 *
 * @param WP_User $profileuser
 */
function evecorp_user_TZ_form( $profileuser )
{
	/* translators: date and time format for exact current time, mainly about
	 * timezones, see http://php.net/date */
	$timezone_format = _x( 'Y-m-d G:i:s', 'timezone date format' );
	?>
	<table class="form-table">
		<tr>
			<?php
			$current_offset	 = get_user_option( 'evecorp_gmt_offset', $profileuser->ID );
			if ( !$current_offset )
				$current_offset	 = get_option( 'gmt_offset' );
			$tzstring		 = get_user_option( 'evecorp_timezone_string', $profileuser->ID );
			if ( !$tzstring )
				$tzstring		 = get_option( 'timezone_string' );
			$check_zone_info = true;

			/* Remove old Etc mappings. Fallback to gmt_offset. */
			if ( false !== strpos( $tzstring, 'Etc/GMT' ) )
				$tzstring = '';

			/* Create a UTC+- zone if no timezone string exists. */
			if ( empty( $tzstring ) ) {
				$check_zone_info = false;
				if ( 0 == $current_offset )
					$tzstring		 = 'UTC+0';
				elseif ( $current_offset < 0 )
					$tzstring		 = 'UTC' . $current_offset;
				else
					$tzstring		 = 'UTC+' . $current_offset;
			}
			?>
			<th scope="row"><label for="timezone_string"><?php _e( 'Your Timezone on Earth' ) ?></label></th>
			<td>

				<select id="timezone_string" name="timezone_string">
					<?php echo wp_timezone_choice( $tzstring ); ?>
				</select>

				<span id="utc-time"><?php printf( __( 'Current <abbr title="Eve Standard Time (ET)">Eve time</abbr> is <code>%s</code>' ), date_i18n( $timezone_format, false, 'gmt' ) ); ?></span>
				<?php if ( get_user_option( 'evecorp_timezone_string', $profileuser->ID ) || !empty( $current_offset ) ) : ?>
					<span id="local-time"><?php printf( __( 'Your local earth time is <code>%1$s</code>' ), date_i18n( $timezone_format ) ); ?></span>
				<?php endif; ?>
				<p class="description"><?php _e( 'Choose a city on planet earth in the same timezone as you.' ); ?></p>
				<?php if ( $check_zone_info && $tzstring ) : ?>
					<br />
					<span>
						<?php
						/* Set TZ so localtime works. */
						date_default_timezone_set( $tzstring );
						$now			 = localtime( time(), true );
						if ( $now['tm_isdst'] )
							_e( 'Your timezone on earth is currently in daylight saving time.' );
						else
							_e( 'Your timezone on earth is currently in standard time.' );
						?>
						<br />
						<?php
						$allowed_zones	 = timezone_identifiers_list();

						if ( in_array( $tzstring, $allowed_zones ) ) {
							$found					 = false;
							$date_time_zone_selected = new DateTimeZone( $tzstring );
							$tz_offset				 = timezone_offset_get( $date_time_zone_selected, date_create() );
							$right_now				 = time();
							foreach ( timezone_transitions_get( $date_time_zone_selected ) as $tr ) {
								if ( $tr['ts'] > $right_now ) {
									$found = true;
									break;
								}
							}

							if ( $found ) {
								echo ' ';
								$message = $tr['isdst'] ?
										__( 'Daylight saving time begins on: <code>%s</code>.' ) :
										__( 'Standard time begins on: <code>%s</code>.' );

								/* Add the difference between the current offset and the new offset to ts to get the correct transition time from date_i18n(). */
								printf( $message, date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $tr['ts'] + ($tz_offset - $tr['offset']) ) );
							} else {
								_e( 'It does not observe daylight saving time.' );
							}
						}
						// Set back to UTC.
						date_default_timezone_set( 'UTC' );
						?>
					</span>
				<?php endif; ?>
			</td>

		</tr>
	</table>
	<?php
}

/**
 * Save the users timezone in his profile.
 *
 * @param int $user_ID The WordPress ID number of the user.
 */
function evecorp_set_user_TZ( $user_ID )
{
	$timezone_string = sanitize_option( 'timezone_string', $_POST['timezone_string'] );
	if ( !empty( $timezone_string ) ) {

		/* Do we have a GMT offset instead of a location? */
		if ( preg_match( '/^UTC[+-]/', $timezone_string ) ) {

			/* Map UTC+- timezones to gmt_offsets. */
			$gmt_offset = preg_replace( '/UTC\+?/', '', $timezone_string );

			/* Emtpy the timezone location. */
			$timezone_string = '';
		} else {
			/* Empty any GMT offseet. */
			$gmt_offset = '';
		}
		update_user_option( $user_ID, 'evecorp_timezone_string', $timezone_string );
		update_user_option( $user_ID, 'evecorp_gmt_offset', $gmt_offset );
	}
}

/**
 * Output HTML form for adding API key ID and vcode.
 *
 * @param WP_User $profileuser The WO user object
 */
function evecorp_userkeys_form( $profileuser )
{
	/* Is the displayed user-profile an Eve Online character? */
	if ( get_user_meta( $profileuser->ID, 'evecorp_character_ID', true ) ) {
		?>
		<h4>Add alternate Character Key</h4>
		<p>Add Eve Online API character keys for your alternate characters here.
			They will not be usable for login on this website. To add a new API key
			usable for login, simply login with API key ID and vcode.</p>
		<table class="form-table">
			<tr>
				<th><label for="key_ID">API Key ID</label></th>
				<td>
					<input type="text" name="key_ID" id="key_id" value="" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="vcode">API Verification Code</label></th>
				<td>
					<textarea id='vcode' name='vcode' cols='32' rows='2'></textarea>
					<p class="description">The verification code is only used to
						retrieve character information and will not be saved.</p>
				</td>
			</tr>
		</table>
		<?php
	}
}

/**
 * Add a Eve Online API key ID to current users meta data.
 *
 * @param int $user_id ID number of WordPress user.
 */
function evecorp_altkey_add( $user_ID )
{
	$key_ID	 = sanitize_text_field( $_REQUEST['key_ID'] );
	$vcode	 = sanitize_text_field( $_REQUEST['vcode'] );
	if ( !empty( $key_ID ) || !empty( $vcode ) ) {

		/* Test the submitted credentials */
		if ( empty( $key_ID ) )
			wp_die( '<strong>ERROR</strong>: You have to
				supply a API key ID.', '', array(
				'response'	 => 200,
				'back_link'	 => TRUE
					)
			);

		if ( empty( $vcode ) )
			wp_die( '<strong>ERROR</strong>: You have to
				supply the verification code for your API key.', '', array(
				'response'	 => 200,
				'back_link'	 => TRUE
					)
			);

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
			wp_die( $keyinfo->get_error_message(), '', array(
				'response'	 => 200,
				'back_link'	 => TRUE
					)
			);

		/* Is the key type for characters (and not a corp key or account key)? */
		if ( 'Character' <> $keyinfo['type'] )
			wp_die( '<strong>ERROR</strong>: This API key is not a character
				key.', '', array(
				'response'	 => 200,
				'back_link'	 => TRUE
					)
			);

		/* Get saved API key ID's for this character from WP users meta table */
		$evecorp_userkeys = get_user_meta( $user_ID, 'evecorp_userkeys', true );

		/* Update the WP users meta table */
		$evecorp_userkeys[$key_ID]['expires']			 = $keyinfo['expires'];
		$evecorp_userkeys[$key_ID]['accessMask']		 = $keyinfo['accessMask'];
		$evecorp_userkeys[$key_ID]['characterName']		 = $keyinfo['characters'][0]['characterName'];
		$evecorp_userkeys[$key_ID]['characterID']		 = $keyinfo['characters'][0]['characterID'];
		$evecorp_userkeys[$key_ID]['corporationName']	 = $keyinfo['characters'][0]['corporationName'];
		update_user_meta( $user_ID, 'evecorp_userkeys', $evecorp_userkeys );
	}
}

/**
 * Add buttons to the tinyMCE toolbar for creating links to Eve Online stuff.
 */
function evecorp_init_mce()
{
	/* Does this user ever see TinyMCE? */

	/* Does he have any editing rights? */
	if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) {

		/* Does he have TinyMCE enabled in his profile? */
		$mce_user = get_user_option( 'rich_editing' );
		if ( 'true' === $mce_user ) {

			/* Add a callback to register our TinyMCE plugin */
			add_filter( "mce_external_plugins", "evecorp_add_mce_plugin" );

			/* Add a callback to add our button to the TinyMCE toolbar */
			add_filter( 'mce_buttons', 'evecorp_add_mce_button' );
		}
	}
}

/**
 *  Register our TinyMCE plugin for adding links to Eve Online stuff.
 *
 * @param array $mce_plugins List of TinyMCE-plugins.
 * @return array Modified list of TinyMCE-plugins.
 */
function evecorp_add_mce_plugin( $mce_plugins )
{
	$mce_plugins['evecorp'] = EVECORP_PLUGIN_URL . 'js/evecorp.mceButtons.js';
	return $mce_plugins;
}

/**
 *  Add our buttons to the TinyMCE toolbar.
 *
 * @param array $mce_buttons List of TinyMCE toolbar buttons.
 * @return array Modified list of TinyMCE toolbar buttons.
 */
function evecorp_add_mce_button( $mce_buttons )
{
	/* Add the button ID */
	array_push( $mce_buttons, '|', 'eve_char', 'eve_corp', 'eve_alliance', 'eve_station', 'eve_solarsystem', 'eve_constellation', 'eve_region'
	);
	return $mce_buttons;
}