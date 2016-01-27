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

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objListings = Listings::Instance();
	
	if($action=='add'){		
		$mode = 'add';
	}else if($action=='create'){
		if($objListings->AddRecord()){
			if(ModulesSettings::Get('listings', 'pre_moderation_allow') == 'yes'){
				$msg = draw_success_message(_SUBMITTED_FOR_MODERATION, false);				
			}else{
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);				
			}
			$mode = 'view';
		}else{
			$msg = draw_important_message($objListings->error, false);
			$mode = 'add';
		}
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){
		if($objListings->UpdateRecord($rid)){
			if($objListings->error != ''){
				$msg = draw_success_message($objListings->error, false);	
			}else{
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			}
			$mode = 'view';
		}else{
			$msg = draw_important_message($objListings->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		if($objListings->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objListings->error, false);
		}
		$mode = 'view';
	}else if($action=='details'){		
		$mode = 'details';		
	}else if($action=='cancel_add'){		
		$mode = 'view';		
	}else if($action=='cancel_edit'){				
		$mode = 'view';
	}

	if($mode == 'view' || $mode == 'add'){
		$objLogin->LoadListings();
	}

	// Start main content
	draw_title_bar(prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_LISTINGS_MANAGEMENT=>'',ucfirst($action)=>'')));

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	if(!empty($msg)){
		echo $msg;
	}else{
		if(Modules::IsModuleInstalled('payments') && ModulesSettings::Get('payments', 'is_active') == 'yes'){
			draw_message(str_replace('_LISTINGS_COUNT_', $objLogin->GetAvailableListings(), _AVAILABLE_LISTINGS_ALERT));
		}
	}
	
	//draw_content_start();
	echo '<div class="pages_contents">';
	if($mode == 'view'){		
		$objListings->DrawViewMode();	
	}else if($mode == 'add'){		
		$objListings->DrawAddMode();		
	}else if($mode == 'edit'){		
		$objListings->DrawEditMode($rid);		
	}else if($mode == 'details'){		
		$objListings->DrawDetailsMode($rid);		
	}
	//draw_content_end();
	echo '</div>';
}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}

?>