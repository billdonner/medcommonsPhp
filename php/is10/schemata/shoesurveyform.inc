<?php
//
// bluform.inc
//
// these codes were aribtrarily thought up by bill
function dbtab()
{
	return "shoesurveytab";
}
function schema ()
{ 
	return 

array(
array('Shoe Info',  array(
array('Shoe Model',array(array(false,900001))),  // should be well known
array('Cut of Shoe',array(array(false,990102))),
array('comment',array(array(false,111001))))),
array('Support Used',array(array('Taped',920101),
				array('Wrapped',920102),
				array('Braced',920103),
				array('No Support',920105)))

);

}

?>