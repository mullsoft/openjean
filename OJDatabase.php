<?php

require_once("oj_base_classes.php");
require_once("project.php");

class OJ_Database_Table_information
{
    private static $instance;

    public static function get_instance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new OJ_Database_Table_information();
        }
        return self::$instance;
    }
	
	private $parameter_type_table;
    
    private function __construct()
    {
		$this->parameter_type_table = ["image" => "text", "icon" => "text", "URL" => "string", "file" => "string", "folder" => "string", "audio" => "string",
			"video" => "string", "email" => "string", "datetime" => "date_Time", "date" => "date_Time", "time" => "date_Time" ];
    }
    
    public function get_tablename_from_classname($classname)
    {
        return strtolower(substr($classname, 3));
    }
    
    public function get_classname_from_tablename($tablename, $modifier = null)
    {
        return 'OJ_'.ucwords($tablename, '_');
    }
    
    public function get_where_array($tablename, $key){
        return null;
    }
    
    public function get_select_array($tablename, $key){
        $ret = null;
        switch ($key)
        {
            default:
                break;
        }
        return $ret;
    }
    
    public function get_number_array($tablename, $key){
        return null;
    }
    
    public function get_user_object($tablename, $key)
    {
        return null;
    }
    
    public function get_user_post($tablename, $post, $exclude = null, $uploads = null, $datadir = null)
    {
        return null;
    }
    
    public function get_description($tablename, $columnname)
    {
        return null;
    }
    
    public function get_table_equivalent($tablename, $columnname)
    {
		$lp = strpos($columnname, '(');
		if ($lp > 0)
		{
			$columnname = substr($columnname, 0, $lp);
		}
		$ret = $columnname;
		$table = $this->$tablename;
		if (array_key_exists($columnname, $table))
		{
			$ret = $table[$columnname];
		}
        return $ret;
    }
}

class OJ_Database_Row extends OJ_Row
{
	
	public static function get_row_id($classname, $row)
	{
		$ret = $row;
		if (!is_numeric($row))
		{
			if (is_string($row))
			{
				$row1 = OJ_Row::load_single_object($classname, ["name"=>$row]);
				$ret = $row1->id;
			}
			else
			{
				$ret = $row->id;
			}
		}
		return $ret;
	}
	
    public function get_table_information()
    {
        return OJ_Database_Table_information::get_instance();
    }
    
    public function filter_hash($hash)
    {
        return $hash;
    }
    
    public function get_name()
    {
        $ret = null;
        if (array_key_exists("name", $this->_data))
        {
            $ret = $this->_data['name'];
        }
        return $ret;
    }
	
	public function get_value()
	{
        $ret = null;
        if (array_key_exists("value", $this->_data))
        {
            $ret = $this->_data['value'];
        }
		elseif (array_key_exists("values_id", $this->_data) && array_key_exists("table_name", $this->_data))
		{
			$id = $this->_data['values_id'];
			if ($id > 0)
			{
				$tabname = $this->_data['table_name'];
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$ret = $obj->get_value();
				}
			}
		}
		if (($ret == null) && method_exists($this, "get_default_value"))
		{
			$ret = $this->get_default_value();
		}
        return $ret;
	}
	
	public function set_value($val)
	{
        if (array_key_exists("value", $this->_data))
        {
            $this->_data['value'] = $val;
			$this->save();
        }
		elseif (array_key_exists("values_id", $this->_data) && array_key_exists("table_name", $this->_data))
		{
			$id = $this->_data['values_id'];
			if ($id > 0)
			{
				$tabname = $this->_data['table_name'];
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$obj->set_value($val);
				}
			}
		}
	}
}

abstract class OJ_Values extends OJ_Database_Row
{
	
	public static function compare($val1, $val2)
	{
		$cval1 = $val1->get_comparison_value();
		$cval2 = $val2->get_comparison_value();
		return OJ_Utilities::compare_comparison_values($cval1, $cval2);
	}
	
    public function __construct($table_name, $param = null)
    {
        parent::__construct($table_name, "id", $param, null, null, null);
//        $this->_must_be_set_to_save[] = 'value';
    }
	
	public function __toString()
	{
		strval($this->_data['value']);
	}
	
	public function get_default_value()
	{
		return  null;
	}
	
	public function get_value()
	{
		return $this->_data["value"];
	}
	
	public abstract function get_comparison_value();
}

class OJ_Attribute_Descriptions extends OJ_Database_Row
{
	
	public static function get_attribute_description($catalogs_id, $entity_types_id, $attribute_types_id)
	{
		$attdesc = new OJ_Attribute_Descriptions(["catalogs_id" => $catalogs_id, "entity_types_id" => $entity_types_id, "attribute_types_id" => $attribute_types_id]);
		$id = $attdesc->id;
		if (($id == null) || ($id == 0))
		{
			$id = $attdesc->save();
		}
		return $attdesc;
	}

    public function __construct($param = null)
    {
        parent::__construct("attribute_descriptions", "id", $param, null, null, null);
//        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Attribute_Types extends OJ_Database_Row
{
	
	private static $attribute_type_table;
	
	public static function get_attribute_type_id($atype)
	{
		return self::get_row_id("OJ_Attribute_Types", $atype);
	}
	
	public static function get_attribute_type($tname)
	{
		$lp = strpos($tname, '(');
		if ($lp > 0)
		{
			$tname = substr($tname, 0, $lp);
		}
		if (self::$attribute_type_table == null)
		{
			$attribute_type_table = OJ_Row::load_hash_of_all_objects("OJ_Attribute_Types", null, "name");
		}
		return array_key_exists($tname, $attribute_type_table)?$attribute_type_table[$tname]:null;
	}

    public function __construct($param = null)
    {
        parent::__construct("attribute_types", "id", $param, "name", null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Attributes extends OJ_Database_Row
{
	
	public static function get_attributes_id($attributes_name, $pages_id, $entities_id = 0)
	{
		return OJ_Row::get_single_value("OJ_Attributes", "id", ["name"=>$attributes_name, "entities_id"=>$entities_id, "pages_id"=>$pages_id]);
	}
	
	/**
	 * 
	 * @param type $xmlatt
	 * <attribute name="" type="" visible="true" ordinal="0">
	 */
	public static function from_xml($entities_id, $pages_id, $xmlatt)
	{
		$aname = (string)$xmlatt->name;
		$atypeid = OJ_Attribute_Types::get_attribute_type_id((string) $xmlatt["type"]);
		if (isset($xmlatt["visible"]))
		{
			$vis1 = (string) $xmlatt["visible"];
			$vis = is_numeric($vis1)?$vis1:($vis1 === 'true'?1:0);
		}
		else
		{
			$vis = 1;
		}
		if (isset($xmlatt["ordinal"]))
		{
			$ord = intval((string) $xmlatt["ordinal"]);
		}
		else
		{
			$ord = 0;
		}
		$values_id = 0;
		$val = html_entity_decode((string) $xmlatt->value);
		if ($val)
		{
			$atype = new OJ_Attribute_Types($atypeid);
			$tname = $atype->table_name;
			$cname = "OJ_".ucfirst($tname)."_Values";
			$hashe = [];
			if ($tname === 'enumeration')
			{
				$enumerations_id = OJ_Attribute_Enumeration_Correspondence::get_enumerations_id($pages_id, $aname);
				if ($enumerations_id === 0)
				{
					$lb = strpos($val, '(');
					if ($lb !== FALSE)
					{
						$rb = strpos($val, '(');
						if ($rb > $lb)
						{
							$enumval = substr($val, $lb + 1, $rb - $lb - 1);
							$val = substr($val, 0, $lb);
							$enumerations_id = OJ_Row::get_single_value("OJ_Enumerations", "id", ["name"=>$enumval]);
							if (!$enumerations_id)
							{
								$enum = new OJ_Enumerations(["name"=>$enumval]);
								$enumerations_id = $enum->save();
							}
						}
					}
				}
				$hashe["enumerations_id"] = $enumerations_id;
			}
			$hashe["value"] = $val;
			$v = new $cname($hashe);
			$values_id = $v->save();
		}
//		OJ_Logger::get_logger()->ojdebug("from xml attribute", $aname, $atypeid, $values_id);
		$hash = ["name"=>$aname, "attribute_types_id"=>$atypeid, "visible"=>$vis, "ordinal"=>$ord, "pages_id"=>$pages_id, "entities_id"=>$entities_id, "values_id"=>$values_id];
		$att = OJ_Row::load_single_object("OJ_Attributes", ["name"=>  addslashes($aname), "attribute_types_id"=>$atypeid, "pages_id"=>$pages_id, "entities_id"=>$entities_id]);
		if ($att == null)
		{
			$att = new OJ_Attributes($hash);
		}
		else
		{
			$att->set_all(["visible"=>$vis, "ordinal"=>$ord, "values_id"=>$values_id]);
		}
		$att->save();
//		OJ_Logger::get_logger()->ojdebug("from xml attribute saved", $att);
		return $att;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("attributes", "id", $param, null, null, null);
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function get_default_value()
	{
		$att = OJ_Row::load_single_object("OJ_Attributes", ["name"=>$this->_data["name"], "pages_id"=>$this->_data["pages_id"],
			"attribute_types_id"=>$this->_data["attribute_types_id"],
			"entities_id"=>0]);
		$ret = null;
		if (($att != null) && ($att->values_id > 0))
		{
			$ret = $att->get_value();
		}
	}
	
	public function delete()
	{
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$atid = $this->_data["attribute_types_id"];
			$atype = new OJ_Attribute_Types($atid);
			$tabname = $atype->table_name;
			$tablename = $tabname."_values";
			$where = "id=".$id;
			OJ_Row::delete_rows($tablename, $where);
		}
		parent::delete();
	}
	
	public function get_value()
	{
        $ret = null;
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$atid = $this->_data["attribute_types_id"];
			$atype = new OJ_Attribute_Types($atid);
			$tabname = $atype->table_name;
			$tname = $tabname."_values";
			$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
			if (class_exists($cname))
			{
				$obj = new $cname($id);
				$ret = $obj->get_value();
			}
		}
		if ($ret == null)
		{
			$ret = $this->get_default_value();
		}
        return $ret;
	}
	
	public function get_comparison_value()
	{
        $ret = null;
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$atid = $this->_data["attribute_types_id"];
			$atype = new OJ_Attribute_Types($atid);
			$tabname = $atype->table_name;
			$tname = $tabname."_values";
			$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
			if (class_exists($cname))
			{
				$obj = new $cname($id);
				$ret = $obj->get_comparison_value();
			}
		}
        return $ret;
	}
	
	public function to_xml_string()
	{
		$atype = OJ_Row::get_single_value("OJ_Attribute_Types", "name", ["id"=>$this->_data["attribute_types_id"]]);
		$ret = '<attribute type="'.$atype.'" visible="'.$this->_data["visible"].'" ordinal="'.$this->_data["ordinal"].'">'.
				'<name>'.htmlentities(stripslashes($this->_data["name"])).'</name><value>'.
				htmlentities(stripslashes($this->get_value()))."</value></attribute>";
		return $ret;
	}
	
	public function get_enumerations_id()
	{
		return OJ_Attribute_Enumeration_Correspondence::get_enumerations_id($this->_data["id"]);
	}
}

class OJ_Catalogs extends OJ_Database_Row
{
	
	public static function get_all_catalogs()
	{
		return OJ_Row::load_hash_of_all_objects("OJ_Catalogs", null, "name");
	}
	
	public static function get_all_catalog_ids()
	{
		$ret = [];
		$cats = OJ_Row::load_hash_of_all_objects("OJ_Catalogs", null, "name");
		foreach ($cats as $cname=>$cat)
		{
			$ret[strtolower($cname)] = $cat->id;
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $cat can be id, name or OJ_Catalogs object
	 * @return type OJ_Catalogs object
	 */
	public static function get_catalog($cat)
	{
		$ret = $cat;
		if (is_numeric($cat))
		{
			$ret = OJ_Row::load_single_object("OJ_Catalogs", ["id"=>$cat]);
		}
		else if (is_string($cat))
		{
			$ret = OJ_Row::load_single_object("OJ_Catalogs", ["name"=>self::get_new_catalog_name($cat)]);
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $cat can be id, name ot OJ_Catalogs object
	 * @return type numeric
	 */
	public static function get_catalog_id($cat)
	{
		$ret = $cat;
		if (!is_numeric($cat))
		{
			if (is_string($cat))
			{
				$cat1 = OJ_Row::load_single_object("OJ_Catalogs", ["name"=>self::get_new_catalog_name($cat)]);
				$ret = $cat1->id;
			}
			else
			{
				$ret = $cat->id;
			}
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $cat can be id, name ot OJ_Catalogs object
	 * @return type numeric
	 */
	public static function get_catalog_name($cat)
	{
		$ret = $cat;
		if (!is_string($cat) || is_numeric($cat))
		{
			if (is_numeric($cat))
			{
				$cat1 = OJ_Row::load_single_object("OJ_Catalogs", ["id"=>$cat]);
				$ret = $cat1->name;
			}
			else
			{
				$ret = $cat->name;
			}
		}
		return $ret;
	}
	
	public static function get_new_catalog_name($oldname)
	{
		switch ($oldname)
		{
			case 'rss':
			case 'Rss':
			case 'RSS':
				$ret = "RSS";
				break;
			case "addressBook":
			case "AddressBook":
				$ret = "AddressBook";
				break;
			default:
				$ret = ucfirst($oldname);
				break;
		}
		return $ret;
	}

	public static function get_parameter_value($cat, $pname)
	{
		$cid = self::get_catalog_id($cat);
		$pram = OJ_Row::load_single_object("OJ_Parameters", ["catalogs_id"=>$cid, "name"=>$pname]);
		$ret = null;
		if ($pram != null)
		{
			$ret = $pram->get_value();
		}
		return $ret;
	}
	
	public static function set_parameter_value($cat, $pname, $val)
	{
		$cid = self::get_catalog_id($cat);
		$pram = OJ_Row::load_single_object("OJ_Parameters", ["catalogs_id"=>$cid, "name"=>$pname]);
		if ($pram != null)
		{
			$pram->set_value($val);
		}
	}
	
	public static function get_parameter($cat, $pname)
	{
		$cid = self::get_catalog_id($cat);
		$pram = OJ_Row::load_single_object("OJ_Parameters", ["catalogs_id"=>$cid, "name"=>$pname]);
		$ret = null;
		if ($pram != null)
		{
			$ret = ["name" => $pname, "type" => $pram->table_name, "value" => $pram->get_value()];
		}
		return $ret;
	}
	
	public static function get_parameters_hash($cat)
	{
		$cid = self::get_catalog_id($cat);
		$prams = OJ_Row::load_hash_of_all_objects("OJ_Parameters", ["catalogs_id"=>$cid], "name");
//		var_dump($prams);
		$ret = [];
		foreach ($prams as $name => $pram)
		{
			$ret[$name] = ["name" => $name, "type" => $pram->table_name, "value" => $pram->get_value()];
		}
		return $ret;
	}
	
	public static function get_parameters($cat)
	{
//		var_dump($cat);
		$cid = self::get_catalog_id($cat);
		$prams = OJ_Row::load_hash_of_all_objects("OJ_Parameters", ["catalogs_id"=>$cid], "name");
//		var_dump($prams);
		$ret = [];
		foreach ($prams as $name => $pram)
		{
			$ret[] = ["name" => $name, "type" => $pram->table_name, "value" => $pram->get_value()];
		}
		return $ret;
	}
	
	public static function get_root_id($cat, $index = null)
	{
		$cid = self::get_catalog_id($cat);
		if ($index == null)
		{
			$index = "default";
		}
		$ret = OJ_Row::get_single_value("OJ_Indexes", "entities_id", ["name"=>$index, "catalogs_id"=>$cid]);
		if (!$ret)
		{
			$ret = OJ_Row::get_single_value("OJ_Entities", "id", ["name"=>"root", "catalogs_id"=>$cid]);
		}
		return $ret;
	}
	
	public static function get_root_link($cat, $index = null)
	{
		$rootname = $index?OJ_Indexes::get_index_name($index):"root";
		$catalogs_id = self::get_catalog_id($cat);
		$id = self::get_root_id($cat, $index);
		$ret = ["name"=>$rootname, "from_entities_id"=>0, "to_entities_id"=>$id, "type"=>"CATEGORY", "from_catalogs_id"=>$catalogs_id,
			"to_catalogs_id"=>$catalogs_id, "ordinal"=>0, "hidden"=>0, "tooltip"=>$id.": ".$rootname];
		return $ret;
	}
	
	public static function get_entity_link($cat, $ojid, $name, $type)
	{
		$catalogs_id = self::get_catalog_id($cat);
		$ret = ["name"=>$name, "from_entities_id"=>0, "to_entities_id"=>$ojid, "type"=>$type, "from_catalogs_id"=>$catalogs_id,
			"to_catalogs_id"=>$catalogs_id, "ordinal"=>0, "hidden"=>0, "tooltip"=>$ojid.": ".$name];
		return $ret;
	}
	
	public static function get_top_level_category_id($cat, $link_name)
	{
		$catalogs_id = self::get_catalog_id($cat);
		$rootid = self::get_root_id($catalogs_id);
		return OJ_Row::get_single_value("OJ_Links", "to_entities_id", ["from_entities_id"=>$rootid, "name"=>$link_name]);
	}
	
	public static function get_subtype_property($catalogs_id, $entity_types_id)
	{
		$cid = OJ_Catalogs::get_catalog_id($catalog_id);
		$etid = OJ_Entity_Types::get_entity_type_id($entity_types_id);
		$eid = 0 - (($cid * 10) + $etid);
		$subtype = OJ_Row::load_single_object("OJ_Properties", ["entities_id"=>$eid, "name"=>"subtype"]);
		$ret = null;
		if (($subtype != null) && ($subtype->id > 0))
		{
			$ret = $subtype->get_value();
		}
		return $ret;
	}
	
	public static function from_xml($xmlcat)
	{
		$catalog_name = (string)$xmlcat["name"];
		$catalog = new OJ_Catalogs(["name"=>$catalog_name]);
		$catalogs_id = $catalog->save(true);
		$root_id = 0;
		foreach($xmlcat->entity as $xmlent)
		{
			OJ_Entities::from_xml($xmlent, $catalogs_id);
			$ename = html_entity_decode((string) $xmlent->name);
			if ($ename === "default-CATEGORY")
			{
				$xmlent->name = "root";
				$root = OJ_Entities::from_xml($xmlent, $catalogs_id);
				$root_id = $root->id;
				$index = new OJ_Indexes(["name"=>"default", "catalogs_id"=>$catalogs_id, "entities_id"=>$root_id]);
				$index->save();
			}
		}
		foreach ($xmlcat->parameters->parameter as $xmlparam)
		{
			OJ_Parameters::from_xml($catalogs_id, $xmlparam);
		}
		$catalog->entities_id = $root_id;
		$catalog->save();
		return $catalog;
	}
	
	public static function has_entity($cat, $ent)
	{
		$catalogs_id = self::get_catalog_id($cat);
		$entities_id = OJ_Entities::get_entity_id($ent);
		$sql = "SELECT COUNT(*) FROM entities WHERE catalogs_id=£catalogs_id AND id=$entities_id";
		$num1 = Project_Details::get_db()->query($sql);
		$num = array_values($num1[0])[0];
		return $num > 0;
	}
	
	/**
	 * assume all categories and last one an item unless otherwise noted e.g. the last or penultimate can be name::GROUP
	 * no checks are made that this is valid.
	 * no attributes or special properties can be set
	 * @param type $catalogs_id
	 * @param type $pathname
	 */
	public static function get_or_create_entity_from_pathname($cat, $pathname)
	{
		$catalogs_id = self::get_catalog_id($cat);
		if (is_array($pathname))
		{
			$path = $pathname;
		}
		else
		{
			$path = explode("/", $pathname);
		}
		$start_index = ((strlen($path[0]) === 0) || (path[0] === 'root'))?1:0;
		$root = self::get_root_id($catalogs_id);
		$current_id = $root;
		$next_id = 0;
		$had_group = false;
		$catid = OJ_Entity_Types::get_entity_type_id("CATEGORY");
		$grpid = OJ_Entity_Types::get_entity_type_id("GROUP");
		$itmid = OJ_Entity_Types::get_entity_type_id("ITEM");
		$len = count($path);
		for ($n = $start_index; $n < $len; $n++)
		{
			$nm = $path[$n];
			$typeid = $n === $len - 1?$itmid:$catid;
			$dc = strpos($nm, "::");
			if ($dc > 0)
			{
				$typeid = OJ_Entity_Types::get_entity_type_id(strtoupper(substr($nm, $dc + 2)));
				$nm = substr($nm, 0, $dc);
			}
			$next_id = OJ_Links::get_destination_id($catalogs_id, $current_id, $nm);
			if ($next_id == 0)
			{
				$xml = '<entity type="'.$typeid.'" catalog="'.$catalogs_id.'"><name>'.$nm.'</name><properties></properties><pages></pages>'.
						'<links><link direction="from" catalog="'.$catalogs_id.'" ordinal="0" hidden="0" other="'.$current_id.'">'.$nm.'</link>'.
						'</links><children></children></entity>';
				$ent = OJ_Entities::from_xml_string($xml);
				$next_id = $ent->id;
			}
			$current_id = $next_id;
		}
		return $next_id;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("catalogs", "id", $param, "name", null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function get_olddb_name()
	{
		$nm = $this->get_name();
		switch ($nm)
		{
			case 'RSS':
				$ret = $nm;
				break;
			default:
				$ret = lcfirst(str_replace(" ", "", $nm));
				break;
		}
		return $ret;
	}

	public function to_xml_string()
	{
		$cid = $this->_data["id"];
		$ret = '<catalog name="'.$this->_data["name"].'">';
		$ret .= OJ_Entities::get_default_xml_string($cid, 1);
		$ret .= OJ_Entities::get_default_xml_string($cid, 2);
		$ret .= OJ_Entities::get_default_xml_string($cid, 3);
		$ret .= "<parameters>";
		$params = OJ_Parameters::get_all_parameters($cid);
//		var_dump($params);
		foreach ($params as $pname=>$param)
		{
			$ret .= $param->to_xml_string();
		}
		$ret .= "</parameters>";
		$ret .= "</catalog>";
		return $ret;
	}
}

class OJ_Date_Time_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("date_time_values", $param);
    }

	public function get_default_value()
	{
		return  date ("Y-m-d H:i:s");
	}

	public function get_comparison_value()
	{
		return ["type"=>0, "value"=>strtotime($this->get_value())];
	}

}

class OJ_Entities extends OJ_Database_Row
{
	
	public static function note_accessed($entities_id)
	{
		$sql = "INSERT INTO entity_access (entities_id) VALUES($entities_id) ON DUPLICATE KEY UPDATE accessed=now()";
		Project_Details::get_db()->query($sql);
	}
	
	public static function note_modified($entities_id)
	{
		$sql = "INSERT INTO entity_access (entities_id) VALUES($entities_id) ON DUPLICATE KEY UPDATE modified=now()";
		Project_Details::get_db()->query($sql);
	}
	
	public static function has_been_accessed($entities_id)
	{
		$sql = "SELECT EXISTS(SELECT 1 FROM entity_access WHERE entities_id=$entities_id)";
		$result = Project_Details::get_db()->query($sql);
		$ret = array_values($result[0])[0];
//		var_dump($ret);
		return $ret === "1";
	}
	
	/**
	 * delete an entity, its properties and attributes and all links/references to and from
	 * @param type $entities_id
	 * @param type $catalogs_id
	 */
	public static function delete_entity($entities_id, $catalogs_id = 0)
	{
		if (is_array($entities_id))
		{
			if (($catalogs_id === 0) && (count($entities_id) > 0))
			{
				$catalogs_id = self::get_catalogs_id($entities_id[0]);
			}
			foreach ($entities_id as $eid)
			{
				self::delete_entity($eid, $catalogs_id);
			}
		}
		else
		{
			$eid = self::get_entity_id($entities_id);
			if ($catalogs_id === 0)
			{
				$catalogs_id = self::get_catalogs_id($eid);
			}
			// delete properties
			$props = OJ_Row::load_array_of_objects("OJ_Properties", ["entities_id"=>$eid]);
			foreach ($props["result"] as $prop )
			{
				$prop->delete();
			}
			// delete attributes
			$atts = OJ_Row::load_array_of_objects("OJ_Attributes", ["entities_id"=>$eid]);
			foreach ($atts["result"] as $att )
			{
				$att->delete();
			}
			// delete links
			OJ_Links::delete_all_links_and_references($eid, $catalogs_id);
			// delete entity
			OJ_Row::delete_rows("entities", " id=".$eid);
		}
	}
	
	/**
	 * deletes entity if it has been accessed
	 * @param type $entities_id an entity name/object/id or an array of them
	 * @param type $catalogs_id
	 * @return type if single entity then either the entity if it has not been accessed or null if it is has (been deleted);
	 * if an array then an array containing those entities that have not been deleted.
	 */
	public static function delete_if_accessed($entities_id, $catalogs_id = 0)
	{
		$ret = null;
		if (is_array($entities_id))
		{
			$ret = [];
			if (($catalogs_id === 0) && (count($entities_id) > 0))
			{
				$catalogs_id = self::get_catalogs_id($entities_id[0]);
			}
			foreach ($entities_id as $eid)
			{
				if (self::delete_if_accessed($eid, $catalogs_id))
				{
					array_push($ret, $eid);
				}
			}
		}
		else
		{
			$ret = $entities_id;
			$eid = self::get_entity_id($entities_id);
			if (self::has_been_accessed($eid))
			{
				self::delete_entity($eid, $catalogs_id);
				$ret = null;
			}
		}
		return $ret;
	}
	
	public static function tree_delete($entities_id, $catalogs_id = 0)
	{
		if ($catalogs_id === 0)
		{
			$catalogs_id = self::get_catalogs_id($entities_id);
		}
		$etid = OJ_Entities::get_entity_types_id($entities_id);
		$etnm = OJ_Entity_Types::get_entity_type_name($etid);
		$lnks = OJ_Links::get_all_links_from($catalogs_id, $entities_id);
		self::delete_entity($entities_id, $catalogs_id);
		if (count($lnks) > 0)
		{
			foreach ($lnks as $lnk)
			{
				$lnksto = OJ_Links::get_all_links_to($catalogs_id, $lnk->to_entities_id);
				if (count($lnksto) === 1)
				{
					self::tree_delete($lnk->to_entities_id, $catalogs_id);
				}
				else
				{
					$lnk->delete();
				}
			}
		}
	}
	
	public static function get_catalogs_id($ent)
	{
		$eid = self::get_entity_id($ent);
		$ret = OJ_Row::get_single_value("OJ_Entities", "catalogs_id", ["id"=>$eid]);
		return $ret;
	}
	
	public static function get_property_value($ent, $pname, $display = false)
	{
		$eid = self::get_entity_id($ent);
		$ret = null;
		if (($pname === 'subtype') && $display)
		{
			$ret = OJ_Row::get_single_value("OJ_Subtypes", "name", ["id"=>$eid]);
		}
		if (!$ret)
		{
			$prop = OJ_Row::load_single_object("OJ_Properties", ["entities_id"=>$eid, "name"=>$pname]);
	//		var_dump($prop);
			if ($prop != null)
			{
				$id = $prop->values_id;
				if ($id > 0)
				{
					$tabname = $prop->table_name;
					if ($tabname === 'enumeration')
					{
						$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
						$ret = $enumval->value;
						if (!$display)
						{
							$enumid = $enumval->enumerations_id;
							$ret .= '('.OJ_Row::get_single_value("OJ_Enumerations", "name", ["id"=>$enumid]).')';
						}
					}
					else
					{
						$tname = $tabname."_values";
						$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
						if (class_exists($cname))
						{
							$obj = new $cname($id);
							if ($display)
							{
								$ret = self::expand_attribute_value($eid, $obj->get_value());
							}
							else
							{
								$ret = $obj->get_value();
							}
						}
					}
				}
			}
		}
		return $ret;
	}
	
	public static function get_properties($ent)
	{
		$eid = self::get_entity_id($ent);
		$props = OJ_Row::load_hash_of_all_objects("OJ_Properties", ["entities_id"=>$eid], "name");
//		var_dump($props);
		$ret = [];
		foreach ($props as $name => $prop)
		{
			$ret[] = ["name" => $name, "type" => $prop->table_name, "value" => $prop->get_value()];
		}
		return $ret;
	}
	
	public static function get_default_properties($catalog_id, $entity_types_id)
	{
		$cid = OJ_Catalogs::get_catalog_id($catalog_id);
		$etid = OJ_Entity_Types::get_entity_type_id($entity_types_id);
		$eid = 0 - (($cid * 10) + $etid);
		$props = OJ_Row::load_hash_of_all_objects("OJ_Properties", ["entities_id"=>$eid], "name");
//		var_dump($props);
		$ret = [];
		foreach ($props as $name => $prop)
		{
			$ret[] = ["name" => $name, "type" => $prop->type, "value" => $prop->get_value()];
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $ent can be id, name ot OJ_Catalogs object
	 * @return type OJ_Catalogs object
	 */
	public static function get_entity($ent)
	{
		$ret = $ent;
		if (is_numeric($ent))
		{
			$ret = OJ_Row::load_single_object("OJ_Entities", ["id"=>$ent]);
		}
		else if (is_string($ent))
		{
			$ret = OJ_Row::load_single_object("OJ_Entities", ["name"=>$ent]);
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $ent can be id, name ot OJ_Entitiess object
	 * @return type numeric
	 */
	public static function get_entity_id($ent)
	{
		$ret = $ent;
		if (!is_numeric($ent))
		{
			if (is_string($ent))
			{
				$entity = OJ_Row::load_single_object("OJ_Entities", ["name"=>$ent]);
				$ret = $entity->id;
			}
			else if ($ent != null)
			{
				$ret = $ent->id;
			}
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $ent can be id, name ot OJ_Entitiess object
	 * @return type numeric
	 */
	public static function get_entity_name($ent)
	{
		$ret = $ent;
		if (!is_string($ent) || is_numeric($ent))
		{
			if (is_numeric($ent))
			{
				$entity = OJ_Row::load_single_object("OJ_Entities", ["id"=>$ent]);
				$ret = $entity->name;
			}
			else
			{
				$ret = $ent->name;
			}
		}
		return $ret;
	}
	
	public static function get_entity_types_id($ent)
	{
		$entities_id = self::get_entity_id($ent);
		return OJ_Row::get_single_value("OJ_Entities", "entity_types_id", ["id"=>$entities_id]);
	}
	
	public static function get_entity_types_name($ent)
	{
		return OJ_Row::get_single_value("OJ_Entity_Types", "name", ["id"=>  self::get_entity_types_id($ent)]);
	}
	
	public static function expand_attribute_value($entity_id, $ret)
	{
		$ret1 = $ret;
		if ($ret != null)
		{
			$offset = 0;
			$vstart = strpos($ret, '${', $offset);
			if ($vstart !== FALSE)
			{
				$ret1 = "";
				$vend = 0;
				while ($vstart !== FALSE)
				{
//					print "1. $vstart $vend $offset $ret1\n";
					$addon = substr($ret, $offset, $vstart - $offset);
					$vend = strpos($ret, '}', $offset + 2);
					if ($vend > $vstart)
					{
//						print "1a. $vstart $vend $offset $ret1\n";
						$var = substr($ret, $vstart + 2, $vend - $vstart - 2);
						$dc = strpos($var, '::', 0);
						if ($dc === FALSE)
						{
//							print "here\n";
							$lg = OJ_Logicals::get_logical($var);
//							var_dump($lg);
							if ($lg != null)
							{
								$val = $lg->get_value();
								if (!OJ_Utilities::ends_with($val, "/"))
								{
									$val .= "/";
								}
								$ret1 .= $addon.$val;
							}
						}
						else
						{
							$pn = substr($var, 0, $dc);
							$an = substr($var, $dc + 2);
							$val = self::get_attribute_value($entity_id, $pn, $an);
//							print "value of $pn::$an is $val from $var\n";
							if ($val != null)
							{
								$ret1 .= $addon.$val;
							}
						}
						$offset = $vend + 1;
						$vstart = strpos($ret, '${', $offset);
					}
					else
					{
						$vstart = FALSE;
					}
//					print "2. $vstart $vend $offset $ret1\n";
				}
				$ret1 .= substr($ret, $offset);
//				print "$ret expanded to $ret1\n";
			}
		}
		return $ret1;
	}
	
	public static function get_attribute($entities_id, $page_name, $attribute_name)
	{
		OJ_Logger::get_logger()->ojdebug("inside", $entities_id, $page_name, $attribute_name);
		$ret = null;
		$sql = "SELECT attribute_types.name as type, attribute_types.table_name as tname, attributes.values_id AS values_id, attributes.name AS name,".
				" attributes.visible as visible, attributes.ordinal as ordinal FROM entities JOIN attributes ON attributes.entities_id = entities.id JOIN attribute_types".
				" ON attributes.attribute_types_id = attribute_types.id JOIN pages ON pages.catalogs_id = entities.catalogs_id AND".
				" pages.entity_types_id = entities.entity_types_id AND attributes.pages_id = pages.id WHERE entities.id = $entities_id";
		if ($attribute_name != null)
		{
			$sql .= " AND attributes.name = '$attribute_name'";
		}
		if ($page_name != null)
		{
			$sql .= " AND pages.name = '$page_name'";
		}
		$res = [];
		Project_Details::get_db()->loadHash($sql, $res);
		OJ_Logger::get_logger()->ojdebug($sql, $res);
		if (isset($res['name']) && ($res['name'] !== null))
		{
			$nm = $res['name'];
			$id = $res['values_id'];
			$val = null;
			if ($id > 0)
			{
				$tname = $res['tname']."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
	//				print "id $id, from table $tname, class $cname\n";
				if (class_exists($cname))
				{
					$obj = new $cname($id);
	//						var_dump($obj);
					$val = self::expand_attribute_value($entities_id, $obj->get_value());
	//						print" got value $val\n";
				}
				if ($res['type'] === 'boolean')
				{
					$val = $val != 'false';
				}
			}
			$ret = ['name'=>$nm, "type"=>$res['type'], "ordinal"=>$res['ordinal'], "visible"=>($res['visible'] == 1), "value"=>  stripslashes($val)];
		}
		return $ret;
	}
	
	public static function add_attribute($entity_id, $page_name, $att)
	{
		$catalogs_id = self::get_catalogs_id($entity_id);
		$pg = OJ_Pages::get_page($catalogs_id, $entity_id, $page_name);
		$val = new OJ_String_Values(["value"=>$att["value"]]);
		$valid = $val->save();
		$natt = ["name"=>$att['name'], "attribute_types_id"=> 1, "entities_id"=>$entity_id, "pages_id"=>$pg->id, "ordinal"=>$att['ordinal'],
			"visible"=>$att['visible'], "values_id"=>$valid];
		$newatt = new OJ_Attributes($natt);
		return $newatt->save();
	}

	public static function get_attribute_value($entity_id, $page_name, $attribute_name = null, $display = true)
	{
		$al = strtolower($page_name);
		if ($al === "ojname")
		{
			$ret = self::get_entity_name($entity_id);
		}
		else if ($al === "ojid")
		{
			$ret = self::get_entity_id($entity_id);
		}
		else
		{
			if ($attribute_name == null)
			{
				$dc = strpos($page_name, "::");
				if ($dc !== FALSE)
				{
					$attribute_name = substr($page_name, $dc + 2);
					$page_name = substr($page_name, 0, $dc);
				}
			}
			$sql = "SELECT attribute_types.table_name, attributes.values_id FROM entities ".
					"JOIN attributes ON attributes.entities_id = entities.id ".
					"JOIN attribute_types ON attributes.attribute_types_id = attribute_types.id ".
					"JOIN pages ON pages.catalogs_id = entities.catalogs_id AND pages.entity_types_id = entities.entity_types_id AND attributes.pages_id = pages.id ".
					"WHERE entities.id = ".$entity_id." AND pages.name = '".$page_name."' AND attributes.name = '".$attribute_name."'";
			$result = [];
			$ret = null;
//			OJ_Logger::get_logger()->ojdebug1("sql ".$sql);
			if (Project_Details::get_db()->loadHash($sql, $result))
			{
//				OJ_Logger::get_logger()->ojdebug("result", $result);
				$id = $result['values_id'];
				if ($id > 0)
				{
					$tabname = $result['table_name'];
					if ($tabname === 'enumeration')
					{
						$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
						$ret = $enumval->value;
						if (!$display)
						{
							$enumid = $enumval->enumerations_id;
							$ret .= '('.OJ_Row::get_single_value("OJ_Enumerations", "name", ["id"=>$enumid]).')';
						}
					}
					else
					{
						$tname = $tabname."_values";
						$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
						if ($display && class_exists($cname))
						{
							$obj = new $cname($id);
							$ret = self::expand_attribute_value($entity_id, $obj->get_value());
						}
					}
				}
			}	
		}
		return $ret;
	}
	
	public static function set_attribute_value($entity_id, $page_name, $attribute_name, $newval)
	{
		$sql = "SELECT attributes.id, attribute_types.table_name, attributes.values_id FROM entities ".
				"JOIN attributes ON attributes.entities_id = entities.id ".
				"JOIN attribute_types ON attributes.attribute_types_id = attribute_types.id ".
				"JOIN pages ON pages.catalogs_id = entities.catalogs_id AND pages.entity_types_id = entities.entity_types_id AND attributes.pages_id = pages.id ".
				"WHERE entities.id = ".$entity_id." AND pages.name = '".$page_name."' AND attributes.name = '".$attribute_name."'";
		$result = [];
		$ret = null;
		if (Project_Details::get_db()->loadHash($sql, $result))
		{
//			echo $sql."\n";
//			var_dump($result);
			$id = $result['values_id'];
			$tabname = $result['table_name'];
			$attid = $result["id"];
			if ($tabname === 'enumeration')
			{
				if ($id > 0)
				{
					$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
					$lb = strpos($newval, '(');
					$nv = $lb > 0?substr($newval, 0, $lb):$newval;
					$enumval->value = $nv;
					$enumval->save();
				}
				else
				{
					$lb = strpos($newval, '(');
					if ($lb > 0)
					{
						$rb = strpos($newval, ')');
						$enumname = substr($newval, $lb + 1, $rb - $lb - 1);
						$nv = substr($newval, 0, $lb);
						$enum = OJ_Row::load_single_object("OJ_Enumerations", ["name"=>$enumname]);
						if ($enum != null)
						{
							$enumval = new OJ_Enumeration_Values(["enumerations_id"=>$enum->id, "name"=>$nv]);
							$enumval->save();
						}
					}
				}
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
//				echo $cname."\n";
				if (class_exists($cname))
				{
					if ($id > 0)
					{
						$obj = new $cname($id);
//						var_dump($obj);
						$obj->value = $newval;
						$obj->save();
					}
					else
					{
						$obj = new $cname(["value"=>$newval]);
//						var_dump($obj);
						$values_id = $obj->save();
//						echo "values_id ".$values_id."\n";
						$att = new OJ_Attributes($attid);
						$att->values_id = $values_id;
						$att->save();
					}
				}
			}
		}
		else
		{
			OJ_Logger::get_logger()->ojdebug1($sql);
			echo $sql;
		}
	}
	
	public static function get_attributes($entity_id, $catalog_id = 0)
	{
		return $entity_id == 0?null:self::get_entity_attributes($entity_id, $catalog_id);
	}
	
	public static function get_visible_attributes($entity_id, $catalog_id = 0)
	{
		return $entity_id == 0?null:self::get_entity_attributes($entity_id, $catalog_id, 1);
	}
	
	public static function get_hidden_attributes($entity_id, $catalog_id = 0)
	{
		return $entity_id == 0?null:self::get_entity_attributes($entity_id, $catalog_id, 0);
	}
	
	public static function get_default_attributes($catalog_id, $entity_types_id)
	{
		$cid = OJ_Catalogs::get_catalog_id($catalog_id);
		$etypeid = 0 - OJ_Entity_Types::get_entity_type_id($entity_types_id);
//		OJ_Logger::get_logger()->ojdebug("get_default_attributes", $catalog_id, $entity_types_id, $cid, $etypeid);
		return $cid == 0?null:self::get_entity_attributes($etypeid, $cid);
	}
	
	private static function get_entity_attributes($entity_id, $catalog_id = 0, $visible = -1)
	{
//		OJ_Logger::get_logger()->ojdebug("get_entity_attributes", $entity_id, $catalog_id);
		$ret = [];
		$vis = "";
		if (($visible === 0) || ($visible === 1))
		{
			$vis = " AND attributes.visible = $visible";
		}
		if (($catalog_id === 0) || ($catalog_id === '0'))
		{
			$catalog_id = self::get_catalogs_id($entity_id);
		}
//		print $entity_id." ".$catalog_id;
		if (($entity_id > 0) || (($entity_id < 0) && ($catalog_id > 0)))
		{
			$rest = "";
			if ($entity_id < 0)
			{
				$etype = OJ_Entity_Types::get_entity_type_id($entity_id);
				$sql = "SELECT attribute_types.id as atypeid, attribute_types.name as type, attribute_types.table_name as tname, attributes.values_id AS values_id, ".
						"pages.name AS page, pages.id AS pageid, attributes.name AS name, attributes.visible as visible, ".
						"attributes.ordinal as ordinal, attributes.id as attid FROM attributes  ".
						"JOIN attribute_types ON attributes.attribute_types_id = attribute_types.id ".
						"JOIN pages ON  attributes.pages_id = pages.id ".
						"WHERE pages.catalogs_id = $catalog_id AND pages.entity_types_id = $etype AND attributes.entities_id = 0".$vis;
			}
			else
			{
				$sql = "SELECT attribute_types.id as atypeid, attribute_types.name as type, attribute_types.table_name as tname, attributes.values_id AS values_id, ".
						"pages.name AS page, pages.id AS pageid, attributes.name AS name, ".
						"attributes.visible as visible, attributes.ordinal as ordinal, attributes.id as attid FROM entities ".
						"JOIN attributes ON attributes.entities_id = entities.id ".
						"JOIN attribute_types ON attributes.attribute_types_id = attribute_types.id ".
						"JOIN pages ON pages.catalogs_id = entities.catalogs_id AND pages.entity_types_id = entities.entity_types_id AND attributes.pages_id = pages.id ".
						"WHERE entities.id = ".$entity_id.$vis;
			}
//			OJ_Logger::get_logger()->ojdebug("sql", $sql);
//			print $sql;
			$result = Project_Details::get_db()->loadList($sql);
			foreach ($result as $res)
			{
	//			var_dump($res);
				$pg = $res['page'];
				$pgid = $res['pageid'];
				$atypeid = $res['atypeid'];
				$attid = $res["attid"];
				$nm = $res['name'];
				if (!array_key_exists($pg, $ret))
				{
					$ret[$pg] = [];
				}
				$id = $res['values_id'];
				$val = null;
				if ($id > 0)
				{
					$tname = $res['tname']."_values";
					$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
	//				print "id $id, from table $tname, class $cname\n";
					if (class_exists($cname))
					{
						$obj = new $cname($id);
//						var_dump($obj);
						$val = self::expand_attribute_value($entity_id, $obj->get_value());
//						print" got value $val\n";
					}
					if ($res['type'] === 'boolean')
					{
						$val = $val != 'false';
					}
				}
				$ret[$pg][$nm] = ['id'=>$attid, 'page'=>$pg, 'pageid'=>$pgid, 'atypeid'=>$atypeid, 'entity'=>$entity_id, 'name'=>$nm, "type"=>$res['type'], "ordinal"=>$res['ordinal'], "visible"=>($res['visible'] == 1), "value"=>  stripslashes($val)];
			}
		}
		return $ret;
	}
	
	private static function create_attributes_from_default($entities_id, $catalogs_id, $entity_types_id)
	{
		$pages = self::get_default_attributes($catalogs_id, $entity_types_id);
		foreach ($pages as $pname=>$pg)
		{
//			OJ_Logger::get_logger()->ojdebug1("create default attributes for page ".$pname." on entity ".$entities_id." of type ".$entity_types_id." in catalog ".$catalogs_id);
			foreach ($pg as $aname=>$att)
			{
				$hash = ["name"=>$aname, "pages_id"=>$att["pageid"], "attribute_types_id"=>$att["atypeid"], "entities_id"=>$entities_id, "values_id"=>0,
					"visible"=>($att["visible"]?1:0), "ordinal"=>$att["ordinal"]];
//				OJ_Logger::get_logger()->ojdebug("create default attribute", $hash);
				$attribute = new OJ_Attributes($hash);
				$attribute->save();
			}
		}
	}
	
	public static function get_forward_links($catalog_id, $entity_id, $includeX = false, $onlyX = false, $entity_type = null)
	{
		$et = "";
		if ($entity_type)
		{
			$et = " AND ";
			if (is_array($entity_type) && (count($entity_type) > 0))
			{
				$et .= "(links.entity_types_id = ".  OJ_Entity_Types::get_entity_type_id($entity_type[0]);
				for ($n = 1; $n < count($entity_type); $n++)
				{
					$et .= " OR links.entity_types_id = ".  OJ_Entity_Types::get_entity_type_id($entity_type[n]);
				}
				$et .= ")";
			}
			else
			{
				$et .= "links.entity_types_id = ".  OJ_Entity_Types::get_entity_type_id($entity_type);
			}
		}
		$crossref1 = $onlyX?" AND links.to_catalogs_id != '$catalog_id'":"";
		$crossref = $includeX?$crossref1:" AND links.to_catalogs_id = '$catalog_id'";
		$sql = "SELECT links.name as name, links.from_entities_id as 'from_entities_id', links.to_entities_id as 'to_entities_id', links.ordinal as ordinal, links.hidden as hidden, ".
				"entity_types.name as type, links.to_catalogs_id as to_catalogs_id, links.from_catalogs_id as from_catalogs_id, links.tooltip as tooltip FROM links ".
				"JOIN entity_types ON links.entity_types_id = entity_types.id ".
				"WHERE links.from_entities_id = '".$entity_id."'".$et.
				" AND links.from_catalogs_id = '$catalog_id' $crossref ORDER BY links.ordinal";
		$ret = Project_Details::get_db()->loadList($sql);
//		echo $sql;
//		var_dump($ret);
		return $ret;
	}
	
	public static function get_child_entities($entity, $catalogs_id = 0)
	{
		$entities_id = self::get_entity_id($entity);
		if ($catalogs_id === 0)
		{
			$catalogs_id = self::get_catalogs_id($entities_id);
		}
		$sql = "SELECT entities.* FROM entities JOIN links ON links.to_entities_id=entities.id WHERE links.from_entities_id=$entities_id ORDER BY links.ordinal";
		return Project_Details::get_db()->loadList($sql);
	}

	public static function get_references($from_catalogs_id, $entity_id, $to_catalogs_id)
	{
		$crossref1 = $onlyX?" AND links.to_catalogs_id != '$catalog_id'":"";
		$crossref = $includeX?$crossref1:" AND links.to_catalogs_id = '$catalog_id'";
		$sql = "SELECT links.name as name, links.from_entities_id as 'from', links.to_entities_id as 'to', links.ordinal as ordinal, links.hidden as hidden, ".
				"entity_types.name as type, links.to_catalogs_id as catalog FROM links JOIN entity_types ON links.entity_types_id = entity_types.id ".
				"WHERE links.from_entities_id = '".$entity_id.
				"' AND links.from_catalogs_id = '$from_catalogs_id'  AND links.to_catalogs_id = '$to_catalogs_id' ORDER BY links.ordinal";
		$ret = Project_Details::get_db()->loadList($sql);
//		echo $sql;
//		var_dump($ret);
		return $ret;
	}

	public static function get_backward_links($catalog_id, $entity_id, $includeX = false)
	{
		$crossref = $includeX?"":" AND links.from_catalogs_id = '$entity_id'";
		$sql = "SELECT links.name as name, links.from_entities_id as 'from', links.to_entities_id as 'to', links.ordinal as ordinal, links.hidden as hidden, ".
				"entity_types.name as type FROM links JOIN entity_types ON links.entity_types_id = entity_types.id WHERE links.to_entities_id = '".$entity_id.
				"' AND links.from_catalogs_id = '$catalog_id' $crossref ORDER BY links.ordinal";
		$ret = Project_Details::get_db()->loadList($sql);
		return $ret;
	}

	public static function set_property($entities_id, $pname, $pval)
	{
		$ret = null;
		if ($pval !== null)
		{
			$lp = strpos($pval, '(');
			if ($lp > 0)
			{
				$ret = self::set_enumeration_property($entities_id, $pname, $pval);
			}
			else
			{
				$ret = self::set_string_property($entities_id, $pname, $pval);
			}
		}
		return $ret;
	}
	
	private static function set_enumeration_property($entities_id, $pname, $pval)
	{
		$lp = strpos($pval, '(');
		$rp = strpos($pval, ')');
		$eval = substr($pval, 0, $lp);
		$enumval = substr($pval, $lp + 1, $rp - $lp - 1);
		$enum = OJ_Row::load_single_object("OJ_Enumerations", ["name"=>$enumval]);
		if ($enum == null)
		{
			$enum = new OJ_Enumerations(["name"=>$enumval]);
			$enum->save();
		}
		$enumid = $enum->id;
		$prop = OJ_Row::load_single_object("OJ_Properties", ["name"=>$pname, "entities_id"=>$entities_id]);
		if ($prop == null)
		{
			$propval = new OJ_Enumeration_Values(["value"=>$eval, "enumerations_id"=>$enumid]);
			$propid = $propval->save();
			$prop = new OJ_Properties(["name"=>$pname, "table_name"=>"enumeration", "values_id"=>$propid, "entities_id"=>$entities_id]);
			$prop->save();
		}
		else
		{
			$values_id = $prop->values_id;
			if ($values_id > 0)
			{
				$propval = new OJ_Enumeration_Values($values_id);
				$propval->value = $eval;
				$propval->save();
			}
			else
			{
				$propval = new OJ_Enumeration_Values(["value"=>$eval, "enumerations_id"=>$enumid]);
				$prop->values_id = $propval->save();
				$prop->save();
			}
		}
		return $prop;
	}
	
	private static function set_string_property($entities_id, $pname, $pval)
	{
		$prop = OJ_Row::load_single_object("OJ_Properties", ["name"=>$pname, "entities_id"=>$entities_id]);
		if ($prop == null)
		{
			$propval = new OJ_String_Values(["value"=>$pval]);
			$propid = $propval->save();
			$prop = new OJ_Properties(["name"=>$pname, "table_name"=>"string", "values_id"=>$propid, "entities_id"=>$entities_id]);
			$prop->save();
		}
		else
		{
			$values_id = $prop->values_id;
			if ($values_id > 0)
			{
				$propval = new OJ_String_Values($values_id);
				$propval->value = $pval;
				$propval->save();
			}
			else
			{
				$propval = new OJ_String_Values(["value"=>$pval]);
				$prop->values_id = $propval->save();
				$prop->save();
			}
		}
		return $prop;
	}
	
	public static function get_default_xml_string($catalogs_id, $entity_types_id)
	{
		if (!is_numeric($catalogs_id))
		{
			$catalogs_id = OJ_Catalogs::get_catalog_id($catalogs_id);
		}
		if (is_numeric($entity_types_id))
		{
			$etname = OJ_Row::get_single_value("OJ_Entity_Types", "name", ["id"=>$entity_types_id]);
		}
		else
		{
			$etname = $entity_types_id;
			$entity_types_id = OJ_Row::get_single_value("OJ_Entity_Types", "id", ["name"=>$etname]);
		}
		$ent = new OJ_Entities(["id"=>0, "catalogs_id"=>$catalogs_id, "entity_types_id"=>$entity_types_id, "name"=>"default-".$etname]);
//		var_dump($ent);
		return $ent->to_xml_string();
	}
	
	public static function from_xml($xmlent, $catid = 0)
	{
		if (isset($xmlent["id"]))
		{
			$ent = new OJ_Entities(intval((string)$xmlent["id"]));
		}
		else
		{
			$catalogs_id = OJ_Catalogs::get_catalog_id((string) $xmlent['catalog']);
			if ($catalogs_id == 0)
			{
				$catalogs_id = $catid;
			}
			$entity_types_id = OJ_Entity_Types::get_entity_type_id((string) $xmlent['type']);
			$entity_types_name = OJ_Entity_Types::get_entity_type_name($entity_types_id);
			$ename = html_entity_decode((string) $xmlent->name);
			if ($ename === ("default-".  strtoupper($entity_types_name)))
			{
				if (isset($xmlent->properties) && ($xmlent->properties->count() > 0))
				{
					$eid = 0 - (($catalogs_id * 10) + $entity_types_id);
					foreach ($xmlent->properties->property as $xmlprop)
					{
						OJ_Properties::from_xml($eid, $xmlprop);
					}
				}
				if ($xmlent->pages->count() > 0)
				{
					foreach ($xmlent->pages->page as $xmlpg)
					{
						OJ_Pages::from_xml($catalogs_id, 0, $entity_types_id, $xmlpg);
					}
				}
				$ent = null;
			}
			else
			{
				$hash = ['name'=>$ename, "catalogs_id"=>$catalogs_id, "entity_types_id"=>$entity_types_id];
				$ent = new OJ_Entities($hash);
				$entities_id = $ent->save(true);
	//			print "1.saved\n";
				$type_property_value = new OJ_String_Values(["value"=>$entity_types_name]);
				$type_property_id = $type_property_value->save();
	//			print "2.saved\n";
				$name_property_value = new OJ_String_Values(["value"=>$ename]);
				$name_property_id = $name_property_value->save();
	//			print "3.saved\n";
				$type_property = new OJ_Properties(["name"=>"type", "entities_id"=>$entities_id, "values_id"=>$type_property_id, "table_name"=>"string"]);
				$type_property->save(true);
	//			print "4.saved\n";
				$name_property = new OJ_Properties(["name"=>"name", "entities_id"=>$entities_id, "values_id"=>$name_property_id, "table_name"=>"string"]);
				$name_property->save(true);
	//			print "5.saved\n";
				if (isset($xmlent->properties) && ($xmlent->properties->count() > 0))
				{
					foreach ($xmlent->properties->property as $xmlprop)
					{
						OJ_Properties::from_xml($entities_id, $xmlprop);
					}
	//				print "6.saved\n";
				}
				self::create_attributes_from_default($entities_id, $catalogs_id, $entity_types_id);
				if ($xmlent->pages->count() > 0)
				{
					foreach ($xmlent->pages->page as $xmlpg)
					{
						OJ_Pages::from_xml($catalogs_id, $entities_id, $entity_types_id, $xmlpg);
					}
	//				print "7.saved\n";
				}
//				OJ_Logger::get_logger()->ojdebug1("doing links");
				if ($xmlent->links->count() > 0)
				{
					foreach($xmlent->links->link as $xmllnk)
					{
						OJ_Links::from_xml($catalogs_id, $entities_id, $xmllnk);
					}
	//				print "8.saved\n";
				}
//				OJ_Logger::get_logger()->ojdebug1("doing children");
				if ($xmlent->children->count() > 0)
				{
					$ord = 0;
					foreach($xmlent->children->entity as $xmlchild)
					{
						$childent = self::from_xml($xmlchild);
						$hash = ["name"=>$childent->name, "hidden"=>0, "ordinal"=>$ord, "from_catalogs_id"=>$catalogs_id, "from_entities_id"=>$entities_id,
						"to_catalogs_id"=>$catalogs_id, "to_entities_id"=>$childent->id];
						$etid = OJ_Row::get_single_value("OJ_Entities", "entity_types_id", ["id"=>$hash["to_entities_id"]]);
						$hash["entity_types_id"] = $etid;
						$lnk = new OJ_Links($hash);
						$lnk->save(true);
						$ord++;
					}
	//				print "9.saved\n";
				}
//				OJ_Logger::get_logger()->ojdebug1("finished");

			}
		}
		return $ent;
	}
	
	public static function from_xml_string($xmlstr)
	{
		if (is_array($xmlstr))
		{
			$ret = [];
			foreach ($xmlstr as $xs)
			{
				array_push($ret, self::from_xml_string($xs));
			}
		}
		else
		{
//		OJ_Logger::get_logger()->ojdebug1($xmlstr);
			$xmlent = new SimpleXMLElement($xmlstr);
			$ret = self::from_xml($xmlent);
		}
		return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("entities", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function to_xml_string($include_id = false)
	{
		$pgs = OJ_Pages::get_all_pages($this->_data["entity_types_id"], $this->_data["catalogs_id"]);
		$lnksfrom = OJ_Links::get_all_references_from($this->_data["catalogs_id"], $this->_data["id"]);
		$children = OJ_Links::get_local_links_from($this->_data["catalogs_id"], $this->_data["id"]);
		$lnksto = OJ_Links::get_all_links_and_references_to($this->_data["catalogs_id"], $this->_data["id"]);
		$props = OJ_Properties::get_extra_properties_for_entity($this->_data["id"]);
		$id = $include_id?' id="'.$this->_data["id"].'"':"";
		$ret = '<entity'.$id.' type="'.$this->_data["entity_types_id"].'" catalog="'.$this->_data["catalogs_id"].'"><name>'.
				htmlentities(stripslashes($this->_data["name"])).'</name>';
		if ($props && (count($props) > 0))
		{
			$ret .= "<properties>";
			foreach ($props as $prop)
			{
				$ret .= $prop->to_xml_string();
			}
			$ret .= "</properties>";
		}
		$ret .= '<pages>';
		foreach ($pgs as $pname=>$pg)
		{
			$ret .= $pg->to_xml_string($this->_data["id"]);
		}
		$ret .= "</pages><links>";
		foreach ($lnksfrom as $lnk)
		{
			$ret .= $lnk->to_xml_string("from");
		}
		foreach ($lnksto as $lnk)
		{
			$ret .= $lnk->to_xml_string("to");
		}
		$ret .= "</links><children>";
		foreach ($children as $childlnk)
		{
			$child = new OJ_Entities($childlnk->to_entities_id);
			$ret .= $child->to_xml_string($include_id);
		}
		$ret .= "</children></entity>";
		return $ret;
	}

}

class OJ_Entity_Types extends OJ_Database_Row
{

	public static function get_all_entity_types()
	{
		return OJ_Row::load_hash_of_all_objects("OJ_Entity_Types", null, "name");
	}
	
	public static function get_all_entity_type_ids()
	{
		$ret = [];
		$ets = OJ_Row::load_hash_of_all_objects("OJ_Entity_Types", null, "name");
		foreach ($ets as $etname=>$et)
		{
			$ret[strtoupper($etname)] = $et->id;
		}
		return $ret;
	}
	
	public static function get_all_entity_type_names()
	{
		$ret = [];
		$ets = OJ_Row::load_array_of_objects("OJ_Entity_Types");
		foreach ($ets["result"] as $et)
		{
			$ret[$et->id] = $et->name;
		}
		return $ret;
	}
	
	public static function can_contain($entity_types_id1, $entity_types_id2)
	{
		$all_entity_type_names = self::get_all_entity_type_names();
		return self::can_contain_quick($all_entity_type_names, $entity_types_id1, $entity_types_id2);
	}
	
	public static function can_contain_quick($all_entity_type_names, $entity_types_id1, $entity_types_id2)
	{
//		var_dump($all_entity_type_names, $entity_types_id1, $entity_types_id2);
		$nm1 = $all_entity_type_names[intval($entity_types_id1)];
		$nm2 = $all_entity_type_names[intval($entity_types_id2)];
		$ret = true;
		switch ($nm1)
		{
			case "ITEM":
				$ret = false;
				break;
			case "GROUP":
				$ret = $nm2 === "ITEM";
				break;
			default:
				break;
		}
		return $ret;
	}
	
	public static function get_entity_type($etype)
	{
		$ret = OJ_Row::load_single_object("OJ_Entity_Types", ["name" => strtoupper($etype)]);
//		var_dump($etype, $ret);
		return $ret;
	}
	
	public static function get_entity_type_name($etype)
	{
		if (is_numeric($etype))
		{
			$ent = OJ_Row::load_single_object("OJ_Entity_Types", ["id" => abs($etype)]);
			$ret = $ent->name;
		}
		else if (is_string($etype))
		{
			$ret = $etype;
		}
		else
		{
			$ret = $etype->name;
		}
		return $ret;
	}

	public static function get_entity_type_id($etype)
	{
		$ret = 0;
		if (is_numeric($etype))
		{
			$ret = abs($etype);
		}
		else if (is_string($etype))
		{
			$ent = OJ_Row::load_single_object("OJ_Entity_Types", ["name" => strtoupper($etype)]);
			if ($ent)
			{
				$ret = $ent->id;
			}
			else
			{
				OJ_Logger::get_logger()->ojerror("no such entity type", $etype);
			}
		}
		else
		{
			$ret = $etype->id;
		}
		return $ret;
	}

    public function __construct($param = null)
    {
        parent::__construct("entity_types", "id", $param, "name", null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Float_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("float_values", $param);
    }

	public function get_default_value()
	{
		return  "0.0";
	}

	public function get_comparison_value()
	{
		return ["type"=>0, "value"=>floatval($this->get_value())];
	}

}

class OJ_Indexes extends OJ_Database_Row
{
	
	public static function get_all_indexes($cat)
	{
//		var_dump($cat);
		$cid = OJ_Catalogs::get_catalog_id($cat);
		$all = OJ_Row::load_array_of_objects("OJ_Indexes", ["catalogs_id"=>$cid]);
//		var_dump($all);
		$ret = [];
		foreach ($all["result"] as $idx)
		{
			$ret[] = ["name"=>$idx->name, "to"=>$idx->entities_id, "omit"=>$idx->omit];
		}
		return $ret;
	}
	
	public static function get_index($cat, $name)
	{
		$cid = OJ_Catalogs::get_catalog_id($cat);
		return OJ_Row::load_single_object("OJ_Indexes", ["catalogs_id"=>$cid, "name"=>$name]);
	}

	/**
	 * 
	 * @param type $ind can be id, name ot OJ_Catalogs object
	 * @return type numeric
	 */
	public static function get_index_id($catalog, $ind)
	{
		$ret = $ind;
		if (!is_numeric($ind))
		{
			if (is_string($ind))
			{
				$catid = OJ_Catalogs::get_catalog_id($catalog);
//				var_dump($catalog, $ind, $catid);
				$ind1 = OJ_Row::load_single_object("OJ_Indexes", ["name"=>$ind, "catalogs_id"=>$catid]);
				$ret = $ind1==null?0:$ind1->id;
			}
			else
			{
				$ret = $ind->id;
			}
		}
		return $ret;
	}
	
	/**
	 * 
	 * @param type $ind can be id, name ot OJ_Catalogs object
	 * @return type numeric
	 */
	public static function get_index_name($ind)
	{
		$ret = $ind;
		if (!is_string($ind) || is_numeric($ind))
		{
			if (is_numeric($ind))
			{
				$ind1 = OJ_Row::load_single_object("OJ_Indexes", ["id"=>$ind]);
				$ret = $ind1 == null?"default":$ind1->name;
			}
			else
			{
				$ret = $ind->name;
			}
		}
		return $ret;
	}
	
	public static function get_logical_categories_id($catalog, $ind)
	{
		$ret = 0;
		if (is_numeric($ind))
		{
				$catid = OJ_Catalogs::get_catalog_id($catalog);
				$ind1 = OJ_Row::load_single_object("OJ_Indexes", ["id"=>$ind, "catalogs_id"=>$catid]);
				$ret = $ind1 == null?0:$ind1->logical_categories_id;
		}
		else
		{
			if (is_string($ind))
			{
				$catid = OJ_Catalogs::get_catalog_id($catalog);
				$ind1 = OJ_Row::load_single_object("OJ_Indexes", ["name"=>$ind, "catalogs_id"=>$catid]);
				$ret = $ind1 == null?0:$ind1->logical_categories_id;
			}
			else
			{
				$ret = $ind->logical_categories_id;
			}
		}
		return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("indexes", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Integer_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("integer_values", $param);
		$str = strval($this->_data['value']);
		if (strlen($str) > 10)
		{
			$this->_data['value'] = substr($str, 0, 10);
		}
    }

	public function get_default_value()
	{
		return '0';
	}

	public function get_comparison_value()
	{
		return ["type"=>0, "value"=>intval($this->get_value())];
	}

}

class OJ_Links extends OJ_Database_Row
{
	public static function get_local_links_from($catalogs_id, $entities_id)
	{
		$where = ["from_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id];
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_links_and_references_from($catalogs_id, $entities_id)
	{
		$where = ["from_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id];
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_links_and_references_to($catalogs_id, $entities_id)
	{
		$where = ["to_entities_id"=>$entities_id, "to_catalogs_id"=>$catalogs_id];
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_links_from($catalogs_id, $entities_id, $entity_types_id = null)
	{
		$where = ["from_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id];
		if ($entity_types_id !== null)
		{
			$where["entity_types_id"] = $entity_types_id;
		}
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_links_to($catalogs_id, $entities_id)
	{
		$where = ["to_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id];
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_references_from($from_catalogs_id, $from_entities_id, $to_catalogs_id = null)
	{
		if ($to_catalogs_id === null)
		{
			$where = ["from_entities_id"=>$from_entities_id, "from_catalogs_id"=>$from_catalogs_id];
		}
		else
		{
			$where = ["from_entities_id"=>$from_entities_id, "from_catalogs_id"=>$from_catalogs_id, "to_catalogs_id"=>$to_catalogs_id];
		}
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function get_all_references_to($to_catalogs_id, $to_entities_id, $from_catalogs_id = null)
	{
		if ($from_catalogs_id === null)
		{
			$where = ["to_entities_id"=>$to_entities_id, "from_catalogs_id<>"=>$to_catalogs_id, "to_catalogs_id"=>$to_catalogs_id];
		}
		else
		{
			$where = ["to_entities_id"=>$to_entities_id, "from_catalogs_id"=>$from_catalogs_id, "to_catalogs_id"=>$to_catalogs_id];
		}
		$ret = OJ_Row::load_array_of_objects("OJ_Links", $where);
		return $ret == null?null:$ret["result"];
	}
	
	public static function add_link($from_catalogs_id, $from_entities_id, $to_entities_id, $name = null, $to_catalogs_id = 0, $ordinal = -1, $hidden = false)
	{
		$ret = null;
		$hash = ["from_catalogs_id"=>$from_catalogs_id, "from_entities_id"=>$from_entities_id];
		$hash["to_catalogs_id"] = $to_catalogs_id === 0?$from_catalogs_id:$to_catalogs_id;
		// get existing links, 
		$existing = OJ_Row::load_array_of_objects("OJ_Links", $hash);
		foreach ($existing["result"] as $lnk)
		{
			if ($lnk->to_entities_id == $to_entities_id)
			{
				$ret = $lnk;
				break;
			}
		}
		if ($ret === null)
		{
			$hash["to_entities_id"] =$to_entities_id;
			$hash['name'] = $name;
			$hash["ordinal"] = $ordinal < 0?count($existing):$ordinal;
			$hash["hidden"] = $hidden?1:0;
			$ret = new OJ_Links($hash);
			$ret->save();
		}
		return $ret;
	}
	
	public static function get_destination_id($from_catalogs_id, $from_entities_id, $linkname, $to_catalogs_id = 0)
	{
		$ret = 0;
		if ($to_catalogs_id === 0)
		{
			$to_catalogs_id = $from_catalogs_id;
		}
		return intval(OJ_Row::get_single_value("OJ_Links", "to_entities_id", ["from_catalogs_id"=>$from_catalogs_id, "to_catalogs_id"=>$to_catalogs_id,
			"from_entities_id"=>$from_entities_id, "name"=>$linkname]));
	}
	
	public static function get_entities_id_from_path($catalogs_id, $path)
	{
		$ret = 0;
		if (is_string($path))
		{
			if ($path[0] === '/')
			{
				$path = "root".$path;
			}
			$patharray = explode('/', $path);
		}
		else
		{
			$patharray = $path;
		}
		$ent0 = OJ_Row::load_single_object("OJ_Entities", ["catalogs_id"=>$catalogs_id, "name"=>$patharray[0]]);
		if ($ent0)
		{
			$ret = $ent0->id;
		}
		else
		{
			$lnk = OJ_Row::load_single_object("OJ_Links", ["from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id, "name"=>$patharray[0]]);
			if ($lnk)
			{
				$ret = $lnk->to_entities_id;
			}
		}
		if ($ret > 0)
		{
			for ($n = 1; $n < count($patharray); $n++)
			{
				$lnk = OJ_Row::load_single_object("OJ_Links", ["from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id, "name"=>$patharray[$n], "from_entities_id"=>$ret]);
				if ($lnk)
				{
					$ret = $lnk->to_entities_id;
				}
				else
				{
					$ret = 0;
					break;
				}
			}
		}
		return $ret;
	}
	
	public static function get_one_pathname_to($catalogs_id, $entities_id, $start_from = 0, $exclude = [], $relative = true)
	{
		$ret = "";
		if ($entities_id == $start_from)
		{
			if (!$relative)
			{
				$ret = OJ_Entities::get_entity_name($entities_id);
			}
		}
		else
		{
			$preva = OJ_Row::load_array_of_objects("OJ_Links", ["to_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id]);
			$prev = $preva["result"];
			if (count($prev) === 0)
			{
				if (!$relative)
				{
					$ret = OJ_Catalogs::get_catalog_name($catalogs_id);
				}
			}
			else
			{
				foreach ($prev as $prev1)
				{
					if (array_search($prev1->from_entities_id, $exclude) === FALSE)
					{
						$prevpath = self::get_one_pathname_to($catalogs_id, $prev1->from_entities_id, $start_from, $exclude);
						$slash = "/";
						if ($relative && (strlen($prevpath) === 0))
						{
							$slash = "";
						}
						$ret = $prevpath.$slash.OJ_Entities::get_entity_name($entities_id);
						break;
					}
				}
			}
		}
		return $ret;
	}
	
	public static function get_paths_to($catalogs_id, $entities_id, $start_from = 0, $exclude = [])
	{
		$ret = ["paths"=>[], "entities"=>[]];
		$ret["entities"][intval($entities_id)] = 1;
		if ($entities_id == $start_from)
		{
			array_push($ret["paths"], [$entities_id]);
		}
		else
		{
//			echo "1 ";
			if ($catalogs_id > 0)
			{
				$prev = OJ_Row::load_column("links", "from_entities_id", ["to_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id]);
//				echo "2 ".count($prev)." ";
				if (count($prev) === 0)
				{
					array_push($ret["paths"], [$entities_id]);
				}
				else
				{
					foreach ($prev as $prev1)
					{
						if (array_search($prev1, $exclude) === FALSE)
						{
//							echo "3";
							$prev1_paths = self::get_paths_to($catalogs_id, $prev1, $start_from, $exclude);
							foreach ($prev1_paths["paths"] as $ppath)
							{
								array_push($ppath, $entities_id);
								array_push($ret["paths"], $ppath);
							}
							foreach ($prev1_paths["entities"] as $id => $v)
							{
								$ret["entities"][$id] = 1;
							}
						}
					}
				}
			}
		}
//		echo "<br/>get_paths_to ".$entities_id." in ".$catalogs_id." from ".$start_from."<br/>";
//		var_dump($start_from);
//		var_dump($exclude);
//		var_dump($ret);
		return $ret;
	}
	
	public static function get_paths_from($catalogs_id, $entities_id)
	{
		$ret = ["paths"=>[], "entities"=>[], "items"=>[]];
		$lnks = self::get_all_links_from($catalogs_id, $entities_id);
		$itemid = OJ_Entity_Types::get_entity_type_id("ITEM");
		foreach ($lnks as $lnk)
		{
			array_push($ret["entities"], $lnk->to_entities_id);
			if ($lnk->entity_types_id == $itemid)
			{
				array_push($ret["paths"], [$lnk->to_entities_id]);
				array_push($ret["items"], $lnk->to_entities_id);
			}
			else
			{
				$pths = self::get_paths_from($catalogs_id, $lnk->to_entities_id);
//				$union = array_unique(array_merge($a, $b));
				$ret["entities"] = array_unique(array_merge($ret["entities"], $pths["entities"]));
				$ret["items"] = array_unique(array_merge($ret["items"], $pths["items"]));
				foreach ($pths["paths"] as $pth)
				{
					array_unshift($pth, $lnk->to_entities_id);
					array_push($ret["paths"], $pth);
				}
			}
		}
		return $ret;
	}
	
	public static function get_all_descendants($catalogs_id, $entities_id, $entity_types_id)
	{
		$all_entity_type_names = OJ_Entity_Types::get_all_entity_type_names();
		return self::get_all_descendants1($all_entity_type_names, $catalogs_id, $entities_id, $entity_types_id);
	}
	
	private static function get_all_descendants1($all_entity_type_names, $catalogs_id, $entities_id, $entity_types_id)
	{
		$etid = OJ_Entities::get_entity_types_id($entities_id);
//		print $etid." from ".$entities_id."\n";
//		$etnm = $all_entity_type_names[$etid];
		$ret = [];
		if (OJ_Entity_Types::can_contain_quick($all_entity_type_names, $etid, $entity_types_id))
		{
			$children = self::get_all_links_from($catalogs_id, $entities_id);
			foreach ($children as $child)
			{
				if ($child->entity_types_id === $entity_types_id)
				{
					array_push($ret, $child->to_entities_id);
				}
				$des = self::get_all_descendants1($all_entity_type_names, $catalogs_id, $child->to_entities_id, $entity_types_id);
				foreach ($des as $d)
				{
					$ret[] = $d;
				}
			}
		}
		return $ret;
	}
	
	public static function is_descended_from($descendant_entities_id, $ancestor_entities_id, $catalogs_id = 0)
	{
		$ret = false;
		if ($catalogs_id == 0)
		{
			$catalogs_id = OJ_Entities::get_catalogs_id($descendant_entities_id);
		}
		$prev = OJ_Row::load_column("links", "from_entities_id", ["to_entities_id"=>$descendant_entities_id, "from_catalogs_id"=>$catalogs_id]);
		if (count($prev) > 0)
		{
			foreach ($prev as $prev1)
			{
				if ($prev1 == $ancestor_entities_id)
				{
					$ret = true;
					break;
				}
			}
			if (!$ret)
			{
				foreach ($prev as $prev1)
				{
					$ret = self::is_descended_from($prev1, $ancestor_entities_id, $catalogs_id);
					if ($ret)
					{
						break;
					}
				}
			}
		}
		return $ret;
	}
	
	public static function find_by_name($name, $starting_from_id, $catalogs_id = 0)
	{
		$ret = 0;
		if ($catalogs_id == 0)
		{
			$catalogs_id = OJ_Entities::get_catalogs_id($starting_from_id);
		}
		$all = OJ_Row::load_array_of_objects("OJ_Links", ["name"=>$name, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id]);
		if ($all && ($all["total_number_of_rows"] > 0))
		{
			foreach ($all["result"] as $lnk)
			{
				if (self::is_descended_from($lnk->to_entities_id, $starting_from_id, $catalogs_id))
				{
					$ret = $lnk->to_entities_id;
				}
			}
		}
		return $ret;
	}
	
	public static function delete_all_links_and_references($entities_id, $catalogs_id = 0)
	{
		if ($catalogs_id == 0)
		{
			$catalogs_id = OJ_Entities::get_catalogs_id($entities_id);
		}
		OJ_Row::delete_rows("links", OJ_Utilities::get_where_clause(["from_entities_id"=>$entities_id, "to_entities_id"=>$entities_id], "OR"));
	}
	
	public static function delete_all_links_from($entities_id, $catalogs_id = 0)
	{
		if ($catalogs_id == 0)
		{
			$catalogs_id = OJ_Entities::get_catalogs_id($entities_id);
		}
		OJ_Row::delete_rows("links", ["from_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id]);
	}

	public static function delete_all_references_from($entities_id, $from_catalogs_id, $to_catalogs_id)
	{
		OJ_Row::delete_rows("links", ["from_entities_id"=>$entities_id, "from_catalogs_id"=>$from_catalogs_id, "to_catalogs_id"=>$to_catalogs_id]);
	}

	public static function delete_all_links_to($entities_id, $catalogs_id = 0)
	{
		if ($catalogs_id == 0)
		{
			$catalogs_id = OJ_Entities::get_catalogs_id($entities_id);
		}
		OJ_Row::delete_rows("links", ["to_entities_id"=>$entities_id, "from_catalogs_id"=>$catalogs_id, "to_catalogs_id"=>$catalogs_id]);
	}

	public static function delete_all_references_to($entities_id, $from_catalogs_id, $to_catalogs_id)
	{
		OJ_Row::delete_rows("links", ["to_entities_id"=>$entities_id, "from_catalogs_id"=>$from_catalogs_id, "to_catalogs_id"=>$to_catalogs_id]);
	}

	/**
	 * 
	 * @param type $xmllnk
	 * <link direction="from|to" catalog="" name="" other=""/>
	 */
	public static function from_xml($catalogs_id, $entities_id, $xmllnk1)
	{
		if (is_string($xmllnk1))
		{
			$xmllnk = new SimpleXMLElement($xmllnk1);
		}
		else
		{
			$xmllnk = $xmllnk1;
		}
		$ret = null;
//		OJ_Logger::get_logger()->ojdebug("from_xml", $entities_id, $xmllnk);
		$lnkname = $xmllnk->getName();
		if ($lnkname === "links")
		{
			$ret = [];
			foreach ($xmllink->link as $xmllnk1)
			{
				$ret[] = self::from_xml($catalogs_id, $entities_id, $xmllnk1);
			}
		}
		else
		{
			$lname = html_entity_decode((string)$xmllnk);
			$direction = (string)$xmllnk["direction"];
			$otherdirection = $direction == "from"?"to":"from";
			if (isset($xmllnk["hidden"]))
			{
				$hdn1 = (string) $xmllnk["hidden"];
				$hdn = is_numeric($hdn1)?$hdn1:($hdn1 === 'true'?1:0);
			}
			else
			{
				$hdn = 0;
			}
			if (isset($xmllnk["ordinal"]))
			{
				$ord = intval((string) $xmllnk["ordinal"]);
			}
			else
			{
				$ord = 0;
			}
			$other_catalogs_id = OJ_Catalogs::get_catalog_id((string)$xmllnk["catalog"]);
			$other = (string)$xmllnk["other"];
	//		OJ_Logger::get_logger()->ojdebug("other", $other);
			$other_entities_id = OJ_Entities::get_entity_id($other);
			$hash = ["name"=>$lname, "hidden"=>$hdn, "ordinal"=>$ord, $direction."_catalogs_id"=>$catalogs_id, $direction."_entities_id"=>$entities_id,
				$otherdirection."_catalogs_id"=>$other_catalogs_id, $otherdirection."_entities_id"=>$other_entities_id];
			$etid = OJ_Row::get_single_value("OJ_Entities", "entity_types_id", ["id"=>$hash["to_entities_id"]]);
			$hash["entity_types_id"] = $etid;
			if (isset($xmllnk->tooltip))
			{
				$hash["tooltip"] = (string) $xmllnk->tooltip;
			}
			$lnk = new OJ_Links($hash);
			$lnk->save(true);
			$ret = $lnk;
		}
		return $ret;
	}
	
	public static function reorder($lnks)
	{
		for ($n = 0; $n < count($lnks); $n++)
		{
			$lnk = $lnks[$n];
			$lnk->ordinal = $n;
			$lnk->save();
		}
	}
	
	public function get_comparison_value($lnk, $page, $attname)
	{
        $ret = null;
		$pgid = OJ_Pages::get_page_id($page);
		$att = OJ_Row::load_single_object("OJ_Attributes", ["pages_id"=>$pgid, "name"=>$attname, "entities_id"=>$lnk["to"]]);
		if ($att != null)
		{
			$ret = $att->get_comparison_value();
		}
        return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("links", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function to_xml_string($direction = "from")
	{
		$other = $direction == "from"?"to":"from";
		$tt = (isset($this->_data['tooltip']) && ($this->_data['tooltip'] != null))?('<tooltip>'.htmlentities(stripslashes($this->_data["tooltip"])).'</tooltip>'):"";
		$ret = '<link direction="'.$direction.'" catalog="'.$this->_data[$other."_catalogs_id"].
				'" ordinal="'.$this->_data["ordinal"].'" hidden="'.$this->_data["hidden"].
				'" other="'.$this->_data[$other."_entities_id"].'">'.htmlentities(stripslashes($this->_data["name"])).$tt.'</link>';
		return $ret;
	}

}

class OJ_Login_Attempts extends OJ_Database_Row
{

    public function __construct($param = null)
    {
        parent::__construct("login_attempts", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Named_Values extends OJ_Database_Row
{

    public function __construct($param = null)
    {
        parent::__construct("named_values", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }

}

class OJ_Pages extends OJ_Database_Row
{
	
	public static function get_page_id($pg)
	{
		return self::get_row_id("OJ_Pages", $pg);
	}
	
	public static function get_page($catalogid, $entitytypeid, $name)
	{
		return OJ_Row::load_single_object("OJ_Pages", ["name" => $name, "catalogs_id" => $catalogid, "Entity_types_id" => $entitytypeid]);
	}

	public static function get_all_pages($entity_types_id, $catalogs_id = 0)
	{
		$ret = (($entity_types_id > 0) && ($catalogs_id > 0))?OJ_Row::load_hash_of_all_objects("OJ_Pages", ["entity_types_id"=>$entity_types_id, "catalogs_id"=>$catalogs_id], "name"):null;
		return $ret;
	}
	
	/**
	 * 
	 * @param type $xmlatt
	 * <page name="" visible="true">
	 */
	public static function from_xml($catalogs_id, $entities_id, $entity_types_id, $xmlpg)
	{
		$pname = (string)$xmlpg["name"];
		if (isset($xmlpg["visible"]))
		{
			$vis1 = (string) $xmlpg["visible"];
			$vis = is_numeric($vis1)?$vis1:($vis1 === 'true'?1:0);
		}
		else
		{
			$vis = 1;
		}
		$hash = ["name"=>$pname, "visible"=>$vis, "entity_types_id"=>$entity_types_id, "catalogs_id"=>$catalogs_id];
		$pg = OJ_Row::load_single_object("OJ_Pages", ["name"=>$pname, "entity_types_id"=>$entity_types_id, "catalogs_id"=>$catalogs_id]);
		if ($pg == null)
		{
			$pg = new OJ_Pages($hash);
		}
		else
		{
			$pg->visible = $vis;
		}
		$pages_id = $pg->save();
//		OJ_Logger::get_logger()->ojdebug("from xml page", $pname, $pages_id);
//		print "6a.saved\n";
		foreach ($xmlpg->attribute as $xmlatt)
		{
			$a = OJ_Attributes::from_xml($entities_id, $pages_id, $xmlatt);
//			print "6b.saved\n"; var_dump($xmlatt); var_dump($a);
		}
		return $pg;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("pages", "id", $param, null, null, null);
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function to_xml_string($entities_id = 0)
	{
		$atts = OJ_Row::load_array_of_objects("OJ_Attributes", ["pages_id"=>$this->_data["id"], "entities_id"=>$entities_id]);
//		var_dump($atts);
		$ret = '<page name="'.$this->_data["name"].'" visible="'.$this->_data["visible"].'">';
		foreach ($atts['result'] as $att)
		{
			$ret .= $att->to_xml_string();
		}
		$ret .= "</page>";
		return $ret;
	}
}

class OJ_Sharing extends OJ_Database_Row
{

    public function __construct($param = null)
    {
        parent::__construct("sharing", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_String_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("string_values", $param);
    }

	public function get_comparison_value()
	{
		return ["type"=>1, "value"=>$this->get_value()];
	}

}

class OJ_Boolean_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("boolean_values", $param);
    }
	
	public function get_default_value()
	{
		return  "false";
	}

	public function get_comparison_value() {
		return ["type"=>0, "value"=>intval($this->get_value())];
	}

}

class OJ_Blob_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("blob_values", $param);
    }

	public function get_comparison_value()
	{
		return ["type"=>2, "value"=>null];
	}

}

class OJ_Options extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("options", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }

	public function get_comparison_value()
	{
		return ["type"=>2, "value"=>null];
	}

}

class OJ_Text_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("text_values", $param);
    }

	public function get_comparison_value() {
		
	}

}

class OJ_Zip_Values extends OJ_Values
{

    public function __construct($param = null)
    {
        parent::__construct("zip_values", $param);
    }
	
	public function get_value()
	{
		$bvid = $this->_data["blob_values_id"];
		if ($bvid > 0)
		{
			
		}
	}

	public function get_comparison_value()
	{
		return ["type"=>2, "value"=>null];
	}

}

class OJ_Logicals extends OJ_Database_Row
{
	public static function import()
	{
		
	}
	
	public static function get_logicals($prefix = null)
	{
		if ($prefix == null)
		{
			$ret = OJ_Row::load_hash_of_all_objects("OJ_Logicals", ["host"=>gethostname()], "name");
		}
		else
		{
			$ret = OJ_Row::load_hash_of_all_objects("OJ_Logicals", ["host"=>gethostname(), "name~%"=>$prefix], "name");
		}
		return $ret;
	}
	
	public static function get_logical($name)
	{
		$log = self::get_logicals();
		$ret = null;
		if (array_key_exists($name, $log))
		{
			$ret = $log[$name];
		}
		return $ret;
	}

	public static function substitute_for_logical($str, $use_alternative = false)
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
				$column_name = $use_alternative?"alternative":"value";
				$where = ["name"=>$logname];
				$part2 = OJ_Row::get_single_value("OJ_Logicals", $column_name, $where);
				if ($part2 !== null)
				{
					$part2 .= "/";
					$ret = str_replace('//', '/', $part1.$part2.$part3);
				}
			}
		}
		return $ret;
	}
	
	public static function substitute_logical_in($str)
	{
		$ret = $str;
		$logs = self::get_logicals();
		foreach ($logs as $lname=>$log)
		{
			if (strpos($str, $log->value) !== false)
			{
				$ret = str_replace($log->value, '${'.$lname.'}', $str);
				break;
			}
			elseif (strpos($str, $log->alternative) !== false)
			{
				$ret = str_replace($log->alternative, '${'.$lname.'}', $str);
				break;
			}
		}
		return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("logicals", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function get_value_ftp()
	{
		$ret = null;
		if (isset($this->_data["value_ftp_id"]) && ($this->_data["value_ftp_id"] > 0))
		{
			$ret = new OJ_Ftp($this->_data["value_ftp_id"]);
		}
		return $ret;
	}
	
	public function get_alternative_ftp()
	{
		$ret = null;
		if (isset($this->_data["alternative_ftp_id"]) && ($this->_data["alternative_ftp_id"] > 0))
		{
			$ret = new OJ_Ftp($this->_data["alternative_ftp_id"]);
		}
		return $ret;
	}
}

class OJ_Parameters extends OJ_Database_Row
{
	
	public static function get_parameter($catalogs_id, $pname)
	{
		return OJ_Row::load_single_object("OJ_Parameters", ["catalogs_id"=>$catalogs_id, "name"=>$pname]);
	}
	
	public static function get_parameter_value($catalogs_id, $pname)
	{
		$prm = self::get_parameter($catalogs_id, $pname);
		return $prm == null?null:$prm->get_value();
	}
	
	public static function get_all_parameters($catalogs_id)
	{
		return OJ_Row::load_hash_of_all_objects("OJ_Parameters", ["catalogs_id"=>$catalogs_id], "name");
	}
	
	public static function from_xml($catalogs_id, $xmlparam)
	{
		$pname = (string) $xmlparam["name"];
		$table_name = (string) $xmlparam["type"];
		$val = html_entity_decode((string) $xmlparam);
		$param = new OJ_Parameters(["name"=>$pname, "table_name"=>$table_name, "values_id"=>0, "catalogs_id"=>$catalogs_id]);
		$param->set_value($val);
		$param->save();
		return $param;
	}

	public static function ignore_the($catalogs_id, $type)
	{
		$ig = self::get_parameter_value($catalogs_id, "ignoreThe");
		return $ig && (strpos($ig, strtoupper($type)) !== FALSE);
	}
	
	public static function get_comparison_type($catalogs_id, $type)
	{
		$ret = "default";
		$ct = self::get_parameter_value($catalogs_id, "linkComparator");
//			var_dump($ct);
		if ($ct != null)
		{
			switch (strtoupper($type))
			{
				default:
				case "ITEM":
					$ind = 0;
					break;
				case "GROUP":
					$ind = 1;
				case "CATEGORY":
					$ind = 2;
					break;
			}
			$cta = explode('|', $ct);
			if ($ind < count($cta))
			{
				$ret = $cta[$ind];
			}
		}
		return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("parameters", "id", $param, null, null, null);
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function get_value($display = false)
	{
		$ret = null;
//		var_dump($this);
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$tabname = $this->_data['table_name'];
			if ($tabname === 'enumeration')
			{
				$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
				$ret = $enumval->value;
				if (!$display)
				{
					$enumid = $enumval->enumerations_id;
					$ret .= '('.OJ_Row::get_single_value("OJ_Enumerations", "name", ["id"=>$enumid]).')';
				}
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$ret = $obj->get_value();
				}
			}
		}
		return $ret;
	}
	
	public function set_value($newval)
	{
		$id = $this->_data['values_id'];
		$tabname = $this->_data['table_name'];
		if ($id > 0)
		{
			if ($tabname === 'enumeration')
			{
				$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
				$lb = strpos($newval, '(');
				$nv = $lb > 0?substr($newval, 0, $lb):$newval;
				$enumval->value = $nv;
				$enumval->save();
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$obj->value = $newval;
					$obj->save();
				}
			}
		}
		else
		{
			if ($tabname === 'enumeration')
			{
				$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
				$lb = strpos($newval, '(');
				if ($lb > 0)
				{
					$rb = strpos($newval, ')', $lb);
					if ($rb > $lb)
					{
						$e = substr($newval, $lb + 1, $rb - $lb - 1);
						$enum = OJ_Row::load_single_object("OJ_Enumerations", ["name"=>$e]);
						if ($enum == null)
						{
							$enum = new OJ_Enumerations(["name"=>$e]);
							$enum->save();
						}
						$nv = substr($newval, 0, $lb);
						$enumval = new OJ_Enumeration_Values(["value"=>$nv, "enumerations_id"=>$enum->id]);
						$this->_data['values_id'] = $enumval->save();
//						$this->save();
					}
				}
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname(["value"=>$newval]);
					$this->_data['values_id'] = $obj->save();
//					echo "saving parameter";
//					var_dump($obj);
//					var_dump($this);
//					$this->save();
				}
			}
		}
	}
	
	public function to_xml_string()
	{
		return '<parameter name="'.$this->_data['name'].'" type="'.$this->_data["table_name"].'">'.htmlentities(stripslashes($this->get_value()))."</parameter>";
	}
}

class OJ_Properties extends OJ_Database_Row
{
	
	public static function get_properties_for_entity($entities_id)
	{
		return OJ_Row::load_hash_of_all_objects("OJ_Properties", ["entities_id"=>$entities_id], "name");
	}
	
	// not name or type
	public static function get_extra_properties_for_entity($entities_id)
	{
		$hash = self::get_properties_for_entity($entities_id);
		$ret = [];
		foreach ($hash as $pname=>$prop)
		{
			if (($pname !== "name") && ($pname !== "type"))
			{
				$ret[$pname] = $prop;
			}
		}
		return $ret;
	}

    public function __construct($param = null)
    {
        parent::__construct("properties", "id", $param, null, null, null);
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function delete()
	{
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$tabname = $this->_data['table_name'];
			$tablename = $tabname."_values";
			$where = "id=".$id;
			OJ_Row::delete_rows($tablename, $where);
		}
		parent::delete();
	}
	
	public function get_value($display = false)
	{
		$ret = null;
		$id = $this->_data['values_id'];
		if ($id > 0)
		{
			$tabname = $this->_data['table_name'];
			if ($tabname === 'enumeration')
			{
				$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
				$ret = $enumval->value;
				if (!$display)
				{
					$enumid = $enumval->enumerations_id;
					$ret .= '('.OJ_Row::get_single_value("OJ_Enumerations", "name", ["id"=>$enumid]).')';
				}
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$val = $obj->get_value();
					if ($display)
					{
						$ret = OJ_Entities::expand_attribute_value($this->_data['entities_id'], $val);
					}
					else
					{
						$ret = $val;
					}
				}
			}
		}
		return $ret;
	}
	
	public function set_value($newval)
	{
		$id = $this->_data['values_id'];
		$tabname = $this->_data['table_name'];
		if ($id > 0)
		{
			if ($tabname === 'enumeration')
			{
				$enumval = OJ_Row::load_single_object("OJ_Enumeration_Values", ["id"=>$id]);
				$lb = strpos($newval, '(');
				$nv = $lb > 0?substr($newval, 0, $lb):$newval;
				$enumval->value = $nv;
				$enumval->save();
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$obj->value = $newval;
					$obj->save();
				}
			}
		}
		else
		{
			if ($tabname === 'enumeration')
			{
				$ent = OJ_Row::load_single_object("OJ_Entities", ["id"=>$this->_data["entities_id"]]);
				$enumerations_id = OJ_Property_Enumeration_Correspondence::get_enumerations_id($ent->catalogs_id, $ent->entity_types_id, $this->_data["name"]);
				$nv = $newval;
				if ($enumerations_id === 0)
				{
					$lb = strpos($newval, '(');
					if ($lb > 0)
					{
						$rb = strpos($newval, ')', $lb);
						$nv = substr($newval, 0, $lb);
						$enumv = substr($newval, $lb + 1, $rb - $lb - 1);
					}
					else
					{
						$enumv = $nv;
					}
					$enum = OJ_Row::load_single_object("OJ_Enumerations", ["name"=>$enumv]);
					if ($enum)
					{
						$enumerations_id = $enum->id;
					}
					else
					{
						$enum = new OJ_Enumerations(["name"=>$enumv]);
						$enumerations_id = $enum->save(true);
					}
				}
				$enumval = new OJ_Enumeration_Values(["value"=>$nv, "enumerations_id"=>0]);
				$this->_data['values_id'] = $enumval->save();
			}
			else
			{
				$tname = $tabname."_values";
				$cname = OJ_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$obj->value = $newval;
					$this->_data['values_id'] = $obj->save();
				}
			}
		}
	}
	
	public static function from_xml($entities_id, $xmlprop)
	{
		$pname = (string) $xmlprop["name"];
		$table_name = (string) $xmlprop["type"];
		$val = html_entity_decode((string) $xmlprop);
		$prop = new OJ_Properties(["name"=>$pname, "table_name"=>$table_name, "entities_id"=>$entities_id, "values_id"=>0]);
		$prop->set_value($val);
		$prop->save();
		return $prop;
	}
	
	public function to_xml_string()
	{
		$ret = '<property type="'.$this->_data["table_name"].'" name="'.$this->_data["name"].'">'.
				htmlentities(stripslashes($this->get_value()))."</property>";
		return $ret;
	}

    public function save($checkfirst = false, $checkid = true)
    {
        $ret = parent::save($checkfirst, $checkid);
		if ($this->_data["name"] === 'subtype')
		{
			$st = new OJ_Subtypes(["id"=>$this->_data["entities_id"], "name"=>$this->get_value(true)]);
			$st->save(true, false);
		}
		return $ret;
	}
	
}

class OJ_Enumerations extends OJ_Database_Row
{
	
	public static function create_enumeration($val1)
	{
		$val = self::check_name($val1);
		if (is_array($val))
		{
			$name = implode('|', $val);
			$vals = $val;
		}
		else
		{
			$name = $val;
			$vals = explode('|', $val);
		}
		$enm = new OJ_Enumerations(["name" => $name]);
		$enmid = $enm->id;
		if (($enmid == null) || ($enmid == 0))
		{
			$enmid = $enm->save();
			if ($enmid > 0)
			{
				foreach ($vals as $v)
				{
					$ev = new OJ_Enumeration_Values(["value" => $v, "enumerations_id" => $enmid]);
					$ev->save();
				}
			}
		}
		return $enmid;
	}
	
	public static function get_enumerations_id_from_name($name)
	{
		$enm = new OJ_Enumerations(["name" => $name]);
		$enmid = $enm->id;
		if (($enmid == null) || ($enmid == 0))
		{
			$enmid = self::create_enumeration($name);
		}
		return $enmid;
	}
	
	private static function check_name($name)
	{
		$ret = $name;
		$lp = strpos($name, '(');
		if ($lp !== FALSE)
		{
			$rp = strpos($name, '(', $lp);
			if ($rp !== FALSE)
			{
				$ret = substr($name, $lp + 1, $rp - $lp - 1);
			}
		}
		return $ret;
	}
	
	private static function check_params($params)
	{
		if (is_array($params))
		{
			$ret = [];
			foreach ($params as $k => $v)
			{
				if ($k === "name")
				{
					$ret[$k] = self::check_name($v);
				}
				else
				{
					$ret[$k] = $v;
				}
			}
		}
		else
		{
			$ret = $params;
		}
		return $ret;
	}

    public function __construct($param = null)
    {
        parent::__construct("enumerations", "id", self::check_params($param), null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function __toString()
	{
		return "enumeration(".$this->name.")";
	}
}

class OJ_Enumeration_Values extends OJ_Values
{
	
	private $enm_name;

    public function __construct($param = null)
    {
        parent::__construct("enumeration_values", $param);
		if (array_key_exists('value', $this->_data))
		{
			$val = $this->_data['value'];
			$lp = strpos($val, '(');
			if ($lp > 0)
			{
				$rp = strpos($val, ')');
				$this->enm_name = substr($val, $lp + 1, $rp - $lp - 1);
				$this->_data['value'] = substr($val, 0 , $lp);
				if (!array_key_exists('enumerations_id', $this->_data) || ($this->_data['enumerations_id'] == 0))
				{
					$this->_data['enumerations_id'] = OJ_Enumerations::get_enumerations_id_from_name($this->enm_name);
				}
			}
			else if (array_key_exists('enumerations_id', $this->_data))
			{
				$enmid = $this->_data['enumerations_id'];
				if ($enmid > 0)
				{
					$enm = new OJ_Enumerations($enmid);
					$this->enm_name = $enm->name;
				}
			}
		}
    }
	
	public function __toString()
	{
		return $this->name."(".$this->enm_name.")";
	}

	public function get_default_value()
	{
		$pipe1 = strpos($this->enm_name, '|');
		return  $pipe1 === FALSE?$this->enm_name:substr($this->enm_name, 0, $pipe1);
	}

	public function get_comparison_value()
	{
		return ["type"=>1, "value"=>$this->get_value()];
	}

}

class OJ_Ftp extends OJ_Database_Row
{
    public function __construct($param = null)
    {
        parent::__construct("ftp", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Imported_Folders extends OJ_Database_Row
{
    public function __construct($param = null)
    {
        parent::__construct("imported_folders", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_New_Old_Ids extends OJ_Database_Row
{
	public static function save_ids($catalogs_id, $entities_id, $old_id)
	{
		$noi = new OJ_New_Old_Ids(["catalogs_id" => $catalogs_id, "entities_id" => $entities_id, "old_id" => $old_id]);
		$noi->save();
	}
	
	public static function get_old_from_new($catalogs_id, $entities_id)
	{
		$noi = OJ_Row::load_single_object("OJ_New_Old_Ids", ["catalogs_id" => $catalogs_id, "entities_id" => $entities_id]);
		return $noi == null?null:$noi->old_id;
	}

	public static function get_new_from_old($catalogs_id, $old_id)
	{
		$noi = OJ_Row::load_single_object("OJ_New_Old_Ids", ["catalogs_id" => $catalogs_id, "old_id" => $old_id]);
		return $noi === null?null:$noi->entities_id;
	}

    public function __construct($param = null)
    {
        parent::__construct("new_old_ids", "id", $param, null, null, null);
    }

}

class OJ_Aka extends OJ_Database_Row
{
	
	public static function add_aka($name, $aka_id, $table_name = "entities")
	{
		$aka = new OJ_Aka(["name"=>$name, "entities_id"=>$aka_id, "table_name"=>$table_name]);
		return $aka->save();
	}

	public function __construct($param = null)
	{
		parent::__construct("aka", "id", $param, null, null, "name");
		$this->_must_be_set_to_save[] = 'name';
	}
}

class OJ_Link_Attributes extends OJ_Database_Row
{
	
	public function __construct($param = null)
	{
		parent::__construct("link_attributes", "id", $param, null, null, "name");
		$this->_must_be_set_to_save[] = 'name';
		$this->_must_be_set_to_save[] = 'links_id';
	}
}

class OJ_Access extends OJ_Database_Row
{
	
	public static function stamp($username, $catalogs_id, $ojhost, $indexes_id = 0)
	{
		$catid = OJ_Catalogs::get_catalog_id($catalogs_id);
		$indid = $indexes_id === 0?0:OJ_Indexes::get_index_id($catalogs_id, $indexes_id);
		$acc = OJ_Row::load_single_object("OJ_Access", ['username'=>$username, 'catalogs_id'=>$catid]);
		if ($acc)
		{
			$sql = "UPDATE access SET last_accessed=now(), indexes_id=$indid WHERE username='$username' AND catalogs_id=$catid AND host=$ojhost";
			Project_Details::get_db()->query($sql);
		}
		else
		{
			$acc = new OJ_Access(['username'=>$username, 'catalogs_id'=>$catid, 'indexes_id'=>$indid]);
			$acc->save();
		}
	}
	
	public static function next_host()
	{
		$sql = "SELECT MAX(host) FROM access";
		$max = Project_Details::get_db()->loadResult($sql);
		return $max + 1;
	}
	
	public static function most_recent_catalog_for($username)
	{
		$sql = "SELECT catalogs_id FROM access ORDER BY last_accessed DESC LIMIT 1";
		return Project_Details::get_db()->loadResult($sql);
	}

	public static function most_recent_access_for($username, $catalog = null)
	{
		if ($catalog == null)
		{
			$sql = "SELECT * FROM access ORDER BY last_accessed DESC LIMIT 1";
		}
		else
		{
			$catid = OJ_Catalogs::get_catalog_id($catalog);
			$sql = "SELECT * FROM access WHERE catalogs_id=$catid ORDER BY last_accessed DESC LIMIT 1";
		}
		$hash = [];
		return Project_Details::get_db()->loadHash($sql, $hash)?$hash:null;
	}
	
	public static function most_recent_catalog_and_index_for($username, $catalog = null)
	{
		$ret = null;
		$acc = self::most_recent_access_for($username, $catalog);
		if ($acc)
		{
			$ret = [];
			$ret['catalog'] = OJ_Catalogs::get_catalog_name($acc['catalogs_id']);
			$acc1 = self::most_recent_access_for($username, $acc['catalogs_id']);
			$ret['index'] = OJ_Indexes::get_index_name($acc1['indexes_id']);
		}
		return $ret;
	}

	public static function most_recent_index_for($username, $catalogname)
	{
		$catid = OJ_Catalogs::get_catalog_id($catalogname);
		$sql = "SELECT indexes_id FROM access WHERE catalogs_id=$catid ORDER BY last_accessed DESC LIMIT 1";
		$ind = Project_Details::get_db()->loadResult($sql);
		if ($ind)
		{
			$ret = OJ_Indexes::get_index_name($ind);
		}
		else
		{
			$ret = "default";
		}
		return $ret;
	}
	
	public static function get_catalog_list($username)
	{
		$ret = $catalog_list = OJ_Catalogs::load_column("catalogs", "name");
		for ($n = 0; $n < count($ret); $n++)
		{
			$ret[$n] .= "__".self::most_recent_index_for($username, $ret[$n]);
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("access", "id", $param, null, null, "username");
		$this->_must_be_set_to_save[] = 'username';
	}
}

// do not add new rows of this table except through login/register system
class OJ_Members extends OJ_Database_Row
{
	
	public static function exists($username)
	{
		return OJ_Row::load_single_object("OJ_Members", ["username"=>$username]) != null;
	}
	
	public function __construct($param = null)
	{
		parent::__construct("members", "id", $param, null, null, "username");
		$this->_must_be_set_to_save[] = 'name';
	}
}

class OJ_Users extends OJ_Database_Row
{

	public static function get_users_name($user)
	{
		if (is_numeric($user))
		{
			$ent = OJ_Row::load_single_object("OJ_Users", ["id" => abs($user)]);
			$ret = $ent == null?null:$ent->name;
		}
		else if (is_string($user))
		{
			$ret = $user;
		}
		else
		{
			$ret = $user->name;
		}
		return $ret;
	}

	public static function get_users_id($user)
	{
		if (is_numeric($user))
		{
			$ret = abs($user);
		}
		else if (is_string($user))
		{
			$ent = OJ_Row::load_single_object("OJ_Users", ["name" => strtolower($user)]);
			$ret = $ent == null?null:$ent->id;
		}
		else
		{
			$ret = $user->id;
		}
		return $ret;
	}
	
	public static function is_admin($user, $catalog)
	{
		$users_id = self::get_users_id($user);
		$roles_id = OJ_Roles::get_roles_id("admin");
		$catalogs_id = OJ_Catalogs::get_catalog_id($catalog);
		$nrows = OJ_Row::count_rows("OJ_User_Roles", ["users_id"=>$users_id, "roles_id"=>$roles_id, "catalogs_id"=>$catalogs_id]);
		return $nrows > 0;
	}

    public function __construct($param = null)
    {
        parent::__construct("users", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Roles extends OJ_Database_Row
{
	
	public static function get_roles_name($role)
	{
		if (is_numeric($role))
		{
			$ent = OJ_Row::load_single_object("OJ_Roles", ["id" => abs($role)]);
			$ret = $ent->name;
		}
		else if (is_string($role))
		{
			$ret = $role;
		}
		else
		{
			$ret = $role->name;
		}
		return $ret;
	}

	public static function get_roles_id($role)
	{
		if (is_numeric($role))
		{
			$ret = abs($role);
		}
		else if (is_string($role))
		{
			$ent = OJ_Row::load_single_object("OJ_Roles", ["name" => strtolower($role)]);
			$ret = $ent->id;
		}
		else
		{
			$ret = $role->id;
		}
		return $ret;
	}
	
	public static function get_role_for_user($user, $catalog)
	{
		$users_id = OJ_Users::get_users_id($user);
		$catalogs_id = OJ_Catalogs::get_catalog_id($catalog);
		$roles_id = OJ_Row::get_single_value("OJ_Users_Roles", "roles_id", ["users_id"=>$users_id, "catalogs_id"=>$catalogs_id]);
		return $ret == null?null:OJ_Row::load_single_object("OJ_Roles", ["id"=>$roles_id]);
	}
	
	public static function get_rolename_for_user($user, $catalog)
	{
		$users_id = OJ_Users::get_users_id($user);
		$catalogs_id = OJ_Catalogs::get_catalog_id($catalog);
		$ret = OJ_Row::get_single_value("OJ_Users_Roles", "roles_id", ["users_id"=>$users_id, "catalogs_id"=>$catalogs_id]);
		return $ret == null?"guest":OJ_Roles::get_roles_name($ret);
	}

    public function __construct($param = null)
    {
        parent::__construct("roles", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
}

class OJ_Users_Roles extends OJ_Database_Row
{

    public function __construct($param = null)
    {
        parent::__construct("users_roles", "id", $param, null, null, null);
    }
}

class OJ_Rss_Items extends OJ_Database_Row
{
	
	private static function do_view_item($rss_id, $item_entities_id, $feed_entities_id)
	{
		$rssitem = OJ_Row::load_single_object("OJ_Rss_Items", ["rssid"=>$rss_id, "feed_entities_id"=>$feed_entities_id]);
//		OJ_Logger::get_logger()->ojdebug("do_view_item", $rssitem);
		if ($rssitem == null)
		{
			$rssitem = new OJ_Rss_Items(["rssid"=>$rss_id, "feed_entities_id"=>$feed_entities_id]);
		}
		else if ($rssitem->item_entities_id != $item_entities_id)
		{
			OJ_Entities::tree_delete($rssitem->item_entities_id);
		}
		$rssitem->status = 1;
		$rssitem->item_entities_id = $item_entities_id;
//		OJ_Logger::get_logger()->ojdebug("do_view_item saving", $rssitem);
		$rssitem->save(true, false);
	}
	
	public static function view_item($rss_id, $item_entities_id, $feed_entities_id)
	{
		// check an item exists
		$rssitem = OJ_Row::load_single_object("OJ_Rss_Items", ["item_entities_id"=>$item_entities_id]);
		if ($rssitem == null)
		{
			// no item, better create one
			$rssitem = new OJ_Rss_Items(["rssid"=>$rss_id, "feed_entities_id"=>$feed_entities_id, "item_entities_id"=>$item_entities_id, "status"=>1]);
			$rssitem->save(true, false);
			OJ_Logger::get_logger()->ojdebug("view_item no rss item so creating for", $item_entities_id);
//			self::do_view_item($rss_id, $item_entities_id, $feed_entities_id);
		}
		else
		{
			// check it has same ids for feed and rss
			if ((intval($rssitem->feed_entities_id) != intval($feed_entities_id)) || (html_entity_decode($rssitem->rssid) != $rss_id))
			{
				// something wrong
				OJ_Logger::get_logger()->ojerror("rss view_item differences", $rssitem, "feed should be", $feed_entities_id, "rss should be",
						$rss_id, $rssitem->feed_entities_id != $feed_entities_id, $rssitem->rssid != $rss_id);
//				OJ_Entities::tree_delete($rssitem->item_entities_id);
//				$rssitem->delete();
//				self::do_view_item($rss_id, $item_entities_id, $feed_entities_id);
			}
			else
			{
				// ok just set status
				$rssitem->status = 1;
//				OJ_Logger::get_logger()->ojdebug("view_item no differences", $rssitem);
				$rssitem->save();
			}
		}
//		OJ_Logger::get_logger()->ojdebug("view_item", $rssitem);
//		$sql = "INSERT INTO rss_items (rssid, item_entities_id, feed_entities_id) VALUES('$rssid', $item_entities_id, $feed_entities_id) ON DUPLICATE KEY UPDATE status=1";
//		$ret = Project_Details::get_db()->query($sql);
//		OJ_Logger::get_logger()->ojdebug("view_item", $sql, $ret);
	}
	
	public static function view_feed($feed_entities_id)
	{
		$sql = "UPDATE rss_items SET status=1 WHERE feed_entities_id=$feed_entities_id";
		Project_Details::get_db()->query($sql);
	}
	
	public static function has_been_viewed($item_entities_id)
	{
		$status = OJ_Row::get_single_value("OJ_Rss_Items", "status", ["item_entities_id"=>$item_entities_id]);
		return $status == 1;
	}
	
	public static function get_status($item_entities_id)
	{
		$status = OJ_Row::get_single_value("OJ_Rss_Items", "status", ["item_entities_id"=>$item_entities_id]);
		return $status;
	}
	
	public static function get_number_unread($feed_entities_id)
	{
		$ret = 0;
		$etype = OJ_Entities::get_entity_types_name($feed_entities_id);
		if ($etype === "GROUP")
		{
			$where = "feed_entities_id=$feed_entities_id";
			$sql = "SELECT COUNT(*) FROM `rss_items` WHERE $where AND status=0";
//			OJ_Logger::get_logger()->ojdebug("get_number_unread", $sql);
			$ret1 = Project_Details::get_db()->query($sql);
			$ret = array_values($ret1[0])[0];
		}
		else if ($etype === "CATEGORY")
		{
			$catalogs_id = OJ_Catalogs::get_catalog_id("RSS");
			$grp_id = OJ_Entity_Types::get_entity_type_id("GROUP");
			$feed_ids = OJ_Links::get_all_descendants($catalogs_id, $feed_entities_id, $grp_id);
//			OJ_Logger::get_logger()->ojdebug("get_number_unread", $catalogs_id, $feed_entities_id, $grp_id, $feed_ids);
			$w = [];
			foreach ($feed_ids as $fid)
			{
				$w[] = "feed_entities_id=$fid";
			}
			$where = implode(" OR ", $w);
			if (count($feed_ids) > 1)
			{
				$where = "(".$where.")";
			}
			$sql = "SELECT COUNT(*) FROM `rss_items` WHERE $where AND status=0";
//			OJ_Logger::get_logger()->ojdebug("get_number_unread", $sql);
			$ret1 = Project_Details::get_db()->query($sql);
			$ret = array_values($ret1[0])[0];
		}
		return $ret;
	}

    public function __construct($param = null)
    {
        parent::__construct("rss_items", "item_entities_id", $param, null, null, null, false);
    }

    public function save($checkfirst = false, $checkid = true)
    {
        $ret = 0;
		if ($this->field_is_set(["rssid", "feed_entities_id", "item_entities_id"]))
		{
			$save = ["rssid"=>$this->_data['rssid'], "feed_entities_id"=>$this->_data['feed_entities_id'], "item_entities_id"=>$this->_data['item_entities_id'],
				"status"=>isset($this->_data['status'])?$this->_data['status']:0];
			$sql = "SELECT COUNT(*) FROM rss_items WHERE item_entities_id = ".$this->_data['item_entities_id'];
			$num1 = Project_Details::get_db()->query($sql);
			$num = array_values($num1[0])[0];
			$ret = $this->_data['item_entities_id'];
			if ($num > 0)
			{
				Project_Details::get_db()->updateArray( "rss_items", $save, "item_entities_id" );
				OJ_Logger::get_logger()->ojdebug1("rss_item exists with item id ".$ret);
			}
			else
			{
				Project_Details::get_db()->insertArray( $this->_tablename, $save);
//				$this->_data["item_entities_id"] = $ret;
				OJ_Logger::get_logger()->ojdebug1("rss_item created with item id ".$ret);
			}
		}
		else
		{
			OJ_Logger::get_logger()->ojdebug("rss_item fields not set", $this->_data);
		}
        return $ret;
    }

	
}

class OJ_Logical_Categories extends OJ_Database_Row
{
	
	public static function get_logical_categories_name($logical_category)
	{
		if (is_numeric($logical_category))
		{
			$ent = OJ_Row::load_single_object("OJ_Logical_Categories", ["id" => abs($logical_category)]);
			$ret = $ent->name;
		}
		else if (is_string($logical_category))
		{
			$ret = $logical_category;
		}
		else
		{
			$ret = $logical_category->name;
		}
		return $ret;
	}

	public static function get_logical_categories_prefix($logical_category)
	{
		if (is_numeric($logical_category))
		{
			$ent = OJ_Row::load_single_object("OJ_Logical_Categories", ["id" => abs($logical_category)]);
			$ret = $ent->get_prefix();
		}
		else if (is_string($logical_category))
		{
			$ent = OJ_Row::load_single_object("OJ_Logical_Categories", ["name" => $logical_category]);
			$ret = $ent->get_prefix();
		}
		else
		{
			$ret = $logical_category->get_prefix();
		}
		return $ret;
	}

	public static function get_logical_categories_id($logical_category)
	{
		if (is_numeric($logical_category))
		{
			$ret = abs($logical_category);
		}
		else if (is_string($logical_category))
		{
			$ent = OJ_Row::load_single_object("OJ_Logical_Categories", ["name" => strtolower($logical_category)]);
			$ret = $ent->id;
		}
		else
		{
			$ret = $logical_category->id;
		}
		return $ret;
	}

    public function __construct($param = null)
    {
        parent::__construct("logical_categories", "id", $param, null, null, "name");
        $this->_must_be_set_to_save[] = 'name';
    }
	
	public function get_prefix()
	{
		return strtolower(str_replace(' ', '', $this->_data['name']));
	}
}

class OJ_Subtypes extends OJ_Database_Row
{

    public function __construct($param = null)
    {
        parent::__construct("subtypes", "id", $param, null, null, "name", false);
        $this->_must_be_set_to_save[] = 'name';
    }

}

class OJ_Catalog_Index_Correspondence extends OJ_Database_Row
{
	public static function get_catalog_and_index_names($cat, $ind)
	{
		$ret = [];
		$old_catalogs_id = OJ_Catalogs::get_catalog_id($cat);
		$old_index_name = $ind?OJ_Indexes::get_index_name($ind):"default";
		$cic = OJ_Row::load_single_object("OJ_Catalog_Index_Correspondence", ["old_catalogs_id"=>$old_catalogs_id, "old_indexes_name"=>$old_index_name]);
		if ($cic)
		{
			$ret["catalog"] = strtolower(OJ_Catalogs::get_catalog_name($cic->new_catalogs_id));
			$ret["index"] = strtolower(OJ_Indexes::get_index_name($cic->new_indexes_id));
		}
		else
		{
			$ret["catalog"] = strtolower(OJ_Catalogs::get_catalog_name($cat));
			$ret["index"] = $old_index_name;
		}
		return $ret;
	}
	
    public function __construct($param = null)
    {
        parent::__construct("catalog_index_correspondence", "id", $param, null, null, null);
        $this->_must_be_set_to_save = ['old_catalogs_id', "old_index_name", "new_catalogs_id", 'new_indexes_id'];
    }
	
}

class OJ_Attribute_Enumeration_Correspondence extends OJ_Database_Row
{
	public static function get_enumerations_id($page, $attributes_name)
	{
		$pages_id = OJ_Pages::get_page_id($page);
		$ret = OJ_Row::get_single_value("OJ_Attribute_Enumeration_Correspondence", "enumerations_id", ["attributes_name"=>$attributes_name, "pages_id"=>$pages_id]);
		return $ret?intval($ret):0;
	}
	
	public static function get_attribute_details($enumerations_id)
	{
		$atts = OJ_Row::load_array_of_objects("OJ_Attribute_Enumeration_Correspondence", ["enumerations_id"=>$enumerations_id]);
		$ret = [];
		if ($atts)
		{
			foreach ($atts as $att)
			{
				$ret[] = ["pages_id"=>$att->pages_id, "name"=>$att->$attributes_name];
			}
		}
		return $ret;
	}
	
	public function __construct($param = null)
    {
        parent::__construct("attribute_enumeration_correspondence", "id", $param, null, null, null);
        $this->_must_be_set_to_save = ["pages_id", 'attributes_name', 'enumerations_id'];
    }
}

class OJ_Property_Enumeration_Correspondence extends OJ_Database_Row
{
	public static function get_enumerations_id($catalogs_id, $entity_types_id, $properties_name)
	{
		$ret = OJ_Row::get_single_value("OJ_Property_Enumeration_Correspondence", "enumerations_id", ["properties_name"=>$properties_name,
			"catalogs_id"=>$catalogs_id, "entity_types_id"=>$entity_types_id]);
		return $ret?intval($ret):0;
	}
	
	public function __construct($param = null)
    {
        parent::__construct("property_enumeration_correspondence", "id", $param, null, null, null);
        $this->_must_be_set_to_save = ["catalogs_id", "entity_types_id", 'properties_name', 'enumerations_id'];
    }
}

class OJ_Session extends OJ_Database_Row
{
	private static function to_value_string($value)
	{
		$ret = [];
		if (is_string($value))
		{
			$ret["json"] = 0;
			$ret["value"] = $value;
		}
		elseif (is_array($value) || is_object($value))
		{
			$ret["json"] = 1;
			$ret["value"] = json_encode($value);
		}
		else
		{
			$ret["json"] = 0;
			$ret["value"] = (string) $value;
		}
		return $ret;
	}
	
	public static function set($user, $name, $value)
	{
		$users_id = OJ_Users::get_users_id($user);
		$where = ["users_id"=>$users_id, "name"=>$name];
		$session_variable = OJ_Row::load_single_object("OJ_Session", $where);
		$val = self::to_value_string($value);
		if ($session_variable)
		{
			$session_variable->json = $val["json"];
			$session_variable->value = $val["value"];
		}
		else
		{
			$where["json"] = $val["json"];
			$where["value"] = $val["value"];
			$session_variable = new OJ_Session($where);
		}
		$session_variable->save();
	}
	
	public static function get($user, $name)
	{
		$ret = null;
		$users_id = OJ_Users::get_users_id($user);
		$where = ["users_id"=>$users_id, "name"=>$name];
		$session_variable = OJ_Row::load_single_object("OJ_Session", $where);
		if ($session_variable)
		{
			if ($session_variable->json)
			{
				$ret = json_decode($session_variable->value);
			}
			else
			{
				$ret = $session_variable->value;
			}
		}
		return $ret;
	}
	
	public static function reset($user, $name)
	{
		$users_id = OJ_Users::get_users_id($user);
		$where = ["users_id"=>$users_id, "name"=>$name];
		$session_variable = OJ_Row::load_single_object("OJ_Session", $where);
		if ($session_variable)
		{
			$session_variable->delete();
		}
	}
	
	public function __construct($param = null)
    {
        parent::__construct("session", "id", $param, null, null, "name");
        $this->_must_be_set_to_save = ["users_id", 'name', 'value'];
    }
}
