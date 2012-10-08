<?
  require_once "alib.inc.php";
  require_once "template.inc.php";

  // Translate the types
  $TYPES=array('PATPHOTO' =>  "Patient Photo", 
    "CURRENTCCR" => "Current CCR",
    "LIVINGWILL" => "Living Will",
    "DNR" => "Do Not Resuscitate",
    "REPLYCCR" => "Reply CCR",
    "NEWCCR" => "New CCR",
    "DURABLEPOA" => "Durable Power of Attorney"
  );

  $user = get_account_info();
  if($user == false) {
    // Check if we have oauth authorization
    try {
      dbg("verifying oauth url ...");
      verify_oauth_url();

      // TODO: Should check read access to the account
    }
    catch(Exception $ex) {
      error_page("You must be logged in to access this content.");
    }
  }

  $accid = isset($_REQUEST['accid']) ?$_REQUEST['accid'] : $user->accid; 
  $forUser = pdo_query("select * from users where mcid = ?",$accid);
  if(count($forUser)==0)
    error_page("Unable to locate selected user ".$accid);

  $forUser = $forUser[0];

  $gw = allocate_gateway($accid);

  $t = template("accountDocuments.tpl.php");

  $documents = pdo_query("select * from document_type where dt_account_id = '".$accid."' order by dt_create_date_time desc");
  if($documents === false)
    error_page("A problem was experienced while trying to query your account documents.");

  $t->set("documents",$documents)->set("TYPES",$TYPES)->set("gw",$gw)->set("user",$user)->set("forUser",$forUser);

  if(req('t')=="widget") {
    $page = template("widget.tpl.php");
  }
  else
    $page = template("base.tpl.php");


  echo $page->set("head","")->set("content",$t)
    ->set("title",htmlspecialchars("Documents for ".$forUser->first_name." ".$forUser->last_name))
    ->fetch();
?>
