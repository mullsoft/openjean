<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'OJDatabase.php';
$catalogs_id = OJ_Catalogs::get_catalog_id("artists");
$color = OJ_Parameters::get_parameter_value($catalogs_id, "bgcolor");
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

.oj-artist-type-span
{
	display:inline-block;
	padding-right:10px;
	text-align: right;
	width : 20%;
}

.oj-artists-div
{
	padding-top:20px;
}

.oj-album-path-label
{
	display:inline-block;
	padding-right:10px;
	text-align: right;
	width : 20%;
}

.oj-album-path-div
{
	padding-top:20px;
}

.oj-playlist-item {
  cursor: move;
}
.oj-playlist-item header {
  height: 20px;
  width: 150px;
  color: black;
  background-color: #ccc;
  padding: 5px;
  border-bottom: 1px solid #ddd;
  border-radius: 10px;
  border: 2px solid #666666;
}

.oj-playlist-item.dragElem {
  opacity: 0.4;
}
.oj-playlist-item.over {
  /*border: 2px dashed #000;*/
  border-top: 2px solid blue;
}

.oj-playing-label
{
	text-align: right;
	padding-right:1em;
}

.oj-playing-container
{
	margin-to:0px;
	padding-top:0px;
	padding-bottom: 5px;
	border-bottom: 1px solid lightgrey;
	margin-bottom: 3px;
}

#oj-player-contents
{
	padding-top:3px;
}

#oj-player-img-div
{
	margin-top:5px;
}

#oj-player-img-div img
{
	max-width:100%;
	max-height:100%;
}

#oj-player-container
{
	min-width:100%;
	max-width:100%;
	max-height:70vh;
}

.oj-alltoplay
{
	color:darkblue;
}

.oj-sometoplay
{
	color:lightblue;
}

.import-artists-list-text
{
	width:45%;
	display:inline-block;
	font-size: 0.9em;
	height:1.7em;
}

.oj-import-artist-type-select
{
	width:30%;
	margin-left:2px;
}

#oj-import-artists-list-div
{
	max-height: 20em;
	overflow: auto;
}

#oj-import-artists-list
{
	padding-left:0px;
}

.oj-import-artists-list-li
{
	width:100%;
	display:inline-block;
	margin-left:0px;
	padding-left:0px;
}

.aka-button
{
	margin-left:2px;
	margin-right:2px;
	padding-left:2px;
	padding-right:2px;
	padding-top:2px;
	padding-bottom:2px;
	height:1.7em;
}

#oj-select-artist-category-contents
{
	max-height:18em;
	min-height:18em;
	padding-top:5px;
	padding-bottom:2px;
	padding-left:3px;
	padding-right:2px;
	margin-top:10px;
	margin-left:5px;
	border: 2px gray solid;
	overflow:auto;
}

.new-artist-text
{
	display:inline-block;
	width:7em;
}

#new-artist-type-select-span
{
	display:inline-block;
	width:12em;
}

#new-artist-type-select
{
	width:12em;
}

#oj-import-artists-div
{
	max-height:14em;
	min-height:14em;
	padding-top:5px;
	padding-bottom:2px;
	padding-left:3px;
	padding-right:2px;
	margin-top:10px;
	margin-left:5px;
	border: 2px gray solid;
	overflow:auto;
}

#oj-import-artists-list
{
	list-style: none;
}

.jp-audio
{
	margin-right:0px;
	display: inline-block;
}

#oj-player-playlist
{
	overflow:auto;
	height:80%;
}

#oj-player-playlist-list
{
	list-style: none;
}

