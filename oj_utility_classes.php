<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
require_once('phpseclib/Net/SFTP.php');
require_once('database/db_object.php');
require_once('project.php');

class OJ_SFTP
{
	private $_host;
	private $_user;
	private $_pword;
	private $_ftp;
			
	public function __construct($host, $user, $pword)
	{
		$this->_host = $host;
		$this->_user = $user;
		$this->_pword = $pword;
		
	}
	
	private function login()
	{
		$ret = true;
		$this->_ftp = new Net_SFTP($this->_host);
		if (!$this->_ftp->login($this->_user, $this->_pword)) {
			$ret = false;
		}
		return $ret;
	}
	
	public function get($remote, $local)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$this->_ftp->get($remote, $local);
			$ret = file_exists($local);
		}
		return $ret;
	}
	
	public function put($remote, $local)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$this->_ftp->put($remote, $local, NET_SFTP_LOCAL_FILE);
			$ret = $this->_ftp->stat($remote);
		}
		return $ret;
	}
	
	public function stat($remote)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->stat($remote);
		}
		return $ret;
	}
	
	public function lstat($remote)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->lstat($remote);
		}
		return $ret;
	}
	
	public function touch($remote)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->touch($remote);
		}
		return $ret;
	}
	
	public function chmod($perm, $remote, $recurse = false)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->chmod($perm, $remote, $recurse);
		}
		return $ret;
	}
	
	public function chown($remote, $uid, $recurse = false)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->chown($remote, $uid, $recurse);
		}
		return $ret;
	}
	
	public function chgrp($remote, $gid, $recurse = false)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->chgrp($remote, $gid, $recurse);
		}
		return $ret;
	}
	
	public function truncate($remote, $size)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->truncate($remote, $size);
		}
		return $ret;
	}
	
	public function delete($remote, $recurse = true)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->delete($remote, $recurse);
		}
		return $ret;
	}
	
	public function rename($remote, $newname)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->rename($remote, $newname);
		}
		return $ret;
	}
	
	public function mkdir($remotedir)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->mkdir($remotedir);
		}
		return $ret;
	}
	
	public function chdir($remotedir)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->chdir($remotedir);
		}
		return $ret;
	}
	
	public function rmdir($remotedir)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->rmdir($remotedir);
		}
		return $ret;
	}
	
	public function pwd()
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->pwd();
		}
		return $ret;
	}
	
	public function ls($remotedir)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->nlist($remotedir);
		}
		return $ret;
	}
	
	public function ls_l($remotedir)
	{
		$ret = true;
		if ($this->_ftp == null)
		{
			$ret = $this->login();
		}
		// copies $source from the SFTP server to $target locally
		if ($ret)
		{
			$ret = $this->_ftp->rawlist($remotedir);
		}
		return $ret;
	}
	
	// $sftp->mkdir('test'); // create directory 'test'
//$sftp->chdir('test'); // open directory 'test'
//echo $sftp->pwd(); // show that we're in the 'test' directory
//$sftp->chdir('..'); // go back to the parent directory
//$sftp->rmdir('test'); // delete the directory

}

class OJ_File_Utilities
{
	public static $audio_extensions = ["wav", "flac", "mp3", "mpeg3", "ape", "ogg", "alc", ".aicc"];
	
	public static $video_extensions = [".mp4", ".mkv", ".avi"];
	
	public static $image_extensions = [".jpg", ".jpeg", ".png", ".gif", ".tiff", ".bmp"];
	
	public static function get_extension($path, $include_dot = false)
	{
		$ret = null;
		$bname = basename($path);
		$lastdot = strrpos($bname, '.');
		if ($lastdot !== FALSE)
		{
			$ret = substr($bname, $lastdot + ($include_dot?0:1));
		}
		return $ret;
	}
	
	public static function has_extension($path, $ext, $case_independent = false)
	{
		$ret = false;
		if (is_string($ext))
		{
			$dotext = str_replace('..', '.', '.'.$ext);
			if ($case_independent)
			{
				$ret = OJ_Utilities::ends_with(strtolower($path), strtolower($dotext));
			}
			else
			{
				$ret = OJ_Utilities::ends_with($path, $dotext);
			}
		}
		else if (is_array($ext))
		{
			for ($n = 0; ($n < count($ext)) && !$ret; $n++)
			{
				$ret = self::has_extension($path, $ext[$n], $case_independent);
			}
		}
		return $ret;
	}
	
	private static function get_extensions($extensions)
	{
		$ret = null;
		if (is_array($extensions))
		{
			$ret = $extensions;
		}
		else if (is_string($extensions))
		{
			$ext = $extensions."_extensions";
			$ret = self::$$ext;
		}
		return $ret;
	}
	
	public static function is_file_of_type($path, $extensions)
	{
		return self::has_extension($path, self::get_extensions($extensions), true);
	}
	
	public static function is_audio_file($path)
	{
		return self::is_file_of_type($path, self::$audio_extensions);
	}
	
	public static function is_video_file($path)
	{
		return self::is_file_of_type($path, self::$video_extensions);
	}
	
	public static function is_image_file($path)
	{
		return self::is_file_of_type($path, self::$image_extensions);
	}
	
	/**
	 * returns directory name ending in separator
	 * @param type $dirpath
	 */
	public static function check_dirname($dirpath)
	{
		$dpath = realpath($dirpath);
		return OJ_Utilities::ends_with($dpath, DIRECTORY_SEPARATOR)?$dpath:($dpath.DIRECTORY_SEPARATOR);
	}
	
	// $restricted by of form [label=>[extensions]...]
	// an empty extensions array means accept everything
	public static function dir_info($dirpath, $recursive = false, $restricted_by = null)
	{
		if ($restricted_by === null)
		{
			$restricted_by = ["files"=>[]];
		}
		$dpath = self::check_dirname($dirpath);
		$files = array_diff(scandir($dpath), array('.', '..'));
		$ret = ["dir"=>$dpath, "subdirs"=>[]];
		foreach ($restricted_by as $label => $exts)
		{
			$ret[$label] = [];
		}
		foreach ($files as $f)
		{
			$file = $dpath.$f;
			if (is_dir($file))
			{
				if ($recursive)
				{
					array_push($ret["subdirs"], self::dir_info($file, $recursive, $restricted_by));
				}
				else
				{
					array_push($ret["subdirs"], $file.DIRECTORY_SEPARATOR);
				}
			}
			else
			{
				foreach ($restricted_by as $label => $extensions)
				{
					if ((count($extensions) == 0) || self::is_file_of_type($path, $extensions))
					{
						array_push($ret[$label], $file);
					}
				}
				
			}
		}
		return $ret;
	}
	
	public static function contains_file_of_type($dirpath, $extensions1, $recurse = true)
	{
		$extensions = self::get_extensions($extensions1);
		$dirinfo = self::dir_info($dirpath);
		$ret = false;
		for ($n = 0; ($n < count($dirinfo["files"])) && !$ret; $n++)
		{
			$path = $dirinfo["files"][$n];
			$ret = self::is_file_of_type($path, $extensions);
		}
		if (!$ret && $recurse)
		{
			for ($n = 0; ($n < count($dirinfo["subdirs"])) && !$ret; $n++)
			{
				$path = $dirinfo["subdirs"][$n];
				$ret = self::contains_file_of_type($path, $extensions, $recurse);
			}
		}
		return $ret;
	}
	
	public static function contains_audio_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$audio_extensions, $recurse);
	}
	
	public static function contains_video_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$video_extensions, $recurse);
	}
	
	public static function contains_image_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$image_extensions, $recurse);
	}
	
	public static function contains_file($dir, $filename)
	{
		return file_exists(self::check_dirname($dir).$filename);
	}
	
	public static function already_imported($dir, $extensions)
	{
		$ret = self::contains_file($dir, ".oj");
		if (!$ret)
		{
			$info = self::dir_info($dir);
			if (count($info["subdirs"]) > 0)
			{
				$ret1 = true;
				$cnt = 0;
				for ($n = 0; ($n < count($info["subdirs"])) && $ret1; $n++)
				{
					$dirpath = $info["subdirs"][$n];
					if (self::contains_file_of_type($dirpath, $extensions, true))
					{
						$cnt++;
						$ret1 = self::already_imported($dirpath, $extensions);
						print "check ".$dirpath." ".$ret1."\n";
					}
	//				print "check ".$dirpath." ".$ret."\n";
				}
				$ret = ($cnt > 0) && $ret1;
			}
		}
		return $ret;
	}
	
	public static function to_logical_path($path, $logicals)
	{
		$ret = $path;
		foreach ($logicals as $lname => $logical)
		{
			if (strpos($path, $logical->value) === 0)
			{
				$ret = '${'.$lname.'}'.substr($path, strlen($logical->value) + 1);
				break;
			}
			elseif (strpos($path, $logical->alternative) === 0)
			{
				$ret = '${'.$lname.'}'.substr($path, strlen($logical->alternative) + 1);
				break;
			}
		}
		return $ret;
	}
	
	public static function find_in_file($filename, $needle, $case_sensitive = true)
	{
		return OJ_Utilities::find_in_array(file($filename, FILE_IGNORE_NEW_LINES), $needle, $case_sensitive);
	}
	
	/** 
	 * Add files and sub-directories in a folder to zip file. 
	 * @param string $folder 
	 * @param ZipArchive $zipFile 
	 * @param int $exclusiveLength Number of text to be exclusived from the file path. 
	 */ 
	private static function folder_to_zip($folder, &$zipFile, $exclusiveLength)
	{ 
		$handle = opendir($folder); 
		while (false !== $f = readdir($handle))
		{ 
			if ($f != '.' && $f != '..')
			{ 
				$filePath = "$folder/$f"; 
				// Remove prefix from file path before add to zip. 
				$localPath = substr($filePath, $exclusiveLength); 
				if (is_file($filePath))
				{ 
					$zipFile->addFile($filePath, $localPath); 
				}
				elseif (is_dir($filePath))
				{ 
					// Add sub-directory. 
					$zipFile->addEmptyDir($localPath); 
					self::folder_to_zip($filePath, $zipFile, $exclusiveLength); 
				} 
			} 
		} 
		closedir($handle); 
	} 

	/** 
	 * Zip a folder (include itself). 
	 * Usage: 
	 *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip'); 
	 * 
	 * @param string $sourcePath Path of directory to be zip. 
	 * @param string $outZipPath Path of output zip file. 
	 */ 
	public static function zip_dir($sourcePath, $outZipPath) 
	{ 
		$pathInfo = pathInfo($sourcePath); 
		$parentPath = $pathInfo['dirname']; 
		$dirName = $pathInfo['basename']; 

		$z = new ZipArchive(); 
		$z->open($outZipPath, ZIPARCHIVE::CREATE); 
		$z->addEmptyDir($dirName); 
		self::folder_to_zip($sourcePath, $z, strlen("$parentPath/")); 
		$z->close(); 
	} 
}

class OJ_Utilities
{
	private static $_system_parameters = [];
	
	public static function set_system_parameter($name, $value = true)
	{
		self::$_system_parameters[$name] = $value;
	}
	
	public static function get_system_parameter($name, $default_value = null)
	{
		return array_key_exists($name, self::$_system_parameters)?self::$_system_parameters[$name]:$default_value;
	}
	
	public static function unset_system_parameter($name)
	{
		if (array_key_exists($name, self::$_system_parameters))
		{
			unset($_system_parameters[$name]);
		}
	}
	
    public static function load_json_file($file)
    {
        $json = file_get_contents($file);
        return json_decode($json);
    }
    
    public static function var_dump_pre($mixed = null) {
        echo '<pre>';
        var_dump($mixed);
        echo '</pre>';
        return null;
    }
    
    public static function starts_with($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    
    public static function ends_with($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
	
	public static function has_string_keys($array) {
		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}
	
	public static function multiexplode ($delimiters, $string)
	{
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);
		return  $launch;
	}
	
	public static function starts_with_number($str)
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
		$nlen = strlen($num);
		$swn = $nlen !== 0;
		if ((($numbase == 16) && ($nlen > 2)) || (($numbase == 10) && ($nlen > 3)))
		{
			$base = $num.$gap.$base;
			$num = "";
			$gap = "";
			$swn = false;
		}
		$num = $swn?intval($num, $numbase):0;
		$ret = ["starts_with_number"=>$swn, "number"=>$num, "gap"=>$gap, "rest"=>$base];
		return $ret;
	}
	
	public static function current_time_millis()
	{
		return round(microtime(true)*1000);
	}
	
	public static function compare_comparison_values($cval1, $cval2)
	{
		$ret = 0;
		$ct = $cval1["type"];
		if (($ct == $cval2["type"]) && ($ct < 2))
		{
			$cv1 = $cval1["value"];
			$cv2 = $cval2["value"];
			if ($ct == 0)
			{
				$ret = $cv1 < $cv2?-1:($cv1 > $cv2?1:0);
			}
			else
			{
				$ret = strcasecmp($cv1, $cv2);
			}
		}
		return $ret;
	}
    
    public static function get_where_clause($hash, $connective='AND')
    {
		if (is_string($hash))
		{
			$ret = $hash;
		}
		else
		{
			$ret = " ";
			$first1 = true;
			$pc = "";
			foreach ($hash as $key => $val)
			{
				$eq = "=";
				if (OJ_Utilities::ends_with($key, "<>"))
				{
					$key = substr($key, 0, -2);
					$eq = "<>";
				}
				elseif (OJ_Utilities::ends_with($key, ">"))
				{
					$key = substr($key, 0, -1);
					$eq = ">";
				}
				elseif (OJ_Utilities::ends_with($key, "<"))
				{
					$key = substr($key, 0, -1);
					$eq = "<";
				}
				elseif (OJ_Utilities::ends_with($key, ">="))
				{
					$key = substr($key, 0, -2);
					$eq = ">=";
				}
				elseif (OJ_Utilities::ends_with($key, "<="))
				{
					$key = substr($key, 0, -2);
					$eq = "<=";
				}
				elseif (OJ_Utilities::ends_with($key, "%~%"))
				{
					$key = substr($key, 0, -3);
					$eq = " LIKE ";
					$pc = "LR";
				}
				elseif (OJ_Utilities::ends_with($key, "%~"))
				{
					$key = substr($key, 0, -2);
					$eq = " LIKE ";
					$pc = "L";
				}
				elseif (OJ_Utilities::ends_with($key, "~%"))
				{
					$key = substr($key, 0, -2);
					$eq = " LIKE ";
					$pc = "R";
				}
				elseif (OJ_Utilities::ends_with($key, "~"))
				{
					$key = substr($key, 0, -2);
					$eq = " LIKE ";
					$pc = "";
				}
				if (!$first1)
				{
					$ret .= " ".$connective." ";
				}
				else
				{
					$first1 = false;
				}
				if ($val === null)
				{
					$ret .= " ".$key." IS NULL";
				}
				elseif (is_array($val) && (count($val) > 0))
				{
					$ret1 = "";
					$first2 = true;
					foreach ($val as $v)
					{
						if (!$first2)
						{
							$ret1 .= " OR ";
						}
						else
						{
							$first2 = false;
						}
						if (($key == 'id') || self::ends_with($key, '_id') || is_numeric($v))
						{
							$ret1 .= " ".$key.$eq.$v;
						}
						else
						{
							switch ($pc)
							{
								case "LR":
									$v = '%'.$v.'%';
									break;
								case "L":
									$v = '%'.$v;
									break;
								case "R":
									$v = $v.'%';
									break;
								default:
									break;
							}
							$ret1.= ' UPPER('.$key.")".$eq."UPPER('".$v."')";
						}
					}
					$ret .= " (".$ret1.") ";
				}
				else
				{
					if (($key == 'id') || self::ends_with($key, '_id') || is_numeric($val))
					{
						$ret .= " ".$key.$eq.$val;
					}
					else
					{
						switch ($pc)
						{
							case "LR":
								$val = '%'.$val.'%';
								break;
							case "L":
								$val = '%'.$val;
								break;
							case "R":
								$val = $val.'%';
								break;
							default:
								break;
						}
						$ret.= ' UPPER('.$key.")".$eq."UPPER('".$val."')";
					}
				}
			}
		}
        return $ret;
    }

    public static function get_input_name_from_column_name($table_name, $column_name)
    {
        return "oj-".str_replace('_', '-', $table_name.'-'.$column_name);
    }
    
    public static function get_hash_values_as_array($hash)
    {
        $ret = array();
        foreach ($hash as $k => $v)
        {
            $ret[] = $v;
        }
        return $ret;
    }
    
    public static function get_array_values_as_hash($array, $fname)
    {
        $ret = array();
        foreach ($array as $obj)
        {
            $key = $obj->$fname;
            $ret[$key] = $obj;
        }
        return $ret;
    }
    
    public static function hash_join($hash1, $hash2)
    {
        $ret = array();
        foreach ($hash1 as $k => $v1)
        {
            if (array_key_exists($k, $hash2))
            {
                $ret[$k] = array($v1, $hash2[$k]);
            }
        }
        return $ret;
    }
    
    public static function array_object_join($array1, $array2, $fname1 = 'id', $fname2 = null)
    {
        $ret = array();
        if ($fname2 == null)
        {
            $fname2 = $fname1;
        }
        $hash1 = self::get_array_values_as_hash($array1, $fname1);
        $hash2 = self::get_array_values_as_hash($array2, $fname2);
        return self::hash_join($hash1, $hash2);
    }
    
    /**
     * hashes are of the same type using the same key as field
     */
    public static function hash_intersection($hash1, $hash2)
    {
        $ret = array();
        foreach ($hash1 as $k => $v1)
        {
            if (array_key_exists($k, $hash2))
            {
                $ret[$k] = $v1;
            }
        }
        return $ret;
    }
    
    /**
     * arrays are of the same type
     */
    public static function array_object_intersection($array1, $array2, $fname1 = 'id', $fname2 = null)
    {
        $ret = array();
        if ($fname2 == null)
        {
            $fname2 = $fname1;
        }
        $hash1 = self::get_array_values_as_hash($array1, $fname1);
        $hash2 = self::get_array_values_as_hash($array2, $fname2);
        $hash = self::hash_intersection($hash1, $hash2);
        return self::get_hash_values_as_array($hash);
    }
    
    public static function mangle_name_for_css($name)
    {
        $ret1 = str_replace(' - ', '-', strtolower($name));
        $ret2 = str_replace(' ', '-', $ret1);
        $ret3 = str_replace('_', '-', $ret2);
        $ret4 = str_replace('--', '-', $ret3);
		return $ret4;
//		return preg_replace('/[^a-z0-9]+/i', '-', $ret3);
    }
    
    public static function decode_array(&$array)
    {
        if (isset($array['usebase64']))
        {
            $keys_to_decode = explode(',', $array['usebase64']);
            foreach ($keys_to_decode as $key)
            {
                $array[$key] = base64_decode($array[$key]);
            }
        }
    }
	
	public static function find_in_array($haystack, $needle, $case_sensitive = true)
	{
		$ret = -1;
		$ndl = $case_sensitive?$needle:strtolower($needle);
		$ln = 0;
		foreach ($haystack as $line)
		{
			$l = $case_sensitive?$line:strtolower($line);
			$n = strpos($l, $ndl);
			if ($n !== FALSE)
			{
				$ret = $ln;
				break;
			}
			$ln++;
		}
		return $ret;
	}
	
	public static function get_cmp_using_array($lines, $case_sensitive = true)
	{
		return function($cmpa, $cmpb) use ($lines, $case_sensitive)
		{
			$ca = OJ_Utilities::find_in_array($lines, $cmpa, $case_sensitive);
			$cb = OJ_Utilities::find_in_array($lines, $cmpb, $case_sensitive);
			return ($ca > $cb?-1:($ca == $cb?0:1));
		};
	}
	


   /**
    * Insert the method's description here. Creation date: (07/02/2000 12:46:41)
    * 
    * @return java.lang.String
    * @param st
    *           java.lang.String
    */
   public static function decrypt($st)
   {
      $ret = $st;
      if (($st != null) && (strlen($st) > 0))
      {
         try
         {
            if (substr($st, 0, 4) === "CHK1")
            {
               $lens = [];
               try
               {
                  $start = 4;
                  $end = 7;
                  $len = substr($st, $start, $start - $end);
                  while ($len != 999)
                  {
                     $lens[] = $len;
                     $start += 3;
                     $end += 3;
                     $len = substr($st, $start, $start - $end);
                  }
                  $st = substr($st, end);
               }
               catch (Exception $ignored)
               {
               }
//               Integer[] lens = v.toArray(new Integer[v.size()]);
               $ret = "";
               $start = 0;
               for ($n = 0; $n < count($lens); $n++)
               {
                  $ret .= decrypt($st, $start, $lens[$n]);
                  $start += $lens[$n];
               }
//               ret = buff.toString();
            }
            else
            {
				$b = unpack('C*', $st);
//				var_dump($b);
				$al1 = unpack('C*', "a");
				$au1 = unpack('C*', "A");
				$al = $al1[1];
				$au = $au1[1];
				$len = count($b);
				$newLen = $len / 3;
				$out = [];
				$out1 = [];
				for ($n = 1; $n <= $len; $n += 3)
				{
					$out1[$b[$n] - $au + 1] = $b[$n + 2] + 2 - $b[$n + 1] + $al;
				}
				for ($n = 1; $n <= count($out1); $n++)
				{
					$out[] = $out1[$n];
				}
//				var_dump($out);
				$ret = call_user_func_array("pack", array_merge(array("C*"), $out));
            }
         }
         catch (Exception $e)
         {
            // err.println("decryption failure for " + st);
         }
      }
      return $ret;
   }

   /**
    * Insert the method's description here. Creation date: (07/02/2000 12:46:41)
    * 
    * @return java.lang.String
    * @param st
    *           java.lang.String
    */
   public static function encrypt($st)
   {
      $ret = $st;
      if (($st != null) && (strlen($st) > 0))
      {
         if (strlen($st) > 32)
         {
//            StringBuilder buff = new StringBuilder("CHK1");
			 $ret = "";
             $lens = [];
            while (strlen($st) > 32)
            {
               $est = encrypt(substr($st, 0, 32));
               $elen = strlen($est);
               if ($elen < 10)
               {
                  $ret .= "00";
               }
               else if (elen < 100)
               {
                  $ret .= '0';
               }
               $ret .= $elen;
               $lens[] = $est;
               $st = substr($st, 32);
            }
            if (strlen($st) > 0)
            {
               $est = encrypt($st);
               $elen = strlen($est);
               if ($elen < 10)
               {
                  $ret .= "00";
               }
               else if (elen < 100)
               {
                  $ret .= '0';
               }
               $ret .= $elen;
               $lens[] = $est;;
            }
            $ret .= "999";
            for ($n = 0; $n < count($lens); $n++)
            {
               $ret .= $lens[$n];
            }
//            ret = buff.toString();
         }
         else
         {
            $b = unpack('C*', $st);
//			var_dump($b);
//            long seed = (new java.util.Date()).getTime();
//            $gen = new java.util.Random(seed);
            $len =count($b);
            $addons = [];
            $positions = [];
			$al1 = unpack('C*', "a");
			$au1 = unpack('C*', "A");
			$al = $al1[1];
			$au = $au1[1];
            for ($n = 0; $n < $len; $n++)
            {
               $addons[$n] = rand(0, 3);
               $positions[$n] = $n;
            }
            $ntimes = rand(0, 99);
            for ($n = 0; $n < $ntimes; $n++)
            {
               $n1 = rand(0, $len - 1);
               $n2 = rand(0, $len - 1);
               $tmp = $positions[$n1];
               $positions[$n1] = $positions[$n2];
               $positions[$n2] = $tmp;
            }
            $out = [];
            for ($n = 0; $n < $len; $n++)
            {
               $out[$n * 3 + 1] = $au + $positions[$n];
               $out[$n * 3 + 2] = $al + $addons[$n];
               $out[$n * 3 + 3] = $b[$positions[$n] + 1] + $addons[$n] - 2;
            }
//			var_dump($out);
//            $ret = pack('C*', $out);
			$ret = call_user_func_array("pack", array_merge(array("C*"), $out));
         }
      }
      return $ret;
   }

	
}

class OJ_Timestamp_Utilities
{
    const MINUTE1 = 60;
    const HOUR1 = 3600;
    const DAY1 = 86400;
    const WEEK1 = 604800;
    
    public static function seconds($tm)
    {
        return $tm % self::MINUTE1;
    }
    
    public static function minutes($tm)
    {
        $min = (int) ($tm / self::MINUTE1);
        return $min % 60;
    }
    
    public static function hours($tm)
    {
        return date("H", $tm);
    }
    
    public static function day_of_week($tm)
    {
        return date('w', $tm);
    }
    
    public static function day_of_month($tm)
    {
        return date('j', $tm);
    }
    
    public static function day_of_month_from_0($tm)
    {
        return self::day_of_month($tm) - 1;
    }
    
    public static function day_of_year($tm)
    {
        return date('z', $tm);
    }
    
    public static function week_of_month($tm)
    {
        return (int) (self::day_of_month_from_0($tm) / 7);
    }
    
    public static function week_of_year($tm)
    {
        return (int) (self::day_of_year($tm) / 7);
    }
    
    public static function month($tm)
    {
        return date('z', $tm);
    }
    
    public static function month_from_0($tm)
    {
        return self::month($tm) - 1;
    }
    
    public static function year($tm)
    {
        return date('Y', $tm);
    }
    
    public static function start_of_day($tm)
    {
//        $str = date("d/m/Y", $tm)
//        echo "hours ".self::hours($tm)," mins ".self::minutes($tm)." secs ".self::seconds($tm)."<br/>";
        return $tm - self::seconds($tm) - (self::minutes($tm) * self::MINUTE1) - (self::hours($tm) * self::HOUR1);
    }
    
    public static function end_of_day($tm)
    {
        return self::start_of_day($tm + self::DAY1) - 1;
    }
    
    public static function start_of_week($tm)
    {
        return self::start_of_day($tm) - ((self::day_of_week($tm) - 1) * self::DAY1);
    }
    
    public static function end_of_week($tm)
    {
        return self::start_of_week($tm) + (7 * self::DAY1) - 1;
    }
    
    public static function start_of_month($tm)
    {
        return self::start_of_day($tm) - (self::day_of_month_from_0($tm) * self::DAY1);
    }
    
    public static function number_of_days_in_month($tm)
    {
        return date('t', $tm);
    }
    
    public static function end_of_month($tm)
    {
        return self::start_of_month($tm) + (self::number_of_days_in_month($tm) * self::DAY1) - 1;
    }
    
    public static function same_day($tm1, $tm2)
    {
        return self::year($tm1) == self::year($tm2) && self::day_of_year($tm1) == self::day_of_year($tm2);
    }
    
    public static function same_time($tm1, $tm2, $include_seconds = false)
    {
        return self::hours($tm1) == self::hours($tm2) && self::minutes($tm1) == self::minutes($tm2) &&
            (!include_seconds || (self::seconds($tm1) == self::seconds($tm2)));
    }
    
    public static function before_time($tm1, $tm2, $include_seconds = false)
    {
        return self::hours($tm1) < self::hours($tm2) ||
         (self::hours($tm1) == self::hours($tm2) && (self::minutes($tm1) < self::minutes($tm2)) ||
         (include_seconds && self::minutes($tm1) == self::minutes($tm2) && self::seconds($tm1) < self::seconds($tm2)));
    }
    
    public static function after_time($tm1, $tm2, $include_seconds = false)
    {
        return self::before_time($tm2, $tm1, $include_seconds);
    }
    
    public static function sql_datetime($tm)
    {
        $ts = is_numeric($tm)?$tm:strtotime($tm);
        return date('Y-m-d H:i:s', $ts);
    }
    
    public static function sql_date($tm)
    {
        return date('Y-m-d', $tm);
    }
    
    public static function sql_time($tm)
    {
        return date('H:i:s', $tm);
    }
}
