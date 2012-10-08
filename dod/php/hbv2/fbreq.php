<?php

// runs a batchy facebook interface, minimizes calls to mysql and facebook

require 'facebook.php';


final class BatchDBRequest
{
	private $fbid,
	$show = array (
	'personal'=>true,'appinfo'=>true,
	'careteam'=>false,'deepcareteam'=>false,
	'carewall'=>false,'caregiving'=>false,
	'deepcaregiving'=>false);

	public $personal = false, $appinfo = false; // point my object representing one db row

	public $careteam = array(), $caregiving = array(); // arrays of team members, not evertyhing will be filled in depending on deep or not
	public $carewall = array();
	public $caregivingwalls = array(); // array of arrays

	public $batchDBstatement = ''; // no batching until we are smarter

	private function db_careteam ($fbid)
	{
		$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid
			                where c.giverfbid = '$fbid' and f.mcid!='0'";
		$result = mysql_query($q) or die("cant  $q ".mysql_error());
		//while($r=mysql_fetch_object($result))$this->careteam[]=$r;
		while($this->careteam[]=mysql_fetch_object($result));

		mysql_free_result($result);
	}

	private function db_caregiving ($fbid)
	{
		$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid
							where c.giverfbid ='$fbid' and c.giverrole='4' and f.mcid!='0'";
		$result = mysql_query($q) or die("cant  $q ".mysql_error());
		//while($u=mysql_fetch_object($result)) $this->caregiving[]=$u;
		while($this->caregiving[]=mysql_fetch_object($result));
		mysql_free_result($result);

	}
	private function db_carewall ($fbid,$limit)
	{
		$wall = array ();
		$q = "select * from  carewalls where wallfbid = '$fbid' order by time desc limit $limit ";
		$result = mysql_query($q) or die("cant  $q ".mysql_error());
		while($u=mysql_fetch_object($result)) $wall[]=$u;
		mysql_free_result($result);
		return $wall;

	}
	private function db_deepcareteam($fbid)
	{ //finds the carewalls for all the caregivees of this user
		foreach ($this->caregiving as $cg) $this->caregivingwalls[] = db_carewall ($cg->fbid,3);
	}
	private function db_personal($fbid)
	{
		$q="SELECT *from fbtab where fbid='$user' ";
		$result = mysql_query($q) or die("cant  $q ".mysql_error());
		$u=mysql_fetch_object($result);
		if ($u) $this->personal = $u; else $this->personal=false;

	}

	function __construct($fbid, $sections=array()) {
		$this->fbid = $fbid;
		$me = $_SERVER['PHP_SELF'];
		$me = substr($me,0,strrpos($me,'/')+1);
		// ssadedin: some sloppy code creates urls with // instead of /
		// it would be nice to clean up the sloppy code, but for the sake
		// of convenience we simply coalesce the doubled slashes together here
		$me = str_replace("//", "/", $me);
		mysql_connect("mysql.internal", "medcommons") or die("facebook boostrap: error  connecting to database.");
		$db = "facebook";
		mysql_select_db($db) or die("can not connect to database $db");
		// do a request for the appinfo stuff and it had btter work
		$result = mysql_query("SELECT * FROM `fbapps` WHERE `key` = '$me' ") or die("$me in $db is not registered with medcommons as a healthbook application".mysql_error());
		$this->appinfo = mysql_fetch_object($result);
		if ($this->appinfo===false)  die("$me is not registered with medcommons as a healthbook application");

		foreach ($sections as $key=>$value) $this->show[$key] = $value;

		if ($show['personal']) db_personal($fbid);
		//if ($show['appinfo']) db_appinfo ($fbid);
		if ($show['carewall']) $this->carewall = db_carewall($fbid,5);
		if ($show['careteam']) db_careteam($fbid);
		if ($show['deepcareteam']) db_deepcareteam($fbid);
		if ($show['caregiving']) db_caregiving($fbid);
		if ($show['deepcaregiving']) db_deepcaregiving($fbid);//depends and deepcaregiving

	}

}
final class BatchFacebookRequest
{
	private $fbid,$dbstuff,
	$show = array ('personal'=>true,'appinfo'=>true,'careteam'=>false,'deepcareteam'=>false,
	'carewall'=>false,'caregiving'=>false,'deepcaregiving'=>false);

	public $personal = array(), $appinfo = array(); // property value pairs

	public $careteam = array(), $caregiving = array(); // arrays of team members, not evertyhing will be filled in depending on deep or not

	public $userinfo = array(); //the result of doing a query to get info on each userid in above liist

	function __construct($dbstuff,$sections=array()) {
		$this->fbid = $dbstuff->fbid;
		$this->dbstuff = $dbstuff;
		//everyone on any careteam or caregivee or self
		$userid_list = array_merge($dbstuff->careteam->fbid,$dbstuff->caregiving->fbid,array($fbid));
		$gids='';
		foreach ($userid_list as $uid){if ($gids!='') $gids .=','; $gids.=$uid;}
		// go do a get_userinfo on the reduced merged list
		$userinfo = $facebook->api_client->fql_query("SELECT uid,first_name,last_name,pic_small,sex,current_location
		                                            					FROM user Where uid in '$gids' ");
		for ($i=0; $i<count($userinfo); $i++)
		$this->userinfo[$userinfo [$i]['uid']] = $userinfo [$i];
		// more to come when topics are re-introduced
	}
}
function newdashboard ($db,$hurl)
{// only works if logged on and personal is already loaded
	$tfbid = $db->personal->targetfbid;
	//$my_viewing_friends = caregiving_list($user); if ($my_viewing_friends=='')
	$my_viewing_friends = "";
	$melink =($db->personal->mcid!=0&&($user!=$tfbid) )?"<a  href='ctviewas.php?fbid=$user' >myself</a> ":'';
	if ($tfbid!=0) $melink = "<a  href='ctviewas.php' >none</a> $melink";
	if ($tfbid==0) {
		$vlinks = '';
		$viewing = "<td width=80px>not viewing anyone's records</td><td  width='60px' class='mugshotrole5'>&nbsp;</td>";
	}
	else {
		$hurlimage = $db->appinfo->medcommons_images."/hurl.png";
		$viewing = " now viewing <fb:name possessive=false uid='$tfbid' useyou='false'/> <a target='_new' title='open healthURL on MedCommons' href='$hurl'>
		<img src=$hurlimage alt=hurl /></a>";
		$viewing = "<td width=80px>$viewing</td><td  width='60px' class='mugshotrole5'>&nbsp;<fb:profile-pic uid=$tfbid ></td>";
		$vlinks = '| <a href="healthurl.php">HealthURL</a>';
	}
	if ($tfbid==0) $color ='white'; else
	{
		if ($user!=$tfbid) $color="#EED8C4"; else $color="#B8D5F3";
	}
	$ulink = " <img src='http://static.ak.facebook.com/images/icons/friend.gif' /><fb:name uid=$user useyou=false/>";
	$xlink = "<img src='{$db->appinfo->hbappuser}' alt='missing usr' />";
	if ($db->appinfo->extgroupurl!='')
	$xlink = "<a href='".$$db->appinfo->extgroupurl."' >$xlink</a>";
	if ($db->appinfo->bigapp ) $topicslink = "<a href='topics.php'>topics</a> | "; else $topicslink = '';
	$markup = <<<XXX
$css<div style="$ffamily  background-color: $color "  >
<span style='float: left; display:inline;margin-top:10px;margin-left:7px;font-size:1.0em '>
   &nbsp;
   <fb:if-user-has-added-app><a href="home.php">home</a> $vlinks
 <fb:else>
       <a class=applink href='http://www.facebook.com/add.php?api_key={$db->appinfo->apikey}&app_ref=dash'
        >add {$db->appinfo->appname}</a> </fb:else>
</fb:if-user-has-added-app>  </span>
   <span style='float: right;display:inline; margin-top:5px;margin-right:13px;'>
	<span style='font-size:1.0em '>
   <fb:if-user-has-added-app><a href="ct.php?o=i">invite</a> | 
      <a href="index.php?privacy">settings</a> | </fb:if-user-has-added-app>
      <a href="http://www.facebook.com/apps/application.php?api_key={$db->appinfo->apikey}&app_ref=about">about</a> | 
           <a href="http://www.facebook.com/group.php?gid=10318079541">forum</a> | 
     <a href="help.php">help</a></span></span><br/><br/>
<table style="margin-left:7px; margin-right:7px; width:635px" ><tr><td align=left width='60px' class='mugshotrole6'>$xlink</td><td width='430px'>
    <span style="font-size:14px;color: black;" >{$db->appinfo->appname}</span><br/><i>{$db->appinfo->version}  
    by: {$db->appinfo->publisher}
   $ulink </i>    
    </td><td>$viewing</td></tr></table>
XXX;
	return $markup; //</div> was deliberaely removed, yes the html will be unbalanced, lets see
}
function newcareteam($db,$user)
{
	$counter=0;
	$my = ($user==$db->personal->targetfbid )?'My':"<fb:name linked=false possessive='true'  uid={$db->personal->targetfbid} ></fb:name>";
	$outstr =" <div class='mugshots'><fb:explanation>
          <fb:message>$my Care Team <a class=tinylink href='home.php?o=t'>more...</a></fb:message><table><tr>";

	foreach ($db->careteam as $r)
	{
		$mod = $counter -  floor($counter/11)*11;
		if ($mod==0 && $counter!=0)$outstr.="</tr><tr>";
		$outstr.="<td class='mugshotgiver' width=55px style='color: #3b5998'><fb:profile-pic uid=$r->giverfbid /> <fb:name linked=false uid=$r->giverfbid /></td>";
		$counter++;
	}
	if ($counter==0) return '';
	else $outstr ="$outstr</tr></table>";
	$outstr .='</div>';

	return $outstr;
}

function  newcaregiving($db, $user)
{
	$my = ($user==$db->personal->targetfbid )?'My':"<fb:name linked=false possessive='true'  uid={$db->personal->targetfbid} ></fb:name>";
	$outstr =" <div class='mugshots'><fb:explanation>
    <fb:message>$my Care Giving <a class=tinylink href='home.php?o=g'>more...</a></fb:message><table><tr>";
	$counter = 0;
	foreach ($db->caregiving as $r)
	{
		$mod = $counter -  floor($counter/11)*11;
		if ($mod==0 && $counter!=0)$outstr.="</tr><tr>";
		$outstr.="<td class='mugshotgiver' width=55px style='color: #3b5998' ><fb:profile-pic uid=$r->fbid /> <fb:name linked=false uid=$r->fbid /></td>";
		$counter++;
	}
	if ($counter==0) return '';
	$outstr ="$outstr</tr></table></fb:explanation>";
	$outstr .='</div>   ';
	return $outstr;
}
function  buildBatchyFBMLPage ($user,$fb,$sections)
{

	//build actually starts here
	$db = $fb->dbstuff; // we'll need this
	$fbmlout = "<fb:fbml version='1.1'><fb:title>$title</fb:title>".mugshot_css();
	$u = HealthBookUser::load($user);
	// ssadedin: note: must get hurl for target user, not current user
	$hurl = $u->t_hurl();
	$fbmlout .= newdashboard($db, $hurl);
	//if (isset($sections['personal'])&&$sections['personal']) $fbmlout.= personal ($fb);
	if (isset($sections['careteam'])&&$sections['careteam']) $fbmlout.= newcareteam ($fb);
	if (isset($sections['deepcareteam'])&&$sections['deepcareteam']) $fbmlout.= newcareteam ($fb);
	if (isset($sections['caregiving'])&&$sections['caregiving']) $fbmlout.= newcaregiving ($fb);
	if (isset($sections['deepcaregiving'])&&$sections['deepcaregiving']) $fbmlout.= newcaregiving ($fb);
	return $fbmlout.'</fb:fbml>';

}
// simple program to test the library by painting the main page, no callbacks into here yet they go to old code
//**start here

$GLOBALS['facebook_config']['debug']=false; // and issue with facebook libraries

// get all the sections and details we need
$options = array('careteam'=>true,'caregiving'=>true)  ;
// from the 'facebook' mysql database
$db =  new BatchDBRequest($user,$options);
// now get setup with facebook, making just one call to get the facebook data

$facebook = new Facebook($db->appinfo->appapikey, $db->appinfo->appsecret);
$facebook->require_frame();
$user = $facebook->require_login();//db_loggedin_user(); //require_login();
// then from facebook, after we know what to ask for
$fb =  new BatchFacebookRequest($user,$db,$options);
// when we have all we need, build the page
$markup = buildBatchyFBMLPage ($user,$fb,$options);
// and put it up on the screen
echo $markup;
?>
