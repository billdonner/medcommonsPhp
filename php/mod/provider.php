<?php

require_once "modpay.inc.php";
require_once "template.inc.php";
require_once "utils.inc.php";
require_once "db.inc.php";

// start here
$v = new stdClass;
$v->err = $v-> name_err = $v->dob_err = $v->name = $v->dob = $v->email_err = $v->email =$v->note= '';
$v-> prname_err = $v->prname =  $v->premail_err = $v->premail ='';

$header = page_header("page_provider","Providers - MedCommons Home Page"  );
$footer = page_footer();

// error check these args
$errs =array()  ;
$id = req('id');
$svcnum = -1;

// read the provider name and email
$db = DB::get();
$r = $db->first_row("select * from users where mcid=?",array($id));

if(!$r)
  $errs[] = array('err',"Can't find any provider on this appliance with that mcid");
else {
    $v->premail = $r->email;
    $v->prname = $r->first_name.' '.$r->last_name;

    if(isset($_POST['name'])) {
        if(!isset($_POST['svcmenu']))
          $errs[] = array('service_err',"Please select a service");

        if(strlen($_POST['note']) > 250)
          $errs[] = array('note_err',"Instructions are too long - please enter less than 250 characters");

        $svcnum = isset($_POST['svcmenu']) ? $_POST['svcmenu'] : -1; // get the one radio button chosen
        $v->name=$_POST['name'];
        if (strlen($v->name)==0) $errs[] = array('name_err',"First name Last name required");
        if (isset($_POST['prname']))
          $v->prname=$_POST['prname'];
        if (isset($_POST['premail']))
          $v->premail=$_POST['premail'];
        $v->dob=$_POST['dob'];
        if(strlen($v->dob)!=0)
          if(!checkValidDate($v->dob)) 
            $errs[] = array('dob_err',"Valid date in form MM/DD/YYYY ");

        $v->email=$_POST['email'];
        if((strlen($v->email)==0) || !checkEmail($v->email)) 
          $errs[] = array('email_err',"Valid email address required");

        $v->note =$_POST['note'];
        if (count($errs)==0) {
            $narg= "$v->name|$v->dob|$v->email|$v->note|$v->prname|$v->premail||$svcnum";
            // alright, its good to go, pick an appiance and deal with the rest of it there
            $narg = base64_encode($narg);
            header ("Location: roireq.php?n=$narg");
            die("Location: $modurl?n=$narg");
        }
    } // end handle post
}

if(count($errs)!=0) {
  for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];
}

// provide some defaults if the user happens to be logged on
$me = testif_logged_in();
if ($me!==false)
{
    list ($accid,$fn,$ln,$email,$idp,$mc,$auth)= $me;
    if ($v->name =='') $v->name = $fn.' '.$ln;
    if ($v->email == '') $v->email = $email;
}

$services = $db->query("select * from modservices where accid=? order by svcnum",array($id));

$content = template('provider.tpl.php')->set("id",$id)
  ->set("svcnum",$svcnum)
  ->set("v",$v)
  ->set("services",$services)
  ->fetch();

echo $header.$content.$footer;
?>
