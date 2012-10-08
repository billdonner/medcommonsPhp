<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>You have been invited to Join a Group on <?=htmlentities($acApplianceName)?></title>
    </head>
    <body>
        <p>Hello,</p>

        <p><?=htmlentities($user->fn)?> <?=htmlentities($user->ln)?> has invited you to join the
           <?=htmlentities($groupName)?> group on <?=htmlentities($acApplianceName)?>.</p>

        <p>You can respond to this invitation by clicking the link
           below which will take you a registration page where you
           can sign up for an account and join the group:</p>

        <a href='<?=$url?>'><?=htmlentities($url)?></a>
    </body>
</html>
