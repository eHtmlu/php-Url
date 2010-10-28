<?php

class mod
{
	function mod()
	{
		if(get_class($this) == 'mod') trigger_error("class 'mod' must not be accessed directly", E_USER_ERROR);
	}


	function config($name = false)
	{
		return config($name, get_class($this));
	}


	function about($name = false)
	{
		static $arr = false;

		if ($arr === false) {
			$file = $this->info('path') . '/' . get_class($this) . '.about.ini';
			if (is_file($file)) $arr = parse_ini_file($file);
			else trigger_error("faled to open '$file'", E_USER_ERROR);
		}

		if ($name) return (isset($arr[$name])) ? $arr[$name] : false;

		return $arr;
	}


	function info($name)
	{
		switch($name) {
			case 'name':
				return get_class($this);
			case 'path':
				return config('module_path') . '/' . get_class($this);
			default:
				return false;
		}
	}


	// stubs for config(), acout() and info() to make it possible to call them using mod()
	function met_config($name = false) { return $this->config($name); }
	function met_about($name = false)  { return $this->about($name);  }
	function met_info($name)           { return $this->info($name);   }


	function met_default()
	{
		$method = $this->config('met_default');

		if (method_exists($this, $method)) {
			$arguments = func_get_args();
			return call_user_func_array(array(&$this, $method), $arguments);
		}
	}
}

?>
