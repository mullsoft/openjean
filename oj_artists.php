<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'oj_multimedia.php';
class OJ_Artists_Catalog_Display extends OJ_Multimedia_Catalog_Display
{
	
//	public function follow_references()
//	{
//		return true;
//	}
//	
	public function get_reference_target_types()
	{
		return 'GROUP';
	}
	
	public function treat_as_category($index, $cat)
	{
		return $this->get_sort_type($cat) === 'CATEGORY';
	}

}

