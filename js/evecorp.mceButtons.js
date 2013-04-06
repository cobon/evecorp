/*
 * Eve Online Plugin for WordPress
 *
 * TinyMCE plugin and Toolbar buttons and functions to add WP shortcodes for
 * Eve Online entities.
 *
 * @package evecorp
 */

(function() {
	/* Load plugin specific language pack */
	tinymce.PluginManager.requireLangPack('evecorp');

	tinymce.create('tinymce.plugins.evecorp', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init: function(ed, url) {

			/* Register Eve Online Character button */
			ed.addButton('eve_char', {
				title: 'Eve Online Character',
				image: url + '/eve_char.png',
				onclick: function() {
					ed.selection.setContent('[eve char="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Corp button */
			ed.addButton('eve_corp', {
				title: 'Eve Online Corporation',
				image: url + '/eve_corp.png',
				onclick: function() {
					ed.selection.setContent('[eve corp="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Alliance button */
			ed.addButton('eve_alliance', {
				title: 'Eve Online Alliance',
				image: url + '/eve_alliance.png',
				onclick: function() {
					ed.selection.setContent('[eve alliance="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Station button */
			ed.addButton('eve_station', {
				title: 'Eve Online Station',
				image: url + '/eve_station.png',
				onclick: function() {
					ed.selection.setContent('[eve station="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Solar System button */
			ed.addButton('eve_solarsystem', {
				title: 'Eve Online Solar System',
				image: url + '/eve_solarsystem.png',
				onclick: function() {
					ed.selection.setContent('[eve solarsystem="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Constellation button */
			ed.addButton('eve_constellation', {
				title: 'Eve Online Constellation',
				image: url + '/eve_constellation.png',
				onclick: function() {
					ed.selection.setContent('[eve constellation="' + ed.selection.getContent() + '"]');
				}
			});

			/* Register Eve Online Region button */
			ed.addButton('eve_region', {
				title: 'Eve Online Region',
				image: url + '/eve_region.png',
				onclick: function() {
					ed.selection.setContent('[eve region="' + ed.selection.getContent() + '"]');
				}
			});

		},
		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl: function(n, cm) {
			return null;
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo: function() {
			return {
				longname: 'Eve Online Corporation Plugin for Wordpress',
				author: 'Mitome Cobon-Han',
				authorurl: 'https://github.com/cobon',
				infourl: 'https://github.com/cobon/evecorp',
				version: "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('evecorp', tinymce.plugins.evecorp);
})();