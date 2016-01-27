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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('contact_us')){		

	$action = MicroGrid::GetParameter('action');
	$rid    = MicroGrid::GetParameter('rid');
	$mode   = 'view';
	$msg    = '';
	
	$objAdsenseSettings = new ModulesSettings('adsense');
	
	if($action=='add'){		
		// $mode = 'add';
	}else if($action=='create'){
		//if($objAdsenseSettings->AddRecord()){
		//	$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
		//	$mode = 'view';
		//}else{
		//	$msg = draw_important_message($objAdsenseSettings->error, false);
		//	$mode = 'add';
		//}
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){
		if($objAdsenseSettings->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objAdsenseSettings->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		if($objAdsenseSettings->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objAdsenseSettings->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_ADSENSE=>'',_ADSENSE_SETTINGS=>'',ucfirst($action)=>'')));
    echo '<br>';
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objAdsenseSettings->DrawViewMode();	
	}else if($mode == 'add'){		
		$objAdsenseSettings->DrawAddMode();		
	}else if($mode == 'edit'){		
		$objAdsenseSettings->DrawEditMode($rid);		
	}else if($mode == 'details'){ 
		$objAdsenseSettings->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
    draw_important_message(_NOT_AUTHORIZED);
}

?>