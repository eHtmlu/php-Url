<?php echo '<?xml version="1.0" ?>' ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
	<head>
		<title>module admin</title>
		<link href="<?php echo $this->path; ?>/tpl/overview.tpl.css" rel="stylesheet" type="text/css" media="screen" />
	</head>
	<body>
		<div id="modules">
		<h1><span>modules</span></h1>
		<ol>
<?php

foreach ($this->m as $name => $v)
{
?>
			<li><a class="icon <?php echo $v['validator']['file']; ?>" href="?mm=moddocu:documentation&amp;modname=<?php echo $name; ?>" target="moddocuframe"><?php echo ($v['standard'] ? '<strong>' : '').$name.($v['standard'] ? '</strong>' : ''); ?></a></li>
<?php
}

?>
		</ol>
		</div>
		<iframe src="about:blank" name="moddocuframe"></iframe>
	</body>
</html>
