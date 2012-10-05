/*
 * Eve Online Plugin for WordPress
 *
 * Browser context menus for Eve Online entities.
 *
 * @package evecorp
 */

jQuery(function(){
	var type = "1377";
	jQuery.contextMenu({
		selector: '.evecorp-char',
		trigger: 'left',
		callback: function(key, options) {
			var m = "clicked: " + key;
			window.console && console.log(m) || alert(m);
		},
		items: {
			"ShowInfo": {
				name: "Show Info",
				callback: function(opt1, opt2){
					CCPEVE.showInfo(type, jQuery(this).attr("id"))
				}
			},
			"sep1": "---------",
			"addContact": {
				name: "Add Contact",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.addContact(jQuery(this).attr("id"))
				}
			},
			"block": {
				name: "Block",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.block(jQuery(this).attr("id"))
				}
			},
			"sep2": "---------",
			"startConversation": {
				name: "Start Conversation",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.startConversation(jQuery(this).attr("id"))
				}
			},
			"sendMail": {
				name: "Send Message",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.sendMail(jQuery(this).attr("id"),'Subject','Body')
				}
			},
			"inviteToFleet": {
				name: "Invite to Fleet",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
				callback: function(opt){
					CCPEVE.inviteToFleet(jQuery(this).attr("id"))
				}
			},
			"sep3": "---------",
			"addBounty": {
				name: "Add Bounty",
				disabled: !jQuery(".evecorp-char").hasClass("trusted"),
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
	});
/*    jQuery('.evecorp-char').on('click', function(e){
        console.log('clicked', this);

	http://eve.battleclinic.com/killboard/combat_record.php?type=player&name=Crazy%27Ivan
	http://eve-kill.net/?a=pilot_detail&plt_external_id=92133225

    }) */
});