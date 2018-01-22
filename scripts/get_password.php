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
	$opts = getopt('p:u:', []);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	$pass = OJ_Passwords::get_password($user, $pname);
	print $pass;
}