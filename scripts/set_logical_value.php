#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
$ojmode = "db";
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$host = gethostname();
$logical_name = null;
$value = NULL;
$alternative = null;
$logical_category = 0;
$ftp = null;
$opts = getopt('h:l:v:a:c:f:', [], $optind);
if (array_key_exists('h', $opts))
{
	$host = $opts['h'];
}
if (array_key_exists('l', $opts))
{
	$logical_name = $opts['l'];
}
if (array_key_exists('v', $opts))
{
	$value = $opts['v'];
}
if (array_key_exists('a', $opts))
{
	$alternative = $opts['a'];
}
if (array_key_exists('c', $opts))
{
	$logical_category = OJ_Logical_Categories::get_logical_categories_id($opts['c']);
}
if (array_key_exists('f', $opts))
{
	$optsf = explode(",", $opts["f"]);
	$ftp = [];
	$ftp[0] = OJ_Ftp::get_ftp_id($optsf[0]);
	$ftp[1] = count($optsf) > 1?OJ_Ftp::get_ftp_id($optsf[1]):$ftp[0];
}
if ($logical_name)
{
	$log = OJ_Row::load_single_object("OJ_Logicals", ["name"=>$logical_name, "host"=>$host]);
	$doit = true;
	if (!$log)
	{
		if ($value && $logical_category)
		{
			$log = new OJ_Logicals(["name"=>$logical_name, "host"=>$host]);
		}
		else
		{
			print "must set at least value and category\n";
			$doit = false;
		}
	}
	if ($doit)
	{
		if ($value)
		{
			$log->value = $value;
		}
		if ($alternative)
		{
			$log->alternative = $alternative;
		}
		if ($logical_category)
		{
			$log->logical_categories_id = $logical_category;
		}
		if ($ftp)
		{
			$log->value_ftp_id = $ftp[0];
			$log->alternative_ftp_id = $ftp[1];
		}
		$log->save();
	}
}
