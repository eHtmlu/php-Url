<?php

class mod_moddocu extends mod
{
	function mod_moddocu()
	{
		$this->standardmod=array(
		'mod_session' => 1,
		'mod_http' => 1,
		'mod_groups' => 1,
		'mod_rights' => 1,
		'mod_mysql' => 1,
		'mod_tpl' => 1,
		'mod_error' => 1
		);
	}

	function met_overview()
	{
		$dir=config('module_path');


		if (is_dir($dir) and ($dh=opendir($dir)))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (substr($file, 0, 1) == '.') continue;

				//if (($validator=$this->met_modvalidator($dir.'/'.$file)) === true)
				$mods[$file]=array('validator' => $this->met_modvalidator($dir.'/'.$file));
				//else
				//$mods[substr($file, 4)]=array('error' => $error);
				//echo "filename: $file : filetype: " . filetype($dir.'/'.$file) . "\n";
			}
			closedir($dh);
		}

		ksort($mods);
		//print '<style type="text/css">html,body { padding:0px; margin:0px; border-style:none; } body,td,th { font-family:verdana; font-size:11px; } td { border-bottom:#eef solid 1px; border-right:#ddf solid 1px; padding:4px; } th { border-bottom:#000 solid 2px; padding:2px; }</style>';
		//print '<table style="width:100%;" cellpadding="0" cellspacing="0" border="0">';
		//print '<tr><th>module</th><th>title</th><th>version</th><th>date</th><th>author</th><th>config</th></tr>';

		foreach($mods as $name => $mod)
		{
			$date='';
			$title='';
			$v='';
			$author='';
			$config='';
			$validator='';
			$modname=$name;
			if (substr($modname, 0, 4) == "mod_") $modname=substr($modname, 4);

			if (count($mod['validator']['error']) === 0)
			{
				if (is_file(mod($modname.':info', 'path').'/'.$modname.'.about.ini'))
				{
					$date=mod($modname.':about', 'date');
					if ($date) $date=date('d. F Y',strtotime($date));
					$v=$this->version_optimize(mod($modname.':about', 'version'));
					$author=mod($modname.':about', 'author');
					$title=mod($modname.':about', 'title');
				}
				if (is_file(mod($modname.':info', 'path').'/'.$modname.'.config.ini'))
					if (count(mod($modname.':config'))) $config='<a href="?mm=moddocu:modconfig_overview&amp;n='.$modname.'">edit</a>';
			}

			$validatorfile="valid";
			if (count($mod['validator']['warning'])) $validatorfile="warning";
			if (count($mod['validator']['error'])) $validatorfile="error";

			$validator_text=count($mod['validator']['error'])." ERRORS - ".count($mod['validator']['warning'])." WARNINGS";

			$c=($c == '#eef' ? $c='#fff' : $c='#eef');

			$modules[$name]=array(
				'validator' => array('file' => $validatorfile, 'text' => $validator_text),
				'standard' => $this->standardmod[$name],
				);

			//print '<tr style="background-color:'.$c.'; "><td><img src="'.$this->info('path').'/pic/'.$validatorfile.'" alt="'.$validator_text.'" title="'.$validator_text.'" width="15" height="15" border="0" /><strong'.($this->standardmod[$name] ? ' style="color:#66f;"' : '').'> '.$name.'&nbsp;</strong><a href="?mm=moddocu:documentation&amp;modname='.$name.'">docu</a></td><td>'.$title.'&nbsp;</td><td>'.$v.'&nbsp;</td><td>'.$date.'&nbsp;</td><td>'.htmlentities($author).'&nbsp;</td><td>'.$config.'&nbsp;</td></tr>';
		}

		//print '</table>';
		//print_r($mods);

		$tpl=mod('tpl:new','~~/tpl','');

		$tpl->assign('path', $this->info('path'));
		$tpl->assign('m', $modules);
		$tpl->display('overview.tpl.php');
	}

	function met_modvalidator($path)
	{
		return require("modvalidator/modvalidator.php");
	}

	function met_documentation($modname=false)
	{
		if ($modname === false and !($modname=mod('http:var', 'G', 'modname'))) return false;
		//$f=file_get_contents(mod($modname.':info', 'path'));

		$functions=array('constructor' => array(), 'public' => array(), 'private' => array(), 'undefined' => array());

		$path=mod($modname.':info', 'path').'/'.mod($modname.':info', 'name').".php";

		$t=token_get_all(file_get_contents($path));

			for($a=0; $a < count($t); $a++)
			{
				$token_name=token_name((INT) $t[$a][0]);

				if ($token_name == 'T_DOC_COMMENT' or $token_name == 'T_COMMENT' and substr($t[$a][1], 0, 3) == '/**')
				{
					// remove asterisks
					$c=preg_replace("/(^\/\*\*|\*\/$)/", "", $t[$a][1]);
					$c=preg_replace("/(\r\n|\r|\n)[\f\t ]*(\* |\*|)/", "\n", $c);

					// split to areas
					$c=preg_split("/\n(?=@param|@return|@version|@author|@todo|@example)/", $c);

					// remove dispensable breaks
					for ($b=0; $b < count($c); $b++)
					{
						if (strpos($c[$b], '@example') !== 0)
						$c[$b]=preg_replace("/(?<=[^\n])[\f\t ]*\n[\f\t ]*(?!@param|@return|@version|@author|@todo|@example|\n)/", " ", $c[$b]);
					}

					// remove dispensable whitespace
					$c=array_map("trim", $c);

					$comments[]=$c;
				}

				if ($token_name == 'T_FUNCTION')
				{
					while(token_name((INT) $t[$a][0]) != 'T_STRING') $a++;

					if (is_metname($t[$a][1],1))
						$functions['public'][$t[$a][1]]=$c;//omments[count($comments)-1];
					elseif (substr($t[$a][1], 0, 1) == "_")
						$functions['private'][$t[$a][1]]=$c;//omments[count($comments)-1];
					elseif ($t[$a][1] == mod($modname.':info', 'name'))
						$functions['constructor'][$t[$a][1]]=$c;//omments[count($comments)-1];
					else
						$functions['undefined'][$t[$a][1]]=$c;//omments[count($comments)-1];

					$c="";
				}

				//$t[$a][0]=token_name($t[$a][0])." (".$t[$a][0].")";
			}


		ksort($functions['public']);
		ksort($functions['private']);
		ksort($functions['undefined']);

		$tpl=mod('tpl:new','~~/tpl',''); //, $this->info('path')."/tpl");

		$tpl->assign('modpath', $this->info('path'));
		$tpl->assign('modname', mod($modname.':info', 'name'));
		$tpl->assign('f', $functions);
		$tpl->display('docu.tpl.php');
/*
		foreach($functions as $type => $type_functions)
		{
			print "<h1>$type</h1>";

			foreach($type_functions as $name => $comment)
			{
				print '<h2 id="f'.$name.'" onclick="var o=document.getElementById(\'c_\'+this.id); if (o.style.display != \'block\') o.style.display=\'block\'; else o.style.display=\'none\';">'.$name.' ('.$type.')</h2><strong>'.$comment[0].'</strong><div style="display:none; " id="c_f'.$name.'">';

				for ($a=1; $a < count($comment); $a++)
				{
					//print $comment[$a]."\n";
					if (preg_match("/^@param[\s]+([^\s]+)[\s]+(\\$[^\s]+)[\s]+(.+)$/", $comment[$a], $hits))
					//print_r($hits);
					print '<div class="parameter"><strong>parameter:</strong> '.$hits[2].' ('.$hits[1].')<br />'.nl2br($hits[3])."</div>";
					else
					print nl2br($comment[$a])."<br />\n";
				}

				print '</div>';
			}
		}
*/
	}

	function met_modconfig_overview()
	{
		$arr=mod(mod('http:var', 'G', 'n').':config');
		print '<html><head><title></title></head><body>';
		print '<form><table><td colspan="2" style="text-align:left; border-bottom:#000 solid 2px; "><strong>'.mod('http:var', 'G', 'n').'</strong> '.$this->version_optimize(mod(mod('http:var', 'G', 'n').':about', 'version')).'</td>';

		foreach ($arr as $n => $v)
		{
			print '<tr><td>'.$n.': </td><td><input type="text" name="'.$n.'" value="'.$v.'" /></td></tr>';
		}

		//print_r($arr);
		print '</table></form>';
		print '</body></html>';
	}

	function version_optimize($v)
	{
		$v=preg_replace("/[_\-+]/", ".", $v);
		$v=preg_replace("/(([0-9])([^0-9.]))/", "$2.$3", $v);
		$v=preg_replace("/(([^0-9.])([0-9]))/", "$2.$3", $v);
		return $v;
	}
}

?>
