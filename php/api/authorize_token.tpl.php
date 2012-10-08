  <style type='text/css'>
    body {
      font-family: arial;
    }
    h1 {
      font-size: 12pt;
    }
    #body {
      padding: 0px 0px;
    }
    .smallnote {
      font-size: 10px;
    }

    .accessline {
      margin: 5px 0px;
    }

    .invalid {
      border: solid 2px red;
    }

    #body p {
      margin: 10px 0px;
    }

    .applicationHighlight {
    }

    table#tokentable {
      border-collapse: collapse;
      margin: 15px 0px;
    }


    table#tokentable td {
      text-align: left;
    }

    table#tokentable td, table#tokentable th{
      padding: 2px 8px 2px 0px;
    }

    table#consentstable {
      width: 80%;
      min-width: 600px;
      margin: 8px 0px;
    }

    table#consentstable thead tr {
      background-color: #B5B5B5;
      color: white;
      height: 22px;
    }
    table#consentstable thead tr th, table#consentstable thead tr td {
      padding: 3px 8px;
    }
    table#consentstable td.rights {
      text-align: right;
      vertical-align: middle;
    }

    #consentbox {
      color: black;
      border: solid 1px black;
      background-color: #ffe;
      padding: 2px 8px;
      margin: 12px 0px;
    }
    input,select {
      vertical-align: middle;
    }
    h1 {
      padding-top: 10px;
    }
  </style>
  <h1>Access Requested to HealthURL</h1>
  <div id='body'>
    <form name='authorizeForm' method='post' action='authorize.php'>
      <input type='hidden' name='oauth_callback' value='<?=$callback?>'/>
      <input type='hidden' name='accid' value='<?=$accid?>'/>
      <input type='hidden' name='realm' value='<?=$realm?>'/>
      <input type='hidden' name='oauth_token' value='<?=$token?>'/>
      <?if(isset($validation_error)):?>
        <p style='color: red;'>You did not check the box to allow authorization of access. If you wish to authorize access please check the box
          and click "Authorize".</p>
      <?endif;?>
      <p>Application <span class='applicationHighlight'>"<?=hsc($es->es_identity)?>"</span> is requesting access to HealthURL 
        <a href='<?=$hurl?>' title='HealthURL for Account <?=$accid?>' ><?=$hurl?></a>.</p>

        <div id='consentbox'>
          <p><b>Consent for Application <?=hsc($es->es_identity)?> to Access Account <?=$accid?> (<?=$firstName?> <?=$lastName?>)</b></p>
          <table id='consentstable'>
            <thead>
              <tr class='consentheader'>
                <th><?=hsc($es->es_identity)?> Application</th><th>&nbsp;</th>
                <td class='rights'>
                  <?if(!$realm):?>
                    <select name='rights'>
                      <option value='RW' <?= ($rights=="RW")?"selected='true'" : ""?> >RW</option>
                      <option value='R' <?= ($rights=="R")?"selected='true'" : ""?> >R</option>
                      <option value='W' <?= ($rights=="W")?"selected='true'" : ""?> >W</option>
                      <option value='' <?= ($rights=="")?"selected='true'" : ""?> >None</option>
                    </select>
                    <input name='C' type='checkbox' value='C' checked="true" onclick='alert("This feature is not supported yet.\n\nTo disable control of consents, please select R access level."); return false;'/>Modify Consents
                  <?endif;?>
                </td></tr>
            </thead>
          <?if($realm):?>
            <tbody>
              <tr>
                <td>&nbsp;</td>
                <td><?=hsc($realm)?></td>
                <td class='rights'>
                  <select name='rights'>
                    <option value='RW' <?= ($rights=="RW")?"selected='true'" : ""?> >RW</option>
                    <option value='R' <?= ($rights=="R")?"selected='true'" : ""?> >R</option>
                    <option value='W' <?= ($rights=="W")?"selected='true'" : ""?> >W</option>
                    <option value='' <?= ($rights=="")?"selected='true'" : ""?> >None</option>
                  </select>
                  <input name='C' type='checkbox' value='C' checked="true" onclick='alert("This feature is not supported yet.\n\nTo disable control of consents, please select R access level."); return false;'/>Modify Consents
                </td>
              </tr>
            </tbody>
          <?endif;?>
          </table>
          <p>I authorize access to account <?=$accid?> with the above permissions <input class='<?=isset($validation_error)?"invalid":""?>' type='checkbox' name='authorized' value='true'/></p>
          <p style='text-align: center;'><input type='submit' name='authorize' value='Authorize'/>&nbsp;<input type='submit' name='cancel' value='Cancel'/></p>
        </div>
      <p class='smallnote'>IMPORTANT:  The owner of the affected account will be notified of this change.</p>
    </form>
  </div>
