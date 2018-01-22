#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
$ojmode = "db";
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 4)
{
	$user = "mike";
	$eid = 0;
	$optind = 0;
	$pname = null;
	$aname = null;
	$opts = getopt('p:a:u:e:c:', [], $optind);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
	}
	if (array_key_exists('a', $opts))
	{
		$aname = $opts['a'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
	$newargv = array_slice($argv, $optind);
//	print "value: ".OJ_Entities::get_attribute_value($eid, $pname, $aname)."\n";
	print OJ_Entities::get_attribute_value($eid, $pname, $aname);
}