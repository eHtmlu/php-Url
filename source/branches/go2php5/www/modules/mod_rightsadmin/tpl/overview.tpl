<table class="sortable" summary="list of rights" cellspacing="0">
	<thead>
		<tr>
			<th>id</th>
			<th>module</th>
			<th>name</th>
			<th>displayname</th>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="4">&nbsp;</td>
			<td>&raquo; <a href="?mm=rightsadmin&amp;t=edit">create new</a></td>
		</tr>
	</tfoot>
	<tbody>
<?php

for ($a=0; isset($this->rights[$a]); $a++){
?>
		<tr<?php echo ($a%2 != 0 ? ' class="second"' : ''); ?>>
			<td class="id"><?php echo $this->rights[$a]['id']; ?></td>
			<td class="module"><?php echo htmlspecialchars($this->rights[$a]['module']); ?></td>
			<td class="name"><?php echo htmlspecialchars($this->rights[$a]['name']); ?></td>
			<td class="displayname"><?php echo htmlspecialchars($this->rights[$a]['displayname']); ?></td>
			<td class="functions">
				&raquo; <a href="?mm=rightsadmin&amp;t=edit&amp;id=<?php echo $this->rights[$a]['id']; ?>">edit</a>
				&raquo; <a href="javascript:;" onclick="this.up('tr').addClassName('deleteselection'); if (confirm('Do you really want to delete the selected right?')) location.href='?mm=rightsadmin&amp;t=delete&amp;id=<?php echo $this->rights[$a]['id']; ?>'; else this.up('tr').removeClassName('deleteselection'); ">delete</a>
			</td>
		</tr>
<?php
}

?>
	</tbody>
</table>