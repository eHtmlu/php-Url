<?php

class mod_sprockets extends mod
{
	var $loadpath = array();
	var $cachepath;

	var $loaded = array();


	/**
	 * Constructor.
	 */
	function mod_sprockets()
	{
		// get some paths we will need later
		$loadpath = explode(',', $this->config('loadpath'));
		foreach ($loadpath as $l) {
			if (realpath($l) == $l) $this->loadpath[] = $l;
			else {
				$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $l);
				if ($path) $this->loadpath[] = $path;
			}
		}

		$this->cachepath = realpath(($this->info('path') . '/cache'));
		if (!$this->cachepath) trigger_error('sprockets cache path not found', E_USER_WARNING);
	}


	/**
	 * Compile a javascript file.
	 *
	 * Taking the source javascript file as an http variable, this function
	 * processes any sprocket directives in that file and outputs the result.
	 * To use mod_sprockets, external javascript files have to be redirected by
	 * apache's mod_rewrite or a similar mechanism and tunneled through
	 * met_generate().
	 * The sprocketized version is also stored in a local cache folder for
	 * easier deployment. A cached file is preferred over parsing the source
	 * files. However, caching can be turned off by configuration during
	 * development.
	 *
	 * @author Vedran Šajatović <vedran.sajatovic@gmail.com>
	 */
	function met_generate()
	{
		// set the proper http header for javascript files
		header('Content-Type: text/javascript');

		// see what the client actually requested
		$request = mod('http:var', 'G', 'js') . '.js';

		// try to load the file from cache (if caching is enabled)
		$cachefile = $this->cachepath . '/' . $request;
		if (!$this->config('disable caching') && $this->cachepath) {
			if (file_exists($cachefile)) {
				readfile($cachefile);
				die;
			}
		}

		$this->loaded[] = realpath($request);

		// read the source javascript
		$script = file_get_contents($request);
		if ($script === false) {
			// source script not found
			// todo do some clever error handling here
		}

		// sprocketize the source javascript
		$output = $this->_sprocketize($script);

		// cache the output
		if ($this->cachepath) {
			if ($filehandle = fopen($cachefile, 'w')) {
				fwrite($filehandle, $output);
			}
		}

		// output the sprocketized javascript
		die($output);
	}


	/**
	 * Sprocketize and return javascript source code passed in $source.
	 *
	 * Currently, only the require directive is processed. Any require directive
	 * are replaced with the source code from the required javascript file.
	 * Required javascript files are sprocketized as well.
	 * Single-line comments and PDoc documentation block comments are stripped
	 * from the returned javascript source code.
	 *
	 * @param string $source javascript code containing sprocket directives
	 *
	 * @return string the processed javascript code
	 *
	 * @author Vedran Šajatović <vedran.sajatovic@gmail.com>
	 */
	function _sprocketize($source)
	{
		// normalize line endings
		$source = str_replace("\r\n", "\n", $source);

		// find required javascript files
		$lines = explode("\n", $source);
		$processed_lines = array();
		foreach($lines as $l) {
			$require = false;
			if (preg_match('/\/\/=\s+require\s+"(.+)"/', $l, $matches))
				$require = $matches[1] . '.js';
			else if (preg_match('/\/\/=\s+require\s+<(.+)>/', $l, $matches))
				foreach ($this->loadpath as $l) {
					$filename = $l . '/' . $matches[1] . '.js';
					if (file_exists($filename)) {
						$require = $filename;
						break;
					}
				}
			if ($require) {
				if (!in_array($require, $this->loaded)) {
					$required_source = file_get_contents($require);
					$processed_lines[] = $this->_sprocketize($required_source);
					$this->loaded[] = $require;
				}
			} else $processed_lines[] = $l;
		}
		$source = implode("\n", $processed_lines);

		// strip comments
		$source = preg_replace('/\/\/.*/', '', $source);
		$source = preg_replace('/\/\*\*.*\*\//sU', '', $source);

		// remove trailing whitespace
		$source = preg_replace('/\s+$/', '', $source);

		return $source;
	}
}

?>
