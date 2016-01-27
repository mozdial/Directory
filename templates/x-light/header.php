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

<div id="header">
	<div class="site_logo">
		<div class="site_name_link">
			<a href="<?php echo APPHP_BASE; ?>index.php"><?php echo ($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') != 'yes') ? _ADMIN_PANEL : $objSiteDescription->DrawHeader('header_text'); ?></a>
		</div>
		<?php
			echo Search::DrawQuickSearch();
		?>	
	</div>
	
	<ul id="menu" class="nav_top dropdown_outer">
		<?php 
			// Draw top menu
			Menu::DrawTopMenu();	
		?>
	</ul>
	<div class="slogan">
		<?php		
			if($objLogin->IsLoggedInAsAdmin() && Application::Get('preview') == 'yes'){
				echo prepare_permanent_link('index.php?preview=no', _BACK_TO_ADMIN_PANEL, '', 'header');
			}else{
				echo $objSiteDescription->GetParameter('slogan_text');
			}		
		?>
	</div>
	
	<?php
		if(Modules::IsModuleInstalled('news') && ModulesSettings::Get('news', 'news_rss') == 'yes'){			
			echo '<div class="rss"><a href="feeds/rss.xml"><img src="templates/'.Application::Get('template').'/images/spacer.gif" title="RSS Feed" alt="RSS Feed" border="0" width="27" height="62" /></a></div>';
		}
	?>	
	
	<div class="nav_language">
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
</div>