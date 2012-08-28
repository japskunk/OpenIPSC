-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 28, 2012 at 12:24 AM
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
-- Table structure for table `Events`
--

CREATE TABLE IF NOT EXISTS `Events` (
  `DateTime` datetime NOT NULL COMMENT 'UTC Start of transmission',
  `EndTime` datetime NOT NULL COMMENT 'UTC end of transmission',
  `DmrID` int(11) NOT NULL COMMENT 'the ID of the device making the call',
  `SourceID` int(9) NOT NULL COMMENT 'The repeater that sourced the call.',
  `DestinationID` int(9) NOT NULL COMMENT 'the destination of the call - can be group or private id''s',
  `NetworkID` int(9) NOT NULL COMMENT 'the network which the call originated from',
  `Slot` int(9) NOT NULL COMMENT 'either slot 1 or 2...',
  `Sequence` int(32) NOT NULL,
  `RawData` char(64) NOT NULL COMMENT 'raw octets from the IPSC stream',
  `Answer` tinyint(1) NOT NULL,
  `Data` tinyint(1) NOT NULL COMMENT 'data call',
  `Final` tinyint(1) NOT NULL,
  `Group` tinyint(1) NOT NULL COMMENT 'indicate a group call',
  `Private` tinyint(1) NOT NULL COMMENT 'private call',
  `Voice` tinyint(1) NOT NULL COMMENT 'indicates a voice call'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Log`
--

CREATE TABLE IF NOT EXISTS `Log` (
  `date_time` datetime NOT NULL,
  `from_id` int(7) NOT NULL,
  `rptr_id` int(6) NOT NULL,
  `grp_id` int(8) NOT NULL,
  `seq` int(3) NOT NULL,
  `ts` int(3) NOT NULL,
  `flags` varchar(10) collate latin1_german1_ci NOT NULL,
  `rawdata` varchar(120) character set ascii NOT NULL,
  `end_time` time NOT NULL,
  `net` int(5) NOT NULL,
  KEY `TST` (`date_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='DMR-Logbuch';

-- --------------------------------------------------------

--
-- Table structure for table `Network`
--

CREATE TABLE IF NOT EXISTS `Network` (
  `DmrID` int(7) NOT NULL,
  `Description` varchar(64) NOT NULL,
  `Publish` tinyint(1) NOT NULL default '1',
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
