<?php
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details

  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html

  Author(s): Yann Hirou <hirou@xname.org>
*/

// create a new user
// parameters:
// - void
// - $loginnew, $passwordnew, $confirmpasswordnew, $emailnew

$page_title = 'str_create_new_user_title';  // used in header.php
include 'includes/header.php';

if (isset($_REQUEST) && isset($_REQUEST['loginnew'])) {
  $loginnew = $_REQUEST['loginnew'];
} else {
  $loginnew = "";
}

if (isset($_REQUEST) && isset($_REQUEST['passwordnew'])) {
  $passwordnew = $_REQUEST['passwordnew'];
} else {
  $passwordnew = "";
}

if (isset($_REQUEST) && isset($_REQUEST['confirmpasswordnew'])) {
  $confirmpasswordnew = $_REQUEST['confirmpasswordnew'];
} else {
  $confirmpasswordnew = "";
}

if (isset($_REQUEST) && isset($_REQUEST['email'])) {
  $email = $_REQUEST['email'];
} else {
  $email = "";
}

if (isset($_REQUEST) && isset($_REQUEST['newlang'])) {
  $newlang = $_REQUEST['newlang'];
} else {
  $newlang = "";
}

if (file_exists("includes/left_side.php")) {
  include "includes/left_side.php";
} else {
  include "includes/left_side_default.php";
}


if (!$config->public) {
  $title = $l['str_uppercase_error'];
  $content = $l['str_not_public_server'];
} else {
  $title = $l[$page_title];
  $content = "";
  if (empty($loginnew)) {
    include 'includes/createuser_form.php';
  } else {
    $localerror = 0;
    $missing = array();

    if (empty($loginnew)) {
      $missing[] = $l['str_login'];
    }
    if (empty($passwordnew)) {
      $missing[] = $l['str_password'];
    }
    if (empty($confirmpasswordnew)) {
      $missing[] = $l['str_confirm_password'];
    }
    if (empty($email)) {
      $missing[] = $l['str_email'];
    }
    if (empty($newlang)) {
      $missing[] = $l['str_language'];
    }
    if ((isset($_REQUEST) && $_REQUEST['ihaveread'] != 1)
        || (!isset($_REQUEST) && $ihaveread != 1)) {
      $missing[] = $l['str_i_have_read_disclaimer'];
    }

    if (!empty($missing)) {
      $localerror = 1;
      $content .= sprintf($html->fontred,
          sprintf($l['str_error_missing_fields'], implode(", ", $missing)));
      $content .= '<br>';
    } else {  // all required fields are there, check them
      if (!checkName($loginnew)) {
        $localerror = 1;
        $content .= sprintf($html->string_error, $l['str_bad_login_name']);
        $content .= '<br>';
      }
      if (!checkEmail($email)) {
        $localerror = 1;
        $content .= sprintf($html->string_error, $l['str_bad_email_syntax']);
        $content .= '<br>';
      } else {
        $result = vrfyEmail($email);
        if ($result != 1) {
          $localerror = 1;
          $content .= sprintf($html->string_error, $result) . '<br>';
        }
      }
      if ($passwordnew != $confirmpasswordnew) {
        $localerror = 1;
        $content .= $l['str_passwords_dont_match'] . '<br>';
      }
    }

    if (!$localerror) {
      $newuser = new User('', '', '');
      $newuser->userCreate($loginnew, $passwordnew, $email);
      if ($newuser->error) {
        // error, print form again
        $content .= sprintf($html->string_error, $newuser->error);
        include 'includes/createuser_form.php';
      } else {
        $newuser->advanced = 0;
        if ($config->advancedinterface) {
          if ((isset($_REQUEST) && !empty($_REQUEST['advanced']))
              || (!isset($_REQUEST) && $advanced)) {
            $newuser->advanced = 1;
          }
        }
        $newuser->ipv6 = 0;
        if ($config->ipv6interface) {
          if ((isset($_REQUEST) && !empty($_REQUEST['ipv6']))
              || (!isset($_REQUEST) && $ipv6)) {
            $newuser->ipv6 = 1;
          }
        }
        $newuser->txtrecords = 0;
        if ($config->txtrecords) {
          if ((isset($_REQUEST) && !empty($_REQUEST['txtrecords']))
              || (!isset($_REQUEST) && $txtrecords)) {
            $newuser->txtrecords = 1;
          }
        }
        $newuser->srvrecords = 0;
        if ($config->srvrecords) {
          if ((isset($_REQUEST) && !empty($_REQUEST['srvrecords']))
              || (!isset($_REQUEST) && $srvrecords)) {
            $newuser->srvrecords = 1;
          }
        }
        $newuser->caarecords = 0;
        if ($config->caarecords) {
          if ((isset($_REQUEST) && !empty($_REQUEST['caarecords']))
              || (!isset($_REQUEST) && $caarecords)) {
            $newuser->caarecords = 1;
          }
        }
        $newuser->tlsarecords = 0;
        if ($config->tlsarecords) {
          if ((isset($_REQUEST) && !empty($_REQUEST['tlsarecords']))
              || (!isset($_REQUEST) && $tlsarecords)) {
            $newuser->tlsarecords = 1;
          }
        }
        $newuser->nbrows = $config->defaultnbrows;
        if (isset($_REQUEST) && !empty($_REQUEST['nbrows'])) {
          $newuser->nbrows = intval($_REQUEST['nbrows']);
        }
        $newuser->lang = $config->defaultlanguage;
        $langlist = GetDirList('includes/strings');
        if (in_array($newlang, $langlist, true)) {
            $newuser->lang = $newlang;
        }
        $options = sprintf(
            'advanced=%d;ipv6=%d;nbrows=%d;grouprights=A;txtrecords=%d;srvrecords=%d;caarecords=%d;tlsarecords=%d;',
            $newuser->advanced, $newuser->ipv6, $newuser->nbrows,
            $newuser->txtrecords, $newuser->srvrecords, $newuser->caarecords, $newuser->tlsarecords);
        $newuser->grouprights = 'A';
        $newuser->options = $options;
        $newuser->changeOptions();
        // generate random ID 
        $randomid= $newuser->generateIDEmail();
        // insert ID in DB
        if (!$newuser->storeIDEmail($newuser->userid, $email, $randomid)) {
          $content .= $newuser->error;
        } else {
          include 'includes/createuser_mail.php';  // set $mailbody variable
          $result = mailer(
              $config->emailfrom, $email,
              sprintf('%s %s', $config->sitename, $l['str_email_validation']),
              sprintf('Content-Type: text/plain; charset=%s',  $l['str_content_type']),
              $mailbody);
          if ($result) {
            $content .= sprintf('<p>%s<br>%s</p>',
                 $l['str_email_successfully_sent_explanation1'],
                 $l['str_email_successfully_sent_explanation2']);
          } else {
            $content .= $l['str_email_error_error_occured'];
            $content .= sprintf($l['str_email_error_please_verify_address_x'],$email);
            $content .= sprintf(
                $l['str_in_doubt_you_can_contact_us_at_x'],
                sprintf('<a href="mailto:%s" class="linkcolor">%s</a>',
                    $config->contactemail, $config->contactemail));
          }
        }
      } // user created successfully
    } else { // error, print form again
      include 'includes/createuser_form.php';
    }
  }
}

print $html->box('mainbox', $title, $content);

if (file_exists("includes/right_side.php")) {
  include "includes/right_side.php";
} else {
  include "includes/right_side_default.php";
}

print $html->footer();

?>
