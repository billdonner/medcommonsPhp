<?

require 'template.inc.php';
require_once "urls.inc.php";

function getRemoteLastModified( $uri ) {
      // default
      $unixtime = 0;
      $fp = fopen( $uri, "r" );
      if( !$fp ) {
        return "< unknown >";
      }
      $metaData = stream_get_meta_data( $fp );
      $unixtime = false;
      foreach( $metaData['wrapper_data'] as $response ) {
          if( substr( strtolower($response), 0, 15 ) == 'last-modified: ' ) {
              $unixtime = strtotime( substr($response, 15) );
              break;
          }
      }
      fclose( $fp );
      if($unixtime === false) {
        return "< unknown >";
      }
      else
        return strftime('%Y-%m-%d %H:%M:%S',$unixtime);
  }

$t = template($acTemplateFolder . 'info.tpl.php');

$t->esc('console_revision',
	file_get_contents($acSite . '/console/media/revision.txt'));

$t->set('console_timestamp',
	getRemoteLastModified($acSite . '/console/media/revision.txt'));

$t->esc('account_revision',
	file_get_contents($acSite . '/acct/revision.txt'));
$t->set('account_timestamp',
	getRemoteLastModified($acSite . '/acct/revision.txt'));

$t->esc('secure_revision',
	file_get_contents($acSite . '/secure/revision.txt'));
$t->set('secure_timestamp',
	getRemoteLastModified($acSite . '/secure/revision.txt'));

$t->esc('gateway_revision',
	file_get_contents($GLOBALS['Default_Repository'].'/revision.jsp'));
$t->esc('gateway_timestamp',
	file_get_contents($GLOBALS['Default_Repository'].'/revisionTimestamp.jsp'));

echo $t->fetch();

?>
