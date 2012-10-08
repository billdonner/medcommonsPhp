<?
/**
 * OAuth Authorization
 *
 * Implements handling of OAuth Authorization requests.  Displays
 * a page allowing user to select appropriate permissions and then
 * choose to whether to authorize or not.
 *
 * If user is not logged in, or is logged into an account that has
 * insufficient rights to grant consent then they will be redirected 
 * to login page.
 *
 * Handles postback response and inserts appropriate rights attached 
 * to the external_share created for the request_token. Redirects
 * back to callback page or displays generic success if no callback.
 */
  require_once("db.inc.php");
  require_once("utils.inc.php");
  require_once("JSON.php");
  require_once("template.inc.php");
  require_once("../secure/securelib.inc.php");

  class InsufficientRightsException extends Exception {
  }

  class AuthorizationNotGrantedException extends Exception {
  }

  nocache();

  // Layout template (styled by appliance owner)
  $layout = template("base.tpl.php")->set("title","Authorize Access to MedCommons HealthURL")->set("head","");
  $t = new Template();

  try {
    // Expect to get oauth_token as request parameter 
    $token = req('oauth_token');
    if($token == null)
      throw new Exception("Expected parameter 'oauth_token' missing");

    // Expect to get account id to authorize access to
    $accid = req('accid');
    if($accid == null) 
      throw new Exception("Expected parameter 'accid' missing");

    if(preg_match("/^[0-9]{16}$/",$accid)!==1) 
      throw new Exception("Parameter 'accid' has incorrect format");

    $realm = req('realm');
    if(preg_match("/^[0-9A-Za-z _]{0,200}$/",$realm)!==1) 
      throw new Exception("Parameter 'realm' only accepts alpha numeric characters, spaces and underscores");

    // Check if the user is authorized for the account
    if(!isset($_COOKIE['mc'])) 
      throw new InsufficientRightsException();

    $auth = get_auth();
    dbconnect();
    $rights = get_rights($auth, $accid);
    if(strpos($rights, "W") === FALSE) 
      throw new InsufficientRightsException();

    $callback = req('oauth_callback');

    // Content template
    $t->set("token",$token)->set("callback", $callback)->set("accid",$accid);
    $t->set("rights", isset($_REQUEST['rights']) ? $_REQUEST['rights'] : "RW");
    $t->set("realm", $realm);

    $db = DB::get();

    // Figure out the external share that corresponds to this token
    $es = $db->query("select * from external_share es, authentication_token at
                      where at_es_id = es_id and at_token = ?", array($token));
    if(count($es)==0)
      throw new Exception("Invalid token: $token");

    $es = $es[0];

    $t->set("es",$es);
    $t->set("hurl",gpath("Secure_Url")."/".$accid);

    // Query settings for account to get name
    $settings = get_url(gpath("Accounts_Url")."/ws/querySettings.php?accid=$accid");

    // Hack: parse XML using preg_match
    if(preg_match(",<firstName>(.*?)</firstName>.*<lastName>(.*?)</lastName>,",$settings,$matches)!==1) {
      error_log("Account settings call failed:  $settings");
      throw new Exception("Unexpected format returned in Account Settings");
    }

    $t->esc("firstName",$matches[1]);
    $t->esc("lastName",$matches[2]);

    if(isset($_POST['authorize'])) {
      if(!isset($_POST['authorized'])) 
        throw new AuthorizationNotGrantedException();
 
      // Grant actual rights
      $rights = $_REQUEST['rights'];

      if($realm) {
        $es->es_identity = $es->es_identity." / ".$realm;
        $db->execute("update external_share set es_identity = ? where es_id = ?",
                      array($es->es_identity,  $es->es_id));
      }

      // Log the change of consents in the user's activity log
      $node = allocate_node($accid);
      $json = new Services_JSON();
      $desc = "$rights access granted to ".$es->es_identity." (".$es->es_identity_type.")";
      $activityUrl = rtrim($node->hostname,"/")."/Activity.action?type=CONSENT_UPDATE".
        "&accid=$accid&auth=$auth".
        "&description=".urlencode($desc);

      dbg("calling activity log url: $activityUrl");
      $result = $json->decode(file_get_contents($activityUrl));
      if(!$result || ($result->status != "ok")) 
        throw new Exception("Unable to update activity log: ".($result?$result->error : "protocol error"));

      // Add rights to the es_id
      $db->execute("insert into rights (rights_id, rights, storage_account_id, es_id)
                    values (NULL, ?, ?, ?)", array($rights, $accid, $es->es_id));

      if(($callback != null) && ($callback !="")) {
        // Redirect back
        header("Location: $callback");
      }
      else 
        echo $layout->set("content",$t->fetch("authorization_completed.tpl.php"))->fetch();
    }
    else
    if(isset($_POST['cancel'])) {
        // Redirect back without authorizing
        header("Location: $callback");
    }
    else { // Display initial authorization page
      echo $layout->set("content",$t->fetch("authorize_token.tpl.php"))->fetch();
    }
  }
  catch(AuthorizationNotGrantedException $e) {
      echo $layout->set("content",$t->set("validation_error","authorized")->fetch("authorize_token.tpl.php"))->fetch();
  }
  catch(InsufficientRightsException $e) {
    // Send user over to login page
    header("Location: ".gpath('Accounts_Url')."/login.php?next=".urlencode(gpath('Commons_Url')."/../api/authorize.php?".$_SERVER['QUERY_STRING'])."&access_accid=".$accid);
  }
  catch(Exception $e) {
    error_log("Failure when authorizing OAuth token - ".$_SERVER['QUERY_STRING']." error=".$e->getMessage());
    echo $layout->set("content",$t->set("message",$e->getMessage())->fetch("error.tpl.php"))->fetch();
  }
?>
