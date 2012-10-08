 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" style="border-style: none;">
  <head>
      <?if(isset($title)):?><title><?=$title?></title><?endif;?>
      <link rel="alternate" type="application/rss+xml" title="rss" href="https://www.medcommons.net/acct/rss.php"/>
      <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
<?if(!$enableCombinedFiles):?>
      <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/tabs.css"/>
      <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/autoComplete.css"/>
      <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/frame.css"/>
      <style type="text/css" media="all"> @import "<?=$httpUrl?>/main.css";</style> 
      <style type="text/css" media="all"> @import "<?=$httpUrl?>/theme.css"; </style>
      <script src="<?=$httpUrl?>/MochiKit.js" type="text/javascript"></script>
      <script src="<?=$httpUrl?>/tabs.js" type="text/javascript"></script>
      <script src="<?=$httpUrl?>/utils.js" type="text/javascript"></script>
      <script src="<?=$httpUrl?>/autoComplete.js" type="text/javascript"></script>
      <script src="<?=$httpUrl?>/featurebox.js" type="text/javascript"></script>
      <script src="<?=$httpUrl?>/ajlib.js" type="text/javascript"></script>
<?else:?>
      <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/acct_all.css.php"/>
<?endif;?>
  </head>
  <body style="background: white;">
    <?=$content;?>
  <?if($enableCombinedFiles):?>
      <script src="<?=$httpUrl?>/acct_all.js.php" type="text/javascript"></script>
  <?endif;?>
      <script type="text/javascript">
        addLoadEvent(addHeightMonitor);
      </script>
  </body>
</html>
