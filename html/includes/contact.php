<?
$title = $l['str_contact_title'];
$content = $l['str_contact_for_bugs_contact_us'] . '<br />
';
print $html->box('contact',$title,$content);

$title = $l['str_contribute_title'];
$content = $l['str_contribute_content'];
print $html->box('contribute',$title,$content);
?>
