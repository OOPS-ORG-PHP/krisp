<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
// $Id: krisp.php,v 1.16 2009-10-21 18:21:29 oops Exp $

require_once 'KRISP/db.php';
require 'KRISP/georegion.php';

class KRISP_engine
{
	static public $db;
	static public $err;
	static public $geocity = 0;
	static private $ISO;
	static private $FIPS;
	static public $isp = array (
		'key'       => '',
		'ip'        => '',
		'netmask'   => '',
		'network'   => '',
		'broadcast' => '',
		'icode'     => '--',
		'iname'     => 'N/A',
		'ccode'     => '--',
		'cname'     => 'N/A',
		'city'      => 'N/A',
		'region'    => 'N/A'
	);

	static public $host = array (
		'ccode'     => '',
		'cname'     => '',
		'icode'     => '',
		'iname'     => '',
		'city'      => '',
		'region'    => '',
		'flag'      => 0
	);

	function __construct ($dbr) {
		self::$db = new krisp_db ($dbr['type']);
		self::$ISO = $GLOBALS['ISO'];
		self::$FIPS = $GLOBALS['FIPS'];

		$this->db      = &self::$db;
		$this->err     = &self::$err;
		$this->geocity = &self::$geocity;
		$this->ISO     = &self::$ISO;
		$this->FIPS    = &self::$FIPS;
		$this->isp     = &self::$isp;
		$this->host    = &self::$host;
	}

	function get_netmask ($dbh, $aclass) {
		$sql = "SELECT subnet FROM netmask WHERE net = '$aclass'";
		$r = self::$db->kr_dbSelect ($dbh, $sql);

		if ( $r === FALSE ) {
			self::$err = self::$db->kr_dbError ();
			return NULL;
		}

		$_r = array ();

		if ( ! is_array ($r) )
			return NULL;

		foreach ( $r as $v )
			$_r[] = $v['subnet'];

		return $_r;
	}

	function kr_ip2long ($v) {
		return sprintf ("%032b", ip2long ($v));
	}

	function getISPinfo ($dbh, $key) {
		$sql = "SELECT * FROM isp WHERE longip = '$key'";
		$r = self::$db->kr_dbSelect ($dbh, $sql);

		if ( $r === FALSE ) {
			self::$err = self::$db->kr_dbError ();
			return 1;
		}

		self::$isp['key'] = $key;
		self::$isp['netmask'] = $r[0]['netmask'];
		self::$isp['network'] = $r[0]['network'];
		self::$isp['broadcast'] = $r[0]['broadcast'];
		self::$isp['iname'] = $r[0]['organization'] ? $r[0]['organization'] : 'N/A';
		self::$isp['icode'] = $r[0]['servicename'] ? $r[0]['servicename'] : '--';

		if ( self::$isp['iname'] != 'N/A' && self::$isp['icode'] == '--' )
			self::$isp['icode'] = self::$isp['iame'];

		if ( self::$isp['icode'] ) {
			self::$isp['ccode'] = 'KR';
			self::$isp['cname'] = 'Korea, Republic of';
		}

		return 0;
	}

	function getHostInfo ($dbh, $host) {
		if ( $dbh === FALSE ) :
			return 1;
		endif;
		$_tmp = explode ('.', $host);
		$cclass = "{$_tmp[0]}.{$_tmp[1]}.{$_tmp[2]}.0";
		$net = bindec (self::kr_ip2long ($cclass));

		$sql = sprintf ('SELECT * FROM userdb WHERE longip = \'%s\'', $net);
		$r = self::$db->kr_dbSelect ($dbh, $sql);

		if ( $r === FALSE ) {
			self::$err = self::$db->kr_dbError ();
			return 1;
		}

		self::$host['ccode']  = $r[0]['country_code'];
		self::$host['cname']  = $r[0]['country'];
		self::$host['icode']  = $r[0]['isp_code'];
		self::$host['iname']  = $r[0]['isp'];
		self::$host['city']   = $r[0]['city'];
		self::$host['region'] = $r[0]['region'];
		self::$host['flag']   = $r[0]['flag'] ? $r[0]['flag'] : 0;

		if ( preg_match ('/([^,]+),(.*)/', self::$host['city'], $m) ) {
			self::$host['city'] = $m[1];
			self::$host['retion'] = $m[2];
		}

		return 0;
	}

	function search ($dbr, $host) {
		$_tmp = explode ('.', $host);

		if ( count ($_tmp) != 4 )
			return self::$isp;

		self::$isp['ip'] = $host;

		$aclass = $_tmp[0];
		$mask_r = self::get_netmask ($dbr['handle'], $aclass);

		if ( is_array ($mask_r) ) {
			$ip_c = self::kr_ip2long (self::$isp['ip']);

			foreach ( $mask_r as $v ) {
				$mask = self::kr_ip2long ($v);
				$mask_c = $mask & $ip_c;
				$key = bindec ($mask_c);

				if ( self::getISPinfo ($dbr['handle'], $key) )
					continue;

				$compare = self::kr_ip2long (self::$isp['netmask']);
				$compare = bindec ($compare & $ip_c);

				if ( $key == $compare ) {
					$r = 1;
					break;
				}

			}
		}

		if ( ! trim (self::$isp['icode']) ) {
			self::$isp['icode'] = '--';
			self::$isp['iname'] = 'N/A';
		}

		if ( extension_loaded ('geoip') ) {
			if ( is_resource ($dbr['gi']['d']) ) {
				$gir = GeoIP_id_by_name ($dbr['gi']['d'], $host);
				self::$isp['ccode'] = $gir['code'];
				self::$isp['cname'] = $gir['name'];
			}
			unset ($gir);
			if ( is_resource ($dbr['gi']['c']) ) {
				$gir = GeoIP_record_by_name ($dbr['gi']['c'], $host);
				#if ( $gir['region'] && ! is_numeric ($gir['region']) ) :
				#	self::$isp['city'] = $gir['region'] . " ";
				#endif;
				#self::$isp['city'] .= $gir['city'];

				self::$isp['region'] = $gir['region'] ? $gir['region'] : 'N/A';
				self::$isp['city'] = $gir['city'];

				if ( ! self::$isp['city'] )
					self::$isp['city'] = "N/A";
			}
			if ( is_resource ($dbr['gi']['p']) ) {
				$gisp = GeoIP_org_by_name ($dbr['gi']['p'], $host);
				if ( $gisp && self::$isp['iname'] == 'N/A' ) {
					self::$isp['icode'] = $gisp;
					self::$isp['iname'] = $gisp;
				}
			}
		}

		if ( ! self::getHostInfo ($dbr['uhandle'], self::$isp['ip']) ) {
			if ( self::$host['flag'] ) {
				self::$isp['ccode'] = self::$host['ccode'] ? self::$host['ccode'] : self::$isp['ccode'];
				self::$isp['cname'] = self::$host['cname'] ? self::$host['cname'] : self::$isp['cname'];
				self::$isp['icode'] = self::$host['icode'] ? self::$host['icode'] : self::$isp['icode'];
				self::$isp['iname'] = self::$host['iname'] ? self::$host['iname'] : self::$isp['iname'];
				self::$isp['city'] = self::$host['city'] ? self::$host['city'] : self::$isp['city'];
				self::$isp['region'] = self::$host['region'] ? self::$host['region'] : self::$isp['region'];
				if ( self::$host['city'] && ! self::$host['region'] )
					self::$isp['region'] = 'N/A';
			} else {
				if ( self::$isp['ccode'] == "--" && self::$host['ccode'] )
					self::$isp['ccode'] = self::$host['ccode'];
				if ( self::$isp['cname'] == "N/A" && self::$host['cname'] )
					self::$isp['cname'] = self::$host['cname'];

				if ( self::$isp['icode'] == "--" && self::$host['icode'] )
					self::$isp['icode'] = self::$host['icode'];
				if ( self::$isp['iname'] == "N/A" && self::$host['iname'] )
					self::$isp['iname'] = self::$host['iname'];

				if ( self::$isp['city'] == "N/A" && self::$host['city'] )
					self::$isp['city'] = self::$host['city'];
				if ( self::$isp['region'] == "N/A" && self::$host['region'] )
					self::$isp['region'] = self::$host['region'];
			}
		}

		$gvar = ( self::$isp['ccode'] == 'CA' || self::$isp['ccode'] == 'US' ) ?
					'ISO' : 'FIPS';
		# region => ${$gvar}[nation_code][region_code]
		self::$isp['region'] = self::${$gvar}[self::$isp['ccode']][self::$isp['region']];
		self::$isp['region'] = self::$isp['region'] ? self::$isp['region'] : 'N/A';

		return self::$isp;
	}

	function krisp_error () {
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
