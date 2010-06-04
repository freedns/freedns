<?
if($config->usergroups){
  $allzones = $group->listallzones();
  $user->error = $group->error;			
}else{
  $allzones = $user->listallzones();
}
$allzones2 = $allzones;

if ($zonetypenew=='S')
 $checksec=" checked";
else if ($zonetypenew=='P')
 $checkpri=" checked";

$form ='
  <table border="0" id="createzonetable">
  <form action="' .  $_SERVER["PHP_SELF"] . '" method="post">' . $hiddenfields . '
  <tr><td style="width:20%;" class="left">' . $l['str_zone'] . ':</td>
  <td colspan="2"><input style="width:100%" type="text" name="zonenamenew" value="'.$zonenamenew.'">
  </td></tr>
  <tr>
  <td class="left">' . $l['str_zonetype'] . ':</td>
  <td colspan="2"><label><h3><input type="radio" name="zonetypenew" value="P"'.$checkpri.'>' . $l['str_primary'] . '</label></h3></td></tr>
  <input type="hidden" name="zonetypenew" value="P">
';
if(!notnull($user->error) && count($allzones)>0){
  $form .= '
  <tr>
    <td></td>
  <td class="left">' . $l['str_using_following_zone_as_template'] . ':</td>
  <td><select name="templatep">
    <option value="">' . $l['str_none'] . '</option>
  ';
  while($otherzone = array_pop($allzones)){
    if ($otherzone[1] == 'P') {
      $form .= '<option value="'.$otherzone[0].'"';
      if ($otherzone[0]==$templatep) $form .= " selected";
      $form .= '>'.$otherzone[0].'</option>';
    }
  }
  $form .= '
  </select></td></tr>
  ';
}

$form .= '
  <tr>
    <td></td>
    <td class="left">' . $l['str_import_server'] . ':</td>
    <td colspan="2" class="right"><input style="width:100%;" type="text" name="serverimport" value="'.$serverimport.'"></td>
  </tr>
  <tr>
    <td></td>
    <td colspan="3">' . sprintf($l['str_import_server_x'], $config->webserverip) . '</td>
  </tr>
';

$form .= '
  <tr>
  <td class="left">' . $l['str_zonetype'] . ':</td>
  <td colspan="3"><h3><input type="radio" name="zonetypenew" value="S"'.$checksec.'>' . $l['str_secondary'] . '</h3></td></tr>
';

if(!notnull($user->error) && count($allzones2)>0){
  $form .= '
  <tr>
  <td></td>
  <td class="left">' . $l['str_using_following_zone_as_template'] . ':</td>
  <td><select name="templates">
    <option value="">' . $l['str_none'] . '</option>
  ';
  while($otherzone = array_pop($allzones2)){
    if ($otherzone[1] == 'S') {
      $form .= '<option value="'.$otherzone[0].'"';
      if ($otherzone[0]==$templates) $form .= " selected";
      $form .= '>'.$otherzone[0].'</option>';
    }
  }
  $form .= '
  </select></td></tr>
  ';
}

$form .= '
  <tr>
    <td></td>
    <td class="left">' . $l['str_authoritative_server'] . ':</td>
    <td colspan="2" class="right"><input style="width:100%;" type="text" name="authoritative" value="'.$authoritative.'"></td>
  </tr>
  <tr>
    <td></td>
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
