#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
$rss_items = OJ_Row::load_array_of_objects("OJ_Rss_Items");
$keep = [];
$delete = [];
foreach ($rss_items["result"] as $ri)
{
	if (array_key_exists($ri->item_entities_id, $keep))
	{
		$delete[] = $ri;
	}
	else
	{
		$keep[$ri->item_entities_id] = $ri;
	}
}
foreach ($delete as $d)
{
	print "delete ".$d->item_entities_id."\n";
	$sql = "DELETE FROM rss_items WHERE item_entities_id=".$d->item_entities_id." LIMIT 1";
	Project_Details::get_db()->query($sql);
}

//foreach ($keep as $kid=>$k)
//{
//	print "keep ".$k->item_entities_id." ".$k->status."\n";
//}
