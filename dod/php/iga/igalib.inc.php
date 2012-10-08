<?php

function img($type,$url,$name){
	return <<<XXX
	<a target="_new" href="$url" title="$url"><img src="mpng.php?t=$type&url=$url&name=$name" alt="$name"
XXX;
}
function inject ($images, $filespec)
{/*
	<a href="http://livepage.apple.com/" title="http://livepage.apple.com/"><img src="servers_files/shapeimage_2.png" alt="Your Server Here" title="" id="
	*/
	// get template file and strings to look for
	$template = file_get_contents($filespec);
	$pref = '<a href="http://livepage.apple.com/" title="http://livepage.apple.com/"><img src="servers_files/yourserverherepic';
	$suf = '.png" alt=""';
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
		$fpos = strpos ($template,'/>',$mpos);
		if ($fpos ===false) break;
		if($imgnum>=$imgcnt) break;

		// alright, we have to substiture, pull one off the list
		list($type,$url,$name,$iconlinks) =$images[$imgnum++];
		// layout the various icon links as a small list
		$iconspan = '<span class="iconlinks">';
		if ($iconlinks!==false)
		foreach ($iconlinks as $iconlink)
		{
			$url = $iconlink[0];
			$image = $iconlink[1];
			$label = $iconlink[2];
			$iconspan .= "<a href='$url'><img src='$image' alt='$label'></a>&nbsp;";
		};
		$iconspan .='</span>';

		$subs = img($type,$url,$name); // this is the working url
		$template = substr($template,0,$npos).
		$subs.
		substr($template,$mpos+strlen($suf),$fpos-$mpos-strlen($suf)).
		'/></a>'.
		$iconspan.
		substr($template,$fpos+6);// this part could be improved
	}
	// fix up the header
	$npos = strpos($template,'</head>'); // see if there are more

	if ($npos!==false)
	{
		$patch = "<meta http-equiv='refresh' content='60'>";
		$template = substr($template,0,$npos).$patch.substr($template,$npos);
	}
	return $template;
}
?>
