#
# Structure for the `tulebox_mod_groups__assignment` table :
#

CREATE TABLE `tulebox_mod_groups__assignment` (
  `parent` int(11) default NULL,
  `child` int(11) default NULL,
  PRIMARY KEY  (`parent`,`child`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_groups__cache_down` table :
#

CREATE TABLE `tulebox_mod_groups__cache_down` (
  `id` int(11) NOT NULL default '0',
  `data` mediumblob,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_groups__cache_up` table :
#

CREATE TABLE `tulebox_mod_groups__cache_up` (
  `id` int(11) NOT NULL default '0',
  `data` mediumblob,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_groups__groups` table :
#

CREATE TABLE `tulebox_mod_groups__groups` (
  `id` int(11) NOT NULL auto_increment,
  `valid` tinyint(1) NOT NULL default '1',
  `namespaceid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `password_algorithm` varchar(50) default NULL,
  `modmet` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`namespaceid`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Data for the `tulebox_mod_groups__groups` table  (LIMIT 0,500)
#

INSERT INTO `tulebox_mod_groups__groups` (`id`, `valid`, `name`, `password`, `password_algorithm`, `modmet`) VALUES
  (1,1,'root','098f6bcd4621d373cade4e832627b4f6','md5','');

#
# Structure for the `tulebox_mod_groups__namespaces` table :
#

CREATE TABLE `tulebox_mod_groups__namespaces` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
