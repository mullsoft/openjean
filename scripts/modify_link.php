#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

if ($argc > 0)
{
	$fromid = 0;
	$toid = 0;
	$optind = 0;
	$cid = 1;
	$fromc = 0;
	$toc = 0;
	$tt = null;
	$type = null;
	$field = null;
	$value = null;
	$required = [
		"from:",
		"to:",
		"type:",
		"field:",
		"value:"
	];
	$optional = [
		"fromcatalog:",
		"tocatalog:",
		"subtype:"
	];
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('from', $opts))
	{
		$fromid = $opts['from'];
	}
	if (array_key_exists('to', $opts))
	{
		$toid = $opts['to'];
	}
	if (array_key_exists('field', $opts))
	{
		$field = $opts['field'];
	}
	if (array_key_exists('value', $opts))
	{
		$value = $opts['value'];
	}
	print "$fromid $toid $field $value\n";
	if (($fromid > 0) && ($toid > 0) && $field && $value)
	{
		if (array_key_exists('type', $opts))
		{
			$type = $opts['type'];
		}
		if (array_key_exists('fromcatalog', $opts))
		{
			$cname = $opts['fromcatalog'];
			$fromc = OJ_Catalogs::get_catalog_id($cname);
		}
		if (array_key_exists('tocatalog', $opts))
		{
			$cname = $opts['tocatalog'];
			$toc = OJ_Catalogs::get_catalog_id($cname);
		}
		else
		{
			$toc = $fromc;
		}
		if ($fromc === 0)
		{
			$fromc = OJ_Entities::get_catalogs_id($fromid);
		}
		if ($toc === 0)
		{
			$toc = OJ_Entities::get_catalogs_id($toid);
		}
		print "$fromc $toc\n";
		$hash = ["from_entities_id"=>$fromid, "to_entities_id"=>$toid, "from_catalogs_id"=>$fromc, "to_catalogs_id"=>$toc];
		if ($type)
		{
			$hash["type"] = $type;
		}
		$lnk = OJ_Row::load_single_object("OJ_Links", $hash);
		var_dump($lnk);
		if ($lnk)
		{
			$lnk->$field = $value;
			$lnk->save();
			print "link modified\n";
		}
		else
		{
			print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
		}
//		var_dump($lnk);
	}
	else
	{
		print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
	}
}
else
{
	print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
}
