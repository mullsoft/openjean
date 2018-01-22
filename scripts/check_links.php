#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$max = OJ_Row::get_max("links");
print $max."\n";
$types = "|solo artist|band|writer|orchestra|conductor|soloist|composer|producer|director|actor|author|editor|other|";
$inc = 1000;
$notes_id = OJ_Catalogs::get_catalog_id("notes");
for ($first = 0; $first <= $max; $first += $inc)
{
	$last = $first + $inc;
	$where = ["id>"=>$first, "id<="=>$last];
	$links = OJ_Row::load_array_of_objects("OJ_Links", $where);
	foreach ($links["result"] as $link)
	{
		$changed = false;
		print $link->id.": ";
		if ($link->name)
		{
			if (strpos($types, '|'.$link->name.'|') !== FALSE)
			{
				print "name to type ".$link->name."\n";
				$link->type = $link->name;
				$link->name = OJ_Entities::get_entity_name($link->to_entities_id);
				$changed = true;
			}
			else
			{
				print "name is ".$link->name."\n";
				if (!$link->type)
				{
					if ($link->to_catalogs_id == $notes_id)
					{
						$link->type = "note";
					}
					elseif ($link->to_catalogs_id == $link->from_catalogs_id)
					{
						$link->type = "child";
					}
					else
					{
						$link->type = "xref";
					}
					$changed = true;
				}
			}
		}
		else
		{
			$link->name = OJ_Entities::get_entity_name($link->to_entities_id);
			if ($link->to_catalogs_id == $notes_id)
			{
				$link->type = "note";
			}
			elseif ($link->to_catalogs_id == $link->from_catalogs_id)
			{
				$link->type = "child";
			}
			else
			{
				$link->type = "xref";
			}
			$changed = true;
			print "type child for ".$link->from_entities_id." to ".$link->to_entities_id."\n";
		}
		if (!$link->rname)
		{
			$changed = true;
			$link->rname = OJ_Entities::get_entity_name($link->from_entities_id);
		}
		if ($changed)
		{
			$link->save();
		}
	}
}