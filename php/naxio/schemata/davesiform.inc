<?php
//
// davesiform.inc
//
// these codes were aribtrarily thought up by bill
function dbtab()
{
	return "injurytab";
}
function schema ()
{ return array(
          array('Background Info',  array(
                                array('short case description',array(array(false,900001))),  // should be well known
                                array('location',array(array('home',990101),array('away',990102))),
                
                                array('surface',array(array('court',990201),
                                                            array('grass',990202),
                                                            array('turf',990203))),                                                            
                    
                                  array('activity',array(array('game',990301),
                                                            array('practice',990302),
                                                            array('lifting',990303),
                                                            array('conditioning',990304),
                                                            array('other',990305))))),
                    array('Head',array(
                                      array('scalp',array(array('laceration',110101),
                                                          array('contusion',110102))),
                                      array('brain',array(array('concussion',110201),
                                                          array('seizure',110202))),
                                     array('eye',array(  array('contusion',110301),
                                                          array('FB',110302),
                                                          array('laceration',110303),
                                                          array('globe injury',110304))),
                                      array('ear',array(array('contusion',110404),
                                                         array('laceration',110405),
                                                          array('eardrum injury',110406))),
                                      array('nose',array(  array('fracture',110501),
                                                          array('laceration',110502),
                                                          array('contusion',110503),
                                                          array('expistaxis',110504))),
                                      array('facial bone',array(array('fracture',110601),
                                                          array('contusion',110602))),
                                      array('mouth/lip',array(array('laceration',110701),
                                                          array('contusion',110702))),
                                      array('jaw',array(array('fracture',110801),
                                                          array('contusion',110802))),
                                       array('teeth',array(array('avulsiton',110901),
                                                          array('fracture',110902))),
                                      array('other',array(array(false,111001))))),
                                      
              array('Neck',array(
                                      array('spine',array(  array('fracture',120101),
                                                          array('laceration',120102),
                                                          array('contusion',120103),
                                                          array('expistaxis',120104)),
                                   array('spinal cord',array(array('contusion',120201),
                                                         array('laceration',120202),
                                                          array('eardrum injury',120203))),
                                      array('larynx',array(
                                                          array('trauma',120301))),
                                      array('throat',array(array('trauma',120302),
                                                          array('laceration',120303)))))),
         		array('UE',array(
                                      array('shoulder',array( array('dislocation',130101),
                                                                     array('ac injury',130102),
                                                                     array('RC tear',130103),
                                                                           array('impingement',130104),
                                                                     array('instability',130105),
                                                                     array('contusion',130106),
                                                                           array('fracture',130107),
                                                                     array('labral tear',130108),
                                                                     array('SC join in',130109))),
                                      array('upper arm',array( array('fracture',130201),
                                                                             array('contusion',130202),
                                                                             array('bicep injury',130203))),
                                       array('elbow',array(  
                                                                    array('fracture',130301),
                                                                     array('ligament inj',130302),
                                                                     array('contusion',130303),
                                                                     array('epicondylitis',130304),
                                                          		array('OCD',130305))),
                                    array('forearm',array(  
                                                               array('fracture',130401),
                                                               array('contusion',130402),
                                                                     array('epicondylitis',130403),
                                                          array('OCD',130404))),
                                      
                                  array('wrist',array(
                                               array('fracture',130501),                                                                
                                               array('contusion',130502),
                                               array('sprain',130503))),
                                               
                                    array('hand' ,array(                   array('carpal fracture',130601),
                                                                     array('metacarpal fracture',130602),
                                                                     array('tendon injury',130603),
                                                                     array('ligament injury',130604),
                                                          array('sprain',130605))),
                                     array('finger/thumb',array(  array('contusion',130701),
                                                          array('FB',130702),
                                                          array('laceration',130703),
                                                          array('globe injury',130704))),
                                     array('nerve' ,array(                   array('brachial plexus',130801),
                                                                     array('axillary nerve',130802),
                                                                     array('median nerve',130803),
                                                                     array('ulnar nerve',130804),
                                                          array('radial nerve',130805))),
                                      array('other',array(array(false,130901))))),
                                      
                                      
                                      
                                      
     array('LE', array(
                                      array('hip',array( array('fracture',140101),
                                                                     array('sprain',140102),
                                                                     array('bursitis',140103),
                                                                           array('muscle strain',140104),
                                                                     array('tendonitis',140105),                                                              
                                                                     array('labral tear',140106)  )),
                                      array('hamstring',array( array('strain',140201),
                                                                             array('tear',140203),
                                                                             array('contusion',140204))),
                                       array('quad',array(  
                                                                    array('strain',140301),
                                                                     array('tear',140302),
                                                                     array('contusion',140303))),
                                    array('groin',array(  
                                                               array('strain',140401),
                                                               array('trauma',140402),
                                                          array('adductor',140403),
                                                           array('hernia',140404),
                                                           array('testicular injury',140405),
                                                           array('pernile injury',140406),                                                                  
                                                          array('sports hernia',140407))),
                                      
                                  array('knee',array(
                                               array('fracture',140501),                                                                
                                               array('mcl/lcl',140502),
                                               array('acl',140503),                  
                                               array('mm/lm',140504),                   
                                               array('pf pain',140505),                   
                                               array('pcl',140506),                   
                                               array('plc',140507),                 
                                               array('chondral injury',140508))),
                                               
                                    array('tib/fib' ,array(array('fracture',140601),                                                              
                                                          array('contusion',140602))),
                                     array('calf/achilles',array(  array('tear muscle',140701),
                                                          array('achilles tear',140702),                                                      
                                                          array('tendonitis',140703))),
                                     array('ankle' ,array(                   array('fracture',140801),
                                                                     array('medial sprain',140802),
                                                                     array('lateral sprain',140803),
                                                                     array('sinus tarsi',140804),                                                                 
                                                                     array('impingement',140805))),
                                   array('foot' ,array(                   array('lisfranc',140901),
                                                                     array('fracture',140902),
                                                                     array('sprain',140903),
                                                                     array('plantar fascitis',140904),
                                                                     array('mortons',140905),array('metatarsalgia',140906),
                                                          array('tendonitis',140907))),
                                   array('toes' ,array(                   array('fracture',141001),        array('contusion',141002),  array('tendonitis',141003))),
                                    array('other',array(array(false,141100))))),
                                    
                                    
        array('Chest',array(
                                      array('lungs',array(  
                                                          array('contusion',150101))),
                                   array('ribs',array(array('fracture',150201),
                                                         array('contusion',150202))),
                                    array('sternum',array(array('fracture',150301),
                                                          array('contusion',150302))))),
                                                          
                                                          
        array('Back',array(
                                      array('thoracic',array(
                                                          array('fracture',160101),                                      
                                                          array('sprain',160102))),
                                         array('lumbar',array(
                                                          array('fracture',160201),                                      
                                                          array('sprain',160202))))),
                                    
        array('Skin',array(array('condition',array(   array('fungal',170101),		
                                                                             array('viral',170102),
                                                                             array('bacterial',170103))))),
                    
         array('Imaging',array(
                       array('Xray',array(array(false,910101))),
                       array('CT',array(array(false,910102))),
                       array('MRI',array(array(false,910103))),
                       array('US',array(array(false,910104))),
                       array('other', array(array(false,910105))))),
                                      
          array('Treatment plan',array(array('rehab',array(array(false,920101))),
                   array('surgery',array(array(false,920102))),
                   array('rest',array(array(false,920103))),
                   array('bracing',array(array(false,920104))),
                   array('taping',array(array(false,920105))) )),
                   
           array('Predicted Outlook',array(array(false,array(array(false,930101)))))
                    );
                    
}                    
                    
?>