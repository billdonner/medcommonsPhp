<?
/**
 * Renders a file upload form pointed at the user's gateway.  
 *
 * User must be specified as $u.
 */
$t = $u->getTargetUser();
?>
<div>
<p>You can add a PDF or other document to your account by entering it below and submitting the form:</p>
<form name="addpdf" enctype="multipart/form-data" action="<?=$t->gw?>/Attach.action" method='post'>
    <input type='hidden' name='callback' value='<?=$GLOBALS['facebook_application_url']?>/upload_result.php'/>
    <input type='hidden' name='auth' value='<?=$t->token?>'/>
    <input type='hidden' name='accid' value='<?=$t->mcid?>'/>
    <input type='file' name='file'>
    <br/>
    <br/>
    <input type='submit' value='Upload Document'/>
</form>
</div>
