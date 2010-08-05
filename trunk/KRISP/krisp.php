<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
// $Id: krisp.php,v 1.18 2010-08-05 14:34:25 oops Exp $

require_once 'ipcalc.php';
require_once 'KRISP/db.php';

class KRISP_engine
{
	static public $db;
	static public $err;
	static public $isp;

	function __construct ($dbr) {
		self::$db = new krisp_db ($dbr['type']);
		self::$isp = (object) array (
			'ip'        => '',
			'start'     => '',
			'end'       => '',
			'netmask'   => '',
			'network'   => '',
			'broadcast' => '',
			'icode'     => '--',
			'iname'     => 'N/A',
			'ccode'     => '--',
			'cname'     => 'N/A',
		);

		$this->db   = &self::$db;
		$this->err  = &self::$err;
		$this->isp  = &self::$isp;
	}

	function getISPinfo ($dbh, $key, $table = null) {
		$table_name = $table ? $table : 'krisp';

		$lkey = IPCALC::ip2long ($key);
		$sql = "SELECT * FROM {$table_name} WHERE start <= '{$lkey}' ORDER by start DESC LIMIT 1";
		self::$db->select ($dbh, $sql, $r);

		if ( $r === false ) {
			self::$err = self::$db->error ();
			return $r;
		}

		self::$isp->ip = $key;
		self::$isp->start = $r[0]['start'];
		self::$isp->end   = $r[0]['end'];

		if ( $table !== null ) {
			unset (self::$isp->ccode);
			unset (self::$isp->cname);
			unset (self::$isp->icode);
			unset (self::$isp->iname);
			self::$isp->dummy = explode ('|', $r[0]['data']);

			self::$isp->netmask   = IPCALC::guess_netmask (self::$isp->start, self::$isp->end);
			self::$isp->network   = IPCALC::network (self::$isp->start, self::$isp->netmask);
			self::$isp->broadcast = IPCALC::broadcast (self::$isp->start, self::$isp->netmask);

			return true;
		}

		$data = explode ('|', $r[0]['data']);
		self::$isp->ccode = $data[0];
		self::$isp->cname = $data[1];
		self::$isp->icode = $data[2];
		self::$isp->iname = $data[3];

		if ( ! self::$isp->icode && self::$isp->iname )
			self::$isp->icode = self::$isp->iname;

		self::$isp->netmask   = IPCALC::guess_netmask (self::$isp->start, self::$isp->end);
		self::$isp->network   = IPCALC::network (self::$isp->start, self::$isp->netmask);
		self::$isp->broadcast = IPCALC::broadcast (self::$isp->start, self::$isp->netmask);

		return true;
	}

	function search ($dbr, $host, $charset = 'utf8') {
		$_tmp = explode ('.', $host);

		if ( count ($_tmp) != 4 )
			return self::$isp;

		self::$isp->ip = $host;

		if ( self::getISPinfo ($dbr['handle'], $host) === false )
			return false;

		if ( ! trim (self::$isp->icode) ) {
			self::$isp->icode = '--';
			self::$isp->iname = 'N/A';
		}

		if ( $charset != 'utf8' ) {
			$target = array ('iname');
			foreach ( $target as $var )
				self::$isp->$var = iconv ('utf-8//IGNORE', $charset, self::$isp->$var);
		}

		return self::$isp;
	}

	function search_ex ($dbr, $host, $table, $charset = 'utf8') {
		$_tmp = explode ('.', $host);

		if ( count ($_tmp) != 4 )
			return self::$isp;

		self::$isp->ip = $host;

		if ( self::getISPinfo ($dbr['handle'], $host, $table) === false )
			return self::$isp;

		if ( $charset != 'utf8' ) {
			$target = array ('iname');
			foreach ( $target as $var )
				self::$isp->$var = iconv ('utf-8//IGNORE', $charset, self::$isp->$var);
		}

		return self::$isp;
	}

	function krisp_error () {
		return self::$err;
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
