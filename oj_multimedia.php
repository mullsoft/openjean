<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_Multimedia_Catalog_Display extends OJ_Catalog_Display
{
	public function get_page_display($indexname, $ojid, $pname, $page, $editable = false)
	{
//		OJ_Logger::get_logger()->ojdebug1("page display", $page);
		$components = [];
		$etype = OJ_Entities::get_entity_types_name($ojid);
		if ($etype === "GROUP")
		{
			if ($indexname === 'internet radio')
			{
				if ($pname ==="detail")
				{
					$att = is_array($page)?$page["website"]:$page->get_attribute("website");
					$comp = $this->get_component($att, $editable);
					if ($comp !== null)
					{
						$components[] = $comp;
					}
				}
			}
			else if ($pname === "images")
			{
				$atts = is_array($page)?array_values($page):$page->get_visible_attributes();
				foreach ($atts as $att)
				{
//					$att->type = "image";
					$att["type"] = "image";
				}
				$comp = $this->get_component($att, $editable);
				if ($comp !== null)
				{
					$components[] = $comp;
				}
			}
			else if ($pname === "pdf")
			{
				$atts = is_array($page)?array_values($page):$page->get_visible_attributes();
				foreach ($atts as $att)
				{
					$att["type"] = "pdf";
				}
				$comp = $this->get_component($att, $editable);
				if ($comp !== null)
				{
					$components[] = $comp;
				}
			}
			else
			{
//				$atts = is_array($page)?array_values($page):$page->get_visible_attributes();
				$att = is_array($page)?(isset($page["title"])?$page["title"]:null):$page->get_attribute("title");
				if ($att != null)
				{
					$comp = $this->get_component($att, $editable);
					if ($comp !== null)
					{
						$components[] = $comp;
					}
				}
				$artists = OJ_Audio_Utilities::get_artists_for_album($ojid);
				$acomp = [];
				foreach ($artists as $atype=>$artista)
				{
					foreach ($artista as $artist)
					{
						$website = OJ_Entities::get_attribute_value($artist->id, "details", "website");
						if ($website && ($website !== "http://"))
						{
							$a = new OJ_A($website, $artist->name, "oj-artist-".$artist->id, "oj-artist-a");
							$a->add_attribute("target", "_blank");
						}
						else
						{
							$a = new OJ_SPAN($artist->name, "oj-artist-".$artist->id, "oj-artist-span");
						}
						$lbl = new OJ_SPAN($atype === "solo artist"?"artist":$atype, null, "oj-artist-type-span");
						$acomp[] = new OJ_DIV([$lbl, $a], null, "oj-artist-div");
					}
				}
				$components[] = new OJ_DIV($acomp, null, "oj-artists-div");
			}
		}
		elseif ($etype === "ITEM")
		{
			if ($pname ==="detail")
			{
//				$atts = is_array($page)?array_values($page):$page->get_visible_attributes();
				$att = is_array($page)?$page["title"]:$page->get_attribute("title");
				$track1 = OJ_Entities::get_attribute_value($ojid, "detail", "track");
				$comp = $this->get_component($att, $editable);
				if ($comp !== null)
				{
					$components[] = $comp;
				}
				$album = OJ_Audio_Utilities::get_album($ojid);
				if ($album && $album->id && ($album->id > 0))
				{
					$pathname = OJ_Links::get_one_pathname_to(OJ_Audio_Utilities::get_multimedia_catalogs_id(), $album->id, OJ_Audio_Utilities::get_audio_id(),
							[OJ_Audio_Utilities::get_playlist_id($this->_username), OJ_Audio_Utilities::get_favorites_id($this->_username)]);
//					$paths = OJ_Links::get_paths_to(OJ_Audio_Utilities::get_multimedia_catalogs_id(), $album->id, OJ_Audio_Utilities::get_audio_id(),
//							[OJ_Audio_Utilities::get_playlist_id($this->_username), OJ_Audio_Utilities::get_favorites_id($this->_username)]);
//					if ($paths && (count($paths["paths"]) > 0))
//					{
//						$span1 = new OJ_SPAN("album", "oj-album-path-label-".$ojid, "oj-album-path-label");
//						$names = [];
//						$pth = $paths["paths"][0];
//						for ($n = 1; $n < count($pth); $n++)
//						{
//							$names[] = OJ_Entities::get_entity_name($pth[$n]);
//						}
//						$span2 = new OJ_SPAN(implode('/', $names), "oj-album-path-value-".$ojid, "oj-album-path-value");
//						$components[] = new OJ_DIV([$span1, $span2], null, "oj-album-path-div");
//					}
					$span1 = new OJ_SPAN("album", "oj-album-path-label-".$ojid, "oj-album-path-label");
					$span2 = new OJ_SPAN($pathname, "oj-album-path-value-".$ojid, "oj-album-path-value");
					$span3 = new OJ_SPAN("file", "oj-album-track-label-".$ojid, "oj-album-track-label");
					$span4 = new OJ_SPAN($track1, "oj-album-track-value-".$ojid, "oj-album-track-value");
					$components[] = new OJ_DIV([$span1, $span2], null, "oj-album-path-div");
					$components[] = new OJ_DIV([$span3, $span4], null, "oj-album-track-div");
				}
				$artists = OJ_Audio_Utilities::get_artists_for_album($ojid);
				$acomp = [];
				foreach ($artists as $atype=>$artista)
				{
					foreach ($artista as $artist)
					{
						$website = OJ_Entities::get_attribute_value($artist->id, "details", "website");
						if ($website && ($website !== "http://"))
						{
							$a = new OJ_A($website, $artist->name, "oj-artist-".$artist->id, "oj-artist-a");
							$a->add_attribute("target", "_blank");
						}
						else
						{
							$a = new OJ_SPAN($artist->name, "oj-artist-".$artist->id, "oj-artist-span");
						}
						$lbl = new OJ_SPAN($atype === "solo artist"?"artist":$atype, null, "oj-artist-type-span");
						$acomp[] = new OJ_DIV([$lbl, $a], null, "oj-artist-div");
					}
				}
				$components[] = new OJ_DIV($acomp, null, "oj-artists-div");
			}
		}
		return new OJ_DIV($components, "oj-page-".$pname, "oj-page tab-pane");
	}
	
	public function get_filter_parameters($subcat)
	{
		$ret = [];
		if ($subcat["type"] === 'GROUP')
		{
			$ret["propagate"] = "down";
			$ret["propagatefrom"] = "self";
		}
		return $ret;
	}

	public function get_entity_menu_items($indexname)
	{
		$import_a = new OJ_A('#', "Import Album", null, null, "show_import_entity();");
		$import_a->add_attribute("target", "_self");
		$import_li = new OJ_LI($import_a, null, null);
		return [OJ_HTML::to_html($import_li)];
	}
	
	public function can_create_groups($index) {
		return $index === "internet radio";
	}

	public function can_create_items($index) {
		return false;
	}

	public function has_values($subcat, $followrefs)
	{
		$ret = parent::has_values($subcat, $followrefs);
		if ($ret && ($subcat["type"] === "GROUP"))
		{
			$subtype = OJ_Entities::get_property_value($subcat["to_entities_id"], "subtype", true);
			$ret = $subtype !== "radio";
		}
		return $ret;
	}
	
	public function get_new_entity_pages($index, $etype)
	{
		$ret = null;
		if ($index === 'internet radio')
		{
			$ret = ["detail"=>["website"]];
		}
		return $ret;
	}

	public function treat_as_category($index, $lnk)
	{
		$ret = $this->get_sort_type($lnk) === 'CATEGORY';
		if ($ret)
		{
			$subtype = OJ_Entities::get_property_value($lnk["to_entities_id"], "subtype", true);
			switch ($index)
			{
				case "audio":
					$ret = $subtype === 'audio_genre';
					break;
				case "video":
					$ret = $subtype === 'video_genre';
					break;
				case "internet radio":
					$ret = $subtype === 'radio_genre';
					break;
				default:
					break;
			}
		}
		return $ret;
	}
	
	public function get_extra_indexes()
	{
		return [["label"=>"artists", "value"=>"artists__audio"]];
	}

	public function get_sort_type($lnk)
	{
		$st = strtoupper(strval($lnk['type']));
//		OJ_Logger::get_logger()->ojdebug1("1.sort subtype ".$lnk["name"]);
		if ('CATEGORY' == $st)
		{
			$subtype = OJ_Entities::get_property_value($lnk["to_entities_id"], "subtype", true);
//				OJ_Logger::get_logger()->ojdebug1("sort subtype ".json_encode($subtype));
//				var_dump($subtype);
			if ('audio_boxset' == $subtype)
			{
				$st = 'GROUP';
			}
		}
		return $st;
	}
	
	public function get_search_excludes($username, $catalog)
	{
		return [OJ_Audio_Utilities::get_playlist_id($username), OJ_Audio_Utilities::get_favorites_id($username)];
	}
	

//	public function get_display_panel_contents($indexname, $ojid) {
//		return "";
////		return '<h4>Playlist</h4>'.
////					'<ul id="oj-playlist-list">'.
////					'</ul>'.
////					'<p>'.
////					'<a class="btn btn-primary" href="#" role="button" onclick="play_playlist();">Play</a>'.
////					'<a class="btn btn-primary" href="#" role="button" onclick="clear_playlist();">Clear</a>'.
////					'</p>';
//	}

	public function oj_action_playlist($params)
	{
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
		$plid = OJ_Audio_Utilities::get_playlist_id($this->_username);
		$this->_catalogs_id = OJ_Audio_Utilities::get_multimedia_catalogs_id();
		$entid = OJ_Links::find_by_name($name, $plid, $this->_catalogs_id);
		if ($entid == 0)
		{
			$xml = OJ_Audio_Utilities::playlist_to_xml_entity_string($this->_username, $name, $list);
			$ent = OJ_Entities::from_xml_string($xml);
			if ($ent)
			{
				$entid = $ent->id;
			}
		}
		else
		{
			$xml = OJ_Audio_Utilities::playlist_to_xml_links_string($this->_username, $name, $list);
			$lnks = OJ_Links::from_xml($this->_catalogs_id, $entid, $xml);
		}
		echo "id=" . $entid;
	}

	public function oj_action_selectplaylist($params)
	{
		$selagrp = OJ_HTML_Display::get_group_select_list($this->_username, "multimedia", "Playlists--" . $this->_username, "select-playlist-");
		echo OJ_HTML::to_html($selagrp);
	}

	public function oj_action_getplaylist($params)
	{
		$ojid = $params["ojid"];
		$plid = OJ_Audio_Utilities::get_playlist_id($this->_username);
		$mmcatid = OJ_Audio_Utilities::get_multimedia_catalogs_id();
		$rootid = OJ_Catalogs::get_root_id($mmcatid);
		$links = OJ_Row::load_array_of_objects("OJ_Links", ["from_entities_id" => $ojid, "from_catalogs_id" => $mmcatid, "to_catalogs_id" => $mmcatid]);
		OJ_Logger::get_logger()->ojdebug("links", $links);
		$items = [];
		foreach ($links["result"] as $lnk)
		{
			$paths = OJ_Links::get_paths_to($mmcatid, $lnk->to_entities_id, $rootid, [$plid]);
			OJ_Logger::get_logger()->ojdebug("paths", $paths);
			$grp = "unknown";
			$grpid = 0;
			if ($paths && (count($paths["paths"]) > 0))
			{
				$pth = $paths["paths"][0];
				$grpid = $pth[count($pth) - 2];
				$grp = OJ_Entities::get_entity_name($grpid);
			}
			$items[] = $lnk->to_entities_id . "|" . $lnk->name . "|" . $grpid . "|" . $grp;
		}
		echo implode('^', $items);
	}

	public function oj_action_addtoplaylist($params)
	{
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
		$grp = "unknown";
		$grpid = 0;
		$tt = "";
		if ($paths && (count($paths["paths"]) > 0))
		{
			$pth = $paths["paths"][0];
			$grpid = $pth[count($pth) - 2];
			$grp = OJ_Entities::get_entity_name($grpid);
			$tt = ' tooltip="' . $grpid . '::' . htmlentities($grp, ENT_XML1) . '"';
		}
		$lnkstr = '<link direction="from" catalog="' . $mmcatid . '" ordinal="' . $nlinks . '" hidden="0" other="' . $ojid . '"' . $tt . '>' . htmlentities($name, ENT_XML1) . '</link>';
		$lnk = OJ_Links::from_xml($mmcatid, $plid, $lnkstr);
		echo "link id " . $lnk->id;
	}

	public function oj_action_playlistdiv($params)
	{
		readfile("oj_playlist.html");
		readfile("oj_select_artist1.html");
		$selacat = OJ_HTML_Display::get_category_select_list($this->_username, "artists", "audio", "new-artist-");
		echo OJ_HTML::to_html($selacat);
		readfile("oj_select_artist2.html");
	}

	public function oj_action_favourite($params)
	{
		$ojid = $params["ojid"];
		$fav = OJ_Audio_Utilities::get_favorites_id($this->_username);
		$xml = '<link direction="from" catalog="' . $this->_catalogs_id . '" ordinal="0" hidden="0" other="' . $ojid . '">' . htmlentities($params["name"], ENT_XML1) . '</link>';
		$lnk = OJ_Links::from_xml($this->_catalogs_id, $fav, $xml);
		echo $lnk == null ? 0 : $lnk->id;
	}

	public function oj_action_unimported($params)
	{
		$unimported = OJ_Audio_Utilities::get_unimported_folders($params['logical'], $this->_catalogs_id);
		$fdiv = [];
		foreach ($unimported as $folder)
		{
			$text = [new OJ_INPUT("radio", "oj-folder", null, "oj-folder-radio", $folder), new OJ_SPAN(basename($folder), null, "oj-folder-span oj-label-span")];
			$label = new OJ_LABEL(null, $text, null, "oj-folder-label oj-label");
			$fdiv[] = new OJ_DIV($label, null, "oj-folder-div oj-div");
		}
		echo OJ_HTML::to_html($fdiv);
	}

	public function oj_action_getartist($params)
	{
		$aname = $params["artist"];
		echo json_encode(OJ_Audio_Utilities::find_artists($aname));
	}

	public function oj_action_import($params)
	{
//			echo json_encode($params);
		$xml = OJ_Audio_Utilities::folderpath_to_xml_entity_string($params['folder'], [$params['category']], explode(',', $params['artists']));
		$entid = 0;
		$ent = OJ_Entities::from_xml_string($xml);
		if ($ent)
		{
			$logbits = explode('|', $params["logical"]);
			$fldr = new OJ_Imported_Folders(["name" => strtolower(basename($params["folder"])), "entities_id" => $ent->id,
				"catalogs_id" => OJ_Catalogs::get_catalog_id("multimedia"), "logicals_name" => $logbits[0]]);
			$fldr->save();
			$entid = $ent->id;
		}
		echo $entid;
	}

}

class OJ_Audio_Utilities
{

	private static $loaded;
	private static $endings = ['trio', 'quartet', "quintet", "sextet", "septet", "group", "octet", "nonet"];
	private static $abbreviations = ['orch' => 'orchestra'];
	private static $_multimedia_catalogs_id;
	private static $_artists_catalogs_id;
	private static $_artist_types = ["solo artist", "band", "writer", "orchestra", "conductor", "soloist", "composer", "producer", "director", "actor", "author", "editor", "other"];

	public static function get_multimedia_catalogs_id()
	{
		if (self::$_multimedia_catalogs_id == null)
		{
			self::$_multimedia_catalogs_id = OJ_Catalogs::get_catalog_id('multimedia');
		}
		return self::$_multimedia_catalogs_id;
	}

	public static function get_artists_catalogs_id()
	{
		if (self::$_artists_catalogs_id == null)
		{
			self::$_artists_catalogs_id = OJ_Catalogs::get_catalog_id('artists');
		}
		return self::$_artists_catalogs_id;
	}

	public static function is_artist_type($str)
	{
		return ($str != null) && (array_search($str, self::$_artist_types) !== FALSE);
	}

	public static function get_artist_type($artist_entities_id)
	{
		$ret = null;
		$art = OJ_Row::load_single_object("OJ_Attributes", ["name" => "type", "entities_id" => $artist_entities_id]);
		if ($art)
		{
			$ret = $art->get_value();
		}
		if (!self::is_artist_type($ret))
		{
			$ret = null;
			$col = OJ_Row::load_column("links", "name", ["from_entities_id" => $artist_entities_id, "to_catalogs_id" => self::get_multimedia_catalogs_id()]);
			if ($col && (count($col) > 0))
			{
				foreach ($col as $typ)
				{
					if (self::is_artist_type($typ))
					{
						$ret = $typ;
						break;
					}
				}
			}
		}
		return $ret;
	}

	public static function get_unimported_folders($logicalname, $catalogs_id)
	{
		if (self::$loaded === null)
		{
			self::$loaded = OJ_Row::load_hash_of_all_objects("OJ_Imported_Folders", ["catalogs_id" => $catalogs_id, "logicals_name" => $logicalname], "name");
		}
		$logical = OJ_Logicals::get_logical($logicalname);
		$root = $logical->value;
		$rootinfo = OJ_File_Utilities::dir_info($root);
		$notthere = [];
//		OJ_Logger::get_logger()->ojdebug("db folders", self::$loaded);
		foreach ($rootinfo["subdirs"] as $folder)
		{
			$fname = strtolower(trim(basename($folder)));
//			OJ_Logger::get_logger()->ojdebug("folder", $fname);
			if (!array_key_exists($fname, self::$loaded))
			{
				$notthere[] = $folder;
			}
		}
		return $notthere;
	}

	public static function mangle_artist_name($aname)
	{
		$ret1 = OJ_Utilities::multiexplode([',', ' & ', ' and ', ' with '], strtolower($aname));
		$ret = [];
		foreach ($ret1 as $str)
		{
			$str = trim($str);
			if (OJ_Utilities::starts_with($str, 'the '))
			{
				$str = substr($str, 4);
			}
			foreach (self::$endings as $ending)
			{
				if (OJ_Utilities::ends_with($str, $ending))
				{
					$str = substr($str, 0, 0 - strlen($ending));
				}
			}
			foreach (self::$abbreviations as $abbrev => $full)
			{
				if (OJ_Utilities::ends_with($str, $abbrev) || (strpos($str, $abbrev . ' ') !== FALSE) || (strpos($str, $abbrev . '.') !== FALSE))
				{
					str_replace($abbrev, $full, $str);
				}
			}
			$ret[] = addslashes(trim($str));
		}
		return $ret;
	}

	public static function find_artists($aname)
	{
		$cid = self::get_artists_catalogs_id();
		$names = self::mangle_artist_name($aname);
		$ret1 = OJ_Row::load_array_of_objects('OJ_Entities', ["catalogs_id" => $cid, "name%~%" => $names]);
		$ret2 = OJ_Row::load_array_of_objects('OJ_Aka', ["name%~%" => $names, "table_name" => "entities"]);
//		var_dump($ret1);var_dump($ret2);
		$ret = [];
		foreach ($ret1['result'] as $r)
		{
			$ret[$r->id] = $r->name;
		}
		foreach ($ret2['result'] as $r)
		{
			if (!array_key_exists($r->entities_id, $ret))
			{
				$ret[$r->entities_id] = OJ_Row::get_single_value("OJ_Entities", 'name', ["id" => $r->aka_id]);
			}
		}
		$ret1 = [];
		foreach ($ret as $aid => $aname)
		{
			$atype = self::get_artist_type($aid);
			if ($atype)
			{
				$ret1[$aid] = $aname . '|' . $atype;
			}
			else
			{
				$ret1[$aid] = $aname;
			}
		}
		return $ret1;
	}

	public static function get_duration($audio_file)
	{
		$output = [];
		exec('soxi -D "' . OJ_Logicals::substitute_for_logical($audio_file) . '"', $output);
		$ret = 0;
		if (count($output) > 0)
		{
			$ret1 = trim($output[0]) * 1000;
			$ret = round($ret1);
		}
		return $ret;
	}

	public static function artist_to_xml_string($artist_name, $artist_type = null, $refids = null)
	{
		$catalogs_id = OJ_Catalogs::get_catalog_id("Artists");
		$entity_types_id = OJ_Entity_Types::get_entity_type_id("ITEM");
		$ret = '<?xml version="1.0"?><entity type="' . $entity_types_id . '" catalog="' . $catalogs_id . '"><name>' . $artist_name . '</name>' .
				'<pages><page name="details" visible="1"><attribute type="string" visible="1" ordinal="3"><name>aka</name><value/></attribute>' .
				'<attribute type="URL" visible="1" ordinal="2"><name>website</name><value/></attribute>' .
				'<attribute type="string" visible="1" ordinal="1"><name>name</name><value>' . $artist_name . '</value></attribute>' .
				'<attribute type="enumeration" visible="1" ordinal="0"><name>type</name><value>' . ($artist_type == null ? "" : $artist_type) . '</value></attribute>' .
				'</page></pages><links>';
		if ($refids != null)
		{
			$mm_catalogs_id = OJ_Catalogs::get_catalog_id("Multimedia");
			foreach ($refids as $refid)
			{
				$ret .= '<link direction="from" catalog="' . $mm_catalogs_id . '" ordinal="0" hidden="0" other="' . $refid . '"/>';
			}
		}
		$ret .= '</links><children/></entity>';
		return $ret;
	}

	public static function get_artists_for_album($albumid)
	{
		$artists_catalogs_id = OJ_Catalogs::get_catalog_id("Artists");
		$multimedia_catalogs_id = OJ_Catalogs::get_catalog_id("Multimedia");
		$links = OJ_Links::get_all_references_to($multimedia_catalogs_id, $albumid, $artists_catalogs_id);
		$ret = [];
		foreach ($links as $lnk)
		{
			$artist = new OJ_Entities($lnk->from_entities_id);
			$type = $lnk->name;
			if (!$type)
			{
				$type = $artist->get_attribute_value($lnk->from_entities_id, "details", "type");
			}
			if (!$type)
			{
				$type = "artist";
			}
			if (array_key_exists($type, $ret))
			{
				array_push($ret[$type], $artist);
			}
			else
			{
				$ret[$type] = [$artist];
			}
		}
		return $ret;
	}

	/*
	 *       <attribute type="string" visible="1" ordinal="0">
	  <name>Ray Charles - What'D I Say (back)</name>
	  <value>${music15}Ray Charles - What'D I Say (1959) (HD VR)/Artwork/Ray Charles - What'D I Say (back).jpg</value>
	  </attribute>

	 */

	public static function image_file_to_xml_attribute_string($file)
	{
		$ret = null;
		if (is_array($file))
		{
			$ret = '';
			$ord = 0;
			foreach ($file as $f)
			{
				$ext = OJ_File_Utilities::get_extension($f, true);
				$path = htmlentities(OJ_File_Utilities::to_logical_path($f, OJ_Logicals::get_logicals()), ENT_XML1);
				$name = htmlentities(basename($f, $ext), ENT_XML1);
				$ret .= '<attribute type="string" visible="1" ordinal="0">';
				$ret .= '<name>' . $name . '</name><value>' . $path . '</value></attribute>';
			}
		}
		else
		{
			$ret = '<attribute type="string" visible="1" ordinal="0">';
			$ext = OJ_File_Utilities::get_extension($file, true);
			$path = htmlentities(OJ_File_Utilities::to_logical_path($file, OJ_Logicals::get_logicals()), ENT_XML1);
			$name = htmlentities(basename($file, $ext), ENT_XML1);
			$ret .= '<name>' . $name . '</name><value>' . $path . '</value></attribute>';
		}
		return $ret;
	}

	/*
	 *
	  <entity type="3" catalog="1">
	  <name>A2 - Jumpin' in the Morning</name>
	  <pages>
	  <page name="artists" visible="0"/>
	  <page name="detail" visible="1">
	  <attribute type="string" visible="1" ordinal="1">
	  <name>title</name>
	  <value>A2 - Jumpin' in the Morning</value>
	  </attribute>
	  <attribute type="integer" visible="1" ordinal="4">
	  <name>duration</name>
	  <value>170271</value>
	  </attribute>
	  <attribute type="audio" visible="1" ordinal="2">
	  <name>track</name>
	  <value>${music15}Ray Charles - What'D I Say (1959) (HD VR)/A2 - Jumpin' in the Morning.flac</value>
	  </attribute>
	  <attribute type="integer" visible="1" ordinal="5">
	  <name>length</name>
	  <value/>
	  </attribute>
	  <attribute type="boolean" visible="1" ordinal="3">
	  <name>variousArtists</name>
	  <value>false</value>
	  </attribute>
	  <attribute type="string" visible="1" ordinal="0">
	  <name>artist</name>
	  <value>Ray Charles</value>
	  </attribute>
	  <attribute type="number" visible="1" ordinal="7">
	  <name>gain</name>
	  <value>0.85</value>
	  </attribute>
	  </page>
	  </pages>
	  <links>
	  <link direction="to" catalog="2" ordinal="0" hidden="0" other="3430"/>
	  <link direction="to" catalog="1" ordinal="2" hidden="0" other="134995">A2 - Jumpin' in the Morning</link>
	  </links>
	  <children/>
	  </entity>
	 */

	public static function audio_file_to_xml_entity_string($file, $artistname = null, $artistids = [])
	{
		$mmcatid = OJ_Catalogs::get_catalog_id("multimedia");
		$typeid = OJ_Entity_Types::get_entity_type_id('ITEM');
		if (is_string($file))
		{
			$f = new OJ_File($file);
		}
		else
		{
			$f = $file;
		}
		$a = "";
		if ($artistname === null)
		{
			$nm = explode(" - ", $f->name);
			if (count($nm) > 1)
			{
				$a = htmlentities(trim($nm[0]), ENT_XML1);
			}
		}
		else
		{
			$a = $artistname;
		}
		$lnkstr = '';
		if (count($artistids) > 0)
		{
			$artcatid = OJ_Catalogs::get_catalog_id("artists");
			$lnkstr .= '<links>';
			$ord = 0;
			foreach ($artistids as $aid)
			{
				$art = explode('|', $aid);
				$lnkstr .= '<link direction="to" catalog="' . $artcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $art[0] . '">' . htmlentities($art[1], ENT_XML1) . '</link>';
				$ord++;
			}
			$lnkstr .= '</links>';
		}
		else
		{
			$lnkstr .= '<links/>';
		}
		$track = OJ_File_Utilities::to_logical_path(htmlentities($file, ENT_XML1), OJ_Logicals::get_logicals());
		$ret = '<entity type="' . $typeid . '" catalog="' . $mmcatid . '"><name>' . htmlentities($f->get_name(), ENT_XML1) . '</name><pages><page name="detail" visible="1">' .
				'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>' . htmlentities($f->get_name(), ENT_XML1) . '</value></attribute>' .
				'<attribute type="audio" visible="1" ordinal="2"><name>track</name><value>' . $track . '</value></attribute>' .
//				'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>'.$file->va?"true":"false".'</value></attribute>'.
				'<attribute type="integer" visible="1" ordinal="4"><name>duration</name><value>' . OJ_Audio_Utilities::get_duration($file) . '</value></attribute>' .
				'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>' . $a . '</value></attribute>' .
				'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>' .
				'</page></pages>' . $lnkstr . '<children/></entity>';
		return $ret;
	}

	/*
	 *         <page name="artists" visible="0"/>
	  <page name="detail" visible="1">
	  <attribute type="string" visible="1" ordinal="1">
	  <name>title</name>
	  <value>Squeeze Me (2008)</value>
	  </attribute>
	  <attribute type="URL" visible="1" ordinal="6">
	  <name>website</name>
	  <value/>
	  </attribute>
	  <attribute type="string" visible="1" ordinal="8">
	  <name>contentGain</name>
	  <value/>
	  </attribute>
	  <attribute type="boolean" visible="1" ordinal="4">
	  <name>variousArtists</name>
	  <value>false</value>
	  </attribute>
	  <attribute type="string" visible="1" ordinal="0">
	  <name>artist</name>
	  <value>Bessie Smith</value>
	  </attribute>
	  <attribute type="number" visible="1" ordinal="7">
	  <name>gain</name>
	  <value>0.85</value>
	  </attribute>
	  </page>
	  <page name="images" visible="0">
	  <attribute type="string" visible="1" ordinal="0">
	  <name>folder</name>
	  <value>${music21}Bessie Smith - Squeeze Me (2008)/CD 1/folder.jpg</value>
	  </attribute>
	  </page>
	  </pages>

	 */

	public static function playlist_to_xml_entity_string($username, $name, $list)
	{
		$grptypeid = OJ_Entity_Types::get_entity_type_id('GROUP');
		$mmcatid = OJ_Catalogs::get_catalog_id("multimedia");
		$plid = self::get_playlist_id($username);
		$ret = '<entity type="' . $grptypeid . '" catalog="' . $mmcatid . '"><name>' . htmlentities($name, ENT_XML1) . '</name>' .
				'<properties><property type="enumeration" name="subtype">playlist(album|playlist|radio|film|video|episode)</property></properties><pages>' .
				'<page name="detail" visible="1">' .
				'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>' . htmlentities($name, ENT_XML1) . '</value></attribute>' .
				'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>true</value></attribute>' .
				'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>Various Artists</value></attribute>' .
				'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>' .
				'<attribute type="URL" visible="1" ordinal="6"><name>website</name><value/></attribute>' .
				'<attribute type="string" visible="1" ordinal="8"><name>contentGain</name><value/></attribute></page></pages><links>' .
				'<link direction="to" catalog="' . $mmcatid . '" ordinal="0" hidden="0" other="' . $plid . '">' . htmlentities($name, ENT_XML1) . '</link>';
		$ord = 0;
		foreach ($list as $item)
		{
			$tt = '<tooltip>' . htmlentities($item->albumid . "::" . $item->albumname) . '</tooltip>';
			$ret .= '<link direction="from" catalog="' . $mmcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $item->id . '">' . htmlentities($item->name, ENT_XML1) . $tt . '</link>';
			$ord++;
		}
		$ret .= '</links></entity>';
		return $ret;
	}

	public static function playlist_to_xml_links_string($username, $name, $list)
	{
		$grptypeid = OJ_Entity_Types::get_entity_type_id('GROUP');
		$mmcatid = OJ_Catalogs::get_catalog_id("multimedia");
		$plid = self::get_playlist_id($username);
		$ret = '<links>';
		$ord = 0;
		foreach ($list as $item)
		{
			$tt = '<tooltip>' . htmlentities($item->albumid . "::" . $item->albumname) . '</tooltip>';
			$ret .= '<link direction="from" catalog="' . $mmcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $item->id . '">' . htmlentities($item->name, ENT_XML1) . $tt . '</link>';
			$ord++;
		}
		$ret .= '</links>';
		return $ret;
	}

	public static function generate_xml_entity_string($username, $name, $list)
	{
		
	}

	public static function folderpath_to_xml_entity_string($folderpath, $parentids = [], $artistids = [])
	{
		$folder = new OJ_Audio_Folder($folderpath);
		return self::folder_to_xml_entity_string($folder, $parentids, $artistids);
	}

	public static function folder_to_xml_entity_string($folder, $parentids = [], $artistids = [])
	{
//		var_dump($folder);
		$mmcatid = OJ_Catalogs::get_catalog_id("multimedia");
		$artcatid = OJ_Catalogs::get_catalog_id("artists");
		$cattypeid = OJ_Entity_Types::get_entity_type_id('CATEGORY');
		$grptypeid = OJ_Entity_Types::get_entity_type_id('GROUP');
		$artistname = "";
		$nm = explode(" - ", $folder->name);
		if (count($nm) > 1)
		{
			$artistname = htmlentities(trim($nm[0]), ENT_XML1);
		}
		if ($folder->contains_audio_files())
		{
			$ret = '<entity type="' . $grptypeid . '" catalog="' . $mmcatid . '"><name>' . htmlentities($folder->get_name(), ENT_XML1) . '</name>' .
					'<properties><property type="enumeration" name="subtype">album(album|playlist|radio|film|video|episode)</property></properties><pages>' .
					'<page name="detail" visible="1">' .
					'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>' . htmlentities($folder->get_name(), ENT_XML1) . '</value></attribute>' .
					'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>' . ($folder->va ? "true" : "false") . '</value></attribute>' .
					'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>' . $artistname . '</value></attribute>' .
					'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>' .
					'<attribute type="URL" visible="1" ordinal="6"><name>website</name><value/></attribute>' .
					'<attribute type="string" visible="1" ordinal="8"><name>contentGain</name><value/></attribute></page>' .
					'<page name="images" visible="1">';
			$imgf = $folder->get_all_image_files();
			foreach ($imgf as $img)
			{
				$ret .= self::image_file_to_xml_attribute_string($img);
			}
			$ret .= '</page><page name="notes" visible="1">';
			$pdff = $folder->get_all_pdf_files();
			foreach ($pdff as $pdf)
			{
				$ret .= self::image_file_to_xml_attribute_string($pdf);
			}
			$ret .= '</page></pages>';
			if ((count($parentids) > 0) || (count($artistids) > 0))
			{
				$ret .= '<links>';
				$ord = 0;
				foreach ($parentids as $pid)
				{
					$ret .= '<link direction="to" catalog="' . $mmcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $pid . '">' . htmlentities($folder->get_name(), ENT_XML1) . '</link>';
					$ord++;
				}
				$ord = 0;
				foreach ($artistids as $aid)
				{
					$art = explode('|', $aid);
					$ret .= '<link direction="to" catalog="' . $artcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $art[0] . '">' . htmlentities($art[1], ENT_XML1) . '</link>';
					$ord++;
				}
				$ret .= '</links>';
			}
			else
			{
				$ret .= '<links/>';
			}
			$ret .= '<children>';
			foreach ($folder->get_audio_files() as $audiof)
			{
				$audioent = self::audio_file_to_xml_entity_string($audiof, $artistname, $artistids);
//				print $audiof."\n\n".$audioent."\n\n\n";
				$ret .= $audioent;
			}
			$ret .= '</children></entity>';
		}
		else
		{
			$ret = '<entity type="' . $cattypeid . '" catalog="' . $mmcatid . '"><name>' . htmlentities($folder->get_name(), ENT_XML1) . '</name><properties>' .
					'<property type="enumeration" name="subtype">audio_boxset(audio_genre|audio_boxset|radio_genre|video_genre|video_series)</property></properties>' .
					'<pages><page name="images" visible="1">';
			$imgf = $folder->get_all_image_files();
//			var_dump($imgf);
			$imgstr = "";
			foreach ($imgf as $img)
			{
				$imgstr .= self::image_file_to_xml_attribute_string($img);
			}
//			print $imgstr."\n\n\n";
			$ret .= $imgstr;
			$ret .= '</page></pages>';
			if ((count($parentids) > 0) || (count($artistids) > 0))
			{
				$ret .= '<links>';
				$ord = 0;
				foreach ($parentids as $pid)
				{
					$ret .= '<link direction="to" catalog="' . $mmcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $pid . '"/>';
					$ord++;
				}
				$ord = 0;
				foreach ($artistids as $aid)
				{
					$art = explode('|', $aid);
					$ret .= '<link direction="to" catalog="' . $artcatid . '" ordinal="' . $ord . '" hidden="0" other="' . $art[0] . '">' . htmlentities($art[1], ENT_XML1) . '</link>';
					$ord++;
				}
				$ret .= '</links>';
			}
			else
			{
				$ret .= '<links/>';
			}
			$ret .= '<children>';
			foreach ($folder->subdirs as $sub)
			{
				if ($sub->contains_audio_files(true))
				{
					$audioent = self::folder_to_xml_entity_string($sub, [], $artistids);
//					print $sub."\n\n".$audioent."\n\n\n";
					$ret .= $audioent;
				}
			}
			$ret .= '</children></entity>';
		}
		return $ret;
	}

	public static function get_album($itemid)
	{
		$sql = "SELECT entities.* FROM entities JOIN links ON entities.id = links.from_entities_id JOIN properties ON properties.entities_id = entities.id " .
				"JOIN enumeration_values ON properties.values_id = enumeration_values.id WHERE links.to_entities_id = $itemid AND enumeration_values.value = 'album'";
		$hash = [];
		$loaded = Project_Details::get_db()->loadHash($sql, $hash);
		$ret = null;
		if ($loaded)
		{
			$ret = new OJ_Entities();
			$ret->set_all($hash);
		}
		return $ret;
	}

	public static function get_playlist_id($username)
	{
		return OJ_Links::get_entities_id_from_path(self::get_multimedia_catalogs_id(), "Playlists/" . $username);
	}

	public static function get_favorites_id($username)
	{
		return OJ_Links::get_entities_id_from_path(self::get_multimedia_catalogs_id(), "Favourites/" . $username);
	}

	public static function get_audio_id()
	{
		return OJ_Catalogs::get_top_level_category_id(self::get_multimedia_catalogs_id(), "audio");
	}

}


