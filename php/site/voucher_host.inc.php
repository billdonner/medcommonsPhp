<?

require_once "site_config.php";
require_once "mc.inc.php";

  /**
   * Decodes the given voucher id and converts it into a host name
   */
  function locate_voucher($voucherid) {
    global $SOLOHOST;
    global $SOLOPROTOCOL;
    global $VOUCHER_ID_SIZE;
    global $CLUSTER_PREFIX;
    $serverid = decode_voucher_id($voucherid);
    $proto = (isset($SOLOPROTOCOL) && $SOLOPROTOCOL) ? $SOLOPROTOCOL : 'https';
    if($serverid !== false) {
      $host = $_SERVER['HTTP_HOST'];
      $pos = strpos ($host,'.');
      $redirserver = $proto.'://'.$CLUSTER_PREFIX.sprintf('%04s',$serverid).substr($host,$pos);
    }
    else {
      $redirserver = $proto.'://'.$SOLOHOST; // if Z then just try this very host
    }
   // return "http://tenth.medcommons.net";
    return $redirserver;
  }
?>
