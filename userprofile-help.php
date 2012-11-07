<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Contextual help for Eve Online user profile settings.
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Contextual Help for user profile settings
 *
 * @global type $evecorp_userprofile_page_hook
 */
function evecorp_userprofile_help()
{

	/* Hook to screen from add_options_page() */
	$screen = get_current_screen();
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_userkey',
		'title'		 => __( 'Eve Online API Keys' ),
		'callback'	 => 'evecorp_userprofile_help_userkey'
			)
	);
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_verification',
		'title'		 => __( 'Identity Verification' ),
		'callback'	 => 'evecorp_userprofile_help_verification'
			)
	);
	$screen->add_help_tab( array(
		'id'		 => 'evecorp_help_igb',
		'title'		 => __( 'In-Game Browser' ),
		'callback'	 => 'evecorp_userprofile_help_igb'
			)
	);

	function evecorp_userprofile_help_userkey()
	{
		?>
		<p>

			The table below lets you manage your Eve Online API keys.

		</p><p>

			The main purpose of using API keys instead of unsernames and passwords,
			is to ensure, only valid and current members have access to restricted
			content of your coporations website at any time.

		</p><p>

			New corporation members can login as soon they create their API key and
			verify their identity through a small ISK payment, while members leaving
			the corporation are shut out immediately.

		</p><p>

			Members are managed automatically, without the neeed of a site
			administrator to register and delete users. All the site administrator
			has to do, is to provide a corporate key, which enables the website to
			keep track of its current members.

		</p>

		<h4>Verification Code</h4>

		<p>

			The verfication code (vcode) you supply instead of a password when
			logging in, is not stored anywhere on this website. It is only passed
			along to the Eve Online servers for verification and discarded afterwards.

		</p><p>

			For automated login, you can have your browser remember the verification
			code like any other password.

		</p>

		<h4>Key Type</h4>

		<p>

			Only API keys of type "<strong>Character</strong>" are accepted.

		</p>

		<h4>Access Mask</h4>

		<p>

			The API key does not need access to any data of your characters or your
			player account. It is only used to verify you as a Eve Online character
			and corporation member. You can safely create your API key with a
			<strong>Access Mask 0</strong>.

		</p>

		<h4>Expiry Date</h4>

		<p>

			Its up to you to choose an expiry date. Just bear in mind, that you will
			not be able to login with a expired API key.

		</p>
		<?php

	}

	function evecorp_userprofile_help_verification()
	{
		?>
		<p>

			As API keys are meant to be given out to third parties (e.g software
			as <i>Evemon</i> or websites like <i>Battleclinic</i>) we need to make
			sure that nobody is trying inpersonate you with some API key you have
			given out to a third party in the past.

		</p><p>

			For this purpose you are asked to verify your identity if you try login
			with a API key the first time only.

		</p><p>

			The verification process is completed by a small ISK payment to your
			corporation. As payments can only be made in-game by a character
			personally, we can rest assured that both the browser logging in and the
			player character are controlled by the same person.

		</p>
		<h4>Validation Code</h4>
		<p>

			The code you are given to send along the payment is secured with one-way
			encryption and saved along with your key ID on the site. Therefore its
			not possible to display the code again, should you fail to copy and
			paste it in your in-game client immediately.

		</p><p>

			The process of checking for verification payments is fully automated.
			The next time someone attempts to login with this API key, the
			corporation journals are checked for payments made by this character,
			and if a matching code is found, this API key is considered as verified.
			And can subsequently be used for login.

		</p><p>

			<strong>Never use the same API key on multiple websites, services or
				applications!</strong>

		</p><p>

			The Eve Online API servers update the corporation journals only every
			half hour or so. So it may take that time, until your API key has been
			verified and can be safely used to login.

		</p>
		<?php

	}

	function evecorp_userprofile_help_igb()
	{
		?>
		<p>

			<strong>The In-Game Browser and Trust</strong>

		</p><p>

			The in-game browser of Eve Online has additional features, which no
			other browser provides, as they are directly related to the game.

		</p><p>

			For the most part, these features are very similar to the links used in
			chat channels.

		</p><p>

			This makes it very easy for 3rd-party websites and web-designers to
			contribute to the game expirience and let the Eve universe expand on to
			the	internet. Or better, let the internet expand from its previous
			earth-bound existence in to far away galaxies like New Eden.

		</p><p>

			<strong>What any website can provide</strong>

		</p><p>

			The following functions can be made available by any websites for their
			users visiting with Eve Online clients.

		</p>
		<ul>
			<li>
				Open the information- and preview-windows for items like:
				<ul>
					<li>
						Corporations, alliances and factions
					</li><li>
						Characters (including NPC characters like agents)
					</li><li>
						Regions, constellations, solar systems and stations
					</li><li>
						Any items (ships, modules, commodities, etc.)
					</li>
				</ul>
			</li><li>
				Open the market window for items traded there.
			</li><li>
				Open the star map and show the location of a system or the route to it.
			</li><li>
				Show a fitting for a ship.
			</li><li>
				Show a specific contract.
			</li><li>
				Open a new eve-mail window with a recipient pre-filled.
			</li>
		</ul>
		<p>

			<strong>Trusted Websites</strong>

		</p><p>

			Other more advanced functions can be regarded as risky or reveal information
			about you while visiting the website. These functions should only be availabe,
			if you know who you are dealing with.

		</p><p>

			Therefore a website can request to be trusted, usually when you visit that
			site the first time. You then decide for yourself, if you want grant that
			trust to a webiste, respectively its owners and web-developers.

		</p><p>

			<strong>Functions only trusted websites can provide</strong>

		</p><p>

			The following additional functions are only available, if you have added a
			website to your list of trusted sites:

		</p>
		<ul>
			<li>
				Set a destination or add a waypoint to your autopilot.
			</li><li>
				Remove all waypoints from your autopilot.
			</li><li>
				Join a chat channel or a mailing list.
			</li><li>
				Open the windows for buying or selling items and creating or searching contracts.
			</li><li>
				Add an item to the market quickbar
			</li><li>
				Open the contents window of a container or a ship.
			</li><li>
				Add, edit or remove contacts.
			</li><li>
				Block contacts from saying anything in your chat windows.
			</li><li>
				Form a fleet with a character or invite him to your existing fleet.
			</li><li>
				Start a private conversation with a character.
			</li><li>
				Add a bounty for the killing of a character.
			</li><li>
				Edit a corporation member, or decorate him with an award.
			</li><li>
				Create mail message with recipient, subject and body pre-filled.
			</li><li>
				Create a bookmark.
			</li>
		</ul>

		<p>

			Its important to note that, with the exception of the autopilot, they only
			open the windows to initiate the actions, sometimes with information
			pre-filled. They do not buy or sell anything fully automated, without any
			confirmation from your part.

		</p><p>

			<strong>Information Revealed to Trusted Websites</strong>

		</p><p>

			Your browser also sends additional information about you to websites you
			have marked as trusted:

		</p>
		<ul>
			<li>
				Your characters name, corporation, alliance and possibly warfaction.
			</li><li>
				In what region, constellation and solar system you are currently located.
			</li><li>
				If you are currently in space or docked and if, at which station.
			</li><li>
				The name and type of ship you are currently in.
			</li><li>
				Roles you have with your corporation.
			</li>
		</ul>
		<p>

			<strong>Revoke Trust</strong>

		</p><p>

			You can always revoke the trust by removing an URL from the trusted sites
			list.

		</p><p>

			You can find the list in the “Options” menue, called “Trusted sites”
			of your In-game browser.

		</p>
		<?php

	}

}
