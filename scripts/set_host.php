#!/usr/bin/php
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 0)
{
	$optind = 0;
	$host = null;
	$ip = null;
	$address = null;
	$opts = getopt('h:a:i:', [], $optind);
	if (array_key_exists('h', $opts))
	{
		$host = $opts['h'];
	}
	if (array_key_exists('a', $opts))
	{
		$address = $opts['a'];
	}
	if (array_key_exists('i', $opts))
	{
		$ip = $opts['i'];
	}
	OJ_Hosts::set_host($host, $address, $ip);
}