<?php
/**
* @project ApPHP Business Directory
* @copyright (c) 2011 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if($objLogin->IsLoggedInAsCustomer() && Modules::IsModuleInstalled('listings')){	

	// handle order
	$task = isset($_REQUEST['task']) ? prepare_input($_REQUEST['task']) : '';
	$payment_type  = isset($_REQUEST['payment_type']) ? prepare_input($_REQUEST['payment_type']) : '';
	$msg  = isset($_REQUEST['msg']) ? prepare_input($_REQUEST['msg']) : '';

    $ccErrors[0] = '';   // No errors
    $ccErrors[1] = _CC_UNKNOWN_CARD_TYPE; 
    $ccErrors[2] = _CC_NO_CARD_NUMBER_PROVIDED;
    $ccErrors[3] = _CC_CARD_INVALID_FORMAT;
    $ccErrors[4] = _CC_CARD_INVALID_NUMBER;
    $ccErrors[5] = _CC_CARD_WRONG_LENGTH; 
	$ccErrors[6] = _CC_CARD_NO_CVV_NUMBER; 
	$ccErrors[7] = _CC_CARD_WRONG_EXPIRE_DATE;
	$ccErrors[8] = _CC_CARD_HOLDER_NAME_EMPTY;
	$msg_text = isset($ccErrors[$msg]) ? $ccErrors[$msg] : '';

	if($payment_type == 'paypal'){
		$title_desc = _PAYPAL_ORDER;
	}else if($payment_type == '2co'){
		$title_desc = _2CO_ORDER;
	}else if($payment_type == 'authorize'){
		$title_desc = _AUTHORIZE_NET_ORDER;
	}else{
		$title_desc = _ONLINE_ORDER;
	}
	
	if(Modules::IsModuleInstalled('payments') && ModulesSettings::Get('payments', 'is_active') == 'yes'){
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_ADVERTISE=>'',$title_desc=>'')),
			prepare_permanent_link('index.php?customer=advertise', _BUTTON_BACK)
		);

		// test mode alert
		if(ModulesSettings::Get('payments', 'mode') == 'TEST MODE'){
			draw_message(_TEST_MODE_ALERT_SHORT, true, true);
		}
		
		if($task == "do_order"){
			if(AdvertisePlans::DoOrder($payment_type)){
				AdvertisePlans::DrawPrepayment();					
			}else{
				draw_important_message(AdvertisePlans::$message);
			}
		}else if($task == "repeat_order"){	
			draw_important_message($msg_text);	
			AdvertisePlans::ReDrawPrepayment();
		}else{
			draw_important_message(_WRONG_PARAMETER_PASSED);
		}		
	}else{
		draw_title_bar(_CUSTOMER);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}

?>