<?php

// this is where the world asks the MedCommons Service Provider to take us to a particular desktop
//
// 
$args = $_GET['a'];
header ("Location: $args");


echo "in /ws/gotodesktop moving to gateway gateway001.test.medcommons.net args $args"

?>