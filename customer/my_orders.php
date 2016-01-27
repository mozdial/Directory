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

if($objLogin->IsLoggedInAsCustomer() && Modules::IsModuleInstalled('payments')){	
	
	if(ModulesSettings::Get('payments', 'is_active') == 'yes'){
		
		$action 	= MicroGrid::GetParameter('action');
		$rid   		= MicroGrid::GetParameter('rid');
		$mode  		= "view";
		$msg   		= "";
		$links 		= '';
		
		$objOrders = new Orders(@$objLogin->GetLoggedID());
		
		if($action=="add"){		
			$mode = "add";
		}else if($action=="create"){
			$mode = "view";
		}else if($action=="edit"){
			$mode = "edit";
		}else if($action=="update"){
			$mode = "view";
		}else if($action=="delete"){
			if($objOrders->DeleteRecord($rid)){
				$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			}else{
				$msg = draw_important_message($objOrders->error, false);
			}
			$mode = "view";
		}else if($action=="details"){		
			$mode = "details";		
		}else if($action=="cancel_add"){		
			$mode = "view";		
		}else if($action=="cancel_edit"){				
			$mode = "view";
		}else if($action=="description"){				
			$mode = "description";
		}else if($action=="invoice"){				
			$mode = "invoice";
		}
		
		// Start main content
		if($mode == 'invoice'){
			$links = '<a href="javascript:void(\'invoice|preview\')" onclick="javascript:appPreview(\'invoice\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
		}else if($mode == 'description'){
			$links = '<a href="javascript:void(\'description|preview\')" onclick="javascript:appPreview(\'description\');"><img src="images/printer.png" alt="" /> '._PRINT.'</a>';
		}	
		draw_title_bar(
			prepare_breadcrumbs(array(_MY_ACCOUNT=>"",_ORDERS_MANAGEMENT=>"",ucfirst($action)=>"")),
			$links		
		);

		//if($user_session->IsMessage('notice')) echo $user_session->GetMessage('notice');
		echo $msg;
		
		draw_content_start();	
		if($mode == "view"){		
			$objOrders->DrawViewMode();	
		}else if($mode == "add"){		
			$objOrders->DrawAddMode();		
		}else if($mode == "edit"){		
			$objOrders->DrawEditMode($rid);
		}else if($mode == "details"){		
			$objOrders->DrawDetailsMode($rid);		
		}else if($mode == "description"){		
			$objOrders->DrawOrderDescription($rid);		
		}else if($mode == "invoice"){
			$objOrders->DrawOrderInvoice($rid);
		}
		draw_content_end();	
		
	}else{
		draw_important_message(_NOT_AUTHORIZED);
	}
}else{
	draw_important_message(_NOT_AUTHORIZED);
}

?>