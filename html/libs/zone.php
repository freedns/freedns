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
				VALUES ('".mysql_real_escape_string($zonename)."','".
					mysql_real_escape_string($zonetype)."','".$userid."')";
			$res = $db->query($query);
			if($db->error()){
				$this->error = $l['str_trouble_with_db'];
				return 0;
			}else{
				$this->retrieveID($zonename,$zonetype);
				$this->zonename=mysql_real_escape_string($zonename);
				$this->zonetype=mysql_real_escape_string($zonetype);

				// if $template, fill-in zone with template content
				// modified for current zone
				if ($template && $template != $l['str_none']) {
					// only if template is owned by group !
					if($userid != $this->getUserIdByZone($template)){
						$this->error.= $l['str_while_configuring_from_template'];
					}else{
						if(!$this->fillinWithTemplate($template, $zonetype)){
							$this->error.= $l['str_while_configuring_from_template'];
						}
					}
				}

        if ($zonetype=='P')
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
						
				if (notnull($this->error)) {
					// argh! no rollback for myisam.
					$query = sprintf("DELETE FROM dns_zone WHERE zone='%s' AND zonetype='%s' AND userid='%s';",
						mysql_real_escape_string($zonename), $zonetype, $userid);
					$res = $db->query($query);
					return 0;
				} else {
					// insert creation log
					$query = "INSERT INTO dns_log (zoneid,content,status,serverid)
						VALUES ('" . $this->zoneid .
						"','" . addslashes($l['str_zone_successfully_created']) . 
						"','I','1')";
					$res = $db->query($query);

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
          if ($zonetype=='S' && notnull($serverimport)){
            $query = "INSERT INTO dns_confsecondary
              (zoneid, masters, xfer) VALUES ('" . $this->zoneid . "', '".
              mysql_real_escape_string($serverimport) . "', '".
							mysql_real_escape_string($serverimport) . "')";
						$res2 = $db->query($query);
            if($db->error()){
                $this->error .= $l['str_trouble_with_db'];
                return 0;
            }
          }
          // flag as 'M'odified to be generated & reloaded
          $query = "UPDATE dns_zone SET 
              status='M' WHERE id='" . $this->zoneid . "'";
          $res = $db->query($query);
          if($db->error()){
              $this->error .= $l['str_trouble_with_db'];
              return 0;
          }
          return 1;
				} // notnull($this->error)
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
			WHERE zone='" . mysql_real_escape_string($zone) . "'";
		$res=$db->query($query);
		$line=$db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			return $line[0];
		}
	}


	Function _f($r) {
		if ($r=="NULL") return $r;
		return "'" . mysql_real_escape_string($r) . "'";
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
		$templatetype = $templatetype[0];
		switch($templatetype){
			case 'S':
				$query = "SELECT c.masters,c.xfer FROM dns_confsecondary c, dns_zone z
						WHERE c.zoneid=z.id AND z.zone='" .
						mysql_real_escape_string($templatezone) .
						"' AND z.zonetype='S'";
				$res=$db->query($query);
				$line=$db->fetch_row($res);
				if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					return 0;
				}else{
					$query = sprintf("INSERT INTO dns_confsecondary (zoneid,masters,xfer)
						VALUES('%s', %s, %s)",
						$this->zoneid,
						$this->_f($line[0]),
						$this->_f($line[1]));
					$res = $db->query($query);
					if($db->error()){ 
						$this->error=$l['str_trouble_with_db'];
						return 0;
					}
				}
				break; // case 'S'

			case 'P':
				$query = "SELECT c.refresh,c.retry,c.expiry,c.minimum,c.xfer,c.defaultttl
					FROM dns_confprimary c, dns_zone z
					WHERE c.zoneid=z.id AND z.zone='" . mysql_real_escape_string($templatezone) . "'
					AND z.zonetype='P'";
				$res=$db->query($query);
				$line=$db->fetch_row($res);
				if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					return 0;
				}else{
					$query = sprintf("INSERT INTO dns_confprimary
						(zoneid,refresh,retry,expiry,minimum,xfer,defaultttl,serial)
						VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
						$this->zoneid,
						mysql_real_escape_string($line[0]),
						mysql_real_escape_string($line[1]),
						mysql_real_escape_string($line[2]),
						mysql_real_escape_string($line[3]),
						mysql_real_escape_string($line[4]),
						mysql_real_escape_string($line[5]),
						getSerial());
					$res = $db->query($query);
					if($db->error()){ 
						$this->error=$l['str_trouble_with_db'];
						return 0;
					}
				}
				// fill in records
				$query = "SELECT r.type,r.val1,r.val2,r.val3,r.val4,r.val5,r.ttl
						FROM dns_record r, dns_zone z
						WHERE r.zoneid=z.id AND z.zone='" . mysql_real_escape_string($templatezone) . "'
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
							case "WWW":
								$line[2] = ereg_replace($templatezone."/", $this->zonename."/", $line[2]);
							case "A":
							case "AAAA":
							case "TXT":
								$line[1] = ereg_replace($templatezone."\.\$", $this->zonename.".", $line[1]);
							case "NS":
							case "MX":
							case "CNAME":
							case "SUBNS":
							case "PTR":
							case "SRV":
								break;
						} // end switch
						$query = sprintf("INSERT INTO dns_record
							(zoneid,type,val1,val2,val3,val4,val5,ttl)
							VALUES ('%s', '%s', %s, %s, %s, %s, %s, %s)",
							$this->zoneid,
							$line[0],
							$this->_f($line[1]),
							$this->_f($line[2]),
							$this->_f($line[3]),
							$this->_f($line[4]),
							$this->_f($line[5]),
							$this->_f($line[6]));
						$db->query($query);				
						if($db->error()){
							$this->error =$l['str_trouble_with_db'];
							return 0;
						}
					} // end while 
				} // end no DB error
				break; // case 'P'

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
		$dig = zoneDig($server,$this->zonename);
		return $this->parseZoneInput($dig);
	}

	Function parseZoneInput($dig){
		global $db,$l;
		$this->error = "";
		$first = 1;
		$dbqueries = 0;

		$diglist = explode("\n",$dig);
		foreach($diglist as $line){
			$query = "";
			if(!preg_match("/^\s*;/",$line)){
				if(preg_match("/^\s*?(.*?)\s+(.*?)\s+IN\s+(.*?)\s+(.*)\s*$/",$line,$record)){
					$data=preg_split("/\s+/",$record[4]);
					$shortname = preg_replace("/\./", "\\.", $this->zonename);
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
									$query = sprintf("INSERT INTO dns_confprimary
										(zoneid,serial,refresh,retry,expiry,minimum,xfer,defaultttl)
										VALUES('%s', '%s', '%s', '%s', '%s', '%s', 'any', '86400')",
										$this->zoneid,
										mysql_real_escape_string(intval($soa[1])),
										mysql_real_escape_string(intval($soa[2])),
										mysql_real_escape_string(intval($soa[3])),
										mysql_real_escape_string(intval($soa[4])),
										mysql_real_escape_string(intval($soa[5]))
										);
								} // SOA params match
							}
							break;
						case "NS":
							// if NS on zone, create NS. Otherwise, create subns.
							if(!strcmp($this->zonename . ".", $record[1])){
								$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,ttl)
										VALUES ('%s', 'NS', '%s', '%s')", $this->zoneid,
										mysql_real_escape_string($data[0]),
										mysql_real_escape_string($record[2]));
							}else{
							// subns
								$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,ttl)
										VALUES ('%s', 'SUBNS', '%s', '%s', '%s')", $this->zoneid,
										mysql_real_escape_string($shortname),
										mysql_real_escape_string($data[0]),
										mysql_real_escape_string($record[2]));
							}
							break;
						case "MX":
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'MX', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($data[1]),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($record[2]));
							break;
						case "A":
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'A', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($record[2]));
							break;
						case "AAAA":
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'AAAA', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($record[2]));
							break;
						case "CNAME":
							if(preg_match("/^(.*)." . $this->zonename . ".$/",$data[0],$tmp)){
								$data[0]=$tmp[1];
							}
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'CNAME', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($record[2]));
							break;
						case "PTR":
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'PTR', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($record[2]));
							break;
						case "SRV":
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,val3,val4,val5,ttl)
									VALUES ('%s', 'SRV', '%s', '%s', '%s', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($data[0]),
									mysql_real_escape_string($data[1]),
									mysql_real_escape_string($data[2]),
									mysql_real_escape_string($data[3]),
									mysql_real_escape_string($record[2]));
							break;
						case "TXT":
							$txt = mysql_real_escape_string(preg_replace("/^\"(.*)\"$/", "\"$1\"", $record[4]));
							$query = sprintf("INSERT INTO dns_record (zoneid,type,val1,val2,ttl)
									VALUES ('%s', 'TXT', '%s', '%s', '%s')", $this->zoneid,
									mysql_real_escape_string($shortname),
									mysql_real_escape_string($txt),
									mysql_real_escape_string($record[2]));
							break;
						default:
							print "<p><span class=\"error\">" . $l['str_log_unknown'] . "</span>" .
				  "<br>\n" . $line . "\n</p>";
					}
					if(notnull($query)){
						$dbqueries++;
						$db->query($query);
						if($db->error()){
							$this->error = $l['str_trouble_with_db'];
							return 0;
						}
					}
				} // standard line 
			} // not ";" beginning line
		} // end foreach line of dig result

    $query = "UPDATE dns_record SET ttl='-1' WHERE ttl='86400' AND zoneid='".$this->zoneid."';";
    $db->query($query);
    if (!$dbqueries) $this->error .= '<pre>' . $dig . '</pre>';
		return $dbqueries;
	}
}
?>
