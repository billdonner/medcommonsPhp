<?php

function make_abbrev ($abbrev,$longer)
{
	$GLOBALS['abbrev'][$abbrev]=$longer;
	$GLOBALS['abbrevcount']++;
}

function emit_list($set)
{
	$out =  "<ul id='$set' class='sortable boxy'>";
	$count = $GLOBALS['layoutcount'];
	for ($i=0; $i<$count; $i++)
	{
		if ($GLOBALS['layout'][$i][0]==$set)
		{
			$out .= '<li id="'.$GLOBALS['layout'][$i][1].'">'.//$GLOBALS['layout'][$i][1].
			' '.$GLOBALS['abbrev'][$GLOBALS['layout'][$i][1]].'</li>';
		}

	}
	$out .= '</ul>';
	return $out;
}
function print_layout()
{

	$count = $GLOBALS['layoutcount'];
	for ($i=0;$i<$count;$i++)
	{   $item = $GLOBALS['layout'][$i][1];
	echo "<br>set=".
	$GLOBALS['layout'][$i][0].',item='.$item.',longer='.
	$GLOBALS['abbrev']["$item"];
	}
}
function encode_layout($set)
{
	$l='';
	$count = $GLOBALS['layoutcount'];
	for ($i=0;$i<$count;$i++)
	{
		if ($GLOBALS['layout'][$i][0]==$set)
		$l.=
		$GLOBALS['layout'][$i][1];
	}
	return $l;
}

function il($set,$item)
{
	$GLOBALS['layout'][]=array($set,$item);
	$GLOBALS['layoutcount']++;
}

function parse_layout($data)
{
	//l ( A )
	//0 1 2 3
	$GLOBALS['layoutcount']=0;
	$containers = explode(":", $data);
	foreach($containers AS $container)
	{
		$len = strlen($container);  //4
		$strpos = strpos($container,'('); // first pos for list 1
		$set = substr($container,0,$strpos); // set =1
		$rest = substr ($container,$strpos+1,$len-$strpos-2);
		$eaches = explode(',',$rest);
		foreach ($eaches as $order)
		{
			//don't include cols without order
			if ($order!=''){
				$GLOBALS['layout'][] = array($set,$order);
				$GLOBALS['layoutcount']++;
			}
		}
	}
}

/*function update_db($data_array, $col_check)
{

foreach($data_array AS $set => $items)
{
$i = 0;
foreach($items AS $item)
{
/* $item = mysql_escape_string($item);
$set  = mysql_escape_string($set);

mysql_query("UPDATE layout SET `set` = '$set', `order` = '$i'  WHERE `item` = '$item' $col_check");

update_layout($set, $item,  $i);
$i ++;
}
}
}
*/

function init_layout()
{	$GLOBALS['layoutcount']=0;
il('right_col', 'g');
il('right_col', 'f');
il('left_col','d');
il('right_col','b');
il('left_col','c');
il('left_col','e');
il('left_col', 'a');
}
//start here

make_abbrev ('a','one');
make_abbrev ('b','two');
make_abbrev ('c','three');
make_abbrev ('d','four');
make_abbrev ('e','five');
make_abbrev ('f','six');
make_abbrev ('g','seven');


//print_abbrev();


if(isset($_POST['order']))
{
	// come here when submite is entered
	parse_layout($_POST['order']); // parse to internal form
	$el = encode_layout('left_col').'|'
	//.encode_layout('center').'|'
	.encode_layout('right_col');
	// redirect so refresh doesnt reset order to last save
	header("location: dd2col.php?el=$el");
	exit;
}

//start here if fresh

$el = $_REQUEST['el'];
if ($el =='')
{// nothing setup so init the table

init_layout();

}
else
{
	list ($left,$right) = explode('|',$el);  // split each apart
	$count = strlen($left); //
	for ($i=0; $i<$count; $i++) il('left_col',substr($left,$i,1));
//	$count = strlen($center); //
//	for ($i=0; $i<$count; $i++) il('center',substr($center,$i,1));
	$count = strlen($right); //
	for ($i=0; $i<$count; $i++) il('right_col',substr($right,$i,1));
}
$left = emit_list('left_col');
//$center = emit_list('center');
$right = emit_list('right_col');
$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html><head><title>Drag Drop</title>


<style type="text/css">
#left_col {
    width: 180px;
    float: left;
    margin-left: 5px;
}

#center {
    width: 180px;
    float: left;
    margin-left: 5px;
}

#right_col {
    width: 180px;
    float: left;
    margin-left: 5px;
}


}

form {
  clear: left;
}

body {
	background: #FCFEF4  repeat-x;
	margin: 10px 10px 10px 10px;
	font-family: Arial, Verdana, Helvetica;
	font-size: 76%;
	color: #3F3F3F;
	text-align: left;
	}

h2 {
	color: #7DA721;
	font-weight: normal;
	font-size: 14px;
	margin: 20px 0 0 0;
	}

br {
        clear: left;
}
</style>

<link rel="stylesheet" href="dd_files/lists.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="dd_files/coordinates.js"></script>
<script language="JavaScript" type="text/javascript" src="dd_files/drag.js"></script>
<script language="JavaScript" type="text/javascript" src="dd_files/dragdrop.js"></script>
<script language="JavaScript" type="text/javascript">

window.onload = function() {

	var list = document.getElementById("left_col");
	DragDrop.makeListContainer( list, 'g1' );
	list.onDragOver = function() { this.style["background"] = "#EEF"; };
	list.onDragOut = function() {this.style["background"] = "none"; };


	list = document.getElementById("right_col");
	DragDrop.makeListContainer( list, 'g1' );
	list.onDragOver = function() { this.style["background"] = "#EEF"; };
	list.onDragOut = function() {this.style["background"] = "none"; };

};

function getSort()
{
	order = document.getElementById("order");
	order.value = DragDrop.serData('g1', null);
}

function showValue()
{
	order = document.getElementById("order");
	alert(order.value);
}
//-->
</script></head>

<body>

<h2>Modify MyPage</h2>
<p>Choose your components and drop them onto your page</p>
<br />
$left
$center
$right
<form action="" method="post">
<br />
<input type="hidden" name="order" id="order" value="" />
<input type="submit" onclick="getSort()" value="Update Order" />
</form>
</body></html>
XXX;

echo $html;
?>
