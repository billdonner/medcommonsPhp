<?php
//$gw = 'https://gateway001.private.medcommons.net:9090';
$gw = 'http://gateway001.private.medcommons.net:9090';
$x=<<<XXX


 <h2>CXP GET TEST</h2>
 <form name="getform" action="$gw/router/CxpRestServlet" method="post" enctype="application/x-www-form-urlencoded" target="_blank">
  <table>
  <tr><td> <input type="hidden" name="Command" value="GET"  ></td></tr>
  <tr><td>GUID</td><td><input type=text name=guid value=""></td></tr>
  <tr> <td> <input type="submit" value="GET"></td></tr>

</table>
  </form>
XXX;

echo $x;
?>