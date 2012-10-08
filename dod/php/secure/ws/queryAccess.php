<?PHP
require_once "../ws/securewslibdb.inc.php";

/**
 * Queries the rights table to find which accounts have access to the given
 * user's document's and returns the list of accounts and associated access rights.
 */
class queryAccessWs extends jsonrestws {

	function jsonbody() {

		$accid=$this->cleanreq('accid');

    $query = "select distinct r.rights_id, coalesce(r.account_id, es.es_identity) as account_id,
                es.es_identity_type as es_identity_type, r.rights, r.es_id as es_id,
                es.es_create_date_time as es_create_date_time, es.es_first_name as es_first_name,
                es.es_last_name as es_last_name, atp.at_token as application_token
              from rights r              
              left join external_share es on es.es_id = r.es_id
              left join authentication_token at on at.at_es_id = es.es_id
              left join authentication_token atp on atp.at_id = at.at_parent_at_id
                where r.storage_account_id = '$accid'
                and r.storage_account_id is not null
                and r.active_status = 'Active'";
      
		$result = $this->dbexec($query,"can not select from table rights - ");
    if($result===false) 
       return false;

    $rights = array();
    while ($r = mysql_fetch_object($result)) {
      // Legacy hack
      if($r->rights == "ALL") {
        $r->rights = "RW";
      }
      $rights[]=$r;
		}
    return $rights;
	}
}

//main
$x = new queryAccessWs();
$x->handlews("response_UserInfo");

?>
