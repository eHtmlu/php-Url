<?php




class mod_error extends mod
{
	/**
	 * @version 1.1
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function mod_error()
	{
		$this->log=array();
		$this->logfile=$this->info('path').'/error.log';
	}


	/**
	 * to initialize this error module
	 *
	 * @return bool TRUE on success otherwise the script ends in an fatal error
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_init()
	{
		if (set_error_handler(array(&$this, '_php_error_handler')) === false) trigger_error('Can\'t set error handler', E_USER_ERROR);

		return true;
	}

	/**
	 * display a small predefined message for errors.
	 *
	 * You can use it in the configuration file for display automatically if there is an error
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_nopanic()
	{
		echo "No panic! An error has occurred, but the world still turns.<br />";
	}

	/**
	 * to trigger a notice
	 *
	 * @param string $text The content of the notice as a brief description
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_notice($text)
	{
		$this->_handler($text, 'notice', 'script');
	}

	/**
	 * to trigger a warning
	 *
	 * @param string $text The content of the warning as a brief description
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_warning($text)
	{
		$this->_handler($text, 'warning', 'script');
	}

	/**
	 * to trigger an error
	 *
	 * @param string $text The content of the error as a brief description
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_error($text)
	{
		$this->_handler($text, 'error', 'script');
	}

	/**
	 * get all entries of temporary log since the start of script.
	 * This are all entries. Not only which logged in the log file
	 *
	 * @return array A multidimensional array with all entries
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_templog()
	{
		return $this->log;
	}

	/**
	 * get all saved log entries
	 *
	 * @return array An array with all saved entries
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_filelog()
	{
		if (is_file($this->logfile))
			return array_map('trim', file($this->logfile));

		return array();
	}

	/**
	 * delete all saved log entries
	 *
	 * @return bool TRUE on success otherwise FALSE
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_delete_filelog()
	{
		if (is_file($this->logfile)) unlink($this->logfile);
		if (is_file($this->logfile)) return false;
		return true;
	}


	/**
	 * private handler to manage all errors, warnings and so on
	 *
	 * @param string $error_msg The error message
	 * @param string $error_type The type of error. Allowed values are 'error', 'warning' and 'notice'
	 * @param string $error_source The error source. Allower values are 'server' (for php and server errors) or 'script' (for self defined script errors)
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _handler($error_msg, $error_type, $error_source, $file=false, $line=false)
	{
		static $groups=array();

		if (ini_get('error_reporting') === '0') return;

		if ($file === false and $line === false)
			list($file, $line)=$this->_get_infos();

		$group=mod('session:owner');
		if (!isset($groups[$group])) $groups[$group]=(mod('groups:is_admin', $group) ? 'admin' : 'default');

		$this->log[]=array($error_source, $error_type, $error_msg, $file, $line);

		if ($num=$this->config($groups[$group].'_'.$error_source.'_'.$error_type))
		{
			$msg=strtoupper($error_source.' '.$error_type).': '.$error_msg.' in '.$file.' on line '.$line;
			$msgd='<b>'.strtoupper($error_source.' '.$error_type).'</b>: '.$error_msg.' in <b>'.$file.'</b> on line <b>'.$line."</b><br />\n";

			if ($num >= 8) { $f=fopen($this->logfile,'a'); fwrite($f, date('r').' - '.$msg."\n"); fclose($f); $num-=8; }
			if ($num >= 4) { echo $msgd; $num-=4; }
			if ($num >= 2) { if (($mm=$this->config($groups[$group].'_modmet')) and mod_exists($mm)) mod($mm); $num-=2; }
			if ($num >= 1) { exit; }
		}
	}

	/**
	 * detects file and line of the last error
	 *
	 * @return array An array with the file name in the first and the line number in the second parameter
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _get_infos()
	{
		$arr=debug_backtrace();

		if ($arr[3]['function'] == 'trigger_error') return array($arr[3]['file'], $arr[3]['line']);

		return array($arr[4]['file'], $arr[4]['line']);
	}

	/**
	 * the php error handler which set by initialization of this module
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _php_error_handler($type, $text, $file, $line, $context)
	{
		switch ($type)
		{
			case E_ERROR: $this->_handler($text, 'error', 'server', $file, $line); break;
			case E_WARNING: $this->_handler($text, 'warning', 'server', $file, $line); break;
			case E_PARSE: $this->_handler($text, 'error', 'server', $file, $line); break;
			case E_NOTICE: $this->_handler($text, 'notice', 'server', $file, $line); break;

			case E_CORE_ERROR: $this->_handler($text, 'error', 'server', $file, $line); break;
			case E_CORE_WARNING: $this->_handler($text, 'warning', 'server', $file, $line); break;

			case E_COMPILE_ERROR: $this->_handler($text, 'error', 'server', $file, $line); break;
			case E_COMPILE_WARNING: $this->_handler($text, 'warning', 'server', $file, $line); break;

			case E_USER_ERROR: $this->_handler($text, 'error', 'script', $file, $line); break;
			case E_USER_WARNING: $this->_handler($text, 'warning', 'script', $file, $line); break;
			case E_USER_NOTICE: $this->_handler($text, 'notice', 'script', $file, $line); break;

			case E_STRICT: $this->_handler($text, 'notice', 'server', $file, $line); break;

			default: $this->_handler($text, 'error', 'server', $file, $line); break;
		}
	}
}

?>
