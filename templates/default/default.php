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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
	<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/4-col-portfolio.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo APPHP_BASE; ?>templates/<?php echo Application::Get('template');?>/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
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

	<script type="text/javascript" src="<?php echo APPHP_BASE; ?>js/main.js"></script>

    <?php echo Application::SetLibraries(); ?>    	
    <?php
	    $banner_image = '';
		Banners::DrawBannersTop($banner_image, false);
    ?>
</head>
<body id="indexHomeBody" dir="<?php echo Application::Get('lang_dir');?>">
<div id="mainWrapper">

	<!-- HEADER -->
	<?php include_once 'templates/'.Application::Get('template').'/header.php'; ?>

	<table id="contentMainWrapper" cellspacing="0" cellpadding="0" width="100%" border="0">
	<tbody>
	<tr>
		
		<td id="navColumnMain">		
			<div id="indexDefault" class="center_column">				
				<div id="indexDefaultMainContent">					

					<!-- BANNERS -->
					<?php echo $banner_image; ?>
					<!-- INQUIRIES GUIDE BLOCK -->
					<?php Inquiries::DrawTopGuideBlock(); ?>	

					<div class="center_box_wrapper <?php echo Application::Get('defined_alignment');?>">
					<!-- MAIN CONTENT -->
					<?php		
						if(Application::Get('page') != '' && Application::Get('page') != 'home'){							
							if(file_exists('page/'.Application::Get('page').'.php')){	 
								include_once('page/'.Application::Get('page').'.php');
							}else{
								include_once('page/404.php');
							}
						}else if(Application::Get('customer') != ''){
							if(Modules::IsModuleInstalled('customers') && file_exists('customer/'.Application::Get('customer').'.php')){	
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
			</div>			
		</td>
				
	</tr>
	</tbody>
	</table>

	<!-- FOOTER -->
	<?php include_once 'templates/'.Application::Get('template').'/footer.php'; ?>

</div>
</body>
</html>