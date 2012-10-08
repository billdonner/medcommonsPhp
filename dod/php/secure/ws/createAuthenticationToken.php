<?PHP
require_once "../ws/securewslibdb.inc.php";
require_once "mc.inc.php";
require_once "utils.inc.php";
require_once "../db.inc.php";

/**
 * Creates an authentication token for the requested accounts and returns it.
 * The authentication token may then be used subsequently to access services
 * using the authority of the returned token.
 *
 * This service understands different formats for account ids and interprets
 * them accordingly.  The following logic is applied:
 *
 *   - [0-9]{16}        =>  MedCommons Account ID
 *   - http[s]{0,1}://  =>  OpenID
 *   - fbid://          =>  Facebook  ID
 *   - tel:[0-9]{11}    =>  Telephone number (prefixed with 1, compatible with RFC 2806)
 *
 * @param accountIds - comma separated list of accounts that should 
 *                     authorized by the token. An account id prefixed by 
 *                     'g:' will be given group priority instead of individual.
 * @param auth - optional, parent auth.  If provided, the created auth will be linked
 *               to the given parent auth.
 *  
 *
 * IMPORTANT: this service must be restricted in production.  Only access from
 * central login servers should be allowed.
 */
class createAuthenticationTokenWs extends jsonrestws {

	function jsonbody() {

    // Generate a random id
    // TODO: Make this more secure by adding some salt
    $token = generate_authentication_token();
    $secret = generate_authentication_token();

    $this->token = $token;

    // Parse account ids
    $accountIds = explode(",",$_REQUEST['accountIds']);

    // User name details, if supplied
    $fn = req('fn');
    $ln = req('ln');
    $idp = req('idp');

    // Parent authentication, if supplied
    $auth = req('auth');

    $atId = 'NULL';
    if($auth != null) {
      $auths = $this->dbexec("select at_id from authentication_token where at_token = '$auth'",
                             "- unable to select from authentication_token");
      if($row = mysql_fetch_row($auths)) {
        $atId = $row[0];
      }
    }

    $fn = ($fn !== false) ? "'".mysql_real_escape_string($fn)."'" : "NULL";
    $ln = ($ln !== false) ?"'". mysql_real_escape_string($ln)."'": "NULL";

    // External share for IDP that has signed on a MedCommons account holder
    $idpEsId = null;

    // insert into table
    foreach($accountIds as $accid) {
      if(is_url($accid)) { // openid
        $this->add_openid_shares($accid, $token, $atId, $secret, $fn, $ln);
      }
      else
      if(preg_match("/^fbid:\/\//",$accid)!==0) { // facebook
        if(!$this->add_facebook_token($accid,$token,$fn,$ln))
          return false;
      }
      else
      if(preg_match("/^tel:1[0-9]{10}/",$accid)==1) { // US telephone number
        $this->add_telephone_token($accid, $token);
      }
      else { // medcommons account
        $priority = "I";
        if(strpos($accid,"g:")===0)  {
          $priority = "G";
          $accid = substr($accid,2);
          dbg("group priority for account $accid");
        }

        // If idp information available, capture that
        if($idp && ($idpEsId == null)) { // not created yet, create one

          $idp_id = req('idp_id');

          $r = $this->dbexec("insert into external_share (es_id,es_identity, es_identity_type, es_first_name, es_last_name) 
                              values (NULL, '$idp_id', '$idp','','')", "unable to insert external_share - ");
          if(!$r) {
            error_log("Error inserting external share for IDP");
            return false;
          }
          $idpEsId = mysql_insert_id();
          dbg("Created new external share $idpEsId for idp $idp with external id $idp_id");
        }
        $idpEs = $idpEsId ? $idpEsId : "NULL";

        $this->dbexec("insert into authentication_token (at_id,at_token,at_account_id,at_es_id,at_create_date_time, at_secret, at_parent_at_id, at_priority) 
                        VALUES (NULL,'$token','$accid',$idpEs,CURRENT_TIMESTAMP,'$secret',$atId,'$priority')","Unable to insert token for account id $accid");
      }
    }
    $this->result = new stdClass;
    $this->result->status = "ok";
    $this->result->result = $token;
    $this->result->secret = $secret;
    return $token;
  }

  /**
   * Inserts authentication token entries for the specified 
   * telephone external share.
   * 
   * @param <type> $accid   telephone URI (eg. tel:17042271900)
   * @param <type> $token   token to associate with login
   */
  function add_telephone_token($accid, $token) {
    $phoneNumber = substr($accid,5);
    dbg("phone number $phoneNumber");
    
    $db = DB::get();

    $db->begin_tx();

    // Find the shares
    $shares = $db->query("select * from external_share 
                          where es_identity = ?
                          and es_identity_type = 'phone' order by es_create_date_time desc", array($phoneNumber));

    foreach($shares as $es) {
      dbg("Adding telephone share {$es->es_identity} [name={$es->es_first_name} {$es->es_last_name}]");
      $db->execute("insert into authentication_token 
                    (at_id, at_token, at_es_id) values (NULL, ?, ?)",
                    array($token, $es->es_id));
    }
    $db->commit();
  }

  /**
   * Inserts authentication_token entries for external shares
   * to Facebook associated with the specified identity.
   *
   * @param <type> $accid  facebook url (fbid://...) to associate wtih tokens
   * @param <type> $token  token value to insert
   * @param <type> $fn     first name associated wtih facebook user
   * @param <type> $ln     last name associated with facebook user
   * @return true / false  true iff successful
   */
  function add_facebook_token($accid, $token, $fn, $ln) {
    $fbid = substr($accid,7, strlen($accid)-7);
      
    dbg("Adding auth token for facebook id $fbid");
      
    if(preg_match("/[0-9]{1,16}/",$fbid)===false) 
      throw new Exception("Invalid fbid $fbid received.  Refusing authentication token.");
      
    // First add external share
    $r = $this->dbexec("insert into external_share (es_id,es_identity, es_identity_type, es_first_name, es_last_name) 
                            values (NULL, '$fbid', 'FaceBook',$fn,$ln)", "unable to insert external_share - ");
    if(!$r) 
      throw new Exception("Error inserting external share for fbid $fbid: ".mysql_error());

    $esId = mysql_insert_id();
      
    error_log("received esId=$esId");
      
    $this->dbexec("insert into authentication_token (at_id,at_token,at_account_id,at_es_id,at_create_date_time, at_secret, at_parent_at_id, at_priority) 
                        VALUES (NULL,'$token',NULL,".$esId.",CURRENT_TIMESTAMP, '$secret',$atId, 'I')","Unable to insert token for account id $accid");
                          
    return true;
  }

  function add_openid_shares($accid, $token, $atId, $secret, $fn, $ln) {
    global $acEnableWildCardOpenID;

    // Look up the external share
    $ess = array();
    if(isset($acEnableWildCardOpenID) && ($acEnableWildCardOpenID)) {
      $parsed_url = @parse_url($accid);
      if($parsed_url === false)
        throw new Exception("Unable to parse openid url $accid");
      $host = $parsed_url['host'];

      // Break the hostname into parts
      $host_segments = explode(".",$host);

      dbg("openid host is $host (".count($host_segments)." segments)");

      // The last 2 segments cannot be wildcards, but we allow the others to be
      if(count($host_segments)<2)
        throw new Exception("OpenID host $host has too few segments.");

      $host_segments = array_reverse($host_segments);

      $tld = $host_segments[ 0 ];
      $root_domain = $host_segments[ 1 ];

      $sql = "select es_id, es_identity
              from external_share 
              where (es_identity = '$accid' or es_identity like '%*%.$root_domain.$tld/%')
              and es_identity_type = 'openid'";
    }
    else {
      $sql = "select es_id, es_identity
              from external_share 
              where es_identity = '$accid' 
              and es_identity_type = 'openid'";
    }

    $result = $this->dbexec($sql,"Failed to query external shares");
    $matched_openids = array();
    while($es = mysql_fetch_object($result)) {
      dbg("checking match of share to openid ".$es->es_identity);
      if(isset($host_segments)) {
        if(!match_openid_url_pattern($parsed_url, $es->es_identity)) {
          continue;
        }
        if(strpos($es->es_identity,"*") !== FALSE) {
          dbg("pattern {$es->es_identity} matched");
          $matched_openids[]=$es;
        }
      }
      $ess[]=$es;
    }

    dbg("found ".count($ess)." shares to openid $accid");

    foreach($ess as $es) {

      $priority = (strpos($es->es_identity,"*") !== FALSE) ? "G" : "I";

      $this->dbexec("insert into authentication_token (at_id,at_token,at_account_id,at_es_id,at_create_date_time, at_secret, at_parent_at_id, at_priority) 
                      VALUES (NULL,'$token',NULL,".$es->es_id.",CURRENT_TIMESTAMP, '$secret',$atId, '$priority')","Unable to insert token for account id $accid");
    }

    // If the only matches were openid groups, add an individual consent for the user
    // This way their consent appears as an individual entry under the group
    // from now on.
    if(count($ess) == count($matched_openids)) {

      dbg("Account $accid was resolved under openid groups only.  Adding individual consent.");

      $max_segments = 0;
      foreach($matched_openids as $e) {
        $count_segments = count(explode(".",$e->es_identity));
        dbg($e->es_identity." has ".$count_segments." parts");
        if($count_segments > $max_segments) {
          $es = $e;
          $max_segments = $count_segments;
        }
      }

      dbg("copying individual consent from external share {$es->es_id} / {$es->es_identity}");

      // 1.  Add an external share
      $this->dbexec("insert into external_share ( es_id, es_identity, es_identity_type, es_first_name, es_last_name )
        values (NULL, '".mysql_real_escape_string($accid)."', 'openid',$fn,$ln)", " - unable to insert new external share");

      $esId = mysql_insert_id();

      dbg("inserted external share $esId");
      
      // 2.  Copy the rights entry
      $result = $this->dbexec("select * from rights where es_id = {$es->es_id} and active_status = 'Active'", "- failed to query rights for duplication");
      dbg("copying ".mysql_num_rows($result)." rights for new individual consent");
      while($r = mysql_fetch_object($result)) {
        $docId = $r->document_id?$r->document_id:"NULL";
        $this->dbexec("insert into rights (rights_id, groups_group_number, document_id, rights, storage_account_id, es_id)
                       values (NULL, 0, {$docId},'{$r->rights}', '{$r->storage_account_id}', {$esId})", " - failed to insert rights derived from openid group");
      }
    }
  }

}

function test_match_url_pattern($url, $pattern, $expected) {
  $x = new createAuthenticationTokenWs();
  $actual = match_openid_url_pattern(parse_url($url), $pattern);
  echo "<p>$url matches $pattern ?  expected = ".($expected?"yes":"no")." actual = ".($actual?"yes":"no")." : "
    .(($expected == $actual)?"<span style='color:green'>Pass</span>":"<span style='color:red'>Fail</span>")."</p>";
}


//main
if(!isset($_REQUEST['test'])) {
  $x = new createAuthenticationTokenWs();
  $x->handlews("response_createAuthenticationToken");
}
else {
  function fobj($sql) {
    $result = array();
    $dbres = mysql_query($sql);
    while($obj=mysql_fetch_object($dbres))
      $result[]=$obj;
    return $result;
  }

  echo "<html><body>";
  test_match_url_pattern("http://foo.baz.bar","http://foo.baz.bar",true);
  test_match_url_pattern("http://foo.baz.bar","http://foo.bar.bar", false);
  test_match_url_pattern("http://foo.baz.bar/x","http://foo.baz.bar", false);
  test_match_url_pattern("http://foo.baz.bar","http://*.baz.bar", true);
  test_match_url_pattern("http://foo.baz.bar/x","http://*.baz.bar", false);
  test_match_url_pattern("http://foo.baz.bar/x","http://*.baz.bar/x", true);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.baz.bar/x", false);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.baz.bar/x?fu=fuboz", false);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.baz.bar/x?fu=fubar",true);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.baz.bar/*?fu=fubar",false);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.bar/x?fu=fubar",true);
  test_match_url_pattern("http://foo.baz.bar/x?fu=fubar","http://*.*.bar/x?fu=fubar",true);


  // Delete 
  dbconnect();

  // Reset test data
  mysql_query("delete from external_share where es_identity like 'http://%.baz.bar/'");
  mysql_query("insert into external_share (es_id, es_identity, es_identity_type) values (NULL, 'http://foo.baz.bar/', 'openid')");
  mysql_query("insert into external_share (es_id, es_identity, es_identity_type) values (NULL, 'http://*.baz.bar/', 'openid')");
  
  // Invoke call
  $_REQUEST['accountIds'] = 'http://foo.baz.bar/';

  $x = new createAuthenticationTokenWs();
  $x->jsonbody();

  echo "<p>Token is {$x->token}</p>";

  // Now we should have 2 auth tokens created, one with type G and other with type I
  $tokens = fobj("select * from authentication_token where at_token = '{$x->token}' order by at_priority desc");

  if(!count($tokens) == 2)
    throw new Exception("Expected 2 tokens returned, got ".count($tokens));

  if($tokens[0]->at_priority != "I")
    throw new Exception("Expected individual priority for first token entry");

  if($tokens[1]->at_priority != "G")
    throw new Exception("Expected group priority for second token entry");
  
}
?>
