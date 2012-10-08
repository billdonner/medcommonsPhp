<?php
require 'healthbook.inc.php';
$GLOBALS['hbmlactiontarget']="hbmlposted.php";
// store fbml code, making it safe and useful prior to storage

function stripcode($code,$pat,$rep)
{	function stripcodeint($code,$term,$pat,$rep)
	{	$obuf = '';
		$len = strlen($code); $patlen = strlen($pat); $pos=0;
		while ($pos<$len)
		{
			//echo "<br/>stripcodeint: $pos $len";
			$pos1 = strpos($code,$pat,$pos);
			if ($pos1===false) break;
			$pos2 = strpos($code,$term,$pos1+$patlen+1);
			if ($pos2 === false) break;
			$oldlen = $pos2-$pos1+1;
                                    //echo "<br/>stripcodeint: pos1 $pos1 pos2 $pos2";
			$obuf .= substr($code,$pos,$pos1-$pos)."action='$rep'";// add code up to and including this point
			$pos = $pos2+1;
		}
		$obuf .= substr ($code,$pos); // add whatever is left
		//echo "<br/>stripcodeint: obuf is ".strlen($obuf);
		return $obuf;
	}
	$pat1 = $pat."'"; // single quote
	$pat2 = $pat.'"'; // double quote
	$code= stripcodeint($code,"'",$pat1,'--**--**');//hack these so they dont get replaced again
	$code= stripcodeint($code,'"',$pat2,'--**--**');
	$code = str_replace('--**--**',$rep,$code);
	return $code;
}
//**start here
connect_db();
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
$dash=hurl_dashboard($user,'plug-ins');
if (isset($_REQUEST['steptwo']))
{
	// massage the code
	//
	//  1) change the action targets, whatever they are, to our own code
	$code = $_REQUEST['code'];
	$code = stripcode($code,'action=',$GLOBALS['hbmlactiontarget']);
	$code = mysql_escape_string($code);

	$description = mysql_escape_string($_REQUEST['description']);
	$q = "REPLACE INTO fbmlcode  set code='$code', description='$description',authorfbid='$user'";
	mysql_query($q) or die("Cant $q ".mysql_error());
	$ind = mysql_insert_id();
	//logHBEvent($user,'hbml',"stored hbml code $description - authored by $user in slot $ind");
	
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:success>
    <fb:message>Your HBML Was Stored</fb:message> 
      <p>You can your code by clicking  <a href='hbmlexec.php?i=$ind' >here</a></p>
  </fb:success>
</fb:fbml>
XXX;
}
else {
$samples = $GLOBALS['base_url'].'hbmlsamples.html';
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:explanation>
    <fb:message>HBML Editor <a class='tinylink' href=hbmlexec.php>hbml directory</a></fb:message> 
    <p>You design HBML Forms to update your personal health record <a target='_new' class=tinylink href='$samples'>view samples</a></p>
      <p>Type or paste your HBML Form in here. HBML is safety and privacy enabled FBML with MedCommons Healthbook enhancements. The form is inspected and re-written for safety. You will be given a link that can be utilitzed for testing. 
      </p>     
    <fb:editor action="hbmledit.php" labelwidth="100">
     <input type=hidden name=steptwo value=steptwo />
       <fb:editor-custom label="description">
          <input type=text name=description />
       </fb:editor-custom>
         <fb:editor-custom label="HBML">
          <textarea rows=20 cols=160 name=code></textarea>
       </fb:editor-custom>
     <fb:editor-buttonset>
          <fb:editor-button value="Store HBML"/>
     </fb:editor-buttonset>
 </fb:editor>
       <p>More coming later</p>  
  </fb:explanation>
</fb:fbml>
XXX;
}
echo $markup;
?>