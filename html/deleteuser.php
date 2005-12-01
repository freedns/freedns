<?
/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// delete user
// parameters 
// - void
// - confirm

$page_title = "str_delete_user_title";
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

// main content

$title=$l['str_delete_user_title'];
$content ="";
if($user->authenticated == 0){
	$content = $l['str_must_log_before_deleting_yourself'];
}else{
	if(isset($_REQUEST) && isset($_REQUEST['confirm'])){
		$confirm=$_REQUEST['confirm'];
	}

	if(!isset($confirm) || !$confirm){

		if($config->usergroups){
			if( $group->getGroupRights($user->userid) == 'A'){
				$content .= '<p />' . $l['str_administrator_delete_content'] . '<p />
				
				<div class="boxheader">' . $l['str_zones_to_be_deleted']  . '</div>
				<p />' . $l['str_following_zones_will_be_deleted'] . '<br />
				<font color="red">';
		 		$zonelist = $group->listallzones();
				while($otherzones= array_pop($zonelist)){
					$content .= '&nbsp;' . $otherzones[0] . ' (' . 
						$otherzones[1] . ")<br />\n";
				}
				
				$content .= '</font>
				<p /><div class="boxheader">' . $l['str_users_to_be_deleted'] . 
					'</div>
					' . sprintf($l['str_following_users_will_be_deleted_from_x'],
					$config->sitename) . ': <p /><font
				color="red">';
				$userlist = $group->RetrieveGroupUsers();
				while($otheruser= array_pop($userlist)){
					$content .= '&nbsp;' . $otheruser[1] . "<br />\n";
				}
				$content .= "</font>";
			}else{ // end user admin
				$content .= '<p />' . $l['str_not_administrator_deletion'];
			} // end user not admin
		}else{ // end if usergroups
			// if no usergroup, list zones to be deleted
			$content .= '
			<div class="boxheader">' . $l['str_zones_to_be_deleted'] . '</div>
			' . $l['str_following_zones_will_be_deleted'] . '<p /><font color="red">';
		 	$zonelist = $user->listallzones();
			while($otherzones= array_pop($zonelist)){
				$content .= '&nbsp;' . $otherzones[0] . ' (' . 
					$otherzones[1] . ")<br />\n";
			}
			$content .= "</font>";
			
		} // end no user group
		$content .= '
			<p /><div class="boxheader">' . $l['str_confirmation'] . '</div>
			' . sprintf($l['str_do_you_confirm_your_deletion_from_x'],
					$config->sitename) . ' 
			<p />

		 	<div align="center">
			<form action="' .  $_SERVER["PHP_SELF"] . '" method="POST">
			' . $hiddenfields . '
			<input type="hidden" name="confirm" value="1">
			<input type="submit" value="' . 
				sprintf($l['str_yes_please_delete_myself_from_x'],
						$config->sitename) . '">
						</form>
			<form action="index.php">
				' . $hiddenfields . '
				<input type="submit" value="' . $l['str_no_dont_delete'] .'"></form>
			</div>
			';
		


		
	}else{ // $confirm = 1
		$content = "";
		$localerror = 0;
		// delete each zone one by one if admin or no usergroup
		if(($config->usergroups && ($group->getGroupRights($user->userid) == 'A'))
			|| !$config->usergroups){
			if($config->usergroups){
				$zonelist = $group->listallzones();
			}else{
				$zonelist = $user->listallzones();
			}
				
			while($otherzones= array_pop($zonelist)){
				$zone = new Zone($otherzones[0],$otherzones[1],$otherzones[2]);
				if($zone->zonetype == 'P'){
					$currenttype = $l['str_primary'];
				}else{
					$currenttype = $l['str_secondary'];
				}
				$content .= sprintf($l['str_deleting_x_x_from_x'], 
								$zone->zonename, $currenttype,
								$config->sitename) . '...<br />';
				
				if(!$zone->zoneDelete()){
					$content .= $zone->error . ' ' . 
								$l['str_errors_occured_during_deletion_plz_try_again']
								. '<br /> ' .
				 				sprintf($l['str_if_problem_persists_x_contact_us_x'],
								'<a href="mailto:' . 
								$config->contactemail . '">','</a>') .
								'<p />';
					$localerror = 1;
				}else{
					$content .= $l['str_zone_successfully_deleted'] . '<p />';
				} 
			} // end while zone
		} // end zones has to be deleted

		
		// delete user
		// if group admin, delete grouplogs
		if(!$localerror){
			if($config->usergroups){
				if($config->userlogs){
					if($group->getGroupRights($user->userid) == 'A'){
						// delete group logs
						$userlogs->deleteLogsBefore(date("YmdHis"));
					}else{ // not admin
						// delete user logs
						$listuserlogs = $userlogs->showUserLogs($user->userid,'A');
						while($logid = array_pop($listuserlogs)){
							$userlogs->deleteLog($logid[0]);			
						}
					}// end not admin
				} // end if $config->userlogs
	
				// delete group
				if($group->getGroupRights($user->userid) == 'A'){
					$userlist = $group->RetrieveGroupUsers();
					while($otheruser= array_pop($userlist)){
						$group->deleteUser($otheruser[0]);
						if(!$group->error){
							$content .= sprintf($l['str_user_x_successfully_deleted'],
							$otheruser[1]) . "<br />";
						}else{
							$localerror = 1;
							$content .= sprintf($html->string_error, 
										sprintf($l['str_while_deleting_user_x'],
										$otheruser[1]) . ": " .
										$group->error) . "<br />";
						}
					}
				}

				
			}else{ // end user groups
                                // delete user logs
                                if($config->userlogs){
                                        $userlogs->deleteLogsBefore(date("YmdHis"));
                                }
                        }
		
			// delete current user
			if($user->deleteuser()){
				$content .= $l['str_user_successfully_deleted'] . "<br />";
			}else{
				$localerror = 1;
				$content .= sprintf($html->string_error,
							$l['str_while_deleting_your_user'])
							. "<br />";
			}
			
			// current user has been deleted => logout
			$user->logout($user->idsession);
		} // end no error

		if($localerror){
			$content .= "<p />" . 
						$l['str_errors_occured_during_deletion_plz_refer_to_upper_msg'];
		}else{
			$content .= '<p />' . 
				sprintf($l['str_you_have_been_successfully_deleted_x_go_back_x'],
				'<a href="' . $config->mainurl . '">','</a>');
		}
	} // end else $confirm = 1
} // end user authenticated

print $html->box('mainbox',$title,$content);

if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();	
?>
