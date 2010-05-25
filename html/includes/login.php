<?
$title = $l['str_loginbox'];
if($user->authenticated == 0){
	// login box
	$content ="";
	if($user->error){
		$content = sprintf($html->string_error,$user->error) . 
					"<br>\n";
	}	

	if($config->public){
		$content .= sprintf($l['str_login_or_x_create_new_user_x'],
			'<a href="createuser.php' . $link . '" class="linkcolor">',
			'</a>') . 
			'<br>';
	}
   $content .= '<form method="post" action="' .
       ereg_replace("^http:", "https:", $config->mainurl) . 'index.php">' .
	$l['str_login'] . ': <br><div align="center"><input
	type="text" name="login" ></div><br>
	' . $l['str_password'] . ': <br><div align="center"><input type="password" name="password"
	></div><br>
	<input type="hidden" name="language" value="' . $lang . '" >
	<div align="center"><input type="submit" class="submit" value="' . $l['str_log_me_in'] . '" ><p>
	<a href="password.php' . $link . '" class="linkcolor">' . $l['str_forgot_password'] . '</a>
	</p>
	</div>
	</form>
	';
	print $html->box('login',$title,$content);
	
}else{ // if authenticated, 
		// print pref box
	
	$content = '<div><p>' . sprintf($l['str_you_are_logged_in_as_x'], $user->login) . '</p>
	<p><a href="'.       ereg_replace("^http:", "https:", $config->mainurl) . 'user.php' . $link . '" class="linkcolor">' . 
		$l['str_change_your_preferences'] . '</a></p>
	';
	if($config->usergroups){
    $content .= '<p>';
		$usergrouprights = $group->getGroupRights($user->userid);
		if(!notnull($user->error)){
			switch ($usergrouprights){
				case '0':
					$content .= $user->error;
					break;
				case 'A':
					$content .= 	'
					<a href="group.php' . $link . '" class="linkcolor">' . 
					 $l['str_administrate_your_group'] . '</a>';
					if($config->userlogs){
						$content .= '<br><a href="userlogs.php' . $link . '"
						class="linkcolor">' . $l['str_view_group_logs'] . '</a>';
					}
					$content .= '<br>
					';
					break;
				case 'R':
					$content .= $l['str_you_have_read_only_access'] . '<br>';
					break;
				case 'W':
					$content .= $l['str_you_have_read_write_access'] . '<br>';
					break;
				default:
					$content .= sprintf($html->string_error,
							$l['str_wrong_group_rights']
							) . '<br>';
			}
		}else{
			$content .=  sprintf($html->string_error,
						$user->error) . "<br>";
		}
    $content .= '</p>';
	}
	$content .= '
	<p><a href="deleteuser.php' . $link . '" class="linkcolor">' . 
			$l['str_delete_your_account']  . '</a></p>
	<p><a href="index.php' . $link . '&amp;logout=1">' . $l['str_logout'] . '</a></p>
	</div>
	';
	
	print $html->box('login',$title,$content);
	
	
	$title = $l['str_log_legend'] ;
	$content = '<div align="center"><table id="legendtable">
	<tr><td class="loghighlightINFORMATION" align="center">' . 
			$l['str_log_information'] . '</td></tr>
	<tr><td class="loghighlightWARNING" align="center">'
		 . $l['str_log_warning'] . '</td></tr>
	<tr><td class="loghighlightERROR" align="center">' . 
			$l['str_log_error'] . '</td></tr>
	<tr><td class="loghighlightUNKNOWN" align="center">' . 
			$l['str_log_unknown'] . '</td></tr>		
	</table></div>';
	print $html->box('legend',$title,$content);		

	// list all other zones for current user
	if($config->usergroups){
		$allzones = $group->listallzones();
		$user->error=$group->error;
	}else{
		$allzones = $user->listallzones();
	}
	if(!notnull($user->error)){
		$content ='<table id="zonelisttable">';
		while($otherzone= array_pop($allzones)){
			// TODO : NEW ZONE
			$newzone = new Zone($otherzone[0],$otherzone[1],$otherzone[2]);
			$status = $newzone->zonestatus();
			switch($status) {
				case 'I':
					$class='INFORMATION';
					break;
				case 'W':
					$class='WARNING';
					break;
				case 'E':
					$class='ERROR';
					break;
				default:
					$class='UNKNOWN';
			}
			$content .= '<tr>';
         $content .= '<td
			 class="loghighlight' . $class . '" align="center"><a href="logwindow.php'
			 .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
			$newzone->zonetype . '" class="linkcolor"
			onclick="window.open(\'logwindow.php'
			 .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
			$newzone->zonetype .
		
'\',\'M\',\'toolbar=no,location=no,directories=no,status=yes,alwaysraised=yes,dependant=yes,resizable=yes,scrollbars=yes,menubar=no,width=640,height=480\');
return false">'.
			 $status . '</a></td>';
         $content .= '<td><a href="modify.php'
			.$link.'&amp;zonename=' . $newzone->zonename . '&amp;zonetype=' .
			$newzone->zonetype . '" class="linkcolor">' .
			 $newzone->zonename . '</a> (' . $newzone->zonetype . ')</td>';
          $content .= '</tr>';
		}
		$content .= '</table>';
	}else{
		$content = $user->error;
    if ($user->authenticated >= 2) $content = "";
	}
  $title = $l['str_all_your_zones'];
	print $html->box('yourzones',$title,$content);
}

?>
