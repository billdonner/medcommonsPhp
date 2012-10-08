<?php
// poke this into the Servers Table
$accid = $_REQUEST['accid'];
$from = $_REQUEST['from'];
$oldloc = "http://medcommons.net/my";
$newloc = "../acct/startPage.php";
$html = <<<XXX
<p>Pardon the inconvenience while we are experimenting with new start pages</p>
<ul>
<li><a target='_top' href=$oldloc>normal start page (click on account number)</a></li>
<li><a target="_top" href=$newloc>new start page</a></li>
</ul>
XXX;
echo $html;
?>

