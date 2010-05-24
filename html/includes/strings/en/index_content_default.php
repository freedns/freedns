<?
$content = '
		<div class="boxheader">XName Software</div>
		<div class="boxcontent">
		You have successfully (?) installed XName software.<br >
		Be carefull to following points:
		<ul>
		<li> copy libs/config.default into libs/config.php</li>
		<li> Modify all items in libs/config.php</li>
		<li> If you have mysql errors, check that configured user in config.php exists, 
		and that database name is the same as the one modified in sql/creation.sql</li>
		<li> Modify this text - html/includes/strings/en/index_content.php
		(and copy this file into all directories html/includes/strings/*)</li>
		<li> Modify all html/*.php to fit your html design (all currently used design functions are
		taken from libs/html.php, feel free to use your owns !). Class HTML is used only in these files 
		and in includes/*.php</li>
		</ul>
		</div>
	';
?>
