<script type="text/javascript">
  var closeTimer = null;
  function updateContents(req) {
    $('patientDetails').innerHTML = req.responseText;
  }
  function reloadGW() {
    doSimpleXMLHttpRequest('home.php?template=pdframe').addCallback(updateContents);
  }
  function setGWCookie(url) {
    var c = (url? url + ";<?=$info->accid?>" : '');
    setCookie("mcgw",c, null, "/",<?= ($sd=='') ? 'null' : '".'.$sd.'"'?>);
    log('set gw to ' + url);
    return true;
  }
  ce_connect('closeCCR',function(guid) {
    closeTimer = setTimeout(reloadGW,3000);
    setGWCookie(null);
  });
  ce_connect('clearGateway',function(gwUrl) {
    setGWCookie(null);
    reloadGW();
  });
  ce_connect('openCCR',function(guid,gwUrl) {
    // don't reload if no gwurl provided.  otherwise this may cause actual current gwUrl to get lost.
    if((guid != 'import') && gwUrl) { 
      setGWCookie(gwUrl);
      reloadGW();
    }
    if(closeTimer != null) {
      window.clearTimeout(closeTimer);
      closeTimer = null;
    }
  });
  ce_connect('openCCRUpdated', function() {
    if(!document.getElementById('patientDetailFrameMarker')) {
      reloadGW();
    }
  });
</script>
