<?php

/**
 *	Class Search (for Business Directory ONLY)
 *  -------------- 
 *  Description : encapsulates search properties
 *  Usage       : BusinessDirectory
 *  Updated	    : 19.12.2012
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *	__construct             DrawQuickSearch			HighLight			 
 *	__destruct              DrawAdvancedSearch
 *	SearchBy
 *	DrawSearchResult
 *	DrawPopularSearches
 *	
 **/

class Search {

	private $pageSize;
	private $totalSearchRecords;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		$this->pageSize = 20;
		$this->totalSearchRecords = 0;        
    }

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Searchs in pages by keyword
	 *		@param $keyword - keyword
	 *		@param $page
	 *		@param $search_in
	 */	
	public function SearchBy($keyword, $page = 1, $search_in = 'listings')
	{		
		$lang_id = Application::Get('lang');
		$order_by_clause = 'ASC';
		
		if($search_in == 'news'){
			$sql = 'SELECT
						CONCAT(\'page=news&nid=\', id) as url,
						header_text as title,
						body_text as text,
						\'article\' as content_type,
						\'\' as link_url 
					FROM '.TABLE_NEWS.' n
					WHERE
						language_id = \''.$lang_id.'\' AND
						(
						  header_text LIKE \'%'.encode_text($keyword).'%\' OR
						  body_text LIKE \'%'.encode_text($keyword).'%\'
						)';
			$order_field = 'n.id';
		}else if($search_in == 'pages'){
			$sql = 'SELECT
						CONCAT(\'page=pages&pid=\', id) as url,
						page_title as title,
						page_text as text,
						content_type,
						link_url 
					FROM '.TABLE_PAGES.' p
					WHERE
						language_id = \''.$lang_id.'\' AND
						is_published = 1 AND
						show_in_search = 1 AND
						is_removed = 0 AND
						(finish_publishing = \'0000-00-00\' OR finish_publishing >= \''.date('Y-m-d').'\') AND 						
						(
						  page_title LIKE \'%'.encode_text($keyword).'%\' OR
						  page_text LIKE \'%'.encode_text($keyword).'%\'
						)';
			$order_field = 'p.id';			
		}else{
			$sel_categories 			= isset($_POST['sel_categories']) ? (int)$_POST['sel_categories'] : '';
			$sel_listings_locations     = isset($_POST['sel_listings_locations']) ? prepare_input($_POST['sel_listings_locations']) : '';
			$sel_listings_sub_locations = isset($_POST['sel_listings_sub_locations']) ? prepare_input($_POST['sel_listings_sub_locations']) : '';
			$sel_view 					= isset($_POST['sel_view']) ? prepare_input($_POST['sel_view']) : '';
			$sel_sortby 				= isset($_POST['sel_sortby']) ? prepare_input($_POST['sel_sortby']) : '';
			$order_by_clause 			= isset($_POST['sel_orderby']) ? prepare_input($_POST['sel_orderby']) : 'ASC';
			$chk_with_images 			= isset($_POST['chk_with_images']) ? prepare_input($_POST['chk_with_images']) : '';

			// 'listings' or 'empty'
			$sql = 'SELECT
						CONCAT(\'page=listing&lid=\', l.id) as url,
						ld.business_name as title,
						ld.business_description as text,
						\'article\' as content_type,
						\'\' as link_url
						'.(($chk_with_images == '1') ? ', l.image_file_thumb' : '').' 
					FROM '.TABLE_LISTINGS.' l
						'.(($sel_categories != '') ? 'LEFT OUTER JOIN '.TABLE_LISTINGS_CATEGORIES.' lc ON l.id = lc.listing_id' : '').'						
						LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
					WHERE
						l.is_published = 1 AND					
						ld.language_id = \''.$lang_id.'\' AND 
						'.(($sel_categories != '') ? 'lc.category_id = \''.$sel_categories.'\' AND ' : '').'
						'.(($sel_listings_locations != '') ? 'l.listing_location_id = \''.$sel_listings_locations.'\' AND ' : '').'
						'.(($sel_listings_sub_locations != '') ? 'l.listing_sub_location_id = \''.$sel_listings_sub_locations.'\' AND ' : '').'
						'.(($sel_view == '1') ? ' l.date_published LIKE \'%'.date('Y-m-d').'%\' AND ' : '').'
						'.(($sel_view == '2') ? ' l.date_published LIKE \'%'.date('Y-m-d', strtotime('-1 day')).'%\' AND ' : '').'
						'.(($sel_view == '3') ? ' l.date_published >= \'%'.date('Y-m-d', strtotime('-7 days')).'%\' AND ' : '').'
						'.(($chk_with_images == '1') ? ' (l.image_file != \'\') AND' : '').'
						(
							'.(!empty($keyword) ?
							   'l.keywords LIKE \'%,'.encode_text($keyword).'%\' OR
							    l.keywords LIKE \'%'.encode_text($keyword).',%\' OR
								ld.business_name LIKE \'%'.encode_text($keyword).'%\' OR
							    ld.business_address LIKE \'%'.encode_text($keyword).'%\' OR
							    ld.business_description LIKE \'%'.encode_text($keyword).'%\'' :
							'1=1').'
						)';
			$order_field = 'l.id';
			if($sel_sortby == '0') $order_field = 'l.date_published';
		}
		
		if(!is_numeric($page) || (int)$page <= 0) $page = 1;
		$this->totalSearchRecords = (int)database_query($sql, ROWS_ONLY);
		$total_pages = (int)($this->totalSearchRecords / $this->pageSize);			
		if(($this->totalSearchRecords % $this->pageSize) != 0) $total_pages++;
		$start_row = ($page - 1) * $this->pageSize;		
		$result = database_query($sql.' ORDER BY '.$order_field.' '.$order_by_clause.' LIMIT '.$start_row.', '.$this->pageSize, DATA_AND_ROWS);
		
		// update search results table		
		if((strtolower(SITE_MODE) != 'demo') && ($result[1] > 0)){
			$sql = 'INSERT INTO '.TABLE_SEARCH_WORDLIST.' (word_text, word_count) VALUES (\''.$keyword.'\', 1) ON DUPLICATE KEY UPDATE word_count = word_count + 1';
			database_void_query($sql);

			// store table contains up to 1000 records
			$sql = 'SELECT id, COUNT(*) as cnt FROM '.TABLE_SEARCH_WORDLIST.' ORDER BY word_count ASC';
			$res1 = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($res1[1] > 0 && $res1[0]['cnt'] > 1000){
				$sql = 'DELETE FROM '.TABLE_SEARCH_WORDLIST.' WHERE id = '.(int)$res1[0]['id'];
				database_void_query($sql);
			}						
		}		
		return $result;
	}

	/**
	 * Draws search result
	 *		@param $search_result - search result
	 *		@param $page
	 *		@param $keyword
	 *		@param $type
	 */	
	public function DrawSearchResult($search_result, $page = 1, $keyword = '', $type = 'quick')
	{		
		$total_pages = (int)($this->totalSearchRecords / $this->pageSize);
		if(!is_numeric($total_pages) || (int)$total_pages <= 0) $total_pages = 1;

		if($search_result != '' && $search_result[1] > 0){
			echo '<div class="pages_contents">';					
			for($i = 0; $i < $search_result[1]; $i++){		
				if($search_result[0][$i]['content_type'] == 'article'){
					echo ($i+1).'. '.prepare_permanent_link('index.php?'.$search_result[0][$i]['url'], decode_text($search_result[0][$i]['title'])).'<br />';

					if(isset($search_result[0][$i]['image_file_thumb'])){
						echo '<img src="images/listings/'.$search_result[0][$i]['image_file_thumb'].'" style="width:42px;height:42px;margin:4px;" align="'.Application::Get('defined_left').'" alt="" />';
					}
					
					$page_text = $search_result[0][$i]['text'];
					$page_text = str_replace(array('\\r', '\\n'), '', $page_text);
					$page_text = preg_replace('/{module:(.*?)}/i', '', $page_text);
					$page_text = strip_tags($page_text);
					$page_text = decode_text($page_text);
					
					if(!empty($keyword)) $page_text = $this->HighLight($page_text, array($keyword));

					echo substr_by_word($page_text, 512).'...<br />';
				}else{
					echo ($i+1).'. <a href="'.$search_result[0][$i]['link_url'].'">'.decode_text($search_result[0][$i]['title']).'</a> <img src="images/external_link.gif" alt="" /><br />';					
				}
				echo '<br />';				
				draw_line();		
				echo '<br />';
			}
			echo '<b>'._PAGES.':</b> ';
			for($i = 1; $i <= $total_pages; $i++){
				echo '<a class="paging_link" href="javascript:void(0);" onclick="javascript:appPerformSearch('.$i.', \''.(($type == 'advanced') ? 'frmAdvSearch' : 'frmQuickSearch').'\');">'.(($i == $page) ? '<b>['.$i.']</b>' : $i).'</a> ';
			}
			echo '</div>';
		}else{
			draw_important_message(_NO_RECORDS_FOUND);		
		}				
	}	

	/**
	 * Draws popular search keywords
	 * 		@param $draw
	 */
	public function DrawPopularSearches($draw = true)
	{
		$output = '';
		$sql = 'SELECT word_text, word_count FROM '.TABLE_SEARCH_WORDLIST.' ORDER BY word_count DESC LIMIT 0, 20';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$output .= '<div class="pages_contents"><a href="javascript:void(0);" onclick="appToggleJQuery(\'popular_search\')">'._POPULAR_SEARCH.' +</a></div>';
			$output .= '<fieldset class="popular_search">';
			$output .= '<legend>'._KEYWORDS.'</legend>';
			for($i = 0; $i < $result[1]; $i++){
				if($i > 0) $output .= ', ';
				$output .= '<a onclick="javascript:appPerformSearch(1, \'frmQuickSearch\', \''.$result[0][$i]['word_text'].'\');" href="javascript:void(0)">'.$result[0][$i]['word_text'].'</a>';
			}
			$output .= '</fieldset>';			
		}

		if($draw) echo $output;
		else return $output;
	}		
	
	/**
	 * Draws quick search form
	 * 		@param $draw
	 */
	public static function DrawQuickSearch($draw = true)
	{	
		$keyword = isset($_POST['keyword']) ? trim(prepare_input($_POST['keyword'])) : _SEARCH_KEYWORDS.'...';
		$keyword   = str_replace('"', '&#034;', $keyword);
		$keyword   = str_replace("'", '&#039;', $keyword);			
				
		$output = '<div class="header_search">
			<form name="frmQuickSearch" action="index.php?page=search" method="post">
				'.draw_hidden_field('task', 'quick_search', false).'
				'.draw_hidden_field('p', '1', false).'
				'.draw_hidden_field('search_in', 'listings', false, 'search_in').'
				'.draw_token_field(false).'
				<div class="search_adv">
					<a href="index.php?page=search_advanced">'._ADVANCED.'</a>
				</div>
				<div class="search_input">
					<input onblur="if(this.value == \'\') this.value=\''._SEARCH_KEYWORDS.'...\';"
						   onfocus="if(this.value == \''._SEARCH_KEYWORDS.'...\') this.value = \'\';"
						   maxlength="50" 
						   value="'.$keyword.'" name="keyword" class="search_field" />
				</div>				
				<img class="search_button" src="templates/'.Application::Get('template').'/images/sbutton.png" onclick="appQuickSearch()" alt="'._SEARCH.'" />
			</form>
		</div>';
		
		if($draw) echo $output;
		else return $output;		
	}	
	
	/**
	 * Draws advanced search form
	 * 		@param $draw
	 */
	public static function DrawAdvancedSearch($draw = true)
	{
		$keyword = isset($_POST['keyword']) ? trim(prepare_input($_POST['keyword'])) : '';
		$keyword   = str_replace('"', '&#034;', $keyword);
		$keyword   = str_replace("'", '&#039;', $keyword);			
		$search_in = Application::Get('search_in');
		$sel_categories = isset($_POST['sel_categories']) ? (int)$_POST['sel_categories'] : '';
		$sel_listings_locations     = isset($_POST['sel_listings_locations']) ? prepare_input($_POST['sel_listings_locations']) : '';
		$sel_listings_sub_locations = isset($_POST['sel_listings_sub_locations']) ? prepare_input($_POST['sel_listings_sub_locations']) : '';
		$sel_view = isset($_POST['sel_view']) ? prepare_input($_POST['sel_view']) : '';
		$sel_sortby = isset($_POST['sel_sortby']) ? prepare_input($_POST['sel_sortby']) : '';
		$sel_orderby = isset($_POST['sel_orderby']) ? prepare_input($_POST['sel_orderby']) : '';
		$chk_with_images = isset($_POST['chk_with_images']) ? prepare_input($_POST['chk_with_images']) : '';

		// prepare categories array
		$objCategories = Categories::Instance();
		$total_categories = $objCategories->GetAllExistingCategories();

		$total_countries = Countries::GetAllCountries('priority_order DESC, name ASC');

		
		$output = '<form style="margin:10px" id="frmAdvSearch" name="frmAdvSearch" action="index.php?page=search_advanced" method="post">
			'.draw_hidden_field('task', 'advanced_search', false).'
			'.draw_hidden_field('p', '1', false).'
			'.draw_hidden_field('search_in', 'listings').'
			'.draw_token_field(false).'
		
			<input type="text" name="keyword" value="'.$keyword.'" placeholder="'._SEARCH_KEYWORDS.'...">		
			<span>'._IN.'</span>
			<select name="sel_categories">';
				$output .= '<option value="">-- '._ALL.' --</option>';
				foreach($total_categories as $key => $val){
					if($val['level'] == '1'){
						$category_name = $val['name'];
					}else if($val['level'] == '2'){
						$category_name = '&nbsp;&nbsp;&bull; '.$val['name'];
					}else if($val['level'] == '3'){
						$category_name = '&nbsp;&nbsp;&nbsp;&nbsp;:: '.$val['name'];
					}
					$output .= '<option '.(($sel_categories == $val['id']) ? 'selected="selected"' : '').' value="'.$val['id'].'">'.$category_name.'</option>';
				}				
			$output .= '</select>&nbsp;';

			$output .= ListingsLocations::DrawAllLocations(array('tag_name'=>'sel_listings_locations', 'selected_value'=>$sel_listings_locations, 'javascript_event'=>'onchange="jQuery(\'#frmAdvSearch\').submit();"'), false).' &nbsp;';
			$output .= ListingsSubLocations::DrawAllSubLocations($sel_listings_locations, array('tag_name'=>'sel_listings_sub_locations', 'selected_value'=>$sel_listings_sub_locations), false);			
		
			$output .= '<span style="float:right;">
			<input value="'._SEARCH.'" class="form_button" type="submit">
			</span>
		
			<div style="padding-top:4px;margin-left:-3px;">
			<table><tbody><tr>
			<td>'._VIEW.':
				<select name="sel_view">
					<option value="0" '.(($sel_view == '0') ? ' selected="selected"' : '').'>-- '._ALL.' --</option>
					<option value="1" '.(($sel_view == '1') ? ' selected="selected"' : '').'>'._TODAY.'</option>
					<option value="2" '.(($sel_view == '2') ? ' selected="selected"' : '').'>'._YESTERDAY.'</option>
					<option value="3" '.(($sel_view == '3') ? ' selected="selected"' : '').'>'._LAST_7_DAYS.'</option>
				</select>&nbsp;
			</td>
			<td>'._SORT_BY.':
				<select name="sel_sortby">
					<option value="0" '.(($sel_sortby == '0') ? ' selected="selected"' : '').'>'._DATE.'</option>
					<option value="1" '.(($sel_sortby == '1') ? ' selected="selected"' : '').'>'._VIEW.'</option>
				</select>&nbsp;
			</td>
			<td>'._ORDER_BY.':
				<select name="sel_orderby">
					<option value="ASC" '.(($sel_orderby == 'ASC') ? ' selected="selected"' : '').'>'._ASCENDING.'</option>
					<option value="DESC" '.(($sel_orderby == 'DESC') ? ' selected="selected"' : '').'>'._DESCENDING.'</option>
				</select>&nbsp;
			</td>
			<td style="padding-top:4px;"><input name="chk_with_images" type="checkbox" '.(($chk_with_images == '1') ? ' checked="checked"' : '').' value="1">'._WITH_IMAGES_ONLY.'</td>
			</tr>
			</tbody>
			</table>
			</div>
			
		</form>';
	
		if($draw) echo $output;
		else return $output;		
	
	}
	
	/**
	 * Higlhlight search result
	 * 		@param $str
	 * 		@param $words
	 */
	private function HighLight($str, $words)
	{
		if(!is_array($words) || empty($words) || !is_string($str)){
			return false;
		}
		$arr_words = implode('|', $words);
		return preg_replace('@\b('.$arr_words.')\b@si', '<strong style="background-color:yellow">$1</strong>', $str);
	}
	
}

?>