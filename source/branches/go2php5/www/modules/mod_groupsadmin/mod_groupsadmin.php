<?php

class mod_groupsadmin extends mod
{
	function mod_groupsadmin()
	{
		if (!mod('groups:is_admin')){
			mod('http:status', 403);
			echo '403 Forbidden';
			exit;
		}
	}

	function met_default()
	{
		$this->met_main();
	}

	function met_main()
	{
		$this->tpl=mod('tpl:new','~~/tpl/','.tpl');

		//mod('groups:create',array('enduser','helmut'),'','tabulator',2);

		$t=mod('http:var', 'G', 't');

		if ($t == 'edit')
		{
			$group=false;

			$id=mod('http:var', 'G', 'id');

			if ($p=mod('http:var', 'P'))
			{
				if ($p['id'] == 'new')
				{
					$name=mod('http:var', 'P', 'name');
					if ($ns=mod('http:var', 'P', 'namespace')) $name=array($ns, $name);

					$id=$p['id']=mod('groups:create', $name, mod('http:var', 'P', 'password'));
				}

				if ($p['id'] == $id)
				{
					if ($p=mod('http:var', 'P', 'password'))
						mod('groups:set_password', $p, $id);

					mod('groups:set_modmet', mod('http:var', 'P', 'modmet'), $id);

					if (($links=mod('http:var', 'P', 'links')) && ($links=json_decode($links)))
					{
						foreach (get_object_vars($links) as $group)
						{
							if ($group->direct_parent === false) mod('groups:unlink', $id, $group->id);
							if ($group->direct_child === false) mod('groups:unlink', $group->id, $id);
						}
						foreach (get_object_vars($links) as $group)
						{
							if ($group->direct_parent === true) mod('groups:link', $id, $group->id);
							if ($group->direct_child === true) mod('groups:link', $group->id, $id);
						}
					}
				}
			}

			if ($id)
			{
				$group=array(
					'id' => $id,
					'name' => is_array($n=mod('groups:get_name', $id)) ? $n[1] : $n,
					'namespace' => is_array($n) ? $n[0] : '',
					'modmet' => mod('groups:get_modmet', $id)
				);
			}

			$this->tpl->assign('group', $group);
			$this->tpl->assign('links', $this->_get_links($id ? $id : false));
			//$this->tpl->assign('groups', $this->met_get_groups());
			$this->tpl->add_js('~~/js/groups.js');
			$this->tpl->assign('content', $this->tpl->fetch('edit'));
		}
		elseif ($t == 'delete')
		{
			if ($id=mod('http:var', 'G', 'id'))
				mod('groups:delete', $id);
			header('Location:?mm=groupsadmin');
			exit;
		}
		elseif ($t == 'getlinks')
		{
			$id=mod('http:var', 'G', 'id');
			echo json_encode($this->_get_links($id));
			exit;
		}
		elseif ($t == 'rights')
		{
			$this->_rights($id);
		}
		else
		{
			$this->tpl->assign('groups', $this->met_get_groups());
			$this->tpl->assign('content', $this->tpl->fetch('overview'));
			$this->tpl->add_js('~~/js/sortabletable.js');
		}

		$this->tpl->add_css('~~/css/main.css');
		$this->tpl->display('main');
	}

	function _get_links($group = false)
	{
		$groups=mod('groups:get_all');
		$result=array();

		foreach ($groups as $i => $id)
			$result[$id]=array(
				'id' => $id,
				'name' => is_array($n=mod('groups:get_name', $id)) ? $n[1] : $n,
				'namespace' => is_array($n) ? $n[0] : '',
				'modmet' => mod('groups:get_modmet', $id),
				'direct_parent' => ($group ? mod('groups:is_direct_parent', $id, $group) : false),
				'direct_child' => ($group ? mod('groups:is_direct_child', $id, $group) : false),
				'indirect_parent' => ($group ? mod('groups:is_indirect_parent', $id, $group) : false),
				'indirect_child' => ($group ? mod('groups:is_indirect_child', $id, $group) : false)
			);

		return $result;
	}

	function met_get_groups($v='all', $g=false)
	{
		$groups=mod('groups:get_'.$v, $g);

		$result=array(
			'id' => array(),
			'namespace' => array(),
			'name' => array(),
			'modmet' => array(),
			'direct_parents' => array(),
			'direct_children' => array(),
			'indirect_parents' => array(),
			'indirect_children' => array()
		);

		$a=0;
		foreach($groups as $group)
		{
			$result['id'][$a]=(INT) $group;
			$result['name'][$a]=is_array($n=mod('groups:get_name', $group)) ? $n[1] : $n;
			$result['namespace'][$a]=is_array($n) ? $n[0] : '';
			$result['modmet'][$a]=mod('groups:get_modmet', $group);
			$result['parents'][$a]=mod('groups:get_parents', $group);
			$result['children'][$a]=mod('groups:get_children', $group);
			$result['direct_parents'][$a]=mod('groups:get_direct_parents', $group);
			$result['direct_children'][$a]=mod('groups:get_direct_children', $group);

			$a++;
		}

		return $result;
	}

	function _rights()
	{
		if (!($id=mod('http:var', 'G', 'id')))
			mod('http:redirect', '?mm=groupsadmin');

		//var_dump(mod('rights:set', 'mailinglist', 'list1', true, $id));
		//exit;
		if ($rights=mod('http:var', 'P', 'rights')){
			foreach ($rights as $right => $value){
				$module=substr($right, 0, strpos($right,':'));
				$name=substr($right, strpos($right,':')+1);
				//var_dump($value,$module,$name,$id);
				mod('rights:remove_by_name', $module, $name, $id);
				switch($value){
					case 'true': mod('rights:set', $module, $name, true, $id); break;
					case 'false': mod('rights:set', $module, $name, false, $id); break;
				}
			}
			//var_dump($id,$p);
			//exit;
		}

		$rights=array();
		foreach (mod('rights:detect') as $right){
			if (!isset($rights[$right['module']]))
				$rights[$right['module']]=array();
			$right['setting']=mod('rights:get_setting', $right['module'], $right['name'], $id);
			//var_dump($right['module'], $right['name'], $right['setting']);
			//exit;

			$right['right']=mod('rights:get', $right['module'], $right['name'], $id);
			//var_dump($right['right']);
			$rights[$right['module']][]=$right;
		}
		//exit;
		//var_dump($rights);
		//exit;

		$this->tpl->add_js('~~/js/rights.js');
//		$this->tpl->add_js('~~/js/sortabletable.js');
		$this->tpl->assign('group', $id);
		$this->tpl->assign('rights', $rights);
		$this->tpl->assign('content', $this->tpl->fetch('edit_rights'));
	}
}

?>