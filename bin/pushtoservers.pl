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
                if(/^[^\.]*$/){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX .=$str_log_pushtoservers_prefix{$SITE_DEFAULT_LANGUAGE};


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################

# list content of $REMOTE_SERVER_DIRS
# group by IP
# if more than one for a given IP, concatenate both in newer & delete old
# scp to remote server
# if success, delete uploaded files

$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);

opendir(DIR, $REMOTE_SERVER_DIR) ||  print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . 
		sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE}, $REMOTE_SERVER_DIR) . "\n";
@list=();
while($item=readdir(DIR)){
	if(($item eq ".") or ($item eq "..")){
	}else{
		push(@list,$item);
	}
}

@sortedlist=sort(@list);
$previousip="";
$previousfile="";
foreach(@sortedlist){
	$file = $_;
	# extract IP & timestamp
	if($file =~ /^(.*)-(.*)$/){
		$ip=$1;
		$timestamp=$2;
		if($ip eq $previousip){
			# concatenate both files in newer
			open(OLD,"< " . $REMOTE_SERVER_DIR . $previousfile) || print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE}, 
			$REMOTE_SERVER_DIR . $previousfile) . "\n";
			open(NEW,">> " . $REMOTE_SERVER_DIR . $file) || print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE},  
			$REMOTE_SERVER_DIR . $file) . "\n";
			while(<OLD>){
				print NEW $_;
			}
			close(OLD);
			close(NEW);
			unlink($REMOTE_SERVER_DIR . $previousfile) || print LOG logtimestamp() . " " . $LOG_PREFIX
			 . " : " . sprintf($str_log_error_deleting_x{$SITE_DEFAULT_LANGUAGE},
						$REMOTE_SERVER_DIR . $previousfile) . "\n";		
		}
		$previousip = $ip;
		$previousfile= $file;
		$finalfilelist{$ip}=$file;
	} # end file match
}

foreach(values(%finalfilelist)){
	$file = $_;
	$file =~ /^(.*)-.*$/;
	$ip=$1;
	# retrieve sshlogin
	$query = "SELECT sshlogin, pathonremote, sshport FROM dns_server 
				WHERE sshhost='" . $ip . "'";
	my $sth = dbexecute($query,$dbh,LOG);
	$ref = $sth->fetchrow_hashref();
	$sth->finish();
	
	$command = $SCP_COMMAND  . " -P " . $ref->{'sshport'} . " " . 
				$REMOTE_SERVER_DIR . $file .
				" " . $ref->{'sshlogin'} . "@" . $ip . ":" .
				$ref->{'pathonremote'} . " 2>&1";
	@output = `$command`;
	if($#output>=0){
		print LOG logtimestamp() . " " . $LOG_PREFIX . " " . 
			sprintf($str_log_error_executing_x{$SITE_DEFAULT_LANGUAGE},$command) . ": \n";
		foreach(@output){
			print LOG logtimestamp() . " " . $LOG_PREFIX . "		" . $_;
		}
	}else{
		unlink($REMOTE_SERVER_DIR . $file) || print LOG logtimestamp() . " " . $LOG_PREFIX
		 . " : " . sprintf($str_log_error_deleting_x{$SITE_DEFAULT_LANGUAGE},$REMOTE_SERVER_DIR . $file). "\n";		
	}
}
