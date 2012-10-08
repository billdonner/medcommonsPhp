<?
 /**
  *  Adds given account id to user's existing auth token and then redirects
  *  onward to specified url
  *
  *  @param accid - account to add
  *  @param return - return URL to redirect back to 
  *
  *  TODO: make this service require signature!!!
  */
  require_once "securelib.inc.php";
  require_once "utils.inc.php";
  
  nocache();

  dbconnect();

  // Find the current auth
  $auth = get_auth();

  $return = isset($_REQUEST['return']) ? $_REQUEST['return'] : '';
  $accid = isset($_REQUEST['accid']) ? $_REQUEST['accid'] : false;

  try {
    if($auth === false) 
      throw new Exception("Attempt to add auth without existing auth");

    if(!preg_match("/[a-z0-9]{40}/", $auth))
      throw new Exception("Bad authentication token value");

    if(!preg_match("/[0-9]{16}/", $accid))
      throw new Exception("Bad account id value");

    // Is user already authorized for this account?
    $r = mysql_query("select count(*) from authentication_token where at_token = '$auth' and at_account_id = '$accid'");
    if(!$r)
      throw new Exception("Unable to query authentication token for account id $accid - ".mysql_error());

    $r = mysql_fetch_row($r);
    if($r && ($r[0] == 0)) { // does not already exist
      // Add to the auth
      if(!mysql_query("insert into authentication_token (at_id, at_token, at_account_id) values (NULL, '$auth', '$accid')")) 
        throw new Exception("Unable to insert authentication token for account id $accid - ".mysql_error());
    }
  }
  catch(Exception $e) {
    error_log("Unable to reauth token for token $auth: ", $e->getMessage());
    echo "<p>Error: ".$e->getMessage()."</p>";
  }

  if($return) {
    header("Location: $return");
  }
?>
