<?php
/*
 * Eve Online Plugin for WordPress
 *
 * WP Shortcodes functions library
 *
 * @package evecorp
 */

/**
 * jQuery context menu for Eve Online stuff
 *
 */
function evecorp_menu_scripts()
{
	wp_register_style( 'evecorp-contextMenu', EVECORP_PLUGIN_URL . 'js/jquery.contextMenu.css' );
	wp_register_script( 'jquery.contextMenu', EVECORP_PLUGIN_URL . 'js/jquery.contextMenu.js', array( 'jquery', 'jquery-ui-position' ) );
	wp_register_script( 'evecorp.contextMenu', EVECORP_PLUGIN_URL . 'js/evecorp.contextMenu.js', array( 'jquery.contextMenu', 'jquery-ui-tooltip', 'jquery-effects-core' ) );
//	wp_register_script( 'evecorp.hover', EVECORP_PLUGIN_URL . 'js/evecorp.hover.js', array( 'jquery-ui' ) );
	wp_enqueue_script( 'evecorp.contextMenu' );
//	wp_enqueue_script( 'evecorp.hover' );
	wp_enqueue_style( 'evecorp-contextMenu' );
	wp_enqueue_style( 'jquery-ui-dialog' );
}

/**
 * Returns HTML code with the linked Eve Online character name
 * inlcuding CSS selectors for the jQuery context menu.
 * @todo Themes should be able to supply a custom context menu CSS.
 *
 * @param string $name The name of the character to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_char( $char_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-char';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Get the ID for this character */
	$character_ID = evecorp_get_id( $char_name );
	if ( is_wp_error( $character_ID ) )
		return '<a title="' . $character_ID->get_error_message() . '">' . $char_name . '</a>';

	/* Check if its a player character and not a NPC */
	$type_of_ID = evecorp_get_ID_type( $character_ID );
	if ( 'Character' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $char_name . '</a>';

	/* Get the public profile for this character */
	$character_info = evecorp_get_char_info( $character_ID );
	if ( is_wp_error( $character_info ) )
		return '<a title="' . $character_info->get_error_message() . '">' . $char_name . '</a>';

	/* Get the date of birth */
	$jobs		 = sizeof( $character_info['employmentHistory'] );
	$birthdate	 = $character_info['employmentHistory'][$jobs - 1]['startDate'];

	/* Calculate the age */
	$age = evecorp_human_time_diff( time(), strtotime( $birthdate ) );

	/* Get kills and losses for this character */
	$killz = evecorp_killz_stats( 'character', $character_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $char_name . '</a>';

	/* Render HTML */
	$html = '<a href="https://gate.eveonline.com/Profile/' . $char_name . '"' .
			' class="' . esc_attr( $classes ) . '"' .
			' id="' . esc_attr( $character_ID ) . '"' .
			' name="' . esc_attr( $char_name ) . '"' .
			' security-status="' . esc_attr( number_format( $character_info['securityStatus'], 2 ) ) . '"' .
			' age="' . esc_attr( $age ) . '"' .
			' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"' .
			' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"' .
			' title="Capsuleer"' .
			'>' .
			$char_name .
			'</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online corporation name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $corp_name The name of the corporation to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_corp( $corp_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-corp';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Lookup the ID */
	$corp_ID = evecorp_get_id( $corp_name );
	if ( is_wp_error( $corp_ID ) )
		return '<a title="' . $corp_ID->get_error_message() . '">' . $corp_name . '</a>';

	/* Check if its a player corporation and not a NPC corporation */
	$type_of_ID = evecorp_get_ID_type( $corp_ID );
	if ( 'Corporation' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $corp_name . '</a>';

	/* Get the public profile for this corporation */
	$corp_sheet = evecorp_get_corpsheet( $corp_ID );
	if ( is_wp_error( $corp_sheet ) )
		return '<a title="' . $corp_sheet->get_error_message() . '">' . $corp_name . '</a>';

	/* Get kills and losses for this corporation */
	$killz = evecorp_killz_stats( 'corporation', $corp_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $corp_name . '</a>';


	$html = '<a href="https://gate.eveonline.com/Corporation/' . $corp_name . '"';
	$html .= ' class="' . esc_attr( $classes ) . '"';
	$html .= ' id="' . esc_attr( $corp_ID ) . '"';
	$html .= ' name="' . esc_attr( $corp_name . ' [' . $corp_sheet['ticker'] . ']' ) . '"';

	/* Member of an Alliance? */
	if ( isset( $corp_sheet['allianceName'] ) ) {
		$html .= ' alliance="' . esc_attr( $corp_sheet['allianceName'] ) . '"';
	}
	$html .= ' member_count="' . esc_attr( number_format( $corp_sheet['memberCount'] ) ) . '"';
	$html .= ' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"';
	$html .= ' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"';
	$html .= ' title="Corporation Information">' . $corp_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online alliance name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $alliance_name The name of the alliance to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_alliance( $alliance_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-alliance';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Lookup the ID */
	$alliance_ID = evecorp_get_id( $alliance_name );
	if ( is_wp_error( $alliance_ID ) )
		return '<a title="' . $alliance_ID->get_error_message() . '">' . $alliance_name . '</a>';

	/* Check if its really a alliance */
	$type_of_ID = evecorp_get_ID_type( $alliance_ID );
	if ( 'Alliance' <> $type_of_ID )
		return '<a title="' . $type_of_ID . '">' . $alliance_name . '</a>';

	/* Get some info on this alliance */
	$alliance_info = evecorp_get_alliance_info( $alliance_ID );
	if ( is_wp_error( $alliance_info ) )
		return '<a title="' . $alliance_info->get_error_message() . '">' . $alliance_name . '</a>';

	/* Get kills and losses for this alliance */
	$killz = evecorp_killz_stats( 'alliance', $alliance_ID );
	if ( is_wp_error( $killz ) )
		return '<a title="' . $killz->get_error_message() . '">' . $alliance_name . '</a>';

	$html = '<a href="https://gate.eveonline.com/Alliance/' . $alliance_name .
			'" class="' . esc_attr( $classes ) . '"' .
			' id="' . esc_attr( $alliance_ID ) . '"' .
			' name="' . esc_attr( $alliance_name ) . '"' .
			' corps_count="' . esc_attr( number_format( sizeof( $alliance_info['memberCorporations'] ) ) ) . '"' .
			' member_count="' . esc_attr( number_format( $alliance_info['memberCount'] ) ) . '"' .
			' kills="' . esc_attr( number_format( $killz['totals']['countDestroyed'] ) ) . '"' .
			' losses="' . esc_attr( number_format( $killz['totals']['countLost'] ) ) . '"' .
			' title="Alliance Information">' . $alliance_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online station name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $station_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_station( $station_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-station';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $station_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $station_name . '</a>';
	$html	 = '<a href="http://evemaps.dotlan.net/station/' . $station_name .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $station_name ) .
			'" title="Station Information">' . $station_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online solar system name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $solarsystem_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_solarsystem( $solarsystem_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-solarsystem';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}

	/* Get the ID of this solar system */
	$solar_system_ID = evecorp_get_id( $solarsystem_name );
	if ( is_wp_error( $solar_system_ID ) )
		return '<a title="' . $solar_system_ID->get_error_message() . '"/>' . $solarsystem_name . '</a>';

	/* Get statistics */
	$stats = evecorp_get_solar_system_stats( $solar_system_ID );

	$html = '<a href="http://wiki.eveonline.com/en/wiki/' . $solarsystem_name . '_(System)';
	$html .= '" class="' . esc_attr( $classes );
	$html .= '" id="' . esc_attr( $solar_system_ID );
	$html .= '" name="' . esc_attr( $solarsystem_name );
	$html .= '" jumps="' . esc_attr( number_format( $stats['jumps'] ) );
	$html .= '" shipKills="' . esc_attr( number_format( $stats['shipKills'] ) );
	$html .= '" factionKills="' . esc_attr( number_format( $stats['factionKills'] ) );
	$html .= '" podKills="' . esc_attr( number_format( $stats['podKills'] ) );
	$html .= '" title="Solar System Information">' . $solarsystem_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online constellation name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $constellation_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_constellation( $constellation_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-constellation';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $constellation_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $constellation_name . '</a>';
	$html	 = '<a href="http://wiki.eveonline.com/en/wiki/Category:' . $constellation_name .
			'_%28Constellation%29' .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $constellation_name ) .
			'" title="Constellation Information">' . $constellation_name . '</a>';
	return $html;
}

/**
 * Returns HTML code with the linked Eve Online region name
 * inlcuding CSS selectors for the jQuery context menu.
 *
 * @param string $region_name The name of the solar system to be linked.
 * @return string HTML code to display on page.
 */
function evecorp_region( $region_name )
{
	/* Add CSS and JavaScript for the JQuery context menu */
	evecorp_menu_scripts();

	$classes = 'evecorp-region';

	/* Access from Eve Online in-game browser? */
	if ( evecorp_is_eve() ) {

		$classes .= '-igb';

		/* Are we in the browsers list of trusted sites? */
		if ( evecorp_is_trusted() )
			$classes .=' trusted';
	}
	$id		 = evecorp_get_id( $region_name );
	if ( is_wp_error( $id ) )
		return '<a title="' . $id->get_error_message() . '"/>' . $region_name . '</a>';
	$html	 = '<a href="http://wiki.eveonline.com/en/wiki/Category:' . $region_name .
			'_%28Region%29' .
			'" class="' . esc_attr( $classes ) .
			'" id="' . esc_attr( $id ) .
			'" name="' . esc_attr( $region_name ) .
			'" title="Region Information">' . $region_name . '</a>';
	return $html;
}

/**
 * Output HTML to render a table of corporation members.
 *
 */
function evecorp_the_members()
{
	require_once EVECORP_PLUGIN_DIR . '/classes/class-members-table.php';

	/* Create an instance of the Members_Table class */
	$Members_Table = new evecorp_Members_Table();

	/* Fetch, prepare, sort, and filter our data */
	$Members_Table->prepare_items();

	/* Display */
	?>
	<!-- Begin members list table -->
	<!--<div class="entry-content">-->
	<?php $Members_Table->display() ?>
	<!--</div>-->
	<!-- End members list table -->
	<?php
}
?>
