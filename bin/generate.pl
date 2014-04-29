#!/usr/bin/perl

###############################################################
#  This file is part of XName.org project                    #
#  See  http://www.xname.org/ for details                     #
#                                                            #
#  License: GPLv2                                            #
#  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html #
#                                                            #
#  Author(s): Yann Hirou <hirou@xname.org>                   #
###############################################################

use DBI;
use IO::Handle;
use Mail::Sendmail;
use Time::localtime;
use Time::HiRes qw(gettimeofday tv_interval);
use POSIX qw(strftime);

$t1 = [gettimeofday];

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


$LOG_PREFIX .= $str_log_generate_prefix{$SITE_DEFAULT_LANGUAGE};


########################################################################
# STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOP STOPS STOP STOP
#
# Do not edit anything below this line           
########################################################################


# 0/ generate the domains.master files for modified or created zones
# accordingly with database content

# 1/ generate the named.conf file
#   accordingly with database content

# 2/ reload named with rndc reconfig

# 3/ reload each modified zone one by one (rndc reconfig does not take them)

# 4/ warn for reload by email
#  accordingly with modified content
#   and delete from modified

# 5/ insert zone & mail & date in db







$dsn = "DBI:mysql:" . $DB_NAME . ";host=" . $DB_HOST . ";port=" . $DB_PORT;
$dbh = DBI->connect($dsn, $DB_USER, $DB_PASSWORD);

if($DB_AUTH_NAME){
        $dsnauth = "DBI:mysql:" . $DB_AUTH_NAME . ";host=" . $DB_AUTH_HOST . ";port=" . $DB_AUTH_PORT;
        $dbhauth = DBI->connect($dsnauth, $DB_AUTH_USER, $DB_AUTH_PASSWORD);
}else{
        $dbhauth=$dbh;
}

open(LOG, ">>" . $LOG_FILE);
#print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : start (" .
# tv_interval ($t1) . ")\n";


$query = "SELECT count(*) as count FROM dns_zone 
  WHERE status='D' OR status='M'";

my $sth = dbexecute($query,$dbh,LOG);

$ref = $sth->fetchrow_hashref();
$sth->finish();
$count = $ref->{'count'};

sub RetrieveRecords {
  my $type = $_[0];
  my $ret = "";

  $sth1 = dbexecute("SELECT val1,val2,val3,val4,val5,ttl
    FROM dns_record 
    WHERE zoneid='$zoneid' AND type='$type'
    ORDER BY val1",$dbh,LOG);

  while (my $ref = $sth1->fetchrow_hashref()) {
    if($ref->{'ttl'} ne "-1"){
      $ttl = $ref->{'ttl'};
    } else {
      $ttl = "\t";
    }
    $_ = $type;
    # NS: [ttl] IN NS val1
    # MX: val1 [ttl] IN MX val2 val3
    # CNAME: val1 [ttl] IN CNAME val2
    # SUBNS: val1 [ttl] IN NS val2
    # AAAA: val1 [ttl] IN AAAA val2
    # TXT: val1 [ttl] IN TXT val2
    # A: val1 [ttl] IN A val2
    # PTR: val1 [ttl] IN PTR val2
    # SRV: val1 [ttl] IN SRV val2 val3 val4 val5
    # WWW: val1 [ttl] IN A val3 (was: $SITE_WWW_IP)

    if ($type eq "TXT") {
      # single label can have at most 255 length so we need to split
      # big TXT records to pieces
      $txtval = $ref->{'val2'};
      $txtval = join(' ', map { '"'.$_.'"' } unpack('(A255)*', $txtval) ) unless $txtval =~ /"/;
      # automatically add SPF record
      if ($txtval =~ /^"v=spf/i) {
        $ret .= $ref->{'val1'} . "\t$ttl\tIN\tSPF\t" . $txtval . "\n";
      }
    }
    $ret .= do {
      if (/^NS$/)
        { "\t$ttl\tIN\tNS\t" . $ref->{'val1'} }
      elsif (/^MX$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tMX\t" . $ref->{'val2'} . "\t" . $ref->{'val3'} }
      elsif (/^CNAME$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tCNAME\t" . $ref->{'val2'} }
      elsif (/^SUBNS$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tNS\t" . $ref->{'val2'} }
      elsif (/^AAAA$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tAAAA\t" . $ref->{'val2'} }
      elsif (/^A$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tA\t" . $ref->{'val2'} }
      elsif (/^WWW$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tA\t" . $ref->{'val3'} }
      elsif (/^TXT$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tTXT\t" . $txtval }
      elsif (/^PTR$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tPTR\t" . $ref->{'val2'} }
      elsif (/^SRV$/)
        { $ref->{'val1'} . "\t$ttl\tIN\tSRV\t" . $ref->{'val2'} . "\t". $ref->{'val3'} . "\t". $ref->{'val4'} . "\t". $ref->{'val5'} }
      else
        { ";ERROR! type=$type, val1=" . $ref->{'val1'} .", val2=" .$ref->{'val2'} }
    };
    $ret .= "\n";
  }
  $sth1->finish();
  return $ret;
}

if($count){

  # retrieve list of all xname servers
  $query = "SELECT serverip,transferip FROM dns_server";
  my $sth = dbexecute($query,$dbh,LOG);

  $serverlist = "";
  $servertransferlist = "";
  while (my $ref = $sth->fetchrow_hashref()) {
    $serverlist .= $ref->{'serverip'} . ";";
    $servertransferlist .= $ref->{'transferip'} . ";";
  }
  $sth->finish();


  ########################################################################
  # GENERATING NAMED_CONF FILE
  ########################################################################

  $t0 = [gettimeofday];
  # backup named.conf
  $cpcommand="[ -f " . $NAMED_CONF_ZONES . " ] && " . $CP_COMMAND . " " . $NAMED_CONF_ZONES . " " .
  $NAMED_TMP_DIR . "named.zones.bak-" . $$;
  @output2 = `$cpcommand 2>&1`;
  if($#output2 != -1){
    print  LOG logtimestamp() . " " . $LOG_PREFIX .
      " : " . $str_log_error_can_not_backup{$SITE_DEFAULT_LANGUAGE} . "\n" .
      "\t" . $output2[0] . "\n";      
  }else{

    open(CONF, ">" . $NAMED_CONF_ZONES) ||   print LOG logtimestamp() . " " .
    $LOG_PREFIX . " : " . sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE}, $NAMED_CONF_ZONES) . "\n";

    ##############
    # primary NS #
    ##############

    $query = "SELECT c.zoneid, c.xfer,LOWER(z.zone) AS zone FROM 
      dns_confprimary c, dns_zone z, dns_user u 
      WHERE c.zoneid=z.id AND u.id=z.userid AND z.status!='B'";

    my $sth = dbexecute($query,$dbh,LOG);
    while (my $ref = $sth->fetchrow_hashref()) {
      # write to named.conf

      # Add ALL xname servers to allow-transfer list if not any
      $xferlist = $ref->{'xfer'};
      if($xferlist eq ""){
        $xferlist = "any";
      }
      if($xferlist ne "any"){
        # concatenate serverlist,servertransferlist and xferlist
        $xferlist = $serverlist . $servertransferlist . $xferlist . ";" . $SITE_WEB_SERVER_IP;
        # explode xferlist to have unique IP addresses
        undef %tmp;
        @tmp{split(/;/,$xferlist)} = ();
        @xferlist = keys %tmp;
        $xferlist = join(";",@xferlist);
      }else{
        $xferlist = "any";
      }
      ($zonef = $ref->{'zone'}) =~ s,/,\\,g;
      print CONF sprintf(
        'zone "%s" { '.
          'type master; ' .
          'file "%s"; ' .
          'allow-transfer { %s; }; ' .
          'allow-query { %s; }; ' .
        '};' ."\n",
        $ref->{'zone'},
        $NAMED_DATA_CHROOTED_DIR . $NAMED_MASTERS_DIR . $zonef,
        $xferlist,
        $NAMED_ALLOW_QUERY eq "" ? "any" : $NAMED_ALLOW_QUERY);
    }
    $sth->finish();

    ################
    # secondary NS #
    ################

    $query = "SELECT LOWER(z.zone) AS zone, c.masters, c.xfer
      FROM dns_confsecondary c, dns_zone z, dns_user u 
      WHERE c.zoneid=z.id AND u.id=z.userid AND z.status!='B'";

    $sth = dbexecute($query,$dbh,LOG);
    while (my $ref = $sth->fetchrow_hashref()) {

      if($ref->{'masters'} ne ""){
        $masters = $ref->{'masters'};
        if( $ref->{'masters'}  =~ /;$/){
          chop($masters);
        }
        $xfer = $ref->{'xfer'};
        if($ref->{'xfer'} =~ /;$/){
          chop($xfer);
        }

        # Add ALL xname servers to allow-transfer list if not any
        if($xfer ne "any"){
          # concatenate serverlist,servertransferlist and xferlist  
          $xfer = $serverlist . $servertransferlist . $xfer . ";" . $SITE_WEB_SERVER_IP;
          # explode xferlist to have unique IP addresses
          undef %tmp;
          @tmp{split(/;/,$xfer)} = ();
          @xfer = keys %tmp;
          $xfer = join(";",@xfer);
        }else{
          $xfer = "any";
        }

        ($zonef = $ref->{'zone'})=~ s,/,\\,g;
        print CONF sprintf(
          'zone "%s" { '.
            'type slave; ' .
            'notify no; ' .
            'file "%s"; ' .
            'masters { %s; }; ' .
            'allow-transfer { %s; }; ' .
            'allow-query { %s; }; ' .
          '};' ."\n",
          $ref->{'zone'},
          $NAMED_DATA_CHROOTED_DIR . $NAMED_SLAVES_DIR . $zonef,
          $masters,
          $xfer,
          $NAMED_ALLOW_QUERY eq "" ? "any" : $NAMED_ALLOW_QUERY);

      } # end if master ne ''
    } # end while

    close CONF;

    $sth->finish();

    ########################################################################

    #print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : freedns.conf done " .
    #  tv_interval ($t0) . "\n";

    ########################################################################
    # GENERATING DATA FILES
    # Primary only
    ########################################################################

    $t0 = [gettimeofday];

    $query = "UPDATE dns_zone SET status='X' WHERE status='M'";
    my $sth = dbexecute($query,$dbh,LOG);
    $query = "SELECT c.zoneid, LOWER(z.zone) AS zone, c.serial, c.refresh, z.userid,
        c.retry,c.expiry,c.minimum,c.defaultttl
        FROM  dns_confprimary c, dns_zone z
        WHERE c.zoneid = z.id AND  z.status='X'";

    my $sth = dbexecute($query,$dbh,LOG);
    while (my $ref = $sth->fetchrow_hashref()) {
      # for each zone, 

      $zone = $ref->{'zone'};
      ($zonef = $zone) =~ s,/,\\,g;
      $zoneid = $ref->{'zoneid'};
      # Retrieve MAIL
      $query = sprintf("SELECT IF(LOCATE('emailsoa=1', options), %s, 'NULL') AS email FROM %s WHERE %s='%s'",
          $DB_AUTH_FLD_EMAIL,
          $DB_AUTH_TABLE,
          $DB_AUTH_FLD_ID,
          $ref->{'userid'}
          );
      my $sthauth = dbexecute($query,$dbhauth,LOG);
      my $refauth = $sthauth->fetchrow_hashref();
      $email = $refauth->{'email'};
      if ($email eq "NULL") {
        $email = $EMAIL_SOA;
        $email =~ s/\@/+$zoneid\@/;
      }
      $email =~ s/\@/\./;
      # Retrieve Serial
      $serial = $ref->{'serial'};
      # Retrieve refresh
      $refresh = $ref->{'refresh'};
      # Retrieve retry
      $retry = $ref->{'retry'};
      # Retrieve expiry
      $expiry = $ref->{'expiry'};
      # Retrieve minimum
      $minimum = $ref->{'minimum'};
      # retrieve defaultttl
      $defaultttl = $ref->{'defaultttl'};

      # Generate & write header
      if($zone =~ /^(.*)\.([^.]+)$/){
        $origin = $2;
        $prefix = $1;
      }else{
        print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . 
          sprintf($str_log_error_zone_x_not_valid{$SITE_DEFAULT_LANGUAGE}, $zone);
      }

      $header = "\$TTL $defaultttl\n";
      $header .= "\$ORIGIN $origin.\n$prefix\t\tIN SOA\t" . $SITE_NS .
        ". $email. (\n\t\t\t$serial $refresh $retry $expiry $minimum )\n";
      $toprint = $header;
      $toprint .= RetrieveRecords("NS");
      # End of zone header, print origin $zone.
      $toprint .= "\n\n\$ORIGIN $zone.\n";

      $toprint .= RetrieveRecords("MX");
      $toprint .= RetrieveRecords("A");
      $toprint .= RetrieveRecords("WWW");
      $toprint .= RetrieveRecords("AAAA");
      $toprint .= RetrieveRecords("CNAME");
      $toprint .= RetrieveRecords("PTR");
      $toprint .= RetrieveRecords("TXT");
      $toprint .= RetrieveRecords("SRV");
      $toprint .= RetrieveRecords("SUBNS");
      $toprint .= "\n";

      # open file 
      open(DATA_FILE, ">" . $NAMED_DATA_DIR . $NAMED_MASTERS_DIR . $zonef ) or
       print LOG logtimestamp() .  " " . $LOG_PREFIX . " : " . 
        sprintf($str_log_error_opening_x{$SITE_DEFAULT_LANGUAGE}, $NAMED_DATA_DIR) .
        $NAMED_MASTERS_DIR . $zonef . "\n";
      print DATA_FILE $toprint;
      close(DATA_FILE);
    }

    $sth->finish();
    ########################################################################

    #print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : master zones done " .
    # tv_interval ($t0) . "\n";


    ########################################################################
    # Reload Named
    ########################################################################

    #  check if error. If error, DO NOT RELOAD

    $t0 = [gettimeofday];
    $error = 0;
    @result=`$CHECKCONF_COMMAND $NAMED_CONF_ZONES 2>&1`;
    # should be
    # if ( WEXITVALUE($?) )
    if ($?>>8) {
      $error = 1;
    }
    # print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : named checkconf done " .
    #   tv_interval ($t0) . "\n";
    if($error == 1){
      # mail admin
      # copy to named.conf.error, send mail, restore backup
      # where is that "restore backup"?
      $command=$CP_COMMAND . " " . $NAMED_CONF_ZONES . " " . 
        $NAMED_TMP_DIR . "named.zones.error";
      system($command);

      $msgto=$EMAIL_ADMIN;
      $msgsubject=sprintf($str_error_checking_subject_x{$SITE_DEFAULT_LANGUAGE},
        $EMAIL_SUBJECT_PREFIX);

      $message = sprintf($str_error_while_checking_conf_on_x_tmp_x{$SITE_DEFAULT_LANGUAGE},
        $SERVER_NAME, $NAMED_TMP_DIR);

      foreach(@result){
        $message .= $_;
      }
      %mail = (
        smtp  => $EMAIL_SMTP,
        To    =>  $msgto,
        From   => $EMAIL_FROM,
        Subject  => $msgsubject,
        'Content-Type' => 'text/plain; charset="' . $str_content_type{$SITE_DEFAULT_LANGUAGE} . '"',
        message  => $message,
      );

      sendmail %mail;

    }else{
      # move backup to named.conf.bak 
      $command=$MV_COMMAND . " " . 
        $NAMED_TMP_DIR . "named.zones.bak-" . $$ . " " .
        $NAMED_TMP_DIR . "named.zones.bak";
      system($command);
      # reload
      $t0 = [gettimeofday];
      system("$HELPER_COMMAND"); 
      # print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : generate helper done " .
      #   tv_interval ($t0) . "\n";
    }

    ########################################################################


    $t0 = [gettimeofday];
    ########################################################################
    # Reload all modified zones - not new zones
    ########################################################################
    $query = "SELECT LOWER(zone) AS zone, zonetype FROM dns_zone WHERE status='X'";
    my $sth = dbexecute($query,$dbh,LOG);
    $tmp_counter = 0;
    while (my $ref = $sth->fetchrow_hashref()) {
      $tmp_counter++;
      $zone = $ref->{'zone'};
      $action = $ref->{'zonetype'} eq "P" ? "reload" : "retransfer";
      if (system("$RNDC_COMMAND $action $zone") == 0)
      {
        system("$RNDC2_COMMAND retransfer $zone") == 0
          or print LOG logtimestamp() . " " . $LOG_PREFIX .
            " : " . "fns2 retransfer $zone failed: ".($?>>8)."\n";
      } else {
        print LOG logtimestamp() . " " . $LOG_PREFIX .
          " : " . "fns1 $action $zone failed: ".($?>>8)."\n";
      }
    }
    $sth->finish();
    ########################################################################

    # print LOG logtimestamp() . " " . $LOG_PREFIX . 
    #   " : DEBUG : rndc $tmp_counter reloads done " . tv_interval ($t0) . "\n";


    ########################################################################
    # Remote server command generation
    ########################################################################
    # retrieve all servers with their IP (for allow_transfer)
    $query = "SELECT id,serverip FROM dns_server  WHERE id!='1'";
    my $sth = dbexecute($query,$dbh,LOG);
    @serverlist=();
    while (my $ref = $sth->fetchrow_hashref()) {
      $serverid = $ref->{'id'};
      push(@serveridlist,$serverid);
      $serverip{$serverid}=$ref->{'serverip'};
    }
    $sth->finish();

    # list of server IPs, to be included in allow_transfer if not "any"
    # and list of master servers... 
    $masters=$SITE_NS_IP . ";";
    foreach(values(%serverip)){
      $masters .= $_ . ";";
    }

    ########################################################################


    $t0 = [gettimeofday];
    ########################################################################
    # retrieve emails & warn
    ########################################################################

    $query = "SELECT id, LOWER(zone) AS zone, userid FROM dns_zone WHERE status='X'";

    my $sth = dbexecute($query,$dbh,LOG);
    while (my $ref = $sth->fetchrow_hashref()) {
      $query = sprintf("SELECT %s as email,%s as lang
             FROM %s WHERE %s='%s'",
          $DB_AUTH_FLD_EMAIL,
          $DB_AUTH_FLD_LANG,
          $DB_AUTH_TABLE,
          $DB_AUTH_FLD_ID,
          $ref->{'userid'});
      my $sthauth = dbexecute($query,$dbhauth,LOG);
      my $refauth = $sthauth->fetchrow_hashref();

      if(!exists($zonelist{$refauth->{'email'}})){
        $zonelist{$refauth->{'email'}} = "";
      }
      $zonelist{$refauth->{'email'}} .= $ref->{'zone'} . ";";
      $lang{$refauth->{'email'}} .= $refauth->{'lang'};

      my $sth2 = dbexecute("UPDATE dns_zone SET status='' WHERE id='" .
      $ref->{'id'} . "'",$dbh,LOG);
    }
    $sth->finish();

    # send an email per user for all zones

    while(($email,$listzones) = each(%zonelist)){
      $msgto=$email;
      $listzones =~ s/;/\n/g;
      if(exists($str_content_type{$lang{$email}})){
        $userlang = $lang{$email};
      }else{
        $userlang = $SITE_DEFAULT_LANGUAGE;
      }
      $msgsubject = sprintf($str_reload_mail_subject_x_x{$userlang},
        $EMAIL_SUBJECT_PREFIX, $SITE_NS);

      $message = sprintf($str_reload_mail_content_x_x_x{$userlang},
        $SITE_NS,$SITE_NAME,$listzones);

      $message .=  $EMAIL_SIGNATURE;
      $message .= "\n\n\n\n";

      %mail = (
        smtp  => $EMAIL_SMTP,
        To    =>  $msgto,
        From   => $EMAIL_FROM,
        Subject  => $msgsubject,
        'Content-Type' => 'text/plain; charset="' . $str_content_type{$userlang} . '"',
        message  => $message,
      );

      if(!sendmail %mail) {
        require $XNAME_HOME . "strings/" . $EMAIL_DEFAULT_LANGUAGE . "/strings.inc";
        print LOG logtimestamp() . " " . $LOG_PREFIX . " : " . 
          sprintf($str_log_error_sending_email_x{$SITE_DEFAULT_LANGUAGE},
            $Mail::Sendmail::error)
          . "\n";
      }
    } # end while each %zonelist
    ########################################################################
    # print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : sending mails done " . 
    #   tv_interval ($t0) . "\n";

  } # end named.conf backup successfull

} # end count of zone modified >= 1 

# Disconnect from the database.
$dbh->disconnect();
#print LOG logtimestamp() . " " . $LOG_PREFIX . " : DEBUG : stop : count=". 
#  $count ." (" . tv_interval ($t1) . ")\n";
close LOG;
