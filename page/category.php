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

$objCategory = Categories::Instance();
$objListings = Listings::Instance();
$category_info = $objCategory->GetLevelsInfo(Application::Get('category_id'));
$button = '';

if(Modules::IsModuleInstalled('inquiries')){
    $listings_locations = isset($_REQUEST['listings_locations']) ? (int)$_REQUEST['listings_locations'] : '';
    $listings_sub_locations = isset($_REQUEST['listings_sub_locations']) ? (int)$_REQUEST['listings_sub_locations'] : '';
    if(!empty($category_info['first']['id'])){
        $button = '<input type="button" class="form_button" value="'._SUBMIT_INQUIRY.'" onclick="appGoToPage(\'index.php?page=inquiry_form\',\'&inquiry_category='.$category_info['first']['id'].'&visitor_locations='.$listings_locations.'&visitor_sub_locations='.$listings_sub_locations.'\',\'post\')">';    
    }
}

draw_title_bar(prepare_breadcrumbs(
    array(_CATEGORIES=>prepare_link('categories', '', '', 'all', _SEE_ALL, '', '', true),
        $category_info['third']['name']  => $category_info['third']['link'],
        $category_info['second']['name'] => $category_info['second']['link'],
        $category_info['first']['name']  => '')
    ),
    $button
);

$objCategory->DrawSubCategories(Application::Get('category_id'), 'listings');
$objListings->DrawListings(Application::Get('category_id'));
	
?>