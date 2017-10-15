<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("ojEntity_xml.php");
require_once("OJDatabase.php");

class OJ_Importer
{
	
	public static function import($user, $cats, $eid, $cmds, $restart = false, $omit = null)
	{
		$ojsys = OJ_System::instance($user);
		if (($cmds == null) || (count($cmds) == 0))
		{
			$cmds = [];
		}
		for ($n = 0; $n < count($cmds); $n++)
		{
			switch ($cmds[$n])
			{
			case "logicals":
				self::import_logicals($ojsys);
				break;
			case "parameters":
//var_dump($cats);
				foreach ($cats as $clog)
				{
					self::import_parameters($ojsys, $clog);
				}
				break;
			case "defaultproperties":
				foreach ($cats as $clog)
				{
					self::import_properties($ojsys, $clog);
				}
				break;
			case "defaultattributes":
				foreach ($cats as $clog)
				{
					self::import_attributes($ojsys, $clog);
				}
				break;
			case "indexes":
				foreach ($cats as $clog)
				{
					self::import_indexes($ojsys, $clog);
				}
				break;
			case "entity":
				$ents = explode(",", $eid);
				foreach ($cats as $clog)
				{
					foreach ($ents as $ent)
					{
						self::import_entity($ojsys, $clog, $ent);
					}
				}
				break;
			case "catalog":
				self::import_catalogs($ojsys, $cats, $restart, $omit);
				break;
			case "folders":
				foreach ($cats as $clog)
				{
					self::import_folders($ojsys, $clog);
				}
				break;
			case "links":
				foreach ($cats as $clog)
				{
					self::import_links($ojsys, $clogs);
				}
				break;
			default:
				print ("unknown command ".$arg);
				break;
			}
		}
	}
	
	private static function import_logicals($ojsys)
	{
		$logicals = $ojsys->load_logicals();
//var_dump($logicals);
		foreach ($logicals as $lgc)
		{
			$lgcal = new OJ_Logicals($lgc);
			$lgcal->save();
		}
	}
	
	private static function import_parameters($ojsys, $clog)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			$params = $cat->load_parameters();
			foreach ($params as $param)
			{
//print ("value in ".$param["name"]." ".$cname);
//var_dump($param['value']);
				if ($param["name"] == "lastaccess")
				{
					$tname = "Date_Time";
					$val = date ("Y-m-d H:i:s", (int) floor($param["value"] / 1000));
				}
				else
				{
					$tname = OJ_Database_Table_information::get_instance()->get_table_equivalent("parameter_type_table", $param['type']);
					$val = $param["value"];
				}
				$valclass = "OJ_".ucfirst($tname)."_Values";
				if (class_exists($valclass))
				{
					$vc = new $valclass(["value" => $val]);
					$valid = $vc->save();
					if ($valid > 0)
					{
						$hash = ["name" => $param["name"], "catalogs_id" => $clog->id, "table_name" => $tname, "values_id" => $valid];
						$pm = new OJ_Parameters($hash);
						$pm->save();
					}
					else
					{
						print ("\nvalue not saved ".$val." from ".$param['value']." in ".$valclass);
					}
				}
				else
				{
					print("\nnot a class ".$valclass);
				}
			}
//var_dump($params);
		}
		else
		{
			print ("\nnot a catalog ".$cname);
		}
	}
	
	private static function import_props($ojsys, $clog, $entityid, $etype, $props)
	{
		if ($props != null)
		{
			foreach ($props as $prop)
			{
				$tname = OJ_Database_Table_information::get_instance()->get_table_equivalent("parameter_type_table", $prop['type']);
				$val = $prop["value"];
				if (is_string($val) && (strlen($val) == 0))
				{
					$val = null;
				}
				if (($tname == 'enumeration') && (strpos($val, '(') == FALSE))
				{
					$tp = $prop['type'];
					$lp = strpos($tp, '(');
					if ($lp !== FALSE)
					{
						$val .= substr($tp, $lp);
					}
				}
				$valclass = "OJ_".ucfirst($tname)."_Values";
				if (class_exists($valclass))
				{
					$vc = new $valclass(["value" => $val]);
					if ($val == null)
					{
						$vcid = 0;
					}
					else
					{
//										print ("\nsaving value ".$val." for class ".$valclass." catalog ".$cname." attribute ".$att["name"]);
//										var_dump($vc);
						$vcid = $vc->save();
//										print ("\nvcid ".$vcid."!");
						if ($vcid <= 0)
						{
							print ("\nvalue not saved ".$val." from ".$att['value']." in ".$valclass);
							var_dump($att);
						}
					}
					if ($entityid == 0)
					{
						$entityid = 0 - (($clog->id * 10) + $etype->id);
					}
//					var_dump($clog, $prop, $etype);
					$hash = ["name" => $prop["name"], "entities_id" => $entityid, "table_name" => $tname, "values_id" => $vcid];
					$pp = new OJ_Properties($hash);
					$pp->save();
				}
				else
				{
					print("\nnot a class ".$valclass);
				}
			}
		}
	}

	private static function import_properties($ojsys, $clog, $oldentity = null, $newentityid = 0)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			if ($oldentity == null)
			{
				$etypes = OJ_Entity_Types::get_all_entity_types();
				foreach ($etypes as $etype)
				{
					$props = $cat->load_default_properties($etype->name);
					self::import_props($ojsys, $clog, $newentityid, $etype, $props);
	//				var_dump($props);continue;
				}
			}
			else
			{
				$props = $oldentity->load_properties();
				$etype = OJ_Entity_Types::get_entity_type($oldentity->get_entity_type());
				self::import_props($ojsys, $clog, $newentityid, $etype, $props);
			}
		}
	}
	
	private static function import_pages($ojsys, $clog, $entityid, $etype, $pages)
	{
		foreach ($pages as $pname => $atts)
		{
			$pg = OJ_Pages::get_page($clog->id, $etype->id, $pname);
			if (($pg == null) || ($pg->id <= 0))
			{
				$vis = $atts["visible"]?1:0;
				$pg = new OJ_Pages(["name" => $pname, "catalogs_id" => $clog->id, "entity_types_id" => $etype->id, "visible" => $vis]);
				$pid = $pg->save();
			}
			else
			{
				$pid = $pg->id;
			}
//			print $pid."\n";
//			var_dump($atts);
			if ($atts["atts"] != null)
			{
				foreach ($atts["atts"] as $attname => $att)
				{
//							$tname = OJ_Database_Table_information::get_instance()->get_table_equivalent("parameter_type_table", $att['type']);
					$otype = array_key_exists('type', $att)?$att['type']:'string';
					$atype = OJ_Attribute_Types::get_attribute_type($otype);
					if ($atype == null)
					{
						print ("no attribute type ".$otype."\n");
					}
					else
					{
						$tname = $atype->table_name;
						$val = array_key_exists("value", $att)?$att["value"]:null;
						if (is_string($val) && (strlen($val) == 0))
						{
							$val = null;
						}
						else if (($tname == 'enumeration') && (strpos($val, '(') == FALSE))
						{
							$tp = $att['type'];
							$lp = strpos($tp, '(');
							if ($lp !== FALSE)
							{
								$val .= substr($tp, $lp);
							}
						}
						$valclass = "OJ_".ucwords($tname, "_")."_Values";
//						print $tname." ".$valclass."  ".$val."\n";
						if (class_exists($valclass))
						{
							$vc = new $valclass(["value" => $val]);
							if ($val == null)
							{
								$vcid = 0;
							}
							else
							{
//										print ("\nsaving value ".$val." for class ".$valclass." catalog ".$cname." attribute ".$att["name"]);
//										var_dump($vc);
								$vcid = $vc->save();
//										print ("\nvcid ".$vcid."!");
								if ($vcid <= 0)
								{
									print ("\nvalue not saved ".$val." from ".$att['value']." in ".$valclass);
									var_dump($att);
								}
							}
//							$attdesc = OJ_Attribute_Descriptions::get_attribute_description($clog->id, $etype->id, $atype->id);
//									var_dump($attdesc);
//									var_dump($att);
							$hash = ["name" => $att["name"], "pages_id" => $pg->id, "attribute_types_id" => $atype->id, "values_id" => $vcid,
								"entities_id" => $entityid, "ordinal" => $att["ordinal"], "visible" => ($att['visible']?1:0)];
							$atr = new OJ_Attributes($hash);
//							var_dump($atr);
							$atr->save();
//									print ("\nvalue saved");
						}
						else
						{
							print("\nnot a class ".$valclass);
						}
					}
				}
			}
		}
	}

	private static function import_attributes($ojsys, $clog, $oldentity = null, $newentityid = 0)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			if ($oldentity == null)
			{
				$etypes = OJ_Entity_Types::get_all_entity_types();
				foreach ($etypes as $etype)
				{
					$pages = $cat->load_default_attributes($etype->name);
					self::import_pages($ojsys, $clog, $newentityid, $etype, $pages);
	//				var_dump($pages);continue;
				}
			}
			else
			{
				$pages = $oldentity->load_attributes();
				$oldetype = strtoupper($oldentity->get_entity_type());
				if (($oldetype == null) || (strlen($oldetype) == 0))
				{
					$oldetype = "ITEM";
				}
//				var_dump($pages);
				$etype = OJ_Entity_Types::get_entity_type($oldetype);
				self::import_pages($ojsys, $clog, $newentityid, $etype, $pages);
			}
		}
	}
	
	private static function import_entity($ojsys, $clog, $oldentityid)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			$oldentity = $cat->get_entity($oldentityid);
			if ($oldentity != null)
			{
				$newentityid = OJ_New_Old_Ids::get_new_from_old($clog->id, $oldentityid);
//				print "new entity ".$newentityid;
				if ($newentityid === null)
				{
					$etype = OJ_Entity_Types::get_entity_type($oldentity->get_entity_type());
					$newentity = new OJ_Entities(["name" => $oldentity->get_name(), "catalogs_id" => $clog->id, "entity_types_id" => $etype->id]);
					$newentityid = $newentity->save();
//					print "saved new entity ".$newentityid;
					self::import_attributes($ojsys, $clog, $oldentity, $newentityid);
					self::import_properties($ojsys, $clog, $oldentity, $newentityid);
					OJ_New_Old_Ids::save_ids($clog->id, $newentityid, $oldentityid);
				}
				else
				{
					print "entity already exists ".$oldentity->get_name()." as ".$newentityid."\n";
				}
			}
			else
			{
				print "no entity with id ".$oldentityid."\n";
			}
		}
	}
	
	private static function import_links_for_entity($ojsys, $clog, $oldentityid)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			$newentityid = OJ_New_Old_Ids::get_new_from_old($clog->id, $oldentityid);
			if ($newentityid === null)
			{
				print "no new entity for ".$oldentityid."\n";
			}
			else
			{
				$lnks = OJ_Entity::load_links($cat, $oldentityid);
				foreach ($lnks as $lnk)
				{
					$newto = OJ_New_Old_Ids::get_new_from_old($clog->id, $lnk["to"]);
					if ($newto === null)
					{
						print "no new destination for ".$lnk["to"]."\n";
					}
					else
					{
						$etype = OJ_Entity_Types::get_entity_type($lnk['type']);
						$hdn = $lnk["hidden"] === "true"?1:0;
						$hash = ["name"=>$lnk['name'], "from_catalogs_id"=>$clog->id, "from_entities_id"=>$newentityid, "to_catalogs_id"=>$clog->id,
							"to_entities_id"=>$newto, "entity_types_id"=>$etype->id, "ordinal"=>$lnk["ordinal"], "hidden"=>$hdn];
						$link = new OJ_Links($hash);
						$link->save();
					}
				}
			}
		}
	}
	
	public static function import_references_for_entity($ojsys, $clog, $oldentityid)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		if ($cat !== null)
		{
			$newentityid = OJ_New_Old_Ids::get_new_from_old($clog->id, $oldentityid);
			if ($newentityid === null)
			{
				print "no new entity for ".$oldentityid."\n";
			}
			else
			{
				$lnks = OJ_Entity::load_references($cat, $oldentityid);
				var_dump($lnks); return;
				foreach ($lnks as $lnk)
				{
					$cname = $lnk['catalog'];
					$ncname = OJ_Catalogs::get_new_catalog_name($cname);
					$clog1 = OJ_Catalogs::get_catalog($ncname);
//					var_dump($clog1);
					if ($clog1 == null)
					{
						print "no catalog ".$cname." ".$ncname."\n";
					}
					else
					{
						$newto = OJ_New_Old_Ids::get_new_from_old($clog1->id, $lnk["to"]);
						if ($newto === null)
						{
							print "no new destination reference for ".$lnk["to"]."\n";
						}
						else
						{
							$etype = OJ_Entity_Types::get_entity_type($lnk['type']);
							$hdn = $lnk["hidden"] === "true"?1:0;
							$hash = ["name"=>$lnk['name'], "from_catalogs_id"=>$clog->id, "from_entities_id"=>$newentityid, "to_catalogs_id"=>$clog1->id,
								"to_entities_id"=>$newto, "entity_types_id"=>$etype->id, "ordinal"=>$lnk["ordinal"], "hidden"=>$hdn];
							$link = new OJ_Links($hash);
							$link->save();
						}
					}
				}
			}
		}
	}
	
	private static function import_catalogs($ojsys, $clogs, $restart, $omit)
	{
		$omitcats = $restart?explode(',', $omit):[];
		foreach ($clogs as $clog)
		{
			if (!in_array(strtolower($clog->get_name()), $omitcats))
			{
				if (!$restart)
				{
					print "importing parameters for ".$clog->get_name()."\n";
					self::import_parameters($ojsys, $clog);
					print "importing default properties for ".$clog->get_name()."\n";
					self::import_properties($ojsys, $clog);
					print "importing default attributes for ".$clog->get_name()."\n";
					self::import_attributes($ojsys, $clog);
				}
				$cname = $clog->get_olddb_name();
				$cat = $ojsys->get_catalog($cname);
				$iter = new OJ_Iterator($cat, 0);
				foreach ($iter as $k=>$v)
				{
					print "importing entity for ".$k." in ".$clog->get_name()."\n";
					self::import_entity($ojsys, $clog, $k);
				}
			}
		}
		foreach ($clogs as $clog)
		{
			self::import_links($ojsys, $clog);
		}
	}
	
	private static function import_links($ojsys, $clog)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		$iter = new OJ_Iterator($cat, 0);
		foreach ($iter as $k=>$v)
		{
			print "importing links for ".$k." in ".$clog->get_name()."\n";
			self::import_links_for_entity($ojsys, $clog, $k);
			print "importing references for ".$k." in ".$clog->get_name()."\n";
			self::import_references_for_entity($ojsys, $clog, $k);
		}
	}
	
	private static function import_indexes($ojsys, $clog)
	{
		$cname = $clog->get_olddb_name();
		$cat = $ojsys->get_catalog($cname);
		$idxs = $cat->load_indices();
		foreach ($idxs as $idx)
		{
			$hash = ["name"=>$idx["name"], "catalogs_id"=>$clog->id, "entities_id"=>OJ_New_Old_Ids::get_new_from_old($clog->id,
					$idx["startat"])];
			if (($idx["omit"] != null) && (strlen($idx["omit"]) > 0))
			{
				$omit = explode(',', $idx["omit"]);
				$omit1 = [];
//				var_dump($idx);
//				var_dump($omit);
				foreach ($omit as $o)
				{
					$omit1[] = OJ_New_Old_Ids::get_new_from_old($clog->id, $o);
				}
//				var_dump($omit1);
				$hash["omit"] = implode(',', $omit1);
			}
			$index = new OJ_Indexes($hash);
			$index->save();
		}
	}
	
	private static function import_folders($ojsys, $clog)
	{
		$albumnames = OJ_Row::load_column("entities", "name", ["catalogs_id"=>1, "entity_types_id"=>2]);
		var_dump($albumnames);
		for ($n = 1; $n <= 28; $n++)
		{
			$logical = OJ_Logicals::get_logical("music".$n);
			$root = $logical->value;
//			print $root."\n";
			$rootinfo = OJ_File_Utilities::dir_info($root);
//			var_dump($rootinfo);exit;
			foreach ($rootinfo["subdirs"] as $folder)
			{
				$fname = basename($folder);
				$ai = OJ_File_Utilities::already_imported($folder, OJ_File_Utilities::$audio_extensions);
				if (!$ai)
				{
					print $folder."\n".$ai."\n";
				}
			}
			var_dump($logical);
		}
	}
}

