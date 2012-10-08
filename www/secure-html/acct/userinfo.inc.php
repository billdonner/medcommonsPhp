<?php

define ('INFOLEVEL',1);
define ('USERLEVEL',0);


function emit($s)
{
	$GLOBALS['uinfo'].=$s;
}
function z($level,$prompt,$value,$dbfield,$db,$edit,$howset)
{
	if ($level<=$GLOBALS['glevel'])
	{
		//    table|field|accid|id
		$ed = ($edit?'edit':' ');
		if ($edit){
			if ($value=='')$value = '&nbsp;&nbsp;';
			$value = "<span id='$db|$dbfield|m|' class='editText'>$value</span>";
		}
		//	echo "db $db field $dbfield prompt $prompt value $value $ed<br>";
		emit ("<tr><td>$prompt</td><td class=inputfield>$value</td></tr>\r\n");
	}
}
function fu($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'users',false,$howset);
}
function eu($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'users',true,$howset);
}
function fa($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'addresses',false,$howset);
}
function ea($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'addresses',true,$howset);
}
function fc($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'ccdata',false,$howset);
}
function ec($level,$prompt,$value,$dbfield,$howset)
{
	z($level,$prompt,$value,$dbfield,'ccdata',true,$howset);
}


function userinfo($accid,$glevel)
{
	$GLOBALS['glevel']=$glevel;
	$GLOBALS['uinfo']=''; // someday I'll figure out how global variables work in php
	// load up everthing into objects
	$q = "SELECT * from users where mcid='$accid'";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	$u = mysql_fetch_object($result);
	mysql_free_result($result);
	$q = "SELECT * from addresses where mcid='$accid'";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	$a = mysql_fetch_object($result);
	$rc = mysql_numrows($result);
	mysql_free_result($result);
	if ($rc==0)
	{// add an address record
		$q="insert into addresses set mcid = '$accid'";
		mysql_query($q) or die ("Can not insert $q ".mysql_error());
		$q = "SELECT * from addresses where mcid='$accid'";
		$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
		$a = mysql_fetch_object($result);
	}




	/*
	$q = "SELECT * from ccdata where accid='$accid'";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	while (true){
	$c = mysql_fetch_object($result);
	if ($c===false) break;
	$cc[] = $c;
	}
	mysql_free_result($result);
	*/
	// now lay this into tables
	emit("<table class=trackertable>\r\n");

	fu(USERLEVEL,'mcid',$u->mcid,'mcid','registration (set by medcommons)');
	fu(USERLEVEL,'email',$u->email,'email','registration');
	fu(INFOLEVEL,'sha1',$u->sha1,'sha1','registration');
	fu(INFOLEVEL,'server id',$u->server_id,'server_id','registration');
	eu(INFOLEVEL,'member since',$u->since,'since','registration');
	eu(USERLEVEL,'first name',$u->first_name,'first_name','registration');
	eu(USERLEVEL,'middle name',$u->middle_name,'middle_name','registration');
	eu(USERLEVEL,'last name',$u->last_name,'last_name','registration');
	eu(USERLEVEL,'moble',$u->mobile,'mobile','registration');
	eu(INFOLEVEL,'smslogin',$u->smslogin,'smslogin','registration');
	fu(INFOLEVEL,'updatetime',$u->updatetime,'updatetime','each change');
	fu(INFOLEVEL,'ccrlog updatetime',$u->ccrlogupdatetime,'ccrlogupdatetime','each ccr change');
	eu(INFOLEVEL,'photo url',$u->photoUrl,'photoUrl','my preferences panel');
	eu(INFOLEVEL,'charge class',$u->chargeclass,'chargeclass','my preferences panel');
	fu(INFOLEVEL,'start page',$u->rolehack,'rolehack','my preferences panel');
	fu(INFOLEVEL,'affiliation group',$u->affiliationgroupid,'affiliationgroupid','my preferences panel');
	fu(INFOLEVEL,'start params',$u->startparams,'startparams','my preferences panel (implicit)');
	eu(INFOLEVEL,'stylesheet url',$u->stylesheetUrl,'stylesheetUrl','my preferences panel');
	eu(INFOLEVEL,'pics layout',$u->picslayout,'picslayout','my preferences panel');

	fa(INFOLEVEL,'address mcid',$a->mcid,'mcid','registration (set by medcommons)');
	ea(INFOLEVEL,'comment',$a->comment,'comment','registration');
	ea(USERLEVEL,'address line1',$a->address1,'address1','registration');
	ea(USERLEVEL,'address line2',$a->address2,'address2','registration');
	ea(USERLEVEL,'city',$a->city,'city','registration');
	ea(USERLEVEL,'state',$a->state,'state','registration');
	ea(USERLEVEL,'postcode',$a->postcode,'postcode','registration');
	ea(USERLEVEL,'country',$a->country,'country','registration');
	ea(USERLEVEL,'telephone',$a->telephone,'telephone','registration');
	ea(USERLEVEL,'date of birth',$a->DOB,'DOB','registration');
	ea(USERLEVEL,'age',$a->Age,'age','registration');
	ea(USERLEVEL,'sex',$a->Sex,'sex','registration');






	fc(INFOLEVEL,'credit cards',"no paymentcards on file for $accid ",' ','payment via cc');

	emit("</table>\r\n");

	return $GLOBALS['uinfo'];

}
?>