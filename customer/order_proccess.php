<?php

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('payments') && ModulesSettings::Get('payments', 'is_active') == 'yes'){
	
	// handle order
	$task			 = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
	$payment_method  = isset($_POST['payment_method']) ? prepare_input($_POST['payment_method']) : '';
	$additional_info = isset($_POST['additional_info']) ? prepare_input($_POST['additional_info']) : '';
	$order_number    = isset($_POST['order_number']) ? prepare_input($_POST['order_number']) : '';
	
	if($payment_method == 'paypal'){
		$title_desc = _PAYPAL_ORDER;
	}else if($payment_method == '2co'){
		$title_desc = _2CO_ORDER;
	}else if($payment_method == 'authorize'){
		$title_desc = _AUTHORIZE_NET_ORDER;
	}else{
		$title_desc = _ONLINE_ORDER;
	}
				
	draw_title_bar(
		prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_CHECKOUT=>'',$title_desc=>'')),
		prepare_permanent_link('index.php?customer=advertise', _BUTTON_BACK)
	);
	
	// test mode alert
	if(ModulesSettings::Get('payments', 'mode') == 'TEST MODE'){
		draw_message(_TEST_MODE_ALERT_SHORT, true, true);
	}

	if($task == "place_order"){		
		if(AdvertisePlans::PlaceOrder($order_number, $cc_params)){
			draw_success_message(_ORDER_PLACED_MSG);
		}else{
			draw_important_message(AdvertisePlans::$message);
		}
	}else{
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}
}else{
    draw_important_message(_NOT_AUTHORIZED);
}
?>