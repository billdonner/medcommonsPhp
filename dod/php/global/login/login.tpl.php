<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="MedCommons, Inc." />
    <meta name="description" content="MedCommons Website Version 1.0 Page Sign In" />
    <meta name="keywords" 
    content="medcommons, personal health records,ccr, phr, privacy, patient, health, records, medical records,
						emergencyccr"/>
    <meta name="robots" content="all"/>
    <meta name="viewport" content="width=320" />
    <title>Sign In</title>

    <link rel="shortcut icon" href="../images/favicon.gif" type="image/gif" />
    <link media="all"
	href="../css/medCommonsStyles.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript">
    function popuploginpage(page) {
    	window.open(  page, "IdentityWindow", "status = 1, height = 400, width = 750, resizable = 0" );
    	return false;
    }
      function setvalue(id,innerhtml)
    {
    	if (document.getElementById( id ))
    	document.getElementById( id ).innerHTML =innerhtml;
    }
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
            function toggle(id)
    {
    	if (document.getElementById( id ))
    	{
    	if  (document.getElementById( id ).style.display == 'none')
    	document.getElementById( id ).style.display = 'block'; else
    	document.getElementById( id ).style.display = 'none';
    	}
    	return false;
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

    function wsinit()
    {
    	args_init();
    	var logout = '';

    	if(getCookie('mc_openid_auth') || getCookie('mc'))
    	logout = '<li><a class=menu_nil    href="/acct/logout.php?next=http://www.medcommons.net/" >Logout</a></li>';
    	else
    	logout = '<li ><a class=menu_nil  href=".?next=/mod/ondemand.php" >Login</a></li>';

    	if (getCookie('mc'))	{
    		signed_on = true;
    		setvalue('_logout',"<span>Logout</span>"); 
    		showblock('loggedon');
    		dontshow('notloggedon');
    		showinline('visi');
    	}
    	else
    	{
    		signed_on = false;
    		setvalue('_logout',"<span>Login</span>");
    		showblock('notloggedon');
    		dontshow('loggedon');
    		dontshow('visi');

    		
    	}

    	for (ix = 0; ix<10 ; ++ ix)
    	{
    		if (args['e'+ix])
    	
    		if (args['p'+ix])
    		{
    			p = args['p'+ix];
    			document.getElementById(p).innerHTML = '** ' + args['e'+ix] + '**';
    		}
    		
    	}
    }

    </script>
</head>
<body  id='page_personal' >
<table id='topheader'><tr>
<td id ='topleft' >
<a href='http://www.medcommons.net/' title='MedCommons Homepage'>
<img  border="0" src="http://www.medcommons.net/images/MEDcommons_logo_246x50.gif" /></a></td>
<td width=100% >&nbsp;</td>
  <td  id='topright' >
 <span id='visi' class=right >

                <a href='../acct/home.php'><img alt='' border=0 id='stamp' src='../acct/stamp.php'  /></a>
 </span>
</td>
 </tr></table>

<div id="ContentBoxInterior"    mainId='page_register' mainTitle="Offer Convenient Health Information Services"  >

<h2>Sign In</h2>
<div >
<form class='p' method='post' action='.' name='login' id='login'>

  <div class='field'>
    <label for='openid_url'>&nbsp;</label>
    <span class='q'>
       <input type='hidden' name='next' value='<?=isset($_REQUEST['next'])?htmlentities($_REQUEST['next']) : ''?>' />
       <input type='text' name='openid_url' id='openid_url' size='30'
              value="<?= $openid_url ?>" />
       <input type='submit' value='Sign In' />

       <div class='r'>
<?php if ($error) { ?>
<span class='errorAlert'><?= $error ?></span>
<?php } ?>
          <em>
           <div>http://user.myopenid.com</div>
           <div>user@email.com</div>

           <div>1283-2124-7623-1981</div>
           <div>9547-6879-9928</div>
         </em>
     </div>
    </span>
  </div>
</form>
  
<br />
<br />

<br />
<br />
</div>
<table class=tinst >
<tbody >
<tr ><td class=lcol >Problems?</td><td class=rcol ><ul id=usefullinks>
<li><b>Review</b> the MedCommons <a href='http://www.medcommons.net/termsofuse.html' >Terms of Use</a> and MedCommons.net <a href='http://www.medcommons.net/privacy.html'>Privacy Policy</a></li>

<li><b>Inquire</b> about your bill at <a href='mailto:application-payments@amazon.com' >application-payments@amazon.com</a></li>
<li><b>View</b> the Amazon <a href='http://www.amazon.com/dp-applications' >Application Billing Page</a></li>
<li><b>Visit</b> the <a href='http://forum.medcommons.net/'>MedCommons Forum</a> for news and support
</ul></td></tr>

</tbody>
</table>

&nbsp;<br/>
</div>
     <div id="footer">
			<ul class="listinlinetiny">
			<li>Copyright &copy; 2008 MedCommons, Inc.</li>&nbsp;&nbsp;&nbsp;&nbsp;
                   <li><a href="http://www.medcommons.net/index.php">Home</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;

                     <li><a href="http://www.medcommons.net/termsofuse.php">Terms of Use</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
                         <li><a href='http://www.medcommons.net/privacy.php'>Privacy Policy</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
                         <li><a target='_new' href="http://ccrcommons.com">Public HealthURLs</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
                            <li><a href="http://forum.medcommons.net/">Forums</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
                       <li><a href='mailto:info@medcommons.net'>Support</a></li> &nbsp;&nbsp;|&nbsp;&nbsp;    
                    <li class="notiPhone"><a href="http://www.medcommons.net/contact.php">Contact Us</a></li>

			</ul>

        </div> <!-- footer  end -->
        </div> <!-- fcontentBoxInterio  end -->
        <script  type="text/javascript">wsinit();</script>
        </script>
        </body>
     </html>
