<?php



class mod_redirect extends mod
{
	function mod_redirect()
	{
//		require_once($this->info('path')."include/redirect.class.php");
	}
	
	/**
	 * to detect the next valid redirection
	 * 
	 * @return  a valid modmet-string (mod:met) or false
	 * 
	 * @version 1.0
	 * @author  Helmut Wandl <helmut@wandls.net>
	 * 
	 */
	function met_detect_now()
	{
		$redirections_=mod('mysql:query', "SELECT `id`, `goto` FROM ~~redirections WHERE `enabled` = 1 ORDER BY `order`");
		
		while($redirection=mod('mysql:fetch_assoc', $redirections_))
		{
			$conditions_=mod('mysql:query', "SELECT `id`, `arrays`, `name`, `regex` FROM ~~conditions WHERE redirect_id = ".$redirection['id']);
			
			while($condition=mod('mysql:fetch_assoc', $conditions_))
			{
				$result=@preg_match("/".str_replace("/", "\\/", $condition['regex'])."/", mod('http:var', $condition['arrays'], $condition['name']));
				
				if ($result !== 1) continue 2;
				if ($result === false) trigger_error("redirect error at condition ".$condition['id'], E_USER_WARNING);
			}
			
			if (mod_exists($redirection['goto'])) return $redirection['goto'];
			else trigger_error("redirect error (missing methode ".$redirection['goto'].")", E_USER_WARNING);
		}
		
		return false;
	}
	
	function met_new($mod, $name, $goto, $conditions, $display_name="")
	{
		$mod=$this->_mod_standardization($mod);
		if (!is_modmetstr($goto)) return false;
		if (!$this->_is_conditionsarray($conditions)) return false;
		
		$order=mod('mysql:query_field', "SELECT MAX(`order`) FROM ~~redirections");
		$order++;
		
		mod('mysql:query', "INSERT INTO ~~redirections SET `mod` = #1, `name` = #2, `goto` = #3, `display_name` = #4, `order` = #5", $mod, $name, $goto, $display_name, $order);
		if (mod('mysql:affected_rows') > 0 and ($id=mod('mysql:insert_id')) > 0)
		{
			if (count($conditions) > 0)
			{
				foreach($conditions as $n => $v)
				{
					mod('mysql:query', "INSERT INTO ~~conditions SET `redirect_id` = #1, `keyword` = #2, `arrays` = #3, `name` = #4, `regex` = #5", $id, $n, $v['arrays'], $v['name'], $v['regex']);
				}
			}
			return $id;
		}
		
		return false;
	}
	
	function met_enable($id)
	{
		mod('mysql:query', "UPDATE ~~redirections SET `enabled` = 1 WHERE `id` = #1", $id);
		if (mod('mysql:query_field', "SELECT `enabled` FROM ~~redirections WHERE `id` = #1", $id))
		return true;
		return false;
	}
	
	function met_disable($id)
	{
		mod('mysql:query', "UPDATE ~~redirections SET `enabled` = 0 WHERE `id` = #1", $id);
		if (mod('mysql:query_field', "SELECT `enabled` FROM ~~redirections WHERE `id` = #1", $id) === '0')
		return true;
		return false;
	}
	
	function met_get($id=false)
	{
		$r_=mod('mysql:query', "SELECT `id`, `enabled`, `mod`, `goto`, `name`, `display_name` FROM ~~redirections".($id > 0 ? " WHERE `id` = #1" : ""), $id);
		
		for ($a=0; $r=mod('mysql:fetch_assoc', $r_); $a++)
		{
			$arr[$a]=$r;
			$c_=mod('mysql:query', "SELECT `keyword`, `arrays`, `name`, `regex` FROM ~~conditions WHERE `redirect_id` = #1", $r['id']);
			
			while ($c=mod('mysql:fetch_assoc', $c_))
			{
				$arr[$a]['conditions'][$c['keyword']]=array(
					'arrays' => $c['arrays'],
					'name' => $c['name'],
					'regex' => $c['regex']
				);
			}
		}
		
		if ($id > 0 and count($arr) == 1)
		return $arr[0];
		return $arr;
	}
	
	function met_delete($id)
	{
		mod('mysql:query', "DELETE FROM ~~conditions WHERE `redirect_id` = #1", $id);
		mod('mysql:query', "DELETE FROM ~~redirections WHERE `id` = #1", $id);
		if (mod('mysql:affected_rows') > 0) return true;
		return false;
	}
	
	function met_deleteall($mod)
	{
		$mod=$this->_mod_standardization($mod);
		
		$r=mod('mysql:query', "SELECT `id` FROM ~~redirections WHERE `mod` = #1", $mod);
		while($row=mod('mysql:fetch_assoc', $r)) $this->met_delete($row['id']);
		
		return true;
	}
	
	function met_enablebyname($mod, $name)
	{ return ($id=$this->_get_id($mod, $name)) ? $this->met_enable($id) : false; }
	
	function met_disablebyname($mod, $name)
	{ return ($id=$this->_get_id($mod, $name)) ? $this->met_disable($id) : false; }
	
	function met_getbyname($mod, $name)
	{ return ($id=$this->_get_id($mod, $name)) ? $this->met_get($id) : false; }
	
	function met_deletebyname($mod, $name)
	{ return ($id=$this->_get_id($mod, $name)) ? $this->met_delete($id) : false; }
	
	function _is_conditionsarray($arr)
	{
		if (!is_array($arr)) { trigger_error("conditions array have not the correct format", E_USER_WARNING); return false; }
		if (count($arr) == 0) return true;
		
		foreach($arr as $n => $v)
		{
			if (!is_array($v)) { trigger_error("condition isn't an array", E_USER_WARNING); return false; }
			if (count($v) != 3) { trigger_error("condition array have not the correct number of elements", E_USER_WARNING); return false; }
			if (!isset($v['arrays']) or !is_string($v['arrays']) or !preg_match("/^[GPCSE]{1,5}$/", strtoupper($v['arrays']))) { trigger_error("condition arrays missing or have not the correct format", E_USER_WARNING); return false; }
			if (!isset($v['name']) or !is_string($v['name'])) { trigger_error("condition name missing or not a string", E_USER_WARNING); return false; }
			if (!isset($v['regex']) or !is_string($v['regex']) or @preg_match("/".str_replace("/", "\\/", $v['regex'])."/", "") === false) { trigger_error("condition regex missing or have an error", E_USER_WARNING); return false; }
		}
		return true;
	}
	
	function _get_id($mod, $name)
	{
		$mod=$this->_mod_standardization($mod);
		$id=mod('mysql:query_field', "SELECT `id` FROM ~~redirections WHERE `mod` = #1 AND `name` = #2", $mod, $name);
		if ($id > 0) return $id;
		return false;
	}
	
	function _mod_standardization($mod)
	{
		if (substr($mod, 0, 4) == 'mod_') return substr($mod, 4);
		return $mod;
	}
}



?>