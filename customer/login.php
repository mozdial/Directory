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

// Draw title bar
draw_title_bar(prepare_breadcrumbs(array(_ACCOUNTS=>'',_CUSTOMER_LOGIN=>'')));

// Check if customer is logged in
if(!$objLogin->IsLoggedIn() && ModulesSettings::Get('customers', 'allow_login') == 'yes'){
	
	if($objLogin->IsWrongLogin()) draw_important_message($objLogin->GetLoginError()).'<br />';
	else if($objLogin->IsIpAddressBlocked()) draw_important_message(_IP_ADDRESS_BLOCKED).'<br />';
	else if($objLogin->IsEmailBlocked()) draw_important_message(_EMAIL_BLOCKED).'<br />';
	else if($objSession->IsMessage('notice')) draw_message($objSession->GetMessage('notice'));

	$remember_me = isset($_POST['remember_me']) ? (int)$_POST['remember_me'] : '';

?>
	<div class="pages_contents">
	<form action="index.php?customer=login" method="post">
		<?php draw_hidden_field('submit_login', 'login'); ?>
		<?php draw_hidden_field('type', 'customer'); ?>
		<?php draw_token_field(); ?>
		
		<table class="loginForm" width="99%" border="0" align="center">
		<tr>
			<td width="12%" nowrap="nowrap"><?php echo _USERNAME;?>:</td>
			<td width="88%"><input class="form_text" type="text" name="user_name" id="txt_user_name" style="width:150px" maxlength="32" value="" autocomplete="off" /></td>
		</tr>
		<tr>
			<td><?php echo _PASSWORD;?>:</td>
			<td><input class="form_text" type="password" name="password" style="width:150px" maxlength="20" value="" autocomplete="off" /></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>		
		<tr>
			<td valign="middle">
				<input class="form_button" type="submit" name="submit" value="<?php echo _BUTTON_LOGIN;?>">
			</td>
			<?php
				if(ModulesSettings::Get('customers', 'remember_me_allow') == 'yes'){
					echo '<td><input type="checkbox" class="form_checkbox" name="remember_me" '.($remember_me == '1' ? 'checked="checked"' : '').' value="1" /> '._REMEMBER_ME.'</td>';				
				}else{
					echo '<td></td>';
				}
			?>			
		</tr>
		<tr><td colspan="2" nowrap height="5px"></td></tr>		
		<tr>
			<td valign="top" colspan="2">
			<?php
				if(ModulesSettings::Get('customers', 'allow_registration') == 'yes'){
					echo prepare_permanent_link('index.php?customer=create_account', _CREATE_ACCOUNT).'<br />';
				}
				if(ModulesSettings::Get('customers', 'allow_reset_passwords') == 'yes'){
					echo prepare_permanent_link('index.php?customer=password_forgotten', _FORGOT_PASSWORD).'<br />';
				}
				if((ModulesSettings::Get('customers', 'allow_registration') == 'yes') && (ModulesSettings::Get('customers', 'reg_confirmation') == 'by email')){
					echo prepare_permanent_link('index.php?customer=resend_activation', _RESEND_ACTIVATION_EMAIL);
				}
			?>
			</td>
		</tr>
		<tr><td colspan="2" nowrap="nowrap" height="5px"></td></tr>		
		</table>
	</form>
	</div>
	<script type="text/javascript">	appSetFocus("txt_user_name");</script>	
<?php
}else if($objLogin->IsLoggedInAsCustomer()){
	echo '<div class="pages_contents">';
	draw_message(_ALREADY_LOGGED, true, true, false, 'width:100%');
	echo '</div>';
?>
	<div class='pages_contents'>
	<form action="index.php?page=logout" method="post">
		<?php draw_hidden_field('submit_logout', 'logout'); ?>
		<?php draw_token_field(); ?>
		<input class="form_button" type="submit" name="submit" value="<?php echo _BUTTON_LOGOUT;?>">
	</form>
	</div>	
<?php
}else{
	$objSession->SetMessage('notice','');
	draw_important_message(_NOT_AUTHORIZED);
}
?>