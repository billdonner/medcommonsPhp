<?PHP

require_once "../ws/wslibextio.inc.php";

// generically sends a message
abstract class notifier extends dbextiorestws {

	// wld 101305 - functions to support outbound voice


	private function placecall($salute,$firstname,$lastname,$phone,$speak)
	{


		// now write an entry in the mysql database
		$qstatus="Awaiting Silverlink";
		$fstatus="NEW";

		$insert="INSERT INTO vcallq (qtime,salute,firstname,lastname,phone,speak,qstatus,fstatus)".
		" VALUES(NOW(),'$salute','$firstname','$lastname','$phone','$speak','$qstatus','$fstatus'	)";
		mysql_query($insert) or die("can not insert into table vcallq - ".mysql_error());
		$id = mysql_insert_id();
		return "OK $id";

	}

	private function ecall($e,$subject)
	{
		// return true if this was an email address that could be parsed into a phonecall

		// test1, must end in @voice.medcommons.net

		$pos = strpos($e, '@voice.medcommons.net');
		if (($pos===false) || ($pos<=0)) return false;

		$front = substr ($e,0,$pos); // take the front matter

		list($phone,$salute,$first,$last) = explode('.',$front);
		if (is_numeric($phone)) {
			// ok, this is a phone call
			return $this->placecall($salute,$first,$last,$phone,$subject);
		}
		else
		return true;

	}



abstract function send_message(&$message,$mcid, $t,$m,$blurb, $a,$b,$c,$d,$e,$f,$g);
	
	
	//
	//xml support - the xml doc we are building is buffered until the very end
	//
	
	function pretty_tracking($tracking) {
		return substr($tracking,0,4)." ".substr($tracking,4,4)." ".substr($tracking,8,4);
	}
	function pretty_mcid($s)
	{
		return substr($s,0,4)." ".substr($s,4,4)." ".substr($s,8,4)." ".substr($s,12,4);
	}


	function z($l,$v){	if ($l[$v]!="") xm ("<$v>".$l[$v]."</$v>");}

	function getparams($set,&$t,&$m,&$a,&$b,&$c,&$d,&$e,&$f,&$g)
	{

		$t = $this->cleanreq("t".$set);
		$m = $this->cleanreq("m".$set);
		$a = $this->cleanreq("a".$set);
		$b = $this->cleanreq("b".$set);
		$c = $this->cleanreq("c".$set);
		$d = $this->cleanreq("d".$set);
		$e = $this->cleanreq("e".$set);
		$f = $this->cleanreq("f".$set);
		$g = $this->cleanreq("g".$set);
	}

	//
	// main program - parse the incoming parameters, and reject ill formed requests, or bad templates
	//
	function xmlbody ()
	{  // this overrides the standard xml body to preserve compatibility with the old service
		// echo "in xmlbody in ns.inc.php";
		$timenow=time();  // get time for writing these
		$mcid = $this->cleanreq('mcid'); //**** just recorded for reference purposes
	$clientid = $this->cleanreq('clientId'); //10/11/05 wld - if from badboy'clientId
	if ($clientid =='badboy-client') $this->bccline = ""; else $this->bccline = "bcc:cmo@medcommons.net\r\n";

		$ref = substr(htmlspecialchars($_SERVER ['REQUEST_URI']),0,255); //get the uri

		$this->getparams(1,$t, $m, $a,$b,$c,$d,$e,$f,$g);

		if (isset($t) && ($t!="")){

			$this->xm("<request_fieldset1>t1=$t;a1=$a;b1=$b;c1=$c;d1=$d;e1=$e;f1=$f;g1=$g</request_fieldset1>");
					
		$v =  $this->ecall($m,$c); // place voice calls
			
		if ($v===false) {

			$retval = $this->send_message($message,$mcid, $t,$m,"MedCommons Notification for $m", $a,$b,$c,$d,$e,$f,$g);//call the routine to send the mail
			

			$this->xm("<request_status>".$retval."</request_status>\n");

			$insert="INSERT INTO emailstatus (time,requesturi,sendermcid,rcvremail,template,arga,argb,argc,argd,arge,argf,argg,message,status)".
			"VALUES(NOW(),'$ref','$mcid','$m','$t','$a','$b','$c','$d','$e','$f','$g','$message','$retval')";

			mysql_query($insert) or $this->xmlend("can not insert into table emailstatus - ".mysql_error());

		} else	$this->xm("<request_status>".$v."</request_status>\n");
		}


		$this->getparams(2,$t,$m, $a,$b,$c,$d,$e,$f,$g);

		if (isset($t) && ($t!="")){

			$this->xm("<request_fieldset2>t2=$t;a2=$a;b2=$b;c2=$c;d2=$d;e2=$e;f2=$f;g2=$g</request_fieldset2>");
			// write a log entry of what we did
			$retval = $this->send_message($message,$mcid, $t,$m,"MedCommons Notification for $m", $a,$b,$c,$d,$e,$f,$g);//call the routine to send the mail

			$this->xm("<request_status>".$retval."</request_status>\n");
			// write a log entry of what we did<BR>

			$insert="INSERT INTO emailstatus (time,requesturi,sendermcid,rcvremail,template,arga,argb,argc,argd,arge,argf,argg,message,status)".
			"VALUES(NOW(),'$ref','$mcid','$m','$t','$a','$b','$c','$d','$e','$f','$g','$message','$retval')";
			mysql_query($insert) or $this->xmlend("can not insert into table notifierlog - ".mysql_error());

		}


		$this->getparams(3,$t,$m,$a,$b,$c,$d,$e,$f,$g);
		if (isset($t) && ($t!="")){

			$this->xm("<request_fieldset3>t3=$t;a3=$a;b3=$b;c3=$c;d3=$d;e3=$e;f3=$f;g3=$g</request_fieldset3>");
			$retval = $this->send_message($message,$mcid, $t,$m,"MedCommons Notification for $m", $a,$b,$c,$d,$e,$f,$g);//call the routine to send the mail
			$this->xm("<request_status>".$retval."</request_status>\n");
			// write a log entry of what we did

			$insert="INSERT INTO emailstatus (time,requesturi,sendermcid,rcvremail,template,arga,argb,argc,argd,arge,argf,argg,message,status)".
			"VALUES(NOW(),'$ref','$mcid','$m','$t','$a','$b','$c','$d','$e','$f','$g','$message','$retval')";
			mysql_query($insert) or $this->xmlend("can not insert into table notifierlog - ".mysql_error());

		}


	}

}
?>
