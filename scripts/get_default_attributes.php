#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 0)
{
	$optind = 0;
	$cid = 1;
	$etype = "ITEM";
	$opts = getopt('c:t:', [], $optind);
	if (array_key_exists('t', $opts))
	{
		$etype = $opts['t'];
	}
	if (array_key_exists('c', $opts))
	{
		$cname = $opts['c'];
		$cid = OJ_Catalogs::get_catalog_id($cname);
	}
	if ($cid > 0)
	{
		// dump catalog
		$atts = OJ_Entities::get_default_attributes($cid, $etype);
		var_dump($atts);
	}
}