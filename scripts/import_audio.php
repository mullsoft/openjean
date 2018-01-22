#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once 'ojEntity_db.php';

$audio = OJ_Row::get_single_value("OJ_Indexes", "entities_id", ["name"=>"audio"]);
echo "audio ".$audio."\n";
$cat = new OJ_Catalog("mike", "multimedia");
$iterator = new OJ_Iterator($cat, $audio);
$albums = [];
foreach ($iterator as $el)
{
//	var_dump($el);
	if ($el["type"] !== 'ITEM')
	{
		print "load\t".$el['name']."\n";
		$anm = stripslashes(trim($el['name']));
		$albums[strtolower($anm)] = ["name"=>$anm, "id"=>$el['to'], "count"=>0];
	}
}
print "found ".count($albums)." albums and categories\n";
$notthere = [];
for ($n = 1; $n <= 28; $n++)
{
	$logical = OJ_Logicals::get_logical("music".$n);
	$root = $logical->value;
//			print $root."\n";
	$rootinfo = OJ_File_Utilities::dir_info($root);
//			var_dump($rootinfo);exit;
	foreach ($rootinfo["subdirs"] as $folder)
	{
		$fname = strtolower(trim(basename($folder)));
		if (array_key_exists($fname, $albums))
		{
			$albums[$fname]["count"]++;
			print "found\t".$fname."\n";
			$imp = new OJ_Imported_Folders(["name"=>$fname, "entities_id"=>$albums[$fname]["id"], "catalogs_id"=>1, "logical_name"=>"music".$n]);
			$imp->save(true);
		}
		else
		{
			$dotoj = $folder.".oj";
			if (file_exists($dotoj))
			{
				$imp = new OJ_Imported_Folders(["name"=>$fname, "entities_id"=>0, "catalogs_id"=>1, "logical_name"=>"music".$n]);
				$imp->save(true);
			}
			else
			{
				$notthere[] = $folder;
			}
		}
		$info = OJ_File_Utilities::dir_info($folder);
		foreach ($info["subdirs"] as $f)
		{
			if (OJ_File_Utilities::contains_audio_file($f))
			{
				$fname1 = strtolower(trim(basename($f)));
				if (array_key_exists($fname1, $albums))
				{
					$albums[$fname1]["count"]++;
					print "found\t".$fname1."\n";
					$imp = new OJ_Imported_Folders(["name"=>$fname1, "entities_id"=>$albums[$fname1]["id"], "catalogs_id"=>1, "logical_name"=>"music".$n]);
					$imp->save(true);
				}
				else
				{
					$dotoj = $f.".oj";
					if (file_exists($dotoj))
					{
						$imp = new OJ_Imported_Folders(["name"=>$fname1, "entities_id"=>0, "catalogs_id"=>1, "logical_name"=>"music".$n]);
						$imp->save(true);
					}
					else
					{
						$notthere[] = $f;
					}
				}
				$info1 = OJ_File_Utilities::dir_info($f);
				foreach ($info1["subdirs"] as $f1)
				{
					if (OJ_File_Utilities::contains_audio_file($f1))
					{
						$fname2 = strtolower(trim(basename($f1)));
						if (array_key_exists($fname2, $albums))
						{
							$albums[$fname2]["count"]++;
							print "found\t".$fname2."\n";
							$imp = new OJ_Imported_Folders(["name"=>$fname2, "entities_id"=>$albums[$fname2]["id"], "catalogs_id"=>1, "logical_name"=>"music".$n]);
							$imp->save(true);
						}
						else
						{
							$dotoj = $f1.".oj";
							if (file_exists($dotoj))
							{
								$imp = new OJ_Imported_Folders(["name"=>$fname2, "entities_id"=>0, "catalogs_id"=>1, "logical_name"=>"music".$n]);
								$imp->save(true);
							}
							else
							{
								$notthere[] = $f1;
							}
						}
					}
				}
			}
		}
//		$ai = OJ_File_Utilities::already_imported($folder, OJ_File_Utilities::$audio_extensions);
//		if (!$ai)
//		{
//			print $folder."\n".$ai."\n";
//		}
	}
//	var_dump($logical);
}
$countr = 0;
foreach($albums as $aname =>$album)
{
	if ($album["count"] == 0)
	{
		$countr++;
		print "remaining\t".$album["name"]."\t".$album["id"]."\n";
	}
}
print "remaining ".$countr." albums and categories and ".count($notthere)." not there\n";
foreach($notthere as $nt)
{
	print "not there\t".$nt."\n";
}
