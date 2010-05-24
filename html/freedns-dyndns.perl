#!/usr/bin/perl

#################################################################
#								#
# (c) 2003 Szymon *sasha* Wojnarowski <szymon@wojnarowski.net> 	#
#								#
#              dyn_fns.pl Wersja: 1.0 (2003-05-22)		#
#								#
#    Skrypt sluzy do aktualizacji IP w serwisie FreeDNS::42   	#
#    znajdujacego sie pod adresem http://freedns.42.pl/		#
#                 						#
#              http://www.wojnarowski.net/dyn_fns/              #
#								#
#################################################################

require RPC::XML;
require RPC::XML::Client;
use Unix::Syslog qw(:macros);
use Unix::Syslog qw(:subs);

($IP_new = `/sbin/ifconfig ppp0`) =~ s/^.*addr:(\S+).*$/$1/s;

############################
#		           #
# Dane konfiguracyjne      #
#  		      	   #
############################

$tmp ={user => 'login',
	password => 'haslo',
	zone => 'domena.pl',
	name => 'domena.pl.',
	oldaddress => '*',
	newaddress => $IP_new,
	ttl => '600'};

$LOG = LOG_LOCAL7;

############################


$IP_old = "0";
if (-e "/tmp/dyn_fns.ip") {
    open(FILE, "</tmp/dyn_fns.ip") || die;
    while(<FILE>){
	$IP_old = $_;
	};
    close FILE;
};

openlog "dyn_fns","" , $LOG;
if ($IP_new eq $IP_old) {
    syslog LOG_INFO, "Update is not necessary";
} else {
    $cli = RPC::XML::Client->new('https://freedns.42.pl/xmlrpc.php');
    $resp = $cli->send_request('xname.updateArecord', $tmp);
    %tt = %{$resp->value};
    foreach (keys %{$resp->value}) {
	if ($_ =~ /serial/ and $tt{$_} =~ /^\d+$/) {
	    open (FILE,">/tmp/dyn_fns.ip") || die;
	    print FILE $IP_new;
    	    close FILE;
    	    syslog LOG_INFO, "Update good and successful, IP updated.";
	    exit;
	} else {
	    syslog LOG_INFO, "Update fail, $tt{$_}";
	    exit;
	};
    };
};
closelog;
