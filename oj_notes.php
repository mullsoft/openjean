<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_Notes_Catalog_Display extends OJ_Catalog_Display
{
	
	public static function get_note($ojid)
	{
		$notes_catalogs_id = OJ_Catalogs::get_catalog_id("Notes");
		$lnk = OJ_Row::load_single_object("OJ_Links", ["from_catalogs_id"=>$notes_catalogs_id, "to_entities_id"=>$ojid]);
		$ret = null;
		if ($lnk)
		{
			$ret = OJ_Entities::get_attribute_value($lnk->from_entities_id, "content", "content");
		}
		return $ret;
	}
	
	public static function get_note_component($note)
	{
		$ret = new OJ_DIV(stripslashes($note), null, "oj-note oj-html-display");
		return $ret;
	}
	
	public function get_category_for_catalog($catalogs_id)
	{
		$name = OJ_Catalogs::get_catalog_name($catalogs_id).'--'.$this->_username;
		$notes_catalogs_id = $this->get_catalog_id();
		$rootid = $this->get_root_id();
		$ret = OJ_Links::get_destination_id($notes_catalogs_id, $rootid, $name);
		if ($ret == 0)
		{
			$typeid = OJ_Entity_Types::get_entity_type_id('CATEGORY');
			$xml = '<entity type="'.$typeid.'" catalog="'.$notes_catalogs_id.'"><name>'.$name.'</name><properties></properties><pages></pages>'.
					'<links><link direction="to" catalog="'.$notes_catalogs_id.'" ordinal="0" hidden="0" other="'.$rootid.'">'.$name.'</link>'.
					'</links><children></children></entity>';
			$ent = OJ_Entities::from_xml_string($xml);
			$ret = $ent->id;
			$ind = new OJ_Indexes(["name"=>$name, "catalogs_id"=>$notes_catalogs_id, "entities_id"=>$ret]);
			$ind->save();
		}
		return $ret;
	}
	
	public function make_note($catalogs_id, $entities_id, $note, $title = null)
	{
		$category_id = $this->get_category_for_catalog($catalogs_id);
		$notes_catalogs_id = $this->get_catalog_id();
		$note_id = OJ_Links::get_destination_id($notes_catalogs_id, $category_id, "note--".$entities_id);
		$now = date ("Y-m-d H:i:s");
		if ($note_id > 0)
		{
			OJ_Entities::set_attribute_value($note_id, "content", "content", htmlentities($note, ENT_XML1));
			OJ_Entities::set_attribute_value($note_id, "detail", "modified", $now);
		}
		else
		{
			if ($title == null)
			{
				$title = "note on ".$now;
			}
			$typeid = OJ_Entity_Types::get_entity_type_id('ITEM');
			$xml = '<entity type="'.$typeid.'" catalog="'.$notes_catalogs_id.'"><name>'.$title.'</name><properties></properties><pages>'.
					'<page name="detail" visible="0">'.
					'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>'.$title.'</value></attribute>'.
					'<attribute type="datetime" visible="1" ordinal="1"><name>created</name><value>'.$now.'</value></attribute>'.
					'<attribute type="datetime" visible="1" ordinal="1"><name>modified</name><value>'.$now.'</value></attribute></page>'.
					'<page name="content" visible="1">'.
					'<attribute type="HTML" visible="1" ordinal="1"><name>content</name><value>'.htmlentities($note, ENT_XML1).'</value></attribute></page></pages>'.
					'<links><link direction="to" catalog="'.$notes_catalogs_id.'" ordinal="0" hidden="0" other="'.$category_id.'">'.$title.'</link>'.
					'<link direction="from" catalog="'.$catalogs_id.'" ordinal="0" hidden="0" other="'.$entities_id.'">entity-'.$entities_id.'</link>'.
					'</links><children></children></entity>';
			$ent = OJ_Entities::from_xml_string($xml);
			$note_id = $ent->id;
		}
		return $note_id;
	}
	
}

