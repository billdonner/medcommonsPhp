<script type='text/javascript'>
var user = { accid: '<?=$accid?>' };
</script>
<style type='text/css'>
  #addGroupMember {
    margin: 10px 0px;
  }
  #addGroupMember input#addgroupMemberButton { 
    vertical-align: middle;
    position: relative;
  }
  #addGroupMember *, #groupNameHeader * {
    vertical-align: middle;
  }
  #addGroupMember input#maccid, input#groupName { 
    font-size: 10px;
    width: 12em; 
    margin: 0px 0.5em 0px 0.5em;
  } 
  #groupContainer div.yui-dt-col-accid {
    text-align: center;
  }
  #groupContainer img.clickable{
    cursor: pointer;
  }
  #groupmsg {
    padding-left: 2em;
    color: orange;
  }
  #groupContainer table {
    background-color: white;
    border-collapse: collapse;
    width: 100%;
  }
  table tbody tr.highlight, table tbody tr.highlight td {
    background-color: #FFD490 !important;
  }
  .yui-dialog .bd table th {
    text-align: right;
  }
  .yui-dialog .bd table td,
  .yui-dialog .bd table th {
    padding: 5px 10px;
    border: solid 1px #444;
  }
  .yui-dialog .bd table {
    margin: 0px 100px;
    width: 300px;
    background-color: white;
    border-collapse: collapse;
  }
  #updatedMsg {
    font-weight: normal;
    color: orange;
    font-size: 11px;
    display: none;
  }
  #groupAcctId h4 {
    display: inline;
  }
  #groupAcctId {
    position: absolute;
    right: 20px;
    font-size: 11px;
  }
</style>
<br/>
<div id='groupsPanel'>
  <div id='groupAcctId'><h4>Group Account ID: </h4> <span><?=pretty_mcid($active_group_accid)?></span></div>
  <h4 id='groupNameHeader'>Group: <input type='text' id='groupName' value='<?=htmlentities($practice)?>'/>
  <input type='image' id='changeButton' value='Change'  
         title='Click to update the group name' 
         src='images/change_button.png' onclick='changeGroupName();' onmousedown='this.style.top="2px";' onmouseup='this.style.top="1px"; this.blur();'/>
         <span id='updatedMsg'>Updated</span>
  </h4> 
  <br/>
  <p>This page displays the details of your active Group.  You can switch your active group on the <a href='?page=personalAccount'>My HealthURL</a> tab.</p>
  <br/>
  <div id='addGroupMember'>
   <b>Account ID:</b> <input type='text' id='maccid' value=''/>  
  <input type='image' id='addgroupMemberButton' value='Add'  
         title='Click to add the entered 16 digit Account ID to your Group' 
         src='images/add_button.png' onmousedown='this.style.top="2px";' onmouseup='this.style.top="1px"; this.blur();'/>
  <span id='groupmsg'></span>

  </div>
  <div id='groupContainer'>
  </div>
</div>
