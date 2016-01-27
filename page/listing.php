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

$objListing = Listings::Instance(Application::Get('listing_id'));
$objCategory = Categories::Instance();

$category_info = $objCategory->GetLevelsInfo($objListing->GetField('category_id'));
draw_title_bar(prepare_breadcrumbs(
    array(_CATEGORIES=>prepare_link('categories', '', '', 'all', _SEE_ALL, '', '', true),
        $category_info['third']['name']=>$category_info['third']['link'],
        $category_info['second']['name']=>$category_info['second']['link'],
        $category_info['first']['name']=>$category_info['first']['link'])
    )
);

$objListing->DrawListing();
	
?>