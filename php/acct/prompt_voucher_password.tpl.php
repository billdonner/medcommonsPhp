<p>You do not currently have consent to access this patient's records.  If you 
have a voucher id and password or PIN to access the patient details, enter it below
to add this patient to your patient list:</p>

<form name='voucherPasswordForm' id='voucherPasswordForm'>
    <input type='hidden' name='vcode' value='<?=$v->voucherid?>'/>
	<table>
	      <tbody>
	        <tr>
	            <th>Voucher ID:</th> <td><?=$v->voucherid?></td>
	        </tr>
	        <tr>
	            <th>Password:</th> <td><input type="password" size="10" name='voucher_password' id='voucher_password'/></td>
	        </tr>
	            <th>&nbsp;</th> <td><input type="button" name='submit' value='Submit' onclick='submitVoucherPassword();'/></td>
	        </tr>
	    </tbody>
	    </tbody>
	</table>
</form>
	
	
	 
	
	