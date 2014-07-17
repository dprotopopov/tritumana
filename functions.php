<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

function unparse_url($parsed_url, $defaults) { 
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : isset($defaults['scheme']) ? $defaults['scheme'] . '://' : ''; 
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : isset($defaults['host']) ? $defaults['host'] : '';
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : isset($defaults['port']) ? ':' . $defaults['port'] : ''; 
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : isset($defaults['user']) ? $defaults['user'] : ''; 
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : isset($defaults['pass']) ?':' . $defaults['pass'] : ''; 
  $pass     = ($user || $pass) ? "$pass@" : ''; 
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
  return "$scheme$user$pass$host$port$path$query$fragment"; 
}

function safe($value){ 
   return addslashes($value); 
} 

function unsafe($value){ 
   return $value; 
} 

// Recursively convert an object to an array
function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;
	if(is_array($obj)) { $new = array(); foreach($obj as $key => $val) $new[$key] = object_to_array($val); return $new; }
	return $obj;
}