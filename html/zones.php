<?

/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

$page_title="str_view_zones_title";
// headers 
include 'includes/header.php';

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}

$zone = @$_REQUEST['zonename'];

// main content
$title = $l['str_view_zones_title'];
if($user->authenticated == 0){
        $content = $l['str_must_log_before_viewing_zones'];
}else{
  // list all zones, with serials, etc....
  // and form to change email & password for $user

  if($config->usergroups){
    $allzones = $group->listallzones($zone);
    $user->error=$group->error;    
  }else{
    $allzones = $user->listallzones($zone);
  }

  if(!notnull($user->error)){
    $content ='<table id="listzonestable">
    <tr><td class="boxheader">' . $l['str_zone'] . '</td>
    <td class="boxheader">' . $l['str_name_server'] . '</td>
    <td class="boxheader">' . $l['str_serial'] . '</td>
    <td class="boxheader">' . $l['str_view'] . '</td>
    <td class="boxheader">' . $l['str_status'] . '</td></tr>';

    // retrieve & generate our name servers list
    $ourservernames = GetListOfServerNames();
    $ourserverlist = array();
    while($servername = array_pop($ourservernames)){
      array_push($ourserverlist, $servername . ".");
    }


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
      $content .= '<tr><td colspan="3"><a href="modify.php'
      .$link.'&amp;zonename=' . $newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '" class="linkcolor">' .
       $newzone->zonename . '</a> (' . $newzone->zonetype . ')</td>
       <td><a href="logwindow.php' 
       .$link.'&amp;zonename=' . $newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '" class="linkcolor" onclick="window.open(\'logwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype .
    
'\',\'M\',\'toolbar=no,location=no,directories=no,status=no,alwaysraised=yes,dependant=yes,resizable=yes,menubar=no,scrollbars=yes,width=640,height=480\');
return false">' . $l['str_logs'] . '</a></td>
      <td
       class="loghighlight' . $class . '" align="center"><a href="logwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '" class="linkcolor" onclick="window.open(\'logwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype .
    
'\',\'Logs\',\'toolbar=no,location=no,directories=no,status=yes,alwaysraised=yes,dependant=yes,resizable=yes,scrollbars=yes,menubar=no,width=640,height=480\');
return false">'.
       $status . '</a></td></tr>';
      // for each retrieve NS & do DigSerial($zone,$server)
      if($newzone->zonetype=='P'){
        $primary = new
      
Primary($newzone->zonename,$newzone->zonetype,$user->userid);
        $keys = array_keys($primary->ns);
        while($nameserver = array_shift($keys)){
          $serial = DigSerial($nameserver,$primary->zonename);
          if($serial == $l['str_not_available']){
            $serial = sprintf($html->fontred,
                $l['str_not_available']
                );
          }
          $content .= '
          <tr><td width="20">&nbsp;</td><td>' . $nameserver .
          '</td><td>' . $serial . '</td><td>
          <a href="digwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '&amp;server=' . $nameserver . '" class="linkcolor" onclick="window.open(\'digwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '&amp;server=' . $nameserver . 
    
'\',\'M\',\'toolbar=no,location=no,directories=no,status=no,alwaysraised=yes,dependant=yes,menubar=no,resizable=yes,scrollbars=yes,width=640,height=480\');
return false">' . $l['str_zone_content'] . '</a></td></tr>';
        }
      }else{
        // secondary NS
        $secondary = new
      
Secondary($newzone->zonename,$newzone->zonetype,$user->userid);
        $masters = split(';',$secondary->masters);
        // add our NS server to secondary NS servers 
        $masters = array_merge($masters,$ourserverlist);
        while($nameserver = array_pop($masters)){
          $serial = DigSerial($nameserver,$secondary->zonename);
          if($serial == 'not available'){
            $serial = sprintf($html->fontred,
                $l['str_not_available']
                );
          }
          $content .= '
          <tr><td width="20">&nbsp;</td><td>' . $nameserver .
          '</td><td>' . $serial . '</td><td>
          <a href="digwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '&amp;server=' . $nameserver . '" class="linkcolor" onclick="window.open(\'digwindow.php'
       .$link .'&amp;zonename=' .$newzone->zonename . '&amp;zonetype=' .
      $newzone->zonetype . '&amp;server=' . $nameserver . 
    
'\',\'M\',\'toolbar=no,location=no,directories=no,status=no,alwaysraised=yes,dependant=yes,menubar=no,resizable=yes,scrollbars=yes,width=640,height=480\');
return false">' . $l['str_zone_content'] . '</a></td></tr>';
          
        }
        
      }
    }
    $content .= '</table>';
  }else{
    $content = $user->error;
  }  
}


// *************************************
//          END OF CONTENT
// *************************************

print $html->box('mainbox',$title,$content);


if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}

print $html->footer();
?>
