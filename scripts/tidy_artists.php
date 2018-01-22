#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;
$category_entity_types_id = 1;

$weird_links = OJ_Row::load_array_of_objects("OJ_Links", ["name%~%"=>"artists::"]);
$total = 0;
$total_with = 0;
$total_without = 0;
foreach ($weird_links["result"] as $wl)
{
	$total++;
	$fr = $wl->from_entities_id;
	$to = $wl->to_entities_id;
	$rn = $wl->rname;
	$nm = $wl->name;
	$ty = $wl->type;
	if ($ty === "xref")
	{
		$ty1 = $ty."[".$wl->from_catalogs_id."->".$wl->to_catalogs_id."]";
	}
	else
	{
		$ty1 = $ty;
	}
	$st1 = $wl->subtype;
	$st = $st1?$st1:"null";
	print "from=$fr, to=$to, rname=$rn, name=$nm, type=$ty1, subtype=$st\n";
	$other = OJ_Row::load_single_object("OJ_Links", ["from_entities_id"=>$fr, "to_entities_id"=>$to, "type"=>$ty]);
	if ($other)
	{
		$total_with++;
		$fr = $other->from_entities_id;
		$to = $other->to_entities_id;
		$rn = $other->rname;
		$nm = $other->name;
		$ty = $other->type;
		if ($ty === "xref")
		{
			$ty1 = $ty."[".$other->from_catalogs_id."->".$other->to_catalogs_id."]";
		}
		else
		{
			$ty1 = $ty;
		}
		$st1 = $other->subtype;
		$st = $st1?$st1:"null";
		print "from=$fr, to=$to, rname=$rn, name=$nm, type=$ty1, subtype=$st\n\n";
		$wl->delete();
	}
	else
	{
		$total_without++;
		print "\n";
	}
}
print "\nTOTAl=$total, of which $total_with have twins and $total_without don't\n";
