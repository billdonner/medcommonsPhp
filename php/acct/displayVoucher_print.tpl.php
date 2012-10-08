<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Medical Records Access Voucher</title>
        <link media="all" href="../css/medCommonsStyles.css" type="text/css" rel="stylesheet" />
        <link ref='stylesheet' type='text/css' href='acct_all.css.php'/>
        <style type='text/css'>
        <? include "voucher_css.inc.php"; ?>
        button {
            display: none;
        }
        </style>
    </head>
    <body onload='window.print();' id='voucherPanel'>
        <?=$contents?>
    </body>
</html>
