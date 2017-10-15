<?php
//echo "mode ".$mode;exit;
//echo "here1 ".$ojmode." ".$editingst;//exit;
//require_once("ojEntity_".$ojmode.".php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("OJDatabase.php");
require_once("OJCatalogs.php");
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_HTML_Display
{
	private static function get_new_category_div($parentid = 0, $load = "")
	{
		$suffix = "-".$parentid;
		$inp = new OJ_INPUT("text", "new-category-name".$suffix, "new-category-name".$suffix, "new-category-input", null);
		$inp->add_attribute("placeholder", "new category");
		$btn = new OJ_BUTTON("button", "+", "new-category-add".$suffix, "new-category-add-button oj-button", "add_new_category(".$parentid.", '".$load."');");
		$btn->add_attribute( "data-mini", "true");
		return new OJ_DIV(array($inp, $btn), "new-category-div".$suffix, "oj-new-category-div-class oj-div-class form-group");
	}
	
	public static function get_subentity_accordion($parent, $subid, $selectable = false)
	{
		$parentid = $parent->id;
		$parentname = $parent->get_name();
		$subcats = $parent->get_children();
		$ret = null;
		if (($subcats != null) && (count($subcats )> 0))
		{
			$sub = array();
			foreach ($subcats as $subcat)
			{
				$subcontents = self::get_subentity_select_list($subcat, $selectable, $subid);
//				var_dump($subcat->name);
				$sub[strval($subcat->get_name())] = $subcontents;
			}
			$ret = new OJ_Accordion($sub, $subid, null, 4, $selectable);
		}
		return $ret;
	}
	
	public static function get_subentity_div($parent, $accid = null)
	{
		$parentid = strval($parent["to_entities_id"]);
		$parentname = strval($parent["name"]);
		$parenttype = strval($parent["type"]);
		$parentcat = OJ_Catalogs::get_catalog_name($parent["from_calaogs_id"]);
		$sedcontents = [];
//		$sedcontents[] = new OJ_H(2, $parentname);
		$sedcontents[] = new OJ_INPUT("hidden", "oj-entity-id", "oj-entity-id-".$parentid, null, $parentid);
		$sedcontents[] = new OJ_INPUT("hidden", "oj-entity-name", "oj-entity-name-".$parentid, null, $parentname);
		$sedcontents[] = new OJ_INPUT("hidden", "oj-entity-type", "oj-entity-type-".$parentid, null, $parenttype);
		$sedcontents[] = new OJ_INPUT("hidden", "oj-entity-catalog", "oj-entity-catalog-".$parentid, null, $parentcat);
//		$contents[] = new OJ_A("#", $parentname, "oj-entity-link-".$parentid, "oj-entity-link", "oj_select_entity(".$parentid.",'".$parentname."', '".$parenttype."');");
		$sedcontents[] = new OJ_DIV("   ", "oj-entity-div-".$parentid, "oj-entity-div");
//		return new OJ_Collapse($sedcontents, "oj-collapse-".$parentid, "oj-collapse", $parentname, 2, false, $accid, $selectable);
		return $sedcontents;
	}
	
	public static function get_subentity_select_list($username, $catalogname, $indexname, $ojid, $name, $type, $id, $cssclass, $selectable = false)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$lnk = OJ_Link::create_link_entity($catalog, $ojid, $name, $type, false);
//		$lnk->set_omit_from($root);
		$lnk = OJ_Catalogs::get_entity_link($catalogname, $ojid, $name, $type);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$contents = [];
		$editingst = isset($_GET['edit']) ? $_GET['edit'] : 'false';
		if (($type === "CATEGORY") && OJ_Utilities::get_system_parameter("editing"))
//		if (($type === "CATEGORY") && ($editingst !== 'false'))
		{
			$contents[] = self::get_new_category_div();
		}
//		$subcats = $lnk->get_children();
//		var_dump($lnk);
//		var_dump($subcats);
		$sub = [];
		foreach ($subcats as $subcat)
		{
			$subcontents = self::get_subentity_div($subcat, $id);
			$type = strval($subcat["type"]);
			$nm = strval($subcat["name"]);
			if ($type !== 'CATEGORY')
			{
				if (($type === 'GROUP') || self::items_selectable($catalogname, $indexname))
				{
					$nm .= '__selectable';
				}
			}
			$sub[$nm] = $subcontents;
		}
//		var_dump($sub);
		$acc = new OJ_Accordion($sub, $id, $cssclass, 2, $selectable);
//		var_dump($acc);exit;
		$contents[] = $acc;
		return $contents; //new OJ_DIV($contents, $id, $cssclass);
	}
	
//	public static function get_entity_select_list($username, $catalogname, $indexname, $id, $cssclass)
//	{
////		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
////		$root = $catalog->get_root($indexname);
////		var_dump($root);
//		$root = OJ_Catalogs::get_root_link($catalogname, $indexname);
//		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
//		$subcats = $cd->get_subcats($lnk, false);
//		$contents = [];
//		$editingst = isset($_GET['edit']) ? $_GET['edit'] : 'false';
//		if (OJ_Utilities::get_system_parameter("editing"))
////		if ($editingst !== 'false')
//		{
//			$contents[] = self::get_new_category_div();
//		}
////		$subcats = $root->get_children();
////		var_dump($subcats);
//		$sub = [];
//		foreach ($subcats as $subcat)
//		{
//			$subcontents = self::get_subentity_div($subcat, $id);
//			$sub[strval($subcat["name"])] = $subcontents;
//		}
////		var_dump($sub);
//		$acc = new OJ_Accordion($sub, $id, $cssclass, 2, false);
////		var_dump($acc);exit;
//		$contents[] = $acc;
//		return $contents; //new OJ_DIV($contents, $id, $cssclass);
//	}
//	
	public static function get_entity_and_group_select_lists1($username, $catalogname, $indexname)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$contents = [];
//		$subcats = $root->get_children();
		$root = OJ_Catalogs::get_root_link($catalogname, $indexname);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$fcats = [];
		$catnames = [];
//		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$stype = $subcat->type;
			$subf = ["label"=>$subcat->get_name(), "value"=>$tocatid."|".$stype."|".$subcat->get_destination_catalog_name(), "id"=>$tocatid,
				"cssclass"=>"filter-class-entity filter-class-".strtolower($stype)];
			$tt = $subcat->get_tooltip();
			if ($tt)
			{
				$subf["tooltip"] = $tt;
			}
			if ($stype !== 'ITEM')
			{
				$subf["values"] = [];
			}
			$xtras = $cd->get_filter_parameters($subcat);
			foreach ($xtras as $x => $v)
			{
				$subf[$x] = $v;
			}
			$fcats[] = $subf;
		}
		return json_encode($fcats); //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_subentity_and_group_select_list1($username, $catalogname, $indexname, $ojid, $name, $type, $selectable = false, $followrefs = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$lnk = OJ_Link::create_link_entity($catalog, $ojid, $name, $type, false);
//		$lnk->set_omit_from($root);
		$lnk = OJ_Catalogs::get_entity_link($catalogname, $ojid, $name, $type);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$followrefs = ($type === 'ITEM') && $cd->follow_references() && $followrefs;
		$subcats = $cd->get_subcats($lnk, $followrefs);
		$fcats = [];
		$ccats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$stype = $subcat->type;
			$val1 = $tocatid."|".$stype."|".$catname;
			if ($catname === 'Artists')
			{
				$atyp = OJ_Audio_Utilities::get_artist_type($subcat->to);
				if ($atyp)
				{
					$val1 .= '|'.$atyp;
				}
				OJ_Logger::get_logger()->ojdebug1("val1 ".$val1);
			}
			$subf = ["label"=>$subcat->get_name(), "value"=>$val1, "id"=>$tocatid, "cssclass"=>"filter-class-entity filter-class-".strtolower($stype)];
			$tt = $subcat["tooltip"];
			if ($tt)
			{
				$subf["tooltip"] = $tt;
			}
			$xtras = $cd->get_filter_parameters($subcat);
			foreach ($xtras as $x => $v)
			{
				$subf[$x] = $v;
			}
			if ($stype === 'ITEM')
			{
				if ($cd->follow_references() && $followrefs)
				{
					$subf["values"] = [];
				}
			}
			else
			{
				$subf["values"] = [];
			}
			$fcats[] = $subf;
			$st = $cd->get_sort_type($subcat);
			if ($st === 'CATEGORY')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$stype."|".$catname, "id"=>$tocatid, "selectable" => false];
				$nextsubs = self::get_subgroup_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'CATEGORY', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
			elseif ($st === 'GROUP')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$stype."|".$catname, "id"=>$tocatid, "selectable" => false];
				$nextsubs = self::get_subgroup_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'GROUP', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
		}
		return json_encode($fcats);

		foreach ($subcats as $subcat)
		{
		}
//		var_dump($ccats);
		return $ccats;
		
	
	}
	
	public static function get_entity_select_list1($username, $catalogname, $indexname)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$subcats = $root->get_children();
		$lnk = OJ_Catalogs::get_root_link($catalogname, $indexname);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$fcats = [];
//		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		foreach ($subcats as $subcat)
		{
			$subf = ["label"=>$subcat["name"], "value"=>$subcat["to_entities_id"]."|".$subcat["type"]."|".OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]),
				"id"=>$subcat["to_entities_id"],
				"cssclass"=>"filter-class-entity filter-class-".strtolower($subcat["type"])];
			$tt = $subcat["tooltip"];
			if ($tt)
			{
				$subf["tooltip"] = $tt;
			}
			if ($subcat["type"] !== 'ITEM')
			{
				$subf["values"] = [];
			}
			$xtras = $cd->get_filter_parameters($subcat);
			foreach ($xtras as $x => $v)
			{
				$subf[$x] = $v;
			}
			$fcats[] = $subf;
		}
		return json_encode($fcats); //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_category_select_list($username, $catalogname, $indexname, $prefix = "", $include_root = false)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
		$root = OJ_Catalogs::get_root_link($catalogname, $indexname);
		if ($include_root)
		{
			$tocatid = $root["to_entities_id"];
			$catname = OJ_Catalogs::get_catalog_name($root["to_catalogs_id"]);
			$ret = [];
			$padding = 10;
			$subci = new OJ_INPUT("radio", $catalogname.'-select-category', $prefix.$catalogname.'-select-category-input-'.$tocatid, "oj-select-category-input",
					$tocatid."|".$root["type"]."|".$catname);
			$subpt = new OJ_INPUT("hidden", null, $prefix.$catalogname.'-select-category-parent-'.$tocatid, null, "0");
			$subcs = new OJ_SPAN("root", $prefix.$catalogname.'-select-category-span-'.$tocatid, "oj-select-category-span");
			$subcl = new OJ_LABEL(null, [$subci, $subpt, $subcs], $prefix.$catalogname.'-select-category-label-'.$tocatid, "oj-select-category-label");
			$subcd = new OJ_DIV($subcl, $prefix.$catalogname.'-select-category-div-'.$tocatid, "oj-select-category-div");
			$subcd->add_attribute("style", "padding-left:".$padding."px;");
			$nextsubs = self::get_subcategory_select_list($username, $catalogname, $indexname, $root, $padding + 10, $prefix);
			if (($nextsubs == null) || (count($nextsubs) == 0))
			{
				$ret[] = $subcd;
			}
			else {
				$ret[] = [$subcd, $nextsubs];
			}
		}
		else
		{
			$ret = self::get_subcategory_select_list($username, $catalogname, $indexname, $root, 0, $prefix);
		}
		return $ret;
	}
	
	public static function get_subcategory_select_list($username, $catalogname, $indexname, $root, $padding = 0, $prefix = "")
	{
		$contents = [];
//		$subcats = $root->get_children();
		$ccats = [];
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($root, false);
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			if ($cd->treat_as_category($indexname, $subcat))
			{
				$tocatid = $subcat["to_entities_id"];
				if (!isset($catnames[$tocatid]))
				{
					$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
				}
				$catname = $catnames[$tocatid];
				$subci = new OJ_INPUT("radio", $catalogname.'-select-category', $prefix.$catalogname.'-select-category-input-'.$tocatid, "oj-select-category-input",
						$tocatid."|".$subcat["type"]."|".$catname);
				$subpt = new OJ_INPUT("hidden", null, $prefix.$catalogname.'-select-category-parent-'.$tocatid, null, "".$root["to_entities_id"]);
				$subcs = new OJ_SPAN($subcat["name"], $prefix.$catalogname.'-select-category-span-'.$tocatid, "oj-select-category-span");
				$subcl = new OJ_LABEL(null, [$subci, $subpt, $subcs], $prefix.$catalogname.'-select-category-label-'.$tocatid, "oj-select-category-label");
				$subcd = new OJ_DIV($subcl, $prefix.$catalogname.'-select-category-div-'.$tocatid, "oj-select-category-div");
				$subcd->add_attribute("style", "padding-left:".$padding."px;");
				$nextsubs = self::get_subcategory_select_list($username, $catalogname, $indexname, $subcat, $padding + 10, $prefix);
				if (($nextsubs == null) || (count($nextsubs) == 0))
				{
					$ccats[] = $subcd;
				}
				else {
					$ccats[] = [$subcd, $nextsubs];
				}
			}
		}
		return $ccats; //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_group_select_list($username, $catalogname, $indexname, $prefix = "", $include_root = false)
	{
		$root = OJ_Catalogs::get_root_link($catalogname, $indexname);
//		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		if ($include_root)
		{
			$tocatid = $root["to_entities_id"];
			$catname = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			$ret = [];
			$padding = 10;
			$subci = new OJ_INPUT("radio", $catalogname.'-select-category', $prefix.$catalogname.'-select-category-input-'.$tocatid, "oj-select-category-input",
					$tocatid."|".$root["type"]."|".$catname);
			$subcs = new OJ_SPAN($root->get_name(), $prefix.$catalogname.'-select-category-span-'.$tocatid, "oj-select-category-span");
			$subcl = new OJ_LABEL(null, [$subci, $subcs], $prefix.$catalogname.'-select-category-label-'.$tocatid, "oj-select-category-label");
			$subcd = new OJ_DIV($subcl, $prefix.$catalogname.'-select-category-div-'.$tocatid, "oj-select-category-div");
			$subcd->add_attribute("style", "padding-left:".$padding."px;");
			$nextsubs = self::get_subgroup_select_list($username, $catalogname, $indexname, $root, $padding + 10, $prefix);
			if (($nextsubs == null) || (count($nextsubs) == 0))
			{
				$ret[] = $subcd;
			}
			else {
				$ret[] = [$subcd, $nextsubs];
			}
		}
		else
		{
			$ret = self::get_subgroup_select_list($username, $catalogname, $indexname, $root, 0, $prefix);
		}
		return $ret;
	}
	
	public static function get_subgroup_select_list($username, $catalogname, $indexname, $root, $padding = 0, $prefix = "")
	{
		$contents = [];
//		$subcats = $root->get_children();
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($root, false);
		$ccats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$st = $cd->get_sort_type($subcat);
			if ($st === 'CATEGORY')
			{
				$subcs = new OJ_SPAN($subcat["name"], $prefix.$catalogname.'-select-group-span-'.$tocatid, "oj-select-group-span");
				$subcl = new OJ_LABEL(null, $subcs, $prefix.$catalogname.'-select-group-label-'.$tocatid, "oj-select-group-label");
				$subcd = new OJ_DIV($subcl, $prefix.$catalogname.'-select-group-div-'.$tocatid, "oj-select-group-div");
				$subcd->add_attribute("style", "padding-left:".$padding."px;");
				$nextsubs = self::get_subgroup_select_list($username, $catalogname, $indexname, $subcat, $padding + 10, $prefix);
				if (($nextsubs == null) || (count($nextsubs) == 0))
				{
					$ccats[] = $subcd;
				}
				else {
					$ccats[] = [$subcd, $nextsubs];
				}
			}
			elseif ($st === 'GROUP')
			{
				$subci = new OJ_INPUT("radio", $catalogname.'-select-group', $prefix.$catalogname.'-select-group-input-'.$tocatid, "oj-select-group-input",
						$tocatid."|".$subcat["type"]."|".$catname);
				$subcs = new OJ_SPAN($subcat["name"], $prefix.$catalogname.'-select-group-span-'.$tocatid, "oj-select-group-span");
				$subcl = new OJ_LABEL(null, [$subci, $subcs], $prefix.$catalogname.'-select-group-label-'.$tocatid, "oj-select-group-label");
				$subcd = new OJ_DIV($subcl, $prefix.$catalogname.'-select-group-div-'.$tocatid, "oj-select-group-div");
				$subcd->add_attribute("style", "padding-left:".$padding."px;");
				$nextsubs = self::get_subgroup_select_list($username, $catalogname, $indexname, $subcat, $padding + 10, $prefix);
				if (($nextsubs == null) || (count($nextsubs) == 0))
				{
					$ccats[] = $subcd;
				}
				else {
					$ccats[] = [$subcd, $nextsubs];
				}
			}
		}
		return $ccats; //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_subentity_select_list1($username, $catalogname, $indexname, $ojid, $name, $type, $selectable = false, $followrefs = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$lnk = OJ_Link::create_link_entity($catalog, $ojid, $name, $type, false);
//		$lnk->set_omit_from($root);
		$contents = [];
//		$editingst = isset($_GET['edit']) ? $_GET['edit'] : 'false';
//		if (($type === "CATEGORY") && OJ_Utilities::get_system_parameter("editing"))
////		if (($type === "CATEGORY") && ($editingst !== 'false'))
//		{
//			$contents[] = self::get_new_category_div();
//		}
		$lnk = OJ_Catalogs::get_entity_link($catalogname, $ojid, $name, $type);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, $followrefs);
//		var_dump($lnk);
//		var_dump($subcats);
		$fcats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $subcat["to_catalogs_id"];
			$toentid = $subcat["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$val1 = $toentid."|".$subcat["type"]."|".$catname;
			if ($catname === 'Artists')
			{
				$atyp = OJ_Audio_Utilities::get_artist_type($toentid);
				if ($atyp)
				{
					$val1 .= '|'.$atyp;
				}
				OJ_Logger::get_logger()->ojdebug1("val1 ".$val1);
			}
			$subf = ["label"=>$subcat["name"], "value"=>$val1, "id"=>$toentid,
				"cssclass"=>$cd->get_cssclass($toentid, $subcat["type"])." filter-class-entity filter-class-".strtolower($subcat["type"]),
				"selectable"=>$selectable];
			$tt = $subcat["tooltip"];
			if ($tt)
			{
				$subf["tooltip"] = $tt;
			}
			$xtras = $cd->get_filter_parameters($subcat);
			foreach ($xtras as $x => $v)
			{
				$subf[$x] = $v;
			}
			if ($cd->has_values($subcat, $followrefs))
			{
				$subf["values"] = [];
			}
			$fcats[] = $subf;
		}
		return json_encode($fcats);
	}
	
	public static function get_category_select_list1($username, $catalogname, $indexname, $selectable = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		var_dump($root);
		$contents = [];
//		$subcats = $root->get_children();
//		var_dump($subcats);
		$lnk = OJ_Catalogs::get_root_link($catalogname, $indexname);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$ccats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			if ($cd->get_sort_type($subcat) === 'CATEGORY')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid,
					"selectable" => $selectable];
				$nextsubs = self::get_subcategory_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
		}
//		var_dump($fcats);
		return json_encode($ccats); //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_subcategory_select_list1($username, $catalogname, $indexname, $ojid, $name, $selectable = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$lnk = OJ_Link::create_link_entity($catalog, $ojid, $name, 'CATEGORY', false);
//		$lnk->set_omit_from($root);
//		$editingst = isset($_GET['edit']) ? $_GET['edit'] : 'false';
//		if (($type === "CATEGORY") && OJ_Utilities::get_system_parameter("editing"))
////		if (($type === "CATEGORY") && ($editingst !== 'false'))
//		{
//			$contents[] = self::get_new_category_div();
//		}
//		$subcats = $lnk->get_children();
//		var_dump($lnk);
//		var_dump($subcats);
		$lnk = OJ_Catalogs::get_entity_link($catalogname, $ojid, $name, "CATEGORY");
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$ccats = [];
		$catnames = [];
//		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname);
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			if ($subcat->get_sort_type() === 'CATEGORY')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid, "selectable" => $selectable];
//			$xtras = $cd->get_filter_parameters($subcat);
//			foreach ($xtras as $x => $v)
//			{
//				$subf[$x] = $v;
//			}
				$nextsubs = self::get_subcategory_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
		}
//		var_dump($ccats);
		return $ccats;
	}
	
	public static function get_group_select_list1($username, $catalogname, $indexname, $selectable = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		var_dump($root);
//		$subcats = $root->get_children();
//		var_dump($subcats);
		$lnk = OJ_Catalogs::get_root_link($catalogname, $indexname);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$contents = [];
		$ccats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$st = $cd->get_sort_type($subcat);
			if ($st === 'CATEGORY')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid,
					"selectable" => false];
				$nextsubs = self::get_subgroup_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'CATEGORY', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
			elseif ($st === 'GROUP')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid,
					"selectable" => $selectable];
				$nextsubs = self::get_subgroup_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'GROUP', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
		}
//		var_dump($fcats);
		return json_encode($ccats); //new OJ_DIV($contents, $id, $cssclass);
	}
	
	public static function get_subgroup_select_list1($username, $catalogname, $indexname, $ojid, $name, $containertype, $selectable = true)
	{
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		$root = $catalog->get_root($indexname);
//		$lnk = OJ_Link::create_link_entity($catalog, $ojid, $name, $containertype, false);
//		$lnk->set_omit_from($root);
//		$subcats = $lnk->get_children();
		$lnk = OJ_Catalogs::get_entity_link($catalogname, $ojid, $name, "CATEGORY");
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		$subcats = $cd->get_subcats($lnk, false);
		$ccats = [];
		$catnames = [];
		foreach ($subcats as $subcat)
		{
			$tocatid = $root["to_entities_id"];
			if (!isset($catnames[$tocatid]))
			{
				$catnames[$tocatid] = OJ_Catalogs::get_catalog_name($subcat["to_catalogs_id"]);
			}
			$catname = $catnames[$tocatid];
			$st = $cd->get_sort_type($subcat);
			if ($st === 'CATEGORY')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid, "selectable" => false];
				$nextsubs = self::get_subgroup_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'CATEGORY', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
			elseif ($st === 'GROUP')
			{
				$subc = ["label"=>$subcat["name"], "value"=>$tocatid."|".$subcat["type"]."|".$catname, "id"=>$tocatid, "selectable" => false];
				$nextsubs = self::get_subcategory_select_list1($username, $catalogname, $indexname, $tocatid, $subcat["name"], 'GROUP', $selectable);
				if (($nextsubs != null) && (count($nextsubs) > 0))
				{
					$subc["values"] = $nextsubs;
				}
				$ccats[] = $subc;
			}
		}
//		var_dump($ccats);
		return $ccats;
	}
	
	public static function get_subtype_values($catalogs_id, $entity_types_id)
	{
		$cid = OJ_Catalogs::get_catalog_id($catalogs_id);
		$etid = OJ_Entity_Types::get_entity_type_id($entity_types_id);
		$eid = 0 - (($cid * 10) + $etid);
		$prop = OJ_Row::load_single_object("OJ_Properties", ["entities_id"=>$eid, "name"=>"subtype"]);
		return $prop == null?null:($prop->get_value());
	}
	
	public static function items_selectable($cat, $ind)
	{
		$ret = true;
		$catalog = $cat === null?"multimedia":$cat;
		$index = $ind === null?($catalog == 'multimedia'?'audio':"default"):$ind;
		switch ($catalog."__".$index)
		{
			case "artists__audio":
			case "multimedia__artists":
				$ret = false;
				break;
			default:
				$ret = true;
				break;
		}
//		echo $cat." ".$ind." ".$ret."<br/>";
		return $ret;
	}
	
	public static function get_css_links($catalog)
	{
		$ret = "";
		switch ($catalog)
		{
			case 'multimedia':
			case "artists":
				$ret = '<link rel="StyleSheet" type="text/css" href="css/jplayer.blue.monday.css"/>'.
					'<link rel="StyleSheet" type="text/css" href="css/oj_multimedia.css"/>';
				break;
			case "rss":
				$ret = '<link rel="StyleSheet" type="text/css" href="css/oj_rss.css"/>';
				break;
		}
		return $ret;
	}
	
	public static function get_js_scripts($catalog)
	{
		$ret = "";
		switch ($catalog)
		{
			case 'multimedia':
			case "artists":
				$ret = '<script src="js/jquery.jplayer.min.js"></script>'.
						'<script src="js/jplayer.playlist.min.js"></script>';
				break;
		}
		return $ret;
	}
	
	public static function get_new_entity_panel($username, $catalogs_id, $entity_types_id, $pages = null)
	{
		$atts = OJ_Entities::get_default_attributes($catalogs_id, $entity_types_id);
		OJ_Logger::get_logger()->ojdebug("new entity attributes", $atts);
		$ret = "";
		$nkeys = $atts == null?0:count(array_keys($atts));
		$catalogname = OJ_Catalogs::get_catalog_name($catalogs_id);
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname);
		if ($nkeys > 0)
		{
			$divs = [];
			$nav = [];
			$n = 1;
			$pgkeys = $pages == null?[]:array_keys($pages);
			if ($pages && (count($pgkeys) == 1))
			{
				$whichatts = $pages[$pgkeys[0]];
				$attributes = $atts[$pgkeys[0]];
				$attcomponents = [];
//				array_push($attcomponents, new OJ_H(3, $pagename));
				foreach ($attributes as $attname=>$attribute)
				{
					if (($whichatts == null) || (array_search($attname, $whichatts) !== FALSE))
					{
						$comp = $cd->get_component($attribute, true);
						$typ = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-type", $attribute['type']);
						$pg = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-page", $attribute["page"]);
						$att = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-name", $attname);
						$ord = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-ordinal", $attribute["ordinal"]);
						$vis = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-vis", ($attribute["visible"]?"1":"0"));
						array_push($attcomponents, new OJ_DIV([$comp, $typ, $pg, $att, $ord, $vis], null, "oj-new-entity-component-div"));
					}
				}
				$ret = OJ_HTML::to_html($attcomponents);
			}
			else
			{
				$newatts = [];
				foreach ($atts as $pagename=>$attributes)
				{
					$attindex = array_search($pagename, $pgkeys);
					if (($pages == null) || ($attindex !== FALSE))
					{
						$whichatts = $pages == null?null:$pages[$pgkeys[$attindex]];
						$newattributes = [];
						foreach ($attributes as $attname=>$attribute)
						{
							if (($whichatts == null) || (array_search($attname, $whichatts) !== FALSE))
							{
								$newattributes[$attname] = $attribute;
							}
						}
						$newatts[$pagename] = $newattributes;
					}
				}
				$newattskeys = array_keys($newatts);
				$newnkeys = $newatts == null?0:count($newattskeys);
				if ($newnkeys > 0)
				{
					if ($newnkeys == 1)
					{
						$attcomponents = [];
						$attributes = $newatts[$newattskeys[0]];
		//				array_push($attcomponents, new OJ_H(3, $pagename));
						foreach ($attributes as $attname=>$attribute)
						{
							$comp = $cd->get_component($attribute, true);
							$typ = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-type", $attribute['type']);
							$pg = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-page", $attribute["page"]);
							$att = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-name", $attname);
							$ord = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-ordinal", $attribute["ordinal"]);
							$vis = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-vis", ($attribute["visible"]?"1":"0"));
							array_push($attcomponents, new OJ_DIV([$comp, $typ, $pg, $att, $ord, $vis], null, "oj-new-entity-component-div"));
						}
						$ret = OJ_HTML::to_html($attcomponents);
					}
					else
					{
						foreach ($newatts as $pagename=>$attributes)
						{
							$attcomponents = [];
			//				array_push($attcomponents, new OJ_H(3, $pagename));
							foreach ($attributes as $attname=>$attribute)
							{
								$comp = $cd->get_component($attribute, true);
								$typ = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-type", $attribute['type']);
								$pg = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-page", $attribute["page"]);
								$att = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-name", $attname);
								$ord = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-ordinal", $attribute["ordinal"]);
								$vis = new OJ_INPUT("hidden", null, null, "oj-new-entity-attribute-vis", ($attribute["visible"]?"1":"0"));
								array_push($attcomponents, new OJ_DIV([$comp, $typ, $pg, $att, $ord, $vis], null, "oj-new-entity-component-div"));
							}
							array_push($divs, new OJ_DIV($attcomponents, "oj-new-entity-tab-".$n, "tab-pane oj-new-entity-tab"));
							$a = new OJ_A("#oj-new-entity-tab-".$n, $pagename);
							$a->add_attribute("data-toggle", "tab");
							array_push($nav, new OJ_LI($a));
							$n++;
						}
						$ret = OJ_HTML::to_html([new OJ_LIST($nav, false, "oj-attributes-tabs", "nav nav-pills"), new OJ_DIV($divs, "oj-new-entity-tab-content", "tab-content clearfix")]);
					}
				}
			}
		}
		return $ret;
	}
	
	public static function get_display_panel_contents($username, $catalogname, $indexname, $ojid, $editable = false)
	{
//		$ret = "";
//		switch ($catalog)
//		{
//			case 'multimedia':
//			case "artists":
//				$ret = '<h4>Playlist</h4>'.
//					'<ul id="oj-playlist-list">'.
//					'</ul>'.
//					'<p>'.
//					'<a class="btn btn-primary" href="#" role="button" onclick="play_playlist();">Play</a>'.
//					'<a class="btn btn-primary" href="#" role="button" onclick="clear_playlist();">Clear</a>'.
//					'</p>';
//				break;
//		}
//		echo "here"; exit;
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
//		var_dump($cd);
		if ($ojid == 0)
		{
			$ojid = $cd->get_root_id();
		}
		return $cd === null?null:$cd->get_display_panel_contents($indexname, $ojid, $editable);
	}
	
	private static function get_cmp($prefix)
	{
		return function($cmpa, $cmpb) use ($prefix)
		{
			if ($prefix == null)
			{
				$ret = $cmpa > $cmpb?-1:($cmpa == $cmpb?0:1);
			}
			else
			{
				$len = strlen($prefix);
				$ca = substr($cmpa, $len);
				$cb = substr($cmpb, $len);
				$ret = $ca > $cb?-1:($ca == $cb?0:1);
			}
			return $ret;
		};
	}
	
	public static function get_sorted_logicals($prefix)
	{
		$logicals = OJ_Logicals::get_logicals($prefix);
		uksort($logicals, self::get_cmp($prefix));
		return $logicals;
	}
	
	public static function get_logical_select($name, $prefix)
	{
		$logicals = self::get_sorted_logicals($prefix);
		$options = [];
		$n = 0;
		foreach ($logicals as $lname => $logical)
		{
//			if (OJ_Utilities::starts_with($lname, $prefix))
//			{
				$options[] = new OJ_OPTION($lname, $logical->name."|".$logical->value.'|'.$logical->alternative, $n === 0);
				$n++;
//			}
		}
		return new OJ_SELECT($options, $name, $name."-id", "oj-select");
	}
	
	public static function get_index_list($catalog, $user)
	{
		$indices = OJ_Row::load_column("indexes", "name", ["catalogs_id"=>  OJ_Catalogs::get_catalog_id($catalog)]);
		$ret = [];
		$u = "--".$user;
		$ul = strlen($u);
		foreach($indices as $index)
		{
			if (strpos($index, "--") === FALSE)
			{
				$ret[] = ["label"=>$index, "value"=>$index];
			}
			elseif (OJ_Utilities::ends_with($index, $u))
			{
				$ret[] = ["label"=>substr($index, 0, 0 - $ul), "value"=>$index];
			}
		}
		$cd = OJ_Catalog_Display::get_catalog_display($user, $catalog);
		$extra = $cd->get_extra_indexes();
		foreach ($extra as $ex)
		{
			$ret[] = $ex;
		}
		return $ret;
	}
	
	public static function get_entity_menu_items($username, $catalogname, $indexname)
	{
		$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
		return $cd->get_entity_menu_items($indexname);
	}

	public function check_password($pwd, &$errors)
	{
		$errors_init = $errors;

		if (strlen($pwd) < 8) {
			$errors[] = "Password too short!";
		}

		if (!preg_match("#[0-9]+#", $pwd)) {
			$errors[] = "Password must include at least one number!";
		}

		if (!preg_match("#[a-zA-Z]+#", $pwd)) {
			$errors[] = "Password must include at least one letter!";
		}     

		return ($errors == $errors_init);
	}

}


?>