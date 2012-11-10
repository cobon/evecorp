<?php

/*
 * Eve Online Plugin for WordPress
 *
 * List/table class for Eve Online API keys
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
class evecorp_APIKey_Table extends WP_List_Table {

	var $user_ID;

	/**
	 * Constructor references the parent constructor.
	 * The parent reference sets some default configs.
	 *
	 * @global type $status
	 * @global type $page
	 */
	function __construct()
	{

		global $status, $page;

		/* Set parent defaults */
		parent::__construct( array(
			'singular'	 => 'Eve Online API Key', //singular name of the listed records
			'plural'	 => 'Eve Online API Keys', //plural name of the listed records
			'ajax'		 => true  //does this table support ajax?
		) );
	}

	/**
	 *
	 */
	function no_items()
	{
		_e( 'No API Keys associated with this user.' );
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
			'key_ID'	 => 'API Key ID',
			'username'	 => 'Character',
			'expires'	 => 'Expires',
			'validated'	 => 'Status'
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
			'key_ID' => array( 'key_ID', false ), //true means its already sorted
			'username' => array( 'character_name', false ),
			'expires' => array( 'expires', false ),
			'validated' => array( 'validated', false )
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
				return print_r( $item, true );
		}
	}

	/**
	 * Returns HTML for rendering the 'title' collumn.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_key_ID( $item )
	{
		$html = '<strong>' . $item['key_ID'] . '</strong>';

		/* Define row actions */
		$actions['remove'] = array(
			'args' => array(
				'action'			 => 'remove',
				'key_ID'			 => $item['key_ID']
			),
			'label'				 => 'Delete',
			'html_attributes'	 => array(
				'title'											 => 'Remove this API key.',
			)
		);
		if ( 'Yes' === $item['validated'] )
			$actions['remove']['html_attributes']['onclick'] = 'return confirm(&#039;Are you sure?\nIdentity verification for this API key will be lost!&#039;)';
		if ( 'No' === $item['validated'] )
			$actions['remove']['html_attributes']['onclick'] = 'return confirm(&#039;Are you sure?\nOngoing identity verification for this API key will be cancelled!&#039;)';

		/* Build row action links */
		foreach ( $actions as $action => $param ) {
			$action_links[$action] = '<a href="' . esc_url( add_query_arg( $param['args'] ) ) . '"';
			foreach ( $param['html_attributes'] as $attribute => $value ) {
				$action_links[$action] .= ' ' . $attribute . '="' . $value . '"';
			}
			$action_links[$action] .= '>';
			$action_links[$action] .= $param['label'] . '</a>';
		}

		/* Add external link for API key update on Eve Online support website */
		if ( get_current_user_id() == $this->user_ID ) {
			$action_links['update'] = '<a href="https://support.eveonline.com/api/Key/Update/' .
					$item['key_ID'] . '" title="Update this API Key on the Eve Online Support website"
						target="_BLANK">Update</a>';
		}

		/* Add row actions to Key ID contents */
		$html .= $this->row_actions( $action_links );

		/* Return the Key ID contents */
		return $html;
	}

	/**
	 * Return HTML for rendering the character name column.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_username( $item )
	{
		$size				 = 32;
		$character_ID		 = $item['character_ID'];
		$character_name		 = $item['character_name'];
		$corporation_name	 = $item['corporation_name'];
		if ( is_ssl() ) {
			$protocol = 'https://';
		} else {
			$protocol		 = 'http://';
		}
		$alt			 = $character_name;
		$host			 = 'image.eveonline.com';
		$server_path	 = 'Character';
		$eve_size		 = evecorp_avatar_size( 32 );
		$suffix			 = 'jpg';
		$eve_avatar_url	 = $protocol . trailingslashit( $host ) . trailingslashit( $server_path ) . $character_ID . '_' . $eve_size . '.' . $suffix;
		$eve_avatar		 = "<img alt='{$alt}' src='{$eve_avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		$html			 = $eve_avatar . evecorp_char( $character_name ) . '<br />' . evecorp_corp( $corporation_name );
		return $html;
	}

	/**
	 * Return HTML for rendering the API key expiry date column.
	 *
	 * @param array $item Array containing all column-data for this row.
	 * @return string HTML text data to output.
	 */
	function column_expires( $item )
	{
		/* Date format the expiry */
		if ( !isset( $item['expires'] ) ) {
			return 'unknown';
		} else {
			$date = date_i18n( get_option( 'date_format' ), $item['expires'] );
			if ( $item['expires'] > time() ) {
				$time_left = 'in ' . human_time_diff( time(), $item['expires'] );
			} else {
				$time_left = human_time_diff( $item['expires'], time() ) . ' ago ';
			}
			return $time_left . '<br />' . $date;
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
	 * Remove API keys bulk action.
	 *
	 * @see $this->prepare_items()
	 */
	function process_action()
	{

		/* Detect when a (bulk) action is being triggered... */
		if ( 'remove' === $this->current_action() ) {
			evecorp_userkey_remove( $_REQUEST['user_id'], $_REQUEST['key_ID'] );
		}
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
	function prepare_items( $user_ID )
	{
		$this->user_ID = $user_ID;

		/* How many records per page to show */
		$per_page = 5;

		/* Define column headers and a list of sortable columns */
		$columns = $this->get_columns();
		$hidden	 = array( );
		$sortable = $this->get_sortable_columns();

		/* Column headers */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/* Actions handler */
		$this->process_action();

		/* Get the data to display */
		$keys = get_user_meta( $user_ID, 'evecorp_userkeys', true );
		if ( is_array( $keys ) ) {

			foreach ( $keys as $key => $values ) {
				$row['key_ID']			 = (string) $key;
				$row['character_ID']	 = $values['characterID'];
				$row['character_name']	 = $values['characterName'];
				$row['corporation_name'] = $values['corporationName'];
				$row['expires']			 = strtotime( $values['expires'] );
				$row['validated']		 = $values['validated'];

				/* Access Mask */
				if ( !isset( $row['access_mask'] ) )
					$row['access_mask'] = 'Unknown';

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
			/* If no sort, default to title */
			$orderby = (!empty( $_REQUEST['orderby'] )) ? $_REQUEST['orderby'] : 'key_ID';

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
	 * Blank override method because WP_List_Table generates its own conflicting
	 * nonce to the edit user-profile nonce.
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