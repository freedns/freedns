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

# used to migrate data from xname-2.1.x to 2.2.0

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

$LOG_PREFIX .= $str_log_migrate_prefix{$SITE_DEFAULT_LANGUAGE};


$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);


# ######################################################

# Adding a column in dns_server for allow-transfer

$query="ALTER TABLE dns_server ADD transferip varchar(255) NULL";
$sth = dbexecute($query,$dbh,LOG);

$query = "UPDATE dns_server SET transferip=serverip";
$sth = dbexecute($query,$dbh,LOG);

print $str_migrate_transferip_field{$SITE_DEFAULT_LANGUAGE};


# ######################################################

# add PTR to dns_record list of types

$query = "ALTER TABLE dns_record MODIFY type
			enum('DELEGATE','PTR','MX','NS','A','AZONE','CNAME','DNAME','A6','AAAA','SUBNS')";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_ptr_delegate{$SITE_DEFAULT_LANGUAGE};


# ######################################################

# add ipv6 and nbrows columns to dns_user

$query = "ALTER TABLE dns_user ADD ipv6 enum('0','1') default '0'";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_ipv6{$SITE_DEFAULT_LANGUAGE};

$query = "ALTER TABLE dns_user ADD nbrows int default '4'";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_nbrows{$SITE_DEFAULT_LANGUAGE};

# ######################################################

# add lang column to dns_user 

$query = " alter table dns_user add lang varchar(2) default 'en'";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_lang{$SITE_DEFAULT_LANGUAGE};

# ######################################################

# change TTL to "-1" instead of "default"

$query = "ALTER TABLE dns_record ALTER ttl SET DEFAULT '-1'";
$sth = dbexecute($query,$dbh,LOG);

$query = "update dns_record set ttl='-1' WHERE ttl='default'";
$sth = dbexecute($query,$dbh,LOG);

print $str_migrate_ttl{$SITE_DEFAULT_LANGUAGE};

# ######################################################

# Adding a column in dns_server for checking servers as mandatory or not

$query="ALTER TABLE dns_server ADD mandatory bool default 1";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_mandatory_field{$SITE_DEFAULT_LANGUAGE};

# ######################################################

# creating dns_admin table

$query = "CREATE TABLE dns_admin (
        userid int NOT NULL,
        KEY admin_userid (userid)
)";
$sth = dbexecute($query,$dbh,LOG);
print $str_migrate_dns_admin{$SITE_DEFAULT_LANGUAGE};

# ######################################################

# add txtrecords columns to dns_user

$query = "ALTER TABLE dns_user ADD txtrecords enum('0','1') default '0'";
$sth = dbexecute($query,$dbh,LOG);
# print $str_migrate_txtrecords{$SITE_DEFAULT_LANGUAGE};

