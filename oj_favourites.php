<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_Favourites_Catalog_Display extends OJ_Catalog_Display
{
	public function get_display_column_cssclass()
	{
		return "col-xs-9 col-sm-9 col-md-9";
	}

	public function get_filter_column_cssclass()
	{
		return "col-xs-3 col-sm-3 col-md-3";
	}

}

