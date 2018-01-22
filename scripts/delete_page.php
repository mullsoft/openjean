#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$pid = 0;
$optind = 0;
$required = [
	"page:"
];
$optional = [
];
if ($argc > 1)
{
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('page', $opts))
	{
		$pid = $opts['page'];
	}
	if ($pid > 0)
	{
		$page = OJ_Row::load_single_object("OJ_Pages", ["id"=>$pid]);
		if ($page)
		{
			$atts1 = OJ_Row::load_array_of_objects("OJ_Attributes", ["pages_id"=>$pid]);
			$atts = $atts1["result"];
			foreach ($atts as $att)
			{
				$tabname = OJ_Row::get_single_value("OJ_Attribute_Types", "table_name", ["id"=>$att->attribute_types_id]);
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$vid = $att->values_id;
					if ($vid > 0)
					{
						$obj = new $cname($vid);
						$obj->delete();
						print "deleted $tname $vid \n";
					}
				}
				$att->delete();
				print "deleted attribute $att->id $att->name \n";
			}
			$page->delete();
			print "deleted page $pid $page->name \n";
		}
	}
	else
	{
		print "no page $pid \n";
		print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
	}
}
else
{
	print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
}
