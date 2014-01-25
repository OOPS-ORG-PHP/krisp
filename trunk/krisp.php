<?php
/**
 * Project: krisp :: KRISP database pear 패키지<br>
 * File:    krisp.php<br>
 * Dependency: {@link http://devel.oops.org/index.php?c=5 libkrisp}
 *
 * libkrisp는 IP address에 대한 국가/ISP 정보를 포함하고 있는 데이터베이스
 * 이다. KRISP pear 패키지는 libkrisp 데이터베이스를 관리하기 위한 PHP로
 * 작성된 코드 이다.
 *
 * 이 패키지를 사용하기 위해서는 시스템에
 * {@link http://devel.oops.org/index.php?c=5 libkrisp}가
 * 설치 되어 있어야 한다.
 *
 * @category    Database
 * @package     krisp
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2014 JoungKyun.Kim
 * @license     LGPL
 * @version     $Id$
 * @link        http://pear.oops.org/package/krisp
 * @since       File available since release 0.0.1
 * @example     pear_krisp/test.php krisp class 예제 코드
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
 * KRISP API의 frontend Class
 *
 * @package		krisp
 * @example		pear_krisp/test.php KRISP pear 예제 코드
 */
class KRISP
{
	// {{{ properties
	/**#@+
	 * @access public
	 */
	/**
	 * KRSIP pear 버전
	 */
	const VERSION = '2.0.0';
	/**
	 * KRSIP pear 정수형 version
	 */
	const UVERSION = '002000000';
	/**#@-*/
	/**
	 * CLI 모드 구분 변수
	 * @access	private
	 * @var		boolean
	 */
	static private $climode = false;
	/**
	 * libkrisp backend 디비 핸들러
	 * @access	private
	 * @var		resource
	 */
	static private $db;
	/**
	 * 에러 메시지
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
	 * @param	string (optional) 기본값은 <b>sqlite</b>이다. 접근할 libkrisp
	 *                 database의 형식을 확인하여 지정을 한다. 지원하는
	 *                 database 형식은 sqlite3, sqlite, mysql을 지원한다.
	 */
	function __construct ($database = 'sqlite') {
		self::init ($database);
	}
	// }}}

	// {{{ (void) KRISP::init ($database = 'sqlite')
	/**
	 * KRISP class 초기화
	 *
	 * new를 이용하지 않을 경우 호출을 한다. new를 이용하여 초기화를
	 * 할 경우, 자동으로 이 method는 호출이 된다.
	 *
	 * @access	public
	 * @return	void
	 * @param	string	(optional) 기본값은 <b>sqlite</b>이다. 접근할 libkrisp
	 *                  database의 형식을 확인하여 지정을 한다. 지원하는
	 *                  database 형식은 sqlite3, sqlite, mysql을 지원한다.
	 */
	static function init ($database = 'sqlite') {
		self::$db = new KRISP_db ($database);
		self::$climode = (php_sapi_name () == 'cli');
	}
	// }}}

	// {{{ (string) KRISP::version (void)
	/**
	 * pear_krisp version을 반환
	 *
	 * @access	public
	 * @return	string	pear_krisp 버전
	 * @param	void
	 */
	static function version () {
		return self::VERSION;
	}
	// }}}

	// {{{ (string) KRISP::uversion (void)
	/**
	 * 정수형 pear_krisp 버전을 반환
	 *
	 * @access	public
	 * @return	string	정수형 pear_krisp version
	 * @param	void
	 */
	static function uversion () {
		return self::UVERSION;
	}
	// }}}

	// {{{ (resource) KRISP::open ($database)
	/**
	 * KRISP::init method에서 지정한 database 형식으로 krisp
	 * databse를 열고, database handle을 반환한다.
	 *
	 * @access	public
	 * @return	resource|false database 오픈 실패시에 false를 반환
	 * @param	string	database 이름. KRISP::init mehtod에서 database
	 *                  형식을 sqlite나 sqlite3으로 지정했을 경우 sqlite
	 *                  절대 경로를 지정
	 */
	static function open ($database) {
		$c = self::$db->connect ($database);
		if ( $c === false ) {
			self::$err = self::$db->error ();
			return false;
		}

		$db = self::$db;
		$r = array ('handle' => $c, 'type' => $db::$type);
		
		return $r;
	}
	// }}}

	// {{{ (object) KRISP::search ($dbr, $host[, $charset = 'utf8'])
	/**
	 * 지정한 호스트 이름이나 IPv4 주소를 krisp database에서 검색하여,
	 * 해당 정보를 반환한다.
	 *
	 * @access	public
	 * @return	stdClass
	 *   <pre>
	 *   stdClass Object
	 *   (
	 *       [ip]        => 지정한 IP 주소
	 *       [start]     => 지정한 IP 주소가 포함된 블럭의 시직 IP 주소
	 *       [end]       => 지정한 IP 주소가 포함된 블럭의 마지막 IP 주소
	 *       [netmask]   => 지정한 IP 주소가 포함된 블럭의 네트워크 마스크
	 *       [network]   => 지정한 IP 주소가 포함된 블럭의 네트워크 주소
	 *       [broadcast] => 지정한 IP 주소가 포함된 블럭의 브로드캐스트 주소
	 *       [icode]     => 지정한 IP 주소가 포함된 블럭의 ISP 코드
	 *       [iname]     => 지정한 IP 주소가 포함된 블럭의 ISP 이름
	 *       [ccode]     => 지정한 IP 주소가 포함된 블럭의 국가 코드
	 *       [cname]     => 지정한 IP 주소가 포함된 블럭의 국가 이름
	 *   )
	 *   </pre>
	 *
	 * @param	resource KRISP::open mehtod로 연 database handle
	 * @param	string   호스트 이름 또는 IPv4 주소
	 * @param	string   (optional)	출력할 문자셋. 기본값으로 <b>utf8</b>을 사용
	 */
	static function search ($dbr, $host, $charset = 'utf8') {
		$s = new KRISP_engine ($dbr);

		$host = gethostbyname ($host);
		$r = $s->search ($dbr, $host, $charset);

		return $r;
	}
	// }}}

	// {{{ (object) KRISP::search_ex ($dbr, $host, $table[, $charset = 'utf8'])
	/**
	 * 지정한 호스트 이름이나 IPv4 주소를 krisp database의 지정한 table에서
	 * 검색하여, 해당 정보를 반환한다.
	 *
	 * 이 기능은 libkrisp의 user defined database를 생성했을 경우, 기본 table이
	 * 아닌 다른 table의 정보를 검색할 수 있도록 한다.
	 *
	 * @access	public
	 * @return	stdClass
	 * @param	resource KRISP::open mehtod로 연 database handle
	 * @param	string   호스트 이름 또는 IPv4 주소
	 * @param	string   user define table
	 * @param	string   (optional) 출력할 문자셋. 기본값으로 <b>utf-8</b>
	 */
	static function search_ex ($dbr, $host, $table, $charset = 'utf8') {
		$s = new KRISP_engine ($dbr);

		$host = gethostbyname ($host);
		$r = $s->search_ex ($dbr, $host, $table, $charset);

		return $r;
	}
	// }}}

	// {{{ (void) KRISP::close ($dbr)
	/**
	 * KRISP::open method로 연 database handle을 닫는다.
	 *
	 * @access	public
	 * @return	void
	 * @param	resource KRISP::open method로 연 database handle
	 */
	static function close ($dbr) {
		self::$db->close ($dbr['handle']);
	}
	// }}}

	// {{{ (string) KRISP::error (void)
	/**
	 * libkrisp의 에러 메시지를 반환한다.
	 *
	 * @access	public
	 * @return	string libkrisp 에러 메시지
	 * @param	void
	 */
	static function error () {
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
