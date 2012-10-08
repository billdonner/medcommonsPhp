<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <?if($enableCombinedFiles):?>
      <link rel="stylesheet" href="<?=$httpUrl?>/acct_all.css" type="text/css"/>
      <script type="text/javascript" src="<?=$httpUrl?>/acct_all.js">This page needs Javascript to work properly.</script>
    <?else:?>
      <link rel="stylesheet" href="<?=$httpUrl?>/acctstyle.css" type="text/css"/>
      <link rel="stylesheet" href="<?=$httpUrl?>/main.css" type="text/css"/>
      <link rel="stylesheet" href="<?=$httpUrl?>/featurebox.css" type="text/css"/>
      <script type="text/javascript" src="<?=$httpUrl?>/MochiKit.js">This page needs Javascript to work properly.</script>
      <script type="text/javascript" src="<?=$httpUrl?>/utils.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/featurebox.js"></script>
      <script type="text/javascript" src="<?=$httpUrl?>/ajlib.js"></script>
    <?endif;?>
    <script type="text/javascript">
        function calculateHeight() {
          if($('featureboxes')) { // prevents errors on load
            var h = $('featureboxes').scrollHeight + 12;
            return h;
          }
          else
            return 0;
        }
    </script>
    <!--[if lt IE 7]>
      <script type="text/javascript">
        function calculateHeight() {
          if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
            h = document.body.scrollHeight;
          } 
          else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
            h = document.body.offsetHeight;
          }
          return h;
        }
      </script>
    <![endif]-->
    <script type="text/javascript">
      var resizeEnabled=true;
      var scriptDomain = '<?=$GLOBALS["Script_Domain"]?>';
      function updateSize() {
        var d = (scriptDomain && (scriptDomain!="")) ? scriptDomain : null;
        //var h = getPageSize().h;
        var h = calculateHeight();
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
      connect(ce_events,'ce_resized',updateSize);
      function init() {
        updateSize();

        <? // Set up auto-sync'ing of iframe heights ?>
        addHeightSync();
        window.setInterval(broadCastHeight,2000); // ugh, hack, why window.resize not working?

   
      }
    </script>
    <style type="text/css">
      body {
        padding: 5px;
      }
    </style>
  </head>
  <body onload="init();" onresize="updateSize();" style="background-color: transparent;">
    <?=$content?>
  </body>
</html>
