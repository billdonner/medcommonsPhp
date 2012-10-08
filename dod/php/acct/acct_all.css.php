<?php
    header('Content-Type: text/css');
    header('Cache-Control: private');
    header('Expires: Tue, 01 Jul 2025 00:00:00 GMT');
    ob_start("ob_gzhandler");
    include("acct_all.css");
?> 
