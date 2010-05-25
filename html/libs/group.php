<?

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

 // class Group
 // general functions regarding user groups

// WARNING : we suppose that all supplied parameters
// have already been MySQL-escaped

/**
 * general functions regarding user groups
 *
 *@access public
 */
class Group {

	var $groupid;
	var $error;

	// Instanciation
	/**
	 * Class constructor
	 *
	 *@access public
	 *@param string $userid ID of user member of group
	 */
	Function Group($userid){
		global $dbauth,$l,$config;	
		$this->error="";
		
		// retrieve groupid
		$query = sprintf(
			"SELECT %s FROM %s WHERE %s='%s'",
			$config->userdbfldgroupid,
			$config->userdbtable,
			$config->userdbfldid,
			$userid);
		$res = $dbauth->query($query);
		if($dbauth->error()){
			$this->error = $l['str_trouble_with_db'];
		}else{
			$line=$dbauth->fetch_row($res);
			$this->groupid = $line[0];
		}
		

	}
	
	
	
// Function GroupUserCreate($login,$password,$email,$groupid,$groupright)
	/**
	 * Create new user with given login, pass & email
	 * in given group with given right (R or W)
	 *
	 *@access public
	 *@param string $login login 
	 *@param string $password password
	 *@param string $email email
	 *@param int $groupid id of group to be inserted in
	 *@param string $groupright group right of created user - [R]ead or [W]rite
	 *@return int 1 if success, 0 if error
	 */
	Function GroupUserCreate($user,$login,$password,$email,$groupid,$groupright){
		global $dbauth,$l,$config;

		$this->error="";
		// check if already exists or not
		if(!$user->Login($login,$password)){
			if(!$user->error){
				// does not exist already ==> OK
				$password = md5($password);
				if ($groupright != 'R' && $groupright != 'W') {
				  $this->error=$l['str_wrong_group_rights'];
				  return 0;
				}
				$options="advanced=0;ipv6=0;txtrecords=0;nbrows=4;grouprights=" . $groupright . ";";	
				$query = sprintf(
					"INSERT INTO %s (%s,%s,%s,%s,%s,%s) VALUES ('%s','%s','%s','%s','%s','%s')",
					$config->userdbtable,
					$config->userdbfldlogin,
					$config->userdbfldemail,
					$config->userdbfldpassword,
					$config->userdbfldvalid,
					$config->userdbfldgroupid,
					$config->userdbfldoptions,
					$login,
					$email,
					$password,
					$config->userdbfldvalidvalue,
					$groupid,
					$options);
				$res = $dbauth->query($query);
				if($dbauth->error()){
					$this->error = $l['str_trouble_with_db'];
					return 0;
				}else{
					return 1;
				}
			}
		}else{
			$this->error=$l['str_login_already_exists'];
			return 0;
		}
	}

// Function RetrieveGroupUsers()
	/**
	 * Return list of users in current group
	 *
	 *@access public
	 *@return list of userid/login/grouprights or 0 if error
	 */
	Function RetrieveGroupUsers(){
		global $dbauth,$l,$config;
		$this->error="";
		$query = sprintf(
			"SELECT %s,%s,%s FROM %s WHERE %s='%s'",
			$config->userdbfldid,
			$config->userdbfldlogin,
			$config->userdbfldoptions,
			$config->userdbtable,
			$config->userdbfldgroupid,
			$this->groupid);

		$res=$dbauth->query($query);
		if($dbauth->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$result = array();
			while($line=$dbauth->fetch_row($res)){
				array_push($result, $line);
			}
			return $result;
		}
	}

// Function getGroupRights($id)
	/**
	 * Return group rights for given userid
	 *
	 *@access public
	 *@param string $id user ID
	 *@return string rights ([A]dmin,[R]ead,[W]rite) or 0 if error
	 */
	Function getGroupRights($id){
		global $dbauth,$l,$config;
		$this->error="";
		// ???? $options
		$query = sprintf(
			"SELECT %s FROM %s WHERE %s='%s'",
			$config->userdbfldoptions,
			$config->userdbtable,
			$config->userdbfldid,
			$id);

		$res=$dbauth->query($query);
		$line=$dbauth->fetch_row($res);
		if($dbauth->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			if(preg_match("/grouprights=([^;]*);/i",$line[0],$match)){
				return $match[1];
			}else{
				return 0;
			}
		}		
	}
	
// Function setGroupRights($id,$groupright)
	/**
	 * Set group right to $groupright for given $id
	 *
	 *@access public
	 *@param string $id user ID
	 *@param string $groupright group right to be set ([R]ead,[W]rite)
	 *@return int 1 if success, 0 if error
	 */
	Function setGroupRights($id,$groupright){
		global $dbauth,$l,$config;
		$this->error="";

		if ($groupright != 'R' && $groupright != 'W') {
		  $this->error=$l['str_wrong_group_rights'];
		  return 0;
		}
		$options=$this->getOptions($id);
		if(strpos($options,"grouprights=") !== false){
			$options = preg_replace("/grouprights=[^;]*;/","grouprights=" . $groupright .";",$options);
		}else{
			$options .= "grouprights=" . $groupright . ";";
		}
		$query = sprintf(
			"UPDATE %s SET %s='%s' WHERE %s='%s'",
			$config->userdbtable,
			$config->userdbfldoptions,
			mysql_real_escape_string($options),
			$config->userdbfldid,
			$id);
		$res=$dbauth->query($query);
		if($dbauth->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			return 1;
		}
	}
				
	
// Function isMember($id)
	/**
	 * Check if given Id is member of current group or not
	 *
	 *@access public
	 *@param string $id user ID to be checked
	 *@return int  1 if member, 0 if not or error
	 */
	Function isMember($id){
		global $dbauth,$l,$config;
		$this->error="";
		$query = sprintf(
			"SELECT count(*) FROM %s WHERE %s='%s' AND %s='%s'",
			$config->userdbtable,
			$config->userdbfldid,
			$id,
			$config->userdbfldgroupid,
			$this->groupid);
		$res=$dbauth->query($query);
		$line=$dbauth->fetch_row($res);
		if($dbauth->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			return $line[0];
		}
	}
	
// Function deleteUser($id)
	/**
	 * Remove user from DB - all zones belongs to group
	 *
	 *@access public
	 *@param string $id user ID to be deleted
	 *@return int 1 if success, 0 if fail
	 */
	Function deleteUser($id){
		global $dbauth,$l,$config;
		$this->error="";
		$query = sprintf(
			"DELETE FROM %s WHERE %s='%s'",
			$config->userdbtable,
			$config->userdbfldid,
			$id);
		$res=$dbauth->query($query);
		if($dbauth->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			return 1;
		}
	}
	
	
	
// Function listallzones()
	/**
	 * list all zones owned by same group
	 *
	 *@access public
	 *@return array array of all zones/zonestypes owned by user or 0 if error
	 */	
	Function listallzones(){
		global $db,$l;
		global $user;
		// warning: be sure to validate user before using this function
		$this->error="";
		if ($user->authenticated >= 2) {
		  $this->error=migrationbox();
		  return "";
		}


		$query = "SELECT zone, zonetype, id FROM dns_zone
		WHERE userid='" . $this->groupid . "' AND status!='D' ORDER BY zone DESC";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}else{
			$result = array();
			while($line = $db->fetch_row($res)){
				array_push($result,$line);
			}
			return $result;
		}
	}

// Function getOptions($id)
        /**
         * Returns options for given user. 
         *
         *@access private
         *@return int 0 if error, 1 if success
         */
         Function getOptions($id){
                global $dbauth,$l,$config;
                $this->error="";
                $query = sprintf(
                        "SELECT %s FROM %s WHERE %s='%s'",
                        $config->userdbfldoptions,
                        $config->userdbtable,
                        $config->userdbfldid,
                        $id);

                $res=$dbauth->query($query,1);
                $line=$dbauth->fetch_row($res);
                if($dbauth->error()){
                        $this->error=$l['str_trouble_with_db'];
                        return 0;
                }else{
                        return $line[0];
                }
        }



}
