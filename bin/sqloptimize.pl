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
                if(/^[a-z][a-z]$/ && -e $XNAME_HOME . "strings/" . $_ . "/strings.inc"){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX .=$str_log_sqloptimize_prefix{$SITE_DEFAULT_LANGUAGE};


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################

# select all tables
# optimize each table
# WARNING: loks table during optimization. Has to be run carefully


$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);


$query = "show tables from $DB_NAME";

my $sth = dbexecute($query,$dbh,LOG);
while($ref = $sth->fetchrow_hashref()){
#	print $ref->{'Tables_in_' . $DB_NAME} . "\n";
	$query = "OPTIMIZE TABLE " . $ref->{'Tables_in_' . $DB_NAME};
	my $sth2 = dbexecute($query,$dbh,LOG);
}
