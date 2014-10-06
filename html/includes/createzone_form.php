<?
if($config->usergroups){
  $allzones = $group->listallzones("", 1);
  $user->error = $group->error;      
}else{
  $allzones = $user->listallzones("", 1);
}

$checksec="";
$checkpri="";
if (empty($template) || $template==$l['str_none']) {
  if (@$zonetypenew=='S')
    $checksec=" checked";
  else if (@$zonetypenew=='P')
    $checkpri=" checked";
}

$form = '
  <table border="0" id="createzonetable">
  <form action="' .  $_SERVER["PHP_SELF"] . '" method="post">' . $hiddenfields . '
  <tr>
  <td class="left">' . $l['str_zone'] . ':</td>
  <td colspan="2"><input style="width:85%" type="text" name="zonenamenew" value="'.@$zonenamenew.'">
  </td>
  </tr>
';
if(empty($user->error) && count($allzones)>0){
  $form .= '
  <tr>
  <td class="left"><nobr>' . $l['str_using_following_zone_as_template'] . ':<nobr></td>
  <td colspan="2"><select name="template">
    <option value="">' . $l['str_none'] . '</option>
  ';
  while($otherzone = array_pop($allzones)){
    $form .= '<option value="'.$otherzone[0].'('.$otherzone[1].')"';
    if ($otherzone[0]==@$template && $otherzone[1]==@$zonetypenew) $form .= " selected";
    $form .= '>'.$otherzone[0].'('.$otherzone[1].')</option>';
  }
  $form .= '
  </select>
  </td>
  </tr>
  ';
}

$form .='
  <tr>
  <td class="left">' . $l['str_zonetype'] . ':</td>
  <td colspan="2"><label><h3><input type="radio" name="zonetypenew" value="P"'.$checkpri.'>' . $l['str_primary'] . '</label></h3></td>
  </tr>
';

$form .= '
  <tr>
  <td></td>
  <td colspan="2">' . sprintf($l['str_import_server_x'], $config->nsaddress) . '</td>
  </tr>
  <tr>
  <td></td>
  <td class="right">' . $l['str_import_server'] . ':</td>
  <td class="right"><input type="text" name="serverimport" value="'.@$serverimport.'"></td>
  </tr>
';

$form .= '
  <tr>
  <td class="left">' . $l['str_zonetype'] . ':</td>
  <td colspan="2"><h3><input type="radio" name="zonetypenew" value="S"'.$checksec.'>' . $l['str_secondary'] . '</h3></td>
  </tr>
';

$form .= '
  <tr>
    <td></td>
    <td colspan="2">' . sprintf($l['str_authoritative_server_x'], $config->nsaddress) . '</td>
  </tr>
  <tr>
    <td></td>
    <td class="right">' . $l['str_authoritative_server'] . ':</td>
    <td class="right"><input type="text" name="authoritative" value="'.@$authoritative.'"></td>
  </tr>
';

$form .= '
  <tr>
  <td colspan="3" align="center">
   <input type="submit" class="submit" value="' . $l['str_create'] . '">
  </td>
  </tr>
</table>
</form>
';

$content .= $form;

?>
