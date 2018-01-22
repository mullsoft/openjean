#!/usr/bin/php
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 0)
{
	$user = "mike";
	$optind = 0;
	$pname = null;
	$opts = getopt('p:u:', [], $optind);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	$newargv = array_slice($argv, $optind);
	if (count($newargv) > 0)
	{
		$value = $newargv[0];
		OJ_Passwords::set_password($user, $pname, $value);
	}
}