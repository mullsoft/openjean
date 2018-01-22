#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
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
	if (count($newargv) > 0)
	{
		$val = $newargv[0];
		print "current value: ".OJ_Entities::get_attribute_value($eid, $pname, $aname)."\n";
		OJ_Entities::set_attribute_value($eid, $pname, $aname, $val);
		print "new value: ".OJ_Entities::get_attribute_value($eid, $pname, $aname)."\n";
	}
}