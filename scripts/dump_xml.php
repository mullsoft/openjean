#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 0)
{
	$eid = 0;
	$optind = 0;
	$cid = 1;
	$etype = "ITEM";
	$opts = getopt('e:c:t:', [], $optind);
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
	if (array_key_exists('t', $opts))
	{
		$etype = $opts['t'];
	}
	if (array_key_exists('c', $opts))
	{
		$cname = $opts['c'];
		$cid = OJ_Catalogs::get_catalog_id($cname);
	}
	if ($eid > 0)
	{
		// dump entity
		$ent = new OJ_Entities($eid);
		print $ent->to_xml_string();
	}
	else if ($cid > 0)
	{
		// dump catalog
		$cat = new OJ_Catalogs($cid);
		print $cat->to_xml_string();
	}
}