/*!40101 SET NAMES utf8 */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `dns_admin` (
  `userid` int(11) NOT NULL DEFAULT '0',
  KEY `admin_userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_confprimary` (
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `serial` varchar(255) NOT NULL DEFAULT '',
  `refresh` varchar(255) NOT NULL DEFAULT '10800',
  `retry` varchar(255) NOT NULL DEFAULT '1800',
  `expiry` varchar(255) NOT NULL DEFAULT '3600000',
  `minimum` varchar(255) NOT NULL DEFAULT '10800',
  `defaultttl` varchar(255) NOT NULL DEFAULT '43200',
  `xfer` varchar(1024) DEFAULT 'any',
  UNIQUE KEY `dns_confprimary_zoneid` (`zoneid`),
  KEY `confprim_id` (`zoneid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_confsecondary` (
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `masters` varchar(255) DEFAULT NULL,
  `xfer` varchar(255) DEFAULT 'any',
  `tsig` text,
  `serial` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `dns_confsecondary_zoneid` (`zoneid`),
  KEY `confsec_id` (`zoneid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_generate` (
  `busy` enum('0','1') DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `zoneid` int(11) DEFAULT NULL,
  `ownerid` int(11) DEFAULT NULL,
  `perms` enum('R','W') DEFAULT 'R',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_log` (
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content` varchar(255) NOT NULL DEFAULT '',
  `status` enum('E','I','W') DEFAULT 'I',
  `serverid` int(11) NOT NULL DEFAULT '1',
  KEY `log_id` (`zoneid`),
  KEY `status_id` (`status`),
  KEY `date_id` (`date`),
  KEY `log_serverid` (`serverid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_logparser` (
  `line` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `content` text,
  `login` text,
  `parentid` int(11) DEFAULT NULL,
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `dns_record` (
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `type` enum('MX','NS','A','PTR','CNAME','DNAME','A6','AAAA','SUBNS','DELEGATE','SRV','TXT','WWW','CAA') DEFAULT NULL,
  `val1` varchar(8000) DEFAULT NULL,
  `val2` varchar(8000) DEFAULT NULL,
  `ttl` varchar(255) NOT NULL DEFAULT '-1',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `val3` varchar(8000) DEFAULT NULL,
  `val4` varchar(8000) DEFAULT NULL,
  `val5` varchar(8000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_zoneid` (`zoneid`),
  KEY `record_typeid` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_recovery` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `id` varchar(255) DEFAULT NULL,
  `insertdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `recovery_userid` (`userid`),
  KEY `recovery_sessionid` (`id`),
  KEY `recovery_insertdate` (`insertdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servername` varchar(255) NOT NULL DEFAULT '',
  `serverip` varchar(255) NOT NULL DEFAULT '',
  `location` varchar(255) NOT NULL DEFAULT '',
  `adminid` int(11) NOT NULL DEFAULT '0',
  `maxzones` int(11) DEFAULT '0',
  `maxzonesperuser` int(11) DEFAULT '0',
  `sshhost` varchar(255) DEFAULT NULL,
  `sshlogin` varchar(255) DEFAULT NULL,
  `sshport` int(11) DEFAULT '22',
  `pathonremote` varchar(255) DEFAULT NULL,
  `sshpublickey` text,
  `transferip` varchar(255) DEFAULT NULL,
  `mandatory` tinyint(1) DEFAULT '1',
  UNIQUE KEY `id` (`id`),
  KEY `server_id` (`id`),
  KEY `server_adminid` (`adminid`),
  KEY `server_servername` (`servername`),
  KEY `server_serverip` (`serverip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_session` (
  `sessionID` varchar(255) NOT NULL DEFAULT '',
  `userid` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `session_id` (`sessionID`),
  KEY `session_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_translation` (
  `varname` varchar(255) DEFAULT NULL,
  `lang` char(2) DEFAULT NULL,
  `text` text,
  UNIQUE KEY `dns_translation_idx` (`varname`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `soamail` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `valid` enum('0','1') DEFAULT '0',
  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `groupid` int(11) NOT NULL DEFAULT '0',
  `lang` char(2) DEFAULT 'pl',
  `options` text,
  UNIQUE KEY `id` (`id`),
  KEY `user_login` (`login`),
  KEY `user_id` (`id`),
  KEY `user_groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_userlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `groupid` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `content` text,
  UNIQUE KEY `id` (`id`),
  KEY `userid` (`userid`),
  KEY `groupid` (`groupid`),
  KEY `date` (`date`),
  KEY `zoneid` (`zoneid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_waitingreply` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `firstdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) NOT NULL DEFAULT '',
  `id` varchar(255) NOT NULL DEFAULT '',
  KEY `waiting_firstdateid` (`firstdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` varchar(255) NOT NULL DEFAULT '',
  `userid` int(11) NOT NULL DEFAULT '0',
  `zonetype` enum('P','S','B') NOT NULL DEFAULT 'P',
  `status` char(1) DEFAULT '',
  UNIQUE KEY `id` (`id`),
  KEY `zone_zone` (`zone`,`zonetype`),
  KEY `zone_userid` (`userid`),
  KEY `zone_status` (`status`),
  KEY `zone_id` (`id`),
  KEY `index_zone` (`zone`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `dns_zonetoserver` (
  `zoneid` int(11) NOT NULL DEFAULT '0',
  `serverid` int(11) NOT NULL DEFAULT '0',
  KEY `zoneid_ztos` (`zoneid`),
  KEY `serverid_ztos` (`serverid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

