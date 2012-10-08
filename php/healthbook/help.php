<?php
// this is required of all facebook apps
require 'healthbook.inc.php';
function about_docs()
{

	$docs = <<<XXX
  <fb:explanation>
    <fb:message>Documents</fb:message>
    <div>
<blockquote style="font-size:8px;" >
A variety of different documents can be stored in your HealthURL. These documents are typically <i>fixed content</i> which means they can be signed and stored permanently without fear of tampering. Examples include a Do Not Recessetitate Order, a Living WIll.
</blockquote><br/>
<h4>Document Templates</h4><p>A Document Template is a prototype document already available online from HealthBook or one of the Topics. You can edit the document as necessary, sign it, and store it in your HealthURL or send it someone on or off Facebook</p>
<h4>Group Templates</h4><p>Group Templates are Document Templates that are managed and posted by a Facebook Group.  All members of the group are kept appraised of any changes to existing templates or new templates as they become available</p>
<h4>Attachment Types </h4><p>A variety of standards documents can be directly connected to the HealthURL, including Imaging from CT Scanners, MRIs, etc, plain PDF Files, as well as fax.
<h4>Activiy Log</h4><p>Displays all recent interactions in and around the HealthURL</p>
<h4>Consents</h4><p>Control precisely whom can access a private HealthURL</p>
</div>
</fb:explanation>
XXX;
	return $docs;
}
function about_hurls()
{

	$hurls = <<<XXX
  <fb:explanation>
    <fb:message>HealthURLs</fb:message>
    <div>
<blockquote style="font-size:8px;" >
HealthURLs are full-strength summary of one person"s clinical situation, over a lifetime from childhood immunizations to chronic disease management,
Independent of insurance companies, employers, bureaucrats, and not tethered to any single provider
</blockquote><br/>
<h4>Public HealthURLs</h4><p>Public HealthURLs are created on a limited set of MedCommons Appliances, notably on http://public.medcommons.net/ &nbsp; by definition all of these HealthURLs can be seen by anybody</p>
<h4>Private HeathURLS</h4><p>Private HealthURLs are accessible only by members of your Care Team </p>
<blockquote style="font-size:8px;" >
The following features require that you add the application and then go to the Settings page to set up additional functions<br/>
</blockquote><br/>
<h4>Activiy Log</h4><p>Displays all recent interactions in and around the HealthURL</p>
<h4>Consents</h4><p>Control precisely whom can access a private HealthURL</p>
</div>
</fb:explanation>
XXX;
	return $hurls;
}

function about_app()
{

	$aoo = <<<XXX
  <fb:explanation>
    <fb:message>Benefits of Adding  The HealthBook Application</fb:message>
    <div>
<blockquote style="font-size:8px;" >
There is an about link in the upper right hand corner which will get you to the official about page<br/>
This page tells you even more, now that you are here, as a motivation for getting you to add this application, if you haven't already
</blockquote><br/>
<h4>Become A CareGiver</h4><p>As a Care Giver, you are entrusted with your friend's medical information and must add the HealthBook application to support this</p>
<h4>Post Public HeathURLS</h4><p>You need to add the HealthBook application in order to publish new Public HealthURLs</p>
<h4>Get Notified When Something Happens</h4><p>If you have a favorite topic that has changed, or are a Care Giver, then you can be notified in your NewsFeed  whenever something changes
</p>
<blockquote style="font-size:8px;" >
The following features require that you add the application and then go to the Settings page to set up additional functions<br/>
</blockquote><br/>
<h4>Keep Your Own Long Term Health Records</h4><p>If you have added the application, you can optionally keep your own medical records</p>
<h4>Build a Care Team for Yourself or a Friend</h4><p>If you are keeping your own medical records, you can invite your trusted friends to your Care Team</p>
</div>
</fb:explanation>
XXX;

	return $aoo;
}

function about_topics()
{

	$topics = <<<XXX
  <fb:explanation>
    <fb:message>About Topics</fb:message>
    <div>
<blockquote style="font-size:8px;" >
The Topics System is primordially based on the categories developed for <a target='_new' href=http://medlineplus.gov/>MedlinePlus&#174;</a> a public resource for authoritative consumer health information<br/>
A future enhancement will allow for Facebook groups and Pages to sponsor new Topics
<br/>
</blockquote><br/>
<h4>Find Topics</h4><p>A variety of search strategies can be employed to find topics of interest, via first letter, part of a disease or condition, or from a drop-down list
</p>
<h4>Moderators</h4><p>A Moderator is an Officer of  a Facebook Group that wants to control postings to a Topic. Approval by MedCommons HealthBook administration is necessary. 
</p>
<h4>Posting Public HealthURLs to Topic</h4>	<p> You can always post any Public HealthURL to any topic, and it will be shown to you personally. To post something for public viewing, the Topic must be moderatedby a Group, and you must be an officer of that Group
</p>
<h4>Favorite Topics</h4>	<p> HealthBook encourages you to identify some Favorite Topics within Healthbook for automated notifications via your Facebook mini-feed of any changes to your favorite Topic Pages  and for ease of navigtion within HealthBook</p>
</p>
<h4>Healthbook Enabled Facebook Groups</h4>	<p>Any Facebook Group that has posted at least one Public HealthURL  to any topic is considered Healthbook Enabled and will be shown to Healthbook Members in the HealthBook Groups directory or via the HealthBook Search Box
</p>
</div>
  </fb:explanation>
XXX;

	return $topics;
}
function about_careteams()
{

	$careteam = <<<XXX
  <fb:explanation>
    <fb:message>Care Team Information</fb:message>
    <div>
<blockquote style="font-size:8px;" >
A Care Team is a subset of your friends who have agreed to share your personal healthcare records<br/>
Here are the actual  ways that you and your  Care Team friends can interact<br/>
</blockquote><br/>
<h4>Invite Friends to Join Your Team</h4><p>This uses the normal Facebook invitation scheme, and invites your friends directly on to your team
</p>
<h4> Your CareWall is Shared WIth Your Team</h4><p>The CareWall allows all members of a CareTeam to communicate about a friend's healthcare. A quick summary of the team carewall is available on your friend's profile page on facebook, and your friend's mini-feed is normally routed into your own incoming notifications stream
</p>
<h4>Your Health Records and Documents are shared by Your CareTeam</h4>	<p> Unless other arrangements are mad, your personal mdical records are shared with members of your Care Team. Your Consents are not shared
</p>
<h4>Your Favorite Topics are shared by Your CareTeam</h4>	<p>Other CareTeam members can be notified via their NewsFeeds of any changes in these Topics
</p>
<h4>Your Facebook Group relationships are shared with Your CareTeam</h4>	<p> Everyone on your Care Team can see what special groups you are associated with, so they may share the groups resources as part of your care
</p>
</div>
  </fb:explanation>
XXX;

	return $careteam;
}

function about_caregiver()
{
	$caregiver = <<<XXX
  <fb:explanation>
    <fb:message>Care Giver Information</fb:message>
    <div>
<blockquote style="font-size:8px;" >
A Care Giver helps her friends to care for themselves<br>
Here are the actual things a Care Giver can do for a friend who has invited her to her Care Team<br/>
</blockquote><br/>
<h4>Groups Affiliation</h4>	<p>You can view, but not change the  Facebook HealthBook Groups your friend is associated with.
</p>
<h4>Favorite Topics</h4>	<p>You can view, but not change, your friend's Favorites.
</p>
<h4>Posted Public HealthURLs</h4>	<p>You, along with the general public, can view, not change, your friend's Posted Pubic HealthURLs.
</p>
<h4>Private HealthURLs</h4>	<p>You can view and add information to your firend's private HealthURL
</p>
<h4>Care Team Membership</h4>	<p>You can see who is on a friend's Care Team, and communicate with other team members.
</p>
<h4>Care Giving Relationships</h4>	<p>You can not see who your friend may be giving care to.
</p>
<h4>Documents</h4>	<p>You can view, but not change, your friend's documents, unless specifically restricted by special Consents</p>
</p>
<h4>Facebook Profile Page</h4>	<p>When you view your friend's profile page, you are able to see his current health status  under the MedCommons HealthBook panel</p>
</p>
<h4>Consents</h4>	<p>You can not view or edit your friend's Consents.
</p>
<h4>Backups</h4>	<p>You can not make a personal backup of your friend's private HealthURLs
</p>
</div>
  </fb:explanation>
XXX;

	return $caregiver;
}
function glossary()
{
	$glossary = <<<XXX
  <fb:explanation>
    <fb:message>HealthBook Glossary</fb:message>
    <div>
<blockquote style="font-size:8px;" >
R: What are you playing at?<br>
G: Words, words.  They're all we have to go on.
<div id="attrib">&mdash;Rosencrantz and Guildenstern Are Dead</div>
</blockquote><br/>
<h4>Activity Log</h4>	<p>a per user log of all interactions with the user's health records </p>
<h4>Care Giver</h4>	<p>an individual who is helping one or more of his friends by sharing their health records</p>
<h4>Care Team</h4>	<p>a group of Facebook Friends who have agreed to help the user with her health </p>
<h4>Care Wall</h4>	<p>a private Facebook Wall shared between members of a Care Team</p>
<h4>CCR</h4>	<p>a document standard for storing patient longterm medical records, used by MedCommons, Google Health, and others</p>
<h4>Consents</h4>	<p>permit the owner of a HealthURL to grant access to the health records and documents associated with her account in a specific and flexible way extending beyond Facebook and into the Healthcare provider community</p>
<h4>DICOM</h4>	<p>medical imaging is normally stored and transported as dicom files, whether over the Internet or via CD/DVD</p>
<h4>Documents</h4>	<p>a storage repostitory associated with the user's healthURL </p>
<h4>Facebook Friends</h4>	<p>provide the pool of individuals who might join a Care Team</p>
<h4>Facebook Groups</h4> <p>are used to organize like minded users around diseases or other interests, used to control moderating topics groups</p>
<h4>Facebook Profile</h4>	<p>a user page on Facebook which has a smalll Healthbook user info window</p>
<h4>Favorite Topics </h4> <p>	the Topics a user has selected as being indicative of the conditions he is interested in studying </p>
<h4>Fax In</h4>	<p>a toll free number is provided to allow you and your Care Team to send faxes into your HealthURL </p>

<h4>Health URL</h4>	<p>a personal health records web service maintained by a MedCommons Appliance, and backed up at Amazon</p>
<h4>Imaging</h4>	<p>jpegs and other special document types are stored</p>
<h4>MedlinePlus</h4><p>the service run by NIH providing categorized, editorially controlled healthcare information for patients and practitioners</p>
<h4>Personal Documents</h4>	<p>pdfs which represent DNRs, wills, etc, associated with a particular HealthURL</p>
<h4>Plug-Ins</h4>	<p>a collection of built in (or dynamically deliverd) forms for interactive input and display of basic healthcare data</p>
<h4>Private HealthURL</h4>	<p>a lifetime archive of health records for one user</p>
<h4>Public HealthURL</h4>	<p>an archive of publically accessible health records for one user</p>
<h4>Topic</h4>	<p>one of HealthBook's current 748 disease and condition related medical categories, as determined by the NLM</p>
<h4>Topic Moderator</h4>	<p>an officer of a Facebook Group that has been authorized to manage the postings for a parituclar topic of which there may be several moderators</p>
</div>
</fb:explanation>
XXX;

	return $glossary;
}
function internals($user,$mcid){
	$appname = $GLOBALS['healthbook_application_name'];
	$glossary = glossary();
	$protected = <<<XXX
	<fb:if-is-group-member gid="5946983684" uid="$user" >
      <p>Facebook user $user, MedCommons id is $mcid.</p>
       <p>Scan for topic and group counts<a href=scanner.php  >here</a>
             <p>Obsolete groups entry point <a href=groups.php  >here</a>

   <p>See who has healthbook accounts right now
      <a href='internals.php'>here</a>
      </p>
      
  </fb:if-is-group-member> 
XXX;
	if (!isset($GLOBALS['newfeatures'])) $protected='';

	$app = $GLOBALS['healthbook_application_name'];
	$markup = <<<XXX

  <fb:explanation>
    <fb:message>$app Help</fb:message>
    <a href=internals.php?fbid=$user >HealthBook Log</a>
$protected
  <p>Thanks for your patience</p>  
    </fb:explanation>

XXX;

	return $markup;
}
function help_dashboard ($user, $kind)
{
	$top = dashboard($user);
	$bottom = <<<XXX
<fb:tabs>
 <fb:tab_item href='help.php?o=g' title='Glossary' />
      <fb:tab_item href='help.php?o=x' title='Topics' />
      <fb:tab_item href='help.php?o=t' title='Care Teams' />
      <fb:tab_item href='help.php?o=j' title='Care Giving' />
       <fb:tab_item href='help.php?o=n' title='HealthURLs' />
       <fb:tab_item href='help.php?o=d' title='Documents' />
        <fb:tab_item href='help.php?o=r' title='HealthBook App' />
        <fb:if-is-group-member gid="5946983684" uid="$user" >
                <fb:tab_item href='help.php?o=i' title='Internals' />
                </fb:if-is-group-member  >
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
//**    start here
if (!isset($_REQUEST['o'])) $op=''; else $op=$_REQUEST['o'];
connect_db();
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->get_loggedin_user(); //require_login();
list($mcid,$appliance) = fmcid($user);  // allow even if not logged in to medcommons
switch ($op)
{


	case 'd': { $menu='Documents';  $title="Help - Document Information";  $markup = about_docs()  ;break;}
	case 'n': { $menu='HealthURLs';  $title="Help - HealthURL Information";  $markup = about_hurls()  ;break;}
	case 'r': { $menu='HealthBook App';  $title="Help - HealthBook Application";  $markup = about_app()  ;break;}
	case 'x': { $menu='Topics';  $title="Help - Topics Information";  $markup = about_topics()  ;break;}
	case 'g': { $menu='Glossary';  $title="Help - Glossary";  $markup = glossary()  ;break;}
	case 't': { $menu='Care Teams';  $title="Help - Care Team Information";  $markup = about_careteams();break;}
	case 'j': { $menu='Care Givers';  $title="Help - Care Giver Information";  $markup = about_caregiver();break;}
	case 'i': { $menu='Internals';     $title="Help - Internals";$markup = internals ($user,$mcid); ;break;}
	default :  { $menu='Glossary';  $title="Help - Glossary";  $markup = glossary()  ;break;}
}
$dash = help_dashboard($user,$menu);

$out =<<<XXX
<fb:fbml version='1.1'><fb:title>$title</fb:title>$dash   
$markup</fb:fbml>
XXX;
echo $out;
?>