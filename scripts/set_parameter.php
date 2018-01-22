#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
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
	$opts = getopt('p:u:c:t:h:i:', [], $optind);
	if (array_key_exists('p', $opts))
	{
		$pname = $opts['p'];
	}
	if (array_key_exists('t', $opts))
	{
		$table_name = $opts['t'];
	}
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('h', $opts))
	{
		$host = $opts['h'];
	}
	if (array_key_exists('c', $opts))
	{
		$cid = OJ_Catalogs::get_catalog_id($opts['c']);
	}
	if (array_key_exists('i', $opts))
	{
		$indexes_id = OJ_Indexes::get_index_id($cid, $opts['i']);
	}
	$newargv = array_slice($argv, $optind);
	if (count($newargv) > 0)
	{
		$pval = $newargv[0];
		$where = ["name"=>$pname, "catalogs_id"=>$cid];
		if ($host)
		{
			$where["host"] = $host;
		}
		if ($indexes_id > 0)
		{
			$where["indexes_id"] = $indexes_id;
		}
		$param = OJ_Row::load_single_object("OJ_Parameters", $where);
		if ($param == null)
		{
			$hash = ["name"=>$pname, "catalogs_id"=>$cid, "values_id"=>0, "table_name"=>$table_name];
			if ($indexes_id > 0)
			{
				$hash["indexes_id"] = $indexes_id;
			}
			$param = new OJ_Parameters($hash);
		}
		else
		{
			if (!$param->field_is_set("table_name") || ($param->table_name !== $table_name))
			{
				$param->table_name = $table_name;
				$param->values_id = 0;
			}
			if (!$param->field_is_set("values_id"))
			{
				$param->values_id = 0;
			}
			if ($param->indexes_id != $indexes_id)
			{
				$param->indexes_id = $indexes_id;
			}
		}
		if ($host)
		{
			$param->host = $host;
		}
		$param->set_value($pval);
		$param->save();
//		var_dump($prop);
		print "done\n";
	}
}