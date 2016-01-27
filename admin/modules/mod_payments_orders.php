<?php

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(@$objLogin->IsLoggedInAsAdmin() && Modules::IsModuleInstalled('payments')){

	$action 	= MicroGrid::GetParameter('action');
	$title_action = $action;
	$rid        = MicroGrid::GetParameter('rid');

	$order_status  = MicroGrid::GetParameter('status', false);
	$order_number  = MicroGrid::GetParameter('order_number', false);
	$customer_id   = MicroGrid::GetParameter('customer_id', false);
	$units_updated = MicroGrid::GetParameter('units_updated', false);

	$mode  = 'view';
	$msg   = '';
	$links = '';
	
	$objOrders = new Orders();
	
	if($action=='add'){		
		$mode = 'add';
	}else if($action=='create'){
		if($objOrders->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objOrders->error, false);
			$mode = 'add';
		}
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){
		if($objOrders->UpdateRecord($rid)){
			if($order_status == '2' || $order_status == '3'){
				// update payment date
				$objOrders->UpdatePaymentDate($rid);				
				// send email to customer
				Orders::SendOrderEmail($order_number, 'completed', $customer_id);
			}else if($order_status == '4'){
				// remove listings from customer
			}
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objOrders->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		if($objOrders->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objOrders->error, false);
		}
		$mode = 'view';
	}else if($action=='details'){		
		$mode = 'details';		
	}else if($action=='cancel_add'){		
		$mode = 'view';		
	}else if($action=='cancel_edit'){				
		$mode = 'view';
	}else if($action=='description'){				
		$mode = 'description';
	}else if($action=='invoice'){				
		$mode = 'invoice';
	}else if($action=='send_invoice'){
		if($objOrders->SendInvoice($rid)){
			$msg = draw_success_message(_INVOICE_SENT_SUCCESS, false);
		}else{
			$msg = draw_important_message($objOrders->error, false);
		}
		$mode = "view";
		$title_action = "Send Invoice";
	}else if($action=='clean_credit_card'){				
		if($objOrders->CleanCreditCardInfo($rid)){
			$msg = draw_success_message(_OPERATION_COMMON_COMPLETED, false);
		}else{
			$msg = draw_important_message($objOrders->error, false);
		}
		$mode = 'view';
		$title_action = 'Clean';
	}
		
	// Start main content
	if($mode == 'invoice'){
		$links .= "<a href=\"javascript:void('invoice|send')\" onclick=\"if(confirm('"._PERFORM_OPERATION_COMMON_ALERT."')) appGoToPage('index.php?admin=mod_payments_orders', '&mg_action=send_invoice&mg_rid=".$rid."&token=".Application::Get("token")."', 'post');\"><img src='images/mail.png' alt='' /> "._SEND_INVOICE."</a>";
		$links .= '&nbsp;|&nbsp;';
		$links .= "<a href=\"javascript:void('invoice|preview')\" onclick=\"javascript:appPreview('invoice');\"><img src='images/printer.png' alt='' /> "._PRINT."</a>";
	}else if($mode == 'description'){
		$links .= "<a href=\"javascript:void('description|preview')\" onclick=\"javascript:appPreview('description');\"><img src='images/printer.png' alt='' /> "._PRINT."</a>";
	}	
	draw_title_bar(
		prepare_breadcrumbs(array(_PAYMENTS=>'',_ORDERS=>'',ucfirst($title_action)=>'')),
		$links
	);

	echo $msg;

	draw_content_start();	
	if($mode == 'view'){
		$objOrders->DrawViewMode();	
	}else if($mode == 'add'){		
		$objOrders->DrawAddMode();		
	}else if($mode == 'edit'){
		$objOrders->DrawEditMode($rid);	
	}else if($mode == 'details'){		
		$objOrders->DrawDetailsMode($rid);		
	}else if($mode == 'description'){
		$objOrders->DrawOrderDescription($rid);		
	}else if($mode == 'invoice'){
		$objOrders->DrawOrderInvoice($rid);		
	}	
	draw_content_end();	

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}
?>