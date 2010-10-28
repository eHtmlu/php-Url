<?php

$path=str_replace("\\", "/", $path);
if (substr($path, -1, 1) == "/") $path=substr($path, 0, -1);
$path=explode("/", $path);
list($name)=array_splice($path, -1);
$path=implode("/", $path)."/";

$r=array();




/************\
*            *
*   ERRORS   *
*            *
\************/


if (!is_dir($path.$name)) $r['error'][]=
	"can't find module directory";

if (!preg_match("/^mod_[a-z0-9_]+$/", $name)) $r['error'][]=
	"\"".$name."\" is not a correct module directory name";

if (!is_file($path.$name."/".$name.".php")) $r['error'][]=
	"can't find module class file";

if (!mod_exists($name)) $r['error'][]=
	"can't load module";


/************\
*            *
*  WARNINGS  *
*            *
\************/


// about file

if (!is_file($path.$name."/".$name.".about.ini")) $r['warning'][]=
	"can't find module about file";

if (!$about=@parse_ini_file($path.$name."/".$name.".about.ini")) $r['warning'][]=
	"can't read module about file";

if ($about['title'] == "") $r['warning'][]=
	"can't find \"title\" attribute in the module about file";

if ($about['version'] == "") $r['warning'][]=
	"can't find \"version\" attribute in the module about file";

if ($about['date'] == "") $r['warning'][]=
	"can't find \"date\" attribute in the module about file";

if (strtotime($about['date']) == -1) $r['warning'][]=
	"the \"date\" attribute in the module about file has the wrong format";

if ($about['author'] == "") $r['warning'][]=
	"can't find \"author\" attribute in the module about file";

if (preg_match("/^.+ <.+@.+\..+>$/", $about['author']) == 0) $r['warning'][]=
	"the \"author\" attribute in the module about file has the wrong format";


// config file

if (!is_file($path.$name."/".$name.".config.ini")) $r['warning'][]=
	"can't find module config file";




return $r;

?>