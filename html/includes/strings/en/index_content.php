<?
$content = '
  <dt>fns2 is dead, long live fns2! [2021-06-24]</dt>
  <dd>OVH has finally refused to restore the service under old IP. Since they have not replied
  to my questions regarding continued sponsorship, and since they have offered a compensation
  voucher for the fire destruction, I have just bought a VPS on my own.</dd>

  <dd style="font-size:large">fns2.42.pl has new IPs: 51.38.99.90 &amp; 2001:41d0:701:1100::66c</dd>

  <dd>For most of you, things will just start working as they were before. But there will be
  some that will have to fix the IP in various places, e.g.:
  <ul><li>firewalls<li>hidden master DNS setup<li>DNS server ACLs (allow-transfer, also-notify, etc)
  <li>if you used your own NS name with old IP instead of fns2.42.pl name (update glue as well)</ul>

  <dt>fns1 failure [2021-06-21]</dt>
  <dd>I am very sorry for fns1 problems, they were totally on me this time: have not been tending to it for quite some time, and disks got full with logs. It is fixed now, with extra measures preventing this class of problems in the future.</dd>
<!--
  <dt>fns2 failure [2021-06-20]</dt>
  <dd>It seems OVH cannot restore fns2 with the same IP address. I am looking for other options, will keep you posted.</dd>
-->
  <dt>fns2 failure [2021-03-10]</dt>
  <dd>fns2 is hosted at OVH, unfortunately at SBG2 which was destroyed by the fire.
  Awaiting replacements.</dd>

  <dt>CAA records [2017-09-19]</dt>
  <dd>FreeDNS now supports CAA records. You can enable them in user options.</dd>

  <dt>FNS2 IP change [2017-01-23]</dt>
  <dd>New IP for fns2.42.pl is <b>193.70.13.218</b> and <b>2001:41d0:1000:2ada::42</b></dd>
  <dd>Everything should be working now. Please make necessary changes at your domain providers, ISPs, firewalls, etc.</dd>

  <dt>FNS2 IP change [2016-12-27]</dt>
  <dd>Current benefactor (after some ownership changes) no longer wishes to serve fns2 pro bono.</dd>
  <dd>I am looking for a replacement right now. Please be ready to change the fns2 IP (zones, registrars, ACLs, etc.) by the end of January 2017.</dd>
  <dd>There will be further announcements with details, of course. Stay tuned.</dd>

  <dt>No more NOTIFY for slave zones.</dt>
  <dd>The NOTIFY has been disabled for slave zones.
  If you encounter problems with it, please email me.</dd>
  <dt>Dynamic DNS</dt>
  <dd>
        For automatic records update we use XML RPC; there is a simple
        <a href="freedns-dyndns.py">python script</a> for easy IP changes.</p>
        <p style="margin-top:10px;">
  </dd>
  <dd>
        It is enough to call the script as<br>
        <tt><nobr>freedns-dyndns.py --newaddress <i>new.ip.add.ress</i></nobr></tt><br>
        or even simpler<br>
        <tt><nobr>freedns-dyndns.py --newaddress <i>&lt;dynamic&gt;</i></nobr></tt><br>
        to update record to your public IP (watch for proxies!)
  </dd>
  <dd>
        <p style="font-size:80%; margin-top:10px; margin-bottom:10px; margin-left:40px; margin-right:40px;">You may want
        to fix default values in the script itself so you do not have to pass on the
        params in the commandline. Old IP can be \'*\', no need to have it written down.
        </p>

        Another way is to use any dynamic dns service (like DynDNS), and have a CNAME
        record pointing there.
  </dd>
  <dt>Idea</dt>
  <dd>
  Free DNS service for the self-reliant people who want to experiment on their
  own, or keep all their zones in a single place.</dd>

  <dd>
  We offer primary and secondary name server, automatic reloads every 15 minutes,
  SRV, TXT and IPv6 support, and unique feature of web redirections / frames (see our
  pseudo-record WWW).
  <br>
  All zones (both primary and secondary) served by primary server
  &mdash; fns1.42.pl (79.98.145.34) &mdash; are automatically replicaated to our
  secondary server &mdash; fns2.42.pl (193.70.13.218 and 2001:41d0:1000:2ada::42).
  </dd>

  <dd>If registrar needs personal info and/or zone admin email, it would be yours,
  not mine. It will be you maintaining the domain, not me. Do not put me as an
  admin of your domain.</dd>

  <dt>Contact me</dt>
  <dd>
  If you have problems write me at
  <a href="mailto:freedns at 42 dot pl" class="linkcolor">
  freedns at 42 pl</a>. Please be patient &mdash; this is free service and I answer
  questions when I have time.
  </dd>
  </dl>
';

$thanks_content = '
<dl id="thanks">
      <dt>site beautification</dt>
      <dt>neverending css and html fixes</dt>
      <dd>Kaja Mikoszewska</dd>
      <dt>fns1 link sponsorship</dt>
      <dt>long nights fixing the machine</dt>
      <dd>Paweł Tyll</dd>
      <dt>code security audit</dt>
      <dd>Sławomir Błażek</dd>
      <dt>system support</dt>
      <dd>Michał Suszko</dd>
      <dt>fns2 server and link sponsorship (2010-2016)</dt>
      <dd>Sylwester Biernacki</dd>
      <dt>fns2 server and link sponsorship (2017-)</dt>
      <dd>OVH PL</dd>
      <dt>original code author</dt>
      <dd>Yann Hirou</dd>
      <dt>bugfixes, improvements and new features</dt>
      <dt>system administration</dt>
      <dd>Piotr Kucharski</dd>
</dl>
';
?>
