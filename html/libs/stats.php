<?
/*
  This file is part of XName.org project
  See     http://www.xname.org/ for details

  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html

  Author(s): Yann Hirou <hirou@xname.org>

*/

// function countSecondary()
/**
 * Count number of secondary zones currently hosted
 *
 *@return int number of zones or N/A in case of error
 */
function countSecondary(){
  global $db;
  $query = "SELECT count(*) FROM dns_zone z,dns_user u " .
    "WHERE z.zonetype='S' AND z.userid=u.id";
  $res = $db->query($query);
  $line = $db->fetch_row($res);
  if($db->error()){
    return "N/A";
  }else{
    return $line[0];
  }
}


// function countPrimary()
/**
 * Count number of primary zones currently hosted
 *
 *@return int number of zones or N/A in case of error
 */
function countPrimary(){
  global $db;
  $query = "SELECT count(*) FROM dns_zone z,dns_user u " .
    "WHERE z.zonetype='P' AND z.userid=u.id";
  $res = $db->query($query);
  $line = $db->fetch_row($res);
  if($db->error()){
    return "N/A";
  }else{
    return $line[0];
  }
}

// function countUsers()
/**
 * Count number of users
 *
 *@return int number of users or N/A in case of error
 */
function countUsers(){
  global $dbauth,$config;
  $query = sprintf("SELECT count(*) FROM %s", $config->userdbtable);
  $res = $dbauth->query($query);
  $line = $dbauth->fetch_row($res);
  if($dbauth->error()){
    return "N/A";
  }else{
    return $line[0];
  }
}

// function countProdUsers()
/**
 * Count number of users with at least one zone in prod
 *
 *@return int number of users or N/A in case of error
 */
function countProdUsers(){
  global $dbauth,$config;
  $query = sprintf("SELECT count(*) FROM %s u, dns_zone z
    WHERE z.userid=u.%s GROUP BY z.userid",
    $config->userdbtable,
    $config->userdbfldid);
  $res = $dbauth->query($query);
  $count = $dbauth->num_rows($res);
  if($dbauth->error()){
    return "N/A";
  }else{
    return $count;
  }
}

// function showTopUsers($number)
/**
 * return $number top users with max zones
 *
 *@return array array of users with id/login/email/nbzones
 */
function showTopUsers($number){
  global $db,$dbauth,$config;
  $query = sprintf(
      "SELECT u.%s,u.%s,u.%s,count(*) as count FROM %s u, dns_zone z
        WHERE z.userid=u.%s
        GROUP BY z.userid
        ORDER BY count DESC
        LIMIT $number",
      $config->userdbfldid,
      $config->userdbfldlogin,
      $config->userdbfldemail,
      $config->userdbtable,
      $config->userdbfldid
    );
  $res = $dbauth->query($query);
  if($dbauth->error()){
    return 0;
  }
  $result = array();
  while($line = $dbauth->fetch_row($res)){
    array_push($result,$line);
  }
  return $result;
}

// function showAbUsers($number)
/**
 * return all users with more than $number zones
 *
 *@return array array of users with id/login/email/nbzones
 */
function showAbUsers($number){
  global $db,$dbauth,$config;
  $query = sprintf(
      "SELECT u.%s,u.%s,u.%s,count(*) as count FROM %s u, dns_zone z
        WHERE z.userid=u.%s
        GROUP BY z.userid
        ORDER BY count DESC",
      $config->userdbfldid,
      $config->userdbfldlogin,
      $config->userdbfldemail,
      $config->userdbtable,
      $config->userdbfldid
    );
  $res = $dbauth->query($query);
  if($dbauth->error()){
    return 0;
  }
  $result = array();
  while($line = $dbauth->fetch_row($res)){
    if($line[3] <= $number){
      return $result;
    }
    array_push($result,$line);
  }
  return $result;
}

// function showTopZones($number)
/**
 * return $number top zones with max records
 *
 *@return array array of zones with zoneid/userid/login/email/zonename/nbrecords
 */
function showTopZones($number){
  global $db,$dbauth,$config;
  $query = sprintf(
      "SELECT z.id,u.%s,u.%s,u.%s,z.zone,count(*) as count
        FROM %s u, dns_zone z, dns_record r
        WHERE z.userid=u.%s
        AND   z.id=r.zoneid
        GROUP BY z.id
        ORDER BY count DESC
        LIMIT $number",
      $config->userdbfldid,
      $config->userdbfldlogin,
      $config->userdbfldemail,
      $config->userdbtable,
      $config->userdbfldid
    );
  $res = $dbauth->query($query);
  if($dbauth->error()){
    return 0;
  }
  $result = array();
  while($line = $dbauth->fetch_row($res)){
    array_push($result,$line);
  }
  return $result;
}

// function showAbZones($number)
/**
 * return all zones with more than $number records
 *
 *@return array array of users with zoneid/userid/login/email/zone/nbrecords
 */
function showAbZones($number){
  global $db,$dbauth,$config;
  $query = sprintf(
      "SELECT z.id,u.%s,u.%s,u.%s,z.zone,count(*) as count
        FROM %s u, dns_zone z, dns_record r
        WHERE z.userid=u.%s
        AND   z.id=r.zoneid
        GROUP BY z.id
        ORDER BY count DESC",
      $config->userdbfldid,
      $config->userdbfldlogin,
      $config->userdbfldemail,
      $config->userdbtable,
      $config->userdbfldid
    );
  $res = $dbauth->query($query);
  if($dbauth->error()){
    return 0;
  }
  $result = array();
  while($line = $dbauth->fetch_row($res)){
    if($line[5] <= $number){
      return $result;
    }
    array_push($result,$line);
  }
  return $result;
}

// function countRecords()
/**
 * Count number of records
 *
 *@return int number of records or N/A in case of error
 */
function countRecords(){
  global $dbauth,$config;
  $query = sprintf("SELECT count(*) FROM dns_record");
  $res = $dbauth->query($query);
  $line = $dbauth->fetch_row($res);
  if($dbauth->error()){
    return "N/A";
  }else{
    return $line[0];
  }
}

?>
