<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

// ********************************************************
// Nothing to be changed in this file regarding design
// ********************************************************

require 'libs/xname.php';

$config = new Config();


// protect variables for db usage  // this is dubious
if (isset($_REQUEST) && isset($_REQUEST['idsession'])) {
  $idsession = addslashes($_REQUEST['idsession']);
} else {
  $idsession = "";
}

if (isset($_REQUEST) && isset($_REQUEST['login'])) {
  $login = addslashes($_REQUEST['login']);
} else {
  $login = "";
}


if (isset($_REQUEST) && isset($_REQUEST['password'])) {
  $password = addslashes($_REQUEST['password']);
} else {
  $password = "";
}

$html = new Html();

$db = new Db();
if ($config->userdbname) {
  $dbauth = new DbAuth();
} else {
  $dbauth = $db;
}


if (isset($_REQUEST)) {
  if (isset($_REQUEST['language'])) {
    $lang = $_REQUEST['language'];
  } else {
    $lang = $config->defaultlanguage;
  }
} else {
  if (isset($language)) {
    $lang = $language;
  } else {
    $lang = $config->defaultlanguage;
  }
}
$lang = substr($lang, 0, 2);
if (!is_file('includes/strings/' . $lang. '/strings.php'))
  $lang = 'en';
include 'includes/strings/' . $lang . '/strings.php';
$html->initialize();
$user = new User($login, $password, $idsession);
if ($config->usergroups) {
  include 'libs/group.php';
  $group = new Group($user->userid);
  if ($config->userlogs) {
    include 'libs/userlogs.php';
    $userlogs = new UserLogs($group->groupid, $user->userid);
  }
}
$lang = $config->defaultlanguage;
// overwrite default strings
if (isset($user->lang)) {
  $lang = $user->lang;
} else {
  if (isset($_REQUEST)) {
    if (isset($_REQUEST['language'])) {
      $lang = $_REQUEST['language'];
    } else {
      $lang = $config->defaultlanguage;
    }
  } else {
    if (isset($language)) {
      $lang = $language;
    }
  }
}

$lang = substr($lang, 0, 2);
// verify if language exists ! 
if (!is_file('includes/strings/' . $lang . '/strings.php')) {
  $lang = $config->defaultlanguage;
}

include 'includes/strings/' . $lang . '/strings.php';

// reinitialize with definitive right language
$html->initialize();

print $html->header($l['str_dig_zone_title']);

if (empty($idsession)) {
  $idsession = $user->idsession;
}

if ((isset($_REQUEST['logout']) && $_REQUEST['logout'])
    || (isset($logout) && $logout)) {
  $user->logout($idsession);
}

$link = "?language=" . $lang;
if (!empty($idsession)) {
  $link .= "&amp;idsession=" . $idsession;
}

if ($user->error) {
  printf($html->string_error,$user->error);
}

if ($user->authenticated == 1) {
  if (isset($_REQUEST)) {
    $zonename = $_REQUEST['zonename'];
    $zonetype = $_REQUEST['zonetype'];
    $server = $_REQUEST['server'];
  }
  $zone = new Zone($zonename,$zonetype);
  if ($zone->error) {
    printf($html->string_error,$zone->error);
  } else {
    if ($zone->RetrieveUser() != $user->userid &&
        ($config->usergroups && $zone->RetrieveUser() != $group->groupid)) {
      printf($html->string_error, $l['str_you_dont_own_this_zone']);
    } else {
      $title = sprintf($l['str_zone_content_for_x_on_server_x'],
          $zone->zonename,htmlspecialchars($server));
      $content = sprintf("\n<pre>%s</pre>",
          htmlspecialchars(zoneDig($server, $zonename)));
      print $html->box('digwindow', $title, $content);
    }
  }
}

// print close "window"

print $html->footerlight();
?>
