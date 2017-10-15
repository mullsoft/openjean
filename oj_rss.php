<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ('rss/simplepie-1.5/autoloader.php');

class OJ_RSS_Status_LinkSorter extends OJLinkSorter
{
	private $_status;
	
	public function __construct($feedid)
	{
		$this->_status = OJ_Row::load_hash_of_all_objects("OJ_Rss_Items", ["feed_entities_id"=>$feedid], "item_entities_id");
	//	OJ_Logger::get_logger()->ojdebug($feedid, "status", $this->_status);
	}
	
	public function compare($lnka, $lnkb)
	{
//		$stata = OJ_Rss_Items::get_status($lnka->to);
//		$statb = OJ_Rss_Items::get_status($lnkb->to);
		$stata = isset($this->_status[$lnka->to_entities_id])?$this->_status[$lnka->to_entities_id]->status:0;
		$statb = isset($this->_status[$lnkb->to_entities_id])?$this->_status[$lnkb->to_entities_id]->status:0;
	//	OJ_Logger::get_logger()->ojdebug($lnka->to_entities_id, $stata, $lnkb->to_entities_id, $statb);
		return $stata < $statb?-1:($stata> $statb?1:0);
	}

}

class OJ_RSS_Catalog_Display extends OJ_Catalog_Display
{
	public function can_create_items($index) {
		return false;
	}

//	public function get_group_name() {
//		return "feed";
//	}

	public function get_new_entity_pages($index, $etype) {
		switch (strtolower(OJ_Entity_Types::get_entity_type_name($etype)))
		{
			case "group":
				$ret = ["channel"=>null];
				break;
			case "item":
				$ret = ["content"=>null];
				break;
			default:
				$ret = null;
				break;
		}
		return $ret;
	}

	public function get_subcats($lnk, $followrefs)
	{
		$subcats = $this->get_children($lnk, $followrefs);
		if ($lnk["type"] === 'GROUP')
		{
//			OJ_Logger::get_logger()->ojdebug("lnk", $lnk);
			$ls = new OJ_RSS_Status_LinkSorter($lnk["to_entities_id"]);//new OJ_LinkSorterArray_LinkSorter([new OJ_RSS_Status_LinkSorter(), new OJ_Attribute_LinkSorter("detail", "published")]);
			$ls->sort_links($subcats);
		}
		return $subcats;
	}
	
	/**
	* Removes invalid XML
	*
	* @access public
	* @param string $value
	* @return string
	*/
   function stripInvalidXml($value)
   {
	   $ret = "";
	   $current;
	   if (empty($value)) 
	   {
		   return $ret;
	   }

	   $length = strlen($value);
	   for ($i=0; $i < $length; $i++)
	   {
		   $current = ord($value{$i});
		   if (($current == 0x9) ||
			   ($current == 0xA) ||
			   ($current == 0xD) ||
			   (($current >= 0x20) && ($current <= 0xD7FF)) ||
			   (($current >= 0xE000) && ($current <= 0xFFFD)) ||
			   (($current >= 0x10000) && ($current <= 0x10FFFF)))
		   {
			   $ret .= chr($current);
		   }
		   else
		   {
			   $ret .= " ";
		   }
	   }
	   return $ret;
   }
	
	public function load_feed($feedid, $cache = null)
	{
		OJ_Logger::get_logger()->ojdebug("load_feed ".$feedid);
		$grpid = OJ_Entity_Types::get_entity_type_id("GROUP");
		if ($feedid == 0)
		{
			$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
			$where = ["entity_types_id"=>$grpid, "catalogs_id"=>$rsscatid];
			$feedids = OJ_Row::load_column("entities", "id", $where);
			foreach ($feedids as $fid)
			{
				$this->load_feed1($fid, $cache);
			}
		}
		else
		{
			$feedetid = OJ_Entities::get_entity_types_id($feedid);
			if ($feedetid == $grpid)
			{
				$this->load_feed1($feedid, $cache);
			}
			else
			{
				$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
				$feedids = OJ_Links::get_all_descendants($rsscatid, $feedid, $grpid);
				OJ_Logger::get_logger()->ojdebug("load descendants of feedid ".$feedid);
				foreach ($feedids as $fid)
				{
					OJ_Logger::get_logger()->ojdebug("load ".$fid);
					$this->load_feed1($fid, $cache);
				}
			}
		}
	}
	
	private function load_feed1($feedid, $cache)
	{
		$feedurl = OJ_Entities::get_attribute_value($feedid, "channel", "feed");
		OJ_Logger::get_logger()->ojdebug("load ".$feedurl);
		$feed = new SimplePie();
		if ($cache == null)
		{
			$cache = $_SERVER['DOCUMENT_ROOT'] . '/openjean/rsscache';
		}
		$feed->set_cache_location($cache);
		$xml = file_get_contents($feedurl);
		$feed->set_raw_data($this->stripInvalidXml($xml));
//		$feed->set_feed_url($feedurl);
		$feed->init();
		$feed->handle_content_type();
		$items = $feed->get_items();
//		$oldlinks = OJ_Utilities::get_array_values_as_hash(OJ_Links::get_all_links_from($this->get_catalog_id(), $feedid), "to_entities_id");
		$old1sres = OJ_Row::load_array_of_objects("OJ_Rss_Items", ["feed_entities_id"=>$feedid]);//  load_hash_of_all_objects("OJ_Rss_Items", ["feed_entities_id"=>$feedid], "rssid");
		$old1s = $old1sres['result'];
		OJ_Logger::get_logger()->ojdebug("loaded ".count($items)." new, ".count($old1s)." old for feed ".$feedurl);
		$olditems = [];
		$oldlinks = [];
		foreach ($old1s as $old1)
		{
			$olditems[$old1->rssid] = $old1;
			$oldlinks[] = $old1->item_entities_id;
		}
		$itemsbyid = [];
		$catalogs_id = $this->get_catalog_id();
		foreach ($items as $item)
		{
			if ($item)
			{
				$itemrssid = $item->get_id();
				if ($itemrssid != null)
				{
					// is it already there
					if (array_key_exists($itemrssid, $olditems))
					{
						$stat = $olditems[$itemrssid];
						$itemsbyid[$stat->item_entities_id] = $item;
					}
					else
					{
						$entity_types_id = OJ_Entity_Types::get_entity_type_id("ITEM");
						$tm = strtotime($item->get_date());
						$dt = date("Y-m-d H:i:s", $tm);
						$contents = htmlentities($item->get_content(), ENT_XML1);
						if (strlen($contents) > 62000)
						{
							$contents = '(contents too long to display)';
						}
						$xml = '<?xml version="1.0"?><entity type="'.$entity_types_id.'" catalog="'.$catalogs_id.'"><name>'.
								htmlentities($item->get_title(), ENT_XML1).'</name>'.'<pages><page name="detail" visible="0">'.
								'<attribute type="URL" visible="1" ordinal="0"><name>link</name><value>'.$item->get_permalink().'</value></attribute>'.
								'<attribute type="string" visible="1" ordinal="1"><name>id</name><value>'.$item->get_id().'</value></attribute>'.
								'<attribute type="datetime" visible="1" ordinal="2"><name>published</name><value>'.$dt.'</value></attribute>'.
								'</page><page name="content" visible="1">'.
								'<attribute type="HTML" visible="1" ordinal="0"><name>description</name><value>'.$contents.'</value></attribute>'.
								'</page></pages><links>'.
								'<link direction="to" catalog="'.$catalogs_id.'" ordinal="0" hidden="0" other="'.$feedid.'"/>'.
								'</links><children/></entity>';
//						OJ_Logger::get_logger()->ojdebug1("xml: ".$xml);
						$ent = OJ_Entities::from_xml_string($xml);
//						OJ_Logger::get_logger()->ojdebug("ent: ",$ent);
						if ($ent != null)
						{
							$rss_item = new OJ_Rss_Items(["item_entities_id"=>$ent->id, "feed_entities_id"=>$feedid, "rssid"=>$item->get_id(), "status"=>0]);
//							OJ_Logger::get_logger()->ojdebug("rss_item: ",$rss_item);
							$rss_item->save(true, false);
//							OJ_Logger::get_logger()->ojdebug1("saved");
//							OJ_Rss_Items::view_item($item->get_id(), $ent->id, $feedid);
						}
					}
				}
			}
//			print "TITLE: ".$item->get_title()."\n\n";
//			print "ID: ".$item->get_id()."\n\n";
//			print $item->get_description()."\n\n\n";
		}
		// delete any that have fallen off the list and been read
		foreach ($oldlinks as $oldid)
		{
			if (!array_key_exists($oldid, $itemsbyid))
			{
				if (OJ_Row::delete_rows("rss_items", ["item_entities_id"=>$oldid, "status"=>1]))
				{
					OJ_Entities::delete_entity($oldid, $catalogs_id);
				}
			}
		}
	}

	public function on_group_create($new_entities_id)
	{
		$this->load_feed($new_entities_id);
	}

	public function on_attribute_display($entities_id, $feedid)
	{
		OJ_Logger::get_logger()->ojdebug1("on_attribute_display ".$entities_id." ".$feedid);
		$etypeitem = OJ_Entity_Types::get_entity_type_id("ITEM");
		$ent = new OJ_Entities($entities_id);
		if ($ent->entity_types_id == $etypeitem)
		{
			$rssid = OJ_Entities::get_attribute_value($entities_id, "detail", "id");
			OJ_Rss_Items::view_item($rssid, $entities_id, $feedid);
		}
	}

	public function get_display_panel_contents($indexname, $ojid, $editable = false)
	{
		$etype = OJ_Entities::get_entity_types_name($ojid);
		switch ($etype)
		{
			default:
			case "CATEGORY":
				$ret = "";
				break;
			case "GROUP":
				$contents = stripslashes(OJ_Entities::get_attribute_value($ojid, "content", "description"));
				$cdiv = new OJ_DIV($contents, null, "oj-rss-contents");
				$ret = OJ_HTML::to_html($cdiv);
				break;
			case "ITEM":
				$lnkaddress = OJ_Entities::get_attribute_value($ojid, "detail", "link");
				$lnk = new OJ_A($lnkaddress, OJ_Entities::get_entity_name($ojid));
				$lnk->add_attribute("target", "_blank");
				$lnkdiv = new OJ_DIV(new OJ_H(4, $lnk), null, "oj-rss-lnkdiv");
				$contents = stripslashes(OJ_Entities::get_attribute_value($ojid, "content", "description"));
				$cdiv = new OJ_DIV($contents, null, "oj-rss-contents");
				$ret = OJ_HTML::to_html([$lnkdiv, $cdiv]);
				break;
		}
		return $ret;
	}
	
	public function get_cssclass($entities_id, $type)
	{
		if ($type == "ITEM")
		{
			if (OJ_Rss_Items::has_been_viewed($entities_id))
			{
				$ret = "oj-rss-viewed";
			}
			else
			{
				$ret = "oj-rss-notviewed";
			}
		}
		else
		{
			$ret = "";
		}
		return $ret;
	}

	public function get_display_column_cssclass()
	{
		return "col-xs-8 col-sm-8 col-md-8";
	}

	public function get_filter_column_cssclass()
	{
		return "col-xs-4 col-sm-4 col-md-4";
	}

	public function get_entity_menu_items($indexname)
	{
		$reload_a = new OJ_A('#', "Reload", null, null, "oj_reloadrss();");
		$reload_a->add_attribute("target", "_self");
		$reload_li = new OJ_LI($reload_a, null, null);
		return [OJ_HTML::to_html($reload_li)];
	}

	public function oj_action_rssreload($params)
	{
		if (isset($params["feed"]))
		{
			$feedids = explode(',', $params["feed"]);
//				OJ_Logger::get_logger()->ojdebug("1.rssreload ".$feedids);
		}
		else
		{
			$grp = OJ_Entity_Types::get_entity_type_id("GROUP");
			$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
			$where = ["entity_types_id" => $grp, "catalogs_id" => $rsscatid];
			$feedids = OJ_Row::load_column("entities", "id", $where);
			OJ_Logger::get_logger()->ojdebug("2.rssreload ", $feedids);
		}
		$cd = new OJ_RSS_Catalog_Display($this->_username, $this->_catalogname);
		foreach ($feedids as $feedid)
		{
			$this->load_feed($feedid);
		}
		echo "ok";
	}

	public function oj_action_rssmarkread($params)
	{
		$feedid = $params['feed'];
		$grpid = OJ_Entity_Types::get_entity_type_id("GROUP");
		$feedetid = OJ_Entities::get_entity_types_id($feedid);
		if ($feedetid == $grpid)
		{
			OJ_Rss_Items::view_feed($feedid);
		}
		else
		{
			$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
			$feedids = OJ_Links::get_all_descendants($rsscatid, $feedid, $grpid);
			foreach ($feedids as $fid)
			{
				OJ_Rss_Items::view_feed($fid);
			}
		}
		echo "ok";
	}

	public function oj_action_rssunread($params)
	{
		$feedid = $params['feed'];
		echo json_encode([$feedid => OJ_Rss_Items::get_number_unread($feedid)]);
	}

}

