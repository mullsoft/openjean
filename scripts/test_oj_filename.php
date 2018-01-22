#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 0)
{
	$ojfname = new OJ_File(OJ_Logicals::substitute_for_logical($argv[1]));
	print OJ_Audio_Utilities::get_duration($argv[1])."\n";
	var_dump($ojfname);
	print $ojfname."\n";
	print $ojfname->starts_with_number() === false?"no\n":$ojfname->starts_with_number()."\n\n\n";
	$folder = new OJ_Folder(dirname($ojfname));
	print "creating audio\n";
	$folder1 = new OJ_Audio_Folder(dirname($ojfname));
	var_dump($folder);
	var_dump($folder1->label_audio);
	print OJ_Audio_Utilities::audio_file_to_xml_entity_string($argv[1], $folder1->artist)."\n";
	$imgf = $folder1->get_all_image_files();
	print count($imgf)." images\n";
	var_dump($imgf);
}