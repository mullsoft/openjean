#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$allpages = OJ_Row::load_array_of_objects("OJ_Pages");
//var_dump($allpages);
$pages1 = [];
foreach ($allpages["result"] as $en)
{
	$nm = $en->name."__".$en->catalogs_id."__".$en->entity_types_id;
	if (array_key_exists($nm, $pages1))
	{
		array_push($pages1[$nm], $en->id);
	}
	else
	{
		$pages1[$nm] = [$en->id];
	}
}
//function printout($pages)
//{
//	foreach ($pages as $k=>$v)
//	{
//		print $k.": ".count($v)."\n";
//	}
//}
//printout($pages1);
$sameas = [];
$gone = [];
print "loaded ".count($allpages["result"])." objects\n";
foreach ($pages1 as $k=>$v)
{
	$len = count($v);
	print $k.":\n";
	if ($len > 1)
	{
		$use1 = $v[0];
		$sql1 = "UPDATE attributes SET pages_id=$use1 WHERE pages_id=";
		$sqld1 = "DELETE FROM pages WHERE id=";
		$inc = 50;
		for ($startat = 1; $startat < $len; $startat += $inc)
		{
			$sql = $sql1.$v[$startat];
			$sqld = $sqld1.$v[$startat];
			for ($n = 1; ($n < $inc) && ($startat + $n < $len); $n++)
			{
				$sql .= " OR pages_id=".$v[$startat + $n];
				$sqld .= " OR id=".$v[$startat + $n];
			}
			Project_Details::get_db()->query($sql);
			Project_Details::get_db()->query($sqld);
//			print "sql: ".$sql."\n";
//			print "sqld: ".$sqld."\n";
		}
	}
}
print "done\n";
