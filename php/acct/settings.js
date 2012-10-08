
/**
 * A wrapper around the DataTable that makes it very simple
 * to create a 100% width table in the settings page that shows
 * a list of accounts sourced from JSON data delivered by XHR.
 *
 * @param cfg contains properties: 
 *    type         - type of entity to be queried (EG: 'group', 'address')
 *    tableDesc    - describes the table, used in messages (EG: 'group', 'address book')
 *    url          - url of json service.  Arguments to randomize and 
 *                   add enc security token will be added
 *    addElementId - id of input field where user enters accounts to add
 *    containerId  - id of container (div) in which table is rendered
 *    onAdd        - callback when user clicks add with valid account
 *    deleteUrl    - URL of service for deleting members
 *    onBeforeDelete - optional callback to allow checking of account 
 *                     before deleting from table 
 *    onDeleteSuccess - optional callback invoked when delete succeeds
 */
function SettingsTableManager(cfg) {
  this.cfg = cfg;
  this.ds = null;
  this.dt = null;
  var yevt = YAHOO.util.Event;
  var mgr = this;

  this.init = function() {
    log("init table manager");

    var nameDef = null;
    var columnDefs = [
        nameDef = {key:"name", label:"Name", sortable:true},
        {key:"email", label:"Email", sortable:true},
        {key:"accid", label:"Account ID", sortable:true},
        {key:"accid", label:"<img id='delete"+this.cfg.type +"MemberImg'"
          + " onclick='__"+cfg.type+"_delete()'"
          + " class='clickable' title='Delete Checked Entries' src='images/trash.gif'/>", 
          formatter: function(cell, record, col, data) {   
            cell.innerHTML='<input type="checkbox" value="'+ data + '"/>';
          }, sortable: false
        }
    ];

    if(this.cfg.nameFormatter) {
        nameDef.formatter = this.cfg.nameFormatter;
    }

    // Need this horrible hack because YUI does someting strange that prevents
    // us from attaching to the events on the img inside the table header
    window['__'+cfg.type+'_delete'] = function() {
      mgr.deleteMember();
    }

    // Set the responseType as JSON
    this.ds = new YAHOO.util.DataSource(this.getUrl());
    this.ds.responseType = YAHOO.util.DataSource.TYPE_JSON;

    // Define the data schema
    this.ds.responseSchema = {
        resultsList: "members", // Dot notation to results array
        fields: ["name","email","accid"]
    };

    try {
      var tableProperties = {scrollable:false, width: '100%', height:"14em"};
      if(this.cfg.formatRow) {
          tableProperties.formatRow = this.cfg.formatRow;
      }
      this.dt = new YAHOO.widget.DataTable(this.cfg.type+"Container", columnDefs, this.ds, tableProperties);
    }
    catch(e) {
      dumpProperties('Failed to display table', e);
    }
    yevt.addListener('add'+this.cfg.type+'MemberButton', "click", function() { mgr.checkAdd(); });
    yuiLoader();
  }

  this.requery = function() {
    mgr.ds.liveData = mgr.getUrl();
    mgr.ds.sendRequest('',{ success: mgr.dt.onDataReturnInitializeTable, scope: mgr.dt });
  }

  this.checkAdd = function() {

    // Kill spaces etc.
    var accid = $(mgr.cfg.addElementId).value.replace(/[# -]/g,'');

    if(accid == '') {
      alert('Please enter a 16 digit account id to add to the Group');
      $(cfg.addElementId).focus();
      return;
    }

    if(!accid.match(/^[0-9]{16}$/)) {
      alert('Your entry did not match the expected format. Account IDs are 16 digit numbers.\n\nPlease adjust your entry and try again.');
      $(cfg.addElementId).focus();
      $(cfg.addElementId).select();
      return;
    }

    // Check if already in table
    var trs = mgr.dt.getBody().getElementsByTagName('tr');
    var dupe = null;
    for(var i = 0; i<trs.length; ++i) {
       var tr = trs[i];
       if(tr.id) {
          var rec = mgr.dt.getRecordSet().getRecord(tr.id).getData();
          if(rec.accid == accid) {
            $(cfg.type+'msg').innerHTML='This account is already in your '+cfg.tableDesc+'.';
            if(YAHOO.env.ua.ie>0) {
              YAHOO.util.Dom.addClass(tr,'highlight');
            }
            else {
              var oldColor = YAHOO.util.Dom.getStyle(tr,'backgroundColor');
              var anim = new YAHOO.util.ColorAnim(tr, {backgroundColor: { to: '#ffd490' } }, 0.3);
              anim.onComplete.subscribe(function() { 
                this.attributes.backgroundColor.to = oldColor; 
                this.onComplete.unsubscribeAll(); 
                this.animate(); 
                });
              anim.animate();
            }
            // tr.scrollIntoView(); - needed if we enable scrollable table
            // window.scrollTo(0,0);
            dupe = tr;
          }
          else
            YAHOO.util.Dom.removeClass(tr,'highlight');
       }
    }

    if(dupe)
      return;

    // Things look ok - proceed to real add
    cfg.onAdd(accid);
  }

  this.deleteMember = function() {
    var checks = $(cfg.containerId).getElementsByTagName('input');
    var checked = [];
    for(var i = 0; i<checks.length; ++i) {
      var c = checks[i];
      if(c.checked) 
        checked.push(c.value);
    }

    if(checked.length == 0) {
      alert('Please select one or more entries to delete!');
      return;
    }

    if(cfg.onBeforeDelete && !cfg.onBeforeDelete(checked)) 
      return;

    var url = cfg.deleteUrl+'?enc='+hex_sha1(getCookie('mc'));
    YAHOO.util.Connect.asyncRequest('POST',url, { success: function(req,resp,payload) {
      var obj = eval('x = '+req.responseText);
      if(obj.status == 'ok')  {
        if(cfg.onDeleteSuccess)
          cfg.onDeleteSuccess(checked);
        mgr.requery();
      }
      else
        alert('An error occurred while deleting the '+cfg.type+': ' + obj.error);

    }},'accid='+encodeURIComponent(checked.join(',')));
  }

  this.getUrl = function() {
    return mgr.cfg.url + "?enc="+hex_sha1(getCookie('mc'))+'&rand='+hex_sha1('x'+Math.random()+''+(new Date().getTime()))+'x&';
  }
}

/**
 * Utilities function
 */
  var el = function(e,a) {
     e = new YAHOO.util.Element(e,a);
     YAHOO.lang.augmentObject(e.DOM_EVENTS,{change:true,focus:true,blur:true});
     return e;
  };

/**
 * Javascript specific to Address Book
 */
var addressTableManager = null; 
function initAddresses() {
  addressTableManager = new SettingsTableManager({ 
      type: 'address', 
       url: 'addresses.php',
       addElementId: 'accid',
       tableDesc: 'addresss book',
       onAdd:     confirmAddAddress,
       containerId: 'addressContainer',
       deleteUrl: 'delete_address.php'
  });
  addressTableManager.init();
}

function confirmAddAddress(accid) {
  var url = 'query_address_details.php?accid='+encodeURIComponent(accid) + '&enc='+hex_sha1(getCookie('mc'));
  var transaction = YAHOO.util.Connect.asyncRequest('GET',url, 
  { 
    success: function(req,resp,payload) {
      if(req.responseText.match(/^\s*Failed\s*$/)) {
        alert('The account you entered could not be found.\n\nPlease check your entry and try again.');
        return;
      }
      yuiLoader().insert(function() {
        var dlg = new YAHOO.widget.SimpleDialog('confirmAddressDlg', { 
            width: '500px',
            fixedcenter:true,
            modal:true,
            visible:true,
            draggable:true,
            buttons: [ {text: 'OK - Add this Address', handler: function(){addAddress(accid,this);}}, 
                       { text: 'Cancel', handler: function(){this.destroy();}} ]
        });
        dlg.setHeader('Add Address - Confirmation');
        dlg.setBody('<p>Do you want to add the following address to your Address Book?</p><br/>'+req.responseText);
        dlg.render(document.body);
      });
    }
  });
}

function addAddress(accid,dlg) {
  var accid = $('accid').value.replace(/[# -]/g,'');
  $('addressmsg').innerHTML = '';
  var url = 'add_address.php?enc='+hex_sha1(getCookie('mc'))+ '&accid='+encodeURIComponent(accid);
  var transaction = YAHOO.util.Connect.asyncRequest('GET',url, { success: function(req,resp,payload) {
    var obj = eval('x = '+req.responseText);
    if(obj.status != 'ok') {
      alert('An error occurred while adding the address: ' + obj.error);
    }
    else {
      $('accid').value = '';
      addressTableManager.requery();
    }
    dlg.destroy();
  }}, null); 

  return false;
}

/**
 * Javascript specific to Group Members Table
 */
var groupTableManager = null; 
function initGroups() {
  groupTableManager = new SettingsTableManager({ 
      type: 'group', 
       url: 'query_groups.php',
       addElementId: 'maccid',
       tableDesc: 'group',
       onAdd:     confirmAddGroup,
       containerId: 'groupContainer',
       deleteUrl: 'delete_group_member.php',
       onBeforeDelete: checkDeleteSelf,
       onDeleteSuccess: checkSelfDeleted,
       formatRow: formatGroupMemberRow,
       nameFormatter: function(cell, record, col, data) {
           if(record.getData('name') == null)
               cell.innerHTML = 'Invitation Sent';
           else
               YAHOO.widget.DataTable.formatText(cell,record,col,data);
       }
  });
  groupTableManager.init();
  var yevt = YAHOO.util.Event;
  yevt.addListener('inviteGroupMemberButton', "click", inviteGroupMember);
}

function formatGroupMemberRow(tr,rec) {
    if(rec.getData('name') == null) {
        YAHOO.util.Dom.addClass(tr, 'pending');
    }
    return true;
}

function inviteGroupMember() {
  yuiLoader().insert(function() {
    var dlg = new YAHOO.widget.SimpleDialog('inviteEmailsDlg', {
        width: '500px',
        fixedcenter:true,
        modal:true,
        visible:true,
        draggable:true,
        buttons: [ {text: 'OK - Invite these People', handler:submitGroupInvite},
                   { text: 'Cancel', handler: function(){this.destroy();}} ]
    });
    dlg.setHeader('Invite to Group by Email');
    dlg.setBody('<div id="inviteDlgBody">'
              + '<p>Enter Email Addresses to invite to this Group:</p>'
              + '<ol id="emailInputs">'
              + '<li><input class="inviteEmail" type="text" value=""/></li>'
              + '</ol></div>');
    dlg.render(document.body);
    var inp = el('inviteEmailsDlg').getElementsByClassName('inviteEmail')[0];
    el(inp).on('keydown', function(e){checkAddEmptyEmailField(e,el(inp));});
    setTimeout(function() {
        inp.select();
        inp.focus();
        el('inviteEmailsDlg').getElementsByClassName('container-close')[0].tabIndex=30;
    },0);
  });
}

function submitGroupInvite() {

    var dlg = this;

    // Get the email addresses
    var emails = [];
    var inps = el('inviteEmailsDlg').getElementsByClassName('inviteEmail');
    for(var i=0; i<inps.length; ++i) {
        var inp = inps[i];
        if(el(inp).hasClass('pending'))
            continue;
        if(inp.value.replace(/[ \t]/,'')=='')
            continue;
        emails.push(inps[i].value);
    }
    if(emails.length == 0) {
        alert("Please enter one or more email addresses to send to!")
        return;
    }

    var url = 'send_group_invites.php?&enc='+hex_sha1(getCookie('mc'));
    YAHOO.util.Connect.asyncRequest('POST',url, { success: function(req,resp,payload) {
      log("got response: " + req.responseText);
      var obj = eval('x = '+req.responseText);
      if(obj.status == 'ok')  {
          alert('Email invites have been sent!');
          groupTableManager.requery();
          dlg.destroy();
      }
      else
        alert('An error occurred while sending invitation emails: ' + obj.error);

    }, failure: function() { alert('A problem occurred sending invitation emails.');}},
    'emails='+encodeURIComponent(emails.join(',')));
}

function checkAddEmptyEmailField(e,me) {

    var code = YAHOO.util.Event.getCharCode(e);

    // Ingore tabs since they come when the user is trying to tab
    // off the field rather than modify it
    if(code == 9) 
        return;

    me.removeClass('pending');

    // Is there an empty field?
    var inps = el('inviteEmailsDlg').getElementsByClassName('pending');
    if(inps.length >0) 
        return;

    // If we got here then there is no empty field - add one
    var inp = el(document.createElement('input'), {
        className:'inviteEmail pending',
        type: 'text',
        value: 'tab to enter additional address'
    });
    var li =  el(document.createElement('li'), {});
    li.appendChild(inp);
    el('emailInputs').appendChild(li);
    inp.on('focus', function() {initEmailField(inp);});
}

function initEmailField(inp) {
    log("init");
    // inp.set('value','');
    inp.unsubscribeAll('focus');
    inp.get('element').focus();
    inp.get('element').select();
    inp.on('keydown', function(e){checkAddEmptyEmailField(e,inp);});
}

function checkDeleteSelf(accids) {
  log('checking accids to be deleted ...');
  for(var i=0; i<accids.length; ++i) {
    log('deleting accid ' + accids[i]);
    if(accids[i] == user.accid) {
      return confirm('You are removing yourself from your active group.\n\n'+
                     'If you continue, you will lose access to the group and will no longer be able to add or remove members.\n\n' +
                     'Are you sure you want to continue?');
    }
  }
  return true;
}

function checkSelfDeleted(accids) {
  log('checking accids to be deleted ...');
  for(var i=0; i<accids.length; ++i) {
    log('deleting accid ' + accids[i]);
    if(accids[i] == user.accid) {
      location.href='?page=groups';
    }
  }
}

function confirmAddGroup(accid) {
  var url = 'query_address_details.php?accid='+encodeURIComponent(accid) + '&enc='+hex_sha1(getCookie('mc'));
  var transaction = YAHOO.util.Connect.asyncRequest('GET',url, 
  { 
    success: function(req,resp,payload) {
      if(req.responseText.match(/^\s*Failed\s*$/)) {
        alert('The account you entered could not be found.\n\nPlease check your entry and try again.');
        return;
      }
      yuiLoader().insert(function() {
        var dlg = new YAHOO.widget.SimpleDialog('confirmAddressDlg', { 
            width: '500px',
            fixedcenter:true,
            modal:true,
            visible:true,
            draggable:true,
            buttons: [ {text: 'OK - Add this Person', handler: function(){addGroupMember(accid,this);}}, 
                       { text: 'Cancel', handler: function(){this.destroy();}} ]
        });
        dlg.setHeader('Add Group Member - Confirmation');
        dlg.setBody('<p>Do you want to add the following person to this Group?</p><br/>'+req.responseText);
        dlg.render(document.body);
      });
    }
  });
}

function addGroupMember(accid,dlg) {
  $('groupmsg').innerHTML = '';
  var url = 'add_group_member.php?enc='+hex_sha1(getCookie('mc'))+ '&accid='+encodeURIComponent(accid);
  YAHOO.util.Connect.asyncRequest('GET',url, { success: function(req,resp,payload) {
    var obj = eval('x = '+req.responseText);
    if(obj.status != 'ok') {
      alert('An error occurred while adding the group member: ' + obj.error);
    }
    else {
      $('maccid').value = '';
      groupTableManager.requery();
    }
    dlg.destroy();
  }}, null); 

  return false;
}

function changeGroupName() {
  var url = 'change_group_name.php?enc='+hex_sha1(getCookie('mc'));
  YAHOO.util.Connect.asyncRequest('POST',url, { success: function(req,resp,payload) {
    $('updatedMsg').style.display = 'inline';
    YAHOO.util.Dom.setStyle('updatedMsg','opacity',1);
    window.setTimeout(function() {
      var anim = new YAHOO.util.Anim($('updatedMsg'), {opacity: {from: 1, to: 0 } }, 0.5);
      anim.animate();
    },3000);
  }}, 'name='+encodeURIComponent($('groupName').value));
}
