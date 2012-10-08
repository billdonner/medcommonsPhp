<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <title>DICOM on Demand</title>
   <link rel="stylesheet" href="http://yui.yahooapis.com/2.5.1/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
   <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css">
   <link rel="stylesheet" type="text/css" href="../acct/rls.css">
</head>
<body>
<div id="doc" class="yui-t7">
     <style type='text/css'>

    html {
      color: #333;
      background-color: #F7F5E7;
    }
    
    body {
      min-height: 100%;
    }

   h1 {
    margin: 0.7em 0em 0.0em 0em;
   }

   .byline {
     font-style: italic;
     margin: 0px;
     padding: 0px;
     font-size: 90%;
   }

   .hdband, h2 {
     position: relative;
     color: white;
     background-color: #958F6D;
   }

   #hd img {
    position: absolute;
     top: 0px;
     right: 0px;
   }

   #hd {
     background-color: white;
     color: #655F3D;
     height: 70px;
   }

   #voucherid {
     width: 7em; 
   }
   #voucherform {
     position: absolute;
     top: 50px;
     right: 7px;
   }


   #bd, #hd, #ft {
     font-family: 'Futura-Medium','Futura','Trebuchet MS',sans-serif;
   }
   a:link, a:visited {
    color: #5987AC;
   }
   a:hover, a:active {
    color: #5987AC;
   }

   #bd, #ft {
     background-color: #FEF6E2;
     background-color: #0066B3;
     background-color: #22415d;
     background-color: #22415d;
     background-color: white;
   }
   #ft {
     padding: 0.2em 0em 0.1em 0em;
   }
   .leadin {
    font-size: 118%;
    font-weight: bold;
   }
   h2.first {
    margin-top: 0px;
   }
   p,h2,#hd,ul {
     padding: 0.2em 0.8em;
  }
  ol {
    padding: 0em 1em 2em;
  }
   p.thinpar {
    padding-top: 1.2em;
    max-width: 508px;
   }
   #rls {
     text-align: center;
   }
   </style>

  <div id="hd" class='hdband'>
      <form name='voucherform' id='voucherform' action='https://healthurl.medcommons.net/mod/voucherclaim.php'>
        Voucher ID:
        <input type='text' name='voucherid' id='voucherid'>
        <input type='image' src='/acct/images/magnifier.png'/>
      </form>

      <img src='images/mc_logo.gif'/>
       <h1>DICOM On Demand</h1>
    <p class='byline'>Imaging made easy</p>
  </div>
  <div id="bd">
	<div class="yui-g">
    <h2 class='first'>How it Works</h2>
    <p>
       First, try out Web DICOM collaboration using anonymous data from a DICOM CD,
       your DICOM modality or PACS. Then, a monthly subscription will allow you to
       print a Web Voucher instead of a CD and to import patient CDs for easy and
       uniform Web access in the office, exam room and OR.</p>

       <h2>Try It</h2>

    <ol>
        <li><a href='https://healthurl.medcommons.net/DDL/app/ddl.jnlp'>Install DDL</a></li>
        <li>Send Anonymous or Phantom DICOM Data</li> 
        <li>Watch data arrive in Live Patient List (click image below to open)</li> 
        <li>Click your "patient" to view</li> 
    </ol>

    <div id='rls'>
      <a href='https://healthurl.medcommons.net/acct/groupdemo' target='_new' onclick='/*window.open("https://healthurl.medcommons.net/acct/groupdemo","","width=800,height=600"); return false;*/'><img src='images/democrush.png' title='Screenshot of Live Demo - Click to Try!'/></a>
    </div>

    <h2>Use It</h2>
    <p>
      Subscribe to use DICOM On Demand in your imaging facility or practice. (Coming Soon!)
    </p>


    </div>


	</div>
  <div id="ft"><p> (c) 2008 MedCommons  <!--  Terms of Use   Privacy Policy   HIPAA --></p> </div>
</div>
</body>
</html>
