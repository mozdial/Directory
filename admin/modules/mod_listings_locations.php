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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('listings')){	

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objListingsLocations = new ListingsLocations();
	
	if($action=='add'){		
		$mode = 'add';
	}else if($action=='create'){
		if($objListingsLocations->AddRecord()){		
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objListingsLocations->error, false);
			$mode = 'add';
		}
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){
		if($objListingsLocations->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objListingsLocations->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		if($objListingsLocations->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objListingsLocations->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_LISTINGS_MANAGEMENT=>'',_SETTINGS=>'',_LOCATIONS=>'',ucfirst($action)=>'')));

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objListingsLocations->DrawViewMode();	
	}else if($mode == 'add'){		
		$objListingsLocations->DrawAddMode();		
	}else if($mode == 'edit'){		
		$objListingsLocations->DrawEditMode($rid);		
	}else if($mode == 'details'){		
		$objListingsLocations->DrawDetailsMode($rid);		
	}
	draw_content_end();		
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

?>