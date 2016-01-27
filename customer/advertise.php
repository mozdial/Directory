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

	if(Modules::IsModuleInstalled('payments') && ModulesSettings::Get('payments', 'is_active') == 'yes'){	
		draw_title_bar(prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_ADVERTISE=>'')));	
		AdvertisePlans::DrawPlans();
	}else{
		draw_title_bar(_CUSTOMER);
		draw_important_message(_NOT_AUTHORIZED);
	}	
}else{
	draw_title_bar(_CUSTOMER);
	draw_important_message(_NOT_AUTHORIZED);
}

?>