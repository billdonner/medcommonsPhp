<?php
require_once "tlib.inc.php";
require_once "mlib.inc.php";
$topic = urldecode($_REQUEST['topic']);
$refer = urldecode($_REQUEST['refer']); // where to go to at the end
$screenname = urldecode($_REQUEST['screenname']);
$other = urldecode($_REQUEST['other']);

$relation =''; //$_REQUEST['relation'];

$sites = '';//$_REQUEST['sites'];

$tif = testif_logged_in();
$accid = ''; $email='';
if ($tif==false)
{
	{header ("Location: iclinfo.php"); exit;} // not logged in
	
	exit;
}
// ok logged on
list($accid,$fn,$ln,$email,$idp,$cookie) = $tif; 
// look, terry's stuff is somehow broken and email is coming back as null, so for these new accounts, just let them in
//if ($email=='')$email = "missingemail@wayforward.net"; else
//if (strpos($email,'.edu')===false) 
	//{header ("Location: iclinfo.php?err=noedudomain"); exit;} // not in a good domain

$db = aconnect_db(); // connect to the right database
// write to the database
$topic = addslashes($topic);
$relation = addslashes($relation); // not used
$screenname = addslashes ($screenname); // screename
$sites = addslashes ($sites); // not used
$other = addslashes ($other); // this is extra info
$insert = "INSERT INTO editrequests set topic='$topic',refer='$refer'
,screenname='$screenname',
other='$other',accid='$accid',email='$email'";
mysql_query($insert) or die("Cant $insert".mysql_error());
$id = mysql_insert_id(); // get this id to stuff into the mail
$identity = ($tif===false?' an unidentified user':" medcommons user $accid $email");

$gg = $GLOBALS['Homepage_Url']."/interests/edittopicget.php?id=$id";

$srv = $_SERVER['SERVER_NAME'];
 $extraheaders =  
 		    "From: TopicsAgent@{$srv}\n" .
 			"Reply-To: cmo.medcommons.net\n".
			"bcc: billdonner@medcommons.net\n".
	        "User-Agent: MedCommons Mailer 1.0\n".
	        "MIME-Version: 1.0\n".
	        "Content-Type: text/plain\n";
// send an email
$mailstat = mail('cmo@medcommons.net',
"MedCommons - Request Granted to Assume Editorial Control of $topic from $identity",
"As a big wheel within MedCommons, particularly as relates to $topic, we thought you'd like to know there is a new request to assume editorial control of $refer. The request
my be viewed here: $gg ",
$extraheaders);

if (!$mailstat) echo "Could not send mail;";


// this actually clones the page and plunks the user into the editor

$name = $topic;
$clone = 1;//isset($_REQUEST['clone'])?1:0;
$share = 1;//isset($_REQUEST['share'])?1:0;
$robots = 1;//isset($_REQUEST['robots'])?1:0;

$identity = " medcommons user $accid $email";
//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database
// write to the database
$topic = addslashes($topic);
$name = addslashes($name);
// get original page
$pos=strpos($refer,'/interests/');
$goto=substr($refer,0,$pos).'/interests/icledit.php';
$url = 'http://www.nlm.nih.gov/medlineplus/'.substr($refer,$pos+strlen('/interests/'));

//echo "topic $topic name $name refer $refer pos $pos url $url"; exit;

$query = "select * from mcdirpages where url='$url'";
$result = mysql_query($query) or die ($query.'iclfin cant find topic page '.mysql_error());
$r = mysql_fetch_object($result);
if ($r===false)
 {header ("Location: iclinfo.php?err=cantfindtopicpage&topic=$topic"); exit;}
$ilinks=addslashes($r->ilinks);
$xlinks = addslashes($r->xlinks);
$testif = "SELECT * from clonedpages where name='$name'";  // pool them all together accid='$accid' and 
$result = mysql_query($testif) or die("Cant select $testif".mysql_error());
$dupe = (mysql_numrows($result)>=1);
if ($dupe) {
	// akready is a group, fix it up
	//$accid = '0000000000001111'; // hack for med res
	$r=mysql_fetch_object($result); 
	$id = $r->pageid;
    $thegroup = $r->thegroup;
    // see if we are already in the group by looking for !$ACCID! - THIS IS A REAL HACK
    if (strpos($thegroup,"!$accid!")!==false)
    {header ("Location: iclinfo.php?err=alreadyaneditor&topic=$topic"); exit;}
	$thegroup.= // simple for now
				"|$email!$accid!$screenname!$other";
	$update = "UPDATE clonedpages SET thegroup='$thegroup' where pageid='$id'";
    mysql_query($update) or die("cant $update ".mysql_error());
}
else

{
	// this is the first member
	$thegroup = // simple for now
				"$email!$accid!$screenname!$other";
	

	$insert = "INSERT INTO clonedpages set name='$name', ilinks='$ilinks'
			, xlinks='$xlinks' , tags='".$r->tags."', keywords='".$r->keywords.
			"', accid='0000000000001111', thegroup='$thegroup', shared='$share', robots='$robots', clone='$clone', roottopic='$topic'";
			mysql_query($insert) or die("cant $insert ".mysql_error());

			$id = mysql_insert_id(); // get this id to stuff into the mail
}
			$gg = "$goto?&pageid=$id";
			$srv = $_SERVER['SERVER_NAME'];
			$extraheaders =
			"From: SuggestionBox@{$srv}\n" .
			"Reply-To: cmo.medcommons.net\n".
			//			"bcc: billdonner@medcommons.net\n".
			"User-Agent: MedCommons Mailer 1.0\n".
			"MIME-Version: 1.0\n".
			"Content-Type: text/plain\n";
			// send an email
			$mailstat = mail('billdonner@gmail.com',//cmo@medcommons.net',
			"MedCommons - $topic cloned by $identity",
			"As topic editor for $topic, we thought you'd like to know that $refer was cloned by $identity. The
new page can be viewed here: $gg ",
			$extraheaders);

			if (!$mailstat) echo "Could not send mail;";

// figure out where to go based on id
// redirect back to the user
$location = "$goto?&pageid=$id";
header("Location: $location");
echo "Redirecting to $location";

?>



