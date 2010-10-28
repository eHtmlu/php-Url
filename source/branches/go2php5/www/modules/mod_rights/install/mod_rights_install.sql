#
# Structure for the `tulebox_mod_rights__cache_give` table :
#

CREATE TABLE `tulebox_mod_rights__cache_give` (
  `gid` int(11) NOT NULL default '0',
  `invalidates` int(11) NOT NULL default '0',
  `data` mediumblob,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_rights__cache_have` table :
#

CREATE TABLE `tulebox_mod_rights__cache_have` (
  `gid` int(11) NOT NULL default '0',
  `invalidates` int(11) NOT NULL default '0',
  `data` mediumblob,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_rights__give` table :
#

CREATE TABLE `tulebox_mod_rights__give` (
  `id` int(11) NOT NULL auto_increment,
  `rid` int(11) default NULL,
  `gid` int(11) default NULL,
  `value` int(11) default NULL,
  `target` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_rights__have` table :
#

CREATE TABLE `tulebox_mod_rights__have` (
  `id` int(11) NOT NULL auto_increment,
  `rid` int(11) default NULL,
  `gid` int(11) default NULL,
  `value` int(11) default NULL,
  `time_start` int(11) NOT NULL default '0',
  `time_end` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Structure for the `tulebox_mod_rights__rights` table :
#

CREATE TABLE `tulebox_mod_rights__rights` (
  `id` int(11) NOT NULL auto_increment,
  `module` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `displayname` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
