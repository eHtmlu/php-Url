<?php

class mod_tabulator extends mod
{
	function mod_tabulator()
	{

	}

	function met_view()
	{
		$tpl=mod('tpl:new');
		$tpl->assign('path', $this->info('path'));
		$tpl->assign('title', "CiC");
		$tpl->assign('titleimage', $this->info('path')."/test_img/titleimage.png");

		$shortcuts=array(
			array('title' => 'sc1', 'icon' => $this->info('path')."/test_img/icon.png"),
			array('title' => 'sc2', 'icon' => $this->info('path')."/test_img/icon.png"),
			array('title' => 'sc3', 'icon' => $this->info('path')."/test_img/icon.png"),
			array('title' => 'sc4', 'icon' => $this->info('path')."/test_img/icon.png")
		);

		$tabs=array(
			array('url' => 'http://en.wikipedia.org', 'title' => 'wikipedia', 'icon' => $this->info('path')."/test_img/icon.png"),
//			array('url' => 'http://www.google.at', 'title' => 'google', 'icon' => $this->info('path')."test_img/icon.png", 'active' => 1),
			array('url' => 'about:blank', 'title' => 'CMS - 74-1-34 das ist ein Länge-Test', 'icon' => $this->info('path')."/test_img/icon.png"),
			array('url' => 'http://www.microsoft.com', 'title' => 'microsoft', 'icon' => $this->info('path')."/test_img/icon.png")
		);

		$tpl->assign('shortcuts', $shortcuts);
		$tpl->assign('tabs', $tabs);

		$tpl->assign('skinheader', $this->_get_skinheader($this->config('skin')));

		$tpl->display('main.tpl.php');
	}

	function met_skintpl()
	{
		if (!is_file($this->info('path').'/skins/'.mod('http:var', 'G', 's').'/'.mod('http:var', 'G', 't'))) return;

		$tpl=mod('tpl:new', $this->info('path').'/skins/'.mod('http:var', 'G', 's').'/');
		$tpl->assign('modpath', $this->info('path'));
		$tpl->assign('skinpath', $this->info('path').'/skins/'.mod('http:var', 'G', 's'));

		$tpl->display(mod('http:var', 'G', 't'));
     }

	function _get_skinheader($skin)
	{
		if (!is_file($this->info('path').'/skins/'.$skin.'/skinheader.tpl.php')) return '';

		$tpl=mod('tpl:new', $this->info('path').'/skins/'.$skin);

		$tpl->assign('modpath', $this->info('path'));
		$tpl->assign('skinpath', $this->info('path').'/skins/'.$skin);

		return $tpl->fetch('skinheader.tpl.php');
	}
}

?>
