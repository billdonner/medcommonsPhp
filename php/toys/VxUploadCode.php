<?php

// get a piece of code from the user and patch into his or her medcommons account


$html =<<<XXX
<html><head><title>VxUploadCode</title>
<script type="text/javascript" >
               <!-- 
function widgetkeypopup(url) {
	newwindow=window.open(url,'_widget','height=200,width=700');
	if (window.focus) {newwindow.focus()}
	return false;
}
// --> 
			</script>
</head><body><form action=storecode.php method=post>
<h2>Adding Code to Your Account</h2>
<p>You can add php functions to your account that will be called for various analysis and translation functions. 
<p>Choose the type of php module you are storing in your account:
<select name=type>
  <option value ="vx">Vx xml message remapper</option>
  <option value ="tx">Tx test message formatter</option>
</select>
<p>Supply a name for your function (1-32 characters): <input type=text size=32 name=name />
<p>Enter your MedCommons Key: <input type=text size=32 name=mckey /> 
<small><a  onclick="return widgetkeypopup('../acct/widgetkey.php');" 
                                     href='../acct/widgetkey.php' >show key</a></small>

<p>
Please type or paste your php function code here, using this template as an exmple.<br>
Do not include the opening and closing php tags<br>
<textarea rows="20" cols="80" name=code>
function vx(\$a,\$b,\$c,\$d,\$e,\$f,\$g,\$h,\$i,\$j)
{ 
\$signature = "MEDCOMMONSDEVELOPERKEYGOESHERE"; // MedCommons Developer Key
\$xml="&lt;vxm&gt;
Layout any XML of your choice. This sample just echoes the arguments to Vx
a=\$a
b=\$b
c=\$c
c=\$d
e=\$e
f=\$f
g=\$g
h=\$h
i=\$i
j=\$j
&lt;/vxm&gt;";
return array(\$signature,\$xml);
}</textarea><br>
<input type=submit value=upload>
</form>
XXX;


echo $html;
?>