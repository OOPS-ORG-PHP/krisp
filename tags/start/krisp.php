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
// $Id: krisp.php,v 1.1.1.1 2006-06-20 07:49:56 oops Exp $

require_once 'PEAR.php';

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * PEAR's krisp:: interface. Defines the php extended krisp library
 *
 * @access public
 * @version $Revision: 1.1.1.1 $
 * @package eSystem
 */
class krisp extends PEAR
{
	var $version = "1.0.4";
	var $uversion = "001000004";
	var $dbtype = 'sqlite';
	var $db;
	var $err;

	function krisp ($database = 'sqlite') {
		require_once "krisp/db.php";
		$this->db = new krdb ($database);
	}

	function krisp_version () {
		return $this->version;
	}

	function krisp_uversion () {
		return $this->uversion;
	}

	function kr_open ($database) {
		$c = $this->db->kr_dbConnect ($database);
		if ( $c === FALSE ) :
			$this->err = $this->db->kr_dbError ();
		endif;

		if ( extension_loaded ('geoip') ) :
			$gi = GeoIP_open ();
		else :
			$gi = NULL;
		endif;

		$r = array ('handle' => $c, 'type' => $this->db->type, 'gi' => $gi);
		
		return $r;
	}

	function kr_search ($dbr, $host) {
		require_once 'krisp/krisp.php';
		$s = new _krisp ($dbr);

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host);

		return $r;
	}

	function kr_close ($dbr) {
		$this->db->kr_dbClose ($dbr['handle']);
		if ( $gi != NULL ) :
			GeoIP_close ($dbr['gi']);
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