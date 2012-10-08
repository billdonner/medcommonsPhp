<?php
function debug($s)
{
	if (isset($GLOBALS['debug'])) echo $s."\r\n";
}

function img($type,$src,$url,$name){
	$url=trim($url); $name =trim($name);
	//	debug("Img url $url name $name type $type src $src");
	return <<<XXX
	<a target="_new" href="$url" title="$url">
	<img src="mpng.php?t=$type&url=$url&name=$name" alt="$name"
XXX;
}
function img2($type,$src,$url,$name){
	$url=trim($url); $name =trim($name);
	//	debug("Img2 url $url name $name type $type src $src");
	return <<<XXX
	<a target="_new" href="$url" title="$url">
	<img src="$src"
XXX;
}
function inject ($kind,$f,$src, $pref,$suf,$images, $template)
{
	//	debug ("inject f $f src $src");
	$subs = "";
	$imgnum = 0;
	$imgcnt = count($images);
	$pos=0; $tlen=strlen($template);
	while (true)
	{
		// this is poorly coded
		$npos = strpos($template,$pref,$pos); // see if there are more
		if ($npos===false) break;
		$mpos = strpos($template,$suf,$npos);
		if ($mpos===false) break;
		$fpos = strpos ($template,"/>",$mpos);
		if ($fpos ===false) break;
		if($imgnum>=$imgcnt) break;

		// alright, we have to substiture, pull one off the list
		list($type,$url,$name,
		$iconlinks[0][0],$iconlinks[0][1],$iconlinks[0][2],
		$iconlinks[1][0],$iconlinks[1][1],$iconlinks[1][2],
		$iconlinks[2][0],$iconlinks[2][1],$iconlinks[2][2])
		=$images[$imgnum++];

		// layout the various icon links as a small list

		if (($kind!='SRV'))
		{ // assume the dim image
			if ($kind=='USR')
			$src = "Images/User 3 32 d p.png"; else
			$src = "Images/Parcel Dispatch 32 d p.png";
			$url = "nav.html";
			if (($type=='db'))
			{   // choose the right icon for the target url
				if ($kind=='USR') $ix = 0;
				else if ($kind=='GRP') $ix = 1;
				$url = $iconlinks[$ix][1];
				$src = $iconlinks[$ix][2];
			}
		}
		$subs = call_user_func($f,$type,$src,$url,$name); // this is the working url
		$template = substr($template,0,$npos).
		$subs.
		substr($template,$mpos+strlen($suf),$fpos-$mpos-strlen($suf)).
		"/></a>".
		$iconspan.
		substr($template,$fpos+6);// this part could be improved
	}
	// fix up the header
	$npos = strpos($template,"</head>"); // see if there are more

	if ($npos!==false)
	{
		$patch = "<meta http-equiv='refresh' content='60'>";
		$template = substr($template,0,$npos).$patch.substr($template,$npos);
	}
	return $template;
}

// start here
//$GLOBALS['debug']=true;

$config = $_REQUEST["f"];
$xmldata = simplexml_load_file($config);
// get the template
$template = trim($xmldata->Template);
$pagename = trim($xmldata->PageName);
$instanceid = trim($xmldata->InstanceId);

//get and save bodies of all attachments
$images = array(); // layout our structure
$servers = $xmldata->Servers;
foreach ($servers->Server as $server)
{
	$iconcount = 0;
	$iconlinks = array();
	// if any additional links specified, include them all
	$xlinks = $server->Xlinks;
	if (count($xlinks)>0)
	foreach ($xlinks->Xlink as $xlink)
	{
		$url =  trim($xlink->PopUpUrl);
		if (strlen($url)>5)
		{

			$icon = trim($xlink->IconImage);
			$label= trim($xlink->Label);

			//				debug ("Iconlinks url $url icon $icon label $label");

		} else
		{$url=false; $icon=false; $label="nolabel";}
		$iconlinks [$iconcount][0] = $url;
		$iconlinks [$iconcount][1] = $icon;
		$iconlinks [$iconcount++][2] = $label;
	}
	$images[]= array(strtolower($server->Type),$server->Poll,$server->Name,$server->OnClick,
	$iconlinks[0][0],$iconlinks[0][1],$iconlinks[0][2],
	$iconlinks[1][0],$iconlinks[1][1],$iconlinks[1][2],
	$iconlinks[2][0],$iconlinks[2][1],$iconlinks[2][2]);


}
// get template file and strings to look for
$html = file_get_contents($template.'.html');
// do simple macro substritutions 

$html = str_replace(array('{{{PageName}}}','{{{IntanceIDGoesHere}}}'),
					array($pagename,$instanceid),$html);
					
$mpref = '<a href="http://livepage.apple.com/" title="http://livepage.apple.com/"><img src="';
$suf = '.png"';
// inject into the templates
$src = trim($xmldata->ServerPic);
$pref = $mpref.$src;
$html = inject ('SRV','img',$src.$suf,$pref,$suf, $images, $html);

$src = trim($xmldata->UsersPic);
$pref = $mpref.$src;
$html = inject ('USR','img2',$src.$suf,$pref,$suf, $images, $html);

$src = trim($xmldata->GroupsPic);
$pref = $mpref.$src;
$html = inject ('GRP','img2',$src.$suf,$pref,$suf, $images, $html);

echo $html;
?>