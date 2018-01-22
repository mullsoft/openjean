#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$cid = 0;
$eid = 0;
$optind = 0;
$opts = getopt('u:c:', [], $optind);
if (array_key_exists('u', $opts))
{
	$user = $opts['u'];
}
if (array_key_exists('e', $opts))
{
	$eid = $opts['e'];
}
if (array_key_exists('c', $opts))
{
	$cid = OJ_Catalogs::get_catalog_id($opts['c']);
}
else
{
	$cid = OJ_Catalogs::get_catalog_id('Multimedia');
}
//	$newargv = array_slice($argv, $optind);
$eids = explode(",", $eid);
foreach ($eids as $entities_id)
{
	print "deleting ".$entities_id."\n";
}
print "done\n";
