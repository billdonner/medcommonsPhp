<?
/**
 * Example MedCommons Plugin / External Application
 *
 * This example shows how to write a simple 3rd party application that fetches a person's
 * CCR (with their permission) and does some analysis on it. In this case we take some of
 * the risk factors for cancer and use them to compute the person's current cancer risk.
 */
  require_once "OAuth.php";
  require_once "template.inc.php";
  require_once "JSON.php";

// Data sourced from
// http://apps.nccd.cdc.gov/uscs/Table.aspx?Group=TableAll&Year=2004&Display=n&variable2=AgespecType|1B&tabletype=INCI&ageadjCrude=crudetype&gender=mal&DatabyAge=All%20Cancer%20Sites%20Combined
$males = array( 0=>25.5,
                4=>22.6,
                9=>12.3,
                14=>13.3,
                19=>22.0,
                24=>30.9,
                29=>43.8,
                34=>62.2,
                39=>87.4,
                44=>149.1,
                49=>280.8,
                54=>551.5,
                59=>940.8,
                64=>1504.3,
                69=>2212.8,
                74=>2708.8,
                79=>3072.9,
                84=>3124.9,
                85=>2872.4);

$females = array( 0=>23.4,
                  4=>19.5,
                  9=>10.4,
                  14=>12.6,
                  19=>19.9,
                  24=>35.1,
                  29=>60.2,
                  34=>107.5,
                  39=>167.8,
                  44=>273.4,
                  49=>416.8,
                  54=>561.9,
                  59=>766.8,
                  64=>1,032.0,
                  69=>1,337.2,
                  74=>1,582.4,
                  79=>1,820.9,
                  84=>1,919.3,
                  85=>1,729.3);

  try {

    if(isset($_POST['calculate'])) { // Request to calculate - get user authorization
      // Clean up the medcommons id in case there are spaces
      $mcid = preg_replace("/[^0-9]/", "",$_POST['mcid']);

      // First step is to figure out the correct appliance for the given account
      $result = explode("&",file_get_contents("https://ci.myhealthespace.com/acct/ws/mcidHost.php?mcid=".$mcid));
      $urlResult = explode("=",$result[1]);
      $appliance = $urlResult[1];

      // Here we are figuring out our own URL so that we can pass it as the return URL to the appliance login page
      $protocol = "http";
      if(isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS']) != ""))
        $protocol = "https";
      $returnUrl = $protocol.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
      $returnUrl .= "?mcid=$mcid&appliance=".urlencode($appliance);

      // Redirect to the appliance login page so that we can get an oauth token back
      // that will permit us to get the CCR we want.
      header("Location: ".$appliance."/acct/login.php?mcid=$mcid&next=".urlencode($returnUrl));
      exit;
    }
    else
      if(isset($_REQUEST['oauth_token'])) { // We have the token - this means we are 
                                            // returning from the above user authorization step
      // Fetch the information we need from the return URL
      $auth = $_REQUEST['oauth_token'];
      $mcid = $_REQUEST['mcid'];
      $appliance = $_REQUEST['appliance'];

      // Let's get the CCR in json format since that's easy to play with in PHP
      $ccrJSON = @file_get_contents($appliance."/router/ccrs/$mcid/?json&at=$auth");

      // Check HTTP status code to make sure  we got the CCR back
      list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
      if($status_code >= 400)
        throw new Exception("Error ".$status_code." returned when attempting to access your CCR");

      // Parse the JSON returned to us
      $json = new Services_JSON();
      $ccr = $json->decode($ccrJSON);
      if(!$ccr) // failed to parse!
        throw new Exception("The format of the CCR returned from your HealthURL was invalid.");

      // Get the important details of this patient
      // We have to iterate all the actors looking for the patient
      $patientActorID = $ccr->patient->actorID;
      foreach($ccr->actors->actor as $a) {
        if($a->actorObjectID == $patientActorID) {
          $given = $a->person->name->currentName->given;
          $dob = $a->person->dateOfBirth;

          if(isset($dob->exactDateTime)) {
            $age = (int)((time() - strtotime($dob->exactDateTime)) /  ( 365 * 24 * 60 * 60 ));
          }
          else 
          if(isset($dob->age)) 
           $age = (int)$dob->age->value;

          if(isset($a->person->gender)) {
            $gender = $a->person->gender->text;
          }

          // Found patient, we're done
          break;
        }  
      }

      if(!isset($given) || !isset($age) || !isset($gender))
        throw new Exception("Although your CCR could be accessed, we couldn't locate the necessary information inside it to calculate your cancer risk.  Sorry!");


      // Now we know about the patient details, try and find the risk for patient's age
      $profile = ($gender == "Male") ? $males : $females;
      foreach($profile as $maxage => $incidence) {
        if($age <= $maxage)
          break;
      }

      // Set up the result page and render it
      $t = template("cancer_risk.tpl.php");
      $t->set("token",$_REQUEST['oauth_token']);
      $t->set("ccr",$ccr);
      $t->set("given",$given);
      $t->set("age",$age);
      $t->set("gender",$gender);
      $t->set("incidence",$incidence);
    }
    else {
      $t = template("welcome.tpl.php");
    }
  }
  catch(Exception $e) {
    $t = template("welcome.tpl.php")->set("error",$e->getMessage());
  }

  echo template("layout.tpl.php")->set("contents",$t)->fetch();
?>
