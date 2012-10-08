<?/**
   * A simple layout with logo and basic links, nothing else
   */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" style="border-style: none;">
  <head>
      <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
      <meta name="author" content="MedCommons"/>
      <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c, web standards"/>
      <meta name="description" content="$desc"/>
      <meta name="robots" content="noindex,nofollow"/>
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
      <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/acct_all.css"/>
      <script src="<?=$httpUrl?>/acct_all.js" type="text/javascript"></script>
<?endif;?>
        <style type="text/css">
          #logo  {
            background-image: url(<?=$relPath?>images/mc_logo.png);
          }
        </style>
        <!-- IE6 logo fix -->
        <!--[if lt IE 7]>
        <style type="text/css">
          #logo  {
            background-image: none;
          }
        </style>
        <![endif]-->
  </head>
  <body style="background: white;">
        <div id="container">
            <div id="intro">
              <div id="introcontainer">
                <div id="pageHeader">
                <a href="<?=$g['BASE_WWW_URL']?>">
                    <img alt="MedCommons" 
                      id="logo"
                      width="246"
                      height="60"
                      src="<?=$relPath?>images/blank.gif"
                      style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=$relPath?>images/mc_logo.png', sizingMethod='scale');"
                    /></a>
                </div>
            </div><!--/introcontainer-->
           </div><!--/intro-->
    <div style="padding:3px 8px;">
    <?=$content;?>
    </div>
  </body>
</html>
