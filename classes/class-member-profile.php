<?php
/*
 * Eve Online Plugin for WordPress
 *
 * Profile page class for displaying Eve Online corporation member details.
 *
 * @package evecorp
 */
/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/**
 * Profile page class for displaying Eve Online corporation member details.
 *
 */
class evecorp_Member_Profile {

	/**
	 * The Eve Online ID number of the character to display
	 *
	 * @var string
	 * @access protected
	 */
	var $character_ID;

	/**
	 * The Eve Online character information.
	 *
	 * @var array
	 * @access protected
	 */
	var $character_info;

	/**
	 * The Date of birth.
	 *
	 * @var string
	 */
	var $birthdate;

	/**
	 * The Date of birth as UNIX time() string.
	 *
	 * @var string
	 */
	var $birthtime;

	/**
	 * The Eve Online corporation roles of the character.
	 *
	 * @var array
	 * @access protected
	 */
	var $roles;

	/**
	 * The Eve Online corporation titles of the character.
	 *
	 * @var array
	 * @access protected
	 */
	var $titles;

	/**
	 * HTML to display the Eve Online character portrait image.
	 *
	 * @var string
	 * @access protected
	 */
	var $portrait;

	function __construct()
	{
		;
	}

	function prepare_profile( $character_ID )
	{
		/* Get character information trough Eve Online API */
		$character_info = evecorp_get_char_info( $character_ID );
		if ( is_wp_error( $character_info ) ) {

			/**
			 * @todo Add proper error handling and display.
			 */
			wp_die( var_dump( $character_info ) );
		} else {
			$this->character_ID		 = $character_ID;
			$this->character_info	 = $character_info;
		}

		/* Calculate the date of birth */
		$jobs			 = sizeof( $this->character_info['employmentHistory'] );
		$this->birthdate = $this->character_info['employmentHistory'][$jobs - 1]['startDate'];
		$this->birthtime = strtotime( $this->birthdate );

		/* Get the character portrait */
		$this->portrait = evecorp_get_portrait( $this->character_ID, 200 );

		/* Get the corporation roles trough Eve Online API */
		$this->roles = array( );
		if ( evecorp_is_CEO( $this->character_ID ) ) {
			$roles = array( 'roleCEO' );
		} else {
			$roles = evecorp_get_roles( $this->character_ID );
		}
		if ( is_array( $roles ) ) {
			foreach ( $roles as $role ) {

				/* Make it human */
				$this->roles[] = camelcase_split( substr( $role, 4 ) );
			}
		}

		/* Get the corporation titles trough Eve Online API */
		$this->titles	 = array( );
		$titles			 = evecorp_get_titles( $this->character_ID );
		if ( is_array( $titles ) ) {
			foreach ( $titles as $title ) {
				$this->titles[] = $title;
			}
		}
	}

	function display()
	{
		/**
		 * @todo Allow theme supplied stylesheet.
		 */
		if ( !wp_style_is( 'member-page' ) ) {
			wp_enqueue_style( 'member-page', EVECORP_PLUGIN_URL . 'css/member-profile.css', array( ), EVECORP_VERSION, 'all' );
		}
		?>
		<div style="float: right; width: 100%; position: relative;">
			<div style="float: left; width: 200px; position: relative">
				<div id="profile-portrait" style="clear: both; float: left; position: relative;">
					<?php echo evecorp_get_portrait( $this->character_ID, 200 ); ?>
				</div>
			</div>
			<div style="margin: 10px 15px 10px 200px;">
				<div style="min-height: 400px; height: auto !important; width: 100%; padding: 15px; float: left; position: relative; border: magenta 1px solid;">
					<div id="profile-title">
						<div id="profile-name">
							<p><span id="name"><?php echo evecorp_char( $this->character_info['characterName'] ); ?></span></p>
						</div>
						<div id="profile-security">
							<p>Security Status: <?php $this->the_sec_status(); ?></p>
						</div>
					</div>
					<div style="border:1px yellow solid; clear:right; position: relative;">
						<div style="border:1px green solid;">
							<p>Age: <?php $this->the_age(); ?>
								(born <?php $this->the_date_of_birth(); ?>)<br />
								Race / Bloodline:
								<?php echo $this->character_info['race']; ?> /
								<?php echo $this->character_info['bloodline']; ?>
							</p>
						</div>
						<div style="border:1px blue solid;">
							<p>Member since <?php $this->the_membership(); ?>
								(joined <?php $this->the_joindate(); ?>)<br />
								Roles: <?php $this->the_roles(); ?><br />
								Titles: <?php $this->the_titles(); ?></p>
						</div>
					</div>
				</div>
			</div>
			<div id="profile-sub">
				<div id="profile-sub-menu">
					<div id="profile-history-title">
						<p>Employment History:</p>
					</div>
				</div>
				<div id="profile-sub-history-data">
					<?php $this->the_history(); ?>
				</div>
			</div>
			<?php
		}

		function the_date_of_birth()
		{
			echo date_i18n( get_option( 'date_format' ), $this->birthtime );
		}

		function the_age()
		{
			echo evecorp_human_time_diff( time(), $this->birthtime );
		}

		function the_sec_status()
		{
			$html		 = '';
			$sec_status	 = round( floatval( $this->character_info['securityStatus'] ), 1 );
			if ( $sec_status >= 5 ) {
				$html = '<span class="highsec">';
			} elseif ( $sec_status < 1 && $sec_status > 0 ) {
				$html = '<span class="neutralsec">';
			} elseif ( $sec_status < 0 && $sec_status > -5 ) {
				$html = '<span class="lowsec">';
			} elseif ( $sec_status <= -5 ) {
				$html = '<span class="verylowsec">';
			}
			$html.= strval( $sec_status ) . '</span>';
			echo $html;
		}

		function the_membership()
		{
			$unixtime = strtotime( $this->character_info['corporationDate'] );
			echo evecorp_human_time_diff( $unixtime, time() );
		}

		function the_joindate()
		{
			$unixtime = strtotime( $this->character_info['corporationDate'] );
			echo date_i18n( get_option( 'date_format' ), $unixtime );
		}

		function the_roles()
		{
			$roles_list = '';
			foreach ( $this->roles as $role ) {

				/* Make it human */
				$roles_list .= $role . ', ';
			}
			$roles_html = substr( $roles_list, 0, -2 );
			echo $roles_html;
		}

		function the_titles()
		{
			$titles_list = '';
			foreach ( $this->titles as $title ) {

				/* Make it human */
				$titles_list .= $title . ', ';
			}
			$titles_html = substr( $titles_list, 0, -2 );
			echo $titles_html;
		}

		function the_history()
		{
			$html	 = '';
			$history = $this->character_info['employmentHistory'];
			foreach ( $history as $key => $corp ) {
				$corp_name		 = evecorp_get_corp_name( $corp['corporationID'] );
				$unix_start_date = strtotime( $corp['startDate'] );
				if ( 0 === $key ) {
					$unix_end_date	 = time();
					$adj			 = ' since ';
				} else {
					$unix_end_date	 = strtotime( $history[$key - 1]['startDate'] );
					$adj			 = ' for ';
				}
				/* Skip current corporation */
				if ( 0 === $key )
					continue;
				$html .= 'Joined ';
				$html .= evecorp_corp( $corp_name );
				$html .= ' on ';
				$html .= date_i18n( get_option( 'date_format' ), $unix_start_date );
				$html .= $adj;
				$html .= evecorp_human_time_diff( $unix_start_date, $unix_end_date );
				$html .= '<br />' . PHP_EOL;
			}
			echo $html;
		}

	}