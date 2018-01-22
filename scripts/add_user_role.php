#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 2)
{
	$user = null;
	$optind = 0;
	$role = 'user';
	$catalog = [];
	$opts = getopt('u:r:c:', [], $optind);
	if (array_key_exists('u', $opts))
	{
		$user = $opts['u'];
	}
	if (array_key_exists('r', $opts))
	{
		$role = $opts['r'];
	}
	if (array_key_exists('c', $opts))
	{
		$catalogs = $opts['c'];
		if ($catalogs === 'all')
		{
			$catalog = OJ_Row::load_column("catalogs", "id");
		}
		else
		{
			$catalo = explode(",", $catalogs);
		}
	}
	$newargv = array_slice($argv, $optind);
	if (($user != null) && ($role != null) && (count($catalog) > 0))
	{
		$users_id = OJ_Users::get_users_id($user);
		if ($users_id == null)
		{
			$u = new OJ_Users(["name"=>$user]);
			$users_id = $u->save();
		}
		$roles_id = OJ_Roles::get_roles_id($role);
		foreach ($catalog as $cat)
		{
			$catalogs_id = OJ_Catalogs::get_catalog_id($cat);
			$ur = new OJ_Users_Roles(["users_id"=>$users_id, "roles_id"=>$roles_id, "catalogs_id"=>$catalogs_id]);
			$ur->save();
		}
	}
	print "finished\n";
}