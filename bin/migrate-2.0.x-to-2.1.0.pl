#!/usr/bin/perl

###############################################################
#	This file is part of XName.org project                    #
#	See	http://www.xname.org/ for details                     #
#	                                                          #
#	License: GPLv2                                            #
#	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html #
#	                                                          #
#	Author(s): Yann Hirou <hirou@xname.org>                   #
###############################################################

# used to migrate data from xname-2.0.x to 2.1.0

use DBI;
use Digest::MD5 qw(md5_hex);
use Time::localtime;
use POSIX qw(strftime);
# *****************************************************
# Where am i run from
$0 =~ m,(.*/).*,;
$XNAME_HOME = $1;

require $XNAME_HOME . "config.pl";
require $XNAME_HOME . "xname.inc";
$LOG_PREFIX .='migrate';


$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);


# MySQL alter table

@tobeadded = ("soamail varchar(255) NULL",
"groupid int NOT NULL",
"groupright enum('A','R','W') default 'W'",
"KEY groupid (groupid)");


foreach(@tobeadded){
	$query = "ALTER TABLE dns_user ADD " . $_;
	
	my $sth = $dbh->prepare($query);
	if(!$sth){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
	}
}

# set default values for each user
# soamail : nothing to be done (NULL is OK)
# groupid : userid
# groupright : 'A'

$query = "SELECT id FROM dns_user";
$sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}

while (my $ref = $sth->fetchrow_hashref()) {
# for each user, 
	$id = $ref->{'id'};

	$query = "UPDATE dns_user SET groupid='" . $id . "', groupright='A'
	WHERE id='" . $id . "'";
	my $sth2 = $dbh->prepare($query);
	if(!$sth2){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth2->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth2->errstr . "\n";
	}
}

# create dns_userlog
# CREATE TABLE dns_userlog (
#        userid int NOT NULL,
#        date timestamp(14) NOT NULL,
#        zoneid int NOT NULL,
#        content TEXT,
#        KEY userid (userid),
#        KEY date (date),
#        KEY zoneid (zoneid)
# }
$query = " CREATE TABLE dns_userlog (
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
 )
";
$sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}




# ####################################################
# Add status field in zones
@tobeadded = ("status char(1) default ''",
	"KEY zone_status(status)");

foreach(@tobeadded){
	$query = "ALTER TABLE dns_zone ADD " . $_;
	
	my $sth = $dbh->prepare($query);
	if(!$sth){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
	}
}

# ####################################################
# drop dns_modified and dns_deleted, replaced by status 
# in dns_zone
$query = "DROP TABLE dns_modified";
my $sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}

$query = "DROP TABLE dns_deleted";
my $sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}


# ####################################################
# Multi Servers


# Create first server
$query = "
CREATE TABLE dns_server (
	id	int auto_increment unique,
	servername varchar(255) NOT NULL,
	serverip varchar(255) NOT NULL,
	location varchar(255) NOT NULL,
	adminid int NOT NULL,
	maxzones int default '0',
	maxzonesperuser int default '0',
	sshlogin varchar(255),
	sshport int default '22',
	pathonremote varchar(255),
	sshpublickey text,
	KEY serverid_srv (id),
	KEY admin_id(adminid),
	KEY servername (servername),
	KEY serverip (serverip)
)";

$sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}


# insert data for server
print "
With this new xname version, you have to define at least one name server
(your main one).

Please answer following questions 
(WARNING: no integrity check is done... answer carefully) :\n";
#	id	int auto_increment unique,
#	servername varchar(255) NOT NULL,
#	serverip varchar(255) NOT NULL,
#	location varchar(255) NOT NULL,
#	adminmail varchar(255) NOT NULL,
#	maxzones int default '0',
#	maxzonesperuser int default '0',
#	sshpublickey text,


print "Servername [$SITE_NS]: ";
$line = <STDIN>;
if($line =~ /^$/){
	$servername = $SITE_NS;
}else{
	chop($line);
	$servername=$line;
}

print "Server IP: ";
$line = <STDIN>;
chop($line);
$serverip=$line;

print "Server location (France/Paris, USA/New York, etc...): ";
$line = <STDIN>;
chop($line);
$location=$line;

print "Server admin login (from current DB): ";
$line = <STDIN>;
chop($line);
$adminid=$line;


print "
Thanxs for your answers.
";


$query = "INSERT into dns_server (id,servername,serverip,location,adminid)
values ('1','" . $servername . "','" . $serverip . "','" . $location . "','" .
$adminmail . "')";


$sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}


# ##########################"
# create dns_zonetoserver and populate with every zone on serverid=1
$query = "
CREATE TABLE dns_zonetoserver (
	zoneid int NOT NULL,
	serverid int NOT NULL,
	KEY zoneid_ztos(zoneid),
	KEY serverid_ztos(serverid)
)";

$sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}


$query = "SELECT id FROM dns_zone";
$sth = $dbh->prepare($query);
if (!$sth) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}

while (my $ref = $sth->fetchrow_hashref()) {
	$zoneid = $ref->{'id'};
	$query = "INSERT INTO dns_zonetoserver (zoneid,serverid)
				VALUES ('" . $zoneid . "','1')";
	$sth2=$dbh->prepare($query);
	if (!$sth2) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth2->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
	}
}




# ##########################"
# alter log table to add server id, by default our new server (id=1)


@tobeadded = ("	serverid int NOT NULL default '1'",
"KEY logserverid (serverid)");


foreach(@tobeadded){
	$query = "ALTER TABLE dns_log ADD " . $_;
	
	my $sth = $dbh->prepare($query);
	if(!$sth){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
	}
}



# ####################################################
#       migrate passwords to md5
# ####################################################

$query = "SELECT id,password FROM dns_user";

my $sth = $dbh->prepare($query);
$sth->execute;

while($ref = $sth->fetchrow_hashref()){
	$id=$ref->{'id'};
	$password=$ref->{'password'};
    $password =  md5_hex($password);


	$query = "UPDATE dns_user SET password='" . $password . "'
				WHERE id='" . $id . "'";
    $sth2 = $dbh->prepare($query);
	$sth2->execute;
	
}


# #####################################################
#       add advanced flag to dns_user
# #####################################################

$query = "ALTER TABLE dns_user add advanced enum('0','1') default '0'";
my $sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}
	
# ####################################################
#       migrate AZONE records
# ####################################################

@tobeexecuted = ("create temporary table azone 
	select zoneid, 'A', concat(zone,'.'), val1 
	from dns_record r, dns_zone z where r.zoneid = z.id and r.type='AZONE'",
	"insert into dns_record select * from azone",
	"delete from dns_record where type='AZONE'",
	"drop table azone");

foreach(@tobeexecuted){
	my $sth = $dbh->prepare($_);
	if(!$sth){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
	}
	if (!$sth->execute) {
		print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
	}
}



# ####################################################
#        Add ttl to all records
# ####################################################

$query = 'ALTER TABLE dns_record add ttl 
			varchar(255) NOT NULL default "default"';
my $sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}


# ####################################################
#        Add default TTL in dns_confprimary
# ####################################################

$query = "ALTER TABLE dns_confprimary add 
	defaultttl varchar(255) NOT NULL default '43200'";
my $sth = $dbh->prepare($query);
if(!$sth){
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $dbh->errstr . "\n";
}
if (!$sth->execute) {
	print LOG logtimestamp() . " " . $LOG_PREFIX . " : Error:" . $sth->errstr . "\n";
}

