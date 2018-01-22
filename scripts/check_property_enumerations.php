#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$enumid = OJ_Attribute_Types::get_attribute_type_id("enumeration");
$allprops = OJ_Row::load_array_of_objects("OJ_Properties", ["table_name"=>"enumeration", "entities_id>"=>0, "values_id>"=>0]);
//var_dump($allpages);
$props1 = [];
foreach ($allprops["result"] as $prop)
{
	$ent = OJ_Row::load_single_object("OJ_Entities", ["id"=>$prop->entities_id]);
	$nm = $prop->name."|".$ent->catalogs_id."|".$ent->entity_types_id;
	if (!array_key_exists($nm, $props1))
	{
//		var_dump($ent);
		$props1[$nm] = OJ_Row::get_single_value("OJ_Enumeration_Values", "enumerations_id", ["id"=>$prop->values_id]);
//		print "values_id: ".$prop->values_id.", enumerations_id: ".$nm." ".$props1[$nm]."\n";
	}
}
//var_dump($props1);exit;
foreach ($props1 as $k=>$v)
{
	$p1 = explode("|", $k);
	$pec = new OJ_Property_Enumeration_Correspondence(["catalogs_id"=>intval($p1[1]), "entity_types_id"=>intval($p1[2]), "properties_name"=>$p1[0], "enumerations_id"=>intval($v)]);
	$pec->save();
}
print "done\n";
