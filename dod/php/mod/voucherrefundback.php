<?php
require_once 'modpay.inc.php';


$paytid = $_REQUEST['paytid'];

	
	sql("Update modcoupons set paytype='amzfpsrefund' where paytid='$paytid'  ");

	header("Location: voucherlist.php?v=1 ");//."?st=$st&v0=".$v[0].'&v1='.$v[1].'&v2='.$v[2].'&t='.$tid);


?>
