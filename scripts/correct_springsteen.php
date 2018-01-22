#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;
$category_entity_types_id = 1;

$springsteen = 288400;

$albums = OJ_Row::load_array_of_objects("OJ_Entities", ["name%~%"=>"Springsteen", "entity_types_id"=>[2, 1]]);
foreach ($albums["result"] as $spr)
{
	print $spr->id.": ".$spr->name."\n";
	if ($spr->id != $springsteen)
	{
		$lnk = new OJ_Links(["name"=>$spr->name, "rname"=>"Bruce Springsteen", "from_catalogs_id"=>$multimedia_catalogs_id,
			"to_catalogs_id"=>$multimedia_catalogs_id, "from_entities_id"=>$springsteen, "to_entities_id"=>$spr->id, "entity_types_id"=>$spr->entity_types_id,
			"type"=>"member", "subtype"=>"solo artist"]);
		$lnk->save();
	}
}
