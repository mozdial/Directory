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

if($objLogin->IsLoggedInAs('owner','mainadmin')){

	$action 	= MicroGrid::GetParameter('action');
	$rid    	= MicroGrid::GetParameter('rid');
	$cid 		= isset($_GET['cid']) ? (int)$_GET['cid'] : '0';
	$cdid 		= isset($_GET['cdid']) ? (int)$_GET['cdid'] : '0';
	$mode   	= 'view';
	$msg 		= '';
	
	$objCategory = Categories::Instance();
	$category_info = $objCategory->GetInfoByID($cdid);
	$category_info_name = isset($category_info['name']) ? $category_info['name'] : _CATEGORY_DESCRIPTION;
	
	if(count($category_info) > 0){

		$objCategoryDescr = new CategoriesDescription();
		
		if($action=='add'){		
			$mode = 'add';
		}else if($action=='create'){
			#if($objCategoryDescr->AddRecord()){
			#	$msg = draw_success_message(_ADDING_OPERATION_COMPLETED, false);
			#	$mode = 'view';
			#}else{
			#	$msg = draw_important_message($objCategoryDescr->error, false);
			#	$mode = 'add';
			#}
		}else if($action=='edit'){
			$mode = 'edit';
		}else if($action=='update'){
			if($objCategoryDescr->UpdateRecord($rid)){
				$msg = draw_success_message(_UPDATING_OPERATION_COMPLETED, false);
				$mode = 'view';
			}else{
				$msg = draw_important_message($objCategoryDescr->error, false);
				$mode = 'edit';
			}		
		}else if($action=='delete'){
			#if($objCategoryDescr->DeleteRecord($rid)){
			#	$msg = draw_success_message(_DELETING_OPERATION_COMPLETED, false);
			#}else{
			#	$msg = draw_important_message($objCategoryDescr->error, false);
			#}
			#$mode = 'view';
		}else if($action=='details'){		
			$mode = 'details';		
		}else if($action=='cancel_add'){		
			$mode = 'view';		
		}else if($action=='cancel_edit'){				
			$mode = 'view';
		}
		
		// Start main content	
		draw_title_bar(
			prepare_breadcrumbs(array(_LISTINGS_MANAGEMENT=>'',_CATEGORY_DESCRIPTION=>'',$category_info_name=>'',ucfirst($action)=>'')),
			prepare_permanent_link('index.php?admin=mod_categories&cid='.(int)$cid, _BUTTON_BACK)
		);
			
		echo $msg;
	
		draw_content_start();	
		if($mode == 'view'){		
			$objCategoryDescr->DrawViewMode();	
		}else if($mode == 'add'){		
			$objCategoryDescr->DrawAddMode();		
		}else if($mode == 'edit'){		
			$objCategoryDescr->DrawEditMode($rid);		
		}else if($mode == 'details'){		
			$objCategoryDescr->DrawDetailsMode($rid);		
		}
		draw_content_end();	

	}else{
		draw_title_bar(
			prepare_breadcrumbs(array(_LISTINGS_MANAGEMENT=>'',_CATEGORY_DESCRIPTION=>''))
		);
		draw_important_message(_WRONG_PARAMETER_PASSED);
	}
}else{
	draw_important_message(_NOT_AUTHORIZED);
}

?>