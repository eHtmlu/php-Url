<?php

/**
 * http methods to detect reqested modmet-string or get request variables for example
 *
 * @version 1.0
 * @author  Helmut Wandl <helmut@wandls.net>
 *
 */
class mod_http extends mod
{
	var $vars;


	/**
	 * (constructor) set configuration modifications and get variables
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function mod_http()
	{
		if (isset($_SERVER['REDIRECT_QUERY_STRING']) && !count($_GET))
			parse_str($_SERVER['REDIRECT_QUERY_STRING'], $_GET);

		if ($this->config('eliminate_magic_quotes')) $this->_eliminate_magic_quotes();

		$this->vars=array('G'=>$_GET, 'P'=>$_POST, 'C'=>$_COOKIE, 'F'=>$_FILES);

		if ($this->config('eliminate_register_globals')) $this->_eliminate_register_globals();
		if ($this->config('eliminate_f_vars')) $this->_eliminate_f_vars();
		if ($this->config('eliminate_gpc_vars')) $this->_eliminate_gpc_vars();
		if ($this->config('eliminate_http_vars')) $this->_eliminate_http_vars();
	}


	/**
	 * to detect the needed modmet-string to call
	 *
	 * @return  a valid modmet-string (mod:met) or false
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 * @author	Vedran Šajatović <vedran.sajatovic@gmail.com>
	 */
	function met_get_modmet()
	{
		// search HTTP parameters for a passed modmet
		$modmet_name = ($this->config('modmet_name')) ? $this->config('modmet_name') : 'mm';
		$modmet = $this->met_var($this->config('modmet_search_order'), $modmet_name);
		if ($modmet && mod_exists($modmet)) return $modmet;

		// search for the default modmet of current group
		if (mod_exists('groups:get_modmet')) {
			$modmet = mod('groups:get_modmet');
			if ($modmet && mod_exists($modmet)) return $modmet;
		}

		// try to read a default modmet from the configuration
		$modmet = $this->config('default_modmet');
		if ($modmet && mod_exists($modmet)) return $modmet;

		// try to redirect..
		if (mod_exists('redirect:detect_now')) return mod('redirect:detect_now');

		// no modmet found
		return false;
	}


	/**
	 * To get the value 'name' of the arrays 'order' searched in indicated order.
	 * The parameter $order is a string with one or more of this characters "GPCES"
	 * Each character stands for one array:
	 *
	 * "G" stands for $_GET
	 * "P" stands for $_POST
	 * "C" stands for $_COOKIE
	 *
	 * The arrays are searched in indicated order from left to right. If the value
	 * of $order is 'GP' the first searched array is $_GET and the second is $_POST
	 * The first search result is the return value.
	 *
	 * If there is no 'name' specified the full array is the return value. If there
	 * are more than one arrays they are merged. The first indicated array is preferred
	 *
	 * @param   string  order   search order
	 * @param   string  name    key name of the searched array element
	 *
	 * @return  mixed   value   the value of the searched array element
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_var($order, $name=false)
	{
		$order=strtoupper($order);

		for ($a=strlen($order)-1; $a >= 0; $a--) $arrays[]=&$this->vars[$order[$a]];
		$arrays_count=count($arrays);

		if ($name === false)
		{
			if ($arrays_count == 1) return $arrays[0];
			if ($arrays_count > 1) return call_user_func_array('array_merge', $arrays);
			return false;
		}

		for ($a=$arrays_count-1; $a >= 0; $a--)
		{
			if (isset($arrays[$a][$name])) return $arrays[$a][$name];
		}

		return false;
	}


	/**
	 * This function negotiates the clients preferred charset based on its Accept-Charset
	 * HTTP header. The qualifier is recognized and charsets without qualifier are rated
	 * highest. The qualifier will be decreased by 10% for partial matches (i.e. matching
	 * primary charset).
	 *
	 * @param   array   supported  array containing the supported charsets as values
	 *
	 * @return  string  the negotiated charset or the default charset (i.e. first array entry) if none match.
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_negotiate_charset($supported)
	{
		return $this->_negotiate($_SERVER['HTTP_ACCEPT_CHARSET'], $supported);
	}


	/**
	 * This function negotiates the clients preferred encoding based on its Accept-Encoding
	 * HTTP header. The qualifier is recognized and encodings without qualifier are rated
	 * highest. The qualifier will be decreased by 10% for partial matches (i.e. matching
	 * primary encoding).
	 *
	 * @param   array   supported  array containing the supported encodings as values
	 *
	 * @return  string  the negotiated encoding or the default encoding (i.e. first array entry) if none match.
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_negotiate_encoding($supported)
	{
		return $this->_negotiate($_SERVER['HTTP_ACCEPT_ENCODING'], $supported);
	}


	/**
	 * This function negotiates the clients preferred language based on its Accept-Language
	 * HTTP header. The qualifier is recognized and languages without qualifier are rated
	 * highest. The qualifier will be decreased by 10% for partial matches (i.e. matching
	 * primary language).
	 *
	 * @param   array   supported  array containing the supported languages as values
	 *
	 * @return  string  the negotiated language or the default language (i.e. first array entry) if none match.
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_negotiate_language($supported)
	{
		return $this->_negotiate($_SERVER['HTTP_ACCEPT_LANGUAGE'], $supported);
	}


	/**
	 * (private) to negotiates the clients preferred settings
	 *
	 * @param   string  accepting_string  a string which declares the accepted values (looks like http-header-fields Accept-Language, ...)
	 * @param   string  supported         array containing the supported settings as values
	 *
	 * @return  string  the negotiated setting or the default setting (i.e. first array entry) if none match.
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _negotiate($accepting_string, $supported)
	{
		static $bestvalues=array();
		$id=$accepting_string.implode("\n", $supported);
		if (isset($bestvalues[$id])) return $bestvalues[$id];

		preg_match_all("/(([a-z]{1,8})(?:-([a-z|-]{1,8}))?)(?:\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(?=,|$)/i", strtolower($accepting_string), $hits, PREG_SET_ORDER);

		for($k=0; isset($hits[$k]); $k++)
			$hits[$k][4]=isset($hits[$k][4]) ? (FLOAT) $hits[$k][4] : 1;

		usort($hits, create_function('&$a,&$b','
			if ($a[4] < $b[4]) return 1;
			if ($a[4] > $b[4]) return -1;
			return 1;
		'));

		$bestvalue = $supported[0];
		$bestqality = 0;

		foreach ($hits as $hit)
		{
			// find q-maximal
			if (in_array($hit[1],$supported) && ($hit[4] > $bestqality)) {
				$bestvalue = $hit[1];
				$bestqality = $hit[4];
			}
			// if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
			else if (in_array($hit[2],$supported) && (($hit[4]*0.9) > $bestqality)) {
				$bestvalue = $hit[2];
				$bestqality = $hit[4]*0.9;
			}
		}

		return $bestvalue;
	}


	/**
	 * to initiate a simple HTTP-redirect with an absolute or relative path and a defined
	 * status code and status text. ATTENTION: This function try to set every status code
	 * without any status code validation or success control. So you have to validate the
	 * status code manually.
	 *
	 * @param   string  location    absolute or relative location path
	 * @param   int     statuscode  (optional) status code. Default value is "302"
	 * @param   string  statustext  (optional) status text. Default value is the equivalent text of the given status code
	 *
	 * @return  null    The script will end with this function
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_redirect($location, $statuscode=302, $statustext=false)
	{
		if (substr($location, 0, 1) == '/') $location=($_SERVER['HTTPS'] ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$location;
		elseif (substr($location, 0, 1) == '?') $location=($_SERVER['HTTPS'] ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].(($p=strpos($_SERVER['REQUEST_URI'], '?')) ? substr($_SERVER['REQUEST_URI'], 0, $p) : $_SERVER['REQUEST_URI']).$location;
		elseif (!preg_match('/^[a-z0-9]+\:/i', $location))
		{
			$location=trim($location, '/');
			$dirname=trim(dirname($_SERVER['REQUEST_URI']), '/');
			$uri=str_replace('\\', '/', ($dirname ? $dirname.'/' : '').$location);

			if (strpos($uri, '/..') !== false || strpos($uri, '/.') !== false)
			{
				$uri=explode('/', $uri);

				for($a=0; $a < count($uri); $a++){
					if ($uri[$a] == '..') { array_splice($uri, $a-1, 2); $a-=2; }
					elseif ($uri[$a] == '.') { array_splice($uri, $a, 1); $a--; }
				}

				$uri=implode('/', $uri);
			}

			$location=($_SERVER['HTTPS'] ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/'.$uri;
		}
		$this->met_status($statuscode, $statustext);

		header('Location: '.$location);
		exit;
	}


	/**
	 * to get the http status code or set the http status code and status text.
	 * ATTENTION: This function try to set every status code without any status
	 * code validation or success control. So you have to validate the status
	 * code manually.
	 *
	 * @param   int     statuscode  (optional) The new status code to set. If there is no value the status code will not change
	 * @param   string  statustext  (optional) The new status text to set. Default value is the equivalent text of the given status code if there is one
	 *
	 * @return  int     The actual or new status code
	 *
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function met_status($statuscode=false, $statustext=false)
	{
		static $currentstatus,$statustable=array(
			'100' => 'Continue',
			'101' => 'Switching Protocols',
			'102' => 'Processing',
			'200' => 'OK',
			'201' => 'Created',
			'202' => 'Accepted',
			'203' => 'Non-Authoritative Information',
			'204' => 'No Content',
			'205' => 'Reset Content',
			'206' => 'Partial Content',
			'207' => 'Multi-Status',
			'300' => 'Multiple Choice',
			'301' => 'Moved Permanently',
			'302' => 'Found',
			'303' => 'See Other',
			'304' => 'Not Modified',
			'305' => 'Use Proxy',
			'306' => '',
			'307' => 'Temporary Redirect',
			'400' => 'Bad Request',
			'401' => 'Unauthorized',
			'402' => 'Payment Required',
			'403' => 'Forbidden',
			'404' => 'Not Found',
			'405' => 'Method Not Allowed',
			'406' => 'Not Acceptable',
			'407' => 'Proxy Authentication Required',
			'408' => 'Request Time-out',
			'409' => 'Conflict',
			'410' => 'Gone',
			'411' => 'Length Required',
			'412' => 'Precondition Failed',
			'413' => 'Request Entity Too Large',
			'414' => 'Request-URI Too Long',
			'415' => 'Unsupported Media Type',
			'416' => 'Requested range not satisfiable',
			'417' => 'Expectation Failed',
			'421' => 'There are too many connections from your internet address',
			'422' => 'Unprocessable Entity',
			'423' => 'Locked',
			'424' => 'Failed Dependency',
			'425' => 'Unordered Collection',
			'426' => 'Upgrade Required',
			'500' => 'Internal Server Error',
			'501' => 'Not Implemented',
			'502' => 'Bad Gateway',
			'503' => 'Service Unavailable',
			'504' => 'Gateway Time-out',
			'505' => 'HTTP Version not supported',
			'506' => 'Variant Also Negotiates',
			'507' => 'Insufficient Storage',
			'509' => 'Bandwidth Limit Exceeded',
			'510' => 'Not Extended'
		);

		if ($statuscode === false)
		{
			if (function_exists('headers_list'))
				foreach(headers_list() as $h)
					if (preg_match('/Status\:\s*([0-9]+)/', $h, $m))
						return $m[1];

			if ($currentstatus)
				return $currentstatus;

			if (isset($_SERVER['REDIRECT_STATUS']))
				return $_SERVER['REDIRECT_STATUS'];

			return false;
		}

		if (!preg_match('/^[1-9][0-9]{2}$/', $statuscode))
			trigger_error('invalid status code ("'.$statuscode.'")', E_USER_ERROR);

		if ($statustext === false && isset($statustable[$statuscode]))
			$statustext=$statustable[$statuscode];

		$statusmessage=$statuscode.($statustext ? ' '.$statustext : '');
		header($_SERVER['SERVER_PROTOCOL'].' '.$statusmessage);
		header('Status: '.$statusmessage);

		return ($currentstatus=$statuscode);
	}


	/**
	 * (private) to eliminate magic quotes of the arrays $_GET, $_POST, $_COOKIE.
	 * There are only changes if magic_quotes_gpc in the php.ini is switched on
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _eliminate_magic_quotes()
	{
		static $magic_quotes_gpc=false;
		static $eliminated=0;

		if ($magic_quotes_gpc === false) $magic_quotes_gpc=get_magic_quotes_gpc();

		if ($magic_quotes_gpc and !$eliminated)
		{
			$_POST=$this->_stripslashes_recursive($_POST);
			$_GET=$this->_stripslashes_recursive($_GET);
			$_COOKIE=$this->_stripslashes_recursive($_COOKIE);
			$eliminated=1;
		}
	}


	/**
	 * (private) to eliminate register globals.
	 * There are only changes if register_globals in the php.ini is switched on
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _eliminate_register_globals()
	{
		if (!ini_get('register_globals')) return;

		foreach($_REQUEST as $n => $v) if ($n != '_') unset($GLOBALS[$n]);
		foreach($_SERVER as $n => $v) if ($n != '_') unset($GLOBALS[$n]);
		foreach($_ENV as $n => $v) if ($n != '_') unset($GLOBALS[$n]);
	}


	/**
	 * (private) to eliminate the array $_FILES.
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _eliminate_f_vars()
	{
		unset($GLOBALS['_FILES']);
	}


	/**
	 * (private) to eliminate the arrays $_REQUEST, $_GET, $_POST, $_COOKIE.
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _eliminate_gpc_vars()
	{
		unset($GLOBALS['_REQUEST']);
		unset($GLOBALS['_GET']);
		unset($GLOBALS['_POST']);
		unset($GLOBALS['_COOKIE']);
	}

	/**
	 * (private) to eliminate the arrays $HTTP_GET_VARS, $HTTP_POST_VARS,
	 * $HTTP_COOKIE_VARS
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _eliminate_http_vars()
	{
		unset($GLOBALS['HTTP_GET_VARS']);
		unset($GLOBALS['HTTP_POST_VARS']);
		unset($GLOBALS['HTTP_COOKIE_VARS']);
		unset($GLOBALS['HTTP_POST_FILES']);
	}


	/**
	 * (private) to strip slashes in a string or array recursive
	 *
	 * @param   mixed   string or array whithin slashes
	 *
	 * @return  mixed   the given string or array whithout slashes
	 *
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 *
	 */
	function _stripslashes_recursive($mixed)
	{
		if (is_array($mixed))
			return array_map(array(&$this, '_stripslashes_recursive'), $mixed);

		return stripslashes($mixed);
	}
}

?>
