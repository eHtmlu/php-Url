<?php

class mod_tpl extends mod
{
	var $_=array('js' => array(), 'css' => array());

	function mod_tpl($path=false, $suffix=false)
	{
		$this->_param('path', ($path !== false ? $path : ($this->config('path') !== false ? $this->config('path') : '~~/tpl')));
		$this->_param('suffix', ($suffix !== false ? $suffix : $this->config('suffix')));
	}

	function met_new($path=false, $suffix=false)
	{
		return new mod_tpl($path, $suffix);
	}

	function assign($name, $value, $htmlspecialchars=false)
	{
		if ($htmlspecialchars) $value=$this->_htmlspecialchars_recursive($value);

		return $this->$name=$value;
	}

	function display($tpl)
	{
		$path=$this->_param('path');

		$path=str_replace("~~", config('module_path').'/'.(mod_lookback(0) == 'mod_tpl' ? mod_lookback() : mod_lookback(0)), $path);
		$path=str_replace("~", config('module_path'), $path);

		return (@include($path . '/' . $tpl . $this->_param('suffix'))) ? true : false;
	}

	function fetch($tpl)
	{
		$result=false;

		ob_start();
		if ($this->display($tpl)) $result=ob_get_contents();
		ob_end_clean();

		return $result;
	}

	function _param($name, $value=false)
	{
		if ($value !== false) $this->_[$name]=$value;

		if (isset($this->_[$name])) return $this->_[$name];
		return false;
	}



	/**
	 * To encode the value of a string or an array.
	 *
	 * @param mixed $value String or multidimensional array which you have to encode
	 * @param int $htmlencode The encode methode (1 for htmlspecialchars; 2 for htmlentities)
	 *
	 * @return mixed The encoded value
	 *
	 * @version 1.0
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _htmlspecialchars_recursive($value)
	{
		if (is_array($value))
		{
			foreach ($value as $n => $v)
			{
				$value[$n]=$this->_htmlspecialchars_recursive($v);
			}
		}
		elseif(is_object($value))
		{
			foreach(get_object_vars($value) as $n => $v)
			{
				$value->$n=$this->_htmlspecialchars_recursive($v);
			}
		}
		else
		{
			$value=htmlspecialchars($value, ENT_COMPAT, $this->_param('charset'));
		}

		return $value;
	}

	function add_css($path, $alternate=false, $title=false)
	{
		$path=str_replace("~~", config('module_path').'/'.(mod_lookback(0) == 'mod_tpl' ? mod_lookback() : mod_lookback(0)), $path);
		$path=str_replace("~", config('module_path'), $path);

		if ($c=@filemtime($path))
		{
			$argument_separator = (strpos($path, '?') === false) ? '?' : '&amp;';
			$path = $path . $argument_separator . 'c=' . $c;
		}

		if(!in_array($path, $this->_['css']))
			$this->_['css'][] = array($path, $alternate, $title);
	}

	function add_js($path)
	{
		$path=str_replace("~~", config('module_path').'/'.(mod_lookback(0) == 'mod_tpl' ? mod_lookback() : mod_lookback(0)), $path);
		$path=str_replace("~", config('module_path'), $path);

		if ($c=@filemtime($path))
		{
			$argument_separator = (strpos($path, '?') === false) ? '?' : '&amp;';
			$path = $path . $argument_separator . 'c=' . $c;
		}

		if(!in_array($path, $this->_['js']))
			$this->_['js'][] = $path;
	}

	function fetch_js()
	{
		$js = '';
		foreach ($this->_['js'] as $j) $js .= '		<script type="text/javascript" src="'.$j.'"></script>' . "\n";
		return $js;
	}

	function fetch_css()
	{
		$css = '';
		foreach ($this->_['css'] as $c) $css .= '		<link rel="' . ($c[1] ? 'alternate ' : '') . 'stylesheet" type="text/css" href="' . $c[0] . '"' . ($c[2] ? ' title="'.$c[2].'"' : '') . ' />' . "\n";
		return $css;
	}
}

?>
