<?
/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

// create a new user
// parameters :
// -void
// - $loginnew,$passwordnew,$confirmpasswordnew, $emailnew

$page_title = "str_create_new_user_title";
// headers 
include 'includes/header.php';


if(isset($_REQUEST) && isset($_REQUEST['loginnew'])){
  $loginnew=$_REQUEST['loginnew'];
}
if(isset($loginnew)){
  $loginnew = addslashes($loginnew);
}

if(isset($_REQUEST) && isset($_REQUEST['passwordnew'])){
  $passwordnew=$_REQUEST['passwordnew'];
}
if(isset($passwordnew)){
  $passwordnew = addslashes($passwordnew);
}

if(isset($_REQUEST) && isset($_REQUEST['confirmpasswordnew'])){
  $confirmpasswordnew=$_REQUEST['confirmpasswordnew'];
}
if(isset($confirmpasswordnew)){
  $confirmpasswordnew = addslashes($confirmpasswordnew);
}

if(isset($_REQUEST) && isset($_REQUEST['email'])){
  $email=$_REQUEST['email'];
}
if(isset($email)){
  $email = addslashes($email);
}

if(isset($_REQUEST) && isset($_REQUEST['newlang'])){
        $newlang=$_REQUEST['newlang'];
}
if(isset($newlang)){
        $newlang = addslashes($newlang);
}

if(file_exists("includes/left_side.php")) {
        include "includes/left_side.php";
}else{
        include "includes/left_side_default.php";
}


if($config->public){
// main content

  $title=$l['str_create_new_user_title'];
  $content = "";
  if(!isset($loginnew)){
    include 'includes/createuser_form.php';
  }else{ // !isset($loginnew)
  // $loginnew is set ==> check & save & mail
    $content = "";
    $localerror = 0;
    $missing = "";
  
    if(!notnull($loginnew)){
      $missing .= " " . $l['str_login'] . ",";
    }
    if(!notnull($passwordnew)){
      $missing .= " " . $l['str_password'] . ",";
    }
    if(!notnull($confirmpasswordnew)){
      $missing .= " " . $l['str_confirm_password'] . ",";
    }
    if(!notnull($email)){
      $missing .= " " . $l['str_email'] . ",";
    }
    if(!notnull($newlang)){
      $missing .= " " . $l['str_language'] . ",";
    }
    if((isset($_REQUEST) && $_REQUEST['ihaveread'] != 1) || (!isset($_REQUEST)
    && $ihaveread != 1)){
      $missing .= " " . $l['str_i_have_read_disclaimer'] . ",";
    }
  
    if(notnull($missing)){
      $localerror = 1;
      $missing = substr($missing,0, -1);
      $content .= sprintf($html->fontred, 
          sprintf($l['str_error_missing_fields'],$missing)
          ) . '<br>';
    }
  
  
    if(!$localerror){
      if(!checkName($loginnew)){
        $localerror = 1;
        $content .= $l['str_bad_login_name'] . '<br>';
      }
    
      if(!checkEmail($email)){
        $localerror = 1;
        $content .= sprintf($html->string_error, $l['str_bad_email_syntax']);
        if (!preg_match('@gmail\.com$', $email))
          $content .= ' ' .$l['str_bad_email_syntax_gmail'];
        $content .= '<br>';
      }else{
        $result = vrfyEmail($email);
        if($result != 1){
          $localerror =1;
          $content .= sprintf($html->string_error,$result) . '<br>';
        }
      }
  
      if($passwordnew != $confirmpasswordnew){
        $localerror = 1;
        $content .= $l['str_passwords_dont_match'] . '<br>';
      }
    } // end no error after empty checks
  


    if(!$localerror){
    // ****************************************
    // *            Create new user           *
    // ****************************************
      $newuser = new User('','','');
      $newuser->userCreate($loginnew,$passwordnew,$email);
      if($newuser->error){
              // error, print form again
        $content .= sprintf($html->string_error,$newuser->error);
        include 'includes/createuser_form.php';
      }else{
      // if advanced interface, save status
      if($config->advancedinterface){
        if((isset($_REQUEST) && $_REQUEST['advanced']) ||
          (!isset($_REQUEST) && $advanced)){
          $newuser->advanced = 1;
        }else{ 
          $newuser->advanced = 0;
        }
      }else{ // end advancedinterface set
        $newuser->advanced = 0;
      }
      
      if($config->ipv6interface){
        if((isset($_REQUEST) && $_REQUEST['ipv6']) ||
          (!isset($_REQUEST) && $ipv6)){
          $newuser->ipv6 = 1;
        }else{ 
          $newuser->ipv6 = 0;
        }
      }else{ // end ipv6interface set
        $newuser->ipv6=0;
      }
      if($config->txtrecords){
        if((isset($_REQUEST) && $_REQUEST['txtrecords']) ||
          (!isset($_REQUEST) && $txtrecords)){
          $newuser->txtrecords = 1;
        }else{ 
          $newuser->txtrecords = 0;
        }
      }else{ // end txtrecords set
        $newuser->txtrecords=0;
      }
      if($config->srvrecords){
        if((isset($_REQUEST) && $_REQUEST['srvrecords']) ||
          (!isset($_REQUEST) && $srvrecords)){
          $newuser->srvrecords = 1;
        }else{ 
          $newuser->srvrecords = 0;
        }
      }else{ // end srvrecords set
        $newuser->srvrecords=0;
      }

      if(isset($_REQUEST) && $_REQUEST['nbrows']){
        $newuser->nbrows = intval($_REQUEST['nbrows']);
      }else{
        if(!isset($_REQUEST) && $nbrows){
          // nothing to be done
        }else{
          $newuser->nbrows = $config->defaultnbrows;
        }
      }      

      $options = "advanced=" . $newuser->advanced . ";ipv6=" . $newuser->ipv6 . ";nbrows=" . $newuser->nbrows . ";grouprights=A;txtrecords=". $newuser->txtrecords .";srvrecords=".$newuser->srvrecords .";";
      $newuser->grouprights='A';
      $newuser->lang=$lang;
      $newuser->options=$options;
      $newuser->changeOptions();

      
      // send email & print message
      // send mail to validate email

      // generate random ID 
      $randomid= $newuser->generateIDEmail();
      
      // send mail
      // print what happened

      include 'includes/createuser_mail.php';

      // insert ID in DB
        if(!$newuser->storeIDEmail($newuser->userid,$email,$randomid)){
          $content .= $newuser->error;
        }else{
      
          if(mailer($config->emailfrom,$email,$config->sitename . " "
          . $l['str_email_validation'],"Content-Type: text/plain; charset=" .
           $l['str_content_type'],$mailbody)){
            include 'includes/createuser_mail_success.php';
          }else{
            include 'includes/createuser_mail_error.php';
          }
      
        }
    
      } // user created successfully
  
    }else{ // error, print form again
      include 'includes/createuser_form.php';
    }
  
  } // end else $loginnew not null

}else{ // end $config->public
  $title=$l['str_uppercase_error'];
  $content=$l['str_not_public_server'];
}
print $html->box('mainbox',$title,$content);



if(file_exists("includes/right_side.php")) {
        include "includes/right_side.php";
}else{
        include "includes/right_side_default.php";
}


print $html->footer();

?>
