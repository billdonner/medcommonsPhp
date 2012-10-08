<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html> 
<head>
   <title>DICOM on Demand</title>
   <link rel="stylesheet" href="http://yui.yahooapis.com/2.5.1/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
   <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css">
</head>
<body>
<div id="doc" class="yui-t7">
     <style type='text/css'>

    html {
      color: #333;
      background-color: black;
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
     background-color: #aaa;
   }

   #hd img {
    position: absolute;
     top: 0px;
     right: 0px;
   }

   #hd {
     background-color: black;
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

   #ft a:link, #ft a:visited {
     font-size: 95%;
     text-decoration: none;
   }

   #ft a:hover, #ft a:active {
     text-decoration: underline;
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
   form {
     display: inline;
   }
   div#agreebuttons {
     margin-left: 75px;
     margin-bottom: 30px;
   }
   </style>

  <div id="hd" class='hdband'>
  </div>
  <div id="bd">
	<div class="yui-g">
      <h2 class='first'>Demonstration System</h2>
        <p><strong>ATTENTION:</strong> You are signing in to the Live DICOM On Demand Demo Group.</p>
        <p> DO NOT UPLOAD PATIENT INFORMATION TO THIS PUBLIC DEMO.</p>

        <p>Demo cases are visible to all visitors. More test DICOM folders are available
             <a href='http://pubimage.hcuge.ch:8080/' target='_new'>here</a> or
             <a href='http://www.clearcanvas.ca/dnn/Downloads/tabid/70/Default.aspx' target='_new'>here</a>.</p>

        <div id='agreebuttons'>
        <form action='demotips.html'><input type='submit' value='I Agree'/></form>
        &nbsp;
        &nbsp;
        &nbsp;
        <form action='/'><input type='submit' value='Cancel'/></form>
    </div>
  </body>
</html>
