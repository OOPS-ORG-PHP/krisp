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
// $Id: krisp.php,v 1.1.1.1 2006-06-20 07:49:56 oops Exp $

class _krisp
{
	var $db;
	var $err;
	var $isp = array (
		'key'       => '',
		'ip'        => '',
		'netmask'   => '',
		'network'   => '',
		'broadcast' => '',
		'serv'      => '',
		'org'       => '',
		'code'      => '',
		'nation'    => ''
	);

	function _krisp ($dbr) {
		require_once 'krisp/db.php';
		$this->db = new krdb ($dbr['type']);
	}

	function get_netmask ($dbh, $aclass) {
		$sql = "SELECT subnet FROM netmask WHERE net = '$aclass'";
		$r = $this->db->kr_dbSelect ($dbh, $sql);

		if ( $r === FALSE ) :
			$this->err = $this->db->kr_dbError ();
			return NULL;
		endif;

		$_r = array ();

		if ( ! is_array ($r) ) :
			return NULL;
		endif;

		foreach ( $r as $v ) :
			$_r[] = $v['subnet'];
		endforeach;

		return $_r;
	}

	function kr_ip2long ($v) {
		return sprintf ("%032b", ip2long ($v));
	}

	function getISPinfo ($dbh, $key) {
		$sql = "SELECT * FROM isp WHERE longip = '$key'";
		$r = $this->db->kr_dbSelect ($dbh, $sql);

		if ( $r === FALSE ) :
			$this->err = $this->db->kr_dbError ();
			return 1;
		endif;

		$this->isp['key'] = $key;
		$this->isp['netmask'] = $r[0]['netmask'];
		$this->isp['network'] = $r[0]['network'];
		$this->isp['broadcast'] = $r[0]['broadcast'];
		$this->isp['org'] = $r[0]['organization'];
		$this->isp['serv'] = $r[0]['servicename'];

		return 0;
	}

	function search ($dbr, $host) {
		$_tmp = explode ('.', $host);

		if ( count ($_tmp) != 4 ) :
			$this->isp['serv'] = '--';
			$this->isp['org' ] = 'N/A';
			$this->isp['code'] = '--';
			$this->isp['nation' ] = 'N/A';

			return $this->isp;
		endif;

		$this->isp['ip'] = $host;

		$aclass = $_tmp[0];
		$mask_r = $this->get_netmask ($dbr['handle'], $aclass);

		if ( is_array ($mask_r) ) :
			$ip_c = $this->kr_ip2long ($this->isp['ip']);

			foreach ( $mask_r as $v ) :
				$mask = $this->kr_ip2long ($v);
				$mask_c = $mask & $ip_c;
				$key = bindec ($mask_c);

				if ( $this->getISPinfo ($dbr['handle'], $key) ) :
					continue;
				endif;

				$compare = $this->kr_ip2long ($this->isp['netmask']);
				$compare = bindec ($compare & $ip_c);

				if ( $key == $compare ) :
					$r = 1;
					break;
				endif;

			endforeach;
		endif;

		if ( ! trim ($this->isp['serv']) ) :
			$this->isp['serv'] = '--';
			$this->isp['org'] = 'N/A';
		endif;

		if ( extension_loaded ('geoip') && $dbr['gi'] != NULL ) :
			$gir = GeoIP_id_by_name ($dbr['gi'], $host);
			$this->isp['code'] = $gir['code'];
			$this->isp['nation'] = $gir['name'];
		endif;

		return $this->isp;
	}

	function krisp_error () {
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