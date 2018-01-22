#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJImportExport.php");
if ($argc > 4)
{
	$user = "mike";
	$eid = 0;
	$optind = 0;
	$pname = null;
	$aname = null;
	$catalogname = null;
	$opts = getopt('u:e:c:', [], $optind);
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('e', $opts))
	{
		$eid = $opts['e'];
	}
	if (array_key_exists('c', $opts))
	{
		$catalogname = $opts['c'];
	}
	$newargv = array_slice($argv, $optind);
	$ojsys = OJ_System::instance($user);
	$clog = new OJ_Catalogs(["name" => ucfirst($catalogname)]);
	$oldentityid = OJ_New_Old_Ids::get_old_from_new($clog->get_key(), $eid);
	print "old entity ".dechex($oldentityid)."\n";
	OJ_Importer::import_references_for_entity($ojsys, $clog, $oldentityid);
}