<?
$title = $l['str_contact_title'];
$content = sprintf($l['str_contact_for_bugs_contact_us'], $config->sitename, $config->emailto, $config->emailto);
print $html->box('contact',$title,$content);

$title = $l['str_contribute_title'];
$content = sprintf($l['str_contribute_content'], $config->sitename);
print $html->box('contribute',$title,$content);
?>
