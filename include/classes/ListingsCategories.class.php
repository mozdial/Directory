<?php

/**
 *	Class ListingsCategories
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 05.02.2012
 *  Usage       : Business directory
 *
 *	PUBLIC				  	STATIC				 		PRIVATE
 * 	------------------	  	---------------     		---------------
 *	__construct             GetCategoriesForListing 
 *	__destruct
 *	BeforeInsertRecord
 *	AfterInsertRecord
 *	BeforeDeleteRecord
 *	AfterDeleteRecord
 *	
 *  1.0.1
 *      - 
 *      -
 *      -
 *      -
 *	
 **/


class ListingsCategories extends MicroGrid {
	
	protected $debug = false;
	
	//-----------------------------------------
	private $listingId = '';
	private $selectedCategoryId;
	private $isPublished;
	private $accessLevel;

	//==========================================================================
    // Class Constructor
	//		@param $listing_id
	//      @param $account_type
	//==========================================================================
	function __construct($listing_id = 0, $account_type = '')
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['category_id'])) $this->params['category_id'] = prepare_input($_POST['category_id']);
		if(isset($_POST['listing_id'])) $this->params['listing_id'] = prepare_input($_POST['listing_id']);
		
		$this->listingId = (int)$listing_id;
		$this->selectedCategoryId = '';

		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_LISTINGS_CATEGORIES;
		$this->dataSet 		= array();
		$this->error 		= '';
		if($account_type == 'me'){
			$this->formActionURL = 'index.php?customer=listings_categories&listing_id='.(int)$listing_id;	
		}else{
			$this->formActionURL = 'index.php?admin=mod_listings_categories&listing_id='.(int)$listing_id;
		}
		$this->actions      = array('add'=>true, 'edit'=>false, 'details'=>false, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = ''.$this->languageId.''';				
		$this->ORDER_CLAUSE = 'ORDER BY cd.name ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
		);

		///$date_format = get_date_format('view');
		///$date_format_edit = get_date_format('edit');				
		///$currency_format = get_currency_format();
		
		// prepare categories array
		// existing categories
		$added_categories = self::GetCategoriesForListing($this->listingId);
		$arr_added_categories = array();
		foreach($added_categories[0] as $key => $val){
			$arr_added_categories[] = $val['category_id'];
		}
		// subtruct existing categories from all 
		$objCategories = Categories::Instance();
		$total_categories = $objCategories->GetAllExistingCategories();
		$arr_categories = array();
		foreach($total_categories as $key => $val){
			if(!in_array($val['id'], $arr_added_categories)){
				if($val['level'] == '1'){
					$arr_categories[$val['id']] = $val['name'];
				}else if($val['level'] == '2'){
					$arr_categories[$val['id']] = '&nbsp;&nbsp;&bull; '.$val['name'];
				}else if($val['level'] == '3'){
					$arr_categories[$val['id']] = '&nbsp;&nbsp;&nbsp;&nbsp;:: '.$val['name'];
				}				
			}			
		}

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A''
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									lc.'.$this->primaryKey.',
									cd.name,
									cd.description
								FROM '.$this->tableName.' lc
									INNER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON lc.category_id = cd.category_id 
									INNER JOIN '.TABLE_LISTINGS.' l ON lc.listing_id = l.id
								WHERE
									cd.language_id = \''.Application::Get('lang').'\' AND 
									lc.listing_id = '.$listing_id;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'          => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'25%', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'40', 'format'=>'', 'format_parameter'=>''),
			'description'   => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'100', 'format'=>'', 'format_parameter'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'category_id' => array('title'=>_CATEGORY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_categories, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),			
			'listing_id'  => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$listing_id),										
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								lc.'.$this->primaryKey.',
								lc.listing_id,
								lc.category_id,
								cd.name as category_name,
								cd.description as category_description
							FROM '.$this->tableName.' lc
								INNER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON lc.category_id = cd.category_id
							WHERE lc.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'category_name' => array('title'=>_CATEGORY, 'type'=>'label'),
			'listing_id'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$listing_id),										
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'category_name'        => array('title'=>_CATEGORY, 'type'=>'label'),
			'category_description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
		);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	public function BeforeInsertRecord()
	{
		// check if we reached the maximum allowed categories
		$arr_added_categories = self::GetCategoriesForListing($this->listingId);

		// get maximum allowed categories for current listing		
		$objListing = Listings::Instance($this->listingId);
		$adv = AdvertisePlans::GetPlanInfo($objListing->GetField('advertise_plan_id'));
		$maximum_categories = isset($adv[0]['categories_count']) ? (int)$adv[0]['categories_count'] : ModulesSettings::Get('listings', 'maximum_categories');		
		
		if($arr_added_categories[1] >= $maximum_categories){
			$this->error = _LISTING_MAX_CATEGORIES_ALERT;
			return false;
		}
		return true;
	}

	/**
	 * After-Insertion - add listing descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		global $objLogin;
		
		$category_id = (isset($_POST['category_id'])) ? (int)$_POST['category_id'] : '0';

		$objListings = Listings::Instance($this->listingId);
		$is_published = $objListings->GetField('is_published');
		$access_level = $objListings->GetField('access_level');
		if($is_published == '1'){
			Categories::UpdateListingsCount($category_id, '+', $access_level);
		}
	}	

	/**
	 *	Before-Delete operations
	 */
	public function BeforeDeleteRecord()
	{
		$rid = MicroGrid::GetParameter('rid');

		$sql = 'SELECT
					lc.id,
					lc.category_id,
					lc.listing_id,
					l.is_published,
					l.access_level
				FROM '.TABLE_LISTINGS_CATEGORIES.' lc
					INNER JOIN '.TABLE_LISTINGS.' l ON lc.listing_id = l.id
				WHERE lc.id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->selectedCategoryId = $result[0]['category_id'];
			$this->isPublished = $result[0]['is_published'];
			$this->accessLevel = $result[0]['access_level'];
		}
		return true;
	}

	/**
	 * After-Deleting - delete listing descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		if($this->isPublished == '1'){
			Categories::UpdateListingsCount($this->selectedCategoryId, '-', $this->accessLevel);
		}
	}

	
	/**
	 * Returns all categories of a specific listing
	 * 		@param $listing_id
	 */
	public static function GetCategoriesForListing($listing_id)
	{
		$output = array();
		
		$sql = 'SELECT
					lc.id,
					lc.category_id,
					cd.name,
					cd.description
				FROM '.TABLE_LISTINGS_CATEGORIES.' lc
					INNER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON lc.category_id = cd.category_id 
					INNER JOIN '.TABLE_LISTINGS.' l ON lc.listing_id = l.id
				WHERE
					cd.language_id = \''.Application::Get('lang').'\' AND 
					lc.listing_id = '.$listing_id;		

		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			$output = $result;
		}
		
		return $output;		
	}
	
	
}

?>