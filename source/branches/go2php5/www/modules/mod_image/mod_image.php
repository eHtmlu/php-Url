<?php

class mod_image extends mod
{
	/**
	 * Constructor
	 */
	function mod_image()
	{
	}


	/**
	 * Calculates the width and height of the given image according to the
	 * specified maximal dimensions, preserving the original aspect ratio. If
	 * one of the parameters max_x of max_y is set to 0 (default), no size limit
	 * will be applied along this axis.
	 * The function returns an array with the following structure:
	 *
	 *	array
	 *	(
	 *		0 => calculated width
	 *		1 => calculated height
	 *		2 => original width
	 *		3 => original height
	 *		'flag' => image type flag if the given source is a filename (as returned by getimagesize()) otherwise FALSE
	 *		'html' => 'width="calculated width" height="calculated height"'
	 *	)
	 *
	 * @param  mixed  source  filename or resource of the image
	 * @param  int    max_x   maximal desired width
	 * @param  int    max_y   maximal desired height
	 *
	 * @return	mixed	an array containing the new image size, or false on
	 * error
	 *
	 * @version	1.1
	 * @author	Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_constrain_size($source, $max_x = 0, $max_y = 0)
	{
		if (is_resource($source) && get_resource_type($source) == 'gd'){
			$x=imagesx($source);
			$y=imagesy($source);
			$flag=false;
		}
		elseif (is_file($source)){
			list($x, $y, $flag) = getimagesize($source);
		}
		else
			return false;

		// get the scaling factor along each axis
		$scale_x = ($max_x && $max_x < $x) ? ($max_x / $x) : 1;
		$scale_y = ($max_y && $max_y < $y) ? ($max_y / $y) : 1;

		// select the smaller one
		$scale = ($scale_x < $scale_y) ? $scale_x : $scale_y;

		// apply the scaling factor if necessary (<1)
		$x_new = ($scale < 1) ? round($x * $scale) : $x;
		$y_new = ($scale < 1) ? round($y * $scale) : $y;

		// avoid problems with dimensions equal to 0
		if ($x_new == 0) $x_new=1;
		if ($y_new == 0) $y_new=1;

		$html = 'width="' . $x_new . '" height="' . $y_new . '"';

		return array($x_new, $y_new, $x, $y, 'flag' => $flag, 'html' => $html);
	}


	function met_create_img_tag($src, $alt = false, $width = false, $height = false, $alias = false, $title = false)
	{
		if ($dimensions = $this->met_constrain_size($src, $width, $height)) {
			if ($title) $title = 'title="' . $title . '" ';

			switch ($dimensions['flag']) {
				case 4:
				case 13:
					$param=($alias && ($clicktag=urlencode($alias)) ? '?clicktag='.$clicktag.'&clickTag='.$clicktag.'&ClickTag='.$clicktag : '');
					$path=$this->info('path') . '/tpl/container.swf?path=' . urlencode($src . $param);
					$tag='<object type="application/x-shockwave-flash" data="' . $path . '" ' . $dimensions['html'] . '><param name="movie" value="' . $path . '" /><param name="wmode" value="transparent" /><param name="allowFullScreen" value="true" />'.$alt.'</object>';
					break;
				default:
					$tag='<img src="' . $src . '" alt="' . $alt . '" ' . $title . $dimensions['html'] . ' />';
			}

			return ($alias ? '<a href="'.$alias.'">'.$tag.'</a>' : $tag);
		}

		return false;
	}


	/**
	 * Create a thumbnail of an image. The desired maximum width and height can
	 * be specified, if no value or 0 is passed, no size limit will be applied.
	 *
	 * Returns the resource (or TRUE if destination is a filename) on success, otherwise false.
	 *
	 * @param  mixed  source            filename or resource of the source image
	 * @param  mixed  destination       filename or resource of the destination image or FALSE for a new resource with the computed destination dimensions
	 * @param  int    max_width         maximal desired width
	 * @param  int    max_height        maximal desired height
	 * @param  bool   create_unchanged  on TRUE creates the thumbnail even if the original is small enough (default is FALSE)
	 *
	 * @return	mixed	resource or TRUE on success, otherwise FALSE
	 *
	 * @version	1.1
	 * @author	Vedran Sajatovic <dummy_0153@gmx.at>
	 * @author	Helmut Wandl <helmut@wandls.net>
	 */
	function met_create_thumbnail($source, $destination = false, $max_width = 0, $max_height = 0, $create_unchanged=false)
	{
		// we need the gd extension to perform this task
		if(!extension_loaded('gd')) return false;

		// calculate proper thumbnail size
		if (!($properties = $this->met_constrain_size($source, $max_width, $max_height)))
			return false;

		// look if the thumbnail would be any smaller than the original
		if (!$create_unchanged && $properties[0] >= $properties[2] && $properties[1] >= $properties[3])
			return false;

		// check if our image format is supported and load the source image
		if (is_resource($source) && get_resource_type($source) == 'gd') {
			$srcimg=$source;
		}
		else {
			switch ($properties['flag']){
				case IMAGETYPE_GIF:  $srcimg = imagecreatefromgif($source); break;
				case IMAGETYPE_JPEG: $srcimg = imagecreatefromjpeg($source); break;
				case IMAGETYPE_PNG:  $srcimg = imagecreatefrompng($source); break;
				default: return false;
			}
		}

		if (is_resource($destination) && get_resource_type($destination) == 'gd')
			$dstimg=$destination;
		else
		{
			// create our thumbnail
			$dstimg = imagecreatetruecolor($properties[0], $properties[1]);

			// support transparent png files
			imagealphablending($dstimg, false);
			imagefilledrectangle($dstimg, 0, 0, $properties[0], $properties[1], imagecolorallocatealpha($dstimg, 0, 0, 0, 127));
			imagesavealpha($dstimg, true);

			// support transparent gif files
			if (imagecolortransparent($srcimg) !== -1)
			{
				$t=imagecolorsforindex($srcimg,imagecolortransparent($srcimg));
				$n=imagecolortransparent($dstimg, imagecolorallocatealpha($dstimg, $t['red'], $t['green'], $t['blue'], $t['alpha']));
				imagefilledrectangle($dstimg, 0, 0, $properties[0], $properties[1], $n);
			}
		}

		imagecopyresampled($dstimg, $srcimg, 0, 0, 0, 0, $properties[0], $properties[1], $properties[2], $properties[3]);

		if (!is_resource($source))
			imagedestroy($srcimg);

		if ($destination && !is_resource($destination))
		{
			// save the thumbnail
			switch ($properties['flag']){
				case IMAGETYPE_GIF: imagegif($dstimg, $destination); break;
				case IMAGETYPE_JPEG: imagejpeg($dstimg, $destination); break;
				case IMAGETYPE_PNG: imagepng($dstimg, $destination); break;
			}
			imagedestroy($dstimg);

			return true;
		}
		else
			return $dstimg;
	}
}

?>
