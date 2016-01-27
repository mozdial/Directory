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

if($objLogin->IsLoggedInAsCustomer() && Modules::IsModuleInstalled('inquiries')){
	
	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$act    = MicroGrid::GetParameter('act', false);
	$mode   = 'view';
	$msg    = '';	
	$inq_id = isset($_GET['inq_id']) ? (int)$_GET['inq_id'] : '0';
	$customer_id = $objLogin->GetLoggedID();
	
	$objInquiries = Inquiries::Instance();
	
	if($objInquiries->CustomerRelatedToInquire($inq_id)){
	
		$objInquiriesReplies = new InquiriesReplies($inq_id, $customer_id);		
		
		if($act == 'add'){
			$customer_replies = (!empty($customer_id)) ? InquiriesReplies::GetInquiryRepliesForCustomer($inq_id, $customer_id) : 0;
			$inquiry_info = $objInquiries->GetInfoByID($inq_id);
			$is_active    = isset($inquiry_info['is_active']) ? $inquiry_info['is_active'] : 0;
			$add_mode     = (($is_active && !empty($customer_id) && !$customer_replies) ? true : false);
			if($add_mode){
				$action = 'add';
				$objInquiriesReplies->SetActions(array('add'=>$add_mode));
			}		
		}
		
		if($action=='add'){		
			$mode = 'add';
		}else if($action=='create'){
			if($objInquiriesReplies->AddRecord()){				
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				// refresh the class 
				$objInquiriesReplies = new InquiriesReplies($inq_id, $objLogin->GetLoggedID());
				$mode = 'view';				
			}else{
				$msg = draw_important_message($objInquiriesReplies->error, false);
				$mode = 'add';
			}
		}else if($action=='edit'){
			$mode = 'edit';
		}else if($action=='update'){
			if($objInquiriesReplies->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objInquiriesReplies->error, false);
				$mode = 'edit';
			}		
		}else if($action=='delete'){
			if($objInquiriesReplies->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objInquiriesReplies->error, false);
			}
			$mode = 'view';
		}else if($action=='details'){		
			$mode = 'details';		
		}else if($action=='cancel_add'){		
			$mode = 'view';		
		}else if($action=='cancel_edit'){				
			$mode = 'view';
		}
		
		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_INQUIRIES=>'','ID:'.$inq_id=>'',_INQUIRIES_REPLIES=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?customer=inquiries', _BUTTON_BACK)
		);
	
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objInquiriesReplies->DrawViewMode();	
		}else if($mode == 'add'){		
			$objInquiriesReplies->DrawAddMode(array('cancel'=>false));		
		}else if($mode == 'edit'){		
			$objInquiriesReplies->DrawEditMode($rid);		
		}else if($mode == 'details'){		
			$objInquiriesReplies->DrawDetailsMode($rid);		
		}
		draw_content_end();

	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_INQUIRIES=>'',_INQUIRIES_REPLIES=>''))
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}

}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}

?>