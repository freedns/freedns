<?
// print a box with list of available languages

$title = $l['str_languages_title'];

$mylink = "?" . $_SERVER["QUERY_STRING"];
$mylink = preg_replace("/(&amp;|&|)language=[a-z][a-z]/", "", $mylink);
if ($mylink!="?")
   $mylink.="&amp;";
$dirlist = GetDirList("includes/strings");
$content = '<div align="center">';
reset($dirlist);
while($countrycode = array_shift($dirlist)){
	$content .= '<a href="' . $_SERVER['PHP_SELF'] . $mylink .
		'language=' . $countrycode . 
		'"><img border="0" src="images/' . $countrycode . '.png" alt="' . $countrycode . '" /></a>
		';
}
$content .= "</div>";
print $html->box('languages',$title,$content);


?>
