<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    KRISP/db.php
 *
 * Sub package of krisp package. The package is supported db abstraction
 * layer for krips logic.
 *
 * @category    Database
 * @package     krisp
 * @subpackage  KRISP_db
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2012 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @filesource
 */

/**
 * DB abstraction layer class
 * 
 * @package krisp
 */
class KRISP_db
{
	// {{{ properties
	/**#@+
	 * @access public
	 * @static
	 */
	/**
	 * database type
	 * @var string
	 */
	static public $type;
	/**
	 * DB handler
	 * @var object
	 */
	static public $db;
	/**
	 * get error messages
	 * @var string
	 */
	static public $err;
	/**
	 * name of database class
	 * @var string
	 */
	static public $otype;
	/**#@-*/

	/**
	 * Supported db list
	 * @access private
	 * @var array
	 * @static
	 */
	static private $dbn = array (
		'sqlite'  => 'sqlite2',
		'sqlite3' => 'sqlite',
		'mysql'   => 'mysql'
	);
	// }}}

	// {{{ (void) KRISP_db::__construct ($t)
	/**
	 * Initialized KRISP_db class
	 *
	 * @access public
	 * @return void
	 * @param  string database type
	 */
	function __construct ($t) {
		self::$type = $t;
		self::$otype = ( $t == 'sqlite' ) ? "KRISP_{$t}" : 'KRISP_pdo';
		$openfile = (self::$otype == 'KRISP_pdo') ? 'pdo' : $t;

		require_once $openfile . ".php";
		self::$db = new self::$otype;
	}
	// }}}

	// {{{ (object) connect ($database)
	/**
	 * open the database handle
	 *
	 * @access public
	 * @return resource database handle object
	 * @param string database path
	 */
	function connect ($database) {
		if ( ! trim ($database) ) {
			self::$err = "nothing database name";
			return false;
		}

		$nodb = 0;
		if ( ! file_exists ($database) )
			$nodb = 1;

		switch (self::$type) {
			case 'sqlite' :
			default :
				if ( self::$type != 'sqlite3' )
					$nodb = 0;

				if ( ! $nodb )
					$database = self::$dbn[self::$type] . ':' . $database;
		}

		if ( $nodb ) {
			self::$err = sprintf ("%s not found\n", $database ? $database : 'Database file');
			return false;
		}

		$c = self::$db->sql_open ($database);

		if ( $c === false )
			self::$err = self::$db->sql_error ();

		return $c;
	}
	// }}}

	// {{{ (void) select ($dbh, $sql, &$r) {
	/**
	 * execute db query
	 *
	 * @access public
	 * @return void
	 * @param object DB handler
	 * @param string SQL query
	 * @param array Query result
	 */
	function select ($dbh, $sql, &$r) {
		$r = self::$db->sql_select ($dbh, $sql);
		if ( $r === false )
			self::$err = self::$db->sql_error ();
	}
	// }}}

	// {{{ (void) close ($dbh) {
	/**
	 * Close db handle
	 *
	 * @access public
	 * @return void
	 * @param object DB handler
	 */
	function close ($dbh) {
		self::$db->sql_close ($dbh);
	}
	// }}}

	// {{{ function error () {
	/**
	 * retrun db error messages
	 *
	 * @access public
	 * @return string
	 */
	function error () {
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
