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

?>

<div id="footerLine"></div>
<div id="footerDiv">
    <table id="footer_tbl" cellpadding="0" width="100%" border="0">
    <tbody>
    <tr>
        <td width="8"></td>
        <td>
            <div id="navSuppWrapper">
                <div id="navSupp">
                    <ul>
                        <li>
                        <?php 
                            // Draw footer menu
                            Menu::DrawFooterMenu();	
                        ?>		  
                        </li>
                    </ul>
                </div>
                <br />        
                <div id="siteInfo">
                    <form name="frmLogout" id="frmLogout" style="padding:0px;margin:0px;" action="index.php" method="post">
                        <?php echo $footer_text = $objSiteDescription->DrawFooter(false); ?>
                        <?php if(!empty($footer_text)) echo '&nbsp;'.draw_divider(false).'&nbsp;'; ?>
                        <?php if($objLogin->IsLoggedIn()){ ?>
                            <?php draw_hidden_field('submit_logout', 'logout'); ?>
                            <a class="main_link" href="javascript:appFormSubmit('frmLogout');"><?php echo _BUTTON_LOGOUT; ?></a>
                        <?php }else{ ?>
                            <?php echo prepare_permanent_link('index.php?admin=login', _ADMIN_LOGIN, '', 'main_link'); ?>
                        <?php } ?>
                    </form>
                </div>            
            </div>
        </td>
        <td width="8"></td></tr>
    <tr>
        <td width="8"><img width="8" height="8" alt="" src="templates/default/images/cor-<?php echo Application::Get('defined_left'); ?>-bot.gif" /></td>
        <td class="tdback"></td>
        <td width="8"><img width="8" height="8" alt="" src="templates/default/images/cor-<?php echo Application::Get('defined_right'); ?>-bot.gif" /></td>
    </tr>
    </tbody>
    </table>
</div>