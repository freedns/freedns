<?
$title = $l['str_loginbox'];
if($user->authenticated == 0){
  // login box
  $content ="";
  if($user->error){
    $content = sprintf($html->string_error,$user->error) . 
          "<br>\n";
  }  

  if($config->public){
    $content .= sprintf($l['str_login_or_x_create_new_user_x'],
      '<a href="createuser.php' . $link . '" class="linkcolor">',
      '</a>') . 
      '<br>';
  }
   $content .= '<form method="post" action="' .
       $config->mainurl . 'index.php">' .
  $l['str_login'] . ': <br><div align="center"><input
  type="text" name="login" ></div><br>
  ' . $l['str_password'] . ': <br><div align="center"><input type="password" name="password"
  ></div><br>
  <input type="hidden" name="language" value="' . $lang . '" >
  <div align="center"><input type="submit" class="submit" value="' . $l['str_log_me_in'] . '" ><p>
  <a href="password.php' . $link . '" class="linkcolor">' . $l['str_forgot_password'] . '</a>
  </p>
  </div>
  </form>
  ';
  print $html->box('login',$title,$content);
  
}else{ // if authenticated, 
    // print pref box
  
  $content = '<div><p>' . sprintf($l['str_you_are_logged_in_as_x'], $user->login) . '</p>
  <p><a href="'.       $config->mainurl . 'user.php' . $link . '" class="linkcolor">' . 
    $l['str_change_your_preferences'] . '</a></p>
  ';
  if($config->usergroups){
    $content .= '<p>';
    $usergrouprights = $group->getGroupRights($user->userid);
    if(empty($user->error)){
      switch ($usergrouprights){
        case '0':
          $content .= $user->error;
          break;
        case 'A':
          $content .=   '
          <a href="group.php' . $link . '" class="linkcolor">' . 
           $l['str_administrate_your_group'] . '</a>';
          if($config->userlogs){
            $content .= '<br><a href="userlogs.php' . $link . '"
            class="linkcolor">' . $l['str_view_group_logs'] . '</a>';
          }
          $content .= '<br>
          ';
          break;
        case 'R':
          $content .= $l['str_you_have_read_only_access'] . '<br>';
          break;
        case 'W':
          $content .= $l['str_you_have_read_write_access'] . '<br>';
          break;
        default:
          $content .= sprintf($html->string_error,
              $l['str_wrong_group_rights']
              ) . '<br>';
      }
    }else{
      $content .=  sprintf($html->string_error,
            $user->error) . "<br>";
    }
    $content .= '</p>';
  }
  $content .= '
  <p><a href="deleteuser.php' . $link . '" class="linkcolor">' . 
      $l['str_delete_your_account']  . '</a></p>
  <p><a href="index.php' . $link . '&amp;logout=1">' . $l['str_logout'] . '</a></p>
  </div>
  ';
  
  print $html->box('login',$title,$content);
  
  // list all other zones for current user
  if($config->usergroups){
    $allzones = $group->listallzones();
    $user->error=$group->error;
  }else{
    $allzones = $user->listallzones();
  }
  if (empty($user->error)) {
    $content = '<div id="legend">';
    $content .= sprintf('<span class="I">%s</span>', $l['str_log_information']);
    $content .= sprintf('<span class="W">%s</span>', $l['str_log_warning']);
    $content .= sprintf('<span class="E">%s</span>', $l['str_log_error']);
    $content .= '</div>';

    $content .= '<table id="zonelisttable">';
    while ($otherzone= array_pop($allzones)) {
      $newzone = new Zone($otherzone[0], $otherzone[1], $otherzone[2]);
      $status = $newzone->zonestatus();
      $class = $status[0];
      if ($status[0] != 'E' and $status[1] == 'E')
        $class2 = 'Elight';
      else
        $class2 = $status[1];
      $urlpar = sprintf('%s&amp;zonename=%s&amp;zonetype=%s',
                        $link, $newzone->zonename, $newzone->zonetype);
      $_url = sprintf('logwindow.php%s', $urlpar);
      $_winopt = sprintf("'%s','M','toolbar=no,location=no,directories=no," .
          "status=no,alwaysraised=yes,dependant=yes,menubar=no,scrollbars=yes," .
          "resizable=yes,width=640,height=480'", $_url);

      $content .= '<tr><td align="center">';
      $content .= sprintf(
          '<a href="%s" class="linkcolor" onclick="window.open(%s); return false">' .
          '<span class="%s">&nbsp;@</span><span class="%s">&nbsp;&nbsp;</span></a>',
          $_url, $_winopt, $class, $class2);
      $content .= '</td><td>';
      $content .= sprintf(
          '<a href="zones.php%s" class="linkcolor">%s</a> (%s)',
          $urlpar, $newzone->zonename, $newzone->zonetype);
      $content .= '</td></tr>';
    }
    $content .= '</table>';
  } else {
    $content = $user->error;
    if ($user->authenticated >= 2) $content = "";
  }
  $title = $l['str_all_your_zones'];
  print $html->box('yourzones', $title, $content);
}
