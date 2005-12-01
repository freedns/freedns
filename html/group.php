<?
/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// create a new user - called by a group admin
// parameters :
// -void / $idsession
// - $loginnew,$passwordnew,$confirmpasswordnew,  $idsession

$page_title="str_manage_your_users";
// headers 
include 'includes/header.php';

if(isset($_REQUEST) && isset($_REQUEST['loginnew'])){
	$loginnew=$_REQUEST['loginnew'];
}
if(isset($loginnew)){
	$loginnew = addslashes($loginnew);
}

if(isset($_REQUEST) && isset($_REQUEST['passwordnew'])){
	$passwordnew=$_REQUEST['passwordnew'];
}
if(isset($passwordnew)){
	$passwordnew = addslashes($passwordnew);
}

if(isset($_REQUEST) && isset($_REQUEST['confirmpasswordnew'])){
	$confirmpasswordnew=$_REQUEST['confirmpasswordnew'];
}
if(isset($confirmpasswordnew)){
	$confirmpasswordnew = addslashes($confirmpasswordnew);
}

if(isset($_REQUEST) && isset($_REQUEST['grouprightsnew'])){
	$grouprightsnew=$_REQUEST['grouprightsnew'];
}
if(isset($grouprightsnew)){
	$grouprightsnew = addslashes($grouprightsnew);
}

if(isset($_REQUEST) && isset($_REQUEST['action'])){
	$action = $_REQUEST['action'];
}


if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

if($user->authenticated){
if($config->usergroups){
	// $group is already set in include/login.php
	if($usergrouprights == 'A'){

	// main content
		$title=$l['str_manage_your_users'];
		if(!isset($action)){
			$content = '<div class="boxheader">' . 
						$l['str_users_in_your_group'] . '</div>';
			// print list of current user, with rights & delete box
			$listofgroupusers = $group->RetrieveGroupUsers();
			if($group->error){
				$content = sprintf($html->string_error,$group->error) . "<p />"; 
			}else{
				switch(count($listofgroupusers)){
					case 0:
						$content .= $l['str_wrong_access'];
						break;
					case 1:
						$content .= $l['str_no_users_in_your_group'];
						break;
					default:
						$content .='
						<form action="' .  $_SERVER["PHP_SELF"] . '" method="post">
						' . $hiddenfields . '
						<input type="hidden" name="action" value="usermodification">
						<table border="0" width="100%">
						<tr><td align="right"><b>' . $l['str_login'] . '</b></td>
						<td width="20">&nbsp;</td><td width="30%"><b>' .
						$l['str_rights'] . '</b></td>
						<td width="30%"><b>' . $l['str_delete'] . ' ?</b></td></tr>
						';
						$counter = 0;
						while($line=array_pop($listofgroupusers)){
						// userid / login / options
							if($line[0] == $user->userid){
							// do not print yourself
							}else{
								$counter++;
								$content .= '<tr><td align="right">' . $line[1] . '
								<input type="hidden" name="userid' . $counter . '"
								value="' . $line[0] . '"></td>
								<td width="20">&nbsp;</td>
								<td><select name="groupright' . $counter . '">';
							// print dropdown listbox to change grouprights
							// if not admin
								$usergrouprights = $group->getGroupRights($line[0]);
								switch($usergrouprights){
									case 'R':
										$content .= '<option value="R" selected>' . 
													$l['str_read_access'] . '</option>';
										$content .= '<option value="W">' . 
													$l['str_write_access'] . '</option>';
										break;
									case 'W':
										$content .= '<option value="R">' . 
													$l['str_read_access'] . '</option>';
										$content .= '<option value="W" selected>' . 
													$l['str_write_access'] . '</option>';
										break;
									case 'A':
										break;
									default:
										$content .= '';
								}
								$content .= '</select></td><td><input type="checkbox"
								name="delete' . $counter . '" value="' .
								$line[0] . '">' . $l['str_delete'] . '</td></tr>';
							}
						}
						$content .= '</table>
						<p align="center">
						<input type="submit" value="' . $l['str_modify_user_list']  .
						'">
						</p></form>';

				} // end switch count($listofgroupusers)
				$content .='
				<p /><div class="boxheader">' . $l['str_add_user'] .'</div><p />
				' . $l['str_warning_user_will_manage_only_group_zones'] . '
				<br />
				' . $l['str_read_explanation']  .'<br />
				' . $l['str_write_explanation'] . '<p />
				<form action="' .  $_SERVER["PHP_SELF"] . '" method="post">
				' . $hiddenfields . '
				<input type="hidden" name="action" value="usercreation">
				<table border="0" width="100%">
				<tr><td align="right">
				' . $l['str_login'] . ': </td><td><input type="text" name="loginnew">
				</td></tr>
				<tr><td align="right">
				' . $l['str_new_password'] . ': </td>
				<td><input type="password" name="passwordnew">
				</td></tr>
				<tr><td align="right">
				' . $l['str_confirm_password'] . ': </td>
				<td><input type="password" name="confirmpasswordnew">
				</td></tr>
				<td align="right">' . $l['str_rights'] . ': </td><td><select name="grouprightsnew">
				<option value="R">' . $l['str_read_access'] . '</option>
				<option value="W">' . $l['str_write_access'] . '</option>
				</select></td></tr>
				<tr><td colspan="2" align="center"><input type="submit"
				value="' . $l['str_create_new_user_button'] . '"></td></tr>
				</table>
				</form>
				';
		
			} // end no $group->error
		}else{ // !isset($action)
			// $action is set.
			// if usermodification, get grouprights & delete & act
			// if usercreation, create new user
			$content = "";
			$localerror = 0;
			$missing = "";
			switch($action){
				case "usermodification":
					
					// retrieve array of "userid"
					// retrieve array of "groupright"
					// retrieve array of "delete"
					$listofuserids = retrieveArgs("userid", $HTTP_POST_VARS);
					$listofgrouprights = retrieveArgs("groupright", $HTTP_POST_VARS);
					$listofdelete = retrieveArgs("delete", $HTTP_POST_VARS);
				
					while($groupright = array_pop($listofgrouprights)){
						$usertochange = array_pop($listofuserids);
						
						// update groupright for user $usertochange
						if(($groupright != 'R') && ($groupright != 'W')){
							$content .= sprintf($html->string_error,
										$l['str_wrong_group_rights']) . "<br />"; 
						}else{
							// check if user in group
							if($group->isMember($usertochange)){
								// set group right
								if(!$group->setGroupRights($usertochange,$groupright)){
									$content .= sprintf($html->string_error, 
									sprintf($l['str_while_changing_group_rights_for_x'],
									$user->RetrieveLogin($usertochange)) .
									': ' . $group->error) . 
									'<br />';
								}else{
									$content .= sprintf($l['str_rights_for_x_successfully_set_to_x'], 
									$user->RetrieveLogin($usertochange),$groupright) .
									'<br />';
								}							
							}else{
								$content .= sprintf($html->string_error, 
									sprintf($l['str_user_x_is_not_member_of_this_group'],
										$user->RetrieveLogin($usertochange))
									) . '<br />';
							}
						}
					}
				
					// Delete
					while($todelete = array_pop($listofdelete)){
						// delete user
	
						// check if user in group
						if($group->isMember($user->userid)){
							// delete user
							$logintodelete=$user->RetrieveLogin($todelete);
							if(!$group->deleteUser($todelete)){
								$content .= sprintf($html->string_error, 
									sprintf($l['str_errors_occured_during_user_deletion_x'],
										$logintodelete) . ': '
									. $group->error) . '<br />';
								
							}else{
								$content .= sprintf($l['str_user_x_successfully_deleted'],
									$logintodelete) . '<br />';
							}
						}else{
							$content .= sprintf($html->string_error, 
								sprintf($l['str_user_x_is_not_member_of_this_group'],
									$user->RetrieveLogin($usertochange))
								) . '<br />';
						}
					}
				
					break;	// end case $action = usermodification	

				case "usercreation":

					if(!notnull($loginnew)){
						$missing .= ' ' . $l['str_login'] . ',';
					}
					if(!notnull($passwordnew)){
						$missing .= ' ' . $l['str_password'] . ',';
					}
					if(!notnull($confirmpasswordnew)){
						$missing .= ' ' . $l['str_confirm_password'] . ',';
					}
	
					if(notnull($missing)){
						$localerror = 1;
						$missing = substr($missing,0, -1);
						$content .= sprintf($html->fontred, 
									sprintf($l['str_error_missing_fields'],
									$missing)
								) . '<br />';
					}
		
	
					if(!$localerror){
						if(!checkName($loginnew)){
							$localerror = 1;
							$content .= sprintf($html->string_error, 
										$l['str_bad_login_name']) . '<br />';
						}
		
						if($passwordnew != $confirmpasswordnew){
							$localerror = 1;
							$content .= sprintf($html->string_error, 
										$l['str_passwords_dont_match']) . '<br />';
						}
					} // end no error after empty checks
	


					if(!$localerror){
					// ****************************************
					// *            Create new user           *
					// ****************************************
						$newuser=new User('','','');
					
						$group->GroupUserCreate($newuser,$loginnew,
						$passwordnew,'',$user->userid, $grouprightsnew);
						if($group->error){
							$content .= sprintf($html->string_error,
										$group->error); 
						}else{
							// user successfully created
							$content .= $l['str_new_user_has_been_successfully_created'];
						} // zone created successfully
	
					}else{ // error, print form again
						$content .='
						<form action="' .  $_SERVER["PHP_SELF"] . '" method="post">
						' . $hiddenfields . '
						<input type="hidden" name="action" value="usercreation">				
						<table border="0" width="100%">
						<tr><td align="right">
						' . $l['str_login'] . ': </td><td><input type="text" name="loginnew"
						value="'.$loginnew.'">
						</td></tr>
						<tr><td align="right">
						' . $l['str_new_password'] . ': </td>
						<td><input type="password" name="passwordnew">
						</td></tr>
						<tr><td align="right">
						' . $l['str_confirm_password'] . ': </td>
						<td><input type="password" name="confirmpasswordnew">
						</td></tr>
						<td align="right">' . $l['str_rights'] . ': </td><td><select name="grouprightsnew">
						<option value="R">' . $l['str_read_access'] . '</option>
						<option value="W">' . $l['str_write_access'] . '</option>
						</select></td></tr>			
						<tr><td colspan="2" align="center"><input type="submit"
						value="' . $l['str_create_new_user_button'] . '"></td></tr>
						</table>
						</form>
						';
					} // end else error (print form again)

					break; // end case $action = usercreation
			} // end swith($action)
	
		} // end else $action not null

	}else{ // end 	if($group->getGroupRights($user->userid) == 'A')
		$title=$l['str_uppercase_error'];
		$content= $l['str_you_are_not_admin_of_your_group'];	
	}

}else{ // end $user->RetrieveGroupRights() == 'A'
	$title=$l['str_uppercase_error'];
	$title=$l['str_groups_disabled_in_server_conf'];
}
}else{ // end $user->authenticated
	$title=$l['str_uppercase_error'];
	$content=$l['str_must_log_first'];
}

print $html->box('mainbox',$title,$content);


if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}


print $html->footer();

?>
