<div id="ContentBoxInterior" mainTitle="Services Request">
<h2>Services Request</h2>

<div class=fform>
<form action=provider.php method=post>
<input type=hidden name=repost value=''>
<input type=hidden name=id value='<?=htmlentities($id)?>'>
<div class=inperr id=err>
<?=htmlentities($v->err)?>
</div>
<h4>Patient</h4>
<div class=field><span class=n>Name</span>
<span class=q><input type=text name=name value='<?=htmlentities($v->name)?>' /><span class=r>firstname and lastname please</span>
<div class=inperr id=name_err><?=htmlentities($v->name_err)?></div></span>
</div>
<div class=field><span class=n>Date of Birth</span>
<span class=q><input type=text name=dob value='<?=htmlentities($v->dob)?>' /><span class=r>optional, mm/dd/yyyy</span>
<div class=inperr id=dob_err><?=htmlentities($v->dob_err)?></div></span>
</div>
<div class=field><span class=n>Email</span>
  <span class=q><input type=text name=email value='<?=htmlentities($v->email)?>' /><span class=r>eg bilbo@baggins.com</span>
  <div class=inperr id=email_err><?=htmlentities($v->email_err)?></div></span>
</div>

<h4>Choose Service</h4>
<?if(isset($v->service_err)):?>
<span class='field'><span class='q'><div class='inperr'><?=htmlentities($v->service_err)?></div></span></span>
<?endif;?>
<?foreach($services as $r):?>
  <div class=field>
    <span class=n><?=htmlentities($r->servicename)?></span>
    <span class=q>
    <input type='radio' name='svcmenu' value='<?=$r->svcnum?>' <?=($svcnum==$r->svcnum)?' checked="checked" ':''?> /><span class=r>&nbsp;</span>
    </span>
  </div>
<?endforeach;?>
<?if(isset($v->note_err)):?>
<span class='field'><span class='q'><div class='inperr'><?=htmlentities($v->note_err)?></div></span></span>
<?endif;?>
<div  class=field><span class=n>Additional Instructions</span><span class=q >
<textarea  name="note"   cols=50 rows=6 onkeypress='if(this.value.length>250) {this.value=this.value.substring(0,250);return false;} else return true;'><?=htmlentities($v->note)?></textarea></span>
</div>

<div class=field><span class=n>&nbsp;&nbsp;</span>
<span class=q><input type=submit class='mainwide'
value='Print Request' /><span class=r>&nbsp;</span></span>
</div>
</form>
</div>

<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Fill out this form 
    to take with you to your healthcare provider.
<br/>This request will remain online for 90 days</td></tr>
</table>
</div>

