<?

/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details
  
  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
  
  Author(s): Yann Hirou <hirou@xname.org>

*/

 // class User
 // general functions regarding users & logins

// WARNING : we suppose that all supplied parameters
// have already been MySQL-escaped

/**
 * general functions regarding users & logins
 *
 *@access public
 */
class User extends Auth {

  var $authenticated;
  var $idsession;
  var $grouprights;
  var $emailsoa;
  var $advanced;
  var $ipv6;
  var $txtrecords;
  var $srvrecords;
  var $nbrows;
  var $isadmin;
  var $error;
  
  // Instanciation
  // if $login or $idsession, match against DB
  // to log in. fill in $authenticated, generate $idsession
  /**
   * Class constructor
   *
   *@access public
   *@param string $login XName login, may be null
   *@param string $password XName password
   *@param string $sessionID current session ID, if user already logged in
   */
  Function User($login, $password, $sessionID, $md5=0){
    global $config;
    $this->idsession=0; // initialization
    $this->authenticated=0;
    $this->grouprights="";
    $this->isadmin=0;
    $this->nbrows=$config->defaultnbrows;
    global $db,$l;
    
    if(notnull($login)){
      if($this->Auth($login,$password,$md5)){
          $this->authenticated=1;
        $id = $this->generateIDSession();
        $query = "INSERT INTO dns_session 
        (sessionID,userid) VALUES ('" . $id . "','" .
        $this->userid . "')";
        $res = $db->query($query);
        if($db->error()){
          $this->error=$l['str_trouble_with_db'];
          return 0;
        }
        $this->idsession=$id;
      }else{ // bad login
        if(notnull($this->error)){
          return 0;
        }else{
          // No authentication
          $this->error=$l['str_bad_login_name'];
          return 0;
        }
      }
    }else{ // end if not null login
      if(notnull($sessionID)){
        // retrieve $login, $password from DB
        // check if session not expired (30mn)
        $this->checkidsession($sessionID);        
        if(notnull($this->error)){
          return 0;
        }
        $this->authenticated=1;
        $this->RetrieveOptions();

        // retrieve username
        $this->login = $this->RetrieveLogin($this->userid);
      }else{
        // nothing entered...
        // do nothing.
        return 0;
      }
    } // end else if not null login 
        if (!$this->Migrated()) {
          if (notnull($this->email))
          $this->authenticated=2;
           else
            $this->authenticated=3;
        }

    // retrieve advanced param
    
    if(preg_match("/advanced=([^;]*);/i",$this->options,$match)){
                        $this->advanced=$match[1];
                }
                if(preg_match("/ipv6=([^;]*);/i",$this->options,$match)){
                        $this->ipv6=$match[1];
                }
                if(preg_match("/txtrecords=([^;]*);/i",$this->options,$match)){
                        $this->txtrecords=$match[1];
                }
                if(preg_match("/emailsoa=([^;]*);/i",$this->options,$match)){
                        $this->emailsoa=$match[1];
                }
                if(preg_match("/srvrecords=([^;]*);/i",$this->options,$match)){
                        $this->srvrecords=$match[1];
                }
                if(preg_match("/nbrows=([^;]*);/i",$this->options,$match)){
                        $this->nbrows=$match[1];
                }
                if(preg_match("/grouprights=([^;]*);/i",$this->options,$match)){
                        $this->grouprights=$match[1];
                }

    
  }

    Function Migrated() {
      global $db,$l;
      $this->error="";
      if ($this->authenticated == 0) {
        return 0;
      }
      $query = "SELECT migrated,email FROM dns_user WHERE id='".$this->userid."'";
      $res = $db->query($query);
      $line = $db->fetch_row($res);
      if($db->error()){
          $this->error=$l['str_trouble_with_db'];
          return 0;
      }
      $this->email=$line[1];
      return $line[0];
    }

    Function MigrateMe() {
      global $db,$l;
      $this->error="";
      if ($this->authenticated != 2) {
        return 0;
      }
      $query = "UPDATE dns_zone SET status='M' WHERE userid='".$this->userid."' AND status!='D';";
      $res = $db->query($query);
      if($db->error()){
          $this->error=$l['str_trouble_with_db'];
          return 0;
      }
      $query = "UPDATE dns_user SET migrated=1 WHERE groupid='".$this->userid."';";
      $res = $db->query($query);
      if($db->error()){
          $this->error=$l['str_trouble_with_db'];
          return 0;
      }
      $this->authenticated = 1;
      return 1;
    }

// Function changeOptions()  
        /**
         * change $this->options content with existing variables
         *
         *@access public
         *@return int 0 if error, 1 if success
         */
  Function changeOptions(){
    global $config;
    // replace params in $this->options
    if(preg_match("/advanced=/",$this->options)){
      $this->options = preg_replace("/advanced=[^;]*;/i",
        "advanced=" . $this->advanced . ";",$this->options);  
    }else{
      $this->options .= "advanced=" . $this->advanced . ";";
    }
    if(preg_match("/ipv6=/",$this->options)){
      $this->options = preg_replace("/ipv6=[^;]*;/i",
        "ipv6=" . $this->ipv6 . ";",$this->options);  
    }else{
      $this->options .= "ipv6=" . $this->ipv6 . ";";
    }
    if(preg_match("/txtrecords=/",$this->options)){
      $this->options = preg_replace("/txtrecords=[^;]*;/i",
        "txtrecords=" . $this->txtrecords . ";",$this->options);  
    }else{
      $this->options .= "txtrecords=" . $this->txtrecords . ";";
    }
    if(preg_match("/srvrecords=/",$this->options)){
      $this->options = preg_replace("/srvrecords=[^;]*;/i",
        "srvrecords=" . $this->srvrecords . ";",$this->options);  
    }else{
      $this->options .= "srvrecords=" . $this->srvrecords . ";";
    }
    if(preg_match("/emailsoa=/",$this->options)){
      $this->options = preg_replace("/emailsoa=[^;]*;/i",
        "emailsoa=" . $this->emailsoa . ";",$this->options);  
    }else{
      $this->options .= "emailsoa=" . $this->emailsoa . ";";
    }
    if(preg_match("/nbrows=/",$this->options)){
      $this->options = preg_replace("/nbrows=[^;]*;/i",
        "nbrows=" . $this->nbrows . ";",$this->options);  
    }else{
      $this->options .= "nbrows=" . $this->nbrows . ";";
    }
    if(preg_match("/grouprights=/",$this->options)){
      $this->options = preg_replace("/grouprights=[^;]*;/i",
        "grouprights=" . $this->grouprights . ";",$this->options);
    }else{
      $this->options .= "grouprights=" . $this->grouprights . ";";
    }
                // return error if any of \'/* is present
                if(preg_match("/[\\\'\/\*]/", $this->options)){
                        $this->error="Illegal char";
                        return 0;
                }else{          
                        return $this->updateOptions();
                }
}
  

//      Function generateIDSession ()
        /**
         * Call randomID() recursively until an ID not already in DB is found
         *
         *@access public
         *@return string ID
         */
        Function generateIDSession (){
                global $db,$l;
                $this->error="";
                $result = randomID();
                
                // check if id already in DB or not
                $query = "SELECT count(*) FROM dns_session
                WHERE sessionID='" . $result . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if($db->error()){
                        $this->error=$l['str_trouble_with_db'];
                        return 0;
                }
                if($line[0] != 0){
                        $result = $this->generateIDSession();
                }
                return $result;
        }
        

  // ********************************************************

  //   Function checkidsession($idsession)
  /**
   * Check if session ID is valid, not expired, & update timestamp to now
   *
   *@access private
   *@param string $idsession Session ID to validate
   *@return int 0 if error, else return 1
   */
  Function checkidsession($idsession){
    global $db,$l;
    
    $query = "SELECT userid,date FROM dns_session
    WHERE sessionID='" . mysql_real_escape_string($idsession) . "'";
    $res = $db->query($query);
    $line = $db->fetch_row($res);
    if($db->error()){
      $this->error=$l['str_trouble_with_db'];
      return 0;
    }
    $date = $line[1];

    if($date){
      // check if $date - now <= 30mn

      if(diffDate($date) > 30*60){

        // session expired
        // delete session 
        $query = "DELETE FROM dns_session WHERE sessionID='" .
        $idsession . "'";
        $db->query($query);
        $this->error=$l['str_session_expired'];
        return 0;  
      }
      
      // update DB with new date
      $query = "UPDATE dns_session SET date=now()
      WHERE sessionID='" . $idsession . "'";
      $db->query($query);
      
      $this->userid=$line[0];
      $this->idsession=$idsession;
    }else{ // date empty == no such id in DB
      $this->error=$l['str_session_expired'];
      return 0;  
    }  
    return 1;
  }

        // ********************************************************
        //      Function logout($idsession)
        /**
         * Log out user by deleting entry from dns_session & reseting user vars
         *
         *@access public
         *@param string $idsession session ID to reset
         *@return int 1 if success, 0 if error 
         */
        Function logout($idsession){
                global $db,$l;
                if($idsession==0){
                        $idsession=$this->idsession;
                }
                $query = "DELETE FROM dns_session WHERE sessionID='" . $idsession . "'";
                $res=$db->query($query);
                if($db->error()){
                        $this->error=$l['str_trouble_with_db'];
                        return 0;
                }
                $this->authenticated=0;
                $this->login="";
                $this->password="";
                $this->idsession="";
                $this->userid=0;
                return 1;
        }



// Function listallzones()
  /**
   * list all zones owned by same user
   *
   *@access public
   *@return array array of all zones/zonestypes owned by user or 0 if error
   */  
  Function listallzones($zone=""){
    global $db,$l;
    global $user;
    // warning: be sure to validate user before using this function
    $this->error="";
    if ($user->authenticated >= 2) {
      $this->error=migrationbox();
      return "";
    }

    $query = "SELECT zone, zonetype, id FROM dns_zone
    WHERE userid='" . $this->userid . "'";
    if (notnull($zone)) $query .= " AND zone='".mysql_real_escape_string($zone)."'";
    $query .= " AND status!='D' ORDER BY zone DESC";
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
}
?>
