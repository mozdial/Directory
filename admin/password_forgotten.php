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

$act 		    = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
$password_sent 	= (bool)Session::Get('password_sent');
$email 			= isset($_POST['email']) ? prepare_input($_POST['email']) : '';
$msg 			= '';

if($act == 'send'){
	if(!check_email_address($email)){
		$msg = draw_important_message(_EMAIL_IS_WRONG, false);					
	}else{
		if(!$password_sent){
			$objAdmin = new Admins($objSession->GetSessionVariable('session_account_id'));
			if($objAdmin->SendPassword($email)){
				$msg = draw_success_message(_PASSWORD_SUCCESSFULLY_SENT, false);
				Session::Set('password_sent', true);
			}else{
				$msg = draw_important_message($objAdmin->error, false);					
			}
		}else{
			$msg = draw_message(_PASSWORD_ALREADY_SENT, false);
		}
	}
}

// Draw title bar
draw_title_bar(prepare_breadcrumbs(array(_ADMIN=>'',_PASSWORD_FORGOTTEN=>'')));

// Check if user is logged in
if(!$objLogin->IsLoggedIn()){
	echo $msg;
?>
	<div class="pages_contents">
	<form action="index.php?admin=password_forgotten" method="post">
		<?php draw_hidden_field('act', 'send'); ?>
		<?php draw_token_field(); ?>
		
		<table class="loginForm" width="99%" border="0">
		<tr>
			<td colspan="2">
				<?php echo '<p>'._PASSWORD_RECOVERY_MSG.'</p>'; ?>
			</td>
		</tr>
		<tr>
			<td width="12%" nowrap='nowrap'><?php echo _EMAIL_ADDRESS;?>:</td>
			<td width="88%"><input class="form_text" type="text" name="email" id="email" size="22" maxlength="70" autocomplete="off" /></td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>			
		<tr>
			<td>
				<input class="form_button" type="submit" name="btnSend" value="<?php echo _SEND;?>">
			</td>
		</tr>
		<tr><td colspan='2' nowrap height='5px'></td></tr>		
		<tr>
			<td colspan='2'>
				<?php echo prepare_permanent_link('index.php?admin=login', _ADMIN_LOGIN); ?>
			</td>				
		</tr>
		<tr><td colspan='2' nowrap height='5px'></td></tr>		
		</table>
	</form>
	</div>
	<script type="text/javascript">
	appSetFocus('email');
	</script>	
<?php
}else if($objLogin->IsLoggedInAsAdmin()){
	draw_important_message(_ALREADY_LOGGED);
}else{
	draw_important_message(_NOT_AUTHORIZED);
}
?>