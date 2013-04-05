/*
 * Eve Online Plugin for WordPress
 *
 * Browser context menus for Eve Online entities.
 *
 * @package evecorp
 */

jQuery(function() {
	/**
	 * In-Game Browser menues.
	 */

	/* Characters In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-char-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("1377", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addContact(jQuery(this).attr("id"));
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.block(jQuery(this).attr("id"));
				}
			},
			"sep2": "---------",
			"startConversation": {
				name: "Start Conversation",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.startConversation(jQuery(this).attr("id"));
				}
			},
			"sendMail": {
				name: "Send Message",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.sendMail(jQuery(this).attr("id"), ' ', ' ');
				}
			},
			"inviteToFleet": {
				name: "Invite to Fleet",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.inviteToFleet(jQuery(this).attr("id"));
				}
			},
			"sep3": "---------",
			"addBounty": {
				name: "Add Bounty",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addBounty(jQuery(this).attr("id"));
				}
			},
			"sep4": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Profile/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/pilot/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/character/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=player&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Corporations In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-corp-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("2", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addContact(jQuery(this).attr("id"));
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.block(jQuery(this).attr("id"));
				}
			},
			"sep2": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Corporation/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/corp/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/corporation/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=corp&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Alliance In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-alliance-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("16159", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addContact(jQuery(this).attr("id"));
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.block(jQuery(this).attr("id"));
				}
			},
			"sep2": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Alliance/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/alli/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/alliance/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=alliance&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Solar System In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-solarsystem-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("5", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"showRouteTo": {
				name: "Show Route to",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.showRouteTo(jQuery(this).attr("id"));
				}
			},
			"setDestination": {
				name: "Set Destination",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.setDestination(jQuery(this).attr("id"));
				}
			},
			"addWaypoint": {
				name: "Add Waypoint",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addWaypoint(jQuery(this).attr("id"));
				}
			},
			"bookmark": {
				name: "Save Location ...",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.bookmark(jQuery(this).attr("id"));
				}
			},
			"showMap": {
				name: "Show on Map",
				callback: function(opt) {
					CCPEVE.showMap(jQuery(this).attr("id"));
				}
			},
			"sep2": "---------",
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/' + jQuery(this).attr("name") + '_(System)';
				}
			},
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/system/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/system/' + jQuery(this).attr("id") + '/';
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Constellation In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-constellation-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("4", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"bookmark": {
				name: "Save Location ...",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.bookmark(jQuery(this).attr("id"));
				}
			},
			/* Broken, opens map at current location */
//			"showMap": {
//				name: "Show on Map",
//				callback: function(opt) {
//					CCPEVE.showMap(jQuery(this).attr("id"));
//				}
//			},
			"sep2": "---------",
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/Category:' + jQuery(this).attr("name") + '_(Constellation)';
				}
			},
			/* @todo Region name needed */
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/universe/' + jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Region In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-region-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("3", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"bookmark": {
				name: "Save Location ...",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.bookmark(jQuery(this).attr("id"));
				}
			},
			/* Broken, opens map at current location */
//			"showMap": {
//				name: "Show on Map",
//				callback: function(opt) {
//					CCPEVE.showMap(jQuery(this).attr("id"));
//				}
//			},
			"sep2": "---------",
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/Category' + jQuery(this).attr("name") + '_(Regions)';
				}
			},
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/map/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/region/' + jQuery(this).attr("id") + '/';
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Station In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-station-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2) {
					CCPEVE.showInfo("3867", jQuery(this).attr("id"));
				}
			},
			"sep1": "---------",
			"setDestination": {
				name: "Set Destination",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.setDestination(jQuery(this).attr("id"));
				}
			},
			"addWaypoint": {
				name: "Add Waypoint",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.addWaypoint(jQuery(this).attr("id"));
				}
			},
			"bookmark": {
				name: "Save Location ...",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt) {
					CCPEVE.bookmark(jQuery(this).attr("id"));
				}
			},
			"sep2": "---------",
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/station/' + jQuery(this).attr("name");
				}
			}
		}
	});

	/**
	 * Out-of-Game Browser menues.
	 */

	/* Characters Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-char',
		trigger: 'left',
		callback: function(key, options) {
			var m = "clicked: " + key;
			window.console && console.log(m) || alert(m);
		},
		items: {
			"sendMail": {
				name: "Send Message",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Mail/Compose/' + jQuery(this).attr("name");
				}
			},
			"sep1": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Profile/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/pilot/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/character/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=player&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Corporations Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-corp',
		trigger: 'left',
		items: {
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Corporation/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/corp/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/corporation/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=corp&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Alliance Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-alliance',
		trigger: 'left',
		items: {
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt) {
					window.location = 'https://gate.eveonline.com/Alliance/' + jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt) {
					window.location = 'http://evewho.com/alli/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/alliance/' + jQuery(this).attr("id");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=alliance&name=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Solar System Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-solarsystem',
		trigger: 'left',
		items: {
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/' + jQuery(this).attr("name") + '_(System)';
				}
			},
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/system/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/system/' + jQuery(this).attr("id") + '/';
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Constellation Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-constellation',
		trigger: 'left',
		items: {
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/Category:' + jQuery(this).attr("name") + '_(Constellation)';
				}
			},
			/* @todo Region name needed */
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/universe/' + jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Region Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-region',
		trigger: 'left',
		items: {
			"EVElopedia": {
				name: "EVElopedia",
				callback: function(opt) {
					window.location = 'http://wiki.eveonline.com/en/wiki/Category:' + jQuery(this).attr("name") + '_(Region)';
				}
			},
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/map/' + jQuery(this).attr("name");
				}
			},
			"zKillboard": {
				name: "zKillboard",
				callback: function(opt) {
					window.location = 'https://zkillboard.com/region/' + jQuery(this).attr("id") + '/';
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt) {
					window.location = 'http://eve.battleclinic.com/killboard/recent_activity.php?searchTerms=' + jQuery(this).attr("name");
				}
			}
		}
	});

	/* Station Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-station',
		trigger: 'left',
		items: {
			"dotlan": {
				name: "dotlan evemaps",
				callback: function(opt) {
					window.location = 'http://evemaps.dotlan.net/station/' + jQuery(this).attr("name");
				}
			}
		}
	});

});