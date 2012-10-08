/**
 * Javascript functions supporting editing of groups
 */
function saveAdminState(userId, groupId, isAdmin) {
  //alert("Saving admin state for user " + userId + " for group " + groupId + " as " + isAdmin);
  execJSONRequest('User.action?setAdmin','user.mcid='+userId+'&groupId='+groupId+'&admin='+isAdmin,
    function(result) {
      if(result.status == "ok")
        showMsg("Saved admin state for user " + userId + " for group " + groupId + " as " + isAdmin);
    });
}

function showMsg(txt) {
  replaceChildNodes($('msg'),DIV({style:'padding:2px;'},txt));
  roundElement($('msg'));
  show('msg');
  fade($('msg'), {delay: 2.0, duration: 0.5, fps:25});
}
