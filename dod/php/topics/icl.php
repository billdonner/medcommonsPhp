<?php
//starts here

require_once "tlib.inc.php";
$p=testif_logged_in(); 
if ($p===false) {header ("Location: iclinfo.php"); exit;}
list($accid,$fn,$ln,$email,$idp,$cookie) =$p;
// this actually clones the page and plunks the user into the editor
$topic = $_REQUEST['topic'];
$accaccid = $_REQUEST['accid'];
$name = $topic;
$goto = "icledit.php";

$identity = (" medcommons user $accid $email");
//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database
// write to the database
//put a ;timestamp as part of the name
$pos = strpos($topic,';');
if ($pos!==false) $name = substr($topic,0,$pos).';'.time(); else $name = $topic.';'.time();
$topic = addslashes($topic);
$name = addslashes($name);

$query = "select * from clonedpages where name='$topic' and accid='$accaccid'";
$result = mysql_query($query) or die ($query.'icl cant find topic page '.mysql_error());
$r = mysql_fetch_object($result);
if ($r===false)
{header ("Location: iclinfo.php?err=cantfindtopicpage"); exit;}
$share = 1; $robots = 1; $clone = 1;
$ilinks = $r->ilinks; $xlinks=$r->xlinks; $phrlinks = $r->phrlinks;
$insert = "INSERT INTO clonedpages set name='$name', ilinks='$ilinks'
			, xlinks='$xlinks' , phrlinks = '$phrlinks', tags='".$r->tags."', keywords='".$r->keywords."', roottopic='".$r->roottopic.
			"', accid='$accid', shared='$share', robots='$robots', clone='$clone'";
			mysql_query($insert) or die("cant $insert ".mysql_error());

			$id = mysql_insert_id(); // get this id to stuff into the mail

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
			"We thought you'd like to know that $accid:$topic was cloned by $identity. The
new page can be viewed here: $gg ",
			$extraheaders);

			if (!$mailstat) echo "Could not send mail;";

			// figure out where to go based on id
			// redirect back to the user
			$location = "$goto?&pageid=$id";
			header("Location: $location");
			echo "Redirecting to $location";

?>

