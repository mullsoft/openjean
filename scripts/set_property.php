#!/usr/bin/php7.1 -q
<?php

ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 2)
{
	$user = "mike";
	$eid = 0;
	$optind = 0;
	$pname = null;
	$aname = null;
	$catalogname = null;
	$opts = getopt('p:a:u:e:c:', [], $optind);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
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
		$pval = $newargv[0];
		$eids = explode(',', $eid);
		foreach ($eids as $id)
		{
			$prop = OJ_Entities::set_property($id, $pname, $pval);
		}
//		var_dump($prop);
		print "done\n";
	}
}