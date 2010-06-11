<?
/*
 This file is part of XName.org project
 See http://www.xname.org/ for details
 
 License: GPLv2
 See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
 
 Author(s): Yann Hirou <hirou@xname.org>

*/

 // Class Secondary
 // All functions for secondary manipulation

/**
 * All functions for secondary manipulation
 *
 *@access public
 */ 
class Secondary extends Zone {

 var $masters;
 var $xfer;
 var $serial;
 var $creation;
 var $user;
 
 // Instanciation
 /**
  * Class constructor - initialize all secondary data from DB
  *
  *@access public
  *@param string $zonename name of zone
  *@param string $zonetype type of zone (necessary secondary...)
  *@param object User $user Current user
  */
  
 Function Secondary($zonename,$zonetype,$user){
  global $db,$l;
  $this->Zone($zonename,$zonetype);
  
  $query = "SELECT masters,xfer,serial
  FROM dns_confsecondary WHERE zoneid='" . $this->zoneid . "'";
  $res = $db->query($query);
  $line = $db->fetch_row($res);

  if($db->error()){
   $this->error=$l['str_trouble_with_db'];
   return 0;
  }

  if(!isset($line[0])){
   $this->creation = 1;
  }else{
   $this->creation = 0;
  }
  $this->masters = $line[0];
  $this->xfer = $line[1];
  $this->serial = $line[2];
  $this->user = $user;

 }


// Function printModifyForm($params)
 /**
  * Print HTML form pre-filled with current data
  *
  *@access public
  *@param array $params params
  *@return string HTML form pre-filled
  */
 Function printModifyForm($params){
  global $l, $hiddenfields;

  $this->error="";
  list($advanced,$ipv6,$nbrows)=$params;
  $result = '
   <h3 class="boxheader">
   ' . $l['str_zone'] . ': ' . $this->zonename . '
   </h3>';

  $result .= '
  <form method="POST">
   <input type="hidden" name="modified" value="1">
   ' . $hiddenfields . '
   <input type="hidden" name="zonename" value="' . 
   $this->zonename . '">
   <input type="hidden" name="zonetype" value="' . 
   $this->zonetype . '">
      <p>' . $l['str_secondary_be_sure_that_name_server_is_auth'] . '</p>
   <table>
   <tr><td class="left">' . $l['str_secondary_primary_ns_ip'] . '</td>
   <td><input type="text" name="primary"
   value="' . $this->masters . '">
   </td></tr>
   </table>
   <p> ' . $l['str_secondary_allow_transfer_from']  . '</p>
   <table>
      <tr><td>
   <label><input type="radio" name="xfer" value="all" ';
   $notothers = 0;
   $xferip="";
   if($this->xfer == 'any'){
    $result .= 'checked';
    $notothers = 1;
   }
   $result .= '>' . $l['str_secondary_allow_transfer_all'] . '</label>
      </td></tr>
      <tr><td>
   <label><input type="radio" name="xfer" value="master" ';
   if($this->xfer == $this->masters){
    $result .= 'checked';
    $notothers = 1;
   }
   $result .= '>' . $l['str_secondary_allow_transfer_master_only'] . '</label>
   </td></tr>
   <tr><td><label><input type="radio" name="xfer" value="others" ';
   if($notothers == 0){
    $result .= 'checked';
    if(strpos('.' . $this->xfer,$this->masters) == 1){
     $xferip = substr($this->xfer,strlen($this->masters)+1);
    }else{
     $xferip = $this->xfer;
    }
   }
   $result .= '>' . $l['str_secondary_allow_transfer_master_and_ip'] . '</label>
   <input id="xferip" type="text" name="xferip" value="' . $xferip . '">
   </td></tr>
      </table>
      <table id="submit">
   <tr><td><input type="submit"
   value="' . $l['str_secondary_modify_button'] . '"></td></tr>
   </table>
  </form>
  ';  

  return $result;
 }

// Function PrintModified($params)
 /**
  * Take params send by "printmodifyform", do integrity 
  * checks,checkDig & call $this->updateDb
  *
  *@access public
  *@param array $params array of params: primary, xfer & xferIP
  *@return string HTML output
  */
 Function PrintModified($params){
  global $db, $config,$html, $l;
    
  list($primary,$xfer,$xferip)=$params;
  $content = "";
  $localerror=0;
  if(!notnull($primary)){
   $localerror = 1;
   $content .= sprintf($html->string_error,
    $l['str_secondary_you_must_provide_a_primary'] 
    ) . '<br >';
  }
  
  // if primary modified ==> try to dig
  if($primary != $this->masters){
   // check primary integrity 
   if(!checkPrimary($primary)){
    $localerror = 1;
    $content .= sprintf($html->string_error, 
       $l['str_secondary_your_primary_should_be_an_ip'] . 
       "<br >" .
       $l['str_secondary_if_you_want_two_primary']
      ) . '<br >';
   }else{
    // remove last ';' if present
    if(substr($primary, -1) == ';'){
     $primary = substr($primary, 0, -1);
    }
    // split $primary into IPs
    $server = split(';',$primary);
    reset($server);
    while($ipserver = array_pop($server)){
     $dig = checkDig($ipserver,$this->zonename);
     if($dig != 'NOERROR'){
      switch($dig){
      case "NOERROR":
       $msg = $l['str_dig_result_NOERROR'];
       break;
      case "FORMERR":
       $msg = $l['str_dig_result_FORMERR'];
       break;
      case "SERVFAIL":
       $msg = $l['str_dig_result_SERVFAIL'];
       break;
      case "NXDOMAIN":
       $msg = $l['str_dig_result_NXDOMAIN'];
       break;
      case "NOTIMP":
       $msg = $l['str_dig_result_NOTIMP'];
       break;
      case "REFUSED":
       $msg = $l['str_dig_result_REFUSED'];
       break;
      case "YXDOMAIN":
       $msg = $l['str_dig_result_YXDOMAIN'];
       break;
      case "YXRRSET":
       $msg = $l['str_dig_result_YXRRSET'];
       break;
      case "NXRRSET":
       $msg = $l['str_dig_result_NXRRSET'];
       break;
      case "NOTAUTH":
       $msg = $l['str_dig_result_NOTAUTH'];
       break;
      case "NOTZONE":
       $msg = $l['str_dig_result_NOTZONE'];
       break;
      case "BADSIG":
       $msg = $l['str_dig_result_BADSIG'];
       break;
      case "BADKEY":
       $msg = $l['str_dig_result_BADKEY'];
       break;
      case "BADTIME":
       $msg = $l['str_dig_result_BADTIME'];
       break;
      }

      if(notnull($msg)){
       $dig = '"' . $dig . '" (' . $msg . ')';
      }

      $content .= sprintf($html->string_warning, 
        sprintf($l['str_trying_to_dig_from_x_returned_status_x'],
         $ipserver, $dig)
        );
      $content .= " " .
       sprintf($l['str_secondary_non_blocking_warning_x_will_not_serve'],
         $config->sitename) . '<br >';
     }
    }
   }
  }
  
  
  // check xferip
  if(notnull($xferip)){
   if(!checkPrimary($xferip)){
    $localerror = 1;
    $content .= sprintf($html->string_error, 
       $l['str_secondary_invalid_list_of_allowtransfer']
      ) . '<br >';
   }
   $xfer='others';
  }else{
   if($xfer=='others'){
    $xfer='master';
   }
  }

  
  switch($xfer){
   case "all":
    $xferip = 'any';
    break;
   case "master":
    $xferip=$primary;
    break;
   case "others":
    // remove last ';' if present
    if(substr($xferip, -1) == ';'){
     $xferip = substr($xferip, 0, -1);
    }
    
    // suppress duplicate entry of $primary if already in $xferip
    $xferarray = split(';',$xferip);
    $xferiparray=array();
    reset($xferarray);
    while($xferitem=array_pop($xferarray)){
     if($xferitem != $primary){
      array_push($xferiparray,$xferitem);
     }
    }
    $xferip = implode(";",$xferiparray);
    $xferip=$primary . ';' . $xferip;
    break;
   default:
    $xferip="any";
   }
  
  
  if(!$localerror){
   // updatedb
   if(!$this->updateDb($primary,$xferip)){  
    $content .= $this->error;
   }else{
    // flag status='M' to be generated & reloaded
    $ret = $this->flagModified($this->zoneid);
    if(notnull($ret)){
     $result .= $ret;
    }else{
     // retrieve list of ns names
     $nsxnames = GetListOfServerNames();
     // retrieve list of ns addresses
     $nsxips = GetListOfServerIPs();
     // retrieve list of ns transfer IPs
     $nsxferips = GetListOfServerTransferIPs();
     $nsxferips = array_merge($nsxferips,$nsxips);
     $nsxferips = array_unique($nsxferips);
     $content .= '
     <h3 class=boxheader>' . 
     sprintf($l['str_secondary_zone_successfully_modified_on_x'],
     $config->sitename) . '</h3>
     ' . $l['str_secondary_after_modif_be_sure_to'] . ':<ul>
     <li>' . $l['str_secondary_after_modif_add_lines_to_zonefile'] . ':<br>
     <pre>
';
     while(list($notwanted,$nsxname) = each($nsxnames)){
      $content .= $this->zonename . '. IN NS ' . $nsxname . '.
';
     }
    $content .='
    </pre>
    
    <li>' . 
    $l['str_secondary_after_modif_add_to_configfile'] . ':
    <p >
    <pre>
// 
// ' . $l['str_secondary_after_modif_comment_in_sample_1'] . '
// ' . $l['str_secondary_after_modif_comment_in_sample_2'] . '
//
zone "' . $this->zonename . '" {
 type master;
 file "' . $this->zonename . '";
 allow-transfer {
  ';
  
  
  while(list($notwanted,$nsxferip) = each($nsxferips)){
   $content .= $nsxferip . '; ';
  }

  $content .= '
 };
};
</pre>
     <li>' . $l['str_secondary_after_modif_set_firewall'] .'
     <li>' . sprintf($l['str_secondary_after_modif_delegate_x_to'],
       $this->zonename) . ': ';
    
     reset($nsxnames);
     $serverlist ='';
     while(list($notwanted,$nsxname) = each($nsxnames)){
      $serverlist .= ' <b>' . $nsxname . '</b>; ';
     }
     $serverlist = substr($serverlist,0,-1);

     $content .= $serverlist . '
     </ul>
    
     <p>' . $l['str_secondary_reload_info'] . '<p>

     ';
    } // else no error
   } // else updatedb
  }else{
   // $error 
   // nothing has been modified, go back and solve troubles
   
   // or print form again
  
  }
  return $content;

 }



// Function updateDb($primary,$xferip)
 /**
  * Update DB with new secondary parameters
  *
  *@access public
  *@param string $primary IP(s) of primary name server(s)
  *@param string $xferip IP(s) allowed to do zone transfers
  *@return int 1 if success, 0 if error
  */
 Function updateDb($primary,$xferip){
   global $db,$l;

   // 27/03/02 not possible to change email address in this script
   // dns_confsecondary  
   if($this->creation==0){
     $query = "UPDATE dns_confsecondary SET masters='" . 
       $primary . "', xfer='" . $xferip . "' WHERE zoneid='" . 
       $this->zoneid . "'";
   }else{
     $query = "INSERT INTO dns_confsecondary (zoneid,masters,xfer)
       VALUES('" . $this->zoneid . "','" . $primary . "','" . $xferip . "')";
   }
   $res = $db->query($query);
   if($db->error()){
     $this->error=$l['str_trouble_with_db'];
     return 0;
   }
   return 1;
 }
}
?>
