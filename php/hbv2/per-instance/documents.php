<?php

require 'healthbook.inc.php';
/*
      <fb:tab_item href='documents.php?o=f' title='fax' />
      <fb:tab_item href='documents.php?o=i' title='img' />
      <fb:tab_item href='documents.php?o=c'  title='ccr' />
      <fb:tab_item href='documents.php?o=d' title='dicom' />
      
      */

function content_dashboard ($user, $kind)
{
	$top = dashboard($user);
	$bottom = <<<XXX
<fb:tabs>
      <fb:tab_item href='documents.php' title='documents' />
 </fb:tabs>
XXX;
	$needle = "title='$kind'";
	$ln = strlen($needle);
	$pos = strpos ($bottom,$needle);
	if ($pos!==false)
	{  // add selected item if we have a match
		$bottom = substr($bottom,0,$pos)." selected='true' ".
		substr ($bottom, $pos);
	}
	return $top.$bottom;
}
function adddicom($me,$user,$hurl){
	$dash = content_dashboard($me,'dicom');
	$appname = $GLOBALS['healthbook_application_name'];
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Add DICOM</fb:title>
$dash
  <fb:explanation>
    <fb:message>Add DICOM Radiology to <fb:name uid='$user' possessive=true linked=false useyou=true /> Account</fb:message>

      <p>put dicom in <fb:name uid='$user' possessive=true linked=false useyou=true/> $appname  Account via upload from a CD</p>
      <p>Diagnostic imaging support (DICOM) is managed under our FDA registration. Please refer to the Settings page associated with your HealthURL account for instructions on installing a free DICOM connector on your computer</p>
       <fb:editor action="?add=image" labelwidth="100">
   
     <fb:editor-custom label="spec of CD">
      <input type="text" name="uploadedFile" size="60"/>
</fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Import DICOM" size="60"/>
     </fb:editor-buttonset>
</fb:editor>
 

  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
}
function addcontent($me,$user, $u){
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = content_dashboard($me,'add document');
  $appliance = $u->getTargetUser()->appliance;
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Add Typed Documents</fb:title>
$dash
  <img src='$appliance/acct/gwauth.php'/>
  <fb:explanation>
    <fb:message>Add Typed Documents  to <fb:name uid='$user' possessive=true linked=false useyou=true /> Account</fb:message>
    Add content to your $appname account by filling out the form below.
    <form name="uploadForm" id="uploadForm" action="$u->gw/AccountDocument.action" target="uploadFrame" enctype="multipart/form-data" method="post" style="font-weight: normal; font-size: 10px;">
     <table class="editorkit" border="0" cellspacing="0" style="width:425px"><tr class="width_setter"><th style="width:75px"></th><td></td></tr>
      <input type="hidden" name="add" value="">
      <input type="hidden" name="storageId" value="$u->targetmcid">
      <input type="hidden" name="returnUrl" value="">
        <fb:editor-custom label="Select a Document Type">
          <select name="documentType">
            <option value="LIVINGWILL">Living Will</option>
            <option value="DURABLEPOA">Durable Power of Attorney</option>
            <option value="DNR">Do Not Resuscitate Instructions</option>
            <option value="PATPHOTO">Patient Photo</option>
          </select>
        </fb:editor-custom>
        <fb:editor-custom label="Select a File"><input type="file" name="uploadedFile" style='width:330px;'></fb:editor-custom>
        <fb:editor-buttonset>
          <fb:editor-button id='uploadButton' value="Upload"/>
        </fb:editor-buttonset>
        <fb:editor-custom><span id='result'></span><a style='display: none; padding-left: 30px;' href='#' id='uploadAgain'>Upload Another</a></fb:editor-custom>
      </table>
    </form>
    <fb:iframe id='uframe' name='uploadFrame' src='$u->gw/blank.html' style='display: none;'/>
  </fb:explanation>
  <div id='uploadWindowContainer' style='display:none;'>
  </div>
  <script>
  //<!--
    document.getElementById('uploadForm').addEventListener('submit',function() { 
      document.getElementById('result').setTextValue('Sending ....');
      document.getElementById('uploadButton').setStyle('display','none');
      document.getElementById('uframe').addEventListener('load',function() {
         document.getElementById('result').setTextValue('Done.');
         document.getElementById('uploadAgain').setStyle('display','inline');
      });
    });
    document.getElementById('uploadAgain').addEventListener('click',function() { 
      this.setStyle('display','none');
      document.getElementById('uploadButton').setStyle('display','inline');
      document.getElementById('result').setTextValue('');
      return false;
    });
  //-->
  </script>
</fb:fbml>
XXX;

	// <form action="$gw/AccountDocument.action"  enctype="multipart/form-data" method="post" style="font-weight: normal; font-size: 10px;">
	return $markup;
}
function addccr($me, $user,$hurl){
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = content_dashboard($me,'ccr');
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation><fb:title>Add CCR</fb:title>
    <fb:message>Add a CCR to <fb:name uid='$user' possessive=true linked=false useyou=true /> Account</fb:message>

      <p>put a CCR in your $appname via upload</p>
       <fb:editor action="?add=iccr" labelwidth="100">
   
     <fb:editor-custom label="spec of CCR">
      <input type="text" name="uploadedFile" size="60"/>
</fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="upload CCR" size="60"/>
     </fb:editor-buttonset>
</fb:editor>
 
      <p>Alternatively, CCRs can be sent as attachments to your $appname email address fb_$user@medcommons.net</p>  
  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
}
function addfax($me,$user,$hurl){
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = content_dashboard($me,'fax');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Add Fax</fb:title>
$dash
  <fb:explanation>
    <fb:message>Add Fax to <fb:name uid='$user' possessive=true linked=false useyou=true /> Account</fb:message>
   
      <p>You Can Empower Anyone To Send Faxes to Your $appname Account
      <ul>
      <li>Print a Custom Fax Cover Sheet and Consent Form</li>
      <li>Sign the form and distribute to your healthcare providers</li>
      <li>Have your provider fax your information to the toll free number on the cover sheet
      </ul></p>
  <fb:editor action="?cover" labelwidth="100">
     <fb:editor-buttonset>
          <fb:editor-button value="Print Cover Sheet" size="60"/>
     </fb:editor-buttonset>
</fb:editor>

  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
}
function addimage($me,$user,$hurl){
	$appname = $GLOBALS['healthbook_application_name'];
	$dash = content_dashboard($me,'img');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Add Images</fb:title>
$dash
  <fb:explanation>
    <fb:message>Add Images to <fb:name uid='$user' possessive=true  linked=false useyou=true /> Account</fb:message>
      <p>Add a JPG, JPEG, GIF, or PNG Image File<p>
   
       <fb:editor action="?add=image" labelwidth="100">
   
     <fb:editor-custom label="pdf file">
      <input type="text" name="uploadedFile" size="60"/>
</fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Import Image" size="60"/>
     </fb:editor-buttonset>
</fb:editor>
      <p>Alternatively, Image files can be sent as attachments to your $appname email address fb_$user@medcommons.net</p>  
  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
}
function addpdf($me,$user,$hurl){
	$appname = $GLOBALS['healthbook_application_name'];
	
	$dash = content_dashboard($me,'pdf');
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Add PDF</fb:title>
$dash
  <fb:explanation>
    <fb:message>Add PDF Content to <fb:name uid='$user' possessive=true  linked=false useyou=true /> Account</fb:message>

      <p>put a PDF file in your MedCommons Account</p>
       <fb:editor action="?add=pdf" labelwidth="100">
   
     <fb:editor-custom label="pdf file">
      <input type="text" name="uploadedFile" size="60"/>
</fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Import PDF" size="60"/>
     </fb:editor-buttonset>
</fb:editor>

      <p>Alternatively, PDFs can be sent as attachments to your $appname email address fb_$user@medcommons.net</p>  
  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
}
function acoverview($me,$user,$u,$t){

	if(($u->mcid==0)&&($u->targetmcid==0))
	{
		$dash = dashboard($me,false);
		$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Documents</fb:title>
$dash
  <fb:explanation>
    <fb:message><fb:name uid='$user' useyou='false' /> -- has no MedCommons Account to store Documents</fb:name></fb:message>
</fb:explanation>
</fb:fbml>
XXX;
		return $markup;
	}

	$appname = $GLOBALS['healthbook_application_name'];
	$dash = hurl_dashboard($me,'documents'); // was content_dashboard ----- 
	$t = $u->getTargetUser();
	$src = $t->authorize($u->appliance."acct/accountDocuments.php?t=widget&accid=".$u->targetmcid,$u);
$markup = <<<XXX
<fb:fbml version='1.1'>
  $dash
  <img src='$u->appliance/acct/gwauth.php' />
  <fb:explanation>
    <fb:message>Documents in  {$t->getFirstName()} {$t->getLastName()}'s HealthURL</fb:message>
    Clicking a CCR will download the XML, other documents may open in their own application.
    <br/>
    <br/>
    <fb:iframe src='$src' smartsize='true' frameborder='0'/>
  </fb:fbml>
XXX;

return $markup;
}

//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
if (isset ($_REQUEST['o'])) $op = $_REQUEST['o']; else $op='';
$u = HealthBookUser::load($user);
$t = $u->getTargetUser(); // not sure what context should be here, discuss with simon
if ($t===false||$t->mcid===false) {
	// redirect back to indexphp
	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
}else{

//	list($mcid,$appliance,$gw, $tfbid, $tmcid) = fmcid($user);
//	if ($mcid===false) die ("Internal error, fb user $user has no mcid");
	switch ($op)
	{
		case 'c': {    $markup = addccr($user,$t->fbid,$t->mcid)  ;break;}
		case 't': {    $markup = addcontent($user,$t->fbid,$t)  ;break;}
		case 'd': {  $markup = adddicom($user,$t->fbid,$t->mcid)   ;break;}
		case 'f': {    $markup = addfax($user,$t->fbid,$t->mcid)  ;break;}
		case 'i': {    $markup = addimage($user,$t->fbid,$t->mcid)  ;break;}
		case 'p': {    $markup = addpdf($user,$t->fbid,$t->mcid)  ;break;}
		default : {  $markup =acoverview($user,$t->fbid,$u,$t) ;break;}
	
	}
}
echo $markup;
?>
