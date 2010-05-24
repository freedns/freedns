###############################################################
#	This file is part of XName.org project                    #
#	See	http://www.xname.org/ for details                 #
#	                                                          #
#	License: GPLv2                                            #
#	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html #
#	                                                          #
#	Author(s): Yann Hirou <hirou@xname.org>                   #
###############################################################

sub logtimestamp {
	$timestamptxt = strftime("%Y%m%d %H:%M:%S", CORE::localtime);
	return $timestamptxt;
} # end logtimestamp

sub dbexecute {
	my $query = $_[0];
	my $dbh = $_[1];
	my $fh = $_[2];

	my $sth = $dbh->prepare($query);
	if(!$sth){
		print $fh logtimestamp() . " " . $LOG_PREFIX . " : " . $str_log_error{$SITE_DEFAULT_LANGUAGE} . ":" . $dbh->errstr . "\n";
	}
	if (!$sth->execute) {
		print $fh logtimestamp() . " " . $LOG_PREFIX . " : " . $str_log_error{$SITE_DEFAULT_LANGUAGE} . ":" . $sth->errstr . "\n";
	}
	return $sth;
}

return 1;
