#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
chdir('/var/www/html/openjean');
require_once("ojEntity_db.php");
//require_once("rss/FeedParser.php");
//
//$parser = new FeedParser();
//echo "parsing ".$argv[1]."\n";
//$parser->parse($argv[1]);
//$items = $parser->getItems();
//var_dump($items);



//require_once("rss/lastRSS.php");
//$rss = new lastRSS;
//
//// Set cache dir and cache time limit (1200 seconds)
//// (don't forget to chmod cahce dir to 777 to allow writing)
//$rss->cache_dir = '';
//$rss->cache_time = 0;
//$rss->cp = 'US-ASCII';
//$rss->date_format = 'l';
//
//// Try to load and parse RSS file of Slashdot.org
////$rssurl = 'http://www.freshfolder.com/rss.php';
//$rssurl = $argv[1];
//if ($rs = $rss->get($rssurl)) {
//	var_dump($rs);
//	}
//else {
//	echo "Error: It's not possible to get $rssurl...";
//}
require_once ('rss/simplepie-1.5/autoloader.php');
$feed = new SimplePie();
$feed->set_feed_url($argv[1]);
$feed->init();
$feed->handle_content_type();
$items = $feed->get_items();
foreach ($items as $item)
{
	print "TITLE: ".$item->get_title()."\n\n";
	print "ID: ".$item->get_id()."\n\n";
	print $item->get_description()."\n\n\n";
}
?>