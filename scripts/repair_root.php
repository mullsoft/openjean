#!/usr/bin/php -q

<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

//$mmroot = new OJ_Entities(["name"=>"root", "catalogs_id"=>1, "entity_types_id"=>1]);
//$arroot = new OJ_Entities(["name"=>"root", "catalogs_id"=>2, "entity_types_id"=>1]);

$mmid = 196223;
$arid = 196224;

print "mm root is ".$mmid;
print "ar root is ".$arid;

$mlnks = OJ_Links::get_all_links_from(1, 1);
var_dump($mlnks);
