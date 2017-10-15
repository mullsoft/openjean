<?php
require_once("oj_base_classes.php");
require_once("project.php");
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

abstract class OJ_Set_Object extends OJ_Object
{
	
	public static function append_child($xmlnode, $xmlchild)
	{
		if ($xmlchild instanceof DOMNode)
		{
			$xmlnode->appendChild($xmlchild);
		}
		else if ($xmlchild instanceof OJ_Set_Object)
		{
			self::append_child($xmlnode, $xmlchild->to_xml_element($xmlnode->ownerDocument));
		}
		else if (is_array($xmlchild))
		{
			foreach ($xmlchild as $k => $xmlc)
			{
				self::append_child($xmlnode, $xmlc);
			}
		}
	}
	
	public static function to_xml($xmldoc, $var)
	{
		$ret = null;
		if (is_object($var))
		{
			if ($var instanceof OJ_Set_Object)
			{
				$ret = $var->to_xml_element($xmldoc);
			}
		}
		else if (is_array($var))
		{
			$ret = array();
			foreach ($var as $k => $v)
			{
				$xv = self::to_xml($xmldoc, $v);
				if ($xv !== null)
				{
					$ret[$k] = $xv;
				}
			}
		}
		else
		{
			$ret = new DOMText(strval($var));
		}
		return $ret;
	}
	
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
	
	protected function to_xml_element1($xmldoc, $name)
	{
		$ret = $xmldoc->createElementNS ( OJ_System::OJURI , OJ_System::NS.$name);
		if ($this->_value)
		{
			$v = self::to_xml($xmldoc, $this->_value);
			if ($v !== null)
			{
				$ret->appendChild($v);
			}
		}
		foreach ($this->_data as $k => $v)
		{
//			echo "set attribute ".$k." to ".$v;
			$ret->setAttribute($k, strval($v));
		}
		return $ret;
	}
	
	protected function to_xml_element2($xmldoc, $name)
	{
		$ret = $xmldoc->createElementNS ( OJ_System::OJURI , OJ_System::NS.$name);
		OJ_Set_Object::append_child($ret, $this->_data);
		return $ret;
	}
	
	public abstract function to_xml_element($xmldoc);
	
	public function save($filename)
	{
		$xmldoc = new DOMDocument("1.0", "UTF-8");
		$xmldoc->preserveWhiteSpace = FALSE; 
		$xmldoc->formatOutput = true;
		$xmldoc->appendChild($this->to_xml_element($xmldoc));
		$xmldoc->save($filename);
	}
	
}

abstract class OJ_Catalog_Object extends OJ_Set_Object
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
	
	public function ojdebug(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_DEBUG, $msg);
	}
	
	public function ojerror(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_ERROR, $msg);
	}
	
	public function ojtrace(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_TRACE, $msg);
	}
	
	public function ojdebug1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_DEBUG, $msg);
	}
	
	public function ojerror1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_ERROR, $msg);
	}
	
	public function ojtrace1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_TRACE, $msg);
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
		return $this->_value;
	}
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element1($xmldoc, "attribute");
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
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element2($xmldoc, "attributeSet");
	}
	
	public function is_visible()
	{
		return $this->_visible;
	}
	
	public function set_visible($vis)
	{
		$this->_visible = $vis;
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
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element2($xmldoc, "pages");
	}
	
	public function get_visible_pages()
	{
		return $this->_visible_pages;
	}
	
	public function get_number_of_visible_pages()
	{
		return $this->_count_visible_pages;
	}
	
}

class OJ_Property extends OJ_Catalog_Object
{
	
	public $_ojid;
	private $_prompt;
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element1($xmldoc, "property");
	}
	
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
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element2($xmldoc, "properties");
	}
	
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
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element1($xmldoc, "parameter");
	}
	
}

class OJ_Link extends OJ_Catalog_Object
{
	
	public static function create_link_entity($catalog, $to, $name, $type, $hidden = "false", $omit = array())
	{
		return new OJ_Link($catalog, array("to"=>$to, "name"=>$name, "type"=>$type, "hidden"=>$hidden, 'omit'=>$omit));
	}
	
	private $_parents;
	private $_number_of_parents = -1;
	private $_children;
	private $_number_of_children = -1;
	private $_sortname;
	private $_sorttype;
	
	public function get_sort_name()
	{
		if ($this->_sortname == null)
		{
			$this->_sortname = $this->get_catalog()->get_the_sort_name(strval($this->_data['type']), strval($this->_data['name']));
		}
		return $this->_sortname;
	}
	
	public function get_name()
	{
		return $this->_data['name'];
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
//		var_dump($this->_data);exit;
//		echo "load children ".$this->_data["name"]."<br/>";
		$bwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["from"]))."backward.xml";
		$this->_parents = new OJ_Typed_Collection();
		if (file_exists($bwdfile))
		{
			$xmlroot = simplexml_load_file($bwdfile);
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'link')
				{
					$atts = $lnk->attributes();
					$to = intval($atts["to"]);
					$omit = $this->get_omit();
					if (!in_array($to, $omit))
					{
						$link = new OJ_Link($this->get_catalog(), $atts);
						$link->set_omit($omit);
						$this->_parents->put($link->type, $link);
					}
				}
			}
			$this->_parents->sort("sort_links");
		}
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
		$bwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["from"]))."backward.xml";
		$xmldoc = new DOMDocument("1.0", "UTF-8");
		$xmldoc->preserveWhiteSpace = FALSE; 
		$xmldoc->formatOutput = true;
		$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
		$parents = $this->get_parents()->get();
		foreach ($parents as $p)
		{
			$top->appendChild($p->to_xml_element($xmldoc));
		}
		$xmldoc->appendChild($top);
		$this->ojdebug1("writing backward links to ".$bwdfile);
		$xmldoc->save($bwdfile);
	}
	
	protected function load_children()
	{
//		var_dump($this->_data);exit;
//		echo "load children ".$this->_data["name"]."<br/>";
		$fwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["to"]))."forward.xml";
		$this->_children = array();
		if (file_exists($fwdfile))
		{
			$xmlroot = simplexml_load_file($fwdfile);
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'link')
				{
					$atts = $lnk->attributes();
					$to = intval($atts["to"]);
					$omit = $this->get_omit();
					if (!in_array($to, $omit))
					{
						$link = new OJ_Link($this->get_catalog(), $atts);
						$link->set_omit($omit);
						$this->_children[] = $link;
					}
				}
			}
			$this->get_catalog()->sort_links($this->_children);
		}
		else 
		{
			$followrefs = $this->get_catalog()->follow_references();
//			var_dump($followrefs);exit;
			if ($followrefs)
			{
				if (is_string($followrefs))
				{
					$followrefs = [$followrefs];
				}
		//		var_dump($this->_data);exit;
				$fwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["to"]))."references.xml";
				$this->_children = array();
				if (file_exists($fwdfile))
				{
					$xmlroot = simplexml_load_file($fwdfile);
					foreach ($xmlroot->children(OJ_System::OJURI) as $ref)
					{
//						var_dump($followrefs, $ref);
						if ($ref->getName() == 'reference')
						{
							$atts = $ref->attributes();
							$type = strval($atts["type"]);
//							var_dump($type);
							if (array_search($type, $followrefs) !== FALSE)
							{
								$to = intval(strval($atts["id"]));
								$cat = OJ_System::instance($this->get_catalog()->get_username())->get_catalog(strval($atts["catalog"]));
								if ($cat !== null)
								{
//									var_dump($cat->get_entity_property($to));
									$atts1 = array("ordinal"=>0, "hidden"=>false, "to"=>$to, "type"=>$type, "name"=>$cat->get_entity_property($to));
									$reference = new OJ_Link($cat, $atts1);
									$reference->set_omit([]);
//									var_dump($reference);
									$this->_children[] = $reference;
								}
							}
						}
					}
					$this->get_catalog()->sort_links($this->_children);
				}
				$this->_number_of_children = count($this->_children);
			}
		}
		$this->_number_of_children = count($this->_children);
	}
	
	public function get_children()
	{
		if ($this->_children == null)
		{
			$this->load_children();
		}
		return $this->_children;
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
		$fwdfile = $this->get_catalog()->get_entity_dir(intval($this->_data["to"]))."forward.xml";
		if ($this->has_children())
		{
			$xmldoc = new DOMDocument("1.0", "UTF-8");
			$xmldoc->preserveWhiteSpace = FALSE; 
			$xmldoc->formatOutput = true;
			$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
			$children = $this->get_children();
			foreach ($children as $child)
			{
				$top->appendChild($child->to_xml_element($xmldoc));
			}
			$xmldoc->appendChild($top);
			$this->ojdebug1("writing forward links to ".$fwdfile);
			$xmldoc->save($fwdfile);
		}
		elseif (file_exists($fwdfile) && is_writable($fwdfile))
		{
			unlink($fwdfile);
		}
	}
	
	public function to_xml_element($xmldoc)
	{
		return $this->to_xml_element1($xmldoc, "link");
	}
	
	public function get_destination_id()
	{
		return intval($this->_data["to"]);
	}
	
	public function get_destination_property($property_name)
	{
		return $this->get_catalog()->get_entity_property(intval($this->_data["to"]), $property_name);
	}
	
	public function get_sort_type()
	{
		if ($this->_sorttype == null)
		{
			$this->_sorttype = strtoupper(strval($this->_data['type']));
			if (('multimedia' === $this->get_catalog()->get_name()) && ('CATEGORY' == $this->_sorttype))
			{
				$subtype = $this->get_destination_property("subtype");
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
	
	const LOG_DEBUG = 0;
	const LOG_TRACE = 1;
	const LOG_ERROR = 2;
	
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
	
	private $_log_file;
	private $_log_level;
	
	private function __construct($username, $userroot)
	{
		$_catalogs = array();
		$this->_userroot = $userroot;
		$this->_username = $username;
		$this->_log_level = self::LOG_DEBUG;
		$this->_log_file = $userroot."logs/".'log_'.date("j.n.Y").".log";
//		echo $this->_userroot."<br/>";
	}
	
	public function get_username()
	{
		return $this->_username;
	}
	
	public function get_userroot()
	{
		return $this->_userroot;
	}
	
	public function get_dbroot()
	{
		return $this->_userroot."database/";
	}
	
	public function get_catalogroot($catalogname)
	{
		return $this->_userroot."database/".$catalogname."/";
	}
	
	public function get_entityroot($catalogname)
	{
		return $this->_userroot."database/".$catalogname."/entities/";
	}
	
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
		$ret = [];
		$xmlroot = simplexml_load_file($this->get_dbroot()."default__logicals.xml");
		foreach ($xmlroot->children(OJ_System::OJURI) as $log)
		{
			if ($log->getName() == 'logical')
			{
				$lname = strval($log->attributes()['name']);
				$alt = strval($log->attributes()['alternative']);
				$val = strval($log->attributes()['value']);
				$ret[] = ["name" => $lname, "value" => $val, "alternative" => $alt];
			}
		}
		return $ret;
	}

	private function get_logicals()
	{
		if ($this->_logicals == null)
		{
			$this->_logicals = array();
			$xmlroot = simplexml_load_file($this->get_dbroot()."default__logicals.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $log)
			{
				if ($log->getName() == 'logical')
				{
					$lname = strval($log->attributes()['name']);
					$lval = strval($log->attributes()['alternative']);
					if (!file_exists($lval))
					{
						$lval = strval($log->attributes()['value']);
					}
					$this->_logicals[$lname] = $lval.'/';
				}
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
		$ret = $str;
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
				$part2 = $this->get_logical($logname);
				if ($part2 !== null)
				{
					$ret = str_replace('//', '/', $part1.$part2.$part3);
				}
			}
		}
		return $ret;
	}
	
	public function ojlog($level, ...$logentries)
	{
		if ($level >= $this->_log_level)
		{
			if ((func_num_args() == 2) && !is_array($logentries))
			{
				$logentries = [$logentries];
			}
			$text = date("F j, Y, H:i: ")." xml ";
			foreach ($logentries as $logentry)
			{
				if (is_array($logentry) || is_object($logentry))
				{
					$text .= json_encode($logentry);
				}
				else
				{
					$text.= strval($logentry);
				}
				$text .= PHP_EOL;
			}
			file_put_contents($this->_log_file, $text, FILE_APPEND);
		}
	}
	
}

interface ChangeListener
{
	public function change_has_occurred($param);
}

class OJ_Catalog extends OJ_Index
{
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
	}
	
	public function get_root_id()
	{
		return 0;
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
	
	public function get_catalogroot()
	{
		return $this->_system->get_catalogroot($this->_data["name"]);
	}
	
	public function get_entityroot()
	{
		return $this->_system->get_entityroot($this->_data["name"]);
	}
	
	public function dbkey_to_filename($dbkey)
	{
		return $this->_system->get_dbroot().substr($dbkey, 7);
	}
	
	private function get_default_attributes($type)
	{
		$type = strtolower($type);
		if (!array_key_exists($type, $this->_default_attributes))
		{
			$attfile = $this->get_catalogroot().$type.".attributes";
			$this->_default_attributes[$type] = $this->_catalog->get_attribute_collection($attfile);
			$this->ojdebug1("loaded attributes from ".$attfile);
//			$this->save_attributes();
//			echo $attfile."<br/>";
		}
		return $this->_default_attributes[$type];
	}
	
	public function load_default_properties($type1)
	{
		$type = strtolower($type1);
		$ret = [];
		$xmlroot = simplexml_load_file($this->get_catalogroot().$type.".properties");
		foreach ($xmlroot->children(OJ_System::OJURI) as $prop)
		{
			if ($prop->getName() == 'property')
			{
				$pname = strval($prop->attributes()['name']);
				$ptype = strval($prop->attributes()['type']);
				$pval = $prop->__toString();
				$ret[] = ["name" => $pname, "type" => $ptype, "value" => $pval];
			}
		}
		return $ret;
	}
	
	private function get_default_properties($type)
	{
		$type = strtolower($type);
		if (!array_key_exists($type, $this->_default_properties))
		{
			$props = array();
			$xmlroot = simplexml_load_file($this->get_catalogroot().$type.".properties");
			foreach ($xmlroot->children(OJ_System::OJURI) as $prop)
			{
				if ($prop->getName() == 'property')
				{
					$pname = strval($prop->attributes()['name']);
					$property = new OJ_Property($this->_catalog, $prop->attributes());
					$property->set_value($prop->__toString());
					$props[$pname] = $property;
				}
			}
			$this->ojlog("loaded properties from ".$this->get_catalogroot().$type.".properties");
			$this->_default_properties[$type] = new OJ_PropertySet($this->_catalog, $props);
//			$this->save_properties();
		}
		return $this->_default_properties[$type];
	}
	
	public function create_entity($name, $type, $parents, $linkname = null, $with_attributes = null, $with_properties = null)
	{
		$attributes = $this->get_default_attributes($type);
		if (($with_attributes !== null) && ($with_attributes instanceof OJ_Attribute_Collection))
		{
			foreach ($with_attributes as $pname => $newpage)
			{
				if (array_key_exists($pname, $attributes))
				{
					$page = $attributes[$pname];
					foreach ($newpage as $aname => $att)
					{
						$page[$aname] = $att;
					}
					$attributes[$pname] = $page;
				}
				else
				{
					$attributes[$pname] = $newpage;
				}
			}
		}
		$properties = $this->get_default_properties($type);
		if (($with_properties !== null) && ($with_properties instanceof OJ_Property_Set))
		{
			foreach ($with_properties as $pname => $prop)
			{
				$properties[$pname] = $prop;
			}
		}
		$ojid = $this->get_parameter_value("jean-nextid");
		$this->set_parameter_value("jean-nextid", $ojid + 1, "integer");
		foreach ($attributes as $pname => $page)
		{
			foreach ($page as $aname => $att)
			{
				$att->set_ojid($ojid);
				$att->set_page($pname);
			}
		}
		$entity = new OJ_Entity($this->get_username(), $this->get_name(), $ojid, $attributes, $properties);
		$this->link($parents, $entity, $linkname);
		if (is_array($parents))
		{
			foreach ($parents as $p)
			{
				$pent = $this->get_entity($p);
				$pent->set_needs_saving("forward");
				$pent->save();
			}
		}
		else
		{
			$pent = $this->get_entity($parents);
			$pent->set_needs_saving("forward");
			$pent->save();
		}
		$entity->set_needs_saving();
		$pent->save();
	}
	
	public function get_entity_property($ojid, $propname = "name")
	{
		$ret = null;
		$pfile = $this->get_catalog()->get_entity_dir(intval($ojid))."properties.xml";
		if (file_exists($pfile))
		{
			$xmlroot = simplexml_load_file($pfile);
			foreach ($xmlroot->children(OJ_System::OJURI) as $prop)
			{
				if ($prop->getName() == 'property')
				{
					$atts = $prop->attributes();
//					var_dump($atts);
					if (isset($atts['name']))
					{
						$nm = strval($atts['name']);
						if ($nm == $propname)
						{
							$ret = strval($prop);
						}
					}
				}
			}
		}
		return $ret;
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
			if (!isset($this->_entities_by_id[$ojid]))
			{
				$this->_entities_by_id[$ojid] = new OJ_Entity($this->_system->get_username(), $this->_data["name"], $ojid);
			}
			$ret = $this->_entities_by_id[$ojid];
		}
		return $ret;
	}
	
	public function get_entity_dir($ojid)
	{
		$hex = dechex($ojid);
		$er = $this->get_entityroot();
		if (strlen($hex) == 1)
		{
			$er .= "0/";
		}
		else
		{
			for ($n = 0; $n < strlen($hex) - 1; $n++)
			{
				$er .= $hex[$n]."/";
			}
		}
		return realpath($er."oj".$hex)."/";
	}
	
	public function load_parameters()
	{
		$ret = [];
		$xmlroot = simplexml_load_file($this->get_catalogroot()."parameters.xml");
		foreach ($xmlroot->children(OJ_System::OJURI) as $param)
		{
			if ($param->getName() == 'parameter')
			{
				$pname = strval($param->attributes()['name']);
				$type = strval($param->attributes()['type']);
				$val = strval($param->attributes()['value']);
				if (strlen($val) == 0)
				{
					$val = $param->__toString();
				}
				$ret[] = ["name" => $pname, "type" => $type, "value" => $val];
			}
		}
		return $ret;
	}
	
	private function get_parameters()
	{
		if ($this->_parameters == null)
		{
			$this->_parameters = array();
			$xmlroot = simplexml_load_file($this->get_catalogroot()."parameters.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $param)
			{
				if ($param->getName() == 'parameter')
				{
					$pname = strval($param->attributes()['name']);
					$this->_parameters[$pname] = new OJ_Parameter($this, $param->attributes());
				}
			}
			$this->save_parameters();
//			var_dump(array_keys($this->_parameters), $this->_parameters["linkComparator"]);
		}
		return $this->_parameters;
	}
	
	public function get_parameter($pname)
	{
		$params = $this->get_parameters();
		return array_key_exists($pname, $params)?$params[$pname]:null;
	}
	
	public function get_parameter_value($pname)
	{
		$param = $this->get_parameter($pname);
		return $param == null?null:strval($param->get_value());
	}
	
	public function set_parameter_value($pname, $val, $ptype = "string")
	{
		$params = $this->get_parameters();
		if (array_key_exists($pname, $params))
		{
			$pm = $params[$pname];
		}
		else
		{
			$pm = new OJ_Parameter($this, OJ_Parameter::get_default_param());
			$pm['name'] = $pname;
			$pm['type'] = $type;
			$pm['value'] = $val;
		}
		$params[$pname] = $pm;
	}
	
	public function save_parameters()
	{
		$xmldoc = new DOMDocument("1.0", "UTF-8");
		$xmldoc->preserveWhiteSpace = FALSE; 
		$xmldoc->formatOutput = true;
		$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."parameterCollection");
		$top->setAttribute("name", $this->get_name());
//		$top->setAttribute("xldbsave", $this->get_name());
		$xmlchildren = self::to_xml($xmldoc, $this->_parameters);
		OJ_Set_Object::append_child($top, $xmlchildren);
		$xmldoc->appendChild($top);
		$pfile = $this->get_catalogroot()."parameters1.xml";
		$this->ojdebug1("writing parameters to ".$pfile);
		$xmldoc->save($pfile);
	}
	
	private function get_indices()
	{
		if ($this->_indices == null)
		{
			$this->_indices = array();
			$xmlroot = simplexml_load_file($this->get_catalogroot()."indices.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $idx)
			{
				if ($idx->getName() == 'index')
				{
					$iname = strval($idx->attributes()['name']);
					$omits = explode(',', strval($idx->attributes()['omit']));
					$omit = array();
					for ($n = 0; $n < count($omits); $n++)
					{
						$omit[$n] = intval($omits[$n]);
					}
					$startat = strval($idx->attributes()['startat']);
					$this->_indices[$iname] = new OJ_Index($this, array("name"=>$iname, "omit"=>$omit, "to"=>$startat, "ordinal"=>0, "type"=>"category"));
				}
			}
		}
		return $this->_indices;
	}
	
	public function load_indices()
	{
		$ret = [];
		$xmlroot = simplexml_load_file($this->get_catalogroot()."indices.xml");
		foreach ($xmlroot->children(OJ_System::OJURI) as $idx)
		{
			if ($idx->getName() == 'index')
			{
				$iname = strval($idx->attributes()['name']);
				$omit = strval($idx->attributes()['omit']);
				$startat = strval($idx->attributes()['startat']);
				$ret[] = ["name"=>$iname, "omit"=>$omit, "startat"=>$startat];
			}
		}
		return $ret;
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
		if (isset($index) && ($index != null))
		{
			$idx = $this->get_indices();
			if (isset($idx[$index]))
			{
				$ret = $idx[$index];
			}
		}
		return $ret;
	}
	
	public function load_default_attributes($type)
	{
		$type = strtolower($type);
		$attfile = $this->get_catalogroot().$type.".attributes";
		$xmlroot = simplexml_load_file($attfile);
		$pages = array();
		foreach ($xmlroot->children(OJ_System::OJURI) as $attset)
		{
			$nm = $attset->getName();
			if ($nm == 'attributeSet')
			{
				$asname = strval($attset->attributes()['name']);
				$attribs = array();
				$viscount = 0;
				foreach ($attset->children(OJ_System::OJURI) as $att)
				{
					if ($att->getName() == 'attribute')
					{
						$attname = strval($att->attributes()['name']);
						$att1 = [];
						foreach ($att->attributes() as $k=>$v)
						{
							$att1[strval($k)] = strval($v);
						}
						$vis = strval($att->attributes()['visible']) === 'true';
						if ($vis)
						{
							$viscount++;
						}
						foreach ($att->children(OJ_System::OJURI) as $cnt)
						{
							if ($cnt->getName() == 'attributeContents')
							{
								$att1["value"] = $cnt->__toString();
							}
						}
						$attribs[] = $att1;
					}
				}
				$pages[$asname] = ["atts" => $attribs, "visible" => ($viscount > 0)];
			}
		}
		return $pages;
	}
	
	public function get_attribute_collection($attfile, $ojid = -4)
	{
		$xmlroot = simplexml_load_file($attfile);
//			var_dump($xmlroot);
		$pages = array();
		foreach ($xmlroot->children(OJ_System::OJURI) as $attset)
		{
			$nm = $attset->getName();
//				echo $nm."<br/>";
			if ($nm == 'attributeSet')
			{
				$asname = strval($attset->attributes()['name']);
//					echo "asname ".$asname."<br/>";
				$attribs = array();
				$viscount = 0;
				foreach ($attset->children(OJ_System::OJURI) as $att)
				{
					if ($att->getName() == 'attribute')
					{
//							var_dump($att->attributes());
						$attname = strval($att->attributes()['name']);
//						$this->ojdebug($att->attributes(), $attname);
						$att1 = [];
						foreach ($att->attributes() as $k=>$v)
						{
//							$this->ojdebug(strval($k), strval($v));
							$att1[strval($k)] = strval($v);
						}
						$vis = strval($att->attributes()['visible']) === 'true';
//							echo "attname ".$attname."<br/>";
//							var_dump($attname);
						$attribute = new OJ_Attribute($this, $att1);
						$this->ojdebug("ojid", $ojid);
						$attribute->set_ojid($ojid);
						$attribute->set_visible($vis);
						if ($vis)
						{
							$viscount++;
						}
						foreach ($att->children(OJ_System::OJURI) as $conts)
						{
							if ($conts->getName() == 'attributeContents')
							{
								foreach ($conts->attributes() as $k=>$v)
								{
									$ky = strval($k);
									if ($ky === 'key')
									{
										$kval = strval($v);
										$fname = $this->_catalog->dbkey_to_filename($kval);
										if (file_exists($fname))
										{
											$attribute->set_value(file_get_contents($fname));
										}
									}
								}
//									var_dump($cnt);
								$attribute->set_value($conts->__toString());
							}
						}
//						$this->ojdebug($attribute, $attname);
//							var_dump($attribute);
						$attribs[$attname] = $attribute;
					}
				}
//					var_dump($attribs);
				$pg = new OJ_Page($this, $attribs);
				$pg->set_visible($viscount > 0);
				foreach ($attribs as $attname=>$att)
				{
					$att->set_page($asname);
				}
				$this->ojdebug($attribs, $asname);
				$pages[$asname] = $pg;
			}
		}
		return new OJ_Attribute_Collection($this, $pages);
	}
	
	public function load_attribute_collection($attfile, $ojid = -4)
	{
		$xmlroot = simplexml_load_file($attfile);
//			var_dump($xmlroot);
		$pages = [];
		foreach ($xmlroot->children(OJ_System::OJURI) as $attset)
		{
			$nm = $attset->getName();
//				echo $nm."<br/>";
			if ($nm == 'attributeSet')
			{
				$asname = strval($attset->attributes()['name']);
//					echo "asname ".$asname."<br/>";
				$attribs = [];
				$viscount = 0;
				foreach ($attset->children(OJ_System::OJURI) as $att)
				{
					if ($att->getName() == 'attribute')
					{
//							var_dump($att->attributes());
						$attname = strval($att->attributes()['name']);
//						$this->ojdebug($att->attributes(), $attname);
						$att1 = [];
						$valset = false;
						foreach ($att->attributes() as $k=>$v)
						{
							$ks = strval($k);
							$valset = $ks === 'value';
							$att1[$ks] = strval($v);
						}
						$vis = strval($att->attributes()['visible']) === 'true';
//							echo "attname ".$attname."<br/>";
//							var_dump($attname);
						if ($vis)
						{
							$viscount++;
						}
						if (!$valset)
						{
							foreach ($att->children(OJ_System::OJURI) as $conts)
							{
								if ($conts->getName() == 'attributeContents')
								{
									$kval = null;
									foreach ($conts->attributes() as $k=>$v)
									{
										$ky = strval($k);
										if ($ky === 'key')
										{
											$kval = strval($v);
										}
									}
									if ($kval == null)
									{
										$cstr = $conts->__toString();
										if (substr($cstr, 7) === 'ojdb://')
										{
											$kval = $cstr;
										}
									}
									if ($kval == null)
									{
										$att1['value'] = $cstr;
									}
									else
									{
										$fname = $this->_catalog->dbkey_to_filename($kval);
										if (file_exists($fname))
										{
											$finfo = finfo_open(FILEINFO_MIME);
											if (substr(finfo_file($finfo, $filename), 0, 4) != 'text')
											{
												$att1['type'] = 'blob';
											}
											$att1['value'] = file_get_contents($fname);
										}
									}
								}
							}
						}
//						$this->ojdebug($attribute, $attname);
//							var_dump($attribute);
						$attribs[$attname] = $att1;
					}
				}
//					var_dump($attribs);
				$pages[$asname] = ["atts" => $attribs, "visible" => ($viscount > 0)];
			}
		}
		return $pages;
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
			$ret = strcmp($lnka->get_sort_name(), $lnkb->get_sort_name());
		}
//		echo "compare ".strval($lnka->name)." (".$cata." ".$orda.") ".strval($lnkb->name)." (".$catb." ".$ordb.") gives ".$ret."<br/>";
		return $ret;
	}
	
	public function cmp_links_dictionary($lnka, $lnkb)
	{
		$ret = strcasecmp($lnka->get_sort_name(), $lnkb->get_sort_name());
		return $ret;
	}
	
	public function cmp_links_alphabetic($lnka, $lnkb)
	{
		$ret = strcasecmp($lnka->get_sort_name(), $lnkb->get_sort_name());
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
			$fr = $this->get_parameter ("followReferences");
			$this->_follow_references = $fr === null?"NONE":explode('|', $fr->get_value());
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
			$to = $this->get_entity($to1);
			$from = $this->get_entity($from1);
			if ($name === null)
			{
				$name = $to->get_property('name');
			}
			if ($ordinal < 0)
			{
				$ordinal = $from->get_number_of_children();
			}
			$lnk1 = new OJ_LINK($this, ['name' => $name, 'to' => $to->get_ojid(), 'hidden' => strval($hidden), 'type' => $to->get_property('type'), 'ordinal' => $ordinal]);
			$lnk2 = new OJ_LINK($this, ['name' => $from->get_property('name'), 'to' => $from->get_ojid(), 'hidden' => strval($hidden), 'type' => $from->get_property('type'), 'ordinal' => 0]);
			$from->add_forward_link($lnk1);
			$to->add_backward_link($lnk2);
		}
	}
	
	public function get_tooltip($entityid)
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
		$this->_root = $this->_catalog->get_entity_dir($ojid);
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
	
	private function get_attributes()
	{
		if ($this->_attributes == null)
		{
			$attfile = $this->_root."attributes.xml";
			$this->_attributes = $this->_catalog->get_attribute_collection($attfile, $this->_ojid);
			$this->ojdebug1("loaded attributes from ".$this->_root."attributes.xml");
//			$this->save_attributes();
//			echo $attfile."<br/>";
		}
		return $this->_attributes;
	}
	
	public function get_visible_pages()
	{
		$atts = $this->get_attributes();
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
			$atts = $this->get_attributes();
			if ($pagename == null)
			{
				$dc = strpos($attname, "::");
				if ($dc !== FALSE)
				{
					$pagename = substr($attname, 0, $dc);
					$attname = substr($attname, $dc + 2);
				}
				else
				{
					foreach ($atts as $key=>$page)
					{
						if (property_exists($page, $attname))
						{
							$pagename = $page->name;
							break;
						}
					}
				}
			}
			if ($pagename != null)
			{
				$page = $atts->$pagename;
				$this->ojdebug("got page", $pagename, $page);
				if ($page != null)
				{
					$ret = $page->$attname;
				}
			}
			elseif($this->has_page($attname))
			{
				$page = $atts->$attname;
				$this->ojdebug("got page", $attname, $page);
				if ($page != null)
				{
					$ret = $page->get_an_attribute();
				}
			}
		}
		return $ret;
	}
	
	public function save_attributes()
	{
		if ($this->_attributes != null)
		{
	//		var_dump($this->_attributes);
			$xmldoc = new DOMDocument("1.0", "UTF-8");
			$xmldoc->preserveWhiteSpace = FALSE; 
			$xmldoc->formatOutput = true;
			$top = $this->_attributes->to_xml_element($xmldoc);
			$top->setAttribute("jeanid", $this->_ojid);
			$xmldoc->appendChild($top);
			$afile = $this->_root."attributes.xml";
			$this->ojdebug1("writing attributes to ".$afile);
			$xmldoc->save($afile);
		}
	}
	
	public function get_number_of_children()
	{
		$fwd = $this->get_forward();
		$ret = 0;
		if (isset($fwd['name']))
		{
			$ret = count($fwd['name']);
		}
		return $ret;
	}
	
	public function load_forward()
	{
		return self::load_links($this->get_catalog(), $this->get_ojid(), true);
	}
	
	public static function load_links($catalog, $ojid, $forward = true)
	{
		$lfile = $catalog->get_entity_dir($ojid).($forward?"for":"back")."ward.xml";
		$ret = [];
		if (file_exists($lfile))
		{
			$xmlroot = simplexml_load_file($lfile);
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'link')
				{
					// <openjean:link hidden="false" name="mullsoft" ordinal="0" to="24" type="CATEGORY"/>
					$link = [];
					$link['name'] = strval($lnk->attributes()['name']);
					$link['to'] = strval($lnk->attributes()['to']);
					$link['hidden'] = strval($lnk->attributes()['hidden']);
					$link['ordinal'] = strval($lnk->attributes()['ordinal']);
					$link['type'] = strval($lnk->attributes()['type']);
					$ret[] = $link;
				}
			}
		}
		return $ret;
	}
	
	public static function load_references($catalog, $ojid)
	{
		$lfile = $catalog->get_entity_dir($ojid)."references.xml";
		$ret = [];
		if (file_exists($lfile))
		{
			$xmlroot = simplexml_load_file($lfile);
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'reference')
				{
					// <openjean:link hidden="false" name="mullsoft" ordinal="0" to="24" type="CATEGORY"/>
					$link = [];
					$link['name'] = strval($lnk->attributes()['attribute']);
					$link['catalog'] = strval($lnk->attributes()['catalog']);
					$link['to'] = strval($lnk->attributes()['id']);
					$link['hidden'] = "false";
					$link['ordinal'] = "0";
					$link['type'] = strval($lnk->attributes()['type']);
					$ret[] = $link;
				}
			}
		}
		return $ret;
	}
	
	public function load_backward()
	{
		return self::load_links($this->get_catalog(), $this->get_ojid(), false);
	}
	
	private function get_forward()
	{
		if ($this->_forward == null)
		{
			$this->_forward = array();
			$byname = array();
			$byid = array();
			$xmlroot = simplexml_load_file($this->_root."forward.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'link')
				{
					$lname = strval($lnk->attributes()['name']);
					$lid = strval($lnk->attributes()['to']);
					$link = new OJ_Link($this->_catalog, $lnk->attributes());
					$byname[$lname] = $link;
					$byid[$lid] = $link;
				}
			}
			$this->_forward["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
			$this->_forward['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
		}
		return $this->_forward;
	}
	
	private function get_backward()
	{
		if ($this->_backward == null)
		{
			$this->_backward = array();
			$byname = array();
			$byid = array();
			$xmlroot = simplexml_load_file($this->_root."backward.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $lnk)
			{
				if ($lnk->getName() == 'link')
				{
					$lname = strval($lnk->attributes()['name']);
					$lid = strval($lnk->attributes()['to']);
						$link = new OJ_Link($this->_catalog, $lnk->attributes());
					$byname[$lname] = $link;
					$byid[$lid] = $link;
				}
			}
			$this->_backward["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
			$this->_backward['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
		}
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
		$lfile = $catalog->get_entity_dir($ojid)."properties.xml";
		$ret = [];
		$xmlroot = simplexml_load_file($lfile);
		foreach ($xmlroot->children(OJ_System::OJURI) as $prop)
		{
			if ($prop->getName() == 'property')
			{
				$pname = strval($prop->attributes()['name']);
				$type = strval($prop->attributes()['type']);
				$val = $prop->__toString();
				$ret[] = ["name" => $pname, "type" => $type, "value" => $val];
			}
		}
		return $ret;
	}
	
	private function get_properties()
	{
		if ($this->_properties == null)
		{
			$props = array();
			$this->ojdebug1("loading properties from ".$this->_root."properties.xml");
			$xmlroot = simplexml_load_file($this->_root."properties.xml");
			foreach ($xmlroot->children(OJ_System::OJURI) as $prop)
			{
				if ($prop->getName() == 'property')
				{
					$pname = strval($prop->attributes()['name']);
					$property = new OJ_Property($this->_catalog, $prop->attributes());
					$property->set_value($prop->__toString());
					$property->set_ojid($this->_ojid);
					$props[$pname] = $property;
				}
			}
			$this->ojdebug1("loaded properties from ".$this->_root."properties.xml");
			$this->_properties = new OJ_PropertySet($this->_catalog, $props);
//			$this->save_properties();
		}
		return $this->_properties;
	}
	
	public function get_property ($pname)
	{
		$props = $this->get_properties();
		return $props->$pname;
	}
	
	public function save_properties()
	{
		if ($this->_properties != null)
		{
			$xmldoc = new DOMDocument("1.0", "UTF-8");
			$xmldoc->preserveWhiteSpace = FALSE; 
			$xmldoc->formatOutput = true;
			$top = $this->_properties->to_xml_element($xmldoc);
			$xmldoc->appendChild($top);
			$pfile = $this->_root."properties.xml";
			$this->ojlog("writing properties to ".$pfile);
			$xmldoc->save($pfile);
		}
	}
	
	public function save_backward()
	{
		if ($this->_backward != null)
		{
			$bwdfile = $this->_root."backward.xml";
			$xmldoc = new DOMDocument("1.0", "UTF-8");
			$xmldoc->preserveWhiteSpace = FALSE; 
			$xmldoc->formatOutput = true;
			$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
			$parents = $this->get_backward();
			foreach ($parents as $p)
			{
				$top->appendChild($p->to_xml_element($xmldoc));
			}
			$xmldoc->appendChild($top);
			$this->ojdebug1("entity writing backward links to ".$bwdfile);
			$xmldoc->save($bwdfile);
		}
	}
	
	public function save_forward()
	{
		if ($this->_forward != null)
		{
			$fwdfile = $this->_root."forward.xml";
			$children = $this->get_forward();
			if (count($children) > 0)
			{
				$xmldoc = new DOMDocument("1.0", "UTF-8");
				$xmldoc->preserveWhiteSpace = FALSE; 
				$xmldoc->formatOutput = true;
				$top = $xmldoc->createElementNS(OJ_System::OJURI, OJ_System::NS."links");
				foreach ($children as $child)
				{
					$top->appendChild($child->to_xml_element($xmldoc));
				}
				$xmldoc->appendChild($top);
				$this->ojdebug1("entity writing forward links to ".$fwdfile);
				$xmldoc->save($fwdfile);
			}
			elseif (file_exists($fwdfile) && is_writable($fwdfile))
			{
				unlink($fwdfile);
			}
		}
	}
	
	public function save()
	{
		if ($this->_needs_saving["forward"])
		{
			$this->save_forward();
			$this->_needs_saving["forward"] = false;
		}
		if ($this->_needs_saving["backward"])
		{
			$this->save_backward();
			$this->_needs_saving["backward"] = false;
		}
		if ($this->_needs_saving["attributes"])
		{
			$this->save_attributes();
			$this->_needs_saving["attributes"] = false;
		}
		if ($this->_needs_saving["properties"])
		{
			$this->save_properties();
			$this->_needs_saving["properties"] = false;
		}
	}
	
	public function add_forward_link($lnk)
	{
		$fwd = $this->get_forward();
		$byname = isset($fwd["name"])?$fwd["name"]:[];
		$byid = isset($fwd['to'])?$fwd["to"]:[];
		$byname[$lnk->get_name()] = $lnk;
		$byid[$lnk->get_destination_id()] = $lnk;
		$fwd["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
		$fwd['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
		$this->_forward = $fwd;
	}
	
	public function add_backward_link($lnk)
	{
		$bwd = $this->get_backward();
		$byname = isset($bwd["name"])?$bwd["name"]:[];
		$byid = isset($bwd['to'])?$bwd["to"]:[];
		$byname[$lnk->get_name()] = $lnk;
		$byid[$lnk->get_destination_id()] = $lnk;
		$bwd["name"] = uasort($byname, array('OJ_Link', 'cmp_links'));
		$bwd['to'] = uasort($byid, array('OJ_Link', 'cmp_links'));
		$this->_backward = $bwd;
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
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_DEBUG, $msg);
	}
	
	public function ojerror(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_ERROR, $msg);
	}
	
	public function ojtrace(...$msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_TRACE, $msg);
	}
	
	public function ojdebug1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_DEBUG, $msg);
	}
	
	public function ojerror1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_ERROR, $msg);
	}
	
	public function ojtrace1($msg)
	{
		OJ_System::instance($this->get_catalog()->get_username())->ojlog(OJ_System::LOG_TRACE, $msg);
	}
}

class OJ_Iterator implements Iterator
{
	private $_stack;
	private $_current;
	private $_catalog;
	private $_first;
	private $_visited;
	
    public function __construct($catalog, $startatojid) {
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
		if ($fwd != null)
		{
			foreach ($fwd as $lnk)
			{
				$this->_stack->push($lnk);
				$n = intval($lnk["to"]);
//				print "pushed ".$n."\n";
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
//			print "popped ".$n."\n";
			$iss = isset($this->_visited[$n]);
			while ($iss && !$this->_stack->isempty())
			{
				$this->_current = $this->_stack->pop();
				$n = intval($this->_current["to"]);
//				print "popped1 ".$n."\n";
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

?>