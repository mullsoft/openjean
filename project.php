<?php

class Project_Details
{
    const PROJECT_NAME = "openjean";
    const PROJECT_DBNAME = "openjean";
    const PROJECT_DBUSER = "oj";
    const PROJECT_DBPASS = "B0risdog";
    const PROJECT_DBHOST = "localhost";
    const PROJECT_DBPORT = "3306";
    const PROJECT_DBPERSIST = false;
    
    private static $_db = null;
    
    public static function get_db()
    {
        if (self::$_db == null)
        {
            self::$_db = new MullSoftDatabaseObject(self::PROJECT_DBNAME, self::PROJECT_DBUSER, self::PROJECT_DBPASS, self::PROJECT_DBHOST, self::PROJECT_DBPORT, self::PROJECT_DBPERSIST);
        }
        return self::$_db;
    }
}

class OJ_Logger
{
	private static $the_logger = null;
	
	const LOG_DEBUG = 0;
	const LOG_TRACE = 1;
	const LOG_ERROR = 2;
	
	public static function get_logger($logroot = '/var/openjean/logs')
	{
		if (self::$the_logger === null)
		{
			self::$the_logger = new OJ_Logger($logroot);
		}
		return self::$the_logger;
	}
	
	private $_log_file;
	private $_log_level;
	
	public function __construct($logroot)
	{
		$this->_log_level = self::LOG_DEBUG;
		$this->_log_file = $logroot."/log_".date("j.n.Y").".log";
	}

	public function ojlog($level, ...$logentries)
	{
		if ($level >= $this->_log_level)
		{
			if ((func_num_args() == 2) && !is_array($logentries))
			{
				$logentries = [$logentries];
			}
			$text = date("F j, Y, H:i: ")."db ";
			foreach ($logentries as $logentry)
			{
				if (is_array($logentry) || is_object($logentry))
				{
//					ob_start();
//					var_dump($logentry);
//					$str = ob_get_clean();
					$str = json_encode($logentry);
//					$str = var_export($logentry, true);
					$text .= $str;
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
	
	public function ojdebug(...$msg)
	{
		$this->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror(...$msg)
	{
		$this->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace(...$msg)
	{
		$this->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
	
	public function ojdebug1($msg)
	{
		$this->ojlog(OJ_Logger::LOG_DEBUG, $msg);
	}
	
	public function ojerror1($msg)
	{
		$this->ojlog(OJ_Logger::LOG_ERROR, $msg);
	}
	
	public function ojtrace1($msg)
	{
		$this->ojlog(OJ_Logger::LOG_TRACE, $msg);
	}
	
}

?>