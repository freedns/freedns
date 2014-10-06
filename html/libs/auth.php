<?

/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details

  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html

  Author(s): Yann Hirou <hirou@xname.org>

*/

/**
 * general functions regarding users
 *
 *@access public
 */
class Auth {

  var $login;
  var $email;
  var $password;
  var $valid;
  var $userid;
  var $lang;
  var $options;

  /**
   * Class constructor
   *
   *@access public
   *@param string $login XName login, may be null
   *@param string $password XName password
   */
  function Auth($login, $password, $md5=0) {
    global $config, $dbauth, $l;

    $this->error = "";
    $this->email = "";
    $this->valid = 0;
    $this->userid = 0;

    if (!notnull($login)) {
      return 0;
    }
    $this->cleanId($config->userdbrecoverytable,
                   $config->userdbrecoveryfldinsertdate,
                   24*60);
    if ($this->Login($login, $password, $md5)) {
      $this->login = $login;
      $this->password = $password;
      # retrieve advanced param
      if ($this->RetrieveOptions()) {
        return 1;
      }
    }

    if (!notnull($this->error)) {
        $this->error = $l['str_bad_login_name'];
    }
    return 0;
  }


  /**
   * Delete IDs from given table if they are older than X mn
   *
   *@access private
   *@param string $table Table to clean
   *@param string $fieldname field from table containing timestamp
   *@param int $mins delete IDs older than $mins minutes
   *@return int 1 if success, 0 if error
   */
  function cleanId($table, $fieldname, $mins) {
    global $dbauth, $l;

    $this->error = "";
    $date = dateToTimestamp(nowDate());
    $date -= $mins*60;
    $date = timestampToDate($date);
    $query = "DELETE FROM " . $table  . " WHERE
    " . $fieldname . " < " . $date;
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }


  /**
   * Try to log in user, after calling $this->Exists to check if user exists
   *
   *@access private
   *@param string $login login
   *@param string $password password
   *@return int 1 if success, 0 if error or not present
   */
  function Login($login, $password, $md5=0) {
    global $dbauth, $l, $config;

    $this->error = "";
    if (!$this->Exists($login)) {
      return 0;
    } else {
      if (!$this->valid($login)) {
        $this->error = $l['str_login_not_activated'];
        return 0;
      } else {
        if (!$md5) $password = md5($password);
        $query = sprintf(
          "SELECT %s FROM %s WHERE %s='%s' AND %s='%s'",
          $config->userdbfldid,
          $config->userdbtable,
          $config->userdbfldlogin,
          mysql_real_escape_string($login),
          $config->userdbfldpassword,
          mysql_real_escape_string($password));
        $res = $dbauth->query($query);
        $line = $dbauth->fetch_row($res);
        if ($dbauth->error()) {
          $this->error = $l['str_trouble_with_db'];
          return 0;
        }

        if ($line[0] == 0) {
          return 0;
        } else {
          $this->userid = $line[0];
          return 1;
        }
      }
    }
  }


  /**
   * Check if user exists or not
   *
   *@access private
   *@param string $login login to check
   *@return int 1 if present, 0 else - or on error
   */
  function Exists($login) {
    global $dbauth, $l, $config;
    $this->error = "";
    $query = sprintf(
      "SELECT count(*) FROM %s WHERE %s='%s'",
      $config->userdbtable,
      $config->userdbfldlogin,
      mysql_real_escape_string($login));
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return $line[0];
  }


  /**
   * Check if given login is flagged as valid or not
   *@access private
   *@param string $login login to check
   *@return int 1 if valid, 0 else
   */
  function Valid($login) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf(
      "SELECT count(*) FROM %s WHERE %s='%s' AND %s='%s'",
      $config->userdbtable,
      $config->userdbfldlogin,
      $login,
      $config->userdbfldvalid,
      $config->userdbfldvalidvalue);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    $this->valid = $line[0];
    return $line[0];
  }


  /**
   * Create new user with given login, pass & email
   *
   *@access public
   *@param string $login login
   *@param string $password password
   *@param string $email email
   *@return int 1 if success, 0 if error
   */

  function userCreate($login, $password, $email) {
    global $dbauth, $l, $config;

    $this->error = "";
    # check if already exists or not
    if (!$this->Exists($login)) {
      $this->error = $l['str_login_already_exists'];
      return 0;
    }
    if (!$this->error) {
      $password = md5($password);
      $query = sprintf("INSERT INTO %s (%s,%s,%s) VALUES ('%s','%s','%s')",
                       $config->userdbtable,
                       $config->userdbfldlogin,
                       $config->userdbfldemail,
                       $config->userdbfldpassword,
                       mysql_real_escape_string($login),
                       mysql_real_escape_string($email),
                       mysql_real_escape_string($password));
      $res = $dbauth->query($query);
      if ($dbauth->error()) {
        $this->error = $l['str_trouble_with_db'];
        return 0;
      }
      $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                       $config->userdbfldid,
                       $config->userdbtable,
                       $config->userdbfldlogin,
                       mysql_real_escape_string($login));
      $res = $dbauth->query($query);
      $line = $dbauth->fetch_row($res);
      if ($dbauth->error()) {
        $this->error = $l['str_trouble_with_db'];
        return 0;
      }
      $this->userid = $line[0];
      if ($config->usergroups) {
        $query = sprintf(
          "UPDATE %s SET %s='%s' WHERE %s='%s'",
          $config->userdbtable,
          $config->userdbfldgroupid,
          $this->userid,
          $config->userdbfldid,
          $this->userid);
        $res = $dbauth->query($query);
        if ($dbauth->error()) {
          $this->error = $l['str_trouble_with_db'];
          return 0;
        }
      }
      return 1;
    }
  }


  /**
   * Change login name for current user
   *
   *@access public
   *@param string $login login
   *@return int 1 if success, 0 if error
   */
  function changeLogin($login) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("UPDATE %s SET %s='%s' WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldlogin,
                     mysql_real_escape_string($login),
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }


  /**
   * Change password for current user
   *
   *@access public
   *@param string $password password
   *@return int 1 if success, 0 if error
   */
  function updatePassword($password) {
    global $dbauth, $l, $config;

    $this->error = "";
    $password = md5($password);
    $query = sprintf("UPDATE %s SET %s='%s' WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldpassword,
                     $password,
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Return email from current user
   *
   *@access public
   *@return string email address or 0 if error
   */
  function Retrievemail() {
    global $dbauth, $l, $config;

    $this->error = "";
    if (notnull($this->email)) {
      return $this->email;
    }
    $query = sprintf(
      "SELECT %s FROM %s WHERE %s='%s'",
      $config->userdbfldemail,
      $config->userdbtable,
      $config->userdbfldid,
      $this->userid);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      $this->email = $line[0];
      return $this->email;
    }
  }

  /**
   * Return email of specified user, even if not logged in
   *
   *@access public
   *@param string $login login to retrieve mail for
   *@return string email address or 0 if error
   */
  function getEmail($login) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldemail,
                     $config->userdbtable,
                     $config->userdbfldlogin,
                     mysql_real_escape_string($login));
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      return $line[0];
    }
  }

  /**
   * Change email address for current user
   *
   *@access public
   *@param string $email new email address
   *@return int 1 if success, 0 if error
   */
  function Changemail($email) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("UPDATE %s SET %s='%s',%s='%s' WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldemail,
                     mysql_real_escape_string($email),
                     $config->userdbfldvalid,
                     $config->userdbfldvalidnullvalue,
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Returns options for given user.
   *
   *@access private
   *@return int 0 if error, 1 if success
   */
  function getOptions($id) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldoptions,
                     $config->userdbtable,
                     $config->userdbfldid,
                     $id);
    $res = $dbauth->query($query, 1);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
            $this->error = $l['str_trouble_with_db'];
            return 0;
    } else {
            return $line[0];
    }
  }

  /**
   * Returns options for current user.
   *
   *@access private
   *@return int 0 if error, 1 if success
   */
  function RetrieveOptions() {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s,%s FROM %s WHERE %s='%s'",
                     $config->userdbfldoptions,
                     $config->userdbfldlang,
                     $config->userdbtable,
                     $config->userdbfldid,
                     $this->userid);

    $res = $dbauth->query($query, 1);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
     } else {
       $this->options = $line[0];
       $this->lang = $line[1];
       return 1;
     }
   }

  /**
   * update Options parameters for current user in DB
   *
   *@access public
   *@return int 1 if success, 0 if error
   */
  function updateOptions() {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("UPDATE %s SET %s='%s',%s='%s' WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldoptions,
                     $this->options,
                     $config->userdbfldlang,
                     $this->lang,
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      $this->advanced = 1;
      return 1;
    }
  }


  /**
   * Return password for current user
   *
   *@access public
   *@return string current password or 0 if error
   */
  function retrievePassword() {
    global $dbauth, $l, $config;

    $this->error = "";
    if (notnull($this->password)) {
      return $this->password;
    }
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldpassword,
                     $config->userdbtable,
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      $this->password = $line[0];
      return $this->password;
    }
  }


  /**
   * Return generated ID for dns_waitingreply not already present (recursive)
   *
   *@access public
   *@return string ID generated or 0 if error
   */
  function generateIDEmail() {
    global $dbauth, $l, $config;

    $this->error = "";
    $result = randomID();
    # check if id already in DB or not
    $query = sprintf("SELECT COUNT(*) FROM %s WHERE %s='%s'",
                     $config->userdbwaitingtable,
                     $config->userdbwaitingfldid,
                     $result);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    if ($line[0] != 0) {
      $result = $this->generateIDEmail();
    }
    return $result;
  }

  /**
   * store userid, email and new ID (generated with generateIDEmail)
   * in dns_waitingreply, to wait for validation of new email address
   *
   *@param string $userid user ID
   *@param string $email user email address
   *@param string $id unique ID for dns_waitingreply
   *@return int 1 if success, 0 if error
   */
  function storeIDEmail($userid, $email, $id) {
    global $dbauth, $l, $config;

    $this->error = "";
    # TODO: check if present
    $query = sprintf("DELETE FROM %s WHERE %s='%s'",
                     $config->userdbwaitingtable,
                     $config->userdbwaitingflduserid,
                     $userid);
    $res = $dbauth->query($query);

    $query = sprintf("INSERT INTO %s (%s,%s,%s) VALUES ('%s','%s','%s')",
                     $config->userdbwaitingtable,
                     $config->userdbwaitingflduserid,
                     $config->userdbwaitingfldemail,
                     $config->userdbwaitingfldid,
                     mysql_real_escape_string($userid),
                     mysql_real_escape_string($email),
                     $id);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }

  /**
   * Validate email corresponding to given ID (implies that user
   * have received email with validating ID)
   *
   *@access public
   *@param string $id ID
   *@return int 1 if success, 0 if error
   */
  function validateIDEmail($id) {
    global $dbauth, $l, $config;

    # TODO: valid for limited time
    $this->error = "";
    $query = sprintf("SELECT %s,%s FROM %s WHERE %s='%s'",
                     $config->userdbwaitingflduserid,
                     $config->userdbwaitingfldemail,
                     $config->userdbwaitingtable,
                     $config->userdbwaitingfldid,
                     mysql_real_escape_string($id));
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    if (!notnull($line[0])) {
      $this->error = $l['str_no_such_id'] ;
      return 0;
    }
    $userid = $line[0];
    $email = $line[1];

    $query = sprintf("DELETE FROM %s WHERE %s='%s'",
                     $config->userdbwaitingtable,
                     $config->userdbwaitingflduserid,
                     $userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    # if user changed email to one containing dot, we cannot
    # generate soa properly, switch off the option.
    $userpart = split('@', $email);
    if (strpos($userpart[0], '.') !== FALSE) {
      $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                       $config->userdbfldoptions,
                       $config->userdbtable,
                       $config->userdbfldid,
                       $userid);
      $res = $dbauth->query($query);
      $line = $dbauth->fetch_row($res);
      $options = $line[0];
      $emailsoa = 0;
      if (preg_match("/emailsoa = ([^;]*);/i", $options, $match)) {
        $emailsoa = $match[1];
      }
      if (notnull($emailsoa)) {
        $options = ereg_replace('emailsoa = 1', 'emailsoa = 0', $options);
      }
      $options = "'" . $options . "'";
    } else {
      $options = $config->userdbfldoptions;
    }
    # update email & set dns_user.valid to 1
    $query = sprintf("UPDATE %s SET %s='%s',%s='%s',%s = %s WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldemail,
                     mysql_real_escape_string($email),
                     $config->userdbfldvalid,
                     $config->userdbfldvalidvalue,
                     $config->userdbfldoptions,
                     $options,
                     $config->userdbfldid,
                     $userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }


  function retrieveEmailToConfirm() {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbwaitingfldemail,
                     $config->userdbwaitingtable,
                     $config->userdbwaitingflduserid,
                     $this->userid);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    if (!notnull($line[0])) {
      $this->error = $l['str_no_such_id'] ;
      return 0;
    }
    return $line[0];
  }


  /**
   * Generate unique ID for account recovery
   *
   *@access public
   *@return string ID if success, 0 if error
   */
  function generateIDRecovery() {
    global $dbauth, $l, $config;

    $this->error = "";
    $result = randomID();
    # check if id already in DB or not
    $query = sprintf("SELECT COUNT(*) FROM %s WHERE %s='%s'",
                     $config->userdbrecoverytable,
                     $config->userdbrecoveryfldid,
                     $result);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    if ($line[0] != 0) {
      $result = $this->generateIDRecovery();
    }
    return $result;
  }


  /**
   * store login and new ID (generated with generateIDRecovery)
   * in dns_recovery, to wait for request of lost password
   *
   *@access public
   *@param string $login login
   *@param string $id generated ID to store
   *@return int 1 if success, 0 if error
   */
  function storeIDRecovery($login, $id) {
    global $dbauth, $l, $config;

    $this->error = "";
    # retrieve user ID
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldid,
                     $config->userdbtable,
                     $config->userdbfldlogin,
                     $login);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    $userid = $line[0];

    # if $login already present, delete id
    $query = sprintf("DELETE FROM %s WHERE %s='%s'",
                     $config->userdbrecoverytable,
                     $config->userdbrecoveryflduserid,
                     $userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    $query = sprintf("INSERT INTO %s (%s,%s) VALUES ('%s','%s')",
                     $config->userdbrecoverytable,
                     $config->userdbrecoveryflduserid,
                     $config->userdbrecoveryfldid,
                     $userid,
                     $id);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }


  /**
   * Validate ID from dns_recovery, and modify current userid
   * to the one from dns_recovery
   *
   *@access public
   *@param string $id ID
   *@return int 1 if success, 0 if error
   */
  function validateIDRecovery($id) {
    global $dbauth, $l, $config;

    # TODO: limit validity time
    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbrecoveryflduserid,
                     $config->userdbrecoverytable,
                     $config->userdbrecoveryfldid,
                     $id);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    if (!notnull($line[0])) {
      $this->error = $l['str_no_such_id'];
      return 0;
    }
    $userid = $line[0];

    $query = sprintf("DELETE FROM %s WHERE %s='%s'",
                     $config->userdbrecoverytable,
                     $config->userdbrecoveryflduserid,
                     $userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    $this->userid = $userid;
    return 1;
  }


  /**
   * Return login matching given userid
   *
   *@access public
   *@param string $id user ID
   *@return string login or 0 if error
   */
  function RetrieveLogin($id) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldlogin,
                     $config->userdbtable,
                     $config->userdbfldid,
                     $id);
    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      return $line[0];
    }
  }

  /**
   * Return id matching given user login
   *
   *@access public
   *@param string $login user login
   *@return string id or 0 if error
   */
  function retrieveId($login) {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
                     $config->userdbfldid,
                     $config->userdbtable,
                     $config->userdbfldlogin,
                     mysql_real_escape_string($login));

    $res = $dbauth->query($query);
    $line = $dbauth->fetch_row($res);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      return $line[0];
    }
  }


  /**
   * Remove user from DB
   *
   *@access public
   *@return int 1 if success, 0 if fail
   */
  function deleteUser() {
    global $dbauth, $l, $config;

    $this->error = "";
    $query = sprintf("DELETE FROM %s WHERE %s='%s'",
                     $config->userdbtable,
                     $config->userdbfldid,
                     $this->userid);
    $res = $dbauth->query($query);
    if ($dbauth->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    } else {
      $this->logout();
      return 1;
    }
  }


  /**
  * Return a random password of $length length
  *
  *@access public
  *@param int $length, desired password length
  *@return string password
  */

  function generateRandomPassword($length) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $chars .= strtolower($chars);
    $chars .= "0123456789@/*0123456789@/*";
    $size = strlen($chars) - 1;
    $pass = "";
    while (strlen($pass) < $length) {
      $pass .= $chars[mt_rand(0, $size)];
    }
    return $pass;
  }

}
?>
