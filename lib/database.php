<?php
/**
   * EDB Class -- Easy Database Class
   * @version 0.2
   * @author Eduards Marhelis <eduards.marhelis@gmail.com>
   * @link https://github.com/eddsstudio/edb
   * @copyright Copyright 2010 Eduards Marhelis
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package EDB Class
   */
class database{
	private	$connection		=	false;
	public	$debug			=	false; //debuging all
	public	$res			=	0; //last result data
	public	$line			=	0; //last line data
	public	$one			=	0; //last one data
	public	$queryAll		= 	array();
	public	$queryCount		= 	0; //tatal query count
	public	$queryTime		= 	0; //total query time
	public	$cacheDir		=	'./dbcache/';
	public	$utf8Cache		=	false; //use only when you have 
	private static $instance;
	protected $config;
	private static $database_config;
	private static $default_database;
	
	/**
	   * @function 			__Construct 
	   * @description 		Connects to database when created new database(); object.
	   * @param string 		$host 	Database Host.
	   * @param string 		$user 	Database user.
	   * @param string 		$pass 	Database pass.
	   * @param string 		$db 	Database name.
	   * @return 			nothing.
	   */
	public function __construct($config = false,$connect = false){
		if($config || $connect){
			$this->connect($config);
		}
	}
	
	/**
	   * @function 			connect 
	   * @description 		Connects to database 
	   * @param string 		$host 	Database Host.
	   * @param string 		$user 	Database user.
	   * @param string 		$pass 	Database pass.
	   * @param string 		$db 	Database name.
	   * @return 			nothing.
	   */
	function connect($config = false){
		
		if(!$config){
			
			$config = self::config();
		}
		
		$this->config = $config;
		$this->connection = new mysqli($this->config['HOST'],$this->config['USER'],$this->config['PASS'],$this->config['DBNAME']);
		$this->connection->set_charset("utf8");
	}
	
	public static function getInstance() {
		if(!self::$instance){
			$config = self::config();
			self::$instance = new self($config);
		}
		return self::$instance;
	}
	
	public static function getCustomInstance($databaseName = false){
	
	}
	
	
	/** get database configuration from config.php */	
	public static function config($config_name = false){
		if(!self::$database_config){
			include(dirname(__FILE__).'/config.php');
			self::$database_config = $DATABASE_CONFIG;
			self::$default_database = $DEFAULT_DATABASE;
		}
		if(!$config_name){
			return self::$database_config[self::$default_database];
		}
	}
	
	
	/**
	   * @function 			q  (shortening for query) 
	   * @description 		runs mysql query and returns php array.
	   * @param string 		$a 	Mysql Code.
	   * @return 			array();
	   */
	public function q($a,$c=0,$t=30){
		$cacheFile = $this->cacheDir . md5($a) .'.cache';
		if($c && is_file($cacheFile) && (time()-filemtime($cacheFile))<$t){
			$this->res = $this->getCache($cacheFile,$a);
		}else{
			$start	=	microtime(1);
			$this->res = array();
			$q = mysql_query("$a", $this->connection) or die(mysql_error());
			while($row = mysql_fetch_array($q)){
				$this->res[] = $row;
			}
			$end = microtime(1);
			if($c) { $this->setCache($cacheFile,$this->res,$a); }
			$this->debugData($start,$end,$a);
		}
		return $this->res;
	}
	/**
	   * @function 			getRow   
	   * @description 		runs mysql query and returns php array with row from db.
	   * @param string 		$a 	Mysql Code.
	   * @return 			array();
	   */
	public function getRow($a,$c=0,$t=30){
		$cacheFile = $this->cacheDir . md5($a) .'.cache';
		if($c && is_file($cacheFile) && (time()-filemtime($cacheFile))<$t){
			$this->line = $this->getCache($cacheFile,$a);
		}else{
			$start	=	microtime(1);
			
			$query = $this->connection->query($a);
			$this->line = $query->fetch_assoc();			
			$end	=	microtime(1);
			if($c) { $this->setCache($cacheFile,$this->line,$a); }
			$this->debugData($start,$end,$a);
			
		}
		return $this->line;
	}
	
	public function getRows($a,$c=0,$t=30){
		
		$cacheFile = $this->cacheDir . md5($a) .'.cache';
		if($c && is_file($cacheFile) && (time()-filemtime($cacheFile))<$t){
			$this->line = $this->getCache($cacheFile,$a);
		}else{
			$start	=	microtime(1);
			
			$return = $this->connection->query($a);
    	
	    	$data = array();
	    	
	    	while ($row = $return->fetch_assoc()) {
			    $data[] = $row;
			}
			
			$end	=	microtime(1);
			if($c) { $this->setCache($cacheFile,$data,$a); }
			$this->debugData($start,$end,$a);
			
		}
		return $data;
	}
	
	
	/**
	   * @function 			getOne   
	   * @description 		runs mysql query and returns php string db.
	   * @param string 		$a 	Mysql Code.
	   * @return 			string.
	   */
	public function getOne($a,$c=0,$t=30){
		$cacheFile = $this->cacheDir . md5($a) .'.cache';
		if($c && is_file($cacheFile) && (time()-filemtime($cacheFile))<$t){
			$this->one = $this->getCache($cacheFile,$a,false);
		}else{
			$start	=	microtime(1);
			$query = mysql_query("$a", $this->connection);
			$r = mysql_fetch_array( $query );
			$end	=	microtime(1);
			$this->debugData($start,$end,$a);
			$i=0; if(isset($b)) {$i=$b;}
			$this->one = $r[$i];
			if($c) { $this->setCache($cacheFile,$this->one,$a,false); }
		}
		return $this->one;
	}
	/**
	   * @function 			s = send data
	   * @description 		runs mysql query and returns result from mysql query. used for inserts and updates. 
	   * @param string 		$a 	Mysql Code.
	   * @return 			string.
	   */
	public function s($a){
		$start	=	microtime(1);
		$q = $this->connection->query($a); 
		$end	=	microtime(1);
		$this->debugData($start,$end,$a);
		return $q;
	}
	
	private function setCache($file,$result,$q,$o=true){
		$fh = fopen($file, 'w') or die("can't open file");
		if($o) { fwrite($fh, json_encode($result)); }
		else{ fwrite($fh, $result); }
		fclose($fh);
	}

	private function getCache($file,$a,$o=true){
		$start	=	microtime(1);
		$fh = fopen($file, 'r');
		$data = fread($fh, filesize($file));
		fclose($fh);
		if($o) { $data = (array)json_decode($data); }
		$end	=	microtime(1);
		$this->debugData($start,$end,$a,'cache');
		return $data;
	}
	   
	private function debugData($start,$end,$a,$b='DB'){
		$this->queryCount++;
		$t = number_format($end - $start, 8);
		$this->queryTime = $this->queryTime + $t;
		$this->queryAll[ $this->queryCount ] = array('query'=>$a,'time'=>$t,'type'=>$b);
	}
	
	//select * from table
	public function selectAll($a,$c=0,$t=30){
		$query = "SELECT * FROM `$a`";
		return $this->q($query,$c,$t);
	}
	
	//$db->select('database.table',['collon1','collon2'],['status'=>'yes'],50);
	public function select($table = false,$collon = array(),$where = array(),$limit = false,$limit2 = false){
		
		$q = 'SELECT ';
		$colls = '';
		$wheres = ' WHERE 1';
		
		if(($limit) && ($limit2)){
			$limit = ' LIMIT '.$limit.', '.$limit2;
		}elseif(($limit) && ((!$limit2))){
			$limit = ' LIMIT '.$limit;
		}else{
			$limit = '';
		}
		
		if($table){
			
			if(is_array($collon)){
				foreach($collon as $col){
					$colls.= ' `'.$col.'`,';
				}
			}
			
			$colls = substr($colls,0,-1);
			
			if(is_array($where)){
				foreach($where as $k=>$v){
					$wheres.= " AND `".$k."` = '$v'  ";
				}
			}
		}
		
		$query = $q.$colls.' FROM '.$table.$wheres.$limit;
		
		return $this->getRows($query);
		
	}
	
	//insert data $db->insert($table,$data);
	public function insert($a,$b){
		$q = "INSERT INTO $a (";
		foreach($b as $c=>$d){
			$q .= "`$c`,";
		}
		$q = substr($q,0,-1);
		$q .= ") values (";
		foreach($b as $c=>$d){
			$q .= "'$d',";
		}
		$q = substr($q,0,-1);
		return $this->s($q.');');
	}
	
	
	//update row or rows, $db->update($tableName,$updateValues,$whereValues);
	public function update($a,$b,$c){
		$q = "UPDATE $a SET ";
		foreach($b as $v=>$k){
			$q .= "`$v`='$k',";
		}
		$q = substr($q,0,-1);
		$q .= " WHERE 1";
		foreach($c as $v=>$k){
			$q .= " AND `$v`='$k'";
		}
		return $this->s($q);
	}
	

	public function countTable($a,$c=0,$t=30){
		$q = "SELECT COUNT(*) FROM `$a` LIMIT 1";
		return $this->getOne($q,$c,$t);
	}
	
	function countWhere($a,$b,$c=0,$t=30){
		$q = "SELECT COUNT(*) FROM `$a` WHERE $b LIMIT 1";
		return $this->getOne($q,$c,$t);
	}
	//get last inserted ID	
	public function lastID()
    {
      return $this->connection->insert_id;
    }
    
    static function timestamp(){
	    return date('Y-m-d H:i:s');
    }
    
	/**
	   * @function 			__destruct   
	   * @description 		closes mysql connection.
	   */
	public function __destruct(){
		$this->connection->close();
	}

}

?>