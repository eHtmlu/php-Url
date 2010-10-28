<?php

/** @version 0.0.1 */


# B A U S T E L L E




#                     K O N V E N T I O N E N:

# Zeugs, was am Zeilenanfang steht, ist zum testen da - der Rest ist eingerückt

# mit der raute eingeleitete Kommentare sind solche, die Problemstellen, offene
# Fragen, noch nicht fertige oder noch gar nicht vorhandene Features etc.
# beschreiben. "Richtige" Kommentare (solche, die auch einmal später bleiben
# sollten, wenn wir uns auf den Rest geeinigt haben und alles funktioniert) sind
# mit // oder /* */ angegeben

# bei solchen #-Kommentaren kannst deinen Senf auch direkt in den Quelltext
# "hineinkommentieren" und wieder ins repository stellen ;-)









#right owner?
#wenn man ein benutzerrecht anlegt, sollte es auch möglich sein, dass man es anderen leuten gibt (z.b. zugriffsrechte ein CMS-dokument)
#oder doch lieber einfach demjenigen, der ein recht anlegt, ALLES erlauben?
#oder dies zumindest per parameter bei cretae() ermöglichen?











#permissions to set rights - lalala - will be implemented later









class mod_rights extends mod
{
	/*
	 * The local cache
	 */
	var $cache_have = array(); // cache for rights one has
	var $cache_give = array(); // cache for rights one can set


	/**
	 * Constructor
	 */
	function mod_rights()
	{
		// do some database cleanup - remove obsolete entries
		mod('mysql:query', 'DELETE FROM ~~cache_have WHERE invalidates > 0 AND invalidates < #1', time());
	}







################################################################################
################################################################################
################################################################################
function met_test()
{
//echo get_strict_modmetstr('sternwerkzeug');
//exit;
###cleanup - we want a "clean startup"
#mod('mysql:query', 'TRUNCATE ~~rights');
#mod('mysql:query', 'TRUNCATE ~~have');
#mod('mysql:query', 'TRUNCATE ~~give');
mod('mysql:query', 'TRUNCATE ~~cache_have');
mod('mysql:query', 'TRUNCATE ~~cache_give');
###



/*
$this->met_create('mod_rights', 'arsch');
$this->met_create('mod_rights', 'arsch>fut');
$this->met_create('mod_rights', 'arsch>fut>du');
$this->met_create('mod_rights', 'arsch>fut>ich');
$this->met_create('mod_rights', 'arsch>mist>ich');
$this->met_create('mod_rights', 'arsch>mist');
$this->met_create('mod_rights', 'arsch>wef');
$this->met_create('mod_rights', 'arsch>wef>fut');
*/




#$this->load_cache_entry(1, 'have');
var_dump($this->met_get('mod_rights', 'arsc', 2));
echo '<br>';
var_dump($this->met_get('mod_rights', 'arsch', 2));
var_dump($this->met_get('mod_rights', 'arsch>', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut>', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut>du', 2));
echo '<br>';
var_dump($this->met_set('mod_rights', 'arsch>fut>du', 1, 2));
echo '<br>';
var_dump($this->met_get('mod_rights', 'arsch', 2));
var_dump($this->met_get('mod_rights', 'arsch>', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut>', 2));
var_dump($this->met_get('mod_rights', 'arsch>fut>du', 2));
echo '<br>';
var_dump($this->met_get('mod_rights', 'arsc', 2));
var_dump($this->met_get('mod_rights', 'arsch>f', 2));
var_dump($this->met_get('mod_rights', 'arsch>>fut', 2));

# function met_set($module, $name, $value, $group = 0)
# function met_set_limited($module, $name, $value, $time_start, $time_end, $group = 0)
/*
var_dump($this->met_get('mod_rights', 'arsch', 2));
var_dump($this->met_set('mod_rights', 'arsch', 1, 2));
var_dump($this->met_get('mod_rights', 'arsch', 2));
echo '<br><br>limited:<br>';


var_dump($this->met_get('mod_rights', 'arsch', 2));
var_dump($this->met_set_limited('mod_rights', 'arsch', 0, 1999999999, 2000000000, 2));
var_dump($this->met_get('mod_rights', 'arsch', 2));
echo '<br>';
var_dump($this->met_get('mod_rights', 'arsch', 2));
var_dump($this->met_set_limited('mod_rights', 'arsch', 0, 599999, 1999999799, 2));
var_dump($this->met_get('mod_rights', 'arsch', 2));
*/
#var_dump($this->set_limited('mod_rights', 'arsch', 1, 1234, 1999999999, 2));





################################################################################
#############                                                      #############
#############      T E S T   I N H E R I T A N C E   W I T H       #############
#############               C H I L D   G R O U P S                #############
#############                                                      #############
################################################################################

# test time limits (set in future, look if you can get() it then)







#$this->dump_cache_have();







#$this->delete('mod_rights', 'arsch>fut');
}

function dump_cache_have()
{
	echo "<br><br><b>cache dump for 'have':</b><br><p style=\"font-family: courier new; font-size:10pt; color:#66a\">";
	echo nl2br(print_r($this->cache_have, 1));
	echo "</p><br>";
}

function dump_cache_give()
{
	echo "<br><br><b>cache dump for 'give':</b><br><p style=\"font-family: courier new; font-size:10pt; color:#66a\">";
	echo nl2br(print_r($this->cache_give, 1));
	echo "</p><br>";
}

function invalidate_all()
{
	mod('mysql:query', "TRUNCATE ~~cache_have");
	mod('mysql:query', "TRUNCATE ~~cache_give");
	echo 'All cache entries have been removed.<br>\n';
}
################################################################################
################################################################################
################################################################################














	/**
	 * Create a new user right and return its id. Dependent rights need to
	 * exist already.
	 *
	 * @param string $module the name of the module the new right should belong
	 * to
	 * @param string $name the name of the new right
	 *
	 * @return int zero on failure, otherwise the id of the newly created right
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_create($module, $name, $displayname='')
	{
#echo "creating... module='$module', name='$name'<br>";
		// is the chosen name valid? (the last character must not be '>')
		if(substr($name, -1, 1) == '>') return false;

		// does the module exist?
		if(!mod_exists($module)) return false;
		$module=get_simple_modname($module);
#Dependent rights need to exist already.
		// see if the right does already exist
		$result = mod('mysql:query_assoc', "SELECT COUNT(*) AS c FROM ~~rights WHERE name=#1 AND module=#2", $name, $module);
		if($result['c']) return false;

		// try to insert the new right
		if(!mod('mysql:query', "INSERT INTO ~~rights (module, name, displayname) VALUES (#1, #2, #3)", $module, $name, $displayname)) return false;

		// we succeeded - return the id of the new right
		return mod('mysql:insert_id');
	}




	/**
	 * Update a user right
	 *
	 * @param int $id the ID of the right
	 * @param string $module The new module name or false for no change
	 * @param string $name The new right name or false for no change
	 * @param string $displayname The new displayname or false for no change
	 *
	 * @return bool TRUE on success, otherwise FALSE
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_update($id, $new_module=false, $new_name=false, $new_displayname=false)
	{
		$right=mod('mysql:query_assoc', "SELECT * FROM ~~rights WHERE id=#1", $id);
		if (!$right) return false;

		$set_query=array();
		if (is_string($new_module) && $new_module != '' && $new_module != $right['module']){
			if(!mod_exists($new_module)) return false;
			$set_query[]='`module`=#1';
		}else
			$new_module=false;
		if (is_string($new_name) && $new_name != '' && $new_name != $right['name']){
			// see if the right does already exist
			$result = mod('mysql:query_assoc', "SELECT COUNT(*) AS c FROM ~~rights WHERE name=#1 AND module=#2", $new_name, ($new_module ? $new_module : $right['module']));
			if($result['c']) return false;
			$set_query[]='`name`=#2';
		}
		if (is_string($new_displayname) && $new_displayname != $right['displayname']){
			$set_query[]='`displayname`=#3';
		}

		if (count($set_query)){
			if(!mod('mysql:query', "UPDATE `~~rights` SET ".implode(', ', $set_query).' WHERE `id`=#4', $new_module, $new_name, $new_displayname, $id)) return false;
		}

		return true;
	}

	/**
	 * Update a user right by name
	 *
	 * @param string $module The current module name
	 * @param string $name The current right name
	 * @param string $new_module The new module name or false for no change
	 * @param string $new_name The new right name or false for no change
	 * @param string $new_displayname The new displayname or false for no change
	 *
	 * @return bool TRUE on success, otherwise FALSE
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_update_by_name($module, $name, $new_module=false, $new_name=false, $new_displayname=false)
	{
		if (!$id=mod('mysql:query_field', "SELECT `id` FROM ~~rights WHERE module=#1 AND name=#2", $module, $name))
			return false;
		return $this->met_update($id, $new_module, $new_name, $new_displayname);
	}



### das hier sollte noch mal gründlich durchdacht werden
##  ist das überhaupt nötig / soll es möglich sein?
#
## function met_delete($module, $name)
#	function delete($module, $name)
#	{
#		// see if the right exists
#		list($id) = mod('mysql:query_assoc', "SELECT id FROM ~~rights WHERE module='$module' AND name='$name'");
#		if($id < 1) return false;
#
#		// are there dependent rights?
#		list($dependency_count) = mod('mysql:query_assoc', "SELECT COUNT(*) AS c FROM ~~rights r WHERE module='$module' AND LOCATE('$name', r.name) = 1");
#		if($dependency_count) return false;
#
#		// is the right in use (currently set)?
#		list($usage_count) = mod('mysql:query_assoc', "SELECT COUNT(*) AS c FROM ~~have WHERE module='$module' AND name='$name'");
#		if($usage_count) return false;
#
#/*!!!@important!!!*/
##cleanup not complete - data incosistence possible
##what about cache_give ;-)
#		mod('mysql:query', "DELETE FROM ~~give WHERE module='$module' AND name='$name'");
#		mod('mysql:query', "DELETE FROM ~~rights WHERE id=$id");
#
#		return true;
#	}




	/**
	 * get a right setting
	 *
	 * @return array An associative array with the two parameters "inherit" and "own"
	 * each with the value TRUE (for given right), FALSE (for forbidden access) or NULL if not set.
	 *
	 * @param string $module name of the module
	 * @param string $name name of the right
	 * @param int $group optional - the group id. If this is not set, the id of
	 * the current group is used
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_setting($module, $name, $group = 0)
	{
		$module=get_simple_modname($module);
		if(!$group) $group = mod('session:owner');

		$this->load_cache_entry($group, 'have');

		$result=$this->cache_have[$group]['data'][$module][$name];
		if (!isset($result['inherit'])) $result['inherit']=null;
		if (!isset($result['own'])) $result['own']=null;
		return $result;
	}





	/**
	 * Look up a single user right.
	 * If $group is member of group 1 (administrators), this function always
	 * returns true (virtually all rights set).
	 *
	 * This function takes care of right dependencies. If the specified right is
	 * not directly set for the given group, it also checks whether there is a
	 * right set whose name starts with $name, and returns true if one is found.
	 *
	 * Example: met_get('module', 'right1') will return true if 'right1' is not
	 * set at all (neither true nor false), but 'right1>right2' is.
	 *
	 * @param string $module name of the owning module
	 * @param string $name name of the right
	 * @param int $group optional - the group id. If this is not set, the id of
	 * the current group is used
	 *
	 * @return bool true if the right was set, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get($module, $name, $group = 0)
	{
#echo "module:$module, name:$name, group:$group<br>";
		$module=get_simple_modname($module);
		if(!$group) $group = mod('session:owner');

		// if the group is member of group 1 (administrators), this function always returns true (all rights set)
		if(mod('groups:is_admin', $group)) return true;

		// we need the cache entry for this group...
		$this->load_cache_entry($group, 'have');

		// see if the right is set
		if(isset($this->cache_have[$group]['data'][$module][$name]))
		{
			// return true if the right is set or false if not
			if (isset($this->cache_have[$group]['data'][$module][$name]['own']))
				return $this->cache_have[$group]['data'][$module][$name]['own'];
			else
				return $this->cache_have[$group]['data'][$module][$name]['inherit'];
			//return ($this->cache_have[$group]['data'][$module][$name]) ? true : false;
		}

		// the right is not set directly, but there might be a dependent right set..
		if(count($this->cache_have[$group]['data'][$module]))
		{
			if($name[strlen($name) - 1] != '>') $name .= '>';
			foreach($this->cache_have[$group]['data'][$module] as $n => $x)
			{
#				echo $name . ' | ' . $n . '<br>';
				if(strpos($n, $name) === 0) return (isset($x['own']) ? $x['own'] : $x['inherit']);
			}
		}

		// no dependent rights found...
		return null;
	}


	/**
	 * Set a user right for the given group. The right will be set without any
	 * time limit.
	 *
	 * @param string $module name of the module the right belongs to
	 * @param string $name the name of the right
	 * @param int $value has to be either true or false
	 * @param int $group optional - id of the target group, defaults to the
	 * current group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_set($module, $name, $value, $group = 0)
	{
		$module=get_simple_modname($module);
		return $this->met_set_limited($module, $name, $value, 0, 0, $group);
	}


	/**
	 * Set a user right for the given group using the specified time limit. The
	 * cache entry for that group is rebuilt, those for its child groups are
	 * deleted.
	 * If no starting time is specified, the change will have immediate effect.
	 * If no ending time is specified, the change will last forever. Otherwise,
	 * it will be undone at this time.
	 *
	 * @param string $module name of the module the right belongs to
	 * @param string $name the name of the right
	 * @param int $value has to be either true or false
	 * @param int $time_start optional - timestamp of the time this change will
	 * have effects
	 * @param int $time_end optional - timestamp of the time this change will be
	 * undone
	 * @param int $group optional - id of the target group, defaults to the
	 * current group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_set_limited($module, $name, $value, $time_start, $time_end, $group = 0)
	{
		$module=get_simple_modname($module);
#does the module exist?
		// checking time boundaries..
#more descriptive return values? --> getlasterror/mod_error
		if($time_start && $time_start == $time_end) return false; // time_start equal to time_end - no effect
#swap automatically?
		if($time_start > $time_end) return false; // we cannot end before we started..
#soll sowas "repariert" werden, oder doch abbruch?
#		if($time_start < time()) $time_start = 0; // time_start is already past
		if($time_end && $time_end <= time()) return false; // nothing do to - time_end has passed

		// do we have permission to modify this right for the given group?
		$me = mod('session:owner');
		if($group == 0) $group = $me;
		if(mod('groups:is_admin', $group)) return true;
		if(!mod('groups:is_admin', $me))
		{
			$this->load_cache_entry($me, 'give');
			if(!(is_array($this->cache_give[$me][$module][$name]) && in_array($group, $this->cache_give[$me][$module][$name]))) return false;
		}

		// value shouldn't be anything but 0 or 1
		$value = ($value) ? 1 : 0;

		// see if the right exists
		if(!$result = mod('mysql:query_assoc', "SELECT id FROM ~~rights WHERE module=#1 AND name=#2", $module, $name)) return false;
		if(!$id = $result['id']) return false;

		// insert the right token into the database
		if(!mod('mysql:query', "INSERT INTO ~~have (rid, gid, value, time_start, time_end) VALUES (#1, #2, #3, #4, #5)", $id, $group, $value, $time_start, $time_end)) return false;

#$this->dump_cache_have();
#statt den cache hier zu reparieren vielleicht doch lieber cache-eintrag löschen und mit create_cache_entry() neu erstellen?
#um fehler zu vermeiden

		// does this change affect the current right settings?
		$this->load_cache_entry($group, 'have');
		if(($time_start && $time_start < time()) || !$time_start)
		{
			// repair the cache of the target group if necessary (there is no need to rebuild the whole thing)
			if($value && !$this->cache_have[$group]['data'][$module][$name]['own']) // we need to set it
			{
				$this->cache_have[$group]['data'][$module][$name]['own'] = true;
			}
			else if(!$value && $this->cache_have[$group]['data'][$module][$name]['own']) // we need to clear it
			{
				$this->cache_have[$group]['data'][$module][$name]['own'] = false;
			}
		}

		// fix invalidation time
		list($old_invalidation_time) = mod('mysql:query_assoc', "SELECT invalidates FROM ~~cache_have WHERE gid=#1", $group);
		$invalidates = 0;
		if($time_end) $invalidates = $time_end;
		if($time_start && $time_start > time()) $invalidates = $time_start;
		if($old_invalidation_time && $old_invalidation_time < $invalidates && $old_invalidation_time > time()) $invalidates = $old_invalidation_time;
		$this->cache_have[$group]['invalidates'] = $invalidates;
#$this->dump_cache_have();
		// insert the cache entry into the database
		mod('mysql:query', "UPDATE ~~cache_have SET data=#1, invalidates=#2 WHERE gid=#3", serialize($this->cache_have[$group]['data']), $invalidates, $group);

		// invalidate the cache entries for child groups
		$this->invalidate_cache_have_for_child_groups($group);

		return true;
	}








	/**
	 * Remove the right token with the id $id. Use this to undo right settings
	 * made with mod_rights::set() or mod_rights::set_limited().
	 *
	 * @param int $id id of the right token
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_remove($id)
	{
		if($result = mod('mysql:query_assoc', "SELECT a.gid, b.module, b.name FROM ~~have a JOIN ~~rights b ON (a.rid = b.id) WHERE a.id=#1", $id))
		{
			// do we have permission to modify this right for the given group?
			$me = mod('session:owner');
			if(!mod('groups:is_admin', $me))
			{
				$this->load_cache_entry($me, 'give');
				if(!in_array($result['gid'], $this->cache_give[$me]['data'][$result['module']][$result['name']])) return false;
			}

			// remove the right token
			mod('mysql:query', "DELETE FROM ~~have WHERE id=#1", $id);

			// invalidate cache entries
			mod('mysql:query', "DELETE FROM ~~cache_have WHERE gid=#1", $result['gid']);
			unset($this->cache_have[$result['gid']]);

			$this->invalidate_cache_have_for_child_groups($group);

			return true;
		}
		return false;
	}



	/**
	 * Remove the right token with the right name. Use this to undo right settings
	 * made with mod_rights::set() or mod_rights::set_limited().
	 *
	 * @param string $module name of the module the right belongs to
	 * @param string $name the name of the right
	 * @param int $group optional - id of the target group, defaults to the
	 * current group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_remove_by_name($module, $name, $group = 0)
	{
		$me = mod('session:owner');
		if($group == 0) $group = $me;

		if($results = mod('mysql:query_accumulate_assoc', "SELECT a.id, a.gid, b.module, b.name FROM ~~have a JOIN ~~rights b ON (a.rid = b.id) WHERE b.module=#1 AND b.name=#2 AND a.gid = #3", $module, $name, $group))
		{
			$count=0;
			foreach ($results as $result)
			{
				// do we have permission to modify this right for the given group?
				if(!mod('groups:is_admin', $me))
				{
					$this->load_cache_entry($me, 'give');
					if(!isset($this->cache_give[$me]['data'][$result['module']][$result['name']]) || !in_array($result['gid'], $this->cache_give[$me]['data'][$result['module']][$result['name']])) return false;
				}

				// remove the right token
				mod('mysql:query', "DELETE FROM ~~have WHERE id=#1", $result['id']);

				// invalidate cache entries
				mod('mysql:query', "DELETE FROM ~~cache_have WHERE gid=#1", $result['gid']);
				unset($this->cache_have[$result['gid']]);
				$count++;
			}

			if ($count)
				$this->invalidate_cache_have_for_child_groups($group);

			return $count;
		}
		return false;
	}



	function met_delete($id)
	{
		$this->met_remove($id);
		mod('mysql:query', "DELETE FROM ~~rights WHERE id=#1", $id);
		return true;
	}




	function met_detect($id=false/*$module=false, $name=false*/)
	{
		/*if ($module) $module=get_simple_modname($module);

# untergeordnete rechte werden noch nicht beachtet ... name-parameter wird nicht geprüft/angepasst
		return mod('mysql:query_accumulate_assoc', "SELECT * FROM ~~rights".($module ? ' WHERE `module` = #1'.($name ? ' AND `name` = #2' : '') : ''), $module, $name);
		*/
		if ($id)
			return mod('mysql:query_assoc', "SELECT * FROM ~~rights WHERE `id` = #1", $id);
		else
			return mod('mysql:query_accumulate_assoc', "SELECT * FROM ~~rights");
	}



#	/**
#	 * @private
#	 * Extracts all dependent rights from a right name and returns them in an
#	 * array.
#	 *
#	 * For example,
#	 * 	get_dependencies('right1>right2>right3');
#	 * will return
#	 * 	Array
#	 * 	(
#	 * 		[0] => right1>
#	 * 		[1] => right1>right2>
#	 * 	)
#	 *
#	 * @param string $name name of a right that may contain dependent rights
#	 *
#	 * @return array dependent rights
#	 *
#	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
#	 */
#	function get_dependencies($right)
#	{
#		$buffer = array();
#
#		$bits = explode('>', $right);
#		if(count($bits) > 1)
#		{
#			for($i = 0 ; $i < count($bits) - 1 ; $i++)
#			{
#				$right = '';
#				for($j = 0 ; $j <= $i ; $j++)
#				{
#					if($right) $right .= '>';
#					$right .= $bits[$j];
#				}
#				$buffer[] = $right . '>';
#			}
#		}
#
#		return $buffer;
#	}


	/**
	 * @private
	 * Remove the cache_have entries for every child group of $group, both in
	 * the database and in the local cache
	 *
	 * @param int $group id of the target group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function invalidate_cache_have_for_child_groups($group)
	{
		$children = mod('groups:get_children', $group);
		if($children)
		{
			$buffer = '';
			$i = 0;
			$args = array();
			foreach($children as $c)
			{
				$buffer .= ($buffer) ? ' OR gid=#' . $i++ : 'gid=#' . $i++;
				$args[] = $c;
				unset($this->cache_have[$c]);
			}
#fix query
			if(!mod('mysql:query', "DELETE FROM ~~cache_have WHERE $buffer", $args)) return false;
		}
		return true;
	}


	/**
	 * @private
	 * Retrieve the cached rights for the given group and store them locally for
	 * further use. If no entry is found in the db, create_cache_entry() is
	 * called to create one.
	 * The parameter $type can be either 'have' to get information about rights
	 * $group has, or 'give' to get information about rights $group can set.
	 *
	 * @param int $group the id of the group to load the cache entry for
	 * @param string $type either 'have' or 'give'
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function load_cache_entry($group, $type)
	{
#echo "loading cache entry for group $group - type: $type...<br>";
		// no need to chech whether $group is valid - if the operation fails, create_cache_entry() will do the check
		// is type valid (has to be either 'have' or 'give')?
		if($type != 'have' && $type != 'give') return false;

		if($type == 'have')
		{
			// if there is already an entry in the local cache, nothing has to be done
			if(isset($this->cache_have[$group])) return true;

			// nothing found in the local cache - we need to load it from the db
			if($result = mod('mysql:query_assoc', "SELECT data, invalidates FROM ~~cache_have WHERE gid=#1", $group))
			{
				$this->cache_have[$group]['data'] = unserialize($result['data']);
				$this->cache_have[$group]['invalidates'] = $result['invalidates'];
				return true;
			}
		}
		else
		{
			// if there is already an entry in the local cache, nothing has to be done
			if(isset($this->cache_give[$group])) return true;

			// nothing found in the local cache - we need to load it from the db
			if($result = mod('mysql:query_assoc', "SELECT data FROM ~~cache_give WHERE gid=#1", $group))
			{
				$this->cache_give[$group] = unserialize($result['data']);
				return true;
			}
		}

		// no (valid) cache entry found in the db - we need to create it
		return $this->create_cache_entry($group, $type);
	}


	/**
	 * @private
	 * Gather information about user rights for the given group and store it in
	 * an array. The resulting dataset is stored in both local and db cache.
	 * The parameter $type can be either 'have' to get information about rights
	 * $group has, or 'give' to get information about rights $group can set.
	 *
	 * @param int $group the id of the group to create the cache entry for
	 * @param string $type either 'have' or 'give'
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function create_cache_entry($group, $type)
	{
#echo "creating cache entry for group $group - type: $type...<br>";
		// is type valid (has to be either 'have' or 'give')?
		if($type != 'have' && $type != 'give') return false;

		// is the group valid?
		if(!mod('groups:is_group', $group)) return false;

		// do some cleanup in the db - remove expired rights
		mod('mysql:query', 'DELETE FROM ~~have WHERE time_end > 0 AND time_end < #1', time());

		// initialize the array we will return
		$dataset = array();
		$invalidates = 0;

		// inherit rights
		if($parents = mod('groups:get_parents', $group))
		{
			foreach($parents as $p)
			{
				$this->load_cache_entry($p, $type);
				if($type == 'have') $parent_cache = $this->cache_have[$p]['data'];
				else $parent_cache = $this->cache_give[$p];

				// adapt invalidation time
				if($type == 'have' && ((!$invalidates) || $invalidates > $this->cache_have[$p]['invalidates']))
					$invalidates = $this->cache_have[$p]['invalidates'];

				// traverse rights of each parent group
				if(is_array($parent_cache))
				{
					foreach($parent_cache as $n_mod => $i_mod) // modules
					{
						foreach($i_mod as $n_right => $i_right) // rights
						{
#echo "mod: $n_mod, right: $n_right<br>";
							if($type == 'have' && $i_right) $dataset[$n_mod][$n_right]['inherit'] = true;
							elseif($type == 'have' && isset($i_right)) $dataset[$n_mod][$n_right]['inherit'] = false;
							elseif($type == 'have') $dataset[$n_mod][$n_right]['inherit'] = null;
							else if(is_array($i_right)) $dataset[$n_mod][$n_right]['inherit'] = array_merge($dataset[$n_mod][$n_right]['inherit'], $i_right);
						}
					}
				}
			}
		}
		if($type == 'give' && is_array($dataset[$n_mod][$n_right]['inherit'])) $dataset[$n_mod][$n_right]['inherit'] = array_unique($dataset[$n_mod][$n_right]['inherit']);

		// get your own rights
		if($type == 'have') $query = mod('mysql:query', "SELECT a.value, a.time_start, a.time_end, b.module, b.name FROM ~~have a JOIN ~~rights b ON (a.rid = b.id) WHERE a.gid=#1", $group);
		else $query = mod('mysql:query', "SELECT a.value, a.target, b.module, b.name FROM ~~give a JOIN ~~rights b ON (a.rid = b.id) WHERE a.gid=#1", $group);
		if(!$query) return false;

		while($result = mod('mysql:fetch_array', $query))
		{
			if($type == 'have')
			{
				if($result['time_start'] < time())
				{
					if($result['value']) $dataset[$result['module']][$result['name']]['own'] = true;
					elseif(isset($result['value'])) $dataset[$result['module']][$result['name']]['own'] = false;
					else $dataset[$result['module']][$result['name']]['own'] = null;

					if($result['time_end'])
					{
						if(!$invalidates || $invalidates > $result['time_end']) $invalidates = $result['time_end'];
					}
				}
				else if(!$invalidates || $invalidates > $result['time_start']) $invalidates = $result['time_start'];
			}
			else // type == 'give'
			{
				$target_groups = explode(',', $result['target']);

				if($result['value'])
				{
					foreach($target_groups as $g)
					{
						if(!(is_array($dataset[$result['module']][$result['name']]['own']) && in_array($g, $dataset[$result['module']][$result['name']]['own'])))
							$dataset[$result['module']][$result['name']]['own'][] = $g;
					}
				}
				else
				{
					foreach($dataset[$result['module']][$result['name']]['own'] as $key => $g)
					{
						if(is_array($target_groups) && in_array($g, $target_groups)) unset($dataset[$result['module']][$result['name']]['own'][$key]);
					}
				}
			}
		}
#var_dump($dataset);
		// store the dataset in the local cache and in the db
		if($type == 'have')
		{
			if(!mod('mysql:query', "INSERT INTO ~~cache_have (gid, data, invalidates) VALUES (#1, #2, #3)", $group, serialize($dataset), $invalidates)) return false;

			$this->cache_have[$group]['data'] = $dataset;
			$this->cache_have[$group]['invalidates'] = $invalidates;
		}
		else
		{
			if(!mod('mysql:query', "INSERT INTO ~~cache_give (gid, data) VALUES (#1, #2)", $group, serialize($dataset))) return false;

			$this->cache_give[$group] = $dataset;
		}

		// done
		return true;
	}

}

?>
