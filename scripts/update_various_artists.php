#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;
$category_entity_types_id = 1;
$enumerations_id = 3;

$valnks1 = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id"=>461]);
$valnks = $valnks1["result"];
foreach ($valnks as $valnk)
{
	$nrows = OJ_Row::count_rows("OJ_Links", ["from_entities_id"=>459, "to_entities_id"=>$valnk->to_entities_id]);
	if ($nrows > 0)
	{
		print "link from 459 to ".$valnk->to_entities_id." exists $nrows\n";
		$valnk->delete();
	}
	else
	{
		print "link from 459 to ".$valnk->to_entities_id." does not exist\n";
		$valnk->from_entities_id = 459;
		$valnk->save();
	}
}