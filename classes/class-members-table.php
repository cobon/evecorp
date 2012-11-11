<?php
/*
 * Eve Online Plugin for WordPress
 *
 * List/table class for Eve Online corporation members
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/* Load the base class */
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * List/table class for Eve Online API keys
 *
 */
class evecorp_Members_Table extends WP_List_Table {

	var $portrait_size = 96;

	/**
	 * Constructor.
	 *
	 * @global type $status
	 * @global type $page
	 * @param array $args An associative array with information about the current table
	 * @access protected
	 */
	function __construct( $args = array( ) )
	{
//		global $status, $page;

		$args = wp_parse_args( $args, array(
			'plural'	 => '',
			'singular'	 => '',
			'ajax'		 => false
				) );

		$args['plural']		 = sanitize_key( $args['plural'] );
		$args['singular']	 = sanitize_key( $args['singular'] );

		$this->_args = $args;

		wp_enqueue_style( 'member-table', EVECORP_PLUGIN_URL . 'css/members-table.css', array( ), EVECORP_VERSION, 'all' );

		if ( $args['ajax'] ) {
			wp_enqueue_script( 'list-table' );
			//add_action( 'admin_footer', array( &$this, '_js_vars' ) );
		}
	}

	/**
	 *
	 */
	function no_items()
	{
		_e( 'No members.' );
	}

	/**
	 * Return the table's columns and titles as array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	function get_columns()
	{
		$columns = array(
			'name'		 => 'Name',
			'alts'		 => 'Alts',
			'localtime'	 => 'Local Time'
		);
		return $columns;
	}

	/**
	 * Returns a array of the columns which are sortable.
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 */
	function get_sortable_columns()
	{
		$sortable_columns = array(
			'name' => array( 'name', false ), //true means its already sorted
			'localtime' => array( 'timezone-offset', false )
		);
		return $sortable_columns;
	}

	/**
	 * Default fallback method which returns HTML for rendering collumns.
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	function column_default( $item, $column_name )
	{
		switch ( $column_name ) {
			default:
				/* Show the whole array for troubleshooting purposes */
				return print_r( $item[$column_name], true );
		}
	}

	/**
	 * Return HTML for rendering the character name column.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_name( $item )
	{
		$character_ID	 = $item['characterID'];
		$character_name	 = $item['name'];
		$roles			 = $item['roles'];
		$titles			 = $item['titles'];

		/* Get the portrait image */
		$portrait_html = evecorp_get_portrait( $character_ID, $this->portrait_size, $character_name );

		/* List the roles */
		foreach ( $roles as $role ) {
			$roles_html .= $role . ', ';
		}
		$roles_html = substr( $roles_html, 0, -2 );

		/* List the titles */
		foreach ( $titles as $title ) {
			$titles_html .= $title . ', ';
		}
		$titles_html = substr( $titles_html, 0, -2 );

		$html .=$portrait_html .
				'<strong>' . evecorp_char( $character_name ) . '</strong><br />' . $roles_html . '<br />' . $titles_html;
		return $html;
	}

	function column_alts( $item )
	{

		if ( empty( $item['alts'] ) ) {
			return '&nbsp;';
		} else {

			/* Our corporation name */
			$site_corp_name = evecorp_get_option( 'corpkey_corporation_name' );

			/* List the alts */
			foreach ( $item['alts'] as $alt ) {
				$alts_html .= '<div class="alts">';
				$alts_html .= evecorp_get_portrait( $alt['character_ID'], $this->portrait_size / 2 );
				$alts_html .= '<strong>' . evecorp_char( $alt['character_name'] ) . '</strong>';
				if ( $alt['corporation_name'] != $site_corp_name )
					$alts_html .= '<br />' . evecorp_corp( $alt['corporation_name'] );
				$alts_html .= '</div>';
			}
			return $alts_html;
		}
	}

	/**
	 * Return HTML for rendering the API key expiry date column.
	 *
	 * @todo Add time format from browser locale.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_localtime( $item )
	{
		if ( empty( $item['localtime'] ) ) {
			return 'Don\'t know';
		} else {

			/* Day of week short */
			$date_format = 'D';
			if ( get_user_option( 'time_format' ) ) {

				/* Time format from user options */
				$time_format = get_user_option( 'time_format' );
			} elseif ( get_option( 'time_format' ) ) {

				/* Time format from site options */
				$time_format = get_option( 'time_format' );
			} else {

				/* Fallback 24h double-digit */
				$time_format = 'H:i';
			}

			/* Current date/time in the specified time zone. */
			$html = $item['localtime']->format( $date_format . ' ' . $time_format );
			$html .= '<div class="timezone">' . $item['timezone'] . '</div>';
			return $html;
		}
	}

	/**
	 * Return HTML for rendering the API key status column.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_validated( $item )
	{
		if ( $item['expires'] < time() )
			return 'Expired ' . evecorp_icon( 'no' );
		if ( $item['character_ID'] <> get_user_meta( $this->user_ID, 'evecorp_character_ID', true ) )
			return 'Alternate Character';
		if ( $item['validated'] === 'No' )
			return 'Identity verification ' . evecorp_icon( 'maybe' );
		if ( $item['validated'] === 'Yes' )
			return 'Valid and verified ' . evecorp_icon( 'yes' );
		return 'Unknwon status';
	}

	/**
	 * Prepare the data for display.
	 * Query the database, sort and filter the data, and generally get it ready
	 * to be displayed.
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 * @param string $user_ID ID number of the WordPress user beeing edited.
	 */
	function prepare_items()
	{

		/* How many records per page to show */
		$per_page = 5;

		/* Define column headers and a list of sortable columns */
		$columns = $this->get_columns();
		$hidden	 = array( );
		$sortable = $this->get_sortable_columns();

		/* Column headers */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/* Get the data to display */
		$members = evecorp_get_members();
		if ( is_wp_error( $members ) ) {
			$rows = array( );
			return;
		}
		if ( is_array( $members ) ) {

			foreach ( $members as $member ) {

				/* Get roles of member */
				if ( evecorp_is_CEO( $member['characterID'] ) ) {
					$roles = array( 'roleCEO' );
				} else {
					$roles = evecorp_get_roles( $member['characterID'] );
				}
				if ( is_array( $roles ) ) {
					foreach ( $roles as &$role ) {

						/* Make it human */
						$role			 = camelcase_split( substr( $role, 4 ) );
					}
					unset( $role ); // Break the reference.
					$member['roles'] = $roles;
				}

				/* Get titles of member */
				$titles = evecorp_get_titles( $member['characterID'] );
				if ( is_array( $titles ) ) {
					$member['titles'] = $titles;
				}

				/* Get known alts of member */
				$alts = evecorp_get_alts( $member['name'] );

				/* Get timezone of member */

				/* Is the character a registred WP user? */
				$user = get_user_by( 'login', sanitize_user( $member['name'] ) );
				if ( $user ) {

					/* Get his personal time zone settings. */
					$gmt_offset		 = get_user_option( 'evecorp_gmt_offset', $user->ID );
					$timezone_string = get_user_option( 'evecorp_timezone_string', $user->ID );
					if ( !empty( $gmt_offset ) ) {
						$timezone = $gmt_offset;
					}
					if ( !empty( $timezone_string ) ) {
						$timezone	 = $timezone_string;
					}
					/* Timezone object */
					$tz			 = new DateTimeZone( $timezone );

					/* Current date/time in the specified time zone. */
					$localtime = new DateTime( null, $tz );

					/* Calculate GMT offset for sorting */
					$timezone_offset = $tz->getOffset( $localtime );
				} else {
					$localtime		 = '';
					$timezone_offset = '';
				}

				/* Assign */
				$row['name']			 = $member['name'];
				$row['characterID']		 = $member['characterID'];
				$row['titles']			 = $member['titles'];
				$row['roles']			 = $member['roles'];
				$row['alts']			 = $alts;
				$row['localtime']		 = $localtime;
				$row['timezone-offset']	 = $timezone_offset;
				$row['timezone']		 = $timezone;

				/* Add */
				$rows[] = $row;
			}
		}

		/**
		 * Checks user-input for sorting and sort the data accordingly.
		 *
		 * @return string
		 */
		function usort_reorder( $a, $b )
		{
			/* If no sort, default to name */
			$orderby = (!empty( $_REQUEST['orderby'] )) ? $_REQUEST['orderby'] : 'name';

			/* If no order, default to asc */
			$order = (!empty( $_REQUEST['order'] )) ? $_REQUEST['order'] : 'asc';

			/* Determine sort order */
			$result = strcmp( $a[$orderby], $b[$orderby] );

			/* Send final sort direction to usort */
			return ($order === 'asc') ? $result : -$result;
		}

		/* What page the user is currently looking at */
		$current_page = $this->get_pagenum();

		if ( is_array( $rows ) ) {
			usort( $rows, 'usort_reorder' );

			/* How many items are in our data array */
			$total_items = count( $rows );

			/* Ensure that the data is trimmed to only the current page */
			$rows = array_slice( $rows, (($current_page - 1) * $per_page ), $per_page );
		}

		/* Add sorted and trimmed data to the items property */
		$this->items = $rows;

		/* Register pagination options and calculations */
		$this->set_pagination_args( array(
			'total_items'	 => $total_items, //WE have to calculate the total number of items
			'per_page'		 => $per_page, //WE have to determine how many items to show on a page
			'total_pages'	 => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
		) );
	}

	/**
	 * Override method because WP_List_Table generates conflicting nonces.
	 */
	function display_tablenav( $which )
	{
		if ( 'top' == $which )

			?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
		<?php
	}

}