-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 12, 2012 at 05:27 AM
-- Server version: 5.0.51a-24+lenny5
-- PHP Version: 5.2.6-1+lenny16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dmrdb`
--
CREATE DATABASE `dmrdb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `dmrdb`;

-- --------------------------------------------------------

--
-- Table structure for table `ChangeLog`
--

CREATE TABLE IF NOT EXISTS `ChangeLog` (
  `DateTime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `DmrID` int(32) NOT NULL,
  `RecordType` char(10) NOT NULL,
  `FieldName` char(32) NOT NULL,
  `OldValue` char(32) NOT NULL,
  `NewValue` varchar(32) NOT NULL,
  `Event` char(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Network`
--

CREATE TABLE IF NOT EXISTS `Network` (
  `DmrID` int(7) NOT NULL,
  `Description` varchar(64) NOT NULL,
  `Publish` tinyint(1) NOT NULL default '1',
  `Mi5Publish` tinyint(1) NOT NULL,
  PRIMARY KEY  (`DmrID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NetworkAffilate`
--

CREATE TABLE IF NOT EXISTS `NetworkAffilate` (
  `Description` text NOT NULL,
  `Pin` text NOT NULL,
  `ID` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  FULLTEXT KEY `Description` (`Description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Prefix`
--

CREATE TABLE IF NOT EXISTS `Prefix` (
  `DmrID` char(64) NOT NULL,
  `Country` char(64) NOT NULL,
  `State` char(64) NOT NULL,
  `Group` char(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DMR-ID Geographic Numbering Scheme';

-- --------------------------------------------------------

--
-- Table structure for table `Repeater`
--

CREATE TABLE IF NOT EXISTS `Repeater` (
  `DmrID` int(8) NOT NULL,
  `Callsign` char(6) NOT NULL,
  `City` char(32) NOT NULL,
  `State` char(32) NOT NULL,
  `Country` char(32) NOT NULL,
  `Frequency` char(32) NOT NULL,
  `ColorCode` int(11) NOT NULL,
  `Offset` char(32) NOT NULL,
  `Assigned` char(32) NOT NULL,
  `Linked` char(32) NOT NULL,
  `Trustee` char(32) NOT NULL,
  `IpscNetwork` char(32) NOT NULL,
  `LastHeard` datetime NOT NULL,
  `Override` tinyint(1) NOT NULL default '0',
  `Ts1Online` tinyint(1) NOT NULL,
  `Ts2Online` tinyint(1) NOT NULL,
  `SourceNet` int(4) NOT NULL,
  `Publish` tinyint(1) NOT NULL default '0',
  `OverrideOnline` tinyint(1) NOT NULL default '0',
  `OverrideTs1Online` tinyint(1) NOT NULL default '0',
  `OverrideTs2Online` tinyint(1) NOT NULL default '0',
  `Role` binary(1) NOT NULL default '0' COMMENT '1 = Master 0 = Slave',
  `lat` text NOT NULL,
  `long` text NOT NULL,
  `Affilated` tinyint(1) NOT NULL default '0',
  `AffilatedNet` text NOT NULL,
  `Map` tinyint(1) NOT NULL default '0',
  `Short` text NOT NULL,
  PRIMARY KEY  (`DmrID`),
  KEY `Affilated` (`Affilated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DMR-MARC and other database of worldwide repeaters';

-- --------------------------------------------------------

--
-- Table structure for table `RepeaterLog`
--

CREATE TABLE IF NOT EXISTS `RepeaterLog` (
  `DmrID` int(11) NOT NULL,
  `SourceNet` int(11) NOT NULL,
  `DateTime` datetime NOT NULL,
  `Ts1Online` int(11) NOT NULL,
  `Ts2Online` int(11) NOT NULL,
  `PacketType` int(11) NOT NULL,
  `TimeSlotRaw` varchar(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Talkgroup`
--

CREATE TABLE IF NOT EXISTS `Talkgroup` (
  `Network` char(32) NOT NULL,
  `Region` char(32) NOT NULL,
  `DmrID` int(11) NOT NULL,
  `Assignment` char(32) NOT NULL,
  `Notes` char(32) NOT NULL,
  PRIMARY KEY  (`DmrID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `DateTime` datetime NOT NULL,
  `DmrID` int(11) NOT NULL,
  `Callsign` char(32) NOT NULL,
  `Name` char(32) NOT NULL,
  `City` char(32) NOT NULL,
  `State` char(32) NOT NULL,
  `Country` char(32) NOT NULL,
  `Radio` char(32) NOT NULL,
  `HomeRepeaterID` char(32) NOT NULL,
  `Remarks` char(32) NOT NULL,
  UNIQUE KEY `DmrID` (`DmrID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `UserLog`
--

CREATE TABLE IF NOT EXISTS `UserLog` (
  `Key` bigint(20) NOT NULL,
  `StartTime` datetime NOT NULL,
  `EndTime` datetime NOT NULL,
  `SourceNet` int(11) NOT NULL,
  `PacketType` int(2) NOT NULL,
  `RepeaterID` int(11) NOT NULL,
  `DmrID` int(11) NOT NULL,
  `DestinationID` int(11) NOT NULL,
  `Sequence` int(11) NOT NULL,
  `TimeSlot` int(11) NOT NULL,
  `GroupCall` int(11) NOT NULL,
  `PrivateCall` int(11) NOT NULL,
  `VoiceCall` tinyint(1) NOT NULL,
  `DataCall` int(11) NOT NULL,
  PRIMARY KEY  (`Key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
