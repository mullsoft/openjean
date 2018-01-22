#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$enumid = OJ_Attribute_Types::get_attribute_type_id("enumeration");
$allatts = OJ_Row::load_array_of_objects("OJ_Attributes", ["attribute_types_id"=>$enumid, "values_id>"=>0]);
//var_dump($allpages);
$atts1 = [];
foreach ($allatts["result"] as $att)
{
	$nm = $att->name."|".$att->pages_id;
	if (!array_key_exists($nm, $atts1))
	{
		$atts1[$nm] = OJ_Row::get_single_value("OJ_Enumeration_Values", "enumerations_id", ["id"=>$att->values_id]);
	}
}
//var_dump($atts1);exit;
foreach ($atts1 as $k=>$v)
{
	$att1 = explode("|", $k);
	$aec = new OJ_Attribute_Enumeration_Correspondence(["pages_id"=>intval($att1[1]), "attributes_name"=>$att1[0], "enumerations_id"=>intval($v)]);
	$aec->save();
}
print "done\n";
