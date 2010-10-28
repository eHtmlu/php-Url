-- 
-- Tabellenstruktur für Tabelle `tulebox_mod_redirect__conditions`
-- 

CREATE TABLE `tulebox_mod_redirect__conditions` (
  `id` int(11) NOT NULL auto_increment,
  `redirect_id` int(11) default NULL,
  `keyword` varchar(255) collate latin1_general_ci default NULL,
  `arrays` varchar(5) collate latin1_general_ci default NULL,
  `name` varchar(100) collate latin1_general_ci default NULL,
  `regex` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `redirection_id` (`redirect_id`,`keyword`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `tulebox_mod_redirect__redirections`
-- 

CREATE TABLE `tulebox_mod_redirect__redirections` (
  `id` int(11) NOT NULL auto_increment,
  `order` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `mod` varchar(255) collate latin1_general_ci default NULL,
  `name` varchar(255) collate latin1_general_ci default NULL,
  `goto` varchar(255) collate latin1_general_ci NOT NULL,
  `display_name` varchar(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `module` (`mod`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci PACK_KEYS=0;
        