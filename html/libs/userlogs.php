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
 * Class containing global functions for userlogs handling
 *
 *@access public
 */
class Userlogs {
	var $error;
	var $groupid;
	var $userid;
	
	/**
	 * Class constructor
	 *
	 *@param string $dbase database currently used
	 *@param int $groupid id of group
	 *@param int $userid user ID
	 */
	Function userlogs($groupid,$userid){
		$this->error="";
		$this->groupid=$groupid;
		$this->userid=$userid;
	}
	

// Function addLogs($zoneid,$text)	
	/**
	 * Insert logs in DB
	 *
	 *@access public
	 *@param int $zoneid ID of modified zone
	 *@param string $text log content
	 *@return int 1 if success, 0 if error
	 */
	Function addLogs($zoneid,$text){
		global $db,$l;
		$this->error="";
		$query = "INSERT INTO dns_userlog
			(userid,groupid,zoneid,content)
			VALUES ('".$this->userid."','".$this->groupid."','".$zoneid.
			"','".$text."')";
		$res = $db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}else{
			return 1;
		}
	}
	
	
// Function showGroupLogs($category,$order){
	/**
	 * Returns logs for current group
	 *
	 *@access public
	 *@param string $category category for order by
	 *@param string $order order for result - [A]sc or [D]esc	 
	 *@return array list of logs (id/date/userid/zoneid/content)
	 */
	Function showGroupLogs($category,$order){
		global $db,$l;
		$this->error="";
		if($order=='D'){
			$order='DESC';
		}else{
			$order='ASC';
		}
		$query = "SELECT id,date,userid,zoneid,content FROM dns_userlog
		WHERE groupid='" . $this->groupid . "' ORDER BY " . $category . 
		" " . $order;

		$res=$db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$result = array();
			while($line=$db->fetch_row($res)){
				array_push($result, $line);
			}
			return $result;
		}
	}
	


// Function showUserLogs($userid,$order){
	/**
	 * Returns logs for given user
	 *
	 *@access public
	 *@param int $userid User ID to retrieve logs for
	 *@param string $order order for result - [A]sc or [D]esc
	 *@return array list of logs (id/date/zoneid/content)
	 */
	Function showUserLogs($userid,$order){
		global $db,$l;
		$this->error="";
		if($order=='D'){
			$order='DESC';
		}else{
			$order='ASC';
		}
		$query = "SELECT id,date,zoneid,content FROM dns_userlog
		WHERE userid='" . $userid . "' ORDER BY date " . $order;

		$res=$db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$result = array();
			while($line=$db->fetch_row($res)){
				array_push($result, $line);
			}
			return $result;
		}
	}
	

// Function showZoneLogs($zoneid,$order){
	/**
	 * Returns logs for given zone
	 *
	 *@access public
	 *@param int $zoneid ID of zone to retrieve logs for
	 *@param string $order order for result - [A]sc or [D]esc
	 *@return array list of logs (id/date/userid/content)
	 */	
	Function showZoneLogs($zoneid,$order){
		global $db,$l;
		$this->error="";
		if($order=='D'){
			$order='DESC';
		}else{
			$order='ASC';
		}
		$query = "SELECT id,date,userid,content FROM dns_userlog
		WHERE zoneid='" . $zoneid . "' ORDER BY date " . $order;

		$res=$db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$result = array();
			while($line=$db->fetch_row($res)){
				array_push($result, $line);
			}
			return $result;
		}
	}


// Function deleteLog($logid){
	/**
	 * Delete given log record
	 *
	 *@access public
	 *@param int $logid Log ID to be deleted
	 *@return int 1 if success, 0 if error
	 */
	Function deleteLog($logid){
		global $db,$l;
		$this->error="";
		$query = "DELETE FROM dns_userlog
				WHERE id='" . $logid . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}else{
			return 1;
		}
	}
	
	
// Function deleteLogsBefore($date)
	/**
	 * Delete all logs before given date
	 *
	 *@access public
	 *@param string $date date to start log deletion
	 *@return int 1 if success, 0 if error
	 */
	Function deleteLogsBefore($date){
		global $db,$l;
		$this->error="";
		$query = "DELETE FROM dns_userlog
				WHERE groupid='" . $this->groupid . "' 
				AND date < '" . $date . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error = $l['str_trouble_with_db'];
			return 0;
		}else{
			return 1;
		}	
	}
}
