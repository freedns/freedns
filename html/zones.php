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
    <tr>
    <td style="width:30%" class="boxheader">' . $l['str_zone'] . '</td>
    <td class="boxheader">' . $l['str_name_server'] . '</td>
    <td class="boxheader">' . $l['str_serial'] . '</td>
    <td class="boxheader">' . $l['str_view'] . '</td>
    </tr>';

    // retrieve & generate our name servers list
    $ourservernames = GetListOfServerNames();
    $ourserverlist = array();
    while($servername = array_pop($ourservernames)){
      array_push($ourserverlist, $servername . ".");
    }

    while($otherzone= array_pop($allzones)){
      $newzone = new Zone($otherzone[0],$otherzone[1],$otherzone[2]);
      if($newzone->zonetype=='P'){
        $primary = new Primary($newzone->zonename,$newzone->zonetype,$user->userid);
        $nameservers = array_keys($primary->ns);
      }else{
        $secondary = new Secondary($newzone->zonename,$newzone->zonetype,$user->userid);
        $masters = split(';',$secondary->masters);
        $nameservers = array_merge($masters,$ourserverlist);
      }
      $urlpar = $link . '&amp;zonename=' . $newzone->zonename .
                        '&amp;zonetype=' . $newzone->zonetype;
      $urlmod = "modify.php" . $urlpar;
      $urldig = 'digwindow.php' . $urlpar;
      $urllog = 'logwindow.php' . $urlpar;

      $content .= '<tr><td colspan="3">
          <a href="' . $urlmod . '" class="linkcolor">' .
             $newzone->zonename . '</a> (' . $newzone->zonetype . ')</td>' .
          '<td><a href="' . urlpop($urllog) . '">' . $l['str_logs'] . '</a></td></tr>';

      // for each retrieve NS & do DigSerial($zone,$server)
      while($nameserver = array_pop($nameservers)){
        $serial = DigSerial($nameserver,$newzone->zonename);
        if($serial == $l['str_not_available']){
          $serial = sprintf($html->fontred, $l['str_not_available']);
        }
        $content .= "<tr><td></td><td>$nameserver</td><td>$serial</td>";
        $content .= '<td><a href="' . 
          urlpop($urldig . '&amp;server=' . $nameserver) .
          '">' . $l['str_zone_content'] .  '</a></td></tr>';
      } // nameservers
    } // allzones
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
