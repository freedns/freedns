<?

/*
  This file is part of XName.org project
  See  http://www.xname.org/ for details

  License: GPLv2
  See LICENSE file, or http://www.gnu.org/copyleft/gpl.html

  Author(s): Yann Hirou <hirou@xname.org>

*/

// Class Primary
//   All functions for primary manipulations
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

  /**
   * Class constructor & data retrieval (use of Retrieve[Multi]Record)
   *
   *@access public
   *@param string $zonename zone name
   *@param string $zonetype zone type (must be 'P'rimary)
   *@param string $user class member user for current user
   */
  function Primary($zonename, $zonetype, $user) {
    global $db,$l;

    $this->Zone($zonename, $zonetype);
    // fill in vars
    $res = $db->query("SELECT serial, refresh, retry, expiry, minimum, defaultttl, xfer
      FROM dns_confprimary WHERE zoneid='" . $this->zoneid . "'");
    $line = $db->fetch_row($res);
    if ($db->error()) {
      $this->error=$l['str_trouble_with_db'];
      return 0;
    }
    $this->creation = empty($line[1]);

    // set default SOA values
    $this->serial = $line[0];
    $this->refresh = empty($line[1]) ? 86400 : $line[1];
    $this->retry = empty($line[2]) ? 10800 : $line[2];
    $this->expiry = empty($line[3]) ? 3600000 : $line[3];
    $this->minimum = empty($line[4]) ? 10800 : $line[4];
    $this->defaultttl = empty($line[5]) ? 86400 : $line[5];
    $this->xfer = $line[6];
    $this->user = $user;
    if (ereg('.arpa$', $zonename) || ereg('.ip6.int$', $zonename)) {
      $this->reversezone = 1;
    } else {
      $this->reversezone = 0;
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

    if ($this->reversezone) {
      $this->ptr = array();
      $this->ptrname = array();
      $this->ptrid = array();
      $this->ptrttl = array();
      $this->delegatefromto = array();
      $this->delegateuser = array();
      $this->delegateid = array();
      $this->delegatettl = array();
    } else {
      $this->mxsrc = array();
      $this->mxpref = array();
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
    $this->RetrieveRecords('NS', $this->ns, $this->nsid, $this->nsttl);
    $this->RetrieveMultiRecords('SUBNS', $this->subns, $this->subnsa,
        $this->nullarray, $this->nullarray, $this->nullaray, $this->subnsid, $this->subnsttl);
    $this->RetrieveRecords('CNAME', $this->cname, $this->cnameid, $this->cnamettl);

    if ($this->reversezone) {
      $this->RetrieveMultiRecords('PTR', $this->ptr, $this->ptrname, $this->nullarray,
          $this->nullarray, $this->nullarray, $this->ptrid, $this->ptrttl);
      $this->RetrieveMultiRecords('DELEGATE', $this->delegatefromto, $this->delegateuser,
          $this->nullarray, $this->nullarray, $this->nullarray, $this->delegateid, $this->delegatettl);
    } else {
      $this->RetrieveMultiRecords('MX', $this->mxsrc, $this->mxpref, $this->mx,
          $this->nullarray, $this->nullarray, $this->mxid, $this->mxttl);
      $this->RetrieveMultiRecords('A', $this->a, $this->aip, $this->nullarray,
          $this->nullarray, $this->nullarray, $this->aid, $this->attl);
      $this->RetrieveMultiRecords('A6', $this->a6, $this->a6ip, $this->nullarray,
          $this->nullarray, $this->nullarray, $this->a6id, $this->a6ttl);
      $this->RetrieveMultiRecords('AAAA', $this->aaaa, $this->aaaaip, $this->nullarray,
          $this->nullarray, $this->nullarray, $this->aaaaid, $this->aaaattl);
      $this->RetrieveMultiRecords('TXT', $this->txt, $this->txtdata, $this->nullarray,
          $this->nullarray, $this->nullarray, $this->txtid, $this->txtttl);
      $this->RetrieveMultiRecords('SRV', $this->srvname, $this->srvpriority,
          $this->srvweight, $this->srvport, $this->srvvalue, $this->srvid, $this->srvttl);
      $this->RetrieveMultiRecords('WWW', $this->www, $this->wwwa, $this->wwwi,
          $this->wwwr, $this->nullarray, $this->wwwid, $this->wwwttl);
    }
  }


   /**
    * verifies and fixes whether given param is within bounds.
    * fixes in-place, returns "fixed" message.
    */
   function fixSOAParam(&$val, $lev, $max=0) {
      global $l;

      $ret = '';
      if ((!$max && $val < $lev) ||
          ($max && $val > $lev)) {
        $ret = sprintf('<br><small>%s</small>',
            sprintf($l['str_primary_soa_fixed_x'], $val));
        $val = $lev;
      }
      return $ret;
   }


  /**
   * returns a pre-filled form to modify primary records
   *
   *@access public
   *@param array $params list of params
   *@return string HTML pre-filled form
   */
  function printModifyForm($params) {
    global $config, $lang;
    global $l;
    global $hiddenfields;

    list ($advanced, $ipv6, $nbrows) = $params;
    if ($nbrows < 1) {
      $nbrows = 1;
    }
    $this->error = "";
    $result = '';
    $deletecount = 0;
    $result .= '
<script type="text/javascript">
function v(t) {
 n = t.value;
 if (n.substr(-1) === ".") return true;
 zonename = document.forms[0]["zonename"].value;
 if (n.match(zonename + "$") == zonename) 
   alert("' . $l['str_js_dotvalidate1'] . '" + n + "." + zonename + "' . $l['str_js_dotvalidate2'] . '");
 return n;
}
</script>';

    // TODO use zoneid instead of zonename & zonetype
    $result .= '<form method="POST">
      ' . $hiddenfields . '
      <input type="hidden" name="zonename" value="' . $this->zonename . '">
      <input type="hidden" name="zonetype" value="' . $this->zonetype . '">
      <input type="hidden" name="modified" value="1">
    ';
    // if advanced, say it to modified - in case
    // of temporary use of advanced interface, not in
    // user prefs.
    if ($advanced) {
      $result .= '<input type="hidden" name="advanced" value="1">';
    }

    # BEGIN SOA-PARAMS
    $modifyheader = '<h3 class="boxheader">%s</h3>';
    if ($advanced) {
      $soafmt = '<p>%s</p>'
          . '<table class="globalparams"><tr>'
          . '<td class="left">%s</td>'
          . '<td><input type="text" name="%s" value="%s">%s</td>'
          . '</tr></table>';

      // print global params ($TTL)
      $result .= sprintf($modifyheader, $l['str_primary_global_params']);

      $fixed = $this->FixSOAParam($this->defaultttl, 86400);
      $result .= sprintf($soafmt,
          $l['str_primary_ttl_explanation'], $l['str_primary_default_ttl'],
          'defaultttl', $this->defaultttl, $fixed);

      // print SOA params
      $result .= sprintf($modifyheader, $l['str_primary_soa_params']);

      # refresh should be min 24h
      $fixed = $this->FixSOAParam($this->refresh, 86400);
      $result .= sprintf($soafmt,
          $l['str_primary_refresh_interval_expl'], $l['str_primary_refresh_period'],
          'soarefresh', $this->refresh, $fixed);

      # retry should be min 3h
      $fixed = $this->FixSOAParam($this->retry, 10800);
      $result .= sprintf($soafmt,
          $l['str_primary_retry_interval_expl'], $l['str_primary_retry_interval'],
          'soaretry', $this->retry, $fixed);

      # expiry should be min 1000h (~42d)
      $fixed = $this->FixSOAParam($this->expiry, 3600000);
      $result .= sprintf($soafmt,
          $l['str_primary_expire_time_expl'], $l['str_primary_expire_time'],
          'soaexpire', $this->expiry, $fixed);

      # minimum should be max 3h
      $fixed = $this->FixSOAParam($this->minimum, 10800, 1);
      $result .= sprintf($soafmt,
          $l['str_primary_negative_caching_expl'], $l['str_primary_negative_caching'],
          'soaminimum', $this->minimum, $fixed);
    }
    # END SOA-PARAMS

    # BEGIN NS-RECORDS
    // retrieve NS names
    $nsxnames = GetListOfServerNames();
    $nsxnamesmandatory = GetListOfServerNames(1);
    $nsxnamesoptional = array();
    if (count($this->ns) == 0) {
      $nsxnamesoptional = array_diff($nsxnames, $nsxnamesmandatory);
    }

    $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_name_server_title']);
    $result .= '<p>';
    $result .= sprintf($l['str_primary_name_server_expl_with_sample_x'], $nsxnames[0]);
    $result .= "</p>\n";
    $result .= sprintf('<table><tr><th>%s</th>', $l['str_primary_name']);
    if ($advanced) {
      $result .= '<th>TTL</th>';
    }
    $result .= sprintf('<th>%s</th>', $l['str_delete']);
    $result .= "</tr>\n";

    $usednsxnames = array();
    $keys = array_keys($this->ns);
    while ($key = array_shift($keys)) {
      $result .= sprintf('<tr><td>%s</td>', $key);
      if ($advanced) {
        $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->nsttl[$key]));
      }
      $result .= '<td>';
      // if ns is mandatory, never delete it
      $keytocompare = substr($key, 0, -1);
      if (!in_array($keytocompare, $nsxnamesmandatory)) {
        $deletecount++;
        $result .= sprintf(
            '<input type="checkbox" name="delete%d" value="ns(%s-%s)">',
            $deletecount, $key, $this->nsid[$key]);
      } else {
        array_push($usednsxnames, $keytocompare);
      }
      $result .= "</td></tr>\n";
    }
    // compare $usednsxnames and $nsxnamesmandatory. If differences, add missing ones.
    $missingns = array_diff($nsxnamesmandatory, $usednsxnames);
    $nscounter = 0;
    while ($missingnsname = array_pop($missingns)) {
      $nscounter++;
      $result .= sprintf(
          '<tr><td><input type="hidden" name="ns%d" value="%s">%s</td>',
          $nscounter, $missingnsname, $missingnsname);
      if ($advanced) {
        $result .= sprintf(
            '<td><input type="text" name="nsttl%d" size="8" value="%s"></td>',
            $nscounter, $l['str_primary_default']);
      }
      $result .= "<td></td></tr>\n";
    }
    $nscounter++;
    for ($count=1; $count <= $nbrows; $count++) {
      $result .= sprintf(
          '<tr><td><input type="text" onchange="v(this)" name="ns%d" value="%s"></td>',
          $nscounter, (isset($nsxnamesoptional[$count]) ? $nsxnamesoptional[$count] : ''));
      if ($advanced) {
        $result .= sprintf(
            '<td><input type="text" name="nsttl%d" size="8" value="%s"></td>',
            $nscounter, $l['str_primary_default']);
      }
      $nscounter++;
      $result .= '</tr>';
    }
    $result .= '</table>';
    # END NS-RECORDS

    if ($this->reversezone) {
      $v6 = preg_match("/\.ip6\.(arpa|int)$/", $this->zonename);
      $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_ptr_title']);
      $result .= sprintf('<p>%s</p><p>%s:<br><tt>%s</tt></p><p>%s<p>',
          $l['str_primary_ptr_expl'], $l['str_primary_ptr_sample'],
          $l['str_primary_ptr_sample_content'], $l['str_primary_ptr_ipv6_note']);
      $result .= '<table><tr><td class="left">';
      $result .= sprintf($l['str_primary_ptr_record_modify_a_x'],
          $v6 ? "AAAA" : "A", $config->sitename);
      $result .= '</td><td><input type=checkbox name="modifya"></td></tr></table>';
      $result .= '<table><tr><th>';
      $result .= sprintf($l['str_primary_ptr_ip_under_x'], $this->zonename);
      $result .= sprintf('</th><th>%s</th>', $l['str_primary_name']);
      if ($advanced) {
        $result .= '<th>TTL</th>';
      }
      $result .= sprintf('<th>%s</th>', $l['str_delete']);

      $counter = 0;
      while (isset($this->ptr[$counter])) {
        $deletecount++;
        $result .= sprintf('<tr><td>%s</td><td>%s</td>',
            $this->ptr[$counter], $this->ptrname[$counter]);
        if ($advanced) {
          $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->ptrttl[$counter]));
        }
        $result .= sprintf(
            '<td><input type="checkbox" name="delete%d" value="ptr(%s/%s-%s)"></td></tr>',
            $deletecount, $this->ptr[$counter],
            $this->ptrid[$counter], $this->ptrname[$counter]);
        $counter++;
      }

      $counter = 0;
      $keys = array_keys($this->ptr);
      while ($key = array_shift($keys)) {
        $deletecount++;
        $counter++;
      }

      $ptrcounter = 0;
      for($count=1; $count <= $nbrows; $count++) {
        $ptrcounter++;
        $result .= '<tr>';
        $result .= sprintf('<td><input type="text" name="ptr%d"></td>', $ptrcounter);
        $result .= sprintf('<td><input type="text" name="ptrname%d"></td>', $ptrcounter);
        if ($advanced) {
          $result .= sprintf(
              '<td><input type="text" name="ptrttl%d" size="8" value="%s"</td>',
              $ptrcounter, $l['str_primary_default']);
        }
        $result .= '<td></td></tr>';
      }
      $result .= '</table>';

      if ($advanced) {
        $result .= sprintf('%s', $l['str_primary_ptr_generate']);
        $result .= '<table><tr><td>$GENERATE ';
        $result .= '<input type="text" name="gen1">-<input type="text" name="gen2"> ';
        $result .= 'PTR <input type="text" name="gen3"></td></tr></table>';
      }

      if (!ereg('in-addr.arpa$', $this->zonename)) {
        # BEGIN SUB-RECORDS
        $result .= sprintf('<h3 class="boxheader">%s</h3', $l['str_primary_sub_zones_title']);
        $result .= sprintf('<p>%s</p>',
            sprintf($l['str_primary_sub_zones_expl_on_x_x'],
                $config->sitename, $this->zonename));
        $result .= '<table>';
        $result .= sprintf('<tr><th>%s</th><th>NS</th>', $l['str_primary_sub_zones_zone']);
        if ($advanced) {
          $result .= '<th>TTL';
        }
        $result .= sprintf('<th>%s</th>', $l['str_delete']);

        $counter = 0;
        while (isset($this->subns[$counter])) {
          if (strstr($this->subns[$counter], "-") !== FALSE) {
            $counter++;
            continue;
          }
          $deletecount++;
          $result .= sprintf('<tr><td>%s</td><td>%s</td>',
              $this->subns[$counter], $this->subnsa[$counter]);
          if ($advanced) {
            $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->subnsttl[$counter]));
          }
          $result .= sprintf(
              '<td><input type="checkbox" name="delete%d" value="subns(%s/%s)"></td></tr>',
              $deletecount, $this->subns[$counter], $this->subnsid[$counter]);
          $counter++;
        }

        $subnscounter = 0;
        for ($count=1; $count <= $nbrows; $count++) {
          $subnscounter++;
          $result .= sprintf('<tr><td><input type="text" name="subns%d"></td>', $subnscounter);
          $result .= sprintf('<td><input type="text" name="subnsa%d"></td>', $subnscounter);
          if ($advanced) {
            $result .= sprintf(
                '<td><input type="text" name="subnsttl%d" size="8" value="%s"></td>',
                $subnscounter, $l['str_primary_default']);
          }
        }
        $result .= "</table>\n";
        # END SUB-RECORDS
      } else {
        # BEGIN REVSUB-RECORDS
        $result .= sprintf('<h3 class="boxheader">%s</h3>',
            $l['str_primary_reverse_sub_zones_title']);
        $result .= sprintf('<p>%s<br>%s<br>%s</p>',
            sprintf($l['str_primary_reverse_sub_zones_delegation_x'], $config->sitename),
            sprintf($l['str_primary_reverse_sub_zones_delegation_expl_x_x'],
                $this->zonename, $config->sitename),
            $l['str_primary_reverse_sub_zones_delegation_how']);
        $result .= sprintf('<table><tr><th>%s</th>',
            $l['str_primary_reverse_sub_zone_range']);
        $result .= sprintf('<th>%s</th>',
            sprintf($l['str_primary_reverse_sub_zone_delegated_to_user_x'], ""));
        $result .= sprintf('<th>TTL</th><th>%s</th></tr>', $l['str_delete']);

        $counter = 0;
        while (isset($this->delegatefromto[$counter])) {
          $deletecount++;
          $result .= '<tr>';
          list($from, $to) = split('-', $this->delegatefromto[$counter]);
          $result .= sprintf('<td>%s&nbsp;%s%s%s</td>',
              $l['str_primary_reverse_sub_zone_range_from'], $from,
              $l['str_primary_reverse_sub_zone_range_to'], $to);
          $result .= sprintf('<td>%s</td>', $this->delegateuser[$counter]);
          if ($advanced) {
            $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->delegatettl[$counter]));
          }
          $result .= sprintf(
              '<td><input type="checkbox" name="delete%d" value="delegate(%d)"></td>',
              $deletecount, $this->delegatefromto[$counter]);
          $result .= '</tr>';
          $counter++;
        }

        $subnscounter = 0;
        for ($count=1; $count <= $nbrows; $count++) {
          $subnscounter++;
          $result .= '<tr><td>';
          $result .= sprintf('%s&nbsp;<input type="text" size="3" name="delegatefrom%d">',
              $l['str_primary_reverse_sub_zone_range_from'], $subnscounter);
          $result .= sprintf('%s&nbsp;<input type="text" size="3" name="delegateto%d">',
              $l['str_primary_reverse_sub_zone_range_to'], $subnscounter);
          $result .= '</td><td>';
          $result .= sprintf(
              '&nbsp;<input type="text" size="10" name="delegateuser%d"></td>',
              $subnscounter);
          if ($advanced) {
            $result .= sprintf(
                '<td><input type="text" name="delegatettl%d" size="8" value="%s"></td>',
                $subnscounter, $l['str_primary_default']);
          }
          $result .= "</tr>\n";
        }
        $result .= "</table>\n";
        # BEGIN REVSUB-RECORDS
      }
    } else { // not reverse zone
      # BEGIN MX-RECORDS
      $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_mail_exchanger_title']);
      $result .= sprintf('<p>%s<br>%s</p>',
          sprintf($l['str_primary_mx_expl_with_sample_x'], $this->zonename),
          $l['str_primary_mx_expl_for_pref']);

      $result .= sprintf('<table><tr><th>%s</th><th>%s</th><th>%s</th>',
         $l['str_primary_name'], $l['str_primary_mx_pref'],
         $l['str_primary_mx_name']);
      if ($advanced) {
        $result .= '<th>TTL</th>';
      }
      $result .= sprintf('<th>%s</th></tr>', $l['str_delete']);

      $counter=0;
      while(isset($this->mx[$counter])) {
        $deletecount++;
        $result .= '<tr>';
        $result .= sprintf('<td>%s</td>', $this->mxsrc[$counter]);
        $result .= sprintf('<td>%s</td>', $this->mxpref[$counter]);
        $result .= sprintf('<td>%s</td>', $this->mx[$counter]);
        if ($advanced) {
          $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->mxttl[$counter]));
        }
        $result .= sprintf(
            '<td><input type="checkbox" name="delete%d" value="mx(%s/%s-%s)"></td>',
            $deletecount, $this->mxsrc[$counter],
            $this->mxid[$counter], $this->mx[$counter]);
        $result .= '</tr>';
        $counter++;
      }

      $counter = 0;
      $keys = array_keys($this->mx);
      while ($key = array_shift($keys)) {
        $deletecount++;
        $counter++;
      }
      $mxcounter = 0;
      for ($count=1; $count <= $nbrows; $count++) {
        $mxcounter++;
        $result .= '<tr>';
        $result .= sprintf('<td><input type="text" onchange="v(this)" name="mxsrc%d"></td>', $mxcounter);
        $result .= sprintf('<td><input type="text" size="5" maxlength="5" name="mxpref%d"></td>', $mxcounter);
        $result .= sprintf('<td><input type="text" onchange="v(this)" name="mx%d"></td>', $mxcounter);
        if ($advanced) {
          $result .= sprintf('<td><input type="text" name="mxttl%d" size="8" value="%s"></td>',
              $mxcounter, $l['str_primary_default']);
        }
        $result .= '<td></td></tr>';
      }
      $result .= '</table>';
      # END MX-RECORDS

      # BEGIN A-RECORDS
      $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_a_record_title']);
      $result .= sprintf('<p>%s<br>%s</p>',
          sprintf($l['str_primary_a_record_what_you_want_before_x_x_x'],
              $this->zonename, $this->zonename, $this->zonename),
          $l['str_primary_a_record_expl']);
      $result .= '<table><tr>';
      $result .= sprintf('<td class="left">%s</td>',
          sprintf($l['str_primary_a_record_modify_ptr_x'], $config->sitename));
      $result .= '<td><input type=checkbox name="modifyptr"></td></tr></table>';
      $result .= sprintf('<table><th>%s</th><th>IP</th>', $l['str_primary_name']);
      if ($advanced) {
        $result .= '<th>TTL</th>';
      }
      $result .= sprintf('<th>%s</th>', $l['str_delete']);

      $counter = 0;
      while (isset($this->a[$counter])) {
        $deletecount++;
        $result .= '<tr>';
        $result .= sprintf('<td>%s</td><td>%s</td>', 
            $this->a[$counter], $this->aip[$counter]);
        if ($advanced) {
          $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->attl[$counter]));
        }
        $result .= sprintf(
            '<td><input type="checkbox" name="delete%d" value="a(%s/%s-%s)"></td>',
            $deletecount, $this->a[$counter], $this->aid[$counter], $this->aip[$counter]);
        $result .= '</tr>';
        $counter++;
      }

      $counter = 0;
      $keys = array_keys($this->a);
      while ($key = array_shift($keys)) {
        $deletecount++;
        $counter++;
      }
      $acounter = 0;
      for ($count=1; $count <= $nbrows; $count++) {
        $acounter++;
        $result .= '<tr>';
        $result .= sprintf(
            '<td><input type="text" onchange="v(this)" name="aname%d"></td>', $acounter);
        $result .= sprintf('<td><input type="text" name="a%d"></td>', $acounter);
        if ($advanced) {
          $result .= sprintf(
              '<td><input type="text" name="attl%d" size="8" value="%s"></td>',
              $acounter, $l['str_primary_default']);
        }
        $result .= '<td></td></tr>';
      }
      $result .= '</table>';
      # END A-RECORDS

      # BEGIN AAAA-RECORDS
      if ($this->user->ipv6) {
        $result .= '
        <h3 class="boxheader">' . $l['str_primary_ipv6_record_title'] .
        '</h3>
        <p>' .
        sprintf($l['str_primary_ipv6_record_expl_before_x_x_x'],
          $this->zonename,$this->zonename,
          $this->zonename) . '<br>
        ' . $l['str_primary_ipv6_record_expl_zone_and_round_robin'] . '
        </p>
        <table>
        <tr><td class="left">' .
        sprintf($l['str_primary_ipv6_record_modify_reverse_x'], $config->sitename) .
        '</td><td><input type=checkbox name="modifyptripv6"></td></tr>
        </table>
        <table>
        <th>'. $l['str_primary_name'] . '<th>IPv6';
        if ($advanced) { $result .= '<th>TTL'; }
        $result .= '<th>' . $l['str_delete'] . '
        ';

        $counter=0;
        while(isset($this->aaaa[$counter])) {
          $deletecount++;
          // if advanced, print TTL fields
          $result .= '<tr>
              <td>' . $this->aaaa[$counter] . '</td>
              <td>' . $this->aaaaip[$counter] . '</td>';
          if ($advanced) {
            $result .= '<td>' . $this->PrintTTL($this->aaaattl[$counter]) . '</td>
            ';
          }
          $result .= '
              <td><input type="checkbox" name="delete' . $deletecount .
              '" value="aaaa(' . $this->aaaa[$counter] . '/' .
              $this->aaaaid[$counter] . ')"></td></tr>
          ';
          $counter++;
        }

        $counter=0;
        $keys = array_keys($this->aaaa);
        while($key = array_shift($keys)) {
          $deletecount++;
          $counter++;
        }
        $aaaacounter = 0;
        for($count=1; $count <= $nbrows; $count++) {
          $aaaacounter++;
          $result .= '
          <tr><td><input type="text" onchange="v(this)" name="aaaaname' .
              $aaaacounter
              . '"></td>
              <td><input type="text" name="aaaa' . $aaaacounter . '"></td>';
          if ($advanced) {
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
      # END AAAA-RECORDS

      # BEGIN CNAME-RECORDS
      $result .= '
      <h3 class="boxheader">' . $l['str_primary_cname_title'] . '</h3>
      <p>' . $l['str_primary_cname_expl'] . '
      </p>
      <table>
      <th>' . $l['str_primary_cname_alias'] .'
      <th>' . $l['str_primary_cname_name_a_record'];
      if ($advanced) { $result .= '<th>TTL'; }
      $result .= '<th>' . $l['str_delete'] . '
            ';

      $counter=0;
      $keys = array_keys($this->cname);
      while($key = array_shift($keys)) {
        $deletecount++;
        $result .= '<tr>
            <td>' . $key . '</td>
            <td> ' . $this->cname[$key] . '</td>';
        if ($advanced) {
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
      for($count=1; $count <= $nbrows; $count++) {
        $cnamecounter++;
        $result .= '
          <tr>
          <td><input
           type="text" onchange="v(this)" name="cname' . $cnamecounter . '"></td>
            <td><input
            type="text" onchange="v(this)" name="cnamea' . $cnamecounter . '">
          </td>';
        if ($advanced) {
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
      # END CNAME-RECORDS

      # BEGIN TXT-RECORDS
      if ($this->user->txtrecords) {
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
        while(isset($this->txt[$counter])) {
          $deletecount++;
          // if advanced, print TTL fields
          $result .= '<tr>
              <td>' . $this->txt[$counter] . '</td>
              <td>' . $this->txtdata[$counter] . '</td>';
          if ($advanced) {
            $result .= '<td>' . $this->PrintTTL($this->txtttl[$counter]) . '</td>
            ';
          }
          $result .= '
              <td><input type="checkbox" name="delete' . $deletecount .
              '" value="txt(' . $this->txt[$counter] . '/' .
              $this->txtid[$counter] . ')"></td></tr>
          ';
          $counter++;
        }

        $counter=0;
        $keys = array_keys($this->txt);
        while($key = array_shift($keys)) {
          $deletecount++;
          $counter++;
        }
        $txtcounter = 0;
        for($count=1; $count <= $nbrows; $count++) {
          $txtcounter++;
          $result .= '
          <tr><td><input type="text" onchange="v(this)" name="txt' .
              $txtcounter
              . '"></td>
              <td><input type="text" name="txtstring' . $txtcounter . '"></td>';
          if ($advanced) {
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
      # END TXT-RECORDS

      # BEGIN SRV-RECORDS
      if ($this->user->srvrecords) {
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
        while(isset($this->srvname[$counter])) {
          $deletecount++;
          // if advanced, print TTL fields
          $result .= '<tr>
          <td>' . $this->srvname[$counter] . '</td>
          <td>' . $this->srvpriority[$counter] . '</td>
          <td>' . $this->srvweight[$counter] . '</td>
          <td>' . $this->srvport[$counter] . '</td>
          <td>' . $this->srvvalue[$counter] . '</td>';
          if ($advanced) {
            $result .= '<td>' . $this->PrintTTL($this->srvttl[$counter]) . '</td>
            ';
          }
          $result .= '
          <td><input type="checkbox" name="delete' . $deletecount .
          '" value="srv(' . $this->srvname[$counter] . '-' .
          $this->srvid[$counter] . ')"></td></tr>
          ';
          $counter++;
        }

        $srvcounter = 0;
        for($count=1; $count <= $nbrows; $count++) {
          $srvcounter++;
          $result .= '
            <tr><td><input type="text" onchange="v(this)" name="srvname' . $srvcounter . '"></td>
            <td><input type="text" size="3" name="srvpriority' . $srvcounter . '"></td>
            <td><input type="text" size="3" name="srvweight' . $srvcounter . '"></td>
            <td><input type="text" size="5" name="srvport' . $srvcounter . '"></td>
            <td><input type="text" name="srvvalue' . $srvcounter . '"></td>';
          if ($advanced) {
            $result .= '
            <td><input type="text" name="srvttl' . $srvcounter .
            '" size="8" value="' . $l['str_primary_default'] . '"></td>';
          }
          $result .= '<td></td></tr>';
        }
        $result .= '
        </table>
        ';
      }
      # END SRV-RECORDS

      # BEGIN SUBNS-RECORDS
      $result .='
        <h3 class="boxheader">' . $l['str_primary_sub_zones_title'] . '</h3>
        <p>
        ' . sprintf(
          $l['str_primary_sub_zones_expl_on_x_x'],
          $config->sitename,
          $this->zonename) . '
        </p>
        <table>
        <th>' . $l['str_primary_sub_zones_zone'] .'<th>NS';
      if ($advanced) { $result .= '<th>TTL'; }
      $result .= '<th>' . $l['str_delete'] . '
      ';

      $counter=0;
      while(isset($this->subns[$counter])) {
        $deletecount++;
        $result .= '<tr>
            <td>' . $this->subns[$counter] . '</td>
            <td>' . $this->subnsa[$counter] . '</td>
            ';
        if ($advanced) {
          $result .= '
          <td>' . $this->PrintTTL($this->subnsttl[$counter]) . '</td>
          ';
        }
        $result .= '<td><input type="checkbox" name="delete' . $deletecount .
            '" value="subns(' . $this->subns[$counter] . '/' .
            $this->subnsid[$counter] . ')"></td></tr>
        ';
        $counter++;
      }

      $subnscounter = 0;
      for($count=1; $count <= $nbrows; $count++) {
        $subnscounter++;
        $result .= '
          <tr><td><input
           type="text" name="subns' . $subnscounter . '"></td>
            <td><input type="text" onchange="v(this)" name="subnsa' . $subnscounter . '">
              </td>';
        if ($advanced) {
          $result .= '
          <td><input type="text" name="subnsttl' . $subnscounter . '" size="8" value="' . $l['str_primary_default'] . '"></td>
          ';
        }
      }
      $result .= '</table>';
      # END SUBNS-RECORDS

      # BEGIN WWW-RECORDS
      $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_www_zones_title']);
      $result .= sprintf('<p>%s</p>',
          sprintf($l['str_primary_www_zones_expl_on_x_x'],
              $config->sitename, $this->zonename));
      $result .= '<table>';
      $result .= sprintf('<th>%s</th>', $l['str_primary_www_zones_zone']);
      $result .= sprintf('<th>%s</th>', $l['str_primary_www_address']);
      $result .= sprintf('<th>%s</th>', $l['str_primary_www_zones_type']);
      if ($advanced) {
        $result .= '<th>TTL</th>';
      }
      $result .= sprintf('<th>%s</th>', $l['str_delete']);

      $counter = 0;
      while (isset($this->www[$counter])) {
        $deletecount++;
        $result .= '<tr>';
        $result .= sprintf('<td>%s</td>', $this->www[$counter]);
        $result .= sprintf('<td>%s</td>', $this->wwwa[$counter]);
        $result .= sprintf('<td>%s</td>', $this->frameRedirect($this->wwwr[$counter]));
        if ($advanced) {
          $result .= sprintf('<td>%s</td>', $this->PrintTTL($this->wwwttl[$counter]));
        }
        $result .= sprintf(
            '<td><input type="checkbox" name="delete%d" value="www(%s-%s)"></td>',
            $deletecount, $this->www[$counter], $this->wwwid[$counter]);
        $result .= '</tr>';
        $counter++;
      }

      $wwwcounter = 0;
      for ($count=1; $count <= $nbrows; $count++) {
        $wwwcounter++;
        $result .= '<tr>';
        $result .= sprintf('<td><input type="text" onchange="v(this)" name="www%d"></td>', $wwwcounter);
        $result .= sprintf('<td><input type="text" name="wwwa%d"></td>', $wwwcounter);
        $result .= '<td>';
        $labelfmt = '<nobr><label><input type="radio" name="wwwr%d" value="%s">%s</label></nobr><br>';
        $result .= sprintf($labelfmt, $wwwcounter, "r", $this->frameRedirect("r"));
        $result .= sprintf($labelfmt, $wwwcounter, "R", $this->frameRedirect("R"));
        $result .= sprintf($labelfmt, $wwwcounter, "F", $this->frameRedirect("F"));
        $result .= '</td>';
        if ($advanced) {
          $result .= sprintf('<td><input type="text" name="wwwttl%d" size="8" value="%s"></td>',
              $wwwcounter, $l['str_primary_default']);
        }
        $result .= '</tr>';
      }
      $result .= '</table>';
      # END WWW-RECORDS

    } // end not reverse zone

    $result .= sprintf('<h3 class="boxheader">%s</h3>', $l['str_primary_allow_transfer_title']);
    $result .= sprintf('<p>%s</p>', $l['str_primary_allow_transfer_expl']);
    $result .= '<table>';
    $result .= sprintf(
        '<tr><td>%s<input id="xferip" type="text" name="xferip" value="%s"></td></tr>',
        $l['str_primary_allow_transfer_ip_allowed'],
        $this->xfer == "any" ? "" : $this->xfer);
    $result .= '</table>';

    $result .= '<input type="hidden" name="valid" value="1">';
    $result .= '<table id="submit"><tr><td>';
    $result .= sprintf('<input type="submit" name="submit" value="%s">',
        $l['str_primary_generate_zone_button']);
    $result .= sprintf('<input type="reset" value="%s">',
        $l['str_primary_reset_form_button']);
    $result .= '</td></tr></table>';
    $result .= '</form>';

    return $result;
  }

  function verifyAllTTL($httpvars) {
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
    return empty($this->error);
  }

  /**
   * Process params from primarymodifyform() form:
   * for each record type execute addTYPERecord, execute updateSOA
   * and outputs result & config file
   *
   *@access public
   *@param array $params contains $VARS ($HTTP_GET_VARS or POST), $xferip and SOA params
   *@return string HTML result
   */
  function printModified($params) {
    global $db;
    global $config;
    global $html,$l;

    list($VARS,$xferip,$defaultttl,$soarefresh,$soaretry,$soaexpire,$soaminimum,
      $modifyptr,$modifyptripv6,$modifya) = $params;
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
    if ($this->reversezone) {
      $ptr = retrieveArgs("ptr", $VARS);
      $ptrname = retrieveArgs("ptrname", $VARS);
      $ptrttl = retrieveArgs("ptrttl", $VARS);
      $delegatefrom = retrieveArgs("delegatefrom", $VARS);
      $delegateto = retrieveArgs("delegateto", $VARS);
      $delegatettl = retrieveArgs("delegatettl",$VARS);
      $delegateuser = retrieveArgs("delegateuser",$VARS);
      $gen = retrieveArgs("gen", $VARS);
    } else {
      $mxsrc = retrieveArgs("mxsrc", $VARS);
      $mx = retrieveArgs("mx", $VARS);
      $mxttl = retrieveArgs("mxttl",$VARS);
      $mxpref = retrieveArgs("mxpref", $VARS);

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
    if ($this->reversezone) {
      $result .= $this->AddPTRRecord($this->zoneid,$ptr,$ptrname,$ptrttl,$modifya);
      $result .= $this->AddGenerateRecord($this->zoneid,$gen,$modifya);
      $result .= $this->AddDELEGATERecord($delegatefrom,$delegateto,$delegateuser,$delegatettl);
      $result .= $this->AddSUBNSRecord($subns,$subnsa,$subnsttl);
    } else {
      $result .= $this->AddMXRecord($mxsrc,$mx,$mxpref,$mxttl);
      if ($this->user->ipv6) {
        $result .= $this->AddAAAARecord($this->zoneid,$aaaa,$aaaaname,$aaaattl,$modifyptripv6);
      }
      $result .= $this->AddARecord($this->zoneid,$a,$aname,$attl,$modifyptr);
      $result .= $this->AddSRVRecord($srvname,$srvpriority,$srvweight,$srvport,$srvvalue,$srvttl);
      $result .= $this->AddSUBNSRecord($subns,$subnsa,$subnsttl);
      $result .= $this->AddWWWRecord($www,$wwwa,$wwwr,$wwwttl);
    }
    $result .= $this->AddCNAMERecord($cname,$cnamea,$cnamettl);
    $result .= $this->AddTXTRecord($txt,$txtstring,$txtttl);

    if ($this->UpdateSOA($xferip,$defaultttl,$soarefresh,$soaretry,$soaexpire,$soaminimum) == 0) {
      $result .= sprintf($html->string_error, $this->error) . '<br>';
    } else {
      $result .= sprintf($l['str_primary_new_serial_x'], $this->serial) . "<br>";

      // check for errors
      // - generate zone file in /tmp/zonename
      if (!$this->generateConfigFile()) {
        $this->flagErroneous($this->error);
        $result .= sprintf($html->string_error, $this->error) . '<br>';
      } else {

        // - do named-checkzone $zonename /tmp/zonename and return result
        $checker = "$config->binnamedcheckzone ".escapeshellarg($this->zonename)." ".
          $this->tempZoneFile();
        $check = shell_exec(escapeshellcmd($checker));
        // if ok
        if (preg_match("/OK/", $check)) {
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
          } else {
            $result .= fread($fd, filesize($this->tempZoneFile()));
            fclose($fd);
          }
          $result .= "</pre>
          </p>&nbsp;";
          unlink($this->tempZoneFile());
          $result .= $this->flagModified($this->zoneid);
        } else {
          $this->flagErroneous($check);
          $result .= $l['str_primary_zone_error_warning'] . ':
          <br>
          <pre>' . $check . '</pre>
          ' .
          sprintf($l['str_primary_error_if_engine_error_x_contact_admin_x'],
            '<a  href="mailto:' . $config->contactemail . '">',
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
          } else {
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




  function deleteARecord($name,$id,$ip,$reverse) {
    global $db;
    global $html,$config,$l;

    $result = sprintf(
      $l['str_primary_deleting_a_x'],
      htmlspecialchars($name) . "/" . htmlspecialchars($ip)) . "... ";
    if (!empty($reverse)) {
      // look for reverse
      // check if managed by user
      // etc...

      // if reverse IP is managed by current user, update PTR
      // else check if reverse IP delegation exists (ie as CNAME)
      # TODO: handle AAAA as well
      $result .= $l['str_primary_looking_for_reverse'] . "... ";
        // construct reverse zone
      $revz = array_reverse(explode('.', $ip));
      $firstip = $revz[0];
      $reversezone = implode('.', array_slice($revz, 1)) . ".in-addr.arpa";
      if ($this->Exists($reversezone,'P')) {
        $alluserzones = $this->user->listallzones();
        $ismanaged=0;
        while($userzones = array_pop($alluserzones)) {
          if (!strcmp($reversezone,$userzones[0])) {
            $ismanaged=1;
          }
        }
        if ($ismanaged) {
          // modification allowed because same owner
          // looking for zoneid
          $result .= $l['str_primary_zone_managed_by_you'] . " ";
          $query = "SELECT id FROM dns_zone
            WHERE zone='" . $reversezone . "' AND zonetype='P'";
          $res = $db->query($query);
          $line = $db->fetch_row($res);
          $newzoneid=$line[0];
          if (strcmp($val1,$this->zonename)) {
            $valtodelete = $name . "." . $this->zonename . ".";
          } else {
            $valtodelete = $name;
          }
          $query = "DELETE FROM dns_record
            WHERE zoneid='" . $newzoneid . "'
            AND type='PTR' AND val1='" . $firstip . "'
            AND val2='" . $valtodelete . "'";

          $res = $db->query($query);
          if ($db->error()) {
            $this->error=$l['str_trouble_with_db'];
          } else {
            $result .= $this->flagModified($newzoneid);
            $this->updateSerial($newzoneid);
          }
        } else {
          // zone exists, but not managed by current user.
          // check for subzone managed by current user
          $result .= $l['str_primary_main_zone_not_managed_by_you'] . "... ";
          $query = "SELECT zone,id FROM dns_zone WHERE
            userid='" . $this->user->userid . "'
            AND zone like '%." . $reversezone . "'";
          $res = $db->query($query);
          $newzoneid = 0;
          while($line = $db->fetch_row($res)) {
            $range =array_pop(array_reverse(split('\.',$line[0])));
            list($from,$to) = split('-',$range);
            if (!empty($to)) {
              if (($firstip >= $from) && ($firstip <= $to)) {
                $newzoneid=$line[1];
              }
            } else {
              list($start, $length) = split('/', $range);
              if ($firstip >= $start && $length>0 && $length<32 && $firstip < ($start+pow(2, 32-$length))) {
                $newzoneid=$line[1];
              }
            }
          }
          if ($newzoneid) {
            if (strcmp($val1,$this->zonename)) {
              $valtodelete = $name . "." . $this->zonename . ".";
            } else {
              $valtodelete = $name;
            }
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $newzoneid . "'
              AND type='PTR' AND val1='" . $firstip . "'
              AND val2='" . $valtodelete . "'";

            $res = $db->query($query);
            if ($db->error()) {
              $this->error=$l['str_trouble_with_db'];
            } else {
              $result .= $this->flagModified($newzoneid);
              $this->updateSerial($newzoneid);
            }
          } else {
            // no zone found
            $result .= $l['str_primary_reverse_exists_but_ip_not_manageable'] . "<br>";
          }

        }
      } else {
        $result .=
          sprintf($l['str_primary_not_managed_by_x'],
            $config->sitename) . "<br>";
      }
    } // end if updatereverse
    if ($id) {
      $query = "DELETE FROM dns_record
        WHERE zoneid='" . $this->zoneid . "'
        AND type='A' AND id='" . $id . "'";
    } else {
      $query = "DELETE FROM dns_record
        WHERE zoneid='" . $this->zoneid . "'
        AND type='A' AND val1='" . addslashes($name) . "' AND val2='" . addslashes($ip) . "'";
    }
    $res = $db->query($query);
    if ($db->error()) {
      $this->error=$l['str_trouble_with_db'];
    } else {
      $result .= $l['str_primary_deleting_ok'] . "<br>\n";
    }
    return $result;
  }


  function deletePTRRecord($ip,$id,$name,$reverse) {
    global $db;
    global $html,$config,$l;

    $result = sprintf($l['str_primary_deleting_ptr_x'],
      htmlspecialchars($ip . "/" . $name)) . "... ";

    if (!empty($reverse)) {
      // if "normal" zone is managed by current user, update A
      // remove all before first dot, and last char.
      $newzone = substr(substr(strstr($name, '.'),1),0,-1);
      $newa = substr($name, 0, strlen($name) - strlen($newzone) -2);
      // construct new IP
      // zone *.in-addr.arpa or *.ip6.arpa
      $iplist = array_slice(explode('.', $this->zonename), 0, -2);
      $newip = "";
      $count = 0; // we have to count in case of zub-zones aa.bb.cc.dd-ee
      while($ipitem = array_pop($iplist)) {
        $count++;
        if (count < 4) {
          $newip .= "." . $ipitem;
        }
      }
      $newip = substr($newip,1) . "." . $ip;
      $result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "... ";
      if ($this->Exists($newzone,'P')) {
        $alluserzones = $this->user->listallzones();
        $ismanaged=0;
        while($userzones = array_pop($alluserzones)) {
          if (!strcmp($newzone,$userzones[0])) {
            $ismanaged=1;
          }
        }
        if ($ismanaged) {
          // modification allowed because same owner
          // looking for zoneid
          $result .= $l['str_primary_zone_managed_by_you'] . " ";
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
          if ($db->error()) {
            $this->error=$l['str_trouble_with_db'];
          } else {
            $result .= $this->flagModified($newzoneid);
            $this->updateSerial($newzoneid);
          }
        } else {
          // zone exists, but not managed by current user.
          $result .= $l['str_primary_main_zone_not_managed_by_you'];
        }
      } else {
        $result .=
          sprintf($l['str_primary_not_managed_by_x'],
            $config->sitename) . "<br>";
      }
    }

    $query = "DELETE FROM dns_record
      WHERE zoneid='" . $this->zoneid . "'
      AND type='PTR' AND id='" . $id . "'";
    $res = $db->query($query);
    if ($db->error()) {
      $this->error=$l['str_trouble_with_db'];
    } else {
      $result .= $l['str_primary_deleting_ok'] . "<br>\n";
    }
    return $result;
  }


  /**
   * Delete all the A records for a given name in current zone
   *
   *@access public
   *@params name $name of the A records to delete
   *@return result as a string text
   */
  function deleteMultipleARecords($name, $what="") {
    global $db, $html, $l;

    $result = sprintf($l['str_primary_deleting_a_x'], htmlspecialchars($name));
    $result .= "... ";
    if (empty($what)) {
      $what = "'A','AAAA'";
    } else {
      $what = "'$what'";
    }
    $query = sprintf(
        "DELETE FROM dns_record WHERE zoneid='%d' AND type IN (%s) AND val1='%s'" ,
        $this->zoneid, $what, mysql_real_escape_string($name));
    $res = $db->query($query);
    if ($db->error()) {
      $this->error = $l['str_trouble_with_db'];
    } else {
      $result .= $l['str_primary_deleting_ok'];
    }
    $result .= "<br>";
    return $result;
  }

  /**
   * Takes list of items to be deleted, and process them
   *
   *@access public
   *@param array $delete list of items cname(alias), a(name), ns(name), etc..
   *@return string text of result (Deleting XXX record... Ok<br>)
   */
  function delete($delete,$updatereverse,$updatea) {
    global $db;
    global $html,$l;

    $result = '';

    // for each delete entry, delete item cname(alias), a(name), ns(name),
    // mx(name)


    while(list($key,$value) = each($delete)) {
      if ($value != "") {
        $newvalue = preg_replace("/^.*\(([^\)]+)\)/","\\1", $value);

        // name of item to be deleted:
        preg_match("/^(.*)\(/",$value,$item);
        $item = $item[1];

        switch($item) {
          case "www":
            preg_match("/^(.*)-(.*)/",$newvalue,$item);
            $valname=$item[1];
            $valid=$item[2];
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='WWW' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_www_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "srv":
            preg_match("/^(.*)-(.*)/",$newvalue,$item);
            $valname=$item[1];
            $valid=$item[2];
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='SRV' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_srv_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "cname":
            preg_match("/^(.*)-(.*)/",$newvalue,$item);
            $valname=$item[1];
            $valid=$item[2];
            // www    IN    CNAME    toto.
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='CNAME' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_cname_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "a":
            // www    IN    A      IP
            preg_match("/^(.*)\/(.*)/",$newvalue,$item);
            $val1 = $item[1];
            $val2 = $item[2];
            if (preg_match("/^(.*)-(.*)/",$val2,$itembis)) {
              $valid=$itembis[1];
              $valip=$itembis[2];
            } else {
              $valid=0;
              $valip=$val2;
            }
            $result .= $this->DeleteARecord($val1,$valid,$valip,$updatereverse);
            $query = "";
            break;

          case "aaaa":
            // www    IN    AAAA      IPv6
            preg_match("/^(.*)\/(.*)/",$newvalue,$item);
            $val1 = $item[1];
            $val2 = $item[2];
            # TODO: use DeleteARecord function(!)
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='AAAA' AND id='" . mysql_real_escape_string($val2) . "'";
            $result .= sprintf($l['str_primary_deleting_aaaa_x'],
              htmlspecialchars($val1)) . "... ";
            break;

          case "txt":
            // www    IN    TXT      String
            preg_match("/^(.*)\/(.*)/",$newvalue,$item);
            $val1 = $item[1];
            $val2 = $item[2];
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='TXT' AND id='" . mysql_real_escape_string($val2) . "' ";
            $result .= sprintf($l['str_primary_deleting_txt_x'],
              htmlspecialchars($val1)) . "... ";
            break;

          case "ptr":
            // ip    IN    PTR      name
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
            //     IN    NS    name
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='NS' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_ns_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "mx":
            preg_match("/^(.*)\/(.*)/",$newvalue,$item);
            $valname=$item[1];
            $valid=$item[2];
            // *     IN    MX    pref    name
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='MX' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_mx_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "subns":
            // newzone  IN    NS    ns.name
            preg_match("/^(.*)\/(.*)/",$newvalue,$item);
            $val1 = $item[1];
            $val2 = $item[2];
            preg_match("/^(.*)-(.*)/",$val2,$itembis);
            $valname=$item[1];
            $valid=$item[2];
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='SUBNS' AND id='" . mysql_real_escape_string($valid) . "'";
            $result .= sprintf($l['str_primary_deleting_sub_zone_x'],
              htmlspecialchars($valname)) . "... ";
            break;

          case "delegate":
            // $newvalue: XX-YY
            list($from,$to) = split('-',$newvalue);
            // remove CNAMEs
            for($cnamecounter=intval($from); $cnamecounter<=intval($to); $cnamecounter++) {
              $query = "DELETE FROM dns_record
                WHERE zoneid='" . $this->zoneid . "'
                AND type='CNAME' AND val1='" . $cnamecounter . "'";
              $res = $db->query($query);
              if ($db->error()) {
                $this->error=$l['str_trouble_with_db'];
              }
            }

            // remove NS
            $query = "DELETE FROM dns_record WHERE zoneid='" . $this->zoneid . "'
              AND type='SUBNS' AND val1='" . mysql_real_escape_string($newvalue) . "'";
            $res = $db->query($query);
            if ($db->error()) {
              $this->error=$l['str_trouble_with_db'];
            }

            // delete zone
            // use zoneDelete()
            $query = "SELECT userid FROM dns_zone WHERE zone='"
                . mysql_real_escape_string($newvalue) . "." . $this->zonename . "' AND zonetype='P'";
            $res = $db->query($query);
            $line=$db->fetch_row($res);
            $zonetodelete = new Zone($newvalue . "." . $this->zonename, 'P','',$line[0]);
            $zonetodelete->zoneDelete();

            // delete DELEGATE record
            $query = "DELETE FROM dns_record
              WHERE zoneid='" . $this->zoneid . "'
              AND type='DELEGATE' AND val1='" . mysql_real_escape_string($newvalue) . "'";
            break;
        }
      }
      if (!empty($query)) {
        $res = $db->query($query);
        if ($db->error()) {
          $this->error = $l['str_trouble_with_db'];
        } else {
          $result .= $l['str_primary_deleting_ok'] . "<br>\n";
        }
      }
    }
    return $result;
  }



  /**
   * Add an MX record to the current zone
   *
   *@access private
   *@param string $mx name of MX
   *@param int $pref preference value for this MX
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding MX Record... Ok)
   */
  function addMXRecord($mxsrc,$mx,$pref,$ttl) {
    global $db, $html,$l;

    $result = '';
    // for each MX, add MX entry
    $i = 0;
    while(list($key,$value) = each($mx)) {
      // value = name
      if ($value != "") {
        if (!$this->checkMXName($value) || !$this->checkAName($mxsrc[$i])) {
          // check if matching A record exists ? NOT OUR JOB
          $this->error = sprintf(
            $l['str_primary_bad_mx_name_x'],
            htmlspecialchars($value));
        } else {
          // if checkName, add zone.
          if (checkName($value)) {
            $value .= "." . $this->zonename;
          }
          // if no trailing ".", add one.
          if (strrpos($value, ".") != strlen($value) -1) {
            $value .= ".";
          }

          // pref[$i] has to be an integer
          if (!$this->checkMXPref($pref[$i])) {
            $this->error = sprintf(
              $l['str_primary_preference_for_mx_x_has_to_be_int'],
              htmlspecialchars($value));
          } else {
            if ($pref[$i] == "") {
              $pref[$i] = 0;
            }

            // Check if record already exists
            $query = "SELECT count(*) FROM dns_record WHERE
            zoneid='" . $this->zoneid . "' AND type='MX'
            AND val1='" . $mxsrc[$i] . "' AND val3='" .$value."'";
            $res = $db->query($query);
            $line = $db->fetch_row($res);
            if ($line[0] == 0) {
              $result .= sprintf($l['str_primary_adding_mx_x'],
                htmlspecialchars($value)) . "... ";
              $ttlval = $this->fixDNSTTL($ttl[$i]);
              $query = "INSERT INTO dns_record (zoneid, type, val1, val2, val3, ttl)
                VALUES ('" . $this->zoneid . "', 'MX', '"
                . $mxsrc[$i] . "', '" . $pref[$i] . "','" .$value. "','" . $ttlval . "')";
              $db->query($query);
              if ($db->error()) {
                $this->error = $l['str_trouble_with_db'];
              } else {
                $result .= $l['str_primary_ok'] . "<br>\n";
              }
            } else { // record already exists
              $result .=
                sprintf($l['str_primary_warning_mx_x_exists_not_overwritten'],
                  htmlspecialchars($value)) ."<br>\n";
            }
          }
        }
      }
      $i++;
    }
    return $result;
  }

  /**
   * Add an NS record to the current zone
   *
   *@access private
   *@param string $ns name of NS
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding NS Record... Ok)
   */
  function addNSRecord($ns,$ttl) {
    global $db,$html,$l;

    $result = '';
    $i=0;
    // for each NS, add NS entry
    while(list($key,$value) = each($ns)) {
      // value = name
      if ($value != "") {
        if (!$this->checkNSName($value)) {
          $this->error = sprintf(
            $l['str_primary_bad_ns_x'],
            htmlspecialchars($value));
        } else {
          // if no trailing ".", add one
          if (strrpos($value, ".") != strlen($value) -1) {
            $value .= ".";
          }

          // Check if record already exists
          $query = "SELECT count(*) FROM dns_record WHERE
            zoneid='" . $this->zoneid .
            "' AND type='NS' AND val1='" . $value . "'";
          $res = $db->query($query);
          $line = $db->fetch_row($res);
          if ($line[0] == 0) {
            $result .= sprintf($l['str_primary_adding_ns_x'],
              htmlspecialchars($value)) . "... ";
            $ttlval = $this->fixDNSTTL($ttl[$i]);
            $query = "INSERT INTO dns_record (zoneid, type, val1,ttl)
              VALUES ('" . $this->zoneid . "', 'NS', '"
              . $value . "','" . $ttlval . "')";
            $db->query($query);
            if ($db->error()) {
              $this->error = $l['str_trouble_with_db'];
            } else {
              $result .= $l['str_primary_ok'] . "<br>\n";
            }
          } else {
            $result .=
              sprintf($l['str_primary_warning_ns_x_exists_not_overwritten'],
                htmlspecialchars($value)) . "<br>\n";
          }
        }
      }
      $i++;
    }
    return $result;
  }

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
  function addARecord($zoneid,$a,$aname,$ttl,$updatereverse) {
    global $db,$html,$config,$l;
    $result = '';
    // for each A, add A entry
    $i = 0;
    while(list($key,$value) = each($aname)) {
      if ($value != "") {
        if (! $this->checkAName($value) ) {
          $this->error = sprintf(
            $l['str_primary_bad_a_x'],
            htmlspecialchars($value));
        } else {
          // a[$i] has to be an ip address
          if ($a[$i] == "") {
            $this->error = sprintf(
              $l['str_primary_no_ip_for'],
              htmlspecialchars($value));
          } else {
            if (!$this->checkAValue($a[$i])) {
              $this->error = sprintf(
                $l['str_primary_x_ip_has_to_be_ip'],
                htmlspecialchars($value));
            } else {
              // Check if record already exists
              $query = "SELECT count(*) FROM dns_record WHERE
                zoneid='" . $zoneid . "' AND type='A'
                AND val1='" . mysql_real_escape_string($value) . "'";
              $res = $db->query($query);
              $line = $db->fetch_row($res);
              if ($line[0] == 0) {
                // check if CNAME record not already exists
                $query = "SELECT count(*) FROM dns_record WHERE
                  zoneid='" . $zoneid . "' AND type='CNAME'
                  AND val1='" . mysql_real_escape_string($value) . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf($l['str_primary_adding_a_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $zoneid . "',
                    'A', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($a[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    if ($updatereverse) {
                      $result .= $this->UpdateReversePTR($a[$i],$value,'A');
                    } // end if updatereverse
                  } // end "primary OK"
                } else { // end check CNAME
                  $result .=
                    sprintf($l['str_primary_warning_cname_x_exists_not_overwritten'],
                      htmlspecialchars($value)) . "<br>\n";
                }
              } else { // end check A
                // check if already same IP or not. If yes, do not
                // change anything
                // if no, warn & assume it is round robin.
                $query .= " AND val2='" . $a[$i] . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf($l['str_primary_warning_a_x_exists_with_diff_value'],
                    htmlspecialchars($value)) . ' ';
                  $result .= sprintf($l['str_primary_adding_a_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $zoneid . "',
                    'A', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($a[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    if ($updatereverse) {
                      $result .= $this->UpdateReversePTR($a[$i],$value,'A');
                    } // end updatereverse
                  } // end primary ok

                } else {
                  $result .= sprintf($l['str_primary_a_x_with_same_ip'],
                    htmlspecialchars($value)). '<br>';
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
  function addAAAARecord($zoneid,$aaaa,$aaaaname,$ttl,$updatereverse) {
    global $db,$config,$html,$l;

    $result = '';
    // for each AAAA, add AAAA entry
    $i = 0;
    while(list($key,$value) = each($aaaaname)) {
      if ($value != "") {
        if (! $this->checkAAAAName($value) ) {
          $this->error = sprintf(
            $l['str_primary_aaaa_bad_aaaa_x'],
            htmlspecialchars($value));
        } else {
          // a[$i] has to be an ipv6 address
          if ($aaaa[$i] == "") {
            $this->error = sprintf(
              $l['str_primary_no_ipv6_for_x'],
              htmlspecialchars($value));
          } else {
            if (! $this->checkAAAAValue($aaaa[$i]) ) {
              $this->error = sprintf(
                $l['str_primary_x_ip_has_to_be_ipv6'],
                htmlspecialchars($value . "/" .$aaaa[$i]));
            } else {
              // Check if record already exists
              $query = "SELECT count(*) FROM dns_record WHERE
                zoneid='" . $zoneid . "' AND type='AAAA'
                AND val1='" . mysql_real_escape_string($value) . "'";
              $res = $db->query($query);
              $line = $db->fetch_row($res);
              if ($line[0] == 0) {
                // check if CNAME record not already exists
                $query = "SELECT count(*) FROM dns_record WHERE
                  zoneid='" . $zoneid . "' AND type='CNAME'
                  AND val1='" . mysql_real_escape_string($value) . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf($l['str_primary_adding_aaaa_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $zoneid . "',
                    'AAAA', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($aaaa[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    if ($updatereverse) {
                      $result .= $this->UpdateReversePTR($aaaa[$i],$value,'AAAA');
                    } // end if updatereverse
                  } // end "primary OK"
                } else { // end check CNAME
                  $result .= sprintf(
                    $l['str_primary_warning_cname_x_exists_not_overwritten'],
                    htmlspecialchars($value)) . "<br>\n";
                }
              } else { // end check AAAA

                // check if already same IP or not. If yes, do not
                // change anything
                // if no, warn & assume it is round robin.
                $query .= " AND val2='" . $aaaa[$i] . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf(
                    $l['str_primary_warning_aaaa_x_exists_with_diff_value'],
                    htmlspecialchars($value)) . ' ';
                  $result .= sprintf(
                    $l['str_primary_adding_aaaa_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $this->zoneid . "',
                    'AAAA', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($aaaa[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    if ($updatereverse) {
                      $result .= $this->UpdateReversePTR($aaaa[$i],$value,'AAAA');
                    } // end updatereverse
                  }

                } else {
                  $result .= sprintf($l['str_primary_aaaa_x_with_same_ip'],
                    htmlspecialchars($value)) . '<br>';
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

  function addGenerateRecord($zoneid, $gen, $updatereverse) {
    $ptr = array();
    $ptrname = array();
    $ttl = array();
    if (!preg_match('/\$/', $gen[2]))
      $gen[2] = "$." . $gen[2];
    for ($x = intval($gen[0]); $x <= intval($gen[1]); $x++) {
      $ptr[] = $x;
      $ptrname[] = preg_replace('/\$/', $x, $gen[2]);
      $ttl[] = -1;
    }
    return $this->AddPTRRecord($zoneid, $ptr, $ptrname, $ttl, $updatereverse);
  }

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
  function addPTRRecord($zoneid,$ptr,$ptrname,$ttl,$updatereverse) {
    global $db, $html,$l,$config;

    $result = '';
    // for each PTR, add PTR entry
    $i = 0;
    while(list($key,$value) = each($ptr)) {
      if ($value != "") {
        if (! $this->checkPTRName($value) ) {
          $this->error = sprintf(
            $l['str_primary_bad_ptr_x'],
            htmlspecialchars($value));
        } else {
          if ($ptrname[$i] == "") {
            $this->error = sprintf(
              $l['str_primary_no_name_for_x'],
              htmlspecialchars($value));
          } else {
            if (ereg('\.$', $value)) {
              $result .= sprintf(
                $l['str_primary_x_name_ends_with_dot'],
                htmlspecialchars($value)) . "<br>\n";
            }
            if (! $this->checkPTRValue($ptrname[$i]) ) {
              $this->error = sprintf(
                $l['str_primary_x_name_has_to_be_fully_qualified_x'],
                htmlspecialchars($value), 
                htmlspecialchars($ptrname[$i]));
            } else {
              // Check if record already exists
              $query = "SELECT count(*) FROM dns_record WHERE
                zoneid='" . $zoneid . "' AND type='PTR'
                AND val1='" . mysql_real_escape_string($value) . "'";
              $res = $db->query($query);
              $line = $db->fetch_row($res);
              if ($line[0] == 0) {
                // check if CNAME record not already exists
                $query = "SELECT count(*) FROM dns_record WHERE
                  zoneid='" . $zoneid . "' AND type='CNAME'
                  AND val1='" . mysql_real_escape_string($value) . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf($l['str_primary_adding_ptr_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $zoneid . "',
                    'PTR', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($ptrname[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    // update associated A record
                    if ($updatereverse) {
                      // if "normal" zone is managed by current user, update A
                      // remove all before first dot, and last char.
                      $newzone = substr(substr(strstr($ptrname[$i],'.'),1),0,-1);
                      $newa = substr($ptrname[$i],0,strlen($ptrname[$i]) - strlen($newzone) -2);
                      // construct new IP
                      // zone *.in-addr.arpa or *.ip6.arpa
                      $iplist = array_slice(explode('.', $this->zonename), 0, -2);
                      $ipreg="[^0-9]";
                      $v6 = preg_match("/\.ip6\.(arpa|int)$/", $this->zonename);
                      if ($v6) {
                        $newval = "";
                        $iplistv = split('\.', $value);
                        while(NULL!=($ipitem = array_pop($iplistv))) {
                          $newval .= "." . $ipitem;
                        }
                        $value = substr($newval,1);
                        $ipreg="[^0-9a-f]";
                      }
                      $newip = "";
                      $count = 0;
                      while(NULL!=($ipitem = array_pop($iplist))) {
                        $count++;
                        if (eregi($ipreg, $ipitem)) break;
                        $newip .= "." . $ipitem;
                      }
                      $newip = substr($newip,1) . "." . $value;
                      if ($v6) {
                        // for v6 get rid of all the dots and
                        // group by four digits, colon separated
                        $newip = preg_replace("/\./", "", $newip);
                        $h="([0-9a-f]{4})";
                        $hreg = "/^$h$h$h$h$h$h$h$h$/i";
                        $newip = preg_replace($hreg, "$1:$2:$3:$4:$5:$6:$7:$8", $newip);
                      }
                      $result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "... ";
                      if ($this->Exists($newzone,'P')) {
                        $alluserzones = $this->user->listallzones();
                        $ismanaged=0;
                        while($userzones = array_pop($alluserzones)) {
                          if (!strcmp($newzone,$userzones[0])) {
                            $ismanaged=1;
                          }
                        }
                        if ($ismanaged) {
                          // modification allowed because same owner
                          // looking for zoneid
                          $result .= $l['str_primary_zone_managed_by_you'] . " ";
                          $query = "SELECT id FROM dns_zone
                            WHERE zone='" . $newzone . "' AND zonetype='P'";
                          $res = $db->query($query);
                          $line = $db->fetch_row($res);
                          $newzoneid=$line[0];
                          if ($v6)
                            $result .= $this->AddAAAARecord($newzoneid,
                              array($newip), array($newa),
                              array($l['str_primary_default']), NULL);
                          else
                            $result .= $this->AddARecord($newzoneid,
                              array($newip), array($newa),
                              array($l['str_primary_default']), NULL);
                          if (!$this->error) {
                            $result .= $this->flagModified($newzoneid);
                            $this->updateSerial($newzoneid);
                          }
                        } else {
                          // zone exists, but not managed by current user.
                          $result .= $l['str_primary_main_zone_not_managed_by_you'];
                        }
                      } else {
                        $result .= sprintf(
                          $l['str_primary_not_managed_by_x'],
                          $config->sitename) . "<br>";
                       }
                    } // end update reverse
                  } // update OK
                } else { // end check CNAME
                  $result .= sprintf(
                    $l['str_primary_warning_cname_x_exists_not_overwritten'],
                    htmlspecialchars($value)) . "<br>\n";
                }
              } else { // end check A

                // check if already same name or not. If yes, do not
                // change anything
                // if no, warn & assume it is round robin.
                $query .= " AND val2='" . $ptrname[$i] . "'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf(
                    $l['str_primary_warning_ptr_x_exists_with_diff_value'],
                    htmlspecialchars($value)) . ' ';
                  $result .=  sprintf(
                    $l['str_primary_adding_ptr_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                    VALUES ('" . $zoneid . "',
                    'PTR', '" . mysql_real_escape_string($value) . "',
                    '" . mysql_real_escape_string($ptrname[$i]) . "','" . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                    // update associated A record
                    if ($updatereverse) {
                      // if "normal" zone is managed by current user, update A
                      // remove all before first dot, and last char.
                      $newzone = substr(substr(strstr($ptrname[$i],'.'),1),0,-1);
                      $newa = substr($ptrname[$i],0,strlen($ptrname[$i]) - strlen($newzone) -2);
                      // construct new IP
                      // zone *.in-addr.arpa or *.ip6.arpa
                      $iplist = array_slice(explode('.', $this->zonename), 0, -2);
                      $newip = "";
                      $count = 0; // we have to count in case of zub-zones aa.bb.cc.dd-ee
                      while($ipitem = array_pop($iplist)) {
                        $count++;
                        if ($count == 4) break;
                        if (preg_match("/[^0-9]/", $ipitem)) break;
                        $newip .= "." . $ipitem;
                      }
                      $newip = substr($newip,1) . "." . $value;
                      $result .= sprintf($l['str_primary_looking_for_zone_x'],$newzone). "... ";
                      if ($this->Exists($newzone,'P')) {
                        $alluserzones = $this->user->listallzones();
                        $ismanaged=0;
                        while($userzones = array_pop($alluserzones)) {
                          if (!strcmp($newzone,$userzones[0])) {
                            $ismanaged=1;
                          }
                        }
                        if ($ismanaged) {
                          // modification allowed because same owner
                          // looking for zoneid
                          $result .= $l['str_primary_zone_managed_by_you'];
                          $query = "SELECT id FROM dns_zone
                            WHERE zone='" . $newzone . "' AND zonetype='P'";
                          $res = $db->query($query);
                          $line = $db->fetch_row($res);
                          $newzoneid=$line[0];
                          $result .= $this->AddARecord($newzoneid,array($newip),array($newa),
                            array($l['str_primary_default']),NULL);
                          if (!$this->error) {
                            $result .= $this->flagModified($newzoneid);
                            $this->updateSerial($newzoneid);
                          }
                        } else {
                          // zone exists, but not managed by current user.
                          $result .= $l['str_primary_main_zone_not_managed_by_you'];
                        }
                      } else {
                        $result .= sprintf(
                          $l['str_primary_not_managed_by_x'],
                          $config->sitename) . "<br>";
                       }
                    } // end update reverse

                  }

                } else {
                  $result .= sprintf(
                    $l['str_primary_warning_ptr_x_already_exists_not_overwritten'],
                    htmlspecialchars($value)) . '<br>';
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

  /**
   * Add an CNAME record to the current zone
   *
   *@access private
   *@param string $cname name of CNAME record
   *@param string $cnamea record pointed by this CNAME record
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding CNAME Record... Ok)
   */
  function addCNAMERecord($cname,$cnamea,$ttl) {
    global $db, $html,$l;

    // for each CNAME, add CNAME entry
    $i = 0;
    $result = "";
    while(list($key,$value) = each($cname)) {
      if ($value != "") {
        if (! $this->checkCNAMEName($value) ) {
          $this->error = sprintf(
            $l['str_primary_bad_cname_x'],
            htmlspecialchars($value));
        }elseif(! $this->checkCNAMEValue($cnamea[$i]) ) {
          $this->error = sprintf(
            $l['str_primary_bad_cname_x'],
            htmlspecialchars($cnamea[$i]));
        } else {
          if ($cnamea[$i] =="") {
            $this->error = sprintf(
              $l['str_primary_no_record_x'],
              htmlspecialchars($value));
          } else {
            // Check if record already exists
            $query = "SELECT count(*) FROM dns_record WHERE
              zoneid='" . $this->zoneid . "' AND type='CNAME'
              AND val1='" . mysql_real_escape_string($value) . "'";
            $res = $db->query($query);
            $line = $db->fetch_row($res);
            if ($line[0] == 0) {
              // check if A record don't already exist
              $query = "SELECT count(*) FROM dns_record WHERE
                zoneid='" . $this->zoneid . "' AND type='A'
                AND val1='" . mysql_real_escape_string($value) . "'";
              $res = $db->query($query);
              $line = $db->fetch_row($res);
              if ($line[0] == 0) {
                $result .= sprintf(
                  $l['str_primary_adding_cname_x'],
                  htmlspecialchars($value)) . "... ";
                $ttlval = $this->fixDNSTTL($ttl[$i]);
                $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                  VALUES ('" . $this->zoneid . "', 'CNAME', '"
                  . mysql_real_escape_string($value) . "', '" 
                  . mysql_real_escape_string($cnamea[$i]) . "','" . $ttlval . "')";
                $db->query($query);
                if ($db->error()) {
                  $this->error = $l['str_trouble_with_db'];
                } else {
                  $result .= $l['str_primary_ok'] . "<br>\n";
                }
              } else { // A record present
                $result .= sprintf(
                  $l['str_primary_warning_a_x_exists_not_overwritten'],
                  htmlspecialchars($value)) . "<br>\n";
              }
            } else {
              $result .= sprintf(
                $l['str_primary_warning_cname_x_exists_not_overwritten'],
                htmlspecialchars($value)) . "<br>\n";
            }
          }
        }
      }
      $i++;
    }
    return $result;
  }



  /**
   * Add a TXT record to the current zone
   *
   *@access private
   *@param string $txt name of TXT record
   *@param string $txtstring string pointed by this TXT record
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding TXT Record... Ok)
   */
  function addTXTRecord($txt, $txtstring, $ttl) {
    global $db, $html,$l;

    // for each TXT, add TXT entry
    $i = -1;
    $result = "";
    while(list($key,$value) = each($txt)) {
      $i++;
      if ($value == "") {
        continue;
      }
      if (!$this->checkTXTName($value)) {
        $this->error = sprintf(
            $l['str_primary_bad_txt_x'], htmlspecialchars($value));
        continue;
      }
      if ($txtstring[$i] =="") {
        $this->error = sprintf(
          $l['str_primary_no_record_x'],
          htmlspecialchars($value));
        continue;
      }

      // Check if CNAME record already exists
      $query = "SELECT count(*) FROM dns_record WHERE
          zoneid='" . $this->zoneid . "' AND type='CNAME'
          AND val1='" . mysql_real_escape_string($value) . "'";
      $res = $db->query($query);
      $line = $db->fetch_row($res);
      if ($line[0] != 0) {
        $result .= sprintf(
          $l['str_primary_warning_cname_x_exists_not_overwritten'],
          htmlspecialchars($value)) . "<br>\n";
        continue;
      }

      $result .= sprintf(
          $l['str_primary_adding_txt_x'], htmlspecialchars($value)) . "... ";
      $newstring = $txtstring[$i];
      $ttlval = $this->fixDNSTTL($ttl[$i]);
      $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
        VALUES ('" . $this->zoneid . "', 'TXT', '"
        . mysql_real_escape_string($value) . "', '" 
        . mysql_real_escape_string($newstring) . "','" . $ttlval . "')";
      $db->query($query);
      if ($db->error()) {
        $this->error = $l['str_trouble_with_db'];
      } else {
        $result .= $l['str_primary_ok'] . "<br>\n";
      }
    }
    return $result;
  }

  function frameRedirect($x) {
    global $l;

    switch($x) {
      case "R":
        return $l['str_primary_www_redirect'];
      case "r":
        return $l['str_primary_www_redirect_301'];
      case "F":
        return $l['str_primary_www_frame'];
      default:
        return "";
    }
  }

  /**
   * Add a pseudo WWW record to the current zone
   *
   *@access private
   *@param string $www name of WWW record
   *@param string $wwwstring address pointed by this WWW record
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding WWW Record... Ok)
   */
  function addWWWRecord($www, $wwwstring, $wwwr, $ttl) {
    global $db, $html,$l,$config;

    // for each WWW, add WWW entry
    $i = 0;
    $result = "";
    while(list($key, $value) = each($www)) {
      if ($value != "") {
        if (!$this->checkWWWName($value)) {
          $this->error = sprintf(
            $l['str_primary_bad_www_x'],
            htmlspecialchars($value));
        } elseif ($this->frameRedirect($wwwr[$i]) == "") {
          $this->error = $l['str_primary_www_no_type'];
        } else {
          if ($wwwstring[$i] == "") {
            $this->error = sprintf(
              $l['str_primary_no_record_x'],
              htmlspecialchars($value));
          } else {
            if (!$this->checkWWWValue($wwwstring[$i])) {
              $wwwstring[$i] = "http://" . $wwwstring[$i];
            }
            // Check if record already exists
            $query = "SELECT count(*) FROM dns_record WHERE
              zoneid='" . $this->zoneid . "'
              AND val1='" . mysql_real_escape_string($value) . "'
              AND type IN ('CNAME','A','WWW')";
            $res = $db->query($query);
            $line = $db->fetch_row($res);
            if ($line[0] == 0) {
              $result .= sprintf($l['str_primary_adding_www_x_x'],
                $this->frameRedirect($wwwr[$i]),
                htmlspecialchars($value));
              $result .= "... ";
              $ttlval = $this->fixDNSTTL($ttl[$i]);
              $query = sprintf("INSERT INTO dns_record " .
                "(zoneid, type, val1, val2, val3, val4, ttl) " .
                "VALUES ('%s', 'WWW', '%s', '%s', '%s', '%s', '%s')",
                $this->zoneid,
                mysql_real_escape_string($value),
                mysql_real_escape_string($wwwstring[$i]),
                $config->webserverip, $wwwr[$i], $ttlval);
              $db->query($query);
              if ($db->error()) {
                $this->error = $l['str_trouble_with_db'];
              } else {
                $result .= $l['str_primary_ok'];
                $result .= "<br>\n";
              }
            } else {
              $result .= sprintf(
                $l['str_primary_warning_www_x_exists_not_overwritten'],
                htmlspecialchars($value));
              $result .= "<br>\n";
            }
          }
        }
      }
      $i++;
    }
    return $result;
  }

  /**
   * Add a zone delegation to the current zone
   *
   *@access private
   *@param string $subns name of subzone
   *@param string $subnsa name of NS server
   *@param int $ttl ttl value for this record
   *@return string text of result (Adding zone NS Record... Ok)
   */
  function addSUBNSRecord($subns, $subnsa, $ttl) {
    global $db, $html,$l;

    // for each SUBNS, add NS entry
    $i = 0;
    $result = "";
    while(list($key,$value) = each($subns)) {
      if ($value != "") {
        if (!$this->checkSUBNSName($value)) {
          $this->error = sprintf(
            $l['str_bad_zone_name_x'],
            htmlspecialchars($value));
        } else {
          if ($subnsa[$i] =="") {
            $this->error = sprintf(
              $l['str_primary_no_ns_x'],
              htmlspecialchars($value));
          } else {
            if (!$this->checkSUBNSValue($subnsa[$i])) {
              $this->error = sprintf(
                $l['str_primary_bad_ns_x'],
                htmlspecialchars($subnsa[$i]));
            }
          }
          if (!$this->error) {
            // Check if record already exists
            // if yes, no problem - multiple different NS possible
            $result .= sprintf($l['str_primary_adding_zone_ns_x'],
              htmlspecialchars($value)) . "... ";
            $query = "SELECT count(*) FROM dns_record
              WHERE zoneid='" . $this->zoneid . "' AND type='SUBNS'
              AND val1='" . mysql_real_escape_string($value) . "'
              AND val2='" . mysql_real_escape_string($subnsa[$i]) . "'";
            $res=$db->query($query);
            $line = $db->fetch_row($res);
            if ($db->error()) {
              $this->error = $l['str_trouble_with_db'];
            } else {
              if ($line[0]==0) {
                $ttlval=$this->fixDNSTTL($ttl[$i]);
                $query = "INSERT INTO dns_record (zoneid, type, val1, val2,ttl)
                  VALUES ('" . $this->zoneid . "', 'SUBNS', '"
                  . mysql_real_escape_string($value) . "', '"
                  . mysql_real_escape_string($subnsa[$i]) . "','" . $ttlval . "')";
                $db->query($query);
                if ($db->error()) {
                  $this->error = $l['str_trouble_with_db'];
                } else {
                  $result .= $l['str_primary_ok'] . "<br>\n";
                }
              } else {
                $result .=sprintf($l['str_primary_warning_ns_x_exists_not_overwritten'],
                  htmlspecialchars($value)) . "<br>\n";
              }
            }
          }
        }
      }
      $i++;
    }
    return $result;
  }

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
  function addDELEGATERecord($delegatefrom, $delegateto, $delegateuser, $ttl) {
    global $db, $html, $l;

    $i = 0;
    $result = "";
    while(list($key,$value) = each($delegatefrom)) {
      if (!empty($value)) {
        $result .= sprintf(
          $l['str_primary_adding_delegate_x'],
          htmlspecialchars($value),
          htmlspecialchars($delegateto[$i])) . "... ";
        if (!ereg('^[0-9]+$',$value)) {
          $this->error = sprintf(
            $l['str_primary_bad_lower_limit_x'],
            htmlspecialchars($value));
        } else {
          if (!ereg('^[0-9]+$',$delegateto[$i])||$delegateto[$i]>255) {
            $this->error = sprintf(
              $l['str_primary_bad_upper_limit_x'],
              htmlspecialchars($delegateto[$i]));
          } else {
            // check if lower if below upper
            if (!($value <= $delegateto[$i])) {
              $this->error = sprintf(
                $l['str_primary_bad_limits_x_x'],
                htmlspecialchars($value),
                htmlspecialchars($delegateto[$i]));
            } else {
              if (empty($delegateuser[$i])) {
                $this->error = $l['str_primary_no_user_for_delegation'];
              } else {
                // check if user is in DB or not
                $newuserid=$this->user->RetrieveId(addslashes($delegateuser[$i]));
                if ($this->user->error) {
                  $this->error = $this->user->error;
                } else {
                  if (!$newuserid) {
                    $this->error = sprintf(
                      $l['str_primary_delegate_user_x_doesnot_exist'],
                      htmlspecialchars($delegateuser[$i]));
                  } else { // user exists
                    // check if items inside this range are already registered or not
                    $query = "SELECT val1 FROM dns_record WHERE zoneid='" .
                      $this->zoneid . "' AND type='DELEGATE'";
                    $res=$db->query($query);
                    if ($db->error()) {
                      $this->error = $l['str_trouble_with_db'];
                    } else {
                      while($line = $db->fetch_row($res)) {
                        list($from,$to)=split('-',$line[0]);
                        if ((($from <= $value) && ($to >= $value)) ||
                          (($from >= $value) && ($from <= $delegateto[$i]))) {
                          $this->error = sprintf(
                            $l['str_primary_delegate_bad_limits_x_x_overlaps_existing_x_x'],
                            htmlspecialchars($value),
                            htmlspecialchars($delegateto[$i]),
                            htmlspecialchars($from),
                            htmlspecialchars($to));
                        }
                      }
                      if (!$this->error) {
                        $ttlval = $this->fixDNSTTL($ttl[$i]);
                        $query = "INSERT INTO dns_record (zoneid, type, val1, val2, ttl)
                          VALUES ('" . $this->zoneid . "', 'DELEGATE', '"
                          . mysql_real_escape_string($value . "-" . $delegateto[$i]) . "','"
                          . mysql_real_escape_string($delegateuser[$i]) . "','"
                          . $ttlval . "')";
                        $db->query($query);
                        if ($db->error()) {
                          $this->error = $l['str_trouble_with_db'];
                        } else {
                          // create zone, affect it to delegateuser
                          // Can NOT use standard create way because
                          // of EXIST check. BUG: can not insert userlog
                          $query = "INSERT INTO dns_zone (zone,zonetype,userid) VALUES ('" .
                            mysql_real_escape_string($value."-".$delegateto[$i].".".$this->zonename)
                            . "','P','".$newuserid."')";
                          $res = $db->query($query);
                          if ($db->error()) {
                            $this->error = $l['str_trouble_with_db'];
                          } else {
                            // create dns_confprimary records
                            // NO - user has to modify it manually
                            // create NS records
                            $nskeys = array_keys($this->ns);
                            while($nskey = array_shift($nskeys)) {
                              $query = "INSERT INTO dns_record
                                (zoneid,type,val1,val2,ttl)
                                VALUES ('" . $this->zoneid . "',
                                'SUBNS','" . mysql_real_escape_string($value) . "-" 
                                . mysql_real_escape_string($delegateto[$i])
                                . "','" . mysql_real_escape_string($nskey) . "','"
                                . $this->fixDNSTTL($this->nsttl[$nskey]) . "')";
                              $res = $db->query($query);
                              if ($db->error()) {
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
                                $cnamecounter++) {
                              $query = "INSERT INTO dns_record
                                (zoneid, type, val1, val2,ttl)
                                VALUES
                                ('" . $this->zoneid . "',
                                'CNAME', '" . $cnamecounter . "',
                                '" . $cnamecounter . "."
                                . mysql_real_escape_string($value) . "-" 
                                . mysql_real_escape_string($delegateto[$i]) . "."
                                . $this->zonename . ".',"
                                . "'" . $ttlval . "')";
                              $db->query($query);
                              if ($db->error()) {
                                $this->error = $l['str_trouble_with_db'];
                              }
                            } // end for each CNAME
                            if (!$this->error) {
                              $result .= $l['str_primary_ok'] . "<br>\n";
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
  function addSRVRecord($srvname,$srvpriority,$srvweight,$srvport,$srvvalue,$ttl) {
    global $db,$html,$l;

    // for each SRV, add SRV entry
    $i = 0;
    $result = "";
    while(list($key,$value) = each($srvname)) {
      if ($value != "") {
        if (!$this->checkSRVName($value) 
          || !$this->checkSRVPort($srvport[$i]) 
          || !$this->checkSRVValue($srvvalue[$i])) {
          $this->error = sprintf(
            $l['str_primary_bad_srvname_x'],
            htmlspecialchars($value));
        } else {
          if ($srvvalue[$i] == "") {
            $this->error = sprintf(
              $l['str_primary_no_record_x'],
              htmlspecialchars($value));
          } else {
            if (!$this->checkSRVPriority($srvpriority[$i])) {
              $this->error = sprintf(
                $l['str_primary_priority_for_srv_x_has_to_be_int'],
                htmlspecialchars($value));
            } else {
              if ($srvpriority[$i] == "") {
                $srvpriority[$i] = 0;
              }
              if (!$this->checkSRVWeight($srvweight[$i])) {
                $this->error = sprintf(
                  $l['str_primary_weight_for_srv_x_has_to_be_int'],
                  htmlspecialchars($value));
              } else {
                if ($srvweight[$i] == "") {
                  $srvweight[$i] = 0;
                }
                // Check if record already exists
                $query = "SELECT count(*) FROM dns_record WHERE
                  zoneid='" . $this->zoneid . "' AND type='SRV'
                  AND val1='" . mysql_real_escape_string($value) . "' 
                  AND val4='" . mysql_real_escape_string($srvport[$i]) . "' 
                  AND val5='" . mysql_real_escape_string($srvvalue[$i]) ."'";
                $res = $db->query($query);
                $line = $db->fetch_row($res);
                if ($line[0] == 0) {
                  $result .= sprintf($l['str_primary_adding_srv_x'],
                    htmlspecialchars($value)) . "... ";
                  $ttlval = $this->fixDNSTTL($ttl[$i]);
                  $query = "INSERT INTO dns_record (zoneid, type, val1, val2, val3,val4,val5,ttl)
                    VALUES ('" . $this->zoneid . "', 'SRV', '"
                    . mysql_real_escape_string($value) . "', '" 
                    . mysql_real_escape_string($srvpriority[$i]) . "','"
                    . mysql_real_escape_string($srvweight[$i]) . "','"
                    . mysql_real_escape_string($srvport[$i]) . "','"
                    . mysql_real_escape_string($srvvalue[$i]) . "','" 
                    . $ttlval . "')";
                  $db->query($query);
                  if ($db->error()) {
                    $this->error = $l['str_trouble_with_db'];
                  } else {
                    $result .= $l['str_primary_ok'] . "<br>\n";
                  }
                } else { // record already exists
                  $result .= sprintf($l['str_primary_warning_srv_x_exists_not_overwritten'],
                    htmlspecialchars($value)) ."<br>\n";
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

  /**
   * Update PTR when modifying A or AAAA
   *
   * @access private
   * @return string 1 if success, 0 If DB error, string of error else
   */

  function updateReversePTR($a, $value, $type) {
    global $l,$db, $config;

    function zoneLenCmp($a, $b) {
      return strlen($a[0]) - strlen($b[0]);
    }

    // if reverse IP is managed by current user, update PTR
    // else check if reverse IP delegation exists (ie as CNAME)
    $result .= $l['str_primary_looking_for_reverse'] . "... ";
    // construct reverse zone
    if (!strcmp($type, "A")) {
      $revz = array_reverse(explode('.', $a));
      $firstip = $revz[0];
      $reversezone = implode('.', array_slice($revz, 1)) . ".in-addr.arpa";
    } else { // not A, then AAAA
      $ip = ConvertIPv6toDotted($a);
      $revz = implode('.', array_reverse(explode('.', $ip))) . ".ip6.arpa";
      $alluserzones = $this->user->listallzones();
      uasort($alluserzones, 'zoneLenCmp');
      while($userzones = array_pop($alluserzones)) {
        if ($userzones[1] != 'P')
          continue;
        $cut = strrpos($revz, $userzones[0]);
        if ($cut !== FALSE) {
          $firstip = substr($revz, 0, $cut-1);  # minus dot
          $reversezone = substr($revz, $cut);
          break;
        }
      }
    }
    if ($this->Exists($reversezone, 'P')) {
      $alluserzones = $this->user->listallzones();
      $ismanaged = 0;
      while($userzones = array_pop($alluserzones)) {
        if (!strcmp($reversezone,$userzones[0])) {
          $ismanaged = 1;
        }
      }

      if ($ismanaged) {
        // modification allowed because same owner
        // looking for zoneid
        $result .= $l['str_primary_zone_managed_by_you'] ."<br>";
        $query = "SELECT id FROM dns_zone
          WHERE zone='" . $reversezone . "' AND zonetype='P'";
        $res = $db->query($query);
        $line = $db->fetch_row($res);
        $newzoneid=$line[0];
        $result .= $this->AddPTRRecord($newzoneid,array($firstip),
          array($value . "." . $this->zonename . "."),
          array($l['str_primary_default']),NULL);
        if (!$this->error) {
          $result .= $this->flagModified($newzoneid);
          $this->updateSerial($newzoneid);
        }
      } else {
        // zone exists, but not managed by current user.
        // check for subzone managed by current user
        $result .= $l['str_primary_main_zone_not_managed_by_you'] . "... ";
        $query = "SELECT zone,id FROM dns_zone WHERE
            userid='" . $this->user->userid . "'
            AND zone like '%." . $reversezone . "'";
        $res = $db->query($query);
        $newzoneid = 0;
        while($line = $db->fetch_row($res)) {
          $range = array_pop(array_reverse(split('\.',$line[0])));
          list($from,$to) = split('-',$range);
          if (!empty($to)) {
            if (($firstip >= $from) && ($firstip <= $to)) {
              $newzoneid=$line[1];
            }
          } else {
            list($start, $length) = split('/', $range);
            if ($firstip >= $start && $length>0 && $length<32 && $firstip < ($start+pow(2, 32-$length))) {
              $newzoneid=$line[1];
            }
          }
        }
        if ($newzoneid) {
          $result .= $this->AddPTRRecord($newzoneid,array($firstip),array($value .
              "." . $this->zonename . "."),array($l['str_primary_default']),NULL);
          if (!$this->error) {
            $result .= $this->flagModified($newzoneid);
            $this->updateSerial($newzoneid);
          }
        } else {
          // no zone found
          $result .= $l['str_primary_reverse_exists_but_ip_not_manageable'] . "<br>";
        }
      }
    } else {
      $result .= sprintf($l['str_primary_not_managed_by_x'],
          $config->sitename) . "<br>";
    }
    return $result;
  }



  function verifySOA(&$val, $defval, $soattl = "SOA") {
    global $l;
    if (empty($val)) {
      $val=$defval;
    } else {
      $nval = intval($val);
      if (0!=strcmp($nval, $val) || $nval <= 0) {
        $this->error .= sprintf(
          $l['str_primary_x_parameter_x_has_to_be_int'],
          htmlspecialchars($soattl),
          htmlspecialchars($val));
        return;
      }
      $val = $nval;
    }
  }


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
  function updateSOA($xferip,$defaultttl,
            $soarefresh,$soaretry,$soaexpire,$soaminimum) {
    global $db, $l;

    $result ="";

    $this->VerifySOA($defaultttl, 86400, "TTL");
    $this->VerifySOA($soarefresh, 86400);
    $this->VerifySOA($soaretry, 10800);
    $this->VerifySOA($soaexpire, 3600000);
    $this->VerifySOA($soaminimum, 10800);

    if (!empty($xferip)) {
      if (!checkPrimary($xferip)) {
        $this->error .= $l['str_primary_soa_invalid_xfer'];
      }
    } else {
      $xferip='any';
    }

    if (!empty($this->error)) {
      return 0;
    }


      // dns_confprimary
      // upgrade serial

      $this->serial = getSerial($this->serial);

      if ($this->creation==0) {
        $query = "UPDATE dns_confprimary SET serial='" . $this->serial . "',
          xfer='" . $xferip . "', refresh='" . $soarefresh . "',
          retry='" . $soaretry . "', expiry='" . $soaexpire . "',
          minimum='" . $soaminimum . "', defaultttl='" . $defaultttl . "'
          WHERE zoneid='" . $this->zoneid . "'";
      } else {
        $query = "SELECT count(*) FROM dns_confprimary WHERE zoneid='" . $this->zoneid . "'";
        $res = $db->query($query);
        if ($db->error()) {
          $this->error = $l['str_trouble_with_db'];
          return 0;
        }
        $line = $db->fetch_row($res);
        if ($line[0] != 0) {
          $this->error = $l['str_zone_already_exists'];
          return 0;
        }

        $query = "INSERT INTO dns_confprimary (zoneid,serial,xfer,refresh,
          retry,expiry,minimum,defaultttl)
          VALUES ('" . $this->zoneid . "','" . $this->serial . "','" . $xferip . "'
          ,'" . $soarefresh . "','" . $soaretry . "','" . $soaexpire . "','" .
          $soaminimum . "','" . $defaultttl . "')";
      }
      $res = $db->query($query);
      if ($db->error()) {
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



  /**
   * Get all the A records with a given name in current zone
   *
   *
   *@access public
   *@params Address of the array to fill (&$arecs) and name of the A records ($name)
   *@return O (error) or 1 (success)
   */

  function getArecords(&$arecs, $name) {
    global $db,$l;
    $this->error='';
    $query = "SELECT val2
      FROM dns_record
      WHERE zoneid='" . $this->zoneid . "'
      AND type IN ('A', 'AAAA') AND val1='" . $name . "'";
    $res =  $db->query($query);
    $arecs = array();
    $i=0;
    while($line = $db->fetch_row($res)) {
      if ($db->error()) {
        $this->error=$l['str_trouble_with_db'];
        return 0;
      }
      $arecs[$i]=$line[0];
      $i++;
    }
    return 1;
  }



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
  function retrieveRecords($type,&$arraytofill,&$arrayofid,&$ttltofill) {
    global $db,$l;
    $this->error='';
    $query = "SELECT id, val1, val2, ttl
      FROM dns_record
      WHERE zoneid='" . $this->zoneid . "'
      AND type='" . $type . "' ORDER BY val1";
    $res =  $db->query($query);
    $arraytofill = array();
    $ttltofill = array();
    while($line = $db->fetch_row($res)) {
      if ($db->error()) {
        $this->error=$l['str_trouble_with_db'];
        return 0;
      }
      $arraytofill[$line[1]]=$line[2];
      $arrayofid[$line[1]]=$line[0];
      $ttltofill[$line[1]] = ($line[3]=="default"?"-1":$line[3]);
    }
    return 1;
  }

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
  function retrieveMultiRecords($type, &$array1tofill, &$array2tofill, &$array3tofill,
      &$array4tofill, &$array5tofill, &$idtofill, &$ttltofill) {
    global $db, $l;

    $this->error = "";
    $query = "SELECT id, val1, val2, val3, val4, val5, ttl
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
    while($line = $db->fetch_row($res)) {
      if ($db->error()) {
        $this->error=$l['str_trouble_with_db'];
        return 0;
      }
      $idtofill[$i]=$line[0];
      $array1tofill[$i]=$line[1];
      $array2tofill[$i]=$line[2];
      $array3tofill[$i]=$line[3];
      $array4tofill[$i]=$line[4];
      $array5tofill[$i]=$line[5];
      $ttltofill[$i]=($line[6]=="default"?"-1":$line[6]);
      $i++;
    }
  }

  /**
   * Generate the file name (with whole path)
   *
   *@access private
   *@return string file
   */
  function tempZoneFile() {
    global $config;
    $tmpzone = ereg_replace("/","\\",$this->zonename);
    return ("{$config->tmpdir}$tmpzone.{$this->zonetype}");
  }

  /**
   * Generate a temporary config file in $this->tempZoneFile()
   *
   *@access private
   *@return int 1
   */
  function generateConfigFile() {
    global $config,$l;
    // reinitialize every records after add/delete/modify
    // fill in with records
    $this->RetrieveRecords('NS',$this->ns,$this->nsid,$this->nsttl);
    $this->RetrieveMultiRecords('SUBNS',$this->subns,$this->subnsa,$this->nullarray,$this->nullarray,$this->nullarray,$this->subnsid,$this->subnsttl);
    $this->RetrieveRecords('CNAME',$this->cname,$this->cnameid,$this->cnamettl);
    $this->RetrieveMultiRecords('TXT',$this->txt,$this->txtdata,$this->nullarray,$this->nullarray,$this->nullarray,$this->txtid,$this->txtttl);
    if ($this->reversezone) {
      $this->RetrieveMultiRecords('PTR',$this->ptr,$this->ptrname,$this->nullarray,$this->nullarray,$this->nullarray,$this->ptrid,$this->ptrttl);
      $this->RetrieveMultiRecords('DELEGATE',$this->delegatefromto,$this->delegateuser,$this->nullarray,$this->nullarray,$this->nullarray,$this->delegateid,$this->delegatettl);
    } else {
      $this->RetrieveMultiRecords('MX',$this->mxsrc,$this->mxpref,$this->mx,$this->nullarray,$this->nullarray,$this->mxid,$this->mxttl);
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
      $this->error = sprintf(
        $l['str_can_not_open_x_for_writing'],
        $this->tempZoneFile());
      return 0;
    }
    $this->generateSOA($this->defaultttl,$config->nsname,$this->zonename,
              $this->user,$this->serial,
              $this->refresh,$this->retry,$this->expiry,$this->minimum,$fd);

    // retrieve & print NS
    $this->generateConfig("NS",$this->ns,$this->nsttl,$fd);

    if ($this->reversezone) {
      // retrieve & print PTR
      $this->generateMultiConfig("PTR",$this->ptr,"","","",$this->ptrname,$this->ptrttl,$fd);
    } else { // end reverse zone
      // retrieve & print MX
      $this->generateMultiConfig("MX",$this->mxsrc,"","",$this->mxpref,$this->mx,$this->mxttl,$fd);
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

  /**
   * Generate SOA config in a file or as return content
   *
   *@access private
   *@return int 1 if in a file, string content if no file given
   */
  function generateSOA($tttl,$nsname,$zonename,$user,
            $serial,$refresh,$retry,$expiry,$minimum,$fd="") {
    global $l, $config;

    $content  = "\n\$TTL " . $tttl . " ; " . $l['str_primary_default_ttl'] ;

    if ($user->emailsoa) {
      $mail = ereg_replace("@",".",$user->Retrievemail()) . ".";
    } else {
      $uh = split("@", $config->soamail);
      $mail = $uh[0] . "+" . $this->zoneid . "." . $uh[1];
    }
    $zonename = $zonename . ".";
    $nsname = $nsname . ".";
    $content .= sprintf("\n%-18s \tIN %-5s %s %s", $zonename, "SOA", $nsname, $mail);

    $content .= " (";
    $content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $serial, $l['str_primary_serial']);
    $content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $refresh, $l['str_primary_refresh_period']);
    $content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $retry, $l['str_primary_retry_interval']);
    $content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $expiry, $l['str_primary_expire_time']);
    $content .= sprintf("\n%-18s \t%8s %-10s ; %s", "", "", $minimum, $l['str_primary_negative_caching']);

    $content .= sprintf("\n%-18s \t)", "");
    $content .= "\n\n\$ORIGIN " . $zonename;
    if ($fd) {
      fputs($fd,$content);
      return 1;
    } else {
      return $content;
    }
  }


  /**
   * Generate config in a file or as return content
   *
   *@access private
   *@return int 1 if in a file, string content if no file given
   */
  function generateMultiConfig($type,$item1,$item2,$item3,$item4,$item5,$ttl,$fd = "") {
    // retrieve & print $type
    $counter = 0;
    $content = "";
    while(isset($item1[$counter])) {
      $rest = "";
      if (isset($item2[$counter]))
        $rest .= $item2[$counter] . " ";
      if (isset($item3[$counter]))
        $rest .= $item3[$counter] . " ";
      if (isset($item4[$counter]))
        $rest .= $item4[$counter] . " ";
      if (isset($item5[$counter]))
        $rest .= $item5[$counter] . " ";
      if ($type=="TXT") {
        $rest = trim($rest);
        if (!preg_match('/"/', $rest)) {
          $restarr = str_split($rest, 255);
          $rest = ""; 
          foreach ($restarr as $v) {
            $rest .= "\"$v\" ";
          }
        }
      }

      $content .= sprintf("\n%-18s %s\tIN %-5s %s",
        $item1[$counter], (@$ttl[$counter] != "-1" ? @$ttl[$counter] : ""),
        $type, $rest);
        $counter++;
    }
    $content .= "\n\n";

    if ($fd) {
      fputs($fd,$content);
      return 1;
    } else {
      return $content;
    }
  }


  /**
   * Generate config in a file or as return content
   *
   *@access private
   *@return int 1 if in a file, string content if no file given
   */
  function generateConfig($type,$item1,$ttl,$fd = "") {
    // retrieve & print $type
    $counter = 0;
    $content = "";

    $keys = array_keys($item1);
    switch($type) {
      case "NS":
        while($key = array_shift($keys)) {
          $content .= sprintf("\n%-18s %s\tIN %-5s %s",
            "", ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $key);
        }
        break;
      case "MX":
        while($key = array_shift($keys)) {
          $content .= sprintf("\n%-18s %s\tIN %-5s %s %s",
            "", ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $item1[$key], $key);
        }
        break;
      default:
        while($key = array_shift($keys)) {
          $content .= sprintf("\n%-18s %s\tIN %-5s %s",
            $key, ($ttl[$key] != "-1" ? $ttl[$key] : ""), $type, $item1[$key]);
        }
        break;
    }

    if ($fd) {
      fputs($fd,$content);
      return 1;
    } else {
      return $content;
    }
  }


  /**
   * return TTL
   *
   *@access private
   *@return string ttl localized value
   */

  function printTTL($ttl) {
    global $l;
    return ($ttl=="-1"||$ttl=="default"?$l['str_primary_default']:$ttl);
  }

  /**
   * return TTL
   *
   *@access private
   *@return string ttl value for DB insertion
   */
  function fixDNSTTL($ttl) {
    global $l;

    if (empty($ttl)) {
      $ttlval = "-1";
    } else {
      if ($ttl == $l['str_primary_default'])
        $ttlval = "-1";
      else
        $ttlval = mysql_real_escape_string($ttl);
    }
    return $ttlval;
  }

  /**
   * update zone serial
   *
   *@access private
   *@param $zoneid int zone id
   *@return int 0 if error, 1 if success
   */
  function updateSerial($zoneid) {
    global $db, $l;
    $result ="";

    // retrieve zone serial
    $query = sprintf("SELECT serial FROM dns_confprimary WHERE zoneid='%d'", $zoneid);
    $res = $db->query($query);
    if ($db->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    $line = $db->fetch_row($res);

    $serial = getSerial($line[0]);
    $query = sprintf(
        "UPDATE dns_confprimary SET serial='%d' WHERE zoneid='%d'",
        $serial, $zoneid);
    $res = $db->query($query);
    if ($db->error()) {
      $this->error = $l['str_trouble_with_db'];
      return 0;
    }
    return 1;
  }

  /**
   * Check if MX name has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkMXName($string) {
    $string = strtolower($string);
    // only valid char, without dot as 1st char (no dot allowed)
    if ((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
      strlen($string)) || (strpos('0'.$string,".") == 1)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if MX pref is integer
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkMXPref($string) {
    if (preg_match("/[^\d]/", $string)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if NS Name has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkNSName($string) {
    $string = strtolower($string);
    // only valid char, without dot as 1st char and at least one dot
    if ((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") !=
      strlen($string)) || (strpos('0'.$string,".") == FALSE)||
      (strpos('0'.$string,".") == 1)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if A name has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkAName($string) {
    $string = strtolower($string);
    if ($string == '*')
      return 1;
    if ($string == "@")
      return 1;
    // name cannot start with a dot
    if ($string[0] == '.')
      return 0;
    // allow zone name itself
    if ($string == $this->zonename.'.')
      return 1;
    // it cannot end with a dot
    if ($string[strlen($string)-1] == '.')
      return 0;
    // otherwise allow only 2 dots
    if (count(explode('.',$string,4))>3)
      return 0;
    // allow only one underscore
    if (count(explode('_',$string,3))>2)
      return 0;
    // only specified chars allowed (not RFC but dummy user prevention)
    if (strspn($string, "_.0123456789abcdefghijklmnopqrstuvwxyz-") != strlen($string))
      return 0;

    return 1;
  }

  /**
   * Check if A value has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkAValue($string) {
    return checkIP($string);
  }


  /**
   * Check if PTR name has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkPTRName($string) {
    // zone name allowed
    if ($string == $this->zonename.'.') {
      $result = 1;
    } else {
      if (!$this->user->ipv6) {
        // no IPv6
        if (ereg("[a-zA-Z]",$string) || ($string > 255)) {
          $result = 0;
        } else {
          $result = 1;
        }
      } else {
        // ipv6
        if (!checkIPv6($string)) {
          $result = 0;
        } else {
          $result = 1;
        }
      }
    }
    return $result;
  }

  /**
   * Check if PTR value has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkPTRValue($string) {
    // no distinction between IPv4 and IPv6
    $string = strtolower($string);
    // only specified char
    if ((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") != strlen($string)) 
      || (strpos('0'.$string,".") == FALSE)
      || (strpos('0'.$string,".") == 1) 
      || !preg_match("/[a-z]\.$/i",$string)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }


  /**
   * Check if CNAME name is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkCNAMEName($string) {
    $string = strtolower($string);
    $numdots = 3;
    if ($string[0] == '*' && $string[1] == '.' && $string[2] != '*') {
      $string=substr($string,2);
      $numdots--;
    }
    $abc = "_0123456789abcdefghijklmnopqrstuvwxyz.-";

    // '*' itself allowed
    if (!strcmp($string, "*"))
      return 1;
    // out of allowed chars
    if (strspn($string, $abc) != strlen($string))
      return 1;
    // starts with a dot
    if (strpos('0'.$string, ".") == 1)
      return 0;

    if (count(explode('.',$string,3))>$numdots) {
      $result = 0;
    } else {
      $result = 1;
    }

    if (checkIP($string)) {
      $result = 0;
    }

    return $result;
  }


  /**
   * Check if CNAME value is valid
   *
   *@param string $string value to be checked
   *@return int 1 if valid, 0 else
   */
  function checkCNAMEValue($string) {
    $string = strtolower($string);
    if ($string == '@') {
      return 1;
    }
    // value can't be an IP
    if (checkIP($string)) {
      $result = 0;
    } else {
      // only specified char without a dot as first char
      if ((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-._") !=
                        strlen($string)) || (strpos('0'.$string,".") == 1)) {
        $result = 0;
      } else {
        $result = 1;
      }
    }
    return $result;
  }
  /**
   * Check if TXT name is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkTXTName($string) {
    $string = strtolower($string);
    // needs better algorithm
    if ($string == "_domainkey")
      return 1;
    if ($string == "_adsp._domainkey")
      return 1;
    return $this->checkAName($string);
  }

  /**
   * Check if WWW name is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkWWWName($string) {
    return $this->checkAName($string);
  }

  function checkWWWValue($string) {
    $string = strtolower($string);
    if (ereg('^http://', $string) || ereg('^https://', $string))
      return 1;
    return 0;
  }

  /**
   * Check if AAAA name has only valid char
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkAAAAName($string) {
    return $this->checkAName($string);
  }

  /**
   * Check if AAAA value is valid
   *
   *@param string $string value to be checked
   *@return int 1 if valid, 0 else
   */
  function checkAAAAValue($string) {
    $string = strtolower($string);
    if (! checkIPv6($string) ) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if SUBNS name is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSUBNSName($string) {
    $string = strtolower($string);
    // allow only one underscore
    if (count(explode('_',$string,2))>1)
      return 0;
    $allowed = "_0123456789abcdefghijklmnopqrstuvwxyz-";
    if (ereg('\.ip6\.arpa$', $this->zonename)) $allowed = "." . $allowed;
    // only specified char
    if (strspn($string, $allowed) != strlen($string)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if SUBNS value is valid
   *
   *@param string $string value to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSUBNSValue($string) {
    $string = strtolower($string);
    // only specified char
    if ((strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz-.") != strlen($string))
      || (strpos('0'.$string,".") == 1)) {
      $result = 0;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
   * Check if SRV name is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSRVName($string) {
    $string = strtolower($string);
    $string = str_replace("." . $this->zonename . ".", "", $string);
    $service = substr($string, -5);
    if ($service == "._tcp" || $service == "._tls" || $service == "._udp") {
       $string = substr($string, 0, -5);
       if ($string[0] == '_') $string = substr($string, 1);
    }
    return $this->checkAName($string);
  }

  /**
   * Check if SRV Priority is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSRVPriority($string) {
    if (preg_match("/[^\d]/", $string)) {
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Check if SRV Weight is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSRVWeight($string) {
    if (preg_match("/[^\d]/", $string)) {
      return 0;
    } else {
      return 1;
    }
  }

  /**
   * Check if SRV Port is valid
   *
   *@param string $string name to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSRVPort($string) {
    return !preg_match("/[^\d]/", $string);
  }

  /**
   * Check if SRV value is valid
   *
   *@param string $string value to be checked
   *@return int 1 if valid, 0 else
   */
  function checkSRVValue($string) {
    $string = strtolower($string);
    // value can't be an IP
    if (checkIP($string))
      return 0;
    // dot cannot be first
    if (strpos($string, ".") === 0)
      return 0;
    if (strpos($string, "__") !== false)
      return 0;
    if (strpos($string, "..") !== false)
      return 0;
    // allowed chars
    if (strspn($string, "0123456789abcdefghijklmnopqrstuvwxyz._-") != strlen($string))
      return 0;
    return 1;
  }

}
?>
