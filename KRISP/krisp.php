<?php
/**
 * Project: krisp :: KRISP database frontend
 * File:    KRISP/krisp.php
 *
 * Sub package of krisp package. The package is includes KRISP logics
 *
 * @category    Database
 * @package     krisp
 * @subpackage  KRISP_engine
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2012 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @filesource
 */

/**
 * import IPCALC (pear.oops.org/IPCALC) class
 */
require_once 'ipcalc.php';
/**
 * import KRISP_db class
 */
require_once 'KRISP/db.php';

/**
 * Main engine of KRISP class
 * 
 * @package krisp
 */
class KRISP_engine
{
	// {{{ properties
	/**#@+
	 * @access public
	 * @static
	 */
	/**
	 * database handler
	 * @var object
	 */
	static public $db;
	/**
	 * KRISP error messsages
	 * @var string
	 */
	static public $err;
	/**
	 * object of ISP structrure
	 * @var object
	 */
	static public $isp;
	/**#@-*/
	// }}}

	// {{{ (void) __construct ($dbr)
	/**
	 * Initialize KRISP engine and open database
	 *
	 * @access public
	 * @return void
	 * @param  resource database handler
	 */
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
	// }}}

	// {{{ (boolean) getISPinfo ($dbh, $key, $table = null)
	/**
	 * get information of given key
	 *
	 * @access public
	 * @return boolean
	 * @param  object  database handle
	 * @param  integer value of db start field
	 * @param  string  db table name
	 */
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
	// }}}

	// {{{ (object) search ($dbr, $host, $charset = 'utf8') {
	/**
	 * Search given hostname or ip address on krisp database and returns
	 * information of given hostname of ip address.
	 *
	 * @access  public
	 * @return  object
	 * @param   resource database handle by KRISP::open
	 * @param   string   ipv4 ip address
	 * @param   string   (optional) charset of output
	 */
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
	// }}}

	// {{{ (object) search_ex ($dbr, $host, $table, $charset = 'utf8')
	/**
	 * Search given hostname or ip address on user define database and returns
	 * information of given hostname of ip address.
	 *
	 * @access public
	 * @return object
	 * @param  resource database handle by KRISP::open
	 * @param  string   ipv4 dotted ip address
	 * @param  string   user define table
	 * @param  string   (optional) charset of output
	 */
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
	// }}}

	// {{{ (string) krisp_error (void)
	/**
	 * return krips error messages
	 *
	 * @access public
	 * @return string
	 */
	function krisp_error () {
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
