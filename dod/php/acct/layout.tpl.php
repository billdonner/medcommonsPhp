<?
/**
 * Layout template for an embedded widget
 *
 * This template is meant to be rendered inside an iframe so it has minimal
 * frills around the actual content.
 *
 * Note that paths to resources in this template should be prefixed by the $relPath 
 * variable. This variable is set automatically by the template class and contains 
 * the relative path to arrive at the web root from the page including the 
 * template.  Use of this variable allows code in child directories to reuse this
 * template but still create correct urls to external resources 
 * (links, javascript, css, images).
 *
 * @auther ssadedin@medcommons.net
 */
$httpUrl=$GLOBALS['BASE_WWW_URL']."/acct";
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" style="border: 0;">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c, web standards"/>
        <meta name="description" content="MedCommons Home Page"/>
        <meta name="robots" content="all"/>
        <title>MedCommons - Interoperable and Private Personal Health Records</title>
        <link rel="stylesheet" type="text/css" media="print" href="<?=$httpUrl?>/print.css"/>
        <link rel="shortcut icon" href="<?=$httpUrl?>/images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "<?=$httpUrl?>/main.css"; </style>
        <style type="text/css" media="all"> @import "<?=$httpUrl?>/theme.css"; </style>
<?/*
        <style type="text/css" media="all"> @import "<?=$relPath?>theme.css.php"; </style>
 */?>
        <link rel="stylesheet" type="text/css" href="<?=$httpUrl?>/frame.css"/>
        <script src="<?=$httpUrl?>/MochiKit.js" type="text/javascript"></script>
        <script src="<?=$httpUrl?>/params.js" type="text/javascript"></script>
        <script src="<?=$httpUrl?>/utils.js" type="text/javascript"></script>
        <script type="text/javascript">
         var scriptDomain = '<?=$GLOBALS["Script_Domain"]?>';
         var resizeEnabled=true;
         function updateSize() {
           var d = (scriptDomain && (scriptDomain!="")) ? scriptDomain : null;
           var h = window.calculateHeight?calculateHeight() : document.body.scrollHeight;
           log("calculated height = " + h);
           if(h == 0) {
             window.setTimeout(updateSize,200);
           }
           else {
             if(resizeEnabled) {
               setCookie("mcf", h, new Date((new Date().getTime()+86400000)), '/');
               //resizeEnabled = false;
             }
             else {
               log("resize disabled");
             }
           }
         }
      </script>
    </head>
    <body onload="if(scriptDomain&&(scriptDomain!=''))document.domain=scriptDomain;window.onresize=updateSize;updateSize();" style="background: white;">
        <script type="text/javascript">
          var relPath = '<?=$relPath?>';
          var args = parseQueryString(window.location.search.substring(1));
          if(args.css) {
            document.write('<style type="text/css" media="all"> @import "'+args.css+'.css"; </style>');
          }
        </script>
        <?=$content?>
    </body>
</html>
