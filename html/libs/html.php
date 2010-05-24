<?
/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/


// Class HTML
// all HTML code has to be here

// WARNING: modify ALL or you will have XName.org site !
/**
 * Class for all design HTML code - DESIGN STUFF ONLY, no real code there
 *
 *@access public
 */
Class Html{
	var $fontred = ' <span class="red">%s</span>';
	var $generic_error = ' <span class="error">%s</span>';
	var $generic_warning = ' <span class="warning">%s</span>';
	var $string_error;
	var $string_warning;
	
	/**
	 * Class constructor
	 *
	 *@access public
	 */
	function Html(){
		return $this;
	}

	function initialize(){
		global $l;
		$this->string_error = sprintf($this->generic_error, $l['str_error'] . ': %s');
		$this->string_warning = sprintf($this->generic_warning, $l['str_warning'] . ': %s');
		return 1;
	}

	
//	function header($title)
//		returns header with $title
	/**
	 * Top of each page
	 *
	 *@access public
	 *@param string $title Title of the page
	 *@return string HTML code
	 */
	function header($title){
	global $config,
		$l;
	Header("Content-Type: text/html; charset=" . $l['str_content_type']);
	$result ='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
   $result ='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>' . $config->sitename . ' - ' . $title . '</title>
	<meta http-equiv="Content-Type" content="text/html; charset=' . $l['str_content_type'] . '">
	<link rel="stylesheet" type="text/css" href="' . $config->cssurl . '">
</head>
<body>
<div id="container">
	';
	
		return $result;
	}
	
//	function subheader($link)
//		returns subheader with $link query-string added to all local URLs
//		(used to pass sessionID)
	/**
	 * Sub-top of each page
	 *
	 *@access public
	 *@param string $link link to be added to all local URLS (used to pass sessionID)
	 *@return string HTML code
	 */
	function subheader($link){
		global $lang,$config;
		global $l;
		$result = '<!-- header -->

<div id="headtitle">
		<div id="header">
			<a href="index.php'.$link.'" class="linkcolor">' .
			$config->sitename . '</a>
		</div>
		<div id="linkline">
		<a href="zones.php'.$link.'" class="linkcolor">' .
		$l['str_html_view_zones'] . '</a> |
		<a href="createzone.php'.$link.'" class="linkcolor">' .
		$l['str_html_create_zone'] . '</a> |
		<a href="modify.php'.$link.'" class="linkcolor">' .
		$l['str_html_modify_zone'] . '</a> |
		<a href="deletezone.php'.$link.'" class="linkcolor">' .
		$l['str_html_delete_zone'] . '</a> |
		<a href="' . $config->mainurl . '" class="linkcolor">' .
		$l['str_html_go_on_xname'] . '!</a>		
		</div>
</div>
		<!-- end header -->
		';
		return $result;
	}
	
//	function footer()
//		returns footer
	/**
	 * global footer
	 *
	 *@access public
	 *@return string HTML code
	 */
	function footer(){
		global $db;	
		$result = '<div id="footer"> SQL:' . $db->totaltime . '</div>
		';
		$result .= $this->footerlight();

		return $result;
	}

//	function footerlight()
//		returns light footer
	/**
	 * global footerlight
	 *
	 *@access public
	 *@return string HTML code
	 */
	function footerlight(){
		$result = "</div></body></html>";
		return $result;
	}
	
//	function box($id,$title,$content)
//		returns designed box with id, title & content
	/**
	 * designed box with id title & content
	 *
	 *@access public
	 *@param string $id id of the box, for CSS layout
	 *@param string $title title of the box, may be HTML
	 *@param string $content content of the box, may be HTML
	 *@return string HTML code
	 */
	function box($id,$title,$content){
		$result = '
		<!-- box beginning "' . $id . '" -->
		<div id="' . $id . '">
			<div id="' . $id . '_title">' 
				. $title . 
			'</div>
			<div id="' . $id . '_content">' 
				. $content . 
			'</div>
		</div>
		<!-- box end "' . $id . '" -->
		';
		return $result;
	}
	
}
?>
