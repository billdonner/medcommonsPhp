<?
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "template.inc.php";

$info = get_validated_account_info();
if(!$info) 
    throw new Exception("You must be logged in to access this page.");
    
if(!$info->practice) 
    throw new Exception("You must be a member of a group to access this page.");

$gwUrl = allocate_gateway($info->accid);

$startURL = "$gwUrl/ddl/pollgroup?auth=".$info->auth;

$content ="<h2>DDL Poller</h2>
  <p>The DDL poller is a specially configured DDL service which automatically downloads
     new DICOM linked to patient orders as it appears in your patient list.</p>
 <br/>
  <p>
  <a href='$startURL'>Click Here to Start DDL Poller</a>
  </p>
";
  
echo template("base.tpl.php")->set("title","DDL Poller")->set("content",$content)->fetch();