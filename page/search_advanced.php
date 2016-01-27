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

$task    = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
$keyword = isset($_POST['keyword']) ? strip_tags(prepare_input($_POST['keyword'])) : '';
		   if($keyword == _SEARCH_KEYWORDS.'...') $keyword = '';
$p       = isset($_POST['p']) ? (int)$_POST['p'] : '';

$objSearch = new Search();
$search_result = '';

draw_title_bar(_ADVANCED_SEARCH);

Search::DrawAdvancedSearch();

// Check if there is a page 
if($task == 'advanced_search'){
	$search_result = $objSearch->SearchBy($keyword, $p, 'listings');
	if(!empty($search_result)){		
		$objSearch->DrawSearchResult($search_result, $p, $keyword, 'advanced');
	}else{
		draw_important_message(_NO_RECORDS_FOUND);
	}
}	
	
?>