<?php

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(!@$objLogin->IsLoggedIn()){
	Session::SetMessage("notice", _MUST_BE_LOGGED);
	header("location: index.php?customer=login");
	exit;
}else{
	
	$collect_credit_card = ModulesSettings::Get('payments', 'online_collect_credit_card');

	$task		   = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
	$payment_type  = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : '';

	$cc_params = array();
	$cc_params['cc_type'] 	       = isset($_POST['cc_type']) ? prepare_input($_POST['cc_type']) : '';
	$cc_params['cc_holder_name']   = isset($_POST['cc_holder_name']) ? prepare_input($_POST['cc_holder_name']) : '';
	$cc_params['cc_number'] 	   = isset($_POST['cc_number']) ? prepare_input($_POST['cc_number']) : '';
	$cc_params['cc_expires_month'] = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : '';
	$cc_params['cc_expires_year']  = isset($_POST['cc_expires_year']) ? prepare_input($_POST['cc_expires_year']) : '';
	$cc_params['cc_cvv_code']      = isset($_POST['cc_cvv_code']) ? prepare_input($_POST['cc_cvv_code']) : '';
		
	if($task == "place_order"){		
		$result = check_credit_card($cc_params);
		if($collect_credit_card == "yes" && $result != '0'){
			header('location: index.php?customer=advertise_prepayment&task=repeat_order&cc_type='.$cc_params['cc_type'].'&msg='.(int)$result);
			exit;
		}
	}	
	
}

?>