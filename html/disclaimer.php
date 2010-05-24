<?
/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

require 'libs/xname.php';


$config = new Config();

$html = new Html();

if(isset($_REQUEST)){
	if(isset($_REQUEST['language'])){
		$lang = $_REQUEST['language'];
	}else{
		$lang = $config->defaultlanguage;
	}
}else{
	if(isset($language)){
		$lang=$language;
	}else{
		$lang = $config->defaultlanguage;
	}
}
$lang=substr($lang, 0, 2);
// verify if language exists ! 
if(!is_file('includes/strings/' . $lang .'/strings.php')){
	$lang = $config->defaultlanguage;
}

include 'includes/strings/' . $lang . '/strings.php';

// reinitialize with definitive right language
$html->initialize();
print $html->header($l['str_warranty_and_disclaimer']);

// ********************************************************
// WRITE YOUR OWN DISCLAIMER !
// ********************************************************


print '<table border="0" width="100%" class="top">
<tr class="top"><td class="top"><div align="center">' .
$l['str_warranty_and_disclaimer'] . '</div></td>
</tr></table>';

include 'includes/strings/' . $lang . '/warranty.php';



print '</body></html>';
?>
