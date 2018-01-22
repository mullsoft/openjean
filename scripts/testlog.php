#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("project.php");

$logger = OJ_Logger::get_logger();

$logger->ojlog(OJ_Logger::LOG_DEBUG,"hello world");

