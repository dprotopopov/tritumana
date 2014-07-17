<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

require_once( dirname(__FILE__) . '/configuration.php' );

class JDatabase {
	private $config;
	var $last_query;
	var $result;
	var $mysqli;
	var $num_queries=0;
	public function __construct() {
		$this->config = new JConfig();
	}
	function connect()
	{
		if($this->config->persistent)
		{
			// http://habrahabr.ru/post/129482/
			$this->mysqli=new mysqli('p:' . $this->config->host, $this->config->user, $this->config->password) or $this->connection_error();
		}
		else
		{
			$this->mysqli=new mysqli($this->config->host, $this->config->user, $this->config->password) or $this->connection_error();
		}
		$this->mysqli->select_db($this->config->db);
		$this->mysqli->set_charset('utf8');
		return $this->mysqli;
	}
	function disconnect()
	{
		if($this->mysqli) { $this->mysqli->close(); $this->mysqli=0; return 1; }
		else { return 0; }
	}

	// The query must consist of a single SQL statement.
   function execute($query,$params=array()){
	 	if($this->config->debug) echo "<pre>$query</pre>";
		$res    = $this->mysqli->prepare($query); 
		$code = array();
		$index=0; foreach($params as $param) { 
			$code[] = '$var'. $index .'=$params[' . $index . ']?$params[' . $index . ']:"";';
			$index++; 
		}
		$code[] = '$res->bind_param("' . str_repeat('s',$index) . '",&$var' . implode(',&$var',range(0,$index-1)) . ');';
	 	if($this->config->debug) echo implode(PHP_EOL,$code);
		if($index) eval(implode(PHP_EOL,$code));
		$res->execute(); 	
		$res->close(); 	
   }
   
   function query($query)
   {
	 if($this->config->debug) echo "<pre>$query</pre>";
     $this->last_query=$query;
     $this->num_queries++;
     $this->result=$this->mysqli->query($this->last_query) or $this->query_error();
     return $this->result;
   }
   
   function multi_query($query)
   {
	 if($this->config->debug) echo "<pre>$query</pre>";
     $this->last_query=$query;
     $this->num_queries++;
     $this->result=$this->mysqli->multi_query($this->last_query) or $this->query_error();
     return $this->result;
   }
   // Returns an associative array of strings representing the fetched row in the result set, where each key in the array represents the name of one of the result set's columns or NULL if there are no more rows in resultset.
   function fetch_row($result=0)
   {
     if(!$result) { $result=$this->result; }
     return $result->fetch_assoc();
   }
   // Returns the number of rows in the result set.
   function num_rows($result=0)
   {
     if(!$result) { $result=$this->result; }
     return $result->num_rows();
   }
   // Frees the memory associated with the result.
   //Note
   //You should always free your result with mysqli_free_result, when your result object is not needed anymore.
   function free_result($result=0)
   {
     if(!$result) $result=$this->result;
     if($result) $result->close();
   }
   function free_multi_result($result=0)
   {
     if(!$result) { $result=$this->result; }
	 if($result) do
		if ($result = $this->mysqli->use_result()) 
		  $result->close();
	 while ($this->mysqli->next_result());
   }
   function connection_error()
   {
     die("<b>FATAL ERROR:</b> Could not connect to database on {$this->config->host} ($this->mysqli->connect_error)");
   }
   function query_error()
   {
     die("<b>QUERY ERROR:</b> ".$this->mysqli->error."<br />
     Query was {$this->last_query}");
   }
   function fetch_single($result=0)
   {
	 // http://stackoverflow.com/questions/1684993/what-does-mysqli-num-mean-and-do
	 // MYSQLI_NUM is a constant in PHP associated with a mysqli_result. If you're using mysqli to retrieve information from the database, MYSQLI_NUM can be used to specify the return format of the data. Specifically, when using the fetch_array function, MYSQLI_NUM specifies that the return array should use numeric keys for the array, instead of creating an associative array.
     if(!$result) { $result=$this->result; }
	 $value = $result->fetch_array(MYSQLI_NUM);
     return is_array($value) ? $value[0] : "";
   }
   function clean_input($in)
   {
     $in=stripslashes($in);
     return str_replace(array("<",">",'"',"'","\n"), array("&lt;","&gt;","&quot;","&#39;","<br />"), $in);
   }
   function unhtmlize($text)
   {
     return str_replace("<br />","\n", $text);
   }  
   function escape($text)
   {
     return $this->mysqli->real_escape_string($text);
   }
   function affected_rows($conn = NULL)
   {
     return $this->mysqli->affected_rows;
   }
}