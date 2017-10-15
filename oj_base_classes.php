<?php
require_once('database/db_object.php');
require_once('project.php');
require_once('oj_utility_classes.php');

interface OJ_Table_Information
{
    public function get_tablename_from_classname($classname);
    public function get_classname_from_tablename($tablename);
    public function get_where_array($tablename, $key);
    public function get_select_array($tablename, $key);
    public function get_number_array($tablename, $key);
    public function get_user_object($tablename, $key);
    public function get_user_post($tablename, $post);
    public function get_description($tablename, $key);
    public function get_table_equivalent($tablename, $columnname);
}

class OJ_HTML_Utilities
{
    
    public static function get_select_list_from_array($array, $name, $selected_value = null, $id = null, $cssclass = null)
    {
        $opts = array();
        foreach ($array as $k => $v)
        {
            $opt = new OJ_OPTION($k, $v);
            if (($selected_value != null) && ($selected_value == $v))
            {
                $opt->add_attribute('selected', 'selected');
            }
            $opts[] = $opt;
        }
        return new OJ_SELECT($opts, $name, $id, $cssclass);
    }
    
    public static function get_select_list_from_hash_array($hash_array, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
    {
        $opts = array();
        foreach ($hash_array as $hash)
        {
            $opt = new OJ_OPTION($hash[$text_key], $hash[$value_key]);
            if (($selected_value != null) && ($selected_value == $hash[$value_key]))
            {
                $opt->add_attribute('selected', 'selected');
            }
            if ($tooltip_key != null)
            {
                $opt->add_tooltip($hash[$tooltip_key]);
            }
            $opts[] = $opt;
        }
        return new OJ_SELECT($opts, $name, $id, $cssclass);
    }
    
    public static function get_select_list_from_object_array($object_array, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
    {
//        echo "keys ".$text_key," ".$value_key;
        $opts = array();
        foreach ($object_array as $obj)
        {
            $hash = $obj->get_as_hash();
//            OJ_Utilities::var_dump_pre($obj);
            $opt = new OJ_OPTION($hash[$text_key], $hash[$value_key]);
            if ($tooltip_key != null)
            {
                $opt->add_tooltip($hash[$tooltip_key]);
            }
            $opts[] = $opt;
        }
        return new OJ_SELECT($opts, $name, $id, $cssclass);
    }
    
    public static function get_select_list_of_all($classname, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
    {
        $object_array = OJ_Row::load_array_of_objects($classname);
        return self::get_select_list_from_object_array($object_array['result'], $name, $text_key, $value_key, $selected_value, $tooltip_key, $id, $cssclass);
    }
    
    public static function get_select_list_of_some($classname, $where, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
    {
        $object_array = OJ_Row::load_array_of_objects($classname, $where);
//        OJ_Utilities::var_dump_pre($object_array);
        return self::get_select_list_from_object_array($object_array['result'], $name, $text_key, $value_key, $selected_value, $tooltip_key, $id, $cssclass);
    }
    
    public static function get_define_row_form($tablename, $classname, $action = null, $instance = null, $omit_columns = null)
    {
        $fid = OJ_Form_Input_Detail::get_form_details_for_table($tablename, $classname, $omit_columns);
//        OJ_Utilities::var_dump_pre($fid);echo $fid->tablename."<br/>";
        $ul = strpos($tablename, '_');
		if ($action == null)
		{
			if ($ul === FALSE)
			{
				$act = $tablename."_define_form_action";
			}
			else
			{
				$act = substr($tablename, 0, $ul)."_define_form_action";
			}
		}
		else
		{
			$act = $action;
		}
		$tname = str_replace('_', '-', $tablename);
        $id = "oj-".$tname."-define-form";
        $cssclass = "oj-".$tname."-form oj-form";
//        OJ_Utilities::var_dump_pre($fid);exit;
        return OJ_Form_Input_Detail::get_form($fid, $act, "POST", $id, $cssclass, $instance);
    }

}

abstract class OJ_Object implements Iterator
{
    public $_data;
    
    public function __construct()
    {
        $this->_data = array();
    }

    // magic methods!
    public function __set($property, $value)
    {
      return $this->_data[$property] = $value;
    }

    public function __get($property)
    {
//        echo "here get ".$property."<br/>";
        return array_key_exists($property, $this->_data)
        ? $this->_data[$property]
        : null
      ;
    }
    
    public function get_as_hash($exclude = null)
    {
        $ret = array();
        foreach ($this->_data as $k=>$v)
        {
            if (($exclude == null) || ($k != $exclude))
            {
                $ret[$k] = $v;
            }
        }
        return $ret;
    }
    
    protected function adjust_hash($hash)
    {
        return $hash;
    }
    
    public function set_all($hash)
    {
        $h = $this->adjust_hash($hash);
        foreach ($h as $k => $v)
        {
            $this->_data[$k] = stripslashes($v);
        }
    }
            
    public function rewind()
    {
//        echo "rewinding\n";
        reset($this->_data);
    }
  
    public function current()
    {
        $var = current($this->_data);
//        echo "current: $var\n";
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->_data);
//        echo "key: $var\n";
        return $var;
    }
  
    public function next() 
    {
        $var = next($this->_data);
//        echo "next: $var\n";
        return $var;
    }
  
    public function valid()
    {
        $key = key($this->_data);
        $var = ($key !== NULL && $key !== FALSE);
//        echo "valid: $var\n";
        return $var;
    }
	
	public function field_is_set($fieldname)
	{
		$ret = false;
		if (is_array($fieldname))
		{
			$len = count($fieldname);
			if ($len > 0)
			{
				$ret = $this->field_is_set($fieldname[0]);
				for ($n = 1; $n < $len && $ret; $n++)
				{
					$ret = $ret && $this->field_is_set($fieldname[$n]);
				}
			}
		}
		else
		{
			$ret = isset($this->_data[$fieldname]) && $this->_data[$fieldname];
		}
		return $ret;
	}
}

class OJ_String_Set
{
    
    public static function union($a, $b)
    {
        $ret = $a->copy();
        $ret->add($b);
        return $ret;
    }
    
    public static function intersection($a, $b)
    {
        $ret = new OJ_String_Set();
        foreach ($a->get_as_array() as $v)
        {
            if ($b->contains($v))
            {
                $ret->add($v);
            }
        }
        return $ret;
    }
    
    public static function difference($a, $b)
    {
        $ret = new OJ_String_Set();
        foreach ($a->get_as_array() as $v)
        {
            if (!$b->contains($v))
            {
                $ret->add($v);
            }
        }
        return $ret;
    }
    
    protected $_data;
    
    public function __construct($param = null)
    {
        $this->_data = array();
        if ($param !== null)
        {
            if (is_string($param))
            {
                $this->_data[$value] = null;
            }
            elseif (is_array($param))
            {
                foreach ($param as $v)
                {
                    $this->_data[$v] = null;
                }
            }
        }
    }

    public function add($value)
    {
        if (is_string($value))
        {
            $this->_data[$value] = null;
        }
        elseif (is_array($value))
        {
            foreach ($value as $v)
            {
                $this->_data[$v] = null;
            }
        }
        elseif ($value instanceof OJ_String_Set)
        {
            foreach ($value->get_as_array() as $v)
            {
                $this->_data[$v] = null;
            }
        }
    }

    public function contains($value)
    {
        return array_key_exists($value, $this->_data);
    }
    
    public function remove($value)
    {
        if (array_key_exists($value, $this->_data))
        {
            unset($this->_data[$value]);
        }
    }
    
    public function get_as_array()
    {
        $ret = array();
        foreach ($this->_data as $k => $v)
        {
            $ret[] = $k;
        }
        return $ret;
    }
    
    public function copy()
    {
        return new OJ_String_Set($this->get_as_array());
    }
    
    public function size()
    {
        return count($this->_data);
    }

}

abstract class OJ_Row extends OJ_Object
{
    private static $_column_names = null;
    private static $_column_name_set = null;
	
	public static $_testing = false;
	private static $_test_counter = 1;
	
	public static function get_single_value($objclass, $column_name, $where)
	{
		$ret = null;
		$tname = null;
		if (class_exists($objclass))
		{
			$obj = new $objclass();
			if ($obj instanceof OJ_Row)
			{
				$tname = $obj->_tablename;
			}
		}
		else
		{
			$tname = $objclass;
		}
		if ($tname != null)
		{
			$whereclause = null;
			if ($where != null)
			{
				if (is_array($where))
				{
					if (count($where) > 0)
					{
						$whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
					}
				}
				else
				{
					$whereclause = " WHERE ".$where;
				}
			}
            $sql = "SELECT $column_name FROM ".$tname.($whereclause = null?"":$whereclause)." LIMIT 1";
			$ret = Project_Details::get_db()->loadResult($sql);
		}
		return $ret;
	}

    public static function load_single_object($objclass, $where)
    {
        $obj = new $objclass();
        $whereclause = null;
        if ($where != null)
        {
            if (is_array($where))
            {
                if (count($where) > 0)
                {
                    $whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
                }
            }
            else
            {
                $whereclause = " WHERE ".$where;
            }
        }
        if ($obj instanceof OJ_Row)
        {
            $sql = "SELECT * FROM ".$obj->_tablename.($whereclause = null?"":$whereclause)." LIMIT 1";
//            echo $sql;exit;
            $hash = array();
//			OJ_Logger::get_logger()->ojdebug1("load single ".$sql);
            $loaded = Project_Details::get_db()->loadHash($sql, $hash);
            if ($loaded)
            {
                $obj->set_all($hash);
            }
            else
            {
                $obj = null;
            }
        }
        else
        {
            $obj = null;
        }
        return $obj;
    }
    
    public static function load_column($tablename, $colname, $where = null)
    {
        $whereclause = null;
        if ($where != null)
        {
            if (is_array($where))
            {
                if (count($where) > 0)
                {
                    $whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
                }
            }
            else
            {
                $whereclause = " WHERE ".$where;
            }
        }
        $sql = "SELECT ".$colname." FROM ".$tablename.($whereclause = null?"":$whereclause);
//		OJ_Logger::get_logger()->ojdebug1("load_column sql ".$sql);
//		echo $sql."<br/>";
        $ret = Project_Details::get_db()->loadColumn($sql);
		return $ret;
    }
	
	public function has_rows($objclass, $where = null)
	{
		return self::count_rows($objclass, $where) > 0;
	}
	
	public static function count_rows($objclass, $where = null)
	{
		$obj = new $objclass();
        $whereclause = null;
        if ($where != null)
        {
            if (is_array($where))
            {
                if (count($where) > 0)
                {
                    $whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
                }
            }
            else
            {
                $whereclause = " WHERE ".$where;
            }
        }
		$ret = 0;
        if ($obj instanceof OJ_Row)
        {
            $sql = "SELECT COUNT(*) FROM ".$obj->_tablename.($whereclause == null?"":$whereclause);
			$ret1 = Project_Details::get_db()->query($sql);
			$ret = array_values($ret1[0])[0];
		}
		return $ret;
	}
    
    public static function load_array_of_objects($objclass, $where = null, $orderby = null, $from = 0, $to = -1, $groupby = null)
    {
        $obj = new $objclass();
        $ret = array();
        $whereclause = null;
//		var_dump($where);
        if ($where != null)
        {
            if (is_array($where))
            {
                if (count($where) > 0)
                {
                    $whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
                }
            }
            else
            {
                $whereclause = " WHERE ".$where;
            }
        }
		$num = 0;
        if ($obj instanceof OJ_Row)
        {
            $sql = "SELECT * FROM ".$obj->_tablename.($whereclause == null?"":$whereclause).($groupby == null?"":(" GROUP BY ".$groupby)).($orderby == null?"":(" ORDER BY ".$orderby));
//            echo $sql."<br/>";
            $list = Project_Details::get_db()->loadList($sql);
			$num = count($list);
			if ($to < 0)
			{
				$to = count($list);
			}
            for ($n = $from; $n < $to; $n++)
            {
                $obj = new $objclass();
                $obj->set_all($list[$n]);
                $ret[] = $obj;
            }
        }
        return array("total_number_of_rows"=> $num, "result" => $ret);
    }
	
	public static function load_hash_of_all_objects($objclass, $where = null, $key = "id", $orderby = null, $saveall = false)
	{
        $obj = new $objclass();
        $ret = array();
		$num = 0;
		$ret = array();
		if (($where != null) && !is_string($where))
		{
			$whereclause = " WHERE".OJ_Utilities::get_where_clause($where);
		}
		else
		{
			$whereclause = $where;
		}
        if ($obj instanceof OJ_Row)
        {
            $sql = "SELECT * FROM ".$obj->_tablename.($whereclause = null?"":$whereclause).($orderby == null?"":(" ORDER BY ".$orderby));
//			OJ_Logger::get_logger()->ojdebug("sql", $sql);
			$list = Project_Details::get_db()->loadList($sql);
			foreach ($list as $hash)
			{
                $obj = new $objclass();
                $obj->set_all($hash);
                $k = $obj->$key;
                if ($k != null)
                {
                    if ($key == 'id')
                    {
                        $ret[$k] = $obj;
                        if ($obj->updateable())
                        {
                            $pk = $obj->get_parent_id();
                            if (($pk > 0) && array_key_exists($pk, $ret))
                            {
                                unset($ret[$pk]);
                            }
                        }
                    }
					elseif ($saveall)
					{
						if (array_key_exists($k, $ret))
						{
							array_push($ret[$k], $obj);
						}
						else
						{
							$ret[$k] = array($obj);
						}
					}
					elseif (!array_key_exists($k, $ret))
					{
						$ret[$k] = $obj;
					}
                }
			}
		}
		return $ret;
	}
    
    
    /**
     * returns a hash of objects keyed by the column named in $key. If this is "id", the values in the
     * hash will be single objects. If it is anything else it will be an array of objects all of which have 
     * the same value for that key
     */
    public static function load_paged_hash_of_unique_objects($objclass, $where = null, $key = "id", $orderby = null, $pageno = 0, $pagesize = 30)
    {
		$obj = new $objclass();
		$tablename = $obj->_tablename;
        $whereclause = null;
        if ($where != null)
        {
            if (is_array($where))
            {
                if (count($where) > 0)
                {
                    $whereclause = " WHERE ".OJ_Utilities::get_where_clause($where);
                }
            }
            else
            {
                $whereclause = " WHERE ".$where;
            }
        }
		$sql = "SELECT * FROM ".$tablename.($whereclause = null?"":$whereclause).($orderby == null?"":(" ORDER BY ".$orderby));
		if (isset($_SESSION[$sql]))
		{
			$list = $_SESSION[$sql];
		}
		else {
			$list = self::load_hash_of_all_objects($objclass, $whereclause, $key, $orderby, false);
			$_SESSION[$sql] = $list;
//			echo "loaded<br/>";
		}
		if (isset($_SESSION[$sql."-keys"]))
		{
			$keys = $_SESSION[$sql."-keys"];
		}
		else {
			$keys = array_keys($list);
			$_SESSION[$sql."-keys"] = $keys;
		}
		$maxnum = count($keys);
		$maxpageno = floor(($maxnum - 1) / $pagesize) + 1;
		$from = $pageno * $pagesize;
		$to = $from + $pagesize;
		if ($to > $maxnum)
		{
			$to = $maxnum;
		}
		
        $ret1 = array();
//		echo "here ".$maxnum." ".$maxpageno." ".$from." ".$to."<br/>";//exit;
		for ($n = $from; $n < $to; $n++)
		{
			$k = $keys[$n];
//			echo $k;
//			var_dump($list[$k]); exit;
			if ($k != null)
			{
				$ret1[$k] = $list[$k];
			}
		}
        $ret = array("total_number_of_rows"=> $maxnum, "result" => $ret1);
//		echo "here "."<br/>";exit;
		return $ret;
    }
	
	public static function delete_rows($tablename, $where)
	{
		return Project_Details::get_db()->delete($tablename, OJ_Utilities::get_where_clause($where));
	}
    
    public static function save_from_form($post, $tablename, $classname, $editof)
    {
//        echo $tablename."<br/>".$classname."<br/>".$editof."<br/>";
//        OJ_Utilities::var_dump_pre($post);//exit;
        $hash = array();
        $len = strlen($tablename) + 4;
        foreach ($post as $k => $v)
        {
            if (OJ_Utilities::starts_with($k, "oj-"))
            {
                $key = str_replace('-', '_', substr($k, $len));
            }
            else
            {
                $key = $k;
            }
            $hash[$key] = $v;
        }
//        echo $tablename. " ".$classname."<br/>";
//        OJ_Utilities::var_dump_pre($hash);exit;
        $obj = new $classname($editof);
//        OJ_Utilities::var_dump_pre($obj);
//        OJ_Utilities::var_dump_pre($hash);
        $obj->set_all($hash);
//        OJ_Utilities::var_dump_pre($obj);exit;
        return $obj->save();
    }
    
    protected $_keyname;
    protected $_tablename;
    protected $_must_be_set_to_save;
    protected $_valid = true;
    protected $_form_input_details;
    protected $_nameval;
	protected $_autoincrement;
    
    public function __construct($tablename, $keyname, $param = null, $checkval = 'name', $where = null, $nameval = null, $autoincrement = true)
    {
        parent::__construct();
        $this->_keyname = $keyname;
        $this->_tablename = $tablename;
        $this->_must_be_set_to_save = array();
        $this->_form_input_details = array();
        $this->_nameval = $nameval;
        $this->_autoincrement = $autoincrement;
        //        $this->_must_be_set_to_save[] = 'name';
        $hash = array();
        if (!isset($param) || ($param == null))
        {
            $hash = array();
        }
        elseif (is_array($param))
        {
            if (isset($param[$keyname]) || (($checkval != null) && isset($param[$checkval])))
            {
                $hash = null;
                if (isset($param[$keyname]))
                {
                    $hash1 = array();
					$pk = $param[$keyname];
					if (!is_numeric($pk))
					{
						$pk = "'".$pk."'";
					}
                    $loaded = Project_Details::get_db()->loadHash("SELECT * FROM ".$tablename." WHERE ".$keyname."=".$pk." LIMIT 1", $hash1);
                    if ($loaded)
                    {
                        $hash = array_merge($hash1, $param);
                    }
//					else if ($param[$keyname])
//					{
//						OJ_Logger::get_logger()->ojdebug("invalid key value", $param[$keyname]);
//					}
					else
					{
						$hash = $param;
					}
                }
                elseif (isset($param[$checkval]))
                {
					$sql = "SELECT * FROM $tablename WHERE".OJ_Utilities::get_where_clause($param)." LIMIT 1";
                    $hash1 = array();
                    $loaded = Project_Details::get_db()->loadHash($sql, $hash1);
                    if ($loaded)
                    {
                        $hash = array_merge($hash1, $param);
                    }
                    else
                    {
//                        echo $sql;
                        $hash = $param;
                    }
                }
            }
            else
            {
                $hash = $param;
            }
        }
        else
        {
            $wherebit = null;
            if (is_numeric($param))
            {
                $wherebit = $keyname."=".$param;
            }
            else if ($checkval != null)
            {
                $wherebit = "UPPER(".$checkval.") = UPPER('".$param."')";
            }
			else
			{
				$wherebit = $keyname."='".$param."'";
			}
            if ($where != null)
            {
                $wh = OJ_Utilities::get_where_clause($where);
                if ($wherebit == null)
                {
                    $wherebit = $wh;
                }
                else
                {
                    $wherebit .= " AND ".$wh;
                }
            }
            if ($wherebit != null)
            {
                $hash = array();
                $sel = "SELECT * FROM ".$tablename." WHERE ".$wherebit." LIMIT 1";
//                echo $sel;
                $loaded = Project_Details::get_db()->loadHash($sel, $hash);
            }
        }
        $this->_valid = $hash != null;
        if ($this->_valid)
        {
            $this->_data = array();
            foreach ($hash as $key=>$value)
            {
                $this->_data[$key] = $value;
            }
        }
    }
	
    public function is_valid()
    {
        return $this->_valid;
    }
    
    protected function saveable()
    {
        $ret = true;
        foreach($this->_must_be_set_to_save as $musthave)
        {
            $ret = $ret && isset($this->_data[$musthave]);
        }
        return $ret;
    }
    
    public function save($checkfirst = false, $checkid = true)
    {
        $ret = 0;
		if (!$checkid && !$this->_autoincrement)
		{
			$checkfirst = true;
		}
        if ($this->saveable())
        {
            $save = array();
            $id = '0';
			$saveid = '0';
            foreach ($this->_data as $key=>$value)
            {
                if ($value !== null)
                {
                    if ($this->_keyname == $key)
                    {
                        if ($checkid && (!$this->_autoincrement || (is_numeric($value) && ($value > 0))))
                        {
							$saveid = $value;
                            $id = $value;
						}
						$save[$key] = $value;
                    }
                    elseif (is_string($value))
                    {
                        $val = trim($value);
                        if (strlen($val) > 0)
                        {
                            $save[$key] = addslashes($val);
                        }
                    }
                    else
                    {
                        $save[$key] = $value;
                    }
                }
            }
            
            $update = false;
            if ($id)
            {
                $update = true;
                $ret = $id;
            }
            if ($update)
            {
				if (self::$_testing)
				{
					print "update ".$this->_tablename."\n";
					var_dump($save);
				}
				else
				{
					Project_Details::get_db()->updateArray( $this->_tablename, $save, $this->_keyname );
				}
            }
            else
            {
				if ($checkfirst)
				{
					$save1 = true;
					if (!$checkid && $saveid)
					{
						$sql = "SELECT COUNT(*) FROM $this->_tablename WHERE $this->_keyname = $saveid";
						$num1 = Project_Details::get_db()->query($sql);
						$num = array_values($num1[0])[0];
						if ($num > 0)
						{
							Project_Details::get_db()->updateArray( $this->_tablename, $save, $this->_keyname );
							$save1 = false;
						}
					}
					if ($save1)
					{
						$whereclause = " WHERE ".OJ_Utilities::get_where_clause($save);
						$sql = "SELECT $this->_keyname FROM ".$this->_tablename.$whereclause." LIMIT 1";
						$ret1 = Project_Details::get_db()->loadResult($sql);
						if ($ret1 == null)
						{
							if (self::$_testing)
							{
								print "1.insert ".$this->_tablename."\n";
								var_dump($save);
								$ret = self::$_test_counter++;
							}
							else
							{
								$ret = Project_Details::get_db()->insertArray( $this->_tablename, $save);
							}
						}
						else
						{
							$ret = $ret1;
						}
					}
				}
				else
				{
					if (self::$_testing)
					{
						print "2.insert ".$this->_tablename."\n";
						var_dump($save);
						$ret = self::$_test_counter++;
					}
					else
					{
						$ret = Project_Details::get_db()->insertArray( $this->_tablename, $save);
					}
				}
            }
        }
		if ($ret > 0)
		{
			$this->_data[$this->_keyname] = $ret;
		}
        return $ret;
    }

    public function get_table_name()
    {
        return $this->_tablename;
    }
    
    public function get_column_names()
    {
        if ($this->_column_names == null)
        {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name='".$this->_tablename."'";
            $this->_column_names = Project_Details::get_db()->loadColumn($sql);
        }
        return $this->_column_names;
    }
    
    public function get_column_name_set()
    {
        if ($this->_column_name_set == null)
        {
            $this->_column_name_set = new OJ_String_Set($this->get_column_names());
        }
        return $this->_column_name_set;
    }
    
    public function updateable()
    {
        $cns = $this->get_column_name_set();
        return $cnc->contains($this->_tablename.'$parent_id');
    }
    
    public function get_parent_id()
    {
        $ret = 0;
        $fname = $this->_tablename.'$parent_id';
        if (array_key_exists($fname, $this->_data))
        {
            $ret = $this->_data[$fname];
        }
        return $ret;
    }
    
    public function get_key()
    {
        $ret = null;
        if (array_key_exists($this->_keyname, $this->_data))
        {
            $ret = $this->_data[$this->_keyname];
        }
        return $ret;
    }
    
    public function delete()
    {
        $ret = false;
        $key = $this->get_key();
        if ($key != null)
        {
			if (is_numeric($key))
			{
				$where = $this->_keyname."=".$key;
			}
			else
			{
				$where = $this->_keyname."='".$key."'";
			}
            $r = Project_Details::get_db()->delete($this->_tablename, $where);
            $ret = $r != null;
        }
        return $ret;
    }
	
	public function get_name()
	{
		if ($this->_nameval == null)
		{
			$ret = strval($this->get_key());
		}
		elseif (array_key_exists($this->_nameval, $this->_data))
        {
            $ret = $this->_data[$this->_nameval];
        }
		else
		{
			$ret = strval($this->get_key());
		}
		return $ret;
	}
    
    public abstract function get_table_information();
    
}

class OJ_HTML
{
    public static function to_html($args)
    {
        $ret = null;
        if ($args !== null)
        {
            $ret = '';
            if (is_array($args))
            {
                foreach ($args as $arg)
                {
                    $ret .= self::to_html($arg);
                }
            }
            elseif ($args instanceof OJ_HTMLizable)
            {
                $ret .= $args->to_html();
            }
            elseif (is_string($args))
            {
                $ret .= $args;
            }
        }
        return $ret;
    }
    
    public static function insert_into_paragraphs($args)
    {
        $ret = null;
        if ($args != null)
        {
            if (is_array($args))
            {
                $ret = array();
                foreach ($args as $arg)
                {
                    $ret[] = new OJ_P($arg);
                }
            }
            elseif ($args instanceof OJ_HTMLizable)
            {
                $ret = new OJ_P($args);
            }
        }
        return $ret;
    }
    
    public static function add_line_breaks($args)
    {
        $ret = null;
        if ($args != null)
        {
            if (is_array($args))
            {
                $ret = array();
                $first = true;
                foreach ($args as $arg)
                {
                    if ($first)
                    {
                        $first = false;
                        $ret[] = $arg;
                    }
                    else
                    {
                        $ret[] = "<br/>".$arg;
                    }
                }
            }
            elseif (is_string($args))
            {
                $ret = $args."<br/>";
            }
            else
            {
                $ret = $args;
            }
        }
        return $ret;
    }
    
    public static function labelled_html_object($text, $htmlobj, $lblid = null, $lblcssclass = null, $inpara = true, $indiv = false, $description = null)
    {
        $id = $htmlobj->id;
        if ($id == null)
        {
            $id = "oj-id-".time();
            $htmlobj->id = $id;
        }
        $ret = new OJ_LABEL($id, array(new OJ_SPAN($text, null, "oj-labelled-object-text"), $htmlobj), $lblid, $lblcssclass);
//        $lbl = new OJ_LABEL($id, $text, $lblid, $lblcssclass);
//        $ret = array($lbl, $htmlobj);
        if ($inpara)
        {
            $ret = self::insert_into_paragraphs($ret);
        }
        elseif ($indiv)
        {
            $containerid = $id.'-container';
            if ($description != null)
            {
                $desc = new OJ_DIV(new OJ_P($description), null, "oj-help-tip");
                $ret = array($desc, $ret);
            }
            $ret = new OJ_DIV($ret, $containerid, "oj-labelled-object-container");
        }
        return $ret;
    }
	
	public static function split_uri($uri)
	{
		$q = strpos($uri, '?');
		if ($q === FALSE)
		{
			$qs = "";
			$path = $uri;
		}
		else
		{
			$qs = substr($uri, $q + 1);
			$path = substr($uri, 0, $q);
		}
		$ret = [];
		if (strlen($qs) > 0)
		{
			$qsa = explode('&', $qs);
			foreach ($qsa as $item)
			{
				$itema = explode('=', $item);
				$ret[$itema[0]] = $itema[1];
			}
		}
		return ["path"=>$path, "query"=>$ret];
	}
    
}

interface OJ_HTMLizable
{
    public function to_html();
}

class OJ_Form_Select_Detail
{

    public function __construct($form_input_detail, $class_name, $where = null, $text_key = null, $value_key = null, $tooltip_key = null)
    {
        $tabinfo = $form_input_detail->get_table_information();
        $tablename = $tabinfo->get_tablename_from_classname($class_name);
//        echo "classname ".$class_name." tablename ".$tablename."<br/>";
        $this->_data['classname'] = $class_name;
        $this->_data['where'] = $where;
        $this->_data['text_key'] = $tabinfo->get_table_equivalent($tablename, $text_key);
        $this->_data['value_key'] = $tabinfo->get_table_equivalent($tablename, $value_key);
        $this->_data['tooltip_key'] = $tabinfo->get_table_equivalent($tablename, $tooltip_key);
        $this->_data['form_input_detail'] = $form_input_detail;
    }

    public function get_input_name()
    {
        return OJ_Utilities::get_input_name_from_column_name($this->_data['form_input_detail']->tablename, $this->_data['form_input_detail']->columnname);
    }
    
    public function get_select_object($instance = null)
    {
        $fid = $this->_data['form_input_detail'];
//        OJ_Utilities::var_dump_pre($fid);
        $id = $fid->get_input_name();
        $cssclass = $fid->cssclass;
//        echo "here1 ".$this->_data['text_key']. " ".$this->_data['value_key']."<br/>";
        if ($this->_data['text_key'] == null)
        {
			$initial_value = null;
			if ($instance != null)
			{
				$fid = $this->_data['form_input_detail'];
				$col = $fid->columnname;
				$initial_value = $instance->$col;
			}
            $ret = OJ_HTML_Utilities::get_select_list_from_array($fid->contents,
             $this->classname, $initial_value, $id, $cssclass);
        }
        else
        {
            $ret = OJ_HTML_Utilities::get_select_list_of_some($this->_data['classname'], $this->_data['where'], $this->get_input_name(),
         $this->_data['text_key'], $this->_data['value_key'], $this->_data['value_key'], $this->_data['tooltip_key'], $id, $cssclass);
        }
        $ret->add_attribute("name", $fid->get_input_name());
        return $ret;
    }
    
}

class OJ_Form_Input_Detail extends OJ_Object
{
    /**
     * returns a hash keyed by column name
     */
    public static function get_form_details_for_table($tablename, $objclass, $omit_columns = null)
    {
        $sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$tablename."'";
        $list = Project_Details::get_db()->loadList($sql);
        $obj = new $objclass();
        $table_info = $obj->get_table_information();
        $ret = array();
        foreach ($list as $hash)
        {
            $nm = $hash['COLUMN_NAME'];
            if (($nm != 'id') && (($omit_columns == null) || (array_search($nm, $omit_columns) === FALSE)))
            {
                $nm1 = $nm;
                if (OJ_Utilities::ends_with($nm, "s_id"))
                {
                    $nm1 = substr($nm, 0, -4);
                }
                else if (OJ_Utilities::ends_with($nm, "_id"))
                {
                    $nm1 = substr($nm, 0, -3);
                }
                if (($dlr = strpos($nm1, '$')) !== FALSE)
                {
                    $nm1 = substr($nm1, $dlr + 1);
                }
                $lbl = str_replace('_', ' ', $nm1).": ";
                switch ($hash['DATA_TYPE'])
                {
                    case 'varchar':
                        if (($dlr = strpos($nm, '$')) !== FALSE)
                        {
                            $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'user');
                            $lbl = substr($nm, $dlr + 1);
                        }
                        elseif ((strpos($nm, 'colour') !== FALSE) || (strpos($nm, 'color') !== FALSE))
                        {
                            $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'color');
                        }
                        elseif ((strpos($nm, 'path') !== FALSE) || (strpos($nm, 'icon') !== FALSE))
                        {
                            $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'file');
                        }
                        else
                        {
                            $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'text');
                        }
                        break;
                    case 'tinyint':
                        $fid = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'select');
                        $fid->contents = 'array:'.$nm;
                        $ret[$nm] = $fid;
                        break;
                    case 'smallint':
                        $fid = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'number');
                        $fid->contents = 'num:'.$nm;
                        $ret[$nm] = $fid;
                        break;
                    case 'int':
                        if (OJ_Utilities::ends_with($nm, '_id'))
                        {
                            $fid = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'select');
                            $fid->contents = $nm;
                            $ret[$nm] = $fid;
                        }
                        else
                        {
                            $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'text');
                        }
                        break;
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, $hash['DATA_TYPE']);
                        break;
                    case 'text':
                        $ret[$nm] = new OJ_Form_Input_Detail($table_info, $obj, $nm, 'textarea');
                        break;
                    default:
                        break;
                }
                $ret[$nm]->label = $lbl;
                $ret[$nm]->description = $table_info->get_description($tablename, $nm);
            }
        }
        return $ret;
    }
    
    public static function get_form($form_input_detail, $action = null, $method = null, $id = null, $cssclass = null, $instance = null)
    {
        $contents_id = $id == null?null:$id."-contents";
        $contents_cssclass = $cssclass == null?"oj-form-input":($cssclass."-contents");
		if (is_array($form_input_detail))
		{
			$keys = array_keys($form_input_detail);
			$fid = $form_input_detail[$keys[0]];
			$tablename = $fid->tablename;
			$classname = $fid->classname;
		}
		else
		{
			$tablename = $form_input_detail->tablename;
			$classname = $form_input_detail->classname;
		}
        $contents = self::get_input_object($form_input_detail, $contents_id, $contents_cssclass, $instance);
        $tbl = new OJ_INPUT("hidden", "tablename", null, null, $tablename);
        $cls = new OJ_INPUT("hidden", "classname", null, null, $classname);
		if ($instance == null)
		{
			$form_contents = array($contents, $tbl, $cls);
		}
		else
		{
			$iid = $instance->get_key();
			$idinp = new OJ_INPUT("hidden", "instance", null, null, $iid);
			$form_contents = array($contents, $tbl, $cls, $idinp);
		}
        return new OJ_FORM($form_contents, $action, $method, $id, $cssclass);
    }
    
    public static function get_form_with_submit_button($form_input_detail, $action = null, $method = null, $id = null, $cssclass = null, $module="sysadmin")
    {
        $querystring = "m=".$module."&a=".$action;
        $encodedhref = '?'.encodeQueryString($querystring);
        $contents_id = $id == null?null:$id."-contents";
        $contents_cssclass = $cssclass == null?null:$cssclass."-contents";
        $hasfile = false;
        if (is_array($form_input_detail))
        {
            $fid = reset($form_input_detail);
            foreach ($form_input_detail as $k => $v)
            {
                $hasfile = $hasfile || $v->type == 'file';
            }
        }
        else
        {
            $hasfile = $form_input_detail->type == 'file';
            $fid = $form_input_detail;
        }
//        OJ_Utilities::var_dump_pre($form_input_detail);exit;
        $contents = self::get_input_object($form_input_detail, $contents_id, $contents_cssclass);
//        OJ_Utilities::var_dump_pre($form_input_detail);OJ_Utilities::var_dump_pre($contents);
        $tbl = new OJ_INPUT("hidden", "tablename", null, null, $fid->tablename);
        $cls = new OJ_INPUT("hidden", "classname", null, null, $fid->classname);
//        $dosql = new OJ_INPUT("hidden", "dosql", null, null, $action);
        $csrf = new OJ_INPUT("hidden", "csrf_token", null, null, $_SESSION['CSRFToken']);
        $submit_id = $id == null?null:$id."-submit";
        $submit_cssclass = $cssclass == null?null:$cssclass."-submit";
        // <input type="submit" value="Submit" data-role="button" data-icon="check" data-theme="c" />
        $btn = new OJ_INPUT("submit", null, null, null, "Submit");
        $btn->add_attribute("data-role","button");
        $btn->add_attribute("data-icon","check");
        $btn->add_attribute("data-theme","c");
        //        $btn = new OJ_BUTTON("submit", "Submit");
//        $btn->add_attribute("style", "width:12em;");
        $submit = new OJ_DIV($btn, null, "oj-submit-button-div");
        $ret = new OJ_FORM(array($contents, $tbl, $cls, $dosql, $csrf, $submit), $encodedhref, $method, $id, $cssclass);
        if ($hasfile)
        {
            $ret->add_attribute("enctype", "multipart/form-data");
        }
        return $ret;
    }
    
    public static function get_input_object($form_input_detail, $id = null, $cssclass = null, $instance = null)
    {
//        OJ_Utilities::var_dump_pre($instance);
        $ret = null;
        if (is_array($form_input_detail))
        {
            $ret1 = array();
            foreach ($form_input_detail as $fid)
            {
                $ret1[] = self::get_input_object($fid, null, null, $instance);
            }
            $ret = new OJ_DIV($ret1, $id, $cssclass);
        }
        else
		{
			if ($form_input_detail instanceof OJ_Form_Input_Detail)
			{
				$form_input_detail->set_initial_value($instance);
				$ret1 = null;
				$contents = $form_input_detail->contents;
	//            echo $form_input_detail->type." <br/>";
	//            OJ_Utilities::var_dump_pre($form_input_detail);
				switch ($form_input_detail->type)
				{
					case 'select':
						if ($contents != null)
						{
	//                        OJ_Utilities::var_dump_pre($contents);
							if ($contents instanceof OJ_Form_Select_Detail)
							{
								$ret1 = $contents->get_select_object($instance);
							}
							elseif (is_array($contents))
							{
								$sel = new OJ_Form_Select_Detail($form_input_detail);
								$ret1 = $sel->get_select_object($instance);
							}
							elseif (is_string($contents))
							{
	//                            echo $contents."<br/>";
								if (OJ_Utilities::starts_with($contents, "array:"))
								{
	//                                OJ_Utilities::var_dump_pre($form_input_detail);
									$colon = strpos($contents, ':');
									$tabinfo = $form_input_detail->get_table_information();
									$array = $tabinfo->get_select_array($form_input_detail->tablename, substr($contents, $colon + 1));
									if ($array != null)
									{
										$form_input_detail->contents = $array;
										$sel = new OJ_Form_Select_Detail($form_input_detail);
										$ret1 = $sel->get_select_object($instance);
									}
								}
								elseif (OJ_Utilities::ends_with($contents, '_id'))
								{
									$tname = substr($contents, 0, -3);
									$dlr = strpos($tname, '$');
									$tabinfo = $form_input_detail->get_table_information();
									if ($dlr === FALSE)
									{

										$class_name = $tabinfo->get_classname_from_tablename($tname);
										$sel = new OJ_Form_Select_Detail($form_input_detail, $class_name, null, "name", "id", null);
										$ret1 = $sel->get_select_object($instance);
									}
									else
									{
										$key = substr($tname, $dlr + 1);
										$tname1 = substr($tname, 0, $dlr);
	//                                    echo $tname1."__".$key."<br/>";
										$class_name = $tabinfo->get_classname_from_tablename($tname1);
	//                                    echo $class_name."<br/>";
										$where = $tabinfo->get_where_array($tname1, $key);
	//                                    OJ_Utilities::var_dump_pre($where);
										$sel = new OJ_Form_Select_Detail($form_input_detail, $class_name, $where, "name", "id", null);
										$ret1 = $sel->get_select_object($instance);
									}
								}
							}
						}
						break;
					case 'number':
						if ($contents !== null)
						{
							if (is_numeric($contents))
							{
								$form_input_detail->initial_value = $contents;
							}
							elseif (is_string($contents)&& OJ_Utilities::starts_with($contents, "num:"))
							{
	//                                OJ_Utilities::var_dump_pre($form_input_detail);
								$colon = strpos($contents, ':');
								$tabinfo = $form_input_detail->get_table_information();
								$key = substr($contents, $colon + 1);
	//                            echo $form_input_detail->tablename."__".$key;
								$array = $tabinfo->get_number_array($form_input_detail->tablename, $key);
	//                            OJ_Utilities::var_dump_pre($array);
								if ($array != null)
								{
									$form_input_detail->add_attributes($array);
									$form_input_detail->contents = null;
								}
							}
						}
						$ret1 = new OJ_INPUT($form_input_detail->type, $form_input_detail->get_input_name(),
							$form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
					case 'user':
	//                    echo "in case user<br/>";
						if (($contents != null) && ($contents instanceof OJ_HTML_Object))
						{
							$ret1 = $contents;
						}
						else
						{
	//                        echo "calling user function<br/>";
							$tabinfo = $form_input_detail->get_table_information();
							$dlr = strpos($form_input_detail->columnname, '$');
							if ($dlr === FALSE)
							{
								$cn = $form_input_detail->columnname;
							}
							elseif ($dlr == (strlen($form_input_detail->columnname) - 1))
							{
								$cn = substr($form_input_detail->columnname, 0, -1);
							}
							else
							{
								$cn = substr($form_input_detail->columnname, $dlr + 1);
							}
	//                        echo $form_input_detail->tablename." ".$cn;
							$ret1 = $tabinfo->get_user_object($form_input_detail->tablename, $cn);
	//                        OJ_Utilities::var_dump_pre($ret1);
						}
						$form_input_detail->label = null;
						break;
					case 'sub':
						if (($contents != null) && ($contents instanceof OJ_Form_Input_Detail))
						{
							$ret1 = new OJ_DIV(get_input_object($contents), $form_input_detail->get_input_name(), $form_input_detail->cssclass);
						}
						elseif (is_array($contents))
						{
							$tmp = array();
							foreach ($contents as $element)
							{
								if ($element instanceof OJ_Form_Input_Detail)
								{
									$tmp[] = get_input_object($element);
								}
							}
							$ret1 = new OJ_DIV($tmp, $form_input_detail->get_input_name(), $form_input_detail->cssclass);
						}
						break;
					case 'textarea':
						$ret1 = new OJ_TEXTAREA($form_input_detail->get_input_name(),
							$form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
					default:
						$ret1 = new OJ_INPUT($form_input_detail->type, $form_input_detail->get_input_name(),
							$form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
				}
				$atts = $form_input_detail->get_attributes();
				foreach ($atts as $attname => $attval)
				{
					$ret1->add_attribute($attname, $attval);
				}
				$lbl = $form_input_detail->label;
				if ($lbl == null)
				{
					$ret = $ret1;
				}
				else
				{
					$ret = OJ_HTML::labelled_html_object($lbl, $ret1, null, null, false, true, $form_input_detail->description);
					$desc = $form_input_detail->description;
				}
	//            echo $lbl."<br/>";
	//OJ_Utilities::var_dump_pre($ret);
			}
			elseif ($form_input_detail instanceof OJ_HTML_Object)
			{
				$ret = $form_input_detail;
			}
			if ($id != null)
			{
				$ret->add_attribute("id", $id);
			}
			if ($cssclass == null)
			{
				$cssclass = "oj-form-input";
			}
			$ret->add_attribute("class", $cssclass);
		}
        return $ret;
    }

    private $_attributes;
    private $_table_info;
    
    public function __construct($table_info, $obj, $column_name, $type)
    {
        $this->_table_info = $table_info;
        if (is_string($obj))
        {
            if (class_exists($obj))
            {
                $this->_data['classname'] = $obj;
                $this->_data['tablename'] = $table_info->get_tablename_from_classname($obj);
            }
            else
            {
                $this->_data['classname'] = $table_info->get_classname_from_tablename($obj);
                $this->_data['tablename'] = $obj;
            }
        }
        else
        {
            $this->_data['classname'] = get_class($obj);
            $this->_data['tablename'] = $obj->get_table_name();
        }
        $this->_data['columnname'] = $column_name;
        $this->_data['type'] = $type;
        $this->_attributes = array();
    }
	
	public function set_initial_value($instance)
	{
		if ($instance != null)
		{
			$col = $this->_data['columnname'];
			$this->_data['initial_value'] = stripslashes($instance->$col);
//			echo "initial data for ".$this->_data['columnname']." set to ".$this->_data['initial_value']."\n";
		}
	}
    
    public function get_table_information()
    {
        return $this->_table_info;
    }
    
    public function get_input_name()
    {
        return OJ_Utilities::get_input_name_from_column_name($this->tablename, $this->columnname);
    }
    
    public function add_attribute($attname, $attval)
    {
        $this->_attributes[$attname] = $attval;
    }
    
    public function add_attributes($atts)
    {
        foreach ($atts as $attname => $attval)
        {
            $this->_attributes[$attname] = $attval;
        }
    }
    
    public function get_attribute($attname)
    {
        return array_key_exists($attname, $this->_attributes)
        ? $this->_attributes[$attname]
        : null
      ;
    }
    
    public function get_attributes()
    {
        return $this->_attributes;
    }
    
}

class OJ_HTML_Object extends OJ_Object implements OJ_HTMLizable
{
    
    protected $_tag;
    protected $_contents;
    
    public function __construct($tag, $contents, $id = null, $cssclass = null)
    {
        parent::__construct();
        $this->_tag = $tag;
        $this->_contents = OJ_HTML::to_html($contents);
        if ($id != null)
        {
            $this->_data['id'] = $id;
        }
        if ($cssclass != null)
        {
            $this->_data['class'] = $cssclass;
        }
    }
	
	public function append_to_contents($newcontents)
	{
		$this->_contents .= OJ_HTML::to_html($newcontents);
	}
    
	public function prepend_to_contents($newcontents)
	{
		$this->_contents = OJ_HTML::to_html($newcontents).$this->_contents;
	}
    
    public function add_attribute($attname, $attval)
    {
        if ($attval === null)
        {
            if (isset($this->_data[$attname]))
            {
                unset($this->_data[$attname]);
            }
        }
        else
        {
            $this->_data[$attname] = $attval;
        }
    }
    
    public function add_attributes($atts)
    {
        foreach ($atts as $attname => $attval)
        {
            $this->add_attribute($attname, $attval);
        }
    }
    
    public function has_attribute($attname)
    {
        return array_key_exists($attname, $this->_data);
    }
    
    public function to_html()
    {
        $rest = $this->_contents === null?'/>':('>'.$this->_contents.'</'.$this->_tag.'>');
        $atts = '';
//        echo "rest of ".$this->_tag." is ".$rest." rest";
        foreach ($this->_data as $key=>$value)
        {
            $atts .= ' '.$key.'="'.$value.'"';
        }
        $ret = '<'.$this->_tag.$atts.$rest;
        // if ($this->_tag == 'div')
        // {
            // $ret .= "<!-- ". $this->_data['id'].'-->';
        // }
        return $ret;
    }
    
    public function get_contents_html()
    {
        return $this->_contents === null?"":$this->_contents;
    }
    
    public function is_empty()
    {
        return $this->_contents == null;
    }
    
    public function add_tooltip($tt)
    {
        $this->add_attribute("title", $tt);
    }
    
    public function add_class($cl)
    {
        if (array_key_exists("class", $this->_data))
        {
            $newcl = $this->_data['class']." ".$cl;
        }
        else
        {
            $newcl = $cl;
        }
        $this->_data['class'] = $newcl;
    }
	
	public function __toString()
	{
		return $this->to_html();
	}
}

class OJ_LABEL extends OJ_HTML_Object
{
    
    public function __construct($for, $text, $id = null, $cssclass = null)
    {
        parent::__construct('label', $text, $id, $cssclass);
        $forid = '';
		if ($for != null)
		{
			if (is_string($for))
			{
				$forid = $for;
			}
			elseif ($for instanceof OJ_HTML_Object)
			{
				$forid = $for->id;
			}
			$this->_data['for'] = $forid;
		}
    }
    
}

class OJ_A extends OJ_HTML_Object
{
    
    public function __construct($href, $contents, $id = null, $cssclass = null, $onclick = null)
    {
        parent::__construct('a', $contents, $id, $cssclass == null?"oj-link-class":$cssclass);
        if ($href != null)
        {
            $this->_data['href'] = $href;
        }
        if ($onclick != null)
        {
            $this->_data['onclick'] = $onclick;
        }
    }
    
}

class OJ_IFRAME extends OJ_HTML_Object
{
    
    public function __construct($src, $id = null, $cssclass = null, $alt = null)
    {
        parent::__construct('iframe', new OJ_P($alt == null?"Your browser does not support iframes.":$alt), $id, $cssclass);
        if ($src != null)
        {
            $this->_data['src'] = $src;
        }
    }
    
}

class OJ_IMG extends OJ_HTML_Object
{
    
    public function __construct($src, $id = null, $cssclass = null, $onclick = null, $alt = "no image available")
    {
        parent::__construct('img', null, $id, $cssclass);
        if ($src != null)
        {
            $this->_data['src'] = $src;
        }
        if ($onclick != null)
        {
            $this->_data['onclick'] = $onclick;
        }
        if ($alt != null)
        {
            $this->_data['alt'] = $alt;
        }
    }
    
}

class OJ_P extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('p', $contents, $id, $cssclass);
    }
    
}

class OJ_H extends OJ_HTML_Object
{
    
    public function __construct($num, $text)
    {
        parent::__construct('h'.$num, $text, null, null);
    }
    
}

class OJ_DIV extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('div', $contents === null?"":$contents, $id, $cssclass);
    }
    
}

class OJ_DIV_ARRAY extends OJ_DIV
{
    private static function make_contents($contents, $vertical)
    {
        $ret = array();
        if (is_array($contents))
        {
            foreach ($contents as $arg)
            {
                if ($arg instanceof OJ_DIV)
                {
                    $ret[] = $arg;
                }
                else
                {
                    $ret[] = new OJ_DIV($arg);
                }
            }
        }
        elseif ($contents instanceof OJ_DIV)
        {
            $ret[] = $contents;
        }
        else
        {
            $ret[] = new OJ_DIV($contents);
        }
        if (!$vertical)
        {
            foreach ($ret as $div)
            {
                $div->add_class("oj-inline-block");
            }
        }
        return $ret;
    }
    
    private $_vertical;
    private $_divs;
    
    public function __construct($contents, $vertical = true, $id = null, $cssclass = null)
    {
        parent::__construct(self::make_contents($contents, $vertical), $id, $cssclass);
        $this->_vertical = $vertical;
        $this->_divs = self::make_contents($contents, $vertical);
    }
    
    public function add_div($div)
    {
        $newdivs = self::make_contents($div, $this->_vertical);
        foreach ($newdivs as $nd)
        {
            $this->_divs[] = $nd;
        }
        $this->_contents = OJ_HTML::to_html($this->_divs);
    }
}

class OJ_FORM extends OJ_HTML_Object
{
    
    public function __construct($contents, $action = null, $method = null, $id = null, $cssclass = null)
    {
        parent::__construct('form', $contents, $id, $cssclass);
        if ($action != null)
        {
            $this->_data['action'] = $action;
        }
        if ($method != null)
        {
            $this->_data['method'] = $method;
        }
    }
    
}

class OJ_INPUT extends OJ_HTML_Object
{
    
    public function __construct($type, $name, $id = null, $cssclass = null, $value = null)
    {
        parent::__construct('input', null, $id, $cssclass);
        $this->_data['type'] = $type;
        if ($name != null)
        {
            $this->_data['name'] = $name;
            // if ($id == null)
            // {
                // $this->_data['id'] = $name;
            // }
        }
        if ($value != null)
        {
			if ($type == 'file')
			{
				$this->_data['title'] = $value;
			}
			else
			{
				$this->_data['value'] = $value;
			}
        }
    }
}

class OJ_TEXTAREA extends OJ_HTML_Object
{
    
    public function __construct($name, $id = null, $cssclass = null, $value = '')
    {
        parent::__construct('textarea', $value, $id, $cssclass);
        if ($name != null)
        {
            $this->_data['name'] = $name;
            if ($id == null)
            {
                $this->_data['id'] = $name;
            }
        }
        if ($value !== null)
        {
            $this->_data['value'] = $value;
        }
    }
}

class OJ_BUTTON extends OJ_HTML_Object
{
    
    public function __construct($type, $text, $id = null, $cssclass = null, $onclick = null)
    {
        parent::__construct('button', $text, $id, $cssclass);
        $this->_data['type'] = $type;
        if ($onclick !== null)
        {
            $this->_data['onclick'] = $onclick;
        }
    }
}

class OJ_SPAN extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('span', $contents, $id, $cssclass);
    }
    
}

class OJ_LI extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('li', $contents, $id, $cssclass);
    }
    
}

class OJ_LEGEND extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('legend', $contents, $id, $cssclass);
    }
    
}

class OJ_FIELDSET extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('fieldset', $contents, $id, $cssclass);
    }
    
}

class OJ_LIST extends OJ_HTML_Object
{
    
    public function __construct($items, $ordered = false, $id = null, $cssclass = null)
    {
        parent::__construct($ordered?'ol':'ul', $items, $id, $cssclass);
    }
    
}
class OJ_OPTION extends OJ_HTML_Object
{
	
	public static function get_options($strings, $selected1 = null)
	{
		$ret = [];
		foreach ($strings as $str)
		{
			$ret[] = new OJ_OPTION($str, $str, $str === $selected1);
		}
		return $ret;
	}
    
    public function __construct($text, $value, $selected = false, $id = null, $cssclass = null)
    {
        parent::__construct('option', $text, $id, $cssclass);
        if ($value !== null)
        {
            $this->_data['value'] = $value;
        }
        else
        {
            $this->_data['value'] = $text;
        }
        if ($selected)
        {
            $this->_data['selected'] = 'selected';
        }
    }
}

class OJ_SELECT extends OJ_HTML_Object
{
    public function __construct($options, $name, $id = null, $cssclass = null)
    {
        parent::__construct('select', OJ_HTML::to_html($options), $id, $cssclass);
        if ($name != null)
        {
            $this->_data['name'] = $name;
            if ($id == null)
            {
                $this->_data['id'] = $name;
            }
        }
    }
}

class OJ_TD extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('td', $contents, $id, $cssclass);
    }
    
}

class OJ_TH extends OJ_HTML_Object
{
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('th', $contents, $id, $cssclass);
    }
    
}

class OJ_TR extends OJ_HTML_Object
{
    
    private static function make_contents($contents)
    {
        $ret = '';
        if (is_array($contents) && (count($contents) > 0) && (($contents[0] instanceof OJ_TD) || ($contents[0] instanceof OJ_TH)))
        {
            $ret = $contents;
        }
        return $ret;
    }
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('tr', self::make_contents($contents), $id, $cssclass);
    }
    
}

class OJ_TBODY extends OJ_HTML_Object
{
    
    private static function make_contents($contents)
    {
//        var_dump($contents); echo "<br/>";
        $ret = '';
        if (is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof OJ_TR))
        {
            $ret = $contents;
        }
//        var_dump($ret); echo "<br/>"; echo "<br/>";
        return $ret;
    }
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('tbody', self::make_contents($contents), $id, $cssclass);
    }
    
}

class OJ_TFOOT extends OJ_HTML_Object
{
    
    private static function make_contents($contents)
    {
        $ret = '';
        if (is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof OJ_TR))
        {
            $ret = $contents;
        }
        return $ret;
    }
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('tfoot', self::make_contents($contents), $id, $cssclass);
    }
    
}

class OJ_THEAD extends OJ_HTML_Object
{
    
    private static function make_contents($contents)
    {
        $ret = '';
        if ((is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof OJ_TR))
            || ($contents instanceof OJ_TR))
        {
            $ret = $contents;
        }
        return $ret;
    }
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('thead', self::make_contents($contents), $id, $cssclass);
    }
    
}

class OJ_TABLE extends OJ_HTML_Object
{
    
    private static function make_contents($contents)
    {
        $ret = '';
        if ($contents != null)
        {
            if ($contents instanceof OJ_TBODY)
            {
                $ret = $contents;
            }
            elseif (is_array($contents) && (count($contents) > 0))
            {
                if ($contents[0] instanceof OJ_TR)
                {
                    $ret = new OJ_TBODY($contents);
                }
                elseif (($contents[0] instanceof OJ_TBODY) || ($contents[0] instanceof OJ_THEAD) || ($contents[0] instanceof OJ_TFOOT))
                {
                    $ret = $contents;
                }
            }
        }
        return $ret;
    }
    
    public function __construct($contents, $id = null, $cssclass = null)
    {
        parent::__construct('table', self::make_contents($contents), $id, $cssclass);
    }
    
}

class OJ_Concertina extends OJ_DIV
{
    
    private static function make_contents($text, $contents, $slug, $loadparam)
    {
        if (($slug == null) && is_a($contents, 'OJ_HTML_Object'))
        {
            $slug = $contents->id;
        }
        $lp = $loadparam == null?'':", '".$loadparam."'";
        $lnk = new OJ_A("#", $text, null, "oj-link-class", "OJ_open_div('".$slug."'".$lp.");");
        $lnkdiv = new OJ_DIV($lnk->to_html(),$slug.'-div-link', "oj-expandable-link");
        $closelnk = new OJ_A("#", "close", null, null, "OJ_close_div('".$slug."');");
        $closediv = new OJ_DIV($closelnk->to_html(),$slug.'-div-close', "oj-expandable-close");
        $div = new OJ_DIV(OJ_HTML::to_html(array($contents, $closediv)), $slug.'-div', "oj-expandable");
        $ret = OJ_HTML::to_html(array($lnkdiv, $div));
        return $ret;
    }
    
    private static function make_id($contents, $slug)
    {
        if (($slug == null) && is_a($contents, 'OJ_HTML_Object'))
        {
            $slug = $contents->id;
        }
        return $slug.'-div-outer';
    }
    
    public function __construct($text, $contents, $slug = null, $loadparam = null)
    {
        parent::__construct(self::make_contents($text, $contents, $slug, $loadparam), self::make_id($contents, $slug), "oj-expandable-outer");
    }
    
}

class OJ_Collapsible extends OJ_DIV
{
	public function __construct($contents, $id, $cssclass, $heading, $headingnum = 2)
	{
		parent::__construct(array(new OJ_H($headingnum, $heading), $contents), $id, $cssclass);
		$this->add_attribute("data-role", "collapsible");
	}
}

class OJ_CollapsibleSet extends OJ_DIV
{
	public function __construct($collapsibles, $id, $cssclass)
	{
		parent::__construct($collapsibles, $id, $cssclass);
		$this->add_attribute("data-role", "collapsible-set");
	}
}

class OJ_Collapse extends OJ_DIV
{
	private static function make_contents($contents, $id, $cssclass, $heading, $headingnum, $open, $parentid, $selectable)
	{
//	  echo "heading=".$heading."<br/>";
		$headingid = $id == null?null:($id."-heading");
		$headingdivid = $id == null?null:($id."-heading-div");
		$panelbodyid = $id == null?null:($id."-panel-body");
		$collapseid = $id == null?("div-".time()."-collapse"):($id."-collapse");
		$hdga = new OJ_A('#'.$collapseid, $heading, null, null);
		$hdga->add_attribute("data-toggle", "collapse");
		if ($parentid != null)
		{
			$hdga->add_attribute("data-parent", "#".$parentid);
		}
		if ($selectable)
		{
			$hdgs = new OJ_INPUT("checkbox", $collapseid."-cb", $collapseid."-cb", "oj-select-cb oj-checkbox");
			$hdgsp = new OJ_SPAN(" ");
			$hdg = new OJ_H($headingnum, array($hdgs, $hdgsp, $hdga));
		}
		else
		{
			$hdg = new OJ_H($headingnum, $hdga);
		}
		$hdg->add_class('panel-title');
		$hdgdiv = new OJ_DIV($hdg, $headingdivid, "panel-heading");
		$panelbody = new OJ_DIV($contents, $panelbodyid, "panel-body");
		$panel = new OJ_DIV($panelbody, $collapseid, "panel-collapse collapse".($open?" in":""));
		return array($hdgdiv, $panel);
	}
	
	public function __construct($contents, $id, $cssclass, $heading, $headingnum = 2, $open = false, $parentid = null, $selectable = false)
	{
		parent::__construct(self::make_contents($contents, $id, $cssclass, $heading, $headingnum, $open, $parentid, $selectable),
			 $id, $cssclass == null?"panel panel-default":("panel panel-default ".$cssclass));
	}	
	
}

class OJ_Accordion extends OJ_DIV
{
	private static function make_contents($contentsarray, $id, $cssclass, $headingnum, $selectable)
	{
		$ret = array();
		$n = 0;
//		var_dump($contentsarray);
		foreach ($contentsarray as $heading => $contents)
		{
			$open = strpos($heading, '__open') !== false;
			$hdg1 = $open?str_replace('__open', '', $heading):$heading;
			$sel = strpos($hdg1, '__selectable') !== false;
			$hdg = $sel?str_replace('__selectable', '', $hdg1):$hdg1;
			$ret[] = new OJ_Collapse($contents, $id."-".$n, null, $hdg, $headingnum, $open, $id, $selectable && $sel);
			$n++;
		}
		return $ret;
	}
	
	/**
	 * contentsarray has form header=>contents. If header ends with '__open' then that one will be initially open
	 */
	public function __construct($contentsarray, $id, $cssclass, $headingnum = 2, $selectable = false)
	{
		parent::__construct(self::make_contents($contentsarray, $id, $cssclass, $headingnum, $selectable), $id, $cssclass);
	}
}
	
abstract class BasicEnum {
	private static $constCacheArray = NULL;

	private static function getConstants() {
		if (self::$constCacheArray == NULL) {
			self::$constCacheArray = [];
		}
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$constCacheArray)) {
			$reflect = new ReflectionClass($calledClass);
			self::$constCacheArray[$calledClass] = $reflect->getConstants();
		}
		return self::$constCacheArray[$calledClass];
	}

	public static function isValidName($name, $strict = false) {
		$constants = self::getConstants();

		if ($strict) {
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	public static function isValidValue($value, $strict = true) {
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict);
	}

	public static function stringValue($value)
	{
		return array_search($value, self::getConstants());
	}

	public static function getValue($str)
	{
		$enum = self::getConstants();
		return array_key_exists($str, $enum)?$enum[$str]:null;
	}
}

class OJ_Stack
{
	
	private $_thestack;
	private $_next1;
	
	public function __construct()
	{
		$this->_thestack = [];
		$this->_next1 = 0;
	}
	
	public function push($obj)
	{
		$this->_thestack[$this->_next1] = $obj;
		$this->_next1++;
	}
	
	public function pop()
	{
		$ret = null;
		if ($this->_next1 > 0)
		{
			$this->_next1--;
			$ret = $this->_thestack[$this->_next1];
		}
		return $ret;
	}
	
	public function peek()
	{
		$ret = null;
		if ($this->_next1 > 0)
		{
			$nxt1 = $this->_next1 - 1;
			$ret = $this->_thestack[$nxt1];
		}
		return $ret;
	}
	
	public function isempty()
	{
		return $this->_next1 === 0;
	}
	
	public function size()
	{
		return $this->_next1;
	}
	
	public function clear()
	{
		$this->_next1 = 0;
	}
}

class OJ_String extends OJ_Object
{
	public static function compare($ojstringa, $ojstringb)
	{
		$ret = 0;
		if ($ojstringa->starts_with_number && $ojstringa->starts_with_number)
		{
			$ret = $ojstringa->number < $ojstringb->number?-1:($ojstringa->number > $ojstringb->number?1:0);
			if ($ret === 0)
			{
				$ret = strcasecmp($ojstringa->rest, $ojstringb->rest);
			}
		}
		else
		{
			$ret = strcasecmp($ojstringa->as_string(), $ojstringb->as_string());
		}
		return $ret;
	}
	
	public static function renumber_string($ojstring, $n)
	{
		$isastring = false;
		if (is_string($ojstring))
		{
			$ojstr = new OJ_String($ojstring);
			$isastring = true;
		}
		else
		{
			$ojstr = $ojstring;
		}
		$ojstr->number = $n;
		$ojstr->gap = " ";
		$ojstr->starts_with_number = true;
		return $isastring?$ojstr->as_string():$ojstr;
	}
	
	public static function renumber_array(&$ojstring_array, $starting_at = 1)
	{
		$n = $starting_at;
		foreach ($ojstring_array as &$ojstr)
		{
			$ojstr->number = $n;
			$n++;
			$ojstr->gap = " ";
			$ojstr->starts_with_number = true;
		}
	}
	
	public function __construct($str)
	{
		$length = strlen($str);
		$num = "";
		$gap = "";
		$base = "";
		$phase = 0;
		$numbase = 10;
		for ($i = 0, $int = ''; $i < $length; $i++)
		{
			if ($phase === 0)
			{
				if (ctype_xdigit($str[$i]))
				{
					if (!ctype_digit($str[$i]))
					{
						$numbase = 16;
					}
					$num .= $str[$i];
				}
				else
				{
					if (ctype_alnum($str[$i]))
					{
						$base = $num;
						$num = "";
						$phase = 2;
					}
					else
					{
						$phase++;
					}
				}
			}
			if ($phase === 1)
			{
				if (!ctype_alnum($str[$i]))
				{
					$gap .= $str[$i];
				}
				else
				{
					$phase++;
				}
			}
			if ($phase === 2)
			{
				$base .= $str[$i];
			}
//			print $phase." ".$str[$i]." ".$numbase."\n";
		}
		$swn = strlen($num) !== 0;
		$num = $swn?intval($num, $numbase):0;
		$this->_data["starts_with_number"] = $swn;
		$this->_data["number"] = $num;
		$this->_data["gap"] = $gap;
		$this->_data["rest"] = $base;
		$this->_data["original"] = $str;
	}
	
	public function as_string()
	{
		return $this->_data["starts_with_number"]?(sprintf('%02d', $this->_data["number"]).$this->_data["gap"].$this->_data["rest"]):$this->_data["original"];
	}
	
	public function __toString()
	{
		$this->as_string();
	}
	
}

class OJ_File extends OJ_Object
{
	
	public static function compare($str1, $str2)
	{
		$ojs1 = new OJ_File($str1);
		$ojs2 = new OJ_File($str2);
		$n1 = $ojs1->starts_with_number();
		$n2 = $ojs2->starts_with_number();
		if (($n1 === false) || ($n2 === false))
		{
			$ret = strcasecmp($ojs1->base, $ojs2->base);
		}
		else
		{
			$ret = $n1 < $n2?-1:($n1 > $n2?1:0);
		}
		return $ret;
	}
	
    public function __construct($path)
    {
        parent::__construct();
		$this->_data["dir"] = dirname($path);
		$bname = basename($path);
		$lastdot = strrpos($bname, '.');
		if ($lastdot !== FALSE)
		{
			$this->_data["extension"] = substr($bname, $lastdot + 1);
			$str = substr($bname, 0, $lastdot);
		}
		else
		{
			$this->_data["extension"] = "";
			$str = $bname;
		}
		$length = strlen($str);
		$num = "";
		$gap = "";
		$base = "";
		$phase = 0;
		$numbase = 10;
		for ($i = 0, $int = ''; $i < $length; $i++)
		{
			if ($phase === 0)
			{
				if (ctype_xdigit($str[$i]))
				{
					if (!ctype_digit($str[$i]))
					{
						$numbase = 16;
					}
					$num .= $str[$i];
				}
				else
				{
					if (ctype_alnum($str[$i]))
					{
						$base = $num;
						$num = "";
						$phase = 2;
					}
					else
					{
						$phase++;
					}
				}
			}
			if ($phase === 1)
			{
				if (!ctype_alnum($str[$i]))
				{
					$gap .= $str[$i];
				}
				else
				{
					$phase++;
				}
			}
			if ($phase === 2)
			{
				$base .= $str[$i];
			}
//			print $phase." ".$str[$i]." ".$numbase."\n";
		}
		$this->_data["number"] = $num;
		$this->_data["gap"] = $gap;
		$this->_data["name"] = $base;
		$this->_data["base"] = $numbase;
	}
	
	public function __toString()
	{
		$dir = (strlen($this->_data["dir"]) === 0) || ($this->_data["dir"] === '.')?"":($this->_data["dir"].DIRECTORY_SEPARATOR);
//		$num = strlen($this->_data["number"]) === 0?"":($this->_data["base"] === 16?$this->_data["number"]):$this->_data["number"]);
		$ext = strlen($this->_data["extension"]) === 0?"":('.'.$this->_data["extension"]);
		return $dir.$this->_data["number"].$this->_data["gap"].$this->_data["name"].$ext;
	}
	
	public function get_name()
	{
		return $this->_data["number"].$this->_data["gap"].$this->_data["name"];
	}
	
	public function starts_with_number()
	{
		$ret = false;
		if (strlen($this->_data["number"]) > 0)
		{
			$ret = intval($this->_data["number"], $this->_data["base"]);
		}
		return $ret;
	}
}

class OJ_Folder extends OJ_File
{
	// $restricted by of form [label=>[extensions]...]
	// an empty extensions array means accept everything
	public function __construct($dirpath, $recursive = false, $restricted_by = null)
	{
        parent::__construct($dirpath);
		if ($restricted_by === null)
		{
			$restricted_by = ["files"=>[]];
		}
		$this->_data["path"] = OJ_File_Utilities::check_dirname($dirpath);
//		print $this->_data["path"]."\n";
		$files = array_diff(scandir($this->_data["path"]), array('.', '..'));
		$this->_data["subdirs"] = [];
		foreach ($restricted_by as $label => $exts)
		{
			$this->_data['label_'.$label] = [];
		}
		$thisclass = get_class($this);
		foreach ($files as $f)
		{
			$file = $this->_data["path"].$f;
			if (is_dir($file))
			{
				if ($recursive)
				{
					array_push($this->_data["subdirs"], new $thisclass($file, $recursive, $restricted_by));
				}
				else
				{
					array_push($this->_data["subdirs"], new OJ_File($file.DIRECTORY_SEPARATOR));
				}
			}
			else
			{
				foreach ($restricted_by as $label => $extensions)
				{
					if ((count($extensions) == 0) || OJ_File_Utilities::is_file_of_type($f, $extensions))
					{
						array_push($this->_data['label_'.$label], new OJ_File($file));
					}
				}
				
			}
		}
//		print get_class($this)."\n";
	}
	
	function contains_files_with_label($label)
	{
		return isset($this->_data['label_'.$label]) && (count($this->_data['label_'.$label]) > 0);
	}
	
	function get_files_with_label($label)
	{
		return isset($this->_data['label_'.$label])?$this->_data['label_'.$label]:[];
	}
	
	public function get_name()
	{
		$ext = strlen($this->_data["extension"]) === 0?"":('.'.$this->_data["extension"]);
		return $this->_data["number"].$this->_data["gap"].$this->_data["name"].$ext;
	}
	
}

class OJ_Audio_Folder extends OJ_Folder
{
	public function __construct($dirpath)
	{
		parent::__construct($dirpath, true, ["audio"=>  OJ_File_Utilities::$audio_extensions, "image"=>  OJ_File_Utilities::$image_extensions,
			"m3u"=>["m3u", "m3u8"], "cue"=>["cue"], "txt"=>["txt"], "pdf"=>["pdf"]]);
		$all_start_with_number = true;
		foreach ($this->_data["label_audio"] as $f)
		{
			if ($f->starts_with_number() === false)
			{
				$all_start_with_number = false;
			}
		}
		if ($all_start_with_number)
		{
			usort($this->_data["label_audio"], "OJ_File::compare");
		}
		$nm = explode(" - ", $this->_data["name"]);
		if (count($nm) > 1)
		{
			$this->_data["artist"] = trim($nm[0]);
		}
		else
		{
			$this->_data["artist"] = "";
		}
		$this->_data["va"] = strtolower($this->_data["artist"]) == "various artists";
	}
	
	function get_audio_files()
	{
		return $this->get_files_with_label("audio");
	}
	
	function get_image_files()
	{
		return $this->get_files_with_label("image");
	}
	
	function get_m3u_file()
	{
		return $this->get_files_with_label("m3u");
	}
	
	function get_cue_file()
	{
		return $this->get_files_with_label("cue");
	}
	
	function get_text_file()
	{
		return $this->get_files_with_label("txt");
	}
	
	function get_pdf_file()
	{
		return $this->get_files_with_label("pdf");
	}
	
	function contains_audio_files($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("audio");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_audio_files($recurse);
			}
		}
		return $ret1;
	}
	
	function contains_image_files($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("image");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_image_files($recurse);
			}
		}
		return $ret1;
	}
	
	function contains_m3u_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("m3u");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_m3u_file($recurse);
			}
		}
		return $ret1;
	}
	
	function contains_cue_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("cue");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_cue_file($recurse);
			}
		}
		return $ret1;
	}
	
	function contains_text_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("txt");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_text_file($recurse);
			}
		}
		return $ret1;
	}
	
	function contains_pdf_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("pdf");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_pdf_file($recurse);
			}
		}
		return $ret1;
	}
	
	function subdirs_contain_audio_files()
	{
		$ret = false;
		foreach ($this->_data["subdirs"] as $af)
		{
			if ($af->contains_audio_files())
			{
				$ret = true;
				break;
			}
		}
		return $ret;
	}
	
	function get_all_image_files()
	{
		$ret = [];
		if ($this->contains_image_files())
		{
			foreach ($this->_data['label_image'] as $f)
			{
				array_push($ret, $f);
			}
		}
		if ($this->contains_audio_files())
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret = array_merge($ret, $af->get_all_image_files());
			}
		}
		else
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				if (!$af->contains_audio_files())
				{
					$ret = array_merge($ret, $af->get_all_image_files());
				}
			}
		}
		return $ret;
	}
	
	function get_all_pdf_files()
	{
		$ret = [];
		if ($this->contains_pdf_file())
		{
			foreach ($this->_data['label_pdf'] as $f)
			{
				array_push($ret, $f);
			}
		}
		if ($this->contains_audio_files())
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret = array_merge($ret, $af->get_all_pdf_files());
			}
		}
		else
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				if (!$af->contains_audio_files())
				{
					$ret = array_merge($ret, $af->get_all_pdf_files());
				}
			}
		}
		return $ret;
	}
}

