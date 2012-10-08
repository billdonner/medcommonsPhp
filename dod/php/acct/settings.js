
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
    var columnDefs = [
        {key:"name", label:"Name", sortable:true},
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
      this.dt = new YAHOO.widget.DataTable(this.cfg.type+"Container", columnDefs, this.ds, {scrollable:false, width: '100%', height:"14em"}); 
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
    var accid = el(mgr.cfg.addElementId).value.replace(/[# -]/g,'');

    if(accid == '') {
      alert('Please enter a 16 digit account id to add to the Group');
      el(cfg.addElementId).focus();
      return;
    }

    if(!accid.match(/^[0-9]{16}$/)) {
      alert('Your entry did not match the expected format. Account IDs are 16 digit numbers.\n\nPlease adjust your entry and try again.');
      el(cfg.addElementId).focus();
      el(cfg.addElementId).select();
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
            el(cfg.type+'msg').innerHTML='This account is already in your '+cfg.tableDesc+'.';
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
    var checks = el(cfg.containerId).getElementsByTagName('input');
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
  var accid = el('accid').value.replace(/[# -]/g,'');
  el('addressmsg').innerHTML = '';
  var url = 'add_address.php?enc='+hex_sha1(getCookie('mc'))+ '&accid='+encodeURIComponent(accid);
  var transaction = YAHOO.util.Connect.asyncRequest('GET',url, { success: function(req,resp,payload) {
    var obj = eval('x = '+req.responseText);
    if(obj.status != 'ok') {
      alert('An error occurred while adding the address: ' + obj.error);
    }
    else {
      el('accid').value = '';
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
       onDeleteSuccess: checkSelfDeleted
  });
  groupTableManager.init();
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
  el('groupmsg').innerHTML = '';
  var url = 'add_group_member.php?enc='+hex_sha1(getCookie('mc'))+ '&accid='+encodeURIComponent(accid);
  YAHOO.util.Connect.asyncRequest('GET',url, { success: function(req,resp,payload) {
    var obj = eval('x = '+req.responseText);
    if(obj.status != 'ok') {
      alert('An error occurred while adding the group member: ' + obj.error);
    }
    else {
      el('maccid').value = '';
      groupTableManager.requery();
    }
    dlg.destroy();
  }}, null); 

  return false;
}

function changeGroupName() {
  var url = 'change_group_name.php?enc='+hex_sha1(getCookie('mc'));
  YAHOO.util.Connect.asyncRequest('POST',url, { success: function(req,resp,payload) {
    el('updatedMsg').style.display = 'inline';
    YAHOO.util.Dom.setStyle('updatedMsg','opacity',1);
    window.setTimeout(function() {
      var anim = new YAHOO.util.Anim(el('updatedMsg'), {opacity: {from: 1, to: 0 } }, 0.5);
      anim.animate();
    },3000);
  }}, 'name='+encodeURIComponent(el('groupName').value));
}
