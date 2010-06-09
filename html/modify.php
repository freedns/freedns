<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

  // modify.php
  // require user to be already logged-in, it means
  // parameters are $idsession or $zonename and $password
  
$page_title = "str_modify_zone_title";
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

// main content
if($user->authenticated==1){
  if(isset($_REQUEST) && isset($_REQUEST['zonename'])){
    $zonename=$_REQUEST['zonename'];
    $zonetype=$_REQUEST['zonetype'];
  }
  if(notnull($zonename)){
    $zonename = addslashes($zonename);
    $zonetype = addslashes($zonetype);
    $zone = new Zone($zonename,$zonetype);
    if($zone->error){
      $content = sprintf($html->string_error,
            $zone->error);
    }else{
      // verify that $zone is owned by user or group
      if((!$config->usergroups &&
        $zone->RetrieveUser() != $user->userid) ||
        ($config->usergroups && 
        $zone->RetrieveUser() != $group->groupid)){
        $content = sprintf($html->string_error, 
          sprintf($l['str_you_can_not_manage_delete_zone_x_x'],
             $zone->zonename,$zone->zonetype)
             );
      }else{
        $content = '<h3>' .
          $l['str_current_zone'] . ': ' . $zone->zonename . '
        </h3>
        ';


        
        if($zone->zonetype=='P'){
          $title = sprintf($l['str_title_zone_type_x_x'],
                  $zone->zonename,$l['str_primary']);
          if(isset($_REQUEST)){
            if(isset($_REQUEST['xferip'])){
              $xferip = $_REQUEST['xferip'];
            }else{
              $xferip="";
            }
            if(isset($_REQUEST['defaultttl'])){
              $defaultttl = $_REQUEST['defaultttl'];
            }else{
              $defaultttl="";
            }
            if(isset($_REQUEST['soarefresh'])){
              $soarefresh = $_REQUEST['soarefresh'];
            }else{
              $soarefresh="";
            }
            if(isset($_REQUEST['soaretry'])){
              $soaretry = $_REQUEST['soaretry'];
            }else{
              $soaretry="";
            }
            if(isset($_REQUEST['soaexpire'])){
              $soaexpire = $_REQUEST['soaexpire'];
            }else{
              $soaexpire="";
            }
            if(isset($_REQUEST['soaminimum'])){
              $soaminimum = $_REQUEST['soaminimum'];
            }else{
              $soaminimum="";
            }
            if(isset($_REQUEST['modifyptr'])){
              $modifyptr = $_REQUEST['modifyptr'];
              if(!notnull($modifyptr) ||
              !strcmp($modifyptr,'off')){
                $modifyptr = "0";
              }else{
                $modifyptr="1";
              }
            }else{
              $modifyptr = "0";
            }
            if(isset($_REQUEST['modifya'])){
              $modifya = $_REQUEST['modifya'];
              if(!notnull($modifya) ||
              !strcmp($modifya,'off')){
                $modifya = "0";
              }else{
                $modifya="1";
              }
            }else{
              $modifya = "0";
            }

            if(isset($_REQUEST['modifyptripv6'])){
              $modifyptripv6 = $_REQUEST['modifyptripv6'];
              if(!notnull($modifyptripv6) ||
              !strcmp($modifyptripv6,'off')){
                $modifyptripv6 = "0";
              }else{
                $modifyptripv6="1";
              }
            }else{
              $modifyptripv6 = "0";
            }
            
          }
          $xferip=addslashes($xferip);
          $defaultttl=addslashes($defaultttl);
          $soarefresh=addslashes($soarefresh);
          $soaretry=addslashes($soaretry);
          $soaexpire=addslashes($soaexpire);
          $soaminimum=addslashes($soaminimum);
          if(isset($_REQUEST)){
            $params=array($_REQUEST,$xferip,$defaultttl,
                $soarefresh,$soaretry,$soaexpire,$soaminimum,
                $modifyptr,$modifyptripv6,$modifya);
          }else{
            $params=array($HTTP_POST_VARS,$xferip,$defaultttl,
                $soarefresh,$soaretry,$soaexpire,$soaminimum,
                $modifyptr,$modifyptripv6,$modifya);
          }
            $currentzone = new Primary($zone->zonename,
            $zone->zonetype,$user);
        }else{
          if($zone->zonetype=='S'){
            $title = sprintf($l['str_title_zone_type_x_x'],
                  $zone->zonename,$l['str_secondary']);

            if(isset($_REQUEST)){
              if(isset($_REQUEST['primary'])){
                $primary = $_REQUEST['primary'];
              }else{
                $primary = "";
              }
              if(isset($_REQUEST['xfer'])){
                $xfer = $_REQUEST['xfer'];
              }else{
                $xfer = "";
              }
              if(isset($_REQUEST['xferip'])){
                $xferip = $_REQUEST['xferip'];
              }else{
                $xferip = "";
              }
            }
            $primary=addslashes($primary);
            $xfer=addslashes($xfer);
            $xferip=addslashes($xferip);
            $params=array($primary,$xfer,$xferip);
            $currentzone = new Secondary($zone->zonename,
              $zone->zonetype,$user);
          }
        }
        if(isset($_REQUEST)){
          if(isset($_REQUEST['modified'])){
            $modified = $_REQUEST['modified'];
          }else{
            $modified = 0;
          }
        }
        if($modified == 1){
          if($config->usergroups && ($usergrouprights == 'R')){ 
          // if usergroups, zone is owned by
          // group and current user has no creation rights
            $content .= sprintf($html->string_error,  
             $l['str_not_allowed_by_group_admin_to_create_write_zones']
                 );
          }else{
            $content .= $currentzone->printModified($params);
            // logs
            if($config->usergroups){ 
              if($config->userlogs){
                if(!$currentzone->error){
                  if($currentzone->zonetype == 'P'){
                    $userlogs->addLogs($currentzone->zoneid,
                    sprintf($l['str_log_modification_of_x_x_new_serial_x'],
                    $currentzone->zonename,
                    $currentzone->zonetype,
                    $currentzone->serial));
                  }else{
                    $userlogs->addLogs($currentzone->zoneid,
                    sprintf($l['str_log_modification_of_x_x'],
                    $currentzone->zonename,
                    $currentzone->zonetype));
                  }
                  
                }else{
                  $userlogs->addLogs($currentzone->zoneid,
                  sprintf($l['str_errors_occured_during_modification_of_x_x'],
                  $currentzone->zonename,
                  $currentzone->zonetype,
                  addslashes($currentzone->error)));
                }              
                if($userlogs->error){
                  $content .= sprintf($html->string_error,
                   sprintf($l['str_logging_action_x'],$userlogs->error)
                   );
                }
              }
            }
          } // end usergrouprights != R    
        }else{
          if($config->usergroups && ($usergrouprights == 'R')){ 
            // if usergroups, zone is owned by
            // group and current user has no creation rights
            $content = sprintf($html->string_warning,
               $l['str_not_allowed_by_group_admin_to_create_write_zones'] . 
              $l['str_validation_of_this_form_will_be_inactive'] 
              );
          }else{
            $content = "";
          }
          // to let user access advanced interface, even if not
          // in its preferences.
          if((isset($_REQUEST) && isset($_REQUEST['advanced'])) ||
            (!isset($_REQUEST) && isset($advanced))){
            $advanced = 1;
          }else{
            $advanced = $user->advanced;
          }
          // to let user access ipv6 interface, even if not
          // in its preferences.
          if((isset($_REQUEST) && isset($_REQUEST['ipv6'])) ||
            (!isset($_REQUEST) && isset($ipv6))){
            $ipv6 = 1;
          }else{
            $ipv6 = $user->ipv6;
          }
          // to let user modify nbrows
          if(isset($_REQUEST) && isset($_REQUEST['nbrows'])){
            $nbrows = intval($_REQUEST['nbrows']);
          }else{
            if(!isset($_REQUEST) && isset($nbrows)){
              // nothing to be done
            }else{
              if(isset($user->nbrows)){
                $nbrows = $user->nbrows;
              }else{
                $nbrows = $config->defaultnbrows;
              }
            }
          }
          if ($nbrows > 16) $nbrows = 16;

          $content .= $currentzone->printModifyForm(array($advanced,$ipv6,$nbrows));
        }
        if($config->usergroups){
          if($config->userlogs){
            // $usergrouprights was set in includes/login.php
            if(($usergrouprights == 'R') || ($usergrouprights =='W')){
              $content .= sprintf($html->string_warningi,
                    $l['str_as_member_of_group_action_logged']
                    );
            }
          }  
        }
      }
    }
  }else{
    $title =  $l['str_choose_a_zone_to_modify'];
  
    if($config->usergroups){
      $allzones = $group->listallzones();
      $user->error=$group->error;      
    }else{
      $allzones = $user->listallzones();
    }
  
    if(!notnull($user->error)){
      $content =  '<h3 class="boxheader">' . $l['str_choose_a_zone_to_modify']
      . '</h3>';
      $content .='<table>';
      while($otherzone= array_pop($allzones)){
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
        $content .= '<tr><td><a href="' .  $_SERVER["PHP_SELF"]
        .$link.'&amp;zonename=' . $newzone->zonename . '&amp;zonetype=' .
        $newzone->zonetype . '" class="linkcolor">' .
         $newzone->zonename . '</a> (' . $newzone->zonetype . ')</td><td
         class="loghighlight' . $class . '" align="center"><a href="logwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '" class="linkcolor" onclick="window.open(\'logwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype .
    
'\',\'' . $l['str_logs'] . '\',\'toolbar=no,location=no,directories=no,status=yes,alwaysraised=yes,dependant=yes,resizable=yes,scrollbars=yes,menubar=no,width=640,height=480\');
return false">'.
         $status . '</a></td></tr>';
      }

      $content .= '</table>';
    }else{
      $content = $user->error;
    }
  }
}elseif($user->authenticated>=2){
  $title = $l['str_modify_zone_title'];
  $content = migrationbox();
}else{
  $title = $l['str_modify_zone_title'];
  $content = $l['str_must_log_before_modifying_zone'];
}
$content = '<div id="modify">
' . $content . '</div> <!-- modify -->
';
print $html->box('mainbox',$title,$content);

if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}


print $html->footer();
?>
