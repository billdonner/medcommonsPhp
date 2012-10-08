<?php
if (!isset($_POST['retloc']))
{
	// some form of GET
	echo "Here's a dump of the arguments posted:<br/>";
	print_r($_POST);
}
else
{
	$err='';$tomap='';
	$func = $_POST['title'];
	// process posted arguments
	for ($i=1;$i<10;$i++)
	{
		if (!isset($_POST["__var_$i"])) break;
		$name = $_POST["__var_$i"];
		$value = $_POST["$name"];
		echo "$i $name $value <br/>";
		// check upper and lower bounds if any
		if (isset($_POST["__var_$i"."_lb"]))
		{
			$lb = $_POST["__var_$i"."_lb"];
			echo "$i $name $value lower $lb<br/>";
			if ($value < $lb ) $err.="$name is $value must be >= $lb <br/>";
			
		}
		if (isset($_POST["__var_$i"."_ub"]))
		{
			$ub = $_POST["__var_$i"."_ub"];
			
			echo "$i $name $value upper $ub<br/>";
			if ($value > $ub ) $err.="$name is $value must be <= $ub <br/>";
		}
		if ($err=='')
		{
			if (isset($_POST["__var_$i"."_pathmap"]))
			{
			//no errors, do the mapping
			$pm = $_POST["__var_$i"."_pathmap"];
			$tomap.="Map $name $value to $pm <br/>";
			echo "$i $name $value pm $pm<br/>";
			}
		}
	}
	
	if ($err!='')
	{
		$tomap='';
		
	}
 else
	
	
	// when all done, redirect, passing along an error block
	$err=urlencode($err);
	$tomap = urlencode($tomap);
	$func = urlencode($func);
	$tomap="&map=$tomap";
	$page = $_POST['retloc']."$tomap&title=$func&err=$err";
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	//	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "</fb:fbml>";
	echo $markup;
	exit;
}
?>