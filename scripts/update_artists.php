#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$artists_catalog_id = 2;
$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$category_entity_types_id = 1;
$enumerations_id = 3;

$artists1 = OJ_Row::load_array_of_objects("OJ_Entities", ["catalogs_id"=>$artists_catalog_id, "entity_types_id"=>$item_entity_types_id]);
$artists = $artists1["result"];

foreach ($artists as $artist)
{
	$artist->entity_types_id = $category_entity_types_id;
	$val = new OJ_Enumeration_Values(["value"=>"artist", "enumerations_id"=>$enumerations_id]);
	$artist->subtype = $val->save();
	$lnks1 = OJ_Row::load_array_of_objects("OJ_Links", ["from_catalogs_id"=>$artists_catalog_id, "to_catalogs_id"=>$artists_catalog_id, "to_entities_id"=>$artist->id]);
	$lnks = $lnks1["result"];
	foreach ($lnks as $lnk)
	{
		$lnk->entity_types_id = $category_entity_types_id;
		$lnk->save();
	}
	$artist->save();
	print "$artist->name \n";
}