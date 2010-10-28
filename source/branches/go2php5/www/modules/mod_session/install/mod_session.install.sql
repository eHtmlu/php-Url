-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 30, 2009 at 08:38 PM
-- Server version: 5.1.32
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tvguide`
--

-- --------------------------------------------------------

--
-- Table structure for table `tulebox_mod_session__sessions`
--

CREATE TABLE IF NOT EXISTS `tulebox_mod_session__sessions` (
  `sid` char(32) NOT NULL DEFAULT '',
  `owner` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `last_activity` int(11) NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

