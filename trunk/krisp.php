<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    krisp.php
 *
 * The libkrisp is supported database that includes informations
 * of Nation/ISP about IP address. The KRISP class is supported
 * libkrisp API that is written with php.
 *
 * @category    Database
 * @package     krisp
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2012 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @example     pear_krisp/test.php Sample code of krisp class
 * @filesource
 */

/**
 * import KRISP_db class
 */
require_once "KRISP/db.php";
/**
 * import KRISP_engine class
 */
require_once 'KRISP/krisp.php';

/**
 * Base class for KRISP API
 * @package		krisp
 */
class KRISP
{
	// {{{ properties
	/**#@+
	 * @access public
	 */
	/**
	 * KRSIP pear version
	 */
	const VERSION = '2.0.0';
	/**
	 * KRSIP pear numeric style version
	 */
	const UVERSION = '002000000';
	/**#@-*/
	/**
	 * libkrisp backend database handle 
	 * @access	private
	 * @var		resource
	 */
	static private $climode = false;
	/**
	 * libkrisp backend database handle 
	 * @access	private
	 * @var		resource
	 */
	static private $db;
	/**
	 * Error messages
	 * @access	public
	 * @var		string
	 */
	static public $err;
	// }}}

	// {{{ (void) KRISP::__construct ($database = 'sqlite')
	/**
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) Defaults to sqlite. Set type of krisp database.
	 *                  Support type of database are sqlite3, sqlite, mysql
	 */
	function __construct ($database = 'sqlite') {
		self::init ($database);

		$this->db           = &self::$db;
		$this->err          = &self::$err;
		$this->climode      = &self::$climode;
	}
	// }}}

	// {{{ (void) KRISP::init ($database = 'sqlite')
	/**
	 * Initialize KRISP class
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) Defaults to sqlite. Set type of krisp database.
	 *                  Support type of database are sqlite3, sqlite, mysql
	 */
	function init ($database = 'sqlite') {
		self::$db = new KRISP_db ($database);
		self::$climode = (php_sapi_name () == 'cli');
	}
	// }}}

	// {{{ (string) KRISP::version (void)
	/**
	 * Returns pear_krisp version
	 *
	 * @access	public
	 * @return	string	pear_krisp version
	 * @param	void
	 */
	function version () {
		return self::VERSION;
	}
	// }}}

	// {{{ (string) KRISP::uversion (void)
	/**
	 * Returns pear_krisp version that has numeric strype
	 *
	 * @access	public
	 * @return	string	numeric pear_krisp version
	 * @param	void
	 */
	function uversion () {
		return self::UVERSION;
	}
	// }}}

	// {{{ (resource) KRISP::open ($database)
	/**
	 * Open the krisp database and return database handler
	 *
	 * @access	public
	 * @return	resource|false If failed to open database, returns false
	 * @param	string	Database name. If database type is set sqlite or sqlite3, set
	 *                  sqlite database file path.
	 */
	function open ($database) {
		$c = self::$db->connect ($database);
		if ( $c === false ) {
			self::$err = self::$db->error ();
			return false;
		}

		$r = array ('handle' => $c, 'type' => self::$db->type);
		
		return $r;
	}
	// }}}

	// {{{ (object) KRISP::search ($dbr, $host[, $charset = 'utf8'])
	/**
	 * Search given hostname or ip address on krisp database and returns
	 * information of given hostname of ip address.
	 *
	 * @access	public
	 * @return	object
	 * @param	resource	database handle by KRISP::open
	 * @param	string		search host or ip address
	 * @param	string	(optional)	charset of output
	 */
	function search ($dbr, $host, $charset = 'utf8') {
		$s = new KRISP_engine ($dbr);

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host, $charset);

		return $r;
	}
	// }}}

	// {{{ (object) KRISP::search_ex ($dbr, $host, $table[, $charset = 'utf8'])
	/**
	 * Search given hostname or ip address on user define database and returns
	 * information of given hostname of ip address.
	 *
	 * @access	public
	 * @return	object
	 * @param	resource	database handle by KRISP::open
	 * @param	string		search host or ip address
	 * @param	string		user define table
	 * @param	string	(optional)	charset of output
	 */
	function search_ex ($dbr, $host, $table, $charset = 'utf8') {
		$s = new KRISP_engine ($dbr);

		$host = gethostbyname ($host);
		$r = $s->search_ex ($dbr, $host, $table, $charset);

		return $r;
	}
	// }}}

	// {{{ (void) KRISP::close ($dbr)
	/**
	 * Close database handle that opend by KRISP::open
	 *
	 * @access	public
	 * @return	void
	 * @param	resource database handle by KRISP::open
	 */
	function close ($dbr) {
		self::$db->close ($dbr['handle']);
	}
	// }}}

	// {{{ (string) KRISP::error (void)
	/**
	 * Return libkrisp error string
	 *
	 * @access	public
	 * @return	string	libkrisp error messages.
	 * @param	void
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
