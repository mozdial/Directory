<?php

/**
 *	Class Menu
 *  -------------- 
 *  Description : encapsulates menu properties
 *  Updated	    : 29.01.2013
 *	Written by  : ApPHP
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				GetAll					
 *	__destruct				DrawMenuSelectBox		
 *	GetName					DrawContentTypeBox
 *	GetParameter			DrawMenuPlacementBox
 *	GetId					DrawMenu
 *	GetOrder				DrawMenuAccessSelectBox
 *	MenuMove				DrawTopMenu
 *	MenuUpdate				DrawFooterMenu
 *	MenuCreate				GetTopMenus
 *	MenuDelete				GetMenuPages
 *	              			GetMenuLinks (private)
 *	              			GetAllFooter (private)
 *	                        GetMenus
 *	
 **/

class Menu {

	private $id;
	
	protected $menu;
	protected $languageId;
	protected $whereClause;
	
	public $langIdByUrl;
	public $error;    
	
	//==========================================================================
    // Class Constructor
	//		@param $id
	//==========================================================================
	function __construct($id = '')
	{
		$this->id = $id;
		$this->languageId   = (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? prepare_input($_REQUEST['language_id']) : Languages::GetDefaultLang();
		$this->whereClause  = '';
		$this->whereClause .= ($this->languageId != '') ? ' AND language_id = \''.$this->languageId.'\'' : '';		
		$this->langIdByUrl = ($this->languageId != '') ? '&language_id='.$this->languageId : '';
		
		if($this->id!=''){
			$sql = 'SELECT
						'.TABLE_MENUS.'.*,
						'.TABLE_LANGUAGES.'.lang_name as language_name
					FROM '.TABLE_MENUS.'
						LEFT OUTER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					WHERE '.TABLE_MENUS.'.id = \''.(int)$this->id.'\'';
			$this->menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		}else{
			$this->menu['menu_name'] = '';
			$this->menu['menu_placement'] = '';
			$this->menu['menu_order'] = '';
			$this->menu['language_id'] = '';
			$this->menu['language_name'] = '';
			$this->menu['access_level'] = '';
		}
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 *	Returns menu name	
	 */
	public function GetName()
	{		
		if(isset($this->menu['menu_name'])) return decode_text($this->menu['menu_name']);
		else return '';
	}

	/**
	 *	Returns parameter value
	 *		#param $param
	 */
	public function GetParameter($param = '')
	{
		if(isset($this->menu[$param])){
			return $this->menu[$param];
		}else{
			return '';
		}
	}
	
	/**
	 *	Returns menu ID	
	 */
	public function GetId()
	{
		return $this->id;
	}
	
	/**
	 *	Returns menu order	
	 */
	public function GetOrder()
	{
		if(isset($this->menu['menu_order'])) return $this->menu['menu_order'];
		else return '';
	}
	
	/**
	 *	Moves menu (change priority order)
	 *		@param $menu_id
	 *		@param $dir - direction
	 *		@param $menu_order  - menu order
	 */
	public function MenuMove($menu_id, $dir = '', $menu_order = '')
	{		
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){ 
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(($dir == '') || ($menu_order == '')){
			$this->error = _WRONG_PARAMETER_PASSED;
			return false;
		}

		$sql = 'SELECT * FROM '.TABLE_MENUS.'
				WHERE
					id <> \''.(int)$menu_id.'\' AND
					menu_order '.(($dir == 'up') ? '<' : '>').' '.(int)$menu_order.' AND
					language_id = \''.$this->languageId.'\'
				ORDER BY menu_order '.(($dir == 'up') ? 'DESC' : 'ASC');
        if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = \''.$menu_order.'\' WHERE id = '.(int)$menu['id'];
			if(database_void_query($sql)){
				$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = \''.$menu['menu_order'].'\' WHERE id = '.(int)$menu_id;				
				if(!database_void_query($sql)){
					$this->error = _TRY_LATER;
					return false;					
				}
			}else{
				$this->error = _TRY_LATER;
				return false;
			}
		}
		return true;		
	}

	/***
	 *	Updates menu 
	 *		@param $param - array of parameters
	 */
	public function MenuUpdate($params = array())
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		if(isset($this->id)){
			// Get input parameters
			if(isset($params['name']) && $params['name'] != ''){
				$this->menu['menu_name'] = $params['name'];
			}else{
				$this->error = _MENU_NAME_EMPTY;
				return false;
			}
			if(isset($params['order'])) 		 $this->menu['menu_order'] = $params['order'];
		    if(isset($params['language_id'])) 	 $this->menu['language_id'] = $params['language_id'];
			if(isset($params['menu_placement'])) $this->menu['menu_placement'] = $params['menu_placement'];
			if(isset($params['access_level'])) 	 $this->menu['access_level'] = $params['access_level'];
			
			$sql = 'SELECT MIN(menu_order) as min_order, MAX(menu_order) as max_order FROM '.TABLE_MENUS;
			if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
				$min_order = $menu['min_order'];
				$max_order = $menu['max_order'];
				
				// insert menu with new priority order in menus list
				$sql = 'SELECT menu_order FROM '.TABLE_MENUS.' WHERE id = '.(int)$this->id;
				if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
					$sql_down = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order - 1 WHERE language_id = \''.$this->menu['language_id'].'\' AND id <> '.(int)$this->id.' AND menu_order <= '.$this->menu['menu_order'].' AND menu_order > '.$menu['menu_order'];
					$sql_up = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order + 1 WHERE language_id = \''.$this->menu['language_id'].'\' AND id <> '.(int)$this->id.' AND menu_order >= '.$this->menu['menu_order'].' AND menu_order < '.$menu['menu_order'];
					
					if($menu['menu_order'] != $this->menu['menu_order']){							
						$sql = 'UPDATE '.TABLE_MENUS.'
						        SET
								    language_id = \''.$this->menu['language_id'].'\',
									menu_name = \''.encode_text($this->menu['menu_name']).'\',
									menu_placement = \''.$this->menu['menu_placement'].'\',
								    menu_order = '.(int)$this->menu['menu_order'].',
									access_level = \''.$this->menu['access_level'].'\'
								WHERE id = '.(int)$this->id.' AND menu_order <> '.(int)$this->menu['menu_order'];
						if($result = database_void_query($sql)){
							if($this->menu['menu_order'] == $min_order){
								$sql = $sql_up;
							}else if($this->menu['menu_order'] == $max_order){
								$sql = $sql_down;
							}else{
								if($menu['menu_order'] < $this->menu['menu_order']) $sql = $sql_down;
								else $sql = $sql_up;
							}
							$result = database_void_query($sql);
						}							
					}else{
						$sql = 'UPDATE '.TABLE_MENUS.'
						        SET
									language_id = \''.$this->menu['language_id'].'\',
								    menu_name = \''.encode_text($this->menu['menu_name']).'\',
									menu_placement = \''.$this->menu['menu_placement'].'\',
									access_level = \''.$this->menu['access_level'].'\'
								WHERE id = '.(int)$this->id;
						$result = database_void_query($sql);
					}
				}
			}

			if($result >= 0){
				return true;
			}else{
				$this->error = _TRY_LATER;
				return false;
			}				
		}else{
			$this->error = _MENU_MISSED;
			return false;
		}
	}

	/**
	 *	Creates new menu 
	 *		@param $param - array of parameters
	 */
	public function MenuCreate($params = array())
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		// Get input parameters
		if(isset($params['name'])) 			$this->menu['menu_name'] = $params['name'];
		if(isset($params['menu_placement'])) $this->menu['menu_placement'] = $params['menu_placement'];
		if(isset($params['order'])) 		$this->menu['menu_order'] = $params['order'];
		if(isset($params['language_id'])) 	$this->menu['language_id'] = $params['language_id'];
		if(isset($params['access_level']))	$this->menu['access_level'] = $params['access_level'];

		// Prevent creating of empty records in our 'menus' table
		if($this->menu['menu_name'] != ''){
			$menu_code = strtoupper(get_random_string(10));

			$total_languages = Languages::GetAllActive();
			for($i = 0; $i < $total_languages[1]; $i++){				

				$m = self::GetAll(' menu_order ASC', TABLE_MENUS, '', $total_languages[0][$i]['abbreviation']);
				$max_order = (int)($m[1]+1);			

				$sql = 'INSERT INTO '.TABLE_MENUS.' (language_id, menu_code, menu_name, menu_placement, menu_order, access_level)
						VALUES(\''.$total_languages[0][$i]['abbreviation'].'\', \''.$menu_code.'\', \''.encode_text($this->menu['menu_name']).'\', \''.$this->menu['menu_placement'].'\', '.$max_order.', \''.$this->menu['access_level'].'\')';
				if(!database_void_query($sql)){
					$this->error = _TRY_LATER;
					return false;
				}
			}
			return true;			
		}else{
			$this->error = _MENU_NAME_EMPTY;
			return false;
		}
	}

	/**
	 *	Deletes menu 
	 *		@param $menu_id - menu ID
	 *		@param $menu_order
	 */
	public function MenuDelete($menu_id = '0', $menu_order = '0')
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'SELECT language_id FROM '.TABLE_MENUS.' WHERE id = '.(int)$menu_id;
		if($menu = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$sql = 'DELETE FROM '.TABLE_MENUS.' WHERE id = '.(int)$menu_id;
			if(database_void_query($sql)){
				$sql = 'UPDATE '.TABLE_MENUS.' SET menu_order = menu_order - 1 WHERE language_id = \''.$menu['language_id'].'\' AND menu_order > '.(int)$menu_order;
				if(database_void_query($sql)){
					return true;    
				}                				   
			}
		}		
		return false;
	}

	//==========================================================================
    // Static Methods
	//==========================================================================		
	/**
	 *	Returns all footer menus array
	 *		@param $where_clause
	 *		@param $lang_id
	 */
	private static function GetAllFooter($where_clause = '', $lang_id = '')
	{
		global $objLogin;

		if($lang_id != '') $where_clause .= 'AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE '.TABLE_MENUS.'.menu_placement = \'bottom\' AND 
					is_published = 1 AND 
					('.TABLE_PAGES.'.finish_publishing = \'0000-00-00\' OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND ('.TABLE_MENUS.'.access_level = \'public\' AND '.TABLE_PAGES.'.access_level = \'public\')' : '').'					
					'.$where_clause.'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC, '.TABLE_PAGES.'.priority_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Returns all left menus array
	 *		@param $position
	 */
	public static function GetMenus($position = 'left')
	{
		global $objLogin;
		
		// Get all left menus
		$sql = 'SELECT
					'.TABLE_MENUS.'.*
				FROM '.TABLE_MENUS.'
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
				WHERE
					'.TABLE_MENUS.'.language_id = \''.Application::Get('lang').'\' AND
					'.TABLE_MENUS.'.menu_placement = \''.$position.'\'
					'.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_MENUS.'.access_level = \'public\'' : '').'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 *	Returns all left menu links array
	 *		@param $menu_id
	 *		@param $lang_id
	 *		@param $position
	 */
	private static function GetMenuLinks($menu_id, $lang_id = '', $position = 'left')
	{
		global $objLogin;

		// Get all left menus
		$sql = 'SELECT
					'.TABLE_PAGES.'.*
				FROM '.TABLE_PAGES.'
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_PAGES.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE
					'.TABLE_PAGES.'.language_id = \''.$lang_id.'\' AND
					'.TABLE_MENUS.'.menu_placement = \''.$position.'\' AND
					'.TABLE_PAGES.'.menu_id = \''.$menu_id.'\' AND
					'.TABLE_PAGES.'.is_home = 0 AND
					'.TABLE_PAGES.'.is_published = 1 AND
					('.TABLE_PAGES.'.finish_publishing = \'0000-00-00\' OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_PAGES.'.access_level = \'public\'' : '').'
				ORDER BY '.TABLE_PAGES.'.priority_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}

	/***
	 *	Returns all menus array
	 *		@param $order - order clause
	 *		@param $join_table - join tables
	 *		@param $menu_placement
	 *		@param $lang_id
	 */
	public static function GetAll($order = ' menu_order ASC', $join_table = '', $menu_placement = '', $lang_id = '')
	{
		$where_clause = '';
		if($menu_placement != ''){
			$where_clause .= 'AND '.TABLE_MENUS.'.menu_placement = \''.$menu_placement.'\' ';
		}
		if($lang_id != '') $where_clause .= 'AND '.$join_table.'.language_id = \''.$lang_id.'\' ';
		
		// Build ORDER BY CLAUSE
		if($order == '')$order_clause = '';
		else $order_clause = 'ORDER BY '.$order;		

		// Build JOIN clause
		if($join_table == '') {
			$join_clause = '';
			$join_select_fields = '';
		}else if($join_table != TABLE_MENUS){
			$join_clause = 'LEFT OUTER JOIN '.$join_table.' ON '.$join_table.'.menu_id='.TABLE_MENUS.'.id ';
			$join_select_fields = ', '.$join_table.'.* ';
		} else {
			$join_clause = '';
			$join_select_fields = '';
        }		
		
		$sql = 'SELECT
					'.TABLE_MENUS.'.*,
					'.TABLE_LANGUAGES.'.lang_name as language_name
					'.$join_select_fields.'
				FROM '.TABLE_MENUS.' 
				    INNER JOIN '.TABLE_LANGUAGES.' ON '.TABLE_MENUS.'.language_id = '.TABLE_LANGUAGES.'.abbreviation
					'.$join_clause.'
				WHERE 1=1
				'.$where_clause.'
				'.$order_clause;			
		
		return database_query($sql, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
	}		
	
	/**
	 *	Draws all menus in dropdowm box
	 *		@param $menu_id
	 *		@param $language_id
	 *		@param $draw
	 */
	public static function DrawMenuSelectBox($menu_id = '', $language_id = '', $draw = true)
	{
		$output = '<select name="menu_id" id="menu_id" style="width:170px">';
		$output .= '<option value="">-- '._SELECT.' --</option>';
		$all_menus = self::GetAll(' menu_order ASC', TABLE_MENUS, '', $language_id);		                 
		for($i = 0; $i < $all_menus[1]; $i++){
			$output .= '<option value="'.$all_menus[0][$i]['id'].'"';
			$output .= ($all_menus[0][$i]['id'] == $menu_id) ? ' selected="selected"' : '';
			$output .= '>'.$all_menus[0][$i]['menu_name'].'</option>';
		}
		$output .= '</select>';		

		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draws pages type dropdowm box
	 *		@param $content_type
	 *		@param $draw
	 */
	public static function DrawContentTypeBox($content_type = '', $draw = true)
	{
		$output = '<select name="content_type" onchange="ContentType_OnChange(this.value);" >';
		$output .= '<option value="article"'.(($content_type == 'article') ? ' selected="selected"' : '').'>'._ARTICLE.'</option>';
		$output .= '<option value="link"'.(($content_type == 'link') ? ' selected="selected"' : '').'>'._LINK.'</option>';
		$output .= '</select>';		

		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draw menus placement in dropdowm box
	 *		@param $menu_placement
	 *		@param $draw
	 */
	public static function DrawMenuPlacementBox($menu_placement = '', $draw = true)
	{
		global $objSettings;

		$output = '';
		$template = $objSettings->GetParameter('template');
		$count = 0;
		
		$output .= '<select name="menu_placement">';
		if(@file_exists('templates/'.$template.'/info.xml')) {
			// load XML file
			$xml = simplexml_load_file('templates/'.$template.'/info.xml');		 
			if(isset($xml->menus->menu)){
				foreach($xml->menus->menu as $menu){
					$output .= '<option value="'.$menu.'"'.((strtolower($menu_placement) == strtolower($menu)) ? ' selected="selected"' : '').'>'.ucfirst($menu).'</option>';
					$count++;
				}				
			}
		}		
		if(!$count){
			$output .= '<option value="left"'.(($menu_placement == 'left') ? ' selected="selected"' : '').'>'._LEFT.'</option>';
			$output .= '<option value="top"'.(($menu_placement == 'top') ? ' selected="selected"' : '').'>'._TOP.'</option>';
			$output .= '<option value="right"'.(($menu_placement == 'right') ? ' selected="selected"' : '').'>'._RIGHT.'</option>';
			$output .= '<option value="bottom"'.(($menu_placement == 'bottom') ? ' selected="selected"' : '').'>'._BOTTOM.'</option>';
		}
        $output .= '<option value="hidden" '.(($menu_placement == 'hidden') ? ' selected="selected"' : '').'>- '._HIDDEN.' -</option>';
		$output .= '</select>';

		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draws top menu
	 *		@param $draw
	 */
	public static function DrawTopMenu($draw = true)
	{
		$output = '';
		$home_css = $customer_css = '';
		
		if(Application::Get('customer') == 'login' || Application::Get('customer') == 'my_account'){
			$customer_css = ' active';
		}else if(Application::Get('page') == 'home'){
			$home_css = ' active';
		}

		$output .= '<li>'.prepare_permanent_link('index.php', _HOME, '', $home_css).'</li>';			

		if(Modules::IsModuleInstalled('customers')){			
			if(ModulesSettings::Get('customers', 'allow_login') == 'yes'){
				$output .= '<li>'.prepare_permanent_link('index.php?customer=my_account', _MY_ACCOUNT, '', $customer_css).'</li>';
			}
		}			
	
		$menus = self::GetTopMenus(Application::Get('lang'));
		for($i = 0; $i < $menus[1]; $i++) {
			$menu_pages = self::GetMenuPages($menus[0][$i]['id'], Application::Get('lang'));
			
			if($menu_pages[1] > 0){
				$output .= '<li><a href="javascript:void(0)">'.$menus[0][$i]['menu_name'].'</a>';
				$output .= '<ul class="dropdown_inner" style="width:200px">';
				// Draw current menu link
				for($j = 0; $j < $menu_pages[1]; $j++){
                    $css_class = (Application::Get('page_id') == $menu_pages[0][$j]['id']) ? 'active' : '';
					if($menu_pages[0][$j]['content_type'] == 'link'){
						$output .= '<li>'.prepare_permanent_link($menu_pages[0][$j]['link_url'], $menu_pages[0][$j]['menu_link'], $menu_pages[0][$j]['link_target'], '').'</li>';					
					}else{
						$output .= '<li>'.prepare_link('pages', 'pid', $menu_pages[0][$j]['id'], $menu_pages[0][$j]['page_key'], $menu_pages[0][$j]['menu_link'], $css_class).'</li>';						
					}
				}
				$output .= '</ul>';
				$output .= '</li>';
			}				
		}

		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draws all menus for footer
	 *		@param $draw
	 */
	public static function DrawFooterMenu($draw = true)
	{
		// If not logged in - draw menu
		global $objSettings;
		$lang = Application::Get('lang');
		$output = '';
		
		$output .= prepare_permanent_link('index.php', _HOME);
	
		if(Modules::IsModuleInstalled('customers')){						
			if(ModulesSettings::Get('customers', 'allow_login') == 'yes'){
				$output .= ': '.prepare_permanent_link('index.php?customer=my_account', _MY_ACCOUNT);
			}
		}			
		$menus = self::GetAllFooter('', $lang);
		if($menus[1] > 0) $output .= '<br />';
		for($menu_ind = 0; $menu_ind < $menus[1]; $menu_ind++){
			// Draw current menu link
			if($menu_ind > 0) $output .= ': ';
			$output .= prepare_link('pages', 'pid', $menus[0][$menu_ind]['id'], $menus[0][$menu_ind]['page_key'], $menus[0][$menu_ind]['menu_link'], '');
		}
		
		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draws menus 
	 *		@param $menu_position
	 *		@param $draw
	 */
	public static function DrawMenu($menu_position = 'left', $draw = true)
	{
		global $objSettings, $objLogin;
		$output = '';
		
		if($menu_position == 'left') $objLogin->DrawLoginLinks();
		
		// Get all menus which have items (links to pages)
		$menus = self::GetMenus($menu_position);
		$menus_count = $menus[1];
		
		$objNews = News::Instance();
		$show_news_block = ModulesSettings::Get('news', 'show_news_block'); 
		$show_subscribe_block = ModulesSettings::Get('news', 'show_newsletter_subscribe_block');
		if(Modules::IsModuleInstalled('news') && ($show_news_block == 'right side' || $show_subscribe_block == 'right side')) $menus_count++;
		
		$show_inquiries_block = ModulesSettings::Get('inquiries', 'show_inquiries_block');
		if(Modules::IsModuleInstalled('inquiries') && $show_inquiries_block == 'right side') $menus_count++;

		if(ModulesSettings::Get('listings', 'show_categories_side_block') == $menu_position.' side') { $output .= Categories::DrawSideBlock(false); $menus_count++; }
		if(ModulesSettings::Get('listings', 'show_featured_block') == $menu_position.' side') { $output .= Listings::DrawFeaturedBlock(false); $menus_count++; }
		if(ModulesSettings::Get('listings', 'show_recent_block') == $menu_position.' side') { $output .= Listings::DrawRecentBlock(false); $menus_count++; }
		if(ModulesSettings::Get('listings', 'show_directory_statistics') == $menu_position.' side') $menus_count++;

		if($menus_count > 0) $output .= '<div id="column-'.$menu_position.'-wrapper">';

		// Display all menu titles (names) according to their order
		for($menu_ind = 0; $menu_ind < $menus[1]; $menu_ind++) {				
			// Start draw new menu
			$output .= draw_block_top($menus[0][$menu_ind]['menu_name'], '', 'maximazed', false);

			$menu_links = self::GetMenuLinks($menus[0][$menu_ind]['id'], Application::Get('lang'), $menu_position);			
			if($menu_links[1] > 0) $output .= '<ul>';
			for($menu_link_ind = 0; $menu_link_ind < $menu_links[1]; $menu_link_ind++) {
				$class = (Application::Get('page_id') == $menu_links[0][$menu_link_ind]['id']) ? ' active' : '';
				if($menu_links[0][$menu_link_ind]['content_type'] == 'link'){
					$output .= '<li>'.prepare_permanent_link($menu_links[0][$menu_link_ind]['link_url'], $menu_links[0][$menu_link_ind]['menu_link'], $menu_links[0][$menu_link_ind]['link_target'], 'main_menu_link'.$class).'</li>';
				}else{
					// draw current menu link
					$output .= '<li>'.prepare_link('pages', 'pid', $menu_links[0][$menu_link_ind]['id'], $menu_links[0][$menu_link_ind]['page_key'], $menu_links[0][$menu_link_ind]['menu_link'], 'main_menu_link'.$class).'</li>';
				}
			}
			if($menu_links[1] > 0) $output .= '</ul>';
			$output .= draw_block_bottom(false);
        }
		
		if($menu_position == 'left'){
			if(!$objLogin->IsLoggedIn() || Application::Get('preview') == 'yes'){
				if(Modules::IsModuleInstalled('customers') && ModulesSettings::Get('customers', 'allow_login') == 'yes'){
					if(Application::Get('customer') != 'login'){
						$output .= Customers::DrawLoginFormBlock(false);		
					}
				}				
			}
			if(Modules::IsModuleInstalled('news')){
				if($show_news_block == 'left side') $output .= $objNews->DrawNewsBlock(false);
				if($show_subscribe_block == 'left side') $output .= $objNews->DrawSubscribeBlock(false);	
			}
			if(Modules::IsModuleInstalled('inquiries')){
				if($show_inquiries_block == 'left side') $output .= Inquiries::DrawLastInquiriesBlock(false);
			}
		}

		if($menu_position == 'right'){
			if(Modules::IsModuleInstalled('inquiries')){
				if($show_inquiries_block == 'right side') $output .= Inquiries::DrawLastInquiriesBlock(false);
			}
			if(Modules::IsModuleInstalled('news')){
				if($show_news_block == 'right side') $output .= $objNews->DrawNewsBlock(false);
				if($show_subscribe_block == 'right side') $output .= $objNews->DrawSubscribeBlock(false);	
			}
		}

		if(ModulesSettings::Get('listings', 'show_directory_statistics') == $menu_position.' side') $output .= Listings::DrawDirectoryStatistics(false);				

		if($menu_position == 'right'){
			if(Modules::IsModuleInstalled('adsense')){
				$output .= '<div class="adsense-vertical">'.Adsense::GetVerticalBanerCode().'</div>';
			}
		}

		if($menus_count > 0) $output .= '</div>';
		$output .= '<br />';
		
		if($draw) echo $output;
		else return $output;		
	}

	/**
	 *	Draw menu accessible dropdown menu
	 *		@param $access_level
	 *		@param $draw 
	 */
	public static function DrawMenuAccessSelectBox($access_level = 'public', $draw = true)
	{
		$output = '<select name="access_level" id="access_level">';
			$output .= '<option value="public" '.(($access_level == 'public') ? ' selected="selected"' : '').'>'._PUBLIC.'</option>';
			$output .= '<option value="registered" '.(($access_level == 'registered') ? ' selected="selected"' : '').'>'._REGISTERED.'</option>';
		$output .= '</select>';
		
		if($draw) echo $output;
		else return $output;
	}	

	/**
	 *	Gets top menus array
	 *		@param $lang_id
	 */
	public static function GetTopMenus($lang_id = '')
	{
		global $objLogin;
		
		$where_clause = ($lang_id != '') ? ' AND '.TABLE_MENUS.'.language_id = \''.$lang_id.'\' ' : '';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_MENUS.'.* 
				FROM '.TABLE_MENUS.'
				WHERE '.TABLE_MENUS.'.menu_placement = \'top\'
					'.((!$objLogin->IsLoggedIn()) ? ' AND '.TABLE_MENUS.'.access_level = \'public\'' : '').'
					'.$where_clause.'
				ORDER BY '.TABLE_MENUS.'.menu_order ASC';
		return database_query($sql, DATA_AND_ROWS);
	}	

	/**
	 *	Get menu pages
	 *		@param $menu_id
	 *		@param $lang_id
	 */
	public static function GetMenuPages($menu_id = '0', $lang_id = '')
	{
		global $objLogin;
		
		$where_clause = ($lang_id != '') ? ' AND '.TABLE_PAGES.'.language_id = \''.$lang_id.'\' ' : '';
		
		// Get all top menus
		$sql = 'SELECT '.TABLE_PAGES.'.* 
				FROM '.TABLE_PAGES.'
					INNER JOIN '.TABLE_MENUS.' ON '.TABLE_PAGES.'.menu_id = '.TABLE_MENUS.'.id
				WHERE
					'.TABLE_MENUS.'.id = \''.$menu_id.'\' AND 
					'.TABLE_PAGES.'.is_published = 1 AND
					('.TABLE_PAGES.'.finish_publishing = \'0000-00-00\' OR '.TABLE_PAGES.'.finish_publishing >= \''.@date('Y-m-d').'\')
					'.((!$objLogin->IsLoggedIn()) ? ' AND ('.TABLE_MENUS.'.access_level = \'public\' AND '.TABLE_PAGES.'.access_level = \'public\')' : '').'
					'.$where_clause.'
					ORDER BY '.TABLE_PAGES.'.priority_order ASC';				
		return database_query($sql, DATA_AND_ROWS);
	}

}
?>