#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if ($argc > 1)
{
	$eid = 0;
	$optind = 0;
	$required = [
		"entity:"
	];
	$optional = [
	];
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('entity', $opts))
	{
		$eid = $opts['entity'];
	}
	if ($eid > 0)
	{
		$newargv = array_slice($argv, $optind);
		if (count($newargv) > 0)
		{
			$ent = OJ_Row::load_single_object("OJ_Entities", ["id"=>$eid]);
			if ($ent)
			{
				$value = $newargv[0];
				OJ_Entities::set_subtype($ent, $value);
			}
		}
		else
		{
			print OJ_Utilities::usage($argv[0], $required, $optional, 1)."\n";
		}
	}
	else
	{
		print OJ_Utilities::usage($argv[0], $required, $optional, 1)."\n";
	}
}
else
{
	print OJ_Utilities::usage($argv[0], $required, $optional, 1)."\n";
}
