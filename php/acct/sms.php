<?
  require_once "utils.inc.php";
  require_once "mc.inc.php";
  require_once "template.inc.php";

  $ph = req('ph','');
  $mcid = req('mcid');

  if(!is_valid_mcid($mcid,true))
    throw new Exception("Invalid value for parameter 'mcid'");
    
  $t = template("base.tpl.php")
         ->set("phoneNumber",$ph)
         ->set("mcid",$mcid);

  echo  $t->set("title","Please Enter your Access Code")
          ->set("content",$t->fetch("sms.tpl.php"))
          ->fetch();
?>
