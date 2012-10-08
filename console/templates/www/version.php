{% extends "www/base.html" %}

{% block head %}
<style type='text/css'><!--
table td, table th {
  text-align: left;
  padding: 5px 30px;
}

// --></style>
{% endblock head %}

{% block main %}
<? require_once "urls.inc.php"; ?>
<?
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
?>
<br style='clear:both;'/>
<h2>Appliance Version Information</h2>
<p style='text-align: center;'>
  <table border='1'>
    <tr><th>Component</th><th>Revision</th><th>Revision Timestamp</th></tr>
    <tr>
      <td>Console</td>
      <td><?=file_get_contents('{{ Site }}/console/media/revision.txt')?></td>
      <td><?=getRemoteLastModified('{{ Site }}/console/media/revision.txt')?></td>
    </tr>
    <tr>
      <td>Account Service</td>
      <td><?=file_get_contents('{{ Site }}/acct/revision.txt')?></td>
      <td><?=getRemoteLastModified('{{ Site }}/acct/revision.txt')?></td>
    </tr>
    <tr>
      <td>Secure Service</td>
      <td><?=file_get_contents('{{ Site }}/secure/revision.txt')?></td>
      <td><?=getRemoteLastModified('{{ Site }}/secure/revision.txt')?></td>
    </tr>
    <tr>
      <td>Gateway</td>
      <td><?=file_get_contents($GLOBALS['Default_Repository']."/revision.jsp")?></td>
      <td><?=file_get_contents($GLOBALS['Default_Repository']."/revisionTimestamp.jsp")?></td>
    </tr>
  </table>
</p>
{% endblock main %}
