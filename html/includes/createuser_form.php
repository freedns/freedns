<?php
  # basic info
  $content .= sprintf('<form action="%s" method="post">', $_SERVER['PHP_SELF']);
  $content .= '<table id="createusertable"><tr>';
  $content .= sprintf('<td align="right">%s:</td>', $l['str_login']);
  $content .= sprintf('<td><input type="text" name="loginnew" value="%s"></td>', $loginnew);
  $content .= '</tr><tr>';
  $content .= sprintf('<td align=right>%s</td>', $l['str_your_valid_email']);
  $content .= sprintf('<td><input type="text" name="email" value="%s"></td>', $email);
  $content .= '</tr><tr>';
  $content .= sprintf('<td align="right">%s:</td>', $l['str_new_password']);
  $content .= '<td><input type="password" name="passwordnew"></td>';
  $content .= '</tr><tr>';
  $content .= sprintf('<td align="right">%s:</td>', $l['str_confirm_password']);
  $content .= '<td><input type="password" name="confirmpasswordnew"></td>';
  $content .= '</tr>';

  # advanced parameters checkboxes
  if ($config->advancedinterface) {
    $content .= sprintf('<tr><td align="right">%s<br>(%s)</td>',
        $l['str_advanced_interface'], $l['str_advanced_interface_details']);
    $content .= sprintf('<td><input type=checkbox name="advanced"%s></td></tr>',
      ($user->advanced || !empty($_REQUEST['advanced'])) ? ' checked' : '');
  }
  if ($config->ipv6interface) {
    $content .= sprintf('<tr><td align="right">%s<br>(%s)</td>',
        $l['str_ipv6_interface'], $l['str_ipv6_interface_details']);
    $content .= sprintf('<td><input type=checkbox name="ipv6"%s></td></tr>',
        ($user->ipv6 || !empty($_REQUEST['ipv6'])) ? ' checked' : '');
  }
  if ($config->txtrecords) {
    $content .= sprintf('<tr><td align="right">%s<br>(%s)</td>',
        $l['str_txt_records'], $l['str_txt_records_details']);
    $content .= sprintf('<td><input type=checkbox name="txtrecords"%s></td></tr>',
        ($user->txtrecords || !empty($_REQUEST['txtrecords'])) ? ' checked' : '');
  }
  if ($config->srvrecords) {
    $content .= sprintf('<tr><td align="right">%s<br>(%s)</td>',
        $l['str_srv_records'], $l['str_srv_records_details']);
    $content .= sprintf('<td><input type=checkbox name="srvrecords"%s></td></tr>',
        ($user->srvrecords || !empty($_REQUEST['srvrecords'])) ? ' checked' : '');
  }

  # number-of-entries row
  if (empty($nbrows))
    $nbrows = $config->defaultnbrows;
  if ($user->authenticated) {
    if ($user->nbrows > $nbrows)
      $nbrows = $user->nbrows;
  } else {
    if (!empty($_REQUEST['nbrows']) && $_REQUEST['nbrows'] > $nbrows)
      $nbrows = $_REQUEST['nbrows'];
  }
  if ($nbrows > 16)
    $nbrows = 16;
  $content .= sprintf('<tr><td align="right">%s:</td>', $l['str_number_of_rows_per_record']);
  $content .= sprintf('<td><input type=text name="nbrows" value="%s" size="3"></td></tr>', $nbrows);

  # language row
  $content .= sprintf('<tr><td align="right">%s:</td>', $l['str_language']);
  $content .= '<td><select name="newlang">';
  // select current available langs
  $list = GetDirList('includes/strings');
  reset($list);
  while ($newlang = array_shift($list)) {
    $content .= sprintf('<option value="%s"%s>%s</option>',
        $newlang, ($newlang === $lang) ? ' selected' : '', $newlang);
  }
  $content .= '</select></td></tr>';

  # disclaimer row
  $content .= '<tr><td width="65%" align="right">';
  $_url = sprintf('disclaimer.php?language=%s', $lang);
  $_url2 = sprintf('%sdisclaimer.php', $config->mainurl);
  $_winopt = sprintf("'%s','M','toolbar=no,location=no,directories=no," .
      "status=no,alwaysraised=yes,dependant=yes,menubar=no,scrollbars=yes," .
      "resizable=yes,width=640,height=480'", $_url);
  $_disclaimer = sprintf('<a target=_blank href="%s" onclick="window.open(%s)">%s</a>',
      $_url, $_winopt, $_url2);
  $content .= sprintf($l['str_i_have_read_and_i_understand_discl_available_at_x'], $_disclaimer);
  $content .= '</td>';
  $content .= '<td><input type="checkbox" name="ihaveread" value="1"></td></tr>';
  $content .= '<tr><td colspan="2" align="center">';
  $content .= sprintf('<input type="submit" class="submit" value="%s"></td></tr>',
      $l['str_create_my_user_button']);
  $content .= '</table></form>';
?>
