/* shows a particlur div */
function showinline(id)
{
	if (document.getElementById( id ))
    document.getElementById( id ).style.display = 'inline';
}
function showblock(id)
{
	if (document.getElementById( id ))
    document.getElementById( id ).style.display = 'block';
}
function dontshow(id)
{
	if (document.getElementById( id ))
    document.getElementById( id ).style.display = 'none';
}
/**
 * gets the value of the specified cookie.
 */
function getCookie(name)
{
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1) {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

// This function is included to overcome a bug in Netscape's implementation
// of the escape () function:

function myunescape (str)
{
	str = '' + str;
	while (true)
	{
		var i = str . indexOf ('+');
		if (i < 0)
			break;
		str = str . substring (0, i) + ' ' + str . substring (i + 1, str . length);
	}
	return unescape (str);
}

// This function creates the args [] array and populates it with data
// found in the URL's search string:

var args;

function args_init ()
{
	args = new Array ();
	var argstring = window . location . search;
	if (argstring . charAt (0) != '?')
		return;
	argstring = argstring . substring (1, argstring . length);
	var argarray = argstring . split ('&');
	var i;
	var singlearg;
	for (i = 0; i < argarray . length; ++ i)
	{
		singlearg = argarray [i] . split ('=');
		if (singlearg . length != 2)
			continue;
		var key = myunescape (singlearg [0]);
		var value = myunescape (singlearg [1]);
		args [key] = value;
	}
}

var signed_on;

function www_init()
{
	args_init();

  var logout = '';
  if(getCookie('mc_openid_auth') || getCookie('mc'))
    logout = '<li><a class="menu_logout" href="{{ Site }}/acct/logout.php">logout</a></li>';
  else
    logout = '<li class="light">logout</li>';
	
	if (getCookie('mc'))	{
		signed_on = true;	
		showblock('loggedon');
		dontshow('notloggedon');
		showinline('visi');
			document.getElementById('navcontainer').innerHTML = 
		            '<ul id="navlist">'+
                        '<li><a class="menu_home" href="{{ Site }}/index.html" title="home">home</a></li>&nbsp;|&nbsp;'+
                        '<li><a class="menu_dashboard" href="{{ Site }}/acct/home.php">dashboard</a></li>&nbsp;|&nbsp;'+
                         '<li><a class="menu_search" href="{{ Site }}/searchExamples.html">search</a></li>&nbsp;|&nbsp;'+
                        '<li><a class="menu_settings" href="{{ Site }}/acct/settings.php">settings</a></li>&nbsp;|&nbsp;'+
                        logout +
                     '</ul>';
	}
	else
	{
		signed_on = false;
		showblock('notloggedon');
		dontshow('loggedon');
		dontshow('visi');
			document.getElementById('navcontainer').innerHTML = 
		            '<ul id="navlist">'+
                        '<li><a class="menu_home" href="{{ Site }}/index.html" title="home">home</a></li>&nbsp;|&nbsp;'+
                        '<li class="light">dashboard</li>&nbsp;|&nbsp;'+
                         '<li><a class="menu_search" href="{{ Site }}/searchExamples.html">search</a></li>&nbsp;|&nbsp;'+
                        '<li class="light">settings</li>&nbsp;|&nbsp;'+
                        logout+
                     '</ul>';
	}
	if (args['error'])
		if (args['p'])
				{
				p = args['p'];
				document.getElementById(p).innerHTML = args['error'];
				}
}
