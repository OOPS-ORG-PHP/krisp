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
// $Id: sqlite.php,v 1.3 2009-10-21 17:13:40 oops Exp $

class krisp_sqlite
{
	static public $err = '';

	function __construct () {
		$this->err = &self::$err;
	}

	function sql_error () {
		return self::$err;
	}

	function sql_open ($database) {
		$c = sqlite_open ($database, 0644);

		if ( ! is_resource ($c) ) {
			self::$err = "Connect failed to $database";
			return false;
		}

		return $c;
	}

	function sql_query ($dbh, $sql) {
		$r = sqlite_query ($db, $sql);

		if ( ! is_resource ($r) ) {
			self::$err = _sql_error ($dbh);
			return false;
		}

		return $r;
	}

	function sql_num_rows ($v) {
		$r = 0;
		if ( is_resource ($v) )
			$r = sqlite_num_rows ($v);

		return $r;
	}

	function sql_fetch_array ($v) {
		$r = sqlite_fetch_array ($v, SQLITE_ASSOC);

		if ( ! is_array ($r) ) {
			self::$err = _sql_error ($dbh);
			return null;
		}

		return $r;
	}

	function sql_select ($dbh, $sql) {
		$r = $this->sql_query ($dbh, $sql);

		if ( $r === false )
			return $r;

		$ret = array ();
		while ( is_array ($a = $this->sql_fetch ($r, SQLITE_ASSOC)) )
			array_push ($ret, $a);

		return $ret;
	}

	function sql_close ($dbh) {
		if ( is_resource ($dbh) )
			sqlite_close ($dbh);
	}

	function _sql_error ($dbh) {
		return sqlite_error_string (sqlite_last_error ($dbh));
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
