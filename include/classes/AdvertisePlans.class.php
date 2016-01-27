<?php

/**
 *	Class AdvertisePlans
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 23.01.2013
 *  Usage       : Business Directory ONLY
 *
 *	PUBLIC:				  	STATIC:				 	 	PRIVATE:
 * 	------------------	  	---------------     	 	---------------
 *	__construct             GetDefaultPlanInfo  	    ValidateTranslationFields
 *	__destruct              GetAllPlans
 *	BeforeInsertRecord      DrawPlans
 *	AfterInsertRecord       GetPlanInfo
 *	BeforeUpdateRecord      DrawPrepayment
 *	AfterUpdateRecord       ReDrawPrepayment
 *  AfterDeleteRecord       DoOrder
 *	                        PlaceOrder
 *	
 *  1.0.0
 *      - 
 *      - 
 *      -
 *      -
 *      -
 *      
 **/


class AdvertisePlans extends MicroGrid {
	
	protected $debug = false;
	
	// 001
	private $arrTranslations = '';
	private $currency_format = '';
	public static $message = '';

	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objLogin;
		
		$this->params = array();		
		if(isset($_POST['price'])) $this->params['price'] = prepare_input($_POST['price']);
		if(isset($_POST['listings_count'])) $this->params['listings_count'] = prepare_input($_POST['listings_count']);
		if(isset($_POST['categories_count'])) $this->params['categories_count'] = prepare_input($_POST['categories_count']);
		if(isset($_POST['keywords_count'])) $this->params['keywords_count'] = prepare_input($_POST['keywords_count']);
		if(isset($_POST['inquiries_count'])) $this->params['inquiries_count'] = prepare_input($_POST['inquiries_count']);
		if(isset($_POST['images_count'])) $this->params['images_count'] = prepare_input($_POST['images_count']);

		if(isset($_POST['duration'])) $this->params['duration'] = prepare_input($_POST['duration']);
		// for checkboxes 
		if(isset($_POST['is_default'])) $this->params['is_default'] = (int)$_POST['is_default']; else $this->params['is_default'] = '0';
		if(isset($_POST['business_name'])) $this->params['business_name'] = (int)$_POST['business_name']; else $this->params['business_name'] = '0';
		if(isset($_POST['business_description'])) $this->params['business_description'] = (int)$_POST['business_description']; else $this->params['business_description'] = '0';
		if(isset($_POST['logo'])) $this->params['logo'] = (int)$_POST['logo']; else $this->params['logo'] = '0';
		if(isset($_POST['phone'])) $this->params['phone'] = (int)$_POST['phone']; else $this->params['phone'] = '0';
		if(isset($_POST['address'])) $this->params['address'] = (int)$_POST['address']; else $this->params['address'] = '0';
		if(isset($_POST['map'])) $this->params['map'] = (int)$_POST['map']; else $this->params['map'] = '0';
		if(isset($_POST['inquiry_button'])) $this->params['inquiry_button'] = (int)$_POST['inquiry_button']; else $this->params['inquiry_button'] = '0';
		if(isset($_POST['rating_button'])) $this->params['rating_button'] = (int)$_POST['rating_button']; else $this->params['rating_button'] = '0';
		if(isset($_POST['video_link'])) $this->params['video_link'] = (int)$_POST['video_link']; else $this->params['video_link'] = '0';		
		

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ADVERTISE_PLANS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_payments_advertise_plans';
		$this->actions      = array('add'=>false, 'edit'=>true, 'details'=>true, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= $objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
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
		
		$inquiry_field_visible = (Modules::IsModuleInstalled('inquiries') == 'yes') ? true : false;
		$ratings_field_visible = (Modules::IsModuleInstalled('ratings') == 'yes') ? true : false;

		///$this->isAggregateAllowed = false;
		///// define aggregate fields for View Mode
		///$this->arrAggregateFields = array(
		///	'field1' => array('function'=>'SUM'),
		///	'field2' => array('function'=>'AVG'),
		///);

		///$date_format = get_date_format('view');
		///$date_format_edit = get_date_format('edit');
		///$datetime_format = get_datetime_format();
		$this->currency_format = get_currency_format();
		$pre_currency_symbol = ((Application::Get('currency_symbol_place') == 'left') ? Application::Get('currency_symbol') : '');
		$post_currency_symbol = ((Application::Get('currency_symbol_place') == 'right') ? Application::Get('currency_symbol') : '');

		$arr_durations = self::PrepareDurationsArray();
		$arr_inquiries = array('0'=>'0', '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '14'=>'15', '20'=>'20', '30'=>'30', '40'=>'40', '50'=>'50', '75'=>'75', '100'=>'100', '150'=>'150', '200'=>'200', '250'=>'250', '500'=>'500', '750'=>'750', '1000'=>'1000', '-1'=>_UNLIMITED);
		$arr_images = array('0'=>'0', '1'=>'1', '2'=>'2', '3'=>'3');

		$max_categories = (int)ModulesSettings::Get('listings', 'maximum_categories');
		$arr_categories = array();
		for($i=0; $i<$max_categories; $i++){
			$arr_categories[$i+1] = $i+1;
		}

		///////////////////////////////////////////////////////////////////////////////
		// 002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// 003. prepare translations array for add/edit/detail modes
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_ADVERTISE_PLANS_DESCRIPTION,
			'advertise_plan_id',
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									ap.'.$this->primaryKey.',
									ap.listings_count,
									ap.categories_count,
									ap.business_name,
									ap.business_description,
									ap.price,
									ap.duration,
									IF(is_default, "<span class=yes>'._YES.'</span>", "<span>'._NO.'</span>") as is_default,
									apd.name
								FROM '.$this->tableName.' ap
									LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' apd ON ap.id = apd.advertise_plan_id AND apd.language_id = \''.$this->languageId.'\'';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  	     => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'listings_count' => array('title'=>_LISTINGS_COUNT, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>''),
			'categories_count' => array('title'=>_CATEGORIES_COUNT, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>''),
			'duration'       => array('title'=>_DURATION, 'type'=>'enum', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$arr_durations),
			'price' 	     => array('title'=>_PRICE, 'type'=>'label', 'align'=>'right', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$this->currency_format.'|2', 'pre_html'=>$pre_currency_symbol, 'post_html'=>$post_currency_symbol),
			'is_default'     => array('title'=>_DEFAULT, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'height'=>'', 'maxlength'=>''),
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
		$this->arrAddModeFields = array();

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		// - for editable passwords they must be defined directly in SQL : '.$this->tableName.'.user_password,
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
									'.$this->primaryKey.',									
									listings_count,
									categories_count,
									keywords_count,
									inquiries_count,
									inquiry_button,
									rating_button,
									price,
									'.$sql_translation_description.'
									duration,
									is_default,
									business_name,
									business_description,
									logo,
									images_count,
									video_link,
									phone,
									address,
									map,
									IF(business_name, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_business_name,
									IF(business_description, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_business_description,
									IF(logo, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_logo,
									IF(phone, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_phone,
									IF(video_link, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_video_link,
									IF(address, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_address,
									IF(map, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_map,
									IF(inquiry_button, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_inquiry_button,
									IF(rating_button, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_rating_button,
									IF(is_default, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as mod_is_default
								FROM '.$this->tableName.' 
								WHERE '.$this->primaryKey.' = _RID_';			

		$rid = MicroGrid::GetParameter('rid');
		$sql = 'SELECT is_default FROM '.TABLE_ADVERTISE_PLANS.' WHERE id = '.(int)$rid;
		$readonly = false;
		if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$readonly = (isset($result['is_default']) && $result['is_default'] == '1') ? true : false;
		}
							
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_general'  =>array(
				'separator_info'   => array('legend'=>_GENERAL_INFO, 'columns'=>'2'),
				'price'            => array('title'=>_PRICE, 'type'=>'textbox',  'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'unique'=>false, 'visible'=>true, 'pre_html'=>$pre_currency_symbol.' ', 'post_html'=>$post_currency_symbol),
				'business_name'  	   => array('title'=>_NAME, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'duration'         => array('title'=>_DURATION, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_durations, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
				'business_description' => array('title'=>_DESCRIPTION, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'categories_count' => array('title'=>_CATEGORIES_COUNT, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_categories, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
				'phone'            => array('title'=>_PHONE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'listings_count'   => array('title'=>_LISTINGS_COUNT, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'4', 'default'=>'1', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'9999', 'unique'=>false, 'visible'=>true),
				'address'          => array('title'=>_ADDRESS, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'inquiry_button'   => array('title'=>_INQUIRY_BUTTON, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>$inquiry_field_visible),
				'map'              => array('title'=>_MAP, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'inquiries_count'  => array('title'=>_INQUIRIES_COUNT, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_inquiries, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'post_html'=>' '._PER_MONTH, 'visible'=>$inquiry_field_visible),
				'rating_button'    => array('title'=>_RATING_BUTTON, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'visible'=>$ratings_field_visible),
				'logo'             => array('title'=>_LOGO, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'keywords_count'   => array('title'=>_KEYWORDS_COUNT, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'0', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'99', 'unique'=>false, 'visible'=>true),
				'images_count'     => array('title'=>_IMAGES, 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_images, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>''),
				'video_link'       => array('title'=>_VIDEO, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
				'is_default'       => array('title'=>_DEFAULT, 'type'=>'checkbox', 'readonly'=>$readonly, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
			)
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_general'  =>array(
				'separator_info'   => array('legend'=>_GENERAL_INFO, 'columns'=>'2'),
				'price'            => array('title'=>_PRICE, 'type'=>'label', 'pre_html'=>$pre_currency_symbol.' ', 'post_html'=>$post_currency_symbol),
				'mod_business_name'  	   => array('title'=>_NAME, 'type'=>'label'),
				'duration'         => array('title'=>_DURATION, 'type'=>'enum', 'source'=>$arr_durations),
				'mod_business_description' => array('title'=>_DESCRIPTION, 'type'=>'label'),
				'categories_count' => array('title'=>_CATEGORIES_COUNT, 'type'=>'enum', 'source'=>$arr_categories),
				'mod_phone'        => array('title'=>_PHONE, 'type'=>'label'),
				'listings_count'   => array('title'=>_LISTINGS_COUNT, 'type'=>'label'),
				'mod_address'      => array('title'=>_ADDRESS, 'type'=>'label'),
				'mod_inquiry_button'  	   => array('title'=>_INQUIRY_BUTTON, 'type'=>'label', 'visible'=>$inquiry_field_visible),
				'mod_map'          => array('title'=>_MAP, 'type'=>'label'),
				'inquiries_count'  => array('title'=>_INQUIRIES_COUNT, 'type'=>'enum', 'source'=>$arr_inquiries, 'post_html'=>' '._PER_MONTH, 'visible'=>$inquiry_field_visible),
				'mod_rating_button'  	   => array('title'=>_RATING_BUTTON, 'type'=>'label'),
				'mod_logo'         => array('title'=>_LOGO, 'type'=>'label'),
				'keywords_count'   => array('title'=>_KEYWORDS_COUNT, 'type'=>'label'),
				'images_count'     => array('title'=>_IMAGES, 'type'=>'label'),
				'mod_video_link'   => array('title'=>_VIDEO, 'type'=>'label'),
				'mod_is_default'   => array('title'=>_DEFAULT, 'type'=>'label'),
			)
		);

		///////////////////////////////////////////////////////////////////////////////
		// 004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
			array(
				'name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
				'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'validation_maxlength'=>'512', 'readonly'=>false)
			)
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


	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
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
			if($val['name'] == ''){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;
			}else if(strlen($val['name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}else if(strlen($val['description']) > 512){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 512, $this->error);
				$this->errorField = 'description_'.$key;
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
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		// set all other plans to be a not default plans
		$is_default = MicroGrid::GetParameter('is_default', false);
		if($is_default == '1'){
			$sql = 'UPDATE '.TABLE_ADVERTISE_PLANS.' SET is_default = \'0\' WHERE id != '.(int)$this->lastInsertId;
			database_void_query($sql);
		}

		$sql = 'INSERT INTO '.TABLE_ADVERTISE_PLANS_DESCRIPTION.'(id, advertise_plan_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			//echo mysql_error();			
			return false;
		}
	}	

	/**
	 * Before-Updating operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Updating - update album item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		$is_default = MicroGrid::GetParameter('is_default', false);
		if($is_default == '1'){
			$sql = 'UPDATE '.TABLE_ADVERTISE_PLANS.' SET is_default = \'0\' WHERE id != '.(int)$this->curRecordId;
			database_void_query($sql);
		}

		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_ADVERTISE_PLANS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE advertise_plan_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
			//echo mysql_error();
		}
	}	
	
	/**
	 * After-Deleting - delete album altem descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' WHERE advertise_plan_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Returns info for default plan
	 */
	public static function GetDefaultPlanInfo()
	{
		$sql = 'SELECT *
		        FROM '.TABLE_ADVERTISE_PLANS.'
				WHERE is_default = 1';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] == 1){
			return $result[0];
		}		
		return false;
	}
	
	/**
	 * Returns all active plans
	 */
	public static function GetAllPlans()
	{
		$sql = 'SELECT
					ap.*,
					apd.name as plan_name,
					apd.description as plan_description
				FROM '.TABLE_ADVERTISE_PLANS.' ap
					LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' apd ON ap.id = apd.advertise_plan_id AND apd.language_id = \''.Application::Get('lang').'\'
				ORDER BY ap.id ASC';
					
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;		
	}

	/**
	 * Returns plan info
	 * 		@param $plan_id
	 */
	public static function GetPlanInfo($plan_id = 0)
	{
		$sql = 'SELECT
					ap.*,
					apd.name as plan_name,
					apd.description as plan_description
				FROM '.TABLE_ADVERTISE_PLANS.' ap
					LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' apd ON ap.id = apd.advertise_plan_id AND apd.language_id = \''.Application::Get('lang').'\'
				WHERE ap.id = '.(int)$plan_id;
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		return $result;		
	}

	/**
	 * Draws all active plans
	 * 		@param $draw
	 */
	public static function DrawPlans($draw = true)
	{
		$output = '';
		$arr_durations = self::PrepareDurationsArray();

		$default_payment_system = isset($_GET['payment_type']) ? $_GET['payment_type'] : ModulesSettings::Get('payments', 'default_payment_system');
		$payment_type_online    = ModulesSettings::Get('payments', 'payment_method_online');
		$payment_type_paypal    = ModulesSettings::Get('payments', 'payment_method_paypal');
		$payment_type_2co       = ModulesSettings::Get('payments', 'payment_method_2co');
		$payment_type_authorize = ModulesSettings::Get('payments', 'payment_method_authorize');
		$payment_type_cnt	    = ($payment_type_online === 'yes')+($payment_type_paypal === 'yes')+($payment_type_2co === 'yes')+($payment_type_authorize === 'yes');
		$exclude_free_plans     = true; //($default_payment_system != 'online') ? true : false;		

		$inquiry_field_visible = (Modules::IsModuleInstalled('inquiries') == 'yes') ? true : false;
		$ratings_field_visible = (Modules::IsModuleInstalled('ratings') == 'yes') ? true : false;
		
		$sql = 'SELECT
					ap.*,
					apd.name,
					apd.description
				FROM '.TABLE_ADVERTISE_PLANS.' ap
					LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' apd ON ap.id = apd.advertise_plan_id AND apd.language_id = \''.Application::Get('lang').'\'
				WHERE 1=1
				ORDER BY ap.id ASC';
				//'.(($exclude_free_plans) ? ' AND ap.price != 0' : '' ).'
					
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			$output .= '<form name="frmAdvertiseForm" id="frmAdvertiseForm" action="index.php?customer=advertise_prepayment" method="post">';
			$output .= draw_hidden_field('task', 'do_order', false);
			$output .= draw_token_field(false);
			
			$output .= '<div class="advertise_plans_container">';
			
			$output .= '<table style="margin:7px 16px 0px 16px">';
			$output .= '<tr><td>'._CURRENCY.':</td><td>'.Currencies::GetCurrenciesDDL(false).'</td></tr>';			

			if($payment_type_cnt >= 1){
				///onchange="appGoToPage(\'index.php?customer=advertise\',\'&payment_type=\'+this.value)"
				$output .= '<tr><td>'._PAYMENT_TYPE.': </td><td>
				<select name="payment_type" id="payment_type">';
					if($payment_type_online == 'yes') $output .= '<option value="online" '.(($default_payment_system == 'online') ? 'selected="selected"' : '').'>'._ONLINE_ORDER.'</option>';	
					if($payment_type_paypal == 'yes') $output .= '<option value="paypal" '.(($default_payment_system == 'paypal') ? 'selected="selected"' : '').'>'._PAYPAL.'</option>';	
					if($payment_type_2co == 'yes') $output .= '<option value="2co" '.(($default_payment_system == '2co') ? 'selected="selected"' : '').'>2CO</option>';	
					if($payment_type_authorize == 'yes') $output .= '<option value="authorize" '.(($default_payment_system == 'authorize') ? 'selected="selected"' : '').'>Authorize.Net</option>';	
				$output .= '</select></td></tr>';
			}else{
				$output .= '<tr><td colspan="2">';
				$output .= draw_important_message(_NO_PAYMENT_METHODS_ALERT, false);
				$output .= '</td></tr>';
			}
			$output .= '</table>';

			$active_ind = '-1';
			$output .= '<div class="plans_wrapper">';
			$output .= '<h2>'._SELECT_PLAN.'</h2>';
			for($i=0; $i<$result[1]; $i++){				
				if($result[0][$i]['price'] != 0 && $active_ind == '-1') $active_ind = $i;				
				$duration = isset($arr_durations[$result[0][$i]['duration']]) ? $arr_durations[$result[0][$i]['duration']] : '';
				$no_text = '<span class=no>'._NO.'</span>';
				$yes_text = '<span class=yes>'._YES.'</span>';
				$output .= '
				<div class="item '.(($i == $active_ind) ? 'active' : (($result[0][$i]['price'] == 0) ? 'free' : '')).'" id="item_'.$i.'">
					<h3>'.$result[0][$i]['name'].'</h3>
					<div class="item_text" title="'._CLICK_TO_SELECT.'">
						<label for="plan_'.$result[0][$i]['id'].'">
						'._DURATION.': <b>'.$duration.'</b><br />
						'._LISTINGS.': <b>'.$result[0][$i]['listings_count'].'</b><br />
						'._CATEGORIES.': <b>'.$result[0][$i]['categories_count'].'</b><br />
						'._KEYWORDS.': <b>'.$result[0][$i]['keywords_count'].'</b><br />
						'._NAME.': <b>'.(($result[0][$i]['business_name']) ? $yes_text : $no_text).'</b><br />
						'._DESCRIPTION.': <b>'.(($result[0][$i]['business_description']) ? $yes_text : $no_text).'</b><br />
						'._LOGO.': <b>'.(($result[0][$i]['logo']) ? $yes_text : $no_text).'</b><br />
						'._IMAGES.': <b>'.$result[0][$i]['images_count'].'</b><br />
						'._VIDEO.': <b>'.(($result[0][$i]['video_link']) ? $yes_text : $no_text).'</b><br />
						'._PHONE.': <b>'.(($result[0][$i]['phone']) ? $yes_text : $no_text).'</b><br />
						'._ADDRESS.': <b>'.(($result[0][$i]['address']) ? $yes_text : $no_text).'</b><br />
						'._MAP.': <b>'.(($result[0][$i]['map']) ? $yes_text : $no_text).'</b><br />
						'.(($inquiry_field_visible) ? _INQUIRY_BUTTON.': <b>'.(($result[0][$i]['inquiry_button']) ? $yes_text : $no_text).'</b><br />' : '').'
						'.(($inquiry_field_visible) ? _INQUIRIES.'/'._MONTH.': <b>'.(($result[0][$i]['inquiries_count'] == '-1') ? '<span title="'._UNLIMITED.'">&infin;</span>' : $result[0][$i]['inquiries_count']).'</b><br />' : '').'
						'.(($ratings_field_visible) ? _RATING.': <b>'.(($result[0][$i]['rating_button']) ? $yes_text : $no_text).'</b><br />' : '').'
						'._PRICE.': <b>'.Currencies::PriceFormat($result[0][$i]['price'] * Application::Get('currency_rate')).'</b><br />
						<div class="item_description">'.$result[0][$i]['description'].'</div>
						</label>						
					</div>
					<div class="item_radio">';
					if($result[0][$i]['price'] != 0){
						$output .= '<input '.(($i==$active_ind) ? 'checked="checked"' : '').' type="radio" name="plan_id" id="plan_'.$result[0][$i]['id'].'" value="'.$result[0][$i]['id'].'" onclick="appSelectBlock(\''.$i.'\');">';
					}
					$output .= '</div>
				</div>';
			}
			$output .= '</div>';			

			if($payment_type_cnt >= 1) $output .= '<div class="plan_button"><input type="submit" class="form_button" name="btnSubmit" value="'._SUBMIT.'" /></div>';			
			$output .= '</div>';			
			$output .= '</form><br /><br />';
		}else{
			$output .= _NO_RECORDS_FOUND;
		}
		
		if($draw) echo $output;
		else $output;
	}
	

	/**
	 * Draw prepayment info
	 * 		@param $draw
	 */
	public static function ReDrawPrepayment($draw = true)
	{
		global $objLogin;
		
		// get order number
		$sql = 'SELECT id, advertise_plan_id, currency, payment_type, order_number FROM '.TABLE_ORDERS.' WHERE customer_id = '.(int)$objLogin->GetLoggedID().' AND status = 0 ORDER BY id DESC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			self::DrawPrepayment($result[0]['advertise_plan_id'], 'online', Application::Get('currency'));			
		}else{
			draw_important_message(_WRONG_PARAMETER_PASSED);
		}
	}		
		

	/**
	 * Draw prepayment info
	 * 		@param $draw
	 */
	public static function DrawPrepayment($plan_id = '', $payment_type = '', $currency = '', $draw = true)
	{		
		global $objSettings, $objLogin;
		
		$plan_id = (empty($plan_id)) ? MicroGrid::GetParameter('plan_id', false) : $plan_id;
		$payment_type = (empty($payment_type)) ? MicroGrid::GetParameter('payment_type', false) : $payment_type;
		$currency = (empty($currency)) ? MicroGrid::GetParameter('currency', false) : $currency;
		$output = '';

		// retrieve module parameters
		$paypal_email        = ModulesSettings::Get('payments', 'paypal_email');
		$collect_credit_card = ModulesSettings::Get('payments', 'online_collect_credit_card');
		$two_checkout_vendor = ModulesSettings::Get('payments', 'two_checkout_vendor');
		$authorize_login_id  = ModulesSettings::Get('payments', 'authorize_login_id');
		$authorize_transaction_key = ModulesSettings::Get('payments', 'authorize_transaction_key');
		$mode                = ModulesSettings::Get('payments', 'mode');
		$vat_value           = ModulesSettings::Get('payments', 'vat_value');

		// retrieve credit card info
		$cc_type = isset($_REQUEST['cc_type']) ? prepare_input($_REQUEST['cc_type']) : '';
		$cc_holder_name  = isset($_POST['cc_holder_name']) ? prepare_input($_POST['cc_holder_name']) : '';
		$cc_number = isset($_POST['cc_number']) ? prepare_input($_POST['cc_number']) : "";
		$cc_expires_month = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : "1";
		$cc_expires_year = isset($_POST['cc_expires_year']) ? prepare_input($_POST['cc_expires_year']) : date("Y");
		$cc_cvv_code = isset($_POST['cc_cvv_code']) ? prepare_input($_POST['cc_cvv_code']) : "";

		// prepare datetime format
		$field_date_format = get_datetime_format();
		$currency_format = get_currency_format();		  
		$arr_durations = self::PrepareDurationsArray();

		// prepare clients info 
		$sql='SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$objLogin->GetLoggedID();
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$client_info = array();
		$client_info['first_name'] = isset($result[0]['first_name']) ? $result[0]['first_name'] : '';
		$client_info['last_name'] = isset($result[0]['last_name']) ? $result[0]['last_name'] : '';
		$client_info['address1'] = isset($result[0]['b_address']) ? $result[0]['b_address'] : '';
		$client_info['address2'] = isset($result[0]['b_address2']) ? $result[0]['b_address2'] : '';
		$client_info['city'] = isset($result[0]['b_city']) ? $result[0]['b_city'] : '';
		$client_info['state'] = isset($result[0]['b_state']) ? $result[0]['b_state'] : '';
		$client_info['zip'] = isset($result[0]['b_zipcode']) ? $result[0]['b_zipcode'] : '';
		$client_info['country'] = isset($result[0]['b_country']) ? $result[0]['b_country'] : '';
		$client_info['email'] = isset($result[0]['email']) ? $result[0]['email'] : '';
		$client_info['company'] = isset($result[0]['company']) ? $result[0]['company'] : '';
		$client_info['phone'] = isset($result[0]['phone']) ? $result[0]['phone'] : '';
		$client_info['fax'] = isset($result[0]['fax']) ? $result[0]['fax'] : '';

		if($cc_holder_name == ''){
			if($objLogin->IsLoggedIn()){
				$cc_holder_name = $objLogin->GetLoggedFirstName().' '.$objLogin->GetLoggedLastName();
			}else{
				$cc_holder_name = $client_info['first_name'].' '.$client_info['last_name'];
			}
		}		
		
		// get order number
		$sql = 'SELECT id, order_number FROM '.TABLE_ORDERS.' WHERE customer_id = '.(int)$objLogin->GetLoggedID().' AND status = 0 ORDER BY id DESC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){					
			$order_number = $result[0]['order_number'];
		}else{
			$order_number = strtoupper(get_random_string(10));
		}

		$additional_info = '';

		$cart_total_wo_vat = 0;
		$vat_cost = 0;
		$cart_total = 0;
		
		$sql = 'SELECT
					ap.id,
					ap.listings_count,
					ap.price,
					ap.duration,
					ap.is_default,
					apd.name,
					apd.description
				FROM '.TABLE_ADVERTISE_PLANS.' ap
					LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' apd ON ap.id = apd.advertise_plan_id AND apd.language_id = \''.Application::Get('lang').'\'
				WHERE ap.id = '.(int)$plan_id;
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

		$fisrt_part = '<table border="0" width="97%" align="center">
			<tr><td colspan="3"><h4>'._ORDER_DESCRIPTION.'</h4></td></tr>
			<tr><td width="20%">'._ORDER_DATE.' </td><td width="2%"> : </td><td> '.format_datetime(date('Y-m-d H:i:s'), $field_date_format).'</td></tr>';
			if($result[1] > 0){
				if($result[0]['price'] == 0){
					$payment_type = 'online';
					$collect_credit_card = 'no';
				}
				$cart_total_wo_vat = ($result[0]['price'] * Application::Get('currency_rate'));
				$vat_cost = ($cart_total_wo_vat * ($vat_value / 100));
				$cart_total = $cart_total_wo_vat + $vat_cost;
				
				$duration = isset($arr_durations[$result[0]['duration']]) ? $arr_durations[$result[0]['duration']] : '';
				
				$fisrt_part .= '<tr><td>'._ADVERTISE_PLAN.' </td><td width="2%"> : </td><td> '.$result[0]['name'].'</td></tr>';
				$fisrt_part .= '<tr><td>'._DURATION.' </td><td width="2%"> : </td><td> '.$duration.'</td></tr>';
				$fisrt_part .= '<tr><td>'._LISTINGS.' </td><td width="2%"> : </td><td> '.$result[0]['listings_count'].'</td></tr>';
				$fisrt_part .= '<tr><td>'._PRICE.' </td><td width="2%"> : </td><td> '.Currencies::PriceFormat($cart_total_wo_vat).'</td></tr>';
				$fisrt_part .= '<tr><td>'._DESCRIPTION.' </td><td width="2%"> : </td><td> '.$result[0]['description'].'</td></tr>';
			}			
	
		$pp_params = array(
			'api_login'       => '',
			'transaction_key' => '',
			'order_number'    => $order_number,			
			
			'address1'      => $client_info['address1'],
			'address2'      => $client_info['address2'],
			'city'          => $client_info['city'],
			'zip'           => $client_info['zip'],
			'country'       => $client_info['country'],
			'state'         => $client_info['state'],
			'first_name'    => $client_info['first_name'],
			'last_name'     => $client_info['last_name'],
			'email'         => $client_info['email'],
			'company'       => $client_info['company'],
			'phone'         => $client_info['phone'],
			'fax'           => $client_info['fax'],
			
			'notify'        => '',
			'return'        => 'index.php?page=payment_return',
			'cancel_return' => 'index.php?page=payment_cancel',
						
			'paypal_form_type'   	   => '',
			'paypal_form_fields' 	   => '',
			'paypal_form_fields_count' => '',
			
			'collect_credit_card' => $collect_credit_card,
			'cc_type'             => '',
			'cc_holder_name'      => '',
			'cc_number'           => '',
			'cc_cvv_code'         => '',
			'cc_expires_month'    => '',
			'cc_expires_year'     => '',
			
			'currency_code'      => Application::Get('currency_code'),
			'additional_info'    => $additional_info,
			'discount_value'     => '',
			'extras_param'       => '',
			'extras_sub_total'   => '',
			'vat_cost'           => $vat_cost,
			'cart_total'         => number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', ','),
			'is_prepayment'      => false,
			'pre_payment_type'   => '',
			'pre_payment_value'  => 0,
			
		);
			
		$fisrt_part .= '
			<tr><td colspan="3" nowrap="nowrap" height="10px"></td></tr>
			<tr><td colspan="3"><h4>'._TOTAL.'</h4></td></tr>
			<tr><td>'._SUBTOTAL.' </td><td> : </td><td> '.Currencies::PriceFormat($cart_total_wo_vat, '', '', $currency_format).'</td></tr>';
			$fisrt_part .= '<tr><td>'._VAT.' ('.$vat_value.'%) </td><td> : </td><td> '.Currencies::PriceFormat($vat_cost, '', '', $currency_format).'</td></tr>';
			$fisrt_part .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($cart_total, '', '', $currency_format).'</b></td></tr>';
			$fisrt_part .= '<tr><td colspan="3" nowrap="nowrap" height="0px"></td></tr>';
			$fisrt_part .= '<tr><td colspan="3">';
			//if($additional_info != ''){
			//	$fisrt_part .= '<tr><td colspan="3" nowrap height="10px"></td></tr>';
			//	$fisrt_part .= '<tr><td colspan="3"><h4>'._ADDITIONAL_INFO.'</h4>'.$additional_info.'</td></tr>';							
			//}
		
		
		$second_part = '
			</td></tr>
		</table><br />';


		if($payment_type == 'online'){

			$output .= $fisrt_part;
				$pp_params['credit_card_required'] = $collect_credit_card;
				$pp_params['cc_type']             = $cc_type;
				$pp_params['cc_holder_name']      = $cc_holder_name;
				$pp_params['cc_number']           = $cc_number;
				$pp_params['cc_cvv_code']         = $cc_cvv_code;
				$pp_params['cc_expires_month']    = $cc_expires_month;
				$pp_params['cc_expires_year']     = $cc_expires_year;
				$output .= PaymentIPN::DrawPaymentForm('online', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
			$output .= $second_part;			
	
		}else if($payment_type == 'paypal'){							
		
			$output .= $fisrt_part;
				$pp_params['api_login']                = $paypal_email;
				$pp_params['notify']        		   = 'index.php?page=payment_notify_paypal';
				$pp_params['paypal_form_type']   	   = 'single';
				$pp_params['paypal_form_fields'] 	   = '';
				$pp_params['paypal_form_fields_count'] = '';						
				$output .= PaymentIPN::DrawPaymentForm('paypal', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
			$output .= $second_part;		
		
		}else if($payment_type == '2co'){				

			$output .= $fisrt_part;
				$pp_params['api_login'] = $two_checkout_vendor;			
				$pp_params['notify']    = 'index.php?page=payment_notify_2co';
				$output .= PaymentIPN::DrawPaymentForm('2co', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
			$output .= $second_part;

		}else if($payment_type == 'authorize'){

			$output .=$fisrt_part;
				$pp_params['api_login'] 	  = $authorize_login_id;
				$pp_params['transaction_key'] = $authorize_transaction_key;
				$pp_params['notify']    	  = 'index.php?page=payment_notify_autorize_net';
				// authorize.net accepts only USD, so we need to convert the sum into USD
				$pp_params['cart_total']      = number_format((($pp_params['cart_total'] * Application::Get('currency_rate'))), '2', '.', ',');												
				$output .= PaymentIPN::DrawPaymentForm('authorize.net', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
			$output .= $second_part;
		}
	
		if($draw) echo $output;
		else $output;
	}
	
	/**
	 * Do (prepare) order
	 * 		@param $payment_type
	 */
	public static function DoOrder($payment_type = '')
	{
		//global $objSettings;
		global $objLogin;
	
        if(SITE_MODE == 'demo'){
           self::$message = draw_important_message(_OPERATION_BLOCKED, false);
		   return false;
        }

		// check if customer has reached the maximum number of allowed 'open' orders
		$max_orders = ModulesSettings::Get('payments', 'maximum_allowed_orders');
		$sql = 'SELECT COUNT(*) as cnt
				FROM '.TABLE_ORDERS.'
				WHERE customer_id = '.(int)$objLogin->GetLoggedID().' AND
				     (status = 0 OR status = 1)';				
		$result = database_query($sql, DATA_ONLY);
		$cnt = isset($result[0]['cnt']) ? (int)$result[0]['cnt'] : 0;
		if($cnt >= $max_orders){
			self::$message = _MAX_ORDERS_ERROR;
			return false;
		}		

		$return = false;
		$currency = MicroGrid::GetParameter('currency', false);
		$plan_id = MicroGrid::GetParameter('plan_id', false);
		$payment_type = MicroGrid::GetParameter('payment_type', false);		
		$additionalInfo = '';
		$payed_by = 0;
		$listings_amount = 0;
		$order_price = 0;
		$vat_percent = ModulesSettings::Get('payments', 'vat_value');
		$vat_cost = 0;
		$total_price = 0;

		// add order to database
		if(in_array($payment_type, array('online', 'paypal', '2co', 'authorize'))){			
			if($payment_type == 'paypal'){
				$payed_by = '1';
				$status = '0';									
			}else if($payment_type == '2co'){
				$payed_by = '2';
				$status = '0';
			}else if($payment_type == 'authorize'){
				$payed_by = '3';
				$status = '0';				
			}else{
				$payed_by = '0';
				$status = '0';
			}
			
			$sql = 'SELECT ap.id, ap.listings_count, ap.price, ap.duration												
					FROM '.TABLE_ADVERTISE_PLANS.' ap
					WHERE ap.id = '.(int)$plan_id;						
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

			if($result[1] > 0){
				$listings_amount = $result[0]['listings_count'];
				$order_price = ($result[0]['price'] * Application::Get('currency_rate'));
				$vat_cost = ($order_price * ($vat_percent / 100));
				$total_price = $order_price + $vat_cost;
				
				/////////////////////////////////////////////////////////////////
				$sql = 'SELECT id, order_number FROM '.TABLE_ORDERS.' WHERE customer_id = '.(int)$objLogin->GetLoggedID().' AND status = 0 ORDER BY id DESC';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){					
					$sql_start = 'UPDATE '.TABLE_ORDERS.' SET ';
					$order_number = $result[0]['order_number'];
					$sql_end = ' WHERE order_number = \''.$order_number.'\'';
				}else{					
					$sql_start = 'INSERT INTO '.TABLE_ORDERS.' SET ';
					$order_number = strtoupper(get_random_string(10));
					$sql_end = '';
				}
				
				$sql_middle = 'order_number = \''.$order_number.'\',
							order_description = \''._LISTINGS_PURCHASING.'\',
							order_price = '.number_format((float)$order_price, (int)Application::Get('currency_decimals'), '.', '').',
							vat_percent = '.$vat_percent.',
							vat_fee = '.number_format((float)$vat_cost, (int)Application::Get('currency_decimals'), '.', '').',
							total_price = '.number_format((float)$total_price, (int)Application::Get('currency_decimals'), '.', '').',
							currency = \''.$currency.'\',
							advertise_plan_id = '.$plan_id.',
							listings_amount = '.(int)$listings_amount.',
							customer_id = '.(int)@$objLogin->GetLoggedID().',
							transaction_number = \'\',
							created_date = \''.date('Y-m-d H:i:s').'\',
							payment_date = \'0000-00-00 00:00:00\',
							payment_type = '.$payed_by.',
							payment_method = 0,
							coupon_number = \'\',
							discount_campaign_id = 0,
							additional_info = \''.$additionalInfo.'\',
							cc_type = \'\',
							cc_holder_name = \'\',
							cc_number = \'\', 
							cc_expires_month = \'\', 
							cc_expires_year = \'\', 
							cc_cvv_code = \'\',
							status = '.(int)$status.',
							status_changed = \'0000-00-00 00:00:00\',
							email_sent = 0';
							
				$sql = $sql_start.$sql_middle.$sql_end;
	
				if(database_void_query($sql)){					
					$return = true;
				}else{
					self::$message = _ORDER_PEPARING_ERROR;
					$return = false;
				}
			}else{
				self::$message = _ORDER_PEPARING_ERROR;
				$return = false;
			}
		}else{
			self::$message = _ORDER_PEPARING_ERROR;
			$return = false;
		}
		
		if(SITE_MODE == 'development' && !empty(self::$message)) self::$message .= '<br>'.$sql.'<br>'.mysql_error();		

		return $return;
	}

	/**
	 * Place order
	 * 		@param $order_number
	 * 		@param $cc_params
	 */
	public static function PlaceOrder($order_number, $cc_params = array())
	{
		global $objLogin;
		
        if(SITE_MODE == 'demo'){
           self::$message = draw_important_message(_OPERATION_BLOCKED, false);
		   return false;
        }
		
		$sql='SELECT id, order_number
			  FROM '.TABLE_ORDERS.'
			  WHERE
			        order_number = \''.$order_number.'\' AND
					customer_id = '.(int)$objLogin->GetLoggedID().' AND
			        status = 0
			  ORDER BY id DESC';				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$sql = 'UPDATE '.TABLE_ORDERS.'
					SET
						created_date = \''.date('Y-m-d H:i:s').'\',
						status_changed = \''.date('Y-m-d H:i:s').'\',
						cc_type = \''.$cc_params['cc_type'].'\',
						cc_holder_name = \''.$cc_params['cc_holder_name'].'\',
						cc_number = AES_ENCRYPT(\''.$cc_params['cc_number'].'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
						cc_expires_month = \''.$cc_params['cc_expires_month'].'\',
						cc_expires_year = \''.$cc_params['cc_expires_year'].'\',
						cc_cvv_code = \''.$cc_params['cc_cvv_code'].'\',
						status = \'1\'
					WHERE order_number = \''.$order_number.'\'';
			database_void_query($sql);
			if(Orders::SendOrderEmail($order_number, 'accepted', $objLogin->GetLoggedID())){
			    // OK	
			}else{
				//$this->message = draw_success_message(_ORDER_SEND_MAIL_ERROR, false);					
			}			
			return true;
		}else{
			self::$message = _ORDER_ERROR;
			return false;			
		}
		
	}
	
	/**
	 * Prepare array of ducrations;
	 */
	private static function PrepareDurationsArray()
	{
		$array = array('1'=>'1 '._DAY, '2'=>'2 '._DAYS, '3'=>'3 '._DAYS, '4'=>'4 '._DAYS, '5'=>'5 '._DAYS, '6'=>'6 '._DAYS, '7'=>'7 '._DAYS, '8'=>'8 '._DAYS, '9'=>'9 '._DAYS, '10'=>'10 '._DAYS, '14'=>'14 '._DAYS, '21'=>'21 '._DAYS, '28'=>'28 '._DAYS, '30'=>'1 '._MONTH, '45'=>'1.5 '._MONTHS, '60'=>'2 '._MONTHS, '90'=>'3 '._MONTHS, '120'=>'4 '._MONTHS, '180'=>'6 '._MONTHS, '240'=>'8 '._MONTHS, '270'=>'9 '._MONTHS, '365'=>'1 '._YEAR, '720'=>'2 '._YEARS, '1095'=>'3 '._YEARS, '1440'=>'4 '._YEARS, '1825'=>'5 '._YEARS, '-1'=>_UNLIMITED);
		return $array;
	}

}
?>