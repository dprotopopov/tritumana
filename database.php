<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

class JDatabase {
	private $config;
	var $last_query;
	var $result;
	var $connection_id;
	var $num_queries=0;
	public function __construct() {
		$this->config = new JConfig();
	}
	function connect()
	{
		if($this->config->persistent)
		{
			 $this->connection_id=mysql_pconnect($this->config->host, $this->config->user, $this->config->password) or $this->connection_error();
		}
		else
		{
			 $this->connection_id=mysql_connect($this->config->host, $this->config->user, $this->config->password, 1) or $this->connection_error();
		}
		mysql_select_db($this->config->db, $this->connection_id);
		mysql_set_charset('utf8',$this->connection_id);
		return $this->connection_id;
	}
	function disconnect()
	{
		if($this->connection_id) { mysql_close($this->connection_id); $this->connection_id=0; return 1; }
		else { return 0; }
	}
	
   function query($query)
   {
	 if($this->config->debug) echo "<pre>$query</pre>";
     $this->last_query=$query;
     $this->num_queries++;
     $this->result=mysql_query($this->last_query, $this->connection_id) or $this->query_error();
     return $this->result;
   }
   function fetch_row($result=0)
   {
     if(!$result) { $result=$this->result; }
     return mysql_fetch_assoc($result);
   }
   function num_rows($result=0)
   {
     if(!$result) { $result=$this->result; }
     return mysql_num_rows($result);
   }
   function connection_error()
   {
     die("<b>FATAL ERROR:</b> Could not connect to database on {$this->host} (".mysql_error().")");
   }
   function query_error()
   {
     die("<b>QUERY ERROR:</b> ".mysql_error()."<br />
     Query was {$this->last_query}");
   }
   function fetch_single($result=0)
   {
     if(!$result) { $result=$this->result; }
     return mysql_result($result, 0, 0);
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
     return mysql_real_escape_string($text, $this->connection_id);
   }
   function affected_rows($conn = NULL)
   {
     return mysql_affected_rows($this->connection_id);
   }
}