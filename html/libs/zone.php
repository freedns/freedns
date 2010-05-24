<?

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// WARNING : we suppose that all supplied parameters
// have already been MySQL-escaped


/**
 * Class containing global functions regarding zones
 *
 *@access public
 */
class Zone { 
	var $error;
	var $zonename;
	var $zonetype;
	var $userid;
	var $zoneid;
	
	// instanciation
	// if $zonename or $idsession, match against DB
	// to log in. fill in $authenticated, generate $idsession
	/**
	 * Class constructor
	 *
	 *@param string $zonename name of zone, may be empty
	 *@param string $zonetype type of zone ('P'rimary or 'S'econdary)
	 *@param int $zoneid id of zone in DB
	 */
	Function Zone($zonename,$zonetype,$zoneid=0){
		global $l;
		$this->error="";

		if(notnull($zoneid)){
			$this->zonename=$zonename;
			$this->zonetype=$zonetype[0];
			$this->zoneid=$zoneid;
		}else{
			if(notnull($zonename)){
				if($this->Exists($zonename,$zonetype)){
					$this->zonename=$zonename;
					$this->zonetype=$zonetype;
					$this->retrieveID($zonename,$zonetype);
				}else{ // does not exist
					if(notnull($this->error)){
						return 0;
					}else{
						// No authentication
						$this->error=$l['str_bad_zone_name'];
						return 0;
					}
				}
			}
		}
	}
	
// Function Exists($zonename,$zonetype)
// 		try to authenticate against primary or secondary
// 		internal use only
	/**
	 * Check if zone already exists
	 *
	 *@access private
	 *@param string $zonename name of zone
	 *@param string $zonetype type of zone ('P'rimary or 'S'econdary)
	 *@return int 1 if true, 0 if false or error 
	 */
	Function Exists($zonename,$zonetype){
		global $db,$l;
		$this->error="";
		$zonename = strtolower($zonename);
		$zonename = mysql_real_escape_string($zonename);

// because XName has only 1 DNS, only primary OR secondary
//		$query = "SELECT count(*) FROM dns_zone
//		WHERE zone='$zonename' AND zonetype='$zonetype'";
		$query = "SELECT id FROM dns_zone
		WHERE LOWER(zone)='$zonename'";
		$res = $db->query($query);
		$line = $db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}

		if(!notnull($line[0])){
			return 0;
		}else{
			return 1;
		}
	}
	
//	Function subExists($zonename,$userid)
// check if zone is sub-zone of an existing one
// or if there is already a sub zone of this one

	/**
	 * Check if part of current zone is already registered
	 *
	 * Check if the current zone is a sub-zone of an
	 * existing one, or if a subzone of this one is
	 * already registered.
	 *
	 *@access private
	 *@param string $zonename name of zone
	 *@param int $userid user ID
	 *@return array list of zones having links with this one, or 0 if error
	 */
	Function subExists($zonename,$userid){
		global $db,$l;
		global $config;
		$this->error="";
		$zonename = strtolower($zonename);
		// sub zone of an existing one ?
		$upper = split('\.',$zonename);
		reset($upper);
		$tocompare = "";
		$list = array();
		if ($config->allowsubzones == 2)
			return $list;
		while($tld = array_pop($upper)){
			if($tocompare == ""){
				$tocompare = $tld;
			}else{
				$tocompare = $tld . "." . $tocompare;
			}
			$query = "SELECT LOWER(zone) from dns_zone WHERE 
			zone='" . mysql_real_escape_string($tocompare) . "' AND userid!='" . $userid . "'";
			if ($config->allowsubzones == 1)
				$query .= " AND zonetype = 'P'";
			$res = $db->query($query);
			if($db->error()){
				$this->error=$l['str_trouble_with_db'];
				return 0;
			}
			while($line = $db->fetch_row($res)){
				array_push($list,$line[0]);			
			}
		}
		
		// already a sub zone of this one ?
		$query = "SELECT LOWER(zone) FROM dns_zone WHERE
		zone like '%." . $zonename . "' AND userid!='" . $userid . "'";
		if ($config->allowsubzones == 1)
			$query .= " AND zonetype = 'P'";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}
		while($line = $db->fetch_row($res)){
			array_push($list,$line[0]);			
		}

		return $list;
	}

//	Function retrieveID($zonename,$zonetype)
	/**
	 * Retrieve ID of current zone in $this->zoneid
	 *
	 *@access public
	 *@param string $zonename name of zone
	 *@param string $zonetype type of zone ('P'rimary or 'S'econdary)
	 *@return int 0 if error or no such zone, 1 if ID found
	 */
	Function retrieveID($zonename,$zonetype){
		global $db,$l;
		$this->error="";
		$zonename = mysql_real_escape_string($zonename);
		$zonetype = mysql_real_escape_string($zonetype);
		$query = "SELECT id FROM dns_zone WHERE 
		zone='" . $zonename . "' AND zonetype='" . $zonetype . "'";
		$res = $db->query($query);
		$line = $db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}

		if($line[0] == 0){
			return 0;
		}else{
			$this->zoneid = $line[0];
			return 1;
		}
	}

// Function zoneCreate($zonename,$zonetype,$template,$serverimport,$userid,$zonearea)
	/**
	 * Insert new zone in dns_zone table 
	 *
	 *@access public
	 *@param string $zonename zone name
	 *@param string $zonetype zone type ('P'rimary or 'S'econdary)
	 *@param string $template zone to be used as template
	 *@param string $serverimport server to be used to import zone
	 *@param int $userid user ID
	 *@return int 1 if success, 0 if trouble
	 */
	Function zoneCreate($zonename,$zonetype,$template,$serverimport,$userid,$zonearea){
		global $db,$l;
		global $config;
		$this->error="";
		$zonename = strtolower($zonename);
		// check if already exists or not
		if(!$this->Exists($zonename,$zonetype)){
			// does not exist already ==> OK
			$query = "INSERT INTO dns_zone (zone,zonetype,userid)
			VALUES ('".mysql_real_escape_string($zonename)."','".$zonetype."','".$userid."')";
			$res = $db->query($query);
			if($db->error()){
				$this->error = $l['str_trouble_with_db'];
				return 0;
			}else{
				$this->retrieveID($zonename,$zonetype);
				// insert creation log
				$query = "INSERT INTO dns_log (zoneid,content,status,serverid)
						VALUES ('" . $this->zoneid .
						"','" . addslashes($l['str_zone_successfully_created']) . 
						"','I','1')";
				$res = $db->query($query);

				$this->zonename=mysql_real_escape_string($zonename);
				$this->zonetype=mysql_real_escape_string($zonetype);

				// if $template, fill-in zone with template content
				// modified for current zone
				if ($template != $l['str_none']) {
					// only if template is owned by group !
					// $template is "xname.org(P)"
					$templatezone = mysql_real_escape_string(substr($template,0,strlen($template)-3));
					$templatetype = mysql_real_escape_string(substr($template,-2,1));
					if($userid != $this->getUserIdByZone($templatezone)){
						$this->error.= $l['str_while_configuring_from_template'];
					}else{
						if(!$this->fillinWithTemplate($templatezone, $templatetype)){
							$this->error.= $l['str_while_configuring_from_template'];
						}
					}
				}

				// if $serverimport, fill-in zone with dig result
				if ($serverimport) {
					if(!$this->fillinWithImport($serverimport)){
						$this->error .= $l['str_failed_serverimport'];
					}
				}
if (0):
				if ($zonearea) {
					if(!$this->parseZoneInput($zonearea)){
						$this->error .= $l['str_failed_serverimport'];
					}
				}
endif;

				// insert in dns_zonetoserver
				// if multiserver, insert for others
				// restrictions on servers should be written here
						
				$query = "SELECT id,servername FROM dns_server";
				$res = $db->query($query);
				$serveridlist=array();
				$servernamelist=array();
				while($line = $db->fetch_row($res)){
					array_push($serveridlist,$line[0]);
					array_push($servernamelist,$line[1]);
				}
				while($serverid=array_shift($serveridlist)){
					$query = "INSERT INTO dns_zonetoserver
								(zoneid,serverid) 
							 VALUES ('" . $this->zoneid . "','" . 
							 	$serverid . "')";
					$res2 = $db->query($query);
				}
				if ($zonetype=='P'){
					$query = "INSERT INTO dns_confprimary 
							(zoneid, serial, refresh, retry, expiry, minimum, defaultttl, xfer)
							 VALUES ('" . $this->zoneid . "','" . getSerial() . "',
							'10800', '3600', '604800', '10800', '86400', 'any')";
					$res2 = $db->query($query);
					while($servername=array_shift($servernamelist)){
						$query = "INSERT INTO dns_record
								(zoneid,type,val1) 
							VALUES ('". $this->zoneid . "', 'NS', '" . $servername .".')";
						$res2 = $db->query($query);
					}
				}
				return 1;
			}
		}else{
			// check if zone status is D or not
			$query = "SELECT status FROM dns_zone WHERE zone='" . mysql_real_escape_string($zonename) . "'
			AND zonetype='" . $zonetype . "'";
			$res = $db->query($query);
			$line = $db->fetch_row($res);
			if($line[0] == 'D'){
				$this->error=$l['str_zone_exists_in_deletion_status'];
			}else{
				$this->error=$l['str_zone_already_exists'];
			}
			return 0;
		}	
	}



// Function zoneDelete()
// 		delete primary or secondary
// 		internal use only
	/**
	 * Delete current zone and records from all tables
	 *
	 *@access public
	 *@return int 1 if success, 0 if trouble
	 */
	Function zoneDelete(){
		global $db,$l;
		$this->error="";
		// Delete from :
		// dns_zone, dns_conf*, dns_record,
		// dns_recovery
		$todelete = array('dns_record','dns_log');
		if($this->zonetype == 'P'){
			array_push($todelete, 'dns_confprimary');
		}else{
			array_push($todelete, 'dns_confsecondary');
		}
		reset($todelete);
		
		while($item = array_pop($todelete)){
			$query = "DELETE FROM " . $item . " WHERE 
			zoneid='" . $this->zoneid . "'";
			$db->query($query);
			if($db->error()){
				$this->error = $l['str_trouble_with_db'];
				return 0;
			}
		}
		$query = "UPDATE dns_zone SET status='D' WHERE 
			id='" . $this->zoneid . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}
		return 1;
	}


// Function zoneLogs($class1,$class2)
// 		print logs regarding zone
// 		in a table, with class $class
// 		alternate color between lines - done with class1 & class2
	/**
	 * Return HTML table with all logs regarding zone
	 *
	 * Lines are alternatively colored using class parameter
	 * for <tr> and <td>. classes have to be defined in CSS file,
	 * as <classname>INFORMATION,<classname>WARNING,<classname>ERROR
	 *
 	 *@access public
	 *@param string $class1 <classname> 
	 *@param string $class2 <alternateclassname>
	 *@return string html code of rows
	 */
	Function zoneLogs($class1,$class2){
		global $db,$l;
		$this->error="";
		$result = "";
		$query = "SELECT count(*) FROM dns_log WHERE zoneid='" .
		$this->zoneid . "'";
		$res=$db->query($query);
		$line=$db->fetch_row($res);
		if($line[0] == 0){
			return $l['str_no_logs_available_for_this_zone'];		
		}
		$query = "SELECT date,content,status,s.servername 
		FROM dns_log l, dns_server s WHERE s.id=l.serverid AND zoneid='" . $this->zoneid . "' ORDER BY date DESC";
		$res=$db->query($query);
		$class=$class2;
		while($line=$db->fetch_row($res)){
			if($db->error()){
				$this->error = $l['str_trouble_with_db'];
				return 0;
			}else{
				if($class==$class2){
					$class=$class1;
				}else{
					$class=$class2;
				}
				$classadd = "INFORMATION";
				if($line[2] == 'E'){
					$classadd="ERROR";
				}else{
					if($line[2] == 'W'){
						$classadd="WARNING";
					}
				}
				// remove seconds
				$timestamp = preg_replace("/(.*):\d\d$/", "$1", $line[0]);
				
				$result .= '<tr class="' . $class . '"><td class="' . $class . '">' . 
				$timestamp .
				'</td><td class="' . $class . '">' .
				$line[3] . '</td><td class="' . $class . '">' . $line[1] . '</td><td class="' . $class . $classadd . 
				'" align="center">&nbsp;' . $line[2] . "&nbsp;</td></tr>\n";
			}
		}
		return $result;
	}

// Function zoneLogsDelete()
	/**
	 * Delete all logs for current zone, and insert a "deleted" line in logs
	 * to avoid empty logs
	 *
	 *@access public
	 *@return int 1 if success, 0 on error
	 */
	 Function zoneLogDelete(){
	 	global $db,$l;

		$this->error="";
		$query = "DELETE from dns_log WHERE zoneid='" . $this->zoneid . "'";
		$res=$db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}else{
			$query = "INSERT INTO dns_log (zoneid,content,status,serverid)
					VALUES ('" . $this->zoneid . "','" . 
						addslashes($l['str_zone_logs_purged']) . "','I','1')";
			$res = $db->query($query);
			return 1;
		}
	}	

//	Function zoneStatus()
// 		Returns global status of zone : I,W,E or U(nknown)
	/**
	 * Returns status of zone: I(nformation), W(arning), E(rror), or U(nknown)
	 *
	 *@access public
	 *@return string I W E or U or 0 if trouble
	 */
	Function zoneStatus(){
		global $db,$l;
		$this->error="";
		$i=0;
		$query = "SELECT status FROM 
		dns_log WHERE zoneid='" . $this->zoneid . "' 
		GROUP BY status";
		$res=$db->query($query);
		while($line=$db->fetch_row($res)){
			if($db->error()){
				$this->error = $l['str_trouble_with_db'];
				return 0;
			}else{
				switch($line[0]) {
					case 'E':
						return 'E';
						break;
					case 'W':
						return 'W';
						break;
					default:
						$i=1;
				}
			}
		}
		if($i){
			return 'I';
		}else{
			return 'U';
		}
	}
	
	
	
//	Function RetrieveUser()
	/**
	 * Retrieve user ID of zone owner
	 *
	 *@access public
	 *@return int user ID or 0 if trouble
	 */
	Function RetrieveUser(){
		global $db,$l;
		$this->error="";
		if($this->userid != 0){
			return $this->userid;
		}
		$query = "SELECT userid FROM dns_zone 
		WHERE id='" . $this->zoneid . "'";
		$res=$db->query($query);
		$line=$db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$this->userid=$line[0];
			return $this->userid;
		}		
	}
	Function getUserIdByZone($zone){
		global $db,$l;
		$this->error="";
		if(empty($zone)) {
			return 0;
		}
		$query = "SELECT userid FROM dns_zone 
			WHERE zone='" . $zone . "'";
		$res=$db->query($query);
		$line=$db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			return $line[0];
		}
	}



//	Function fillinWithTemplate($templatezone, $templatetype)
	/**
	 * Fill in new zone with template content
	 *
	 *@access private
	 *@param string $template zone template to be used
	 *@return int 1 if success, 0 if error
	 */
	Function fillinWithTemplate($templatezone, $templatetype){
		global $db,$l;
		$this->error="";
		switch($templatetype){
			case 'S':
				$query = "SELECT c.masters,c.xfer FROM dns_confsecondary c, dns_zone z
						WHERE c.zoneid=z.id AND z.zone='" . $templatezone . "'
						AND z.zonetype='S'";
				$res=$db->query($query);
				$line=$db->fetch_row($res);
				if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					return 0;
				}else{
					$query = "INSERT INTO dns_confsecondary (zoneid,masters,xfer)
						 VALUES('" . $this->zoneid . "','" . $line[0]. "','" . $line[1]. "')";
					$res = $db->query($query);
					if($db->error()){ 
						$this->error=$l['str_trouble_with_db'];
						return 0;
					}
				}

				break;
			case 'P':
				$query = "SELECT c.refresh,c.retry,c.expiry,c.minimum,c.xfer,c.defaultttl
							FROM dns_confprimary c, dns_zone z
						WHERE c.zoneid=z.id AND z.zone='" . $templatezone . "'
						AND z.zonetype='P'";
				$res=$db->query($query);
	   		$line=$db->fetch_row($res);
		if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					return 0;
				}else{
				$query = "INSERT INTO dns_confprimary
					(zoneid,refresh,retry,expiry,minimum,xfer,defaultttl,serial)
					 VALUES('" . $this->zoneid . "','" . $line[0]. "','" . $line[1]. "','".
								 			$line[2] ."','". $line[3] ."','".$line[4] . "','" . 
											$line[5] ."','" . getSerial("") . "')";
					$res = $db->query($query);
					if($db->error()){ 
						$this->error=$l['str_trouble_with_db'];
						return 0;
					}
				}
				// fill in records
				$query = "SELECT r.type,r.val1,r.val2,r.val3,r.val4,r.val5,r.ttl FROM dns_record r, dns_zone z
						WHERE r.zoneid=z.id AND z.zone='" . $templatezone . "'
						AND z.zonetype='P'";
				$res=$db->query($query);
		if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					return 0;
				}else{
					$listofrecords = array();
					while($line=$db->fetch_row($res)){
						array_push($listofrecords,$line);
					}
					
					while($line=array_pop($listofrecords)){
						// NS - simple copy
						// MX - simple copy
						// A - if domain name itself, substitute
						// AAAA - if domain name itself, substitute
						// WWW - if domain name itself, substitute
						// CNAME - simple copy
						// sub zones - simple copy
						
						switch($line[0]){
							case "NS":
								$query = "INSERT INTO dns_record (zoneid,type,val1,ttl)
											VALUES ('" . $this->zoneid."','NS','" . $line[1] . "','" 
												. $line[6] . "')";
								$db->query($query);			
								break;
							case "MX":
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','MX','" . $line[1] . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);			
								break;
							case "A":
								$pattern = "^" . $templatezone . ".\$";
								if(ereg($pattern,$line[1])){
									$val1 = $this->zonename . ".";
								}else{
									$val1 = $line[1];
								}
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','A','" . $val1 . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);											
								break;
							case "AAAA":
								$pattern = "^" . $templatezone . ".\$";
								if(ereg($pattern,$line[1])){
									$val1 = $this->zonename . ".";
								}else{
									$val1 = $line[1];
								}
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','AAAA','" . $val1 . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);											
								break;
							case "WWW":
								$pattern = "^" . $templatezone . ".\$";
								if(ereg($pattern,$line[1])){
									$val1 = $this->zonename . ".";
								}else{
									$val1 = $line[1];
								}
								$pattern = $templatezone . "/";
								if(ereg($pattern,$line[2])){
									$val2 = ereg_replace($templatezone, $this->zonename, $line[2]);
								}else{
									$val2 = $line[2];
								}
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl,val3,val4)
											VALUES ('" . $this->zoneid."','WWW','$val1', '" 
												. $val2 . "','" . $line[6] . "','".$line[3]."','".$line[4]."')";
								$db->query($query);											
								break;
							case "CNAME":
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','CNAME','" . $line[1] . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);				
								break;
							case "SUBNS":
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','SUBNS','" . $line[1] . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);				
								break;
							case "PTR":
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
											VALUES ('" . $this->zoneid."','PTR','" . $line[1] . "','" 
												. $line[2] . "','" . $line[6] . "')";
								$db->query($query);				
								break;
							case "SRV":
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,val3,val4,val5,ttl)
											VALUES ('" . $this->zoneid."','PTR','" . $line[1] . "','" 
												. $line[2] . "','" . $line[3] . "','"
												. $line[4] . "','" . $line[5] . "','"
												. $line[6] . "')";
								$db->query($query);				
								break;
						} // end switch
						if($db->error()){
							$this->error =$l['str_trouble_with_db'];
							return 0;
						}
					} // end while 
					// flag as 'M'odified to be generated & reloaded
					$query = "UPDATE dns_zone SET 
						status='M' WHERE id='" . $this->zoneid . "'";
					$res = $db->query($query);
					if($db->error()){
						$this->error = $l['str_trouble_with_db'];
						return 0;
					}

				} // end no DB error
				
				break;
			default:
				break;
		}
		return 1;
	}

//	Function fillinWithImport($server)
	/**
	 * Fill in new zone with zonecontent from $server
	 *
	 *@access private
	 *@param string $server server to be used to dig zone content
	 *@return int 1 if success, 0 if error
	 */
	Function fillinWithImport($server){
		global $db,$l;
		$this->error="";
		$first = 1;
		$dig = zoneDig($server,$this->zonename);

		// split into lines
		$diglist = explode("\n",$dig);
		foreach($diglist as $line){
			$query = "";
			if(!preg_match("/^\s*;/",$line)){
				if(preg_match("/^\s*?(.*?)\s+(.*?)\s+IN\s+(.*?)\s+(.*)\s*$/",$line,$record)){
					$data=preg_split("/\s+/",$record[4]);
					// first turn dots into escaped dots, so next regex matches literal dot
					$shortname = preg_replace("/\./", "\\.", $this->zonename);
					// now remove zonename from the end of fqdn 
					$shortname = preg_replace("/\.".$shortname."\.$/", "", $record[1]);
					switch($record[3]){
						case "SOA":
							if($first){
								$first=0;
								// split SOA params
								if(preg_match("/([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s*$/",
									$record[4],$soa)){
									/* serial $soa[1]
									   refresh $soa[2];
									   retry $soa[3];
									   expire $soa[4];
									   negative caching $soa[5];
									*/
									$query = "INSERT INTO dns_confprimary
										(zoneid,refresh,retry,expiry,minimum,xfer,defaultttl,serial)
										VALUES('" . $this->zoneid . "','" . $soa[2] . "','" .
										$soa[3] . "','" . $soa[4] . "','" . $soa[5] . 
										"','any','86400','" . $soa[1] . "')";
								} // SOA params match
							}
							break;
						case "NS":
							// if NS on zone, create NS. Otherwise, create subns.
							if(!strcmp($this->zonename . ".", $record[1])){
								$query = "INSERT INTO dns_record (zoneid,type,val1,ttl)
									VALUES ('" . $this->zoneid . "','NS','" . $data[0] . 
									"','" . $record[2] . "')";
							}else{
							// subns
								$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
					       					VALUES ('" . $this->zoneid . "','SUBNS','" .
										$shortname . "','" .
										$data[0] . "','" . $record[2] . "')";
							}
							break;
						case "MX":
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES('" . $this->zoneid . "','MX','" .
									$data[1] . "','" . $data[0] . "','" . $record[2] . "')";
							break;
						case "A":
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
								VALUES('" . $this->zoneid . "','A','" .  $shortname . "','" .
									$data[0] . "','" . $record[2] . "')";
							break;
						case "AAAA":
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES('" . $this->zoneid . "','AAAA','" .
									$shortname . "','" .
									$data[0] . "','" . $record[2] . "')";
							break;
						case "CNAME":
							if(preg_match("/^(.*)." . $this->zonename . ".$/",$data[0],$tmp)){
								$data[0]=$tmp[1];
							}
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES('" . $this->zoneid . "','CNAME','" .
									$shortname . "','" .
									$data[0] . "','" . $record[2] . "')";
							break;
						case "PTR":
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES('" . $this->zoneid . "','AAAA','" .
									$shortname . "','" .
									$data[0] . "','" . $record[2] . "')";
							break;
						case "SRV":
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,val3,val4,val5,ttl)
									VALUES('" . $this->zoneid . "','SRV','" .
									$shortname . "','" . $data[0] . "','" . $data[1] . "','"
									 . $data[2] . "','" . $data[3] . "','" . $record[2] . "')";
							break;
						case "TXT":
							$txt = mysql_real_escape_string(preg_replace('/"/', '', $record[4]));
							$query = "INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES('" . $this->zoneid . "','TXT','" .
									$shortname . "','\"" . $txt . "\"','" . $record[2] . "')";
							break;
						default:
							print "<p><span class=\"error\">" . $l['str_log_unknown'] . "</span>" .
				  "<br>\n" . $line . "\n</p>";
					}
					if(notnull($query)){
						$db->query($query);
						if($db->error()){
							$this->error = $l['str_trouble_with_db'];
							return 0;
						}
					}
				} // standard line 
			} // not ";" beginning line
		} // end foreach line of dig result

		// flag as 'M'odified to be generated & reloaded
		$query = "UPDATE dns_zone SET 
			status='M' WHERE id='" . $this->zoneid . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}
		return 1;
	}
}
?>
