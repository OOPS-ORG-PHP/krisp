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
// $Id: sqlite.php,v 1.1.1.1 2006-06-20 07:49:56 oops Exp $

class sqlite
{
	var $err = '';

	function sql_error () {
		return $this->err;
	}

	function sql_open ($database) {
		$c = @sqlite_open ($database, 0644);

		if ( ! is_resource ($c) ) :
			$this->err = "Connect failed to $database";
			return FALSE;
		endif;

		return $c;
	}

	function sql_query ($dbh, $sql) {
		$r = sqlite_query ($db, $sql);

		if ( ! is_resource ($r) ) :
			$this->err = _sql_error ($dbh);
			return FALSE;
		endif;

		return $r;
	}

	function sql_num_rows ($v) {
		$r = 0;
		if ( is_resource ($v) ) :
			$r = sqlite_num_rows ($v);
		endif;

		return $r;
	}

	function sql_fetch_array ($v) {
		$r = sqlite_fetch_array ($v, SQLITE_ASSOC);

		if ( ! is_array ($r) ) :
			$this->err = _sql_error ($dbh);
			return NULL;
		endif;

		return $r;
	}

	function sql_select ($dbh, $sql) {
		$r = $this->sql_query ($dbh, $sql);

		if ( $r === FALSE )
			return $r;

		$ret = array ();
		while ( is_array ($a = $this->sql_fetch ($r, SQLITE_ASSOC)) ) :
			array_push ($ret, $a);
		endwhile;

		return $ret;
	}

	function sql_close ($dbh) {
		if ( is_resource ($dbh) ) :
			sqlite_close ($dbh);
		endif;
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
