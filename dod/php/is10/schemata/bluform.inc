<?php
//
// bluform.inc
//
// these codes were aribtrarily thought up by bill
function dbtab()
{
	return "blutab";
}
function schema ()
{ return array(
array('Background Info',  array(
array('short case description',array(array(false,900001))),  // should be well known
array('location',array(array('home',990101),array('away',990102))),
array('comment',array(array(false,111001))))),



array('Skin',array(array('condition',array(   array('fungal',170101),
array('viral',170102),
array('bacterial',170103))))),



array('Treatment plan',array(array('rehab',array(array(false,920101))),
array('surgery',array(array(false,920102))),
array('rest',array(array(false,920103))),
array('bracing',array(array(false,920104))),
array('taping',array(array(false,920105))) )),

array('Predicted Outlook',array(array(false,array(array(false,930101)))))
);

}

?>