<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    KRISP/pdo.php
 *
 * Sub package of krisp package. The package is includes PDO driver code
 *
 * @category    Database
 * @package     krisp
 * @subpackage  KRISP_driver
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2012 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @filesource
 */

/**
 * KRISP PDO driver for sqlite3 and mysql
 * 
 * @package krisp
 */
class KRISP_pdo
{
	// {{{ properties
	/**
	 * pdo error messages
	 * @access public
	 * @var string
	 * @static
	 */
	static public $err;
	// }}}

	// {{{ (void) __construct (void)
	/**
	 * Initialize PDO driver
	 *
	 * @access public
	 * @return void
	 */
	function __construct () {
		$this->err = &self::$err;
	}
	// }}}

	// {{{ (object) sql_open ($database)
	/**
	 * open the PDO handle
	 *
	 * @access public
	 * @return resource database handle object
	 * @param string database path
	 */
	function sql_open ($database) {
		try {
			$db = new PDO ($database);
			return $db;
		} catch (PDOException $e) {
			self::$err = $e->getMessage ();
			return false;
		}

		return $db;
	}
	// }}}

	// {{{ (array) sql_select ($dbh, $sql)
	/**
	 * execute db query and return query result
	 *
	 * @access public
	 * @return array
	 * @param object DB handler
	 * @param string SQL query
	 */
	function sql_select ($dbh, $sql) {
		try {
			$r = array ();
			$ret = $dbh->query ($sql);

			foreach ( $ret as $row )
				array_push ($r, $row);

		} catch (PDOException $e) {
			self::$err = $e->getMessage ();
			return false;
		}

		return $r;
	}
	// }}}

	// {{{ (void) sql_close ($dbh)
	/**
	 * Close pdo db handle
	 *
	 * @access public
	 * @return void
	 * @param resource DB handler
	 */
	function sql_close ($dbh) {
		$dbh = null;
	}
	// }}}

	// {{{ (string) sql_error (void)
	/**
	 * retrun pdo db error messages
	 *
	 * @access public
	 * @return string
	 */
	function sql_error () {
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
