<?
require_once "mc.inc.php";
require_once "urls.inc.php";

global $Secure_Url;

/**
 * Patient List Atom Template
 *
 * Displays atom feed for worklist.  Needs to be initialized with data from rls.php
 */
header('Content-Type: application/atom+xml');
echo '<?xml version="1.0" encoding="utf-8"?>';

$url = $Secure_Url."/acct/patient_list_atom.php?accid=".$groupAccountId;

if(count($rows) > 0) 
    $updated = strftime('%Y-%m-%dT%H:%M:%SZ',$rows[0]['CreationDateTime']);
?>

<feed xmlns="http://www.w3.org/2005/Atom" xmlns:mc="http://medcommons.net/patientdata">
  <title>MedCommons Patient List Feed</title> 
  <link href="<?=$url?>"/>
<?if(isset($updated)):?> 
  <updated><?=$updated?></updated>
<?endif;?>
  <author> 
    <name><?=$practicename?></name>
  </author> 
  <id><?=$url?></id>
<?
  $now = time();
  $odd = false;
  $acctsUrl = gpath("Accounts_Url"); 
  foreach($rows as $l):
    $odd = (!$odd); // flip polarity
    //guid is a link
    $guid = $l['Guid'];
    $ct = $l['CreationDateTime'];
    
    $updated = strftime('%Y-%m-%dT%H:%M:%SZ',$ct);
    
    // <updated>2003-12-13T18:30:02Z</updated>
    
    if (($lasttime!=0)&&($ct > ($lasttime -100))){
      // still new
      $rowclass = ($odd?"oddnew":"evennew");
    }else {
      $rowclass = ($odd?"odd":"even");
    }
    if($l['ViewStatus']=='Hidden') {
      $hurlImg = 'hurl_bw.png';
      $rowclass .= " hiddenPatient";
      $hidden = true;
    }
    else {
      $hidden = false;
      $hurlImg = 'hurl.png';
    }

    $cc = $l['ConfirmationCode'];
    $rs = $l['RegistrySecret'];
    $purpose = htmlspecialchars($l['Purpose']);
    $xdob = $l['DOB'];
    $viewerurl = $l['ViewerURL']."&a=$accid&at=$auth"; // add our account id and auth token to viewer url
    $serverurl = $l['CXPServerURL'];
    $servervendor=$l['CXPServerVendor'];
    $comment = htmlspecialchars($l['Comment']);
    if($l['PatientIdentifier']) {
      $href = $Secure_Url."/".$l['PatientIdentifier'];
      if($l['couponstatus'] && ($l['couponstatus']=='issued'))
        $href.="?c=iv";
    }
    else
      $href = $viewerurl;

    $patientidentifier = $l['PatientIdentifier'];
    if ($patientidentifier=='') $patientidentifier = "-no patientid-";
    $patientidentifiersource = $l['PatientIdentifierSource'];

    $ts = isset($transferState[$patientidentifier]) ? $transferState[$patientidentifier] : false;

    if ($patientidentifiersource=='') $patientidentifiersource = "-no patientid source-";

    $patientName = htmlentities($l['PatientGivenName'],ENT_QUOTES).' '.htmlentities($l['PatientFamilyName'],ENT_QUOTES);

    $messages = "";
    if($ts) {
        $messageCount = 1;
        foreach($ts->messages as $m) {
            $messages .= "  $messageCount.    $m \r\n";
            $messageCount++;
        }
    }
?>

  <entry>
    <title><?=$patientName?></title>
    <link href="<?=$href?>"/>
    <id><?=$patientidentifier?></id>
    <updated><?=$updated?></updated>
    <summary><?=$purpose?></summary>
    <author>
        <uri><?=$href?></uri>
    </author>
<?if($l['order_status']):?>
    <mc:status><?=$l['order_status']?></mc:status>
    <mc:order-reference><?=$l['order_reference']?></mc:order-reference>
    <mc:guid><?=$l['Guid']?></mc:guid>
<?endif;?>
  </entry>
<?endforeach;?>
</feed>
