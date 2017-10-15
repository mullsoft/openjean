<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_AddressBook_Catalog_Display extends OJ_Catalog_Display
{
	public function get_display_pages($ojid)
	{
//		var_dump($ent);
		$ret = [];
		if (strtolower(OJ_Entities::get_entity_types_name($ojid)) !== 'category')
		{
//			$attcoll = $ent->get_attributes();
			$attcoll = OJ_Entities::get_visible_attributes($ojid);
			if ($attcoll != null)
			{
//				echo "getting type</br>";
//				echo "entity type ".$ent->get_entity_type()."</br>";
//				var_dump($attcoll);
//				$notes_page = $attcoll->get_page("Notes");
//				$phone_page = $attcoll->get_page("Phone");
//				$identity_page = $attcoll->get_page("Identity");
//				$address_page = $attcoll->get_page("Address");
//				$internet_page = $attcoll->get_page("Internet");
				$notes_page = $attcoll["Notes"];
				$phone_page = $attcoll["Phone"];
				$identity_page = $attcoll["Identity"];
				$address_page = $attcoll["Address"];
				$internet_page = $attcoll["Internet"];
//				$pgs = $ent->get_visible_pages();
				$p1atts = [];
//				$fnatt = $address_page->get_attribute("fullName");
				$fnatt = $address_page["fullName"];
				if ($fnatt)
				{
//					$fnatt->set_prompt("name");
					$fnatt["prompt"] = "name";
					array_push($p1atts, $fnatt);
				}
//				$att = $address_page->get_attribute("address");
				$att = $address_page["address"];
				if ($att)
				{
					array_push($p1atts, $att);
				}
//				$att = $phone_page->get_attribute("home");
				$att = $phone_page["home"];
				if ($att)
				{
					array_push($p1atts, $att);
				}
///				$att = $phone_page->get_attribute("mobile");
				$att = $phone_page["mobile"];
				if ($att)
				{
					array_push($p1atts, $att);
				}
//				$att = $phone_page->get_attribute("work");
				$att = $phone_page["work"];
				if ($att)
				{
					array_push($p1atts, $att);
				}
//				$att = $internet_page->get_attribute("email");
				$att = $internet_page["email"];
				if ($att)
				{
					array_push($p1atts, $att);
				}
//				array_push($p1atts, $phone_page->get_attribute("home"));
//				array_push($p1atts, $phone_page->get_attribute("mobile"));
//				array_push($p1atts, $phone_page->get_attribute("work"));
//				array_push($p1atts, $internet_page->get_attribute("email"));
//				$ret["details"] = new OJ_Page($this->_catalog, $p1atts);
				$ret["details"] = $p1atts;
			}
		}
		return $ret;
	}

}

