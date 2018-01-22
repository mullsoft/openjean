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

$boxsets = OJ_Entities::get_all_with_subtype($category_entity_types_id, "audio_boxset");
//var_dump($boxsets);exit;
$vartists = OJ_Row::load_column("links", "to_entities_id", ["from_catalogs_id"=>$multimedia_catalogs_id, "to_catalogs_id"=>$multimedia_catalogs_id,
	"entity_types_id"=>$group_entity_types_id, "from_entities_id"=>459]);
$member_links_items1 = OJ_Row::load_array_of_objects("OJ_Links", ["from_catalogs_id"=>$multimedia_catalogs_id, "to_catalogs_id"=>$multimedia_catalogs_id,
	"entity_types_id"=>$item_entity_types_id, "type"=>'member']);
$member_links_items = $member_links_items1["result"];
$multi = [];
$va = [];
$vamulti = [];
$orphans = [];
foreach ($member_links_items as $mli)
{
//	$key = $mli->to_entities_id.": ".$mli->name;
//	print "member ".$mli->to_entities_id." from ".$mli->rname." to ".$mli->name."\n";
	$child_links1 = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$mli->to_entities_id, "type"=>"child"]);
	$child_links = $child_links1["result"];
//	if (count($child_links) == 0)
//	{
//		$orphans[] = $key;
//	}
//	elseif (count($child_links) > 1)
//	{
//		$multi[$key] = $child_links;
//	}
	foreach ($child_links as $cl)
	{
		$i = array_search($cl->from_entities_id, $vartists);
		if ($i === FALSE)
		{
			// not a various artists child
			$plnk = OJ_Row::load_single_object("OJ_Links", ["from_entities_id"=>$mli->from_entities_id, "to_entities_id"=>$cl->from_entities_id, "type"=>"member"]);
			$plnkok = $plnk?"ok":("not ok from ".$mli->from_entities_id." to ".$cl->from_entities_id);
			if (!$plnk)
			{
				$newname = OJ_Entities::get_entity_name($cl->from_entities_id);
				$newlnk = new OJ_Links(["from_entities_id"=>$mli->from_entities_id, "to_entities_id"=>$cl->from_entities_id, "type"=>"member",
					"from_catalogs_id"=>$multimedia_catalogs_id, "to_catalogs_id"=>$multimedia_catalogs_id, "name"=>$newname, "rname"=>$mli->rname,
					"subtype"=>"possible error", "entity_types_id"=>$group_entity_types_id]);
				$newlnk->save();
			}
			$mli->delete();
			print "delete item link ".$mli->id." from ".$mli->rname." to ".$mli->name." parent link $plnkok\n";
			break;
		}
		else
		{
			print "not deleting item link ".$mli->id." from ".$mli->rname." to ".$mli->name."\n";
		}
//		if ($i !== FALSE)
//		{
//			if (count($child_links) > 1)
//			{
//				$vamulti[$key] = $cl;
//			}
//			else
//			{
//				$va[$key] = $cl;
//			}
//			break;
//		}
	}
}
$member_links_groups1 = OJ_Row::load_array_of_objects("OJ_Links", ["from_catalogs_id"=>$multimedia_catalogs_id, "to_catalogs_id"=>$multimedia_catalogs_id,
	"entity_types_id"=>$group_entity_types_id, "type"=>'member']);
$member_links_groups = $member_links_groups1["result"];
foreach ($member_links_groups as $mlg)
{
	$child_links1 = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$mlg->to_entities_id, "type"=>"child"]);
	$child_links = $child_links1["result"];
	foreach ($child_links as $cl)
	{
		$i = array_search($cl->from_entities_id, $boxsets);
		if ($i !== FALSE)
		{
			// is a boxset child
			$plnk = OJ_Row::load_single_object("OJ_Links", ["from_entities_id"=>$mlg->from_entities_id, "to_entities_id"=>$cl->from_entities_id, "type"=>"member"]);
			$plnkok = $plnk?"ok":("not ok from ".$mlg->from_entities_id." to ".$cl->from_entities_id);
			if (!$plnk)
			{
				$newname = OJ_Entities::get_entity_name($cl->from_entities_id);
				$newlnk = new OJ_Links(["from_entities_id"=>$mlg->from_entities_id, "to_entities_id"=>$cl->from_entities_id, "type"=>"member",
					"from_catalogs_id"=>$multimedia_catalogs_id, "to_catalogs_id"=>$multimedia_catalogs_id, "name"=>$newname, "rname"=>$mlg->rname,
					"subtype"=>$mlg->subtype, "entity_types_id"=>$category_entity_types_id]);
				$newlnk->save();
			}
			$mlg->delete();
			print "delete group link ".$mlg->id." from ".$mlg->rname." to ".$mlg->name." parent link $plnkok\n";
			break;
		}
		else
		{
			print "not deleting group link ".$mlg->id." from ".$mlg->rname." to ".$mlg->name."\n";
		}
	}
}
//if (count($multi)> 0)
//{
//	print count($multi)." multiple parents:\n";
//	foreach ($multi as $m=>$p)
//	{
//		$pk = [];
//		foreach ($p as $pp)
//		{
//			$pk[] = $pp->from_entities_id.": ".$pp->rname;
//		}
//		print "$m  has ".implode(" AND ", $pk)."\n";
//	}
//}
//if (count($va)> 0)
//{
//	print "\n\n".count($va)." va parents:\n";
//	foreach ($va as $v=>$p)
//	{
//		print "$v has va parent ".$p->from_entities_id.": ".$p->rname."\n";
//	}
//}
//if (count($vamulti)> 0)
//{
//	print "\n\n".count($vamulti)." multi va parents:\n";
//	foreach ($vamulti as $v=>$p)
//	{
//		print "$v has, among others, va parent ".$p->from_entities_id.": ".$p->rname."\n";
//	}
//}
//if (count($orphans)> 0)
//{
//	print "\n\n".count($orphans)." orphans:\n";
//	foreach ($orphans as $o)
//	{
//		print "$o\n";
//	}
//}
//	print "child of ".implode(", ", $child_links)."\n";
