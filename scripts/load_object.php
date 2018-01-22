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
	$opts = getopt('c:e:', [], $optind);
	if (array_key_exists('c', $opts))
	{
		$cname = $opts['c'];
	}
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
	$newargv = array_slice($argv, $optind);
	if ($cname != null)
	{
		$ret = OJ_Row::load_single_object($cname, ["id"=>$eid]);
		var_dump($ret);
	}
}