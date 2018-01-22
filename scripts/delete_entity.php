#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 1)
{
	$user = "mike";
	$eid = 0;
	$optind = 0;
	$opts = getopt('u:e:', [], $optind);
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
//	$newargv = array_slice($argv, $optind);
	$eids = explode(",", $eid);
	foreach ($eids as $entities_id)
	{
		print "deleting ".$entities_id."\n";
		OJ_Entities::tree_delete($entities_id);
	}
	print "done\n";
}