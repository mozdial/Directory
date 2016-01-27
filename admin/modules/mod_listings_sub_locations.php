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
	$lid 	= isset($_GET['lid']) ? (int)$_GET['lid'] : '0';
	$mode   = 'view';
	$msg    = '';
	
	// Start main content
	$objListingsLocations = new ListingsLocations();
	$location_info = $objListingsLocations->GetInfoByID($lid);
	$location_info_name = isset($location_info['name']) ? $location_info['name'] : '';
	
	if(!empty($lid) && count($location_info)){

		$objListingsSubLocations = new ListingsSubLocations($lid);
		
		if($action=='add'){		
			$mode = 'add';
		}else if($action=='create'){
			if($objListingsSubLocations->AddRecord()){		
				$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objListingsSubLocations->error, false);
				$mode = 'add';
			}
		}else if($action=='edit'){
			$mode = 'edit';
		}else if($action=='update'){
			if($objListingsSubLocations->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objListingsSubLocations->error, false);
				$mode = 'edit';
			}		
		}else if($action=='delete'){
			if($objListingsSubLocations->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objListingsSubLocations->error, false);
			}
			$mode = 'view';
		}else if($action=='details'){		
			$mode = 'details';		
		}else if($action=='cancel_add'){		
			$mode = 'view';		
		}else if($action=='cancel_edit'){				
			$mode = 'view';
		}
	
		draw_title_bar(
			prepare_breadcrumbs(array(_LISTINGS_MANAGEMENT=>'',$location_info_name=>'',_SUB_LOCATIONS=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_listings_locations', _BUTTON_BACK)				   
		);
	
		//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
		echo $msg;
	
		draw_content_start();
		if($mode == 'view'){		
			$objListingsSubLocations->DrawViewMode();	
		}else if($mode == 'add'){		
			$objListingsSubLocations->DrawAddMode();		
		}else if($mode == 'edit'){		
			$objListingsSubLocations->DrawEditMode($rid);		
		}else if($mode == 'details'){		
			$objListingsSubLocations->DrawDetailsMode($rid);		
		}
		draw_content_end();		
	}else{
		draw_title_bar(_ADMIN);
		draw_important_message(_WRONG_PARAMETER_PASSED);		
	}		
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

?>