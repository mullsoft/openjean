<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'OJDatabase.php';
$catalogs_id = OJ_Catalogs::get_catalog_id("favourites");
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

#oj-get-title-button
{
	margin-left:5px;
}