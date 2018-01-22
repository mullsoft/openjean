#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$user = "mike";
$allenums = OJ_Row::load_array_of_objects("OJ_Enumerations");
//var_dump($allenums);
$enums1 = [];
foreach ($allenums["result"] as $en)
{
	$nm = $en->name;
	if (array_key_exists($nm, $enums1))
	{
		array_push($enums1[$nm], $en->id);
	}
	else
	{
		$enums1[$nm] = [$en->id];
	}
}
function printout($enums)
{
	foreach ($enums as $k=>$v)
	{
		print $k.": ".count($v)."\n";
	}
}
printout($enums1);
$sameas = [];
$gone = [];
$sameas1 = [];
function same_as($k2, $k1)
{
	$ka = explode('|', $k1);
	$ret = true;
	$kk2 = '|'.$k2.'|';
	foreach ($ka as $k)
	{
		$ret = $ret && (strpos($kk2, '|'.$k.'|') !== FALSE);
	}
	return $ret;
}
foreach ($enums1 as $k1=>$v1)
{
	foreach ($enums1 as $k2=>$v2)
	{
		if (($k1 !== $k2) && same_as($k2, $k1))
		{
			$sameas[] = [$k2, $k1];
			$gone[] = $k1;
		}
	}
}
foreach ($sameas as $sa)
{
	if (array_search($sa[0], $gone) === FALSE)
	{
		$sameas1[] = $sa;
	}
}
foreach ($sameas1 as $sa)
{
	$ids1 = $enums1[$sa[1]];
	foreach ($ids1 as $id)
	{
		array_push($enums1[$sa[0]], $id);
	}
	unset($enums1[$sa[1]]);
}
//var_dump($sameas1);
print "\n\n";
printout($enums1);
print "loaded ".count($allenums["result"])." objects\n";
foreach ($enums1 as $k=>$v)
{
	$len = count($v);
	print $k.": has ".$len."\n";
	if ($len > 1)
	{
		$use1 = $v[0];
		$sql1 = "UPDATE enumeration_values SET enumerations_id=$use1 WHERE enumerations_id=";
		$sqld1 = "DELETE FROM enumerations WHERE id=";
		$inc = 50;
		for ($startat = 1; $startat < $len; $startat += $inc)
		{
			$sql = $sql1.$v[$startat];
			$sqld = $sqld1.$v[$startat];
			for ($n = 1; ($n < $inc) && ($startat + $n < $len); $n++)
			{
				$sql .= " OR enumerations_id=".$v[$startat + $n];
				$sqld .= " OR id=".$v[$startat + $n];
			}
			Project_Details::get_db()->query($sql);
			Project_Details::get_db()->query($sqld);
//			print $sql."\n";
//			print $sqld."\n";
		}
	}
}
print "done\n";
