#!/usr/bin/php
<?
# $Id: test.php,v 1.8 2010-08-05 14:34:25 oops Exp $

require_once "krisp.php";

echo "*************** Object mode test ***************\n";

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
 * resource KRISP::kr_open (database_path)
 *
 * if failed, return FALSE
 *
 */
$c = $kr->open ("/usr/share/krisp/krisp.dat");

/*
 * print krisp error message
 *
 * string KRISP::error (void)
 */
if ( $c === FALSE ) {
	echo "ERROR: " . $kr->error () . "\n";
	exit (1);
}

/*
 * search krisp database
 *
 * array KRISP::search (krisp handle, host[, charset = utf8])
 *
 *   return:
 *      array (
 *             key,
 *             ip,
 *             netmask,
 *             network,
 *             broadcast,
 *             icode,      // ISP code
 *             iname,      // ISP name
 *             ccode,      // GeoIP code
 *             cname,      // GeoIP name
 *            );
 *
 */
$r = $kr->search ($c, $argv[1]);
print_r ($r);

/*
 * search user defined database
 *
 * array KRISP::search_ex (krisp handle, host, table[, charset = utf8])
 *
 *   return:
 *      array (
 *             key,
 *             ip,
 *             netmask,
 *             network,
 *             broadcast,
 *             dummy, // array
 *            );
 *
 */

/*
 * close krisp database
 */
$kr->close ($c);

echo "*************** Self   mode test ***************\n";

KRISP::init ('sqlite3');
#KRISP::$geocity = false;
$c = KRISP::open ('/usr/share/krisp/krisp.dat');
if ( $c === false ) {
	echo "ERROR: " . KRISP::error () . "\n";
	exit (1);
}
$r = KRISP::search ($c, $argv[1]);
#$r = KRISP::search ($c, $argv[1], 'cp949');
print_r ($r);
KRISP::close ($c);

?>
