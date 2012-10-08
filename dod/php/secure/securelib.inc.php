<?
/**
 * Functions needed by both web services and web front end to secure services
 *
 * Some of these used to be in ws/wslibdb.inc.php, but since they are needed by the web
 * front end provided by secure services as well as the internal web services
 * they are moved here as a common location.
 */

require_once "dbparams.inc.php";
require_once "db.inc.php";
require_once "utils.inc.php";

/**
 * The MedCommons ID assigned to public documents
 */
$GLOBALS['PUBLIC_MEDCOMMONS_ID']='0000000000000000';

/**
 * Connect to the database
 */
function dbconnect() {
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");

	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
}

/*
 * SQL to resolve Rights given an Authentication Token
 *
 * Takes parameters:  
 *
 *   1. guid
 *   2. rights (can be "" for wildcard) 
 *   3. authentication token
 *
 * Note: this SQL returns entries even in situations where
 * the user may not have access to the document.
 */
$RESOLVE_AUTH_SQL = 
        "select * from rights r,  document_location l, node n, authentication_token at, document d
         left join document_charge dc on dc.dc_document_id = d.id and dc.dc_status = 'OUTSTANDING'
         where ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
           and d.guid = '%s' 
           and r.active_status = 'Active'
           and (r.rights like '%%%s%%' or r.rights = 'ALL')
           and ((r.account_id = at.at_account_id) or (r.es_id = at.at_es_id) or (r.account_id='".$GLOBALS['PUBLIC_MEDCOMMONS_ID']."'))
           and l.document_id = d.id
           and n.node_id = l.node_node_id
           and at.at_token = '%s'
           order by at.at_priority desc, at.at_id asc";

$RESOLVE_SQL =  "select * from rights r, document_location l, node n, document d 
         left join document_charge dc on dc.dc_document_id = d.id and dc.dc_status = 'OUTSTANDING'
         where ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
           and d.guid = '%s' 
           and r.active_status = 'Active'
           and (r.rights like '%%%s%%' or r.rights = 'ALL')
           and ((r.account_id = '%s') or (r.account_id = '".$GLOBALS['PUBLIC_MEDCOMMONS_ID']."'))
           and l.document_id = d.id
           and n.node_id = l.node_node_id";

$RESOLVE_PUBLIC_SQL =  "select * from rights r, document_location l, node n, document d
         left join document_charge dc on dc.dc_document_id = d.id and dc.dc_status = 'OUTSTANDING'
         where ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
           and d.guid = '%s' 
           and r.active_status = 'Active'
           and (r.rights like '%%%s%%' or r.rights = 'ALL')
           and r.account_id = '".$GLOBALS['PUBLIC_MEDCOMMONS_ID']."'
           and l.document_id = d.id
           and n.node_id = l.node_node_id";

class AccountSpec {

  /**
   * Construct an Account Description from an Authentication Token record
   */
  function __construct($accId, $esId=null, $esIdentity=null, $esIdentityType=null, $esFirstName=null, $esLastName=null) {
    $this->accId = $accId;
    $this->esId = $esId;
    $this->esIdentity = $esIdentity;
    $this->esIdentityType = $esIdentityType;
    $this->esFirstName = $esFirstName;
    $this->esLastName = $esLastName;
  }

  function getRights() {
    $db = DB::get();

    // Get the rights entry, if any for the account
    if($this->esId)
      $rights = $db->query("select * from rights where es_id = ? and active_status = 'Active'", array($this->esId));
    else
      $rights = $db->query("select * from rights where account_id = ?  and active_status = 'Active'", array($this->accId));

    if(count($rights) > 0)
      return $rights[0];
    else
      return false;
  }

};

/**
 * Returns an array of accounts that are recognized by the given
 * authentication token.  The primary account under which the user
 * was authorized (identifying the user themselves) will always be
 * the first account.
 */
function get_authorized_accounts($authToken) {

  // Default auth format is the 'token:' protocol
  // If no colon, append 'token:' so that people can pass either raw or 
  // qualified tokens to this function
  if(preg_match("/^[a-z0-9]{40}$/i",$authToken)===1) 
    return get_authorized_accounts("token:".$authToken);

  if(strpos($authToken,"token:")===0) {
    $token = substr($authToken,6);
    $accounts = array();
    $result = mysql_query("select at.at_account_id, at.at_es_id, es.es_identity, es.es_identity_type, es.es_first_name, es.es_last_name
                           from authentication_token at
                           left join external_share es on es.es_id = at.at_es_id
                           where at.at_token = '$token'
                           order by at.at_priority desc, at.at_id asc");
    if($result === false) {
      error_log("Unable to read authentication token table");
      return false;
    }
    if($result) {
      while($t = mysql_fetch_array($result)) {
        $accounts[]=new AccountSpec($t[0],$t[1],$t[2],$t[3],$t[4],$t[5]);
      }
      mysql_free_result($result);
    }
    return $accounts;
  }

  error_log("Unknown scheme in authentication token: $authToken");
  return false;
}

/**
 * Return all the rights entries applicable to the given guid
 */
function resolve_guid($accid, $guid, $auth) {
  global $RESOLVE_SQL, $RESOLVE_AUTH_SQL, $RESOLVE_PUBLIC_SQL;
  $rights = array();

  dbg("resolving guid=$guid accid=$accid using auth=$auth");
  $auth = str_replace("token:","",$auth);
  if($auth == "") { // No auth, you can only see public stuff
    dbg("no auth - checking for public rights");
    $sql = sprintf($RESOLVE_PUBLIC_SQL, $guid, ""); // find entries with any rights
  }
  else
  if($auth != "Gateway") { // We have an auth token, use that
    $sql = sprintf($RESOLVE_AUTH_SQL, $guid, "", $auth); // find entries with any rights
  }
  else 
    return array(); // no rights

  // Check if the auth token authenticates the user as the storage id itself
  // we grant RW access implicitly in that case
  dbg("checking direct storage access");
  $result = mysql_query("select *, 'RW' as rights, '$accid' as account_id
               from document_location l, node n, authentication_token at, document d
               left join document_charge dc on dc.dc_document_id = d.id and dc.dc_status = 'OUTSTANDING'
               where 
                 d.guid = '$guid' 
                 and l.document_id = d.id
                 and n.node_id = l.node_node_id
                 and at.at_token = '$auth'
                 and d.storage_account_id = '$accid'
                 order by at.at_priority desc, at.at_id asc");

  if($result) {
    $r = mysql_fetch_object($result);
    if($r) {
      dbg("found direct storage access");
      $r->es_id = null;
      $r->rights_id = null;
      $rights[]=$r;
      return $rights;
    }
  }
  else
    error_log("Failed to select rights: ".mysql_error());

  dbg($sql);
  $result = mysql_query($sql);
  if($result === false) {
    error_log("resolve_guid: query failed - $sql: ".mysql_error());
    return false;
  }
  while($r = mysql_fetch_object($result)) {
    $rights[]=$r;
  }

  return $rights;
}

/**
 * Return a node record indicating where the given guid can
 * be found for the given authentication token.
 *
 * Note1: the node record is augmented with an 'auth' attribute 
 * that is set equal to the given auth.
 *
 * Note2: the fact that a node is returned by this function does not guarantee
 * that the authentication token can access the guid.  Rather, this function
 * tells you where to try and access the guid.  Use get_rights() to 
 * determine if the given guid is actually accessible.
 */
function resolve_node($auth, $guid) {
  global $RESOLVE_SQL, $RESOLVE_AUTH_SQL;

  if(preg_match("/[0-9a-z]{40}/",$guid) !== 1)
      throw new Exception("Bad format for guid: $guid");

  if(preg_match("/[0-9a-z]{40}/",$auth) !== 1)
      throw new Exception("Bad format for auth: $auth");

  $sql = sprintf($RESOLVE_AUTH_SQL, $guid, "", $auth); // find entries with any rights
  $result = mysql_query($sql);
  if($result === false) {
    error_log("resolve_node: query failed - $sql: ".mysql_error());
    return false;
  }
  if(mysql_num_rows($result)==0) {
    return false;
  }

  $node = mysql_fetch_object($result);

  $node->auth = $auth;

  return $node;
}

/**
 * Determine the logged in user's auth credentials, if any and return them.
 * If not logged in, return false.
 */
function get_auth() {
	if(!isset($_COOKIE['mc'])) {
    return get_public_auth(); // not logged in
  }

  $c = $_COOKIE['mc'];
  $props = explode(',',$c);
  $auth = false;
  for($i=0; $i<count($props); $i++) {
    list($prop,$val)= explode('=',$props[$i]);
    if($prop=='auth') {
      $auth = $val; break;
    }
  }
  return $auth;
}

function generate_authentication_token() {
    // Generate a random id
    // TODO:  this is weak.  Improve it.
    return sha1(strval(time()).strval(rand()));
}

/**
 * Returns an auth token for public / anonymous user
 */
function get_public_auth() {
  $token = generate_authentication_token();
  $result = mysql_query("insert into authentication_token (at_id, at_token, at_account_id) values (NULL, '$token','".$GLOBALS['PUBLIC_MEDCOMMONS_ID']."')");
  if(!$result) {
    error_log("Failed to create authentication token: ".mysql_error());
    return false;
  }
  return $token;
}

/**
 * Finds the correct node for displaying the given guid for the currently
 * logged in user, if any.  
 *
 * @return - if a node is found, a node record specifying the gateway to use for 
 *           accessing the content.  The node record is augmented with an 'auth'
 *           attribute indicating the auth that should be used for accessing the 
 *           specified gateway.
 */
function find_node($guid, $auth = null) {
  if($auth == null)
    $auth = get_auth();

  if($auth === false) {
    return;
  }

  $node = $node = resolve_node($auth,$guid);
  if($node === false) { // Not found with normal auth, is it found with open id auth?
    $openidAuth = isset($_COOKIE['mc_anon_auth']) ? $_COOKIE['mc_anon_auth'] : false;
    if($openidAuth) {
      error_log("## resolving using anon auth ".$openidAuth);
      $node = resolve_node($openidAuth,$guid);
      $auth = $openidAuth;
    }
  }

  return $node;
}

function can_resolve_guid($accid, $guid, $rights) {
  global $RESOLVE_SQL;
  $sql = sprintf($RESOLVE_SQL, $guid, $rights, $accid);
  $result = mysql_query($sql);
  if($result === false) {
    error_log("resolve_guid: query failed - $sql");
    return false;
  }
  return $result ? mysql_fetch_object($result) : false;
}

/**
 * Return the first document location accessible for the given tracking number and PIN
 */
function resolve_tracking($tracking, $pinHash=null, $auth=null) {
  $db = DB::get();

  $params = array($tracking);

    $sql =  "select * from rights r, document d, document_location l, tracking_number t
             where t.tracking_number = ? 
               and t.doc_id = d.id
               and ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
               and t.es_id = r.es_id
               and r.active_status = 'Active'
               and l.document_id = d.id";

  if($pinHash) {
    $sql .= " and t.encrypted_pin = ?";
    $params[]= $pinHash;
  }
  $result = $db->query($sql,$params);

  if(count($result) > 0) 
    return $result[0];

  // Not found using PIN, but maybe they have rights anyway
  if($auth) {
    $sql = "select * 
            from rights r, document d, document_location l, tracking_number t, authentication_token at
            where t.tracking_number = ?
            and ((r.document_id = d.id) or (r.storage_account_id = d.storage_account_id))
            and t.doc_id = d.id
            and r.active_status = 'Active'
            and l.document_id = d.id
            and at.at_account_id = r.account_id
            and at.at_token = ?
            order by at.at_priority desc, at.at_id asc";


    $result = $db->query($sql, array($tracking, $auth));
    
    if(count($result) > 0) 
      return $result[0];
  }

  // Not found
  return false;
}

/**
 * Resolve a node for use by the given account for data storage
 *
 * Note: this is an analog of same function under /acct/alib.inc.php
 * which is currently not implemented.
 */
function allocate_node($accid) {
  $sql = "select n.*, 1 as priority
          from node n,document d, document_location l
          where d.storage_account_id = $accid
          and l.document_id = d.id
          and l.node_node_id = n.node_id
          union 
          select n2.*, 2 as priority from node n2";

  if(!($result = mysql_query($sql))) {
    error_log("failed to query nodes for allocation: ".mysql_error());
    return false;
  }

  // Look for available node
  $nodes = array();
  while($n = mysql_fetch_object($result)) {
    if($n->priority == 1) {
      return $n;
    }
    else
      $nodes[]=$n;
  }

  // No priority 1 nodes available (ones already used for this use) - fall back to priority 2
  if(count($nodes) > 0)
    return $nodes[0];

  error_log("no node identified from ".count($nodes)." to create content for account $accid");
  return false;
}

/**
 * Return the rights that the provided authentication token has to 
 * the given account.
 *
 * @param auth - authentication possessing the rights to be resolved
 * @param toAccount - the account for access to which rights are to be resolved
 * @throws Exception - for invalid inputs, database failures
 */
function get_rights($auth, $toAccount) {
   dbg("Decoding token $auth for rights to $toAccount");

   if(preg_match("/^([a-z]*:){0,1}[a-z0-9]{0,64}$/i",$auth)!==1) 
     throw new Exception("Invalid authentication token $auth");

   if(preg_match("/^[0-9]{16}$/i",$toAccount)!==1) 
     throw new Exception("Invalid account id  $toAccount");

   // HACK: allow auth token Gateway through
   // Needed because gateway does CXP transactions with this token.  Needs
   // to be fixed inside gateway
   if(($auth == 'token:Gateway') || ($auth == 'Gateway') || ($auth == 'RLSHandler')) {
     dbg("return rights using hard coded token $auth");
     return "RW";
   }

   $rights = "";

   // Special case for POPS account
   if($toAccount === $GLOBALS['PUBLIC_MEDCOMMONS_ID']) { // POPS
     // Everybody can store
     // Nobody can read (at least, not everything)
     return "W";
   }

   $accounts = get_authorized_accounts($auth);

   if($accounts === false) 
     throw new Exception("Unabled to resolve authorized accounts for token $auth");

   dbg("decoded auth token to ".count($accounts)." accounts");

   // Check accounts in order
   foreach($accounts as $a) {
     if($a->accId == $toAccount) {
       $rights = "RW";
     }
     else {
       $result =
         mysql_query("select r.rights from rights r 
                        where r.storage_account_id = '$toAccount' 
                        and r.account_id = '{$a->accId}' ".($a->esId ? " or r.es_id = {$a->esId}" : "")." and active_status = 'Active'");

       if(!$result) {
         error_log("Unable to query rights for account : ".mysql_error());
         throw new Exception("Internal error: failed to query rights for account");
       }

       if($result) {
         while($r = mysql_fetch_array($result)) {
           $rights .= $r[0];
         }
       }
     }

     // Use rights from first entry that has a record
     if($rights != "") {
       break;
     }
   }

   return $rights;
}

if(isset($_REQUEST['__mc_test_securelib_inc_php'])) {

  require_once "../acct/testdata_ids.inc.php";

  function inssql($sql) {
    testsql($sql);
    return mysql_insert_id();
  }

  function testsql($sql) {
    $result = mysql_query($sql);
    if(!$result) {
      throw new Exception("SQL failed: ".mysql_error()." SQL: $SQL");
    }
    return $result;
  }

  function insert_rights($rights,$storage_account_id, $accountId, $esId) {
    if($esId == null)
      $esId = "NULL";

    if($accountId == null)
      $accountId = "NULL";
    else
      $accountId = "'".$accountId."'";

     return inssql("insert into rights (rights_id, account_id, document_id, rights, storage_account_id, es_id)
                    values (NULL, NULL,'{$rights}', '{$storage_account_id}'");
  }

  dbconnect();

  // Delete all rights for user2
  testsql("delete from rights where account_id = $user2Id");

  // Give rights to user1 who is not a member of a group

}

?>
