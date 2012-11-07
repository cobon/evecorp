<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Contextual help for Eve Online settings
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Contextual Help for Eve Online settings
 *
 * @global string $evecorp_settings_page_hook
 */
function evecorp_settings_help()
{
	/* Hook to screen from add_options_page() */
	global $evecorp_settings_page_hook;
	$screen = get_current_screen();

	/* Check if current screen is our plugin page */
	if ( $screen->id != $evecorp_settings_page_hook )
		return;

	/* Remove the admin notice about visting this page. */
	remove_action( 'admin_notices', 'evecorp_config_notifiy' );

	/* Add help tabs */
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_overview',
		'title'		 => __( 'Overview' ),
		'callback'	 => 'evecorp_settings_help_overview'
			)
	);
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_corpkey',
		'title'		 => __( 'Corporate API Key' ),
		'callback'	 => evecorp_settings_help_corpkey
			)
	);
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_auth',
		'title'		 => __( 'User Authentication' ),
		'callback'	 => 'evecorp_settings_help_authentication'
			)
	);
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_authz',
		'title'		 => __( 'User Authorization' ),
		'callback'	 => 'evecorp_settings_help_authorization'
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

	function evecorp_settings_help_overview()
	{
		?>
		<p>

			This website can now be protected from access by non-members and also
			from anyone trying to impersonate a corporation member.

		</p><p>

			Visitors will be authenticated and authorized as corporation members in
			order to access non-public information and be able to write pages and
			posts or leave comments.

		</p>

		<h4>No User Registration</h4>

		<p>

			Users don't need to register with user-ids and passwords. They don't need to
			disclose their email address or any other personal information.

		</p>

		<h4>No User Management</h4>

		<p>

			No additional user management involved.	Characters who are no longer members
			of the corporation are immediately and automatically denyied access. As well
			as new recruits gain access the moment they become members.

		</p><p>

			See the help tabs on the left for information on how this works in
			detail and what risks are involved.

		</p>
		<?php

	}

	function evecorp_settings_help_corpkey()
	{
		?>
		<p>

			The corporation API key allows this website to retrieve information about
			your corporation from Eve Online servers.

		</p><p>

			It is used to automatically manage user profiles and access rights on this
			website, according to actual status, role and title of your
			corporation-members.

		</p>

		<h4>Where do I get the API key?</h4>

		<p>

			The CEO and the directors of a coporation can create corporation keys
			at the <a href="https://support.eveonline.com/api/"
					  title="Eve Online Support website" target="_BLANK">
				Eve Online Support website</a>.

		</p>

		<h4>Which permissions are needed?</h4>

		<p>

			Following is a list of the information your corporation key must be able to
			retrieve from the Eve Online Servers:

		</p>
		<ul>
			<li>Account and Market
				<ul>
					<li>WalletJournal<br />
						<i>to confirm a users identity trough ISK payments.</i></li>
				</ul>
			</li>
			<li>Corporation Members
				<ul>
					<li>Titles<br />
						<i>to apply roles and capabilites on the website based on a characters corporation titles.</i></li>
					<li>MemberTrackingLimited<br />
						<i>to retrieve the list of all current corporation members.</i></li>
					<li>MemberSecurity<br />
						<i>to apply roles and capabilites on the website according to characters coporation roles.</i></li>
				</ul>
			</li>
			<li>Corporation Members
				<ul>
					<li>CorporationSheet<br />
						<i>to retrieve general information about the corporaiton.</i></li>
				</ul>
			</li>
		</ul>
		<p>

			The Access Mask field should display the number <strong>524448</strong>.

		</p>
		<?php

	}

	function evecorp_settings_help_authentication()
	{
		?>
		<p>

			Your corporation members will login with a API key ID and verification
			code insead of the usual user-id and password.

		</p><p>

			Eve Online servers will be contacted to verify that the supplied
			API key belongs to a character which is a current and valid member of
			your corporation.

		</p><p>

			A new userprofile for the character is automatically created if its not
			already existing. Existing users will have their user profile
			updated with current Eve Online character information.

		</p><p>

			If a API key is used the first time an additional identity verification
			trough a small ISK payment is required, before access to the site is
			actually granted.

		</p><p>

			This is to ensure nobody can login with API keys given
			out to third parties in the past. A validation code is displayed to the
			user, which he must include as payment reason.

		</p><p>

			The next time someone uses the same API key to login, your corporation
			journal is checked for payments made by that character.

		</p><p>

			If a payment with matching validation code is found, that API key
			considered as verified and can be subsequently used for login to this
			site.

		</p>
		<?php

	}

	function evecorp_settings_help_authorization()
	{
		?>
		<p>

			Once the user is succesfully authenticated, Eve Online servers will be
			asked for the characters roles and titles within your corporation.

		</p><p>

			The user is then given specific WordPress roles and access rights
			according to his roles and titles in the corporation.

		</p>

		<table class="wp-list-table widefat fixed eveonlineapikeys" cellspacing="0">
			<thead>
				<tr>
					<th class='manage-column sortable desc'>Eve Online Role</th>
					<th class='manage-column sortable desc'>WordPress Role</th>
					<th class='manage-column sortable desc'>Additional Capabilities</th>
				</tr>
			</thead>
			<tbody id="the-list" class='list:eveonlineapikey'>
				<tr class="alternate">
					<td>Director</td>
					<td>Administrator</td>
					<td></td>
				</tr>
				<tr>
					<td>Communications Officer</td>
					<td>Editor</td>
					<td></td>
				</tr>
				<tr class="alternate">
					<td>Diplomat</td>
					<td>Author</td>
					<td></td>
				</tr>
				<tr>
					<td>Personnel Manager</td>
					<td>(New User Default Role)</td>
					<td>list_users, edit_users</td>
				</tr>
				<tr class="alternate">
					<td>(All others)</td>
					<td>(New User Default Role)</td>
					<td></td>
				</tr>
			</tbody>
		</table>

		<p>

			The "New User Default Role" can be set in "General Settings" by
			WordPress Administrators.

		</p><p>

			Additionally if a Eve Online character has a "Title" in the corporation
			and a WordPress "Role" with the same name exists, his WordPress
			user-profile will be assigned to that Role.

		</p><p>

			The "WordPress Roles" of these users can be manually changed by
			Administrators and others who have the capability to update user
			profiles. However if changes of "Eve Online Roles and Titles" are
			detected in the corresponding Eve Online character, the "WordPress
			Roles and Capabilities" are reset as listed in the table above.

		</p>
		<?php

	}

}

