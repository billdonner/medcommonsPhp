
    $(document).ready(function() {
    	if ($.browser.msie==0) {
      	Nifty("div#playernotes,div#playerlinks","same-height");
      	Nifty("div#ajaxarea","same-height");
	      Nifty("div#teamroster","same-height");
  	  }
    });

    var lelem='';
    function toggle(elem) {
      if (lelem!='') {
        $('#'+lelem+'_content').slideUp("fast");
        $('#'+lelem).css('font-weight','normal');
        $('#'+lelem).css('background','#ccc');
      }
      if (elem==lelem) {
        lelem='';
        return;
      }
      $('#'+elem+'_content').slideDown("fast");
      $('#'+elem).css('font-weight','bold');
      $('#'+elem).css('background','#aaa');
      lelem=elem;
    }
    
    function setAccordian(elem) {
      $('#'+elem).css('font-weight','bold');
      $('#'+elem).css('background','#aaa');
      lelem=elem;
		}

//==================== javascript ============ 
// written by Calvin Nguyen - 01/05 
function menuSwap(onMenu){ 
    //alert("menuSwap"); 
    //turn off all buttons 
    var mybuttons = document.getElementsByName("menuNavButton"); 
    for (var i = 0; i < mybuttons.length; i++){ 
        //alert("now disabling " + mybuttons[i].id); 
        classChange(mybuttons[i].id, "menuButton"); 
        hideLayer(mybuttons[i].id + "Disp"); 
    } 
    //turn on current button/layer 
   // alert("now enabling " + onMenu); 
    classChange(onMenu, "onmenuButton"); 
    showLayer(onMenu + "Disp"); 
} 

// change class function 
function classChange(mytarget, myclass){ 
    //alert("classChange. target: " + mytarget + " to class: " + myclass); 
    if (document.getElementById(mytarget)){ 
        //alert("element detected") 
        document.getElementById(mytarget).className = myclass; 
    } 
} 

// hide layer function 
function hideLayer(myLayer) { 
    //alert("hidelayer"); 
    if (document.getElementById(myLayer)){ 
        //alert("element detected"); 
        document.getElementById(myLayer).style.display = "none"; 
    } 
} 

// show layer function 
function showLayer(myLayer) { 
    //alert("showlayer " + myLayer); 
    if (document.getElementById(myLayer)){ 
        //alert("element detected"); 
        document.getElementById(myLayer).style.display = ""; 
    } 
} 
