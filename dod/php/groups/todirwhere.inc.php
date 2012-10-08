<?php
// build WHERE clause for select statement based on the arguments
$where = ""; $wc = 0;
if ($xid!='') $where.= (($wc++ != 0)?" AND ":"" )."xid LIKE  '$xid' ";
if ($ctx!='') $where.= (($wc++ != 0)?" AND ":"" )."ctx LIKE '$ctx' ";
if ($alias!='') $where.= (($wc++ != 0)?" AND ":"" )."'$alias'=alias";
if ($accid!='') $where.= (($wc++ != 0)?" AND ":"" )."'$accid'=accid";
if ($wc!=0) $whereclause = "WHERE $where"; else $whereclause='';
?>