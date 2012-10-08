<style type='text/css'>
  #addresses {
    float: left;
  }
  ul.addressList {
    padding: 0px !important;
  }
  ul.addressList li {
    list-style-type: none;
  }
  #addAC ul {
    padding: 0px;
    margin: 0px;
  }
  #addAC {
    left: 23em;
    width: 15em;
    clear: both;
    display: none;
  }
  input#addInput, #accontainer {
    font-size: 11px;
  }
  input#shareAccount {
    width: 15em;
    position: relative;
  }
</style>
<? $self ='http://'.$_SERVER['HTTP_HOST']; ?>
<div id="CBIWide"  mainId='page_setup' mainTitle="Services - MedCommons on Demand"  >
<?=$svclist?>
<a name='edit'/>
<table class=tinst>
<td class=lcol >Service Request Form Permalink: </td><td class=rcol >
<b><a href='provider.php?id=<?=$accid?>'><?=$self?>/mod/provider.php?id=<?=$accid?></a></b><br/>
Doctor: Paste this link into emails or your patient portal web site - it needs no added security. Try it as if you were the patient. <br/>
When the patient presents the signed form, enter the code on your Dashboard, price the service and print a Voucher for the patient to take away.  <br/>
To complete the Service, click on the 'issued' item on your Dashboard Patient List, add a PDF (there are Fax In, CCR editing and DICOM options as well) 
and click 'Complete'. The patient will be able to pay for and pick up the completed records on line.		<br/>
</td></tr>
</table>
<hr/>
<?=$h2?>
<div id=addsvc class=fform <?=isset($showform) && !$showform ? " style='display: none;'":""?> >
<form action=svcsetup.php method=post name='svcsetupform'>
<input type=hidden name=editslot value ='<?=$editslot?>'/>
<?=$v->err?>
<div class='field'><span class='n'>Service Name</span><span class='q'>
<input type=text name=servicename value='<?=$v->servicename?>' /><span class='r'>shown on voucher</span>
<div class='inperr' id=servicename_err><?=$v->servicename_err?></div></span></div>

<div class='field'><span class='n'>Service Description</span><span class='q'>
<input type=text name=servicedescription value='<?=htmlentities($v->servicedescription)?>' /><span class='r'>shown on voucher</span>
<div class='inperr' id=servicedescription_err><?=htmlentities($v->servicedescription_err)?></div></span></div>

<div class='field'><span class='n'>Suggested Price</span><span class='q'>
<input type=text name=suggestedprice value='<?=$v->suggestedprice?>' /><span class='r'>e.g. 17.25, can be overriden</span>
<div  class='inperr' id=suggestedprice_err><?=$v->suggestedprice_err?></div></span></div>
<div class='field'><span class='n'>Printed Voucher HTML </span><span class='q' ><a onclick='toggle("vph")'> <?=$bluec?> </a></span>
<span class='r'><a href=vouchercustomize.php>Customization Instructions</a></span>
<span class='closed' id=vph style="display:none;" >
<textarea  name="voucherprinthtml"  cols=60 rows=15 maxlength="1250"><?=$v->voucherprinthtml?></textarea></span>
</div>
<div class='field'><span class='n'>Duration</span><span class='q'>
<?=$duration?>
<span class='r'>can be overriden on voucher</span></span></div>
<div class='field'><span class='n'>DICOM credits</span><span class='q'>
<?=$dcredits?>
<span class='r'>this voucher can utilize DICOM uploads</span></span></div>
<div class='field'><span class='n'>FAXIN credits</span><span class='q'>
<?=$fcredits?>
<span class='r'>this voucher can receive FAXIN pages</span></span></div>

<div class='field'>
  <span class='n'>Consents</span>
  <span class='q'>
      <div id='addresses'>
        <ul class='addressList'>
          <?foreach($addresses as $a):?>
          <li>
          <input type='checkbox' name='consents[]' value='<?=$a->accid?>' <?if(in_array($a->accid,$v->consents)):?>checked<?endif;?>/>&nbsp;<?=htmlentities($a->name)?> (<?=htmlentities($a->email)?>)
          </li>
          <?endforeach;?>
          <li><a href='../acct/settings.php?page=addresses' onclick='return check_modified();'>Go to Address Book</a></li>
        </ul>
      </div>
  </span>
</div>
<br style='clear: both;'/>

<div class=field><span class=n>&nbsp;</span><span class=q><input type=submit class=mainwide value='<?=$buttval?>' />&nbsp;<input type=submit class='altshort' name=cancel  value='Cancel' /></span></div>
</form>
<table class=tinst>
<td class=lcol >Instructions</td><td class=rcol ><?=$tinst?></td></tr>
</table>
</div>
<?=$sharewith?>
</div>

<script type='text/javascript'>
var modified = <?=$is_postback ? 'true' : 'false' ?>;
window.onload=function() {
<?
if(isset($scrolltoform) && $scrolltoform):?> 
    location.hash = '#editsvc';
<?endif;?>
  if((location.hash == '#edit') || (location.hash == '#editsvc')) {
    document.svcsetupform.servicename.focus();
    document.svcsetupform.servicename.select();
  }
  document.body.className = 'yui-skin-sam'; // because header is hard coded
  var f = document.svcsetupform;
  for(var i=0; i<f.elements.length; ++i) {
    f.elements[i].onchange = function() { modified = true; };
  }
}
function check_modified() {
  return !modified || confirm('If you leave this page now you will lose edits you have made to this form.\n\nContinue?');
}
</script>
