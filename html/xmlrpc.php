<?php

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Eric van der Vlist <vdv@dyomedea.com>

	This file implements a XML RPC service giving
	update access to A records.

	It relies on Edd Dumbill's XML RPC implementation
	available at: http://phpxmlrpc.sourceforge.net/
	and has been tested with version 2.2.2

*/

/* they come directly from phpxmlrpc lib/ dir */
include("xmlrpc.inc");
include("xmlrpcs.inc");

require("libs/xname.php");

$config = new Config();

// main content

$updateArecord_sig=array(array($xmlrpcStruct, $xmlrpcStruct));
$updateArecord_doc="Updates a A record from a primary zone.";
// *******************************************************
	
	//	Function updateArecord()
	/**
	 * XML-RPC service to update A records
	 *
	 *@access public
	 *@params XML-RPC message $m
	 *@return XML-RPC answer
	 *
	 *The request for this service is a structure containing:
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
	 */

Function updateArecord($m) {

  global $xmlrpcerruser, $stateNames;
	global $db, $dbauth, $user, $config;
	$res = "";
	$modified = 0;
	$req = php_xmlrpc_decode($m->getParam(0));
	$db = new Db();
	if($config->userdbname){
        $dbauth = new DbAuth();
	}else{
        	$dbauth = $db;
	}

	$user = new User($req["user"],$req["password"], NULL);
	if ($user->authenticated==0) {
		$user = new User($req["user"],$req["password"], NULL, 1);
	}
	if ($user->authenticated==0) {
		return new xmlrpcresp(0, $xmlrpcerruser, "authentication refused");
	}
	elseif ($user->authenticated==2) {
		return new xmlrpcresp(0, $xmlrpcerruser, "you have to migrate first");
	}
	$zonename = $req["zone"];
	$zonetype = "P";
	$zone = new Zone($zonename,$zonetype);
	if($zone->error){
		return new xmlrpcresp(0, $xmlrpcerruser, $zone->error);
	}
	if($config->usergroups){
		include 'libs/group.php';
		$group = new Group($user->userid);
		if($config->userlogs){
			include 'libs/userlogs.php';
			$userlogs=new UserLogs($group->groupid,$user->userid);
		}
	}

	if((!$config->usergroups &&
				$zone->RetrieveUser() != $user->userid) ||
				($config->usergroups && 
				$zone->RetrieveUser() != $group->groupid)){
		return new xmlrpcresp(0, $xmlrpcerruser, "You can not manage zone ". $zone->zonename);
	}
	$currentzone = new Primary($zone->zonename, $zone->zonetype, $user);
	if (notnull($req["oldaddress"])) {
		$modified = 1;
		if ($req["oldaddress"] == "*") {
			$currentzone->DeleteMultipleARecords($req["name"]);
		} else {
			$delete = array ( "a(" . mysql_real_escape_string($req["name"]) . "/" . mysql_real_escape_string($req["oldaddress"]).")");
			$currentzone->Delete($delete,0,0);
		}
		if($currentzone->error){
			return new xmlrpcresp(0, $xmlrpcerruser, $currentzone->error);
		}
	}
	$ttl = notnull(intval($req["ttl"])) ? intval($req["ttl"]) : "-1";
	if (notnull($req["newaddress"])) {
		$modified = 1;
			$res = $currentzone->AddARecord($zone->zoneid,
			array(mysql_real_escape_string($req["newaddress"])), array(mysql_real_escape_string($req["name"])),
			array($ttl), NULL);

		if($currentzone->error){
			return new xmlrpcresp(0, $xmlrpcerruser, $res);
		}
	}
	$currentzone->generateConfigFile();
	$checker = "$config->binnamedcheckzone " . $currentzone->zonename . " " .
			$currentzone->tempZoneFile();
	$check = `$checker`;
	unlink($currentzone->tempZoneFile());
	// if ok
	if(! preg_match("/OK/", $check)){
		return new xmlrpcresp(0, $xmlrpcerruser, "Check error: ". $checker);
	}
	if ($modified) {
		$currentzone->flagModified($currentzone->zoneid);
		$currentzone->updateSerial($currentzone->zoneid);
		if($currentzone->error){
			return new xmlrpcresp(0, $xmlrpcerruser, "DB error: ". $currentzone->error);
		}
	}
	$ret = array(
			"zone" => $req["zone"],
			"serial" => $currentzone->serial,
			"name" => $req["name"],
			"addresses" => NULL,
			"ttl" => $ttl
		);
	
	$currentzone->getArecords($ret["addresses"], mysql_real_escape_string($req["name"]));

	if($currentzone->error){
		return new xmlrpcresp(0, $xmlrpcerruser, $currentzone->error);
	}

	return new xmlrpcresp(php_xmlrpc_encode($ret));

}

$s=new xmlrpc_server( array( 
														 "xname.updateArecord" => 
														 array("function" => "updateArecord",
																	 "signature" => $updateArecord_sig,
																	 "docstring" => $updateArecord_doc)
														 
														 ));

?>
