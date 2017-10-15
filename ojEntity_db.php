<?php
require_once("oj_base_classes.php");
require_once("project.php");
require_once("OJDatabase.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OJ_Typed_Collection
{
	private $_collection;
	private $_catalog;
	
	public function __construct($catalog)
	{
		$this->_catalog = $catalog;
		$this->_collection = ["all"=>[], "item"=>[], "group"=>[], "category"=>[]];
	}
	
	public function put($type, $value)
	{
		$t = strtolower($type);
		if (array_key_exists($t, $this->_collection))
		{
			array_push($this->_collection[$t], $value);
		}
		array_push($this->_collection["all"], $value);
	}
	
	public function get($type = null)
	{
		$ret = null;
		if ($type === null)
		{
			$ret = $this->_collection["all"];
		}
		else
		{
			$t = strtolower($type);
			if (array_key_exists($t, $this->_collection))
			{
				$ret = $this->_collection[$t];
			}
		}
		return $ret;
	}
	
	public function count($type = null)
	{
		$ret = 0;
		if ($type === null)
		{
			$ret = count($this->_collection["all"]);
		}
		else
		{
			$t = strtolower($type);
			if (array_key_exists($t, $this->_collection))
			{
				$ret = count($this->_collection[$t]);
			}
		}
		return $ret;
	}
	
	public function sort($catalog_sort_function_name)
	{
		if (method_exists( $this->_catalog , $catalog_sort_function_name ))
		{
			foreach ($this->_collection as $t=>$link_array)
			{
				$this->_catalog->$catalog_sort_function_name($this->_collection[$t]);
			}
		}
	}
}

class OJ_Set_Object extends OJ_Object
{
	
	protected $_value;
	
	public function __construct($xml)
	{
		parent::__construct();
		if (isset($xml))
		{
			foreach ($xml as $k => $v)
			{
				$this->_data[strval($k)] = $v;
			}
		}
	}
	
	public function set_value($val)
	{
		$this->_value = $val;
	}
	
	public function get_value()
	{
		return $this->_value;
	}
		
	public function __toString()
	{
		return strval($this->get_value());
	}
	
	public function save($filename)
	{
	}
	
}

class OJ_Catalog_Object extends OJ_Set_Object
{
	
	protected $_catalog;
	
	public function __construct($catalog, $xml)
	{
		parent::__construct($xml);
//		if ($catalog)
//		{
//			$catalog->ojdebug("xml", $xml);
//			$catalog->ojdebug("data", $this->_data);
//		}
		$this->_catalog = $catalog;
		
	}
	
	public function get_catalog()
	{
		return $this->_catalog;
	}
	
	public function get_catalog_name()
	{
		return $this->_catalog->get_name();
	}
	
	public function ojdebug(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
	
	public function ojdebug1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
	
	// eventually can be removed.
	public function to_xml_element()
	{
		
	}
}

abstract class OJ_Attribute_Type extends BasicEnum
{
	const string = 0;
	const integer = 1;
	const enumeration = 2;
	const boolean = 3;
	const date = 4;
	const time = 5;
	const datetime = 6;
	const file = 7;
	const audio = 8;
	const video = 9;
	const url = 10;
	const xmldatabasekey = 11;
	const email = 12;
	const password = 13;
	const reference = 14;
}

class OJ_Attribute extends OJ_Catalog_Object
{
	public $_page;
	public $_ojid;
	public $_visible;
	private $_prompt;
	
	public function __construct($catalog, $xml)
	{
		parent::__construct($catalog, $xml);
		$this->_visible = true;
	}
	
	public function set_value($val)
	{
		$this->_value = $val;
	}
	
	public function get_value()
	{
		if (($this->_value === null) && isset($this->_data["value"]))
		{
			$this->_value = $this->_data["value"];
		}
		return $this->_value;
	}
	
	public function get_page()
	{
		return $this->_page;
	}
	
	public function set_page($page)
	{
		$this->_page = $page;
	}
	
	public function set_ojid($ojid)
	{
		$this->_ojid = $ojid;
	}
	
	public function get_ojid()
	{
		return $this->_ojid;
	}
	
	public function is_visible()
	{
		return $this->_visible;
	}
	
	public function set_visible($vis)
	{
		$this->_visible = $vis;
	}
	
	public function get_fullname()
	{
		$pg = $this->get_page();
		if ($pg === null)
		{
			$ret = $this->name;
		}
		else
		{
			$ret = $pg."::".$this->name;
		}
		return $ret;
	}
	
	public function get_prompt()
	{
		$ret = $this->_prompt;
		if ($ret === null)
		{
			$ret = $this->_data['name'];
		}
		return $ret;
	}
	
	public function set_prompt($prompt)
	{
		$this->_prompt = $prompt;
	}
	
	public function to_hash()
	{
		return ['name'=>$this->_data['name'], "type"=>$this->_data['type'], "ordinal"=>$this->_data['ordinal'], "visible"=>($this->_data['visible']?1:0),
			"value"=>$this->_data('value')];
	}
	
}

class OJ_Page extends OJ_Catalog_Object
{
	
	private $_visible = true;
	private $_visible_attributes;
	private $_count_visible_attributes;
		
	public function __construct($catalog, $xml)
	{
		parent::__construct($catalog, $xml);
		$this->_visible_attributes = [];
		$this->_count_visible_attributes = 0;
		foreach ($xml as $attname => $att) {
			if ($att->is_visible())
			{
				$this->_visible_attributes[$attname] = $att;
				$this->_count_visible_attributes++;
			}
		}
	}
	
	public function is_visible()
	{
		return $this->_visible;
	}
	
	public function set_visible($vis)
	{
		$this->_visible = $vis;
	}
	
	public function get_attribute_iterator()
	{
		return new OJ_Data_Iterator($this->_data, "OJ_Attribute");
	}
	
	public function get_visible_attributes()
	{
		return $this->_visible_attributes;
	}
	
	public function get_number_of_visible_attributes()
	{
		return $this->_count_visible_attributes;
	}
	
	public function get_an_attribute()
	{
		$ret = null;
		$keys = array_keys($this->_visible_attributes);
		if (count($keys) > 0)
		{
			$ret = $this->_visible_attributes[$keys[0]];
		}
		return $ret;
	}
	
	public function get_attribute($attname)
	{
		$ret = null;
		if (array_key_exists($attname, $this->_visible_attributes))
		{
			$ret = $this->_visible_attributes[$attname];
		}
		return $ret;
	}
	
}

class OJ_Attribute_Collection extends OJ_Catalog_Object
{
	
	public $_visible_pages;
	public $_count_visible_pages;
	
	public function __construct($catalog, $xml)
	{
		parent::__construct($catalog, $xml);
		$this->_visible_pages = [];
		$this->_count_visible_pages = 0;
		foreach ($xml as $pagename => $page) {
//			$this->ojdebug("attribute collection", $page, $pagename, $page->is_visible());
			if ($page->is_visible())
			{
				$this->_visible_pages[$pagename] = $page;
				$this->_count_visible_pages++;
			}
		}
	}
	
	public function get_visible_pages()
	{
		return $this->_visible_pages;
	}
	
	public function get_number_of_visible_pages()
	{
		return $this->_count_visible_pages;
	}
	
	public function get_page($pagename)
	{
		$ret = null;
		if (array_key_exists($pagename, $this->_visible_pages))
		{
			$ret = $this->_visible_pages[$pagename];
		}
		return $ret;
	}
	
}

class OJ_Property extends OJ_Catalog_Object
{
	
	public $_ojid;
	private $_prompt;
	
	public function get_page()
	{
		return "properties";
	}
	
	public function set_ojid($ojid)
	{
		$this->_ojid = $ojid;
	}
	
	public function get_ojid()
	{
		return $this->_ojid;
	}
	
	public function get_prompt()
	{
		$ret = $this->_prompt;
		if ($ret === null)
		{
			$ret = $this->_data['name'];
		}
		return $ret;
	}
	
	public function set_prompt($prompt)
	{
		$this->_prompt = $prompt;
	}
	
}

class OJ_PropertySet extends OJ_Catalog_Object
{
	
}

class OJ_Parameter extends OJ_Catalog_Object
{
	public static function get_default_param()
	{
		$t = strval(OJ_Utilities::current_time_millis());
		return ["editable" => "true", "encrypted" => "false", "page" => "default", "lastAccessed" => $t, "lastModified" => $t];
	}
	
	public function set_value($val)
	{
		switch (strval($this->_data["type"]))
		{
			case 'integer':
			case 'boolean':
				$this->_data["value"] = strval($val);
				break;
			default:
				$this->_data["value"] = $val;
				break;
		}
	}
	
	public function get_value()
	{
		$val = strval($this->_data["value"]);
		switch ($this->_data["type"])
		{
			case 'integer':
				$ret = intval($val);
				break;
			case 'boolean':
				$ret = strtolower($val) == 'true';
				break;
			default:
				$ret = $val;
				break;
		}
		return $ret;
	}
	
	public function __toString()
	{
		return $this->_data["value"];
	}
	
}

class OJ_Link extends OJ_Catalog_Object
{
	
	public static function create_link_entity($catalog, $to, $name, $type, $hidden = "false", $omit = [], $tooltip = null)
	{
		$hash = ["to"=>$to, "name"=>$name, "type"=>$type, "hidden"=>$hidden, 'omit'=>$omit, "catalog"=>OJ_Entities::get_catalogs_id($to)];
		$ret = new OJ_Link($catalog, $hash);
		if ($tooltip != null)
		{
			$ret->set_tooltip($tooltip);
		}
		return $ret;
	}
	
	private $_parents;
	private $_number_of_parents = -1;
	private $_children;
	private $_number_of_children = -1;
	private $_sortname;
	private $_sorttype;
	private $_destination_catalog;
	private $_name_set = false;
	private $_tooltip;
	
	public function get_tooltip()
	{
		if ($this->_tooltip == null)
		{
			$ret = $this->_data["to"].": ".$this->_data["name"];
		}
		else
		{
			$ret = $this->_tooltip;
		}
		return $ret;
	}
	
	public function set_tooltip($tt)
	{
		$this->_tooltip = $tt;
	}
	
	public function get_sort_name()
	{
		if ($this->_sortname == null)
		{
			$this->_sortname = $this->get_catalog()->get_the_sort_name(strval($this->_data['type']), strval($this->get_name()));
		}
		return $this->_sortname;
	}
	
	public function get_name()
	{
		if (!$this->_name_set)
		{
			$this->_name_set = true;
			if (($this->_data['name'] == null) || $this->is_reference())
			{
				$ent = new OJ_Entities($this->_data['to']);
				$this->_data['name'] = stripslashes($ent->get_name());
			}
		}
//		$this->ojdebug("get_name returning ".$this->_data['name']." is_reference ".(($this->is_reference())?"yes":"no")." from catalog ".($this->get_catalog()->get_id()), $this);
		return (string) $this->_data['name'];
	}
	
	public function set_value($val)
	{
		$this->_value = $val;
	}
	
	public function get_value()
	{
		if ($this->_value == null)
		{
			$this->_value = $this->get_catalog()->get_entity($this->_data['to']);
		}
		return $this->_value;
	}
	
	public function get_omit()
	{
		return array_key_exists("omit", $this->_data)?$this->_data["omit"]:array();
	}
	
	public function set_omit($omit)
	{
		$this->_data["omit"] = $omit;
	}
	
	public function set_omit_from($root)
	{
		$this->_data["omit"] = $root == null?array():$root->get_omit();
	}
	
	protected function load_parents()
	{
		$catalog_id = $this->get_catalog()->get_id();
		$array = OJ_Entities::get_backward_links($catalog_id, $this->_data['from']);
		$this->_parents = new OJ_Typed_Collection();
		foreach ($array as $lnk)
		{
			$to = $lnk["to"];
			$omit = $this->get_omit();
			if (!in_array($to, $omit))
			{
				$link = new OJ_Link($this->get_catalog(), $lnk);
				$link->set_omit($omit);
				$this->_parents->put($link->type, $link);
			}
		}
		$this->_parents->sort("sort_links");
		$this->_number_of_parents = $this->_parents->count();
	}
	
	public function get_parents($type = null)
	{
		if ($this->_parents == null)
		{
			$this->load_parents();
		}
		$ret = $this->_parents->get($type);
//		$this->get_catalog()->sort_links($ret);
	}
	
	public function get_number_of_parents()
	{
		if ($this->_number_of_parents < 0)
		{
			$this->load_parents();
		}
		return $this->_number_of_parents;
	}
	
	public function save_parents()
	{
//		$bwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["from"]))."backward.xml";
//		$xmldoc = new DOMDocument("1.0", "UTF-8");
//		$xmldoc->preserveWhiteSpace = FALSE; 
//		$xmldoc->formatOutput = true;
//		$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
//		$parents = $this->get_parents()->get();
//		foreach ($parents as $p)
//		{
//			$top->appendChild($p->to_xml_element($xmldoc));
//		}
//		$xmldoc->appendChild($top);
//		$this->ojdebug1("writing backward links to ".$bwdfile);
//		$xmldoc->save($bwdfile);
	}
	
	public function get_comparison_value($page, $attname)
	{
        $ret = null;
		$pgid = OJ_Pages::get_page_id($page);
		$att = OJ_Row::load_single_object("OJ_Attributes", ["pages_id"=>$pgid, "name"=>$attname, "entities_id"=>$this->_data["to"]]);
		if ($att != null)
		{
			$ret = $att->get_comparison_value();
		}
        return $ret;
	}
	
	protected function load_children()
	{
		$followrefs = $this->get_catalog()->follow_references();
		$includeX = false;
		$onlyX = false;
		if (in_array($this->_data['type'], $followrefs))
		{
			$includeX = true;
			if ($this->_data['type'] === 'ITEM')
			{
				$onlyX = true;
			}
		}
//		$this->ojdebug1("load children ".$this->_data['to']." ".($includeX?"true":"false")." ".($onlyX?"true":"false"));
//		$this->ojdebug1($followrefs);
		$catalog_id = $this->get_catalog()->get_id();
		$array = OJ_Entities::get_forward_links($catalog_id, $this->_data['to'], $includeX, $onlyX);
		$this->_children = [];
		$omit = $this->get_omit();
		$omit1 = $omit;
		foreach ($array as $lnk)
		{
			$to = $lnk["to"];
			if (!in_array($to, $omit1))
			{
				array_push($omit1, $to);
				$nm = stripslashes($lnk['name']);
				$lnk['name'] = $nm;
				$link = new OJ_Link($this->get_catalog(), $lnk);
				$link->set_omit($omit);
				if ($lnk["tooltip"])
				{
					$link->set_tooltip(stripslashes($lnk["tooltip"]));
				}
				$this->_children[] = $link;
			}
		}
		$this->get_catalog()->sort_links($this->_children);
		$this->_number_of_children = count($this->_children);
	}
	
	public function get_children($oftype = null)
	{
		if ($this->_children == null)
		{
			$this->load_children();
		}
		$ret = $this->_children;
		if ($oftype != null)
		{
			$ret = [];
			if (is_string($oftype))
			{
				$oft = strtoupper($oftype);
				foreach ($this->_children as $child)
				{
					if ($child->type === $oft)
					{
						$ret[] = $child;
					}
				}
			}
			elseif (is_array($oftype))
			{
				foreach ($this->_children as $child)
				{
					if (in_array($child->type, $oftype))
					{
						$ret[] = $child;
					}
				}
			}
		}
		return $ret;
	}
	
	public function get_number_of_children()
	{
		if ($this->_number_of_children < 0)
		{
			$this->load_children();
		}
		return $this->_number_of_children;
	}
	
	public function has_children()
	{
		return $this->get_number_of_children() > 0;
	}
	
	public function save_children()
	{
//		$fwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["to"]))."forward.xml";
//		if ($this->has_children())
//		{
//			$xmldoc = new DOMDocument("1.0", "UTF-8");
//			$xmldoc->preserveWhiteSpace = FALSE; 
//			$xmldoc->formatOutput = true;
//			$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
//			$children = $this->get_children();
//			foreach ($children as $child)
//			{
//				$top->appendChild($child->to_xml_element($xmldoc));
//			}
//			$xmldoc->appendChild($top);
//			$this->ojdebug1("writing forward links to ".$fwdfile);
//			$xmldoc->save($fwdfile);
//		}
//		elseif (file_exists($fwdfile) && is_writable($fwdfile))
//		{
//			unlink($fwdfile);
//		}
	}
	
	public function get_destination_id()
	{
		return intval($this->_data["to"]);
	}
	
	public function get_destination_property($property_name)
	{
		return $this->get_catalog()->get_entity_property(intval($this->_data["to"]), $property_name);
	}
	
	public function get_destination_catalog_id()
	{
		return intval($this->_data["catalog"]);
	}
	
	public function get_destination_catalog_name()
	{
		if ($this->_destination_catalog == null)
		{
			$this->_destination_catalog = OJ_Catalogs::get_catalog($this->get_destination_catalog_id());
		}
		return $this->_destination_catalog->get_name();
	}
	
	public function is_reference()
	{
		return intval($this->get_catalog()->get_id()) !== $this->get_destination_catalog_id();
	}
	
	public function get_sort_type()
	{
		if ($this->_sorttype == null)
		{
			$this->_sorttype = strtoupper(strval($this->_data['type']));
//			$this->ojdebug1("1.sort subtype ".$this->get_catalog()->get_name());
			$cname = strtolower($this->get_catalog()->get_name());
			if ((('multimedia' === $cname) || ('artists' === $cname)) && ('CATEGORY' == $this->_sorttype))
			{
				$subtype = $this->get_destination_property("subtype");
//				$this->ojdebug1("sort subtype ".json_encode($subtype));
//				var_dump($subtype);
				if ('audio_boxset' == $subtype)
				{
					$this->_sorttype = 'GROUP';
				}
			}
		}
		return $this->_sorttype;
	}
	
}

class OJ_Index extends OJ_Link
{
	
}

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
		$userroot  = self::$_sysroot.$username."/";
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
		$this->_logger = OJ_Logger::get_logger(self::$_sysroot."logs");
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
	
	public function get_catalog($catalogname)
	{
		if (!isset($this->_catalogs[$catalogname]))
		{
			$this->_catalogs[$catalogname] = new OJ_Catalog($this->_username, $catalogname);
		}
		return $this->_catalogs[$catalogname];
	}
	
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
				$this->_logicals[$lname] = ["value"=>$lval.'/', "isalternative"=>$isalternative, "ftpid"=>$ftpid];
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
		$ret = ["value"=>$str, "original"=>$str, "isalternative"=>false, "ftp"=>null];
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
						$ret["value"] = str_replace('//', '/', $part1.$part2.$part3);
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

interface ChangeListener
{
	public function change_has_occurred($param);
}

class OJ_Catalog extends OJ_Index
{
	
	public static function get_search_excludes($username, $catalog)
	{
		switch ($catalog)
		{
			case "multimedia":
			case "Multimedia":
				$ret = [OJ_Audio_Utilities::get_playlist_id($username), OJ_Audio_Utilities::get_favorites_id($username)];
				break;
			default:
			$ret = [];
		}
		return $ret;
	}
	
	private $_system;
	private $_entities_by_id;
	private $_entities_by_name;
	private $_indices;
	private $_change_listeners;
	private $_parameters;
	private $_ignorethe;
	private $_comparison_type;
	private $_follow_references;
	private $_default_attributes;
	private $_default_properties;
	private $_db_catalog;
	
	public function __construct($username, $catalogname)
	{
		parent::__construct(null, array("hidden"=>"false", "name"=>$catalogname, "ordinal"=>"0", "to"=>0, "type"=>"CATEGORY"));
		$this->_system = OJ_System::instance($username);
		$this->_entities_by_id = array();
		$this->_entities_by_name = array();
		$this->_change_listeners = array();
		$this->_catalog = null;
		$this->_follow_references = null;
		$this->_default_attributes = array();
		$this->_default_properties = array();
		$this->_db_catalog = OJ_Catalogs::get_catalog($catalogname);
		$this->_data["to"] = OJ_Catalogs::get_root_id($this->_db_catalog->id);
		$this->_data['catalog'] = $this->_db_catalog->id;
		$this->ojdebug1("loaded root ".$this->_data["to"]." for catalog ".$this->_db_catalog->id);
	}
	
	public function get_root_id()
	{
		return $this->_data["to"];
	}
	
	public function get_id()
	{
		return $this->_db_catalog->id;
	}
	
	public function get_catalog()
	{
		return $this;
	}
	
	public function get_username()
	{
		return $this->_system->get_username();
	}
	
	public function add_change_listener($key, $change_listener)
	{
		if (!isset($this->_change_listeners[$key]))
		{
			$this->_change_listeners[$key] = array();
		}
		array_push($this->_change_listeners[$key], $change_listener);
	}
	
	public function notify_change($key, $param)
	{
		if (!isset($this->_change_listeners[$key]))
		{
			foreach ($this->_change_listeners[$key] as $change_listener)
			{
				$change_listener->change_has_occurred($param);
			}
		}
	}
	
//	public function get_catalogroot()
//	{
//		return $this->_system->get_catalogroot($this->_data["name"]);
//	}
//	
//	public function get_entityroot()
//	{
//		return $this->_system->get_entityroot($this->_data["name"]);
//	}
//	
//	public function dbkey_to_filename($dbkey)
//	{
//		return $this->_system->get_dbroot().substr($dbkey, 7);
//	}
//	
	public function get_default_attributes($type)
	{
		$ltype = strtolower($type);
		if (!array_key_exists($ltype, $this->_default_attributes))
		{
			$this->_default_attributes[$ltype] = OJ_Entities::get_default_attributes($this->get_id(), $type);
			$this->ojdebug1("loaded default attributes for ".$type);
//			$this->save_attributes();
//			echo $attfile."<br/>";
		}
		return $this->_default_attributes[$ltype];
	}
	
	public function load_default_properties($type)
	{
		$catalog_id = OJ_Catalogs::get_catalog_id($this->get_catalog());
		$entity_types_id = OJ_Entity_Types::get_entity_type_id($type);
		$ret = OJ_Entities::get_default_properties($catalog_id, $entity_types_id);
		return $ret;
	}
	
	public function create_entity($name, $type, $parents, $linkname = null, $with_attributes = null, $with_properties = null)
	{
		if ($linkname == null)
		{
			$linkname = $name;
		}
		// 1. create entity and save
		$entity_types_id = OJ_Entity_Types::get_entity_type_id($type);
		$catalogs_id = $this->get_id();
		$entity = new OJ_Entities(["name"=>$name, "catalogs_id"=> $catalogs_id, "entity_types_id"=>$entity_types_id]);
		$entities_id = $entity->save();
		// 2. load default attributes for type
		// 2.1 load pages
		$pages = OJ_Row::load_hash_of_all_objects("OJ_Pages", ["catalogs_id"=>$catalogs_id, "entity_types_id"=>$entity_types_id], "name");
		$allatributes = [];
		foreach ($pages as $pname=>$page)
		{
			// 2.2 load default attributes for that page
			$defatts = OJ_Row::load_hash_of_all_objects("OJ_Attributes", ["pages_id"=>$page->id, "entities_id"=>0], "name");
			foreach ($defatts as $aname=>$defatt)
			{
			// 3. foreach attribute assign value and create
				$newatt = new OJ_Attributes($defatt->get_as_hash("id"));
				$newatt->entities_id = $entities_id;
				if (($with_attributes != null) && array_key_exists($pname, $with_attributes))
				{
					$withpage = $with_attributes[$pname];
					if (array_key_exists($aname, $withpage))
					{
						$val = $withpage[$aname];
						$atype = new OJ_Attribute_Types($newatt->attribute_types_id);
						$classname = "OJ_".ucfirst($atype->table_name)."_Values";
						if (class_exists($classname))
						{
							$value = new $classname(["value"=>$val]);
							$valid = $value->save();
							$newatt->values_id = $valid;
						}
						unset($withpage[$aname]);
					}
				}
				$newatt->save();
			}
		}
		// 3.1 any new attributes on existing pages
		if ($with_attributes != null)
		{
			$atype = OJ_Attribute_Types::get_attribute_type_id("string");
			foreach ($with_attributes as $withpagename=>$withpage)
			{
				if (array_key_exists($withpagename, $pages))
				{
					$pgid = OJ_Pages::get_page_id($withpagename);
					foreach ($withpage as $withattname=>$val)
					{
						$newatt = new OJ_Attributes(["pages_id"=>$pgid, "attribute_types_id"=>$atype,"entities_id" => $entities_id]);
						$value = new OJ_String_Values(["value"=>$val]);
						$valid = $value->save();
						$newatt->values_id = $valid;
						$newatt->save();
					}
				}
			}
		}
		// 4. load default properties for type
		// 5. foreach property assign value and create
		OJ_Entities::set_property($entities_id, "name", $name);
		OJ_Entities::set_property($entities_id, "type", $type);
		if ($with_properties != null)
		{
			foreach ($with_properties as $pn => $pv)
			{
				OJ_Entities::set_property($entities_id, $pn, $pv);
			}
		}
		// 6. create link from parent
		$pids = [];
		if (is_array($parents))
		{
			foreach ($parents as $pnt)
			{
				$pids[] = OJ_Entities::get_entity_id($pnt);
			}
		}
		else
		{
			$pids[] = OJ_Entities::get_entity_id($parents);
		}
		$this->link($pids, $entities_id, $linkname);
		return $entities_id;
		// 7. create references
	}
	
	public function get_entity_property($ojid, $propname = "name")
	{
		return OJ_Entities::get_property_value($ojid, $propname, true);
	}
	
	public function get_name()
	{
		return $this->_data["name"];
	}
	
	public function get_entity($obj)
	{
		$ret = null;
		if ($obj instanceof OJ_Entity)
		{
			$ret = $obj;
		}
		else
		{
			if ($obj instanceof OJ_Link)
			{
				$ojid = $obj->get_destination_id();
			}
			else
			{
				$ojid = intval($obj);
			}
			$this->ojdebug1("get_entity ojid ".$this->_system->get_username()." ".$this->_data["name"]." ".$ojid);
			if (!isset($this->_entities_by_id[$ojid]))
			{
				$this->_entities_by_id[$ojid] = new OJ_Entity($this->_system->get_username(), $this->_data["name"], $ojid);
			}
			$ret = $this->_entities_by_id[$ojid];
		}
		return $ret;
	}
	
//	public function get_entity_dir($ojid)
//	{
//		$hex = dechex($ojid);
//		$er = $this->get_entityroot();
//		if (strlen($hex) == 1)
//		{
//			$er .= "0/";
//		}
//		else
//		{
//			for ($n = 0; $n < strlen($hex) - 1; $n++)
//			{
//				$er .= $hex[$n]."/";
//			}
//		}
//		return realpath($er."oj".$hex)."/";
//	}
	
	public function load_parameters()
	{
//		echo "getting parameters</br>";
		return OJ_Catalogs::get_parameters_hash($this->get_id());
	}
	
	private function get_parameters()
	{
		return $this->load_parameters();
	}
	
	public function get_parameter($pname)
	{
//		$params = $this->get_parameters();
//		var_dump($params);
		return OJ_Catalogs::get_parameter($this->get_id(), $pname);
	}
	
	public function get_parameter_value($pname)
	{
//		$param = $this->get_parameter($pname);
//		var_dump($param);
		return OJ_Catalogs::get_parameter_value($this->get_id(), $pname);
	}
	
	public function set_parameter_value($pname, $val, $ptype = "string")
	{
		$params = $this->get_parameters();
		if (array_key_exists($pname, $params))
		{
			$params[$pname]['value'] = $val;
			$pram = OJ_Parameters::get_parameter($this->get_id(), $pname);
			if ($pram != null)
			{
				$pram->set_value($val);
			}
		}
	}
	
	private function get_indices()
	{
//		if ($this->_indices == null)
//		{
			$this->_indices = [];
			$all = OJ_Indexes::get_all_indexes($this->_db_catalog);
			$catalogs_id = OJ_Catalogs::get_catalog_id($this->_db_catalog);
//			var_dump($all);
			foreach ($all as $idx)
			{
				$iname = $idx['name'];
				$omits = explode(',', $idx['omit']);
				$omit = array();
				for ($n = 0; $n < count($omits); $n++)
				{
					$omit[$n] = intval($omits[$n]);
				}
				$startat = $idx['to'];
				$this->_indices[$iname] = new OJ_Index($this, array("name"=>$iname, "omit"=>$omit, "to"=>$startat, "ordinal"=>0, "type"=>"category", "catalog"=>$catalogs_id));
			}
//		}
		return $this->_indices;
	}
	
	public function get_logical($logical)
	{
		$ret = null;
		$logs = $this->get_logicals();
		if (isset($logs[$logical]))
		{
			$ret = $logs[$logical];
		}
		return ret;
	}
	
	public function get_root($index = null)
	{
		$ret = $this;
		if (isset($index) && ($index != null) && ($index !== 'default'))
		{
			$idx = $this->get_indices();
//			var_dump($idx);
			if (isset($idx[$index]))
			{
				$ret = $idx[$index];
			}
		}
		return $ret;
	}
	
	public function load_default_attributes($type)
	{
		$all = OJ_Entities::get_default_attributes($this->get_id(), $type);
		$pages = array();
		foreach ($all as $asname => $attset)
		{
			$viscount = 0;
			foreach ($attset as $attname => $att)
			{
				$vis = $att['visible'];
				if ($vis)
				{
					$viscount++;
				}
			}
			$pages[$asname] = ["atts" => $attset, "visible" => ($viscount > 0)];
		}
		return $pages;
	}
	
	public function get_attribute_collection($ojid)
	{
		$all = OJ_Entities::get_attributes($ojid);
//			echo $ojid."<br>";var_dump($all);
		$pages = array();
		foreach ($all as $asname => $attset)
		{
			$attribs = array();
			$viscount = 0;
			foreach ($attset as $attname => $att)
			{
				$vis = $att['visible'];
//							echo "attname ".$attname."<br/>";
//							var_dump($attname);
				$attribute = new OJ_Attribute($this, $att);
//				$this->ojdebug("ojid", $ojid);
				$attribute->set_ojid($ojid);
				$attribute->set_visible($vis);
				if ($vis)
				{
					$viscount++;
				}
//						$this->ojdebug($attribute, $attname);
//							var_dump($attribute);
				$attribs[$attname] = $attribute;
			}
//					var_dump($attribs);
			$pg = new OJ_Page($this, $attribs);
			$pg->set_visible($viscount > 0);
			foreach ($attribs as $attname=>$att)
			{
				$att->set_page($asname);
			}
//			$this->ojdebug($attribs, $asname);
			$pages[$asname] = $pg;
		}
		return new OJ_Attribute_Collection($this, $pages);
	}
	
	public function load_attribute_collection($attfile, $ojid = -4)
	{
		return OJ_Entities::get_attributes($ojid);;
	}
	
	public function ignore_the($type)
	{
		if ($this->_ignorethe == null)
		{
			$this->_ignorethe = array('CATEGORY' => false, "GROUP" => false, "ITEM" => false);
			$ig = $this->get_parameter_value("ignoreThe");
			if ($ig !== null)
			{
				$iga = explode('|', $ig);
				foreach ($iga as $typ)
				{
					$this->_ignorethe[strtoupper($typ)] = true;
				}
			}
		}
		return $this->_ignorethe[strtoupper($type)];
	}
	
	public function get_comparison_type($type)
	{
		if ($this->_comparison_type == null)
		{
			$this->_comparison_type = array('CATEGORY' => "default", "GROUP" => "default", "ITEM" => "default");
			$ct = $this->get_parameter_value("linkComparator");
//			var_dump($ct);
			if ($ct != null)
			{
				$cta = explode('|', $ct);
				if (count($cta) > 0)
				{
					$this->_comparison_type['ITEM'] = $cta[0];
					if (count($cta) > 1)
					{
						$this->_comparison_type['GROUP'] = $cta[1];
						if (count($cta) > 2)
						{
							$this->_comparison_type['CATEGORY'] = $cta[2];
						}
					}
				}
			}
		}
		return $this->_comparison_type[strtoupper($type)];
	}
	
	private function compare_strings($stra, $strb, $case = false)
	{
		$ret = 0;
		$numa = OJ_Utilities::starts_with_number($stra);
		if (!$numa["starts_with_number"])
		{
			$ret = $case?strcasecmp($stra, $strb):  strcmp($stra, $strb);
		}
		else
		{
			$numb = OJ_Utilities::starts_with_number($strb);
			if (!$numb["starts_with_number"])
			{
				$ret = $case?strcasecmp($stra, $strb):  strcmp($stra, $strb);
			}
			else
			{
				$ret = $numa["number"] < $numb["number"]?-1:($numa["number"] > $numb["number"]?1:0);
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
		$orda = intval(strval($lnka->ordinal));
		$ordb = intval(strval($lnkb->ordinal));
		$ret = $orda < $ordb?-1:($orda > $ordb?1:0);
		if ($ret === 0)
		{
			$ret = $this->compare_strings($lnka->get_sort_name(), $lnkb->get_sort_name());
		}
//		echo "compare ".strval($lnka->name)." (".$cata." ".$orda.") ".strval($lnkb->name)." (".$catb." ".$ordb.") gives ".$ret."<br/>";
		return $ret;
	}
	
	public function cmp_links_dictionary($lnka, $lnkb)
	{
		$ret = $this->compare_strings($lnka->get_sort_name(), $lnkb->get_sort_name(), true);
		return $ret;
	}
	
	public function cmp_links_alphabetic($lnka, $lnkb)
	{
		$ret = $this->compare_strings($lnka->get_sort_name(), $lnkb->get_sort_name());
		return $ret;
	}
	
	private function cmp_links($lnka, $lnkb)
	{
		$cata = $lnka->get_sort_type();
		$catb = $lnkb->get_sort_type();
		$ret = self::cmp_types($cata, $catb);
		if ($ret === 0)
		{
			$ct = "cmp_links_".$this->get_comparison_type($cata);
			$ret = $this->$ct($lnka, $lnkb);
//			echo "comparing ".$lnka->name." with ".$lnkb->name." using ".$this->get_comparison_type($cata)." giving ".$ret."<br/>";
		}
		return $ret;
//		echo "compare ".strval($lnka->name)." (".$cata." ".$orda.") ".strval($lnkb->name)." (".$catb." ".$ordb.") gives ".$ret."<br/>";
	}
	
	public function get_the_sort_name($type, $name)
	{
		$lname = strtolower($name);
		if ($this->ignore_the($type) && OJ_Utilities::starts_with($lname, "the "))
		{
			$name = substr($name, 4);
			$lname = substr($lname, 4);
		}
		switch ($this->get_comparison_type($type))
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
	
	public function set_follow_references($fr = null)
	{
		$this->_follow_references = $fr;
	}
	
	public function follow_references()
	{
		if ($this->_follow_references === null)
		{
//			echo "here1";
			$fr = $this->get_parameter ("followReferences");
//			var_dump($fr);
//			echo "here2";
			$this->_follow_references = $fr === null?[]:explode('|', $fr['value']);
		}
		return $this->_follow_references;
	}
	
	public function link($from1, $to1, $name = null, $ordinal = -1, $hidden = false)
	{
		if (is_array($from1))
		{
			foreach ($from1 as $f)
			{
				$this->link($f, $to1, $name, $ordinal, $hidden);
			}
		}
		elseif (is_array($to1))
		{
			if (OJ_Utilities::has_string_keys($to1))
			{
				foreach ($to1 as $nm => $t)
				{
					$this->link($from1, $t, $nm, $ordinal, $hidden);
				}
			}
			else
			{
				foreach ($to1 as $t)
				{
					$this->link($from1, $t, null, $ordinal, $hidden);
				}
			}
		}
		else
		{
			OJ_Links::add_link($this->get_id(), OJ_Entities::get_entity_id($from1), OJ_Entities::get_entity_id($to1), 
					$name == null?OJ_Entities::get_entity_name($to1):$name, 0, $ordinal, $hidden);
		}
	}
	
	public function reference($from1, $to1, $to_catalog, $name = null, $ordinal = -1, $hidden = false)
	{
		if (is_array($from1))
		{
			foreach ($from1 as $f)
			{
				$this->link($f, $to1, $name, $ordinal, $hidden);
			}
		}
		elseif (is_array($to1))
		{
			if (OJ_Utilities::has_string_keys($to1))
			{
				foreach ($to1 as $nm => $t)
				{
					$this->reference($from1, $t, $to_catalog, $nm, $ordinal, $hidden);
				}
			}
			else
			{
				foreach ($to1 as $t)
				{
					$this->reference($from1, $t, $to_catalog, null, $ordinal, $hidden);
				}
			}
		}
		else
		{
			OJ_Links::add_link($this->get_id(), OJ_Entities::get_entity_id($from1), OJ_Entities::get_entity_id($to1), 
					$name == null?OJ_Entities::get_entity_name($to1):$name, OJ_Catalogs::get_catalog_id($to_catalog), $ordinal, $hidden);
		}
	}
	
	public function get_entity_tooltip($entityid)
	{
		
	}
	
}

class OJ_Entity
{
	
	protected $_attributes;
	protected $_properties;
	protected $_forward;
	protected $_backward;
	protected $_data;
	
	protected $_ojid;
	protected $_root;
	protected $_catalog;
	
	protected $_needs_saving;
	
	public function __construct($username, $catalogname, $ojid, $attributes = null, $properties = null)
	{
		$this->_catalog = OJ_System::instance($username)->get_catalog($catalogname);
		$this->_ojid = $ojid;
//		$this->_root = $this->_catalog->get_entity_dir($ojid);
		if ($attributes !== null)
		{
			$this->_attributes = $attributes;
		}
		if ($properties !== null)
		{
			$this->_properties = $properties;
		}
		$this->_needs_saving = array("forward" => false, "backward" => false, "attributes" => false, "properties" => false);
	}
	
	public function get_catalog()
	{
		return $this->_catalog;
	}
	
	public function set_needs_saving($which = null, $to = true)
	{
		if ($which == null)
		{
			$this->_needs_saving["forward"] = $to;
			$this->_needs_saving["backward"] = $to;
			$this->_needs_saving["attributes"] = $to;
			$this->_needs_saving["properties"] = $to;
		}
		elseif (array_key_exists($which, $this->_needs_saving))
		{
			$this->_needs_saving[$which] = $to;
		}
	}
	
	public function get_attributes()
	{
		if ($this->_attributes == null)
		{
			$this->_attributes = $this->get_catalog()->get_attribute_collection($this->_ojid);
			$this->ojdebug1("loaded attributes for ".$this->_ojid);
//			echo "loaded attributes for ".$this->_ojid."</br>";
//			var_dump($this->_attributes);
//			$this->save_attributes();
//			echo $attfile."<br/>";
		}
		return $this->_attributes;
	}
	
	public function get_visible_pages()
	{
		$atts = $this->get_attributes();
//		var_dump($atts);
		return $atts->get_visible_pages();
	}
	
	public function get_number_of_visible_pages()
	{
		$atts = $this->get_attributes();
		return $atts->get_number_of_visible_pages();
	}
	
	public function has_page($pagename)
	{
		$atts = $this->get_attributes();
		return ($atts !== null) && ($atts->$pagename !== null);
	}
	
	public function get_page($pagename)
	{
		$atts = $this->get_attributes();
		$ret = null;
		if ($atts !== null)
		{
			$ret = $atts->$pagename;
		}
		return $ret;
	}
	
	public function get_attribute ($attname, $pagename = null)
	{
		$ret = null;
		$al = strtolower($attname);
		if ($al === "ojname")
		{
			$ret = $this->get_property("name");
		}
		else if ($al === "ojid")
		{
			$ret = $this->get_ojid();
		}
		else
		{
//			$atts = $this->get_attributes();
			if ($pagename == null)
			{
				$dc = strpos($attname, "::");
				if ($dc !== FALSE)
				{
					$pagename = substr($attname, 0, $dc);
					$attname = substr($attname, $dc + 2);
				}
//				else
//				{
//					foreach ($atts as $key=>$page)
//					{
//						if (property_exists($page, $attname))
//						{
//							$pagename = $page->name;
//							break;
//						}
//					}
//				}
			}
			$this->ojdebug($this->get_ojid(), $pagename, $attname);
			$att = OJ_Entities::get_attribute($this->get_ojid(), $pagename, $attname);
			$this->ojdebug1($att);
			if ($att != null)
			{
				$ret = new OJ_Attribute($this->get_catalog(), $att);
			}
		}
		return $ret;
	}
	
	public function set_attribute($pagename, $attname, $val)
	{
		OJ_Entities::set_attribute_value($this->get_ojid(), $pagename, $attname, $val);
	}
	
	public function get_number_of_children()
	{
		$where = ["from_catalogs_id"=>$this->get_catalog()->get_id(), "from_entities_id"=>$this->get_ojid(), "to_catalogs_id"=>$this->get_catalog()->get_id()];
		return OJ_Row::count_rows("OJ_Links", $where);
//		$fwd = $this->get_forward();
//		$ret = 0;
//		if (isset($fwd['name']))
//		{
//			$ret = count($fwd['name']);
//		}
//		return $ret;
	}
	
	public function load_forward()
	{
		return self::load_links($this->get_catalog(), $this->get_ojid(), true);
	}
	
	public static function load_links($catalog, $ojid, $forward = true)
	{
		return OJ_Entities::get_forward_links($catalog->get_id(), $ojid);
	}
	
	public static function load_references($catalog, $ojid)
	{
		return OJ_Entities::get_forward_links($catalog->get_id(), $ojid, true, true);
	}
	
	public function load_backward()
	{
		return self::load_links($this->get_catalog(), $this->get_ojid(), false);
	}
	
	private function get_forward()
	{
//		if ($this->_forward == null)
//		{
			$this->_forward = array();
			$byname = array();
			$byid = array();
			$links = $this->load_forward();
			foreach ($links as $lnk)
			{
				$link = new OJ_Link($this->_catalog, $lnk);
				$byname[$lname] = $link;
				$byid[$lid] = $link;
			}
			$this->_forward["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
			$this->_forward['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
//		}
		return $this->_forward;
	}
	
	private function get_backward()
	{
//		if ($this->_backward == null)
//		{
			$this->_backward = array();
			$byname = array();
			$byid = array();
			$links = $this->load_backward();
			foreach ($links as $lnk)
			{
				$link = new OJ_Link($this->_catalog, $lnk);
				$byname[$lname] = $link;
				$byid[$lid] = $link;
			}
			$this->_backward["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
			$this->_backward['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
//		}
		return $this->_forward;
	}
	
	public function load_attributes()
	{
		return $this->get_catalog()->load_attribute_collection($this->_root."attributes.xml", $this->get_ojid());
	}
	
	public function load_properties()
	{
		return self::load_entity_properties($this->get_catalog(), $this->get_ojid());
	}
	
	public static function load_entity_properties($catalog, $ojid)
	{
		return OJ_Entities::get_properties($ojid);
	}
	
	public function get_property ($pname, $display = false)
	{
		return OJ_Entities::get_property_value($this->get_ojid(), $pname, $display);
	}
	
	public function set_property($pname, $pval)
	{
		OJ_Entities::set_property($this->get_ojid(), $pname, $pval);
		$this->_properties = null;
	}
	
	public function get_ojid()
	{
		return $this->_ojid;
	}
	
	public function get_name()
	{
		return strval($this->get_property("name"));
	}
	
	public function get_entity_type()
	{
		return strval($this->get_property("type"));
	}
	
	public function __toString() {
		return strval($this->get_property("name"));
	}
	
	public function ojdebug(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
	
	public function ojdebug1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
}

class OJ_Data_Iterator implements Iterator
{
	private $_keys;
	private $_values;
	private $_position = 0;
	
	public function __construct($data, $classname = null)
	{
		$this->_keys = [];
		$this->_values = [];
		foreach($data as $k=>$v)
		{
			if (($classname = null) || is_a($v, $classname))
			{
				$this->_keys[] = $k;
				$this->_values[] = $v;
			}
		}
	}
	
	public function current()
	{
		return $this->_values[$this->_position];
	}

	public function key()
	{
		return $this->_keys[$this->_position];
	}

	public function next()
	{
		$this->_position++;
	}

	public function rewind()
	{
		$this->_position = 0;
	}

	public function valid()
	{
		return $this->_position < count($this->_keys);
	}

}

class OJ_Iterator implements Iterator
{
	private $_stack;
	private $_current;
	private $_catalog;
	private $_first;
	private $_visited;
	
    public function __construct($catalog, $startatojid)
	{
		$this->_stack = new OJ_Stack();
		$this->_catalog = $catalog;
		$props = OJ_Entity::load_entity_properties($catalog, $startatojid);
		$nm = "";
		$ty = "";
		foreach ($props as $p)
		{
			if ($p['name'] == 'name')
			{
				$nm = $p['value'];
			}
			elseif ($p['name'] == 'type')
			{
				$ty = $p['value'];
			}
		}
		$this->_visited[intval($startatojid)] = true;
//		var_dump($props);
		$this->_first = ["name" => $nm, "type" => $ty, "to" => $startatojid, "ordinal" => 0, "hidden" => false];
//		$this->_current = $this->_first;
//		$this->stack_children($startatojid);
    }
	
	private function stack_children($ojid)
	{
		$fwd = OJ_Entity::load_links($this->_catalog, $ojid);
//		var_dump($fwd);
		if ($fwd != null)
		{
			foreach ($fwd as $lnk)
			{
				$this->_stack->push($lnk);
//				$n = intval($lnk["to"]);
//				if ($n === 92727) print "pushed ".$n."\n";
			}
		}
	}

    public function rewind()
	{
		$this->_stack->clear();
		$this->_current = $this->_first;
		$this->_visited = [intval($this->_first["to"])=>true];
		$this->stack_children($this->_current["to"]);
    }

    public function current()
	{
        return $this->_current;
    }

    public function key()
	{
        return $this->_current["to"];
    }

    public function next()
	{
		if ($this->_stack->size() > 0)
		{
			$this->_current = $this->_stack->pop();
			$n = intval($this->_current["to"]);
//			if ($n === 92727) print "popped ".$n."\n";
			$iss = isset($this->_visited[$n]);
			while ($iss && !$this->_stack->isempty())
			{
				$this->_current = $this->_stack->pop();
				$n = intval($this->_current["to"]);
//				if ($n === 92727) print "popped1 ".$n."\n";
				$iss = isset($this->_visited[$n]);
			}
			if ($iss)
			{
				$this->_current = null;
			}
			else
			{
				$this->_visited[$n] = true;
				$this->stack_children($this->_current["to"]);
			}
		}
		else
		{
			$this->_current = null;
		}
    }

    public function valid()
	{
		return $this->_current !== null;
    }
}

class OJ_Audio_Utilities
{
	private static $loaded;
	private static $endings = ['trio', 'quartet', "quintet", "sextet", "septet", "group", "octet", "nonet"];
	private static $abbreviations = ['orch'=>'orchestra'];
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
		$art = OJ_Row::load_single_object("OJ_Attributes", ["name"=>"type", "entities_id"=>$artist_entities_id]);
		if ($art)
		{
			$ret = $art->get_value();
		}
		if (!self::is_artist_type($ret))
		{
			$ret = null;
			$col = OJ_Row::load_column("links", "name", ["from_entities_id"=>$artist_entities_id, "to_catalogs_id"=>self::get_multimedia_catalogs_id()]);
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
			self::$loaded = OJ_Row::load_hash_of_all_objects("OJ_Imported_Folders", ["catalogs_id"=>$catalogs_id, "logicals_name"=>$logicalname], "name");
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
			foreach (self::$abbreviations as $abbrev=>$full)
			{
				if (OJ_Utilities::ends_with($str, $abbrev) || (strpos($str, $abbrev.' ') !== FALSE) || (strpos($str, $abbrev.'.') !== FALSE))
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
		$ret1 = OJ_Row::load_array_of_objects('OJ_Entities', ["catalogs_id"=>$cid, "name%~%"=>$names]);
		$ret2 = OJ_Row::load_array_of_objects('OJ_Aka', ["name%~%"=>$names, "table_name"=>"entities"]);
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
				$ret[$r->entities_id] = OJ_Row::get_single_value("OJ_Entities", 'name', ["id"=>$r->aka_id]);
			}
		}
		$ret1 = [];
		foreach ($ret as $aid=>$aname)
		{
			$atype = self::get_artist_type($aid);
			if ($atype)
			{
				$ret1[$aid] = $aname.'|'.$atype;
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
		exec('soxi -D "'.OJ_Logicals::substitute_for_logical($audio_file).'"', $output);
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
		$ret = '<?xml version="1.0"?><entity type="'.$entity_types_id.'" catalog="'.$catalogs_id.'"><name>'.$artist_name.'</name>'.
				'<pages><page name="details" visible="1"><attribute type="string" visible="1" ordinal="3"><name>aka</name><value/></attribute>'.
				'<attribute type="URL" visible="1" ordinal="2"><name>website</name><value/></attribute>'.
				'<attribute type="string" visible="1" ordinal="1"><name>name</name><value>'.$artist_name.'</value></attribute>'.
				'<attribute type="enumeration" visible="1" ordinal="0"><name>type</name><value>'.($artist_type == null?"":$artist_type).'</value></attribute>'.
				'</page></pages><links>';
		if ($refids != null)
		{
			$mm_catalogs_id = OJ_Catalogs::get_catalog_id("Multimedia");
			foreach ($refids as $refid)
			{
				$ret .= '<link direction="from" catalog="'.$mm_catalogs_id.'" ordinal="0" hidden="0" other="'.$refid.'"/>';
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
				$ret .= '<name>'.$name.'</name><value>'.$path.'</value></attribute>';
			}
		}
		else
		{
			$ret = '<attribute type="string" visible="1" ordinal="0">';
			$ext = OJ_File_Utilities::get_extension($file, true);
			$path = htmlentities(OJ_File_Utilities::to_logical_path($file, OJ_Logicals::get_logicals()), ENT_XML1);
			$name = htmlentities(basename($file, $ext), ENT_XML1);
			$ret .= '<name>'.$name.'</name><value>'.$path.'</value></attribute>';
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
				$lnkstr .= '<link direction="to" catalog="'.$artcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$art[0].'">'.htmlentities($art[1], ENT_XML1).'</link>';
				$ord++;
			}
			$lnkstr .= '</links>';
		}
		else
		{
			$lnkstr .= '<links/>';
		}
		$track = OJ_File_Utilities::to_logical_path(htmlentities($file, ENT_XML1), OJ_Logicals::get_logicals());
		$ret = '<entity type="'.$typeid.'" catalog="'.$mmcatid.'"><name>'.htmlentities($f->get_name(), ENT_XML1).'</name><pages><page name="detail" visible="1">'.
				'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>'.htmlentities($f->get_name(), ENT_XML1).'</value></attribute>'.
				'<attribute type="audio" visible="1" ordinal="2"><name>track</name><value>'.$track.'</value></attribute>'.
//				'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>'.$file->va?"true":"false".'</value></attribute>'.
				'<attribute type="integer" visible="1" ordinal="4"><name>duration</name><value>'.OJ_Audio_Utilities::get_duration($file).'</value></attribute>'.
				'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>'.$a.'</value></attribute>'.
				'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>'.
				'</page></pages>'.$lnkstr.'<children/></entity>';
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
		$ret = '<entity type="'.$grptypeid.'" catalog="'.$mmcatid.'"><name>'.htmlentities($name, ENT_XML1).'</name>'.
				'<properties><property type="enumeration" name="subtype">playlist(album|playlist|radio|film|video|episode)</property></properties><pages>'.
				'<page name="detail" visible="1">'.
				'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>'.htmlentities($name, ENT_XML1).'</value></attribute>'.
				'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>true</value></attribute>'.
				'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>Various Artists</value></attribute>'.
				'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>'.
				'<attribute type="URL" visible="1" ordinal="6"><name>website</name><value/></attribute>'.
				'<attribute type="string" visible="1" ordinal="8"><name>contentGain</name><value/></attribute></page></pages><links>'.
				'<link direction="to" catalog="'.$mmcatid.'" ordinal="0" hidden="0" other="'.$plid.'">'.htmlentities($name, ENT_XML1).'</link>';
		$ord = 0;
		foreach ($list as $item)
		{
			$tt = '<tooltip>'.htmlentities($item->albumid."::".$item->albumname).'</tooltip>';
			$ret .= '<link direction="from" catalog="'.$mmcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$item->id.'">'.htmlentities($item->name, ENT_XML1).$tt.'</link>';
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
			$tt = '<tooltip>'.htmlentities($item->albumid."::".$item->albumname).'</tooltip>';
			$ret .= '<link direction="from" catalog="'.$mmcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$item->id.'">'.htmlentities($item->name, ENT_XML1).$tt.'</link>';
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
			$ret = '<entity type="'.$grptypeid.'" catalog="'.$mmcatid.'"><name>'.htmlentities($folder->get_name(), ENT_XML1).'</name>'.
					'<properties><property type="enumeration" name="subtype">album(album|playlist|radio|film|video|episode)</property></properties><pages>'.
					'<page name="detail" visible="1">'.
					'<attribute type="string" visible="1" ordinal="1"><name>title</name><value>'.htmlentities($folder->get_name(), ENT_XML1).'</value></attribute>'.
					'<attribute type="boolean" visible="1" ordinal="3"><name>variousArtists</name><value>'.($folder->va?"true":"false").'</value></attribute>'.
					'<attribute type="string" visible="1" ordinal="0"><name>artist</name><value>'.$artistname.'</value></attribute>'.
					'<attribute type="number" visible="1" ordinal="7"><name>gain</name><value>0.85</value></attribute>'.
					'<attribute type="URL" visible="1" ordinal="6"><name>website</name><value/></attribute>'.
					'<attribute type="string" visible="1" ordinal="8"><name>contentGain</name><value/></attribute></page>'.
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
					$ret .= '<link direction="to" catalog="'.$mmcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$pid.'">'.htmlentities($folder->get_name(), ENT_XML1).'</link>';
					$ord++;
				}
				$ord = 0;
				foreach ($artistids as $aid)
				{
					$art = explode('|', $aid);
					$ret .= '<link direction="to" catalog="'.$artcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$art[0].'">'.htmlentities($art[1], ENT_XML1).'</link>';
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
			$ret = '<entity type="'.$cattypeid.'" catalog="'.$mmcatid.'"><name>'.htmlentities($folder->get_name(), ENT_XML1).'</name><properties>'.
					'<property type="enumeration" name="subtype">audio_boxset(audio_genre|audio_boxset|radio_genre|video_genre|video_series)</property></properties>'.
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
					$ret .= '<link direction="to" catalog="'.$mmcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$pid.'"/>';
					$ord++;
				}
				$ord = 0;
				foreach ($artistids as $aid)
				{
					$art = explode('|', $aid);
					$ret .= '<link direction="to" catalog="'.$artcatid.'" ordinal="'.$ord.'" hidden="0" other="'.$art[0].'">'.htmlentities($art[1], ENT_XML1).'</link>';
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
		$sql = "SELECT entities.* FROM entities JOIN links ON entities.id = links.from_entities_id JOIN properties ON properties.entities_id = entities.id ".
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
		return OJ_Links::get_entities_id_from_path(self::get_multimedia_catalogs_id(), "Playlists/".$username);
	}
	
	public static function get_favorites_id($username)
	{
		return OJ_Links::get_entities_id_from_path(self::get_multimedia_catalogs_id(), "Favourites/".$username);
	}
	
	public static function get_audio_id()
	{
		return OJ_Catalogs::get_top_level_category_id(self::get_multimedia_catalogs_id(), "audio");
	}
}