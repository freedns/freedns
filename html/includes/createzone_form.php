<?
$content .='
	<form action="' .  $_SERVER["PHP_SELF"] . '" method="post"> ' . $hiddenfields . '
	<table id="createzonetable">
	<tr>
	<td class="left">' . $l['str_zone'] . ': </td>
	<td><input type="text" name="zonenamenew" value="'.$zonenamenew.'"></td>
	</tr>
	<tr>
	<td class="left">' . $l['str_zonetype'] . ':</td>
	<td nowrap><label><input type=radio name="zonetypenew" value="P"
';
if($zonetypenew=='P'){
        $content .=' checked';
}
$content .='>' . $l['str_primary'] . '</label>
	<label><input type=radio name="zonetypenew" value="S"
';
if($zonetypenew=='S'){
	$content .= ' checked';
}
$content .= '>' . $l['str_secondary'] . '</label></td></tr>
';

if($config->usergroups){
	$allzones = $group->listallzones();
	$user->error=$group->error;			
}else{
	$allzones = $user->listallzones();
}

if(!notnull($user->error) && count($allzones)>0){
	$content .= '
		<tr>
		<td class="left">' . $l['str_using_following_zone_as_template'] . '</td>
		<td><select name="template">
		<option value="">' . $l['str_none'] . '</option>
	';
	while($otherzone= array_pop($allzones)){
		$newzone = new Zone($otherzone[0],$otherzone[1],$otherzone[2]);
		$content .= '<option value="'.$newzone->zonename.'('.$newzone->zonetype.')">'.
			$newzone->zonename.' (' .
			$newzone->zonetype.')</option>';
	}
        $content .='
		</select>
		</td></tr>
	';
}

$content .= '
	<tr>
	<td class="left">' . sprintf($l['str_use_server_for_import_x'], $config->webserverip) . '</td>
	<td valign="center"><input type="text" name="serverimport"></td></tr>
	<tr><td colspan="2" align="center">
	<input type="submit" class="submit" value="' . $l['str_create'] . '"></td>
	</tr>
	</table>
	</form>
';
?>
