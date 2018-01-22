#!/usr/bin/php
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 2)
{
	$user = "mike";
	$cid = 0;
	$optind = 0;
	$pname = null;
	$catalogname = null;
	$table_name = 'string';
	$host = null;
	$indexes_id = 0;
	$opts = getopt('p:u:c:h:i:', []);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('h', $opts))
	{
		$host = $opts['h'] === 'true'?true:$opts['h'];
	}
	if (array_key_exists('c', $opts))
	{
		$cid = OJ_Catalogs::get_catalog_id($opts['c']);
	}
	if (array_key_exists('i', $opts))
	{
		$indexes_id = OJ_Indexes::get_index_id($cid, $opts['i']);
	}
	$param = OJ_Parameters::get_parameter_value($cid, $pname, $host);
	var_dump($param);
	echo $param;
}