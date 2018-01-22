#!/usr/bin/php
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 0)
{
	$optind = 0;
	$host = null;
	$opts = getopt('h:', [], $optind);
	if (array_key_exists('h', $opts))
	{
		$host = $opts['h'];
	}
	print OJ_Hosts::get_host($host);
}