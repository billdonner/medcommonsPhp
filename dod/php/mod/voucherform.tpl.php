<style type='text/css'>
  form#voucherform input[type=text] {
    width: 178px;
  }
  #addresses {
    float: left;
  }
  ul.addressList {
    padding: 0px !important;
  }
  ul.addressList li {
    list-style-type: none;
  }
</style>
<div id="ContentBoxInterior"  mainId='page_voucher' mainTitle="Create MedCommons Voucher"  >
<h2>Create Voucher</h2>
<?=htmlentities($extrasvcdetails)?>	
<div class=fform>
<h3><?=$r2->servicename?> -- <?=$r2->servicedescription?></h3>
<form action=vouchersetup.php method='post'>
  <?=$svcchooser?>
<?/*
  <div class='field'>
    <span class=n>Optional Patient MCID</span><span class=q><input  type='text' name='mcid' value='<?=$mcid?>'  /></span></span>
  </div>
 */?>
</form>
<form name='voucherform' id='voucherform' action='vouchersetup.php' method='post'>
  <input type=hidden name=svcnum value='<?=$svcnum?>' />
  <div class=inperr id=err><?=htmlentities($v->err)?></div>
  <div class=field><span class=n>Patient Name</span>
  <span class=q><input <?=$patientdisabled?> type=text name=patientname value='<?=htmlentities($v->patientname)?>' />
  <span class=r><?=$acctype?> <?=$mobile?></span>
  <div class=inperr id=patientname_err><?=htmlentities($v->patientname_err)?></div></span></div>
  <div class=field><span class=n>Patient Email</span>
  <span class=q><input <?=$patientdisabled?> type=text name=patientemail value='<?=htmlentities($v->patientemail)?>' />
  <span class=r><?=$emailverified?></span>
  <div class=inperr id=patientemail_err><?=htmlentities($v->patientemail_err)?></div></span></div>

  <div class=field><span class=n>Special Instructions</span><span class=q><input type=text name=addinfo value='<?=htmlentities($v->addinfo)?>' /><span class=r>&nbsp;</span>
  <div class=inperr id=addinfo_err><?=htmlentities($v->addinfo_err)?></div></span></div>

  <div class='field'>
     <span class='n'>Price to Patient</span>
     <span class='q'>
       <input type='text' name='patientprice' <?if($free):?>readonly='true' style='color: #aaa;' title='The price of this voucher can not be edited because it is a free voucher.'<?endif;?> value='<?=$suggestedprice?>' />
       <span class='r'><?if($free):?>Free Voucher<?else:?>e.g. 17.25<?endif;?></span>
       <div class='inperr' id='patientprice_err'><?=htmlentities($v->patientprice_err)?></div>
     </span>
  </div>

  <div class=field><span class=n>Duration</span><span class=q>
  <?=$duration?>
  <span class=r>can be overridden on voucher</span></div>

  <div class=field><span class=n>DICOM credits</span><span class=q>
  <?=$dcredits?>
  <span class=r>this voucher can utilize DICOM uploads</span></div>

  <div class=field><span class=n>FAXIN credits</span><span class=q>
  <?=$fcredits?>
  <span class=r>this voucher can receive FAXIN pages</span></div>

  <div class='field'>
    <span class='n'>Consents</span>
    <span class='q'>
        <div id='addresses'>
          <ul class='addressList'>
            <?foreach($addresses as $a):?>
            <li>
              <input type='checkbox' name='consents[]' 
                     value='<?=$a->accid?>' 
                    <?if(in_array($a->accid,$v->consents) || ($a->accid == $mcid)):?>checked<?endif;?>/>
              &nbsp;<?=htmlentities($a->name)?> (<?=htmlentities($a->email)?>)
            </li>
            <?endforeach;?>
          </ul>
        </div>
    </span>
  </div>

  <div class=field><span class=n>&nbsp;&nbsp;</span><span class=q><input type=submit class='mainwide' value='Create Voucher' />
  <input type=submit value='Cancel' class='altshort' name='cancel' /></span></div>

</form>


</div>
<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Before the patient leaves your premises, print out a customized "MedCommons Voucher" which invites them to pick up their records online. <br/>This paper Voucher can also be used as a fax cover sheet for sending additional records back into the account.
</td></tr>
</table>
</div>

