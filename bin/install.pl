#!/usr/bin/env perl

###############################################################
#	This file is part of XName.org project                    #
#	See	http://www.xname.org/ for details                     #
#	                                                          #
#	License: GPLv2                                            #
#	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html #
#	                                                          #
#	Author(s): Yann Hirou <hirou@xname.org>                   #
###############################################################

use DBI;
use Time::localtime;
use POSIX qw(strftime);

# *****************************************************
# Where am i run from
$0 =~ m,(.*/).*,;
$XNAME_HOME = $1;

require $XNAME_HOME . "config.pl";
require $XNAME_HOME . "xname.inc";
# load all languages
if(opendir(DIR,$XNAME_HOME . "strings")){
        foreach(readdir(DIR)){
                if(/^[^\.]*$/){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX .=$str_log_install_prefix{$SITE_DEFAULT_LANGUAGE};

$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);

# insert data for server
print $str_install_new_server{$SITE_DEFAULT_LANGUAGE} . " :\n";
#	id	int auto_increment unique,
#	servername varchar(255) NOT NULL,
#	serverip varchar(255) NOT NULL,
#	location varchar(255) NOT NULL,
#	adminmail varchar(255) NOT NULL,
#	maxzones int default '0',
#	maxzonesperuser int default '0',
#	sshpublickey text,


print $str_install_servername{$SITE_DEFAULT_LANGUAGE} . " [$SITE_NS]: ";
$line = <STDIN>;
if($line =~ /^$/){
	$servername = $SITE_NS;
}else{
	chop($line);
	$servername=$line;
}

print $str_install_server_ip{$SITE_DEFAULT_LANGUAGE} . ": ";
$line = <STDIN>;
chop($line);
$serverip=$line;

print $str_install_server_location{$SITE_DEFAULT_LANGUAGE} .  ": ";
$line = <STDIN>;
chop($line);
$location=$line;

#print "Server admin login id (from current DB): ";
#$line = <STDIN>;
#chop($line);
#$adminid=$line;


print "
" .  $str_install_thanx{$SITE_DEFAULT_LANGUAGE} . "
";


$query = "INSERT into dns_server (id,servername,serverip,location)
values ('1','" . $servername . "','" . $serverip . "','" . $location . "')";


$sth = dbexecute($query,$dbh,LOG);
