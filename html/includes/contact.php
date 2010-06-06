<?
$title = $l['str_contact_title'];
$content = sprintf($l['str_contact_for_bugs_contact_us'], $config->sitename, $config->emailto, $config->emailto);
print $html->box('contact',$title,$content);

$content = '<p>
<a href="http://nitronet.pl"><img src="images/nitronet.png" alt="Nitronet"></a><br>
<a href="http://www.plix.pl"><img src="images/plix.png" alt="PLIX"></a>
</p>';
print $html->box('sponsor', $l['str_sponsor_title'], $content);

$title = $l['str_contribute_title'];
$content = sprintf($l['str_contribute_content'], $config->sitename);
print $html->box('contribute',$title,$content);

?>
