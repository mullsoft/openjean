<?php 

//echo "here";exit;

define('__ROOT__', dirname(__FILE__)); 

//echo "here1 ".__ROOT__; exit;
// load the db specific handlers
//require_once( __ROOT__."/db_mysql.php" );
require_once( dirname(__FILE__)."/meekrodb.2.3.class.php" );
//DB::$user = 'root';
//DB::$password = 'daff0d1l';
//DB::$dbName = 'mullsoft';
//DB::$encoding = 'utf8';
//DB::$host = 'localhost';

class MullSoftDatabaseObject
{
	private $thedb;
	
	public function __construct($dbname, $dbuser, $dbpass, $dbhost = 'localhost', $dbport = "3306", $dbencoding = 'utf8')
	{
		$this->thedb = new MeekroDB($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbencoding);
	}
	
	public function query($sql)
	{
		return $this->thedb->query($sql);
	}

    /**
    * This global function loads the first field of the first row returned by the query.
    *
    * @param string The SQL query
    * @return The value returned in the query or null if the query failed.
    */
    public function loadResult( $sql ) {
		return $this->thedb->queryFirstField($sql);
    }
    
    /**
    * This global function return a result row as an associative array 
    *
    * @param string The SQL query
    * @param array An array for the result to be return in
    * @return <b>True</b> is the query was successful, <b>False</b> otherwise
    */
    public function loadHash( $sql, &$hash ) {
		$hash1 = $this->thedb->queryFirstRow($sql);
		$ret = false;
		if ($hash1 != null)
		{
			$ret = true;
			foreach ($hash1 as $k => $v)
			{
				$hash[$k] = $v;
			}
		}
		return $ret;
    }
    
    /**
    * Document::db_loadList()
    *
    * { Description }
    *
    * @param [type] $maxrows
    */
    public function loadList( $sql ) {
		return $this->thedb->query($sql);
    }
    
    /**
    * Document::db_loadColumn()
    *
    * { Description }
    *
    * @param [type] $maxrows
    */
    public function loadColumn( $sql ) {
		return $this->thedb->queryFirstColumn($sql);
    }
    
    
    /**
    * Document::db_insertArray()
    *
    * { Description }
    *
    * @param [type] $verbose
    */
    public function insertArray( $table, &$hash ) {
		$this->thedb->insert($table, $hash);
		return $this->thedb->insertId();
    }
    
    /**
    * Document::db_updateArray()
    *
    * { Description }
    *
    * @param [type] $verbose
    */
    public function updateArray( $table, &$hash, $keyName ) {
		$hash1 = array();
		$where = "";
		foreach ($hash as $k => $v) {
			if ($k == $keyName)
			{
				if (is_numeric($v))
				{
					$where = "$keyName=".$v;
				}
				else
				{
					$where = "$keyName='".$v."'";
				}
			}
			else
			{
				$hash1[$k] = $v;
			}
		}
		if (strlen($where) > 0)
		{
			$this->thedb->update($table, $hash1, $where);
		}
		
    }

    public function delete( $table, $where )
	{
		if (strlen($where) > 0)
		{
			$this->thedb->delete($table, $where);
		}
		return $this->thedb->affectedRows() > 0;
    }

}

?>
