#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
require_once("OJCatalogs.php");
require_once("oj_multimedia.php");

$eid = 0;
$optind = 0;
$required = [
	"album:"
];
$optional = [
];
if ($argc > 1)
{
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('album', $opts))
	{
		$eid = $opts['album'];
	}
	if ($eid > 0)
	{
		$artists = OJ_Audio_Utilities::get_artists_for_album($eid);
		if ($artists)
		{
			foreach ($artists as $type=>$artistsa)
			{
				foreach ($artistsa as $artist)
				{
					print "$type: $artist->name\n";
				}
			}
		}
//		var_dump($artists);
	}
}
else
{
	print "playlist ".OJ_Audio_Utilities::get_playlist_id("mike")."\n";
	print "favorites ".OJ_Audio_Utilities::get_favorites_id("mike")."\n";
	print "music ".OJ_Audio_Utilities::get_music_id()."\n";
	print "audiobooks ".OJ_Audio_Utilities::get_audiobooks_id()."\n";
}


