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

# used to migrate data from xname-2.2.x to 2.3.0

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
			if(! /CVS/){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
			}
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX .= $str_log_migrate_prefix{$SITE_DEFAULT_LANGUAGE};


$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);


# ######################################################

# Adding id column to dns_record

# add text !
$query="alter table dns_record add id int auto_increment, add primary key (id)";
$sth = dbexecute($query,$dbh,LOG);

# ######################################################

# add "options" to dns_user, remove extra
# add text !
$query = "alter table dns_user add options text";
$sth = dbexecute($query,$dbh,LOG);

# add text !
$query='UPDATE dns_user SET options=CONCAT("grouprights=",groupright,";advanced=",
  advanced,";ipv6=",ipv6,";nbrows=",nbrows,";txtrecords=",txtrecords,";")';
$sth = dbexecute($query,$dbh,LOG);

# add text !
$query = "alter table dns_user drop groupright, drop advanced, drop ipv6, drop nbrows, drop txtrecords";
$sth = dbexecute($query,$dbh,LOG);


# ######################################################

# migrate dns_logparser
$query = "ALTER TABLE dns_logparser MODIFY line TEXT";
$sth = dbexecute($query,$dbh,LOG);

# ######################################################

# if usergroups=0, groupid=id for everyone

