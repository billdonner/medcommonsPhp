<?php

//define the path as relative
$path = "/var/www/php/funcs";

//using the opendir function
$dir_handle = @opendir($path) or die("Unable to open $path");

echo "<h3>Template Functions Installed on $path</h3>";
echo "<table border=2><tr><th>account</th><th>template</th><th>type</th><th>view</th>
<th>test</th><th>test</th><th>test</th></tr>";
//running the while loop
while ($file = readdir($dir_handle))
{
	if($file!="." && $file!="..")

	{   list ($accid, $name, $ftype) = explode ('-',$file);
	if ($ftype=='vxm.ccr.php') $type='vxm'; else $type='unk';

	echo "<tr><td>$accid</td><td>$name</td><td>$type</td>
	<td><a target='_new' href='Vx.php?c=code&t=$accid:$name' >code</a></td>
	<td><a target='_new' href='Vx.php?c=form&t=$accid:$name&a=ARGA|ARGB|ARGC|ARGD|ARGE|ARGF|ARGG|ARGH|ARGI|ARGJ' >form</a></td>
	<td><a target='_new' href='Vx.php?c=xml&t=$accid:$name&a=ARGA|ARGB|ARGC|ARGD|ARGE|ARGF|ARGG|ARGH|ARGI|ARGJ' >xml</a></td>
	<td><a target='_new' href='Vx.php?c=do&t=$accid:$name&a=ARGA|ARGB|ARGC|ARGD|ARGE|ARGF|ARGG|ARGH|ARGI|ARGJ' >do</a></td>
	</tr>";
	}

}
echo "</table>";
//closing the directory
closedir($dir_handle);

?> 