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
	$mode   = 'view';
	$msg    = '';	

	$objInquiries = Inquiries::Instance();
	
	if($action=='add'){		
		$mode = 'add';
	}else if($action=='create'){
		$mode = 'view';
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){
		if($objInquiries->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objInquiries->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		$mode = 'view';
	}else if($action=='details'){		
		$mode = 'details';		
	}else if($action=='cancel_add'){		
		$mode = 'view';		
	}else if($action=='cancel_edit'){				
		$mode = 'view';
	}
	
	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_INQUIRIES=>'',ucfirst($action)=>'')));

	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objInquiries->DrawViewMode();	
	}else if($mode == 'add'){		
		$objInquiries->DrawAddMode();		
	}else if($mode == 'edit'){		
		$objInquiries->DrawEditMode($rid);		
	}else if($mode == 'details'){		
		$objInquiries->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}

?>