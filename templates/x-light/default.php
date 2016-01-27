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

header('content-type: text/html; charset=utf-8');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="<?php echo Application::Get('tag_keywords'); ?>" />
	<meta name="description" content="<?php echo Application::Get('tag_description'); ?>" />
    <meta name="author" content="ApPHP Company - Advanced Power of PHP">
    <meta name="generator" content="ApPHP Business Directory v<?php echo CURRENT_VERSION; ?>">

    <title><?php echo Application::Get('tag_title'); ?></title>

    <base href="<?php echo APPHP_BASE; ?>" /> 
	<link rel="SHORTCUT ICON" href="<?php echo APPHP_BASE; ?>images/icons/apphp.ico" />
	
	<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/style.css" type="text/css" rel="stylesheet" />
	<?php if(Application::Get('lang_dir') == 'rtl'){ ?>
		<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/style-rtl.css" type="text/css" rel="stylesheet" />
	<?php } ?>
	<!--[if IE]>
	<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/style-ie.css" type="text/css" rel="stylesheet" />
	<![endif]-->
	<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/print.css" type="text/css" rel="stylesheet" media="print" />

	<!-- Opacity Module -->
	<link href="<?php echo APPHP_BASE; ?>modules/opacity/opacity.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="<?php echo APPHP_BASE; ?>modules/opacity/opacity.js"></script>

	<script type="text/javascript" src="<?php echo APPHP_BASE; ?>js/jquery-1.6.3.min.js"></script>
	<script type="text/javascript" src="<?php echo APPHP_BASE; ?>js/main.js"></script>

    <?php echo Application::SetLibraries(); ?>    	
    <?php
	    $banner_image = '';
		Banners::DrawBannersTop($banner_image, false);
    ?>
</head>
<body>
<div id="site" dir="<?php echo Application::Get('lang_dir');?>">
	<div id="wrapper">
		
		<!-- HEADER -->
		<?php include_once 'templates/'.Application::Get('template').'/header.php'; ?>
		
		<div id="content">
			<div id="sidebar_left" class="no_print">
				<!-- LEFT COLUMN -->
				<?php
					// Draw menu tree
					Menu::DrawMenu('left'); 
				?>
				<!-- END OF LEFT COLUMN -->			
			</div>
			<div id="main">				
				<!-- BANNERS -->
				<?php echo $banner_image;?>
				<!-- INQUIRIES GUIDE BLOCK -->
				<?php Inquiries::DrawTopGuideBlock(); ?>	
				
				<div class="center_box_wrapper <?php echo Application::Get('defined_alignment');?>">
				<!-- MAIN CONTENT -->
				<?php		
					if((Application::Get('page') != '') && file_exists('page/'.Application::Get('page').'.php')){
						include_once('page/'.Application::Get('page').'.php');
					}else if((Application::Get('customer') != '') && file_exists('customer/'.Application::Get('customer').'.php')){
						if(Modules::IsModuleInstalled('customers')){	
							include_once('customer/'.Application::Get('customer').'.php');
						}else{
							include_once('customer/404.php');
						}
					}else if((Application::Get('admin') != '') && file_exists('admin/'.Application::Get('admin').'.php')){
						include_once('admin/'.Application::Get('admin').'.php');
					}else{
						if(Application::Get('template') == 'admin'){
							include_once('admin/home.php');
						}else{										
							include_once('page/pages.php');										
						}
					}
				?>
				<!-- END OF MAIN CONTENT -->
				</div>
			</div>
			<div id="sidebar_right">
				<!-- RIGHT COLUMN -->
				<?php
					// Draw menu tree
					Menu::DrawMenu('right'); 
				?>
				<!-- END OF RIGHT COLUMN -->			
			</div>				
		</div>
		
		<!-- FOOTER -->
		<?php include_once 'templates/'.Application::Get('template').'/footer.php'; ?>
		
	</div>
</div>
</body>
</html>