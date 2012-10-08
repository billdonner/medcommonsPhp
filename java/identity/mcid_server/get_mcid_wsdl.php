#!/usr/bin/env php
<?php

    $client = new SoapClient('http://mcid.internal:1080/wsdl');

    $mcid = $client->next_mcid_str();

    echo $mcid;

?>

