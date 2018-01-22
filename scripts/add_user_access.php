#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 2)
{
	$user = null;
	$eid = 0;
	$optind = 0;
	$role = 'user';
	$catalog = null;
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
		$catalog = $opts['c'];
	}
	$newargv = array_slice($argv, $optind);
	if (($user != null) && ($catalog != null))
	{
		$users = explode(',', $user);
		foreach ($users as $usr)
		{
			if (OJ_Members::exists($usr))
			{
				if (($catalog === 'all') || ($catalog === '0'))
				{
					$catid = OJ_Row::load_column("catalogs", "id");
				}
				else
				{
					$cats = explode(',', $catalog);
					$catid = [];
					foreach ($cats as $c)
					{
						$cid = OJ_Catalogs::get_catalog_id($catalog);
						if ($catid > 0)
						{
							$catid[] = $cid;
						}
					}
				}
				foreach ($catid as $cid)
				{
					$hash = ["catalogs_id"=>$cid, "username"=>$usr, "role"=>$role];
					$access = new OJ_Access($hash);
					$access->save(true);
				}
				$acc = OJ_Access::most_recent_access_for($usr);
				var_dump($acc);
			}
			else
			{
				print "no such user ".$usr."\n";
			}
		}
	}
}