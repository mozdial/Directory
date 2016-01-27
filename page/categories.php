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

draw_title_bar(prepare_breadcrumbs(array(_CATEGORIES=>'')));

$objCategory = Categories::Instance();
$objCategory->DrawSubCategories();
	
?>