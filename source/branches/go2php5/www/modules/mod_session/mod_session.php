<?php

class mod_session extends mod
{
	var $sid;	// id of this session
	var $owner;	// id of the group that owns this session
	var $new;	// we may want to know later whether this session has been newly created or resumed

#	var $override_modmet = false;

	function mod_session()
	{
		// initialize error handler
		if ($this->config('use_mod_error'))
			mod('error:init');

		// do some cleanup in the db - remove old entries
		mod('mysql:query', "DELETE FROM ~~sessions WHERE last_activity < #1", (time() - $this->config('timeout')));

		// do we have a session id?
		$this->new = false;
		$sid = mod('http:var', $this->config('session_transfer_method'), 'sid');
		if($sid)
		{
			// is this session id (still) valid?
			list($owner) = mod('mysql:query_row', "SELECT owner FROM ~~sessions WHERE sid = #1", $sid);
			if ($owner !== null) {
				// the session is valid
				mod('mysql:query', "UPDATE ~~sessions SET last_activity = #1 WHERE sid = #2", time(), $sid);
				$this->owner = $owner;
			}
			else $sid = 0;
		}
		if(!$sid) // invalid or no session id passed
		{
			// create an unique session id
			do
			{
				$sid = md5(uniqid(rand().time()));
				list($sid_exists) = mod('mysql:query_row', "SELECT COUNT(*) AS c FROM ~~sessions WHERE sid=#1", $sid);
			}
			while($sid_exists);

			$this->owner = $this->_get_default_owner();

			if (function_exists('inet_pton'))
				$ip = inet_pton($_SERVER['REMOTE_ADDR']);

			// inet_pton for PHP < 5.3.0 on Windows and for PHP < 5.1.0 on other systems
			else {
				# ipv6
				if (strpos($ip, ':') !== FALSE) {
					$ip = explode(':', $ip);
					$res = '';
					foreach ($ip as $seg) {
						if ($seg === '') $res .= str_repeat('0', 4*(9-count($ip)));
						else $res .= str_pad($seg, 4, '0', STR_PAD_LEFT);
					}
					$ip = pack('H'.strlen($res), $res);
				}
				# ipv4
				elseif (strpos($ip, '.') !== FALSE) {
					$ip = pack('N',ip2long($ip));
				}
			}

			mod('mysql:query', "INSERT INTO ~~sessions (sid, owner, created, last_activity, ip) VALUES (#1, #2, #3, #4, #5)", $sid, $this->owner, time(), time(), $ip);

			// since we have created a new session, we have to dismiss the submitted modmet and use the default one instead
#			$this->override_modmet = true;

			$this->new = true;
		}
		$this->sid=$sid;

		// set the cookie if we need it
#cookie name should be somthing like tulebox_sid ('sid' may be used by other applications as well)
		if(strpos($this->config('session_transfer_method'), 'C') !== false) setcookie("sid", $sid, false, preg_replace('/^[a-z]+\:\/\/[^\/]+/', '', BASEURL), false, 0);
	}

	// this function is called automatically by the main script index.php
	// entry point of the tulebox script - here we determine what action to do
	function met_go()
	{
		$modmet = mod('http:get_modmet');

		if ($modmet) mod($modmet);

		// no modmet found at all - abort
		else trigger_error('I am so happy because I have nothing to do. But thanks for your request. :)', E_USER_ERROR);
	}

	function met_change_owner($group=false, $password=false, $timer=false)
	{
		if($group === false and $password === false)
			$gid = mod('groups:is_group', $this->_get_default_owner());

		elseif($password === false)
			$gid = mod('groups:is_group', $group);

		elseif(mod('groups:validate', $group, $password))
			$gid = mod('groups:is_group', $group);

		if($gid and mod('mysql:query', "UPDATE ~~sessions SET owner=#1 WHERE sid=#2", $gid, $this->sid))
		{
			$this->owner = $gid;
			return true;
		}

		return false;
	}


	/**
	 * Returns the session id of the current session as a string.
	 *
	 * @return string session id
	 */
	function met_sid()
	{
		return $this->sid;
	}


	function met_is_new()
	{
		return $this->new;
	}


	/**
	 * Returns the id of the group owning the current session.
	 *
	 * @return int id of owning group
	 */
	function met_owner()
	{
		return (INT) $this->owner;
	}


	function _get_default_owner()
	{
		return $owner = ($this->config('use_mod_groups'))
			? mod('groups:get_default')
			: 0;
	}
}

?>
