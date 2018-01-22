#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
$ojmode = "db";
ini_set('memory_limit', '1024M');
require_once("ojHTML.php");
$user = "mike";
$eid = null;
$optind = 0;
$catalogname = "RSS";
$opts = getopt('e:u:', [], $optind);
if (array_key_exists('e', $opts))
{
	$eid = explode(',', $opts['e']);
}
if (array_key_exists('u', $opts))
{
	$user = $opts['u'];
}
$newargv = array_slice($argv, $optind);
//$catalog = OJ_System::instance($user)->get_catalog($catalogname);
//var_dump($catalog);
$cd = OJ_Catalog_Display::get_catalog_display($user, $catalogname);
if ($eid == null)
{
	$grp = OJ_Entity_Types::get_entity_type_id("GROUP");
	$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
	$where = ["entity_types_id"=>$grp, "catalogs_id"=>$rsscatid];
	$eid = OJ_Row::load_column("entities", "id", $where);
}
foreach ($eid as $feedid)
{
	print "reload ".$feedid."\n";
	$cd->load_feed($feedid, "./rsscache");
}