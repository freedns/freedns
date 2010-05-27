<?
if($config->usergroups){
  $allzones = $group->listallzones();
  $allzones2 = $allzones;
  $user->error = $group->error;			
}else{
  $allzones = $user->listallzones();
  $allzones2 = $allzones;
}
if ($zonetypenew=='P') {
  $zonenamenewp = $zonenamenew;
} else {
  $zonenamenews = $zonenamenew;
}

$form ='
  <form action="' .  $_SERVER["PHP_SELF"] . '" method="post">' . $hiddenfields . '
  <table border="0" id="createzonetable">
  <tr><td style="width:40%;" class="left">' . $l['str_zone'] . ': </td>
  <td colspan="2"><input style="width:100%" type="text" name="zonenamenew" value="'.$zonenamenew.'">
  </td></tr>
  <tr>
  <td colspan="2"></td>
  <td class="right">' . $l['str_using_following_zone_as_template'] . '</td>
  </tr>
  <tr><td rowspan="2" class="left">' . $l['str_zonetype'] . ':</td>
  <td nowrap><label><input type=radio name="zonetypenew" value="P"
';

if($zonetypenew=='P'){
  $form .=' checked';
}
$form .= '>' . $l['str_primary'] . '</label></td>';
if(!notnull($user->error) && count($allzones)>0){
  $form .= '
  <td><select name="templatep">
    <option value="">' . $l['str_none'] . '</option>
  ';
  while($otherzone = array_pop($allzones)){
    $newzone = new Zone($otherzone[0], $otherzone[1], $otherzone[2]);
    if ($newzone->zonetype == 'P') {
      $form .= '<option value="'.$newzone->zonename.'">'.$newzone->zonename.'</option>';
      #$form .= '<option value="'.$newzone->zonename.'('.$newzone->zonetype.')">'.
      #            $newzone->zonename.' ('.$newzone->zonetype.')</option>';
    }
  }
}

$form .= '
  </select>
  </td>
  </tr>';

$form .= '<tr>
  <td nowrap><label><input type=radio name="zonetypenew" value="S"
';
if($zonetypenew=='S'){
  $form .= ' checked';
}
$form .= '>' . $l['str_secondary'] . '</label></td>';
if(!notnull($user->error) && count($allzones2)>0){
  $form .= '
  <td><select name="templates">
    <option value="">' . $l['str_none'] . '</option>
  ';
  while($otherzone = array_pop($allzones2)){
    $newzone = new Zone($otherzone[0], $otherzone[1], $otherzone[2]);
    if ($newzone->zonetype == 'S') {
      $form .= '<option value="'.$newzone->zonename.'">'.$newzone->zonename.'</option>';
    }
  }
  $form .= '
  </select>
  </td></tr>
  ';
}

$form .= '
  <tr>
    <td class="left">' . $l['str_authoritative_server'] . ':</td>
    <td colspan="2" class="right"><input style="width:100%;" type="text" name="serverimport" value="'.$serverimport.'"></td>
  </tr>
  <tr>
    <td colspan="3">' . sprintf($l['str_authoritative_server_x'], $config->webserverip) . '</td>
  </tr>
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
