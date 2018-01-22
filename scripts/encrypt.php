#!/usr/bin/php -q
<?php
chdir("/var/www/html/openjean");
require_once 'oj_base_classes.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if ($argc > 1)
{
	$ret = OJ_Utilities::encrypt($argv[1]);
	print $ret."\n";
}