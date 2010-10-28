<?php

$IE6=preg_match("/MSIE [0-6]/", mod("http:var", "S", "HTTP_USER_AGENT"));

echo '<?xml version="1.0" ?>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
	<head>
		<title>Test</title>
		<script src="<?php echo $this->path; ?>/js/prototype.js" type="text/javascript"></script>
		<script src="<?php echo $this->path; ?>/js/tabulator.js" type="text/javascript"></script>
		<?php echo $this->skinheader; ?>
	</head>
	<body>
		<div id="top">
		<h1<?php

		if (is_file($this->titleimage))
		{
			echo ' style="';
			if ($IE6 and strtolower(substr($this->titleimage, -4, 4)) == ".png")
				echo "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='".$this->titleimage."'); ";
			else
				echo "background-image:url(".$this->titleimage."); background-repeat:no-repeat; ";
			echo '"';
		}

		?>><span><?php echo $this->title; ?></span></h1>
		<div id="shortcut_menu">
		<?php

		foreach($this->shortcuts as $shortcut)
		{
		?>
			<a href="javascript:;" title="<?php echo $shortcut['title']; ?>"><span style="<?php

			if ($IE6 and strtolower(substr($shortcut['icon'], -4, 4)) == ".png")
				echo "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='".$shortcut['icon']."'); ";
			else
				echo "background-image:url(".$shortcut['icon']."); background-repeat:no-repeat; ";


			?>"><?php echo $shortcut['title']; ?></span></a>
		<?php
		}

		?>
		</div>
		<a id="addtab" href="javascript:;" onclick="tabulator.tabs.add('http://www.google.at', 'new', 'modules/mod_tabulator/test_img/icon2.png'); " title="open new tab"><span>open new tab</span></a>
		<div id="tabarea">
			<div class="JSON">
				<?php
				$t = array();
				if (count($this->tabs))
					foreach ($this->tabs as $tab)
						$t[] = '{"icon": "' . $tab['icon'] . '", "title": "' . $tab['title'] . '", "url": "' . $tab['url'] . '"}';

				$t = implode(', ', $t);

				echo '{"tabs": [' . $t . ']}';
				?>
			</div>
		</div>
		<div id="tabcontrols" class="hidden">
			<div class="button"></div>
		</div>
		<div id="control_panel">
			<form id="search" action="javascript:;"><input type="text" name="search" value="" /></form>
			<!--object title="Uhr" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="25" height="25" id="clock" align="middle">
			<param name="allowScriptAccess" value="sameDomain" />
			<param name="movie" value="<?php echo $this->path; ?>fla/clock.swf" /><param name="quality" value="high" /><param name="wmode" value="transparent" /><param name="bgcolor" value="" /><embed src="<?php echo $this->path; ?>fla/clock.swf" quality="high" wmode="transparent" width="25" height="25" name="clock" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object-->
			<div id="clock">
				<span id="clock_h"><span class="digit">00</span></span><span class="colon">:</span><span id="clock_m"><span class="digit">00</span></span><span class="colon">:</span><span id="clock_s"><span class="digit">00</span></span>
			</div>
			<a id="logout" href="javascript:;" title="logout"><span>logout</span></a>
		</div>
		</div>
		<div id="tabframes">
		<!--iframe src="http://localhost/_SELF/tv/www/index.php"></iframe-->
		<!--iframe src="http://www.google.at/images?svnum=10&hl=de&rlz=1B3GGGL_de___AT248&q=eierlegende+wollmilchsau&btnG=Bilder-Suche"></iframe-->
		</div>
		<!--div id="dev" style="border:#00f solid 1px; position:absolute; top:0px; left:0px; z-index:1000; ">test</div-->
	</body>
</html>
