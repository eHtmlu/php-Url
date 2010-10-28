		<link href="?mm=tabulator:skintpl&amp;s=default&amp;t=css.tpl.php" rel="stylesheet" type="text/css" />
		<?php
		
		$IE6=preg_match("/MSIE [0-6]/", mod("http:var", "S", "HTTP_USER_AGENT"));
		
		if ($IE6)
			echo '<link href="?mm=tabulator:skintpl&amp;s=default&amp;t=css_ie6.tpl.php" rel="stylesheet" type="text/css" />'."\n";
		
		?>
		<script src="?mm=tabulator:skintpl&amp;s=default&amp;t=clock.js" type="text/javascript"></script>
