<?
$title = $l['str_contact_title'];
$googleplus = '<p align="center"><a href="https://plus.google.com/112615986755984549471" rel="publisher">Google+</a>';
$content = sprintf($l['str_contact_for_bugs_contact_us'] . $googleplus,
                    $config->sitename, '', '‮' . strrev($config->emailto));
print $html->box('contact',$title,$content);

$title = $l['str_sponsor_title'];
$content = '<p>
<a href="http://nitronet.pl"><img src="images/nitronet.png" alt="Nitronet"></a><br>
<a href="http://www.plix.pl"><img src="images/plix.png" alt="PLIX"></a><br>
<a href="/index.php'.$link.'&what=thanks">' . $l['str_thanks'] . '</a>
</p>';
print $html->box('sponsor', $title, $content);

$title = $l['str_contribute_title'];
$content = sprintf($l['str_contribute_content'], $config->sitename);
print $html->box('contribute', $title, $content);

?>
