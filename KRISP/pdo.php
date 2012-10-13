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
// $Id: pdo.php,v 1.2 2006-09-14 17:29:09 oops Exp $

class krisp_pdo
{
	var $err;

	function sql_open ($database) {
		try {
			$db = new PDO ($database);
			return $db;
		} catch (PDOException $e) {
			$this->err = $e->getMessage ();
			return FALSE;
		}

		return $db;
	}

	function sql_select ($dbh, $sql) {
		try {
			$r = array ();
			$ret = $dbh->query ($sql);

			foreach ( $ret as $row ) :
				array_push ($r, $row);
			endforeach;
		} catch (PDOException $e) {
			$this->err = $e->getMessage ();
			return FALSE;
		}

		return $r;
	}

	function sql_close ($dbh) {
		$dbh = null;
	}

	function sql_error () {
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
