<?php
$title = $l['str_loginbox'];
if ($user->authenticated == 0) {
  // login box
  $content = "<div>";
  if ($user->error) {
    $content = sprintf($html->string_error, $user->error) . "<br>\n";
  }
  if ($config->public) {
    $content .= '<p>' . sprintf(
        $l['str_login_or_x_create_new_user_x'],
        sprintf('<a href="createuser.php%s" class="linkcolor">', $link),
        '</a>') . '</p>';
  }
  $content .= sprintf('<form method="post" action="%sindex.php">', $config->mainurl);
  $content .= sprintf('<p><label for="login">%s</label><br>', $l['str_login']);
  $content .= '<input type="text" id="login" name="login"><br>';
  $content .= sprintf('<label for="password">%s</label><br>', $l['str_password']);
  $content .= '<input type="password" name="password" id="password"><br>';
  $content .= sprintf('<input type="hidden" name="language" value="%s">', $lang);
  $content .= sprintf('<input type="submit" class="submit" value="%s">', $l['str_log_me_in']);
  $content .= sprintf('</p><p><a href="password.php%s" class="linkcolor">', $link);
  $content .= $l['str_forgot_password'] . '</a></p></div></form>';
  print $html->box('login', $title, $content);

} else { // if authenticated,
  // print pref box

  $content = '<div>';
  $content .= sprintf('<p>%s</p>', sprintf($l['str_you_are_logged_in_as_x'], $user->login));
  $content .= sprintf('<p><a href="%suser.php%s" class="linkcolor">%s</a></p>',
      $config->mainurl, $link, $l['str_change_your_preferences']);

  if ($config->usergroups) {
    $content .= '<p>';
    $usergrouprights = $group->getGroupRights($user->userid);
    if (empty($user->error)) {
      switch ($usergrouprights){
        case '0':
          $content .= $user->error;
          break;
        case 'A':
          $content .= sprintf(
              '<a href="group.php%s" class="linkcolor">%s</a>',
              $link, $l['str_administrate_your_group']);
          if ($config->userlogs) {
            $content .= sprintf(
                '<br><a href="userlogs.php%s" class="linkcolor">%s</a>',
                $link, $l['str_view_group_logs']);
          }
          break;
        case 'R':
          $content .= $l['str_you_have_read_only_access'];
          break;
        case 'W':
          $content .= $l['str_you_have_read_write_access'];
          break;
        default:
          $content .= sprintf($html->string_error, $l['str_wrong_group_rights']);
      }
    } else {
      $content .=  sprintf($html->string_error, $user->error);
    }
    $content .= '</p>';
  }
  $content .= '<p>';
  $content .= sprintf(
      '<a href="deleteuser.php%s" class="linkcolor">%s</a>',
      $link, $l['str_delete_your_account']);
  $content .= '</p><p>';
  $content .= sprintf(
      '<a href="index.php%s&amp;logout=1">%s</a>', $link, $l['str_logout']);
  $content .= '</p></div>';

  print $html->box('login', $title, $content);

  // list all other zones for current user
  if($config->usergroups){
    $allzones = $group->listallzones();
    $user->error = $group->error;
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
    while ($otherzone = array_pop($allzones)) {
      $newzone = new Zone($otherzone[0], $otherzone[1], $otherzone[2]);
      $status = $newzone->zonestatus();
      $urlpar = sprintf('%s&amp;zonename=%s&amp;zonetype=%s',
                        $link, $newzone->zonename, $newzone->zonetype);
      $_winopt = sprintf("'logwindow.php%s','M','toolbar=no,location=no,directories=no," .
          "status=no,alwaysraised=yes,dependant=yes,menubar=no,scrollbars=yes," .
          "resizable=yes,width=640,height=480'", $urlpar);
      $_loginfo = sprintf(
          '<a href="logwindow.php%s" class="linkcolor" onclick="window.open(%s); return false">' .
          '<span class="%s">&nbsp;@</span><span class="%s">&nbsp;&nbsp;</span></a>',
          $urlpar, $_winopt, $status[0], $status[1]);
      $_zoneinfo = sprintf(
          '<a href="zones.php%s" class="linkcolor">%s</a> (%s)',
          $urlpar, $newzone->zonename, $newzone->zonetype);

      $content .= sprintf(
          '<tr><td align="center">%s</td><td>%s</td></tr>',
          $_loginfo, $_zoneinfo);
    }
    $content .= '</table>';
  } else {
    $content = $user->error;
    if ($user->authenticated >= 2) $content = "";
  }
  $title = $l['str_all_your_zones'];
  print $html->box('yourzones', $title, $content);
}
