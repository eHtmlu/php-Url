<?php echo '<?xml version="1.0" ?>' ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
	<head>
		<title>documentation of <?php echo $this->modname; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<?php echo $this->modpath; ?>/tpl/docu.tpl.css" rel="stylesheet" type="text/css" media="screen" />
	</head>
	<body>
		<h1>documentation of <?php echo $this->modname; ?></h1>
		<?php

if (count($this->f['undefined']))
echo '<div class="warning">WARNING: There are one or more undefined functions. A function name have to beginn with "met_" for public function or with "_" for private function.</div>';

foreach($this->f as $funcgroup => $group)
{
	echo '<h2>'.$funcgroup.' ('.count($group).')</h2>';
	foreach($group as $name => $c)
	{
		$param=array();
		$version='undefined';
		$return=array();
		$author=array();
		$comments=array();
		$todo=array();
		$example=array();

		if (is_array($c))
		foreach ($c as $a)
		{
			if (preg_match("/^@version[\s]+(.+)$/", $a, $p)) $version=$p[1];
			elseif (preg_match("/^@param[\s]+([^\s]+)[\s]+(\\$[^\s]+)[\s]+(.+)$/s", $a, $p)) $param[]=array($p[1], $p[2], $p[3]);
			elseif (preg_match("/^@return[\s]+([^\s]+)[\s]+(.+)$/s", $a, $p)) $return=array($p[1], $p[2]);
			elseif (preg_match("/^@author[\s]+(.+)$/", $a, $p)) $author[]=$p[1];
			elseif (preg_match("/^@todo[\s]+(.+)$/s", $a, $p)) $todo[]=$p[1];
			elseif (preg_match("/^@example[\s]+(.+)$/s", $a, $p)) $example[]=$p[1];
			else $comments=array_merge($comments, explode("\n\n", $a));
		}
		//print_r($comments);

		echo '<div class="dfnheader" id="func_'.htmlspecialchars($name).'" onclick="var obj=document.getElementById(\'comment_\'+this.id); if (obj.style.display == \'block\') obj.style.display=\'none\'; else obj.style.display=\'block\'; ">';
		echo "<h3>".htmlspecialchars($name)."</h3>";
		echo '<span class="firstline">'.$comments[0].'</span>';
		echo (count($todo) ? '<span class="todo">todo('.count($todo).')</span>' : '');
		echo "</div>";

		echo '<div class="dfn" id="comment_func_'.htmlspecialchars($name).'">';
		echo "<span>version ".htmlspecialchars($version)."</span>";

		if (count($param))
		{
			echo '<div class="parameters"><h4>parameters:</h4><ol>';

			foreach ($param as $p)
			{
				echo '<li><p><var><strong>'.htmlspecialchars($p[1]).'</strong>('.htmlspecialchars($p[0]).')</var> '.str_replace('TRUE', '<span class="true">TRUE</span>', str_replace('FALSE', '<span class="false">FALSE</span>', str_replace("\n\n", "</p><p>", htmlspecialchars($p[2])))).'</p></li>';
			}

			echo '</ol></div>';
		}

		if (count($return))
		{
			echo '<h4>return:</h4><div class="return"><p><var>('.htmlspecialchars($return[0]).')</var> '.str_replace('TRUE', '<span class="true">TRUE</span>', str_replace('FALSE', '<span class="false">FALSE</span>', str_replace("\n\n", "</p><p>", htmlspecialchars($return[1])))).'</p></div>';
		}

		echo '<h4>comments:</h4><div class="comments">';
		echo "<p>".str_replace('TRUE', '<span class="true">TRUE</span>', str_replace('FALSE', '<span class="false">FALSE</span>', str_replace("\n\n", "</p><p>", htmlspecialchars(implode("\n\n", $comments)))))."</p>";
		echo '</div>';

		if (count($example))
		{
			//$example=array_map('htmlspecialchars', $example);
			echo '<h4>examples:</h4>';//<div class="example"><ul><li><p>'.str_replace("\n\n", "</p><p>", implode("</p></li><li><p>", $todo)).'</p></li></ul></div>';
			echo '<div class="examples"><code>'.str_replace("\n", "<br />", str_replace(" ", "&nbsp;", str_replace("	", "&nbsp;&nbsp;&nbsp;&nbsp;", implode("</code><code>", $example)))).'</code></div>';
		}

		if (count($todo))
		{
			$todo=array_map('htmlspecialchars', $todo);
			echo '<h4>todo:</h4><div class="todo"><ul><li><p>'.str_replace("\n\n", "</p><p>", implode("</p></li><li><p>", $todo)).'</p></li></ul></div>';
		}

		echo '<h4>authors:</h4><cite>'.preg_replace("/(?!^|[^a-zA-Z0-9\.+&\_-])([a-zA-Z0-9\.+&\_-]+@[a-zA-Z0-9\.\_-]{2,}\.[a-zA-Z0-9]{2,4})(?=[^a-zA-Z0-9]|$)/", '<a href="mailto:$1">$1</a>', htmlspecialchars(implode(", ", $author))).'</cite>';
		echo '</div>';
	}
}

		?>
	</body>
</html>
