<?
/**
 * destroy_token.php
 *
 * Destroys the requested token and any rights associated with it 
 * so that it can no longer be used.
 */
require_once "urls.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";
require_once "mc_oauth.inc.php";
require_once("../secure/securelib.inc.php");

dbg("delete_token.php");

$result = new stdClass;
$json = new Services_JSON();

$oauth = new OAuthServer(new AuthTokenOAuthDataStore());
$oauth->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());

try {
  $req = OAuthRequest::from_request();
  list($consumer, $oauth_token) = $oauth->verify_request($req);

  // list back the non-OAuth params
  $params = array();
  foreach($req->get_parameters() as $k => $v) {
    if(substr($k, 0, 5) == "oauth") 
      continue;
    $params[$k] = $v;
  }

  $token = @$params['token'];
  if(!$token)
    throw new Exception("Missing required parameter token");

  if(preg_match("/^[0-9a-z]{40}$/",$token)===0)
    throw new Exception("Invalid token");

  // Ok, token looks good, delete it
  dbg("deleting token ".$token." for consumer with key ".$consumer->key);

  $db = DB::get();

  // Validate the parent
  $parent = $db->query("select distinct atp.at_id
              from authentication_token at, authentication_token atp
              where at.at_parent_at_id = atp.at_id
              and at.at_token = ?
              and atp.at_token = ?",array($token, $consumer->key));

  if(count($parent) !== 1)
    throw new Exception("Unknown token or token issued to different consumer");

  $parent_at_id = $parent[0]->at_id;

  $rights = $db->query("select r.* from rights r, external_share es, authentication_token at
                        where r.es_id = es.es_id
                              and r.active_status = 'Active'
                              and at.at_es_id = es.es_id
                              and at.at_token = ?
                              and at.at_parent_at_id = ?",array($token, $parent_at_id));
  dbg("got ".count($rights)." rights entries for token to be deleted");
  if(count($rights)>0) {
    $desc = "Access Token Revoked";
    $accid = $rights[0]->storage_account_id;
    if($accid) {
      dbconnect();
      $node = allocate_node($accid);
      $activityUrl = rtrim($node->hostname,"/")."/Activity.action?type=CONSENT_UPDATE".
        "&accid=$accid&auth=$token".
        "&description=".urlencode($desc);

      dbg("calling activity log url: $activityUrl");
      $activity_result = $json->decode(get_url($activityUrl));
      if(!$activity_result || ($activity_result->status != "ok")) 
        throw new Exception("Unable to update activity log for account $accid: ".($activity_result?$activity_result->error : "protocol error"));
    }
  }

  $db->execute("update rights r, external_share es, authentication_token at
                set r.active_status = 'Inactive'
                where r.es_id = es.es_id
                      and r.active_status = 'Active'
                      and at.at_es_id = es.es_id
                      and at.at_token = ?
                      and at.at_parent_at_id = ?",array($token, $parent_at_id));

  // Just to be really sure, delete the secret for the specified token
  $db->execute("update authentication_token set at_secret = NULL where at_token = ?",array($token));

  $result->status = "ok";
} 
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
echo $json->encode($result);
?>
