#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$properties = OJ_Row::load_array_of_objects("OJ_Properties", ["name"=>"subtype"]);
foreach($properties["result"] as $prop)
{
	$st = new OJ_Subtypes(["id"=>$prop->entities_id, "name"=>$prop->get_value(true)]);
	$st->save(true, false);
	print $st->id." ".$st->name."\n";
}