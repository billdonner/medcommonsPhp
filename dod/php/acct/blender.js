
//some parts of script by yvoschaap.com
//freely useable
//optional link back would be very web 2.0 :)

var infield = false;

function hide(s) {}

// support functions for forms handling, tabs and ajax processing
var fieldupdateloc = "fieldupdate.php"; //wld 4/26/07
var accountid = 0;
var names;
var values;
var ind;
var wwwHost;
var secureHost;
var fieldUpdateBase;

// ajax stuff
var xmlHttp;
var interval = 30; //seconds
var timerid = 0;
var onIE = "";
var lasttimesynch =0;

var currtab = "tab1";

var xDate = new Date(); // external date/time of ajax server
// support highlighting and capture data when leaving fields
function highlight(field) {
	if(field.getAttribute('readonly')) {
		return;
	}
	if(field.select != null)
	field.select();
	field.onblur=unhighlight;
	//  field.oldBackgroundColor = computedStyle(field,'backgroundColor','background-color');
	field.style.backgroundColor='#e6e6e6';
	infield = true;
}

function unhighlight() {
	this.style.backgroundColor='#f3f3f3';
	if(this.oldBackgroundColor) {
		this.style.backgroundColor = this.oldBackgroundColor;

	}
	infield = false;	// note no longer in any field
}


// button actions
function remoteop(op,value,type)
{
	//alert(fieldupdateloc+" remoteop"+op+" type "+type+ " value" + value);
	this.ajaxServer(fieldupdateloc+"?fieldname=" +escape(op)+ "&type="+type+"&content="+escape(value));
}

function fieldEnter(field,evt,idfld) {
	evt = (evt) ? evt : window.event;
	if ((evt.keyCode == 13 ) ||(evt.keyCode == 10)){ //make tab same as ret
		elem = document.getElementById( idfld );

		remoteop (elem.id,field.value,'inputfield');
		return false;
	}
	else return true; // ?? latest change

}

function fieldBool(field, evt,idfld) {
	evt = (evt) ? evt : window.event;
	elem = document.getElementById( idfld );
	remoteop (elem.id,field.value,'boolfield')
	infield = false;
	return true;
}

function maketextfield (id, width, height,value){
	return "<textarea name=\"textarea\" id=\""+ id +	"_field\" "+
	"style=\"width: "+width+	"px; height: "+height+"px;\" "+
	"onfocus=\"highight(this);\" "+
	//			onblur=\"noLight(this); return fieldBlur(this,'" + actual.id + 	"');\"
	">"
	+ value + "</textarea>";
}

function makeinputfield (id, width, height,value){
	return "<input id='" + id +	"_field' " +
	//	"style=\"width: "+width+"px; height: "+height+"px;\" " +
	"maxlength=\"254\" "+
	"size=\"44\" "+   // updated 4/26/07 by wld was 24
	"type=\"text\" "+
	"value=\""+ value + "\""+
		"onkeypress=\"return fieldEnter(this,event,'" + id + 	"');\" "+
	"onfocus=\"highlight(this);\" "+
	//			onblur=\"noLight(this); return fieldBlur(this,'" + actual.id + 	"');\"
	"/>";
}
function makecheckboxfield (id, width, height, value){
	if (value=='1')
	checked= "checked='checked'";
	else checked='';
	return "<input id='" + id +	"_field' style=\"width: "+width+"px; height: "+height+"px;\" " +
	"maxlength=\"1\" "+
	"type=\"checkbox\" "+
	"value=\" "+ value + " \""+ checked +
	//	"onkeypress=\"return fieldEnter(this,event,'" + id + 	"');\" "+
	"onfocus=\"highlight(this);\" "+
	//			onblur=\"noLight(this); return fieldBlur(this,'" + actual.id + 	"');\"
	"/>";
}

//edit field created
function oncheckboxfieldclicked (actual) {
	//		alert ('checkboxclick '+actual.innerHTML);
	//		if (actual.checked) checked=1; else checked=0; // ask simon, must move on
	remoteop (actual.id,1,'checkboxfield'); // if udefined it blows the whole remote call
	return false;

}


//edit field created
function ontextfieldclicked (actual) {

	if (!infield) {  //; //throw away multi clicks
		width = widthEl(actual.id) + 20;
		height =heightEl(actual.id) + 2;
		if(width < 100)
		width = 150;
		if(height < 40)
		actual.innerHTML= makeinputfield (actual.id, width, height,actual.innerHTML);

		else actual.innerHTML = maketextfield (actual.id, width, height,actual.innerHTML);

		infield = true;

		actual.focus();
		return true; // dont do stuff
	}
	return false; //
}

//edit field created
function onboolfieldclicked (actual) {
	remoteop (actual.id,actual.value,'boolfield');
	return false; // dont do actual work of the anchor, etc
}

//find all span tags with class editText and id as fieldname parsed to update script. add onclick function
function editbox_init(){
	if (!document.getElementsByTagName){ return; }
	var spans = document.getElementsByTagName("span");

	// loop through all span tags
	for (var i=0; i<spans.length; i++){
		var spn = spans[i];

		if (((' '+spn.className+' ').indexOf("editText") != -1) && (spn.id))
		{
			spn.onclick = function () { ontextfieldclicked(this); }
			spn.style.cursor = "pointer";
			spn.title = "Click to edit field";
			width = widthEl(spn.id);
			height =heightEl(spn.id);

			spn.innerHTML= makeinputfield (spn.id, width, height,spn.innerHTML);
		}
		else
		if (((' '+spn.className+' ').indexOf("checkboxText") != -1) && (spn.id))
		{
			spn.onclick = function () { oncheckboxfieldclicked(this); }
			spn.style.cursor = "pointer";
			spn.title = "Click to toggle checkbox";
			spn.innerHTML = makecheckboxfield(spn.id,10,10,spn.innerHTML);
		}
		else
		if (((' '+spn.className+' ').indexOf("boolText") != -1) && (spn.id))
		{
			spn.onclick = function () { onboolfieldclicked(this); }
			spn.style.cursor = "pointer";
			spn.title = "Click to toggle field";
		}

	}


}

//crossbrowser load function
function addEvent(elm, evType, fn, useCapture)
{
	if (elm.addEventListener){
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent){
		var r = elm.attachEvent("on"+evType, fn);
		return r;
	} else {
		alert("Please upgrade your browser to use full functionality on this page");
	}
}

//get width of text element
function widthEl(span){

	if (document.layers){
		w=document.layers[span].clip.width;
	} else if (document.all && !document.getElementById){
		w=document.all[span].offsetWidth;
	} else if(document.getElementById){
		w=document.getElementById(span).offsetWidth;
	}
	return w;
}

//get height of text element
function heightEl(span){

	if (document.layers){
		h=document.layers[span].clip.height;
	} else if (document.all && !document.getElementById){
		h=document.all[span].offsetHeight;
	} else if(document.getElementById){
		h=document.getElementById(span).offsetHeight;
	}
	return h;
}



function showtab( tabnum ) {
	tabname = "tab" + tabnum
	if(tabname != currtab) {
		document.getElementById(currtab).style.display="none";
		document.getElementById(tabname).style.display="block";
		currtab = tabname;
		parentSize();
	}
}


function createXMLHttpRequest(){
	if (window.ActiveXObject) {  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}
	else if (window.XMLHttpRequest) {xmlHttp= new XMLHttpRequest();
	}
}


function refreshTime()
{
	//nothing much to do here, as the time is updated only when the server responds right now
	// but we could display our time as well as the server time
	if($('timeofday'))
	$('timeofday').innerHTML = showtime(new Date(),'black');

}

function timeHandler()
{
	// if just running the timer, dont poll the server, just display right here


	if (accountid!=0)
	this.ajaxServer("ajtimerpoll.php?accid="+accountid+"&lt="+lasttimesynch+"&interval="+interval);
	else
	{
		timerid = setTimeout("timeHandler()",interval*1000);
		refreshTime();
	}
}



function ajaxServer(url){
	//when dispatching to an external ajax service kill the timer
	if (timerid !=0) {
		clearTimeout(timerid);
		timerid=0;
	}

	// prevent caching
	if(url.indexOf('?')<0)
	url += '?';
	url += '&v='+new Date().getTime();

	erl = encodeURI(url);
	// alert ("Sending "+url);
	createXMLHttpRequest();
	xmlHttp.onreadystatechange=ajaxCallback;
	xmlHttp.open("GET",erl,true);

	xmlHttp.send(null);
}

function getTagContents(str,tag)
{
	var taglen = tag.length;
	var start = str.indexOf('<'+tag+'>');
	var end = str.indexOf('</'+tag+'>');
	if (start < 0) return false;
	if (end <0) return false;
	var sl = str.slice (start + taglen + 2, end);
	var slsub = sl.slice(1,100)+"..."+sl.slice(sl.length-100,sl.length);
	//alert ("tag "+tag+" start "+start+" end "+end+" slice "+slsub);
	return sl;
}

/**
* Give parent opportunity to adjust size of this windoe when framed
*/
function parentSize() {
	try {
		if(window.parent && window.parent.sizecontent) {
			window.parent.setTimeout("sizecontent()",20);
		}
	}
	catch(e) {
	}
}



function ajaxCallback(){
	if (xmlHttp.readyState == 4) {
		if(xmlHttp.status !=200) {
			if($('timeofday'))
			$('timeofday').innerHTML = showtime(new Date(),'red')+ " ("+xmlHttp.status+")";
		}
		else {

			if($('timeofday'))
			$('timeofday').innerHTML = showtime(new Date(),'black');

			//log("RESPONSE: " + xmlHttp.responseText);

			// ssadedin: add einfo
			var eminfo = getTagContents(xmlHttp.responseText,'einfo');
			if((eminfo != '') && (eminfo != false)) {
				window.einfo = evalJSON(getTagContents(xmlHttp.responseText,'einfo'));

			}
			updatePatientCard(window.einfo);

			if (xmlHttp.responseText != '') {
				var xcontent = getTagContents(xmlHttp.responseText,'content');
				if (xcontent !='')
				document.getElementById('content').innerHTML = xcontent;
				var xstatus = getTagContents(xmlHttp.responseText,'status');
				if((xstatus !='') && $('status')) document.getElementById('status').innerHTML = xstatus;

				var xemergencyccr = getTagContents(xmlHttp.responseText,'emergencyccr');
				if (xemergencyccr !='')    document.getElementById('emergencyccr').innerHTML = xemergencyccr;

				var xtimesynch = getTagContents(xmlHttp.responseText,'timesynch');
				if (xtimesynch !='')    {
					//alert ("Received timesynch "+xtimesynch);
					xDate.setTime(xtimesynch*1000); // convert seconds to milliseconds and format
					if($('timesynch'))
					$('timesynch').innerHTML = showtime(xDate,'lightgrey');

					// save last time so we can send it back
					lasttimesynch = xtimesynch;
				}
				var rtype = getTagContents(xmlHttp.responseText,'rtagtype');
				//alert ('xtype='+xtype+' xid='+xid+' xcontent='+xcontent);*******
				if (rtype !=''){
					var rcontent = getTagContents(xmlHttp.responseText,'rcontent');
					var rid = getTagContents(xmlHttp.responseText,'rfid');
					//var xwidth = getTagContents(xmlHttp.responseText,'width');
					//var xheight = getTagContents(xmlHttp.responseText,'tagtype');
					// if content is present then an id tag is also present
					width = widthEl(rid);
					height =heightEl(rid);
					if (rtype=='checkboxfield')
					inner = makecheckboxfield (rid,10,10,rcontent);
					else if (rtype=='inputfield')
					inner = makeinputfield (rid,width,height,rcontent);
					else if (rtype=='textfield')
					inner = maketextfield (rid,width,height,rcontent);
					else if (rtype=='boolfield')
					inner = "<a id=\""+ rid +"_field\"   onclick=\"return fieldBool(this,event,'" + rid + "')\" >"+rcontent+"</a>";
					document.getElementById(rid).innerHTML = inner ;
					//infield=false; // clear flag if waiting
				}
			} // response != ''
		} // status == OK
	} // readystate == 4

	// get the timer going again if needed
	if (timerid == 0) {
		timerid = setTimeout("timeHandler()",interval*1000);
		refreshTime();
	}
	window.setTimeout(parentSize, 100);
}

function ajaxInit()
{

	timerid = setTimeout("timeHandler()",interval*1000);
	refreshTime();
}

// these functions run when the page is loaded

function initBlender(accid,servertime,www,secure,field) {
	wwwHost = www;
	secureHost = secure;
	fieldUpdateBase = field;
	accountid = accid; ind=0;
	names = new Array(); values = new Array();
	//roundElement('patientCard'); //
	//roundClass('div','rounded');
	//	if($('trackingBox'))
	//	$('trackingBox').innerHTML = trackingBox();//must come first
	if($('timeofday'))
	$('timeofday').innerHTML = showtime(new Date(),'black');
	//document.getElementById('status').innerHTML = "<div>initialized...</div>";
	lasttimesynch = servertime;
	updatePatientCard(window.einfo);
	ajaxInit();
}
function initBasic() {
	if($('timeofday'))
	$('timeofday').innerHTML = showtime(new Date(),'black');
	ajaxInit();
}
function initApplianceConfigurator() {
	if($('timeofday'))
	$('timeofday').innerHTML = showtime(new Date(),'black');
	fieldupdateloc = "ajapplianceupdate.php";
	ajaxInit();
}


function updatePatientCard(info) {
	if(!info) {
		var eccr = $('emergencyccr');
		hide('patientCardOuter');
		if(eccr) {
			if(!eccr.rounded) {
				roundElement(eccr);
				eccr.rounded = true;
			}
			eccr.style.display = 'block';
			if(eccr.innerHTML!='') {
				//eccr.style.border = 'solid 2px';
			}
			else {
				eccr.style.border = 'none';
			}
		}
		return;
	}
	else {
		tabify('patientTab2');
		$('patientCardOuter').style.display = 'block';
		$('emergencyccr').style.border = 'none';
		$('emergencyccr').style.display = 'none';
	}

	var p = [
	[ 'patientGivenName', info.givenName ],
	[ 'patientFamilyName', info.familyName ],
	[ 'patientMiddleName', info.middleName ],
	[ 'patientEmail', info.email ],
	[ 'patientGender', info.gender ],
	[ 'patientAddress1', info.address1 ],
	[ 'patientState', info.state],
	[ 'patientPostalCode', info.postalCode ],
	[ 'patientPhoneNumber', info.phoneNumber ],
	[ 'patientCountry', info.country ],
	[ 'patientCity', info.city ],
	[ 'patientAge', info.age ]
	];

	for(i=0; i<p.length; ++i) {
		if(p[i][1]) {
			var el = document.getElementById(p[i][0]);
			if(el)
			el.value = p[i][1];
		}
	}

	// Hack: ensure ccr edit link is updated with correct guid
	if($('eccrEditLink')) {
		var url = $('eccrEditLink').href;
		url = url.replace(/guid=[a-z0-9]*/,'guid='+window.einfo.guid);
		$('eccrEditLink').href = url;
		$('eccrEditLink2').href = url;
	}

	if($('patientMiddleName') && info.middleName && (info.middleName != '')) {
		$('patientMiddleName').style.display='inline';
	}
	else
	hide('patientMiddleName');
}

// button actions
function lockaccount()
{
	this.ajaxServer("ajacctlock.php?accid="+accountid+"&lock=1");
}
function unlockaccount()
{
	this.ajaxServer("ajacctlock.php?accid="+accountid+"&lock=0");
}

function privacylockclicked()
{
	this.ajaxServer("ajlock.php?accid="+accountid+"&lock=0");
}

function privacyunlockclicked()
{
	this.ajaxServer("ajlock.php?accid="+accountid+"&lock=1");
}

function submitpressed() {

	this.ajaxServer("ajuserupdate.php?accid="+accountid+"&names="+names+"&values="+values);
	ind=0;
	names = new Array(); values = new Array();
	return false;
}

function redccrop (id,which)
{
	this.ajaxServer("ajredccrop.php?accid="+accountid+"&id="+id+"&op="+which);
}

function delccrop (id,which)
{
	this.ajaxServer("ajdelccrop.php?accid="+accountid+"&id="+id+"&op="+which);
}


function resetpressed() {
	ind=0;
	names = new Array(); values = new Array();
	return true;
}

function setccrpressed(id) {
	if (confirm("Are you sure you want to set this CCR as your Emergency CCR? "))
	redccrop(id,1);
}

function clearccrpressed(id) {
	if (confirm("Are you sure you want to remove this CCR as your Emergency CCR? ")) {
		window.einfo = null;
		$('emergencyccr').style.display='none';
		redccrop(id,0);
	}
}

function trashpressed(id) {
	if (confirm("Are you sure you want to delete this CCR reference from your Account? "))
	delccrop(id,0);
}

function restorePin() {
	if(!confirm("Restoring the PIN on your Emergency CCR will cause all access to it to require a PIN.\r\n\r\nDo you want to continue?")) {
		return;
	}
	document.location.href="restorePin.php";
}


function showtime(Digital,color)
{

	var hours=Digital.getHours();
	var minutes=Digital.getMinutes();
	var seconds=Digital.getSeconds();
	var dn="pm";
	if (hours<12)
	dn="am";
	if (hours>12)
	hours=hours-12;
	if (hours==0)
	hours=12;
	if (minutes<=9)
	minutes="0"+minutes;
	if (seconds<=9)
	seconds="0"+seconds;
	var ctime=hours+":"+minutes+":"+seconds+" "+dn;
	return "<b style='color:"+color+"'>"+ctime+"</b>";
}
function paymentpopup(url) {
	newwindow=window.open(url,'_payment','height=600,width=450,toolbar=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function personapopup(url) {
	newwindow=window.open(url,'_persona','height=300,width=500');
	if (window.focus) {newwindow.focus()}
	return false;
}

function toggleLayer(whichLayer)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	}
	else if (document.all)
	{
		// this is the way old msie versions work
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	else if (document.layers)
	{
		// this is the way nn4 works
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
}
function disableSubmit(whichButton)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		document.getElementById(whichButton).disabled = true;
	}
	else if (document.all)
	{
		// this is the way old msie versions work
		document.all[whichButton].disabled = true;
	}
	else if (document.layers)
	{
		// this is the way nn4 works
		document.layers[whichButton].disabled = true;
	}
}

//get this connected when we load, most initialization happens in the onload routine

addEvent(window, "load", editbox_init);

-->