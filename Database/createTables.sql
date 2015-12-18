SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `directory` (
  `directoryID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `path` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Path',
  `status` enum('active','removed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Status',
  `firstDate` datetime NOT NULL COMMENT 'First date',
  `modifyDate` datetime NOT NULL COMMENT 'Modify date',
  `owner` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Owner',
  `group` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Group',
  `permissions` int(10) NOT NULL COMMENT 'Permissions',
  `linkTarget` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Link target',
  `firstScanID` int(11) unsigned NOT NULL COMMENT 'First scan',
  `modifyScanID` int(11) unsigned NOT NULL COMMENT 'Modify scan',
  `lastScanID` int(11) unsigned DEFAULT NULL COMMENT 'Last scan',
  PRIMARY KEY (`directoryID`),
  KEY `FK_directory__modifyScanID` (`modifyScanID`),
  KEY `FK_directory__lastScanID` (`lastScanID`),
  KEY `IX_directory__path` (`path`(200)),
  KEY `FK_directory__firstScanID` (`firstScanID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Directories';

CREATE TABLE `directoryHistory` (
  `directoryID` int(11) unsigned NOT NULL COMMENT 'Directory',
  `scanID` int(11) unsigned NOT NULL COMMENT 'Scan',
  `operation` enum('new','modified','removed') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Status',
  `dateOnly` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no' COMMENT 'Date only',
  `modifyDate` datetime DEFAULT NULL COMMENT 'Modify date',
  `owner` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Owner',
  `group` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Group',
  `permissions` int(10) DEFAULT NULL COMMENT 'Permissions',
  `linkTarget` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Link target',
  PRIMARY KEY (`directoryID`,`scanID`),
  KEY `FK_directoryHistory__scanID` (`scanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Directory histories';

CREATE TABLE `file` (
  `directoryID` int(11) unsigned NOT NULL COMMENT 'Directory',
  `fileID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name',
  `status` enum('active','removed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Status',
  `firstDate` datetime NOT NULL COMMENT 'First date',
  `modifyDate` datetime NOT NULL COMMENT 'Modify date',
  `owner` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Owner',
  `group` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Group',
  `permissions` int(10) NOT NULL COMMENT 'Permissions',
  `linkTarget` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Link target',
  `size` int(10) NOT NULL COMMENT 'Size',
  `contentHash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Content hash',
  `storedAs` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stored as',
  `firstScanID` int(11) unsigned NOT NULL COMMENT 'First scan',
  `modifyScanID` int(11) unsigned NOT NULL COMMENT 'Modify scan',
  `lastScanID` int(11) unsigned NOT NULL COMMENT 'Last scan',
  PRIMARY KEY (`fileID`),
  UNIQUE KEY `IX_file__directoryID_name` (`directoryID`,`name`),
  KEY `FK_file__directoryID` (`directoryID`),
  KEY `FK_file__modifyScanID` (`modifyScanID`),
  KEY `FK_file__lastScanID` (`lastScanID`),
  KEY `FK_file__firstScanID` (`firstScanID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Files';

CREATE TABLE `fileHistory` (
  `fileID` int(11) unsigned NOT NULL COMMENT 'File',
  `scanID` int(11) unsigned NOT NULL COMMENT 'Scan',
  `operation` enum('new','modified','removed') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Status',
  `dateOnly` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no' COMMENT 'Date only',
  `modifyDate` datetime DEFAULT NULL COMMENT 'Modify date',
  `owner` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Owner',
  `group` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Group',
  `permissions` int(10) DEFAULT NULL COMMENT 'Permissions',
  `linkTarget` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Link target',
  `size` int(10) DEFAULT NULL COMMENT 'Size',
  `contentHash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Content hash',
  `storedAs` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stored as',
  PRIMARY KEY (`fileID`,`scanID`),
  KEY `FK_fileHistory__scanID` (`scanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='File histories';

CREATE TABLE `log` (
  `scanID` int(11) unsigned NOT NULL COMMENT 'Scan',
  `logID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` datetime NOT NULL COMMENT 'Date',
  `type` enum('info','error') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type',
  `text` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text',
  PRIMARY KEY (`logID`),
  KEY `FK_log__scanID` (`scanID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Log';

CREATE TABLE `scan` (
  `scanID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `status` enum('progress','done') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'progress' COMMENT 'Status',
  `startDate` datetime NOT NULL COMMENT 'Start date',
  `lastOperationDate` datetime NOT NULL COMMENT 'Last operation date',
  `endDate` datetime DEFAULT NULL COMMENT 'End date',
  `directoryChanges` int(10) DEFAULT NULL COMMENT 'Directory changes',
  `directoryDateOnlyChanges` int(10) DEFAULT NULL COMMENT 'Directory date only changes',
  `fileChanges` int(10) DEFAULT NULL COMMENT 'File changes',
  `fileDateOnlyChanges` int(10) DEFAULT NULL COMMENT 'File date only changes',
  `errors` int(10) DEFAULT NULL COMMENT 'Errors',
  `mailSent` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no' COMMENT 'Mail sent',
  PRIMARY KEY (`scanID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Scans';


ALTER TABLE `directory`
  ADD CONSTRAINT `FK_directory__firstScanID` FOREIGN KEY (`firstScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_directory__lastScanID` FOREIGN KEY (`lastScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_directory__modifyScanID` FOREIGN KEY (`modifyScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE;

ALTER TABLE `directoryHistory`
  ADD CONSTRAINT `FK_directoryHistory__directoryID` FOREIGN KEY (`directoryID`) REFERENCES `directory` (`directoryID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_directoryHistory__scanID` FOREIGN KEY (`scanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE;

ALTER TABLE `file`
  ADD CONSTRAINT `FK_file__directoryID` FOREIGN KEY (`directoryID`) REFERENCES `directory` (`directoryID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_file__firstScanID` FOREIGN KEY (`firstScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_file__lastScanID` FOREIGN KEY (`lastScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_file__modifyScanID` FOREIGN KEY (`modifyScanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE;

ALTER TABLE `fileHistory`
  ADD CONSTRAINT `FK_fileHistory__fileID` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_fileHistory__scanID` FOREIGN KEY (`scanID`) REFERENCES `scan` (`scanID`) ON UPDATE CASCADE;

ALTER TABLE `log`
  ADD CONSTRAINT `FK_log__scanID` FOREIGN KEY (`scanID`) REFERENCES `scan` (`scanID`) ON DELETE CASCADE ON UPDATE CASCADE;
