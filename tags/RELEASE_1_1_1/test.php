#!/usr/bin/php
<?
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
