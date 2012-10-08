<?php
//snmpget system stats
$host = 'mcpurple01.homeip.net';
$community = 'public';
                                                    
//get system name
$sysname = snmpget($host, $community, "system.sysName.0");

//get system uptime
$sysup = snmpget($host, $community, "system.sysUpTime.0");
$sysupre = eregi_replace("([0-9]{3})","",$sysup);
$sysupre2 = eregi_replace("Timeticks:","",$sysupre);
$sysupre3 = eregi_replace("[()]","",$sysupre2);

//get tcp connections
$tcpcon = snmpget($host, $community,"tcp.tcpCurrEstab.0");
$tcpconre = eregi_replace("Gauge32:","",$tcpcon);

echo '
System Name: '.$sysname.'<br>
System Uptime: '.$sysupre3.'<br>
Current Tcp Connections: '.$tcpconre.'<br>';

?>
 