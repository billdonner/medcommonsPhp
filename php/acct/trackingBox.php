<? 
 
    include("dbparamsidentity.inc.php");

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  $idUrl = $GLOBALS['Identity_Base_Url'];
  $wwwUrl = $GLOBALS['BASE_WWW_URL'];
  $secureUrl = $GLOBALS['Commons_Url'];
?>
<html>
  <head>
    <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
    <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
    <style type="text/css" media="all"> @import "trackingBox.css"; </style>
    <script src="MochiKit.js" type="text/javascript"></script>
    <script src="utils.js" type="text/javascript"></script>
    <script src="trackingBox.js" type="text/javascript"></script>
    <script type="text/javascript">
      var secureHost = '<?echo $secureUrl;?>';

      function trackingError(e) {
        if(!e) {
          e = window.bridgeResult;
        }
        $('goButton').disabled=false;
        alert('An error occurred while processing your tracking number: \r\n\r\n  ' + e.description);
      }

      function goParent(url) {
        window.bypasscheck = true;
        var f = document.trackingForm;
        f.action = url;
        f.method='post';
        f.target='_parent';
        f.p.value='';
        f.submit();
      }

      function goWww(ft,p,accid) {
        var f = document.navForm;
        f.target='_parent';
        f.p.value=p;
        f.f.value=ft;
        f.accid.value=accid;
        f.submit();
      }

      function track(trackInfo) {
        if(!trackInfo)
          trackInfo = window.bridgeResult;

        alert(1);

        $('goButton').disabled=false;
        window.bypasscheck = true;
        if(trackInfo.status) {
          goParent(trackInfo.url+'/tracking.jsp?tracking='+trackInfo.tn);
        }
        else {
          goWww('','badtracknum.php',0);
        }
      }

      function checkTBId() {
        if(window.bypasscheck) {
          window.bypasscheck = false;
          return true;
        }

        var tn = document.trackingForm.trackingbox.value.replace(/[- \t]/g,'');
        if(tn.length > 13) { // treat as account id
          window.setTimeout('goLogin();');
          return false;
        }
        else {
          $('goButton').disabled=true;
          var url = secureHost+'tracking_translate.php?tracking='+tn;
          window.securebridge.setTimeout("loadJSONDoc('"+url+"').addCallbacks(window.parent.track, window.parent.trackingError);");
          return false;
        }
      }

      function goLogin() {
          goWww('login.php','',window.accountInfo ? window.accountInfo.accountId : '');
      }

      function account() {
        goWww('acctredir.php','goStart.php',window.accountInfo.accountId);
      }

      function initLoginCheck() {
        var domain = '<? echo $GLOBALS['Script_Domain']; ?>';
        if(domain != '') {
          document.domain = domain;
        }
        //updateAccountInfo();
        //checkLogin();
        setInterval(checkLogin,500);
      }
    </script>
  </head>
  <body style='background: transparent;' onload="initLoginCheck();">
    <div id="trackingBox">
      <div id="trackboxOuter">
        <? /* hidden form used to target parent for navigations */ ?>
        <div style="display: none;">
          <form name="navForm" action="<?echo $wwwUrl;?>" target="_parent">
          <input type="hidden" name="p"/>
          <input type="hidden" name="f"/>
          <input type="hidden" name="accid"/>
          </form>
        </div>
        <div id="trackboxTop">
          <form name="trackingForm" onsubmit='return checkTBId();' action="index.html" method="get" target="_parent">
            <input type="hidden" value="login.php" name="f">
            <input type="hidden" value="trackingbox" name="p">
            <input type="hidden" value="<?echo $wwwUrl;?>/alreadyin.php" name="returnurl2">            
            <input type="hidden" value="<?echo $wwwUrl;?>/badtracknum.php" name="returnurl">
            <span id="tbTnLabel" style="vertical-align: middle;">Tracking# or ID</span>&nbsp;
            <span style="vertical-align: middle;"><input maxLength="64" size="16" name="trackingbox"> <input id="goButton" type="submit" value="Go"/></span>
          </form>
        </div>
        <div id="trackboxBottom">
          <div style="font-size: 10px; padding: 0px 3px">
            <span style="float: right;"><a href="<?echo $wwwUrl;?>logout.php" target="_parent" title="Log out from your MedCommons Account">logout</a></span>
            <b>Welcome, <span id='tbName'>&nbsp</span></b><br/>
            Acct <a href='javascript:account();'><span id='tbAccId'>&nbsp;</span></a><br/>
            Logged on, updated at <span id='tbDateTime'>&nbsp;</span>
          </div>
        </div>
      </div>
    </div>            
    <iframe style="display: none;" name="securebridge" src="<?echo $secureUrl;?>/tracking_bridge.php">Your browser needs to support iframes</iframe>
  </body>
</html>
     
