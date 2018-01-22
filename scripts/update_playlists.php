#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
ini_set('xdebug.var_display_max_depth', '-1');
ini_set('xdebug.var_display_max_children', '-1');
ini_set('xdebug.var_display_max_data', '-1');
require_once("ojEntity_db.php");
$user = "mike";
$optind = 0;
$opts = getopt('u:', [], $optind);
if (array_key_exists('u', $opts))
{
	$user = $opts['u'];
}
$plid = OJ_Audio_Utilities::get_playlist_id($user);
$mmcatid = OJ_Catalogs::get_catalog_id("Multimedia");
$paths = OJ_Links::get_paths_from($mmcatid, $plid);
$items = [];
foreach ($paths["paths"] as $pth)
{
	$len = count($pth);
	$itm = $pth[$len - 1];
	$grp = $pth[$len - 2];
	if (!array_key_exists($itm, $items))
	{
		$items[$itm] = [];
	}
	array_push($items[$itm], $grp);
}
foreach ($items as $itm=>$grps)
{
	$itm_parents = OJ_Row::load_column("links", "from_entities_id", ["from_catalogs_id"=>$mmcatid, "to_catalogs_id"=>$mmcatid, "to_entities_id"=> $itm]);
	$other_grps = array_diff($itm_parents, $grps);
	$nm = "unknown";
	if (count($other_grps) > 0)
	{
		$ogkeys = array_keys($other_grps);
		$og = $other_grps[$ogkeys[0]];
		$nm = OJ_Entities::get_entity_name($og);
		$tt = $og."::".$nm;
		foreach ($grps as $grp)
		{
			$lnk = OJ_Row::load_single_object("OJ_Links", ["from_catalogs_id"=>$mmcatid, "to_catalogs_id"=>$mmcatid, "to_entities_id"=> $itm, "from_entities_id"=>$grp]);
			if ($lnk == null)
			{
				print "no link from ".$grp." to ".$itm."\n";
			}
			else
			{
				$lnk->tooltip = $tt;
				$lnk->save();
				print "tooltip added for ".$grp." to ".$itm."\n";
			}
		}
	}
	print $itm." is in ".$nm."\n";
}
//print "playlist id ".$plid."\n";
//var_dump($paths);
