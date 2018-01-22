#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;
$category_entity_types_id = 1;

$lnks = OJ_Row::load_column("links", "from_entities_id", ["from_catalogs_id"=>$multimedia_catalogs_id, "type"=>"member", "subtype<>"=>"indexentry"]);
$lnksa = array_unique($lnks);
print "found ".count($lnksa)." from ".count($lnks)."\n";
$n = 1;
foreach ($lnksa as $lnkid)
{
	$lnksto = OJ_Row::load_array_of_objects("OJ_Links", ["from_catalogs_id"=>$multimedia_catalogs_id, "to_entities_id"=>$lnkid, "type"=>"child"]);
	foreach ($lnksto["result"] as $lnk)
	{
		$lnk->subtype = "indexentry";
		$lnk->save();
		print $n." done ".$lnk->name." to ".$lnkid."\n";
	}
	$n++;
}