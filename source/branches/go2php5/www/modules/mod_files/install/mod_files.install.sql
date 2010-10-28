#
# Structure for the `tulebox_mod_files_files` table :
#

CREATE TABLE `tulebox_mod_files__files` (
  `id` int(11) NOT NULL auto_increment,
  `folder` int(11) NOT NULL default '0',
  `filename` varchar(255) default NULL,
  `hash` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for the `tulebox_mod_files_folders` table :
#

CREATE TABLE `tulebox_mod_files__folders` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(5) default NULL,
  `path` varchar(17) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;