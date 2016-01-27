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

<!-- logotop -->
<div id="logoTop">
	<!-- cart icon -->
	<div id="siteLogo">
		<a href="<?php echo APPHP_BASE; ?>index.php"><?php echo ($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') != 'yes') ? _ADMIN_PANEL : $objSiteDescription->DrawHeader('header_text'); ?></a>
	</div>
	<div id="siteSlogan">
		<?php
			if($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') == 'yes'){
				echo prepare_permanent_link('index.php?preview=no', _BACK_TO_ADMIN_PANEL, '', 'header');
			}else{
				echo $objSiteDescription->GetParameter('slogan_text');				
			}
		?>
	</div>
</div>


<?php if(!$objLogin->IsLoggedInAsAdmin() || Application::Get('preview') == 'yes'){ ?>
<div id="topMenuBar">
  <table style="border-collapse:collapse;" cellpadding="0" width="100%" border="0">
  <tbody>
  <tr>
    <td width="34"><img width="34" height="41" alt="" src="<?php echo APPHP_BASE;?>templates/default/images/cor-start-<?php echo Application::Get('lang_dir');?>.gif" /></td>
    <td valign="middle">
		<div id="navPagesTop">
		<ul class="nav_top_<?php echo Application::Get('defined_alignment');?> dropdown_outer">
			<?php 
				// Draw top menu
				Menu::DrawTopMenu();	
			?>		  
		</ul>
		</div>
	</td>
	<td style="vertical-align:middle;text-align:<?php echo Application::Get('defined_right');?>;">
	<?php
		if(Modules::IsModuleInstalled('news') && ModulesSettings::Get('news', 'news_rss') == 'yes'){
		///$objSettings->GetParameter('rss_feed')){
			echo '<a href="feeds/rss.xml" title="RSS Feed"><img src="images/rss.gif" alt="RSS Feed" border="0" /></a>&nbsp;';
		}
		echo '<a href="mailto:'.$objSettings->GetParameter('admin_email').'" title="'._CONTACT_US.'"><img src="images/letter.gif" alt="'._EMAIL_ADDRESS.'" border="0" /></a>&nbsp;';
	?>
	</td>
	<td width="9"><img width="9" height="41" alt="" src="<?php echo APPHP_BASE;?>templates/default/images/cor-end-<?php echo Application::Get('lang_dir');?>.gif" /></td>
	</tr>
	</tbody>
	</table>
</div>
<?php
	if(Application::Get('defined_alignment') == 'left'){
		echo '<div class="round_top"><img width="5" height="5" alt="" src="'.APPHP_BASE.'templates/default/images/round-top-left.gif" /></div>';
	}else{
		echo '<div class="round_top_right"><img width="5" height="5" alt="" src="'.APPHP_BASE.'templates/default/images/round-top-left.gif" /></div>';
	}
?>
<?php } ?>


<?php if(!$objLogin->IsLoggedInAsAdmin() || Application::Get('preview') == 'yes'){ ?>
	<div id="navWrapper">	
		<!-- language -->
		<div class="nav_language <?php echo 'float_'.Application::Get('defined_left'); ?>">
			<?php				
				$objLang = new Languages();				
				if($objLang->GetLanguagesCount('front-end') > 1){
					echo '<div style="padding-top:3px;margin:0px 6px;float:'.Application::Get('defined_left').';">'._LANGUAGES.'</div>';			
					$path = 'page';					
					echo '<div style="padding-top:4px;float:left;">';
					$objLang->DrawLanguagesBar($path);
					echo '</div>';
				}				
			?>		
		</div>
		
		<!-- search -->
		<?php
			Search::DrawQuickSearch();
		?>
	</div>
<?php } ?>