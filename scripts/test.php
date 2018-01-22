#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
$ojmode = "db";
ini_set('memory_limit', '1024M');
require_once("ojHTML.php");
if ($argc > 1)
{
	$user = "mike";
	$eid = 0;
	$optind = 0;
	$cname = null;
	$mname = null;
	$lname = null;
	$sname = null;
	$paramlist = "";
	$opts = getopt('s:l:c:u:e:m:p:', [], $optind);
	if (array_key_exists('s', $opts))
	{
		$sname = $opts['s'];
	}
	if (array_key_exists('l', $opts))
	{
		$lname = $opts['l'];
	}
	if (array_key_exists('m', $opts))
	{
		$mname = $opts['m'];
	}
	if (array_key_exists('c', $opts))
	{
		$cname = $opts['c'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
	if (array_key_exists('p', $opts))
	{
		$paramlist = $opts['p'];
	}
	$newargv = array_slice($argv, $optind);
	if (($sname != null) && ($mname != null))
	{
		$params = [];
		for ($n = 0; $n < strlen($paramlist); $n++)
		{
			switch ($paramlist[$n])
			{
				case 'e':
					array_push($params, OJ_Entities::get_entity_id($eid));
					break;
				case 'c':
					array_push($params, OJ_Catalogs::get_catalog_id($cname));
					break;
				default:
					break;
			}
		}
		for ($n = 0; $n < count($newargv); $n++)
		{
			if (OJ_Utilities::starts_with($newargv[$n], '{') || OJ_Utilities::starts_with($newargv[$n], '['))
			{
				array_push($params, json_decode($newargv[$n]));
			}
			else
			{
				array_push($params, $newargv[$n]);
			}
		}
//		var_dump($params);
//		$params = [$eid];
		$ret = call_user_func_array([$sname, $mname], $params);
		var_dump($ret);
	}
}