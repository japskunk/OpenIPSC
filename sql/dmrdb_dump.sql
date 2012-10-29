-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 29, 2012 at 03:45 PM
-- Server version: 5.0.51a-24+lenny5-log
-- PHP Version: 5.2.6-1+lenny16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `dmrdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `ChangeLog`
--
-- Creation: Sep 15, 2012 at 09:54 PM
-- Last update: Oct 29, 2012 at 04:00 AM
--

DROP TABLE IF EXISTS `ChangeLog`;
CREATE TABLE IF NOT EXISTS `ChangeLog` (
  `DateTime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `DmrID` int(32) NOT NULL,
  `RecordType` char(10) NOT NULL,
  `FieldName` char(32) NOT NULL,
  `OldValue` char(64) NOT NULL,
  `NewValue` varchar(64) NOT NULL,
  `Event` char(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LastHeard`
--
-- Creation: Oct 29, 2012 at 07:17 PM
-- Last update: Oct 29, 2012 at 07:45 PM
--

DROP TABLE IF EXISTS `LastHeard`;
CREATE TABLE IF NOT EXISTS `LastHeard` (
  `DmrID` int(11) NOT NULL,
  `StartTime` datetime NOT NULL,
  `SourceNet` int(11) NOT NULL,
  `TimeSlot` int(1) NOT NULL,
  `RepeaterID` int(11) NOT NULL,
  `DestinationID` int(11) NOT NULL,
  PRIMARY KEY  (`DmrID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Network`
--
-- Creation: Sep 20, 2012 at 10:23 PM
-- Last update: Oct 29, 2012 at 07:45 PM
--

DROP TABLE IF EXISTS `Network`;
CREATE TABLE IF NOT EXISTS `Network` (
  `DmrID` int(7) NOT NULL,
  `Description` varchar(64) NOT NULL,
  `Publish` tinyint(1) NOT NULL default '1',
  `Mi5Publish` tinyint(1) NOT NULL,
  `DateTime` datetime NOT NULL,
  PRIMARY KEY  (`DmrID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NetworkAffilate`
--
-- Creation: Aug 18, 2012 at 09:52 PM
-- Last update: Aug 18, 2012 at 09:52 PM
-- Last check: Sep 12, 2012 at 09:36 AM
--

DROP TABLE IF EXISTS `NetworkAffilate`;
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
-- Creation: May 30, 2012 at 10:44 AM
-- Last update: Oct 18, 2012 at 04:00 AM
-- Last check: Sep 12, 2012 at 09:35 AM
--

DROP TABLE IF EXISTS `Prefix`;
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
-- Creation: Oct 02, 2012 at 02:10 PM
-- Last update: Oct 29, 2012 at 07:45 PM
-- Last check: Oct 02, 2012 at 02:10 PM
--

DROP TABLE IF EXISTS `Repeater`;
CREATE TABLE IF NOT EXISTS `Repeater` (
  `DmrID` int(8) NOT NULL,
  `Callsign` char(6) NOT NULL,
  `City` char(32) NOT NULL,
  `State` char(32) NOT NULL,
  `Country` char(32) NOT NULL,
  `Frequency` char(32) NOT NULL,
  `PublishFrequency` decimal(10,5) NOT NULL,
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
  `SourceNet` int(11) NOT NULL,
  PRIMARY KEY  (`DmrID`),
  KEY `Affilated` (`Affilated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='DMR-MARC and other database of worldwide repeaters';

--
-- RELATIONS FOR TABLE `Repeater`:
--   `SourceNet`
--       `Network` -> `DmrID`
--

-- --------------------------------------------------------

--
-- Table structure for table `RepeaterLog`
--
-- Creation: Sep 12, 2012 at 06:20 PM
-- Last update: Oct 29, 2012 at 07:45 PM
--

DROP TABLE IF EXISTS `RepeaterLog`;
CREATE TABLE IF NOT EXISTS `RepeaterLog` (
  `ID` int(11) NOT NULL auto_increment,
  `DmrID` int(11) NOT NULL,
  `SourceNet` int(11) NOT NULL,
  `DateTime` datetime NOT NULL,
  `Ts1Online` int(11) NOT NULL,
  `Ts2Online` int(11) NOT NULL,
  `PacketType` int(11) NOT NULL,
  `TimeSlotRaw` varchar(2) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3017642 ;

--
-- RELATIONS FOR TABLE `RepeaterLog`:
--   `DmrID`
--       `Repeater` -> `DmrID`
--

-- --------------------------------------------------------

--
-- Table structure for table `Talkgroup`
--
-- Creation: Aug 18, 2012 at 10:01 PM
-- Last update: Sep 13, 2012 at 04:00 AM
-- Last check: Sep 12, 2012 at 09:36 AM
--

DROP TABLE IF EXISTS `Talkgroup`;
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
-- Creation: Sep 15, 2012 at 09:16 PM
-- Last update: Oct 29, 2012 at 04:00 AM
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
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
-- Creation: Sep 17, 2012 at 12:59 AM
-- Last update: Oct 29, 2012 at 07:45 PM
--

DROP TABLE IF EXISTS `UserLog`;
CREATE TABLE IF NOT EXISTS `UserLog` (
  `Key` char(32) NOT NULL,
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
  `Priority` char(4) NOT NULL,
  `FlowControlFlags` char(10) NOT NULL,
  `CallControlInfo` char(4) NOT NULL,
  `ContribSrcID` char(4) NOT NULL,
  `PayloadType` char(4) NOT NULL,
  `SeqNumber` char(6) NOT NULL,
  `TimeStamp` char(8) NOT NULL,
  `SyncSrcID` char(4) NOT NULL,
  `DataType` char(4) NOT NULL,
  `RssiThreshold` char(4) NOT NULL,
  `Length` char(4) NOT NULL,
  `RssiStatus` char(4) NOT NULL,
  `SlotType` char(4) NOT NULL,
  `DataSize` char(4) NOT NULL,
  PRIMARY KEY  (`Key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- RELATIONS FOR TABLE `UserLog`:
--   `DmrID`
--       `User` -> `DmrID`
--

