<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

  // validate email address, using $id.
  // delete from  dns_waitingreply
  
$page_title="str_email_validation_title";
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}


// ***************************************************

$title = $l['str_email_validation_title'];

if((isset($_REQUEST) && !empty($_REQUEST['id'])) || 
  (!isset($_REQUEST) && !empty($id))){
  if(isset($_REQUEST)){
    $id = $_REQUEST['id'];
  }
  if($user->validateIDEmail($id)){
  
    $content = $l['str_email_flagged_valid'] . '<br>' .
    sprintf($l['str_you_can_now_use_the_x_main_interface_x_to_log_in'],
    '<a href="index.php?language=' . $lang . '">','</a>');
  }else{
    $content = sprintf($html->string_error,$user->error);
  }
}else{
  $content = $l['str_wrong_access'];
}


// main content

print $html->box('mainbox',$title,$content);


if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}


print $html->footer();
?>
