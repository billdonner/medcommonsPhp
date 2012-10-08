<?php
// build WHERE clause for select statement based on the arguments
// added $gid multiplexing on groupid
$where = "WHERE groupinstanceid = '$gid' "; $wc = 1;
if ($pfn!='') $where.= (($wc++ != 0)?" AND ":"" )."PatientFamilyName LIKE  '$pfn' ";
if ($pgn!='') $where.= (($wc++ != 0)?" AND ":"" )."PatientGivenName LIKE '$pgn' ";
if ($pid!='') $where.= (($wc++ != 0)?" AND ":"" )."'$pid'=PatientIdentifier";
if ($pis!='') $where.= (($wc++ != 0)?" AND ":"" )."'$pis'=PatientIdentifierSource";
if ($cc!='')  $where.= (($wc++ != 0)?" AND ":"" )."'$cc'=ConfirmationCode";
if ($dob!='') $where.= (($wc++ != 0)?" AND ":"" )."'$dob'=DOB";
if ($spid!='') 
             $where.= (($wc++ != 0)?" AND ":"" )."'$spid'=SenderProviderId";
if ($rpid!='') 
			 $where.= (($wc++ != 0)?" AND ":"" )."'$rpid'=ReceiverProviderId";

if ($wc!=0) $whereclause = $where; else $whereclause='';
?>