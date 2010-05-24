<?
$mailbody = '
' . $l['str_email_this_is_an_automatic_email'] . '

' . sprintf($l['str_email_password_recovery_for_account_on_x'],$config->sitename) . '

' . $l['str_email_go_on_followin_page_to_recover'] . '

' . $config->mainurl . 'password.php?id=' . $id . '&language=' . $lang . '

-- 
' . $config->emailsignature . '
';
?>
