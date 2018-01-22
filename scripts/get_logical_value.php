#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
$ojmode = "db";
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$host = gethostname();
$opts = getopt('h:', [], $optind);
if (array_key_exists('h', $opts))
{
	$host = $opts['h'];
}
$newargv = array_slice($argv, $optind);
$val = [];
foreach ($newargv as $lname)
{
	$val[] = OJ_Row::get_single_value("OJ_Logicals", "value", ["name"=>$lname, "host"=>$host]);
}
echo implode(' ', $val);

