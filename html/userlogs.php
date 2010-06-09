<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

// print group logs, with sorting by user, zone or date
// (also sorted in each sub category)

$page_title="str_user_logs_title";
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

if($user->authenticated==1){
  $title=$l['str_user_logs_title'];
  $content ="";
  if($config->usergroups){
    // $group is already set in include/login.php
    if($usergrouprights == 'A'){
      // main content
       if(isset($_REQUEST)){
        if(isset($_REQUEST['sortcategory'])){
          $sortcategory = $_REQUEST['sortcategory'];
        }
        if(isset($_REQUEST['order'])){
          $order = $_REQUEST['order'];
        }
      }
      if(!isset($sortcategory)){
        $sortcategory='D'; // default is by date
      }
      if(!isset($order)){
        $order='D'; // default is descending
      }
      $sortcategory = addslashes($sortcategory);
      
      
      // delete 
      if((isset($_REQUEST) && (isset($_REQUEST['delete'])))
        || (!isset($_REQUEST) && isset($delete))){
        $listofdelete = retrieveArgs("delete", $_REQUEST);
        $numbertodelete = count($listofdelete);
        $localerror ="";
        while($todelete=array_pop($listofdelete)){
          if(!$userlogs->deleteLog($todelete)){
            $localerror = $userlogs->error;
          }
        }
        if($localerror){
          $content .= sprintf($html->string_error,
                sprintf($l['str_while_deleting_logs_x'],
                $localerror)
              ) . '<br>';
        }else{
          if($numbertodelete){
            $content .= $numbertodelete . " " .
                  $l['str_logs_successfully_deleted'] . '<br>';
          }else{
            $content .= $l['str_no_logs_for_deletion'] . '<br>';
          }
        }
      
      }
      
      // purge
      if((isset($_REQUEST) && (isset($_REQUEST['purgebutton'])))
        || (!isset($_REQUEST) && isset($purgebutton))){
        
        if(isset($_REQUEST)){
          $purge=$_REQUEST['purge'];
        }

        $nowdate= nowDate();
        $year=substr($nowdate,0,4);
        $month=substr($nowdate,4,2);
        $day=substr($nowdate,6,2);
        $hour=substr($nowdate,8,2);
        $min=substr($nowdate,10,2);
        $sec=substr($nowdate,12,2);
        $datets=mktime($hour,$min,$sec,$month,$day,$year);

        switch($purge){
          case "all":
            $listofdelete = retrieveArgs("id", $_REQUEST);
            $numbertodelete = count($listofdelete);
            $localerror ="";
            while($todelete=array_pop($listofdelete)){
              if(!$userlogs->deleteLog($todelete)){
                $html = $userlogs->error;
              }
            }
            if($localerror){
              $userlogs->error=$localerror;
            }else{
              if($numbertodelete){
                $content .= sprintf($l['str_x_logs_purged'],
                        $numbertodelete)
                      .'<br>';
              }
            }
            break;
            
          case "day":
            $deletedate = timestampToDate($datets -60*60*24);
            $userlogs->deleteLogsBefore($deletedate);
            break;
          case "month":
            $deletedate = timestampToDate($datets -60*60*24*30);
            $userlogs->deleteLogsBefore($deletedate);
            break;
          case "year":
            $deletedate = timestampToDate($datets -60*60*24*365);
            $userlogs->deleteLogsBefore($deletedate);
            break;
        } // end switch purge
        if($userlogs->error){
          $content .= sprintf($html->string_error, 
            sprintf($l['str_while_purging_logs_x'],
              $userlogs->error) 
              ) . '<br>';
        }
      } // end if purge
      
      
      // print table with logs & delete
      $content .= '<form action="' .  $_SERVER["PHP_SELF"] . '" method="get">
      ' . $hiddenfields . '
      <input type="hidden" name="sortcategory" value="' . $sortcategory .
      '"><input type="hidden" name="order" value="' . $order .'">
      ' . $l['str_sort_results'] . ':&nbsp;&nbsp; 
      <a href="' .  $_SERVER["PHP_SELF"] . $link .
      '&amp;sortcategory=D">' . $l['str_per_date'] . '</a> &nbsp;&nbsp;
      |&nbsp;&nbsp; <a href="' . $PHP_SELF . $link .
      '&amp;sortcategory=Z">' . $l['str_per_zone'] . '</a>&nbsp;&nbsp; |&nbsp;&nbsp; 
      <a href="' .  $_SERVER["PHP_SELF"] . $link .
      '&amp;sortcategory=U">' . $l['str_per_user'] . '</a><br>
      <a href="' .  $_SERVER["PHP_SELF"] . $link . '&amp;sortcategory=' .
      $sortcategory . '&amp;order=A">&lt;</a> 
      <a href="' .  $_SERVER["PHP_SELF"] . $link . '&amp;sortcategory=' .
      $sortcategory . '&amp;order=D">&gt;</a> 
      <p >
      <table id="userlogstable">
      ';

      switch($sortcategory){
        case "Z": // print by zone
          $listoflogs=$userlogs->showGroupLogs("zoneid", $order);
          break;
        case "U": // print by user
          $listoflogs=$userlogs->showGroupLogs("userid", $order);
          break;
        default: // print by date
          $listoflogs=$userlogs->showGroupLogs("date", $order);
      } // end switch $sortcategory
      
      $deletecount=0;
      if(!count($listoflogs)){
        $content .= '<tr><td>' . $l['str_no_logs_available'] . '</td></tr>';
      }
      while($line=array_pop($listoflogs)){ 
        // id/date/userid/zoneid/content
        $deletecount++;
        // MySQL 3
        $newdate = preg_replace("/^(....)(..)(..)(..)(..)(..)$/",
            "\\2&nbsp;\\3&nbsp;\\4:\\5:\\6", 
            $line[1]);
        // MySQL 4
        $newdate = preg_replace("/^(....)-(..)-(..) (..):(..):(..)$/",
            "\\2&nbsp;\\3&nbsp;\\4:\\5:\\6", 
            $newdate);
        $content .= '<tr><td valign="top">';
        // dummy entry necessary for retrieveArgs to work
        // correctly... 
        // used for purge.
        $content .= '<input type="hidden" name="id' . $deletecount . '"
        value="' . $line[0] . '">';
        $content .= $newdate . '</td>
        <td valign="top">' .
        $user->RetrieveLogin($line[2]) . '</td>
        <td valign="top">' . $line[4] . '</td><td valign="top">
        <input type="checkbox" name="delete' . $deletecount . '"
        value="' . $line[0] . '">' . $l['str_delete'] . '</td></tr>';
      }
      $content .= '</table>
      <table id="clearlogstable"><tr><td align="center">
      <select name="purge">
      <option value="all" selected>' . $l['str_purge_all'] . '</option>
      <option value="day">' . $l['str_purge_keeping_last_day'] . '</option>
      <option value="month">' . $l['str_purge_keeping_last_month'] . '</option>
      <option value="year">' . $l['str_purge_keeping_last_year'] . '</option>
      </select><input type="submit" class="submit" name="purgebutton" value="' .
      $l['str_purge'] . '">
      </td>
      <td align="right">
      <input type="submit" class="submit" name="delete" value="' . 
      $l['str_delete_selected'].'">
      </td></tr></table>
      </form>';
      
    }else{ // end groupright == A
      $content = sprintf($html->string_error, 
            $l['str_you_are_not_admin_of_your_group'] .
            $l['str_only_admin_can_access_logs']
          );
    } // end groupright != A
  }else{ // end config usergroups
    $content = sprintf($html->string_error, 
          $l['str_groups_disabled_in_server_conf']
        );
  }
}else{ // end $user->authenticated
  $title=$l['str_uppercase_error'];
  $content=$l['str_must_log_first'];
}

if ($user->authenticated>=2) print migrationbox(1);
else
print $html->box('mainbox',$title,$content);

if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();
?>
