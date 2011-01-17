<?
$title = $l['str_currently_hosted'];
global $user;
$cP=countPrimary();
$cS=countSecondary();
$content  = sprintf("%s: %s<br>\n", $l['str_primary_count'], $cP);
$content .= sprintf("%s: %s<br>\n", $l['str_secondary_count'], $cS);
# TODO: fix and use ->isadmin
if ($user->login=="Beeth") {
  $cU=countUsers();
  $cR=countProdUsers();
  $content .= sprintf("%s: %s<br>\n", $l['str_user_count'], $cU);
  $content .= sprintf("%s: %s<br>\n", $l['str_user_count_prod'], $cR);
}
if ($user->login!="") {
  $cT=countRecords();
  $content .= sprintf("%s: %s<br>\n", $l['str_records_count'], $cT);
}
print $html->box('currentzones',$title,$content);
?>
