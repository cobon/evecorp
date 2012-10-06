/*
 * Eve Online Plugin for WordPress
 *
 * Browser context menus for Eve Online entities.
 *
 * @package evecorp
 */

jQuery(function(){

	/* Characters In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-char-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2){
					CCPEVE.showInfo("1377", jQuery(this).attr("id"))
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.addContact(jQuery(this).attr("id"))
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.block(jQuery(this).attr("id"))
				}
			},
			"sep2": "---------",
			"startConversation": {
				name: "Start Conversation",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.startConversation(jQuery(this).attr("id"))
				}
			},
			"sendMail": {
				name: "Send Message",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.sendMail(jQuery(this).attr("id"),'Subject','Body')
				}
			},
			"inviteToFleet": {
				name: "Invite to Fleet",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.inviteToFleet(jQuery(this).attr("id"))
				}
			},
			"sep3": "---------",
			"addBounty": {
				name: "Add Bounty",
				disabled: !jQuery(".evecorp-char-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.addBounty(jQuery(this).attr("id"))
				}
			},
			"sep4": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt){
					window.location = 'https://gate.eveonline.com/Profile/'+jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt){
					window.location = 'http://evewho.com/pilot/'+jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt){
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=player&name='+jQuery(this).attr("name");
				}
			},
			"EveKill": {
				name: "Eve Kill",
				callback: function(opt){
					window.location = 'http://eve-kill.net/?a=pilot_detail&plt_external_id='+jQuery(this).attr("id");
				}
			}
		}
	})

	/* Corporations In-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-corp-igb',
		trigger: 'left',
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2){
					CCPEVE.showInfo("2", jQuery(this).attr("id"))
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.addContact(jQuery(this).attr("id"))
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-corp-igb").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.block(jQuery(this).attr("id"))
				}
			},
			"sep2": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt){
					window.location = 'https://gate.eveonline.com/Corporation/'+jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt){
					window.location = 'http://evewho.com/corp/'+jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt){
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=corp&name='+jQuery(this).attr("name");
				}
			},
			"EveKill": {
				name: "Eve Kill",
				callback: function(opt){
					window.location = 'http://eve-kill.net/?a=corp_detail&crp_external_id='+jQuery(this).attr("id");
				}
			}
		}
	})

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
				callback: function(opt){
					window.location = 'https://gate.eveonline.com/Mail/Compose/'+jQuery(this).attr("name");
				}
			},
			"sep1": "---------",
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt){
					window.location = 'https://gate.eveonline.com/Profile/'+jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt){
					window.location = 'http://evewho.com/pilot/'+jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt){
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=player&name='+jQuery(this).attr("name");
				}
			},
			"EveKill": {
				name: "Eve Kill",
				callback: function(opt){
					window.location = 'http://eve-kill.net/?a=pilot_detail&plt_external_id='+jQuery(this).attr("id");
				}
			}
		}
	})

	/* Corporations Out-of-Game Browser Context Menu */
	jQuery.contextMenu({
		selector: '.evecorp-corp',
		trigger: 'left',
		items: {
			"eveGate": {
				name: "Eve Gate",
				callback: function(opt){
					window.location = 'https://gate.eveonline.com/Corporation/'+jQuery(this).attr("name");
				}
			},
			"eveWho": {
				name: "Eve Who",
				callback: function(opt){
					window.location = 'http://evewho.com/corp/'+jQuery(this).attr("name");
				}
			},
			"BattleClinic": {
				name: "BattleClinic",
				callback: function(opt){
					window.location = 'http://eve.battleclinic.com/killboard/combat_record.php?type=corp&name='+jQuery(this).attr("name");
				}
			},
			"EveKill": {
				name: "Eve Kill",
				callback: function(opt){
					window.location = 'http://eve-kill.net/?a=corp_detail&crp_external_id='+jQuery(this).attr("id");
				}
			}
		}
	})

});
