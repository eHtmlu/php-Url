<?php

class mod_statistics extends mod
{
	/**
	 * Constructor
	 */
	function mod_statistics()
	{
	}


	function met_hit($target = false)
	{
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		$IP = inet_pton($_SERVER['REMOTE_ADDR']);

		$user = ($this->config('use_mod_session') == true)
			? mod('session:owner')
			: 0;

		$new_session = ($this->config('use_mod_session') == true)
			? mod('session:is_new')
			: 0;

		mod('mysql:query', 'INSERT IGNORE INTO ~~useragents SET useragent=#1', $useragent);
		mod('mysql:query', 'INSERT INTO ~~hits (timestamp, target, user, ip, useragent, new_session) VALUES (#1, #2, #3, #4, (SELECT id FROM ~~useragents WHERE useragent=#5), #6)', time(), $target, $user, $IP, $useragent, $new_session);
	}
}

?>
