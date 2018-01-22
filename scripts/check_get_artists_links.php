#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
require_once("OJCatalogs.php");
require_once("oj_multimedia.php");

$eid = 0;
$artistname = null;
$optind = 0;
$required = [
];
$optional = [
	"id:",
	"artist:"
];
if ($argc > 1)
{
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('id', $opts))
	{
		$eid = $opts['id'];
		if ($eid > 0)
		{
			$lnks = OJ_Audio_Utilities::get_artist_links($eid);
			var_dump($lnks);
		}
	}
	if (array_key_exists('artist', $opts))
	{
		$artistname = $opts['artist'];
		if ($artistname)
		{
			$arts = OJ_Audio_Utilities::find_artists($artistname);
			var_dump($arts);
		}
	}
}
else
{
	print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
}
