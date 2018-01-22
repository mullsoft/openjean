#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 2)
{
	$user = null;
	$eid = 0;
	$optind = 0;
	$role = 'user';
	$catalog = null;
	$ojid = 1;
	$opts = getopt('u:r:c:h:', [], $optind);
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('c', $opts))
	{
		$catalog = $opts['c'];
	}
	if (array_key_exists('h', $opts))
	{
		$ojid = $opts['h'];
	}
	$newargv = array_slice($argv, $optind);
	if (($user != null) && ($catalog != null))
	{
		$catid = OJ_Catalogs::get_catalog_id($catalog);
		OJ_Access::stamp($user, $catid, $ojid);
	}
}