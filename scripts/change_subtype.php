#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$subtype_properties = OJ_Row::load_array_of_objects("OJ_Properties", ["name"=>"subtype"]);
foreach ($subtype_properties["result"] as $sp)
{
	$entities_id = $sp->entities_id;
	if ($entities_id > 0)
	{
		$ent = OJ_Row::load_single_object("OJ_Entities", ["id"=>$entities_id]);
		if ($ent)
		{
			$ent->subtype = $sp->values_id;
			$ent->save();
			print "modified $entities_id\n";
		}
	}
}