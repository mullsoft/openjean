#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;

$playlists = OJ_Links::get_all_descendants($multimedia_catalogs_id, 6308, $group_entity_types_id);
foreach ($playlists as $pl)
{
	print $pl.": ".OJ_Entities::get_entity_name($pl)."\n";
	$itemlinks1 = OJ_Row::load_array_of_objects("OJ_Links",["from_entities_id"=>$pl, "entity_types_id"=>3]);
	$itemlinks = $itemlinks1["result"];
	foreach ($itemlinks as $il)
	{
		print "    ".$il->to_entities_id.": ".$il->name."\n";
		$il->type = 'member';
		$il->save();
	}
}