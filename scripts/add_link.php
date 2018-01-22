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
	$subtype = null;
	$rname = null;
	$ordinal = 0;
	$hidden = 0;
	$name = null;
	$required = [
				"from:",
				"to:",
				"type:"
	];
	$optional = [
		"fromcatalog:",
		"tocatalog:",
		"subtype:",
		"tooltip:",
		"hidden",
		"ordinal:",
		"rname:",
		"name:"
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
	if (array_key_exists('type', $opts))
	{
		$type = $opts['type'];
	}
	if (($fromid > 0) && ($toid > 0) && $type)
	{
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
		if (array_key_exists('hidden', $opts))
		{
			$hidden = 1;
		}
		if (array_key_exists('ordinal', $opts))
		{
			$ordinal = $opts['ordinal'];
		}
		if (array_key_exists('rname', $opts))
		{
			$rname = $opts['rname'];
		}
		else
		{
			$rname = OJ_Entities::get_entity_name($fromid);
		}
		if ($fromc === 0)
		{
			$fromc = OJ_Entities::get_catalogs_id($fromid);
		}
		if ($toc === 0)
		{
			$toc = OJ_Entities::get_catalogs_id($toid);
		}
		if (array_key_exists('name', $opts))
		{
			$name = $opts['name'];
		}
		else
		{
			$name = OJ_Entities::get_entity_name($toid);
		}
		$hash1 = ["from_entities_id"=>$fromid, "to_entities_id"=>$toid, "from_catalogs_id"=>$fromc, "to_catalogs_id"=>$toid, "type"=>$type];
		$lnk = OJ_Row::load_single_object("OJ_Links", $hash1);
		if ($lnk)
		{
			print "link exists\n";
		}
		else
		{
			$entity_types_id = OJ_Entities::get_entity_types_id($toid);
			$hash = ["from_entities_id"=>$fromid, "to_entities_id"=>$toid, "from_catalogs_id"=>$fromc, "to_catalogs_id"=>$toc, "type"=>$type,
				"ordinal"=>$ordinal, "hidden"=>$hidden, "rname"=>$rname, "name"=>$name, "entity_types_id"=>$entity_types_id];
			if ($subtype)
			{
				$hash["subtype"] = $subtype;
			}
			if ($tt)
			{
				$hash["tooltip"] = $tt;
			}
			print "new link\n";
			$lnk = new OJ_Links($hash);
			$lnk->save();
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
