<?php

# wer darf was - rechte!!!
#password expiration



class mod_groups extends mod
{
	// group relations
	var $cache = array();
	// known group names <> ids
	var $namecache = array();


	/**
	 * Constructor
	 */
	function mod_groups()
	{
	}


	/**
	 * Checks if a certain group exists. Both the name and the id of the group
	 * can be used as argument.
	 *
	 * @param mixed $group name or id of the group to check
	 *
	 * @return int the group id (>0) if the group exists, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_group($group)
	{
		// do a local cache lookup first...
		if(is_numeric($group))
		{
			if(isset($this->namecache[$group]) && $this->namecache[$group]) return (int) $group;
			$sql = 'id';
		}
		else
		{
			if($id = array_search($group, $this->namecache, true)) return (int)$id;
			$sql = 'name';
		}

		// we don't know this group yet, query the database
		if(is_array($group) && ($row = mod('mysql:query_assoc', "SELECT g.id, g.name, n.name AS nname FROM ~~groups g, ~~namespaces n WHERE g.namespaceid = n.id AND n.name=#1 AND g.name=#2", $group[0], $group[1])))
		{
			$this->namecache[$row['id']] = array($row['nname'], $row['name']);
			return (int) $row['id'];
		}
		elseif (is_string($group) && $row = mod('mysql:query_assoc', "SELECT id, name FROM ~~groups WHERE name=#1", $group))
		{
			$this->namecache[$row['id']] = $row['name'];
			return (int) $row['id'];
		}
		elseif (is_numeric($group) && $row = mod('mysql:query_assoc', "SELECT g.id, g.name, n.name AS nname FROM ~~groups g LEFT JOIN ~~namespaces n ON g.namespaceid = n.id WHERE g.id=#1", $group))
		{
			//$this->namecache[$row['id']] = $row['name'];
			if ($row['nname'] == NULL)
				$this->namecache[$row['id']] = $row['name'];
			else
				$this->namecache[$row['id']] = array($row['nname'], $row['name']);
			return (int) $row['id'];
		}

		return false;
	}


	/**
	 * Checks if a certain group is member of group 1 (administrators). If no
	 * group is specified, the current group id is used. Both name or id of a
	 * group can be used for $group.
	 *
	 * @param mixed $group id or name of the group to check
	 *
	 * @return bool true if the group is an administrator, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_admin($group = false)
	{
		if(!$group) $group = mod('session:owner');

		if($group && $this->met_is_group($group) === 1) return true;

		return $this->met_is_child($group, 1);
	}


	/**
	 * Similar to is_child(). Does the same thing, just the other way round.
	 *
	 * Checks if a certain group is parent of another given group. The child
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $parent id of the parent group
	 * @param mixed $child optional - id of the child group
	 *
	 * @return bool true if $parent is a parent of $child, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_is_parent($parent, $child = false)
	{
		if(!$child) $child = mod('session:owner');

		return $this->met_is_child($child, $parent);
	}


	/**
	 * Checks if a certain group is member of another given group. The parent
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $child id of the child group
	 * @param mixed $parent optional - id of the parent group
	 *
	 * @return bool true if $child is a member of $parent, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_is_child($child, $parent = false)
	{
		if(!$parent) $parent = mod('session:owner');

		// are the group ids valid?
		if(!($child = $this->met_is_group($child)) || !($parent = $this->met_is_group($parent))) return false;

		$children = $this->met_get_children($parent);

		return (array_search($child, $children) === false) ? false : true;
	}


	/**
	 * Similar to is_direct_child(). Does the same thing, just the other way round.
	 *
	 * Checks if a certain group is direct parent of another given group. The child
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $parent id of the parent group
	 * @param mixed $child optional - id of the child group
	 *
	 * @return bool true if $parent is a parent of $child, otherwise false
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_direct_parent($parent, $child = false)
	{
		if(!$child) $child = mod('session:owner');

		return $this->met_is_direct_child($child, $parent);
	}


	/**
	 * Checks if a certain group is direct member of another given group. The parent
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $child id of the child group
	 * @param mixed $parent optional - id of the parent group
	 *
	 * @return bool true if $child is a member of $parent, otherwise false
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_direct_child($child, $parent = false)
	{
		if(!$parent) $parent = mod('session:owner');

		// are the group ids valid?
		if(!($child = $this->met_is_group($child)) || !($parent = $this->met_is_group($parent))) return false;

		$children = $this->met_get_direct_children($parent);

		return (array_search($child, $children) === false) ? false : true;
	}


	/**
	 * Similar to is_indirect_child(). Does the same thing, just the other way round.
	 *
	 * Checks if a certain group is indirect parent of another given group. The child
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $parent id of the parent group
	 * @param mixed $child optional - id of the child group
	 *
	 * @return bool true if $parent is a parent of $child, otherwise false
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_indirect_parent($parent, $child = false)
	{
		if(!$child) $child = mod('session:owner');

		return $this->met_is_indirect_child($child, $parent);
	}


	/**
	 * Checks if a certain group is indirect member of another given group. The parent
	 * group identifier is optional, if not set, the current group is used.
	 *
	 * For the parameters $parent and $child both the name or the id of a group
	 * can be used.
	 *
	 * @param mixed $child id of the child group
	 * @param mixed $parent optional - id of the parent group
	 *
	 * @return bool true if $child is a member of $parent, otherwise false
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_is_indirect_child($child, $parent = false)
	{
		if(!$parent) $parent = mod('session:owner');

		// are the group ids valid?
		if(!($child = $this->met_is_group($child)) || !($parent = $this->met_is_group($parent))) return false;

		$children = array();

		foreach($this->met_get_children($parent,false) as $key => $value)
			if(is_array($value)) $children = array_merge($children, $this->_flatten($value));

		return (array_search($child, $children) === false) ? false : true;
	}


	/**
	 * Validate a group. A group is valid when the valid flag in its database
	 * entry is set and the passed password is correct. Use this when a user
	 * logs in.
	 *
	 * There is support for multiple hash algorithms, though it is recommended
	 * to use md5 only (to create less unneccessary data mess). Other algorithms
	 * should be used only for compatibility purposes.
	 *
	 * @param mixed $group name or id of the group
	 * @param string $password the plain password to verify
	 *
	 * @return bool true if $group is a valid user group and the password was
	 * successfully verified, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
# password algorithm mechinism is subject to change - see the wiki for details
	function met_validate($group, $password)
	{
		if(!$group = $this->met_is_group($group)) return false; // check if the group exists, if $group is a string, it is converted to the group id
		if($result = mod('mysql:query_assoc', "SELECT password, password_algorithm FROM ~~groups WHERE valid='1' AND id=#1", $group))
		{
			if($this->_encrypt_password($password, $result['password_algorithm']) === $result['password']) return true;
		}
		return false;
	}


	/**
	 * @private
	 * Encrypt a password using the specified algorithm (defaults to md5).
	 * $algorithm needs to be the name of a valid, existing php function that
	 * accepts only a single string as parameter and returns the hashed string.
	 *
	 * Note that _encrypt_password() can't check whether the specified hash
	 * function really returns a (good/useful) hashed value. If abused, this
	 * feature can cause security problems. It is recommended to use the default
	 * hash algorithm md5.
	 *
	 * @todo password algorithm mechinism is subject to change - see the wiki for details
	 *
	 * @param string $password the plain password to encrypt
	 * @param string $algorithm name of the php function to be used to hash
	 *
	 * @return string the hashed password
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _encrypt_password($password, $algorithm = 'md5')
	{
#salt
		if(!function_exists($algorithm)) $algorithm = 'md5';
		return (string)$algorithm($password);
	}


	/**
	 * Changes the password of the given group. Both name or id of a group can
	 * be used for $group.
	 *
	 * The optional parameter $group defaults to the current group id.
	 *
	 * @param string $password the new password
	 * @param mixed $group optional - name or id of the target group
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_set_password($new_password, $group = false)
	{
#check rights - who can change ones password
		if(!$group) $group = mod('session:owner');
		if(!$group = $this->met_is_group($group)) return false;

#password criteria? length, different chars, not similar to name etc.
		$new_password = $this->_encrypt_password($new_password);

		$result = mod('mysql:query', "UPDATE ~~groups SET password=#1, password_algorithm='md5' WHERE id=#2", $new_password, $group);

		if($result && mod('mysql:affected_rows') == 1) return true;
		return false;
	}






################################################################################
################################################################################
################################################################################
function test()
{

//mod('mysql:query', 'TRUNCATE ~~groups');
mod('mysql:query', 'TRUNCATE ~~cache_up');
mod('mysql:query', 'TRUNCATE ~~cache_down');
//mod('mysql:query', 'TRUNCATE ~~assignment');

//$this->met_create('root', 'test', '');

/*
$a = $this->create('a', 'test', 'abcd');
$b = $this->create('b', 'test', 'abcd', $a);
$c = $this->create('c', 'test', 'abcd', $b);
$d = $this->create('d', 'test', 'abcd', $a);
$e = $this->create('e', 'test', 'abcd');
$f = $this->create('f', 'test', 'abcd');
$g = $this->create('g', 'test', 'abcd');
$h = $this->create('h', 'tet', 'abcd');
$i = $this->create('i', 'test', 'abcd');

$this->link($h, $g);
$this->link($g, $h);
$this->link($a, $g);*/
/*
$this->unlink($b, $a);


$this->delete($f);
$this->delete($a);
*/

//echo ($this->is_child(39, 40)) ? 'true' : 'false';
}

function met_dump_cache()
{
	print_r($this->cache);
}

################################################################################
################################################################################
################################################################################







	/**
	 * Creates a new group. The name and password are required, an optional
	 * modmet string can also be specified. If no modmet is passed, the default
	 * value is used. Also, a parent group can be specified - in that case the
	 * new group automatically becomes member of it. If the chosen group doesn't
	 * exist, this parameter is ignored.
	 *
	 * @param string $name name of the new group
	 * @param string $password the plain password of the new group
	 * @param string $modmet optional - the default module/method string of this
	 * group (as required by mod())
	 * @param mixed $parent optional - id or name of a existing group to become
	 * parent of the newly created group
	 *
	 * @return int the id of the new group (>0), false on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_create($name, $password, $modmet = false, $parent = false)
	{
#check rights
#validate name (must not be numeric by the means of php), modmet
#get default modmet

		// validate parameters
		if ($modmet && !is_modmetstr($modmet)) return false;
		if ($parent && !($parent = $this->met_is_group($parent))) return false;

		// does the group already exist?
		if (is_array($name)) $result = mod('mysql:query_assoc', 'SELECT COUNT(*) AS c FROM ~~groups g LEFT JOIN ~~namespaces n ON g.namespaceid = n.id WHERE n.name=#1 AND g.name=#2', $name[0], $name[1]);
		else $result = mod('mysql:query_assoc', 'SELECT COUNT(*) AS c FROM ~~groups WHERE name=#1 AND namespaceid = 0', $name);

		if($result['c'] == 0)
		{
			if(is_array($name))
			{
				if (($nsid=$this->_get_namespaceid($name[0])) || ($nsid=$this->_create_namespace($name[0])))
				$gname=$name[1];
			}
			else
			{
				$nsid=0;
				$gname=$name;
			}

			if($nsid !== false && mod('mysql:query', 'INSERT INTO ~~groups (namespaceid, name, modmet) VALUES (#1, #2, #3)', $nsid, $gname, $modmet))
			{
				$id = mod('mysql:insert_id');
				$this->met_set_password($password, $id);

				if($parent) $this->met_link($id, $parent);

				// insert the new group into our local name resolving cache
				$this->namecache[$id] = $name;
				return $id;
			}
		}

		// undo create namespace because something went wrong
		if ($nsid) $this->_delete_empty_namespace($nsid);

		return false;
	}



	/**
	 * get id of a given namespace name
	 *
	 * @param string $name name of the namespace
	 *
	 * @return int the namespace id on success, otherwise FALSE
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _get_namespaceid($name)
	{
		if ($id=mod('mysql:query_field', 'SELECT id FROM ~~namespaces WHERE name=#1', $name)) return $id;
		return false;
	}



	/**
	 * create a new namespace
	 *
	 * If there is already existing a namespace with the given name the return value is FALSE
	 *
	 * @param string $name name of the new namespace
	 *
	 * @return int the namespace id on success, otherwise FALSE
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _create_namespace($name)
	{
		if (mod('mysql:query', 'INSERT INTO ~~namespaces SET name=#1', $name) && $id=mod('mysql:insert_id'))
		return $id;

		return false;
	}



	/**
	 * deletes the namespace with the given id if it is empty
	 *
	 * If the namespace is in use, the return value is FALSE
	 *
	 * @todo use one mysql query instead of two for deletion. I'm sorry but i worked three hours with no result for that now :(
	 *
	 * @param int $id id of the namespace
	 *
	 * @return bool TRUE on success, otherwise FALSE
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function _delete_empty_namespace($id)
	{
		//if (mod('mysql:query', 'DELETE FROM ~~namespaces n LEFT JOIN ~~groups g ON n.id = g.namespaceid WHERE n.id=#1 AND g.namespaceid IS NULL', $id) && mod('mysql:affected_rows') > 0)
		//if (mod('mysql:query', 'DELETE FROM ~~namespaces n WHERE n.id=#1 AND (SELECT COUNT(*) FROM tulebox_mod_groups__groups g WHERE g.namespaceid = #1) = 0', $id) && mod('mysql:affected_rows') > 0)
		//if (mod('mysql:query', 'DELETE FROM ~~namespaces n WHERE n.id=#1 AND n.id NOT IN (SELECT namespaceid FROM ~~groups)', $id) && mod('mysql:affected_rows') > 0)
		if (mod('mysql:query_field', 'SELECT COUNT(*) FROM ~~groups WHERE namespaceid=#1', $id) == 0 && mod('mysql:query', 'DELETE FROM ~~namespaces WHERE id=#1', $id) && mod('mysql:affected_rows') > 0)
		return true;

		return false;
	}



	/**
	 * Delete a group.
	 *
	 * Note that this may significantly change the group hierarchy structure.
	 *
	 * @param mixed $group name or id of an existing group to be deleted
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_delete($group)
	{
#check rights
#echo "delete $group<br>";
		// is the group id valid?
		if(!$group = $this->met_is_group($group)) return false;

		if($group == 1) return false; // make it impossible to delete group 1 = administrator (because there is no way to get the id 1 back)

		//invalidate cache entries
		$this->_invalidate_cache($group, 2);
		unset($this->namecache[$group]);

		// get namespace id
		$nsid=mod('mysql:query_field', 'SELECT namespaceid FROM ~~groups WHERE id=#1', $group);

		// delete the group
		mod('mysql:query', 'DELETE FROM ~~assignment WHERE parent=#1 OR child=#1', $group);
		mod('mysql:query', 'DELETE FROM ~~groups WHERE id=#1', $group);

		// delete namespace if existing and empty
		if ($nsid) $this->_delete_empty_namespace($nsid);

		return true;
	}











	/**
	 * @private
	 * Deletes cache entries for a certain group that are no longer valid. Cache
	 * entries of affected child/parent groups are deleted as well (depending of
	 * the direction).
	 *
	 * A direction of 1 means that all cache entries containing parent group
	 * information will be deleted for every child group of $group. A direction
	 * of 0 means that all cache entries containing child group information will
	 * be deleted for every parent group of $group. A direction of 2 means that
	 * both will be done.
	 *
	 * @param int $group id of the target group
	 * @param int $direction direction - should be either 1 (up), 0 (down) or 2
	 * (both)
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _invalidate_cache($group, $direction)
	{
#echo "invalidate $group $direction<br>";
		// filter invalid directions
		if($direction < 0 || $direction > 2) return false;

		if($direction != 0) $children = $this->met_get_children($group);
		if($direction != 1) $parents = $this->met_get_parents($group);

		// $direction == 1 => up
		if($direction != 0)
		{
			$sql = '';
			foreach($children as $item)
			{
				$sql .= " OR id='$item'";
				unset($this->cache[1][$item]);
			}
			mod('mysql:query', "DELETE FROM ~~cache_up WHERE id='$group' $sql");
			unset($this->cache[1][$group]);
		}

		// $direction == 0 => down
		if($direction != 1)
		{
			$sql = '';
			foreach($parents as $item)
			{
				$sql .= " OR id='$item'";
				unset($this->cache[0][$item]);
			}
			mod('mysql:query', "DELETE FROM ~~cache_down WHERE id='$group' $sql");
			unset($this->cache[0][$group]);
		}

		return true;
	}


	/**
	 * Creates a new connection between two groups. $child becomes member of
	 * $parent.
	 *
	 * @param mixed $child name or id of the child group
	 * @param mixed $parent name or id of the parent group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_link($child, $parent)
	{
#echo "link $child $parent<br>";
		if(!($child = $this->met_is_group($child)) || !($parent = $this->met_is_group($parent)) || $child == $parent || $this->met_is_child($parent, $child)) return false;

		if($this->met_is_direct_child($child, $parent)) return true;

		if(mod('mysql:query', 'INSERT INTO ~~assignment (parent, child) VALUES (#1, #2)', $parent, $child))
		{
			//invalidate cache entries
			$this->_invalidate_cache($child, 1);
			$this->_invalidate_cache($parent, 0);

			return true;
		}
		return false;
	}


	/**
	 * Breaks a connection between two groups. $child is no longer member of
	 * $parent. If $child was not member of $parent, nothing happens.
	 *
	 * @param mixed $child id of the child group
	 * @param mixed $parent id of the parent group
	 *
	 * @return bool true on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_unlink($child, $parent)
	{
#echo "unlink $child $parent<br>";
		if(!($child = $this->met_is_group($child)) || !($parent = $this->met_is_group($parent))) return false;
		if(!$this->met_is_child($child, $parent)) return true;

		//invalidate cache entries
		$this->_invalidate_cache($child, 1);
		$this->_invalidate_cache($parent, 0);

		if(mod('mysql:query', 'DELETE FROM ~~assignment WHERE child=#1 AND parent=#2', $child, $parent)) return true;
		return false;
	}










	/**
	 * Finds all direct parents of the group with the name or id $group and returns
	 * them in an array.
	 *
	 * @param mixed $group optional - the owning group. If this is not set, the
	 * current group id is used
	 *
	 * @return array parent group ids (as values)
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_direct_parents($group = false)
	{
		if(!$group) $group = mod('session:owner');
		// is the group id valid?
		if(!$group = $this->met_is_group($group)) return false;

		$query = mod('mysql:query', 'SELECT parent FROM ~~assignment WHERE child=#1', $group);
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['parent'];

		return $buffer;
	}


	/**
	 * Finds all direct children of the group with the id $group and returns
	 * them in an array.
	 *
	 * @param mixed $group optional - the owning group. If this is not set, the
	 * current group id is used
	 *
	 * @return array child group ids (as values)
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_direct_children($group = false)
	{
		if(!$group) $group = mod('session:owner');
		// is the group id valid?
		if(!$group = $this->met_is_group($group)) return false;

		$query = mod('mysql:query', 'SELECT child FROM ~~assignment WHERE parent=#1', $group);
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['child'];

		return $buffer;
	}


	/**
	 * Finds all childless groups and return them in an array
	 *
	 * @return array childless group ids (as values)
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_childless()
	{
		$query=mod('mysql:query', 'SELECT g.id AS id FROM `~~groups` g LEFT JOIN `~~assignment` a ON g.id = a.parent WHERE a.child IS NULL');
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['id'];

		return $buffer;
	}


	/**
	 * Finds all parentless groups and return them in an array
	 *
	 * @return array parentless group ids (as values)
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_parentless()
	{
		$query=mod('mysql:query', 'SELECT g.id AS id FROM `~~groups` g LEFT JOIN `~~assignment` a ON g.id = a.child WHERE a.parent IS NULL');
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['id'];

		return $buffer;
	}


	/**
	 * Finds all lonely groups (parentless and childless) and return them in an array
	 *
	 * @return array lonely group ids (as values)
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_lonely()
	{
		$query=mod('mysql:query', 'SELECT g.id AS id FROM `~~groups` g LEFT JOIN `~~assignment` a ON g.id = a.child OR g.id = a.parent WHERE a.child IS NULL AND a.parent IS NULL');
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['id'];

		return $buffer;
	}


	/**
	 * Finds all groups and return them in an array
	 *
	 * @return array all group ids (as values)
	 *
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_all()
	{
		$query=mod('mysql:query', 'SELECT id FROM `~~groups`');
		$buffer = array();
		while($result = mod('mysql:fetch_array', $query)) $buffer[] = $result['id'];

		return $buffer;
	}


	/**
	 * Returns information about parent groups.
	 *
	 * Depending on the boolean parameter $flatten, the returned array
	 * can either be simple, containing only group ids, or more complex,
	 * containing additional group hierarchy information.
	 *
	 * @example
	 * # Examples for the structure of the returned arrays:
	 *
	 * 	$group = 5;
	 * 	$parents = get_parents($group, true);
	 * 	print_r($parents);
	 *
	 * 	// possible output:
	 * 	// Array
	 * 	// (
	 * 	//     [0] => 4
	 * 	//     [1] => 3
	 * 	//     [2] => 2
	 * 	//     [3] => 1
	 * 	//     [4] => 13
	 * 	// )
	 *
	 * 	$parents = get_parents($group, false);
	 * 	print_r($parents);
	 *
	 * 	// possible output:
	 * 	// Array
	 * 	// (
	 * 	//     [4] => Array
	 * 	//         (
	 * 	//             [3] => Array
	 * 	//                 (
	 * 	//                 )
	 * 	//             [2] => Array
	 * 	//                 (
	 * 	//                     [1] => Array
	 * 	//                         (
	 * 	//                         )
	 * 	//                 )
	 * 	//             [13] =>
	 * 	//                 (
	 * 	//                 )
	 * 	//         )
	 * 	// )
	 *
	 *
	 * # Note that the returned arrays are not sorted - the group ids appear in
	 * # random order.
	 *
	 * @param mixed $group optional - the target group. If this is not set, the
	 * current group id is used
	 * @param bool $flatten optional - indicates whether the returned array
	 * should be simple (but without hierarchy data) or complex (containing full
	 * hierarchy data, but not so easy to parse. Defaults to true (simple).
	 *
	 * @return array parent groups
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_parents($group = false, $flatten = true)
	{
		if(!$group) $group = mod('session:owner');
		// is the group id valid?
		if(!$group = $this->met_is_group($group)) return false;

		$this->_load_cache_entry(1, $group);

		if($flatten) return $this->_flatten($this->cache[1][$group]);
#if($flatten) echo"output: ".nl2br(print_r($this->cache[1][$group], 1))."<br>";
		return $this->cache[1][$group];
	}


	/**
	 * Returns information about child groups.
	 *
	 * For Examples how the returned arrays might look like see get_parents();
	 *
	 * Note that the returned arrays are not sorted - the group ids appear in
	 * random order.
	 *
	 * @param mixed $group optional - the target group. If this is not set, the
	 * current group id is used
	 * @param bool $flatten optional - indicates whether the returned array
	 * should be simple (but without hierarchy data) or complex (containing full
	 * hierarchy data, but not so easy to parse). Defaults to true (simple).
	 *
	 * @return array the flattened array
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_children($group = false, $flatten = true)
	{
		if(!$group) $group = mod('session:owner');
		// is the group id valid?
		if(!$group = $this->met_is_group($group)) return false;

		$this->_load_cache_entry(0, $group);

		if($flatten) return $this->_flatten($this->cache[0][$group]);
		return $this->cache[0][$group];
	}


	/**
	 * @private
	 * _flatten() receives an array containing group hierarchy information (in a
	 * nested array structure). It returns a simpler, flat array containing
	 * nothing else but group ids. The hierarchy information (and the subarray
	 * structures) are dropped.
	 *
	 * Note that _flatten() requires the input array to contain group ids as
	 * keys, while it returns them as values.
	 *
	 * @param array $array the source array containing group hierarchy data
	 *
	 * @return array the flattened array
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _flatten($array)
	{
#echo 'flatten<br>';
#echo"flatten:array: ".nl2br(print_r($array, 1))."<br>";

		$buffer = array();

#		if(is_array($array))
#		{

#echo"keys: ".nl2br(print_r(array_keys($array), 1))."<br>";
		foreach($array as $key => $value)
		{
			$buffer[] = $key;
			if(is_array($value)) $buffer = array_merge($buffer, $this->_flatten($value));
		}
#		}
#echo"flatten_return: ".nl2br(print_r($buffer, 1))."<br>";
		return array_unique($buffer);
	}


	/**
	 * @private
	 * Creates a cache entry for the group $group. Assumes that no entry exists.
	 * The created cache entry is written into the db.
	 *
	 * Upward direction means that information about parents is gathered. Down
	 * will do the same for children.
	 *
	 * @param int $direction either 1 (up) or 0 (down). Any nonzero value will
	 * be treated as 1
	 * @param int $group a group id
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _create_cache_entry($direction, $group)
	{
#echo "create $group, direction:  $direction<br>";
		$direction = $direction ? 1 : 0; // we don't want direction to be anything else but 1 or 0

		// is the group id valid?
		if(!$this->met_is_group($group)) return false;

		$buffer = array();

		if($direction)
		{
			$sql = 'up';
			$relatives = $this->met_get_direct_parents($group);
		}
		else
		{
			$sql = 'down';
			$relatives = $this->met_get_direct_children($group);
		}

		foreach($relatives as $gid)
		{
#		echo "$gid.";
			$this->_load_cache_entry($direction, $gid);
			$buffer[$gid] = $this->cache[$direction][$gid];
		}

		// save the results
		mod('mysql:query', "INSERT INTO ~~cache_$sql (id, data) VALUES (#1, #2)", $group, serialize($buffer));
		$this->cache[$direction][$group] = $buffer;
#echo "output: ".nl2br(print_r($buffer, 1))."<br>";
	}


	/**
	 * @private
	 * Loads a dataset from the group cache in the database. If no cache entry
	 * for the group $group is found, it is created.
	 *
	 * Upward direction means that information about parents is gathered. Down
	 * will do the same for children.
	 *
	 * @param int $direction either 1 (up) or 0 (down). Any nonzero value will
	 * be treated as 1
	 * @param int $group a group id
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function _load_cache_entry($direction, $group)
	{
#echo "load $group, direction: $direction<br>";
		$direction = $direction ? 1 : 0; // we don't want direction to be anything else but 1 or 0

		// is the group id valid?
		if(!$this->met_is_group($group)) return false;

		$sql = ($direction) ? 'up' : 'down';

		if(!isset($this->cache[$direction][$group]))
		{
			if($row = mod('mysql:query_assoc', "SELECT data FROM ~~cache_$sql WHERE id=#1", $group))
			{
				$this->cache[$direction][$group] = unserialize($row['data']);
				return true;
			}

			$this->_create_cache_entry($direction, $group);
		}

		return true;
#echo "output: ".nl2br(print_r($this->cache[$direction][$group], 1))."<br>";
	}


	/**
	 * Returns the default module and method of the given group. Both name or id
	 * of a group can be used for $group. $group defaults to the current group
	 * id.
	 *
	 * @param mixed $group optional - the owning group. Defaults to the current
	 * group id
	 *
	 * @return string the module and method string as required by mod(), false
	 * on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_get_modmet($group = false)
	{
		if(!$group) $group = mod('session:owner');
		if(!$group = $this->met_is_group($group)) return false;

		if($result = mod('mysql:query_assoc', "SELECT modmet FROM ~~groups WHERE id=#1", $group)) return $result['modmet'];
		else return false;
	}


	/**
	 * Set the default module and method of the given group. Both name or id
	 * of a group can be used for $group, if nothing is specified, the
	 * default-modmet of the current group is changed.
	 *
	 * @param string $modmet the module and method string as required by mod()
	 * @param mixed $group optional - either the nameor the id of a group,
	 * defaults to the current group id.
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author Helmut Wandl <helmut@wandls.net>
	 */
	function met_set_modmet($modmet, $group = false)
	{
# $modmet should be verified somehow
# do is_modmet() when it gets available
		if(!$group) $group = mod('session:owner');
		if(!$group = $this->met_is_group($group)) return false;

		$result = mod('mysql:query', "UPDATE ~~groups SET modmet=#1 WHERE id=#2", $modmet, $group);

		if($result && mod('mysql:affected_rows') == 1) return true;
		else return false;
	}


	/**
	 * Returns the name of the group with the id $group
	 *
	 * @param int $group optional - if this is not set, the
	 * current group id is used
	 *
	 * @return string the name of the given group, false on error
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_name($group = false)
	{
		if(!$group) $group = mod('session:owner');

		if($group = $this->met_is_group($group)) return $this->namecache[$group];
		return false;
	}


	/**
	 * Returns the id of the default group
	 *
	 * @return int the id of the default group
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_default()
	{
		return $this->config('default_group');
	}

}

?>
