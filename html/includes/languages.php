<?
// print a box with list of available languages

$title = $l['str_languages_title'];

$mylink = "?" . $_SERVER["QUERY_STRING"];
$mylink = preg_replace("/(&amp;|&|)language=[a-z][a-z]/", "", $mylink);
if ($mylink!="?")
   $mylink.="&amp;";
$mylink = preg_replace("/&amp;/", "&", $mylink);
$mylink = preg_replace("/&/", "&amp;", $mylink);
$dirlist = GetDirList("includes/strings");
$content = '';
reset($dirlist);
while($countrycode = array_shift($dirlist)){
	$content .= '<a href="' . $_SERVER['PHP_SELF'] . $mylink .
		'language=' . $countrycode . 
		'"><img src="images/' . $countrycode . '.png" alt="' . $countrycode . '"></a>
		';
}
$content = '<p>'. $content .'</p>';
print $html->box('languages',$title,$content);


?>
