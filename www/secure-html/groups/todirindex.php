<?php
//todir functions restricted to working just for group with this ID
$id = $_REQUEST['id'];
$html = <<<XXX
<html><head><title>MedCommons - ToDir HomePage </title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
</head>
<body>
<table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="ToDir Services Home" /></a>
                </td><td>ToDir Services Home <small><i>for internal use only</i>
                 </small></td><td> &nbsp;<a href = 'todirquery.php'>query</a></small></td>
<td> &nbsp;<a href = 'todiradd.html'>add</a></small></td>
<td> &nbsp;<a href = 'todirdel.html'>delete</a></small></td>
</tr></table>
<h3>MedCommons ToDir Services HomePage - intended for internal use only</h3>
<p>This is the ToDir HomePage. It contains documentation and tests for the ToDir services
<p>At this point the ToDir services have no security and are implemented as a single MySQL table.
</p>
<h4>Structure of the ToDir</h4>
At the present time, the ToDir is just a big table. It doesn't even have any particular keys of interest. 
Here's the current schema
<xmp>

CREATE TABLE `todir` (
  `xid` varchar(255) NOT NULL default '',
  `ctx` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `contact` varchar(255) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  `accid` varchar(16) NOT NULL default ''
) TYPE=MyISAM COMMENT='holds mappings for to and replyto fields';
    
</xmp>
<h4>Browser Interface to ToDir</h4>
<fieldset>
<legend>
links to UI admin screens
</legend>
<ul>
<li><a href=todirquery.php>Query the ToDir</a></li>
<li><a href=todiradd.php>Add an entry to the ToDir</a></li>
<li><a href=todirdel.php>Delete an entry in the Todir</a></li>
</ul>
</fieldset>

<h4>Webservice Interface to ToDir</h4>
There are 3 REST Services, implemented according to simon's memo TODIR_WEB_SERVICES_INTERFACE with mos by donner.
<h4>Webservice Test Cases</h4>
All of these cases work as of this writing. They also can take an additional &accid= to add a medcommons id. We'll figure out
where this comes from in short order (either the user, or perhaps an administrator making entries on behalf of a group
<h5>addToDirEntry.php</h5>
<ul>
<li><a href='ws/addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo'>
addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo</a></li>
<li><a href='ws/addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo'>
addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo</a></li>
<li><a href='ws/addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo'>
addToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com&contact=mumbojumbo</a></li>
</ul>
<h5>deleteToDirEntry.php</h5>
<ul>
<li><a href='ws/deleteToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com'>deleteToDirEntry.php?ctx=123&xid=456&alias=foo@bar.com</a></li>
<li><a href='ws/deleteToDirEntry.php?ctx=123&xid=456'>deleteToDirEntry.php?ctx=123&xid=456</a></li>
<li><a href='ws/deleteToDirEntry.php?ctx=123'>deleteToDirEntry.php?ctx=123</a></li>
<li><a href='ws/deleteToDirEntry.php?xid=456'>deleteToDirEntry.php?xid=456</a></li>
</ul>
<h5>queryToDir.php</h5>
<ul>
<li><a href='ws/queryToDir.php?ctx=123&xid=456'>queryToDir.php?ctx=123&xid=456</a></li>
<li><a href='ws/queryToDir.php?ctx=123&alias=foo@bar.com'>queryToDir.php?ctx=123&alias=foo@bar.com</a></li>
<li><a href='ws/queryToDir.php?ctx=123'>queryToDir.php?ctx=123</a></li>
<li><a href='ws/queryToDir.php?ctx=123&xid=456'>queryToDir.php?ctx=123&xid=456</a></li>
</ul>

</body>
</html>