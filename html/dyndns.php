<?

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// headers 
include 'includes/header.php';

// zone numbers
include 'includes/currentzones.php';

// faq
//include 'includes/faq.php';

// login & logs
include 'includes/login.php';

// end left column


// ********************************************************
// MODIFY THIS TO CHANGE DESIGN
// ********************************************************
print $html->globaltablemiddle();
// ********************************************************

// main content
$title = $str_dyndns_title;
$content = '

<pre>
         *
         * XML-RPC service to update A records
         *
         * The request for this service is a structure containing:
         *
         * - user: the user name
         * - password: his password
         * - zone: the name of the zone
         * - name: the name of the A record(s)
         * - oldaddress (optional): the address of the A record to
         *                          delete or "*" to delete all A records
         *                          for the given name.
         * - newaddress (optional): the address of the A record to add.
         * - ttl (optional): the TTL of the A record to add.
         *
         * The return value is the whole zone as text.
         *
         * Inserts can be performed by leaving "oldaddress" empty.
         * Deletes can be performed by leaving "newaddress" empty.
         * Updates are performed by giving both old and new addresses.
         *
</pre>
';
#	include('includes/strings/' . $lang . '/dyndns_content.php');


// *************************************
//          END OF CONTENT
// *************************************

print $html->box($title,$content);



// ********************************************************
// MODIFY THIS TO CHANGE DESIGN
// ********************************************************
print $html->globaltableright();
// ********************************************************

// contact 
include 'includes/contact.php';


// ********************************************************
// MODIFY THIS TO CHANGE DESIGN
// ********************************************************
print $html->globaltableend();
// ********************************************************


print $html->footer();

?>
