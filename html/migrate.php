<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

  // modify user parameters

$page_title="str_user_preferences";  
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}
$boxname='migrate';
$title = $l['str_index_migrate'];
$localerror=0;
// main content
if($user->authenticated == 0){
  $content = $l['str_must_log_before_editing_pref'];
}else{
  // print login, email, change password
  // valid or not
  if((isset($_REQUEST) && !isset($_REQUEST['modify'])) ||
    (!isset($_REQUEST) && !$modify)){
    $content = "";
    if ($user->authenticated == 3) {
      $content = $l['str_migrate_subaccount'];
    } else
    if ($user->authenticated == 2) {
    $content .= '
      <form action="' .  $_SERVER["PHP_SELF"] . '" method="post">
      <input type="hidden" name="modify" value="1">' . $hiddenfields . '
      <table id="migratetable">';
    $content .= '<tr><td align="center" colspan="2">' . 
      $l['str_migrate_me'] . '</td></tr>';
    $content .= '
      <tr><td colspan="2" align="center">
      <input type="submit" class="submit" value="' . $l['str_migrate_button'] . '"></td></tr>
      </table>
      </form>
      ';
    } else {
      $content = $l['str_migrate_already_done'];
    }
  }else{
    if ($user->authenticated == 2) {
      if ($user->MigrateMe())
      $content = $l['str_migrate_success'];
    } else {
      $content = $l['str_migrate_already_done'];
    }
  }
}

print $html->box($boxname,$title,$content);

if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();
?>
