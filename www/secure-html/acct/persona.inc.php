<?php

function showpersona($accid,$id)
{
	// read them all as they now exist
	$q = "select * from users where mcid='$accid'";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	$user=mysql_fetch_object($result);
	$upersona=$user->persona;

	// read them all as they now exist
	$q = "select * from personas  where accid='$accid' and '$id'=personanum";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());


	$persona=mysql_fetch_object($result);

	$p = $persona->persona;
	if ($persona->isactive!=1)$out="not active"; else
	{
		$out ="<div><h4><span>Personal Information Disclosure Rules for $p <img src='".$persona->personagif."' /> </span></h4><p class='p2'>
			
		<p class='p2'><i>These are the factoids MedCommons will reveal to outside services</i></p><p class='p2'>";

		if ($persona->exposephone==1)
		{
			if ($persona->inheritphone==1)$out.="Your phone number will be disclosed as $user->mobile, from your account id<br>";
			else $out.="Your phone number will be disclosed as $persona->phone<br>";
		}


		if ($persona->exposemyid==1)
		{
			if ($persona->inheritmyid==1)$out.="Your idnum number will be disclosed as $user->mcid from your account<br>";
			else $out.="Your idnum number will be disclosed as $persona->myid<br>";
		}

	}


	return $out;
}
?>