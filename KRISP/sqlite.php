<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    KRISP/pdo.php
 *
 * Sub package of krisp package. The package is includes SQLite2 driver code
 *
 * @category    Database
 * @package     krisp
 * @subpackage  KRISP_driver
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2014 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @filesource
 */

/**
 * KRISP SQLite driver for sqlite2
 * 
 * @package krisp
 */
class KRISP_sqlite
{
	// {{{ properties
	/**
	 * sqlite error messages
	 * @access public
	 * @var string
	 * @static
	 */
	static public $err = '';
	// }}}

	// {{{ (void) __construct (void)
	/**
	 * Initialize sqlite2 driver
	 *
	 * @access public
	 * @return void
	 */
	function __construct () {
		$this->err = &self::$err;
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

	// {{{ (resource) sql_open ($database)
	/**
	 * Opens an SQLite database and create the database if it does not exist
	 *
	 * @access public
	 * @return resource Returns a resource (database handle) on success,
	 *                  FALSE on error.
	 * @param string    The filename of the SQLite database.
	 */
	function sql_open ($database) {
		$c = sqlite_open ($database, 0644);

		if ( ! is_resource ($c) ) {
			self::$err = "Connect failed to $database";
			return false;
		}

		return $c;
	}
	// }}}

	// {{{ (array) sql_select ($dbh, $sql)
	/**
	 * execute db query and return query result
	 *
	 * @access public
	 * @return array
	 * @param resource The SQLite Database resource
	 * @param string SQL query
	 */
	function sql_select ($dbh, $sql) {
		$r = $this->sql_query ($dbh, $sql);

		if ( $r === false )
			return $r;

		$ret = array ();
		while ( is_array ($a = $this->sql_fetch ($r, SQLITE_ASSOC)) )
			array_push ($ret, $a);

		return $ret;
	}
	// }}}

	// {{{ (void) sql_close ($dbh)
	/**
	 * Close sqlite2 db handle
	 *
	 * @access public
	 * @return void
	 * @param resource The SQLite Database resource
	 */
	function sql_close ($dbh) {
		if ( is_resource ($dbh) )
			sqlite_close ($dbh);
	}
	// }}}

	/*
	 * Private methods
	 */

	// {{{ (SQLiteResult) sql_query ($dbh, $sql)
	/**
	 * Executes a query against a given database and returns a result handle
	 *
	 * @access private
	 * @return SQLiteResult The SQLite Database resource
	 * @param  resource The SQLite Database resource
	 * @param  string   SQL query
	 */
	private function sql_query ($dbh, $sql) {
		$r = sqlite_query ($db, $sql);

		if ( ! is_resource ($r) ) {
			self::$err = _sql_error ($dbh);
			return false;
		}

		return $r;
	}
	// }}}

	// {{{ (int) sql_num_rows ($v)
	/**
	 * Returns the number of rows in a buffered result set
	 *
	 * @access private
	 * @return integer
	 * @param  The SQLite result resource.
	 */
	private function sql_num_rows ($v) {
		$r = 0;
		if ( is_resource ($v) )
			$r = sqlite_num_rows ($v);

		return $r;
	}
	// }}}

	// {{{ (array) sql_fetch_array ($v)
	/**
	 * Fetches the next row from a result set as an array
	 *
	 * @access private
	 * @return array
	 * @param  The SQLite result resource.
	 */
	private function sql_fetch_array ($v) {
		$r = sqlite_fetch_array ($v, SQLITE_ASSOC);

		if ( ! is_array ($r) ) {
			self::$err = _sql_error ($dbh);
			return null;
		}

		return $r;
	}
	// }}}

	// {{{ (string) _sql_error ($dbh)
	/**
	 * Returns a human readable description of the error_code, as a string.
	 *
	 * @access private
	 * @return string
	 * @param  The SQLite Database resource
	 */
	private function _sql_error ($dbh) {
		return sqlite_error_string (sqlite_last_error ($dbh));
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
