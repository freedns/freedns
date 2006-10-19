create database xnamedev;
use xnamedev;

CREATE TABLE dns_user (
	id int auto_increment unique,
	login varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	password varchar(255) NOT NULL,
	valid enum('0','1') default '0',
	creationdate timestamp,
	groupid int NOT NULL,
	options TEXT,
	lang varchar(2) default 'en',
	KEY user_login (login),
	KEY user_id(id),
	KEY user_groupid (groupid)
);


CREATE TABLE dns_zone (
	id int auto_increment unique,
	zone varchar(255) NOT NULL,
	userid int NOT NULL,
	zonetype enum('P','S','B') NOT NULL,
	status char(1) default '',
	KEY zone_zone (zone,zonetype),
	KEY index_zone (zone),
	KEY zone_userid (userid),
	KEY zone_status(status),
	PRIMARY KEY (id)
);


CREATE TABLE dns_confsecondary (
	zoneid int NOT NULL,
	masters varchar(255) NULL,
	xfer varchar(255) NULL default 'any',
	tsig text NULL,
	serial varchar(255) NOT NULL,
	PRIMARY KEY (zoneid)
);


CREATE TABLE dns_confprimary (
	zoneid int NOT NULL,
	serial varchar(255) NOT NULL,
	refresh varchar(255) NOT NULL default '10800',
	retry varchar(255) NOT NULL default '1800',
	expiry varchar(255) NOT NULL default '3600000',
	minimum varchar(255) NOT NULL default '10800',
	defaultttl varchar(255) NOT NULL default '43200',
	xfer varchar(255) NULL default 'any',
	PRIMARY KEY (zoneid)
);

CREATE TABLE dns_log (
	zoneid int NOT NULL,
	date timestamp(14) NOT NULL,
	content varchar(255) NOT NULL,
	status enum('E','I','W') default 'I',
	serverid int NOT NULL,
	KEY log_id(zoneid),
	KEY status_id(status),
	KEY date_id(date),
	KEY log_serverid(serverid)
);

CREATE TABLE dns_userlog (
	id int auto_increment unique,
	userid int NOT NULL,
	groupid int NOT NULL,
	date timestamp(14) NOT NULL,
	zoneid int NOT NULL,
	content TEXT,
        KEY userid (userid),
	KEY groupid (groupid),
	KEY date (date),
	KEY zoneid (zoneid)
);

CREATE TABLE dns_logparser (
	line TEXT
);

CREATE TABLE dns_session (
	sessionID varchar(255) NOT NULL,
	userid int NOT NULL,
	date timestamp(14) NOT NULL,
	KEY session_id(sessionID),
	KEY session_date(date),
	KEY index_userid(userid)
);


CREATE TABLE dns_generate (
	busy enum('0','1')
);



CREATE TABLE dns_record (
	zoneid int NOT NULL,
	type enum('MX','NS','A','PTR','CNAME','TXT','DNAME','A6','AAAA','SUBNS','DELEGATE','SRV') NOT NULL,
	val1 varchar(255) NULL,
	val2 varchar(255) NOT NULL,
	val3 varchar(255) NULL,
	val4 varchar(255) NULL,
	val5 varchar(255) NULL,
	ttl varchar(255) NOT NULL default "-1",
	id int NOT NULL auto_increment,
	PRIMARY KEY (id),
	KEY record_zoneid(zoneid),
	KEY record_typeid(type)
);

CREATE TABLE dns_recovery (
	userid int NOT NULL,
	id varchar(255),
	insertdate timestamp(14),
	KEY recovery_userid(userid),
	KEY recovery_insertdate(insertdate),
	KEY recovery_sessionid(id)
);


CREATE TABLE dns_waitingreply (
	userid int NOT NULL,
	firstdate timestamp(14),
	email varchar(255) NOT NULL,
	id varchar(255) NOT NULL,
	KEY waiting_firstdateid (firstdate)
);

CREATE TABLE dns_server (
	id int auto_increment unique,
	servername varchar(255) NOT NULL,
	serverip varchar(255) NOT NULL,
	transferip varchar(255) NOT NULL,
	mandatory bool default 1,
	location varchar(255) NOT NULL,
	adminid int NOT NULL,
	maxzones int default '0',
	maxzonesperuser int default '0',
	sshhost varchar(255),
	sshlogin varchar(255),
	sshport int default '22',
	pathonremote varchar(255),
	sshpublickey text,
	KEY server_id (id),
	KEY server_adminid(adminid),
	KEY server_servername (servername),
	KEY server_serverip (serverip)
);

CREATE TABLE dns_zonetoserver (
	zoneid int NOT NULL,
	serverid int NOT NULL,
	KEY zoneid_ztos(zoneid),
	KEY serverid_ztos(serverid)
);

CREATE TABLE dns_admin (
	userid int NOT NULL,
	KEY admin_userid (userid)
);

