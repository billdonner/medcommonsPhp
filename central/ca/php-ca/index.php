<?
$member = $_REQUEST['member'];
$o = $_REQUEST['o'];
$t = $_REQUEST['t'];
$secretmsg = $_REQUEST['msg'];
$caSetup = false;
include_once("./include/$member/common.php");
@include_once("./config/configuration.php");

switch ($_REQUEST['area']) {
	case "main":
	case "":
		switch ($_REQUEST['stage']) {
			case "":
				printHeader("Welcome to the {$config['orgName']} Certificate Authority");
				include_once("./modules/$member/main/welcome.php");
				printFooter();
				break;

			case "about":
				printHeader("About MedCommons-CA");
				include_once("./modules/$member/main/about.php");
				printFooter();
				break;

			case "help":
				printHeader("MedCommons-CA Help");
				include_once("./modules/$member/main/help.php");
				printFooter();
				break;

			case "trust":
				include_once("./modules/$member/main/trust.php");
				break;

			default:
				printHeader("Certificate application and issue");
				print "Unknown application option: " . htmlspecialchars($_REQUEST['stage']);
				printFooter();
				break;
		}
	break;
	
	case "apply":
		switch ($_REQUEST['stage']) {
			case "":
				printHeader("Certificate application and issue");
				include_once("./modules/apply/emailConfirm.php");
				printFooter();
				break;
				
			case "enterKey":
				printHeader("Certificate application and issue");
				include_once("./modules/$member/apply/enterKey.php");
				printFooter();
				break;
				
			case "issueCert":
				include_once("./modules/$member/apply/issueCert.php");
				break;
				
			case "signCert":
				include_once("./modules/$member/apply/signCert.php");
				break;
				
			case "fetchSpkac":
				include_once("./modules/$member/apply/fetchSpkac.php");
				break;
				
			case "fetchPem":
				include_once("./modules/$member/apply/fetchPem.php");
				break;
				
			case "list":
				include_once("./modules/$member/apply/listCert.php");
				break;
			default:
				printHeader("Certificate application and issue");
				print "Unknown application option: " . htmlspecialchars($_REQUEST['stage']);
				printFooter();
				break;
		}
		break;
	
	default:
		printHeader("Unknown area");
		print "Unknown area: " . htmlspecialchars($_REQUEST['area']);
		printFooter();
		break;
}


?>
