<?
$title = $l['str_currently_hosted'];
$content = $l['str_primary_count'] ." : " . countPrimary() . "<br />
" . $l['str_secondary_count'] . " : " . countSecondary();
print $html->box('currentzones',$title,$content);
?>
