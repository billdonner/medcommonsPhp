<h3>Group Membership Created</h3>
<p>You have successfully joined the <?=hsc($p->practicename)?></span> Group.</p>
<p>Your new group membership is valid immediately.</p>
<form name="returnForm" action="<?=rtrim($g['BASE_WWW_URL'],'/').'/info.php'?>">
    <input type="submit" name="send" size="40" value="Go to Worklist Page" title="Return to the Group Worklist"/>
</form>

