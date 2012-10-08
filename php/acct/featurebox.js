/**
 * Support for feature heading boxes
 */
 var activeSection;
function activate(section) {
  if(activeSection) {
    minimize();
  }

  activeSection = $(section);
  hide(section);
  var sectionHeading = $(section).getElementsByTagName('div')[0];
  setMainSectionHeading($(section).getElementsByTagName('h4')[0].innerHTML);
  $('mainfeature').getElementsByTagName('a')[0].innerHTML='X&nbsp;';
  $('mainfeature').getElementsByTagName('a')[0].href='javascript:minimize()';
  show('mainfeature');

  if(section=='worklist') {
    $('mainfeatureContents').innerHTML="<iframe name='currentgadget' src='myworklist/?tpl=widget' width='98%' allowtransparency='true' background-color='transparent' frameborder='0' scrolling='no' height='300px'>Your browser doesn't support iframes.</iframe>";
  }
  else
    $('mainfeatureContents').innerHTML="<iframe name='currentgadget' src='"+section+"/widget.php' width='98%' allowtransparency='true' background-color='transparent' frameborder='0' scrolling='no' height='300px'>Your browser doesn't support iframes.</iframe>";

  // NOTE:  script below broken on IE7 - IE7 forces iframes created as DOM nodes to have borders
  /*replaceChildNodes($('mainfeatureContents'),
  createDOM('iframe',{
    src:'myworklist?tpl=widget', 
    style:'border:0;',
    width:'98%',
    allowtransparency:'true',
    'background-color':'transparent',
    frameborder:'0', 
    border:'0',
    scrolling:'no',
    height:'300px'
    },'This page requires iframe support.'));
  */
  updateSize();
}

function setMainSectionHeading(h) {
  var mf = $('mainfeature');
  try {
      mf = window.parent.document.getElementById('mainfeature');
  }
  catch(e) {
  } 

  if(mf) 
    mf.getElementsByTagName('h4')[0].innerHTML=h;
  else {
    log("WARN: unable to locate main feature box to set title " + h);
  }
}

function minimize() {
  if(!activeSection)
    return;
  hide('mainfeature');
  show(activeSection.id);
  updateSize();
}

function updatePrimaryInterest() {
  loadJSONDoc('updatePrimaryInterest.php?'+queryString(document.interestsForm)).addCallbacks(function(res) {
    if(res.status=="ok") {
      alert("Your primary interest has been updated.");
    }
  },genericErrorHandler);
}
