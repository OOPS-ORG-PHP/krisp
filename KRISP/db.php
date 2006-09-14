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
// $Id: db.php,v 1.2 2006-09-14 17:29:09 oops Exp $

class krisp_db
{
	var $type;
	var $db;
	var $err;
	var $otype;

	var $dbn = array (
		'sqlite'  => 'sqlite2',
		'sqlite3' => 'sqlite',
		'mysql'   => 'mysql'
	);

	function krisp_db ($t) {
		$this->type = $t;
		$this->otype = ( $t == 'sqlite' ) ? "krisp_{$t}" : 'krisp_pdo';
		$openfile = ($this->otype == 'krisp_pdo') ? 'pdo' : $t;

		require_once $openfile . ".php";
		$this->db = new $this->otype;
	}

	function kr_dbConnect ($database) {
		switch ($this->type) :
			case 'sqlite' :
				break;
			default :
				$database = $this->dbn[$this->type] . ':' . $database;
		endswitch;

		$c = $this->db->sql_open ($database);

		if ( $c === FALSE ) :
			$this->err = $this->db->sql_error ();
		endif;
		return $c;
	}

	function kr_dbSelect ($dbh, $sql) {
		$r = $this->db->sql_select ($dbh, $sql);
		if ( $r === FALSE ) :
			$this->err = $this->db->sql_error ();
			return FALSE;
		endif;

		return $r;
	}

	function kr_dbClose ($dbh) {
		$this->db->sql_close ($dbh);
	}

	function kr_dbError () {
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
