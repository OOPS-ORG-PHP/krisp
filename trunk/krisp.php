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
 * @version		CVS: $Id: krisp.php,v 1.8 2009-10-21 16:31:33 oops Exp $
 * @link		http://pear.oops.org/package/krisp
 * @since		File available since release 0.0.1
 */

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * Base class for KRISP API
 * @package		krisp
 */
class krisp
{
	var $version = "1.2.1";
	var $uversion = "001002001";
	var $dbtype = 'sqlite';
	var $db;
	var $err;
	var $geoipset = 0;
	var $geocity = 0;
	var $geoip_type;
	var $geoisp_type;
	var $geocity_type;

	// {{{ (void) krisp::krisp ($database = 'sqlite')
	/**
	 * Initialize KRISP class
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) Defaults to sqlite. Set type of krisp database.
	 *                  Support type of database are sqlite2, sqlite, mysql
	 */
	function krisp ($database = 'sqlite') {
		require_once "krisp/db.php";
		$this->db = new krisp_db ($database);

		if ( extension_loaded ('geoip') ) :
			$this->geoipset = 1;
		else :
			if ( @ dl ('geoip.so') ) :
				$this->geoipset = 1;
			endif;
		endif;

		if ( $geoipset ) :
			$this->geoip_type   = '';
			$this->geoisp_type  = GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE;
			$this->geocity_type = GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE;
		endif;
	}
	// }}}

	// {{{ (string) krisp::krisp_version (void)
	/**
	 * Returns pear_krisp version
	 *
	 * @access	public
	 * @return	string
	 * @param	void
	 */
	function krisp_version () {
		return $this->version;
	}
	// }}}

	// {{{ (string) krisp::krisp_uversion (void)
	/**
	 * Returns pears version that has numeric strype
	 *
	 * @access	public
	 * @return	string
	 * @param	void
	 */
	function krisp_uversion () {
		return $this->uversion;
	}
	// }}}

	// {{{ priavte (resource) krisp::kr_userdb ($f)
	private function kr_userdb ($f) {
		$u = '';

		if ( file_exists ($f . "-userdb") ) :
			$u = $f . "-userdb";
		else : 
			preg_match ('/(.*)\.dat/', $f, $m);
			$u = $m[1] . "-userdb.dat";

			$u = file_exists ($u) ? $u : '';
		endif; 
        
		return $u;
	}
	// }}}

	// {{{ (resource) krisp::kr_open ($database)
	/**
	 * Open the krisp database and return database handler
	 *
	 * @access	public
	 * @return	resource|false If failed to open database, returns false
	 * @param	string	Database name. If database type is set sqlite or sqlite2, set
	 *                  sqlite database file path.
	 */
	function kr_open ($database) {
		$c = $this->db->kr_dbConnect ($database);
		if ( $c === FALSE ) :
			$this->err = $this->db->kr_dbError ();
			return FALSE;
		endif;

		/* connect user database */
		$u = $this->db->kr_dbConnect ($this->kr_userdb ($database));

		$gi = NULL;

		if ( $this->geoipset ) :
			$gi['d'] = GeoIP_open ($this->goeip_type);
			$gi['p'] = GeoIP_open (GEOIP_ISP_EDITION, $this->geoisp_type);
			$gi['c'] = (geocity) ? GeoIP_open (GEOIP_CITY_EDITION_REV0, $this->geocity_type) : NULL;
		endif;

		$r = array ('handle' => $c, 'uhandle' => $u, 'type' => $this->db->type, 'gi' => $gi);
		
		return $r;
	}
	// }}}

	// {{{ (array) krisp::kr_search ($dbr, $host)
	/**
	 * Search given hostname or ip address on krisp database and returns
	 * information of given hostname of ip address.
	 *
	 * @access	public
	 * @return	array
	 * @param	resource	database handle by krisp::kr_open
	 * @param	string		search host or ip address
	 */
	function kr_search ($dbr, $host) {
		require_once 'krisp/krisp.php';
		$s = new krisp_engine ($dbr);

		$s->geocity = $this->geocity;

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host);

		return $r;
	}
	// }}}

	// {{{ (void) krisp::kr_close ($dbr)
	/**
	 * Close database handle that opend by krisp::kr_open
	 *
	 * @access	public
	 * @return	void
	 * @param	resource database handle by krisp::kr_open
	 */
	function kr_close ($dbr) {
		$this->db->kr_dbClose ($dbr['handle']);
		$this->db->kr_dbClose ($dbr['uhandle']);
		if ( is_resource ($dbr['gi']['d']) ) :
			GeoIP_close ($dbr['gi']['d']);
		endif;
		if ( is_resource ($dbr['gi']['c']) ) :
			GeoIP_close ($dbr['gi']['c']);
		endif;
		if ( is_resource ($dbr['gi']['p']) ) :
			GeoIP_close ($dbr['gi']['p']);
		endif;
	}
	// }}}

	// {{{ (string) krisp::kr_error (void)
	/**
	 * Return libkrisp error string
	 *
	 * @access	public
	 * @return	string	libkrisp error messages.
	 * @parma	void
	 */
	function kr_error () {
		return $this->err;
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
