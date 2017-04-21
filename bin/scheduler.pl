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

use Time::localtime;
use POSIX qw(strftime);

# *****************************************************
# Where am i run from
$0 =~ m,(.*/).*,;
$XNAME_HOME = $1;

require $XNAME_HOME . "config.pl";

$LOG_PREFIX .='scheduler';


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################

# loop with a timer.
# timers for: $DELAY_GENERATE $DELAY_INSERTLOGS $DELAY_RETRIEVE_REMOTE_LOGS
#             $DELAY_OPTIMIZE


if(!$SCHEDULER_RUN_AS_DAEMON){
	print "\n";
	print $0 . " can not be run as daemon. \n";
	print "Modify \$SCHEDULER_RUN_AS_DAEMON variable in bin/config.pl \n";
	print "if you want to use a scheduler instead of using crontab.\n\n";
	exit(1);
}

my $timer = 0;
my $lasttime = time();
my $sleeptime = 60;
my $end = 0;

my $lastgenerate=0;
my $lastinsertlogs=0;
my $lastretrieveremotelogs=0;
my $lastoptimize=0;

# convert $DELAY* vars into seconds
@listofdelay = ('GENERATE','INSERTLOGS',
				'RETRIEVE_REMOTE_LOGS','OPTIMIZE');
foreach(@listofdelay){
	$myvar = '$DELAY_' . $_;
	$txt = $_;
	$currentvar = eval($myvar);
	$currentvar =~ /^(.*)([H|M|D])/;
	if($2 eq 'H'){
		$delay{$txt} = $1 * 3600;
	}else{
		if($2 eq 'M'){
			$delay{$txt} = $1 * 60;
		}else{
			if($2 eq 'D'){
				$delay{$txt} = $1 * 24*3600;
			}
		}
	}
}


# infinite loop
while(!$end){
	$currenttime = time();
	$difftime = $currenttime - $lasttime;
	$lasttime=$currenttime;
	
	# GENERATE
	$lastgenerate += $difftime;
	if($lastgenerate >= $delay{'GENERATE'}){
		system($XNAME_HOME . "delete.pl");
		system($XNAME_HOME . "generate.pl");
		if($MULTISERVER){
			system($XNAME_HOME . "pushtoservers.pl");
		}
		$lastgenerate = 0;	
	}
	
	# INSERTLOGS
	$lastinsertlogs += $difftime;
	if($lastinsertlogs >= $delay{'INSERTLOGS'}){
		system($XNAME_HOME . "insertlogs.pl");
		$lastinsertlogs = 0;
	}
	
	# retrieveremotelogs
	if($MULTISERVER){
		$lastretrieveremotelogs += $difftime;
		if($lastretrieveremotelogs >= $delay{'RETRIEVE_REMOTE_LOGS'}){
			system($XNAME_HOME . "getremotelogs.pl");
			$lastretrieveremotelogs = 0;
		}
	}
	
	# optimize
	$lastoptimize += $difftime;
	if($lastoptimize >= $delay{'OPTIMIZE'}){
		system($XNAME_HOME . "sqloptimize.pl");
		$lastoptimize = 0;
	}
	
	
	sleep($sleeptime);
} # end infinite loop (while(!$end))
