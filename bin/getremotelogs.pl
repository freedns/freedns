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

if(!$MULTISERVER){
	exit(1);
}

# load all languages
if(opendir(DIR,$XNAME_HOME . "strings")){
        foreach(readdir(DIR)){
                if(/^[^\.][^\.]$/){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}
$LOG_PREFIX .= $str_log_getremotelogs_prefix{$SITE_DEFAULT_LANGUAGE};

########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################

# retrieve server list
# for each server
#   connect & retrieve logfile
#   for each log line 
#     look for zone ID insert in DB
#   end foreach
# end foreach


$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);



# Retrieve server list

$query = "SELECT id, sshlogin, serverip, pathonremote, sshport FROM dns_server WHERE id!='1'";
my $sth = dbexecute($query,$dbh,LOG);
while($ref = $sth->fetchrow_hashref()){
	# connect & retrieve log file into $REMOTE_SERVER_LOGS
	$command = $SCP_COMMAND  . " -P " . $ref->{'sshport'} . " " . $ref->{'sshlogin'} . "@" . $ref->{'serverip'} . ":" .
				$ref->{'pathonremote'} . "/logpush.txt " . $REMOTE_SERVER_LOGS . 
				$ref->{'serverip'} . "-logpush.txt " . " 2>&1";
	@output = `$command`;
	if($#output>=0){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " " . 
				sprintf($str_log_error_executing_x{$SITE_DEFAULT_LANGUAGE},
					$command) . ": \n";
		foreach(@output){
			print LOG logtimestamp() . " " . $LOG_PREFIX . "		" . $_;
		}
	}else{
		# parse file & insert in DB
		open(FILE, "< " . $REMOTE_SERVER_LOGS . $ref->{'serverip'} . "-logpush.txt");
		while(<FILE>){
			# timestamp zonename status content
			$line = $_;
			$line =~ /^([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+(.*)$/;
			$timestamp = $1;
			$zonename = $2;
			$status = $3;
			$content = $4;
		 # code taken from insertlogs.pl

	 		# insert in DB
			# escape from mysql... 
			$zonename =~ s/'/\\'/g;
			$content =~ s/'/\\'/g;
			$zonename =~ s/"/\\"/g;		
			$content =~ s/"/\\"/g;
		
			# select zoneid
			$query = "SELECT id FROM dns_zone WHERE zone='" . $zonename .
			"'";
			my $sth2 = dbexecute($query,$dbh,LOG);
			$ref2 = $sth2->fetchrow_hashref();
			if(!$ref2->{'id'}){
				# check if zone exists with a "." at the end
				$query = "SELECT id FROM dns_zone WHERE zone='" . $zonename .
				".'";
				my $sth2 = dbexecute($query,$dbh,LOG);
				$ref2 = $sth2->fetchrow_hashref();
				if($ref->{'id'}){
		
					$query = "INSERT INTO dns_log (zoneid, date, content, status,serverid)
					VALUES ('" . $ref2->{'id'} . "','" . $timestamp . "','" . $content . 
					"','" . $status . "','" . $ref->{'id'} . "')";

					my $sth2 = dbexecute($query,$dbh,LOG);
				}
			}else{
				$query = "INSERT INTO dns_log (zoneid, date, content, status,serverid)
				VALUES ('" . $ref2->{'id'} . "','" . $timestamp . "','" . $content . 
				"','" . $status . "','" . $ref->{'id'} . "')";
				my $sth2 = dbexecute($query,$dbh,LOG);
			}
	
		   # end code from insertlogs.pl

			

		} # end while FILE
		close(FILE);
		# no need to delete local file - but remote ?
		# purge it
		open(FILE, "> " . $REMOTE_SERVER_LOGS . $ref->{'serverip'} . "-logpush.txt");
		print FILE "";
		close FILE;
		$command = $SCP_COMMAND  . " -P " . $ref->{'sshport'} . " " . $REMOTE_SERVER_LOGS . 
				$ref->{'serverip'} . "-logpush.txt " . $ref->{'sshlogin'} . "@" . $ref->{'serverip'} . ":" .
				$ref->{'pathonremote'} . "/logpush.txt " . " 2>&1";

		@output = `$command`;
		if($#output>=0){
			print LOG logtimestamp() . " " . $LOG_PREFIX . " " . 
				sprintf($str_log_error_executing_x{$SITE_DEFAULT_LANGUAGE},
					$command) . ": \n";
			foreach(@output){
				print LOG logtimestamp() . " " . $LOG_PREFIX . "		" . $_;
			}
		}
	} # end else no output error	
} # end while SELECT FROM dns_server
