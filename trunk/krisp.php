<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    krisp.php
 *
 * PHP Version 5
 *
 * Copyright (c) 1997-2009 JoungKyun.Kim
 *
 * LICENSE: GPL v2
 *
 * @category	Database
 * @package		krisp
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	1997-2009 OOPS.org
 * @license		GPL v2
 * @version		CVS: $Id: krisp.php,v 1.12 2009-10-21 18:03:08 oops Exp $
 * @link		http://pear.oops.org/package/krisp
 * @since		File available since release 0.0.1
 */

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

require_once "KRISP/db.php";
require_once 'KRISP/krisp.php';

/**
 * Base class for KRISP API
 * @package		krisp
 */
class KRISP
{
	// {{{ properties
	/**
	 * KRSIP pear version
	 * @accss	public
	 * @const	string
	 */
	const VERSION = '1.2.1';
	/**
	 * KRSIP pear numeric style version
	 * @accss	public
	 * @const	string
	 */
	const UVERSION = '001002001';
	/**
	 * libkrisp backend database handle 
	 * @accss	private
	 * @var		resource
	 */
	static private $db;
	/**
	 * Error messages
	 * @accss	public
	 * @var		string
	 */
	static public $err;
	/**
	 * Whether suooprt geoip extension no don't
	 * @accss	private
	 * @var		integer
	 */
	static private $geoipset = 0;
	/**
	 * Whether check geoip database or don't
	 * @accss	public
	 * @var		string
	 */
	static public $geocity = 0;
	/**
	 * GeoIP.dat open flag.
	 * Defaults 'GEOIP_MEMORY_CACHE | GEOIP_CHECK_CACHE'
	 * @accss	public
	 * @var		and operation (integer)
	 */
	static public $geoip_type;
	/**
	 * GeoIPISP.dat open flag.
	 * Defaults 'GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE'
	 * @accss	public
	 * @var		and operation (integer)
	 */
	static public $geoisp_type;
	/**
	 * GeoIPCity.dat open flag.
	 * Defaults 'GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE'
	 * @accss	public
	 * @var		and operation (integer)
	 */
	static public $geocity_type;
	// }}}

	// {{{ (void) KRISP::__construct ($database = 'sqlite')
	/**
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) Defaults to sqlite. Set type of krisp database.
	 *                  Support type of database are sqlite3, sqlite, mysql
	 */
	function __construct ($database = 'sqlite') {
		self::init ($database);

		$this->db           = &self::$db;
		$this->err          = &self::$err;
		$this->geoipset     = &self::$geoipset;
		$this->geocity      = &self::$geocity;
		$this->geoip_type   = &self::$geoip_type;
		$this->geoisp_type  = &self::$geoisp_type;
		$this->geocity_type = &self::$geocity_type;
	}
	// }}}

	// {{{ (void) KRISP::krisp ($database = 'sqlite')
	/**
	 * Initialize KRISP class
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) Defaults to sqlite. Set type of krisp database.
	 *                  Support type of database are sqlite3, sqlite, mysql
	 */
	function init ($database = 'sqlite') {
		self::$db = new KRISP_db ($database);

		if ( extension_loaded ('geoip') )
			self::$geoipset = 1;
		else {
			if ( @ dl ('geoip.so') )
				self::$geoipset = 1;
		}

		if ( self::$geoipset ) {
			self::$geoip_type   = '';
			self::$geoisp_type  = GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE;
			self::$geocity_type = GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE;
		}
	}
	// }}}

	// {{{ (string) KRISP::krisp_version (void)
	/**
	 * Returns pear_krisp version
	 *
	 * @access	public
	 * @return	string
	 * @param	void
	 */
	function krisp_version () {
		return self::VERSION;
	}
	// }}}

	// {{{ (string) KRISP::krisp_uversion (void)
	/**
	 * Returns pears version that has numeric strype
	 *
	 * @access	public
	 * @return	string
	 * @param	void
	 */
	function krisp_uversion () {
		return self::UVERSION;
	}
	// }}}

	// {{{ priavte (resource) KRISP::kr_userdb ($f)
	private function kr_userdb ($f) {
		$u = '';

		if ( file_exists ($f . "-userdb") )
			$u = $f . "-userdb";
		else {
			preg_match ('/(.*)\.dat/', $f, $m);
			$u = $m[1] . "-userdb.dat";

			$u = file_exists ($u) ? $u : '';
		}
        
		return $u;
	}
	// }}}

	// {{{ (resource) KRISP::kr_open ($database)
	/**
	 * Open the krisp database and return database handler
	 *
	 * @access	public
	 * @return	resource|false If failed to open database, returns false
	 * @param	string	Database name. If database type is set sqlite or sqlite3, set
	 *                  sqlite database file path.
	 */
	function kr_open ($database) {
		$c = self::$db->kr_dbConnect ($database);
		if ( $c === false ) {
			self::$err = self::$db->kr_dbError ();
			return false;
		}

		/* connect user database */
		$u = self::$db->kr_dbConnect (self::kr_userdb ($database));

		$gi = null;

		if ( self::$geoipset ) {
			$gi['d'] = GeoIP_open (self::$geoip_type);
			$gi['p'] = GeoIP_open (GEOIP_ISP_EDITION, self::$geoisp_type);
			$gi['c'] = (geocity) ? GeoIP_open (GEOIP_CITY_EDITION_REV0, self::$geocity_type) : null;
		}

		$r = array ('handle' => $c, 'uhandle' => $u, 'type' => self::$db->type, 'gi' => $gi);
		
		return $r;
	}
	// }}}

	// {{{ (array) KRISP::kr_search ($dbr, $host)
	/**
	 * Search given hostname or ip address on krisp database and returns
	 * information of given hostname of ip address.
	 *
	 * @access	public
	 * @return	array
	 * @param	resource	database handle by KRISP::kr_open
	 * @param	string		search host or ip address
	 */
	function kr_search ($dbr, $host) {
		$s = new KRISP_engine ($dbr);

		$s->geocity = &self::$geocity;

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host);

		return $r;
	}
	// }}}

	// {{{ (void) KRISP::kr_close ($dbr)
	/**
	 * Close database handle that opend by KRISP::kr_open
	 *
	 * @access	public
	 * @return	void
	 * @param	resource database handle by KRISP::kr_open
	 */
	function kr_close ($dbr) {
		self::$db->kr_dbClose ($dbr['handle']);
		self::$db->kr_dbClose ($dbr['uhandle']);
		if ( is_resource ($dbr['gi']['d']) )
			GeoIP_close ($dbr['gi']['d']);
		if ( is_resource ($dbr['gi']['c']) )
			GeoIP_close ($dbr['gi']['c']);
		if ( is_resource ($dbr['gi']['p']) )
			GeoIP_close ($dbr['gi']['p']);
	}
	// }}}

	// {{{ (string) KRISP::kr_error (void)
	/**
	 * Return libkrisp error string
	 *
	 * @access	public
	 * @return	string	libkrisp error messages.
	 * @parma	void
	 */
	function kr_error () {
		return self::$err;
	}
	// }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>
