<?php

class mod_rightsadmin extends mod
{
	function mod_rightsadmin()
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
		$tpl=mod('tpl:new','~~/tpl/','.tpl');

		$t=mod('http:var', 'G', 't');

		if ($t == 'edit')
		{
			$right=false;

			$id=mod('http:var', 'G', 'id');

			if ($p=mod('http:var', 'P'))
			{
				if ($p['id'] == 'new')
				{
					$id=$p['id']=mod('rights:create', mod('http:var', 'P', 'module'), mod('http:var', 'P', 'name'), mod('http:var', 'P', 'displayname'));
				}

				if ($p['id'] == $id)
				{
					mod('rights:update', $id, $p['module'], $p['name'], $p['displayname']);
				}

			}

			if ($id)
			{
				//mod('rights:detect', $id);
				$right=mod('rights:detect', $id);
			}

			$tpl->assign('right', $right);
			//$tpl->assign('groups', $this->met_get_groups());
			$content=$tpl->fetch('edit');
		}
		elseif ($t == 'delete')
		{
			if ($id=mod('http:var', 'G', 'id'))
				mod('rights:delete', $id);
			header('Location:?mm=rightsadmin');
			exit;
		}
		else
		{
			$tpl->assign('rights', mod('rights:detect'));
			$content=$tpl->fetch('overview');
			$tpl->add_js('~~/js/sortabletable.js');
		}

		$tpl->assign('content', $content);

		$tpl->add_css('~~/css/main.css');
		$tpl->add_js('~~/js/element_storage.js');
		$tpl->display('main');

//		$tpl->assign('rights', mod('rights:detect'));
//		$tpl->assign('content', $tpl->fetch('overview'));

//		$tpl->add_js('~~/js/sortabletable.js');
//		$tpl->add_js('~~/js/element_storage.js');
//		$tpl->add_css('~~/css/main.css');
//		$tpl->display('main');
	}
}

?>