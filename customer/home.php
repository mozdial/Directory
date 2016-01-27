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

if($objLogin->IsLoggedInAsCustomer()){

	draw_title_bar(prepare_breadcrumbs(array(_MY_ACCOUNT=>'',_ACCOUNT_PANEL=>'')));
	
?>
	<div style="padding:5px 0;">
	<?php

		$msg = '<div style="padding:9px;min-height:250px">';
        $welcome_text = _WELCOME_CUSTOMER_TEXT;
        $welcome_text = str_replace('_FIRST_NAME_', $objLogin->GetLoggedFirstName(), $welcome_text);
		$welcome_text = str_replace('_LAST_NAME_', $objLogin->GetLoggedLastName(), $welcome_text);
        $welcome_text = str_replace('_TODAY_', _TODAY.': <b>'.format_datetime(@date('Y-m-d H:i:s'), '', '', true).'</b>', $welcome_text);
		$welcome_text = str_replace('_LAST_LOGIN_', _LAST_LOGIN.': <b>'.format_datetime($objLogin->GetLastLoginTime(), '', _NEVER, true).'</b>', $welcome_text);
		$welcome_text = str_replace('_HOME_', _HOME, $welcome_text);
        $welcome_text = str_replace('_EDIT_MY_ACCOUNT_', _EDIT_MY_ACCOUNT, $welcome_text);
		$welcome_text = str_replace('_MY_LISTINGS_', _MY_LISTINGS, $welcome_text);
		$welcome_text = str_replace('_ADVERTISE_', _ADVERTISE, $welcome_text);
		$welcome_text = str_replace('_ORDERS_PAGE_DESCR_', ((Modules::IsModuleInstalled('payments') == 'yes') ? _DASHBOARD_ORDERS_LINK : ''), $welcome_text);
		$welcome_text = str_replace('_INQUIRIES_PAGE_DESCR_', ((Modules::IsModuleInstalled('inquiries') == 'yes') ? _DASHBOARD_INQUIRIES_LINK : ''), $welcome_text);
		
        $msg .= $welcome_text;
		$msg .= '<p><br /></p>';
        $msg .= '</div>';	
	
		draw_message($msg, true, false);		
	?>
    </div>
<?php
}else if($objLogin->IsLoggedIn()){
    draw_title_bar(prepare_breadcrumbs(array(_GENERAL=>'')));
    draw_important_message(_NOT_AUTHORIZED);
}else{
    draw_title_bar(prepare_breadcrumbs(array(_CUSTOMER=>'')));
    draw_important_message(_MUST_BE_LOGGED);
}
?>