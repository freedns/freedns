<?
$title = $l['str_currently_hosted'];
$content = $l['str_primary_count'] ." : " . countPrimary() . "<br>
" . $l['str_secondary_count'] . " : " . countSecondary();
global $user;
# TODO: fix and use ->isadmin
if ($user->login=="Beeth") {
$cP=countPrimary(); $cP0=countPrimary(0);
$cS=countSecondary(); $cS0=countSecondary(0);
$cU=countUsers(); $cU0=countUsers(0);
$cR=countProdUsers(); $cR0=countProdUsers(0);
$content  = sprintf("%s: %s %d%% %s<br>\n", $l['str_primary_count'], $cP, 100*$cP/($cP+$cP0), $cP0);
$content .= sprintf("%s: %s %d%% %s<br>\n", $l['str_secondary_count'], $cS, 100*$cS/($cS+$cS0), $cS0);
$content .= sprintf("%s: %s %d%% %s<br>\n", $l['str_user_count'], $cU,100*$cU/($cU+$cU0),  $cU0);
$content .= sprintf("%s: %s %d%% %s<br>\n", $l['str_user_count_prod'], $cR,100*$cR/($cR+$cR0),  $cR0);
}
print $html->box('currentzones',$title,$content);
?>
