<?
/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// delete zone
// parameters 
// - void
// - zonename,zonetype

$page_title = "str_delete_zone_title";
// headers 
include 'includes/header.php';


if(isset($_REQUEST) && isset($_REQUEST['zonename'])){
	$zonename=$_REQUEST['zonename'];
}
if(isset($zonename)){
	$zonename = addslashes($zonename);
}

if(isset($_REQUEST) && isset($_REQUEST['zonetype'])){
	$zonetype=$_REQUEST['zonetype'];
}
if(isset($zonetype)){
	$zonetype=addslashes($zonetype);
}

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

// main content

$title=$l['str_delete_zone_title'];

if($user->authenticated == 0){
	$content = $l['str_must_log_before_deleting_zone'];
}else{
	if($config->usergroups && ($usergrouprights == 'R')){ 
	// if usergroups, zone is owned by
	// group and current user has no creation rights
		$content = sprintf($html->string_error, 
			$l['str_not_allowed_by_group_admin_to_create_write_zones']);
	}else{
	
		if(!isset($zonename)){
	
			if($config->usergroups){
				$allzones = $group->listallzones();
				$user->error=$group->error;			
			}else{
				$allzones = $user->listallzones();
			}

		
			if(!notnull($user->error)){
				$content =  '<div class="boxheader">' .
							$l['str_choose_a_zone_to_delete'] . '</div>';
				while($otherzone= array_pop($allzones)){
					$newzone = new Zone($otherzone[0],$otherzone[1],$otherzone[2]);
					$content .= '<a href="' .  $_SERVER["PHP_SELF"] 
					.$link.'&amp;zonename=' . $newzone->zonename . '&amp;zonetype=' .
					$newzone->zonetype . '" class="linkcolor">' .
					 $newzone->zonename . '</a> (' . $newzone->zonetype . ')<br />';
				}
			}else{
				$content = $user->error;
			}

		}else{ // zonename is set ==> confirm & delete
			$zone = new Zone($zonename,$zonetype);

			if($zone->error){
				$content = sprintf($html->string_error,$zone->error); 
			}else{
				if((!$config->usergroups &&
					$zone->RetrieveUser() != $user->userid) ||
					($config->usergroups && 
					$zone->RetrieveUser() != $group->groupid)){
					$content = sprintf($html->string_error, 
							sprintf($l['str_you_can_not_manage_delete_zone_x_x'],
								$zone->zonename,$zone->zonetype)
							);
				}else{

					if((isset($_REQUEST) && !isset($_REQUEST['confirm'])) ||
						(!isset($_REQUEST) && !isset($confirm))){
					// ==> print confirm screen
						$content = '
						<div class="boxheader">' . $l['str_confirmation'] . '</div>';
						if($zone->zonetype == 'P'){
							$tempzonetype = $l['str_primary'];
						}else{
							$tempzonetype = $l['str_secondary'];
						}
						$content .=
							sprintf($l['str_do_you_confirm_zone_deletion_x_x_from_x'],
							$zone->zonename,$tempzonetype,$config->sitename) . '
					 	<div align="center">
						<form action="' .  $_SERVER["PHP_SELF"] . '" method="POST">
						' . $hiddenfields . '
						<input type="hidden" name="zonename" value="' .
						$zone->zonename . 
						'">
						<input type="hidden" name="zonetype" value="' . $zone->zonetype . 
						'">
						<input type="hidden" name="confirm" value="1">
						<input type="submit" value="';
						if($zone->zonetype == 'P'){
							$tempzonetype = $l['str_primary'];
						}else{
							$tempzonetype = $l['str_secondary'];
						}
						$content .= sprintf($l['str_yes_please_delete_x_x_from_x'],
						$zone->zonename,$tempzonetype,$config->sitename) . '">
						</form>
						<form action="index.php">
						' . $hiddenfields . '
						<input type="submit" value="' . $l['str_no_dont_delete']. 
						'"></form>
						</div>
						';
					}else{ // not confirmed
						// delete
						// delete from dns_conf$zonetype, dns_log,
						// dns_record
						$localerror = 0;
						if($zone->zonetype == 'P'){
							$tempzonetype = $l['str_primary'];
						}else{
							$tempzonetype = $l['str_secondary'];
						}
						$content = sprintf($l['str_deleting_x_x_x'],$zone->zonename,
							$tempzonetype,$config->sitename) . '...<br />';
						$query = "DELETE FROM dns_conf";
						if($zone->zonetype == 'P'){
							$query .= 'primary';
						}else{
							$query .= 'secondary';
						}
						 $query .= " WHERE zoneid='" . $zone->zoneid . "'";
						$res = $db->query($query);
						if($db->error()){
							$localerror = 1;
							$content .= sprintf($html->string_error,
										$l['str_trouble_with_db']);
						}
						$query = "DELETE FROM dns_log WHERE zoneid='" . $zone->zoneid . "'";
						$res = $db->query($query);
						if($db->error()){
							$localerror = 1;
							$content .= sprintf($html->string_error, 
										$l['str_trouble_with_db']);
						}
						if($zone->zonetype=='P'){
							$query = "DELETE FROM dns_record WHERE zoneid='" . $zone->zoneid . "'";
							$res = $db->query($query);
							if($db->error()){
								$localerror = 1;
								$content .= sprintf($html->string_error,
											$l['str_trouble_with_db']); 
							}
						}		
						// log user action
						if($config->usergroups){ 
							if($config->userlogs){
								if(!$localerror){
									$userlogs->addLogs($zone->zoneid,
									sprintf($l['str_log_deletion_of_x_x'],
									$zone->zonename,
									$zone->zonetype));
								}else{
									$userlogs->addLogs($currentzone->zoneid,
									sprintf($l['str_trouble_during_deletion_of_x_x'],
									$zone->zonename,$zone->zonetype) . " " . 
									$l['str_trouble_with_db']);
								}							
								if($userlogs->error){
									$content .= sprintf($html->string_error, 
										sprintf($l['str_logging_action_x'],$userlogs->error)
										); 
								}
							}
						}

						if(!$localerror){
						// flag as deleted in dns_zone 
							$query = "UPDATE dns_zone SET status='D' WHERE 
										id='" . $zone->zoneid . "'";
							$res = $db->query($query);
							if($db->error()){
								$localerror = 1;
								$content .= sprintf($html->string_error,
											$l['str_trouble_with_db']); 
							}
						}
	
						if($localerror){

							$content .= '<p>' . 
								$l['str_errors_occured_during_deletion_plz_try_again']
								. "<br />" . 
								sprintf($l['str_if_problem_persists_x_contact_us_x'],
									'<a href="mailto:' . 
										$config->contactemail . '">','</a>');
						}else{
							$content .= $l['str_zone_successfully_deleted'];
						} 
					} // end deletion confirmed
				} // end retrieve user != userid (or groupid)
			} // end else no zone->error
		} // end else zonename is set ==> confirm & delete
	} // end usergroupright == R
	
	
}

print $html->box('mainbox',$title,$content);


if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();	
?>
