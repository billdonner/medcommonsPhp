<?php
// record suggestions in the db and give adrian an email
require_once "alib.inc.php";
$topic = $_REQUEST['topic'];
$refer = $_REQUEST['refer']; // where to go to at the end
$addresources = $_REQUEST['addresources'];
$remresources = $_REQUEST['remresources'];
$graphics = $_REQUEST['graphics'];
$other = $_REQUEST['other'];
$tif = testif_logged_in();
$accid = ''; $email='';
if ($tif!==false) list($accid,$fn,$ln,$email,$idp,$cookie) = $tif; // does not return if not lo
//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database
// write to the database
$topic = addslashes($topic);
$addresources = addslashes($addresources);
$remresouces = addslashes ($remresources);
$graphics = addslashes ($graphics);
$other = addslashes ($other);
$insert = "INSERT INTO suggestions set topic='$topic',refer='$refer',addresources='$addresources',remresources='$remresources',
graphics='$graphics',other='$other',accid='$accid',email='$email'";
mysql_query($insert) or die("Cant $insert".mysql_error());
$id = mysql_insert_id(); // get this id to stuff into the mail
$identity = ($tif===false?' an unidentified user':" medcommons user $accid $email");
$gg = $GLOBALS['Accounts_Url']."impget.php?id=$id";
$srv = $_SERVER['SERVER_NAME'];
 $extraheaders =  
 		    "From: SuggestionBox@{$srv}\n" .
 			"Reply-To: cmo.medcommons.net\n".
			"bcc: billdonner@medcommons.net\n".
	        "User-Agent: MedCommons Mailer 1.0\n".
	        "MIME-Version: 1.0\n".
	        "Content-Type: text/plain\n";
// send an email
$mailstat = mail('cmo@medcommons.net',
"MedCommons - $topic Suggestion Received from $identity",
"As topic editor for $topic, we thought you'd like to know there is a new suggestion regarding page $refer. The suggeston
my be viewed here: $gg ",
$extraheaders);

if (!$mailstat) echo "Could not send mail;";

// redirect back to the user
header("Location: $refer");
echo "Redirecting to $refer";

?>

