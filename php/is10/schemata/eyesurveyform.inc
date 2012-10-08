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

array('General',array(array('Seen by Opthamologist?',920101),
				array('Glasses',920102),
				array('Contacts',920103),
				array('Goggles',920103),
				array('Eye Ware Broken',920105))),
array('Eyeware Used',array(array('None',920101),
				array('Glasses',920102),
				array('Contacts',920103),
				array('Goggles',920103),
				array('Other',920105))),
			

array('Complaint',array(array('Declining Vision',920101),
				array('Red eye',920102),
				array('Foreign Body',920103),
				array('Pain',920103),
				array('Bleeding',920103),
				array('Tearing',920103),
				array('Photophobia',920103),
				array('Asymptomatic',920105))

				
				
				)

);

}

?>