#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

function print_link($wl, $prefix = "")
{
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
	print $prefix."from=$fr, to=$to, rname=$rn, name=$nm, type=$ty1, subtype=$st\n";
}

function is_artist($id)
{
	$from_links = OJ_Row::load_hash_of_all_objects("OJ_Links", ["from_entities_id"=>$id], "type");
	return count(array_keys($from_links)) === 1 && array_key_exists("member", $from_links);
}

function is_empty($id)
{
	$from_links = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id"=>$id]);
	return $from_links["total_number_of_rows"] === 0;
}

$multimedia_catalogs_id = 1;
$item_entity_types_id = 3;
$group_entity_types_id = 2;
$category_entity_types_id = 1;

$eid = 3895; // classical

$eid_links = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id"=>$eid]);
$result = ["corresponding"=>[], "artist"=>[], "empty"=>[], "subcategory"=>[]];
$scids = [];
foreach ($eid_links["result"] as $cl)
{
//	print_link($cl);
	$clto = $cl->to_entities_id;
	$clname = $cl->name;
	$corr = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$clto, "type"=>"correspondence"]);
	if ($corr["total_number_of_rows"])
	{
		array_push($result["corresponding"], $cl);
//		print "CORRESPONDENCE: ";
//		foreach ($corr["result"] as $cr)
//		{
//			print_link($cr);
//		}
	}
	elseif (is_artist($clto))
	{
		array_push($result["artist"], $cl);
//		print "ARTIST\n";
	}
	elseif (is_empty($clto))
	{
		array_push($result["empty"], $cl);
//		print "EMPTY\n";
	}
	else
	{
		array_push($result["subcategory"], $cl);
		array_push($scids, $cl->to_entities_id);
//		print "SUBCATEGORY\n";
	}
//	print "\n";
}
$inerror = [];
foreach ($result as $k=>$v)
{
	print strtoupper($k).":\n";
	foreach ($v as $lnk)
	{
		print_link($lnk);
		$other_links = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$lnk->to_entities_id]);
		foreach ($other_links["result"] as $ol)
		{
			if ($ol->from_entities_id != $eid)
			{
				print_link($ol, "\t");
			}
		}
	}
	print "\n";
}
print "ERROR:\n";
foreach ($result["artist"] as $lnk)
{
	$other_links = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$lnk->to_entities_id]);
	$ok = false;
	foreach ($other_links["result"] as $ol)
	{
		if (array_search($ol->from_entities_id, $scids) !== FALSE)
		{
			$ok = true;
			$lnk->delete();
			break;
		}
	}
	if (!$ok)
	{
		print_link($ol, "\t");
	}
}