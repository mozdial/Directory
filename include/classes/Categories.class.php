<?php

/**
 *	Categories (for Business Directory ONLY)
 *  -------------- 
 *	Written by  : ApPHP
 *  Updated	    : 22.01.2012
 *	Written by  : ApPHP
 *
 *	PUBLIC:				  	STATIC:				 		PRIVATE:
 * 	------------------	  	---------------     		---------------
 *	__construct             DrawSideBlock				GetCategoriesSelectBox
 *	__destruct				DrawHomePageBlock       	GetLevel
 *	BeforeInsertRecord      UpdateListingsCount     	
 *	AfterInsertRecord       RecalculateListingsCount    
 *	BeforeDeleteRecord      GetCategoryInfo
 *	AfterDeleteRecord       GetAllActive
 *	DrawCategory            Instance
 *	DrawSubCategories
 *	GetInfoByID
 *	GetAllExistingCategories
 *	GetLevelsInfo
 *
 **/


class Categories extends MicroGrid {
	
	protected $debug = false;

	//----------------------------------	
	private static $instance;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		$this->params = array();
		
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = (int)$_POST['priority_order'];
		if(isset($_POST['parent_id']))  	$this->params['parent_id'] = (int)$_POST['parent_id'];
		$name 			= (isset($_POST['descr_name'])) ? prepare_input($_POST['descr_name']) : '';
		$description 	= (isset($_POST['descr_description'])) ? prepare_input($_POST['descr_description']) : '';
		$cid 			= (isset($_REQUEST['cid'])) ? (int)$_REQUEST['cid'] : '0';

		if(isset($_POST['icon'])){
			$this->params['icon'] = prepare_input($_POST['icon']);
		}else if(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
			// nothing 			
		}else if (self::GetParameter('action') == 'create'){
			$this->params['icon'] = '';
		}
		
		// for checkboxes
		/// if(isset($_POST['parameter4']))   $this->params['parameter4'] = $_POST['parameter4']; else $this->params['parameter4'] = '0';
		
		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
		$rid = MicroGrid::GetParameter('rid');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CATEGORIES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_categories&cid='.(int)$cid;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->isHtmlEncoding = true;
		
		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; 
		$this->ORDER_CLAUSE = 'ORDER BY '.TABLE_CATEGORIES.'.parent_id ASC, '.TABLE_CATEGORIES.'.priority_order ASC';
		$this->categoryCode = isset($_POST['category_code']) ? prepare_input($_POST['category_code']) : '';
		
		$this->isAlterColorsAllowed = true;
        
		$this->isPagingAllowed = true;
		$this->pageSize = 20;
        
		$this->isSortingAllowed = true;
        
		$this->isExportingAllowed = true;
		$this->arrExportingTypes = array('csv'=>true);

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			//'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			//'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		);

		$this->isAggregateAllowed = true;
		// define aggregate fields for View Mode
		$this->arrAggregateFields = array(
			'listings_count' => array('function'=>'SUM')
		);
		
		// prepare languages array		
		//$total_languages = Languages::GetAllActive();
		//$arr_languages      = array();
		//foreach($total_languages[0] as $key => $val){
		//	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		//}
		
		$level = $this->GetLevel($cid);
		
		// retrieve default priority order for new record
		$default_priority_order = '';
		if(self::GetParameter('action') == 'add'){
			$default_priority_order = $this->GetMaxOrder('priority_order', 999);
		}

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.icon,
									'.$this->tableName.'.icon_thumb,
									'.$this->tableName.'.parent_id,
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.listings_count,
									'.TABLE_CATEGORIES_DESCRIPTION.'.language_id,
									'.TABLE_CATEGORIES_DESCRIPTION.'.name,
									'.TABLE_CATEGORIES_DESCRIPTION.'.description,
									CONCAT("<a href=index.php?admin=mod_categories_description&cid=", '.$this->tableName.'.parent_id, "&cdid=", '.TABLE_CATEGORIES.'.'.$this->primaryKey.', ">[ ", "'._DESCRIPTION.'", " ]</a>") as link_cat_description,
									CONCAT("<a href=index.php?admin=mod_categories&cid=", '.$this->tableName.'.'.$this->primaryKey.',
										">[ ", "'._SUB_CATEGORIES.' ]</a> (",
										(SELECT COUNT(*) FROM '.$this->tableName.' c1 WHERE c1.parent_id = '.$this->tableName.'.'.$this->primaryKey.'),
										")") as link_sub_categories
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CATEGORIES_DESCRIPTION.'.category_id
								WHERE
									'.$this->tableName.'.parent_id = '.(int)$cid.' AND
									'.TABLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'';
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  		 => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>''),
			'description'    => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'30'),
			//'parent_id'      => array('title'=>_PARENT_CATEGORY, 'type'=>'enum', 'align'=>'center', 'width'=>'120px', 'source'=>$arr_categories),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'maxlength'=>'', 'movable'=>true),
			'listings_count' => array('title'=>_LISTINGS, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'maxlength'=>''),
			'icon_thumb'  	 => array('title'=>_ICON_IMAGE, 'type'=>'image', 'align'=>'center', 'width'=>'80px', 'image_width'=>'40px', 'image_height'=>'30px', 'target'=>'images/categories/', 'no_image'=>'no_image.png'),
			'link_sub_categories'  => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'140px', 'maxlength'=>'', 'visible'=>(($level >= 3) ? false : true)),
			'link_cat_description' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'maxlength'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		
			//'parent_id'         => array('title'=>_PARENT_CATEGORY, 'type'=>'enum',     'required'=>false, 'readonly'=>false, 'width'=>'210px', 'source'=>$arr_categories, 'unique'=>false, 'javascript_event'=>''),
			'descr_name' 		=> array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'default'=>$name, 'validation_type'=>'text', 'maxlength'=>'50'),
			'descr_description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'370px', 'height'=>'90px', 'required'=>false, 'readonly'=>false, 'default'=>$description, 'validation_type'=>'text', 'validation_maxlength'=>'255'),
			'icon'              => array('title'=>_ICON_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'target'=>'images/categories/', 'no_image'=>'', 'random_name'=>'true', 'unique'=>false, 'image_width'=>'120px', 'image_height'=>'90px', 'thumbnail_create'=>true, 'thumbnail_field'=>'icon_thumb', 'thumbnail_width'=>'115px', 'thumbnail_height'=>'', 'file_maxsize'=>'200k'),
			'priority_order'    => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'default'=>$default_priority_order, 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
			'parent_id'  	    => array('title'=>'', 'type'=>'hidden', 'required'=>false, 'default'=>$cid),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.icon,
									'.$this->tableName.'.icon_thumb,
									'.$this->tableName.'.parent_id,
									'.$this->tableName.'.priority_order,
									'.$this->tableName.'.listings_count,
									'.TABLE_CATEGORIES_DESCRIPTION.'.name as category_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CATEGORIES_DESCRIPTION.'.category_id
								WHERE
									'.TABLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\' AND
								    '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			///'parent_id'       => array('title'=>_PARENT_CATEGORY, 'type'=>'enum',     'required'=>false, 'readonly'=>false, 'width'=>'210px', 'source'=>$arr_categories, 'unique'=>false, 'javascript_event'=>''),
			'category_name'   => array('title'=>_NAME, 'type'=>'label'),
			'icon'            => array('title'=>_ICON_IMAGE, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'target'=>'images/categories/', 'no_image'=>'', 'random_name'=>'true', 'unique'=>false, 'image_width'=>'120px', 'image_height'=>'90px', 'thumbnail_create'=>true, 'thumbnail_field'=>'icon_thumb', 'thumbnail_width'=>'115px', 'thumbnail_height'=>'', 'file_maxsize'=>'200k'),
			'listings_count'  => array('title'=>_LISTINGS, 'type'=>'label'),
			'priority_order'  => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'maxlength'=>'3', 'default'=>'0', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			///'parent_id'       => array('title'=>_PARENT_CATEGORY, 'type'=>'enum', 'source'=>$arr_categories),
			'category_name'   => array('title'=>_NAME, 'type'=>'label'),
			'icon'  		  => array('title'=>_ICON_IMAGE, 'type'=>'image', 'target'=>'images/categories/', 'no_image'=>'no_image.png'),
			'listings_count'  => array('title'=>_LISTINGS, 'type'=>'label'),
			'priority_order'  => array('title'=>_ORDER, 'type'=>'label'),
		);

	}
	
	/**
	 *	Return instance of the class
	 */
	public static function Instance()
	{
		if(self::$instance == null) self::$instance = new Categories();
		return self::$instance;
	}       

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Before-insertion function
	 */
	public function BeforeInsertRecord()
	{
		$name = (isset($_POST['descr_name'])) ? prepare_input($_POST['descr_name']) : '';
		$description = (isset($_POST['descr_description'])) ? prepare_input($_POST['descr_description']) : '';

		if($name == ''){
			$this->error = str_replace('_FIELD_', _NAME, _FIELD_CANNOT_BE_EMPTY);
			$this->errorField = 'descr_name';
			return false;
		}else if(strlen($description) > 255){
			$msg_text = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_ALERT);
			$this->error = str_replace('_LENGTH_', '255', $msg_text);
			$this->errorField = 'descr_description';
			return false;
		}
		
		return true;
	}

	/**
	 * After-insertion function
	 */
	public function AfterInsertRecord()
	{
		$name = (isset($_POST['descr_name'])) ? prepare_input($_POST['descr_name']) : '';
		$description = (isset($_POST['descr_description'])) ? prepare_input($_POST['descr_description']) : '';
	
		// languages array		
		$total_languages = Languages::GetAllActive();
		foreach($total_languages[0] as $key => $val){			
			$sql = 'INSERT INTO '.TABLE_CATEGORIES_DESCRIPTION.'(
						id, category_id, language_id, name, description)
					VALUES(
						NULL, '.$this->lastInsertId.', \''.$val['abbreviation'].'\', \''.$name.'\', \''.$description.'\'
					)';
			if(!database_void_query($sql)){
				// error	
			}		
		}
		
	}
	
	/**
	 * Before-deleting function
	 */
	public function BeforeDeleteRecord()
	{
		$cid = MicroGrid::GetParameter('rid');

		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CATEGORIES.' WHERE parent_id = '.(int)$cid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if(isset($result[0]['cnt']) && $result[0]['cnt'] > 0){
			$this->error = _CATEGORY_DELETE_SUBCATEGORIES;			
			return false;			
		}else{
			$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LISTINGS.' WHERE category_id = '.(int)$cid;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if(isset($result[0]['cnt']) && $result[0]['cnt'] > 0){
				$this->error = _CATEGORY_DELETE_LISTINGS;			
				return false;			
			}			
		}

		return true;
	}

	/**
	 * After-deleting function
	 */
	public function AfterDeleteRecord()
	{
        // delete from categories description table
		$cid = MicroGrid::GetParameter('rid');
		$sql = 'DELETE FROM '.TABLE_CATEGORIES_DESCRIPTION.' WHERE category_id = '.(int)$cid;		
		if(!database_void_query($sql)){ /* echo 'error!'; */ }		
		$sql = 'DELETE FROM '.TABLE_LISTINGS_CATEGORIES.' WHERE category_id = '.(int)$cid;		
		if(!database_void_query($sql)){ /* echo 'error!'; */ }		
	}

	/**
	 * Draws side block with categories links
	 * 		@param $draw
	 **/
	public static function DrawSideBlock($draw = true)
	{
		global $objLogin;

		$listings_count_field = (!$objLogin->IsLoggedIn()) ? 'listings_count_public' : 'listings_count';
		$lang = Application::Get('lang');
		
		ob_start();
		$sql = 'SELECT '.TABLE_CATEGORIES.'.id,
					'.TABLE_CATEGORIES.'.icon,
					'.TABLE_CATEGORIES.'.listings_count,
					'.TABLE_CATEGORIES.'.listings_count_public,
					'.TABLE_CATEGORIES.'.priority_order,
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id,
					'.TABLE_CATEGORIES_DESCRIPTION.'.name,									
					'.TABLE_CATEGORIES_DESCRIPTION.'.description
				FROM '.TABLE_CATEGORIES.'
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' ON '.TABLE_CATEGORIES.'.id = '.TABLE_CATEGORIES_DESCRIPTION.'.category_id
				WHERE
					'.TABLE_CATEGORIES.'.parent_id = _CID_ AND
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id = \''.$lang.'\'
				ORDER BY '.TABLE_CATEGORIES.'.priority_order ASC';
		$sql_1 = str_replace('_CID_', '0', $sql);
		$result = database_query($sql_1, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);

		draw_block_top(_CATEGORIES);
		## +---------------------------------------------------------------------------+
		## | 1. Creating & Calling:                                                    |
		## +---------------------------------------------------------------------------+

		## *** define a relative (virtual) path to treemenu.class.php file
		define ('TREEMENU_DIR', 'modules/treemenu/');                  /* Ex.: 'treemenu/' */
		## *** include TreeMenu class
		require_once(TREEMENU_DIR.'treemenu.class.php');
		## *** create TreeMenu object
		$treeMenu = new TreeMenu();
		$treeMenu->SetDirection(Application::Get('lang_dir'));
		
		## +---------------------------------------------------------------------------+
		## | 2. General Settings:                                                      |
		## +---------------------------------------------------------------------------+
		## *** set unique numeric (integer-valued) identifier for TreeMenu
		## *** (if you want to use several independently configured TreeMenu objects on single page)
		$treeMenu->SetId(1);
		## *** set style for TreeMenu
		$treeMenu->SetStyle('vista');
		## *** set TreeMenu caption
		//$treeMenu->SetCaption('ApPHP TreeMenu v'.$treeMenu->Version());
		## *** show debug info - false|true
		$treeMenu->Debug(false);
		## *** specifies whether to show node(folder) icons
		$treeMenu->UseDefaultFolderIcons(false);		 
		## *** set postback method: 'get', 'post' or 'ajax'
		$treeMenu->SetPostBackMethod('post');
		## *** set variables that used to get access to the page (like: my_page.php?act=34&id=56 etc.)
		/// $treeMenu->SetHttpVars(array('id'));
		## *** show number of subnodes to the left of every node - false|true
		$treeMenu->ShowNumSubNodes(false);

		## +---------------------------------------------------------------------------+
		## | 3. Adding nodes:                                                          |
		## +---------------------------------------------------------------------------+
		## *** add nodes
		## arguments:
		## arg #1 - node's caption
		## arg #2 - file associated with this node (optional)
		## arg #3 - icon associated with this node (optional)
		## Example: $treeMenu->AddNode('Title', 'text.txt', 'icon.gif');
		$node = array();
		for($i=0; $i < $result[1]; $i++){
			$node = $treeMenu->AddNode($result[0][$i]['name'].' ('.$result[0][$i][$listings_count_field].')', prepare_link('category', 'cid', $result[0][$i]['id'], '', $result[0][$i]['name'], '', '', true));
			$node->OpenNewWindow(true);
			
			$sql_2 = str_replace('_CID_', $result[0][$i]['id'], $sql);
			$result_2 = database_query($sql_2, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
			for($j=0; $j < $result_2[1]; $j++){

				$sub_node = $node->AddNode($result_2[0][$j]['name'].' ('.$result_2[0][$j][$listings_count_field].')', prepare_link('category', 'cid', $result_2[0][$j]['id'], '', $result_2[0][$j]['name'], '', '', true));
				$sub_node->OpenNewWindow(true);
				
				$sql_3 = str_replace('_CID_', $result_2[0][$j]['id'], $sql);
				$result_3 = database_query($sql_3, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				for($k=0; $k < $result_3[1]; $k++){
					$sub_sub_node = $sub_node->AddNode($result_3[0][$k]['name'].' ('.$result_3[0][$k][$listings_count_field].')', prepare_link('category', 'cid', $result_3[0][$k]['id'], '', $result_3[0][$k]['name'], '', '', true));
					$sub_sub_node->OpenNewWindow(true);					
				}				
			}
		}

		## +---------------------------------------------------------------------------+
		## | 5. Draw TreeMenu:                                                         |
		## +---------------------------------------------------------------------------+
		$treeMenu->ShowTree();

		echo '<ul><li>'.prepare_link('categories', '', '', 'all', _SEE_ALL.' &raquo;', 'main_menu_link main_menu_last', _SEE_ALL).'</li></ul>';
		draw_block_bottom();

		// save the contents of output buffer to the string
		$output = ob_get_contents();
		ob_end_clean();

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws home page block with categories links
	 * 		@param $draw
	 */
	public static function DrawHomePageBlock($draw = true)
	{
		global $objSettings;
		global $objLogin;

		$listings_count_field = (!$objLogin->IsLoggedIn()) ? 'listings_count_public' : 'listings_count';
		$lang = Application::Get('lang');
		$categories_images = false;
		$categories_columns = '3';
		$sub_categories_count = ModulesSettings::Get('listings', 'sub_categories_count');
		
		if(Modules::IsModuleInstalled('listings')){				
			if(ModulesSettings::Get('listings', 'show_categories_images') == 'yes') $categories_images = true;
			$categories_columns = ModulesSettings::Get('listings', 'columns_number_on_page');
		}
		
		$output = '';
		$sql = 'SELECT c.id,
					c.icon,
					c.icon_thumb,
					c.listings_count,
					c.listings_count_public,
					c.priority_order,
					cd.language_id,
					cd.name,									
					cd.description
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE
					c.parent_id = _PARENT_ID_ AND 
					cd.language_id = \''.$lang.'\'
				ORDER BY c.priority_order ASC';

		$result = database_query(str_replace('_PARENT_ID_', '0', $sql), DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
		if($result[1] > 0){
//> START da Organizacao do Cabecalho e titlo.
			//$output = $output.'<div class="row">';
						            //$output = $output.'<div class="col-lg-12">';
						              //  $output = $output.'<h1 class="page-header">';
						                  //  $output = $output.'<small>'.draw_sub_title_bar(_CATEGORIES, false).'</small>';
						               // $output = $output.'</h1> </div>  </div>';
						           
						       
//> END da Organizacao do Cabecalho e titlo.

//> START da composicao do corpo.
		    $output = $output.'<div class="row">';
            for($i=0; $i < $result[1]; $i++){
            $output = $output.'<div class="col-md-3 portfolio-item">';
	            if($categories_images){
                  $icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : 'no_image.png';
                  $output .= '<div class="catg_content"><a href="#">';
                  $output = $output.'<img src="images/categories/'.$icon_file_thumb.'" src="http://placehold.it/750x450" class="img-responsive" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';
	              $output .= '</div></a>';
	            }
	        $output .='<span class="label">';
	        $output = $output.prepare_link('category', 'cid', $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], 'category_link', $result[0][$i]['description']).' <span class="categories_span">('.$result[0][$i][$listings_count_field].')</span>';
	        $output .='</span>';
            $output = $output.'</div>';
            }

		    $output = $output.'</div>';
//> END da composicao do corpo.
/*
    //> Precorre o ARRAY result: indice 0 => contem CATEGORIAS, indice 1 => contem o tamanho do ARRAY
			for($i=0; $i < $result[1]; $i++){
    //> Se o ARRAY estiver vasio imprime uma linha vazia e abre outra linha. 
				if($i != 0 && $i % $categories_columns == 0) $output = $output.'</tr><tr>';
	//> Verifiva se o modulo image esta activo. 		
				if($categories_images){
					$output = $output.'<td valign="top" width="40px">';
	//> Carrega a imagem ('icon_thumb'), e Imprime na <td></td>
					$icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : 'no_image.png';
					$output = $output.'<img src="images/categories/'.$icon_file_thumb.'" width="64px" height="64px" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';
					$output = $output.'</td>';
				}
				
				$output = $output.'<td valign="top" width="'.intval(100/$categories_columns).'%">';
	//> Imprime link da Categoria			
				$output = $output.prepare_link('category', 'cid', $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], 'category_link', $result[0][$i]['description']).' <span class="categories_span">('.$result[0][$i][$listings_count_field].')</span>';
	//> Imprime link em Subcategorias
				$result_1 = database_query(str_replace('_PARENT_ID_', $result[0][$i]['id'], $sql), DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				$output .= '<br><div style="padding-top:5px;">';				
				for($j=0; ($j < $result_1[1] && $j <= $sub_categories_count); $j++){
					if($j > 0) $output = $output.', ';
					if($j < $sub_categories_count){
	//> Separa as subcategorias com virgula. 
						$output = $output.prepare_link('category', 'cid', $result_1[0][$j]['id'], $result_1[0][$j]['name'], $result_1[0][$j]['name'], 'sub_category_link', $result_1[0][$j]['description']).' <span class="sub_categories_span">('.$result_1[0][$j][$listings_count_field].')</span>';					
					}else{
						$output = $output.prepare_link('category', 'cid', $result[0][$i]['id'], _MORE, _MORE.'...', 'sub_category_link', _READ_MORE);
					}					
				}
				$output = $output.'<div>';								
				$output = $output.'</td>';
			}
			$output = $output.'</tr>';
			$output = $output.'</table>';
			*/
		}
		if($draw) echo $output;
		else return $output;
		
		//if($draw){
			//var_dump($result);
			//echo $result[0];
		//} 
	}

	/**
	 * Draws category
	 * 		@param $category_id
	 */
	public function DrawCategory($category_id = '0')
	{
		$lang = Application::Get('lang');
		$output = '';
		
		$sql = 'SELECT c.id,
					c.icon,
					c.icon_thumb,
					c.listings_count,
					c.listings_count_public,
					c.priority_order,
					cd.language_id,
					cd.name,									
					cd.description
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE
					c.id = '.(int)$category_id.' AND 
					cd.language_id = \''.$lang.'\'';
		$sql_1 = str_replace('_CAT_ID_', $category_id, $sql);
		$result = database_query($sql_1, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
		$output .= '<table border="1" width="100%" cellpadding="5" cellspacing="5">';	
		if($result[1] > 0){
			$output .= '<tr>';
			for($i=0; $i < $result[1]; $i++){
				if($i != 0 && $i % 4 == 0) $output .= '</tr><tr>';
				$output .= '<td valign="top" width="25%" style="border:1px solid #cccccc;">';
				
				$output .= '<h3>';
				$output .= prepare_link('categories', 'cid', $result[0][$i]['id'], '', $result[0][$i]['name']);
				$output .= prepare_link('category', 'cid', $result[0][$i]['id'], $result[0][$i]['name'], '&nbsp;&nbsp;<img src=images/url.gif>', '', _CLICK_TO_SEE_LISTINGS);
				$output .= '</h3>';
			
				$icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : 'no_image.png';
				$output .= '<div class="category_icon_small"><img src="images/categories/'.$icon_file_thumb.'" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" /></div>';

				$sql_2 = str_replace('_CAT_ID_', (int)$result[0][$i]['id'], $sql);
				$result_2 = database_query($sql_2, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				for($j=0; $j < $result_2[1]; $j++){
					$output .= prepare_link('categories', 'cid', $result_2[0][$j]['id'], '', $result_2[0][$j]['name'], '', $result_2[0][$j]['description']);
					$output .= prepare_link('category', 'cid', $result_2[0][$j]['id'], $result_2[0][$j]['name'], '&nbsp;&nbsp;<img src=images/url.gif>', '', _CLICK_TO_SEE_LISTINGS);
					$output .= '<br />';
				}				
				$output .= '</td>';
			}
			$output .= '</tr>';
		}else{
			$output .= '<tr><td>'._NO_SUBCATEGORIES.'</td><tr>';
		}
		$output .= '</table>';	
		
		//echo $output;
	}

	/**
	 * Draws sub categories
	 * 		@param $category_id
	 * 		@param $show_on
	 * 		@param $draw
	 */
	public function DrawSubCategories($category_id = '0', $show_on = '', $draw = true)
	{
		global $objLogin;

		$listings_count_field = (!$objLogin->IsLoggedIn()) ? 'listings_count_public' : 'listings_count';
		$lang = Application::Get('lang');
		$output = '';

		$categories_images = false;
		$categories_columns = '3';
		if(Modules::IsModuleInstalled('listings')){				
			if(ModulesSettings::Get('listings', 'show_categories_images') == 'yes') $categories_images = true;
			$categories_columns = ModulesSettings::Get('listings', 'columns_number_on_page');
		}
		
		$category_info = $this->GetInfoByID($category_id);

		$sql = 'SELECT c.id,
					c.icon,
					c.icon_thumb, 
					c.listings_count,
					c.listings_count_public,
					c.priority_order,
					cd.language_id,
					cd.name,									
					cd.description
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE
					c.parent_id = '.(int)$category_id.' AND 
					cd.language_id = \''.$lang.'\'';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
		
		if($result[1] > 0){
			$output .= '<table class="sub_categories_table" width="100%" align="center" border="0" style="margin:10px auto">';
			$output .= '<tr>';
			for($i=0; $i < $result[1]; $i++){
				if(($i > 0) && ($i % $categories_columns == 0)) $output .= '</tr><tr>';
				$output .= '<td align="left" valign="top" width="32px">';
				$icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : '';
				if($categories_images && $icon_file_thumb != ''){
					$output .= '<img src="images/categories/'.$icon_file_thumb.'" width="24px" height="24px" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';
				}else{
					$directory_icon = ($result[0][$i][$listings_count_field] > 0) ? 'not_empty_directory.gif' : 'empty_directory.gif';
					$output .= '<img src="images/categories/'.$directory_icon.'" width="24px" height="24px" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';				
				}
				$output .= '</td>';
				$output .= '<td>';
				$output .= prepare_link('category', 'cid', $result[0][$i]['id'], '', $result[0][$i]['name'], '', '').' ('.$result[0][$i][$listings_count_field].')';
				//$output .= '&nbsp;&nbsp;';
				//$output .= prepare_link('category', 'cid', $result[0][$i]['id'], '', '<img src=images/external_link.gif>', '', _VIEW_LISTINGS);
				$output .= '</td>';
			}
			$output .= '</tr>';
			$output .= '</table>';			
		}else{
			if($show_on == '') $output .= draw_message(_NO_SUBCATEGORIES, false, true).'<br />';
		}
		
		if($draw) echo $output;		
		else return $output;
	}
	
	/**
	 *	Returns info by ID
	 *		@param $key
	 */
	public function GetInfoByID($key = '')
	{
		if(empty($key)) return false;

		$sql = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
					'.$this->tableName.'.icon,									
					'.$this->tableName.'.priority_order,
					'.$this->tableName.'.parent_id,
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id,
					'.TABLE_CATEGORIES_DESCRIPTION.'.name,									
					'.TABLE_CATEGORIES_DESCRIPTION.'.description
				FROM '.$this->tableName.'
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' ON '.$this->tableName.'.id = '.TABLE_CATEGORIES_DESCRIPTION.'.category_id
				WHERE
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
					'.(($key != '') ? ' AND '.$this->tableName.'.'.$this->primaryKey.'='.(int)$key : '');
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
	}

	/**
	 *	Returns info of category
	 *		@param $key
	 */
	public static function GetCategoryInfo($key = '')
	{
		if(empty($key)) return false;
		
		$sql = 'SELECT '.TABLE_CATEGORIES.'.id,
					'.TABLE_CATEGORIES.'.icon,									
					'.TABLE_CATEGORIES.'.priority_order,
					'.TABLE_CATEGORIES.'.parent_id,
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id,
					'.TABLE_CATEGORIES_DESCRIPTION.'.name,									
					'.TABLE_CATEGORIES_DESCRIPTION.'.description
				FROM '.TABLE_CATEGORIES.'
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' ON '.TABLE_CATEGORIES.'.id = '.TABLE_CATEGORIES_DESCRIPTION.'.category_id
				WHERE
					'.TABLE_CATEGORIES_DESCRIPTION.'.language_id = \''.Application::Get('lang').'\'
					'.(($key != '') ? ' AND '.TABLE_CATEGORIES.'.id = '.(int)$key : '');
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
	}
	
	/**
	 *	Returns all active categories
	 */
	public static function GetAllActive($where = '')
	{		
		$sql = 'SELECT
					c.id,
					cd.name,									
					cd.description
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE 1 = 1 AND
				    cd.language_id = \''.Application::Get('lang').'\'
					'.(!empty($where) ? ' AND '.$where : '').'				
				ORDER BY cd.name ASC';			
		return database_query($sql, DATA_AND_ROWS);
	}
	
	/**
	 *	Returns all existing categories
	 */
	public function GetAllExistingCategories()
	{
		$lang = Languages::GetDefaultLang();
		$sql = 'SELECT c.id,
					c.icon,
					c.listings_count,
					c.listings_count_public,
					c.priority_order,
					cd.language_id,
					cd.name,									
					cd.description
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE
					c.parent_id = _CAT_ID_ AND 
					cd.language_id = \''.$lang.'\'
				ORDER BY priority_order ASC';
		$sql_1 = str_replace('_CAT_ID_', '0', $sql);
		$result = database_query($sql_1, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
		$output = array();	
		if($result[1] > 0){
			for($i=0; $i < $result[1]; $i++){
				$output[$result[0][$i]['id']] = array('id'=>$result[0][$i]['id'], 'name'=>$result[0][$i]['name'], 'parent_name'=>'', 'level'=>'1');
			
				$sql_2 = str_replace('_CAT_ID_', (int)$result[0][$i]['id'], $sql);
				$result_2 = database_query($sql_2, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				for($j=0; $j < $result_2[1]; $j++){
					$output[$result_2[0][$j]['id']] = array('id'=>$result_2[0][$j]['id'], 'name'=>$result_2[0][$j]['name'], 'parent_name'=>$result[0][$i]['name'], 'level'=>'2');

					$sql_3 = str_replace('_CAT_ID_', (int)$result_2[0][$j]['id'], $sql);
					$result_3 = database_query($sql_3, DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
					for($k=0; $k < $result_3[1]; $k++){
						$output[$result_3[0][$k]['id']] = array('id'=>$result_3[0][$k]['id'], 'name'=>$result_3[0][$k]['name'], 'parent_name'=>$result_2[0][$j]['name'], 'level'=>'3');
						
					}					
				}					
			}
		}
		#echo '<pre>';
		#print_r($output);
		#echo '</pre>';
		return $output;
	}
	
	/**
	 *	Returns level of current category
	 */
	private function GetLevel($cid = 0)
	{
		static $level = 0;
		$sql = 'SELECT id, parent_id FROM '.TABLE_CATEGORIES.' WHERE id = '.(int)$cid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
            // additional check with level
			if(($result[0]['parent_id'] == '0') || ($level++ > 2)) return 2;
			else return 1 + $this->GetLevel($result[0]['parent_id']);
		}
		return 1;
	}
	
	/**
	 * Returns levels info
	 */
	public function GetLevelsInfo($category_id, $target = 'category')
	{
		$lang = Application::Get('lang');
		
		$output = array('first'=>array('id'=>'', 'name'=>'', 'link'=>''),
					    'second'=>array('id'=>'', 'name'=>'', 'link'=>''),
						'third'=>array('id'=>'', 'name'=>'', 'link'=>''));

		$sql = 'SELECT
					c.id,
					c.parent_id,
					cd.name									
				FROM '.TABLE_CATEGORIES.' c
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON c.id = cd.category_id
				WHERE
					c.id = _CID_ AND
					cd.language_id = \''.$lang.'\'';
		
		$sql_1 = str_replace('_CID_', (int)$category_id, $sql);
		$result = database_query($sql_1, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output['first']['id'] = $result[0]['id'];
			$output['first']['name'] = $result[0]['name'];
			$output['first']['link'] = prepare_link($target, 'cid', $result[0]['id'], '', $result[0]['name'], '', '', true);
			
			$sql_2 = str_replace('_CID_', $result[0]['parent_id'], $sql);
			$result_2 = database_query($sql_2, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result_2[1] > 0){
				$output['second']['id'] = $result_2[0]['id'];
				$output['second']['name'] = $result_2[0]['name'];
				$output['second']['link'] = prepare_link($target, 'cid', $result_2[0]['id'], '', $result_2[0]['name'], '', '', true);				
			
				$sql_3 = str_replace('_CID_', $result_2[0]['parent_id'], $sql);
				$result_3 = database_query($sql_3, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result_3[1] > 0){
					$output['third']['id'] = $result_3[0]['id'];
					$output['third']['name'] = $result_3[0]['name'];
					$output['third']['link'] = prepare_link($target, 'cid', $result_3[0]['id'], '', $result_3[0]['name'], '', '', true);
				}				
			}
		}
		
		return $output;		
	}
	
	/**
	 * Updates listings count
	 */
	public static function UpdateListingsCount($category_id = 0, $operation = '+', $access_level = '')	
	{
		$operation_clause = '';
		if($operation == '-'){
			$operation_clause .= 'listings_count = IF(listings_count >= 1, listings_count - 1, 0)';
			if($access_level == 'public') $operation_clause .= ', listings_count_public = IF(listings_count_public >= 1, listings_count_public - 1, 0)';
		}else if($operation == '+'){			
			$operation_clause .= 'listings_count = listings_count + 1';
			if($access_level == 'public') $operation_clause .= ', listings_count_public = listings_count_public + 1';
		}else{
			if($access_level == 'public') $operation_clause .= 'listings_count_public = listings_count_public + 1';
			if($access_level == 'registered') $operation_clause .= 'listings_count_public = listings_count_public - 1';			
		}
		
		while(!empty($category_id)){			
			$sql = 'UPDATE '.TABLE_CATEGORIES.' SET '.$operation_clause.' WHERE id = '.(int)$category_id;
			if(!database_void_query($sql)){ /* echo 'error!'; */ }
			
			$sql = 'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE id = '.(int)$category_id;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$category_id = $result[0]['parent_id'];
			}else{
				$category_id = 0;
			}				
		}		
	}
	
	/**
	 * Updates listings count for all categories
	 * 		@param $parent_id
	 */
	public static function RecalculateListingsCount($parent_id = 0)	
	{
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}

		$sql = 'SELECT id, parent_id FROM '.TABLE_CATEGORIES.' WHERE parent_id = '.(int)$parent_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		$count = 0;
		$count_public = 0;
		$total_listings = array('count'=>0, 'count_public'=>0);
		$current_listings = array('count'=>0, 'count_public'=>0);
		$child_listings = array('count'=>0, 'count_public'=>0);
		
		for($i=0; $i < $result[1]; $i++){

			$child_listings = self::RecalculateListingsCount($result[0][$i]['id']);			

			$sql = 'SELECT
						COUNT(*) as cnt,
						SUM(IF('.TABLE_LISTINGS.'.access_level = "public", 1, 0)) as cnt_public						
					FROM '.TABLE_LISTINGS.'
						INNER JOIN '.TABLE_LISTINGS_CATEGORIES.' ON '.TABLE_LISTINGS.'.id = '.TABLE_LISTINGS_CATEGORIES.'.listing_id
					WHERE
						'.TABLE_LISTINGS.'.is_published = 1 AND
						('.TABLE_LISTINGS.'.finish_publishing = "0000-00-00 00:00:00" OR '.TABLE_LISTINGS.'.finish_publishing > "'.date('Y-m-d H:i:s').'") AND 
						'.TABLE_LISTINGS_CATEGORIES.'.category_id = '.(int)$result[0][$i]['id'];
			$res = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
			$current_listings['count'] = (isset($res['cnt']) ? $res['cnt'] : 0);
			$current_listings['count_public'] = (isset($res['cnt_public']) ? $res['cnt_public'] : 0);
			
			$count = $current_listings['count'] + $child_listings['count'];
			$count_public = $current_listings['count_public'] + $child_listings['count_public'];

			$sql = 'UPDATE '.TABLE_CATEGORIES.'
					SET listings_count = '.(int)$count.',
						listings_count_public = '.(int)$count_public.'
					WHERE id = '.(int)$result[0][$i]['id'];
			database_void_query($sql);
			
			$total_listings['count'] += $count;
			$total_listings['count_public'] += $count_public;
		}		

		if(mysql_error() != ''){
			self::$static_error = _TRY_LATER;
			return 0;
		}else{
			return $total_listings;
			/// ($total_listings['count'] > 0 || $total_listings['count_public'] > 0)
			/// return true;	
		}		
	}
	
}
?>