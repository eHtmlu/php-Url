<?php

$isadmin=mod('groups:is_admin', $this->group);
$groupname=mod('groups:get_name', $this->group);

?>
<form action="" method="post" class="rights">
	<h1>edit group rights of "<?php echo (is_array($groupname) ? $groupname[0].':'.$groupname[1] : $groupname); ?>"</h1>
<?php

if ($isadmin){
?>
	<fieldset class="admin">
		This group is <?php echo ($this->group == 1 ? 'the "root" group' : 'a descendant of the "root" group'); ?>. So you can not change the rights. This group has access anyway.
	</fieldset>
<?php
}

foreach ($this->rights as $mod => $rights){
	echo '<fieldset>';
	echo '<legend>'.$mod.'</legend>';
	echo '<div class="container">';
	echo '<table class="sortable" summary="" cellspacing="0" cellpadding="0" border="0">';
	echo '<thead><tr><th>name</th><th>display name</th><th>setting</th></tr></thead>';
	echo '<tbody>';

	foreach ($rights as $right){
		echo '<tr class="right right-'.($right['right'] ? 'true' : 'false').' setting-'.($right['setting']['own'] === true ? 'true' : ($right['setting']['own'] === false ? 'false' : 'inherit')).'"><td>';
		//echo '<div class="right right-'.($right['right'] ? 'true' : 'false').' setting-'.($right['setting']['own'] === true ? 'true' : ($right['setting']['own'] === false ? 'false' : 'inherit')).'">';
		echo ($right['name'] ? '<strong>'.htmlspecialchars($right['name']).'</strong>' : '');
		echo '</td><td>';
		echo ($right['displayname'] ? '<small>'.htmlspecialchars($right['displayname']).'</small>' : '');
		echo '</td><td>';
		//var_dump($right['setting']);
		if ($isadmin)
			echo '<span class="isadmin">root access</span>';
		else{
			echo '<label class="is'.($right['setting']['inherit'] || $isadmin ? 'true' : 'false').' inherit"><input name="'.htmlspecialchars('rights['.$mod.':'.$right['name']).']" type="radio" value="inherit"'.($right['setting']['own'] === null ? ' checked="checked"' : '').' /> inherit <small>('.($right['setting']['inherit'] ? 'allowed' : 'forbidden').')</small></label>';
			echo '<label class="istrue seperate-true"><input name="'.htmlspecialchars('rights['.$mod.':'.$right['name']).']" type="radio" value="true"'.($right['setting']['own'] === true ? ' checked="checked"' : '').' /> seperate <small>allowed</small></label>';
			echo '<label class="isfalse seperate-false"><input name="'.htmlspecialchars('rights['.$mod.':'.$right['name']).']" type="radio" value="false"'.($right['setting']['own'] === false ? ' checked="checked"' : '').' /> seperate <small>forbidden</small></label>';
		}
		//echo '</div>';
		echo '</td></tr>'."\r\n";
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</fieldset>';
}

?>
	<fieldset class="control">
		<input type="submit" class="submit" value="save" />
		<input type="button" value="cancel" onclick="location.href='?mm=groupsadmin'; " />
	</fieldset>
</form>