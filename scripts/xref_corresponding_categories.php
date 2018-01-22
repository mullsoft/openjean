#!/usr/bin/php
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
require_once("OJCatalogs.php");

if ($argc > 0)
{
	$optind = 0;
	$source_catalogs_id = 0;
	$destination_catalogs_id = 0;
	$create_new = false;
	$username = "mike";
	$opts = getopt('u:c:d:n', [], $optind);
	if (array_key_exists('c', $opts))
	{
		$source_catalogs_id = OJ_Catalogs::get_catalog_id($opts['c']);
	}
	if (array_key_exists('d', $opts))
	{
		$destination_catalogs_id = OJ_Catalogs::get_catalog_id($opts['d']);
	}
	if (array_key_exists('n', $opts))
	{
		$create_new = true;
	}
	if (array_key_exists('u', $opts))
	{
		$username = $opts['u'];
	}
	if (($source_catalogs_id > 0) && ($destination_catalogs_id > 0))
	{
		$cd = OJ_Catalog_Display::get_catalog_display($username, OJ_Catalogs::get_catalog_name($source_catalogs_id));
		$category_types_id = OJ_Entity_Types::get_entity_type_id("CATEGORY");
		$source_root = OJ_Catalogs::get_root_id($source_catalogs_id);
		$destination_root = OJ_Catalogs::get_root_id($destination_catalogs_id);
		$cd->correspondence_check_link($source_catalogs_id, $source_root, $destination_catalogs_id, $destination_root, $create_new, $category_types_id, $cd);
	}
}