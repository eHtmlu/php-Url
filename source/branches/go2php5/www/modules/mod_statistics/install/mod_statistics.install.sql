-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 26, 2009 at 11:55 AM
-- Server version: 5.1.32
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tvguide`
--

-- --------------------------------------------------------

--
-- Table structure for table `tulebox_mod_statistics__hits`
--

CREATE TABLE IF NOT EXISTS `tulebox_mod_statistics__hits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) unsigned NOT NULL,
  `target` varchar(50) NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `useragent` int(11) unsigned NOT NULL,
  `new_session` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tulebox_mod_statistics__useragents`
--

CREATE TABLE IF NOT EXISTS `tulebox_mod_statistics__useragents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `useragent` varchar(512) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `useragent` (`useragent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

