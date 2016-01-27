<?php

/**
 * 	Class Listings (for BusinessDirectory ONLY)
 *  -------------- 
 *  Description : encapsulates listings properties
 *	Usage       : BusinessDirectory
 *  Updated	    : 11	10.2012
 *	Written by  : ApPHP
 *	Version     : 1.0.1
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:                    PROTECTED
 * 	------------------	  	---------------     	---------------             ---------------
 *	__construct             GetListings             __construct_single          OnItemCreated_ViewMode
 *	__destruct              GetRandomListing        __construct_all
 *	__construct_single      GetAllListings          ValidateTranslationFields
 *	__construct_all         DrawFeaturedBlock       ValidateKeywordNumber
 *	BeforeInsertRecord      DrawFeaturedAll
 *	AfterInsertRecord       DrawRecentBlock
 *	BeforeEditRecord        DrawRecentAll 
 *	BeforeUpdateRecord      DrawDirectoryStatistics
 *  AfterUpdateRecord       CacheAllowed
 *  BeforeDeleteRecord      CacheAllowed
 *  AfterDeleteRecord       Instance  
 *  BeforeDetailsRecord     GetCustomerInfoByListing
 *  GetField                UpdateStatus 
 *  DrawListing             
 *  DrawListings
 *
 **/


class Listings extends MicroGrid {
	
	protected $debug = false;

	//----------------------------------	
	private static $instance;
	
	private $arrTranslations = '';	
	private $accessLevel;
	private $advertisePlanID;
	private $isApproved;
	private $listing_info = array();
	private $is_published = false;
	private $show_expired_listings = true;
	private $bpf_keywords_count = 0;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($id = '')
	{
		parent::__construct();
		
		$this->show_expired_listings = ModulesSettings::Get('listings', 'show_expired_listings');		

		if($id != ''){
			$this->__construct_single($id);
		}else{
			$this->__construct_all();
		}		
	}
	
	//==========================================================================
    // Constructor for Single Record
	//==========================================================================
	private function __construct_single($id)
	{
		$lang = Application::Get('lang');
		
		$sql = 'SELECT 
					l.*,
					ld.business_name,
					ld.business_address,
					ld.business_description,
					ll.name as listing_location,
					lsl.name as listing_sub_location
				FROM '.TABLE_LISTINGS.' l
					LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
					LEFT OUTER JOIN '.TABLE_LISTINGS_LOCATIONS.' ll ON l.listing_location_id = ll.id
					LEFT OUTER JOIN '.TABLE_LISTINGS_SUB_LOCATIONS.' lsl ON l.listing_sub_location_id = lsl.id
				WHERE
					'.(($this->show_expired_listings != 'yes') ? ' ((l.finish_publishing = \'0000-00-00 00:00:00\') OR (l.finish_publishing > \''.date('Y-m-d H:i:s').'\')) AND ' : '').'
					ld.language_id = \''.$lang.'\' AND 
					l.id = '.(int)$id;
		$this->listing_info = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);		
	}

	//==========================================================================
    // Constructor for All Records
	//==========================================================================
	private function __construct_all()
	{
		global $objLogin;

		$this->params = array();		
		if(isset($_POST['website_url'])){
			$website_url = prepare_input($_POST['website_url'], false, 'medium');
			if(preg_match('/www./i', $website_url) && !preg_match('/http:/i', $website_url)){
				$website_url = 'http://'.$website_url;
			}
			$this->params['website_url'] = $website_url;
		}
		if(isset($_POST['listing_location_id']))      $this->params['listing_location_id'] = prepare_input($_POST['listing_location_id']);
		if(isset($_POST['listing_sub_location_id']))  $this->params['listing_sub_location_id'] = prepare_input($_POST['listing_sub_location_id']);
		if(isset($_POST['business_email'])) $this->params['business_email'] = prepare_input($_POST['business_email']);
		if(isset($_POST['business_phone'])) $this->params['business_phone'] = prepare_input($_POST['business_phone']);
		if(isset($_POST['business_fax']))   $this->params['business_fax'] = prepare_input($_POST['business_fax']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_featured']))    $this->params['is_featured'] = (int)$_POST['is_featured']; else $this->params['is_featured'] = '0';
		if(isset($_POST['is_published']))   $this->params['is_published'] = (int)$_POST['is_published']; else $this->params['is_published'] = '0';
		if(isset($_POST['is_approved']))    $this->params['is_approved'] = prepare_input($_POST['is_approved']);
		if(isset($_POST['access_level']))   $this->params['access_level'] = prepare_input($_POST['access_level']);
		if(isset($_POST['date_published'])) $this->params['date_published'] = prepare_input($_POST['date_published']);
		if(isset($_POST['customer_id']))    $this->params['customer_id'] = (int)($_POST['customer_id']);
		if(isset($_POST['map_code']))       $this->params['map_code'] = prepare_input($_POST['map_code'], false, 'low');
		if(isset($_POST['video_url']))      $this->params['video_url'] = prepare_input($_POST['video_url'], false, 'low');
		if(isset($_POST['keywords']))       $this->params['keywords'] = implode(',', array_map('trim', explode(',', prepare_input($_POST['keywords']))));		
		if(isset($_POST['advertise_plan_id'])) $this->params['advertise_plan_id'] = prepare_input($_POST['advertise_plan_id']);
		
		///$this->params['language_id'] 	= MicroGrid::GetParameter('language_id');
		$rid = MicroGrid::GetParameter('rid');
		$action = MicroGrid::GetParameter('action');
		$operation = MicroGrid::GetParameter('operation');
		$filter_by_cid = MicroGrid::GetParameter('filter_by_cid', false);
		$payments_module = (Modules::IsModuleInstalled('payments') && ModulesSettings::Get('payments', 'is_active') == 'yes') ? true : false;
        $watermark = (ModulesSettings::Get('listings', 'watermark') == 'yes') ? true : false;
        $watermark_text = ModulesSettings::Get('listings', 'watermark_text');
		
		if($action != 'view') $listings_info = self::GetListingInfo($rid);
				
		## for images
		if(isset($_POST['image_file'])){
			$this->params['image_file'] = prepare_input($_POST['image_file']);
		}else if(isset($_FILES['image_file']['name']) && $_FILES['image_file']['name'] != ''){
			// nothing 			
		}else if(self::GetParameter('action') == 'create'){
			$this->params['image_file'] = '';
		}
		
		if($payments_module){
			$listings_count = $objLogin->GetAvailableListings();	
		}else{
			$listings_count = -1;	
		}		
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_LISTINGS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?'.(($objLogin->GetLoggedType() == 'customer') ? 'customer=my_listings' : 'admin=mod_listings_management');
		$categoriesActionURL = 'index.php?'.(($objLogin->GetLoggedType() == 'customer') ? 'customer=listings_categories' : 'admin=mod_listings_categories');		
		$this->actions      = array('add'=>(($objLogin->GetLoggedType() == 'customer' && $listings_count == 0) ? false : true), 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->advertisePlanID = '0';
		$this->accessLevel = '';
		$this->isApproved = 0;

		$this->allowLanguages = false;
		//$this->languageId  	=  '';
		$default_lang = (($objLogin->GetLoggedType() == 'customer') ? Languages::GetDefaultLang() : $objLogin->GetPreferredLang());
		$is_expired = false;
		if($action == 'edit' && $action == 'details'){			
			$is_expired = ($listings_info['finish_publishing'] == '0000-00-00 00:00:00' || $listings_info['finish_publishing'] > date('Y-m-d H:i:s')) ? false : true;
		}		
	
		$this->WHERE_CLAUSE = 'WHERE ld.language_id = \''.$default_lang.'\'';
		if($operation != 'reset_filtering' && !empty($filter_by_cid)) $this->WHERE_CLAUSE .= ' AND l.id IN (SELECT lc.listing_id FROM '.TABLE_LISTINGS_CATEGORIES.' lc WHERE lc.category_id = '.(int)$filter_by_cid.')';
		$this->ORDER_CLAUSE = 'ORDER BY l.date_published ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = ($objLogin->IsLoggedInAsAdmin() ? true : false);
		$this->arrExportingTypes = array('csv'=>true);

		// prepare access levels array
		$arr_access_levels = array('public'=>_PUBLIC, 'registered'=>_REGISTERED);
		// prepare featured array
		$arr_featured = array('0'=>_NO, '1'=>_YES);
		// prepare published array
		$arr_published = array('0'=>_NO, '1'=>_YES);
		
		// prepare advertise plans array
		$arr_advertise_plans = array();
		$total_plans = AdvertisePlans::GetAllPlans();
		foreach($total_plans[0] as $key => $val){
			$arr_advertise_plans[$val['id']] = $val['plan_name'];
		}
		if($payments_module){
			$arr_customer_advertise_plans = array();
			if($objLogin->IsLoggedInAsCustomer()){
				$plans = $objLogin->GetCustomerPlans();
				foreach($plans as $key => $val){
					$arr_customer_advertise_plans[$val['id']] = $val['name'].' ('.$val['count'].' '._LISTINGS.')';
				}					
			}
		}
		
		// prepare categories array
		$objCategories = Categories::Instance();
		$total_categories = $objCategories->GetAllExistingCategories();
		$arr_categories = array();
		foreach($total_categories as $key => $val){
			if($val['level'] == '1'){
				$arr_categories[$val['id']] = $val['name'];
			}else if($val['level'] == '2'){
				$arr_categories[$val['id']] = '&nbsp;&nbsp;&bull; '.$val['name'];
			}else if($val['level'] == '3'){
				$arr_categories[$val['id']] = '&nbsp;&nbsp;&nbsp;&nbsp;:: '.$val['name'];
			}
		}
		$datetime_format = get_datetime_format();
		$date_format = get_date_format();

		$arr_locations = array();
		$arr_sub_locations = array();
		if($action != 'view'){
			// prepare array for location
			$total_locations = ListingsLocations::GetAllLocations('name ASC');
			foreach($total_locations[0] as $key => $val){
				$arr_locations[$val['id']] = $val['name'];
			}
			if($action != 'edit' && $action != 'details'){
				// prepare array for sub-location
				$listing_location_id = (int)MicroGrid::GetParameter('listing_location_id', false);
				$total_sub_locations = ListingsSubLocations::GetAllSubLocations($listing_location_id, 'name ASC');
				foreach($total_sub_locations[0] as $key => $val){
					$arr_sub_locations[$val['id']] = $val['name'];
				}
			}
		}
		
		// define filtering fields
		$this->isFilteringAllowed = true;
		$this->arrFilteringFields = array(
			_NAME     => array('table'=>'ld', 'field'=>'business_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'110px'),
			_CATEGORY => array('table'=>'c', 'field'=>'id', 'type'=>'dropdownlist', 'source'=>$arr_categories, 'sign'=>'=', 'width'=>'', 'visible'=>($objLogin->IsLoggedInAsAdmin() ? true : false), 'custom_handler'=>true),
			_FEATURED => array('table'=>'l', 'field'=>'is_featured', 'type'=>'dropdownlist', 'source'=>$arr_featured, 'sign'=>'=', 'width'=>'', 'visible'=>($objLogin->IsLoggedInAsAdmin() ? true : false)),
			_PUBLISHED => array('table'=>'l', 'field'=>'is_published', 'type'=>'dropdownlist', 'source'=>$arr_published, 'sign'=>'=', 'width'=>''),
		);

		// retrieve pre-moderation settings
		$pre_moderation_allow = (ModulesSettings::Get('listings', 'pre_moderation_allow') == 'yes') ? true : false;
	
		///////////////////////////////////////////////////////////////////////////////
		// 1. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('business_name', 'business_address', 'business_description')
		);
		///////////////////////////////////////////////////////////////////////////////			
		
		///////////////////////////////////////////////////////////////////////////////			
		// 2. prepare translations array for edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_LISTINGS_DESCRIPTION,
			'listing_id',
			array('business_name', 'business_address', 'business_description')
		);
		///////////////////////////////////////////////////////////////////////////////			
		
		// retrieve default priority order for new record
		$default_priority_order = '';
		if(self::GetParameter('action') == 'add'){
			$default_priority_order = $this->GetMaxOrder('priority_order', 9999);
		}
		
		// get plan features
		$advertise_plan_id = '';
		if($action == 'add' || $action == 'create'){
			$advertise_plan_id = MicroGrid::GetParameter('advertise_plan_id', false);
		}else if(in_array($action, array('details', 'edit', 'update'))){
			// 1st time
			if(MicroGrid::GetParameter('advertise_plan_id', false)){
				$advertise_plan_id = MicroGrid::GetParameter('advertise_plan_id', false);				
			}else{
				$advertise_plan_id = $listings_info['advertise_plan_id'];
			}			
		}
		
		if(empty($advertise_plan_id)) $advertise_plan_id = 1;	
		$advertise_plan_info = AdvertisePlans::GetPlanInfo($advertise_plan_id);
		
		// print_r($advertise_plan_info);
		$bpf_business_name = $advertise_plan_info[0]['business_name'];
		$bpf_business_description = $advertise_plan_info[0]['business_description'];
		$bpf_address = $advertise_plan_info[0]['address'];
		$bpf_logo = $advertise_plan_info[0]['logo'];
		$bpf_images_count = $advertise_plan_info[0]['images_count'];
		$bpf_phone = $advertise_plan_info[0]['phone'];
		$bpf_map = $advertise_plan_info[0]['map'];
		$bpf_video_link = $advertise_plan_info[0]['video_link'];
		$this->bpf_keywords_count = (int)$advertise_plan_info[0]['keywords_count'];
		// not needed to be inserted: inquiries_count, inquiries_count/per month, rating_button 

		
		//---------------------------------------------------------------------- 
		// VIEW MODE
		//----------------------------------------------------------------------
		$this->VIEW_MODE_SQL = 'SELECT l.'.$this->primaryKey.',
		                            l.customer_id,
									l.image_file,
									l.image_file_thumb,
									l.website_url,
									l.business_email,
									l.business_phone,
									l.business_fax,
									l.priority_order,
									l.access_level,
									l.is_featured,
									l.advertise_plan_id,
									IF(
										l.finish_publishing = "0000-00-00 00:00:00",
										"- '._NEVER.' -",
										IF(l.finish_publishing < \''.date('Y-m-d H:i:s').'\', "<span class=no>'._EXPIRED.'</span>", l.finish_publishing)
									) as finish_publishing,
									CONCAT("<img src=\"images/", IF(l.is_published, "published_g.gif", "published_x.gif"), "\" alt=\"\" />") as mod_is_published,
									CONCAT("<img src=\"images/", IF(l.is_approved, "published_g.gif", "waiting.gif"), "\" alt=\"\" />") as mod_is_approved,
									ld.business_name,
									IF(l.customer_id = 0, \'Admin\', cust.user_name) as customer_username,
									CONCAT("[ '._CATEGORIES.' ]", " (", (SELECT COUNT(*) FROM '.TABLE_LISTINGS_CATEGORIES.' lc WHERE lc.listing_id = l.id), ")") as link_categories
								FROM '.$this->tableName.' l
								    '.(($objLogin->GetLoggedType() == 'customer') ? 'INNER JOIN '.TABLE_CUSTOMERS.' cust ON (l.customer_id = cust.id AND l.customer_id='.(int)$objLogin->GetLoggedID().')' : 'LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cust ON (l.customer_id = cust.id)').'
									LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON (l.id = ld.listing_id AND ld.language_id = \''.$default_lang.'\')';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'image_file_thumb'  => array('title'=>_IMAGE, 'type'=>'image', 'sortable'=>false, 'align'=>'left', 'width'=>'64px', 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'images/listings/', 'no_image'=>'no_image.png'),
			'business_name'     => array('title'=>_NAME, 'type'=>(($objLogin->GetLoggedType() == 'customer') ? 'link' : 'label'),  'align'=>'left', 'width'=>'200px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'45', 'format'=>'', 'format_parameter'=>'', 'href'=>'index.php?page=listing&lid={id}', 'target'=>'_new'),
			'advertise_plan_id'  => array('title'=>_ADVERTISE_PLAN, 'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_advertise_plans),
			'customer_username' => array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'center', 'width'=>'', 'visible'=>($objLogin->IsLoggedInAsAdmin() ? true : false)),
			'access_level'      => array('title'=>_ACCESS, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'source'=>$arr_access_levels, 'visible'=>($objLogin->IsLoggedInAsAdmin() ? true : false)),
			//'is_featured'       => array('title'=>_FEATURED, 'type'=>'enum',  'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'source'=>$arr_featured),
			'mod_is_published'  => array('title'=>_PUBLISHED, 'type'=>'label', 'align'=>'center', 'width'=>'70px', 'visible'=>$objLogin->IsLoggedInAsAdmin()),
			'mod_is_approved'   => array('title'=>_STATUS, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'visible'=>$objLogin->IsLoggedInAsCustomer()),
			'finish_publishing' => array('title'=>_FINISH_PUBLISHING, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'date', 'format_parameter'=>$date_format),
			'link_categories'   => array('title'=>'', 'type'=>'link',  'align'=>'center', 'width'=>'115px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>$categoriesActionURL.'&listing_id={id}', 'target'=>''),
			'priority_order'    => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'60px', 'movable'=>true, 'visible'=>($objLogin->IsLoggedInAsAdmin() ? true : false)),
			'id'                => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'separator_general'   =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'advertise_plan_id' => array('title'=>_ADVERTISE_PLAN, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>(($objLogin->IsLoggedInAsAdmin()) ? $arr_advertise_plans : $arr_customer_advertise_plans), 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'onchange="appChangePlan(\''.$this->tableName.'\')"', 'visible'=>$payments_module),
				'listing_location_id'     => array('title'=>_LOCATION,	 'type'=>'enum',     'width'=>'', 'source'=>$arr_locations, 'required'=>true, 'default'=>'', 'javascript_event'=>'onchange="appRefillLocations(\''.$this->tableName.'\')"'),
				'listing_sub_location_id' => array('title'=>_SUB_LOCATION, 'type'=>'enum',     'width'=>'', 'source'=>$arr_sub_locations, 'required'=>true, 'default'=>''),
				'website_url'    => array('title'=>_WEBSITE_URL.' (http://)', 'type'=>'textbox',  'width'=>'270px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'maxlength'=>'255'),				
				'business_email' => array('title'=>_EMAIL_ADDRESS, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'email', 'maxlength'=>'75'),
				'date_published' => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>(($pre_moderation_allow && $objLogin->GetLoggedType() == 'customer') ? '0000-00-00 00:00:00' : date('Y-m-d H:i:s'))),
				'customer_id'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>($objLogin->GetLoggedType() == 'customer') ? $objLogin->GetLoggedID() : '0'),
			),			
		);		

		if($bpf_phone){
			$this->arrAddModeFields['separator_general']['business_phone'] = array('title'=>_PHONE, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');
			$this->arrAddModeFields['separator_general']['business_fax']   = array('title'=>_FAX, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');
		}
		if($bpf_video_link){
			$this->arrAddModeFields['separator_general']['video_url'] = array('title'=>_VIDEO, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');			
		}
		if($this->bpf_keywords_count){
			$this->arrAddModeFields['separator_general']['keywords'] = array('title'=>_KEYWORDS, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'58px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
		}else{
			$this->arrAddModeFields['separator_general']['keywords'] = array('title'=>'', 'type'=>'hidden', 'required'=>false, 'readonly'=>false, 'default'=>'');
		}
		if($bpf_logo){
			$this->arrAddModeFields['separator_gallery']['separator_info'] = array('legend'=>_GALLERY);
			$this->arrAddModeFields['separator_gallery']['image_file'] = array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/listings/', 'no_image'=>'', 'image_width'=>'150px', 'image_height'=>'120px', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_thumb', 'thumbnail_width'=>'150px', 'thumbnail_height'=>'120px', 'file_maxsize'=>'200k');
			for($i=1; $i <= $bpf_images_count; $i++){
				$this->arrAddModeFields['separator_gallery']['image_'.$i] = array('title'=>_IMAGE.' #'.$i, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/listings/', 'no_image'=>'', 'image_width'=>'150px', 'image_height'=>'120px', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_'.$i.'_thumb', 'thumbnail_width'=>'150px', 'thumbnail_height'=>'120px', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text);
			}
		}
		if($bpf_map){
			$this->arrAddModeFields['separator_map']['separator_info'] = array('legend'=>_MAP_CODE);
			$this->arrAddModeFields['separator_map']['map_code'] = array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
		}else{
			$this->arrAddModeFields['separator_general']['map_code'] = array('title'=>'', 'type'=>'hidden', 'required'=>false, 'readonly'=>false, 'default'=>'');
		}		
		if($objLogin->IsLoggedInAsAdmin()){
			$this->arrAddModeFields['separator_general']['is_published']   = array('title'=>_PUBLISHED, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0');
			$this->arrAddModeFields['separator_general']['is_approved']    = array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'1');
			$this->arrAddModeFields['separator_general']['priority_order'] = array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'default'=>$default_priority_order, 'readonly'=>false, 'validation_type'=>'numeric|positive', 'maxlength'=>'4');
			$this->arrAddModeFields['separator_general']['is_featured']    = array('title'=>_FEATURED, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0');
			$this->arrAddModeFields['separator_general']['access_level']   = array('title'=>_ACCESSIBLE_BY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'public', 'source'=>$arr_access_levels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'');
		}else{
			if($pre_moderation_allow){
				$this->arrAddModeFields['separator_general']['is_published'] = array('type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'0');
				$this->arrAddModeFields['separator_general']['is_approved']  = array('type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'0');
				$this->arrAddModeFields['separator_general']['access_level'] = array('type'=>'hidden', 'required'=>true, 'default'=>'public');
			}else{
				$this->arrAddModeFields['separator_general']['is_published'] = array('type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'1');
				$this->arrAddModeFields['separator_general']['is_approved']  = array('type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'1');
			}
		}

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		// define edit mode fields
		$this->EDIT_MODE_SQL = 'SELECT
								l.*,
								'.$sql_translation_description.'
								IF(
									l.finish_publishing = \'0000-00-00 00:00:00\',
									"- '._NEVER.' -",
									IF(l.finish_publishing < \''.date('Y-m-d H:i:s').'\', \'<span class=no>'._EXPIRED.'</span>\', l.finish_publishing)
								) as finish_publishing,								
								CONCAT("<img src=\"images/listings/", l.image_file_thumb, "\" alt=\"\" width=\"40px\ height=\"30px\" />") as my_image_file,
								IF(l.is_featured, \''._YES.'\', \''._NO.'\') as mod_is_featured,
								IF(l.is_published, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_is_published,
								IF(l.is_approved, "<span class=yes>'._APPROVED.'</span>", "<span class=no>'._PENDING.'</span>") as mod_is_approved
							FROM '.$this->tableName.' l
								'.(($objLogin->GetLoggedType() == 'customer') ? 'INNER JOIN '.TABLE_CUSTOMERS.' cust ON (l.customer_id = cust.id AND l.customer_id='.(int)$objLogin->GetLoggedID().')' : '').'
							WHERE l.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_general'   =>array(
				'separator_info' => array('legend'=>_GENERAL),
				'advertise_plan_id' => array('title'=>_ADVERTISE_PLAN, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>($objLogin->IsLoggedInAsAdmin() ? ($is_expired ? true : false) : true), 'default'=>'', 'source'=>$arr_advertise_plans, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'onchange="appChangePlan(\''.$this->tableName.'\')"', 'visible'=>$payments_module),
				'listing_location_id' 	  => array('title'=>_LOCATION, 'type'=>'enum', 'width'=>'', 'source'=>$arr_locations, 'required'=>true, 'javascript_event'=>'onchange="appRefillLocations(\''.$this->tableName.'\')"'),
				'listing_sub_location_id' => array('title'=>_SUB_LOCATION,	'type'=>'enum', 'width'=>'', 'source'=>$arr_sub_locations, 'required'=>true),
				'website_url'    => array('title'=>_WEBSITE_URL.' (http://)', 'type'=>'textbox',  'width'=>'270px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'maxlength'=>'255'),
				'business_email' => array('title'=>_EMAIL_ADDRESS, 'type'=>'textbox',  'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'email', 'maxlength'=>'75'),				
			),
		);
		if($bpf_phone){
			$this->arrEditModeFields['separator_general']['business_phone'] = array('title'=>_PHONE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');
			$this->arrEditModeFields['separator_general']['business_fax']   = array('title'=>_FAX, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');
		}
		if($bpf_video_link){
			$this->arrEditModeFields['separator_general']['video_url'] = array('title'=>_VIDEO, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'default'=>'', 'readonly'=>false, 'validation_type'=>'', 'maxlength'=>'32');			
		}
		if($this->bpf_keywords_count){
			$this->arrEditModeFields['separator_general']['keywords'] = array('title'=>_KEYWORDS, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'58px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
		}
		if($bpf_logo){
			$this->arrEditModeFields['separator_gallery']['separator_info'] = array('legend'=>_GALLERY);
			$this->arrEditModeFields['separator_gallery']['image_file'] = array('title'=>_LOGO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/listings/', 'no_image'=>'', 'image_width'=>'150px', 'image_height'=>'120px', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_thumb', 'thumbnail_width'=>'150px', 'thumbnail_height'=>'120px', 'file_maxsize'=>'200k');
			for($i=1; $i <= $bpf_images_count; $i++){
				$this->arrEditModeFields['separator_gallery']['image_'.$i] = array('title'=>_IMAGE.' #'.$i, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/listings/', 'no_image'=>'', 'image_width'=>'150px', 'image_height'=>'120px', 'random_name'=>'true', 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'image_'.$i.'_thumb', 'thumbnail_width'=>'150px', 'thumbnail_height'=>'120px', 'file_maxsize'=>'500k', 'watermark'=>$watermark, 'watermark_text'=>$watermark_text);
			}
		}
		if($bpf_map){
			$this->arrEditModeFields['separator_map']['separator_info'] = array('legend'=>_MAP_CODE);
			$this->arrEditModeFields['separator_map']['map_code'] = array('title'=>_MAP_CODE, 'type'=>'textarea', 'required'=>false, 'width'=>'480px', 'height'=>'100px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false);
		}
        $this->arrEditModeFields['separator_general']['mod_is_approved'] = array('title'=>_STATUS, 'type'=>'label');
		if($objLogin->IsLoggedInAsAdmin()){
			$this->arrEditModeFields['separator_general']['priority_order'] = array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive', 'maxlength'=>'4');
			$this->arrEditModeFields['separator_general']['is_featured'] 	= array('title'=>_FEATURED, 'type'=>'checkbox', 'readonly'=>(($is_expired) ? true : false), 'true_value'=>'1', 'false_value'=>'0');
			///$this->arrEditModeFields['separator_general']['date_published'] = array('title'=>_DATE_PUBLISHED, 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s'));
			$this->arrEditModeFields['separator_general']['is_published'] 	= array('title'=>_PUBLISHED, 'type'=>'checkbox', 'readonly'=>(($is_expired) ? true : false), 'visible'=>(($is_expired) ? false : true), 'true_value'=>'1', 'false_value'=>'0');			
			$this->arrEditModeFields['separator_general']['access_level']   = array('title'=>_ACCESSIBLE_BY, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>(($is_expired) ? true : false), 'default'=>'public', 'source'=>$arr_access_levels, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'');
		}
		$this->arrEditModeFields['separator_general']['finish_publishing'] = array('title'=>_FINISH_PUBLISHING, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$date_format);			
		$this->arrEditModeFields['separator_general']['customer_id'] = array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>'');


		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_general'   =>array(
				'separator_info'   => array('legend'=>_GENERAL),
				'advertise_plan_id'       => array('title'=>_ADVERTISE_PLAN, 'type'=>'enum', 'source'=>$arr_advertise_plans, 'visible'=>$payments_module),
				'listing_location_id'     => array('title'=>_LOCATION,	 'type'=>'enum', 'source'=>$arr_locations),
				'listing_sub_location_id' => array('title'=>_SUB_LOCATION,	 'type'=>'enum', 'source'=>$arr_sub_locations),
				'website_url'      => array('title'=>_WEBSITE_URL, 'type'=>'label'),
				'business_email'   => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
				'mod_is_approved'  => array('title'=>_STATUS, 'type'=>'label'),
				'mod_is_published' => array('title'=>_PUBLISHED, 'type'=>'label'),
				'date_published'   => array('title'=>_DATE_PUBLISHED, 'type'=>'datetime', 'format'=>'datetime', 'format_parameter'=>$datetime_format),
			),
		);
		if($bpf_phone){
			$this->arrDetailsModeFields['separator_general']['business_phone'] = array('title'=>_PHONE, 'type'=>'label');
			$this->arrDetailsModeFields['separator_general']['business_fax']   = array('title'=>_FAX, 'type'=>'label');
		}		
		if($bpf_video_link){
			$this->arrDetailsModeFields['separator_general']['video_url'] = array('title'=>_VIDEO, 'type'=>'label');
		}
		if($this->bpf_keywords_count){
			$this->arrDetailsModeFields['separator_general']['keywords'] = array('title'=>_KEYWORDS, 'type'=>'label');
		}
		if($bpf_logo){
			$this->arrDetailsModeFields['separator_gallery']['separator_info'] = array('legend'=>_GALLERY);
			$this->arrDetailsModeFields['separator_gallery']['image_file'] = array('title'=>_LOGO, 'type'=>'image', 'target'=>'images/listings/', 'image_width'=>'90px', 'image_height'=>'80px', 'no_image'=>'no_image.png');
			for($i=1; $i <= $bpf_images_count; $i++){
				$this->arrDetailsModeFields['separator_gallery']['image_'.$i] = array('title'=>_IMAGE.' #'.$i, 'type'=>'image', 'target'=>'images/listings/', 'image_width'=>'90px', 'image_height'=>'80px', 'no_image'=>'no_image.png');
			}
		}
		if($bpf_map){
			$this->arrDetailsModeFields['separator_map']['separator_info'] = array('legend'=>_MAP_CODE);
			$this->arrDetailsModeFields['separator_map']['map_code'] = array('title'=>_MAP_CODE, 'type'=>'html');
		}		
		if($objLogin->IsLoggedInAsAdmin()){
			$this->arrDetailsModeFields['separator_general']['priority_order'] = array('title'=>_ORDER, 'type'=>'label');
			$this->arrDetailsModeFields['separator_general']['mod_is_featured'] = array('title'=>_FEATURED, 'type'=>'label');
			$this->arrDetailsModeFields['separator_general']['access_level']   = array('title'=>_ACCESSIBLE_BY, 'type'=>'enum', 'source'=>$arr_access_levels);
		}
		$this->arrDetailsModeFields['separator_general']['finish_publishing'] = array('title'=>_FINISH_PUBLISHING, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$date_format);

		///////////////////////////////////////////////////////////////////////////////
		// 3. add translation fields to all modes
		$arrTranslations = array();
		if($bpf_business_name)        $arrTranslations['business_name'] = array('title'=>_NAME, 'type'=>'textbox', 'required'=>true, 'width'=>'410px', 'maxlength'=>'125');
		if($bpf_address)              $arrTranslations['business_address'] = array('title'=>_ADDRESS, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'50px', 'maxlength'=>'255');
		if($bpf_business_description) $arrTranslations['business_description'] = array('title'=>_DESCRIPTION, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'90px', 'maxlength'=>'1024');

		$this->AddTranslateToModes(
			$this->arrTranslations,
			$arrTranslations
		);		
		
		///////////////////////////////////////////////////////////////////////////////			
		
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 *	Return instance of the class
	 */
	public static function Instance($id = '')
	{
		if(self::$instance == null) self::$instance = new Listings($id);
		return self::$instance;
	}       
	
	/**
	 * Returns listings
	 */
	public static function GetListings()
	{
		$lang = Application::Get('lang');
		$output = '';
		
		$sql = 'SELECT
					l.id, l.image_file, l.website_url, l.priority_order,
					ld.business_name
				FROM '.TABLE_LISTINGS.' l
					LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
				WHERE l.is_published = 1 AND l.image_file != \'\' AND ld.language_id = \''.$lang.'\' 
				ORDER BY RAND() ASC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$image = '<img src="images/listings/'.$result[0]['image_file'].'" width="723px" height="140px" alt="" />';	
			if($result[0]['website_url'] != '' && $result[0]['website_url'] != 'http://'){
				$output .= '<a href="'.$result[0]['website_url'].'" title="'.$result[0]['business_name'].'">'.$image.'</a>';
			}else{
				$output .= $image;
			}			
		}
	    return $output;
	}
	
	/**
	 * Returns random listing
	 */
	public static function GetRandomListing()
	{
		$lang = Application::Get('lang');
		$output = '';
		
		$sql = 'SELECT 
					l.id, l.image_file, l.website_url, l.priority_order,
					ld.business_name
				FROM '.TABLE_LISTINGS.' l
					LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
				WHERE l.is_published = 1 AND l.image_file != \'\' AND ld.language_id = \''.$lang.'\' 
				ORDER BY RAND() ASC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$image = '<img src="images/listings/'.$result[0]['image_file'].'" title="'.$result[0]['business_name'].'" width="100%" height="140px" alt="" />';
			if($result[0]['website_url'] != '' && $result[0]['website_url'] != 'http://'){
				$output .= '<a href="'.$result[0]['website_url'].'" title="'.$result[0]['business_name'].'">'.$image.'</a>';
			}else{
				$output .= $image;
			}			
		}
	    return $output;
	}

	/**
	 * Returns listing info
	 */
	public static function GetListingInfo($listing_id = 0, $field = '')
	{
		$output = '';
		if(empty($listing_id)) return false;
		$sql = 'SELECT 
					l.*,
					ld.business_name, ld.business_address, ld.business_description  
				FROM '.TABLE_LISTINGS.' l
					LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
				WHERE
					ld.language_id = \''.Application::Get('lang').'\' AND 
					l.id = '.(int)$listing_id;				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if(empty($field)) $output = $result[0];
			else $output = isset($result[0][$field]) ? $result[0][$field] : '';
		}
		return $output;
	}

	/**
	 * Returns listings array
	 * 		@param $where_clause
	 * 		@param $order_clause
	 * 		@param $limit
	 */
	public static function GetAllListings($where_clause = '', $order_clause = 'priority_order ASC', $limit = '')
	{
		$lang = Application::Get('lang');
		$output = array('0'=>array(), '1'=>'0');
		
		$sql = 'SELECT 
					l.id,
					l.image_file,
					l.date_published,
					l.website_url,
					l.priority_order,
					ld.business_name,
					cd.name as category_name
				FROM '.TABLE_LISTINGS.' l
					INNER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
					INNER JOIN '.TABLE_LISTINGS_CATEGORIES.' lc ON l.id = lc.listing_id
					INNER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON cd.category_id = lc.category_id
				WHERE
					l.is_published = 1 AND
					ld.language_id = \''.$lang.'\' AND 
					cd.language_id = \''.$lang.'\' 
					'.(($where_clause != '') ? ' AND '.$where_clause : '').'
				GROUP BY l.id 
				ORDER BY '.$order_clause.
				((!empty($limit)) ? ' LIMIT 0, '.(int)$limit : '');
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$output[0] = $result[0];
			$output[1] = $result[1];
		}
	    return $output;
	}
	
	/**
	 * Draw single listing
	 * 		@param $draw
	 */
	public function DrawListing($draw = true)
	{
		$output = '';
		$nl = "\n";
		
		if(isset($this->listing_info[1]) && $this->listing_info[1] > 0){
			
			// get info about some fields that depends on advertise plan
			$advertise_plan_info = AdvertisePlans::GetPlanInfo($this->GetField('advertise_plan_id'));			
			$bpf_business_name = $advertise_plan_info[0]['business_name'];
			$bpf_business_description = $advertise_plan_info[0]['business_description'];
			$bpf_address = $advertise_plan_info[0]['address'];
			$bpf_logo = $advertise_plan_info[0]['logo'];
			$bpf_images_count = $advertise_plan_info[0]['images_count'];
			$bpf_phone = $advertise_plan_info[0]['phone'];
			$bpf_map = $advertise_plan_info[0]['map'];
			$bpf_video_link = $advertise_plan_info[0]['video_link'];
			$bpf_inquiry_button = $advertise_plan_info[0]['inquiry_button'];
			$bpf_rating_button = $advertise_plan_info[0]['rating_button'];
			$nl = "\n";
		
			if($bpf_logo && $bpf_images_count && !Application::Get('js_included', 'lytebox')){
				$output .= '<!-- LyteBox v3.22 Author: Markus F. Hay Website: http://www.dolem.com/lytebox -->'.$nl;
				$output .= '<link rel="stylesheet" href="modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'.$nl;
				$output .= '<script type="text/javascript" language="javascript" src="modules/lytebox/js/lytebox.js"></script>'.$nl;
			}
			
			if(Modules::IsModuleInstalled('ratings') == 'yes' && $bpf_rating_button){
				$output .= '<link href="modules/ratings/css/ratings.css" rel="stylesheet" type="text/css" />';
				if(Application::Get('lang_dir') == 'rtl') $output .= '<link href="modules/ratings/css/ratings-rtl.css" rel="stylesheet" type="text/css" />';
				$ratings_lang = (file_exists('modules/ratings/langs/'.Application::Get('lang').'.js')) ? Application::Get('lang') : 'en';
				$output .= '<script src="modules/ratings/langs/'.$ratings_lang.'.js" type="text/javascript"></script>';
				$output .= '<script src="modules/ratings/js/ratings.js" type="text/javascript"></script>';
			}
			
			$output .= '<div class="listing_description">';
			$output .= '<div class="wide_block">'.$nl;

				if($bpf_rating_button) $output .= '<div class="ratings_stars" id="rt_listing_'.$this->GetField('id').'"></div>'.$nl;
				if($bpf_business_name) $output .= '<h2>'.$this->GetField('business_name').'</h2><br />'.$nl;
				
				$output .= '<ul class="l_items">'.$nl;
				if($bpf_address && $this->GetField('business_address') != '') $output .= '<li><span class="l_item">'._ADDRESS.':</span> <span class="l_description">'.$this->GetField('business_address').'</span></li>'.$nl;
				if($bpf_phone && $this->GetField('business_phone') != '') $output .= '<li><span class="l_item">'._PHONE.':</span> <span class="l_description">'.$this->GetField('business_phone').'</span></li>'.$nl;	
				if($bpf_phone && $this->GetField('business_fax') != '') $output .= '<li><span class="l_item">'._FAX.':</span> <span class="l_description">'.$this->GetField('business_fax').'</span></li>'.$nl;
				
				if($this->GetField('website_url') != '') $output .= '<li><span class="l_item">'._WEB_SITE.':</span> <span class="l_description"><a href="'.$this->GetField('website_url').'" target="_blank">'.$this->GetField('website_url').'</a> <img src="images/external_link.gif" alt="" /></span></li>'.$nl;
				if($this->GetField('business_email') != '') $output .= '<li><span class="l_item">'._EMAIL_ADDRESS.':</span> <span class="l_description"><a href="mailto:'.$this->GetField('business_email').'" target="_blank">'.$this->GetField('business_email').'</a></span></li>'.$nl;
				if($bpf_video_link && $this->GetField('video_url') != '') $output .= '<li><span class="l_item">'._VIDEO.':</span> <span class="l_description"><a href="'.$this->GetField('video_url').'" target="_blank">'.$this->GetField('video_url').'</a> <img src="images/external_link.gif" alt="" /></span></li>'.$nl;
				
				$output .= '<li><span class="l_item">'._LOCATION.':</span> <span class="l_description">'.$this->GetField('listing_location').'</span></li>'.$nl;
				$output .= '<li><span class="l_item">'._SUB_LOCATION.':</span> <span class="l_description">'.$this->GetField('listing_sub_location').'</span></li>'.$nl;
				if($this->GetField('date_published') != '0000-00-00 00:00:00') $output .= '<li><span class="l_item">'._PUBLISHED.':</span> <span class="l_description">'.format_datetime($this->GetField('date_published'), get_datetime_format(false), _UNKNOWN).'</span></li>'.$nl;
				if($bpf_business_description) $output .= '<li><span class="l_item">'._DESCRIPTION.':</span> <br>'.$this->GetField('business_description').'</li>'.$nl;
				
				$added_categories = ListingsCategories::GetCategoriesForListing($this->GetField('id'));
				$arr_added_categories = array();
				$output .= '<li><span class="l_item">'._CATEGORIES.':</span><br>';
				$categories = '';
				foreach($added_categories[0] as $key => $val){
					$categories .= (!empty($categories)) ? ', ' : '';
					$categories .= prepare_link('category', 'cid', $val['category_id'], '', $val['name'], '', '');
				}
				$output .= $categories;
				$output .= '</li>';
				$output .= '</ul>';
				
			$output .= '</div>';
			$output .= '<div class="narrow_block">';
					
				$output .= '<div class="listing_images_wrapper">';
				if($bpf_logo){
					$image_file = ($this->GetField('image_file') != '') ? $this->GetField('image_file') : '';
					$image_file_thumb = ($this->GetField('image_file_thumb') != '') ? $this->GetField('image_file_thumb') : 'no_image.png';
					if(!empty($image_file)) $output .= '<a href="images/listings/'.$image_file.'" rel="lyteshow'.$this->GetField('id').'">';
					$output .= '<img class="listing_image'.(($image_file == '') ? ' no_hover' : '').'" src="images/listings/'.$image_file_thumb.'" alt="" />';				
					if(!empty($image_file)) $output .= '</a>';
					$output .= '<br />';					
				}
				$additional_images = array();
				for($i = 1; $i <= $bpf_images_count; $i++){
					$additional_image = ($this->GetField('image_'.$i) != '') ? $this->GetField('image_'.$i) : '';
					$additional_image_thumb = ($this->GetField('image_'.$i.'_thumb') != '') ? $this->GetField('image_'.$i.'_thumb') : '';
					if($additional_image != ''){
						$output .= '<a href="images/listings/'.$additional_image.'" rel="lyteshow'.$this->GetField('id').'">';
						$output .= '<img class="listing_icon" src="images/listings/'.$additional_image_thumb.'" alt="" />';				
						$output .= '</a>';
					}
				}
				$output .= '</div>';				

				$map_code = $this->GetField('map_code', false);
				if($bpf_map && $map_code != ''){
					$map_code = preg_replace('/width="(.*?)"/', 'width="240px"', $map_code);
					$map_code = preg_replace('/height="(.*?)"/', 'height="200px"', $map_code);
					$output .= '<div class="map">'.$map_code.'</div><br /><br />';
				}

				if(Modules::IsModuleInstalled('inquiries') == 'yes' && $bpf_inquiry_button){
					$output .= '<form name="frmDirectInquiry" action="index.php?page=inquiry_form" method="post">';
					$output .= draw_token_field(false);					
					$output .= draw_hidden_field('listing_id', $this->listing_info[0]['id'], false);				
					$output .= draw_hidden_field('visitor_locations', $this->listing_info[0]['listing_location_id'], false);
					$output .= draw_hidden_field('visitor_sub_locations', $this->listing_info[0]['listing_sub_location_id'], false);
					$output .= draw_hidden_field('business_name', $this->GetField('business_name'), false);
					$output .= draw_hidden_field('inquiry_type', '1', false);					
					$output .= '<center><input type="submit" class="form_button" value="'._SUBMIT_INQUIRY.'"></center>';
					$output .= '</form><br /><br />';
				}

			
			$output .= '</div>';
			$output .= '<div style="clear:both;"></div>';
			$output .= '</div>';

			
		}else{
			$output .= draw_important_message(_NO_LISTINGS_TO_DISPLAY, false);			
		}
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Get field
	 */
	public function GetField($param = '', $decode = true)
	{
		$output = '';
		if(isset($this->listing_info[0][$param])){
			$output = ($decode) ? decode_text($this->listing_info[0][$param]) : $this->listing_info[0][$param];
		}
		return $output;
	}

	/**
	 * Draws listings in category
	 * 		@param $category_id
	 * 		@param $draw
	 */
	public function DrawListings($category_id, $draw = true)
	{
		global $objLogin, $objSettings;

		$lang = Application::Get('lang');
		$nl = "\n";
		
		if(empty($lang)) $lang = Languages::GetDefaultLang();
		
		$listings_locations     = isset($_REQUEST['listings_locations']) ? prepare_input($_REQUEST['listings_locations']) : '';
		$listings_sub_locations = isset($_REQUEST['listings_sub_locations']) ? prepare_input($_REQUEST['listings_sub_locations']) : '';
		$listings_sort_by       = (isset($_REQUEST['listings_sort_by']) && $_REQUEST['listings_sort_by'] != '') ? prepare_input($_REQUEST['listings_sort_by']) : 'rating';
		$listings_order_by      = (isset($_REQUEST['listings_order_by']) && $_REQUEST['listings_order_by'] != '') ? prepare_input($_REQUEST['listings_order_by']) : 'ASC';
		$sort_by = '';
		$order_by = '';
		$output  = ''; 
		
		if($listings_sort_by == 'name'){
			$sort_by = 'ld.business_name';
			$order_by = $listings_order_by;
		}else if($listings_sort_by == 'date'){	
			$sort_by = 'l.date_published';
			$order_by = $listings_order_by;
		}else if($listings_sort_by == 'rating'){
			// rating according to advertising plans high rate = high advertising plan
			$sort_by = 'l.advertise_plan_id';
			$order_by = (($listings_order_by == 'ASC') ? 'DESC' : 'ASC').', RAND()';
		}else{
			$sort_by = 'l.priority_order';
			$order_by = $listings_order_by;
		}
				

		if(!Application::Get('js_included', 'lytebox')){
			$output .= '<!-- LyteBox v3.22 Author: Markus F. Hay Website: http://www.dolem.com/lytebox -->'.$nl;
			$output .= '<link rel="stylesheet" href="modules/lytebox/css/lytebox.css" type="text/css" media="screen" />'.$nl;
			$output .= '<script type="text/javascript" src="modules/lytebox/js/lytebox.js"></script>'.$nl;
		}

		// draw category description
		$category_info = Categories::GetCategoryInfo($category_id);
		if($category_info['description'] != '') $output .= draw_message($category_info['description'], false);
		
		// draw result		
		$sql_from = TABLE_LISTINGS.' l 
					INNER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON l.id = ld.listing_id
					INNER JOIN '.TABLE_LISTINGS_LOCATIONS.' ll ON l.listing_location_id = ll.id
				WHERE
					'.(!empty($listings_locations) ? 'l.listing_location_id = \''.$listings_locations.'\' AND ' : '').'
					'.(!empty($listings_sub_locations) ? 'l.listing_sub_location_id = \''.$listings_sub_locations.'\' AND ' : '').'
					'.((!$objLogin->IsLoggedIn()) ? 'l.access_level=\'public\' AND ' : '').'
					l.is_published = 1 AND
					'.(($this->show_expired_listings != 'yes') ? ' ((l.finish_publishing = \'0000-00-00 00:00:00\') OR (l.finish_publishing > \''.date('Y-m-d H:i:s').'\')) AND ' : '').'
					ld.language_id = \''.$lang.'\'
					'.(($category_id != '') ? ' AND l.id IN (SELECT listing_id FROM '.TABLE_LISTINGS_CATEGORIES.' lc WHERE category_id = '.(int)$category_id.')' : '').'
				ORDER BY '.$sort_by.' '.$order_by;

		// pagination prepare
		$page_size = ModulesSettings::Get('listings', 'listings_per_page');
		$start_row = '0';
		$total_pages = '1';
		pagination_prepare($page_size, $sql_from, $start_row, $total_pages);		

		$sql = 'SELECT l.id,
					l.image_file,
					l.image_file_thumb,
					l.priority_order,
					l.date_published,
					l.website_url,
					l.business_email,
					l.advertise_plan_id,
					ll.name as listing_location_name,
					ld.language_id,					
					ld.business_name,
					ld.business_address,
					ld.business_description
				FROM '.$sql_from.'
				LIMIT '.$start_row.', '.$page_size;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);		

		if($result[1] > 0 || !empty($listings_locations)){			
			$output .= '<form id="frmCategoryView" action="index.php?page=category&cid='.$category_id.'" method="post">';
			$output .= draw_token_field(false);
            $output .= draw_hidden_field('p', '1', false);
			$output .= '<table width="98%" border="0" align="center">';
			$output .= '<tr><th colspan="3" nowrap="nowrap" height="5px"></th></tr>';
			$output .= '<tr><th colspan="2" align="'.Application::Get('defined_left').'" valign="middle">';
				$output .= '&nbsp;'._FILTER_BY.': ';
				$output .= ListingsLocations::DrawAllLocations(array('tag_name'=>'listings_locations', 'selected_value'=>$listings_locations, 'javascript_event'=>'onchange="jQuery(\'#frmCategoryView\').submit();"'), false).' &nbsp;';
				$output .= ListingsSubLocations::DrawAllSubLocations($listings_locations, array('tag_name'=>'listings_sub_locations', 'selected_value'=>$listings_sub_locations, 'javascript_event'=>'onchange="jQuery(\'#frmCategoryView\').submit();"'), false);			
			$output .= '</th>';
			$output .= '<th colspan="2" align="'.Application::Get('defined_right').'" valign="middle">';
			$output .= _SORT_BY.': 
					<select name="listings_sort_by" onchange="jQuery(\'#frmCategoryView\').submit();">
						<option value="rating" '.(($listings_sort_by == 'rating') ? ' selected="selected"' : '').'>'._RATING.'</option>
						<option value="name" '.(($listings_sort_by == 'name') ? ' selected="selected"' : '').'>'._NAME.'</option>
						<option value="date" '.(($listings_sort_by == 'date') ? ' selected="selected"' : '').'>'._DATE_PUBLISHED.'</option>
					</select>&nbsp;
					<select name="listings_order_by" onchange="jQuery(\'#frmCategoryView\').submit();">
						<option value="ASC" '.(($listings_order_by == 'ASC') ? ' selected="selected"' : '').'>'._ASCENDING.'</option>
						<option value="DESC" '.(($listings_order_by == 'DESC') ? ' selected="selected"' : '').'>'._DESCENDING.'</option>
					</select>
					</th>
				</tr>
			</table>
			</form>';
		}
		
		if($result[1] > 0){
			$output .= '<table width="99%" border="0" align="center">';
			$output .= '<tr><th colspan="2" nowrap="nowrap" height="5px"></th></tr>
				<tr>
					<th align="'.Application::Get('defined_left').'">&nbsp; '._LISTINGS.' &nbsp;</th>
					<th align="center">'._IMAGE.'</th>
				</tr>';			

			for($i=0; $i < $result[1]; $i++){
				$image_file       = ($result[0][$i]['image_file'] != '') ? $result[0][$i]['image_file'] : 'no_image.png';
				///$result[0][$i]['advertise_plan_id'] > 1 && 
				$image_file_thumb = ($result[0][$i]['image_file_thumb'] != '') ? $result[0][$i]['image_file_thumb'] : 'no_image.png';
				$output .= '<tr><td colspan="2" style="padding:7px;">'.draw_line('no_margin_line', IMAGE_DIRECTORY, false).'</td></tr>
					<tr valign="top">
						<td>';	
							$link_1 = prepare_link('listing', 'lid', $result[0][$i]['id'], '', $result[0][$i]['business_name'], '', _CLICK_TO_SEE_DESCR);
							$link_2 = prepare_link('listing', 'lid', $result[0][$i]['id'], '', _MORE_INFO, '', _CLICK_TO_SEE_DESCR);
							$output .= '<div class="listing_info">';
							$output .= '<div class="header">'.$link_1.'</div>';
							$output .= '<div class="address">'.substr_by_word(strip_tags($result[0][$i]['business_address']), 300, true, Application::Get('lang')).' '.$result[0][$i]['listing_location_name'].'</div>';
							$output .= '<div class="description">'.substr_by_word(strip_tags($result[0][$i]['business_description']), 180, true, Application::Get('lang')).'</div>';
							$output .= '<div class="links">
											'.$link_2.'
											'.(($result[0][$i]['website_url'] != '') ? ' : <a href="'.$result[0][$i]['website_url'].'" target="_new">'._WEBSITE_URL.'</a>' : '').'
											'.(($result[0][$i]['business_email'] != '') ? ' : <a href="mailto:'.$result[0][$i]['business_email'].'">'._EMAIL.'</a>' : '').'
											'.(($result[0][$i]['date_published'] != '0000-00-00 00:00:00') ? '<div class="published">'._PUBLISHED.': '.format_datetime($result[0][$i]['date_published'], get_datetime_format(false), _UNKNOWN).'<div>' : '').'
										</div>';
							$output .= '</div>';
							$output .= '
						</td>
						<td width="130px" align="center">
							<div class="listing_icon">';
								if($image_file != 'no_image.png') $output .= '<a href="images/listings/'.$image_file.'" rel="lyteshow_'.$result[0][$i]['id'].'">';
								$output .= '<img class="listings_image'.(($image_file == 'no_image.png') ? ' no_hover' : '').'" src="images/listings/'.$image_file_thumb.'" width="120px" height="90px" title="'.(($image_file != 'no_image.png') ? _CLICK_TO_INCREASE : '').'" alt="" />';
								if($image_file != 'no_image.png') $output .= '</a>';						
								$output .= '
							</div>
						</td>
					</tr>';			
			}
			// draw pagination links
			if($total_pages > 1) $output .= '<tr><td colspan="2" style="padding:7px;">'.draw_line('no_margin_line', IMAGE_DIRECTORY, false).'</td></tr>';
			$output .= '<tr><td colspan="2">';
			$output .= pagination_get_links($total_pages, '');
			$output .= '</td></tr>'; 
			$output .= '<tr><td colspan="2">&nbsp;</td></tr>';
			$output .= '</table>';
		}else{			
			// draw message only if this is a last-level empty category 
			$categories = Categories::GetAllActive('c.parent_id = '.(int)$category_id);
			if(!$categories[1]) $output .= draw_message(_NO_LISTINGS_FOUND, false, true);			
		}		
		
		if($draw) echo $output;
		else return $output;
	}

	
	////////////////////////////////////////////////////////////////////
	// BEFORE/AFTER METHODS
	///////////////////////////////////////////////////////////////////
	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		// check for required fields		
		foreach($this->arrTranslations as $key => $val){			
			if($val['business_name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'business_name_'.$key;
				return false;
			}else if(strlen($val['business_name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'business_name_'.$key;
				return false;
			}else if(strlen($val['business_address']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._ADDRESS.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 255, $this->error);
				$this->errorField = 'business_address_'.$key;
				return false;
			}else if(strlen($val['business_description']) > 1024){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 1024, $this->error);
				$this->errorField = 'business_description_'.$key;
				return false;
			}			
		}		
		return true;		
	}
	
	/**
	 * Before-Insertion
	 */
	public function BeforeInsertRecord()
	{
		$keywords = MicroGrid::GetParameter('keywords', false);
		
		if(!$this->ValidateTranslationFields()) return false;
		if(!$this->ValidateKeywordNumber($keywords, $this->bpf_keywords_count)) return false;

		return true;
	}

	/**
	 * After-Insertion - add listing descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		global $objLogin;
		
		$advertise_plan_id = MicroGrid::GetParameter('advertise_plan_id', false);

		// update amount of listings/advertise plans for customer (after listing was added)
		if($objLogin->IsLoggedInAsCustomer()){
			$objLogin->RemoveListing($advertise_plan_id);
		}
		
		// update finish publishing date
		$advertise_plan_info = AdvertisePlans::GetPlanInfo($advertise_plan_id);
		$finish_publishing = '0000-00-00 00:00:00';
		if($advertise_plan_info[1] > 0){
			$duration = $advertise_plan_info[0]['duration'];
			$finish_publishing = ($duration == '-1') ? '0000-00-00 00:00:00' : date('Y-m-d H:i:s', strtotime('+'.(int)$duration.' day'));
			$sql = 'UPDATE '.TABLE_LISTINGS.'
					SET	finish_publishing = \''.$finish_publishing.'\'
					WHERE id = '.(int)$this->lastInsertId;						
			if(!database_void_query($sql)){ /* echo 'error!'; */ }
		}
		
		// update listing descriptions
		$sql = 'INSERT INTO '.TABLE_LISTINGS_DESCRIPTION.'(id, listing_id, language_id, business_name, business_address, business_description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){			
			if($count > 0) $sql .= ',';
			$sql .= '(NULL,
					'.$this->lastInsertId.',
					\''.$key.'\',
					\''.encode_text(prepare_input($val['business_name'])).'\',
					\''.encode_text(prepare_input($val['business_address'])).'\',
					\''.encode_text(prepare_input($val['business_description'])).'\'
				)';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}	

	/**
	 *	Before-Update operations
	 */
	public function BeforeUpdateRecord()
	{
		$listing_id = MicroGrid::GetParameter('rid');
		$keywords = MicroGrid::GetParameter('keywords', false);
		
		if(!$this->ValidateTranslationFields()) return false;
		if(!$this->ValidateKeywordNumber($keywords, $this->bpf_keywords_count)) return false;

		$sql = 'SELECT is_published, access_level, advertise_plan_id, is_approved FROM '.TABLE_LISTINGS.' WHERE id = '.(int)$listing_id;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->is_published = (bool)$result[0]['is_published'];
			$this->accessLevel = $result[0]['access_level'];
			$this->isApproved = (int)$result[0]['is_approved'];
			$this->advertisePlanID = (int)$result[0]['advertise_plan_id'];
		}
		
		$sql = 'SELECT * FROM '.TABLE_LISTINGS_DESCRIPTION.' WHERE listing_id = '.(int)$listing_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$this->listing_info = $result;
		}
		return true;
	}

	/**
	 * After-Updating - update listing descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		global $objLogin, $objSettings;
		
		// update translations
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_LISTINGS_DESCRIPTION.'
					SET
						business_name = \''.encode_text(prepare_input($val['business_name'])).'\',
						business_address = \''.encode_text(prepare_input($val['business_address'])).'\',
						business_description = \''.encode_text(prepare_input($val['business_description'])).'\'
					WHERE listing_id = '.(int)$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}

		// retrieve pre-moderation settings
		if($objLogin->IsLoggedInAsCustomer() && ModulesSettings::Get('listings', 'pre_moderation_allow') == 'yes'){
			// check if we have to put listing on moderation
			$sql = 'SELECT * FROM '.TABLE_LISTINGS_DESCRIPTION.' WHERE listing_id = '.(int)$this->curRecordId;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			$langs_count = count($this->arrTranslations);
			for($i=0; $i < $langs_count; $i++){
				$result_diff = array_diff_assoc($this->listing_info[0][$i], $result[0][$i]);
				if(count($result_diff) > 0){
					$sql = 'UPDATE '.TABLE_LISTINGS.' SET is_published = 0 WHERE id = '.(int)$this->curRecordId;
					if(!database_void_query($sql)){ /* echo 'error!'; */ }
					$this->error = _UPDATED_FOR_MODERATION;
					return false;
				}				
			}
		}
		
		$access_level = MicroGrid::GetParameter('access_level', false);
		$advertise_plan_id = MicroGrid::GetParameter('advertise_plan_id', false);
		
		// update listings count and date of publishing
		if($objLogin->IsLoggedInAsAdmin()){
			$customer_id = (int)MicroGrid::GetParameter('customer_id', false);
			$is_published = (bool)MicroGrid::GetParameter('is_published', false);
			$is_published_value = '';
			$recalculate_listings = false;
			if(!$this->is_published && $is_published){
				$is_published_value = date('Y-m-d H:i:s');
				$recalculate_listings = true;
			}else if($this->is_published && !$is_published){
				$is_published_value = '0000-00-00 00:00:00';
				$recalculate_listings = true;
			}else if($this->accessLevel == 'registered' && $access_level == 'public'){
				$recalculate_listings = true;
			}else if($this->accessLevel == 'public' && $access_level == 'registered'){
				$recalculate_listings = true;
			}

			if($recalculate_listings){
				// update listings count in categories
				Categories::RecalculateListingsCount();
			}
            
			// update finish publishing date
			$advertise_plan_info = AdvertisePlans::GetPlanInfo($advertise_plan_id);
			if($this->advertisePlanID != $advertise_plan_id && $advertise_plan_info[1] > 0){
				$duration = $advertise_plan_info[0]['duration'];
				$finish_publishing = ($duration == '-1') ? '0000-00-00 00:00:00' : date('Y-m-d H:i:s', strtotime('+'.(int)$duration.' day'));
				$sql = 'UPDATE '.TABLE_LISTINGS.'
						SET	finish_publishing = \''.$finish_publishing.'\'
						WHERE id = '.(int)$this->curRecordId;						
				if(!database_void_query($sql)){ /* echo 'error!'; */ }
			}

			if($is_published_value != ''){
				$sql = 'UPDATE '.TABLE_LISTINGS.'
				        SET						    
							date_published = \''.$is_published_value.'\'
							'.(($this->isApproved == '0' && $is_published_value != '0000-00-00 00:00:00') ? ', is_approved=1' : '').'
						WHERE id = '.(int)$this->curRecordId;						
				if(!database_void_query($sql)){ /* echo 'error!'; */ }
				if($this->isApproved == '0' && $is_published_value != '0000-00-00 00:00:00'){
					////////////////////////////////////////////////////////////
					$customer_info = Customers::GetCustomerInfo($customer_id);
					$email = isset($customer_info['email']) ? $customer_info['email'] : '';
					$last_name = isset($customer_info['last_name']) ? $customer_info['last_name'] : '';
					$first_name = isset($customer_info['first_name']) ? $customer_info['first_name'] : '';
					$preferred_language = isset($customer_info['preferred_language']) ? $customer_info['preferred_language'] : '';
					$business_name = MicroGrid::GetParameter('business_name_'.$preferred_language, false);
					
					$sender = $objSettings->GetParameter('admin_email');
					$recipiant = $email;
					
					$listing_details  = _NAME.': '.$business_name.' <br>';
					$listing_details .= _LINK.': '.APPHP_BASE.'index.php?page=listing&lid='.(int)$this->curRecordId.' <br>';

					send_email(
						$recipiant,
						$sender,
						'listing_approved_by_admin',
						array(
							'{FIRST NAME}' => $first_name,
							'{LAST NAME}'  => $last_name,
							'{LISTING DETAILS}' => $listing_details,
							'{WEB SITE}' => $_SERVER['SERVER_NAME']
						),
						$preferred_language
					);
					////////////////////////////////////////////////////////////					
				}
			}
		}
	}	

	/**
	 *	Before-Delete operations
	 */
	public function BeforeDeleteRecord()
	{
		$listing_id = MicroGrid::GetParameter('rid');

		$sql = 'SELECT access_level, advertise_plan_id, is_approved FROM '.TABLE_LISTINGS.' WHERE id = '.(int)$listing_id;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$this->accessLevel = $result[0]['access_level'];
			$this->advertisePlanID = $result[0]['advertise_plan_id'];
			$this->isApproved = $result[0]['is_approved'];
		}
		return true;
	}

	/**
	 * After-Deleting - delete listing descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		global $objLogin;
		
		// update amount of listings/advertise plans for customer (if a listing was not approved yet)
		if($objLogin->IsLoggedInAsCustomer() && !$this->isApproved){
			$objLogin->AddListing($this->advertisePlanID);
		}

		$sql = 'DELETE FROM '.TABLE_LISTINGS_DESCRIPTION.' WHERE listing_id = '.(int)$this->curRecordId;
		if(database_void_query($sql)){

			// delete all records from relation table 'listings_categories' 
			$sql = 'DELETE FROM '.TABLE_LISTINGS_CATEGORIES.' WHERE listing_id = '.(int)$this->curRecordId;
			database_void_query($sql);
			
			// update listings count in categories
			Categories::RecalculateListingsCount();
			
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Before Edit mode
	 */
	public function BeforeEditRecord()
	{
		// prepare array for sub-location
		$listing_location_id = MicroGrid::GetParameter('listing_location_id', false);
		if(empty($listing_location_id)) $listing_location_id = (int)$this->result[0][0]['listing_location_id'];
		$total_sub_locations = ListingsSubLocations::GetAllSubLocations($listing_location_id, 'name ASC');
		$arr_sub_locations = array();
		foreach($total_sub_locations[0] as $key => $val){
			$arr_sub_locations[$val['id']] = $val['name'];
		}
		$this->arrEditModeFields['separator_general']['listing_sub_location_id'] = array('title'=>_SUB_LOCATION, 'type'=>'enum', 'width'=>'', 'source'=>$arr_sub_locations, 'required'=>true);
	}

	/**
	 * Before Edit mode
	 */
	public function BeforeDetailsRecord()
	{
		// prepare array for sub-location
		$listing_location_id = (int)$this->result[0][0]['listing_location_id'];
		$total_sub_locations = ListingsSubLocations::GetAllSubLocations($listing_location_id, 'name ASC');
		$arr_sub_locations = array();
		foreach($total_sub_locations[0] as $key => $val){
			$arr_sub_locations[$val['id']] = $val['name'];
		}
		$this->arrDetailsModeFields['separator_general']['listing_sub_location_id'] = array('title'=>_SUB_LOCATION,	 'type'=>'enum', 'width'=>'', 'source'=>$arr_sub_locations, 'required'=>true);
	}

	
	////////////////////////////////////////////////////////////////////
	// STATIC METHODS
	///////////////////////////////////////////////////////////////////
	/**
	 * Draws featured side block with listing links
	 * 		@param $draw
	 */
	public static function DrawFeaturedBlock($draw = false)
	{
		global $objLogin;
		$show_expired_listings = ModulesSettings::Get('listings', 'show_expired_listings');				
		$output = draw_block_top(_FEATURED_LISTINGS, '', 'maximized', false);
		$listings_name_length = ModulesSettings::Get('listings', 'listings_name_length');
		$max_listings = 5;
		
		$where_condition  = 'is_featured = 1'.((!$objLogin->IsLoggedIn()) ? ' AND access_level=\'public\'' : '');
		$where_condition .= (($show_expired_listings != 'yes') ? ' AND ((finish_publishing = \'0000-00-00 00:00:00\') OR (finish_publishing > \''.date('Y-m-d H:i:s').'\'))' : '');
		
		$result = self::GetAllListings($where_condition, 'RAND() ASC', $max_listings+1);
		if($result[1] > 0){
			$output .= '<ul>';
			for($i=0; $i < $result[1] && ($i < $max_listings); $i++){
				$output .= '<li>'.prepare_link('listing', 'lid', $result[0][$i]['id'], $result[0][$i]['business_name'], substr_by_word($result[0][$i]['business_name'], $listings_name_length, true, Application::Get('lang')), '', $result[0][$i]['business_name']).'</li>';
			}
			if($result[1] > $max_listings) $output .= '<li>'.prepare_link('listings', 'type', 'featured', 'all', _MORE.' &raquo;', '', _MORE).'</li>';	
			$output .= '</ul>';			
		}else{
			$output .= _NO_LISTINGS_TO_DISPLAY;
		}
		$output .= draw_block_bottom(false);		

		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Draws featured all links
	 * 		@param $draw
	 */
	public static function DrawFeaturedAll($draw = true)
	{
		global $objLogin;
		$show_expired_listings = ModulesSettings::Get('listings', 'show_expired_listings');		
		
		echo '<table border="0" cellspacing="5">';
		echo '<tr><th></th><td colspan="3">'.draw_sub_title_bar(_FEATURED_LISTINGS, false).'</td></tr>';

		$where_condition  = 'is_featured = 1'.((!$objLogin->IsLoggedIn()) ? ' AND access_level=\'public\'' : '');
		$where_condition .= (($show_expired_listings != 'yes') ? ' AND ((finish_publishing = \'0000-00-00 00:00:00\') OR (finish_publishing > \''.date('Y-m-d H:i:s').'\'))' : '');
		
		$result = self::GetAllListings($where_condition, 'RAND() ASC', 100);
		if($result[1] > 0){
			echo '<tr>
					<th width="20px" ></td>
					<th>'._NAME.'</th>
					<th width="200px" align="center">'._CATEGORY.'</th>
					<th width="200px" align="center">'._DATE_PUBLISHED.'</th>
			    </tr>';
			for($i=0; $i < $result[1] && ($i < 100); $i++){
				echo '<tr>
						<td align="right">'.($i+1).'.</td>
						<td nowrap="nowrap">'.prepare_link('listing', 'lid', $result[0][$i]['id'], $result[0][$i]['business_name'], $result[0][$i]['business_name'], '').'</td>
						<td align="center">'.$result[0][$i]['category_name'].'</td>												
						<td align="center">'.format_datetime($result[0][$i]['date_published']).'</td>						
				</tr>';
			}
			echo '<tr><td colspan="4">&nbsp;</td></tr>';
		}else{
			echo '<tr><td colspan="4">'._NO_LISTINGS_TO_DISPLAY.'</td></tr>';
		}		
		echo '</table>';		
	}

	/**
	 * Draw recent listings side block with listings links
	 * 		@param $draw
	 */
	public static function DrawRecentBlock($draw = true)
	{
		global $objLogin;
		$show_expired_listings = ModulesSettings::Get('listings', 'show_expired_listings');		
		$output = draw_block_top(_RECENT_LISTINGS, '', 'maximized', false);
		$listings_name_length = ModulesSettings::Get('listings', 'listings_name_length');
		$max_listings = 5;

		$where_condition  = ' 1=1 '.((!$objLogin->IsLoggedIn()) ? ' AND access_level="public"' : '');
		$where_condition .= (($show_expired_listings != 'yes') ? ' AND ((finish_publishing = \'0000-00-00 00:00:00\') OR (finish_publishing > \''.date("Y-m-d H:i:s").'\'))' : '');

		$result = self::GetAllListings($where_condition, 'date_published DESC', $max_listings+1);
		if($result[1] > 0){
			$output .= '<ul>';
			for($i=0; $i < $result[1] && ($i < $max_listings); $i++){
				$output .= '<li>'.prepare_link('listing', 'lid', $result[0][$i]['id'], $result[0][$i]['business_name'], substr_by_word($result[0][$i]['business_name'], $listings_name_length, true, Application::Get('lang')), '', $result[0][$i]['business_name']).'</li>';
			}
			if($result[1] > $max_listings){
				$output .= '<li>'.prepare_link('listings', 'type', 'recent', 'all', _MORE.' &raquo;', '', _MORE).'</li>';	
			}
			$output .= '</ul>';			
		}else{
			$output .= _NO_LISTINGS_TO_DISPLAY;
		}		
		$output .= draw_block_bottom(false);		

		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Draw featured all links
	 * 		@param $draw
	 */
	public static function DrawRecentAll($draw = true)
	{
		global $objLogin;
		$show_expired_listings = ModulesSettings::Get('listings', 'show_expired_listings');		

		echo '<table border="0" cellspacing="5">';
		echo '<tr><th></th><td colspan="3">'.draw_sub_title_bar(_RECENT_LISTINGS, false).'</td></tr>';		

		$where_condition  = ' 1=1 '.((!$objLogin->IsLoggedIn()) ? ' AND access_level="public"' : '');
		$where_condition .= (($show_expired_listings != 'yes') ? ' AND ((finish_publishing = \'0000-00-00 00:00:00\') OR (finish_publishing > \''.date('Y-m-d H:i:s').'\'))' : '');
		
		$result = self::GetAllListings($where_condition, 'date_published DESC', 100);
		if($result[1] > 0){
			echo '<tr>
					<th width="20px"></td>
					<th>'._NAME.'</th>
					<th width="200px" align="center">'._CATEGORY.'</th>
					<th width="200px" align="center">'._DATE_PUBLISHED.'</th>
			</tr>';
			for($i=0; $i < $result[1] && ($i < 100); $i++){
				echo '<tr>
						<td align="right">'.($i+1).'.</td>
						<td nowrap="nowrap">'.prepare_link('listing', 'lid', $result[0][$i]['id'], $result[0][$i]['business_name'], $result[0][$i]['business_name'], '').'</td>
						<td align="center">'.$result[0][$i]['category_name'].'</td>						
						<td align="center">'.format_datetime($result[0][$i]['date_published']).'</td>						
				</tr>';
			}
			echo '<tr><td colspan="4">&nbsp;</td></tr>';			
		}else{
			echo '<tr><td colspan="4">'._NO_LISTINGS_TO_DISPLAY.'</td></tr>';
		}		
		echo '</table>';		
	}

	/**
	 * Draw directory statistics side block 
	 * 		@param $draw
	 */
	public static function DrawDirectoryStatistics($draw = true)
	{		
		$where_condition = ' AND ((finish_publishing = \'0000-00-00 00:00:00\') OR (finish_publishing > \''.date('Y-m-d H:i:s').'\'))';
		
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LISTINGS.' WHERE is_published = 1'.$where_condition;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$listings_total = isset($result['cnt']) ? (int)$result['cnt'] : '0';

		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LISTINGS.' WHERE is_published = 1 AND TIMESTAMPDIFF(HOUR, date_published, \''.date('Y-m-d H:i:s').'\') < 24';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$listings_last_total = isset($result['cnt']) ? (int)$result['cnt'] : '0';

		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LISTINGS.' WHERE is_published = 0';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$listings_pending = isset($result['cnt']) ? (int)$result['cnt'] : '0';

		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CATEGORIES;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$categories_total = isset($result['cnt']) ? (int)$result['cnt'] : '0';
		
		$output  = draw_block_top(_DIRECTORY_STATISTICS, '', 'maximized', false);
		$output .= '<ul>';
		$output .= '<li>'._LISTINGS.': '.$listings_total.'</li>';
		$output .= '<li>'._PENDING.': '.$listings_pending.'</li>';
		$output .= '<li>'._NEW_SUBMISSION_IN_24H.': '.$listings_last_total.'</li>';
		$output .= '<li>'._CATEGORIES.': '.$categories_total.'</li>';
		$output .= '</ul>';
		$output .= draw_block_bottom(false);
		
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 *	Get number of listings awaiting moderation
	 */
	public static function AwaitingModerationCount()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_LISTINGS.' WHERE is_published = 0';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return $result[0]['cnt'];
		}
		return '0';
	}
	
	/**
	 *	Checks if the page with listing may be cached
	 *		@param $listing_id
	 */
	public static function CacheAllowed($param = array())
	{
		$listing_id = isset($param['id']) ? $param['id'] : '';
		$listing_type = isset($param['type']) ? $param['type'] : '';
		
		if($listing_id != ''){
			$result = self::GetAllListings('l.id = '.(int)$listing_id);
			if($result[1] > 0){			
				return true;		
			}			
		}else if($listing_type == 'featured' || $listing_type == 'recent'){
			return true;		
		}
		
		return false;	
	}
	
	/**
	 * Close expired listings
	 */
	static public function UpdateStatus()
	{
		$sql = 'UPDATE '.TABLE_LISTINGS.'
				SET is_published = 0 
				WHERE is_published = 1 AND
				      finish_publishing != \'0000-00-00 00:00:00\' AND 
					  finish_publishing < \''.date('Y-m-d H:i:s').'\'';
		$result = database_void_query($sql, false, false);
		if($result){			
			Categories::RecalculateListingsCount();
		}
		return $result;
	}


	/**
	 * Returns customer info by listing id
	 * 		@param $where_clause
	 */
	public static function GetCustomerInfoByListing($where_clause = '')
	{
		$sql = 'SELECT
					l.id,
					c.id as customer_id,
					c.first_name,
					c.last_name,
					c.email,
					(SELECT COUNT(*) FROM '.TABLE_INQUIRIES_HISTORY.' ih WHERE ih.customer_id = l.customer_id AND DATEDIFF(\''.date('Y-m-d H:i:s').'\', ih.date_added) < 31) as inquiries_sent,
					ap.inquiries_count as inquiries_allowed
				FROM '.TABLE_LISTINGS.' l
					INNER JOIN '.TABLE_CUSTOMERS.' c ON l.customer_id = c.id
					INNER JOIN '.TABLE_ADVERTISE_PLANS.' ap ON l.advertise_plan_id = ap.id
				WHERE
					1 = 1
					'.(!empty($where_clause) ? ' AND '.$where_clause : '');					
					
		return database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	}
	
	/**
	 * Validates a number of keywords
	 * 		@param $keywords
	 * 		@param $max_keywords
	*/
	private function ValidateKeywordNumber($keywords, $max_keywords = 0)
	{
		if($max_keywords && count(explode(',',$keywords)) > $max_keywords){
			$this->error = str_ireplace('_MAX_', '<b>'.$max_keywords.'</b>', _LISTING_MAX_KEYWORDS_ALERT);
			return false;
		}
		return true;
	}
	
	//==========================================================================
    // MicroGrid Triggers
	//==========================================================================	
	/**
	 * Trigger method - allows to work with View Mode items
	 */
	protected function OnItemCreated_ViewMode($field_name, &$field_value)
	{
		if($field_name == 'customer_username' && $field_value == 'Admin'){
			$field_value = _ADMIN;
		}
	}
	
	
}
?>