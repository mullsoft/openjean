<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once("ojEntity_".$ojmode.".php");
require_once("OJLinkSorters.php");
require_once("oj_notes.php");

class OJ_System
{

	const OJURI = "http://www.mullsoft.co.uk/openjean";
	const NS = "openjean:";

	private static $_instances;
	private static $_sysroot = "/var/openjean/";

	public static function set_sysroot($sysroot)
	{
		self::$_sysroot = $sysroot;
	}

	public static function instance($username)
	{
		if (self::$_instances == null)
		{
			self::$_instances = array();
		}
		$userroot = self::$_sysroot . $username . "/";
		if (!isset(self::$_instances[$userroot]))
		{
			self::$_instances[$userroot] = new OJ_System($username, $userroot);
		}
		return self::$_instances[$userroot];
	}

	private $_userroot;
	private $_username;
	private $_catalogs;
	private $_logicals;
	private $_logicalftps;
	private $_logicalobjs;
	private $_logger;

	private function __construct($username, $userroot)
	{
		$_catalogs = array();
		$this->_userroot = $userroot;
		$this->_username = $username;
		$this->_logger = OJ_Logger::get_logger(self::$_sysroot . "logs");
//		echo $this->_userroot."<br/>";
	}

	public function get_username()
	{
		return $this->_username;
	}

//	public function get_userroot()
//	{
//		return $this->_userroot;
//	}
//	
//	public function get_dbroot()
//	{
//		return $this->_userroot."database/";
//	}
//	
//	public function get_catalogroot($catalogname)
//	{
//		return $this->_userroot."database/".$catalogname."/";
//	}
//	
//	public function get_entityroot($catalogname)
//	{
//		return $this->_userroot."database/".$catalogname."/entities/";
//	}
//	public function get_catalog($catalogname)
//	{
//		if (!isset($this->_catalogs[$catalogname]))
//		{
//			$this->_catalogs[$catalogname] = new OJ_Catalog($this->_username, $catalogname);
//		}
//		return $this->_catalogs[$catalogname];
//	}
//	
	public function load_logicals()
	{
		return OJ_Logicals::get_logicals();
	}

	private function get_logicals()
	{
		if ($this->_logicals == null)
		{
			$this->_logicalobjs = $this->load_logicals();
			$this->_logicalftps = [];
			foreach ($this->_logicalobjs as $lname->$log)
			{
				$lval = $log->value;
				$isalternative = false;
				$ftpid = $log->value_ftp_id;
				if (($log->value_ftp_id == 0) && !file_exists($lval))
				{
					$lval = $log->alternative;
					$ftpid = $log->alternative_ftp_id;
					$isalternative = false;
				}
				$this->_logicalftps[$lname] = $ftpid;
				$this->_logicals[$lname] = ["value" => $lval . '/', "isalternative" => $isalternative, "ftpid" => $ftpid];
			}
		}
		return $this->_logicals;
	}

	public function get_logical($logical)
	{
		$ret = null;
		$logs = $this->get_logicals();
//		var_dump($logs);
		if (isset($logs[$logical]))
		{
			$ret = $logs[$logical];
		}
		return $ret;
	}

	public function substitute_logical($str)
	{
		$ret = ["value" => $str, "original" => $str, "isalternative" => false, "ftp" => null];
		$lstart = strpos($str, '${');
		if ($lstart !== false)
		{
			$part1 = substr($str, 0, $lstart);
			$lend = strpos($str, '}', $lstart);
			if ($lend && $lend > $lstart)
			{
				$part3 = substr($str, $lend + 1);
				$logname = substr($str, $lstart + 2, $lend - $lstart - 2);
//				echo $logname."<br/>";
				$log = $this->get_logical($logname);
				if ($log !== null)
				{
					$part2 = $log["value"];
					if ($part2 !== null)
					{
						$ret["value"] = str_replace('//', '/', $part1 . $part2 . $part3);
					}
					$ret["isalternative"] = $log["isalternative"];
					$ftpid = $log["ftpid"];
					if ($ftpid > 0)
					{
						$ret["ftp"] = new OJ_Ftp($ftpid);
					}
				}
			}
		}
		return $ret;
	}

	public function ojlog($level, ...$logentries)
	{
		$this->_logger->ojlog($level, $logentries);
	}

}

abstract class OJ_Catalog_Display
{

//	private static $known_catalog_displays = [];

	public static function get_catalog_display($username, $catalogname, $indexname = "default")
	{
		require_once 'oj_' . strtolower($catalogname) . '.php';
		$ret = null;
//		$catalog = OJ_System::instance($username)->get_catalog($catalogname);
//		if ($catalog !== null)
//		{
//			$cdu = [];
//			if (array_key_exists($username, self::$known_catalog_displays))
//			{
//				$cdu = self::$known_catalog_displays[$username];
//			}
//			else
//			{
//				self::$known_catalog_displays[$username] = $cdu;
//			}
//			if (array_key_exists($catalogname, $cdu))
//			{
//				$ret = $cdu[$catalogname];
//			}
//			else
//			{
		$cd = "OJ_" . ucfirst($catalogname) . "_Catalog_Display";
		if (class_exists($cd))
		{
			$ret = new $cd($username, $catalogname, $indexname);
		}
		else
		{
			$ret = new OJ_Default_Catalog_Display($username, $catalogname, $indexname);
		}
		$cdu[$catalogname] = $ret;
//			}
//		}
		return $ret;
	}

	protected $_catalogname;
	protected $_username;
	protected $_indexname;
	protected $_index;
	protected $_indexes_id;
	protected $_catalogs_id;
	protected $_omit;
	protected $_ignoreThe;
	protected $_comparison_types;
	protected $_users_id;

	public function __construct($username, $catalogname, $indexname = "default")
	{
		$this->_catalogname = $catalogname;
		$this->_catalogs_id = OJ_Catalogs::get_catalog_id($catalogname);
		$this->_username = $username;
		$this->_indexname = $indexname;
		$this->_users_id = OJ_Users::get_users_id($username);
		$this->_indexes_id = OJ_Indexes::get_index_id($this->_catalogs_id, $indexname);
	}

	public function get_root_id()
	{
		$index = $this->get_index();
		if ($index)
		{
			$ret = $index->entities_id;
		}
		else
		{
			$ret = OJ_Catalogs::get_root_id($this->_catalogs_id, $this->_indexname);
		}
		return $ret;
	}

	public function get_catalog_id()
	{
		return $this->_catalogs_id;
	}

	public function get_catalog_name()
	{
		return $this->_catalogname;
	}

	public function get_index()
	{
		if ($this->_index == null)
		{
			$this->_index = OJ_Indexes::get_index($this->get_catalog_id(), $this->_indexname);
		}
		return $this->_index;
	}

	public function get_omit()
	{
		if (!is_array($this->_omit))
		{
			$index = $this->get_index();
			if ($index)
			{
				$om = $index->omit;
				if ($om)
				{
					$this->_omit = explode(',', $om);
				}
				else
				{
					$this->_omit = [];
				}
			}
			else
			{
				$this->_omit = [];
			}
		}
		return $this->_omit;
	}

	public function get_component($attribute, $editable = false)
	{
		$ret = null;
		OJ_Logger::get_logger()->ojdebug("get component", $attribute);
//		var_dump($attribute);
		if (is_string($attribute))
		{
			$type = "string";
			$val = $attribute;
			$aname = "";
			$ojid = 0;
			$pg = "page";
			$attid = 0;
			$fname = "";
		}
		else if (is_array($attribute))
		{
			$type = $attribute['type'];
			$val = strval($attribute["value"]);
			$aname = isset($attribute["prompt"]) ? $attribute["prompt"] : $attribute["name"];
			$ojid = $attribute['entity'];
			$pg = $attribute['page'];
			$attid = $attribute["id"];
			$fname = $pg . "::" . $attribute["name"];
		}
		else
		{
			$type = $attribute->type;
			$val = strval($attribute->get_value());
			$aname = $attribute->get_prompt();
			$ojid = $attribute->get_ojid();
			$pg = $attribute->get_page();
			$attid = $attribute->get_id();
			$fname = $attribute->get_fullname();
		}
//		echo "attribute $aname has value $val</br>";
		$type1 = strtolower($type);
		if (OJ_Utilities::starts_with($type, "enumeration("))
		{
			$type1 = "enumeration";
		}
		$id = OJ_Utilities::mangle_name_for_css($pg) . "-" . $aname . "-" . abs($ojid);
		$cssclass = $editable ? "oj-attribute oj-input" : "oj-attribute oj-display";
		switch ($type1)
		{
			case "integer":
				{
					$ret1 = new OJ_INPUT("number", $id, $id, $cssclass, $val);
					$ret1->add_attribute("step", "1");
					if (!$editable)
					{
						$ret1->add_attribute("disabled", "disabled");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "text":
				{
					$ret1 = new OJ_TEXTAREA($id, $id, $cssclass, $val);
					if (!$editable)
					{
						$ret1->add_attribute("disabled", "disabled");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "file":
				{
					if (!$editable)
					{
						$ret1 = new OJ_INPUT("text", $id, $id, $cssclass, $val);
						$ret1->add_attribute("disabled", "disabled");
					}
					else
					{
						$ret1 = new OJ_INPUT("file", $id, $id, $cssclass);
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "audio":
				{
					if (!$editable)
					{
						$ret1 = new OJ_INPUT("text", $id, $id, $cssclass, $val);
						$ret1->add_attribute("disabled", "disabled");
					}
					else
					{
						$ret1 = new OJ_INPUT("file", $id, $id, $cssclass);
						$ret1->add_attribute("accept", "audio/*");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "video":
				{
					if (!$editable)
					{
						$ret1 = new OJ_INPUT("text", $id, $id, $cssclass, $val);
						$ret1->add_attribute("disabled", "disabled");
					}
					else
					{
						$ret1 = new OJ_INPUT("file", $id, $id, $cssclass);
						$ret1->add_attribute("accept", "video/*");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "image":
				{
					if (!$editable)
					{
						$olduri = OJ_HTML::split_uri($_SERVER["REQUEST_URI"]);
						$newuri = "http://" . $_SERVER["SERVER_NAME"] . $olduri['path'] . '?';
						$qs = $olduri["query"];
						$newuri .= "user=" . $qs["user"] . "&catalog=" . $qs["catalog"] . "&action=image&ojid=" . $ojid . "&img=" . urlencode($fname);
						OJ_Logger::get_logger()->ojdebug1("image " . $newuri);
						$ret1 = new OJ_IMG($newuri, null, "oj-image", null, "$aname");
						$ret = new OJ_DIV($ret1, null, "oj-image-container");
//					$ret = new OJ_INPUT("text", $id, $id, $cssclass, $val);
//					$ret->add_attribute("disabled", "disabled");
					}
					else
					{
						$ret = new OJ_INPUT("file", $id, $id, $cssclass);
						$ret->add_attribute("accept", "image/*");
					}
					break;
				}
			case "reference":
				{
					// artists[1538]ITEM.OJname
					$lb = strpos($val, "[");
					if ($lb !== FALSE)
					{
						$rb = strpos($val, "]", $lb + 1);
						if ($rb > $lb)
						{
							$catname = substr($val, 0, $lb);
							$refid = substr($val, $lb + 1, $rb - $lb - 1);
							$attname = substr($val, $rb + 1);
							$dot = strpos($attname, ".");
							if ($dot !== FALSE)
							{
								$attname = substr($attname, $dot + 1);
							}
							$pagename = null;
							$dc = strpos($attname, "::");
							if ($dc !== FALSE)
							{
								$pagename = substr($attname, 0, $dc);
								$attname = substr($attname, $dc + 2);
							}
							OJ_Logger::get_logger()->ojdebug1("getting catalog " . $catname);
//						$refcat = OJ_System::instance($this->_catalog->get_username())->get_catalog($catname);
							$refatt = OJ_Entities::get_attribute($refid, $pagename, $attname);
							if ($refatt === null)
							{
								OJ_Logger::get_logger()->ojerror1("null reference attribute " . $attname);
							}
							else
							{
								$ret = $this->get_component($refatt, $editable);
							}
						}
					}
					break;
				}
			case "boolean":
				{
					$ret1 = new OJ_INPUT("checkbox", $id, $id, $cssclass);
					if (strtolower($val) === 'true')
					{
						$ret1->add_attribute("checked", "checked");
					}
					if (!$editable)
					{
						$ret1->add_attribute("disabled", "disabled");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "enumeration":
				{
					$lp = strpos($type, "(");
					if ($lp !== FALSE)
					{
						$rp = strpos($type, ")");
						if ($rp > $lp)
						{
							$vals = explode("|", substr($type, $lp + 1, $rp - $lp - 1));
							$v = $val;
							if ($v)
							{
								$vlp = strpos($v, "(");
								if ($vlp !== FALSE)
								{
									$v = substr($v, 0, $vlp);
								}
							}
							$ret1 = new OJ_SELECT(OJ_OPTION::get_options($vals, $v), $id, $id, $cssclass);
							if (!$editable)
							{
								$ret1->add_attribute("disabled", "disabled");
							}
							$alp = strpos($aname, "(");
							if ($alp !== FALSE)
							{
								$aname = substr($aname, 0, $alp);
							}
							$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
						}
					}
					break;
				}
			case "URL":
			case "url":
				if ($editable)
				{
					$ret1 = new OJ_INPUT("url", $id, $id, $cssclass, $val);
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
				}
				else
				{
					$ret1 = new OJ_IFRAME($val, $id, str_replace("oj-display", "oj-iframe-display", $cssclass));
					if ($cssclass == null)
					{
						$cssclass = "btn btn-default oj-button";
					}
					else
					{
						$cssclass .= " btn btn-default oj-button";
					}
					$ret1->add_attribute("onload", "var len = document.getElementsByTagName('body')[0].innerHTML.length; oj_iframe_loaded('$id', len);");
					$ret1->add_attribute("onerror", "oj_iframe_error('$id');");
					$divid = $id == null ? null : ("div-" . $id);
					$bdivid = $id == null ? null : ("button-div-" . $id);
					$a = new OJ_A($val, "Open in new page", $id, $cssclass);
					$div1 = new OJ_DIV($a, $bdivid, "oj-button-container");
					$ret = new OJ_DIV([$ret1, $div1], $divid, "oj-iframe-container");
				}
				break;
			case "email":
				if ($editable)
				{
					$ret1 = new OJ_INPUT($type1, $id, $id, $cssclass, $val);
				}
				else
				{
					if (OJ_Utilities::starts_with($val, "mailto:"))
					{
						$val1 = $val;
						$val = substr($val, 7);
					}
					else
					{
						$val1 = "mailto:" . $val;
					}
					if ($cssclass == null)
					{
						$cssclass = "btn btn-default oj-button";
					}
					else
					{
						$cssclass .= " btn btn-default oj-button";
					}
					$ret1 = new OJ_A($val1, $val, $id, $cssclass);
					$ret1->add_attribute("target", "_self");
				}
				$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
				break;
			case "number":
			case "date":
			case "time":
			case "datetime":
				{
					$ret1 = new OJ_INPUT($type1, $id, $id, $cssclass, $val);
					if (!$editable)
					{
						$ret1->add_attribute("disabled", "disabled");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
			case "html":
				$ret = new OJ_DIV($val, null, "oj-html-display");
				break;
			case "string":
			default:
				{
					$imgsrc = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
					OJ_Logger::get_logger()->ojdebug1("string image " . $imgsrc);
					$ret1 = new OJ_INPUT("text", $id, $id, $cssclass, $val);
					if (!$editable)
					{
						$ret1->add_attribute("disabled", "disabled");
					}
					$ret = OJ_HTML::labelled_html_object($aname, $ret1, null, "oj-component-label", false, true, null);
					break;
				}
		}
		OJ_Logger::get_logger()->ojdebug("get component for type", $type1, $ret);
		return $ret;
	}

	public function get_page_display($indexname, $ojid, $pname, $page, $editable = false)
	{
//		OJ_Logger::get_logger()->ojdebug1("page display", $page);
		$components = [];
		if (is_array($page))
		{
			$atts = array_values($page);
		}
		else
		{
			$atts = $page->get_visible_attributes();
		}
		foreach ($atts as $att)
		{
			$comp = $this->get_component($att, $editable);
			if ($comp !== null)
			{
				$components[] = $comp;
			}
		}
		return new OJ_DIV($components, "oj-page-" . $pname, "oj-page tab-pane");
	}

	public function follow_references($etype)
	{
		$ret = false;
//		OJ_Logger::get_logger()->ojdebug("etype", $etype);
		$etypename = OJ_Entity_Types::get_entity_type_name($etype);
		$param = OJ_Parameters::get_parameter($this->get_catalog_id(), "followReferences");
		if ($param)
		{
			$val = $param->get_value();
			$ret = $val && (strpos($val, $etypename) !== FALSE);
		}
		return $ret;
	}

	public function get_reference_target_types()
	{
		return null;
	}

	public function on_entity_create($type, $new_entities_id)
	{
		switch ($type)
		{
			case "category":
			case "CATEGORY":
				$this->on_category_create($new_entities_id);
				break;
			case "group":
			case "GROUP":
				$this->on_group_create($new_entities_id);
				break;
			case "item":
			case "ITEM":
				$this->on_item_create($new_entities_id);
				break;
			default:
				break;
		}
		OJ_Logger::get_logger()->ojdebug1("new " . $type . " created with id " . $new_entities_id);
	}

	protected function ignore_the($type)
	{
		if (!$this->_ignoreThe)
		{
			$ig = OJ_Parameters::get_parameter_value($this->get_catalog_id(), "ignoreThe");
			$this->_ignoreThe = strtoupper($ig ? $ig : "none");
		}
		$ret = strpos($this->_ignoreThe, strtoupper($type)) !== FALSE;
//		OJ_Logger::get_logger()->ojdebug1("ignore the ".$this->_ignoreThe." ".$type." ".$ret);
		return $ret;
	}

	protected function get_comparison_type($type)
	{
		if (!$this->_comparison_types)
		{
			$this->_comparison_types = ["ITEM" => "default", "GROUP" => "default", "CATEGORY" => "default"];
			$ct = OJ_Parameters::get_parameter_value($this->get_catalog_id(), "linkComparator");
			if ($ct)
			{
				$cta = explode('|', $ct);
				switch (count($cta))
				{
					case 3:
						$this->_comparison_types["CATEGORY"] = $cta[2];
					case 2:
						$this->_comparison_types["GROUP"] = $cta[1];
					case 1:
						$this->_comparison_types["ITEM"] = $cta[0];
					default:
						break;
				}
			}
		}
		return $this->_comparison_types[strtoupper($type)];
	}

	public function get_search_excludes($username, $catalog)
	{
		return [];
	}

	public function get_group_name($indexname = null)
	{
		$ret = OJ_Parameters::get_parameter_value($this->_catalogs_id, "groupName");
		return $ret ? $ret : "group";
	}

	public function get_item_name($indexname = null)
	{
		$ret = OJ_Parameters::get_parameter_value($this->_catalogs_id, "itemName");
		return $ret ? $ret : "item";
	}

	public function get_category_name($indexname = null)
	{
		$ret = OJ_Parameters::get_parameter_value($this->_catalogs_id, "categoryName");
		return $ret ? $ret : "category";
	}

	public function get_entity_type_name($entities_id, $indexname = null)
	{
		$nm = strtolower(OJ_Entities::get_entity_types_name($entities_id));
		$mthd = "get_" . $nm . "_name";
		if (method_exists($this, $mthd))
		{
			$nm = $this->$mthd();
		}
		return $nm;
	}

	public function get_entity_menu_items($indexname)
	{
		return [];
	}

	public function get_display_pages($ojid)
	{
		return OJ_Entities::get_visible_attributes($ojid);
	}

	public function get_display_panel_contents($indexname, $ojid, $editable = false)
	{
//		$ent = $this->_catalog->get_entity($ojid);
		$ret = "";
		if ($ojid > 0)
		{
//			OJ_Logger::get_logger()->ojdebug1("got entity for ".$ojid);
			$lis = [];
			$pgs = [];
			$pages = $this->get_display_pages($ojid);
//			$pages = OJ_Entities::get_visible_attributes($ent->get_id());
//			var_dump($pages);
			foreach ($pages as $pagename => $page)
			{
				$pname = strtolower(OJ_Utilities::mangle_name_for_css($pagename));
//				OJ_Logger::get_logger()->ojdebug1("got page ".$pagename, $page);
				$a = new OJ_A("#oj-page-" . $pname, $pagename, null, "oj-page-tab-link");
				$a->add_attribute("data-toggle", "tab");
				$li = new OJ_LI($a);
				$pg = $this->get_page_display($indexname, $ojid, $pname, $page, $editable);
				if ($pname === "images")
				{
					array_unshift($pgs, $pg);
					array_unshift($lis, $li);
				}
				else
				{
					$pgs[] = $pg;
					$lis[] = $li;
				}
			}
			$note = OJ_Notes_Catalog_Display::get_note($ojid);
			if ($note)
			{
				$a = new OJ_A("#oj-note-page", "Note", null, "oj-page-tab-link");
				$a->add_attribute("data-toggle", "tab");
				$lis[] = new OJ_LI($a);
				$pgs[] = new OJ_DIV(OJ_Notes_Catalog_Display::get_note_component($note), "oj-note-page", "oj-page tab-pane");
			}
			$h = new OJ_INPUT("hidden", "oj-contents-of-" . $ojid, "oj-contents-of-" . $ojid);
			switch (count($lis))
			{
				case 0:
					$sp = new OJ_SPAN(ucfirst($this->get_entity_type_name($ojid)) . ": " . OJ_Entities::get_entity_name($ojid), null, "oj-entity-name-span");
					$ret1 = new OJ_DIV([$h, $sp], "oj-display-" . $ojid, "container oj-display-container");
					break;
				case 1:
					$ret1 = new OJ_DIV([$h, $pgs[0]], "oj-display-" . $ojid, "container oj-display-container");
					break;
				default:
					$lis[0]->add_class("active");
					$pgs[0]->add_class("active");
					$ul = new OJ_LIST($lis, false, null, "nav nav-pills");
					$contents = new OJ_DIV([$h, $pgs], "oj-contents-" . $ojid, "tab-content clearfix");
					$ret1 = new OJ_DIV([$ul, $contents], "oj-display-" . $ojid, "container oj-display-container");
					break;
			}
			$ret = $ret1->to_html();
		}
		return $ret;
	}

	public function get_filter_parameters($subcat)
	{
		return [];
	}

	public function can_create_groups($index)
	{
		return true;
	}

	public function can_create_items($index)
	{
		return true;
	}

	public function get_new_entity_pages($index, $etype)
	{
		return null;
	}

	private function compare_strings($stra, $strb, $case = false)
	{
		$ret = 0;
		$numa = OJ_Utilities::starts_with_number($stra);
		if (!$numa["starts_with_number"])
		{
			$ret = $case ? strcasecmp($stra, $strb) : strcmp($stra, $strb);
		}
		else
		{
			$numb = OJ_Utilities::starts_with_number($strb);
			if (!$numb["starts_with_number"])
			{
				$ret = $case ? strcasecmp($stra, $strb) : strcmp($stra, $strb);
			}
			else
			{
				$ret = $numa["number"] < $numb["number"] ? -1 : ($numa["number"] > $numb["number"] ? 1 : 0);
			}
		}
		return $ret;
	}

	private function cmp_types($cata, $catb)
	{
		$ret = 0;
		if ($cata != $catb)
		{
			if ($cata == 'CATEGORY')
			{
				$ret = -1;
			}
			elseif ($catb == 'CATEGORY')
			{
				$ret = 1;
			}
			elseif ($cata == 'GROUP')
			{
				$ret = -1;
			}
			else
			{
				$ret = 1;
			}
		}
		return $ret;
	}

	public function cmp_links_default($lnka, $lnkb)
	{
		$orda = intval(strval($lnka['ordinal']));
		$ordb = intval(strval($lnkb['ordinal']));
		$ret = $orda < $ordb ? -1 : ($orda > $ordb ? 1 : 0);
		if ($ret === 0)
		{
			$ret = $this->compare_strings($this->get_the_sort_name($lnka["type"], $lnka["name"]), $this->get_the_sort_name($lnkb["type"], $lnkb["name"]), true);
		}
//		echo "compare ".strval($lnka->name)." (".$cata." ".$orda.") ".strval($lnkb->name)." (".$catb." ".$ordb.") gives ".$ret."<br/>";
		return $ret;
	}

	public function cmp_links_dictionary($lnka, $lnkb)
	{
		$ret = $this->compare_strings($this->get_the_sort_name($lnka["type"], $lnka["name"]), $this->get_the_sort_name($lnkb["type"], $lnkb["name"]), true);
		return $ret;
	}

	public function cmp_links_alphabetic($lnka, $lnkb)
	{
		$ret = $this->compare_strings($this->get_the_sort_name($lnka["type"], $lnka["name"]), $this->get_the_sort_name($lnkb["type"], $lnkb["name"]));
		return $ret;
	}

	private function cmp_links($lnka, $lnkb)
	{
		$cata = $this->get_sort_type($lnka);
		$catb = $this->get_sort_type($lnkb);
		$ret = self::cmp_types($cata, $catb);
		if ($ret === 0)
		{
			$ct = "cmp_links_" . $this::get_comparison_type($cata);
			$ret = $this->$ct($lnka, $lnkb);
//			echo "comparing ".$lnka->name." with ".$lnkb->name." using ".$this->get_comparison_type($cata)." giving ".$ret."<br/>";
		}
		return $ret;
//		echo "compare ".strval($lnka->name)." (".$cata." ".$orda.") ".strval($lnkb->name)." (".$catb." ".$ordb.") gives ".$ret."<br/>";
	}

	public function get_sort_type($lnk)
	{
		return strtoupper(strval($lnk['type']));
	}

	public function get_the_sort_name($type, $name)
	{
		$lname = strtolower($name);
		if ($this->ignore_the($type) && OJ_Utilities::starts_with($lname, "the "))
		{
			$name = substr($name, 4);
			$lname = substr($lname, 4);
		}
		switch ($this::get_comparison_type($type))
		{
			case "dictionary":
			case "default":
				$ret = $lname;
				break;
			default:
				$ret = $name;
				break;
		}
		return $ret;
	}

	public function sort_links(&$link_array)
	{
		usort($link_array, array($this, "cmp_links"));
//		var_dump($this->_comparison_type);
	}

	protected function get_children($lnk, $followrefs, $oftype = null)
	{
		$followrefs = $this->follow_references($lnk["type"]) && $followrefs;
		$includeX = false;
		$onlyX = false;
		if ($followrefs)
		{
			$includeX = true;
			if ($lnk['type'] === 'ITEM')
			{
				$onlyX = true;
			}
		}
//		$this->ojdebug1("load children ".$this->_data['to']." ".($includeX?"true":"false")." ".($onlyX?"true":"false"));
//		$this->ojdebug1($followrefs);
		$catalog_id = $this->get_catalog_id();
		$array = OJ_Entities::get_forward_links($catalog_id, $lnk['to_entities_id'], $includeX, $onlyX, $oftype);
		$ret = [];
		$omit = $this->get_omit();
		$omit1 = $omit;
		foreach ($array as $lnk)
		{
			$to = $lnk["to_entities_id"];
			if (!in_array($to, $omit1))
			{
				array_push($omit1, $to);
				$nm = stripslashes($lnk['name'] ? $lnk["name"] : OJ_Entities::get_entity_name($to));
				$lnk['name'] = $nm;
//				$link = new OJ_Link($this->get_catalog(), $lnk);
//				$link->set_omit($omit);
				if ($lnk["tooltip"])
				{
					$tt = $lnk["tooltip"];
					$lnk["tooltip"] = stripslashes($tt);
//					$link->set_tooltip(stripslashes($lnk["tooltip"]));
				}
				$ret[] = $lnk;
			}
		}
		$this->sort_links($ret);
//		$this->_number_of_children = count($this->_children);
		return $ret;
	}

	public function get_subcats($lnk, $followrefs)
	{
		$subcats = [];
		if (($lnk["type"] === 'ITEM') && $this->follow_references($lnk["type"]) && $followrefs)
		{
			$subcats = $this->get_children($lnk, $followrefs, $this->get_reference_target_types());
		}
		else
		{
			$subcats = $this->get_children($lnk, $followrefs);
		}
		return $subcats;
	}

	public function on_category_create($new_entities_id)
	{
		
	}

	public function on_group_create($new_entities_id)
	{
		
	}

	public function on_item_create($new_entities_id)
	{
		
	}

	public function on_attribute_display($entities_id, $parentid)
	{
		
	}

	public function get_cssclass($entities_id, $type)
	{
		return "";
	}

	public function get_display_column_cssclass()
	{
		return "col-xs-6 col-sm-6 col-md-6";
	}

	public function get_filter_column_cssclass()
	{
		return "col-xs-6 col-sm-6 col-md-6";
	}

	public function has_values($subcat, $followrefs)
	{
		return ($subcat["type"] !== "ITEM") || ($this->follow_references($subcat["type"]) && $followrefs);
	}

	public function treat_as_category($index, $cat)
	{
		return $this->get_sort_type($cat) === 'CATEGORY';
	}

	public function get_extra_indexes()
	{
		return [];
	}

	public function can_create_categories($index)
	{
		return true;
	}

	public function oj_action_stamp($params)
	{
		$ojhost = isset($params["ojhost"]) ? $params["ojhost"] : "1";
		if ($ojhost == 0)
		{
			$ojhost = OJ_Access::next_host();
		}
		OJ_Access::stamp($this->_username, $this->_catalogs_id, $ojhost, $this->_indexes_id);
		echo $ojhost;
	}

	public function oj_action_defatt($params)
	{
		$gnamel = $this->get_group_name();
		$inamel = $this->get_item_name();
		$cnamel = $this->get_category_name();
		$gname = $gnamel . "+" . ucfirst($gnamel) . "+" . ($this->can_create_groups($this->_indexname) ? "true" : "false");
		$iname = $inamel . "+" . ucfirst($inamel) . "+" . ($this->can_create_items($this->_indexname) ? "true" : "false");
		$cname = $cnamel . "+" . ucfirst($cnamel) . "+" . ($this->can_create_categories($this->_indexname) ? "true" : "false");
		$logical_category = OJ_Indexes::get_logical_categories_id($this->_catalogs_id, $this->_indexname);
		$prefix = $logical_category == 0 ? null : OJ_Logical_Categories::get_logical_categories_prefix($logical_category);
		$retarray = [OJ_Entities::get_default_attributes($this->_catalogs_id, "CATEGORY"),
			OJ_Entities::get_default_attributes($this->_catalogs_id, "GROUP"),
			OJ_Entities::get_default_attributes($this->_catalogs_id, "ITEM"),
			$this->get_search_excludes($this->_username, $this->_catalogname),
			OJ_HTML_Display::get_entity_select_list1($this->_username, $this->_catalogname, $this->_indexname),
			OJ_HTML_Display::get_subtype_values($this->_catalogname, "CATEGORY"),
			OJ_HTML_Display::get_subtype_values($this->_catalogname, "GROUP"),
			$iname . "|" . $gname . "|" . $cname,
			OJ_HTML_Display::get_index_list($this->_catalogname, $this->_username),
			OJ_HTML_Display::get_entity_menu_items($this->_username, $this->_catalogname, $this->_indexname),
			implode('|', OJ_Access::get_catalog_list($this->_username)),
			OJ_HTML_Display::get_sorted_logicals($prefix),
			$this->get_filter_column_cssclass() . '|' . $this->get_display_column_cssclass(),
			OJ_Roles::get_rolename_for_user($this->_username, $this->_catalogname)
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
			array_push($retarray, OJ_HTML_Display::get_entity_select_list1($this->_username, $other_catalog, $other_index));
		}
		echo json_encode($retarray);
	}

	public function oj_action_open($params)
	{
		$to = $params["ojid"];
		$name = $params["name"];
		$type = $params["type"];
//			$parent = OJ_Link::create_link_entity($catalog, $to, $name, $type);
//			$list = OJ_HTML_Display::get_subentity_select_list($parent, $type != "CATEGORY");
//			echo OJ_HTML::to_html($list);
		$catalog_list = OJ_HTML_Display::get_subentity_select_list($this->_username, $this->_catalogname, $this->_indexname, $to, $name, $type, "oj-multimedia-list-" . $to, "oj-catalog-list-class", true);
//			var_dump($catalog_list);exit;
//					get_entity_select_list($user, $this->_catalogname, $index, "oj-multimedia-list-".$to, "oj-catalog-list-class", $type != "CATEGORY");
		echo OJ_HTML::to_html($catalog_list);
//			var_dump($params);
	}

	public function oj_action_show($params)
	{
		$to = $params["ojid"];
		$name = $params["name"];
		$type = $params["type"];
		$selectable = !isset($params["selectable"]) || ($params["selectable"] == 'true');
		$followrefs = !isset($params["followrefs"]) || ($params["followrefs"] == 'true');
		echo OJ_HTML_Display::get_subentity_select_list1($this->_username, $this->_catalogname, $this->_indexname, $to, $name, $type, $selectable, $followrefs);
	}

	public function oj_action_image($params)
	{
		$ojid = $params["ojid"];
		$imgatt = isset($params["img"]) ? $params["img"] : null;
		OJ_Logger::get_logger()->ojdebug1("calling get_entity " . $ojid);
//			$entity = $catalog->get_entity($ojid);
		OJ_Logger::get_logger()->ojdebug1("calling get_attribute " . $imgatt);
//			$imgfile_logical = $entity->get_attribute($imgatt);
		$imgfile_logical = OJ_Entities::get_attribute_value($ojid, $imgatt);
		OJ_Logger::get_logger()->ojdebug1("imgfile_logical " . $imgfile_logical);
		$imgfile1 = OJ_System::instance($this->_username)->substitute_logical($imgfile_logical);
		$imgfile = $imgfile1["value"];
		OJ_Logger::get_logger()->ojdebug1("imgfile " . $imgfile);
		if (file_exists($imgfile))
		{
			$lastdot = strrpos($imgfile, '.');
			$ext = strtolower(substr($imgfile, $lastdot + 1));
			OJ_Logger::get_logger()->ojdebug1("ext " . $ext);
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

			header("Content-Type: " . $mimetype);
//				header("Content-Disposition: attachment; filename=\"myfile.zip\"");
			header("Content-Length: " . filesize($imgfile));
			header("Cache-Control: max-age=2592000");
			readfile($imgfile);
			exit();
		}
		else
		{
			OJ_Logger::get_logger()->ojdebug1("no image file found " . $imgfile);
		}
	}

	public function oj_action_audiodownload($params)
	{
//			$ojids = explode(',', $params["ojid"]);
//			echo $catalog->get_entity_dir($ojid)."<br/>";
		$format = array_key_exists("format", $params) ? $params["format"] : "flac";
		$plname = $params["name"];
		if ($format === "mpeg")
		{
			$format = "mp3";
		}
		$bits = array_key_exists("bits", $params) ? intval($params["bits"]) : 16;
		$rate = array_key_exists("rate", $params) ? intval($params["rate"]) : 48;
		$qual = array_key_exists("qual", $params) ? intval($params["qual"]) : 320;
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
		$dir = "/tmp/" . $plname;
		if (!file_exists($dir) || !is_dir($dir))
		{
			mkdir($dir);
		}
		$dirname = "/tmp/" . $plname . "/";
		$zipname = "/tmp/" . $plname . ".zip";
		foreach ($list as $item)
		{
			OJ_Logger::get_logger()->ojdebug1("download: calling get_entity " . $item->id);
//				$entity = $catalog->get_entity($item->id);
			OJ_Logger::get_logger()->ojdebug1("download: calling get_attribute " . $item->id);
//				$track1 = $entity->get_attribute ("detail", "track");
			$track1 = OJ_Entities::get_attribute_value($item->id, "detail", "track");
			OJ_Logger::get_logger()->ojdebug1("download: playing " . $item->id);
			OJ_Logger::get_logger()->ojdebug1("download: 1.looking for track " . json_encode($track1));
			//			echo $track."<br/>";
			if ($track1 != null)
			{
				//				$track1 = OJ_System::instance($this->_username)->substitute_logical($track);
				//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
				$track = stripslashes($track1->get_value());
				$lastdot = strrpos($track, ".");
				$bname = basename($track, substr($track, $lastdot));
				OJ_Logger::get_logger()->ojdebug1("download: looking for track " . $track);
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
				$tmpfile = $dirname . $bname . "." . $format;
				$err = 0;
				if (!file_exists($tmpfile))
				{
					if ($format == "mp3")
					{
						OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "' . $track . '" -C ' . $qual . ' "' . $tmpfile . '"');
						exec('/usr/bin/sox "' . $track . '" -C ' . $qual . ' "' . $tmpfile . '"', $err);
					}
					else
					{
						OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "' . $track . '" -b ' . $bits . ' -r ' . $rate . 'k "' . $tmpfile . '"');
						exec('/usr/bin/sox "' . $track . '" -b ' . $bits . ' -r ' . $rate . 'k "' . $tmpfile . '"', $err);
					}
				}
				OJ_Logger::get_logger()->ojdebug1($item->id . " ok");
			}
			else
			{
				OJ_Logger::get_logger()->ojdebug1($item->id . " not ok");
			}
		}
		OJ_File_Utilities::zip_dir($dir, $zipname);
		echo "file=" . $zipname;
	}

	public function oj_action_downloadfile($params)
	{
		$filename = $params["file"];
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename=' . basename($filename));
		header('Content-Length: ' . filesize($filename));
		readfile($filename);
	}

	public function oj_action_audiopreload($params)
	{
		$ojids = explode(',', $params["ojid"]);
//			echo $catalog->get_entity_dir($ojid)."<br/>";
		$format = array_key_exists("format", $params) ? $params["format"] : "wav";
		if ($format === "mpeg")
		{
			$format = "mp3";
		}
		$bits = array_key_exists("bits", $params) ? intval($params["bits"]) : ($format == 'mp3' ? 16 : 24);
		foreach ($ojids as $ojid)
		{
			OJ_Logger::get_logger()->ojdebug1("preload: calling get_entity " . $ojid);
//				$entity = $catalog->get_entity($ojid);
			OJ_Logger::get_logger()->ojdebug1("preload: calling get_attribute " . $ojid);
//				$track1 = $entity->get_attribute ("detail", "track");
			$track1 = OJ_Entities::get_attribute_value($ojid, "detail", "track");
			OJ_Logger::get_logger()->ojdebug1("preload: playing " . $ojid);
			OJ_Logger::get_logger()->ojdebug1("preload: 1.looking for track " . json_encode($track1));
			//			echo $track."<br/>";
			if ($track1 != null)
			{
				//				$track1 = OJ_System::instance($this->_username)->substitute_logical($track);
				//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
//					$track = stripslashes($track1->get_value());
				$track = stripslashes($track1);
				OJ_Logger::get_logger()->ojdebug1("preload: looking for track " . $track);
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
				$tmpfile = "/tmp/" . $ojid . "." . $format;
				$err = 0;
				if (!file_exists($tmpfile))
				{
					OJ_Session::set($this->_users_id, "preload", $ojid);
					OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "' . $track . '" -b ' . $bits . ' ' . $tmpfile);
					exec('/usr/bin/sox "' . $track . '" -b ' . $bits . ' ' . $tmpfile, $err);
				}
				echo $ojid . " ok";
			}
			else
			{
				echo $ojid . " not ok";
			}
		}
		OJ_Session::reset($this->_users_id, "preload");
//			echo $track;
	}

	public function oj_action_audio($params)
	{
		$ojid = $params["ojid"];
//			echo $catalog->get_entity_dir($ojid)."<br/>";
		$format = array_key_exists("format", $params) ? $params["format"] : "wav";
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
		$bits = array_key_exists("bits", $params) ? intval($params["bits"]) : ($format == 'mp3' ? 16 : 24);
		if (array_key_exists("track", $params))
		{
			$track = $params["track"];
			$useftp = false;
		}
		else
		{
			OJ_Logger::get_logger()->ojdebug1("calling get_entity " . $ojid);
//				$entity = $catalog->get_entity($ojid);
			OJ_Logger::get_logger()->ojdebug1("calling get_attribute " . $ojid);
//				$track1 = $entity->get_attribute ("detail", "track");
			$track1 = OJ_Entities::get_attribute_value($ojid, "detail", "track");
			OJ_Logger::get_logger()->ojdebug1("playing " . $ojid);
			OJ_Logger::get_logger()->ojdebug1("1.looking for track " . json_encode($track1));
//				$track = $track1 == null?null:stripslashes($track1->get_value());
//				$useftp = !file_exists($track) && ($track1["ftp"] != null);
			$track = $track1 == null ? null : stripslashes($track1);
			$useftp = false;
		}
//			echo $track."<br/>";
		if ($track != null)
		{
			$pre = OJ_Session::get($this->_users_id, "preload");
			while ($pre && $pre == $ojid)
			{
				OJ_Logger::get_logger()->ojdebug1("waiting for preload " . $ojid);
				sleep(1);
				$pre = OJ_Session::get($this->_users_id, "preload");
			}
//				$track1 = OJ_System::instance($this->_username)->substitute_logical($track);
//				OJ_Logger::get_logger()->ojdebug1("looking for track1 ".json_encode($track1));
			$tmpfile = "/tmp/" . $ojid . "." . $format;
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
				OJ_Logger::get_logger()->ojdebug1('executing /usr/bin/sox "' . $track . '" -b ' . $bits . ' ' . $tmpfile);
				exec('/usr/bin/sox "' . $track . '" -b ' . $bits . ' ' . $tmpfile, $err);
			}
			header("Content-Type: " . $mimetype);
//				header("Content-Disposition: attachment; filename=\"myfile.zip\"");
			header("Content-Length: " . filesize($tmpfile));
			header("Cache-Control: max-age=2592000");
			readfile($tmpfile);
//				passthru('/usr/bin/sox "'.$track.'" -t wav -',$err);
//				exec('/usr/bin/sox "'.$track.'" /tmp/'.$ojid.".wav",$err);
			exit();
		}
//			echo $track;
	}

	public function oj_action_link($params)
	{
		//<link direction="from" catalog="1" ordinal="0" hidden="0" other="6311">test1</link>
		$sourceid = $params["source"];
		$destid = $params["destination"];
		$name = isset($params["name"]) ? $params["name"] : OJ_Entities::get_entity_name($destid);
//			$catalogs_id = $catalog->get_id();
		$ord = isset($params["ordinal"]) ? $params["ordinal"] : 0;
		$xml = '<link direction="from" catalog="' . $this->_catalogs_id . '" ordinal="' . $ord . '" hidden="0" other="' . $destid . '">' . $name . '</link>';
		echo OJ_Links::from_xml($this->_catalogs_id, $sourceid, $xml);
	}

	public function oj_action_move($params)
	{
		$ret = "no";
		$fromid = $params["from"];
		$toid = $params["to"];
		$ojid = $params["ojid"];
//			$catalogs_id = $catalog->get_id();
		$lnk = OJ_Row::load_single_object("OJ_Links", ["from_entities_id" => $fromid, "to_entities_id" => $ojid, "catalogs_id" => $this->_catalogs_id]);
		if ($lnk && ($lnk->id > 0))
		{
			$lnk->from_entities_id = $toid;
			$lnk->save();
			$ret = "yes";
		}
		echo $ret;
	}

	public function oj_action_unlink($params)
	{
		$fromid = $params["from"];
		$ojid = $params["ojid"];
//			$catalogs_id = $catalog->get_id();
		$lnksto = OJ_Links::get_all_links_to($this->_catalogs_id, $ojid);
		if (count($lnksto === 1))
		{
			if ($lnksto[0] . from_entities_id == $fromid)
			{
				OJ_Entities::tree_delete($lnk->to_entities_id, $this->_catalogs_id);
			}
		}
		else
		{
			foreach ($lnksto as $lnk)
			{
				if ($lnk . from_entities_id == $fromid)
				{
					$lnk->delete();
					break;
				}
			}
		}
	}

	public function oj_action_create($params)
	{
//			var_dump($_SERVER);exit;
		$eid = 0;
		if (isset($params["xml"]))
		{
			$xml = $params["xml"];
//				echo $xml;$eid = 0;
			$ent = OJ_Entities::from_xml_string($params["xml"]);
			$eid = $ent == null ? 0 : $ent->id;
			$type = $ent == null ? null : OJ_Entity_Types::get_entity_type_name($ent->entity_types_id);
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
			$this->on_entity_create($type, $eid);
		}
		echo $eid;
	}

	public function oj_action_attributes($params)
	{
		$ojid = $params["ojid"];
		$parent = $params["parent"];
		OJ_Entities::note_accessed($ojid);
		$editable = isset($params["editable"]) && ($params["editable"] === "true");
		echo OJ_HTML_Display::get_display_panel_contents($this->_username, $this->_catalogname, $this->_indexname, $ojid, $editable);
		$this->on_attribute_display($ojid, $parent);
	}

	public function oj_action_aka($params)
	{
		$ret = 0;
		if (isset($params["name"]) && isset($params["ojid"]))
		{
			$table_name = isset($params["tablename"]) ? $params["tablename"] : "entities";
			$ret = OJ_Aka::add_aka($params["name"], $params["ojid"], $table_name);
		}
		echo $ret;
	}

	public function oj_action_imageurl($params)
	{
		$ret = "";
//			OJ_Logger::get_logger()->ojdebug1("imageurl");
		if (isset($params["ojid"]))
		{
//				$ent = $catalog->get_entity($params["ojid"]);
			$ojid = $params["ojid"];
			$atts = OJ_Entities::get_attributes($ojid, $this->_catalogs_id);
//				$entitytypeid = OJ_Entities::get_entity_types_id($ojid);
//				OJ_Logger::get_logger()->ojdebug("entity ".$params["ojid"], $ent);
			if ($atts && isset($atts["images"]))
			{
//					$pg = $ent->get_page("images");
//					$pg = OJ_Pages::get_page($this->_catalogs_id, $entitytypeid, "images");
//					OJ_Logger::get_logger()->ojdebug("page", $pg);
//					if ($pg)
//					{
//					$imgs = $pg->get_as_hash();
				$imgs = $atts["images"];
				$imgarr = array_keys($imgs);
				if (count($imgarr) > 0)
				{
					$olduri = OJ_HTML::split_uri($_SERVER["REQUEST_URI"]);
					$ret = "http://" . $_SERVER["SERVER_NAME"] . $olduri['path'] . '?';
					$ret .= "user=" . $this->_username . "&catalog=" . $this->_catalogname . "&action=image&ojid=" . $params["ojid"] . "&img=" . urlencode("images::" . $imgarr[0]);
//							OJ_Logger::get_logger()->ojdebug1("image ".$ret);
				}
//					}
			}
		}
		echo $ret;
	}

	public function oj_action_search($params)
	{
		$crit = $params["search"];
		$found = OJ_Row::load_column("entities", "id", ["catalogs_id" => $this->_catalogs_id, "name%~%" => $crit]);
		OJ_Logger::get_logger()->ojdebug1("search found " . count($found));
//			$root = $catalog->get_root($this->_indexname);
		$root = OJ_Catalogs::get_root_id($this->_catalogs_id, $this->_indexname);
		$exclude = $this->get_search_excludes($this->_username, $this->_catalogname);
//			var_dump($found);
		$paths = ["paths" => [], "entities" => []];
		foreach ($found as $ojid)
		{
			$paths1 = OJ_Links::get_paths_to($this->_catalogs_id, $ojid, $root, $exclude);
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
	}

	public function oj_action_categories($params)
	{
		$prefix = isset($params["prefix"]) ? $params["prefix"] : "";
		$include_root = isset($params["root"]) && strtolower($params["root"]) === 'true';
		$selcat = OJ_HTML_Display::get_category_select_list($this->_username, strtolower($this->_catalogname), $this->_indexname, $prefix, $include_root);
		echo OJ_HTML::to_html($selcat);
	}

	public function oj_action_groups($params)
	{
		$prefix = isset($params["prefix"]) ? $params["prefix"] : "";
		$include_root = isset($params["root"]) && strtolower($params["root"]) === 'true';
//			$selgrp = OJ_HTML_Display::get_group_select_list($this->_username, strtolower($this->_catalogname), $this->_indexname, $prefix, $include_root);
		$selgrp = OJ_HTML_Display::get_group_select_list1($this->_username, $this->_catalogname, $this->_indexname);
//			echo OJ_HTML::to_html($selgrp);
		echo $selgrp;
	}

	public function oj_action_rename($params)
	{
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
			$link = OJ_Row::load_single_object("OJ_Links", ["from_entities_id" => $parentid, "to_entities_id" => $ojid]);
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
	}

	public function oj_action_newentityattributes($params)
	{
		$entity_types_id = $params["etype"];
		$pages = null;
		if (array_key_exists("pages", $params))
		{
			$pages = explode(',', $params["pages"]);
		}
		$pages = $this->get_new_entity_pages($this->_indexname, $entity_types_id);
		echo OJ_HTML_Display::get_new_entity_panel($this->_username, $this->_catalogname, $entity_types_id, $pages);
	}

	public function oj_action_treedelete($params)
	{
		$entities_id = $params["ojid"];
		OJ_Entities::tree_delete($entities_id, $this->_catalogs_id);
		echo "ok";
	}

	public function oj_action_makenote($params)
	{
		$ncd = OJ_Catalog_Display::get_catalog_display($this->_username, "Notes");
		$entities_id = $params["ojid"];
		$note = $params["note"];
		$title = isset($params["title"]) ? $params["title"] : null;
		echo $ncd->make_note($this->_catalogs_id, $entities_id, $note, $title);
	}

	public function oj_action_gettitle($params)
	{
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
					$title = basename($pth, '.' . $info['extension']);
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
	}

	public function oj_action_anymessages($params)
	{
//			var_dump($_SERVER);
		$dirname = dirname($_SERVER["SCRIPT_FILENAME"]);
		$msgs = scandir($dirname . DIRECTORY_SEPARATOR . "messages");
//			var_dump($msgs);
		if (count($msgs) > 2)
		{
//				header('Content-Type: text/plain');
//				header('Content-disposition: attachment; filename='.basename($msgs[2]));
//				header('Content-Length: ' . filesize($msgs[2]));
			readfile($dirname . DIRECTORY_SEPARATOR . "messages" . DIRECTORY_SEPARATOR . $msgs[2]);
		}
		else
		{
			echo "zilch";
		}
	}

	public function oj_action_parameters($params)
	{
		$parameters = OJ_Parameters::get_all_parameters($this->_catalogs_id);
		$pms = [];
		foreach ($parameters as $pname => $p)
		{
			$pms[$pname] = ["type" => $p->table_name, "value" => $p->get_value()];
		}
		echo json_encode($pms);
	}

	public function oj_action_setparameter($params)
	{
		$pname = $params["name"];
		$ptype = $params["type"];
		$pval = $params["value"];
		$p = OJ_Row::load_single_object("OJ_Parameters", ["name" => $pname, "catalogs_id" => $this->_catalogs_id]);
		if ($p == null)
		{
			$p = new OJ_Parameters(["name" => $pname, "catalogs_id" => $this->_catalogs_id, "table_name" => $ptype]);
		}
		$p->set_value($pval);
		$p->save();
		echo "ok";
	}

	public function oj_action_newlogicalcategory($params)
	{
		$lcname = $params["name"];
		$existing = OJ_Row::load_single_object("OJ_Logical_Categories", ["name" => $lcname]);
		if ($existing)
		{
			echo $existing->id;
		}
		else
		{
			$lc = new OJ_Logical_Categories(["name" => $lcname]);
			echo $lc->save();
		}
	}

	public function oj_action_newftp($params)
	{
		$site = $params["site"];
		$ftpuser = $params["ftpuser"];
		$pword = $params["password"];
		$existing = OJ_Row::load_single_object("OJ_Logical_Categories", ["name" => $site, "user" => $ftpuser]);
		if ($existing)
		{
			echo $existing->id;
		}
		else
		{
			$ftp = new OJ_Ftp(["name" => $site, "user" => $ftpuser, "pword" => OJ_Utilities::encrypt($pword)]);
			echo $ftp->save();
		}
	}

	public function oj_action_getlogicalcategories($params)
	{
		echo json_encode(OJ_Row::load_column("logical_categories", "name"));
	}

}

class OJ_Default_Catalog_Display extends OJ_Catalog_Display
{
	
}

