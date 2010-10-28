<?php

/**
 * to manage many of database connections and one default connection
 * the data for default connection is set in config.ini
 *
 * To use default connection please use this module like a normal module.
 * So you have to use the mod function.
 *
 * If you want to have a second or third connection use mod to call met_new methode.
 * The returned value is a new instance of mod_mysql. Use it like a normal instance.
 * So you don't have to use the mod funktion.
 *
 * @version 1.1.beta
 * @author	Helmut Wandl <helmut@wandls.net>
 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
 */
class mod_mysql extends mod
{
	var $resource=false;
	var $wildcards=array();
	var $hostname="";
	var $username="";
	var $password="";
	var $database="";

	var $resultresource = false;

	var $log = array();


	/**
	 * connect to database and select a table. Sets the variables resource and wildcards
	 *
	 * @param  string  $hostname       hostname of database
	 * @param  string  $username       username of database
	 * @param  string  $password       passphrase of database
	 * @param  string  $database       name of database
	 * @param  array   $wildcards      associative array with wildcards as index (use the methode set_wildcard to create dynamic wildcards)
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function mod_mysql($hostname=false, $username=false, $password=false, $database=false, $wildcards=false)
	{
		if (!$this->_open($hostname, $username, $password, $database))
			trigger_error("can't connect to database", E_USER_ERROR);

		$this->met_set_wildcard('~', $this->config('tableprefix'));
		$this->met_set_wildcard('~~', "return '~'.mod_lookback().'__';", 1);

		if ($wildcards and is_array($wildcards) and count($wildcards) > 0)
		{
			foreach($wildcards as $name => $value)
			{
				$this->met_set_wildcard($name, $value);
			}
		}
	}


	/**
	 * to create a new database connection.
	 *
	 * You don't need this methode for the default connection.
	 * So you have to use it only for the second or third connection.
	 * The return value is a new instance of this class. This new instance you can use like a normal object.
	 * So you don't have to use the mod function for all methodes of this instance.
	 *
	 * @param   string  $hostname       hostname of database
	 * @param   string  $username       username of database
	 * @param   string  $password       passphrase of database
	 * @param   string  $database       name of database
	 * @param   array   $wildcards      associative array with wildcards as index (use the methode set_wildcard to create dynamic wildcards)
	 *
	 * @return  object  instance of this class
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_new($hostname=false, $username=false, $password=false, $database=false, $wildcards=false)
	{
		return new mod_mysql($hostname, $username, $password, $database, $wildcards);
	}


	/**
	 * set a new or update an available wildcard
	 *
	 * @param  string  $wildcard_character   the wildcard character
	 * @param  string  $value                value of the wildcard or php source code as a string for dynamic wildcard
	 * @param  bool    $dynamic              optional - true for dynamic wildcard and false (default) for static wildcard
	 *
	 * @return bool    TRUE on success or FALSE on failure.
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_set_wildcard($wildcard_character, $value, $dynamic=false)
	{
		$dynamic=($dynamic ? true : false);
		$this->wildcards[$wildcard_character] = array($value, $dynamic);
		krsort($this->wildcards);
		return true;
	}


	/**
	 * delete an available wildcard
	 *
	 * @param  string  $wildcard_character   the wildcard character
	 *
	 * @return bool    TRUE on success or FALSE on failure.
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_unset_wildcard($wildcard_character)
	{
		if (isset($this->wildcards[$wildcard_character])) unset($this->wildcards[$wildcard_character]);
		krsort($this->wildcards);
		return true;
	}


	/**
	 * sends a request to mysql
	 *
	 * This function is very useful to send requests on a secure and handy way
	 * the first parameter always is the mysql query. You also can use all defined wildcards in it.
	 * all other parameters are for insecure values. You can use them on two ways:
	 *
	 * - if the second parameter is an associative array you can insert the values with "#" followed of the key name
	 * For example you can write "#birthday" into the query string to insert the value of array['birthday']
	 * In this case there must not be more than this two parameters
	 *
	 * - instead of one array you can use more than one strings. Each string have to be one parameter.
	 * You can insert the values with "#" followed by the number of the string. The first string (second parameter) have the number 1 and so on.
	 * For example you can write "#2" into the query string to insert the value of the second string (third parameter)
	 *
	 * This two ways are secure because the values become escaped
	 *
	 * @param   string    $query    mysql query whithin wildcards
	 * @param   mixed     $param    array or string with insecure values
	 * @param   string    $param    string with insecure values
	 * @param   string    $param    ...
	 *
	 * @return  mixed     the result resource or TRUE on success or FALSE on failure.
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query($query)
	{
		$arguments=func_get_args();
		$query=call_user_func_array(array(&$this, "met_parse"), $arguments);

		// logging
		$this->log[] = $query . "\n";

		return $this->resultresource = mysql_query($query, $this->resource);
	}


	/**
	 * sends a request to mysql and returns a string within the first field of the first found result
	 *
	 * It's a combination of met_query, met_fetch_rows and disrupt the returned array.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   like the returned value of met_fetch_array
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_field()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		if ($this->met_num_rows($resultresource) > 0)
		{
			list($field)=$this->met_fetch_row($resultresource);
			return $field;
		}

		return false;
	}


	/**
	 * sends a request to mysql and returns a mixed array within the first found result
	 *
	 * It's a combination of met_query and met_fetch_array.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   like the returned value of met_fetch_array
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_array()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		if ($this->met_num_rows($resultresource) > 0)
		return $this->met_fetch_array($resultresource);

		return false;
	}


	/**
	 * sends a request to mysql and returns an associative array within the first found result
	 *
	 * It's a combination of met_query and met_fetch_assoc.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   like the returned value of met_fetch_assoc
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_assoc()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		if ($this->met_num_rows($resultresource) > 0)
		return $this->met_fetch_assoc($resultresource);

		return false;
	}


	/**
	 * sends a request to mysql and returns a numeric array within the first found result
	 *
	 * It's a combination of met_query and met_fetch_row.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   like the returned value of met_fetch_row
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_row()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		if ($this->met_num_rows($resultresource) > 0)
		return $this->met_fetch_row($resultresource);

		return false;
	}


	/**
	 * sends a request to mysql and returns all found results in a multidimensional array (each entry like return value of met_fetch_array)
	 *
	 * It's a combination of met_query and repeated met_fetch_array.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   a numeric array (each entry like the returned value of met_fetch_array)
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_accumulate_array()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		$result=array();
		while($row=$this->met_fetch_array($resultresource)) $result[]=$row;

		return $result;
	}


	/**
	 * sends a request to mysql and returns all found results in a multidimensional array (each entry like return value of met_fetch_assoc)
	 *
	 * It's a combination of met_query and repeated met_fetch_assoc.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   a numeric array (each entry like the returned value of met_fetch_assoc)
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_accumulate_assoc()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		$result=array();
		while($row=$this->met_fetch_assoc($resultresource)) $result[]=$row;

		return $result;
	}


	/**
	 * sends a request to mysql and returns all found results in a multidimensional array (each entry like return value of met_fetch_row)
	 *
	 * It's a combination of met_query and repeated met_fetch_row.
	 *
	 * @param   string  $query   like the first parameter of met_query
	 * @param   mixed   $param   like the second parameter of met_query
	 * @param   string  $param   ...
	 *
	 * @return  array   a numeric array (each entry like the returned value of met_fetch_row)
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_query_accumulate_row()
	{
		$arguments=func_get_args();
		$resultresource=call_user_func_array(array(&$this, "met_query"), $arguments);

		$result=array();
		while($row=$this->met_fetch_row($resultresource)) $result[]=$row;

		return $result;
	}


	/**
	 * return the next result as a mixed array
	 * like the php function mysql_fetch_array
	 *
	 * @param   resource  $resultresource  the result resource
	 *
	 * @return  array     a mixed array with associative and numeric entries
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_fetch_array($resultresource = false)
	{
		if($resultresource === false) $resultresource = $this->resultresource;
		return mysql_fetch_array($resultresource);
	}


	/**
	 * return the next result as an associative array
	 * like the php function mysql_fetch_assoc
	 *
	 * @param   resource  $resultresource  the result resource
	 *
	 * @return  array     an array with associative entries
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_fetch_assoc($resultresource = false)
	{
		if($resultresource === false) $resultresource = $this->resultresource;
		return mysql_fetch_assoc($resultresource);
	}


	/**
	 * return the next result as a numeric array
	 * like the php function mysql_fetch_row
	 *
	 * @param   resource  $resultresource  the result resource
	 *
	 * @return  array     an array with numeric entries
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_fetch_row($resultresource = false)
	{
		if($resultresource === false) $resultresource = $this->resultresource;
		return mysql_fetch_row($resultresource);
	}


	/**
	 * like the php function mysql_affected_rows but without the parameter for connection resource
	 *
	 * @return  int  number of affected datasets
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_affected_rows()
	{
		return mysql_affected_rows($this->resource);
	}


	/**
	 * like the php function mysql_num_rows
	 *
	 * @param   resource  $resultresource  the result resource
	 *
	 * @return  int       number of result datasets
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_num_rows($resultresource = false)
	{
		if($resultresource === false) $resultresource = $this->resultresource;
		return mysql_num_rows($resultresource);
	}


	/**
	 * like the php function mysql_insert_id but without the parameter for connection resource
	 *
	 * @return  int       primary key of last insert
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_insert_id()
	{
		return mysql_insert_id($this->resource);
	}


	/**
	 * (privat) to open the database connection.
	 *
	 * @param   string  $hostname       hostname of database
	 * @param   string  $username       username of database
	 * @param   string  $password       passphrase of database
	 * @param   string  $database       name of database
	 *
	 * @return  bool    TRUE on success or FALSE on failure.
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function _open($hostname=false, $username=false, $password=false, $database=false)
	{
		$this->hostname=($hostname === false ? $this->config('hostname') : $hostname);
		$this->username=($username === false ? $this->config('username') : $username);
		$this->password=($password === false ? $this->config('password') : $password);
		$this->database=($database === false ? $this->config('database') : $database);

		if ($resource=mysql_connect($this->hostname, $this->username, $this->password, 1) and mysql_select_db($this->database, $resource))
		{
			$this->resource=$resource;
			$this->met_query("SET NAMES 'utf8'");
			return true;
		}
		else
			return false;
	}


	/**
	 * (privat) to close the database connection.
	 *
	 * @return  bool  TRUE on success or FALSE on failure.
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
# what's the use of this? It is never used from within the class itself, but
# declared as private and therefore not to be called from outside...
	function _close()
	{
		if (mysql_close($this->resource))
		{
			$this->resource=false;
			return true;
		}
		else
			return false;
	}


	/**
	 * (privat) parse a mysql query string to replace the wildcards with their values
	 *
	 * @param   string  $str  a mysql query string within wildcards
	 *
	 * @return  string  the given mysql query string within the values of the wildcards instead of the wildcards
	 *
	 * @version 1.0
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function _parse_wildcards($str)
	{
		foreach ($this->wildcards as $wildcard_character => $value)
		{
			if ($value[1])
				$str=str_replace($wildcard_character, eval($value[0]), $str);
			else
				$str=str_replace($wildcard_character, $value[0], $str);
		}

		return $str;
	}


	/**
	 * return the query log string
	 *
	 * @param bool $as_string indicates whether to make a string with breaks before returning the
	 * result
	 *
	 * @return mixed executed queries (after parsing) as an array or a string with one query per line
	 *
	 * @version 1.0
	 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_getlog($as_string = false)
	{
		return ($as_string) ? implode('<br />', $this->log) : $this->log;
	}


	/**
	 * dump the query log and die
	 *
	 * @param bool $html optional - if set, the output will have html line
	 * breaks (<br>), otherwise plain text will be used
	 *
	 * @author Vedran Šajatović <vedran.sajatovic@gmail.com>
	 */
	function met_dumplog($html = false)
	{
		$output = $this->met_getlog(true);
		if ($html) $output = nl2br($output);

		die($output);
	}


	/**
	 * replace wildwards in a string and replace #xy tokens with escaped values
	 *
	 * @param   string    $query    mysql query with wildcards
	 * @param   mixed     $param    array or string with insecure values
	 * @param   string    $param    string with insecure values
	 * @param   string    $param    ...
	 *
	 * @return  string    the parsed query string
	 *
	 * @version 1.0
	 * @author Helmut Wandl <helmut@wandls.net>
	 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
	 */
	function met_parse($query)
	{
		$args=func_get_args();
		unset($args[0]);

		if (isset($args[1]) and is_array($args[1]) and !isset($args[2])) $args=$args[1];

		$query=$this->_parse_wildcards($query);

		$args=array_reverse($args,true);
		$keys=array();
		$keys_preg_quoted=array();
		$this->_parse_values=array();

		foreach($args as $key => $value)
		{
			$keys[]=$key;
			$keys_preg_quoted[]=preg_quote($key, '/');
			$this->_parse_values[$key] = mysql_real_escape_string($value, $this->resource);
		}

		$query=preg_replace_callback('/\#('.implode('|', $keys_preg_quoted).')/', array(&$this, '_parse_callback'), $query);

		return $query;
	}

	function _parse_callback($match)
	{
		return '\''.$this->_parse_values[$match[1]].'\'';
	}


	/**
	 * To escape mixed data recursive.
	 *
	 * @param mixed $value String or multidimensional array or object which you have to escape
	 *
	 * @return mixed The escaped value
	 *
	 * @version 1.beta
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_escape_data_recursive(&$value)
	{
		if (is_array($value))
		{
			foreach ($value as $n => $v)
				$value[$n]=$this->met_escape_data_recursive($v);
		}
		elseif(is_object($value))
		{
			foreach(get_object_vars($value) as $n => $v)
				$value->$n=$this->met_escape_data_recursive($v);
		}
		else
			$value=mysql_real_escape_string($value, $this->resource);

		return $value;
	}


	/**
	 * To move the result pointer
	 *
	 * @param   int       $datasetnumber   The number of the next dataset (starting from 0)
	 * @param   resource  $resultresource  the result resource
	 *
	 * @return  bool      TRUE on success, otherwise FALSE
	 *
	 * @version 1.beta
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_data_seek($datasetnumber = 0, $resultresource = false)
	{
		if($resultresource === false) $resultresource = $this->resultresource;
		return mysql_data_seek($resultresource, $datasetnumber);
	}
}



?>
