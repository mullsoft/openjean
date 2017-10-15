<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'OJDatabase.php';
//echo "here";exit;
$catalogs_id = OJ_Catalogs::get_catalog_id("rss");
$color = OJ_Parameters::get_parameter_value($catalogs_id, "bgcolor");
//echo $color; exit;
header ("Content-type: text/css");
?>
body,div
{
	background-color: <?php echo $color; ?>;
}

.oj-button-label
{
	background-color: <?php echo $color; ?>;
}

.oj-panel-heading
{
	background-color: <?php echo $color; ?>;
}

.filter-menu-list
{
	background-color: <?php echo $color; ?>;
}

.filter-class-entity
{
	background-color: <?php echo $color; ?>;
}

.oj-rss-viewed
{
	color:red;
}

.oj-rss-notviewed
{
	color:green;
}

.oj-rss-lnkdiv
{
	margin-left:10px;
}

.oj-rss-contents
{
	margin-left:2px;
	padding-left:3px;
	padding-right:3px;
	max-height: 85%;
	overflow:auto;
}

.has-unread
{
	border: 1px solid green !important;
}

#oj-button-group-feeds
{
	padding-bottom:5px;
}

#oj-button-label-feeds
{
	width:4em;
}

