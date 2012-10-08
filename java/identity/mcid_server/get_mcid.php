#!/usr/bin/env php
<?php

    $URL = 'http://mcid.internal:1080/mcid';
    $NS = 'http://www.medcommons.net/mcid';

    $client = new SoapClient(null, array('location' => $URL, 'uri' => $NS));

    $mcid = $client->next_mcid();

    echo $mcid;

?>

