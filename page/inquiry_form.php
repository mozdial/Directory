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

if(Modules::IsModuleInstalled('inquiries')){
    
    
	draw_title_bar(($params['inquiry_type'] == '1') ? _SEND_DIRECT_INQUIRY : _SEND_INQUIRY);
    
	if(!empty($msg)) echo $msg;
	
	if($params['inquiry_type'] == '1'){		
		Inquiries::DrawInquiryDirectForm($params);	
	}else{
		Inquiries::DrawInquiryForm($params);	
	}    
}else{
	draw_title_bar(_PAGE);
	draw_important_message(_NOT_AUTHORIZED);    
}

?>