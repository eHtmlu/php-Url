<?php

/* a few settings... */

define('CONFIG_FILE', 'config.ini');

define('REGEX_MOD', '/^(?:mod_|)((?!mod_)[a-z0-9][a-z0-9_]{2,})$/');
define('REGEX_MET', '/^(?:met_|)((?!met_)[a-zA-Z][a-zA-Z0-9_]*)$/');
define('REGEX_MODMET', '/^(?:mod_|)((?!mod_)[a-z0-9][a-z0-9_]{2,})(?::(?:met_|)((?!met_)[a-zA-Z][a-zA-Z0-9_]*)|)$/');
define('REGEX_MOD_STRICT', '/^(?:mod_)((?!mod_)[a-z0-9][a-z0-9_]{2,})$/');
define('REGEX_MET_STRICT', '/^(?:met_)((?!met_)[a-zA-Z][a-zA-Z0-9_]*)$/');
define('REGEX_MODMET_STRICT', '/^(?:mod_)((?!mod_)[a-z0-9][a-z0-9_]{2,})(?::(?:met_)((?!met_)[a-zA-Z][a-zA-Z0-9_]*)|)$/');

define('URL', substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')).'://'.$_SERVER["SERVER_NAME"].preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']));
define('BASEURL', substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')).'://'.$_SERVER["SERVER_NAME"].preg_replace('/^[\\/\\\\]$/', '', dirname($_SERVER["SCRIPT_NAME"])).'/');
define('SUBURL', preg_replace('/^'.preg_quote(BASEURL, '/').'/', '', URL));
define('BASEPATH', dirname($_SERVER["SCRIPT_FILENAME"]));


 /*-----------------------------------------------------------------*\
|                                                                     |
| the following functions are public and you can and have to use them |
|                                                                     |
 \*-----------------------------------------------------------------*/



/**
 * THE ONE HOLY FUNCTION for the communication between modules
 *
 * Calls a certain method of a certain object and include the module if doesn't exist before.
 *
 * This is the one holy function which you can and have to use all in your modules.
 * ATTENTION: The ONE holy function! Don't use any other function to get
 * informations outside your module.
 *
 * @param $modmetstr string The name of the class (without the prefix 'mod_') and the methodename which you want to call (without the prefix 'met_') separated by ":" (colon)
 * @param $... mixed A few parameters more. They are passed on to the called method
 *
 * @return mixed The return value of the called method.
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 *
 */
function mod($modmetstr)
{
	global $_;

	list($mod, $met)=get_strict_modmetarray($modmetstr);

	if (mod_exists($modmetstr))
	{
		if (!isset($_['M'][$mod]) || !is_object($_['M'][$mod])) _mod_init($mod);

		$arguments=func_get_args();
		$arguments=array_splice($arguments, 1);

		$_['M_PATH'][]=$mod;
		$item=_mod_log_start($mod, $met, $arguments, microtime());
		$return=call_user_func_array(array(&$_['M'][$mod], $met), $arguments);
		_mod_log_stop($item, $return, microtime());
		array_pop($_['M_PATH']);

		return $return;
	}

	if ($_['M_ERRORMSG']) trigger_error($_['M_ERRORMSG'], E_USER_ERROR);

	trigger_error('Missing module \''.$mod.'\'', E_USER_ERROR);
}



/**
 * checks the availability of a module or a methode
 *
 * @param string $modmetstr The name of module you want to check or the name
 * of module and the name of methode separated by ":" (colon) if you want to
 * check both
 *
 * @return bool TRUE if the module and if required also the methode is available otherwise FALSE
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 */
function mod_exists($modmetstr)
{
	global $_;
	static $buffer=array();

	// get from buffer
	if (isset($buffer[$modmetstr])) return $buffer[$modmetstr];

	// get modmet
	if ($mm=get_strict_modmetarray($modmetstr))
	{
		// mod exists
		if ((class_exists($mm[0]) or _mod_load($mm[0])) and in_array($mm[1], get_class_methods($mm[0])))
			return ($buffer[$modmetstr]=true);
	}

	return ($buffer[$modmetstr]=false);
}



/**
 * to get the name of the module which requested the current module
 *
 * @return string The name of requested module or FALSE if the current
 * module is the configurated default module and started the script
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 */
function mod_lookback($back=1)
{
	global $_;

	if ($back >= 0 and ($c=count($_['M_PATH'])) > $back) return $_['M_PATH'][$c-1-$back];
	return false;
}





function get_simple_modmetstr($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return implode(':', $modmet);
}

function get_simple_modmetarray($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return $modmet;
}

function get_simple_modname($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return $modmet[0];
}

function get_simple_metname($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return $modmet[1];
}

function get_strict_modmetstr($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return 'mod_' . $modmet[0] . ':met_' . $modmet[1];
}

function get_strict_modmetarray($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return array('mod_' . $modmet[0], 'met_' . $modmet[1]);
}

function get_strict_modname($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return 'mod_' . $modmet[0];
}

function get_strict_metname($modmetvar)
{
	$modmet = _normalize_modmetvar($modmetvar);
	return 'met_' . $modmet[1];
}


/**
 * Checks whether a string is a valid modmet string. A modmet string consists of
 * a module and a method name, separated by a colon.
 *
 * @param string $modmetstr the modmet string to check
 * @param bool $strict optional TRUE for strict mode otherwise FALSE (default). The
 * strict mode also needs prefixes of module name and methode name.
 *
 * @return bool TRUE for valid string otherwise FALSE
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
 */
function is_modmetstr($modmetstr, $strict=false)
{
	return preg_match(($strict) ? REGEX_MODMET_STRICT : REGEX_MODMET, $modmetstr) ? true : false;
}


/**
 * Checks whether a string is a valid module name
 *
 * @param string $modname The module name
 * @param bool $strict optional TRUE for strict mode otherwise FALSE (default). The
 * strict mode also needs the prefix of module name.
 *
 * @return bool TRUE for valid string otherwise FALSE
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
 */
function is_modname($modname, $strict=false)
{
	return preg_match(($strict) ? REGEX_MOD_STRICT : REGEX_MOD, $modname) ? true : false;
}


/**
 * Checks whether a string is a valid methode name
 *
 * @param string $metname The methode name
 * @param bool $strict optional TRUE for strict mode otherwise FALSE (default). The
 * strict mode also needs the prefix of methode name.
 *
 * @return bool TRUE for valid string otherwise FALSE
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
 */
function is_metname($metname, $strict=false)
{
	return preg_match(($strict) ? REGEX_MET_STRICT : REGEX_MET, $metname) ? true : false;
}


/**
 * Retrieves settings from the global configuration.
 *
 * The name of a desired setting may be passed as an optional parameter, in that
 * case only the corresponding value is returned. If none is specified, the
 * whole configuration is returned as an associative array.
 *
 * @param string $name optional - the name of the parameter you want to fetch
 *
 * @return mixed the value of the given parameter as a string or all parameters
 * as an associative array.
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 * @author Vedran Sajatovic <vedran.sajatovic@gmail.com>
 */
function config($name = false, $section = 'general')
{
	static $configuration = false;

	if ($configuration === false) {
		if (is_file(CONFIG_FILE)) $configuration = parse_ini_file(CONFIG_FILE, true);
		else trigger_error('failed to open main configuration file.', E_USER_ERROR);
	}

	if ($name) return (isset($configuration[$section][$name])) ? $configuration[$section][$name] : false;
	return $configuration;
}























 /*-----------------------------------------------------------------*\
|                                                                     |
|           DON'T USE ANYTHING AFTER THIS POINT FROM OUTSIDE          |
|                                                                     |
|                           IT'S PRIVATE                              |
|                                                                     |
 \*-----------------------------------------------------------------*/



// all private variables of this script. Don't use them outside!
$_=array(
	'M' => array(),
	'M_BUFFER' => array(),
	'M_LOG' => array(),
	'M_PATH' => array(),
);





/**
 * loads a module.
 *
 * require the module class and create an object named $_['M'][modulename].
 *
 * @param string $mod The name of the module (within the prefix 'mod_')
 *
 * @return bool TRUE on success otherwise FALSE
 *
 * @version 1.0
 *
 * @author Helmut Wandl <helmut@wandls.net>
 */
function _mod_load($mod)
{
	global $_;
	static $modpath='';
	if (!$modpath) $modpath=config('module_path');

	if (!is_dir($modpath.'/'.$mod)) { $_['M_ERRORMSG']="module directory '".$mod."' not found"; return false; }
	if (!is_file($modpath.'/'.$mod.'/'.$mod.'.php')) { $_['M_ERRORMSG']="module file '".$mod.".php' not found"; return false; }
	if (!include_once($modpath.'/'.$mod.'/'.$mod.'.php')) { $_['M_ERRORMSG']="can't require module file '".$mod.".php'"; return false; }
	if (!class_exists($mod)) { $_['M_ERRORMSG']="there is no class named '".$mod."' in the module file '".$mod.".php'"; return false; }

	return true;
}


function _mod_init($mod)
{
	global $_;

	$_['M_PATH'][]=$mod;
	$item=_mod_log_start($mod, $mod, array(), microtime());
	$_['M'][$mod]=new $mod();
	_mod_log_stop($item, $_['M'][$mod], microtime());
	array_pop($_['M_PATH']);

	return is_object($_['M'][$mod]);
}






function _normalize_modmetvar($modmetvar)
{
	global $_;

	if (is_array($modmetvar)) $modmetvar=implode(':', $modmetvar);

	if (!isset($_['M_BUFFER'][$modmetvar]))
	{
		if (!preg_match(REGEX_MODMET, $modmetvar, $arr)) return false;
		if (!isset($arr[2])) $arr[2] = 'default';

		$_['M_BUFFER'][$modmetvar] = array($arr[1], $arr[2]);
	}

	return $_['M_BUFFER'][$modmetvar];
}








function _mod_log_start($mod, $met, $arguments, $time)
{
	global $_;

	$pos=count($_['M_LOG']);
	$_['M_LOG'][$pos]['mod']=$mod;
	$_['M_LOG'][$pos]['met']=$met;
	$_['M_LOG'][$pos]['type']='start';
	$_['M_LOG'][$pos]['start']=$time;
	$_['M_LOG'][$pos]['arguments']=$arguments;

	return $pos;
}

function _mod_log_stop($startpos, $returned, $time)
{
	global $_;

	$pos=count($_['M_LOG']);
	$_['M_LOG'][$pos]['type']='stop';
	$_['M_LOG'][$pos]['startpos']=$startpos;
	$_['M_LOG'][$startpos]['stop']=$time;
	$_['M_LOG'][$startpos]['returned']=$returned;
}


function _mod_log_xml()
{
	global $_;
	$result="";

	$tab=0;

	for ($a=0; $a < count($_['M_LOG']); $a++)
	{
		if ($_['M_LOG'][$a]['type'] == 'start')
		{
			$result.=str_repeat("	", $tab)."<".$_['M_LOG'][$a]['mod'].":".$_['M_LOG'][$a]['met']." start=\"".$_['M_LOG'][$a]['start']."\" stop=\"".$_['M_LOG'][$a]['stop']."\"";

			for ($b=0; $b < count($_['M_LOG'][$a]['arguments']); $b++)
			{
				$result.=" argument".($b+1)."=\"";

				$_['M_LOG'][$a]['arguments'][$b]=serialize($_['M_LOG'][$a]['arguments'][$b]);
				$result.=htmlentities(str_replace("\r", "\\r", str_replace("\n", "\\n", $_['M_LOG'][$a]['arguments'][$b])));
				$result.="\"";
			}

			if (isset($_['M_LOG'][$a]['returned']))
			{
				$_['M_LOG'][$a]['returned']=serialize($_['M_LOG'][$a]['returned']);
				$result.=" return=\"".htmlentities(str_replace("\r", "\\r", str_replace("\n", "\\n", $_['M_LOG'][$a]['returned'])))."\"";
			}

			if ($_['M_LOG'][$a+1]['type'] == 'stop' and $_['M_LOG'][$a+1]['startpos'] == $a) { $result.=" />\n"; $a++; }
			else { $result.=">\n"; $tab++; }
		}
		elseif ($_['M_LOG'][$a]['type'] == 'stop')
		{
			$tab--;
			$result.=str_repeat("	", $tab)."</".$_['M_LOG'][$_['M_LOG'][$a]['startpos']]['mod'].":".$_['M_LOG'][$_['M_LOG'][$a]['startpos']]['met'].">\n";
		}
	}

	return $result;
}




?>
