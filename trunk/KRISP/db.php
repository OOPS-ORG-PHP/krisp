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
// $Id: db.php,v 1.4 2009-10-21 17:13:40 oops Exp $

class krisp_db
{
	static public $type;
	static public $db;
	static public $err;
	static public $otype;

	static private $dbn = array (
		'sqlite'  => 'sqlite2',
		'sqlite3' => 'sqlite',
		'mysql'   => 'mysql'
	);

	function __construct ($t) {
		self::$type = $t;
		self::$otype = ( $t == 'sqlite' ) ? "krisp_{$t}" : 'krisp_pdo';
		$openfile = (self::$otype == 'krisp_pdo') ? 'pdo' : $t;

		require_once $openfile . ".php";
		self::$db = new self::$otype;

		$this->type  = &self::$type;
		$this->db    = &self::$db;
		$this->err   = &self::$err;
		$this->otype = &self::$otype;
	}

	function kr_dbConnect ($database) {
		if ( ! trim ($database) ) {
			self::$err = "nothing database name";
			return false;
		}

		switch (self::$type) {
			case 'sqlite' :
				break;
			default :
				$database = self::$dbn[$this->type] . ':' . $database;
		}

		$c = self::$db->sql_open ($database);

		if ( $c === false )
			self::$err = self::$db->sql_error ();

		return $c;
	}

	function kr_dbSelect ($dbh, $sql) {
		$r = self::$db->sql_select ($dbh, $sql);
		if ( $r === false ) {
			self::$err = self::$db->sql_error ();
			return false;
		}

		return $r;
	}

	function kr_dbClose ($dbh) {
		self::$db->sql_close ($dbh);
	}

	function kr_dbError () {
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
