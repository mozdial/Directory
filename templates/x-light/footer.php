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

<div id="footer">
    <ul>
        <li>
            <?php 
                // Draw footer menu
                Menu::DrawFooterMenu();	
            ?>		  
        </li>
    </ul>
    <form name="frmLogout" id="frmLogout" style="padding:0px;margin:0px;" action="index.php" method="post">
    <p>
        <?php echo $footer_text = $objSiteDescription->DrawFooter(false); ?>
        <?php if(!empty($footer_text)) echo '&nbsp;'.draw_divider(false).'&nbsp;'; ?>
        <?php if($objLogin->IsLoggedIn()){ ?>
            <?php draw_hidden_field('submit_logout', 'logout'); ?>
            <a class="main_link" href="javascript:appFormSubmit('frmLogout');"><?php echo _BUTTON_LOGOUT; ?></a>
        <?php }else{ ?>
            <?php echo prepare_permanent_link('index.php?admin=login', _ADMIN_LOGIN, '', 'main_link'); ?>
        <?php } ?>
    </p>
    </form>
</div>