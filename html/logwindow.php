<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

// ********************************************************
// Nothing to be changed here regarding design
// ********************************************************


require 'libs/xname.php';

$config = new Config();



// protect variables for db usage
if(isset($_REQUEST) && isset($_REQUEST['idsession'])){
  $idsession=$_REQUEST['idsession'];
}
if(isset($idsession)){
  $idsession = addslashes($idsession);
}

if(isset($_REQUEST) && isset($_REQUEST['login'])){
  $login=$_REQUEST['login'];
}
if(isset($login)){
  $login = addslashes($login);
}

if(isset($_REQUEST) && isset($_REQUEST['password'])){
  $password=$_REQUEST['password'];
}
if(isset($password)){
  $password = addslashes($password);
}

$html = new Html();


$db = new Db();
if($config->userdbname){
        $dbauth = new DbAuth();
}else{
        $dbauth = $db;
}

if(isset($_REQUEST)){
  if(isset($_REQUEST['language'])){
    $lang = $_REQUEST['language'];
  }else{
    $lang = $config->defaultlanguage;
  }
}else{
  if(isset($language)){
    $lang=$language;
  }else{
    $lang = $config->defaultlanguage;
  }
}
$lang=substr($lang, 0, 2);
if (!is_file('includes/strings/' . $lang. '/strings.php'))
  $lang = $config->defaultlanguage;
$html->initialize();

$user = new User($login,$password,$idsession);
if($config->usergroups){
        include 'libs/group.php';
        $group = new Group($user->userid);
        if($config->userlogs){
                include 'libs/userlogs.php';
                $userlogs=new UserLogs($group->groupid,$user->userid);
        }
}
$lang = $config->defaultlanguage;
// overwrite default strings
if(isset($user->lang)){
  $lang = $user->lang;
}else{
  if(isset($_REQUEST)){
    if(isset($_REQUEST['language'])){
      $lang = $_REQUEST['language'];
    }else{
      $lang = $config->defaultlanguage;
    }
  }else{
    if(isset($language)){
      $lang=$language;
    }
  }
}

$lang=substr($lang, 0, 2);
// verify if language exists ! 
if(!is_file('includes/strings/' . $lang . '/strings.php')){
  $lang = $config->defaultlanguage;
}

include 'includes/strings/' . $lang . '/strings.php';

// reinitialize with definitive right language
$html->initialize();
print $html->header($l['str_log_viewer_title']);
$title=$l['str_log_viewer_title'];

if(!notnull($idsession)){
  $idsession=$user->idsession;
}

if((isset($_REQUEST['logout']) && $_REQUEST['logout']) || (isset($logout) &&
$logout)){
  $user->logout($idsession);
}

if(notnull($idsession)){
        $link="?idsession=" . $idsession;
        $hiddenfields = '<input type="hidden" name="idsession" value="' . $idsession . '">';
        // add language only if different 
        if(strcmp($lang,$user->lang)){
                $link .= "&amp;language=" . $lang;
                $hiddenfields .= '<input type="hidden" name="language" value="' . $lang . '">';

        }
}else{
        $link="?language=" .$lang;
}

if($user->error){
  printf($html->string_error,$user->error);
}

if($user->authenticated==1){

  if(isset($_REQUEST)){
    $zonename = $_REQUEST['zonename'];
    $zonetype = $_REQUEST['zonetype'];
  }
  $zonename = addslashes($zonename);
  $zonetype = addslashes($zonetype);
  $zone = new Zone($zonename,$zonetype);
  if($zone->error){
    printf($html->string_error,$user->error);
  }else if($zone->RetrieveUser() != $user->userid &&
         ($config->usergroups && 
         $zone->RetrieveUser() != $group->groupid)){
         $content = sprintf($html->string_error, 
         sprintf($l['str_you_can_not_view_logs_zone_x_x'],
                $zone->zonename,$zone->zonetype)
                );
    print $html->box('logwindow',$title,$content);
  }else{
    $title = sprintf($l['str_last_logs_for_x'],$zone->zonename);
    $content = "";
    // if $deleteall, delete & insert a "deleted" line in logs
    // maybe only admin should be able to delete logs... ?
    if((isset($_REQUEST) && $_REQUEST['deleteall']) ||
      (!isset($_REQUEST) && $deleteall == 1)){
      if(!$zone->zoneLogDelete()){
        $content = sprintf($html->string_error,$zone->error);
      }
    }
    
    $content .= '
    <table class="logtable">' .
    $zone->zoneLogs("loghighlight","loglowlight") . 
    '</table>
    <div align="center">
    <form action="' .  $_SERVER["PHP_SELF"] . '" method="get">
    <input type="hidden" name="deleteall" value="1">
    ' . $hiddenfields . '
    <input type="hidden" name="zonename" value="' . $zonename . '">
    <input type="hidden" name="zonetype" value="' . $zonetype . '">    
    <input type="submit" class="submit" name="deletebutton" value="' . 
    $l['str_delete_all_logs'] . '">
    </form></div>';
  
    print $html->box('logwindow',$title,$content);
  }
}

// print close "window"

print $html->footerlight();
?>
