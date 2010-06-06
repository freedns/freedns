<?

/*
	This file is part of XName.org project
	See	http://www.xname.org/ for details
	
	License: GPLv2
	See LICENSE file, or http://www.gnu.org/copyleft/gpl.html
	
	Author(s): Yann Hirou <hirou@xname.org>

*/

// Class Primary
// 	All functions for primary manipulations
/**
 * Class for all functions for primary manipulation
 *
 *@access public
 */
class Primary extends Zone {
	var $creation;
	var $serial;
	var $refresh;
	var $retry;
	var $expiry;
	var $minimum;
	var $defaultttl;
	var $xfer;
	var $user;

	var $mx;
	var $mxid;
	var $mxttl;
	var $ns;
	var $nsid;
	var $nsttl;
	var $a;
	var $attl;
	var $aip;
	var $aid;
	var $cname;
	var $cnameid;
	var $cnamettl;
	var $a6;
	var $a6ttl;
	var $a6id;
	var $aaaa;
	var $aaaattl;
	var $aaaaid;
	var $txt;
	var $txtttl;
	var $txtid;
	var $subns;
	var $subnsttl;
	var $subnsa;
	var $subnsid;
	var $www;
	var $wwwa;
	var $wwwi;
	var $wwwr;
	var $wwwid;
	var $wwwttl;
	var $delegatefromto;
	var $delegateuser;
	var $delegatettl;
	var $delegateid;
	var $ptr;
	var $ptrname;
	var $ptrid;
	var $ptrttl;
	var $srvname;
	var $srvpriority;
	var $srvweight;
	var $srvport;
	var $srvvalue;
	var $srvttl;
	var $srvid;
	var $nullarray;

	var $reversezone;
	var $ipv6;
		
	// instanciation
	/**
	 * Class constructor & data retrieval (use of Retrieve[Multi]Record)
	 *
	 *@access public
	 *@param string $zonename zone name
	 *@param string $zonetype zone type (must be 'M'aster)
	 *@param string $user class member user for current user
	 */
	Function Primary($zonename,$zonetype,$user){
		global $db,$l;
		$this->Zone($zonename,$zonetype);

		// fill in vars
		$res = $db->query("SELECT serial, refresh, retry, expiry, minimum, defaultttl, xfer
			FROM dns_confprimary WHERE zoneid='" . $this->zoneid . "'");
		$line = $db->fetch_row($res);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}
		if(!isset($line[1])){
			$this->creation = 1;
		}else{
			$this->creation = 0;
		}

		// set default SOA values
		$this->serial = $line[0];
		if($line[1]){
			$this->refresh = $line[1];
		}else{
			$this->refresh = 10800;
		}
		if($line[2]){
			$this->retry = $line[2];
		}else{
			$this->retry = 3600; 
		}
		if($line[3]){
			$this->expiry = $line[3];
		}else{
			$this->expiry = 604800;
		}
		if($line[4]){
			$this->minimum = $line[4];
		}else{
			$this->minimum = 10800;
		}
		if($line[5]){
			$this->defaultttl = $line[5];
		}else{
			$this->defaultttl = 86400;
		}
		
		$this->xfer = $line[6];
		$this->user=$user;
		if(ereg('.arpa$',$zonename) || ereg('.ip6.int$',$zonename)){
			$this->reversezone=1;
		}else{
			$this->reversezone=0;
		}
		// initialize arrays
		$this->ns = array();
		$this->nsid = array();
		$this->nsttl = array();
		$this->subns = array();
		$this->subnsttl = array();
		$this->subnsa = array();
		$this->subnsid = array();
		$this->cname = array();
		$this->cnameid = array();
		$this->cnamettl = array();

		if($this->reversezone){
			$this->ptr = array();
			$this->ptrname = array();
			$this->ptrid = array();
			$this->ptrttl = array();
			$this->delegatefromto = array();
			$this->delegateuser = array();
			$this->delegateid = array();
			$this->delegatettl = array();
		}else{
			$this->mx = array();
			$this->mxid = array();
			$this->mxttl = array();
			$this->a = array();
			$this->attl = array();
			$this->aip = array();
			$this->aid = array();
			$this->a6 = array();
			$this->a6ttl = array();
			$this->a6ip = array();
			$this->a6id = array();
			$this->aaaa = array();
			$this->aaaattl = array();
			$this->aaaaip = array();
			$this->aaaaid = array();
			$this->txt = array();
			$this->txtttl = array();
			$this->txtdata = array();
			$this->txtid = array();
			$this->srvname = array();
			$this->srvpriority = array();
			$this->srvweight = array();
			$this->srvport = array();
			$this->srvvalue = array();
			$this->srvttl = array();
			$this->srvid = array();
		$this->www = array();
		$this->wwwttl = array();
		$this->wwwa = array();
		$this->wwwi = array();
		$this->wwwr = array();
		$this->wwwid = array();
			$this->nullarray = array();
		}		
		// fill in with records
		$this->RetrieveRecords('NS',$this->ns,$this->nsid,$this->nsttl);
		$this->RetrieveMultiRecords('SUBNS',$this->subns,$this->subnsa,$this->nullarray,$this->nullarray,$this->nullaray,$this->subnsid,$this->subnsttl);
		$this->RetrieveRecords('CNAME',$this->cname,$this->cnameid,$this->cnamettl);
		
		if($this->reversezone){
			$this->RetrieveMultiRecords('PTR',$this->ptr,$this->ptrname,$this->nullarray,$this->nullarray,$this->nullarray,$this->ptrid,$this->ptrttl);
			$this->RetrieveMultiRecords('DELEGATE',$this->delegatefromto,$this->delegateuser,$this->nullarray,$this->nullarray,$this->nullarray,$this->delegateid,$this->delegatettl);
		}else{
			$this->RetrieveRecords('MX',$this->mx,$this->mxid,$this->mxttl);
			$this->RetrieveMultiRecords('A',$this->a,$this->aip,$this->nullarray,$this->nullarray,$this->nullarray,$this->aid,$this->attl);
			$this->RetrieveMultiRecords('A6',$this->a6,$this->a6ip,$this->nullarray,$this->nullarray,$this->nullarray,$this->a6id,$this->a6ttl);
			$this->RetrieveMultiRecords('AAAA',$this->aaaa,$this->aaaaip,$this->nullarray,$this->nullarray,$this->nullarray,$this->aaaaid,$this->aaaattl);
			$this->RetrieveMultiRecords('TXT',$this->txt,$this->txtdata,$this->nullarray,$this->nullarray,$this->nullarray,$this->txtid,$this->txtttl);
			$this->RetrieveMultiRecords('SRV',$this->srvname,$this->srvpriority,$this->srvweight,$this->srvport,$this->srvvalue,$this->srvid,$this->srvttl);
			$this->RetrieveMultiRecords('WWW',$this->www,$this->wwwa,$this->wwwi,$this->wwwr,$this->nullarray,$this->wwwid,$this->wwwttl);
		}
	}


// *******************************************************
	
	//	Function printModifyForm($params)
	/**
	 * returns a pre-filled form to modify primary records
	 *
	 *@access public
	 *@param array $params list of params
	 *@return string HTML pre-filled form
	 */
	Function printModifyForm($params){
		global $config,$lang;
		global  $l;
		global $hiddenfields;

		list($advanced,$ipv6,$nbrows) = $params;
		if($nbrows < 1){
			$nbrows=1;
		}
		$this->error="";
		$result = '';
			$deletecount = 0;
			// TODO use zoneid instead of zonename & zonetype
			$result .= '<form method="POST">
			 ' . $hiddenfields . '
			 <input type="hidden" name="zonename"
			 value="' . $this->zonename . '">
			 <input type="hidden" name="zonetype"
			 value="' . $this->zonetype . '">
			 
			<input type="hidden" name="modified" value="1">
			';
			// if advanced, say it to modified - in case
			// of temporary use of advanced interface, not in
			// user prefs.
			if($advanced){ 
				$result .= '<input type="hidden" name="advanced" value="1">
				';
			}
			
			
			if($advanced){
				// print global params ($TTL)
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_global_params'] . '</h3>
				<p>' . $l['str_primary_ttl_explanation'] . '</p>
				<table class="globalparams">
				<tr><td class="left">' . $l['str_primary_default_ttl'] . '</td>
				<td><input type="text" name="defaultttl" value="' . 
				$this->defaultttl . '"></td></tr>
				</table>
				';
				// print SOA params
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_soa_params'] . '</h3>
				<p>' . $l['str_primary_refresh_interval_expl'] . '</p>
				<table class="globalparams">
				<tr><td class="left">' . $l['str_primary_refresh_period'] . '</td>
				<td><input type="text" name="soarefresh" value="' .
				$this->refresh . '"></td></tr>
				</table>
				<p>' . $l['str_primary_retry_interval_expl'] . '</p>
				<table class="globalparams">
				<tr><td class="left">' . $l['str_primary_retry_interval'] . '
				</td><td><input type="text"	name="soaretry" value="' .
				$this->retry . '"></td></tr>
				</table>
				<p>' . $l['str_primary_expire_time_expl'] . '</p>
				<table class="globalparams">
				<tr><td class="left">' . 
				$l['str_primary_expire_time'] . '</td><td><input type="text"
				name="soaexpire" value="' .
				$this->expiry . '"></td></tr>
        </table>
				<p>' . $l['str_primary_negative_caching_expl'] . '</p>
				<table class="globalparams">
				<tr><td class="left">' . $l['str_primary_negative_caching'] . '</td>
				<td><input type="text" name="soaminimum" value="' .
				$this->minimum . '"></td></tr>
				</table>
				';
			}
		
			// retrieve NS names
			$nsxnames = GetListOfServerNames();
			$nsxnamesmandatory = GetListOfServerNames(1);
			if (count($this->ns) == 0)
				$nsxnamesoptional = array_diff($nsxnames, $nsxnamesmandatory);

			$result .= '
			<h3 class="boxheader">' . $l['str_primary_name_server_title'] . '</h3>
				<p>' .
				sprintf($l['str_primary_name_server_expl_with_sample_x'],
					$nsxnames[0]) .'</p>
				<table><tr><th>' .
				$l['str_primary_name'] . '</th>';
				if($advanced) { $result .= '<th>TTL</th>'; }
				$result .= '<th>' . $l['str_delete'] . '</th></tr>
				';
			
			$usednsxnames = array();
			$keys = array_keys($this->ns);
			while($key = array_shift($keys)){
				$result .= '<tr>
				<td>' . $key . '</td>
				';
				if($advanced){
					$result .= '
					<td>' . $this->PrintTTL($this->nsttl[$key]) . '</td>';
				}
				$result .= '<td>';
				// if ns is mandatory, never delete it
				$keytocompare = substr($key,0,-1);
				if(!in_array($keytocompare,$nsxnamesmandatory)){
					$deletecount++;
					$result .= '<input type="checkbox" name="delete' .
					 $deletecount .
					'" value="ns(' . $key . '-' . $this->nsid[$key] . ')">';
				}else{
					array_push($usednsxnames, $keytocompare);
				}
				$result .= "</td></tr>\n";
			}
			// compare $usednsxnames and $nsxnamesmandatory. If differences, add missing ones.
			$missingns = array_diff($nsxnamesmandatory,$usednsxnames);
			$nscounter=0;
			while($missingnsname = array_pop($missingns)){
				$nscounter++;
				$result .= '
				<tr>
				<td><input type="hidden" name="ns' . $nscounter .'" value="' 
				. $missingnsname . '.">' . $missingnsname . '.</td>
				';
				if($advanced){
					$result .= '
					<td><input type="text" name="nsttl' . $nscounter . 
					'" size="8" value="' . $l['str_primary_default'] . '"></td>';
				}
				$result .= '<td></td></tr>
				';
			}
			$nscounter++;
			for($count=1;$count <= $nbrows;$count++){
				$result .= '
					<tr>
					<td><input type="text" name="ns' . $nscounter . '" value="' .
					$nsxnamesoptional[$count] . '"></td>';
				if($advanced){
					$result .= '
					<td><input type="text" name="nsttl' . $nscounter . 
					'" size="8" value="' . $l['str_primary_default'] . '"></td>
					';
				}
				$nscounter++;
				$result .= '</tr>';
			}

			$result .= '
			</table>
			';

			if($this->reversezone){
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_ptr_title'] . '</h3>
				<p>
				<p>' . $l['str_primary_ptr_expl'] . '<br >
				' . $l['str_primary_ptr_sample'] . ': <br >
				<tt>' . $l['str_primary_ptr_sample_content'] . '</tt>
				<br >' . $l['str_primary_ptr_ipv6_note'] . '<p>
        <table>
				<tr><td class="left">' . 
				sprintf($l['str_primary_ptr_record_modify_a_x'],
				$config->sitename) . '</td><td><input type=checkbox
				name="modifya"></td></tr>
				</table>
				<table>
				<tr><th>' . sprintf($l['str_primary_ptr_ip_under_x'],
					$this->zonename) .'</th><th>'.$l['str_primary_name'].'</th>';
				if($advanced) { $result .= '<th>TTL</th>'; }
				$result .= '<th>' . $l['str_delete'] . '
	                                ';

				$counter=0;
				while(isset($this->ptr[$counter])){
					$deletecount++;
					// if advanced, print TTL fields
					$result .= '<tr>
							<td>' . $this->ptr[$counter] . '</td>
							<td>' . $this->ptrname[$counter] . '</td>';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->ptrttl[$counter]) . '</td>
						';
					}
					$result .= '
							<td><input type="checkbox" name="delete' . $deletecount .
							'" value="ptr(' . $this->ptr[$counter] . '/' .
							$this->ptrid[$counter] . '-' . $this->ptrname[$counter] . ')"></td>
							</tr>
					';
					$counter ++;
				}	

				$counter=0;
				$keys = array_keys($this->ptr);
				while($key = array_shift($keys)){
					$deletecount++;
					$counter++;
				}	
			
				$ptrcounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$ptrcounter++;			
					$result .= '
						<tr>
							<td>
							<input type="text" name="ptr' . $ptrcounter . '"></td>
							<td><input type="text" name="ptrname' . $ptrcounter . '"></td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="ptrttl' . $ptrcounter . '" size="8" value="' . 
							$l['str_primary_default'] . '"></td>
						';
					}
					$result .= '<td></td></tr>';
				}
				
				$result .='
				</td></tr></table>
				';

		if(!ereg('in-addr.arpa$',$this->zonename)) {
				$result .='
			<p>
			<h3 class="boxheader">' . $l['str_primary_sub_zones_title'] . '</h3>
				<p>
				' . sprintf($l['str_primary_sub_zones_expl_on_x_x'],$config->sitename,
					$this->zonename) . '
				</p>
        <table>
        <th>' . $l['str_primary_sub_zones_zone'] .'<th>NS';
        if($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
				';

				$counter=0;
				while(isset($this->subns[$counter])){
               if (strstr($this->subns[$counter], "-")!==FALSE){
                 $counter++; continue;
               }
					$deletecount++;
					$result .= '<tr>
							<td>' . $this->subns[$counter] . '</td>
							<td>' . $this->subnsa[$counter] . '</td>
							';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->subnsttl[$counter]) . '</td>
						';
					}
					$result .= '<td><input type="checkbox" name="delete' . $deletecount . 
							'" value="subns(' . $this->subns[$counter] . '/' . 
							$this->subnsid[$counter] . ')"></td></tr>
					';
					$counter ++;
				}	
			
				$subnscounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$subnscounter++;
					$result .= '
						<tr><td><input
						 type="text" name="subns' . $subnscounter . '"></td>
							<td><input type="text" name="subnsa' . $subnscounter . '">
								</td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="subnsttl' . $subnscounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
						';
					}
				}

				$result .= '
				</table>
				';

				
         }else{
				$result .='
				<p>
				<h3 class="boxheader">' . $l['str_primary_reverse_sub_zones_title'] . '</h3>
				<p>
				' . sprintf($l['str_primary_reverse_sub_zones_delegation_x'],
						$config->sitename) . '
				<br >
				' . sprintf($l['str_primary_reverse_sub_zones_delegation_expl_x_x'],
						$this->zonename, $config->sitename) . '<br >
				' . $l['str_primary_reverse_sub_zones_delegation_how'] . '
				</p>
				<table>
				<tr><th>' . $l['str_primary_reverse_sub_zone_range'] . '</th>
				    <th>' . sprintf($l['str_primary_reverse_sub_zone_delegated_to_user_x'],
						"...") .'</th>
				    <th>TTL</th>
				    <th>' . $l['str_delete'] .'</th>
				</tr>
				';

				$counter=0;
				while(isset($this->delegatefromto[$counter])){
					$deletecount++;
					list($from,$to) = split('-',$this->delegatefromto[$counter]);
					$result .= '<tr><td>' . $l['str_primary_reverse_sub_zone_range_from']
								. '&nbsp;' . $from . '
								' . $l['str_primary_reverse_sub_zone_range_to'] . '
								' . $to . '</td>
								<td>'.	$this->delegateuser[$counter] .
							'</td> 
							';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->delegatettl[$counter]) . '</td>
						';
					}
					$result .= '<td><input type="checkbox" name="delete' . $deletecount . 
							'" value="delegate(' .
							$this->delegatefromto[$counter] . ')"></td> 
							</tr>
					';
					$counter ++;
				}	
			
				$subnscounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$subnscounter++;
					$result .= '
						<tr><td>' . $l['str_primary_reverse_sub_zone_range_from'] . 
							'&nbsp;<input type="text" size="3" 
							name="delegatefrom' . $subnscounter . '">
							' . $l['str_primary_reverse_sub_zone_range_to'] . 
							'&nbsp;<input type="text" name="delegateto' . $subnscounter . '" size="3">
								</td><td>
								&nbsp;<input type="text" name="delegateuser' .
								$subnscounter . '" size="10"></td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="delegatettl' . $subnscounter . '" size="8" value="' . 
							$l['str_primary_default'] . '"></td>
						';
					}
				}
					$result .= '
					</table>
					';

          }


			}else{ // not reverse zone
				// MX
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_mail_exchanger_title'] . '</h3>
					<p>' . 
					sprintf($l['str_primary_mx_expl_with_sample_x'],
						$this->zonename) . '<br >' .
					$l['str_primary_mx_expl_for_pref'] . '
					</p>
	        <table><th>' . $l['str_primary_mx_pref'] .'
	        <th>' . $l['str_primary_name'];
	        if($advanced) { $result .= '<th>TTL'; }
	        $result .= '<th>' . $l['str_delete'] . '
				  ';
	
				$counter=0;
				$keys = array_keys($this->mx);
				while($key = array_shift($keys)){			
					$deletecount++;
					$result .= '<tr>
						<td>' . $this->mx[$key] . '</td>
						<td>' . $key . '</td>';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->mxttl[$key]) . '</td>
						';
					}
					$result .= '
							<td><input type="checkbox" name="delete' . $deletecount .
							'" value="mx(' . $key . '-' . $this->mxid[$key] . ')"></td></tr>
					';
					$counter++;
				}	
			
				$mxcounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$mxcounter++;
					$result .= '
					<tr><td><input type="text" size="5" maxlength="5"
							 name="pref' . $mxcounter . '"></td>
							<td><input type="text" name="mx' . $mxcounter . '"></td>';
					if($advanced){
						$result .= '
							<td><input type="text" name="mxttl' . $mxcounter . '" size="8" value="' . 
							$l['str_primary_default'] . '"></td>
						';
					}
					$result .= '<td>&nbsp;</td></tr>';
				}
				
				$result .= '
				</table>
				';
			
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_a_record_title'] . '</h3>
				<p>' .
				sprintf($l['str_primary_a_record_what_you_want_before_x_x_x'],
					$this->zonename, $this->zonename,
					$this->zonename) . '<br >
				' . $l['str_primary_a_record_expl'] . '
				</p>
				<table>
				<tr><td class="left">' . 
				sprintf($l['str_primary_a_record_modify_ptr_x'],
				$config->sitename) . '</td><td><input type=checkbox
				name="modifyptr"></td></tr>
        </table>
        <table>
        <th>' . $l['str_primary_name'] . '<th>IP';
        if($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
				';
	
				$counter=0;
				while(isset($this->a[$counter])){
					$deletecount++;
					// if advanced, print TTL fields
					$result .= '<tr>
							<td>' . $this->a[$counter] . '</td>
							<td>' . $this->aip[$counter] . '</td>';
					if($advanced){
						$result .= '<td>' . $this->PrintTTL($this->attl[$counter]) . '</td>
						';
					}
					$result .= '
							<td><input type="checkbox" name="delete' . $deletecount .
							'" value="a(' . $this->a[$counter] . '/' .
							$this->aid[$counter] . '-' . $this->aip[$counter] . ')"></td></tr>
					';
					$counter ++;
				}	

				$counter=0;
				$keys = array_keys($this->a);
				while($key = array_shift($keys)){
					$deletecount++;
					$counter++;
				}	
				$acounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$acounter++;
					$result .= '
					<tr>
							<td><input type="text" name="aname' . $acounter
							. '"></td>
							<td><input type="text" name="a' . $acounter . '"></td>';
					if($advanced){
						$result .= '<td><input type="text" name="attl' . $acounter . '" size="8" value="' . 
							$l['str_primary_default'] . '"></td>
						';
					}
				
					$result .= '<td>&nbsp;</td></tr>';
				}

				$result .= '
				</table>
				';
	
				if($this->user->ipv6){
					$result .= '
					<h3 class="boxheader">' . $l['str_primary_ipv6_record_title'] . 
					'</h3>
					<p>' . 
					sprintf($l['str_primary_ipv6_record_expl_before_x_x_x'],
						$this->zonename,$this->zonename,
						$this->zonename) . '<br >
					' . $l['str_primary_ipv6_record_expl_zone_and_round_robin'] . '
					</p>
          <table>
          <th>'. $l['str_primary_name'] . '<th>IPv6';
          if ($advanced) { $result .= '<th>TTL'; }
          $result .= '<th>' . $l['str_delete'] . '
					<!-- <tr><td colspan="4">' .
					sprintf($l['str_primary_ipv6_record_modify_reverse_x'],
					$config->sitename) . ' ? <input type=checkbox
					name="modifyptripv6"></td></tr>
					-->';
	
					$counter=0;
					while(isset($this->aaaa[$counter])){
						$deletecount++;
						// if advanced, print TTL fields
						$result .= '<tr>
								<td>' . $this->aaaa[$counter] . '</td>
								<td>' . $this->aaaaip[$counter] . '</td>';
						if($advanced){
							$result .= '<td>' . $this->PrintTTL($this->aaaattl[$counter]) . '</td>
							';
						}
						$result .= '
								<td><input type="checkbox" name="delete' . $deletecount .
								'" value="aaaa(' . $this->aaaa[$counter] . '/' .
								$this->aaaaid[$counter] . ')"></td></tr>
						';
						$counter ++;
					}	

					$counter=0;
					$keys = array_keys($this->aaaa);
					while($key = array_shift($keys)){
						$deletecount++;
						$counter++;
					}	
					$aaaacounter = 0;
					for($count=1;$count <= $nbrows;$count++){
						$aaaacounter++;
						$result .= '
						<tr><td><input type="text" name="aaaaname' . 
								$aaaacounter
								. '"></td>
								<td><input type="text" name="aaaa' . $aaaacounter . '"></td>';
						if($advanced){
							$result .= '
							<td><input type="text" name="aaaattl' . $aaaacounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
							';
						}
				
						$result .= '<td></td></tr>';
					}

					$result .= '
					</table>
					';
				} // end IPv6	
	
				
				
				$result .= '
				<h3 class="boxheader">' . $l['str_primary_cname_title'] . '</h3>
				<p>' . $l['str_primary_cname_expl'] . '
				</p>
				<table>
        <th>' . $l['str_primary_cname_alias'] .'
        <th>' . $l['str_primary_cname_name_a_record'];
        if($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
							';

				$counter=0;
				$keys = array_keys($this->cname);
				while($key = array_shift($keys)){
					$deletecount++;
					$result .= '<tr>
							<td>' . $key . '</td>
							<td> ' . $this->cname[$key] . '</td>';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->cnamettl[$key]) . '</td>
						';
					}
					$result .= '
							<td><input type="checkbox" name="delete' . $deletecount . 
							'" value="cname(' . $key . '-' . $this->cnameid[$key] . ')"></td></tr>
					';
				}	
			

				$cnamecounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$cnamecounter++;
					$result .= '
						<tr>
						<td><input
						 type="text" name="cname' . $cnamecounter . '"></td>
							<td><input 
							type="text" name="cnamea' . $cnamecounter . '">
						</td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="cnamettl' . $cnamecounter . '" 
						size="8" value="' . $l['str_primary_default'] . '"></td>
						';
					}
					$result .= '<td></td></tr>';
				}

				$result .= '
				</table>
				';
				
				// END CNAME

				// BEGIN TXT
				if($this->user->txtrecords){
					$result .= '
					<h3 class="boxheader">' . $l['str_primary_txt_record_title'] . 
					'</h3>
					<p>' . 
					sprintf($l['str_primary_txt_record_expl_x_x_x'],
						$this->zonename,$this->zonename,
						$this->zonename) . '
					</p>
          <table>
          <th>'. $l['str_primary_name'] . '<th>TXT';
          if ($advanced) { $result .= '<th>TTL'; }
          $result .= '<th>' . $l['str_delete'] ;
	
					$counter=0;
					while(isset($this->txt[$counter])){
						$deletecount++;
						// if advanced, print TTL fields
						$result .= '<tr>
								<td>' . $this->txt[$counter] . '</td>
								<td>' . $this->txtdata[$counter] . '</td>';
						if($advanced){
							$result .= '<td>' . $this->PrintTTL($this->txtttl[$counter]) . '</td>
							';
						}
						$result .= '
								<td><input type="checkbox" name="delete' . $deletecount .
								'" value="txt(' . $this->txt[$counter] . '/' .
								$this->txtid[$counter] . ')"></td></tr>
						';
						$counter ++;
					}	

					$counter=0;
					$keys = array_keys($this->txt);
					while($key = array_shift($keys)){
						$deletecount++;
						$counter++;
					}	
					$txtcounter = 0;
					for($count=1;$count <= $nbrows;$count++){
						$txtcounter++;
						$result .= '
						<tr><td><input type="text" name="txt' . 
								$txtcounter
								. '"></td>
								<td><input type="text" name="txtstring' . $txtcounter . '"></td>';
						if($advanced){
							$result .= '
							<td><input type="text" name="txtttl' . $txtcounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
							';
						}
				
						$result .= '<td></td></tr>';
					}

					$result .= '
					</table>
					';
				} 
				// END TXT


				// BEGIN SRV
				if($this->user->srvrecords){
					$result .= '
					<h3 class="boxheader">' . $l['str_primary_srv_record_title'] .
					'</h3>
					<p>' .
					sprintf($l['str_primary_srv_record_expl']) 
					. '
					</p>
          <table>
          <th>'. $l['str_primary_name'] . 
	 '<th>'. $l['str_primary_srv_priority'] .
	 '<th>'. $l['str_primary_srv_weight'] .
	 '<th>'. $l['str_primary_srv_port'] .
	 '<th>SRV';
          if ($advanced) { $result .= '<th>TTL'; }
          $result .= '<th>' . $l['str_delete'] ;

					$counter=0;
					while(isset($this->srvname[$counter])){
						$deletecount++;
						// if advanced, print TTL fields
						$result .= '<tr>
						<td>' . $this->srvname[$counter] . '</td>
						<td>' . $this->srvpriority[$counter] . '</td>
						<td>' . $this->srvweight[$counter] . '</td>
						<td>' . $this->srvport[$counter] . '</td>
						<td>' . $this->srvvalue[$counter] . '</td>';
						if($advanced){
							$result .= '<td>' . $this->PrintTTL($this->srvttl[$counter]) . '</td>
							';
						}
						$result .= '
						<td><input type="checkbox" name="delete' . $deletecount .
						'" value="srv(' . $this->srvname[$counter] . '-' .
						$this->srvid[$counter] . ')"></td></tr>
						';
						$counter ++;
					}

					$srvcounter = 0;
					for($count=1;$count <= $nbrows;$count++){
						$srvcounter++;
						$result .= '
						<tr><td><input type="text" name="srvname' . 
								$srvcounter
								. '"></td>
						<td><input type="text" size="3" name="srvpriority' . $srvcounter . '"></td>
						<td><input type="text" size="3" name="srvweight' . $srvcounter . '"></td>
						<td><input type="text" size="5" name="srvport' . $srvcounter . '"></td>
						<td><input type="text" name="srvvalue' . $srvcounter . '"></td>';
						if($advanced){
							$result .= '
							<td><input type="text" name="srvttl' . $srvcounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
							';
						}
						$result .= '<td></td></tr>';
					}
					$result .= '
					</table>
					';
				}
				// END SRV

				// BEGIN SUBNS
				
				$result .='
			<h3 class="boxheader">' . $l['str_primary_sub_zones_title'] . '</h3>
				<p>
				' . sprintf($l['str_primary_sub_zones_expl_on_x_x'],$config->sitename,
					$this->zonename) . '
				</p>
        <table>
        <th>' . $l['str_primary_sub_zones_zone'] .'<th>NS';
        if($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
				';

				$counter=0;
				while(isset($this->subns[$counter])){
					$deletecount++;
					$result .= '<tr>
							<td>' . $this->subns[$counter] . '</td>
							<td>' . $this->subnsa[$counter] . '</td>
							';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->subnsttl[$counter]) . '</td>
						';
					}
					$result .= '<td><input type="checkbox" name="delete' . $deletecount . 
							'" value="subns(' . $this->subns[$counter] . '/' . 
							$this->subnsid[$counter] . ')"></td></tr>
					';
					$counter ++;
				}	
			
				$subnscounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$subnscounter++;
					$result .= '
						<tr><td><input
						 type="text" name="subns' . $subnscounter . '"></td>
							<td><input type="text" name="subnsa' . $subnscounter . '">
								</td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="subnsttl' . $subnscounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
						';
					}
				}

				$result .= '
				</table>
				';

				// BEGIN WWW
				
				$result .='
			  <h3 class="boxheader">' . $l['str_primary_www_zones_title'] . '</h3>
				<p>
				' . sprintf($l['str_primary_www_zones_expl_on_x_x'],$config->sitename,
					$this->zonename) . '
				</p>
        <table>
        <th>' . $l['str_primary_www_zones_zone'] .'
        <th>' . $l['str_primary_www_address'] . '
        <th>' . $l['str_primary_www_zones_type'];
        if($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
				';

				$counter=0;
				while(isset($this->www[$counter])){
					$deletecount++;
					$result .= '<tr>
							<td>' . $this->www[$counter] . '</td>
							<td>' . $this->wwwa[$counter] . '</td>
              <td>' . ($this->wwwr[$counter]=='R' ? $l['str_primary_www_redirect'] : $l['str_primary_www_frame']) . '</td>
							';
					if($advanced){
						$result .= '
						<td>' . $this->PrintTTL($this->wwwttl[$counter]) . '</td>
						';
					}
					$result .= '<td><input type="checkbox" name="delete' . $deletecount . 
							'" value="www(' . $this->www[$counter] . '-' . 
							$this->wwwid[$counter] . ')"></td></tr>
					';
					$counter ++;
				}	
			
				$wwwcounter = 0;
				for($count=1;$count <= $nbrows;$count++){
					$wwwcounter++;
					$result .= '
						<tr><td><input
						 type="text" name="www' . $wwwcounter . '"></td>
							<td><input type="text" name="wwwa' . $wwwcounter . '">
								</td>
							<td><nobr><label><input type="radio" name="wwwr' . $wwwcounter . '" value="R">' . $l['str_primary_www_redirect'] . '</label></nobr><nobr><label>
							    <input type="radio" name="wwwr' . $wwwcounter . '" value="F">' .
$l['str_primary_www_frame'] . '</label></nobr></td>';
					if($advanced){
						$result .= '
						<td><input type="text" name="wwwttl' . $wwwcounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
						';
					}
				}

				$result .= '
				</table>
				';


			} // end not reverse zone
			


			$result .= '
			<h3 class="boxheader">' . $l['str_primary_allow_transfer_title'] . '</h3>
				<p>' . $l['str_primary_allow_transfer_expl'] . '</p>
  			<table>
				<tr><td>
				' . $l['str_primary_allow_transfer_ip_allowed'] . '
				<input id="xferip" type="text" name="xferip" value="';
				if($this->xfer=="any"){
					$result .= '';
				}else{
					$result .= $this->xfer;
				}
				$result .= '"></td></tr>
			  </table>

';

			// generate count$ vars to fill in httpvars array
			// so retrieveArgs will parse everything, including high numbered delete$

			for($fakecounter=0;$fakecounter < $deletecount;$fakecounter++){
				$result .= '<input type="hidden" name="count' . $fakecounter . '" value="1" >
					';
			}

$result .= '
			<input type="hidden" name="valid" value="1">
			<table id="submit"><tr><td>
			<input type="submit" value="' . $l['str_primary_generate_zone_button'] . '">
			<input type="reset" value="' . $l['str_primary_reset_form_button'] . '">
      </td></tr></table>
			</form>
		';
		
		return $result;
	}

  Function VerifyAllTTL($httpvars) {
   global $user, $l;
   if ($user->userid != 1) {
     return 1;
   }

    // parse all http vars 
    while (list($key, $val) = each($httpvars)) {
      if (ereg("[a-z]+ttl[0-9]+", $key)) {
         if ($val != $l['str_primary_default']) {
           $this->VerifySOA($val, -1, "TTL");
         }
      }
    }
    return !notnull($this->error);
  }

// *******************************************************	
//	Function PrintModified($params)
	/**
	 * Process params from primarymodifyform() form:
	 * for each record type execute addTYPERecord, execute updateSOA 
	 * and outputs result & config file
	 *
	 *@access public
	 *@param array $params contains $VARS ($HTTP_GET_VARS or POST), $xferip and SOA params
	 *@return string HTML result
	 */
	Function PrintModified($params){
		global $db;
		global $config;
		global  $html,$l;
	
list($VARS,$xferip,$defaultttl,$soarefresh,$soaretry,$soaexpire,$soaminimum,
								$modifyptr,$modifyptripv6,$modifya)=$params;
		$this->error="";
		$result = '';

    if (!$this->VerifyAllTTL($VARS))
      return sprintf($html->string_error, $this->error);
		$delete = retrieveArgs("delete", $VARS);
		$ns = retrieveArgs("ns", $VARS);
		$nsttl = retrieveArgs("nsttl",$VARS);
		$subns = retrieveArgs("subns", $VARS);
		$subnsa = retrieveArgs("subnsa", $VARS);
		$subnsttl = retrieveArgs("subnsttl",$VARS);
		if($this->reversezone){
			$ptr = retrieveArgs("ptr", $VARS);
			$ptrname = retrieveArgs("ptrname", $VARS);
			$ptrttl = retrieveArgs("ptrttl", $VARS);			
			$delegatefrom = retrieveArgs("delegatefrom", $VARS);
			$delegateto = retrieveArgs("delegateto", $VARS);
			$delegatettl = retrieveArgs("delegatettl",$VARS);
			$delegateuser = retrieveArgs("delegateuser",$VARS);
		}else{
			$mx = retrieveArgs("mx", $VARS);
			$mxttl = retrieveArgs("mxttl",$VARS);
			$pref = retrieveArgs("pref", $VARS);

			$aaaaname = retrieveArgs("aaaaname", $VARS);
			$aaaa = retrieveArgs("aaaa", $VARS);
			$aaaattl = retrieveArgs("aaaattl",$VARS);

			$aname = retrieveArgs("aname", $VARS);
			$a = retrieveArgs("a", $VARS);
			$attl = retrieveArgs("attl",$VARS);

			$srvname = retrieveArgs("srvname", $VARS);
			$srvpriority = retrieveArgs("srvpriority", $VARS);
			$srvweight = retrieveArgs("srvweight", $VARS);
			$srvport = retrieveArgs("srvport", $VARS);
			$srvvalue = retrieveArgs("srvvalue", $VARS);
			$srvttl = retrieveArgs("srvttl", $VARS);

			$www = retrieveArgs("www", $VARS);
			$wwwa = retrieveArgs("wwwa", $VARS);
			$wwwr = retrieveArgs("wwwr", $VARS);
			$wwwttl = retrieveArgs("wwwttl",$VARS);
		}
		$cname = retrieveArgs("cname", $VARS);
		$cnamea = retrieveArgs("cnamea", $VARS);
		$cnamettl = retrieveArgs("cnamettl",$VARS);
		
		$txt = retrieveArgs("txt", $VARS);
		$txtstring = retrieveArgs("txtstring", $VARS);
		$txtttl = retrieveArgs("txtttl",$VARS);

		$result .= $this->Delete($delete,$modifyptr,$modifya);
		$result .= $this->AddNSRecord($ns,$nsttl);
		if($this->reversezone){
			$result .= $this->AddPTRRecord($this->zoneid,$ptr,$ptrname,$ptrttl,$modifya);
			$result .= $this->AddDELEGATERecord($delegatefrom,$delegateto,$delegateuser,$delegatettl);
			$result .= $this->AddSUBNSRecord($subns,$subnsa,$subnsttl);
		}else{
			$result .= $this->AddMXRecord($mx,$pref,$mxttl);
			if($this->user->ipv6){
				$result .= $this->AddAAAARecord($this->zoneid,$aaaa,$aaaaname,$aaaattl,$modifyptripv6);
			}
			$result .= $this->AddARecord($this->zoneid,$a,$aname,$attl,$modifyptr);
			$result .= $this->AddSRVRecord($srvname,$srvpriority,$srvweight,$srvport,$srvvalue,$srvttl);
			$result .= $this->AddSUBNSRecord($subns,$subnsa,$subnsttl);
			$result .= $this->AddWWWRecord($www,$wwwa,$wwwr,$wwwttl);
		}
		$result .= $this->AddCNAMERecord($cname,$cnamea,$cnamettl);
		$result .= $this->AddTXTRecord($txt,$txtstring,$txtttl);
		
		if($this->UpdateSOA($xferip,$defaultttl,$soarefresh,$soaretry,$soaexpire,$soaminimum) == 0){
			$result .= sprintf($html->string_error, 
						$this->error
					) . '<br >';
		}else{
			$result .= sprintf($l['str_primary_new_serial_x'],
				$this->serial) . "<br>";
		
			// check for errors
			// - generate zone file in /tmp/zonename
			if(!$this->generateConfigFile()){
				$result .= sprintf($html->string_error,
							$this->error
						) . '<br >';
			}else{

				// - do named-checkzone $zonename /tmp/zonename and return result
				$checker = "$config->binnamedcheckzone ".escapeshellarg($this->zonename)." ".
					$this->tempZoneFile();
				$check = shell_exec(escapeshellcmd($checker));
				// if ok
				 if(preg_match("/OK/", $check)){
				// if($check == "OK\n"){
					$result .= $l['str_primary_internal_tests_ok'] . '<br>
					' . $l['str_primary_generated_config'] . ': 
					<p align="center">
					<pre>
					';
					// Print /tmp/zonename
					$fd = fopen($this->tempZoneFile(),"r");
					if ($fd == 0)
					{
						$result .= sprintf($html->string_error, 
									sprintf($l['str_can_not_open_x_for_reading'],
											$this->tempZoneFile())
								);
					}else{
						$result .= fread($fd, filesize($this->tempZoneFile()));
						fclose($fd);
					}
					$result .= "</pre>
					</p>&nbsp;";
					unlink($this->tempZoneFile());
					$result .= $this->flagModified($this->zoneid);
				}else{
					$result .= $l['str_primary_zone_error_warning'] . ': 
					<br>
					<pre>' . $check . '</pre>
					' . 
					sprintf($l['str_primary_error_if_engine_error_x_contact_admin_x'],
						'<a	href="mailto:' . $config->contactemail . '">',
						'</a>') . '
					<br>
					' . $l['str_primary_trouble_occured_when_checking'] . ':
					<p align="center">
					<pre>
					';
					// Print /tmp/zonename
					$fd = fopen($this->tempZoneFile(),"r");
					if ($fd == 0)
					{
						$result .= sprintf($html->string_error, 
									sprintf($l['str_can_not_open_x_for_reading'],
											$this->tempZoneFile())
								);
					}else{
						$result .= fread($fd, filesize($this->tempZoneFile()));
						fclose($fd);
					}
					$result .= "</pre>
					</p>&nbsp;";
				}
			}
		}	
		return $result;
	}
	



// *******************************************************	
	Function DeleteARecord($name,$id,$ip,$reverse){
		global $db;
		global  $html,$config,$l;

		$result = sprintf($l['str_primary_deleting_a_x'],
					stripslashes($name) . "/" . stripslashes($ip)) . "...";
	
		if(notnull($reverse)){
			// look for reverse
			// check if managed by user
			// etc...

			// if reverse IP is managed by current user, update PTR
			// else check if reverse IP delegation exists (ie as CNAME)
			$result .= $l['str_primary_looking_for_reverse'] . "...";
				// construct reverse zone
			$ipsplit = split('\.',stripslashes($ip));
			$reversezone="";
			$firstip=0;
			while($reverseipvalue = array_pop($ipsplit)){
				if($firstip){
					$reversezone .= $reverseipvalue . ".";
				}else{
					$firstip = $reverseipvalue;
				}
			}
			$reversezone .= "in-addr.arpa";
			if($this->Exists($reversezone,'P')){
				$alluserzones = $this->user->listallzones();
				$ismanaged=0;
				while($userzones = array_pop($alluserzones)){
					if(!strcmp($reversezone,$userzones[0])){
						$ismanaged=1;
					}
				}
				if($ismanaged){
					// modification allowed because same owner
					// looking for zoneid
					$result .= " " . $l['str_primary_zone_managed_by_you'];
					$query = "SELECT id FROM dns_zone 
						WHERE zone='" . $reversezone . "' AND zonetype='P'";
					$res = $db->query($query);
					$line = $db->fetch_row($res);
					$newzoneid=$line[0];
					if(strcmp($val1,$this->zonename)){
						$valtodelete = $name . "." . $this->zonename . ".";
					}else{
						$valtodelete = $name;
					}
					$query = "DELETE FROM dns_record 
						WHERE zoneid='" . $newzoneid . "'
						AND type='PTR' AND val1='" . $firstip . "' 
						AND val2='" . $valtodelete . "'";
	
					$res = $db->query($query);
					if($db->error()){
						$this->error=$l['str_trouble_with_db'];
					}else{
						$result .= " " . $this->flagModified($newzoneid);
						$this->updateSerial($newzoneid);
					}
				}else{
					// zone exists, but not managed by current user.
					// check for subzone managed by current user
					$result .= " " . 
						$l['str_primary_main_zone_not_managed_by_you'] . "...";
					$query = "SELECT zone,id FROM dns_zone WHERE
						userid='" . $this->user->userid . "'
						AND zone like '%." . $reversezone . "'";
					$res = $db->query($query);
					$newzoneid = 0;
					while($line = $db->fetch_row($res)){
						$range =array_pop(array_reverse(split('\.',$line[0])));
						list($from,$to) = split('-',$range);
						if(($firstip >= $from) && ($firstip <= $to)){
							$newzoneid=$line[1];
						}
					}
					if($newzoneid){
						if(strcmp($val1,$this->zonename)){
							$valtodelete = $name . "." . $this->zonename . ".";
						}else{
							$valtodelete = $name;
						}
						$query = "DELETE FROM dns_record 
							WHERE zoneid='" . $newzoneid . "'
							AND type='PTR' AND val1='" . $firstip . "' 
							AND val2='" . $valtodelete . "'";
		
						$res = $db->query($query);
						if($db->error()){
							$this->error=$l['str_trouble_with_db'];
						}else{
							$result .= " " . $this->flagModified($newzoneid);
							$this->updateSerial($newzoneid);
						}
					}else{
						// no zone found
						$result .= " " . 
							$l['str_primary_reverse_exists_but_ip_not_manageable'] . "<br >";
					}
											
				}
			}else{
				$result .=
					sprintf($l['str_primary_not_managed_by_x'],
						$config->sitename) . "<br >";
			}
		} // end if updatereverse
		if($id){
			$query = "DELETE FROM dns_record 
				WHERE zoneid='" . $this->zoneid . "'
				AND type='A' AND id='" . $id . "'";
		}else{
			$query = "DELETE FROM dns_record
				WHERE zoneid='" . $this->zoneid . "'
                                AND type='A' AND val1='" . addslashes($name) . "' AND val2='" . addslashes($ip) . "'";
		}
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			$result .= sprintf($html->string_error,
						$l['str_trouble_with_db']
					) . '<br >';
		}else{
			$result .= $l['str_primary_deleting_ok'] . "<br >\n";
		}
		return $result;
	}
	

// *******************************************************	
	Function DeletePTRRecord($ip,$id,$name,$reverse){
		global $db;
		global  $html,$config,$l;

		$result = sprintf($l['str_primary_deleting_ptr_x'],
					stripslashes($ip) . "/" . stripslashes($name)) . "...";
	
		if(notnull($reverse)){
		// if "normal" zone is managed by current user, update A 
		// remove all before first dot, and last char.
			$newzone = substr(substr(strstr(stripslashes($name),'.'),1),0,-1);
			$newa = substr(stripslashes($name),0,strlen(stripslashes($name)) - strlen($newzone) -2);
			// construct new IP
			// zone *.in-addr.arpa or *.ip6.int
			$iplist = split('\.',strrev(
									substr(
										strstr(
											substr(
												strstr(
													strrev(
														$this->zonename
													),
												'.'),
											1),
										'.'),
									1)
								)
							);
			$newip = "";
			$count = 0; // we have to count in case of zub-zones aa.bb.cc.dd-ee
			while($ipitem = array_pop($iplist)){
				$count++;
				if(count < 4){
					$newip .= "." . $ipitem;
				}
			}
			$newip = substr($newip,1) . "." . $ip;
			$result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "...";
			if($this->Exists($newzone,'P')){
				$alluserzones = $this->user->listallzones();
				$ismanaged=0;
				while($userzones = array_pop($alluserzones)){
					if(!strcmp($newzone,$userzones[0])){
						$ismanaged=1;
					}
				}
				if($ismanaged){
					// modification allowed because same owner
					// looking for zoneid
					$result .= " " . $l['str_primary_zone_managed_by_you'];
					$query = "SELECT id FROM dns_zone 
						WHERE zone='" . $newzone . "' AND zonetype='P'";
					$res = $db->query($query);
					$line = $db->fetch_row($res);
					$newzoneid=$line[0];
					$query = "DELETE FROM dns_record 
						WHERE zoneid='" . $newzoneid . "'
						AND type='A' AND val1='" . $newa . "' 
						AND val2='" . $newip . "'";
					$res = $db->query($query);
					if($db->error()){
						$this->error=$l['str_trouble_with_db'];
					}else{
						$result .= " " . $this->flagModified($newzoneid);
						$this->updateSerial($newzoneid);
					}
				}else{
					// zone exists, but not managed by current user.
					$result .= " " . 
					$l['str_primary_main_zone_not_managed_by_you'];
				}
			}else{
				$result .=
					sprintf($l['str_primary_not_managed_by_x'],
						$config->sitename) . "<br >";
			}
		}		

		$query = "DELETE FROM dns_record 
			WHERE zoneid='" . $this->zoneid . "'
			AND type='PTR' AND id='" . $id . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			$result .= sprintf($html->string_error,
						$l['str_trouble_with_db']
					) . '<br >';
		}else{
			$result .= $l['str_primary_deleting_ok'] . "<br >\n";
		}
		return $result;
	}

// *******************************************************
	
	//	Function DeleteMultipleARecords()
	/**
	 * Delete all the A records for a given name in current zone
	 *
	 *@access public
	 *@params name $name of the A records to delete
	 *@return result as a string text
	 */


	Function DeleteMultipleARecords($name){

		global $db,$html,$l;
		
		$query = "DELETE FROM dns_record 
			WHERE zoneid='" . $this->zoneid . "'
			AND type='A' AND val1='" . $name . "'";
						$result .= sprintf($l['str_primary_deleting_a_x'],
						stripslashes($newvalue)) . "...";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			$result .= sprintf($html->string_error,
						$l['str_trouble_with_db']
					) . '<br >';
		}else{
			$result .= $l['str_primary_deleting_ok'] . "<br >\n";
		}
		return $result;
	}


		
// *******************************************************	

//	Function Delete($delete)
	/**
	 * Takes list of items to be deleted, and process them
	 *
	 *@access public
	 *@param array $delete list of items cname(alias), a(name), ns(name), etc..
	 *@return string text of result (Deleting XXX record... Ok<br >)
	 */
	Function Delete($delete,$updatereverse,$updatea){
		global $db;
		global  $html,$l;
				
		$result = '';
		
		// for each delete entry, delete item cname(alias), a(name), ns(name),
		// mx(name)


		while(list($key,$value) = each($delete)){
			if($value != ""){
				$newvalue = preg_replace("/^.*\(([^\)]+)\)/","\\1", $value);
				
				// name of item to be deleted: 
				preg_match("/^(.*)\(/",$value,$item);
				$item = $item[1];

				switch($item){
					case "www":
						preg_match("/^(.*)-(.*)/",$newvalue,$item);
						$valname=$item[1];
						$valid=$item[2];
						$query = "DELETE FROM dns_record
						WHERE zoneid='" . $this->zoneid . "'
						AND type='WWW' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_www_x'],
						stripslashes($valname)) . "...";
						break;

					case "srv":
						preg_match("/^(.*)-(.*)/",$newvalue,$item);
						$valname=$item[1];
						$valid=$item[2];
						$query = "DELETE FROM dns_record
						WHERE zoneid='" . $this->zoneid . "'
						AND type='SRV' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_srv_x'],
						stripslashes($valname)) . "...";
						break;

					case "cname":
						preg_match("/^(.*)-(.*)/",$newvalue,$item);
						$valname=$item[1];
						$valid=$item[2];
						// www		IN		CNAME		toto.
						$query = "DELETE FROM dns_record 
								WHERE zoneid='" . $this->zoneid . "'
								AND type='CNAME' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_cname_x'],
						stripslashes($valname)) . "...";
						break;
					
					 
					case "a":
						// www		IN		A			IP
						preg_match("/^(.*)\/(.*)/",$newvalue,$item);
						$val1 = $item[1];
						$val2 = $item[2];
						if(preg_match("/^(.*)-(.*)/",$val2,$itembis)){
							$valid=$itembis[1];
							$valip=$itembis[2];
						}else{
							$valid=0;
							$valip=$val2;
						}
						$result .= $this->DeleteARecord($val1,$valid,$valip,$updatereverse);
						$query = "";
						break;


					case "aaaa":
						// www		IN		AAAA			IPv6
						preg_match("/^(.*)\/(.*)/",$newvalue,$item);
						$val1 = $item[1];
						$val2 = $item[2];
						$query = "DELETE FROM dns_record 
								WHERE zoneid='" . $this->zoneid . "'
								AND type='AAAA' AND id='" . $val2 . "'";
						$result .= sprintf($l['str_primary_deleting_aaaa_x'],
						stripslashes($val1)) . "...";
						break;

					case "txt":
						// www		IN		TXT			String
						preg_match("/^(.*)\/(.*)/",$newvalue,$item);
						$val1 = $item[1];
						$val2 = $item[2];
						$query = "DELETE FROM dns_record 
								WHERE zoneid='" . $this->zoneid . "'
								AND type='TXT' AND id='" . $val2 . "' ";
						$result .= sprintf($l['str_primary_deleting_txt_x'],
						stripslashes($val1)) . "...";
						break;

					
					
					case "ptr":
						// ip		IN		PTR			name
						preg_match("/^(.*)\/(.*)/",$newvalue,$item);
						$val1 = $item[1];
						$val2 = $item[2];
						preg_match("/^(.*)-(.*)/",$val2,$itembis);
						$valid=$itembis[1];
						$valname=$itembis[2];
						$result .= $this->DeletePTRRecord($val1,$valid,$valname,$updatea);
						$query = "";
						break;
						
					case "ns":
						preg_match("/^(.*)-(.*)/",$newvalue,$item);
						$valname=$item[1];
						$valid=$item[2];
						// 		IN		NS		name
						$query = "DELETE FROM dns_record 
							WHERE zoneid='" . $this->zoneid . "'
							AND type='NS' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_ns_x'],
						stripslashes($valname)) . "...";
						break;

					case "mx":
						preg_match("/^(.*)-(.*)/",$newvalue,$item);
						$valname=$item[1];
						$valid=$item[2];
						// * 		IN		MX		pref		name
						$query = "DELETE FROM dns_record 
						WHERE zoneid='" . $this->zoneid . "'
						AND type='MX' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_mx_x'],
						stripslashes($valname)) . "...";
						break;
					case "subns":
						// newzone	IN		NS		ns.name
						preg_match("/^(.*)\/(.*)/",$newvalue,$item);
						$val1 = $item[1];
						$val2 = $item[2];
						preg_match("/^(.*)-(.*)/",$val2,$itembis);
						$valname=$item[1];
						$valid=$item[2];
						
						$query = "DELETE FROM dns_record
						WHERE zoneid='" . $this->zoneid . "'
						AND type='SUBNS' AND id='" . $valid . "'";
						$result .= sprintf($l['str_primary_deleting_sub_zone_x'],
						stripslashes($valname)) . "...";
						break;
					case "delegate":
						// $newvalue: XX-YY
						list($from,$to) = split('-',$newvalue);
						// remove CNAMEs
						for($cnamecounter=$from;$cnamecounter<= $to; $cnamecounter++){
							$query = "DELETE FROM dns_record 
								WHERE zoneid='" . $this->zoneid . "'
								AND type='CNAME' AND val1='" . $cnamecounter . "'";
							$res = $db->query($query);
							if($db->error()){
								$this->error=$l['str_trouble_with_db'];
							}
						}
						
						// remove NS
						$query = "DELETE FROM dns_record WHERE zoneid='" . $this->zoneid . "'
								AND type='SUBNS' AND val1='" . $newvalue . "'";
						$res = $db->query($query);
						if($db->error()){
							$this->error=$l['str_trouble_with_db'];
						}
						
						// delete zone
						// use zoneDelete()
						$query = "SELECT userid FROM dns_zone WHERE zone='" 
								. $newvalue . "." . $this->zonename . "' AND zonetype='P'";
						$res = $db->query($query);
						$line=$db->fetch_row($res);
						$zonetodelete = new Zone($newvalue . "." . $this->zonename, 'P','',$line[0]);
						$zonetodelete->zoneDelete();
						
						// delete DELEGATE record
						$query = "DELETE FROM dns_record
									WHERE zoneid='" . $this->zoneid . "'
									AND type='DELEGATE' AND val1='" . $newvalue . "'";
						break;
				}
			}
			if(notnull($query)){
				$res = $db->query($query);
				if($db->error()){
					$this->error=$l['str_trouble_with_db'];
					$result .= sprintf($html->string_error,
								$l['str_trouble_with_db'] 
							) . '<br >';
				}else{
					$result .= $l['str_primary_deleting_ok'] . "<br >\n";
				}
			}
		}
		return $result;
	}


// *******************************************************

//	Function AddMXRecord($mx,$pref,$ttl)
	/**
	 * Add an MX record to the current zone
	 *
	 *@access private
	 *@param string $mx name of MX 
	 *@param int $pref preference value for this MX
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding MX Record... Ok)
	 */
	Function AddMXRecord($mx,$pref,$ttl){
		global $db, $html,$l;

		$result = '';
		// for each MX, add MX entry
		$i = 0;
		while(list($key,$value) = each($mx)){
			// value = name
			if($value != ""){
				if(!$this->checkMXName($value)){
					// check if matching A record exists ? NOT OUR JOB
					$result .= ' ' . 
						sprintf($html->string_error, 
							sprintf($l['str_primary_bad_mx_name_x'],
								stripslashes($value))
						) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					// if checkName, add zone.
					if(checkName($value)){
						$value .= "." . $this->zonename;
					}
					// if no trailing ".", add one. 
					if(strrpos($value, ".") != strlen($value) -1){
						$value .= ".";
					}
				
					// pref[$i] has to be an integer
					if(!$this->checkMXPref($pref[$i])){
						$result .= ' ' . 
							sprintf($html->string_error, 
								sprintf($l['str_primary_preference_for_mx_x_has_to_be_int'],
									stripslashes($value)) 
							) . '<br >';
						$this->error = $l['str_primary_data_error'];
					}else{
						if($pref[$i] == ""){
							$pref[$i] = 0;
						}
	
						// Check if record already exists
						$query = "SELECT count(*) FROM dns_record WHERE 
						zoneid='" . $this->zoneid . "' AND type='MX' 
						AND val1='" . $value . "'";
						$res = $db->query($query);
						$line = $db->fetch_row($res);
						if($line[0] == 0){
							$result .= sprintf($l['str_primary_adding_mx_x'],
							stripslashes($value)) . "...";
							$ttlval = $this->DNSTTL($ttl[$i]);
							$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
								VALUES ('" . $this->zoneid . "', 'MX', '" 
								. $value . "', '" . $pref[$i] . "','" . $ttlval . "')";
							$db->query($query);
							if($db->error()){
								$result .= ' ' . 
									sprintf($html->string_error, 
										$l['str_trouble_with_db']
									) . '<br >';
								$this->error = $l['str_trouble_with_db'];
							}else{
								$result .= $l['str_primary_ok'] . "<br >\n";
							}
						}else{ // record already exists
							$result .= 
								sprintf($l['str_primary_warning_mx_x_exists_not_overwritten'],
									stripslashes($value)) ."<br >\n";
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}



// *******************************************************

//	Function AddNSRecord($ns,$ttl)
	/**
	 * Add an NS record to the current zone
	 *
	 *@access private
	 *@param string $ns name of NS
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding NS Record... Ok)
	 */
	Function AddNSRecord($ns,$ttl){
		global $db,$html,$l;

		$result = '';
		$i=0;
		// for each NS, add NS entry
		while(list($key,$value) = each($ns)){
			// value = name
			if($value != ""){
				if(!$this->checkNSName($value)){
					$result .= sprintf($html->string_error, 
							sprintf($l['str_primary_bad_ns_x'],
								stripslashes($value))
							) . '<br >';
					$this->error = $l['str_primary_data_error'];
				}else{
					// if no trailing ".", add one
					if(strrpos($value, ".") != strlen($value) -1){
						$value .= ".";
					}
					
					// Check if record already exists
					$query = "SELECT count(*) FROM dns_record WHERE 
					zoneid='" . $this->zoneid . 
					"' AND type='NS' AND val1='" . $value . "'";
					$res = $db->query($query);
					$line = $db->fetch_row($res);
					if($line[0] == 0){
						$result .= sprintf($l['str_primary_adding_ns_x'],
						stripslashes($value)) . "...";
						$ttlval = $this->DNSTTL($ttl[$i]);
						$query = "INSERT INTO dns_record (zoneid, type, val1,ttl) 
							VALUES ('" . $this->zoneid . "', 'NS', '" 
							. $value . "','" . $ttlval . "')";
						$db->query($query);
						if($db->error()){
							$result .= sprintf($html->string_error,
									$l['str_trouble_with_db']) .
								'<br >';
							$this->error = $l['str_trouble_with_db'];
						}else{
							$result .= $l['str_primary_ok'] . "<br >\n";
						}
					}else{
						$result .= 
							sprintf($l['str_primary_warning_ns_x_exists_not_overwritten'],
									stripslashes($value)) . "<br >\n";
					}
				}
			}
			$i++;
		}
		return $result;
	}


// *******************************************************

//	Function AddARecord($zoneid,$a,$aname,$ttl,$updatereverse)
	/**
	 * Add an A record to given zone
	 *
	 *@access private
	 *@param int $zoneid id of zone
	 *@param string $a ip of A record
	 *@param string $aname name of A record
	 *@param int $ttl ttl value for this record
	 *@param int $updatereverse flag to update or not reverse zone
	 *@return string text of result (Adding A Record... Ok)
	 */
	Function AddARecord($zoneid,$a,$aname,$ttl,$updatereverse){
		global $db,$html,$config,$l;
		$result = '';
		// for each A, add A entry
		$i = 0;
		while(list($key,$value) = each($aname)){
			if($value != ""){
				if(! $this->checkAName($value) ){
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_a_x'], 
								stripslashes($value))
							) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					// a[$i] has to be an ip address
					if($a[$i] == ""){
						$result .= sprintf($html->string_error, 
								 sprintf($l['str_primary_no_ip_for'],
									stripslashes($value))
								) . "<br >\n";
						$this->error = $l['str_primary_data_error'];
					}else{
						if(!$this->checkAValue($a[$i])){
							$result .= sprintf($html->string_error, 
									sprintf($l['str_primary_x_ip_has_to_be_ip'],
									stripslashes($value))
									) . "<br >\n";
							$this->error = $l['str_primary_data_error'];
						}else{
							// Check if record already exists
							$query = "SELECT count(*) FROM dns_record WHERE 
							zoneid='" . $zoneid . "' AND type='A' 
							AND val1='" . $value . "'";
							$res = $db->query($query);
							$line = $db->fetch_row($res);
							if($line[0] == 0){
								// check if CNAME record not already exists
								$query = "SELECT count(*) FROM dns_record WHERE 
								zoneid='" . $zoneid . "' AND type='CNAME' 
								AND val1='" . $value . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_adding_a_x'],
									stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $zoneid . "', 
									'A', '" . $value . "', '" . $a[$i] . "','" . $ttlval . "')";
									$db->query($query);
									if($db->error()){
										$result .= 
										sprintf($html->string_error,
											$l['str_trouble_with_db']
										) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= $l['str_primary_ok'] . "<br >\n";
										
										if($updatereverse){									
											$result .= $this->UpdateReversePTR($a[$i],$value,'A');
										} // end if updatereverse
									} // end "primary OK"	
								}else{ // end check CNAME
									$result .= 
										sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
										stripslashes($value)) . "<br >\n";
								}
							}else{ // end check A
								
								// check if already same IP or not. If yes, do not
								// change anything 
								// if no, warn & assume it is round robin.
								$query .= " AND val2='" . $a[$i] . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_warning_a_x_exists_with_diff_value'],
													stripslashes($value)) . ' ';
									$result .= sprintf($l['str_primary_adding_a_x'],
									stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $zoneid . "', 
									'A', '" . $value . "', '" . $a[$i] . "','" . $ttlval . "')
									";
									$db->query($query);
									if($db->error()){
										$result .= sprintf($html->string_error,
												$l['str_trouble_with_db']
											) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= $l['str_primary_ok'] . "<br >\n";									
										if($updatereverse){	
											$result .= $this->UpdateReversePTR($a[$i],$value,'A');
										} // end updatereverse
									} // end primary ok

								}else{
									$result .= sprintf($l['str_primary_a_x_with_same_ip'],
									 				stripslashes($value)). '<br >';
								}
							}
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}
	


// *******************************************************

//	Function AddAAAARecord($aaaa,$aaaaname,$ttl,$updatereverse)
	/**
	 * Add an AAAA record to the current zone
	 *
	 *@access private
	 *@param string $aaaa ipv6 of AAAA record
	 *@param string $aaaaname name of AAAA record
	 *@param int $ttl ttl value for this record
	 *@param int $updatereverse flag to update or not reverse zone
	 *@return string text of result (Adding AAAA Record... Ok)
	 */
	Function AddAAAARecord($zoneid,$aaaa,$aaaaname,$ttl,$updatereverse){
		global $db,$config,$html,$l;

		$result = '';
		// for each AAAA, add AAAA entry
		$i = 0;
		while(list($key,$value) = each($aaaaname)){
			if($value != ""){
				if(! $this->checkAAAAName($value) ){
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_aaaa_bad_aaaa_x'],
								stripslashes($value))
							) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					// a[$i] has to be an ipv6 address
					if($aaaa[$i] == ""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_ipv6_for_x'],
									stripslashes($value)) 
								) . "<br >\n";
						$this->error = $l['str_primary_data_error'];
					}else{
						if(! $this->checkAAAAValue($aaaa[$i]) ){
							$result .= sprintf($html->string_error, 
									sprintf($l['str_primary_x_ip_has_to_be_ipv6'],
										stripslashes($value))
									) . "<br >\n";
							$this->error = $l['str_primary_data_error'];
						}else{
							// Check if record already exists
							$query = "SELECT count(*) FROM dns_record WHERE 
							zoneid='" . $this->zoneid . "' AND type='AAAA' 
							AND val1='" . $value . "'";
							$res = $db->query($query);
							$line = $db->fetch_row($res);
							if($line[0] == 0){
								// check if CNAME record not already exists
								$query = "SELECT count(*) FROM dns_record WHERE 
								zoneid='" . $this->zoneid . "' AND type='CNAME' 
								AND val1='" . $value . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_adding_aaaa_x'], 
									stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $this->zoneid . "', 
									'AAAA', '" . $value . "', '" . $aaaa[$i] . "','" . $ttlval . "')";
									$db->query($query);
									if($db->error()){
										$result .= sprintf($html->string_error,
												$l['str_trouble_with_db']
												) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= " " . $l['str_primary_ok'] . "<br >\n";

										if($updatereverse){
											$result .= $this->UpdateReversePTR($aaaa[$i],$value,'AAAA');
                                                                                } // end if updatereverse
									} // end "primary OK"	
								}else{ // end check CNAME
									$result .= sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
									stripslashes($value)) . "<br >\n";
								}
							}else{ // end check AAAA
								
								// check if already same IP or not. If yes, do not
								// change anything 
								// if no, warn & assume it is round robin.
								$query .= " AND val2='" . $aaaa[$i] . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .=
										sprintf($l['str_primary_warning_aaaa_x_exists_with_diff_value'],
												stripslashes($value)) . ' ';
									$result .= sprintf($l['str_primary_adding_aaaa_x'],
													stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $this->zoneid . "', 
									'AAAA', '" . $value . "', '" . $aaaa[$i] . "','" . $ttlval . "')
									";
									$db->query($query);
									if($db->error()){
										$result .= sprintf($html->string_error,
													$l['str_trouble_with_db']
												) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= $l['str_primary_ok'] . "<br >\n";
										if($updatereverse){
                                                                                        $result .= $this->UpdateReversePTR($aaaa[$i],$value,'AAAA');
                                                                                } // end updatereverse
									}	

								}else{
									$result .= sprintf($l['str_primary_aaaa_x_with_same_ip'],
													stripslashes($value)) . '<br >';
								}
							}
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}


// *******************************************************

//	Function AddPTRRecord($zoneid,$ptr,$ptrname,$ttl,$updatereverse)
	/**
	 * Add a PTR record to the $zoneid zone
	 * $zoneid param added for easiest reverse automatic filling.
	 *
	 *@access private
	 *@param string $zoneid id of zone in which PTR has to be added
	 *@param string $ptr ip of PTR record
	 *@param string $ptrname name of PTR record
	 *@param int $ttl ttl value for this record
	 *@param int $updatereverse to try to update or not matching A record
	 *@return string text of result (Adding PTR Record... Ok)
	 */
	Function AddPTRRecord($zoneid,$ptr,$ptrname,$ttl,$updatereverse){
		global $db, $html,$l,$config;
				
		$result = '';
		// for each PTR, add PTR entry
		$i = 0;
		while(list($key,$value) = each($ptr)){
			if($value != ""){
				if(! $this->checkPTRName($value) ){
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_ptr_x'],
								stripslashes($value))
							) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if($ptrname[$i] == ""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_name_for_x'],
									stripslashes($value)) 
								) . "<br >\n";
						$this->error = "Data error";
					}else{
                                                if (ereg('\.$', $value)) {
							$result .= sprintf($l['str_primary_x_name_ends_with_dot'], stripslashes($value)) . "<br >\n";
						}
						if(! $this->checkPTRValue($ptrname[$i]) ){
							$result .= sprintf($html->string_error, 
									sprintf($l['str_primary_x_name_has_to_be_fully_qualified_x'],	
										stripslashes($value),$ptrname[$i])
									) . "<br >\n";
							$this->error = $l['str_primary_data_error'];
						}else{
							// Check if record already exists
							$query = "SELECT count(*) FROM dns_record WHERE 
							zoneid='" . $zoneid . "' AND type='PTR' 
							AND val1='" . $value . "'";
							$res = $db->query($query);
							$line = $db->fetch_row($res);
							if($line[0] == 0){
								// check if CNAME record not already exists
								$query = "SELECT count(*) FROM dns_record WHERE 
								zoneid='" . $zoneid . "' AND type='CNAME' 
								AND val1='" . $value . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_adding_ptr_x'],
									stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $zoneid . "', 
									'PTR', '" . $value . "', '" . $ptrname[$i] . "','" . $ttlval . "')";
									$db->query($query);
									if($db->error()){
										$result .= sprintf($html->string_error, 
												$l['str_trouble_with_db']
												) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= $l['str_primary_ok'] . "<br >\n";
										
										// update associated A record
										if($updatereverse){
											// if "normal" zone is managed by current user, update A 
											// remove all before first dot, and last char.
											$newzone = substr(substr(strstr($ptrname[$i],'.'),1),0,-1);
											$newa = substr($ptrname[$i],0,strlen($ptrname[$i]) - strlen($newzone) -2);
											// construct new IP
											// zone *.in-addr.arpa or *.ip6.int
											$iplist = split('\.',strrev(
																	substr(
																		strstr(
																			substr(
																				strstr(
																					strrev(
																						$this->zonename
																					),
																				'.'),
																			1),
																		'.'),
																	1)
																)
															);
											$newip = "";
											$count = 0; // we have to count in case of zub-zones aa.bb.cc.dd-ee
											while($ipitem = array_pop($iplist)){
												$count++;
												if($count == 4) break;
                        if(ereg('[^0-9]', $ipitem)) break;
                        $newip .= "." . $ipitem;
											}
											$newip = substr($newip,1) . "." . $value;
											$result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "...";
											if($this->Exists($newzone,'P')){
												$alluserzones = $this->user->listallzones();
												$ismanaged=0;
												while($userzones = array_pop($alluserzones)){
													if(!strcmp($newzone,$userzones[0])){
														$ismanaged=1;
													}
												}
												if($ismanaged){
													// modification allowed because same owner
													// looking for zoneid
													$result .= " " . $l['str_primary_zone_managed_by_you'];
													$query = "SELECT id FROM dns_zone 
														WHERE zone='" . $newzone . "' AND zonetype='P'";
													$res = $db->query($query);
													$line = $db->fetch_row($res);
													$newzoneid=$line[0];
													$result .= " " . $this->AddARecord($newzoneid,array($newip),array($newa),
														array($l['str_primary_default']),NULL);
													if(!$this->error){
														$result .= " " . $this->flagModified($newzoneid);
														$this->updateSerial($newzoneid);
													}
												}else{
													// zone exists, but not managed by current user.
													$result .= " " . 
													$l['str_primary_main_zone_not_managed_by_you'];
												}
											}else{
												$result .=
													sprintf($l['str_primary_not_managed_by_x'],
														$config->sitename) . "<br >";
									 		}
										} // end update reverse
									} // update OK 
								}else{ // end check CNAME
									$result .= sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
									stripslashes($value)) . "<br >\n";
								}
							}else{ // end check A
								
								// check if already same name or not. If yes, do not
								// change anything 
								// if no, warn & assume it is round robin.
								$query .= " AND val2='" . $ptrname[$i] . "'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_warning_ptr_x_exists_with_diff_value'],
												stripslashes($value)) . ' ';
									$result .=  sprintf($l['str_primary_adding_ptr_x'],
									stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
									VALUES ('" . $zoneid . "', 
									'PTR', '" . $value . "', '" . $ptrname[$i] . "','" . $ttlval . "')
									";
									$db->query($query);
									if($db->error()){
										$result .= sprintf($html->string_error, 
										$l['str_trouble_with_db']) . "<br >\n";
										$this->error = $l['str_trouble_with_db'];
									}else{
										$result .= $l['str_primary_ok'] . "<br >\n";
										// update associated A record
										if($updatereverse){									
											// if "normal" zone is managed by current user, update A 
											// remove all before first dot, and last char.
											$newzone = substr(substr(strstr($ptrname[$i],'.'),1),0,-1);
											$newa = substr($ptrname[$i],0,strlen($ptrname[$i]) - strlen($newzone) -2);
											// construct new IP
											// zone *.in-addr.arpa or *.ip6.int
											$iplist = split('\.',strrev(
																	substr(
																		strstr(
																			substr(
																				strstr(
																					strrev(
																						$this->zonename
																					),
																				'.'),
																			1),
																		'.'),
																	1)
																)
															);
											$newip = "";
											$count = 0; // we have to count in case of zub-zones aa.bb.cc.dd-ee
											while($ipitem = array_pop($iplist)){
												$count++;
												if(count < 4){
													$newip .= "." . $ipitem;
												}
											}
											$newip = substr($newip,1) . "." . $value;
											$result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "...";
											if($this->Exists($newzone,'P')){
												$alluserzones = $this->user->listallzones();
												$ismanaged=0;
												while($userzones = array_pop($alluserzones)){
													if(!strcmp($newzone,$userzones[0])){
														$ismanaged=1;
													}
												}
												if($ismanaged){
													// modification allowed because same owner
													// looking for zoneid
													$result .= " " . $l['str_primary_zone_managed_by_you'];
													$query = "SELECT id FROM dns_zone 
														WHERE zone='" . $newzone . "' AND zonetype='P'";
													$res = $db->query($query);
													$line = $db->fetch_row($res);
													$newzoneid=$line[0];
													$result .= " " . $this->AddARecord($newzoneid,array($newip),array($newa),
														array($l['str_primary_default']),NULL);
													if(!$this->error){
														$result .= " " . $this->flagModified($newzoneid);
														$this->updateSerial($newzoneid);
													}
												}else{
													// zone exists, but not managed by current user.
													$result .= " " . 
													$l['str_primary_main_zone_not_managed_by_you'];
												}
											}else{
												$result .=
													sprintf($l['str_primary_not_managed_by_x'],
														$config->sitename) . "<br >";
									 		}
										} // end update reverse
										
									}	

								}else{
									$result .= sprintf($l['str_primary_warning_ptr_x_already_exists_not_overwritten'],
													stripslashes($value)) . '<br >';
								}
							}
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}



	
	
		
// *******************************************************

//	Function AddCNAMERecord($cname,$cnamea,$ttl)
	/**
	 * Add an CNAME record to the current zone
	 *
	 *@access private
	 *@param string $cname name of CNAME record
	 *@param string $cnamea record pointed by this CNAME record
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding CNAME Record... Ok)
	 */
	Function AddCNAMERecord($cname,$cnamea,$ttl){
		global $db, $html,$l;
				
				// for each CNAME, add CNAME entry
		$i = 0;
		$result = "";
		while(list($key,$value) = each($cname)){
			if($value != ""){	
				if(! $this->checkCNAMEName($value) || !$this->checkCNAMEValue($cnamea[$i]) ){
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_cname_x'],
								stripslashes($value))
							) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if($cnamea[$i] ==""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_record_x'],
									stripslashes($value))
								) . "<BR >\n";
						$this->error = 1;
					}else{
						// Check if record already exists
						$query = "SELECT count(*) FROM dns_record WHERE 
						zoneid='" . $this->zoneid . "' AND type='CNAME' 
						AND val1='" . $value . "'";
						$res = $db->query($query);
						$line = $db->fetch_row($res);
						if($line[0] == 0){
							// check if A record don't already exist
							$query = "SELECT count(*) FROM dns_record WHERE 
							zoneid='" . $this->zoneid . "' AND type='A' 
							AND val1='" . $value . "'";
							$res = $db->query($query);
							$line = $db->fetch_row($res);
							if($line[0] == 0){
								$result .= sprintf($l['str_primary_adding_cname_x'],
								stripslashes($value)) . "...";
								$ttlval = $this->DNSTTL($ttl[$i]);
								$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
								VALUES ('" . $this->zoneid . "', 'CNAME', '"
								 . $value . "', '" . $cnamea[$i] . "','" . $ttlval . "')
								";
								$db->query($query);
								if($db->error()){
									$result .= sprintf($html->string_error,
											$l['str_trouble_with_db']
										) . '<br >';
									$this->error = $l['str_trouble_with_db'];
								}else{
									$result .= $l['str_primary_ok'] . "<br >\n";	
								}
							}else{ // A record present
								$result .= sprintf($l['str_primary_warning_a_x_exists_not_overwritten'],
								stripslashes($value)) . "<br >\n";
							}							
						}else{
							$result .= sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
											stripslashes($value)) . "<br >\n";
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}

		
// *******************************************************

//	Function AddTXTRecord($txt,$txtstring,$ttl)
	/**
	 * Add a TXT record to the current zone
	 *
	 *@access private
	 *@param string $txt name of TXT record
	 *@param string $txtstring string pointed by this TXT record
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding TXT Record... Ok)
	 */
	Function AddTXTRecord($txt,$txtstring,$ttl){
		global $db, $html,$l;
				
				// for each TXT, add TXT entry
		$i = 0;
		$result = "";
		while(list($key,$value) = each($txt)){
			if($value != ""){	
				if(!$this->checkTXTName($value)){
				$result .= "VALUE: $value";
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_txt_x'],
							stripslashes($value))
						) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if($txtstring[$i] ==""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_record'],
								stripslashes($value))
							) . "<br >\n";
						$this->error = 1;
					}else{
						// Check if CNAME record already exists
						$query = "SELECT count(*) FROM dns_record WHERE 
						zoneid='" . $this->zoneid . "' AND type='CNAME' 
						AND val1='" . $value . "'";
						$res = $db->query($query);
						$line = $db->fetch_row($res);
						if($line[0] == 0){
							$result .= sprintf($l['str_primary_adding_txt_x'],
							stripslashes($value)) . "...";
							// suppress all quotes, and add new ones
							$newstring = preg_replace("/\"/","",stripslashes($txtstring[$i]));
							$newstring = preg_replace("/'/","''",$newstring);
							// suppress all remaining "\"
							$newstring = preg_replace("/\\\/","",$newstring);
							$ttlval = $this->DNSTTL($ttl[$i]);
							$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
							VALUES ('" . $this->zoneid . "', 'TXT', '"
							 . $value . "', '\"" . $newstring . "\"','" . $ttlval . "')
							";
							$db->query($query);
							if($db->error()){
								$result .= sprintf($html->string_error,
										$l['str_trouble_with_db'] 
									 ) . '<br >';
								$this->error = $l['str_trouble_with_db'];
							}else{
								$result .= $l['str_primary_ok'] . "<br >\n";	
							}
						}else{
							$result .= sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
											stripslashes($value)) . "<br >\n";
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}

// *******************************************************

//	Function AddWWWRecord($www,$wwwstring,$wwwtype,$ttl)
	/**
	 * Add a pseudo WWW record to the current zone
	 *
	 *@access private
	 *@param string $www name of WWW record
	 *@param string $wwwstring address pointed by this WWW record
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding WWW Record... Ok)
	 */
	Function AddWWWRecord($www,$wwwstring,$wwwr,$ttl){
		global $db, $html,$l,$config;
				
				// for each WWW, add WWW entry
		$i = 0;
		$result = "";
		while(list($key,$value) = each($www)){
			if($value != ""){	
				if(!$this->checkWWWName($value)){
				$result .= "VALUE: $value";
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_www_x'],
							stripslashes($value))
						) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if($wwwstring[$i] ==""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_record'],
								stripslashes($value))
							) . "<br >\n";
						$this->error = 1;
					}else if (!$this->checkWWWValue($wwwstring[$i])){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_bad_www_value_x'],
								stripslashes($wwwstring[$i]))
							) . "<br >\n";
						$this->error = 1;
					}else{
						// Check if record already exists
						$query = "SELECT count(*) FROM dns_record WHERE 
						zoneid='" . $this->zoneid . "'
						AND val1='" . $value . "' AND type IN ('CNAME','A','WWW')";
						$res = $db->query($query);
						$line = $db->fetch_row($res);
						if($line[0] == 0){
							$result .= sprintf($l['str_primary_adding_www_x_x'],
							($wwwr[$i]=="R"?$l['str_primary_www_redirect']:$l['str_primary_www_frame']),
							stripslashes($value)) . "...";
							// suppress all quotes, and add new ones
							$newstring = mysql_escape_string($wwwstring[$i]);
/*							$newstring = preg_replace("/\"/","",stripslashes($wwwstring[$i]));
							$newstring = preg_replace("/'/","''",$newstring);
							// suppress all remaining "\"
							$newstring = preg_replace("/\\\/","",$newstring); */
							$ttlval = $this->DNSTTL($ttl[$i]);
							$query = "INSERT INTO dns_record (zoneid, type, val1, val2, val3, val4, ttl) 
							VALUES ('" . $this->zoneid . "', 'WWW', '"
							 . $value . "', '" . $newstring . "',
                      '" . $config->webserverip . "',
                      '" . ($wwwr[$i]=="R"?'R':'F') . "',
                      '" . $ttlval . "')
							";
							$db->query($query);
							if($db->error()){
								$result .= sprintf($html->string_error,
										$l['str_trouble_with_db'] 
									 ) . '<br >';
								$this->error = $l['str_trouble_with_db'];
							}else{
								$result .= $l['str_primary_ok'] . "<br >\n";	
							}
						}else{
							$result .= sprintf($l['str_primary_warning_www_x_exists_not_overwritten'],
											stripslashes($value)) . "<br >\n";
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}

// *******************************************************

//	Function AddSUBNSRecord($subns,$subnsa,$ttl)
	/**
	 * Add a zone delegation to the current zone
	 *
	 *@access private
	 *@param string $subns name of subzone
	 *@param string $subnsa name of NS server
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding zone NS Record... Ok)
	 */
	Function AddSUBNSRecord($subns,$subnsa,$ttl){
		global $db, $html,$l;

		// for each SUBNS, add NS entry
		$i = 0;
		$result = "";
		while(list($key,$value) = each($subns)){
			if($value != ""){	
				if(!$this->checkSUBNSName($value)){
					$result .= sprintf($html->string_error,
							sprintf($l['str_bad_zone_name_x'],
							' ' . stripslashes($value))
						) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if($subnsa[$i] ==""){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_no_ns_x'], 
								stripslashes($value))
							) . "<br >\n";
						$this->error = 1;
					}else{
						if( ! $this->checkSUBNSValue($subnsa[$i]) ){
							$result .= sprintf($html->string_error,
								sprintf($l['str_primary_bad_ns_x'],
		                                                       stripslashes($subnsa[$i]))
							) . "<br >\n";
							$this->error = 1;
						}
					}
					if(!$this->error){
						// Check if record already exists
						// if yes, no problem - multiple different NS possible
						$result .= sprintf($l['str_primary_adding_zone_ns_x'],
						stripslashes($value)) . "...";
						$query = "SELECT count(*) FROM dns_record 
						WHERE zoneid='" . $this->zoneid . "' AND type='SUBNS' 
						AND val1='" . $value . "' AND val2='" . $subnsa[$i] . "'";
						$res=$db->query($query);
						$line = $db->fetch_row($res);
						if($db->error()){
							$result .= sprintf($html->string_error,
										$l['str_trouble_with_db'] 
								) . '<br >';
							$this->error = $l['str_trouble_with_db'];
						}else{
							if($line[0]==0){
								$ttlval=$this->DNSTTL($ttl[$i]);
								$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
								VALUES ('" . $this->zoneid . "', 'SUBNS', '"
								 . $value . "', '" . $subnsa[$i] . "','" . $ttlval . "')
								";
								$db->query($query);
								if($db->error()){
									$result .= sprintf($html->string_error,
											$l['str_trouble_with_db']
										) . '<br >';
									$this->error = $l['str_trouble_with_db'];
								}else{
									$result .= $l['str_primary_ok'] . "<br >\n";	
								}
							}else{
								$result .=sprintf($l['str_primary_warning_ns_x_exists_not_overwritten'],
											stripslashes($value)) . "<br >\n";
							}
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}


// *******************************************************

//	Function AddDELEGATERecord($delegatefrom,$delegateto,$delegateuser,$ttl)
	/**
	 * Add a delegation to the current zone
	 *
	 *@access private
	 *@param string $delegatefrom lower limit of range
	 *@param string $delegateto upper limit of range
	 *@param string $delegateuser user to delegate zone to
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding DELEGATE Record... Ok)
	 */
	Function AddDELEGATERecord($delegatefrom,$delegateto,$delegateuser,$ttl){
		global $db,$html,$l;

		$i = 0;
		$result = "";
		while(list($key,$value) = each($delegatefrom)){
			if(notnull($value)){
				$result .= sprintf($l['str_primary_adding_delegate_x'],
				stripslashes($value),$delegateto[$i]) . "...";
				if(!ereg('^[0-9]+$',$value)){
					$result .= sprintf($html->string_error,
							sprintf($l['str_primary_bad_lower_limit_x'],
							stripslashes($value))
						) . "<br >\n";
					$this->error = $l['str_primary_data_error'];
				}else{
					if(!ereg('^[0-9]+$',$delegateto[$i])||$delegateto[$i]>255){
						$result .= sprintf($html->string_error,
								sprintf($l['str_primary_bad_upper_limit_x'],
								stripslashes($delegateto[$i]))
							) . "<br >\n";
						$this->error = $l['str_primary_data_error'];
					}else{
						// check if lower if below upper
						if(!($value <= $delegateto[$i])){
							$result .= sprintf($html->string_error,
									sprintf($l['str_primary_bad_limits_x_x'],
									stripslashes($value),stripslashes($delegateto[$i])) 
								) . "<br >\n";
							$this->error = $l['str_primary_data_error'];		
						}else{
							if(!notnull($delegateuser[$i])){
								$result .= sprintf($html->string_error, 
										$l['str_primary_no_user_for_delegation'] 
									) . '<br >';
								$this->error = $l['str_primary_data_error'];
							}else{
								// check if user is in DB or not
								$newuserid=$this->user->RetrieveId(addslashes($delegateuser[$i]));
								if($this->user->error){
									$result .= sprintf($html->string_error,
											$this->user->error
										) . '<br >';
									$this->error = $this->user->error;
								}else{
									if(!$newuserid){
										$result .= sprintf($html->string_error, 
											sprintf($l['str_primary_delegate_user_x_doesnot_exist'],
											stripslashes($delegateuser[$i]))
											) . '<br >';
										$this->error = $l['str_primary_data_error'];
									}else{ // user exists
										// check if items inside this range are already registered or not
										$query = "SELECT val1 FROM dns_record WHERE zoneid='" .
											$this->zoneid . "' AND type='DELEGATE'";
										$res=$db->query($query);
										if($db->error()){
											$result .= sprintf($html->string_error,
													$l['str_trouble_with_db']
												) . '<br >';
											$this->error = $l['str_trouble_with_db'];
										}else{
											while($line = $db->fetch_row($res)){
												list($from,$to)=split('-',$line[0]);
												if(
												(($from <= $value) && ($to >= $value)) ||
												(($from >= $value) && ($from <= $delegateto[$i]))
												){
													$result .= sprintf($html->string_error,
															sprintf($l['str_primary_delegate_bad_limits_x_x_overlaps_existing_x_x'],
															stripslashes($value),stripslashes($delegateto[$i]), 
															$from,$to)
														) . "<br >\n";
													$this->error = $l['str_primary_data_error'];
												}
											}
											if(!$this->error){
												$ttlval = $this->DNSTTL($ttl[$i]);
												$query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl) 
												VALUES ('" . $this->zoneid . "', 'DELEGATE', '"
												 . $value . "-" . $delegateto[$i] . "','" .
												 stripslashes($delegateuser[$i]) . "','" . $ttlval . "')
												";
												$db->query($query);
												if($db->error()){
													$result .= sprintf($html->string_error,
															$l['str_trouble_with_db']
														) . '<br >';
													$this->error = $l['str_trouble_with_db'];
												}else{
													// create zone, affect it to delegateuser
													// Can NOT use standard create way because
													// of EXIST check. BUG: can not insert userlog
													$query = "INSERT INTO dns_zone 
																(zone,zonetype,userid)
													VALUES ('".$value . "-" . $delegateto[$i] . "." . 
													 $this->zonename."','P','".$newuserid."')";
													$res = $db->query($query);
													if($db->error()){
														$this->error = $l['str_trouble_with_db'];
													}else{
														// create dns_confprimary records
														// NO - user has to modify it manually
														// create NS records
														$nskeys = array_keys($this->ns);
														while($nskey = array_shift($nskeys)){
															$query = "INSERT INTO dns_record
																(zoneid,type,val1,val2,ttl)
																VALUES ('" . $this->zoneid . "',
																'SUBNS','" . $value . "-" . $delegateto[$i]
																. "','" . $nskey . "','" .
																	$this->nsttl[$nskey] . "')";
															$res = $db->query($query);
															if($db->error()){
																$this->error = $l['str_trouble_with_db'];
															}
														}

														// create CNAME records
														$newzone = new Zone($value . "-" . $delegateto[$i] . "." . 
													 		$this->zonename, 'P','',$newuserid);
														$newzone->retrieveID($value . "-" . $delegateto[$i] . "." . 
													 		$this->zonename,'P');
														
														for($cnamecounter=$value;
																$cnamecounter <= $delegateto[$i];
																$cnamecounter++){
															$query = "INSERT INTO dns_record 
																		(zoneid, type, val1, val2,ttl) 
																		VALUES 
																		('" . $this->zoneid . "', 
																		'CNAME', '" . $cnamecounter . "',
																		'" . $cnamecounter . "." . $value . "-" . $delegateto[$i] . "." . 
																 		$this->zonename . ".',
																		'" . $ttlval . "')
																		";
															$db->query($query);
															if($db->error()){
																$result .= sprintf($html->string_error, 
																	$l['str_trouble_with_db'] 
																	) . '<br >';
																$this->error = $l['str_trouble_with_db'];
															}
														} // end for each CNAME
														if(!$this->error){
															$result .= $l['str_primary_ok'] . "<br >\n";
														}
													}
												}
											} // no error
										} // else db error
									} // user exists
								} // no db error
							} // delegateuser not null								
						} // $from < $to
					} // bad upper limit
				} // bad lower limit
			} // not null
			$i++;
		} // while
		return $result;
	}

// *******************************************************
//	Function AddSRVRecord($srvname,$srvpriority,$srvweight,$srvport,$srvvalue,$ttl)
	/**
	 * Add an SRV record to the current zone
	 *
	 *@access private
	 *@param string $srvname name of SRV record
	 *@param int $srvpriority priority of this srv record
	 *@param int $srvweight weight of this srv record
	 *@param int $srvport port of this srv record
	 *@param string $srvvalue value for this record
	 *@param int $ttl ttl value for this record
	 *@return string text of result (Adding SRV Record... Ok)
	 */
	Function AddSRVRecord($srvname,$srvpriority,$srvweight,$srvport,$srvvalue,$ttl){
		global $db,$html,$l;
		
		// for each SRV, add SRV entry
		$i = 0;
		$result = "";
		while(list($key,$value) = each($srvname)){
			if($value != ""){
				if(!$this->checkSRVName($value) || !$this->checkSRVPort($srvport[$i]) || !$this->checkSRVValue($srvvalue[$i])){
                                        $result .= sprintf($html->string_error,
                                                        sprintf($l['str_primary_bad_srvname_x'],
                                                                stripslashes($value))
                                                        ) . "<br >\n";
                                        $this->error = $l['str_primary_data_error'];
				}else{
					if($srvvalue[$i] == ""){
                                                $result .= sprintf($html->string_error,
                                                                sprintf($l['str_primary_no_record'],
                                                                        stripslashes($value))
                                                                ) . "<br >\n";
                                                $this->error = 1;
                                        }else{
						if(!$this->checkSRVPriority($srvpriority[$i])){
        	                                        $result .= ' ' .
                	                                        sprintf($html->string_error,
                        	                                        sprintf($l['str_primary_priority_for_srv_x_has_to_be_int'],
                                	                                        stripslashes($value))
                                        	                ) . '<br >';
                                                	$this->error = $l['str_primary_data_error'];
                                        	}else{
							if($srvpriority[$i] == ""){
								$srvpriority[$i] = 0;
							}
							if(!$this->checkSRVWeight($srvweight[$i])){
								$result .= ' ' .
 	                                                               sprintf($html->string_error,
        	                                                                sprintf($l['str_primary_weight_for_srv_x_has_to_be_int'],
                	                                                                stripslashes($value))
                        	                                        ) . '<br >';
                                	                        $this->error = $l['str_primary_data_error'];
                                        	        }else{
								if($srvweight[$i] == ""){
        	                                                        $srvweight[$i] = 0;
								}
								// Check if record already exists
                                                		$query = "SELECT count(*) FROM dns_record WHERE
                                                		zoneid='" . $this->zoneid . "' AND type='SRV'
                                                		AND val1='" . $value . "' AND val4='" . $srvport[$i] . "' and val5='" . $srvvalue[$i] ."'";
								$res = $db->query($query);
								$line = $db->fetch_row($res);
								if($line[0] == 0){
									$result .= sprintf($l['str_primary_adding_srv_x'],
									 stripslashes($value)) . "...";
									$ttlval = $this->DNSTTL($ttl[$i]);
									$query = "INSERT INTO dns_record (zoneid, type, val1, val2, val3,val4,val5,ttl)
										VALUES ('" . $this->zoneid . "', 'SRV', '"
										. $value . "', '" . $srvpriority[$i] . "','" . 
										$srvweight[$i] . "','" . $srvport[$i] . "','" . 
										$srvvalue[$i] . "','" . $ttlval . "')";
									$db->query($query);
 		                                                       if($db->error()){
                		                                                $result .= ' ' .
                                		                                        sprintf($html->string_error,
                                                		                                $l['str_trouble_with_db']
                                                                		        ) . '<br >';
 		                                                               $this->error = $l['str_trouble_with_db'];
                		                                        }else{
                                		                                $result .= $l['str_primary_ok'] . "<br >\n";
                                                		        }
                                             			 }else{ // record already exists
									$result .=
									sprintf($l['str_primary_warning_srv_x_exists_not_overwritten'],
										stripslashes($value)) ."<br >\n";
								}
							}
						}
					}
				}
			}
			$i++;
		}
		return $result;
	}

// *******************************************************
// 	Function UpdateReversePTR($a,$value)
	/**
	 * Update PTR when modifying A or AAAA
			$i++;
	 *
	 * @access private
	 * @return string 1 if success, 0 If DB error, string of error else
	 */

	Function UpdateReversePTR($a,$value,$type) {
		global $l,$db, $config;

		// if reverse IP is managed by current user, update PTR
		// else check if reverse IP delegation exists (ie as CNAME)
		$result .= $l['str_primary_looking_for_reverse'] . "...";
		// construct reverse zone
		if(!strcmp($type,"A")){
			$ipsplit = split('\.',$a);
			$reversezone="";
			$firstip=0;
			while(($reverseipvalue = array_pop($ipsplit)) !== NULL){
				if($firstip){
					$reversezone .= $reverseipvalue . ".";
				}else{
					$firstip = $reverseipvalue;
				}
			}
			$reversezone .= "in-addr.arpa";
		}else{ // not A, then AAAA
			$ip = ConvertIPv6toDotted($a);
			$ipsplit = split('\.',$ip);
			$reversezone="";
			$firstip=0;
			reset($ipsplit);
			// remove first element (has to be modified)
			while(($reverseipvalue = array_pop($ipsplit)) !== NULL ){
				if($firstip){
					$reversezone .= $reverseipvalue . ".";
				}else{
					$firstip = $reverseipvalue;
				}
			}
			$reversezone .= "ip6.arpa";
		}
		// TODO needed to recognize upper than a dot away for IPv6
		if($this->Exists($reversezone,'P')){
			$alluserzones = $this->user->listallzones();
			$ismanaged=0;
			while($userzones = array_pop($alluserzones)){
				if(!strcmp($reversezone,$userzones[0])){
					$ismanaged=1;
				}
			}

			if($ismanaged){
				// modification allowed because same owner
				// looking for zoneid
				$result .= " " . $l['str_primary_zone_managed_by_you'];
				$query = "SELECT id FROM dns_zone 
					WHERE zone='" . $reversezone . "' AND zonetype='P'";
				$res = $db->query($query);
				$line = $db->fetch_row($res);
				$newzoneid=$line[0];
				$result .= " " . $this->AddPTRRecord($newzoneid,array($firstip),array($value .
						"." . $this->zonename . "."),array($l['str_primary_default']),NULL);
				if(!$this->error){
					$result .= " " . $this->flagModified($newzoneid);
					$this->updateSerial($newzoneid);
				}
			}else{
				// zone exists, but not managed by current user.
				// check for subzone managed by current user
				$result .= " " .
				$l['str_primary_main_zone_not_managed_by_you'] . "...";
				$query = "SELECT zone,id FROM dns_zone WHERE
						userid='" . $this->user->userid . "'
						AND zone like '%." . $reversezone . "'";
				$res = $db->query($query);
				$newzoneid = 0;
				while($line = $db->fetch_row($res)){
					$range = array_pop(array_reverse(split('\.',$line[0])));
					list($from,$to) = split('-',$range);
          if (notnull($to)) {
            if(($firstip >= $from) && ($firstip <= $to)){
              $newzoneid=$line[1];
            }
          } else {
            list($start, $length) = split('/', $range);
            if ($firstip >= $start && $length>0 && $length<32 && $firstip < ($start+pow(2, 32-$length))) {
              $newzoneid=$line[1];
            }
          }
				}
				if($newzoneid){
					$result .= " " . $this->AddPTRRecord($newzoneid,array($firstip),array($value .
							"." . $this->zonename . "."),array($l['str_primary_default']),NULL);
					if(!$this->error){
						$result .= " " . $this->flagModified($newzoneid);
						$this->updateSerial($newzoneid);
					}
				}else{
					// no zone found
					$result .= " " .
							$l['str_primary_reverse_exists_but_ip_not_manageable'] . "<br >";
				}
			}
		}else{
			$result .= sprintf($l['str_primary_not_managed_by_x'],
					$config->sitename) . "<br >";
		}
		return $result;
	}



  Function VerifySOA($val, $defval, $soattl = "SOA") {
    global $l;
    if (!notnull($val)) {
      $val=$defval;
    } else {
      $nval = intval($val);
      if (0!=strcmp($nval, $val) || $nval <= 0) {
        $this->error .= sprintf($l['str_primary_x_parameter_x_has_to_be_int'], $soattl, $val);
        return;
      }
      $val = $nval;
    }
  }


// *******************************************************
//	Function UpdateSOA($xferip,$defaultttl,$soarefresh,$soaretry,$soaexpire,$soaminimum)
	/**
	 * Update SOA of current zone
	 *
	 *@access private
	 *@param string $xferip IP(s) allowed to do zone transfers
	 *@param int $defaultttl default TTL to be used
	 *@param int $soarefresh refresh interval
	 *@param int $soaretry retry interval
	 *@param int $soaexpire expire interval
	 *@param int $soaminimum negative TTL
	 *@return string 1 if success, 0 if DB error, string of error else
	 */
	Function UpdateSOA($xferip,$defaultttl,
						$soarefresh,$soaretry,$soaexpire,$soaminimum){
		global $db, $l;

		$result ="";

    $this->VerifySOA(&$defaultttl, 86400, "TTL");
    $this->VerifySOA(&$soarefresh, 10800);
    $this->VerifySOA(&$soaretry, 10800);
    $this->VerifySOA(&$soaexpire, 604800);
    $this->VerifySOA(&$soaminimum, 10800);

		if(notnull($xferip)){
			if(!checkPrimary($xferip)){
				$this->error .= $l['str_primary_soa_invalid_xfer'];
			}
		}else{
			$xferip='any';
		}
	
    if (notnull($this->error)) {
			return 0;
		}
		
	
			// dns_confprimary
			// upgrade serial
			
			$this->serial = getSerial($this->serial);

			if($this->creation==0){
				$query = "UPDATE dns_confprimary SET serial='" . $this->serial . "',
				xfer='" . $xferip . "', refresh='" . $soarefresh . "',
				retry='" . $soaretry . "', expiry='" . $soaexpire . "',
				minimum='" . $soaminimum . "', defaultttl='" . $defaultttl . "'
				WHERE zoneid='" . $this->zoneid . "'";
			}else{
				$query = "SELECT count(*) FROM dns_confprimary WHERE zoneid='" . $this->zoneid . "'";
	                        $res = $db->query($query);
        	                if($db->error()){
                	                $this->error=$l['str_trouble_with_db'];
                        	        return 0;
	                        }
				$line = $db->fetch_row($res);
				if($line[0] != 0){
                        	        $this->error=$l['str_zone_already_exists'] . "ICI";
                                	return 0;
				} 
 
				$query = "INSERT INTO dns_confprimary (zoneid,serial,xfer,refresh,
						retry,expiry,minimum,defaultttl)
				VALUES ('" . $this->zoneid . "','" . $this->serial . "','" . $xferip . "'
				,'" . $soarefresh . "','" . $soaretry . "','" . $soaexpire . "','" .
				$soaminimum . "','" . $defaultttl . "')";
			}
			$res = $db->query($query);
			if($db->error()){
				$this->error=$l['str_trouble_with_db'];
				return 0;
			}
			$this->refresh = $soarefresh;
			$this->retry = $soaretry;
			$this->expire = $soaexpire;
			$this->minimum = $soaminimum;
			$this->defaultttl = $defaultttl;
			return 1;
	}


// *******************************************************
	
	//	Function getArecords()
	/**
	 * Get all the A records with a given name in current zone
	 * 
	 *
	 *@access public
	 *@params Address of the array to fill (&$arecs) and name of the A records ($name)
	 *@return O (error) or 1 (success)
	 */

	Function getArecords(&$arecs, $name) {
		global $db,$l;
		$this->error='';
		$query = "SELECT val2 
			FROM dns_record 
			WHERE zoneid='" . $this->zoneid . "'
			AND type='A' AND val1='" . $name . "'";
		$res =  $db->query($query);
		$arecs = array();
		$i=0;
		while($line = $db->fetch_row($res)){
			if($db->error()){
				$this->error=$l['str_trouble_with_db'];
				return 0;
			}
			$arecs[$i]=$line[0];
			$i++;
		}
		return 1;
	}



// *******************************************************	
//	Function RetrieveRecords($type,&$arraytofill,&$arrayofid,&$ttltofill)
	/**
	 * Fill in given array with all records of type $type for current zone
	 *
	 *@access private
	 *@param string $type type of record to be retrieved
	 *@param array &$arraytofill reference of array to be filled with records
	 *@param array &$arrayofid reference of array to be filled with ids of records 
	 *@param array &$ttltofill reference of array to be filled with ttl
	 *@return int 1 if success, 0 if error
	 */
	Function RetrieveRecords($type,&$arraytofill,&$arrayofid,&$ttltofill){
		global $db,$l;
		$this->error='';
		$query = "SELECT id, val1, val2, ttl
			FROM dns_record 
			WHERE zoneid='" . $this->zoneid . "'
			AND type='" . $type . "' ORDER BY val1";
		$res =  $db->query($query);
		$arraytofill = array();
		$ttltofill = array();
		while($line = $db->fetch_row($res)){
			if($db->error()){
				$this->error=$l['str_trouble_with_db'];
				return 0;
			}
			$arraytofill[$line[1]]=$line[2];
			$arrayofid[$line[1]]=$line[0];
			$ttltofill[$line[1]] = ($line[3]=="default"?"-1":$line[3]);
		}
		return 1;
	}

// *******************************************************	
//	Function RetrieveMultiRecords($type,&$array1,&$array2,&$array3,&$array4,&$array5,&$idtofill,&$ttltofill)
	/**
	 * Same as RetrieveRecords, but used when a type of record might 
	 * have multiple similar entries (A for round robin, NS, etc...)
	 * Results are stored in two separate arrays
	 *
	 *@access private
	 *@param string $type type of record to be retrieved
	 *@param array &$array1tofill reference of array to be filled with first record element
	 *@param array &$array2tofill reference of array to be filled with second record element
	 *@param array &$array3tofill reference of array to be filled with third record element
	 *@param array &$array4tofill reference of array to be filled with fourth record element
	 *@param array &$array5tofill reference of array to be filled with fifth record element
	 *@param array &$idtofill reference of array to be filled with record id
	 *@param array &$ttltofill reference of array to be filled with ttl
	 *@return int 1 if success, 0 if error
	 */
	Function RetrieveMultiRecords($type,&$array1tofill,&$array2tofill,&$array3tofill,&$array4tofill,&$array5tofill,&$idtofill,&$ttltofill){
		global $db,$l;
		$this->error='';
		$query = "SELECT id,val1, val2, val3, val4, val5, ttl
			FROM dns_record 
			WHERE zoneid='" . $this->zoneid . "'
			AND type='" . $type . "' ORDER BY val1";
		$res =  $db->query($query);
		$array1tofill = array();
		$array2tofill = array();
		$array3tofill = array();
		$array4tofill = array();
		$array5tofill = array();
		$idtofill = array();
		$ttltofill = array();
		$i=0;
		while($line = $db->fetch_row($res)){
			if($db->error()){
				$this->error=$l['str_trouble_with_db'];
				return 0;
			}
			$idtofill[$i]=$line[0];
			$array1tofill[$i]=$line[1];
			$array2tofill[$i]=stripslashes($line[2]);
			$array3tofill[$i]=stripslashes($line[3]);
			$array4tofill[$i]=stripslashes($line[4]);
			$array5tofill[$i]=stripslashes($line[5]);
			$ttltofill[$i]=($line[6]=="default"?"-1":$line[6]);
			$i++;
		}
	}

// *******************************************************	
//	Function TempZoneFile()
	/**
	 * Generate the file name (with whole path)
	 *
	 *@access private
	 *@return string file
	 */
	Function tempZoneFile(){
		global $config;
		$tmpzone = ereg_replace("/","\\",$this->zonename);
		return ("{$config->tmpdir}$tmpzone.{$this->zonetype}");
	}

// *******************************************************	
//	Function generateConfigFile()
	/**
	 * Generate a temporary config file in $this->tempZoneFile()
	 *
	 *@access private
	 *@return int 1
	 */
	Function generateConfigFile(){
		global $config,$l;
		// reinitialize every records after add/delete/modify
		// fill in with records
		$this->RetrieveRecords('NS',$this->ns,$this->nsid,$this->nsttl);
		$this->RetrieveMultiRecords('SUBNS',$this->subns,$this->subnsa,$this->nullarray,$this->nullarray,$this->nullarray,$this->subnsid,$this->subnsttl);
		$this->RetrieveRecords('CNAME',$this->cname,$this->cnameid,$this->cnamettl);
		$this->RetrieveMultiRecords('TXT',$this->txt,$this->txtdata,$this->nullarray,$this->nullarray,$this->nullarray,$this->txtid,$this->txtttl);
		if($this->reversezone){
			$this->RetrieveMultiRecords('PTR',$this->ptr,$this->ptrname,$this->nullarray,$this->nullarray,$this->nullarray,$this->ptrid,$this->ptrttl);
			$this->RetrieveMultiRecords('DELEGATE',$this->delegatefromto,$this->delegateuser,$this->nullarray,$this->nullarray,$this->nullarray,$this->delegateid,$this->delegatettl);
		}else{
			$this->RetrieveRecords('MX',$this->mx,$this->mxid,$this->mxttl);
			$this->RetrieveMultiRecords('A',$this->a,$this->aip,$this->nullarray,$this->nullarray,$this->nullarray,$this->aid,$this->attl);
			$this->RetrieveMultiRecords('A6',$this->a6,$this->a6ip,$this->nullarray,$this->nullarray,$this->nullarray,$this->a6id,$this->a6ttl);
			$this->RetrieveMultiRecords('AAAA',$this->aaaa,$this->aaaaip,$this->nullarray,$this->nullarray,$this->nullarray,$this->aaaaid,$this->aaaattl);
			$this->RetrieveMultiRecords('SRV',$this->srvname,$this->srvpriority,$this->srvweight,$this->srvport,$this->srvvalue,$this->srvid,$this->srvttl);
			$this->RetrieveMultiRecords('WWW',$this->www,$this->wwwa,$this->wwwi,$this->wwwr,$this->nullarray,$this->wwwid,$this->wwwttl);
		}
		// select SOA items
		$fd = fopen($this->tempZoneFile(),"w");
		if ($fd == 0)
		{
			$this->error = sprintf($l['str_can_not_open_x_for_writing'],
								$this->tempZoneFile());
			return -1;
		}
		$this->generateSOA($this->defaultttl,$config->nsname,$this->zonename,
							$this->user->Retrievemail(), $this->serial,
							$this->refresh,$this->retry,$this->expiry,$this->minimum,$fd);
							
		// retrieve & print NS
		$this->generateConfig("NS",$this->ns,$this->nsttl,$fd);
				
		if($this->reversezone){
			// retrieve & print PTR
			$this->generateMultiConfig("PTR",$this->ptr,"","","",$this->ptrname,$this->ptrttl,$fd);
		}else{ // end reverse zone
			// retrieve & print MX
			$this->generateConfig("MX",$this->mx,$this->mxttl,$fd);
			// retrieve & print A
			$this->generateMultiConfig("A",$this->a,"","","",$this->aip,$this->attl,$fd);
			// retrieve & print AAAA
			$this->generateMultiConfig("AAAA",$this->aaaa,"","","",$this->aaaaip,$this->aaaattl,$fd);
			// retrieve & print SRV
			$this->generateMultiConfig("SRV",$this->srvname,$this->srvpriority,$this->srvweight,$this->srvport,$this->srvvalue,$this->srvttl,$fd);
		} // end not reverse zone
		
		$this->generateConfig("CNAME",$this->cname,$this->cnamettl,$fd);
		$this->generateMultiConfig("TXT",$this->txt,"","","",$this->txtdata,$this->txtttl,$fd);

		// retrieve & print SUBNS
		$this->generateMultiConfig("NS",$this->subns,"","","",$this->subnsa,$this->subnsttl,$fd);
		$this->generateMultiConfig("A",$this->www,"","","",$this->wwwi,$this->wwwttl,$fd);

		fputs($fd,"\n\n");
		fclose($fd);
		return 1;
	}


// *******************************************************	
//	Function generateSOA($tttl,$nsname,$zonename,$email,
//						$serial,$refresh,$retry,$expiry,$minimum,$fd="")
	/**
	 * Generate SOA config in a file or as return content
	 *
	 *@access private
	 *@return int 1 if in a file, string content if no file given
	 */
	Function generateSOA($tttl,$nsname,$zonename,$email,
						$serial,$refresh,$retry,$expiry,$minimum,$fd=""){
		global $l;
		
		$content  = "\n\$TTL " . $tttl . " ; " . $l['str_primary_default_ttl'] ;

    $zonename = $zonename . ".";
    $nsname = $nsname . ".";
		$mail = ereg_replace("@",".",$email) . ".";
    $content .= sprintf("\n%-18s \tIN %-5s %s %s", $zonename, "SOA", $nsname, $mail);

    $content .= " (";
		$content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $serial, $l['str_primary_serial']);
		$content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $refresh, $l['str_primary_refresh_period']);
		$content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $retry, $l['str_primary_retry_interval']);
		$content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $expiry, $l['str_primary_expire_time']);
		$content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $minimum, $l['str_primary_negative_caching']);

		$content .= sprintf("\n%-18s \t)", "");
		$content .= "\n\n\$ORIGIN " . $zonename;
		if($fd){
			fputs($fd,$content);
			return 1;
		}else{
			return $content;
		}
	}


// *******************************************************	
//	Function generateMultiConfig($type,$item1,$item2,$ttl,$fd = "")
	/**
	 * Generate config in a file or as return content
	 *
	 *@access private
	 *@return int 1 if in a file, string content if no file given
	 */
	Function generateMultiConfig($type,$item1,$item2,$item3,$item4,$item5,$ttl,$fd = ""){
		// retrieve & print $type
		$counter = 0;
		$content = "";
		while(isset($item1[$counter])){
      $rest = "";
      if (isset($item2[$counter]))
        $rest .= $item2[$counter] . " ";
      if (isset($item3[$counter]))
        $rest .= $item3[$counter] . " ";
      if (isset($item4[$counter]))
        $rest .= $item4[$counter] . " ";
      if (isset($item5[$counter]))
        $rest .= $item5[$counter] . " ";

			$content .= sprintf("\n%-18s %s\tIN %-5s %s",
						$item1[$counter], (@$ttl[$counter] != "-1" ? @$ttl[$counter] : ""),
            $type, $rest);
			$counter++;
		}
		$content .= "\n\n";

		if($fd){
			fputs($fd,$content);
			return 1;
		}else{
			return $content;
		}
	}


// *******************************************************	
//	Function generateConfig($type,$item1,$ttl,$fd = "")
	/**
	 * Generate config in a file or as return content
	 *
	 *@access private
	 *@return int 1 if in a file, string content if no file given
	 */
	Function generateConfig($type,$item1,$ttl,$fd = ""){
		// retrieve & print $type
		$counter = 0;
		$content = "";
		
		$keys = array_keys($item1);
		switch($type){
			case "NS":
				while($key = array_shift($keys)){
          $content .= sprintf("\n%-18s %s\tIN %-5s %s",
							"", ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $key);
				}
				break;
			case "MX":
				while($key = array_shift($keys)){
          $content .= sprintf("\n%-18s %s\tIN %-5s %s %s",
							"", ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $item1[$key], $key);
				}  		
				break;
			default:	
				while($key = array_shift($keys)){
          $content .= sprintf("\n%-18s %s\tIN %-5s %s",
							$key, ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $item1[$key]);
				}
				break;
		}

		if($fd){
			fputs($fd,$content);
			return 1;
		}else{
			return $content;
		}
	}


// *******************************************************	
//	Function PrintTTL($ttl)
	/**
	 * return TTL 
	 *
	 *@access private
	 *@return string ttl localized value
	 */

	Function PrintTTL($ttl){
		global $l;
    	return ($ttl=="-1"||$ttl=="default"?$l['str_primary_default']:$ttl);
  	}

// *******************************************************	
//	Function DNSTTL($ttl)
	/**
	 * return TTL 
	 *
	 *@access private
	 *@return string ttl value for DB insertion
	 */	
	Function DNSTTL($ttl){
		global $l;
		if(!notnull($ttl)){
			$ttlval = "-1";
		}else{
			if ($ttl==$l['str_primary_default'])
				$ttlval = "-1";
			else
				$ttlval = mysql_real_escape_string($ttl);
		}							
		return $ttlval;
	}
	
// *******************************************************	
//	Function flagModified($zoneid)
	/**
	 * flag given zone as 'M'odified to be generated & reloaded
	 *
	 *@access private
	 *@param $zoneid int zone id
	 *@return string result text
	 */	
	Function flagModified($zoneid){
		global $db, $l;
				
		$query = "UPDATE dns_zone SET 
					status='M' WHERE id='" . $zoneid . "'";
		$res = $db->query($query);
		if($db->error()){
			$result = '<p>' .
			sprintf($html->string_error,
				$l['str_trouble_with_db']
			) . '
			' . $l['str_primary_zone_error_not_available_try_again'] . '</p>';
		}
	}
	
// *******************************************************	
//	Function updateSerial($zoneid)
	/**
	 * update zone serial
	 *
	 *@access private
	 *@param $zoneid int zone id
	 *@return int 0 if error, 1 if success
	 */	
	Function updateSerial($zoneid){
		global $db, $l;
		$result ="";
	
		// retrieve zone serial
		$query = "SELECT serial FROM dns_confprimary WHERE zoneid='" . $zoneid . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}
		$line = $db->fetch_row($res);
		
		$serial = getSerial($line[0]);
		$query = "UPDATE dns_confprimary SET serial='" . $serial . "'
				WHERE zoneid='" . $zoneid . "'";
		$res = $db->query($query);
		if($db->error()){
			$this->error=$l['str_trouble_with_db'];
			return 0;
		}
		return 1;
	}


// *******************************************************
// *             All check functions                     *
// *******************************************************	

// *******************************************************	
	// function checkMXName($string)
	/**
	 * Check if MX name has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkMXName($string){
	        $string = strtolower($string);
	        // only valid char, without dot as 1st char (no dot allowed)
	        if((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
	                strlen($string)) || (strpos('0'.$string,".") == 1)
	        ){
	                $result = 0;
	        }else{
	                $result = 1;
	        }
	        return $result;
	}

	// function checkMXPref($string)
	/**
	 * Check if MX pref is integer
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkMXPref($string){
	        if(preg_match("/[^\d]/", $string)){
	                $result = 0;
	        }else{
        	        $result = 1;
	        }
        	return $result;
	}

// *******************************************************	
	// function checkNSName($string)
	/**
	 * Check if NS Name has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkNSName($string){
	        $string = strtolower($string);
	        // only valid char, without dot as 1st char and at least one dot
	        if((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
	        strlen($string)) || (strpos('0'.$string,".") == FALSE)||
	        (strpos('0'.$string,".") == 1)){
	                $result = 0;
	        }else{
	                $result = 1;
	        }
	        return $result;
	}

	// function checkNSValue($string)
	// not used - useful only with SUBNS


// *******************************************************	
// function checkAName($string)
/**
 * Check if A name has only valid char
 *
 *@param string $string name to be checked
 *@return int 1 if valid, 0 else
 */
	function checkAName($string){
	        $string = strtolower($string);
	        // only specified char - dot not allowed (not RFC but dummy user prevention)
		// except if zone name itself
		if($string == $this->zonename.'.'){
			$result = 1;
		}else{
		        if(strcmp($string,"@") && (strspn($string, ".0123456789abcdefghijklmnopqrstuvwxyz-") !=
			        strlen($string))){
	                	$result = 0;
					}else if ($string[0]=='.'||$string[strlen($string)-1]=='.'||count(explode('.',$string,3))>2){
	                	$result = 0;
		        }else{
		                $result = 1;
	        	}
		}
	        return $result;
	}
	// function checkAValue($string)
	/**
	 * Check if A value has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkAValue($string){
	        return checkIP($string);
	}


// *******************************************************	
	// function checkPTRName($string)
	/**
	 * Check if PTR name has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkPTRName($string){
		// zone name allowed
		if($string == $this->zonename.'.'){
			$result = 1;
		}else{
			if(!$this->user->ipv6){
			        // no IPv6
			        if( ereg("[a-zA-Z]",$string) || ($string > 254) ){
		        	        $result = 0;
			        }else{
			                $result = 1;
			        }
			}else{
				// ipv6
				if(!checkIPv6($string)){
	                		$result = 0;
			        }else{
        	        		$result = 1;
		        	}
			}
		}
		return $result;
	}

	// function checkPTRValue($string)
	/**
	 * Check if PTR value has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkPTRValue($string){
		// no distinction between IPv4 and IPv6

		$string = strtolower($string);
		        // only specified char
	        if((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
		     strlen($string)) || (strpos('0'.$string,".") == FALSE)||
		     (strpos('0'.$string,".") == 1) || !preg_match("/[a-z]\.$/i",$string)){
	                $result = 0;
		}else{
			$result = 1;
	        }
        	return $result;
	}


// *******************************************************	
// function checkCNAMEName($string)
        /**
         * Check if CNAME name is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	function checkCNAMEName($string){
		$string = strtolower($string);
    $numdots = 2;
    if ($string[0] == '*' && $string[1] == '.' && $string[2] != '*') { $string=substr($string,2); $numdots--;}
    $abc = "0123456789abcdefghijklmnopqrstuvwxyz-.";

	        // only specified char without a dot as first char - * allowed 
	        if(strcmp($string,"*") && (strspn($string, $abc) !=
		        strlen($string)) || (strpos('0'.$string, ".") == 1) 
		){
	                $result = 0;
	        }else if(count(explode('.',$string,3))>$numdots){
	                $result = 0;
	        }else{
        	        $result = 1;
	        }

		if( checkIP($string) ){
			$result = 0;
		}

		return $result;
	}


// function checkCNAMEValue($string)
	// function checkCNAMEValue($string)
	/**
	 * Check if CNAME value is valid
	 * 
	 *@param string $string value to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkCNAMEValue($string){
		$string = strtolower($string);
		// value can't be an IP
		if(checkIP($string)){
			$result = 0;
		}else{
			// only specified char without a dot as first char
			if( (strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
                        strlen($string)) || (strpos('0'.$string,".") == 1) ){
				$result = 0;
			}else{
				$result = 1;
			}
		}
		return $result;
	}
// *******************************************************	
// function checkTXTName($string)
        // function checkTXTName($string)
        /**
         * Check if TXT name is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	 function checkTXTName($string){
	        $string = strtolower($string);
        	// only specified char
		// "_" only as first char
					if (!strcmp($string, "@") || !strcmp($string, "*"))
						return 1;
        	if((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-._") != strlen($string))
                	|| ((strpos($string,"_") !== FALSE) && !(
                       strpos($string,"_") == 0
                       || $string[strpos($string,"_")-1] == '.')
                     )
	                ){
        	        $result = 0;
	        }else{
	                $result = 1;
	        }
	        return $result;
	}

// function checkTXTValue($string)
// not used - everything is allowed

// *******************************************************	
// function checkWWWName($string)
        // function checkWWWName($string)
        /**
         * Check if WWW name is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	 function checkWWWName($string){
	        $string = strtolower($string);
        	// only specified char
					// "_" only as first char
        	if(strcmp($string,"@") && strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.")!=strlen($string))
	        {
        	        $result = 0;
	        }else{
	                $result = 1;
	        }
	        return $result;
	}
	function checkWWWValue($string) {
		$string = strtolower($string);
		if (ereg('^http://', $string) || ereg('^https://', $string))
			return 1;
		return 0;
	}
// *******************************************************	
// function checkA6Name($string)
// function checkA6Value($string)

// A6 not used

// *******************************************************	
// function checkAAAAName($string)
	/**
	 * Check if AAAA name has only valid char
	 *
	 *@param string $string name to be checked
	 *@return int 1 if valid, 0 else
	 */
        function checkAAAAName($string){
	        $string = strtolower($string);
	        // only specified char - dot not allowed (not RFC but dummy user prevention)
		// except if zone name itself
		if($string == $this->zonename.'.'){
			$result = 1;
		}else{
		        if(strcmp($string,"@") && (strspn($string, ".0123456789abcdefghijklmnopqrstuvwxyz-") !=
			        strlen($string))){
	                	$result = 0;
					}else if ($string[0]=='.'||$string[strlen($string)-1]=='.'||count(explode('.',$string,3))>2){
	                	$result = 0;
		        }else{
		                $result = 1;
	        	}
		}
	        return $result;
        }

// function checkAAAAValue($string)
        /**
         * Check if AAAA value is valid
         *
         *@param string $string value to be checked
         *@return int 1 if valid, 0 else
         */
        function checkAAAAValue($string){
                $string = strtolower($string);
		if(! checkIPv6($string) ){
			$result = 0;
		}else{
			$result = 1;
		}
		return $result;	
	}	

// *******************************************************	
// function checkSUBNSName($string)
        /**
         * Check if SUBNS name is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
        function checkSUBNSName($string){
		$string = strtolower($string);
      $allowed = "0123456789abcdefghijklmnopqrstuvwxyz-";
      if (ereg('\.ip6\.arpa$', $this->zonename)) $allowed .= ".";
		// only specified char
	        if(strspn($string, $allowed) != strlen($string)){
	                $result = 0;
	        }else{
	                $result = 1;
	        }
        	return $result;
	}

// function checkSUBNSValue($string)
        /**
         * Check if SUBNS value is valid
         *
         *@param string $string value to be checked
         *@return int 1 if valid, 0 else
         */
        function checkSUBNSValue($string){
                $string = strtolower($string);
	        // only specified char 
	        if((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
	        strlen($string)) || (strpos('0'.$string,".") == 1)){
	                $result = 0;
	        }else{
        	        $result = 1;
	        }
		return $result;
	}
// *******************************************************	
// function checkDELEGATEName($string)
// function checkDELEGATEValue($string)
// not used - nothing to do with DNS

// *******************************************************	

// *******************************************************	
// function checkSRVName($string)
        /**
         * Check if SRV name is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	function checkSRVName($string){
		$string = strtolower($string);

	        // only specified char without a dot as first char - * allowed 
	        if(strcmp($string,"*") && (strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-._*") !=
		        strlen($string)) || (strpos('0'.$string,".") == 1)
		){
	                $result = 0;
	        }else{
        	        $result = 1;
	        }

		if( checkIP($string) ){
			$result = 0;
		}

		return $result;
	}

// *******************************************************	
// function checkSRVPriority($string)
        /**
         * Check if SRV Priority is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	function checkSRVPriority($string){

	        if(preg_match("/[^\d]/", $string)){
	                return 0;
	        }else{
        	        return 1;
	        }
	}

// *******************************************************	
// function checkSRVWeight($string)
        /**
         * Check if SRV Weight is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	function checkSRVWeight($string){
	        if(preg_match("/[^\d]/", $string)){
	                return 0;
	        }else{
        	        return 1;
	        }
	}

// *******************************************************	
// function checkSRVPort($string)
        /**
         * Check if SRV Port is valid
         *
         *@param string $string name to be checked
         *@return int 1 if valid, 0 else
         */
	function checkSRVPort($string){

	        if(preg_match("/[^\d]/", $string)){
	                return 0;
	        }else{
        	        return 1;
	        }
	}

// function checkSRVValue($string)
	// function checkSRVValue($string)
	/**
	 * Check if SRV value is valid
	 * 
	 *@param string $string value to be checked
	 *@return int 1 if valid, 0 else
	 */
	function checkSRVValue($string){
		$string = strtolower($string);
		// value can't be an IP
		if(checkIP($$string)){
			$result = 0;
		}else{
			// only specified char without a dot as first char
			if( (strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
                        strlen($string)) || (strpos('0'.$string,".") == 1) ){
				$result = 0;
			}else{
				$result = 1;
			}
		}
		return $result;
	}
// *******************************************************	

}
?>
