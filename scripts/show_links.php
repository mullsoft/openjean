#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

function display_link($tabs1, $tl, $isto, $recurse)
{
	$fr = $isto?("from=".$tl->from_entities_id):("to=".$tl->to_entities_id);
	$rn = $tl->rname;
	$nm = $tl->name;
	$ty = $tl->type;
	if ($ty === "xref")
	{
		$ty .= "[".$tl->from_catalogs_id."]";
	}
	$st1 = $tl->subtype;
	$st = $st1?$st1:"null";
	print $tabs1."$fr, rname=$rn, name=$nm, type=$ty, subtype=$st\n";
	if ($recurse > 0)
	{
		if ($isto)
		{
			display_links($tl->from_entities_id, strlen($tabs1), $recurse - 1, 0);
		}
		else
		{
			display_links($tl->to_entities_id, strlen($tabs1), $recurse - 1, 0);
		}
	}
}

function display_links($id, $tab, $recursefrom, $recurseto)
{
	$to_links = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$id]);
	$from_links = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id"=>$id]);
	$name = OJ_Entities::get_entity_name($id);
	$tabs = "";
	$tabs1 = "\t";
	for ($n = 0; $n < $tab; $n++)
	{
		$tabs .= "\t";
		$tabs1 .= "\t";
	}
	print $tabs."$id: $name\n";
	print $tabs."TO:\n";
	foreach ($to_links["result"] as $tl)
	{
		display_link($tabs1, $tl, true, $recurseto);
	}
	print $tabs."FROM:\n";
	foreach ($from_links["result"] as $fl)
	{
		display_link($tabs1, $fl, false, $recursefrom);
	}
}

$eid = 0;
$fromdepth = 1;
$todepth = 1;
$recursefrom = 0;
$artistname = null;
$optind = 0;
$required = [
	"id:"
];
$optional = [
	"recursefrom:",
	"recurseto:",
	"fromdepth:",
	"todepth:"
];
if ($argc > 1)
{
	$opts = getopt('', array_merge($optional, $required), $optind);
	if (array_key_exists('id', $opts))
	{
		$eid = $opts['id'];
	}
	if (array_key_exists('fromdepth', $opts))
	{
		$fromdepth = $opts['fromdepth'];
	}
	if (array_key_exists('todepth', $opts))
	{
		$todepth = $opts['todepth'];
	}
	if (array_key_exists('recursefrom', $opts))
	{
		$recursefrom = $opts['recursefrom'];
	}
	if (array_key_exists('recurseto', $opts))
	{
		$recurseto = $opts['recurseto'];
	}
	if ($eid > 0)
	{
		display_links($eid, 0, $recursefrom, $recurseto);
	}
}
else
{
	print OJ_Utilities::usage($argv[0], $required, $optional)."\n";
}
