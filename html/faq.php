		<!-- box beginning "FAQ" -->
		<table border="0" width="100%">
		<tr class="boxtitle"><td class="boxtitle">FAQ</td></tr>
		<tr class="boxtext"><td class="boxtext"><table border="0" width="100%"><tr><td colspan="2"><a href="faq.php?#sect2"
		class="boxheader">Registrar Configuration</a></td></tr>
		<tr><td width="20">&nbsp;</td>
				<td><a href="?#item3">I use XName primary name service. What Name Server is serving my zone ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item4">I use XName secondary name service. Which Name Server is serving my zone ?</a></td></tr><tr><td colspan="2"><a href="faq.php?#sect3"
		class="boxheader">XName web interface</a></td></tr>
		<tr><td width="20">&nbsp;</td>
				<td><a href="?#item5">Can I have only one NS record, the default one (ns0.xname.org) ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item6">What is the difference between primary and secondary ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item7">Can I register IPv6 records ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item17">Does XName allow dynamic updates ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item9">Which zones can I register ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item10">Can I register in-addr.arpa zone ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item18">Can I use * CNAME records ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item19">Can I delete my user ?</a></td></tr><tr><td colspan="2"><a href="faq.php?#sect5"
		class="boxheader">XName DNS configuration</a></td></tr>
		<tr><td width="20">&nbsp;</td>
				<td><a href="?#item11">What is the reload frequency of your DNS server ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item12">I would like to have other parameters  in your DNS config, like allow-update, etc...</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item13">Can I change my zone ttl, refresh, etc... ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item20">Why is my domain name appended twice to my record ?</a></td></tr><tr><td colspan="2"><a href="faq.php?#sect6"
		class="boxheader">XName Philosophy</a></td></tr>
		<tr><td width="20">&nbsp;</td>
				<td><a href="?#item14">Why is it free ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item15">I think it's a great idea. How can I contribute ?</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a href="?#item16">My question is not listed...</a></td></tr><tr><td colspan="2"><hr /></td></tr><tr><td colspan="2"><a name="sect2"
		class="boxheader">Registrar Configuration</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item3" class="boxheader">I use XName primary name service. What Name Server is serving my zone ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>XName has two name servers.<br />
They are:
<ul>
<li>ns0.xname.org, IP address 213.11.111.252.</li>
<li>ns1.xname.org, IP address 213.133.115.5.</li>
</ul>
Both are serving your zone.<br />

When registering a new domain, you have to give these parameters to your registrar.<br />
</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item4" class="boxheader">I use XName secondary name service. Which Name Server is serving my zone ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>XName has two name servers.<br />
They are:
<ul>
<li>ns0.xname.org, IP address 213.11.111.252.</li>
<li>ns1.xname.org, IP address 213.133.115.5.</li>
</ul>
Both are serving your zone.<br />

When registering a new domain, you have to give these parameters to your registrar.<br />

Please configure your primary name server to accept zone transfers from 213.11.111.252 and 213.133.115.2 (warning, ns1 requests transfers from a different IP as its canonical one).</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td colspan="2"><a name="sect3"
		class="boxheader">XName web interface</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item5" class="boxheader">Can I have only one NS record, the default one (ns0.xname.org) ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes, you can.<br>
But for redundancy, every zone should be served by at least two name servers, located on different networks.<br>
XName provides you two name servers, so you don't need to find another one. just use ns0.xname.org and ns1.xname.org.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item6" class="boxheader">What is the difference between primary and secondary ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>A primary name server acts as "Master".<br>
It means that all information are stored on it, and all
modification has to be done on it.<br>
A secondary name server acts as "Slave".<br>
All its information come from the master.<br>
There can only be one primary server. As for the secondary servers, you can have as many as you want: the information they have about your zone will be replicated from the primary server.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item7" class="boxheader">Can I register IPv6 records ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Not on primary.<br />
Our interface doesn't accept IPv6 records at this time.
<br />
But you can have IPv6 records in a secondary zone.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item17" class="boxheader">Does XName allow dynamic updates ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes.<br />
See our <a href="http://www.xname.org/doc/dynamic-update.php" class="linkcolor">Dynamic Update page</a> for details. </td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item9" class="boxheader">Which zones can I register ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>All zones are accepted (except TLD).<br>
It means you can register bar.com, foo.bar.com, but not foo (like com, net, info...)<br>
All TLD are accepted (.com, .net, .info, .fr, .in-addr.arpa...)
</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item10" class="boxheader">Can I register in-addr.arpa zone ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes.<br></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item18" class="boxheader">Can I use * CNAME records ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes, but be sure to know what you are doing... </td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item19" class="boxheader">Can I delete my user ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes. <br />
When logged in, you have a link on the left named "Delete your account". <br />
All your zones will be deleted from our name servers.<br />
if you are administrator of your group and have sub-users, all users of your group will also be deleted.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td colspan="2"><a name="sect5"
		class="boxheader">XName DNS configuration</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item11" class="boxheader">What is the reload frequency of your DNS server ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>The server is currently reloaded every hour.<br>
If the server load increases to much, we will schedule the reload not so often.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item12" class="boxheader">I would like to have other parameters  in your DNS config, like allow-update, etc...</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Just ask. We will take a look at your needs, and if it looks interesting for everyone and it can be added without too much change in our architecture, we will implement it. For example allow-transfer parameter has been added.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item13" class="boxheader">Can I change my zone ttl, refresh, etc... ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Yes.<br />
When using advanced interface (you have a checkbox in your preference panel), SOA and TTL will be modifiable. <br />
Default values are : <br>
TTL : 1D<br>
refresh : 3H<br>
retry : 1H<br>
expiry : 1W<br>
minimum : 1D</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item20" class="boxheader">Why is my domain name appended twice to my record ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>If you have registered a CNAME (or Name Server), and <tt>nslookup</tt> or <tt>host</tt> command output looks like:<br />
<tt>www.mydomain.com is an alias for machine.myotherdomain.com.mydomain.com</tt><br />
(or <tt>mydomain.com name server ns.mydomain.com.mydomain.com</tt>)<br />
this is because you forgot the trailing dot <strong>.</strong> in your DNS configuration. Simply modify your zone, and add the trailing dot.<br />
Remember: each time you put a fully qualified name in your DNS configuration, you have to append a trailing dot.
</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td colspan="2"><a name="sect6"
		class="boxheader">XName Philosophy</a></td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item14" class="boxheader">Why is it free ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>It's free because too much people earn money with a non-reactive and non-user friendly name service. The only way to change this is to provide a better service, cheaper.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item15" class="boxheader">I think it's a great idea. How can I contribute ?</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Have a look at the <a href="contribute.php">contribute section</a>.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr><tr><td width="20">&nbsp;</td>
				<td><a name="item16" class="boxheader">My question is not listed...</a></td></tr>
				<tr><td width="20">&nbsp;</td>
				<td>Send us an email at <a href="mailto:xname@xname.org">xname@xname.org</a>. <br>
Be careful, only non-FAQ will be answered.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr></table></td></tr>
		</table>
		<!-- box end "$title" -->
