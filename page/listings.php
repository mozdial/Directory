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

$listing_type = Application::Get('type');

$listing_type_title = '';
if($listing_type == 'featured'){
    $listing_type_title = _FEATURED_LISTINGS;
}else if($listing_type == 'recent'){
    $listing_type_title = _RECENT_LISTINGS;
}

draw_title_bar(
    prepare_breadcrumbs(
        array(_LISTINGS=>'', $listing_type_title=>'')        
    )
);

if(!empty($listing_type_title)){
    if($listing_type == 'featured'){
        Listings::DrawFeaturedAll();    
    }else if($listing_type == 'recent'){
        Listings::DrawRecentAll();    
    }    
}else{
    draw_important_message(_PAGE_UNKNOWN);		
}

?>