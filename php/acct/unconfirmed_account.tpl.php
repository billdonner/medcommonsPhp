
<script type='text/javascript'>
  window.setTimeout(function() { window.location.href='check_confirm_account.php'; }, 20000);
</script>
<?if(isset($msg)):?>
<div style='padding: 30px;'>
  <div class='dashboardmsg'>
    <?=$msg?>
  </div>
  <p style='text-align: center;'><a href='javascript:window.location.href="check_confirm_account.php"'>Refresh Page</a></p>
</div>
<?endif?>
