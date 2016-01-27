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
    
    draw_title_bar(_SEND_INQUIRY);
    
    draw_success_message(_INQUIRY_SENT_SUCCESS_MSG);
    echo '<br>';
	
}else{
	draw_title_bar(_PAGE);
	draw_important_message(_NOT_AUTHORIZED);    
}
	
?>