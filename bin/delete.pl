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
                if(/^[^\.][^\.]$/ && -e $XNAME_HOME . "strings/" . $_ . "/strings.inc"){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX.=$str_log_delete_prefix{$SITE_DEFAULT_LANGUAGE};


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################


# Delete old users from DB
# Table impacted : 
# dns_waitingreply 
# dns_user

$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

if($DB_AUTH_NAME){
	$dsnauth = "DBI:mysql:" . $DB_AUTH_NAME . ";host=" . $DB_AUTH_HOST . ";port=" . $DB_AUTH_PORT;
	$dbhauth = DBI->connect($dsnauth, $DB_AUTH_USER, $DB_AUTH_PASSWORD);
}else{
	$dbhauth=$dbh;
}
open(LOG, ">>" . $LOG_FILE);

$timetouse = strftime("%Y%m%d%H%M%S", localtime(time()-604800));

$query = sprintf("SELECT w.%s as userid,u.%s as login
			FROM %s w, %s u
			WHERE %s <= %s AND w.%s=u.%s AND u.%s=0",
		$DB_AUTH_WAITING_USERID,
		$DB_AUTH_FLD_LOGIN,
		$DB_AUTH_WAITING_TABLE,
		$DB_AUTH_TABLE,
		$DB_AUTH_WAITING_FIRSTDATE,
		$timetouse,
		$DB_AUTH_WAITING_USERID,
		$DB_AUTH_FLD_ID,
		$DB_AUTH_FLD_VALID);	

my $sth = dbexecute($query,$dbhauth,LOG);
while (my $ref = $sth->fetchrow_hashref()) {
# for each user, 
	$userid = $ref->{'userid'};
	print LOG logtimestamp() . " " . $LOG_PREFIX . " " . 
		sprintf($str_log_deleting_user_x{$SITE_DEFAULT_LANGUAGE},$userid . " / " . $ref->{'login'}) . "\n";	

	# TODO send email to warn 
	
	# mark zones to be deleted !
	$query = "UPDATE dns_zone set status='D' WHERE userid='" . $userid . "'";
	my $sth2 = dbexecute($query,$dbh,LOG);

	$query = sprintf("DELETE FROM %s WHERE %s='%s'",
			$DB_AUTH_TABLE,
			$DB_AUTH_FLD_ID,
			$userid);
	my $sth2 = dbexecute($query,$dbhauth,LOG);

	$query = sprintf("DELETE FROM %s WHERE %s='%s'",
			$DB_AUTH_WAITING_TABLE,
			$DB_AUTH_WAITING_USERID,
			$userid);
	my $sth2 = dbexecute($query,$dbhauth,LOG);
}

$query = "SELECT zone,zonetype
		FROM dns_zone WHERE status='D'";

	$sth = dbexecute($query,$dbh,LOG);

@todelete=();
while (my $ref = $sth->fetchrow_hashref()) {
# for each zone, 
	$zonename = $ref->{'zone'};
	$zonetype = $ref->{'zonetype'};
	print LOG logtimestamp() . " " . $LOG_PREFIX . " " . 
			sprintf($str_log_deleting_zone_x{$SITE_DEFAULT_LANGUAGE},$zonename) . "\n";	

	# Delete $NAMED_DATA_DIR/masters|slaves
	if($zonetype eq "P"){
		$command= "$RM_COMMAND $NAMED_DATA_DIR" . $NAMED_MASTERS_DIR . $zonename;
	}else{
		$command= "$RM_COMMAND $NAMED_DATA_DIR" . $NAMED_SLAVES_DIR . $zonename;
	}
	`$command`;
	push(@todelete,$zonename);
}


# delete from DB
while(<@todelete>){
	# delete from dns_conf* dns_log dns_record if not already done
	$query = "SELECT id,zonetype FROM dns_zone WHERE zone='" . $_ . "'";
 	my $sth = dbexecute($query,$dbh,LOG);
	my $ref = $sth->fetchrow_hashref();
	$query = "DELETE FROM dns_conf";
	if($ref->{'zonetype'} == 'P'){
		$query .= "primary";
	}else{
		$query .= "secondary";
	}
	$query .= " WHERE zoneid='" . $ref->{'id'} . "'";
 	my $sth = dbexecute($query,$dbh,LOG);

	$query = "DELETE FROM dns_log WHERE  zoneid='" . $ref->{'id'} . "'";
        my $sth = dbexecute($query,$dbh,LOG);

	if($ref->{'zonetype'} == 'P'){
		$query = "DELETE FROM dns_record WHERE  zoneid='" . $ref->{'id'} . "'";
	        my $sth = dbexecute($query,$dbh,LOG);
	}

	$query = "DELETE from dns_zone WHERE zone='" . $_ . "'";
 	my $sth = dbexecute($query,$dbh,LOG);
}

close LOG;
