<?php
/*
 * Main sample application logic.
 *
 * This file handles all the actions of a logged in user.
 *
 * Copyright 2008 MedCommons Inc.
 */

require_once 'setup.inc.php';
require_once 'common.php';
require_once './OAuth.php';

/***************************  main starts here  ********************************/

// Handle openid requests without requiring login
if (isset($_GET['openid_ns']) || isset($_GET['openid_mode'])) {
  handle_openid();
}

// All the functions below this line require login
check_login('officer,member'); // only returns if logged in as officer or member

// Switch to handle various cases below.
if(isset($_REQUEST['importmember'])) {
  handle_import_member();
} 
else
if(isset($_REQUEST['addmemberpost'])) {
  handle_add_member_post();
} 
else
if(isset($_REQUEST['authorize_member'])) { // Appliance callback for successful authorization
  handle_authorize_member();
}
else
if(isset($_REQUEST['addmember'])) {
  handle_add_member();
}
else { // no special arguments
  handle_default();
}

/***************************  end main code section ****************************/


/***************************  supporting functions  ****************************/

function error_redirect($err) {
    header("Location: index.php?logout");
    echo "redirecting to index.php?logout&err=".urlencode($err);
    exit;
}

function my_identity()
{
  if (!isset($_COOKIE['u']))
  {
    return "NotLoggedIn";
  }
  return  urldecode($_COOKIE['u']);
}


/**
 * Check if the user is logged in and also in one of the given roles
 * where roles are specified in a comma delimited form.
 *
 * If not, user is redirected to an error page and logged out.
 */
function check_login($roles)
{
  // Logged in at all?
  if(!isset($_COOKIE['u']))  // not logged in
    error_redirect("not logged in");

  $openid = urldecode($_COOKIE['u']);
  $result = dosql("select * from users where openid='".mysql_real_escape_string($openid)."'");
  $r = mysql_fetch_object($result);
  if(!$r)
    error_redirect("nouser"); 

  if(in_array($r->role, explode(',',$roles)))
    return $r->role;

  error_redirect("badrole");
}

/**
 * Execute the given SQL and return result.
 *
 * Checks for failure and exits on failure with 
 * error message.
 */
function dosql($q)
{
  if(!isset($GLOBALS['db_connected']) ){
      $GLOBALS['db_connected'] = mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
      $db = $GLOBALS['DB_Database'];
      mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
  }

  $status = mysql_query($q);
  if (!$status) 
    die ("dosql failed $q ".mysql_error());

  return $status;
}

/**
 * Create a club member with the given attributes.  If a HealthURL is not specified,
 * make a HealthURL for the member on the default configured MedCommons appliance.
 */
function makemember($healthurl, $ln,$fn,$dob,$sex,$img, $status)
{
  dbg("making member with healthurl $healthurl");
  if(!$healthurl) {
    $api = new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$GLOBALS['appliance']);
    $result = $api->new_health_url($ln,$fn,$dob,$sex,$img);
    $healthurl = $GLOBALS['appliance'].$result->patientMedCommonsId;
    $auth = $result->auth;
    $secret = $result->secret;
    dbg("created healthurl $healthurl auth $auth secret $secret");
  }
  else {
    $auth = "";
    $secret = "";
  }
  // lets be careful and make sure we always make new records
  $sql = "insert into users set 
    role='member', 
    name='".mysql_real_escape_string("$fn $ln")."',  
    imageurl='".mysql_real_escape_string($img)."', 
    oauthtoken='$auth,$secret', 
    born='".mysql_real_escape_string($dob).".', 
    status='".mysql_real_escape_string($status)."',
    healthurl='".mysql_real_escape_string($healthurl)."'";

  $status = mysql_query($sql);
  if ($status == false ) return false;

  $memberind = mysql_insert_id(); // get last
  return $memberind;
}

function membersetupform($fn,$fnerr,$gn,$gnerr,$dob,$doberr,$sex,$sexerr,$img,$imgerr,$hurl,$hurlerr,$oauth,$oautherr)
{
  //<tr><td class=prompt>oauth</td><td><input class=infield type=text name=oauth value='$oauth' /></td><td class=errfield>$oautherr</td></tr>
  $maleselected= ($sex=='M')?'selected':'';
  $femaleselected= ($sex=='F')?'selected':'';
  if ($doberr=='') $doberr="<small>e.g. 11/23/87</small>";
  if ($oautherr=='') $oautherr="<small>token,secret pair, leave blank to authorize after submission</small>";
  $form =<<<XXX
<input type='hidden' name='oauth' value='$oauth'/>
<fieldset>
<legend>Create New member</legend>
<table>
<tr><td class=prompt>family name</td><td><input class=infield type=text name=familyName value='$fn' /></td><td class=errfield>$fnerr</td></tr>
<tr><td class=prompt>given name</td><td><input class=infield type=text name=givenName value='$gn' /></td><td class=errfield>$gnerr</td></tr>
<tr><td class=prompt>date of birth</td><td><input class=infield type=text name=dateOfBirth value='$dob' /></td><td class=errfield>$doberr</td></tr>
<tr><td class=prompt>image url</td><td><input class=infield type=text name=image value='$img' /></td><td class=errfield>$imgerr</td></tr>

<tr><td class=prompt>sex</td><td><select  class=infield name=sex>
<option value='M' $maleselected >male</option>
<option value='F' $femaleselected >female</option>
</td><td>$sexerr</td></tr>
<tr><td></td><td><input type=submit name=addmemberpost value='Create member'/></td><td></td></tr>
</table>
</fieldset>
<p></p>

<fieldset>
<legend>Import Existing HealthURL</legend>
<table>
<tr><td class=prompt>HealthURL</td><td><input class=infield type=text name=hurl size='50' value='$hurl' onchange='document.isform.oauth.value=""' /></td>
    <td class=errfield>$hurlerr</td></tr>
<tr><td>&nbsp;</td><td><input type='submit' name='importmember' value='Import member'/></td><td></td></tr>
</table>
</fieldset>

XXX;
  return $form;
}


function handle_openid() {
  /*******************************
  * TTW 31-Jan-2008 Add OpenID...
  */
  session_start();

  $response = $consumer->complete(get_trust_root() . 'main.php');

  if ($response->status != Auth_OpenID_SUCCESS) {
    if ($response->status == Auth_OpenID_CANCEL)
    $url = 'index.php?err=Verification+Cancelled';
    else if ($response->status == Auth_OpenID_FAILURE)
    $url = 'index.php?err=OpenID+Authentication+Failed';
    else
    $url = 'index.php?err=Unknown+OpenID+Error';

    header("Location: $url");
    exit;
  }

  $openid = $response->identity_url;

  /* ... end of OpenID... use $openid instead of $email */

  $result = dosql ("Select * from users u  where u.openid='$openid' ");
  $r=mysql_fetch_object($result);
  if ($r===false)
  $url ="index.php?err=No+such+user+" . urlencode($openid);
  else
  {
    setcookie('u',urlencode($openid)); // setup a simple cookie to remember where we are
    // pick a starting point based on role
    switch ($r->role)
    {
      case 'member': {$url = $GLOBALS['memberr_start_page'];break;}
      case 'officer':{$url = $GLOBALS['officer_start_page'];break;}
      default :{$url = "index.php?err=badOpcode"; break;}
    }
  }
  header("Location: $url");
  echo ("Redirecting to $url");
  exit;
}

function handle_add_member_post() {
  $any=false;
  $fn = $_REQUEST['familyName']; $fnerr='';
  $gn = $_REQUEST['givenName']; $gnerr='';
  $dob = $_REQUEST['dateOfBirth']; $doberr='';
  $sex = $_REQUEST['sex']; $sexerr='';
  $img = $_REQUEST['image']; $imgerr='';
  $hurl = $_REQUEST['hurl']; $hurlerr='';
  $oauth = $_REQUEST['oauth']; $oautherr='';
  // edit check all the fields
  if (strpos($fn,"'")) {$fnerr = "no quotes allowed in family name"; $any=true;}
  if (strpos($gn,"'")) {$gnerr = "no quotes allowed in given name";$any=true;}
  if ($hurl!='') if ($oauth=='') {$oautherr="Please authorize this HealthURL"; $any=true;}

  if ($any) {
    //addmemberpost
    $header = isheader('Error adding new member',true);
    $formbody = membersetupform($fn,$fnerr,$gn,$gnerr,$dob,$doberr,$sex,$sexerr,$img,$imgerr,$hurl,$hurlerr,$oauth,$oautherr);
    $markup = <<<XXX
$header<h5>please correct these errors to add a member</h5>
<form name='isform' action="?" method=post>
$formbody
</form>
</div>
</body>
XXX;
    echo $markup;
    exit;
  }
  // otherwise create the healthurl and then create the member in our tables
  //echo "making healthurl and then member $fn $gn";
  $memberind = makemember ($hurl, $fn,$gn,$dob,$sex,$img,'test');
  if ($memberind == false) {
    dbg("dupe member");
    $loc = "main.php?addmember=add&err=".urlencode("Duplicate member");
  }
  else  {  // success
    $loc ="member.php?memberind=$memberind";
  }

  dbg("redirecting to $loc");
  header ("Location: $loc");
  echo "Redirecting to $loc";
  exit;
}

function handle_import_member() {
  try {
    $hurl = $_REQUEST['hurl']; $hurlerr='';

    $callback = get_trust_root()."main.php?authorize_member";
    list($req_token,$url)= ApplianceApi::authorize($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$hurl,$callback);

    // set cookie with token and secret
    setcookie('oauth', $req_token->key.",".$req_token->secret.",".$hurl.",".$realm, time()+300); // expire after 300 seconds

    header("Location: $url");
    exit;
  }
  catch(Exception $e) {
    die(isheader('Error adding new member',true)."<p>An error occurred while attempting to authorize the HealthURL you entered.</p><pre>{$e->getMessage()}</pre>");
  }
  exit;
}
function handle_authorize_member() {
  dbg("successful return from authorization call");

  if(!isset($_COOKIE['oauth']))
    die(isheader('Error adding new member',true)."<p>An error occurred while attempting to authorize the HealthURL you entered - missing cookie</p>");

  // We stored the oauth details in a cookie 
  // so get them out again (see handle_import_member() )
  $oauth = explode(",",$_COOKIE['oauth']);
  $hurl = $oauth[2];
  $teamind = $oauth[3];

  try {
    // Exchange the request token we got earlier for 
    // an access token.  This returns an instance of the 
    // ApplianceApi to us, ready to use
    $api = ApplianceApi::confirm_authorization($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$oauth[0], $oauth[1],$hurl);

    // Parse the HealthURL we started with to figure out the user's account id
    list($base_url,$accid) = $api->parse_health_url($hurl);

    // Now we have the account id, use it to fetch the CCR
    $ccr = $api->get_ccr($accid);

    // Got the CCR
    // Get the important details of this patient
    // We have to iterate all the actors looking for the patient
    $patientActorID = $ccr->patient->actorID;
    foreach($ccr->actors->actor as $a) {
      if($a->actorObjectID == $patientActorID) {
        $given = $a->person->name->currentName->given;
        $family = $a->person->name->currentName->family;
        $dob = $a->person->dateOfBirth;

        if(isset($dob->exactDateTime)) {
          $age = (int)((time() - strtotime($dob->exactDateTime)) /  ( 365 * 24 * 60 * 60 ));
        }
        else
        if(isset($dob->age))
        $age = (int)$dob->age->value;

        if(isset($a->person->gender)) {
          $gender = $a->person->gender->text;
        }

        // Found patient, we're done
        break;
      }
    }

    $fmtDob = $dob->exactDateTime ? date("m/d/Y",strtotime($dob->exactDateTime)) : "";
    if($gender == "Female")
      $genderIndex = 1;
    else
    if($gender == "Male")
      $genderIndex = 0;
    else
      $genderIndex = -1;
  }
  catch(Exception $ex) {
    error_log("failed to initialize member from health url: ".$ex->getMessage());
    die(isheader('Error adding new member',true)."<p>An error occurred while attempting to access the HealthURL you entered.</p>");
  }

  // create the member in our tables
  $memberind = makemember($base_url.$accid, $family,$given,$fmtDob,"",null,$teamind,'test');
  if($memberind == false) {
    // Most likely failure here is that there is a duplicate member
    $loc = "index.php?addmember=add&err=".urlencode("Duplicate member");
  }
  else {  // success
    // Update the user's oauth token with the access token returned to us
    // from the authorization call
    $access_token = "{$api->access_token->key},{$api->access_token->secret}";
    dosql("update users set oauthtoken = '$access_token' where userid = $memberind");
    $loc ="member.php?memberind=$memberind";
  }

  header ("Location: $loc");
  echo "Redirecting to $loc";
  exit;
}

function handle_add_member() {
  $formbody = membersetupform($teamname,'','','','','','','','','','','','','',''); // put up a blank form
  $header = isheader("Add member to Club",true);
  $err = @$_REQUEST['err'];
  if($err) {
    $err="<p style='color: red;'>".htmlspecialchars($err)."</p>";
  }
  $markup = <<<XXX
$header
<h5>Add a member to Club</h5>
$err
<form name='isform' action=is.php method=post>
<input type=hidden name=addmemberpost value=addmemberpost />

$formbody
</form>
</div>
</body>
XXX;
  echo $markup;
  exit;
}

function handle_default() {

  if (isset($_REQUEST['err'])) 
    $err=$_REQUEST['err']; 
  else 
    $err='';

  $appl = $GLOBALS['appliance'];
  $markup = <<<XXX
<h3>Club Administration</h3>
$err
<p>We are currently creating new healthURLs on $appl;</p>
<br/>
<h4>Operate on members</h4>
<table>
<tr><td class=prompt><span>Add member/span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=addmember value=add />
<input type=submit value=go name=go  />
</form></td></tr>
<tr><td class=prompt><span>Remove member from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=delmember value=del />
<input type=submit value=go name=go  />
</form>
</td><td>also on member dropdown for is employees</td></tr>
<p>As a courtesy to our friends, we can add and care for anyone on the roster of the team  'Friends of Informed Sports'</p>
</div>

</body>
XXX;
  echo $markup;
}

?>
