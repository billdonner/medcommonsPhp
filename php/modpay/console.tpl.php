<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/DTD/strict.dtd">
<!-- 
  Copyright MedCommmons Inc. 2008
 -->
<html>
<head>
    <title>MedCommons Payments Console</title>
    <!--CSS file (default YUI Sam Skin) -->
    <link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.5.1/build/datatable/assets/skins/sam/datatable.css">
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.1/build/button/assets/skins/sam/button.css"> 

    <!-- Dependencies -->
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/yahoo-dom-event/yahoo-dom-event.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/element/element-beta-min.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/datasource/datasource-beta-min.js"></script>

    <!-- OPTIONAL: External JSON parser from http://www.json.org/ (enables JSON validation) -->
    <script type="text/javascript" src="http://www.json.org/json.js"></script>

    <!-- OPTIONAL: Connection (enables XHR) -->
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/connection/connection-min.js"></script>

    <!-- OPTIONAL: Drag Drop (enables resizeable columns) -->
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/dragdrop/dragdrop-min.js"></script>

    <!-- OPTIONAL: Calendar (enables calendar editors) -->
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/calendar/calendar-min.js"></script>

    <!-- Source files -->
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/datatable/datatable-beta-min.js"></script>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/button/button-min.js"></script> 
    <script type="text/javascript" src="sha1.js"></script> 

    <script type='text/javascript'>
      var activeBillingId = null;
      var activeColumn = null;

      function init() {
        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("tokenTable"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
            fields: [{key:"Token"},
                    {key:"Account ID"},
                    {key:"User"},
                    {key:"Fax", parser:YAHOO.util.DataSource.parseNumber},
                    {key:"DICOM", parser:YAHOO.util.DataSource.parseNumber},
                    {key:"Accounts", parser:YAHOO.util.DataSource.parseNumber}
            ]
        };

        var myColumnDefs = [
            {key:"Token", sortable:true},
            {key:"Account ID", sortable:true},
            {key:"User", sortable:true},
            {key:"Fax", sortable:true, formatter:'number', editor:"textbox", editorOptions:{validator:YAHOO.widget.DataTable.validateNumber}},
            {key:"DICOM", sortable:true, formatter:'number', editor:"textbox", editorOptions:{validator:YAHOO.widget.DataTable.validateNumber}},
            {key: "Accounts", sortable:true, formatter:'number', editor:"textbox", editorOptions:{validator:YAHOO.widget.DataTable.validateNumber}}
        ];

        var myDataTable = new YAHOO.widget.DataTable("tokenTableWrapper", myColumnDefs, myDataSource,
          { 
            sortedBy: {key: "Token", dir:"desc"},
            paginated: true,
            paginator: {
              containers: null,
              currentPage: 1,
              rowsPerPage: 20 
            }

          });

        myDataTable.subscribe("cellClickEvent", function(evt) {
          var target = YAHOO.util.Event.getTarget(evt);
          activeColumn = this.getColumn(target).key;
          activeBillingId = this.getRecord(target).getData('Token');
          this.onEventShowCellEditor(evt);
        }); 

        myDataTable.subscribe("editorSaveEvent", function(evt) {
          var editor = evt.editor;
          // alert('You changed value from ' + evt.oldData + ' to ' + evt.newData + ' for billing id ' + activeBillingId + ' and column ' + activeColumn);
          YAHOO.util.Connect.asyncRequest('GET', '?update&billingId='+activeBillingId+'&value='+evt.newData+'&counter='+activeColumn, 
              { success: function(r) {if(r.responseText!="ok") alert(r.responseText); }, failure: function(r) { alert(r.responseText); } });
        }); 

        document.getElementById('yui-dt-pagselect0').style.display='none';

        var b = new YAHOO.widget.Button( "addbutton", { type: "button", name: "add", value: "add" });
        b.addListener("click", function() {
          document.addAccountForm.btk.value=hex_sha1('medcommons'+(new Date()).getTime());
          alert('adding user with billing token '+ document.addAccountForm.btk.value);
          YAHOO.util.Connect.setForm(document.addAccountForm);
          YAHOO.util.Connect.asyncRequest('GET', 'wsCounters.php', { success: function(r) {
            alert(r.responseText); 
            window.location.href=window.location.href; 
          }}); 
        });
        
      }
    </script>
    <style type='text/css'>
      * {
       font-family: arial;
      }
      body {
        font-size: 10pt;
      }
    </style>
</head>
<body class="yui-skin-sam" onload="init();">
  <h1>MedCommons PrePay Counters</h1>
  <p>Click in table to update values</p>

<form name='addAccountForm'>
  <input type='hidden' name='btk' value=''/>
  <input type='hidden' name='pc' value=''/>
  <input type='hidden' name='ak' value=''/>
  <b>Account ID</b>: <input type='text' name='accid'/>
 <span id="addbutton" class="yuibutton">
      <span class="first-child">
          <button type="button">Add Billing User</button>
      </span>
  </span>
</form>

  <div id='tokenTableWrapper'>
    <table id='tokenTable'>
      <thead>
        <tr><th>Token</th><th>Account ID</th><th>User</th><th>Fax</th><th>DICOM</th><th>Accounts</th></tr>
      </thead>
      <tbody>
        <?$previousToken = null; ?>
        <?foreach($tokens as $t):?>
          <?if($t->billingid !== $previousToken):?>
            <tr><td><?=htmlentities($t->billingid)?></td><td><?=htmlentities($t->accid)?></td><td><?=htmlentities($t->first_name)?> <?=htmlentities($t->last_name)?></td><td id='faxin<?=$t->billingid?>'><?=$t->faxin?></td><td><?=$t->dicom?></td><td><?=$t->acc?></td></tr>
          <?else:?>
            <tr><td>&nbsp;</td><td><?=htmlentities($t->accid)?></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
          <?endif;?>
          <? $previousToken = $t->billingid; ?>
        <?endforeach;?>
      </tbody>
    </table>
  </div>
</body>
</html>

