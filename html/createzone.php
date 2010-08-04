<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

// create a new zone
// parameters : 
// - void
// - zonenamenew zonetypenew

$page_title = "str_create_new_zone";
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}


// main content

$title=$l['str_create_new_zone'];
if($user->authenticated == 0){
  $content = $l['str_must_log_before_creating_new_zone'];
}else if ($user->authenticated >= 2) {
  $content = migrationbox();
}else{
  if($config->usergroups && ($usergrouprights == 'R')){ 
    // if usergroups, zone is owned by
    // group and current user has no creation rights
    $content = sprintf($html->string_error,  
        $l['str_not_allowed_by_group_admin_to_create_write_zones']);
  }else{

    if(!isset($_POST) || $_POST == array()){
      $content = "";
      include("includes/createzone_form.php");
    }else{
    // $zonenamenew is set
      if(isset($_REQUEST)){
        $zonetypenew = @$_REQUEST['zonetypenew'];
        $zonenamenew = @$_REQUEST['zonenamenew'];
        if(isset($_REQUEST['template']) && $_REQUEST['template']!=''){
          if ($_REQUEST['template'] != $l['str_none']) {
            $template = substr($_REQUEST['template'], 0, -3);
            $zonetypenew = substr($_REQUEST['template'], -2, -1);
          }
        }else{
          $template = "";
        }
        if(isset($_REQUEST['serverimport'])){
          $serverimport = $_REQUEST['serverimport'];
        }else{
          $serverimport = "";
        }
        if(isset($_REQUEST['authoritative'])){
          $authoritative = $_REQUEST['authoritative'];
        }else{
          $authoritative = "";
        }
if (0):
        if(isset($_REQUEST['zonearea'])){
          $zonearea = $_REQUEST['zonearea'];
        }else{
          $zonearea = "";
        }
endif;
          $zonearea = "";
      }
      $content = "";
      $localerror = 0;
      $missing = "";
    
      if(!notnull($zonenamenew)){
        $missing .= " " . $l['str_zone'] . ",";
      }
      if(!notnull($zonetypenew)){
        $missing .= " " . $l['str_zonetype'] . ",";
      }
      if($zonetypenew=='S'){
        if(!notnull($template) || $template==$l['str_none']){
          if(!notnull($authoritative)){
            $missing .= " " . $l['str_authoritative_server'] . ",";
          }elseif (!checkIP($authoritative)){
            $missing .= " " . $l['str_secondary_your_primary_should_be_an_ip'] . ",";
          } 
        } else {
          // we take all from template
          $authoritative = "";
        }
      }
      if(notnull($missing)){
        $localerror = 1;
        $missing = substr($missing,0, -1);
        $content .= sprintf($html->fontred,
            sprintf($l['str_error_missing_fields'],
              $missing)
            ) . '<br>';
      }
  
      if ($zonetypenew == "S") {
        $server = $authoritative;
      }
      if ($zonetypenew == "P") {
        $server = $serverimport;
      }
      if (!notnull($template)) $template = $l['str_none'];
  
      if(!$localerror){
        if(!checkZone($zonenamenew)){
          $localerror = 1;
          $content .= sprintf($html->string_error,
                sprintf($l['str_bad_zone_name_x'],
                  $zonenamenew)
                ) . '<br>';
        }else{
          if(preg_match("/^(.*)\.$/",$zonenamenew,$newzonename)){
            $zonenamenew = $newzonename[1];
          }
          $newzone = new Zone('','');
          if($config->usergroups){ 
            // if usergroups, zone is owned by
            // group and not individuals
            $list = $newzone->subExists($zonenamenew,$group->groupid);
          }else{
            $list = $newzone->subExists($zonenamenew,$user->userid);
          }
          if($list == 0){
            $localerror = 1;
            $content .= sprintf($html->string_error,$newzone->error) . '<br>';
          }else{
            if(count($list) != 0){
              if(count($list) == 1){
                $toprint =  $l['str_zone_linked_exists_and_not_manageable'] . 
                '<br> ';
              }else{
                $toprint = $l['str_zones_linked_exist_and_not_manageable']  . 
                '<br> ';
              }
              if (count($list) < 10) {
              $toprint .= implode("<br>",$list) .'<br>'; 
              }
              $content .= sprintf($html->string_error,
                  $toprint) . '<br>'; 
              $localerror = 1;
            }
          }
        }
      } // end no error after empty checks
  


      if(!$localerror){
        // ****************************************
        // *            Create new zone           *
        // ****************************************
        // import zone content
        if(notnull($serverimport)){
            // check if server is IP or NS name
            if(!(checkIP($serverimport) || checkDomain($serverimport)) ){
              $content .= sprintf($html->string_warning, 
                $l['str_bad_serverimport_name']);
              $server="";
            } 
        }
if (0):
        if(notnull($zonearea)){
          if(strcmp($zonetypenew, 'P')){
            $content .= sprintf($html->string_warning, 
              $l['str_no_zonearea']); 
            $zonearea="";
          }
        }
endif;
        if($config->usergroups){
          // if usergroups, zone is owned by
          // group and not individuals
          if($usergrouprights != 'R'){
            $newzonereturn = $newzone->zoneCreate($zonenamenew,
              $zonetypenew,$template,$server,$group->groupid,$zonearea);
            // logs
            if(!$newzone->error){
              if($config->userlogs){
                $userlogs->addLogs($newzone->zoneid,
                  sprintf($l['str_creation_of_x_x'],
                    $zonenamenew,$zonetypenew));
                if($userlogs->error){
                  $content .= sprintf($html->string_error, 
                        sprintf($l['str_logging_action_x'],
                        $userlogs->error)
                      ); 
                }
              }
            }
          }else{ // user is read only
            $content .= sprintf($html->string_error, 
                  $l['str_not_allowed_by_group_admin_to_create_write_zones']);
            $localerror=1;
          }
        }else{
          $newzonereturn = $newzone->zoneCreate($zonenamenew,$zonetypenew,$template,$server,$user->userid,$zonearea);
        }
        if(($newzone->error && !$newzonereturn) || $localerror){
          if(!$localerror){
            $content .= sprintf($html->string_error,
                  $newzone->error);
          }
        }else{
          if($template && $template!=$l['str_none']){
            $content .= '<p>' . 
                sprintf($l['str_using_zone_x_as_template'], $template);
            if($newzone->error){
              $content .= "<p>" . 
                  sprintf($html->string_warning, 
                  $l['str_errors_occured_during_tmpl_usage_check_content']);
            }
          }
          // send email & print message
          $content .= '<p>' .
            sprintf($l['str_zone_x_successfully_registered_on_x_server'],
               $zonenamenew,$config->sitename) . '<p > 
          ';
          if($template && $template!=$l['str_none']){
            $content .=
              sprintf($l['str_you_can_now_use_the_x_modif_interface_x_to_configure'],
                  '<a href="modify.php' . $link . 
                '&amp;zonename=' . $zonenamenew . '&amp;zonetype=' .
                $zonetypenew .'">','</a>');
          }else{
            $content .=
              sprintf($l['str_you_can_now_use_the_x_modif_interface_x_to_verify'],
                  '<a href="modify.php' . $link . 
                '&amp;zonename=' . $zonenamenew . '&amp;zonetype=' .
                $zonetypenew .'">','</a>');
          }
        } // zone created successfully  
  
      }else{ // error, print form again
        include("includes/createzone_form.php");
      }
  

    } // end else $zonenamenew not null

    if($config->usergroups){
      if($config->userlogs){
        // $usergrouprights was set in includes/login.php
        if(($usergrouprights == 'R') || ($usergrouprights =='W')){
          $content .= '<p >' . 
            sprintf($html->string_warning, 
              $l['str_as_member_of_group_action_logged']);
        }
      }  
    }
  } // end usergroups && usergrouprights != R
}

print $html->box('mainbox',$title,$content);

if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();

?>
