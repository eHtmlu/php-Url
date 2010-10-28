<form action="" method="post">
	<h1>edit right</h1>
	<fieldset>
		<label>
			<span class="label">id:</span>
			<?php echo ($this->right ? $this->right['id'] : 'new'); ?>
			<input type="hidden" name="id" value="<?php echo ($this->right ? $this->right['id'] : 'new'); ?>" />
		</label>
		<label>
			<span class="label">module:</span>
			<input type="text" name="module" value="<?php echo htmlspecialchars($this->right ? $this->right['module'] : ''); ?>" />
		</label>
		<label>
			<span class="label">name:</span>
			<input type="text" name="name" value="<?php echo htmlspecialchars($this->right ? $this->right['name'] : ''); ?>" />
		</label>
		<label>
			<span class="label">displayname:</span>
			<input type="text" name="displayname" value="<?php echo htmlspecialchars($this->right ? $this->right['displayname'] : ''); ?>" />
		</label>
	</fieldset>
	<fieldset class="control">
		<input type="submit" class="submit" value="save" />
		<input type="button" value="cancel" onclick="location.href='?mm=rightsadmin'; " />
	</fieldset>
</form>