<?
require_once "../alib.inc.php";

/**
 * Settings associated with an account.
 */
class AccountSettings {
  public $groupinstanceid = "";
  public $accid = "";
  public $name = "";
  public $directory = "";
  public $practiceRlsUrl = "";
  public $emergencyCcrGuid = "";
  public $currentCcrGuid = "";
  public $coupon = false;
  public $statusValues;
  public $logicalDocuments = array();
  public $applications = array();

  public static function load($user) {

    $accid = $user->accid;

    $settings = new AccountSettings();

    // If user has a group, return that group for group settings
    if($user->providergroupid) {
      // Note: these properties set in querySettings.php: need to factor them out of there
      $settings->practiceRlsUrl = $user->practiceRlsUrl;
      $settings->groupinstanceid = $user->providergroupid;
      $settings->name = $user->practicename;
      $settings->createdatetime = $user->group_create_date_time;
      $settings->accid = $user->active_group_accid;
    }
    else { // not found as a member of a group, see if can be found as a group directly
      AccountSettings::loadGroupSettings($accid, $settings);
    }

    if(!isset($settings->practiceRlsUrl)) {
      $settings->practiceRlsUrl = "";
    }

    if(!isset($settings->directory)) {
      $settings->directory = "";
    }

    // If not found any other way, check whether account has an RLS specified at the user level
    if($settings->practiceRlsUrl == "") {
      if($accountRls = pdo_first_row("select ar_rls_url from account_rls where ar_accid = ?",array($accid))) {
        $settings->practiceRlsUrl = $accountRls->ar_rls_url;
      }
    }

    $documentSql = "select d1.dt_type, d1.dt_guid from document_type d1
                    left join document_type d2 on d2.dt_type = d1.dt_type and d1.dt_create_date_time < d2.dt_create_date_time and d2.dt_account_id = d1.dt_account_id
                    where d1.dt_account_id='$accid' and d2.dt_type is NULL
                    order by d1.dt_id desc";

    $docs = pdo_query($documentSql,"- Unable to select from document_type");
    $typesFound = array();
    foreach($docs as $doc) {
      if(isset($typesFound[$doc->dt_type]))
        continue;
      $typesFound[$doc->dt_type] = true;
      $settings->logicalDocuments[]= array("type" => $doc->dt_type, "guid" => $doc->dt_guid );
    }

    // Get Emergency CCR, if possible
    $settings->emergencyCcrGuid = getECCRGuid($accid);
    if($settings->emergencyCcrGuid == false) {
      $settings->emergencyCcrGuid = "";
    }

    // Get account statuses
    $row = pdo_first_row("select value from mcproperties where property = 'acAccountStatus'",array());
    $settings->statusValues = $row ? $row->value : "";

    // Is this account a temporary one?
    $cpn = pdo_first_row( "select c.*,s.accid as providerAccId 
                              from modcoupons c, modservices s 
                              where s.svcnum = c.svcnum and c.mcid = ?",array($accid));
    if($cpn) {
      $settings->coupon = $cpn;
    }

    $settings->applications = pdo_query("select ea.*
                               from external_application  ea,
                                     authentication_token at_parent,
                                     authentication_token at_child,
                                     external_share es,
                                     rights r
                               where es.es_id = at_child.at_es_id
                                     and at_child.at_parent_at_id = at_parent.at_id
                                     and at_parent.at_token = ea.ea_key
                                     and r.es_id = es.es_id 
                                     and r.storage_account_id = ?",array($accid));
      
     return $settings;
  }

  public static function loadGroupSettings($accid, &$settings) {
   $groupSettings = pdo_first_row("select g.*,p.practiceRlsUrl
                                     from groupinstances g, practice p
                                     where g.accid = ? and p.providergroupid = g.groupinstanceid",
                                   array($accid));
    if($groupSettings) {
      $settings->groupinstanceid = $groupSettings->groupinstanceid;
      $settings->accid = $groupSettings->accid;
      $settings->name = $groupSettings->name;
      $settings->practiceRlsUrl = $groupSettings->practiceRlsUrl;
      $settings->createdatetime = $groupSettings->createdatetime;
    }
  }
}
?>
