<?
			$mailbody = '
' . $l['str_email_this_is_an_automatic_email'] . '

' . sprintf($l['str_email_you_have_created_an_account_on_x'],$config->sitename) . '
' . sprintf($l['str_email_this_email_is_sent_to_validate_email_x'],$email) .'

' . $l['str_email_please_follow_this_link'] . '
' . $config->mainurl . 'validate.php?id=' . $randomid . '&amp;language=' . $lang . '

' . $l['str_email_account_can_not_be_used_without_this_validation'] . '

' . $l['str_email_regards'] . '

-- 
' . $config->emailsignature . '
';

?>
