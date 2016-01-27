<?php

////////////////////////////////////////////////////////////////////////////////
// PayPal Order Notify
// Last modified: 23.02.2012
////////////////////////////////////////////////////////////////////////////////

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('payments')){
	
	$mode = ModulesSettings::Get('payments', 'mode');

	if(ModulesSettings::Get('payments', 'is_active') == 'yes'){		

		//----------------------------------------------------------------------
		define('LOG_MODE', false);
		define('LOG_TO_FILE', false);
		define('LOG_ON_SCREEN', false);
		
		define('TEST_MODE', ($mode == 'TEST MODE') ? true : false);
		$log_data = '';
		$msg      = '';
		$nl       = "\n";

		// --- Get PayPal response
		$objPaymentIPN 		= new PaymentIPN($_REQUEST, 'paypal');
		$status 			= $objPaymentIPN->GetPaymentStatus();
		$order_number 		= $objPaymentIPN->GetParameter('custom');
	    $transaction_number = $objPaymentIPN->GetParameter('txn_id');
		$payer_status		= $objPaymentIPN->GetParameter('payer_status');
		$pp_payment_type    = $objPaymentIPN->GetParameter('payment_type');
		$total				= $objPaymentIPN->GetParameter('mc_gross');		
		
		// Payment Types   : 0 - Online Order, 1 - PayPal, 2 - 2CO, 3 - Authorize.Net	
		// Payment Methods : 0 - Payment Company Account, 1 - Credit Card, 2 - E-Check
		if($status == 'Completed'){
			if($payer_status == 'verified'){
				$payment_method = '0';
			}else{
				$payment_method = '1';
			}			
		}else{
			$payment_method = ($pp_payment_type == 'echeck') ? '2' : '0'; 
		}
		
		if(TEST_MODE){
			$status = 'Completed';
		}
				
		////////////////////////////////////////////////////////////////////////
		if(LOG_MODE){
			if(LOG_TO_FILE){
				$myFile = 'tmp/logs/payment_paypal.log';
				$fh = fopen($myFile, 'a') or die('can\'t open file');				
			}
	  
			$log_data .= $nl.$nl.'=== ['.date('Y-m-d H:i:s').'] ==================='.$nl;
			$log_data .= '<br>---------------<br>'.$nl;
			$log_data .= '<br>POST<br>'.$nl;
			foreach($_POST as $key => $value) {
				$log_data .= $key.'='.$value.'<br>'.$nl;        
			}
			$log_data .= '<br>---------------<br>'.$nl;
			$log_data .= '<br>GET<br>'.$nl;
			foreach($_GET as $key=>$value) {
				$log_data .= $key.'='.$value.'<br>'.$nl;        
			}        
		}      
		////////////////////////////////////////////////////////////////////////  

		switch($status)    
		{
			// 1 order pending
			case 'Pending':
				$pending_reason = $objPaymentIPN->GetParameter('pending_reason');
				$msg = 'Pending Payment - '.$pending_reason;

				$sql = 'SELECT 
							c.first_name,
							c.last_name,
							c.user_name as customer_name,
							c.preferred_language,
							c.email
					FROM '.TABLE_ORDERS.' o
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON o.customer_id = c.id
					WHERE
						o.order_number = "'.$order_number.'"';

				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){

					$recipient = $result[0]['email'];
					$sender = $objSettings->GetParameter('admin_email');			
					$email_text = '<b>Dear Customer!</b><br />
					Thank you for purchasing from our site!
					Your order has been placed in our system.
					Current status: PENDING.<br />
					  
					Payments from PayPal using an eCheck (electronic funds transfer from your bank account) will be
					credited to your account when your bank clears the transaction. Your PayPal account will show
					an estimated clearing date for the transaction. Once the transaction is cleared, the purchased
					products will be credited to your account in a few minutes.<br /><br />
					
					If you don\'t see any changes on your account during 72 hours,
					please contact us to: '.$sender;
					
					////////////////////////////////////////////////////////////
					send_email_wo_template(
						$recipient,
						$sender,
						'Order placed (eCheck payment in progress - '.$objSiteDescription->GetParameter('header_text').')',
						$email_text
					);
					////////////////////////////////////////////////////////////
				}

				break;
			case 'Completed':
				// 2 order completed					
				$sql = 'SELECT id, order_number, currency, customer_id, advertise_plan_id, listings_amount, order_price, vat_fee, total_price 
						FROM '.TABLE_ORDERS.'
						WHERE order_number = \''.$order_number.'\' AND status = 0';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					write_log($sql);
					
					// check for possible problem or hack attack
					if($total <= 1 || abs($total - $result[0]['total_price']) > 1){
						$ip_address = (isset($_SERVER['HTTP_X_FORWARD_FOR']) && $_SERVER['HTTP_X_FORWARD_FOR']) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
						$message  = 'From IP: '.$ip_address."<br />\n";
						$message .= 'Status: '.$status."<br />\n";
						$message .= 'Possible Attempt of Hack Attack? '."<br />\n";
						$message .= 'Please check this order: '."<br />\n";
						$message .= 'Order Price: '.$result[0]['total_price']."<br />\n";
						$message .= 'Payment Processing Gross Price: '.$total."<br />\n";
						write_log($message);
						break;            
					}

					$sql = 'UPDATE '.TABLE_ORDERS.' SET
								status = 2,
								transaction_number = \''.$transaction_number.'\',
								payment_date = \''.date('Y-m-d H:i:s').'\',
								status_changed = \''.date('Y-m-d H:i:s').'\',
								payment_type = 1,
								payment_method = '.$payment_method.'
							WHERE order_number = \''.$order_number.'\'';
					if(database_void_query($sql)){						
						// update customer orders/listings amount
						Customers::SetOrdersForCustomer($result[0]['customer_id'], '+');
						Customers::SetListingsForCustomer($result[0]['customer_id'], $result[0]['advertise_plan_id'], $result[0]['listings_amount'], '+');

						// send email to customer
						if(Orders::SendOrderEmail($order_number, 'completed', $result[0]['customer_id'])){
							write_log($sql, _ORDER_PLACED_MSG);
						}else{
							write_log($sql, _ORDER_ERROR);
						}						
					}else{
						write_log($sql, mysql_error());
					}					
				}else{
					write_log($sql, 'Error: no records found. '.mysql_error());
				}				
				break;
			case 'Updated':
				// 3 updated already
				$msg = 'Thank you for your order!<br><br>';
				break;
			case 'Failed':
				// 4 this will only happen in case of echeck.
				$msg = 'Payment Failed';
				break;
			case 'Denied':
				// 5 denied payment by us
				$msg = 'Payment Denied';
				break;
			case 'Refunded':
				// 6 payment refunded by us
				$msg = 'Payment Refunded';			
				break;
			case 'Canceled':
				/* 7 reversal cancelled
				 mark the payment as dispute cancelled */
				$msg = 'Cancelled reversal';
				break;	
			default:
				// 0 order is not good
				$msg = 'Unknown Payment Status - please try again.';
				// . $objPaymentIPN->GetPaymentStatus();
				break;
		}

		////////////////////////////////////////////////////////////////////////
		if(LOG_MODE){
			$log_data .= '<br>'.$nl.$msg.'<br>'.$nl;    
			if(LOG_TO_FILE){
				fwrite($fh, strip_tags($log_data));
				fclose($fh);        				
			}
			if(LOG_ON_SCREEN){
				echo $log_data;
			}
		}
		////////////////////////////////////////////////////////////////////////

		if(TEST_MODE){
			header('location: index.php?page=payment_return');
			exit;
		}
	}	
}

function write_log($sql, $msg = ''){
    global $log_data;
    if(LOG_MODE){
        $log_data .= '<br>'.$nl.$sql;
        if($msg != '') $log_data .= '<br>'.$nl.$msg;
    }    
}

?>