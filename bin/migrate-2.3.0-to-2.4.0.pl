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

# used to migrate data from xname-2.3.x to 2.4.0

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

# Adding val3, val4 and val5 columns to dns_record

$query="alter table dns_record add val3 varchar(255) null, add val4 varchar(255) null, add val5 varchar(255) null";
$sth = dbexecute($query,$dbh,LOG);

# ######################################################

# Adding SRV record type

$query = "alter table dns_record modify type enum('MX','NS','A','TXT','PTR','CNAME','DNAME','A6','AAAA','SUBNS','DELEGATE','SRV')";
$sth = dbexecute($query,$dbh,LOG);

# ######################################################

