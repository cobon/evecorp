<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Contextual help for Eve Online settings
 *
 * @package evecorp
 */
$evecorp_options_help_overview = <<< END
<p>
	<strong>Protected Site</strong>

</p><p>

	This corporation website is protected from access by non-members and
	from anyone trying to impersonate a corporation member.

</p><p>

	Visitors need to be authenticated and authorized in order to access
	non-public information and be able to write pages and posts or leave
	comments.

</p><p>

	<strong>No User Registration</strong>

</p><p>

	Users don't need to register with user-ids and passwords. They don't need to
	disclose their email address or any other personal information.

</p><p>

	<strong>No User Management</strong>

</p><p>

	No additional user management involved.	Characters who are no longer members
	of the corporation are immediately and automatically denyied access. As well
	as new recruits gain access the moment they become members.

</p><p>

	See the help tabs on the left for information on how this works in detail
	and what risks are involved.

</p>
END;

$evecorp_options_help_settings = <<< END
<p>

	The name of the corporation you supply, will be verified with Eve Online by API.

</p><p>

	Eve Online servers will be contacted and information for the corporation
	name requested. If the name is recognized as a valid and existing Eve Online
	corporation, your configuration will be accepted.

</p>
END;

$evecorp_options_help_authentication = <<< END

<p>

	<strong>Authorization</strong>

</p><p>

	Eve Online servers will be contacted to verify that the supplied character
	name is a current and valid member of your corporation.


</p><p>

	<strong>Authentication</strong>

</p><p>

	The user needs to supply a customizable API key from Eve Online to verify
	that the character accessing the website is controlled by the corresponding
	and rightful Eve Online player account.

</p>
END;

$evecorp_options_help_userkey = <<< END
<p>

	The supplied Eve Online API Key only serves for the purpose of verifing that
	the visting character name is in fact controlled by its rightful Eve Online
	player-account.

</p><p>

	The API key does not need to allow access to any data of that character or
	other characters of the player account or any data the player account itself
	(access mask 0).

</p><p>

	The website does not request use or store any additional information on the
	the character or the user. We aks the Eve Online servers only for a "yes"
	or a "no" to the question if we should allow access to the webiste.

</p><p>

	The user can choose if he wants the ID and verification code of his supplied
	API key stored in the database of this webserver. This will allow him to be
	logged in automatically on subsuquent visits to the site (by browser cookie).

</p><p>

	If the user chooses not to save the key credentials, he will need to supply
	them again on every visit. As in this case will be used by the website for
	the duration of the visist only and discarded afterwards.

</p>
END;

$evecorp_options_help_risks = <<< END
<p>

	<strong>Risks Involved</strong>

</p><p>

	Stored IDs and verification codes of API keys could fall in to the wrong
	hands, in case of a server break-in, hacker-attack, or also by users giving
	out the that information themself.

</p><p>

	Aside from what is publicly available in-game and on Eve Gate, an attacker
	in posession of ID and verification code, can gain access to the following
	information:

</p>
<ul>
	<li>
		The access Mask of the key (which should be 0)
	</li>
	<li>
		The access level of the key (which can be one of "Account", "Character"
		or "Corporation" and should be "Character")
	</li>
	<li>
		The date the API key expires.
	</li>
</ul>
<p>

	An attacker in posession of ID and verification code will be able to
	inpersonate the corporation member on this website and gain access to
	internal information.

</p>
<ul>

	<li>Never use the same API keys on multiple websites, services or
	applications.</li>

	<li>Set expiration date on a short time-span (default is one year) and renew the
	key periodically.</li>

</ul>
<p>

	The user can change the verification code or delete the key anytime on the
	Eve Online API website, thus will be no longer usable either by this website
	or anyone else.

</p>
END;

$evecorp_options_help_corpkey = <<< END
<p>

	Corporate API Key information.

</p>
END;

$evecorp_options_help_igb = <<< END
<p>

	<strong>The In-Game Browser and Trust</strong>

</p><p>

	The in-game browser of Eve Online has additional features, which no other
	browser provides, as they are directly related to the game.

</p><p>

	For the most part, these features are very similar to the links used in chat
	channels.

</p><p>

	This makes it very easy for 3rd-party websites and web-designers to
	contribute to the game expirience and let the Eve universe expand on to the
	internet. Or better, let the internet expand from its earth-bound existence
	in to far away galaxies like New Eden.

</p><p>

	<strong>Functionality any website can provide</strong>

</p><p>

	The following functions can be made available by any websites for their visitors
	as their is no risk for the pilot involved.

</p>
<ul>
	<li>
		Open the information and preview windows for items like:
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
END;
