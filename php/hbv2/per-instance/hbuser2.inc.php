<?
//require_once "globals.inc.php";
require_once "appinclude.php";
require_once "./OAuth.php";
require_once "./mc_oauth_client.php";

/**
 * Basic class modelling a HealthBook user.
 */
class HealthBookUser {
  public $mcid = false;
  public $fbid = false;
  public $gw;
  public $gw_update_time;
  public $appliance;
 // no longer needed --  public $targetfbid;
 public $sponsorfbid;
 public $familyfbid;
  public $targetmcid;
  public $token = false;
  public $secret = false;

  // True if this user's storage account has been claimed
  // ie. by printing skeys.
  public $storage_account_claimed = false;

  /*
   * Private fields, access using getXXX()
   */

  /**
   * Target user, loaded using load()
   */
  private $targetUser = null;

  /**
   * Facebook information about the user - loaded lazily
   */
  private $profileInfo;
  
  private $fname, $lname;

  /**
   * Returns an appropriate possessive pronoun for this user
   */
  public function my_str() {
    return //($this->fbid==$this->targetfbid )?'My':
    "<fb:name possessive='true' uid=$this->familyfbid></fb:name>";
  }

  /**
   * Returns the health url of the target user for this user
   */
  public function t_hurl() {
    $t = $this->getTargetUser();
     if (!$t) return false;
   	return $t->authorize($t->appliance.$t->mcid,$this);
  }

  /**
   * Return first name of user, may attempt to load from Facebook
   */
  public function getFirstName() {
  	return $this->firstname;
   }

  /**
   * Return last name of user, may attempt to load from Facebook
   */
  public function getLastName() {
  return $this->lastname;
   
  }

  /**
   * Returns the current target user for this HealthBook user
   */
  public function getTargetUser() {

    if($this->targetUser == null) 
      $this->targetUser = HealthBookUser::loadbymcid($this->targetmcid);  // just get this simply
  

    return $this->targetUser;
  }

  public function authorize($url, $u = null) {

    global $oauth_consumer_key;
    global $oauth_consumer_secret;

    if($u == null)
      $u = $this;

    if(strpos("$url","?")===FALSE)
      $url .= "?";
    else
      $url .= "&";

    // $result = $url."oauth_token=".$this->getTargetUser()->token;

    // Add identity information so that receiving appliance can 
    // identify our facebook user
    $result = $url."identity_type=Facebook".
      "&identity=".$this->fbid.
      "&identity_name=".urlencode($u->getFirstName()." ".$u->getLastName());

    $api = $this->getOAuthAPI();
    if(!$api) {
      error_log("Unable to create oauth api for user ".$this->fbid);
      return false;
    }

    return $api->sign($url);
  }

  /**
   * Returns an OAuth API configured for accessing the user's appliance
   */
  public function getOAuthAPI() {
    global $oauth_consumer_key, $oauth_consumer_secret;

    if(!$this->mcid)
      return false;

    if(!$this->token)
      return false;

    $api = new ApplianceApi($oauth_consumer_key, $oauth_consumer_secret, rtrim($this->appliance,"/"), $this->token, $this->secret);
    return $api;
  }

  private function connect_db() {
    mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or err("Error connecting to database.");
    $db = $GLOBALS['DB_Database'];
    mysql_select_db($db) or die("can not connect to database $db");
  }



  /**
   * Loads the requested face book user as a HealthBook user.  Returns false if not found.
   *
   * @param fbid - facebook id of user to load
   */
  public static function load($fbid) {

    $hbc = new HealthBookUser();

    $hbc->connect_db();

    error_log("loading facebook user $fbid");

    $q = "select *, UNIX_TIMESTAMP(gw_modified_date_time) as gwtime from fbtab where fbid = '$fbid' ";
    $result = mysql_query($q) or die("cant select from  $q ".mysql_error());
    $u=mysql_fetch_object($result);
    if($u==false) {
      return false;
    }
   //echo "in load fbid is $fbid mcid is $u->mcid";
    // Initialize basic fields
    if ($u->mcid=='0') 
      $hbc->mcid='0'; 
    else //patched by bill
    {
      $hbc->mcid=$u->mcid ? $u->mcid : false;
    }

    $hbc->appliance = $u->applianceurl ? rtrim($u->applianceurl,'/')."/" : false;
    $hbc->targetmcid = $u->targetmcid;
  	$hbc->sponsorfbid = $u->sponsorfbid;
    $hbc->familyfbid = $u->familyfbid;
    $hbc->firstname = $u->firstname;
    
    $hbc->lastname = $u->lastname;
    
    $hbc->token = $u->oauth_token;
    $hbc->secret = $u->oauth_secret;
 //   $hbc->targetfbid = $u->targetfbid;
    $hbc->fbid = $fbid;
    $hbc->storage_account_claimed = isset($u->storage_account_claimed) && ($u->storage_account_claimed > 0);

    $json = new Services_JSON();
    $hbc->gw = false;
    if ($hbc->mcid!='0') {
      // none of this if there is no medcommons account
      $hbc->gw_update_time = $u->gwtime;

      // If gateway more than 24 hours stale then fetch it again
      if(($u->gw != null) && ($u->gwtime > time()-86400)) {
        $hbc->gw = $u->gw;
      }
      else {
        $gwres = $json->decode(file_get_contents($hbc->appliance."/acct/ws/queryAccountNode.php?accid=".$hbc->mcid));
        if($gwres && ($gwres->status == "ok")) {
          $hbc->gw = $gwres->gw;
        }
        else
          die("Unable to associate to gateway");

        mysql_query("update fbtab set gw='".mysql_real_escape_string($hbc->gw)."', gw_modified_date_time=CURRENT_TIMESTAMP where fbid = ".$u->fbid);
        $hbc->gw_update_time = time();
      }
    }
    return $hbc;
  }
 public static function loadbymcid($mcid) {
	$fbid = 0;

    $hbc = new HealthBookUser();

    $hbc->connect_db();

    error_log("loading medcommons target mcid $mcid");

    $q = "select *, UNIX_TIMESTAMP(gw_modified_date_time) as gwtime from mcaccounts where mcid = '$mcid' ";
    $result = mysql_query($q) or die("cant select from  $q ".mysql_error());
    $u=mysql_fetch_object($result);
    if($u==false) {
      return false;
    }
   //echo "in load fbid is $fbid mcid is $u->mcid";
    // Initialize basic fields
    if ($u->mcid=='0') 
      $hbc->mcid='0'; 
    else //patched by bill
    {
      $hbc->mcid=$u->mcid ? $u->mcid : false;
    }

    $hbc->appliance = $u->applianceurl ? rtrim($u->applianceurl,'/')."/" : false;
 //   $hbc->targetmcid = $u->targetmcid;
     $hbc->familyfbid = $u->familyfbid; 
  $hbc->sponsorfbid = $u->sponsorfbid;
        $hbc->firstname = $u->firstname;
    
    $hbc->lastname = $u->lastname;
    $hbc->token = $u->oauth_token;
    $hbc->secret = $u->oauth_secret;
// $hbc->targetfbid = $u->targetfbid;
   
    $hbc->storage_account_claimed = isset($u->storage_account_claimed) && ($u->storage_account_claimed > 0);

    $json = new Services_JSON();
    $hbc->gw = false;
    if ($hbc->mcid!='0') {
      // none of this if there is no medcommons account
      $hbc->gw_update_time = $u->gwtime;

      // If gateway more than 24 hours stale then fetch it again
      if(($u->gw != null) && ($u->gwtime > time()-86400)) {
        $hbc->gw = $u->gw;
      }
      else {
        $gwres = $json->decode(file_get_contents($hbc->appliance."/acct/ws/queryAccountNode.php?accid=".$hbc->mcid));
        if($gwres && ($gwres->status == "ok")) {
          $hbc->gw = $gwres->gw;
        }
        else
          die("Unable to associate to gateway");

        mysql_query("update fbtab set gw='".mysql_real_escape_string($hbc->gw)."', gw_modified_date_time=CURRENT_TIMESTAMP where fbid =0 and mcid= ".$u->mcid);
        $hbc->gw_update_time = time();
      }
    }
    return $hbc;
  }
}

if(isset($_REQUEST['hbuser_test'])) {
  // Test harness
  $u = HealthBookUser::load(726998047);
?>
  <html>
    <body>
      <p>HealthBook user <?=$u->fbid?> has MedCommons ID <?=$u->mcid?> and is associated with gateway <?=$u->gw?> which was last updated
         at time <?=$u->gw_update_time?></p>
      <p>
       <?if($u->getTargetUser()):?>
         This user is associated with target <?=$u->getTargetUser()->fbid?> who has mcid <?=$u->getTargetUser()->mcid?>.
       <?else:?>
         This user is not associated with a target facebook account.
       <?endif;?>
     </p>
    </body>
  </html>
<?
}
?>
