<?
$content .= $l['str_email_error_error_occured'] . '
' . sprintf($l['str_email_error_please_verify_address_x'],$email) .'
' . sprintf($l['str_in_doubt_you_can_contact_us_at_x'],
'<a href="mailto:' . $config->contactemail . '" 
class="linkcolor">' . $config->contactemail . '</a>') . '
';
?>
