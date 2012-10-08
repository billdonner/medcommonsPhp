<?
  require_once "alib.inc.php";
  require_once "template.inc.php";

  // First remove all the entries from the patient list
  pdo_execute("delete pe.*
               from practiceccrevents pe,
                    modcoupons v
                    where v.mcid <> 0
                      and pe.PatientIdentifier = v.mcid");

  // Delete all temporary vouchers and accounts
  pdo_execute("delete from modcoupons");

  echo template("base.tpl.php")->set("content","
      <h2>Demonstration Content Reset</h2>
      <p>All demonstration data has been reset.</p>")->fetch();
?>
