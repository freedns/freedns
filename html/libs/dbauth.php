<?

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/



/*
 *	Generic interface for database access
 * 	some code taken from daCode project http://www.dacode.org,
 *	originally from Fabien Seisen <seisen@linuxfr.org>
 */

// uncomment this if not in the php.ini file
//  dl('mysql.so');

/**
 * Generic interface for DBAUTH access. Currently supports mysql only
 *
 *@access public
 */
class Dbauth {
  var $dbh, $sh;
  var $result;
  var $lastquery;
  var $cachecontent;
  var $totaltime;

  /**
   * Class constructor. Connects to DB 
   *
   *@access public
   *@return object DB database object
   */
  Function Dbauth() {
  	global $config;
	$this->totaltime=0;
    if($config->dbpersistent){
      $this->dbh = $this->pconnect($config->userdbhost . ":" . $config->userdbport, $config->userdbuser, $config->userdbpass, $config->userdbname);
    }else{
      $this->dbh = $this->connect($config->userdbhost, $config->userdbuser, $config->userdbpass, $config->userdbname);
    }
    return $this->dbh;
  }
  
  /**
   * Do simple connect
   *
   *@access private
   *@param string $host hostname or IP of DB host
   *@param string $user username for db access
   *@param string $pass password for db access
   *@param string $db database name
   *@return object Db database handler
   */
  Function connect($host, $user, $pass, $db){
    $this->sh = mysql_connect($host, $user, $pass);
    $res = mysql_select_db($db, $this->sh);
    return $res;
  }

  /**
   * Do permanent connect
   *
   *@access private
   *@param string $host hostname or IP of DB host
   *@param string $user username for db access
   *@param string $pass password for db access
   *@param string $db database name
   *@return object Db database handler
   */
  Function pconnect($host, $user, $pass, $db){
    $this->sh = mysql_pconnect($host, $user, $pass);
    $res = mysql_select_db($db, $this->sh);
    return $res;
  }
  
  /**
   * Pass query to DB
   *
   *@access public
   *@param string $string QUERY 
   *@return object query handler
   */
  Function query($string,$cache = 0){
	$string = preg_replace('/[\n\s\t]+?/',' ',$string);
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$tstart = $mtime;
	if($cache && $cachecontent[$string]){
		$this->result = $cachecontent[$string];
	}else{
    		$this->result = mysql_query($string, $this->sh);
	}
	if($cache){
		$cachecontent[$string] = $this->result;
	}
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$tend = $mtime;
	$ttot = $tend - $tstart;
	$this->lastquery = $string;
	$this->totaltime+=$ttot;
    return $this->result;
  }
  
  /**
   * Fetch next row from query handler
   *
   *@access public
   *@param object Query $res query handler
   *@return array next result row
   */
  Function fetch_row($res){
    if($res){
      return  mysql_fetch_row($this->result);
    }else{
      return 0;
    }
  }	

  /**
   * Returns number of affected rows by given query handler
   *
   *@access public
   *@param object Query $res query handler
   *@return int number of affected rows
   */
  Function affected_rows($res){
  	if($res){
		return mysql_affected_rows($res);
	}else{
		return 0;
	}
  }
  
  /**
   * Returns number of affected rows by given query handler (select only)
   *
   *@access public
   *@param object Query $res query handler
   *@return int number of affected rows
   */
  Function num_rows($res){
  	if($res){
		return mysql_num_rows($res);
	}else{
		return 0;
	}
  }
  
  /**
   * Free current db handlers - query & results
   *
   *@access public
   *@return int 0
   */
  Function free(){
//	   return mysql_free_result($this->result);
	return 0;
  }


  /**
   * Check if an error occured, & take action
   *
   *@access public
   *@return int 1 if error, 0 else
   */
  Function error(){
  	global $config,$l;
    if(mysql_errno()){
      mailer($config->emailfrom,$config->emailto,$config->sitename . 
	  $l['str_trouble_with_db'],'',mysql_errno() . ": " . mysql_error() . "\n"
	  . $this->lastquery . "\n");
	  return 1;
    }else{
		return 0;
	}
  }
  
}


?>
