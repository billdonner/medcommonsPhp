<?
require_once "mc.inc.php";
require_once "urls.inc.php";

global $Secure_Url;

/**
 * Patient List Table Template
 *
 * Displays table for worklist.  Needs to be initialized with data from rls.php
 * Called  by both the initial display render (whole page), and dynamic updates
 * (partial page update via AJAX call).
 *
 * Note this only displays the table body.  See rlswidget.tpl.php for the main page
 * in which this is embedded.
 */
?>
<?
  $now = time();
  $odd = false;
  $acctsUrl = gpath("Accounts_Url"); 
  foreach($rows as $l) {
    $odd = (!$odd); // flip polarity
    //guid is a link
    $guid = $l['Guid'];
    $ct = $l['CreationDateTime'];
    $dateTime = $template->formatAge($ct,$now);

    $time = htmlspecialchars(strftime('%H:%M:%S',$ct));
    $date = htmlspecialchars(strftime('%m/%d/%y',$ct));
    if (($lasttime!=0)&&($ct > ($lasttime -100))){
      // still new
      $rowclass = ($odd?"oddnew":"evennew");
    }else {
      $rowclass = ($odd?"odd":"even");
    }
    if($l['ViewStatus']=='Hidden') {
      $hurlImg = 'hurl_bw.png';
      $rowclass .= " hiddenPatient";
      $hidden = true;
    }
    else {
      $hidden = false;
      $hurlImg = 'hurl.png';
    }

    $cc = $l['ConfirmationCode'];
    $rs = $l['RegistrySecret'];
    $purpose = htmlspecialchars($l['Purpose']);
    $xdob = $l['DOB'];
    $viewerurl = $l['ViewerURL']."&a=$accid&at=$auth"; // add our account id and auth token to viewer url
    $serverurl = $l['CXPServerURL'];
    $servervendor=$l['CXPServerVendor'];
    $comment = htmlspecialchars($l['Comment']);
    if($l['PatientIdentifier']) {
      $href = "/".$l['PatientIdentifier'];
      if($l['couponstatus'] && ($l['couponstatus']=='issued'))
        $href.="?c=iv";
    }
    else
      $href = $viewerurl;

    $patientidentifier = $l['PatientIdentifier'];
    if ($patientidentifier=='') $patientidentifier = "-no patientid-";
    $patientidentifiersource = $l['PatientIdentifierSource'];

    $ts = isset($transferState[$patientidentifier]) ? $transferState[$patientidentifier] : false;

    if ($patientidentifiersource=='') $patientidentifiersource = "-no patientid source-";

    $patientName = htmlentities($l['PatientGivenName'],ENT_QUOTES).' '.htmlentities($l['PatientFamilyName'],ENT_QUOTES);

    $messages = "";
    if($ts) {
        $messageCount = 1;
        foreach($ts->messages as $m) {
            $messages .= "  $messageCount.    $m \r\n";
            $messageCount++;
        }
    }

    if($ts && ($ts->ts_progress < 1.0)) {
      // Calculate upload percentage as thousandths
      $perc = round($ts->ts_progress / .125) * 125;
      if($perc >= 1000)
        $perc = 1000;

      // This is cheating a little, but 
      if(($perc > 0) && ($perc < 125))
        $perc = 125;

      // How long running?
      $tsStartTime = $ts->ts_crt;

      $secs = time() - $tsStartTime;
      if($secs > 3600)
        $secs = round($secs/3600) ." hours ";
      else
      if($secs > 60)
        $secs = round($secs/60) ." mins ";
      else
        $secs = "$secs seconds";
    }
    elseif($ts && ($ts->ts_progress >= 1.0)) {
      $perc = 1000;
    }

    if(isset($l['gwUrl']))
      // $downloadDICOMURL = $l['gwUrl']."/ddl/download?ddl.storageid=".urlencode($l['PatientIdentifier'])."&auth=$auth&ddl.guid=".$l['Guid'];
      $downloadDICOMURL = 'javascript:downloadDICOM("'.$l['gwUrl'].'","'.$accid.'","'.$auth.'","'.$l['Guid'].'","'.$l['PatientIdentifier'].'", "'.$Secure_Url.'")';

    if($viewerurl=='') $anchor = "$dateTime"; else $anchor = <<<XXX
      <a title="Open HealthURL CCR for this Patient" target="ccr" href="$href" onclick='if(setGWCookie) setGWCookie("$viewerurl");' >$dateTime</a>
XXX;
?>
    <tr id='row_<?=$patientidentifier?>' class='<?=$rowclass?>'>
      <td title='dob:<?=$xdob?>' class='rlsPn'>
         <a href="<?=$href?>" onclick="return openCcrWindow('<?=$href?>',<?=$hidden ? 'true' : 'false'?>, '<?=$patientidentifier?>');" 
                              target="ccr"><img src='images/<?=$hurlImg?>'/> <?=$patientName?></a>
      </td>
      <td class='rlsTm'><?=$dateTime?></td>
      <td class='rlsPps'><?=$purpose?></td>
      <td align='left' id='r<?=$cc?>' title='Status'>
      <?if($l['couponum']):?>
        <?=$l['couponstatus']?>
      <?elseif($cc):?>
        <input type='text' readonly='true' id='sTxt<?=$cc?>' class='statusInput' style='width: 70px;' value='<?=$l['Status']?>'/>&nbsp;
        <img id='sImg<?=$cc?>' title='Click here to change the status of this record' onclick='editStatus("<?=$cc?>")' class='editStatusImg' src='images/black_arrow_down.gif'/>
      <?else:?>
        &nbsp;
      <?endif;?>
      </td>
      <td class='iconCell'>
        <?if($l['ViewStatus']!='Hidden'): // Visible record ?>
          <a class='deleteLink' href='javascript:hidePatient("<?=$patientidentifier?>")' title='Hide Patient <?=pretty_mcid($patientidentifier)?>'>X</a>
        <?else: // Hidden record ?>
          &nbsp;
        <?endif;?>

        <?if($ts && ($ts->ts_status == 'Uploading') && ($ts->ts_progress<=1.0)):?>
          <img xtitle='Data is uploading for this patient <?=round($ts->ts_progress*100)?>% complete, running for <?=$secs?><?"\r\n"?>'
               class='transferImg'
               onmouseover='showTransferTip(this);'
               transferKey='<?=$ts->ts_key?>'
               src='images/uploading.gif' />
          <?if($perc<1000):?><img title='Upload <?=round($ts->ts_progress*100)?>% complete, running for <?=$secs?>' 
               src='images/prog<?=$perc?>.gif'
               width='16'
               height='16'/>
          <?endif;?>
        <?elseif($ts && ($ts->ts_status == 'Downloading') && ($ts->ts_progress<1.0)):?>
          <img xtitle='Data is downloading for this patient <?=round($ts->ts_progress*100)?>% complete, running for <?=$secs?>'
               class='transferImg'
               onmouseover='showTransferTip(this);'
               transferKey='<?=$ts->ts_key?>'
               src='images/downloading.gif' />
          <img title='Download <?=round($ts->ts_progress*100)?>% complete, running for <?=$secs?>'
               src='images/prog<?=$perc?>.gif'
               width='16'
               height='16'/>
        <?elseif($l['wi_available_id']):?>
          <a href='<?=$downloadDICOMURL?>'><img title='Data is available to download for this patient' src='images/download_available.png'/></a>
        <?elseif($l['wi_downloaded_id']):?>
         <a href='<?=$downloadDICOMURL?>'><img title='Data has been downloaded for this Patient' src='images/downloaded.png'/></a>
        <?endif;?>
        <?if($messages):?>
         <a href='javascript:showMessages("<?=htmlentities($l['PatientIdentifier'],ENT_QUOTES)?>")'
            ><img title='<?=count($messages)?> message(s) logged for this transfer: <?="\r\n\r\n".$messages?>' style='top: -2px;' src='images/microinfo.png'/></a>
        <?endif;?>
        </td>
        <td class='iconCell'>
          <?if($l['couponum']):?>
          <a href='/mod/voucherprint.php?c=<?=$l['couponum']?>&reprint'
             onclick='return showVoucher(<?=$l['couponum']?>);'
            ><img src='images/voucher.png'
                  onmouseover='this.src="images/voucher_hi.png"' onmouseout='this.src="images/voucher.png"'/></a>
        <?endif;?>
      </td>
    </tr>
<? } ?>
  <?if(count($rows) == 0):?>
    <tr><td colspan='6'><p>No Entries Available</p></td></tr>
  <?endif;?>
  <tr><td colspan='6'>&nbsp;</td></tr>
  <tr id='registryTableBottomRow' messageTimestamp='<?=$messageTimestamp?>'>
    <td style='text-align: left;' colspan='2'>
    Showing <?=$displayedCount?> of <?=$allCount?> Patients
    <?if($allCount != $visibleCount):?> 
      (<?=$allCount-$visibleCount?> hidden <a href='javascript:toggleShowHiddenPatients()'>show all</a>)
    <?elseif($showHidden):?>
      (<a href='javascript:toggleShowHiddenPatients();'>hide hidden</a>)
    <?endif;?>
    </td>
    <td align='right' colspan='4' id='pageLinks'><?=$pageLinks?></td>
  </tr>
