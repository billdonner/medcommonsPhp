<script type='text/javascript'>

</script>
<style type='text/css'>
  #addAddress {
    margin: 10px 0px;
  }
  #addAddress input#addAddressButton { 
    vertical-align: middle;
    position: relative;
  }
  #addAddress * {
    vertical-align: middle;
  }
  #addAddress input#accid { 
    font-size: 10px;
    width: 12em; 
    margin: 0px 0.5em 0px 0.5em;
  } 
  #addressContainer div.yui-dt-col-accid {
    text-align: center;
  }
  #addressContainer img.clickable{
    cursor: pointer;
  }
  #addressmsg {
    padding-left: 2em;
    color: orange;
  }
  #addressContainer table {
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
</style>
<h4>Address Book</h4>
<br/>
<p>Your Address Book is used to auto-complete and populate accounts in other pages.  Enter accounts here
with which you communicate regularly and wish to have convenient access to.</p>
<br/>
<div id='addAddress'>
<b>Account ID:</b> <input type='text' id='accid' value=''/> 
<input type='image' id='addaddressMemberButton' value='Add'  
       title='Click to add the entered 16 digit Account ID to your address book' 
       src='images/add_button.png' onmousedown='this.style.top="2px";' onmouseup='this.style.top="1px"; this.blur();'/>
<span id='addressmsg'></span>

</div>
<div id='addressContainer'>
  
</div>
