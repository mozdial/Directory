<?php
/**
* @project ApPHP Medical Appointment
* @copyright (c) 2012 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if($objLogin->IsLoggedInAsCustomer() && Modules::IsModuleInstalled('listings')){	
	
	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$listing_id  = MicroGrid::GetParameter('listing_id', false);
	$mode   	= 'view';
	$msg 		= '';
	
	if(!empty($listing_id)){
		$objListingsCategories = new ListingsCategories($listing_id, 'me');
	
		if($action=='add'){		
			$mode = 'add';
		}else if($action=='create'){
			if($objListingsCategories->AddRecord()){
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objListingsCategories->error, false);
				$mode = 'add';
			}
		}else if($action=='edit'){
			$mode = 'edit';
		}else if($action=='update'){
			if($objListingsCategories->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objListingsCategories->error, false);
				$mode = 'edit';
			}		
		}else if($action=='delete'){
			if($objListingsCategories->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objListingsCategories->error, false);
			}
			$mode = 'view';
		}else if($action=='details'){		
			$mode = 'details';		
		}else if($action=='cancel_add'){		
			$mode = 'view';		
		}else if($action=='cancel_edit'){				
			$mode = 'view';
		}
		
		$objListings = Listings::Instance($listing_id);
		$listing_info_name = $objListings->GetField('business_name');
	
		// Start main content
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_MY_LISTINGS=>'',$listing_info_name=>'',_CATEGORIES=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?customer=my_listings', _BUTTON_BACK)
		);
	
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();	
		if($mode == 'view'){		
			$objListingsCategories->DrawViewMode();	
		}else if($mode == 'add'){		
			$objListingsCategories->DrawAddMode();		
		}else if($mode == 'edit'){		
			$objListingsCategories->DrawEditMode($rid);		
		}else if($mode == 'details'){		
			$objListingsCategories->DrawDetailsMode($rid);		
		}
		draw_content_end();
	}else{
		draw_title_bar(_CUSTOMER);
		draw_important_message(_WRONG_PARAMETER_PASSED);		
	}	
}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}
?>