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

  <dt>fns2 failure [2021-03-10]</dt>
  <dd>fns2 is hosted at OVH, unfortunately at SBG2 which was destroyed by the fire.
  Awaiting replacements.</dd>

  <dt>CAA-Einträge [2017-09-19]</dt>
  <dd>
  FreeDNS unterstützt nun auch CAA-Einträge. Sie können diese in Ihren Benutzeroptionen aktivieren.
  </dd>

  <dt>FNS2 IP Änderung [2017-01-23]</dt>
  <dd>
  Das neue IP von fns2.42.pl ist <b>193.70.13.218</b> und <b>2001:41d0:1000:2ada::42</b></p>
  Alles sollte jetzt funktionieren. Bitte nehmen Sie die erforderlichen Änderungen
  an Ihren Domainanbietern, Firewalls, usw.
  </dd>

  <dt>Idea</dt>
  <dd>
  Das ist freie DNS-Hosting bei 42.pl.<br>
  Versuchen Sie, um die Schnittstelle zu erhalten, sollte es hoffentlich OK.
  </dd>

  <dd>
  /PL/ A jesli jestes Polakiem i widzisz ten komunikat, to oznacza,
  ze jezyk polski nie jest domyslnym jezykiem w przegladarce!
  Warto to sobie poprawic tak czy inaczej. :-)
  </dd>
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
