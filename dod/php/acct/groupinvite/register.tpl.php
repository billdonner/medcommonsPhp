<h3>Welcome to the <span style='color: #080;'><?=hsc($p->practicename)?></span> Group.</h3>
<?if(isset($msg)):?>
<p style="color: red;"><?=$msg?></p>
<?endif;?>
<p>Apologies, this page is still under construction.</p>
<? /*
<p>To proceed with joining the group, please fill out the registration details below to create your MedCommons Account:</p>
<form action="../register.php" method="post">
*?>
<?
/*
  $next = detrail($g['Accounts_Url'])."/groupinvite/verifyJoin.php?join=true&a=$accid&e=".urlencode($email)."&h=$hmac";
  echo template("../register.tpl.php")->set("email",$email)->set("fixedEmail","true")->set("next",$next)->set("fn","")->set("ln","")->fetch();
 */
?>
