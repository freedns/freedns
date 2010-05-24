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
use Date::Parse;

# *****************************************************
# Where am i run from
$0 =~ m,(.*/).*,;
$XNAME_HOME = $1;

require $XNAME_HOME . "config.pl";
require $XNAME_HOME . "xname.inc";

# load all languages
if(opendir(DIR,$XNAME_HOME . "strings")){
        foreach(readdir(DIR)){
                if(/^[a-z][a-z]$/){
                        require $XNAME_HOME . "strings/" . $_ . "/strings.inc";
                }
        }
        closedir(DIR);
}else{
        print "ERROR: no language available";
}

$LOG_PREFIX.= $str_log_insertlogs_prefix{$SITE_DEFAULT_LANGUAGE};


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################


# parse log file, look for named logs
# retrieve : - date (timestamp(14) format),
# 			 - zone name
#            - status (Error, Information, Warning)

# delete logs older than 3 hours

# TODO : manage case Secondary AND primary
# 	==> must have a table with server & zone & type
# 		and parse logs on each server (no centralized 
# 		management)

# Current solution : print logs whenever primary or secondary
# or both

# connect to DB
# retrieve last parsed line

# open file
# go to last parsed line
# split line
# insert in DB
# next line


# connect to DB
$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

open(LOG, ">>" . $LOG_FILE);


# retrieve last parsed line
$query = "SELECT line FROM dns_logparser";
$sth = dbexecute($query,$dbh,LOG);
$ref = $sth->fetchrow_hashref();
$lastline = $ref->{'line'};


# open file
if(-e($SYSLOG_FILE)){
	open(FILE, "< " . $SYSLOG_FILE) || die sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE},$SYSLOG_FILE);
	# go to last parsed line
	if($lastline ne ""){
		$currentline=<FILE>;
		# compare currentline date and $lastline date
		# Apr 14 04:25:44 unlimited
		$currentline =~ /^([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+/;
		$currentlinedate=getTimestamp($1,$2,$3);

		$lastline =~ /^([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+/;
		$lastlinedate=getTimestamp($1,$2,$3);

		# if currentline date > lastline date, file has been rotated
		if($currentlinedate < $lastlinedate){
			while(<FILE> ne $lastline){
			}
		}
	}
	# if no line is read, don't save last read line !
	$readline = 0;
	
	while(<FILE>){
		$line = $_;
		my $status;
		my $zonename;
		my $content;
		my $newcontent;
		
		if (ord(substr($line,-1))!=10) {break;}
		$readline++;
	 	if(/$LOG_PATTERN_NAMED$/){
			# $1 : month
			# $2 : day
			# $3 : hour:min:sec
			if ($LOG_USE_NAMED_SEVERITY) {
				$status = $4;
				$content = $5;
			} else {
				$content = $4;
			}
	
			# split line
			my $timestamp = getTimestamp($1,$2,$3);
		
			# retrieve zonename...
			if($content =~ /$LOG_PATTERN_ZONE/){
				$zonename = $2;
			}else{
		
				if($content =~ /$LOG_PATTERN_FILE/){
					$zonename = $2;
				}else{	
					print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . 
						$str_log_not_matching{$SITE_DEFAULT_LANGUAGE} . " : $content\n";
				}
			}
		
			# status : Error, Warning, Information
			if ($LOG_USE_NAMED_SEVERITY) {
			$_ = $status;
			$status = do {
				if (/^info$/)
					{ "I" }
				elsif (/^warning$/)
					{ "W" }
				elsif (/^error$/)
					{ "E" }
				else
					{ "U" }
				};
			} else {
	
				# remove zonename from matching words
				$newcontent=$content;
				$newcontent =~ s/$zonename/ /g;
				if($newcontent =~ /(failed|failure|non-authoritative|denied|exceeded|expired)/){
					$status = 'E';
				}else{
					if($newcontent =~ /(started|transferred|end of transfer|loaded|sending notifies)/){
						$status = 'I';
					}else{
						$status = 'W';
					}
				}
			}	
			# insert in DB
			# escape from mysql... 
			$zonename =~ s/'/\\'/g;
			$content =~ s/'/\\'/g;
			$zonename =~ s/"/\\"/g;		
			$content =~ s/"/\\"/g;
		
			# select zoneid
			$query = "SELECT id FROM dns_zone WHERE zone='" . $zonename .
			"'";
			$sth = dbexecute($query,$dbh,LOG);
			$ref = $sth->fetchrow_hashref();
			if(!$ref->{'id'}){
				# check if zone exists with a "." at the end
				$query = "SELECT id FROM dns_zone WHERE zone='" . $zonename .
				".'";
				$sth = dbexecute($query,$dbh,LOG);
				$ref = $sth->fetchrow_hashref();
				if($ref->{'id'}){
			
					$query = "INSERT INTO dns_log (zoneid, date, content, status)
					VALUES ('" . $ref->{'id'} . "','" . $timestamp . "','" . $content . 
					"','" . $status . "')";

					$sth = dbexecute($query,$dbh,LOG);
				}
			}else{
				$query = "INSERT INTO dns_log (zoneid, date, content, status)
				VALUES ('" . $ref->{'id'} . "','" . $timestamp . "','" . $content . 
				"','" . $status . "')";
				$sth = dbexecute($query,$dbh,LOG);
			}
		
		}else{
	#		print "DONT MATCH : $_\n";
		}
	}
	close(FILE);

	# save last line in DB
	if($readline){
		$line =~ s/'/\\'/g;
		$line =~ s/"/\\"/g;
		$query = "DELETE FROM dns_logparser where 1>0";
		$sth = dbexecute($query,$dbh,LOG);

		$query = "INSERT INTO dns_logparser (line) values ('" . $line . "')";
		$sth = dbexecute($query,$dbh,LOG);
	}

}else{ # file don't exist
	print LOG sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE},$SYSLOG_FILE);
}

deleteOldLogs($LOG_HOURS_TO_KEEP,$LOG_NB_MIN_TO_KEEP);



close(LOG);








# ###################################################################"


sub deleteOldLogs(){
	# delete logs older than $nbhours minutes
	# if more than $nblogs log entries
	$nbhours = @_[0];
	$nblogs = @_[1];

	# count number of logs younger than $nbhours PER ZONE
	# if less than $nblogs, keep $nblogs-result and delete others

	# retrieve all zones having more than $nblogs logs.
	$query = "SELECT zoneid,COUNT(*) as count FROM dns_log
			GROUP BY zoneid HAVING count > " . $nblogs;
	$sth = dbexecute($query,$dbh,LOG);
	while(my $ref = $sth->fetchrow_hashref()){
		$zoneid = $ref->{'zoneid'};
		$count = $ref->{'count'};
	
		# count logs younger than $timestamp
		$query = "SELECT COUNT(*) AS count FROM dns_log
			WHERE date > NOW() - INTERVAL " . $nbhours . " HOUR
			AND zoneid='" . $zoneid . "'";

		my $sth2 = dbexecute($query,$dbh,LOG);
	
		$ref2 = $sth2->fetchrow_hashref();
		$nb = $ref2->{'count'};
		$sth2->finish();
		
		if($nb < $nblogs){
			# keep everyting until nblogs
			# count total of logs 
			# delete ascending until total - nblogs
			if($count > $nblogs){
				$nbtodelete = $count - $nblogs;
				$query = "DELETE FROM dns_log
						WHERE zoneid='" . $zoneid . "'
						LIMIT " . $nbtodelete;
				# XXX: one day, when all are mysql 4.x.x, add
				# "ORDER BY date" to above query. By that time
				# pray that this DELETE does not remove
				# most recent records. :-(
				my $sth2 = dbexecute($query,$dbh,LOG);
				$sth2->finish();
			}
			
	
		}else{ # $nb >= $nblogs
			# more than $nblogs will be not deleted => ok to delete all
			# before timestamp
			$query = "DELETE FROM dns_log WHERE
				date < NOW() - INTERVAL " . $nbhours . " HOUR
				AND zoneid='" . $zoneid . "'";
			my $sth2 = dbexecute($query,$dbh,LOG);

		}

	} # end while zoneid
	
} # end deleteoldlogs


# ###################################################################"

sub getTimestamp(){
	my $month = @_[0];
	my $day = @_[1];
	my $time = @_[2];

	my $result;
	my $monthpart;
	my $daypart;
	my $timepart;
	my $year;
	
	if(!($month =~ /[0123456789]/)){
		$monthpart = sprintf("%02d", getMonthNumber($month));
	}else{
		$monthpart = sprintf("%02d", $month);
	}
	$daypart = sprintf("%02d", $day);
	
	$timepart = $time;
	$timepart =~ s/\.\d{3}$//g;
	$timepart =~ s/://g;

	# WARNING : what will happen 31/12 and 01/01 ?	
	$year = localtime->year() + 1900;

	#PK#if(localtime->mon() == 0){ # Jan
	#PK#	if($monthpart ne "01"){ # month != jan ==> dec or previous
	if(localtime->mon() < 3){ # Jan or Feb
		if($monthpart ne "01" && $monthpart ne "02"){ # month != jan ==> dec or previous
			$year = $year - 1;
		}
	}

	$result = $year . $monthpart . $daypart . $timepart;
	return $result;
}




# ###################################################################"

sub getMonthNumber(){
	my $month = @_[0];
	my $result;
	my $i;
	my @months = ("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	$i=1;
	foreach(@months){
		if($month eq $_){
			return $i;
		}
		$i++;
	}
	return "00";
}


