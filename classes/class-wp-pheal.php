<?php

/*
 * Eve Online Plugin for WordPress
 *
 * Pheal class extensions for WordPress
 *
 * @package evecorp
 */

/* Silence is golden. */
if ( !function_exists( 'add_action' ) ) {
	die();
}

/**
 * WordPress implememntation of Pheal
 *
 */
class WP_Pheal extends Pheal {

}

require_once dirname( __FILE__ ) . "/pheal/PhealCacheInterface.php";

/**
 * Implememnts WordPress Transients API into Pheal
 */
class WP_Transients_Pheal implements PhealCacheInterface {

	protected $prefix = "";

	/**
	 * construct PhealMemcache,
	 * @param string $prefix optional prefix_key, defaults to 'Pheal_'
	 * Truncated if longer then 8 characters.
	 */
	public function __construct( $prefix = 'Pheal_' )
	{
		// Prefix
		if ( strlen( $prefix ) > 8 )
			$prefix= substr( $prefix , 0, 7 ).'_';
		$this->prefix = $prefix;
	}

	/**
	 * create a unique identifier (prepend $prefix to not conflict with other identifiers)
	 * @param int $userid
	 * @param string $apikey
	 * @param string $scope
	 * @param string $name
	 * @param array $args
	 * @return string
	 */
	protected function get_uid( $userid, $apikey, $scope, $name, $args )
	{
		$uid = "$userid|$apikey|$scope|$name";
		foreach ( $args as $k => $v ) {
			if ( !in_array( strtolower( $uid ), array( 'userid', 'apikey', 'keyid', 'vcode' ) ) )
				$uid .= "|$k|$v";
		}
		return $this->prefix . md5( $uid );
	}

	/**
	 * Load XML from cache
	 * @param int $userid
	 * @param string $apikey
	 * @param string $scope
	 * @param string $name
	 * @param array $args
	 */
	public function load( $userid, $apikey, $scope, $name, $args )
	{
		$transient = $this->get_uid( $userid, $apikey, $scope, $name, $args );
		return get_transient( $transient );
	}

	/**
	 *  Return the number of seconds the XML is valid. Will never be less than 1.
	 *  @return int
	 */
	protected function getTimeout( $xml )
	{
		$tz = date_default_timezone_get();
		date_default_timezone_set( "UTC" );

		$xml	 = new SimpleXMLElement( $xml );
		$dt		 = (int) strtotime( $xml->cachedUntil );
		$time	 = time();

		date_default_timezone_set( $tz );
		return max( 1, $dt - $time );
	}

	/**
	 * Save XML to cache
	 * @param int $userid
	 * @param string $apikey
	 * @param string $scope
	 * @param string $name
	 * @param array $args
	 * @param string $xml
	 */
	public function save( $userid, $apikey, $scope, $name, $args, $xml )
	{
		$transient	 = $this->get_uid( $userid, $apikey, $scope, $name, $args );
		$expiration = $this->getTimeout( $xml );
		set_transient( $transient, $xml, $expiration );
	}

}
