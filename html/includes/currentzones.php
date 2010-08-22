<?
$title = $l['str_currently_hosted'];
$content = $l['str_primary_count'] ." : " . countPrimary() . "<br>
" . $l['str_secondary_count'] . " : " . countSecondary();
global $user;
# TODO: fix and use ->isadmin
if ($user->login=="Beeth") {
$cP=countPrimary();
$cS=countSecondary();
$cU=countUsers();
$cR=countProdUsers();
$content  = sprintf("%s: %s<br>\n", $l['str_primary_count'], $cP);
$content .= sprintf("%s: %s<br>\n", $l['str_secondary_count'], $cS);
$content .= sprintf("%s: %s<br>\n", $l['str_user_count'], $cU);
$content .= sprintf("%s: %s<br>\n", $l['str_user_count_prod'], $cR);
}
print $html->box('currentzones',$title,$content);
?>
