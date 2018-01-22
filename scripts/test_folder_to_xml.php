#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("ojEntity_db.php");
if ($argc > 0)
{
	print OJ_Audio_Utilities::folderpath_to_xml_entity_string(OJ_Logicals::substitute_for_logical($argv[1]));
}