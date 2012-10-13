<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2006 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: JoungKyun Kim <http://www.oops.org>                          |
// +----------------------------------------------------------------------+
//
// $Id: krisp.php,v 1.6 2006-11-29 09:33:56 oops Exp $

require_once 'PEAR.php';

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * PEAR's krisp:: interface. Defines the php extended krisp library
 *
 * @access public
 * @version $Revision: 1.6 $
 * @package eSystem
 */
class krisp extends PEAR
{
	var $version = "1.2.0";
	var $uversion = "001002000";
	var $dbtype = 'sqlite';
	var $db;
	var $err;
	var $geoipset = 0;
	var $geocity = 0;
	var $geoip_type;
	var $geoisp_type;
	var $geocity_type;

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

	function krisp_version () {
		return $this->version;
	}

	function krisp_uversion () {
		return $this->uversion;
	}

	function kr_userdb ($f) {
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

	function kr_search ($dbr, $host) {
		require_once 'krisp/krisp.php';
		$s = new krisp_engine ($dbr);

		$s->geocity = $this->geocity;

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host);

		return $r;
	}

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

	function kr_error () {
		return $this->err;
	}
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
