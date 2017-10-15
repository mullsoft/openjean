<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");
if (array_key_exists('action', $_GET))
{
    $act = $_GET['action'];
    if (array_key_exists('post', $_GET))
    {
        $params = $_POST;
    }
    else
    {
        $params = $_GET;
    }
}
else
{
    $act = $_POST['action'];
    $params = $_POST;
}
//			var_dump($params);exit;
$ojmode = isset($params['ojmode']) ? $params['ojmode'] : 'db';
//var_dump($_GET);
//echo "mode ".$ojmode;exit;
OJ_Utilities::decode_array($params);
$testingst = isset($params['test']) ? $params['test'] : 'false';
$testing = $testingst === 'true';
OJ_Row::$_testing = $testing;
$catalogname = isset($params['catalog']) ? $params["catalog"] : "";
$username = $params["user"];
$users_id = OJ_Users::get_users_id($username);
$indexname = isset($params["index"])?$params["index"]:"default";
$ojhost = isset($params["ojhost"])?$params["ojhost"]:"1";
if (($catalogname == 'multimedia') && ($indexname == "artists"))
{
	$catalogname = "artists";
	$indexname = "audio";
}
if (($catalogname == 'artists') && ($act == "audio"))
{
	$catalogname = "multimedia";
	$indexname = "audio";
}
$catalogs_id = OJ_Catalogs::get_catalog_id($catalogname);
if (!$catalogs_id)
{
	echo "INVALID CATALOG: ".$catalogname."\n";
	exit;
}
$indexes_id = OJ_Indexes::get_index_id($catalogs_id, $indexname);
if (!$indexes_id)
{
	echo "INVALID INDEX: ".$catalogname."\n";
	exit;
}
require_once('ojHTML.php');
$editingst = isset($params['edit']) ? $params['edit'] : 'false';
$ojediting = $editingst === 'true';
OJ_Utilities::set_system_parameter("editing", $editingst === 'true');


$cd = OJ_Catalog_Display::get_catalog_display($username, $catalogname, $indexname);
//$catalog = OJ_System::instance($username)->get_catalog($catalogname);
if ($act)
{
	$mthd = "oj_action_".$act;
	$cd->$mthd($params);
	exit;
/*    switch ($act)
    {
		case "stamp":
			if ($ojhost == 0)
			{
				$ojhost = OJ_Access::next_host();
			}
			OJ_Access::stamp($username, $catalogs_id, $ojhost, $indexes_id);
			echo $ojhost;
			break;
		case "defatt":
			$gnamel = $cd->get_group_name();
			$inamel = $cd->get_item_name();
			$cnamel = $cd->get_category_name();
			$gname = $gnamel."+".ucfirst($gnamel)."+".($cd->can_create_groups($indexname)?"true":"false");
			$iname = $inamel."+".ucfirst($inamel)."+".($cd->can_create_items($indexname)?"true":"false");
			$cname = $cnamel."+".ucfirst($cnamel)."+".($cd->can_create_categories($indexname)?"true":"false");
			$logical_category = OJ_Indexes::get_logical_categories_id($catalogs_id, $indexname);
			$prefix = $logical_category == 0?null:OJ_Logical_Categories::get_logical_categories_prefix($logical_category);
			$retarray = [OJ_Entities::get_default_attributes($catalogs_id, "CATEGORY"),
				OJ_Entities::get_default_attributes($catalogs_id, "GROUP"),
				OJ_Entities::get_default_attributes($catalogs_id, "ITEM"),
				$cd->get_search_excludes($username, $catalogname),
				OJ_HTML_Display::get_entity_select_list1($username, $catalogname, $indexname),
				OJ_HTML_Display::get_subtype_values($catalogname, "CATEGORY"),
				OJ_HTML_Display::get_subtype_values($catalogname, "GROUP"),
				$iname."|".$gname."|".$cname,
				OJ_HTML_Display::get_index_list($catalogname, $username),
				OJ_HTML_Display::get_entity_menu_items($username, $catalogname, $indexname),
				implode('|', OJ_Access::get_catalog_list($username)),
				OJ_HTML_Display::get_sorted_logicals($prefix),
				$cd->get_filter_column_cssclass().'|'.$cd->get_display_column_cssclass(),
				OJ_Roles::get_rolename_for_user($username, $catalogname)
				];
			if (isset($params['other']))
			{
				$other_index = "default";
				$other_catalog = $params['other'];
				$u2 = strpos($other_catalog, "__");
				if ($u2 > 0)
				{
					$other_index = substr($other_catalog, $u2 + 2);
					$other_catalog = substr($other_catalog, 0, $u2);
				}
				array_push($retarray, OJ_HTML_Display::get_entity_select_list1($username, $other_catalog, $other_index));
			}
			echo json_encode($retarray);
			break;
        case "open":
			$to = $params["ojid"];
			$name = $params["name"];
			$type = $params["type"];
//			$parent = OJ_Link::create_link_entity($catalog, $to, $name, $type);
//			$list = OJ_HTML_Display::get_subentity_select_list($parent, $type != "CATEGORY");
//			echo OJ_HTML::to_html($list);
			$catalog_list = OJ_HTML_Display::get_subentity_select_list($username, $catalogname, $indexname, $to, $name, $type, "oj-multimedia-list-".$to, "oj-catalog-list-class", true);
//			var_dump($catalog_list);exit;
//					get_entity_select_list($user, $catalogname, $index, "oj-multimedia-list-".$to, "oj-catalog-list-class", $type != "CATEGORY");
			echo OJ_HTML::to_html($catalog_list);
//			var_dump($params);
			break;
        case "show":
			$to = $params["ojid"];
			$name = $params["name"];
			$type = $params["type"];
			$selectable = !isset($params["selectable"]) || ($params["selectable"] == 'true');
			$followrefs = !isset($params["followrefs"]) || ($params["followrefs"] == 'true');
			echo OJ_HTML_Display::get_subentity_select_list1($username, $catalogname, $indexname, $to, $name, $type, $selectable, $followrefs);
			break;
		case "image":
			$ojid = $params["ojid"];
			$imgatt = isset($params["img"])?$params["img"]:null;
			OJ_Logger::get_logger()->ojdebug1("calling get_entity ".$ojid);
//			$entity = $catalog->get_entity($ojid);
			OJ_Logger::get_logger()->ojdebug1("calling get_attribute ".$imgatt);
//			$imgfile_logical = $entity->get_attribute($imgatt);
			$imgfile_logical = OJ_Entities::get_attribute_value($ojid, $imgatt);
			OJ_Logger::get_logger()->ojdebug1("imgfile_logical ".$imgfile_logical);
			$imgfile1 = OJ_System::instance($username)->substitute_logical($imgfile_logical);
			$imgfile = $imgfile1["value"];
			OJ_Logger::get_logger()->ojdebug1("imgfile ".$imgfile);
			if (file_exists($imgfile))
			{
				$lastdot = strrpos($imgfile, '.');
				$ext = strtolower(substr($imgfile, $lastdot + 1));
				OJ_Logger::get_logger()->ojdebug1("ext ".$ext);
				// image/gif, image/png, image/jpeg, image/bmp, image/webp
				switch ($ext)
				{
					case "gif":
						$mimetype = "image/gif";
						break;
					case "jpg":
					case "jpeg":
						$mimetype = "image/jpeg";
						break;
					case "bmp":
						$mimetype = "image/bmp";
						break;
					case "webp":
						$mimetype = "image/webp";
						break;
					case "png":
					default:
						$mimetype = "image/png";
						break;
				}
				
				header("Content-Type: ".$mimetype);
//				header("Content-Disposition: attachment; filename=\"myfile.zip\"");
				header("Content-Length: ". filesize($imgfile));
				header("Cache-Control: max-age=2592000");
				readfile($imgfile);
				exit();
			}
			else
			{
				OJ_Logger::get_logger()->ojdebug1("no image file found ".$imgfile);
			}
			break;
		case "audiodownload":
//			$ojids = explode(',', $params["ojid"]);
//			echo $catalog->get_entity_dir($ojid)."<br/>";
			$format = array_key_exists("format", $params)?$params["format"]:"flac";
			$plname = $params["name"];
			if ($format === "mpeg")
			{
				$format = "mp3";
			}
			$bits = array_key_exists("bits", $params)?intval($params["bits"]):16;
			$rate = array_key_exists("rate", $params)?intval($params["rate"]):48;
			$qual = array_key_exists("qual", $params)?intval($params["qual"]):320;
			$list = json_decode($params["list"]);
			OJ_Logger::get_logger()->ojdebug1($list);
			$name = $params["name"];
			$ojnames = [];
			foreach ($list as $item)
			{
				$ojnames[] = new OJ_String($item->name);
			}
			OJ_Logger::get_logger()->ojdebug1($ojnames);
			OJ_String::renumber_array($ojnames);
			OJ_Logger::get_logger()->ojdebug1($ojnames);
			for ($n = 0; $n < count($list); $n++)
			{
				$list[$n]->name = $ojnames[$n]->as_string();
			}
			OJ_Logger::get_logger()->ojdebug1($list);
			$dir = "/tmp/".$plname;
			if (!file_exists($dir) || !is_dir($dir))
			{
				mkdir($dir);
			}
			$dirname = "/tmp/".$plname."/";
			$zipname = "/tmp/".$plname.".zip";
			foreach ($list as $item)
			{
				OJ_Logger::get_logger()->ojdebug1("download: calling get_entity ".$item->id);
//				$entity = $catalog->get_entity($item->id);
				OJ_Logger::get_logger()->ojdebug1("download: calling get_attribute ".$item->id);
//				$track1 = $entity->get_attribute ("detail", "track");
				$track1 = OJ_Entities::get_attribute_value($item->id, "detail", "track");
				OJ_Logger::get_logger()->ojdebug1("download: playing ".$item->id);
				OJ_Logger::get_logger()->ojdebug1("download: 1.looking for track ".json_encode($track1));
	//			echo $track."<br/>";
				if ($track1 != null)
				{
	//				$track1 = OJ_System::instance($username)->substitute_logical($track);
	//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
					$track = stripslashes($track1->get_value());
					$lastdot = strrpos($track, ".");
					$bname = basename($track, substr($track, $lastdot));
					OJ_Logger::get_logger()->ojdebug1("download: looking for track ".$track);
//					if (!file_exists($track) && ($track1["ftp"] != null))
//					{
//						OJ_Logger::get_logger()->ojdebug1("download: looking for track on ftp");
//						if ($lastdot > 0)
//						{
//							$origformat = substr($track, $lastdot + 1);
//							$tmpfile1 = "/tmp/".$item->id.".".$origformat;
//							$track1["ftp"]->get($track, $tmpfile1);
//							if (file_exists($tmpfile1))
//							{
//								$track = $tmpfile1;
//							}
//						}
//					}
					$tmpfile = $dirname.$bname.".".$format;
					$err = 0;
					if (!file_exists($tmpfile))
					{
						if ($format == "mp3")
						{
							OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "'.$track.'" -C '.$qual.' "'.$tmpfile.'"');
							exec('/usr/bin/sox "'.$track.'" -C '.$qual.' "'.$tmpfile.'"', $err);
						}
						else
						{
							OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "'.$track.'" -b '.$bits.' -r '.$rate.'k "'.$tmpfile.'"');
							exec('/usr/bin/sox "'.$track.'" -b '.$bits.' -r '.$rate.'k "'.$tmpfile.'"', $err);
						}
					}
					OJ_Logger::get_logger()->ojdebug1($item->id." ok");
				}
				else
				{
					OJ_Logger::get_logger()->ojdebug1($item->id." not ok");
				}
			}
			OJ_File_Utilities::zip_dir($dir, $zipname);
			echo "file=".$zipname;
			break;
		case "downloadfile":
			$filename = $params["file"];
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.basename($filename));
			header('Content-Length: ' . filesize($filename));
			readfile($filename);
			break;
		case "audiopreload":
			$ojids = explode(',', $params["ojid"]);
//			echo $catalog->get_entity_dir($ojid)."<br/>";
			$format = array_key_exists("format", $params)?$params["format"]:"wav";
			if ($format === "mpeg")
			{
				$format = "mp3";
			}
			$bits = array_key_exists("bits", $params)?intval($params["bits"]):($format == 'mp3'?16:24);
			foreach ($ojids as $ojid)
			{
				OJ_Logger::get_logger()->ojdebug1("preload: calling get_entity ".$ojid);
//				$entity = $catalog->get_entity($ojid);
				OJ_Logger::get_logger()->ojdebug1("preload: calling get_attribute ".$ojid);
//				$track1 = $entity->get_attribute ("detail", "track");
				$track1 = OJ_Entities::get_attribute_value($ojid, "detail", "track");
				OJ_Logger::get_logger()->ojdebug1("preload: playing ".$ojid);
				OJ_Logger::get_logger()->ojdebug1("preload: 1.looking for track ".json_encode($track1));
	//			echo $track."<br/>";
				if ($track1 != null)
				{
	//				$track1 = OJ_System::instance($username)->substitute_logical($track);
	//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
//					$track = stripslashes($track1->get_value());
					$track = stripslashes($track1);
					OJ_Logger::get_logger()->ojdebug1("preload: looking for track ".$track);
//					if (!file_exists($track) && ($track1["ftp"] != null))
//					{
//						OJ_Logger::get_logger()->ojdebug1("preload: looking for track on ftp");
//						$lastdot = strrpos($track, ".");
//						if ($lastdot > 0)
//						{
//							$origformat = substr($track, $lastdot + 1);
//							$tmpfile1 = "/tmp/".$ojid.".".$origformat;
//							$track1["ftp"]->get($track, $tmpfile1);
//							if (file_exists($tmpfile1))
//							{
//								$track = $tmpfile1;
//							}
//						}
//					}
					$tmpfile = "/tmp/".$ojid.".".$format;
					$err = 0;
					if (!file_exists($tmpfile))
					{
						OJ_Session::set($users_id, "preload", $ojid);
						OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "'.$track.'" -b '.$bits.' '.$tmpfile);
						exec('/usr/bin/sox "'.$track.'" -b '.$bits.' '.$tmpfile, $err);
					}
					echo $ojid." ok";
				}
				else
				{
					echo $ojid." not ok";
				}
			}
			OJ_Session::reset($users_id, "preload");
//			echo $track;
			break;
		case "audio":
			$ojid = $params["ojid"];
//			echo $catalog->get_entity_dir($ojid)."<br/>";
			$format = array_key_exists("format", $params)?$params["format"]:"wav";
			switch ($format)
			{
				default:
				case "wav":
					$mimetype = "audio/wav";
					break;
				case "mpeg":
					$format = "mp3";
				case "mp3":
					$mimetype = "audio/mpeg";
					break;
				case "flac":
					$mimetype = "audio/flac";
					break;
			}
			$bits = array_key_exists("bits", $params)?intval($params["bits"]):($format == 'mp3'?16:24);
			if (array_key_exists("track", $params))
			{
				$track = $params["track"];
				$useftp = false;
			}
			else
			{
				OJ_Logger::get_logger()->ojdebug1("calling get_entity ".$ojid);
//				$entity = $catalog->get_entity($ojid);
				OJ_Logger::get_logger()->ojdebug1("calling get_attribute ".$ojid);
//				$track1 = $entity->get_attribute ("detail", "track");
				$track1 = OJ_Entities::get_attribute_value($ojid, "detail", "track");
				OJ_Logger::get_logger()->ojdebug1("playing ".$ojid);
				OJ_Logger::get_logger()->ojdebug1("1.looking for track ".json_encode($track1));
//				$track = $track1 == null?null:stripslashes($track1->get_value());
//				$useftp = !file_exists($track) && ($track1["ftp"] != null);
				$track = $track1 == null?null:stripslashes($track1);
				$useftp = false;
			}
//			echo $track."<br/>";
			if ($track != null)
			{
				$pre = OJ_Session::get($users_id, "preload");
				while ($pre && $pre == $ojid)
				{
					OJ_Logger::get_logger()->ojdebug1("waiting for preload ".$ojid);
					sleep(1);
					$pre = OJ_Session::get($users_id, "preload");
				}
//				$track1 = OJ_System::instance($username)->substitute_logical($track);
//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
				$tmpfile = "/tmp/".$ojid.".".$format;
				if (!file_exists($tmpfile))
				{
//					OJ_Logger::get_logger()->ojdebug1("looking for track ".$track);
//					if ($useftp)
//					{
//						OJ_Logger::get_logger()->ojdebug1("looking for track on ftp");
//						$lastdot = strrpos($track, ".");
//						if ($lastdot > 0)
//						{
//							$origformat = substr($track, $lastdot + 1);
//							$tmpfile1 = "/tmp/".$ojid.".".$origformat;
//							$track1["ftp"]->get($track, $tmpfile1);
//							if (file_exists($tmpfile1))
//							{
//								$track = $tmpfile1;
//							}
//						}
//					}
					$err = 0;
					OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "'.$track.'" -b '.$bits.' '.$tmpfile);
					exec('/usr/bin/sox "'.$track.'" -b '.$bits.' '.$tmpfile,$err);
				}
				header("Content-Type: ".$mimetype);
//				header("Content-Disposition: attachment; filename=\"myfile.zip\"");
				header("Content-Length: ". filesize($tmpfile));
				header("Cache-Control: max-age=2592000");
				readfile($tmpfile);
//				passthru('/usr/bin/sox "'.$track.'" -t wav -',$err);
//				exec('/usr/bin/sox "'.$track.'" /tmp/'.$ojid.".wav",$err);
				exit();
			}
//			echo $track;
			break;
		case "link":
			//<link direction="from" catalog="1" ordinal="0" hidden="0" other="6311">test1</link>
			$sourceid = $params["source"];
			$destid = $params["destination"];
			$name = isset($params["name"])?$params["name"]:OJ_Entities::get_entity_name($destid);
//			$catalogs_id = $catalog->get_id();
			$ord = isset($params["ordinal"])?$params["ordinal"]:0;
			$xml = '<link direction="from" catalog="'.$catalogs_id.'" ordinal="'.$ord.'" hidden="0" other="'.$destid.'">'.$name.'</link>';
			echo OJ_Links::from_xml($catalogs_id, $sourceid, $xml);
			break;
		case "move":
			$ret = "no";
			$fromid = $params["from"];
			$toid = $params["to"];
			$ojid = $params["ojid"];
//			$catalogs_id = $catalog->get_id();
			$lnk = OJ_Row::load_single_object("OJ_Links", ["from_entities_id"=>$fromid, "to_entities_id"=>$ojid, "catalogs_id"=>$catalogs_id]);
			if ($lnk && ($lnk->id > 0))
			{
				$lnk->from_entities_id = $toid;
				$lnk->save();
				$ret = "yes";
			}
			echo $ret;
			break;
		case "unlink":
			$fromid = $params["from"];
			$ojid = $params["ojid"];
//			$catalogs_id = $catalog->get_id();
			$lnksto = OJ_Links::get_all_links_to($catalogs_id, $ojid);
			if (count($lnksto === 1))
			{
				if ($lnksto[0].from_entities_id == $fromid)
				{
					OJ_Entities::tree_delete($lnk->to_entities_id, $catalogs_id);
				}
			}
			else
			{
				foreach ($lnksto as $lnk)
				{
					if ($lnk.from_entities_id == $fromid)
					{
						$lnk->delete();
						break;
					}
				}
			}
			break;
		case "create":
//			var_dump($_SERVER);exit;
			$eid = 0;
			if (isset($params["xml"]))
			{
				$xml = $params["xml"];
//				echo $xml;$eid = 0;
				$ent = OJ_Entities::from_xml_string($params["xml"]);
				$eid = $ent == null?0:$ent->id;
				$type = $ent == null?null:OJ_Entity_Types::get_entity_type_name($ent->entity_types_id);
			}
//			elseif (isset($params["ojid"]))
//			{
//				$parents = explode(",", $params["ojid"]);
//				$type = $params['type'];
//				$name = $params['name'];
//				$attributes = null;
//				if (isset($params['attributes']))
//				{
//					$attributes = json_decode($params['attributes']);
//				}
//				$properties = null;
//				if (isset($params['properties']))
//				{
//					$properties = json_decode($params['properties']);
//				}
//				$eid = $catalog->create_entity($name, $type, $parents, $linkname, $attributes, $properties);
//			}
			if ($eid > 0)
			{
				$cd->on_entity_create($type, $eid);
			}
			echo $eid;
			break;
		case "attributes":
			$ojid = $params["ojid"];
			$parent = $params["parent"];
			OJ_Entities::note_accessed($ojid);
			$editable = isset($params["editable"]) && ($params["editable"] === "true");
			echo OJ_HTML_Display::get_display_panel_contents($username, $catalogname, $indexname, $ojid, $editable);
			$cd->on_attribute_display($ojid, $parent);
			break;
		case "unimported":
			$unimported = OJ_Audio_Utilities::get_unimported_folders($params['logical'], $catalogs_id);
			$fdiv = [];
			foreach ($unimported as $folder)
			{
				$text = [new OJ_INPUT("radio", "oj-folder", null, "oj-folder-radio", $folder), new OJ_SPAN(basename($folder), null, "oj-folder-span oj-label-span")];
				$label = new OJ_LABEL(null, $text, null, "oj-folder-label oj-label");
				$fdiv[] = new OJ_DIV($label,null,"oj-folder-div oj-div");
			}
			echo OJ_HTML::to_html($fdiv);
			break;
		case "getartist":
			$aname = $params["artist"];
			echo json_encode(OJ_Audio_Utilities::find_artists($aname));
			break;
		case "import":
//			echo json_encode($params);
			$xml = OJ_Audio_Utilities::folderpath_to_xml_entity_string($params['folder'], [$params['category']], explode(',', $params['artists']));
			$entid = 0;
			$ent = OJ_Entities::from_xml_string($xml);
			if ($ent)
			{
				$logbits = explode('|', $params["logical"]);
				$fldr = new OJ_Imported_Folders(["name"=>  strtolower(basename($params["folder"])), "entities_id"=>$ent->id,
					"catalogs_id"=>OJ_Catalogs::get_catalog_id("multimedia"), "logicals_name"=>$logbits[0]]);
				$fldr->save();
				$entid = $ent->id;
			}
			echo $entid;
			break;
		case "aka":
			$ret = 0;
			if (isset($params["name"]) && isset($params["ojid"]))
			{
				$table_name = isset($params["tablename"])?$params["tablename"]:"entities";
				$ret = OJ_Aka::add_aka($params["name"], $params["ojid"], $table_name);
			}
			echo $ret;
			break;
		case "playlist":
			$entid = 0;
			$list = json_decode($params["list"]);
			OJ_Logger::get_logger()->ojdebug1($list);
			$name = $params["name"];
			$ojnames = [];
			foreach ($list as $item)
			{
				$ojnames[] = new OJ_String($item->name);
			}
			OJ_Logger::get_logger()->ojdebug1($ojnames);
			OJ_String::renumber_array($ojnames);
			OJ_Logger::get_logger()->ojdebug1($ojnames);
			for ($n = 0; $n < count($list); $n++)
			{
				$list[$n]->name = $ojnames[$n]->as_string();
			}
			OJ_Logger::get_logger()->ojdebug1($list);
			$plid = OJ_Audio_Utilities::get_playlist_id($username);
			$catalogs_id = OJ_Audio_Utilities::get_multimedia_catalogs_id();
			$entid = OJ_Links::find_by_name($name, $plid, $catalogs_id);
			if ($entid == 0)
			{
				$xml = OJ_Audio_Utilities::playlist_to_xml_entity_string($username, $name, $list);
				$ent = OJ_Entities::from_xml_string($xml);
				if ($ent)
				{
					$entid = $ent->id;
				}
			}
			else
			{
				$xml = OJ_Audio_Utilities::playlist_to_xml_links_string($username, $name, $list);
				$lnks = OJ_Links::from_xml($catalogs_id, $entid, $xml);
			}
			echo "id=".$entid;
			break;
		case "selectplaylist":
			$selagrp = OJ_HTML_Display::get_group_select_list($username, "multimedia", "Playlists--".$username, "select-playlist-");
			echo OJ_HTML::to_html($selagrp);
			break;
		case "getplaylist":
			$ojid = $params["ojid"];
			$plid = OJ_Audio_Utilities::get_playlist_id($username);
			$mmcatid = OJ_Audio_Utilities::get_multimedia_catalogs_id();
			$rootid = OJ_Catalogs::get_root_id($mmcatid);
			$links = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id"=>$ojid, "from_catalogs_id"=>$mmcatid, "to_catalogs_id"=>$mmcatid]);
			OJ_Logger::get_logger()->ojdebug("links", $links);
			$items = [];
			foreach ($links["result"] as $lnk)
			{
				$paths = OJ_Links::get_paths_to($mmcatid, $lnk->to_entities_id, $rootid, [$plid]);
				OJ_Logger::get_logger()->ojdebug("paths", $paths);
				$grp ="unknown";
				$grpid = 0;
				if ($paths && (count($paths["paths"]) > 0))
				{
					$pth = $paths["paths"][0];
					$grpid = $pth[count($pth) - 2];
					$grp = OJ_Entities::get_entity_name($grpid);
				}
				$items[] = $lnk->to_entities_id."|".$lnk->name."|".$grpid."|".$grp;
			}
			echo implode('^', $items);
			break;
		case "addtoplaylist":
			$ojid = $params["ojid"];
			$plid = $params["plid"];
			$mmcatid = OJ_Audio_Utilities::get_multimedia_catalogs_id();
			$item_type = OJ_Entity_Types::get_entity_type_id("ITEM");
			$current_list_links = OJ_Links::get_all_links_from($mmcatid, $plid, $item_type);
//			var_dump($current_list_links);
			$nlinks = count($current_list_links);
			$name = OJ_String::renumber_string(OJ_Entities::get_entity_name($ojid), $nlinks + 1);
			$rootid = OJ_Catalogs::get_root_id($mmcatid);
			$paths = OJ_Links::get_paths_to($mmcatid, $ojid, $rootid, [$plid]);
//			OJ_Logger::get_logger()->ojdebug("paths", $paths);
			$grp ="unknown";
			$grpid = 0;
			$tt = "";
			if ($paths && (count($paths["paths"]) > 0))
			{
				$pth = $paths["paths"][0];
				$grpid = $pth[count($pth) - 2];
				$grp = OJ_Entities::get_entity_name($grpid);
				$tt = ' tooltip="'.$grpid.'::'.htmlentities($grp, ENT_XML1).'"';
			}
			$lnkstr = '<link direction="from" catalog="'.$mmcatid.'" ordinal="'.$nlinks.'" hidden="0" other="'.$ojid.'"'.$tt.'>'.htmlentities($name, ENT_XML1).'</link>';
			$lnk = OJ_Links::from_xml($mmcatid, $plid, $lnkstr);
			echo "link id ".$lnk->id;
			break;
		case "playlistdiv":
			readfile("oj_playlist.html");
			readfile("oj_select_artist1.html");
			$selacat = OJ_HTML_Display::get_category_select_list($username, "artists", "audio", "new-artist-");
			echo OJ_HTML::to_html($selacat);
			readfile("oj_select_artist2.html");
			break;
		case "imageurl":
			$ret = "";
//			OJ_Logger::get_logger()->ojdebug1("imageurl");
			if (isset($params["ojid"]))
			{
//				$ent = $catalog->get_entity($params["ojid"]);
				$ojid = $params["ojid"];
				$atts = OJ_Entities::get_attributes($ojid, $catalogs_id);
//				$entitytypeid = OJ_Entities::get_entity_types_id($ojid);
//				OJ_Logger::get_logger()->ojdebug("entity ".$params["ojid"], $ent);
				if ($atts && isset($atts["images"]))
				{
//					$pg = $ent->get_page("images");
//					$pg = OJ_Pages::get_page($catalogs_id, $entitytypeid, "images");
//					OJ_Logger::get_logger()->ojdebug("page", $pg);
//					if ($pg)
//					{
//					$imgs = $pg->get_as_hash();
					$imgs = $atts["images"];
					$imgarr = array_keys($imgs);
					if (count($imgarr) > 0)
					{
						$olduri = OJ_HTML::split_uri($_SERVER["REQUEST_URI"]);
						$ret = "http://".$_SERVER["SERVER_NAME"].$olduri['path'].'?';
						$ret .= "user=".$username."&catalog=".$catalogname."&action=image&ojid=".$params["ojid"]."&img=".urlencode("images::".$imgarr[0]);
//							OJ_Logger::get_logger()->ojdebug1("image ".$ret);
					}
//					}
				}
			}
			echo $ret;
			break;
		case "search":
			$crit = $params["search"];
			$found = OJ_Row::load_column("entities", "id", ["catalogs_id"=>$catalogs_id, "name%~%"=>$crit]);
			OJ_Logger::get_logger()->ojdebug1("search found ".count($found));
//			$root = $catalog->get_root($indexname);
			$root = OJ_Catalogs::get_root_id($catalogs_id, $indexname);
			$exclude = $cd->get_search_excludes($username, $catalogname);
//			var_dump($found);
			$paths = ["paths"=>[], "entities"=>[]];
			foreach ($found as $ojid)
			{
				$paths1 = OJ_Links::get_paths_to($catalogs_id, $ojid, $root, $exclude);
				foreach ($paths1["paths"] as $p)
				{
					array_push($paths["paths"], $p);
				}
				foreach ($paths1["entities"] as $id => $v)
				{
					$paths["entities"][$id] = 1;
				}
			}
			OJ_Logger::get_logger()->ojdebug1("search got paths");
			echo json_encode($paths["entities"]);
			break;
		case "favourite":
			$ojid = $params["ojid"];
			$fav = OJ_Audio_Utilities::get_favorites_id($username);
			$xml = '<link direction="from" catalog="'.$catalogs_id.'" ordinal="0" hidden="0" other="'.$ojid.'">'.htmlentities($params["name"], ENT_XML1).'</link>';
			$lnk = OJ_Links::from_xml($catalogs_id, $fav, $xml);
			echo $lnk == null?0:$lnk->id;
			break;
		case "categories":
			$prefix = isset($params["prefix"])?$params["prefix"]:"";
			$include_root = isset($params["root"]) && strtolower($params["root"]) === 'true';
			$selcat = OJ_HTML_Display::get_category_select_list($username, strtolower($catalogname), $indexname, $prefix, $include_root);
			echo OJ_HTML::to_html($selcat);
			break;
		case "groups":
			$prefix = isset($params["prefix"])?$params["prefix"]:"";
			$include_root = isset($params["root"]) && strtolower($params["root"]) === 'true';
//			$selgrp = OJ_HTML_Display::get_group_select_list($username, strtolower($catalogname), $indexname, $prefix, $include_root);
			$selgrp = OJ_HTML_Display::get_group_select_list1($username, $catalogname, $indexname);
//			echo OJ_HTML::to_html($selgrp);
			echo $selgrp;
			break;
		case "rename":
			$ojid = $params["ojid"];
			$parentid = $params["parent"];
			$newname = $params["name"];
			$rename_link = array_key_exists("link", $params) && ($params["link"] === "true");
			$rename_entity = array_key_exists("entity", $params) && ($params["entity"] === "true");
			$ret = [];
			if ($rename_entity)
			{
				$entity = new OJ_Entities($ojid);
				if ($entity && $entity->id == $ojid)
				{
					$entity->name = $newname;
					$entity->save();
					$ret[] = "entity renamed ok";
				}
				else
				{
					$ret[] = "no entity found";
				}
			}
			if ($rename_link)
			{
				$link = OJ_Row::load_single_object("OJ_Links", ["from_entities_id"=>$parentid, "to_entities_id"=>$ojid]);
				if ($link)
				{
					$link->name = $newname;
					$link->save();
					$ret[] = "link renamed ok";
				}
				else
				{
					$ret[] = "no link found";
				}
			}
			echo implode(", ", $ret);
			break;
		case "newentityattributes":
			$entity_types_id = $params["etype"];
			$pages = null;
			if (array_key_exists("pages", $params))
			{
				$pages = explode(',', $params["pages"]);
			}
			$pages = $cd->get_new_entity_pages($indexname, $entity_types_id);
			echo OJ_HTML_Display::get_new_entity_panel($username, $catalogname, $entity_types_id, $pages);
			break;
		case "treedelete":
			$entities_id = $params["ojid"];
			OJ_Entities::tree_delete($entities_id, $catalogs_id);
			echo "ok";
			break;
		case "rssreload":
			if (isset($params["feed"]))
			{
				$feedids = explode(',', $params["feed"]);
//				OJ_Logger::get_logger()->ojdebug("1.rssreload ".$feedids);
			}
			else
			{
				$grp = OJ_Entity_Types::get_entity_type_id("GROUP");
				$rsscatid = OJ_Catalogs::get_catalog_id("RSS");
				$where = ["entity_types_id"=>$grp, "catalogs_id"=>$rsscatid];
				$feedids = OJ_Row::load_column("entities", "id", $where);
				OJ_Logger::get_logger()->ojdebug("2.rssreload ", $feedids);
			}
			$cd = new OJ_RSS_Catalog_Display($username, $catalogname);
			foreach ($feedids as $feedid)
			{
				$cd->load_feed($feedid);
			}
			echo "ok";
			break;
		case "rssmarkread":
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
			break;
		case "rssunread":
			$feedid = $params['feed'];
			echo json_encode([$feedid=>OJ_Rss_Items::get_number_unread($feedid)]);
			break;
		case "makenote":
			$ncd = OJ_Catalog_Display::get_catalog_display($username, "Notes");
			$entities_id = $params["ojid"];
			$note = $params["note"];
			$title = isset($params["title"])?$params["title"]:null;
			echo $ncd->make_note($catalogs_id, $entities_id, $note, $title);
			break;
		case "gettitle":
			$url = $params["url"];
			OJ_Logger::get_logger()->ojdebug("url", $url);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json'));
			$data = curl_exec($curl);
			$data = trim($data);
			$title = "";
			if ($data)
			{
				curl_close($curl);
				OJ_Logger::get_logger()->ojdebug("data", $data);
				$title = preg_match("/<title>(.*)<\/title>/siU", $data, $title_matches);
				OJ_Logger::get_logger()->ojdebug("title_matches", $title_matches);
				if ($title_matches && is_array($title_matches) && isset($title_matches[1]))
				{
					$title = preg_replace('/\s+/', ' ', $title_matches[1]);
					$title = trim($title);
				}
			}
			if (!$title)
			{
				$pth1 = parse_url($url);
				OJ_Logger::get_logger()->ojdebug("pth1", $pth1);
				if (isset($pth1["path"]) && (strlen($pth1["path"]) > 1))
				{
					$pth = $pth1["path"];
					$info = pathinfo($pth);
					if (isset($info['extension']) && $info['extension'])
					{
						$title =  basename($pth,'.'.$info['extension']);
					}
					else
					{
						$title = basename($pth);
					}
				}
				else
				{
					$title = $pth1["host"];
				}
			}

			// Clean up title: remove EOL's and excessive whitespace.
 			echo $title;
			break;
		case "anymessages":
//			var_dump($_SERVER);
			$dirname = dirname($_SERVER["SCRIPT_FILENAME"]);
			$msgs = scandir($dirname.DIRECTORY_SEPARATOR."messages");
//			var_dump($msgs);
			if (count($msgs) > 2)
			{
//				header('Content-Type: text/plain');
//				header('Content-disposition: attachment; filename='.basename($msgs[2]));
//				header('Content-Length: ' . filesize($msgs[2]));
				readfile($dirname.DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$msgs[2]);
			}
			else
			{
				echo "zilch";
			}
			break;
		case "parameters":
			$parameters = OJ_Parameters::get_all_parameters($catalogs_id);
			$pms = [];
			foreach ($parameters as $pname=>$p)
			{
				$pms[$pname] = ["type"=>$p->table_name, "value"=>$p->get_value()];
			}
			echo json_encode($pms);
			break;
		case "setparameter":
			$pname = $params["name"];
			$ptype = $params["type"];
			$pval = $params["value"];
			$p = OJ_Row::load_single_object("OJ_Parameters", ["name"=>$pname, "catalogs_id"=>$catalogs_id]);
			if ($p == null)
			{
				$p = new OJ_Parameters(["name"=>$pname, "catalogs_id"=>$catalogs_id, "table_name"=>$ptype]);
			}
			$p->set_value($pval);
			$p->save();
			echo "ok";
			break;
		case "newlogicalcategory":
			$lcname = $params["name"];
			$existing = OJ_Row::load_single_object("OJ_Logical_Categories", ["name"=>$lcname]);
			if ($existing)
			{
				echo $existing->id;
			}
			else
			{
				$lc = new OJ_Logical_Categories(["name"=>$lcname]);
				echo $lc->save();
			}
			break;
		case "newftp":
			$site = $params["site"];
			$ftpuser = $params["ftpuser"];
			$pword = $params["password"];
			$existing = OJ_Row::load_single_object("OJ_Logical_Categories", ["name"=>$site, "user"=>$ftpuser]);
			if ($existing)
			{
				echo $existing->id;
			}
			else
			{
				$ftp = new OJ_Ftp(["name"=>$site, "user"=>$ftpuser, "pword"=>  OJ_Utilities::encrypt($pword)]);
				echo $ftp->save();
			}
			break;
		case "getlogicalcategories":
			echo json_encode(OJ_Row::load_column("logical_categories", "name"));
			break;
	} */
}


?>