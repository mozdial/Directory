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
	
	$objContactSettings = new ModulesSettings('contact_us');
	
	if($action=='add'){		
		$mode = 'add';
	}else if($action=='create'){
		if($objContactSettings->AddRecord()){
			$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objContactSettings->error, false);
			$mode = 'add';
		}
	}else if($action=='edit'){
		$mode = 'edit';
	}else if($action=='update'){		
		if($objContactSettings->UpdateRecord($rid)){
			$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
			$mode = 'view';
		}else{
			$msg = draw_important_message($objContactSettings->error, false);
			$mode = 'edit';
		}		
	}else if($action=='delete'){
		if($objContactSettings->DeleteRecord($rid)){
			$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
		}else{
			$msg = draw_important_message($objContactSettings->error, false);
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
	draw_title_bar(prepare_breadcrumbs(array(_MODULES=>'',_CONTACT_US=>'',_CONTACT_US_SETTINGS=>'',ucfirst($action)=>'')));
    echo '<br />';
	
	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;

	draw_content_start();
	if($mode == 'view'){		
		$objContactSettings->DrawViewMode();	
	}else if($mode == 'add'){		
		$objContactSettings->DrawAddMode();		
	}else if($mode == 'edit'){		
		$objContactSettings->DrawEditMode($rid);		
	}else if($mode == 'details'){ 
		$objContactSettings->DrawDetailsMode($rid);		
	}
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

?>