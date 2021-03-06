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

if($objLogin->IsLoggedInAsAdmin() && Modules::IsModuleInstalled('inquiries')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';	
	$inq_id = isset($_GET['inq_id']) ? (int)$_GET['inq_id'] : '0';

	$objInquiries = Inquiries::Instance();
	$inquiry_info = $objInquiries->GetInfoByID($inq_id);
	
	if(count($inquiry_info) > 0){
	
		$objInquiriesReplies = new InquiriesReplies($inq_id);
		
		if($action=='add'){		
			$mode = 'view';
		}else if($action=='create'){
			$mode = 'view';
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
			prepare_breadcrumbs(array(_MODULES=>'',_INQUIRIES_MANAGEMENT=>'','ID:'.$inq_id=>'',_INQUIRIES_REPLIES=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_inquiries_management', _BUTTON_BACK)
		);
	
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objInquiriesReplies->DrawViewMode();	
		}else if($mode == 'add'){		
			$objInquiriesReplies->DrawAddMode();		
		}else if($mode == 'edit'){		
			$objInquiriesReplies->DrawEditMode($rid);		
		}else if($mode == 'details'){		
			$objInquiriesReplies->DrawDetailsMode($rid);		
		}
		draw_content_end();

	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_MODULES=>'',_INQUIRIES_MANAGEMENT=>'',_INQUIRIES_REPLIES=>''))
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

?>