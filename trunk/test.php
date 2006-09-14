#!/usr/bin/php
<?
# $Id: test.php,v 1.4 2006-09-14 13:08:43 oops Exp $

require_once "krisp.php";

/*
 * INIT krisp pear
 *
 * object new krisp ("database type");
 *
 *    database type:
 *         sqlite2      => sqlite
 *         sqlite3      => sqlite3
 */
$kr = new krisp ("sqlite3");

/*
 * open krisp database
 *
 * resource krisp::kr_open (database_path)
 *
 * if failed, return FALSE
 *
 * $kr->geocity = 1   => search GeoIPCity database. set 0, don't search
 * $kr->geo_type      => GeoIP database open type (default: GEOIP_MEMORY_CACHE | GEOIP_CHECK_CACHE)
 * $kr->geoisp_type   => GeoIPISP database open type (default: GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE)
 * $kr->geocity_type  => GeoIPCity database open type (default: GEOIP_INDEX_CACHE | GEOIP_CHECK_CACHE)
 *
 * GeoIP database open type :
 *       GEOIP_STANDARD
 *       GEOIP_MEMORY_CACHE
 *       GEOIP_INDEX_CACHE
 *       GEOIP_CEHCK_CACHE
 *       see also GeoIP C API Source code (GeoIP_new)
 *
 */
$c = $kr->kr_open ("/usr/share/krisp/krisp.dat");

/*
 * print krisp error message
 *
 * string krisp::kr_error (void)
 */
if ( $c === FALSE ) :
	echo "ERROR: " . $kr->kr_error () . "\n";
	exit (1);
endif;

/*
 * search krisp database
 *
 * array krisp::kr_search (krisp handle, host)
 *
 *   return:
 *      array (
 *             key,
 *             ip,
 *             netmask,
 *             network,
 *             broadcast,
 *             serv,       // ISP code
 *             org,        // ISP name
 *             gcode,      // GeoIP code
 *             gname,      // GeoIP name
 *             gcity,      // GeoIP city name
 *            );
 *
 */
$r = $kr->kr_search ($c, $argv[1]);
print_r ($r);

/*
 * close krisp database
 */
$kr->kr_close ($c);
?>
