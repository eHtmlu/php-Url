<?php


class mod_files extends mod
{
	/**
	 * Constructor
	 */
	function mod_files()
	{
		// random number generator initialization - obsolete as of PHP 4.2.0
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float)$sec + ((float)$usec * 100000));

		// set the working directory to $this->config('filepath')
//		chdir($this->config('filepath'));
	}




################################################################################
################################################################################
################################################################################

function met_test()
{/*
echo'
<html>
<head><title></title></head>
<body>

	<form enctype="multipart/form-data" method="POST" action="?mod=files&met=handle_upload"><input name="blah" type="file"><input type="submit"></form>

</body>
</html>
';*/

echo $this->dump(60);

#echo $this->get_js();
//var_dump($this->create_new_folder());



#$this->create_new_folder("");

}

################################################################################
################################################################################
################################################################################


function get_js()
{
//	return mod('tpl:display', $this->info('filepath') . 'js.tpl');
}





/*	function met_delete($id)
	{
		if($path = $this->met_get_path($id))
		{
			if(unlink($path))
			{
				return mod('mysql:query', "DELETE FROM ~~files WHERE id='$id'");
			}
		}
		return false;
	}*/




# die hier sollte auch mal was gescheites zurückgeben..
	function met_handle_upload($name)
	{
#echo nl2br(print_r($_FILES, 1));
#		foreach($_FILES as $name => $f)
#		{
#			if(!$f['error'])
#			{
#				if(is_uploaded_file($f['tmp_name'])) $this->insert_file($f['tmp_name'], 1, $f['name']);
#			}
#			else
#			{
# do some error handling here
#			}
#		}
		if(isset($_FILES[$name]) && !$_FILES[$name]['error'])
		{
			if(is_uploaded_file($_FILES[$name]['tmp_name'])) return $this->insert_file($_FILES[$name]['tmp_name'], 1, $_FILES[$name]['name']);
		}
		return false;
	}



	/**
	 * Dump the contents of the file with the id $id to the output buffer. The
	 * right mime-content-type is sent as a header first.
	 *
	 * @param int $id id of the file
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_dump($id)
	{
		header('Content-Type: ' . mime_content_type('../' . $this->met_get_path($id)));
		readfile('../' . $this->met_get_path($id));
	}


	/**
	 * Get the direct path to the file with the id $id
	 *
	 * @param int $id id of the file
	 *
	 * @return mixed the path of the file on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function met_get_path($id)
	{
		if(!$result = mod('mysql:query_assoc', "SELECT fo.path, fi.hash FROM ~~files fi JOIN ~~folders fo ON (fo.id = fi.folder) WHERE fi.id=#1", $id)) return false;
		return $this->config('filepath') . '/' . $result['path'] . '/' . $result['hash'];
	}


	/**
	 * Insert a new file into the internal file structure. The file is moved to
	 * a random position and assigned a new, random (see $this->hashbit())
	 * filename. The original filename, as well as other relevant information is
	 * stored in the database.
	 *
	 * Depending on the optional flag $remove_original, the original file is
	 * either deleted or remains untouched (default).
	 *
	 * The optional parameter $override_filename can be used to override the
	 * file name the file actually has (e.g. after file uploads, when php has
	 * already created a temporary file name for the file but you still know the
	 * original one).
	 *
	 * @param string $filename path to the file, either absolute or relative to
	 * config('filepath')
	 * @param bool $remove_original optional - if true, the original file is
	 * deleted, if false (default), it remains untouched
	 * @param string $override_filename optional - a custom filename to be
	 * stored in the database
	 *
	 * @return bool int the file id on success, otherwise false
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function insert_file($filename, $remove_original = false, $override_filename = false)
	{
		if(!is_file($filename)) return false;

		if($override_filename)
		{
			$name = $override_filename;
			$e = pathinfo($override_filename, PATHINFO_EXTENSION);
		}
		else
		{
			$name = basename($filename);
			$e = pathinfo($filename, PATHINFO_EXTENSION);
		}

		$extension = ($e) ? ".$e" : '';

		// create an unique filename
		$slot = $this->next_slot();
		do
		{
			$new_filename = $slot[1] . '/' . $this->hashbit() . $extension;
		}
		while(is_file($this->config('filepath') . '/' . $new_filename));

		if($remove_original) $success = rename($filename, $this->config('filepath') . '/' . $new_filename);
		else $success = copy($filename, $this->config('filepath') . '/' . $new_filename);

		chmod($this->config('filepath') . '/' . $new_filename, octdec("0777"));

		if($success)
		{
			mod('mysql:query', "INSERT INTO ~~files (folder, filename, hash) VALUES (#1, #2, #3)", $slot[0], $name, basename($new_filename));
			return mod('mysql:insert_id');
		}
		else return false;
	}


	/**
	 * Finds the next folder with room for another entry and returns both its id
	 * and path in an array:
	 *
	 * 	array
	 * 	(
	 * 	    0 => id
	 * 	    1 => path
	 * 	)
	 *
	 * If all existing folders are full, a new one is created.
	 *
	 * @return array the next free slot
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function next_slot()
	{
		// to prevent errors, we have to check if there are any existing folders (this could be replaced by placing a first folder by a setup routine one day)
		list($count) = mod('mysql:query_row', "SELECT COUNT(*) AS c FROM ~~folders");
		if($count)
		{
			list($id, $path) = mod('mysql:query_row', "SELECT id, path FROM ~~folders ORDER BY id DESC LIMIT 1");
			list($count) = mod('mysql:query_row', "SELECT COUNT(*) AS c FROM ~~files WHERE folder=#1", $id);
			if($count < $this->config('max_files_per_folder')) return array($id, $path);
		}
		else $path = '';

		return $this->create_new_folder($path);
	}


	/**
	 * Count the entries in the given folder (files and subfolders, but not '.'
	 * and '..').
	 *
	 * @param string $folder path to the folder
	 *
	 * @return int the number of entries in $folder
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function count_entries_in_folder($folder)
	{
		if(!$handle = opendir($folder)) return false;
		$count = 0;
		while($file = readdir($handle))
		{
			if($file != '.' && $file != '..') $count++;
		}
		closedir($handle);
		return $count;
	}


	/**
	 * Creates a new folder for file storage. Starting from $path,
	 * create_new_folder() scans every parent folder (up to config('filepath'))
	 * until it finds one with fewer entries than
	 * config('max_files_per_folder'). From that starting point, it creates as
	 * many subfolders as needed to get back to the desired three-level depth.
	 * The path of the resulting folder is returned, relative to
	 * config('filepath').
	 *
	 * It is assumed that the starting folder passed in $path is already full.
	 *
	 * @param string $path starting point
	 *
	 * @return int the number of entries in $folder
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function create_new_folder($path)
	{
		// search for the first folder with room for another entry
		do
		{
			$path = substr($path, 0, strrpos($path, '/'));
			if($this->count_entries_in_folder($this->config('filepath') . '/' . $path) < $this->config('max_files_per_folder'))
			{
				// we've found our folder..
				break;
			}
		}
		while($path);

		// create subfolders until we are three levels deep
		do
		{
			do
			{
				$hash = $this->hashbit();
				$new_path = ($path) ? $path . '/' . $hash : $hash;
# this warning (folder exists) should be surpressed.. it doesn't work for me
				$success = @mkdir($this->config('filepath') . '/' . $new_path, octdec("0777"));
			}
			while(!$success);
			$path = $new_path;
		}
		while(strlen($path) < 17);

		mod('mysql:query', "INSERT INTO ~~folders (path) VALUES (#1)", $path);
		return array(mod('mysql:insert_id'), $path);
	}


	/**
	 * Calculates a random five-character sequence consisting of the characters
	 * in $character_pool (defaults to [a-z0-9]) to be used as hash for file and
	 * folder name anonymization.
	 *
	 * Taking 5 characters out of 36 makes 60466176 possible combinations.
	 * Assuming that the maximum number of files per folder will be lower than
	 * 100000 (which is VERY high), there will still always be more than
	 * 60000000 (6*10^7) unused combinations, which sould be enough to keep
	 * files from being detected by pure guessing.
	 *
	 * For platform compatibility reasons, only lowercase characters are used.
	 *
	 * @return string a random five-character hash
	 *
	 * @author Vedran Sajatovic <dummy_0153@gmx.at>
	 */
	function hashbit($character_pool = '0123456789abcdefghijklmnopqrstuvwxyz')
	{
		$hash = '';
		for($i = 0 ; $i < 5 ; $i++) $hash .= $character_pool[mt_rand(0, strlen($character_pool) - 1)];
		return $hash;
	}

}

?>
