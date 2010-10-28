<form action="" method="post">
	<h1>edit group</h1>
	<fieldset>
		<label>
			<span class="label">id:</span>
			<?php echo ($this->group ? $this->group['id'] : 'new'); ?>
			<input type="hidden" name="id" value="<?php echo ($this->group ? $this->group['id'] : 'new'); ?>" />
		</label>
		<label>
			<span class="label">namespace:</span>
			<?php echo ($this->group ? ($this->group['namespace'] ? '<strong>'.$this->group['namespace'].'</strong>' : '') : '<input type="text" class="namespace" name="namespace" value="" />'); ?>
		</label>
		<label>
			<span class="label">name:</span>
			<?php echo ($this->group ? $this->group['name'] : '<input type="text" name="name" value="" />'); ?>
		</label>
		<label>
			<span class="label">new password:</span>
			<input type="text" name="password" value="" />
		</label>
		<label>
			<span class="label">modmet:</span>
			<input type="text" name="modmet" value="<?php echo ($this->group ? $this->group['modmet'] : ''); ?>" />
		</label>
		<div class="label link">
			<span class="label">parents:</span>
			<select name="parents" size="5" multiple="multiple">
				<option value="">&nbsp;</option>
			</select>
			<span>
				<input class="parentsadd" type="button" value="&lt;&lt; add" />
				<input class="parentsremove" type="button" value="remove &gt;&gt;" />
			</span>
			<select name="noparents" size="5" multiple="multiple">
				<option value="">&nbsp;</option>
			</select>
		</div>
		<div class="label link">
			<span class="label">children:</span>
			<select name="children" size="15" multiple="multiple">
				<option value="">&nbsp;</option>
			</select>
			<span>
				<input class="childrenadd" type="button" value="&lt;&lt; add" />
				<input class="childrenremove" type="button" value="remove &gt;&gt;" />
			</span>
			<select name="nochildren" size="15" multiple="multiple">
				<option value="">&nbsp;</option>
			</select>
		</div>
		<input type="hidden" name="links" value="<?php echo htmlspecialchars(json_encode($this->links)); ?>" />
	</fieldset>
	<fieldset class="control">
		<input type="submit" class="submit" value="save" />
		<input type="button" value="cancel" onclick="location.href='?mm=groupsadmin'; " />
	</fieldset>
</form>