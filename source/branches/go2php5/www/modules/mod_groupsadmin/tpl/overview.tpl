<table class="sortable" summary="list of groups" cellspacing="0">
	<thead>
		<tr>
			<th>id</th>
			<th>name</th>
			<th>modmet</th>
			<th>parents</th>
			<th>children</th>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="5">&nbsp;</td>
			<td>&raquo; <a href="?mm=groupsadmin&amp;t=edit">create new</a></td>
		</tr>
	</tfoot>
	<tbody>
<?php

///var_dump($this->groups);
//echo json_encode($this->groups);

for ($a=0; isset($this->groups['id'][$a]); $a++){
	echo '<tr'.($a%2 != 0 ? ' class="second"' : '').'>';
	echo '	<td class="id">'.$this->groups['id'][$a].'</td>';
	//echo '	<td>'.$this->groups['namespace'][$a].'</td>';
	echo '	<td class="name">'.($this->groups['namespace'][$a] ? '<strong>'.$this->groups['namespace'][$a].':</strong>' : '').$this->groups['name'][$a].'</td>';
	echo '	<td class="modmet">'.($this->groups['modmet'][$a] ? $this->groups['modmet'][$a] : '&nbsp;').'</td>';
	echo '	<td class="parents">'.count($this->groups['direct_parents'][$a]).'</td>';
	echo '	<td class="children">'.count($this->groups['direct_children'][$a]).'</td>';
	echo '	<td class="functions">';
	echo '		&raquo; <a href="?mm=groupsadmin&amp;t=edit&amp;id='.$this->groups['id'][$a].'">edit</a>';
	echo '		&raquo; <a href="?mm=groupsadmin&amp;t=rights&amp;id='.$this->groups['id'][$a].'">rights</a>';
	echo '		&raquo; <a href="javascript:;" onclick="this.up(\'tr\').addClassName(\'deleteselection\'); if (confirm(\'Do you really want to delete the selected group?\')) location.href=\'?mm=groupsadmin&amp;t=delete&amp;id='.$this->groups['id'][$a].'\'; else this.up(\'tr\').removeClassName(\'deleteselection\'); ">delete</a>';
	echo '	</td>';
	echo '</tr>';
}

?>
	</tbody>
</table>
