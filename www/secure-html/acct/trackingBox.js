/**************************************************************/
/* Javascript support routines for tracking box               */
/**************************************************************/

var loggedIn = false;
function checkLogin() {
  //log("loggedIn = " + loggedIn + " mc=["+getCookie("mc")+"]");
  if(!loggedIn && getCookie('mc')) {
    log('sending idinfo.php');
    loadJSONDoc("idinfo.php").addCallbacks(updateAccountInfo, error );
  }
  else
  if(loggedIn && !getCookie('mc')) {
    loadJSONDoc("idinfo.php").addCallbacks(updateAccountInfo, error);
    loggedIn = false;
  }
}

function updateAccountInfo(info) {
  log('idinfo success');
  //alert(1);
  if(info) {
    window.accountInfo = info;
  }
  else
    window.accountInfo = null;

  var tbt = $('trackboxTop');
  if(window.accountInfo) {
    var ai = window.accountInfo;
    $('tbName').innerHTML = ai.firstName + ' ' + ai.lastName;
    $('tbDateTime').innerHTML =  toISOTime(new Date());
    $('tbAccId').innerHTML = '#'+prettyAccId(ai.accountId);
    show('trackboxBottom','leftAccountLink');
    hide('leftRegisterLink','menuBarLogInLink');
    //tbt.style.backgroundColor=tbt.originalBackgroundColor;
    removeElementClass(tbt,"notLoggedInTrackboxTop");
    if(!tbt.rounded) {
      roundElement('trackboxTop',  { corners: 'tl', color: '#cbd6e3' });
      roundElement('trackboxBottom',  { corners: 'br', color: '#e6e6e6'});
      tbt.rounded = true;
    }
    tbt.childNodes[0].style.display='block';
    loggedIn = true; 
  }
  else {
    hide('trackboxBottom','leftAccountLink');
    show('leftRegisterLink');
    if($('menuBarLogInLink'))
      $('menuBarLogInLink').style.display='inline';
    
    setElementClass(tbt,"notLoggedInTrackboxTop");
    if(tbt.rounded) {
      tbt.childNodes[0].style.display='none';
    }
    loggedIn = false;
  }
  show('trackboxTop');
}

function error(e) {
  log('Error occurred retrieving content: '+e.description);
}

