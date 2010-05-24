<?
$title = $l['str_currently_hosted'];
$content = $l['str_primary_count'] ." : " . countPrimary() . "<br>
" . $l['str_secondary_count'] . " : " . countSecondary();
global $user;
# TODO: fix and use ->isadmin
if ($user->login=="Beeth") {
$content  = sprintf("%s: %s / %s<br>\n", $l['str_primary_count'], countPrimary(), countPrimary(0));
$content .= sprintf("%s: %s / %s<br>\n", $l['str_secondary_count'], countSecondary(), countSecondary(0));
$content .= sprintf("%s: %s / %s<br>\n", $l['str_user_count'], countUsers(), countUsers(0));
$content .= sprintf("%s: %s / %s<br>\n", $l['str_user_count_prod'], countProdUsers(), countProdUsers(0));
}
print $html->box('currentzones',$title,$content);
?>
