<?php

/**
 * Grants a specified account access rights to another account.
 *
 * @param accessBy - comma separated list of accounts that will be granted access
 * @param accessTo - target account that will be madded accessible to accessBy
 * @param es_identity - an external identity to be granted access
 * @param es_identity_type - an external identity type to be granted access
 * @param es_first_name - optional first name of person granted access
 * @param es_last_name - optional last name of person granted access
 * @param es_auth_token - a token to act of parent token created for external share.
 * @param auth - a token having equal or greater rights as those being granted.
 * @param rights - string of characters indicating rights to be granted (eg. "RW").
 *
 * TODO: require auth / login to secure this service
 */
require_once "../ws/securewslibdb.inc.php";
require_once "../securelib.inc.php";
require_once "utils.inc.php";

class grantAccountAccessWs extends jsonrestws {

 function jsonbody(){

    $accessTo=$_REQUEST["accessTo"];
    $accessBy=req('accessBy','');
    $rights=req('rights');
    $es_identity = req('es_identity');
    $es_identity_type = req('es_identity_type');
    $es_auth_token = req('es_auth_token');
    $es_first_name = req('es_first_name');
    $es_last_name = req('es_last_name');

    $ret = new stdClass;

    if($rights == null) 
      throw new Exception('parameter rights is required');

    $accessBys=explode(',',$accessBy);

    foreach($accessBys as $ab) {
      $esId="NULL";
      $mcid="NULL";
      if(is_url($ab)) { // External Id (OpenID)
        $this->dbexec("insert into external_share ( es_id, es_identity, es_identity_type )
                       values (NULL, '$ab', 'openid')", "Unable to create external share");
        $esId = mysql_insert_id();
      }
      else {
        $mcid = "'$ab'";
      }

      $result = $this->dbexec(
        "INSERT INTO rights (account_id,es_id,storage_account_id,rights,creation_time) ".
        "VALUES($mcid,$esId,'$accessTo','$rights',NOW())","Unable to insert rights");
    }

    if($es_identity) {

        $result = $this->dbexec("insert into external_share ( es_id, es_identity, es_identity_type, es_first_name, es_last_name )
          values (NULL, '".mysql_real_escape_string($es_identity)."', ".
                 "'".mysql_real_escape_string($es_identity_type)."', ".
                 ($es_first_name?"'".mysql_real_escape_string($es_first_name)."'":null).",".
                 ($es_last_name?"'".mysql_real_escape_string($es_last_name)."'":null).")",
                 "Unable to create external share");

        if(!$result)
          throw new Exception("Failed to insert new external share: ".mysql_error());

        $esId = mysql_insert_id();

        $result = $this->dbexec(
          "INSERT INTO rights (account_id,es_id,storage_account_id,rights,creation_time) ".
          "VALUES(NULL,$esId,'$accessTo','$rights',NOW())","Unable to insert rights");

        if(!$result) 
          throw new Exception("Failed to insert external share: ".mysql_error());

        $result = $this->dbexec("select * from authentication_token where at_token = '$es_auth_token'","failed to select from authentication_token");
        $parent_at = mysql_fetch_object($result);
        if(!$parent_at)
          throw new Exception("Failed to find authentication token ".$es_auth_token);

        // Create a new authentication token 
        $at = generate_authentication_token();
        $secret = generate_authentication_token();

        $this->dbexec("insert into authentication_token (at_id, at_token, at_secret, at_es_id, at_parent_at_id) 
                       values (NULL, '$at','$secret', $esId, {$parent_at->at_id})", "unable to insert authentication_token");

        $ret->es_id = $esId;
        $ret->authentication_secret = $secret;
        $ret->authentication_token = $at;
    }
    return $ret;
  }
}

// main
$x = new grantAccountAccessWs();
$x->handlews("grantAccountAccess_Response");
?>
