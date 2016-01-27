<?php

/**
 *	Class Customers (for Business Directory ONLY)
 *  -------------- 
 *  Description : encapsulates customers operations & properties
 *  Updated	    : 31.01.2012
 *	Written by  : ApPHP
 *	
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct			  	SendPassword                                  
 *	__destruct            	Reactivate                           
 *	BeforeEditRecord        DrawLoginFormBlock  
 *	BeforeUpdateRecord      SetListingsForCustomer
 *	AfterUpdateRecord       GetListingsForCustomer
 *	AfterInsertRecord	    GetCustomerInfo
 *	                        SetOrdersForCustomer
 *	                        GetAllCustomers
 *	                        
 *	
 **/

class Customers extends MicroGrid {
	
	protected $debug = false;
	
    //------------------------
	private $email_notifications;
	private $user_password;
	private $allow_adding_by_admin;
	private $allow_changing_password;
	private $reg_confirmation;

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		$this->params = array();		
		if(isset($_POST['group_id']))   $this->params['group_id']    = (int)prepare_input($_POST['group_id']);
		if(isset($_POST['first_name'])) $this->params['first_name']  = prepare_input($_POST['first_name']);
		if(isset($_POST['last_name']))	$this->params['last_name']   = prepare_input($_POST['last_name']);
		if(isset($_POST['birth_date']) && ($_POST['birth_date'] != ''))  $this->params['birth_date'] = prepare_input($_POST['birth_date']); else $this->params['birth_date'] = '0000-00-00';	
		if(isset($_POST['company']))   	$this->params['company']     = prepare_input($_POST['company']);
		if(isset($_POST['b_address']))  $this->params['b_address']   = prepare_input($_POST['b_address']);
		if(isset($_POST['b_address_2']))$this->params['b_address_2'] = prepare_input($_POST['b_address_2']);
		if(isset($_POST['b_city']))   	$this->params['b_city']      = prepare_input($_POST['b_city']);
		if(isset($_POST['b_state']))   	$this->params['b_state']     = prepare_input($_POST['b_state']);
		if(isset($_POST['b_country']))	$this->params['b_country']   = prepare_input($_POST['b_country']);
		if(isset($_POST['b_zipcode']))	$this->params['b_zipcode']   = prepare_input($_POST['b_zipcode']);
		if(isset($_POST['phone'])) 		$this->params['phone'] 		 = prepare_input($_POST['phone']);
		if(isset($_POST['fax'])) 		$this->params['fax'] 		 = prepare_input($_POST['fax']);
		if(isset($_POST['email'])) 		$this->params['email'] 		 = prepare_input($_POST['email']);
		if(isset($_POST['url'])) 		$this->params['url'] 		 = prepare_input($_POST['url'], false, 'medium');
		if(isset($_POST['user_name']))  $this->params['user_name']   = prepare_input($_POST['user_name']);
		if(isset($_POST['user_password']))  	$this->params['user_password']  = prepare_input($_POST['user_password']);
		if(isset($_POST['preferred_language'])) $this->params['preferred_language'] = prepare_input($_POST['preferred_language']);
		if(isset($_POST['date_created']))  		$this->params['date_created']   = prepare_input($_POST['date_created']);
		if(isset($_POST['date_lastlogin']))  	$this->params['date_lastlogin'] = prepare_input($_POST['date_lastlogin']);
		if(isset($_POST['registered_from_ip'])) $this->params['registered_from_ip'] = prepare_input($_POST['registered_from_ip']);
		if(isset($_POST['last_logged_ip'])) 	$this->params['last_logged_ip'] 	= prepare_input($_POST['last_logged_ip']);
		if(isset($_POST['email_notifications'])) 		 $this->params['email_notifications'] 		  = prepare_input($_POST['email_notifications']); else $this->params['email_notifications'] = '0';
		if(isset($_POST['notification_status_changed'])) $this->params['notification_status_changed'] = prepare_input($_POST['notification_status_changed']);
		if(isset($_POST['is_active']))  		$this->params['is_active']  		= (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['is_removed'])) 		$this->params['is_removed'] 		= (int)$_POST['is_removed']; else $this->params['is_removed'] = '0';
		if(isset($_POST['comments'])) 			$this->params['comments'] 		 	= prepare_input($_POST['comments']);
		if(isset($_POST['registration_code'])) 	$this->params['registration_code'] 	= prepare_input($_POST['registration_code']);
		if(isset($_POST['plan1_listings'])) 	$this->params['plan1_listings'] = prepare_input($_POST['plan1_listings']);
		if(isset($_POST['plan2_listings'])) 	$this->params['plan2_listings'] = prepare_input($_POST['plan2_listings']);
		if(isset($_POST['plan3_listings'])) 	$this->params['plan3_listings'] = prepare_input($_POST['plan3_listings']);
		if(isset($_POST['plan4_listings'])) 	$this->params['plan4_listings'] = prepare_input($_POST['plan4_listings']);

		$rid = MicroGrid::GetParameter('rid');
		$action = MicroGrid::GetParameter('action');

		$this->email_notifications = '';
		$this->user_password = '';

		$this->allow_adding_by_admin = ModulesSettings::Get('customers', 'allow_adding_by_admin');
		$this->allow_changing_password = ModulesSettings::Get('customers', 'password_changing_by_admin');
		$this->reg_confirmation = ModulesSettings::Get('customers', 'reg_confirmation');
		$allow_adding = ($this->allow_adding_by_admin == 'yes') ? true : false;
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CUSTOMERS;
		$this->dataSet 		= array();
		$this->error 		= '';
		///$this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();
		$this->formActionURL = 'index.php?admin=mod_customers_management';
		$this->actions      = array('add'=>$allow_adding, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;

		$this->allowLanguages = false;
		$this->WHERE_CLAUSE = '';		
		$this->ORDER_CLAUSE = 'ORDER BY id DESC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$total_countries = Countries::GetAllCountries('priority_order DESC, name ASC');
		$arr_countries = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}

		// prepare plans array
		$total_plans = AdvertisePlans::GetAllPlans();
		$arr_plans = array();
		foreach($total_plans[0] as $key => $val){
			$arr_plans[$val['id']] = $val['plan_name'];
		}
				
        // prepare groups array
		$total_groups = CustomerGroups::GetAllGroups();
		$arr_groups = array();
		foreach($total_groups[0] as $key => $val){
			$arr_groups[$val['id']] = $val['name'];
		}

		// prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}
		
		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_FIRST_NAME => array('table'=>'c', 'field'=>'first_name',  'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
			_LAST_NAME  => array('table'=>'c', 'field'=>'last_name',  'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
			_EMAIL      => array('table'=>'c', 'field'=>'email',  'type'=>'text', 'sign'=>'like%', 'width'=>'90px'),
			_ACTIVE     => array('table'=>'c', 'field'=>'is_active', 'type'=>'dropdownlist', 'source'=>array('0'=>_NO, '1'=>_YES), 'sign'=>'=', 'width'=>'85px'),
			_GROUP      => array('table'=>'c', 'field'=>'group_id', 'type'=>'dropdownlist', 'source'=>$arr_groups, 'sign'=>'=', 'width'=>'85px'),
		);

		$customer_ip = get_current_ip();
		$datetime_format = get_datetime_format();		
		$date_format_view = get_date_format('view');
		$date_format_edit = get_date_format('edit');

		$default_plan_info = AdvertisePlans::GetDefaultPlanInfo();
		$default_plan_id = isset($default_plan_info['id']) ? (int)$default_plan_info['id'] : 0;
		$default_plan_lc = isset($default_plan_info['listings_count']) ? (int)$default_plan_info['listings_count'] : 0;

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									c.'.$this->primaryKey.',
		                            c.*,
									CONCAT(c.first_name, " ", c.last_name) as full_name,
									IF(c.is_active, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as customer_active,
									cg.name as group_name
								FROM '.$this->tableName.' c
									LEFT OUTER JOIN '.TABLE_CUSTOMER_GROUPS.' cg ON c.group_id = cg.id ';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'full_name'    => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'20'),
			'user_name'    => array('title'=>_USERNAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'20'),
			'email' 	   => array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'width'=>'', 'maxlength'=>'36'),
			'b_country'    => array('title'=>_COUNTRY, 'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'source'=>$arr_countries),
			'customer_active'  => array('title'=>_ACTIVE, 'type'=>'label', 'align'=>'center', 'width'=>'90px'),
			'group_name'   => array('title'=>_GROUP, 'type'=>'label', 'align'=>'left', 'width'=>'90px'),
			'id'           => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'50px'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'last_name'    	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'birth_date'    => array('title'=>_BIRTH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'validation_type'=>'date', 'unique'=>false, 'visible'=>true, 'min_year'=>'90', 'max_year'=>'0', 'format'=>'date', 'format_parameter'=>$date_format_edit),
				'url' 			=> array('title'=>_URL,		 'type'=>'textbox', 'width'=>'270px', 'required'=>false, 'maxlength'=>'255', 'validation_type'=>'text'),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_BILLING_ADDRESS),
				'company' 		=> array('title'=>_COMPANY,	 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'128', 'validation_type'=>'text'),
				'b_address' 	=> array('title'=>_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_address_2' 	=> array('title'=>_ADDRESS_2,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_city' 		=> array('title'=>_CITY,	 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_zipcode' 	=> array('title'=>_ZIP_CODE, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'b_country' 	=> array('title'=>_COUNTRY,	 'type'=>'enum',     'width'=>'', 'source'=>$arr_countries, 'required'=>true),
				'b_state' 		=> array('title'=>_STATE_PROVINCE, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'64', 'validation_type'=>'text'),
			),
		    'separator_3'   =>array(
				'separator_info' => array('legend'=>_CONTACT_INFORMATION),
				'phone' 		=> array('title'=>_PHONE,	 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'32', 'validation_type'=>'text'),
				'fax' 		    => array('title'=>_FAX,	          'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'32', 'validation_type'=>'text'),
				'email' 		=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'230px', 'required'=>false, 'maxlength'=>'70', 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
			),
		    'separator_4'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name' 	 => array('title'=>_USERNAME,  'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'validation_type'=>'text', 'maxlength'=>'32', 'validation_minlength'=>'4', 'readonly'=>false, 'unique'=>true),
				'user_password'  => array('title'=>_PASSWORD,  'type'=>'password', 'width'=>'210px', 'required'=>true, 'validation_type'=>'password', 'maxlength'=>'20', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY),
				'group_id'       => array('title'=>_CUSTOMER_GROUP, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>$arr_groups),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'default'=>Application::Get('lang'), 'source'=>$arr_languages),
			),
		    'separator_5'   =>array(
				'separator_info' => array('legend'=>_OTHER),
				'date_created'		   => array('title'=>_DATE_CREATED,	'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s')),
				'registered_from_ip'   => array('title'=>_REGISTERED_FROM_IP, 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>$customer_ip),
				'last_logged_ip'	   => array('title'=>_LAST_LOGGED_IP,	  'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
				'email_notifications'  => array('title'=>_EMAIL_NOTIFICATION,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
				'is_active'			=> array('title'=>_ACTIVE,		  'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
				'is_removed'		=> array('title'=>_REMOVED,		  'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>'0'),
				'comments'			=> array('title'=>_COMMENTS,	  'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'2048'),
				'registration_code'	=> array('title'=>_REGISTRATION_CODE, 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),

				'plan1_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[1], 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>(($default_plan_id == '1') ? (int)$default_plan_lc : '0')),
				'plan2_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[2], 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>(($default_plan_id == '2') ? (int)$default_plan_lc : '0')),
				'plan3_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[3], 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>(($default_plan_id == '3') ? (int)$default_plan_lc : '0')),
				'plan4_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[4], 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>(($default_plan_id == '4') ? (int)$default_plan_lc : '0')),
			),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// * password field must be written directly in SQL!!!
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
		                            '.$this->tableName.'.*,
									'.$this->tableName.'.user_password,
									'.$this->tableName.'.date_created,
									'.$this->tableName.'.date_lastlogin,
									'.$this->tableName.'.notification_status_changed
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
								
		// define edit mode fields
		$this->arrEditModeFields = array(
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'last_name'    	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'birth_date'    => array('title'=>_BIRTH_DATE, 'type'=>'date', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'validation_type'=>'date', 'unique'=>false, 'visible'=>true, 'min_year'=>'90', 'max_year'=>'0', 'format'=>'date', 'format_parameter'=>$date_format_edit),
				'url' 			=> array('title'=>_URL,		 'type'=>'textbox', 'width'=>'270px', 'required'=>false, 'maxlength'=>'255', 'validation_type'=>'text'),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_BILLING_ADDRESS),
				'company' 		=> array('title'=>_COMPANY,	 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'128', 'validation_type'=>'text'),
				'b_address' 	=> array('title'=>_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_address_2' 	=> array('title'=>_ADDRESS_2,'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_city' 		=> array('title'=>_CITY,		 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'64', 'validation_type'=>'text'),
				'b_zipcode' 	=> array('title'=>_ZIP_CODE,	 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'b_country' 	=> array('title'=>_COUNTRY,	 'type'=>'enum',     'width'=>'', 'source'=>$arr_countries, 'required'=>true),
				'b_state' 		=> array('title'=>_STATE_PROVINCE, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'64', 'validation_type'=>'text'),
			),
		    'separator_3'   =>array(
				'separator_info' => array('legend'=>_CONTACT_INFORMATION),
				'phone' 		=> array('title'=>_PHONE,	 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'32', 'validation_type'=>'text'),
				'fax' 		    => array('title'=>_FAX,	          'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'maxlength'=>'32', 'validation_type'=>'text'),
				'email' 		=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'230px', 'required'=>true, 'maxlength'=>'70', 'readonly'=>false, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
			),
		    'separator_4'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name' 	 => array('title'=>_USERNAME, 'type'=>'label'),
				'user_password'  => array('title'=>_PASSWORD, 'type'=>'password', 'width'=>'210px', 'maxlength'=>'20', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'visible'=>(($this->allow_changing_password == 'yes') ? true : false)),
				'group_id'       => array('title'=>_CUSTOMER_GROUP, 'type'=>'enum', 'required'=>false, 'readonly'=>false, 'width'=>'', 'source'=>$arr_groups),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'source'=>$arr_languages),
			),
		    'separator_5'   =>array(
				'separator_info'  => array('legend'=>_OTHER),
				'date_created'	  => array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'date_lastlogin'  => array('title'=>_LAST_LOGIN, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'registered_from_ip'   => array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
				'last_logged_ip'	   => array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
				'email_notifications'  => array('title'=>_EMAIL_NOTIFICATION,	'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
				'notification_status_changed' => array('title'=>_NOTIFICATION_STATUS_CHANGED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'is_active'			=> array('title'=>_ACTIVE,		  'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
				'is_removed'		=> array('title'=>_REMOVED,		  'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
				'comments'			=> array('title'=>_COMMENTS,	  'type'=>'textarea', 'width'=>'420px', 'height'=>'70px', 'required'=>false, 'readonly'=>false, 'validation_type'=>'text', 'validation_maxlength'=>'2048'),
				'registration_code'	=> array('title'=>_REGISTRATION_CODE, 'type'=>'hidden', 'width'=>'210px', 'required'=>false, 'default'=>''),
			),
		    'separator_6'   =>array(
				'separator_info' => array('legend'=>_LISTINGS),
				'orders_count'	    => array('title'=>_ORDERS_COUNT, 'type'=>'label'),
				'plan1_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[1], 'type'=>'label'),
				'plan2_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[2], 'type'=>'label'),
				'plan3_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[3], 'type'=>'label'),
				'plan4_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[4], 'type'=>'label'),
			),				
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
									c.'.$this->primaryKey.',
		                            c.*,
									IF(c.email_notifications, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as email_notifications,
									IF(c.is_active, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as customer_active,
									IF(c.is_removed, "<span class=yes>'._YES.'</span>", "<span class=no>'._NO.'</span>") as customer_removed,
									c.date_created,
									c.date_lastlogin,
									c.notification_status_changed,
									cg.name as group_name
								FROM '.$this->tableName.' c
									LEFT OUTER JOIN '.TABLE_CUSTOMER_GROUPS.' cg ON c.group_id = cg.id
								WHERE c.'.$this->primaryKey.' = _RID_';
		$this->arrDetailsModeFields = array(			
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	=> array('title'=>_FIRST_NAME, 'type'=>'label'),
				'last_name'    	=> array('title'=>_LAST_NAME,  'type'=>'label'),
				'birth_date'    => array('title'=>_BIRTH_DATE,  'type'=>'date', 'format'=>'date', 'format_parameter'=>$date_format_view),
				'url' 			=> array('title'=>_URL,		 'type'=>'label'),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_BILLING_ADDRESS),
				'company' 		=> array('title'=>_COMPANY,	 'type'=>'label'),
				'b_address' 	=> array('title'=>_ADDRESS,	 'type'=>'label'),
				'b_address_2' 	=> array('title'=>_ADDRESS_2,'type'=>'label'),
				'b_city' 		=> array('title'=>_CITY,	 'type'=>'label'),
				'b_zipcode' 	=> array('title'=>_ZIP_CODE, 'type'=>'label'),
				'b_country' 	=> array('title'=>_COUNTRY,	 'type'=>'enum', 'source'=>$arr_countries),
				'b_state' 		=> array('title'=>_STATE_PROVINCE, 'type'=>'label'),
			),
		    'separator_3'   =>array(
				'separator_info' => array('legend'=>_CONTACT_INFORMATION),
				'phone' 		=> array('title'=>_PHONE,	 'type'=>'label'),
				'fax' 		    => array('title'=>_FAX,	'type'=>'label'),
				'email' 		=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
			),
		    'separator_4'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name' 	 => array('title'=>_USERNAME, 'type'=>'label'),
				'group_name'     => array('title'=>_CUSTOMER_GROUP, 'type'=>'label'),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages),				
			),
		    'separator_5'   =>array(
				'separator_info' => array('legend'=>_OTHER),
				'date_created'	=> array('title'=>_DATE_CREATED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'date_lastlogin'=> array('title'=>_LAST_LOGIN,	 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'registered_from_ip'   => array('title'=>_REGISTERED_FROM_IP, 'type'=>'label'),
				'last_logged_ip'	   => array('title'=>_LAST_LOGGED_IP,	 'type'=>'label'),
				'email_notifications'  => array('title'=>_EMAIL_NOTIFICATION,	'type'=>'label'),
				'notification_status_changed' => array('title'=>_NOTIFICATION_STATUS_CHANGED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'customer_active'	=> array('title'=>_ACTIVE,		 'type'=>'label'),
				'customer_removed'	=> array('title'=>_REMOVED,		 'type'=>'label'),
				'comments'			=> array('title'=>_COMMENTS,     'type'=>'label'),
			),
		    'separator_6'   =>array(
				'separator_info' => array('legend'=>_LISTINGS),
				'orders_count'	    => array('title'=>_ORDERS_COUNT, 'type'=>'label'),
				'plan1_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[1], 'type'=>'label'),
				'plan2_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[2], 'type'=>'label'),
				'plan3_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[3], 'type'=>'label'),
				'plan4_listings'	=> array('title'=>_ADVERTISE_PLAN.' '.$arr_plans[4], 'type'=>'label'),
			),				
		);

	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }


	/**
	 * Send forgotten password
	 *		@param $email
	 */
	public static function SendPassword($email)
	{		
		global $objSettings;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}
		
		if(!empty($email)) {
			if(check_email_address($email)){   
				if(!PASSWORDS_ENCRYPTION){
					$sql = 'SELECT id, first_name, last_name, user_name, user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql = 'SELECT id, first_name, last_name, user_name, AES_DECRYPT(user_password, \''.PASSWORDS_ENCRYPT_KEY.'\') as user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
					}else if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql = 'SELECT id, first_name, last_name, user_name, \'\' as user_password, preferred_language FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\' AND is_active = 1';
					}
				}
				
				$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
				if(is_array($temp) && count($temp) > 0){
					$sender = $objSettings->GetParameter('admin_email');
					$recipiant = $email;
	
					if(!PASSWORDS_ENCRYPTION){
						$user_password = $temp['user_password'];
					}else{
						if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
							$user_password = $temp['user_password'];
						}else if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
							$user_password = get_random_string(8);
							$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = \''.md5($user_password).'\' WHERE id = '.$temp['id'];
							database_void_query($sql);
						}				
					}
					////////////////////////////////////////////////////////////
					send_email(
						$recipiant,
						$sender,
						'password_forgotten',
						array(
							'{FIRST NAME}' => $temp['first_name'],
							'{LAST NAME}'  => $temp['last_name'],
							'{USER NAME}'  => $temp['user_name'],
							'{USER PASSWORD}' => $user_password,
							'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
							'{BASE URL}'   => APPHP_BASE,
							'{YEAR}' 	   => date('Y')
						),
						$temp['preferred_language']
					);
					////////////////////////////////////////////////////////////					
					return true;					
				}else{
					self::$static_error = _EMAIL_NOT_EXISTS;
					return false;
				}				
			}else{
				self::$static_error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			self::$static_error = _EMAIL_EMPTY_ALERT;
			return false;
		}
		return true;
	}
	
	/**
	 * Before Edit Record
	 */
	public function BeforeEditRecord()
	{
		$registration_code = isset($this->result[0][0]['registration_code']) ? $this->result[0][0]['registration_code'] : '';
		$is_active         = isset($this->result[0][0]['is_active']) ? $this->result[0][0]['is_active'] : '';
		$reactivation_html = '';
		
        if($registration_code != '' && !$is_active && $this->reg_confirmation == 'by email'){
			$reactivation_html = ' &nbsp;<a href="javascript:void(\'email|reactivate\')" onclick="javascript:if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\'))__mgDoPostBack(\''.TABLE_CUSTOMERS.'\',\'reactivate\');">[ '._REACTIVATION_EMAIL.' ]</a>';
		}
		$this->arrEditModeFields['separator_3']['email']['post_html'] = $reactivation_html;
	}

	/**
	 * Before-Updating operation
	 */
	public function BeforeUpdateRecord()
	{	
		$sql = 'SELECT email_notifications, user_password FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        if(isset($result['email_notifications'])) $this->email_notifications = $result['email_notifications'];
		if(isset($result['user_password'])) $this->user_password = $result['user_password'];
		return true;
	}

	/**
	 * After-Updating operation
	 */
	public function AfterUpdateRecord()
	{
		global $objSettings;
		
		$registration_code = self::GetParameter('registration_code', false);
		$is_active         = self::GetParameter('is_active', false);
		$removed_update_clause = ((self::GetParameter('is_removed', false) == '1') ? ', is_active = 0' : '');
		$confirm_update_clause = '';

		$sql = 'SELECT user_name, user_password, preferred_language FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$preferred_language = isset($result['preferred_language']) ? $result['preferred_language'] : '';
		$user_password = isset($result['user_password']) ? $result['user_password'] : '';

		if(!empty($registration_code) && $is_active && $this->reg_confirmation == 'by admin'){
			$confirm_update_clause = ', registration_code=\'\'';
			////////////////////////////////////////////////////////////
			send_email(
				self::GetParameter('email', false),
				$objSettings->GetParameter('admin_email'),
				'registration_approved_by_admin',
				array(
					'{FIRST NAME}' => self::GetParameter('first_name', false),
					'{LAST NAME}'  => self::GetParameter('last_name', false),
					'{USER NAME}'  => self::GetParameter('user_name', false),
					'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
					'{BASE URL}'   => APPHP_BASE,
					'{YEAR}' 	   => date('Y')
				),
				$preferred_language
			);
			////////////////////////////////////////////////////////////
		}		

		$sql = 'UPDATE '.$this->tableName.' SET
					notification_status_changed = IF(email_notifications <> \''.$this->email_notifications.'\', \''.date('Y-m-d H:i:s').'\', notification_status_changed)
				    '.$removed_update_clause.'
					'.$confirm_update_clause.'
				WHERE '.$this->primaryKey.' = '.$this->curRecordId;		
		database_void_query($sql);

        // send email, if password was changed
		if($user_password != $this->user_password){
			////////////////////////////////////////////////////////////
			send_email(
				self::GetParameter('email', false),
				$objSettings->GetParameter('admin_email'),
				'password_changed_by_admin',
				array(
					'{FIRST NAME}'    => self::GetParameter('first_name', false),
					'{LAST NAME}'     => self::GetParameter('last_name', false),
					'{USER NAME}'     => $result['user_name'],
					'{USER PASSWORD}' => self::GetParameter('user_password', false),
					'{WEB SITE}'      => $_SERVER['SERVER_NAME']
				),
				$preferred_language
			);
			////////////////////////////////////////////////////////////			
		}
		
		return true;
	}

	/**
	 * After-Addition operation
	 */
	public function AfterInsertRecord()
	{
		global $objSettings, $objSiteDescription;
		
		////////////////////////////////////////////////////////////
		if(!empty($this->params['email'])){
			send_email(
				$this->params['email'],
				$objSettings->GetParameter('admin_email'),
				'new_account_created_by_admin',
				array(
					'{FIRST NAME}' => $this->params['first_name'],
					'{LAST NAME}'  => $this->params['last_name'],
					'{USER NAME}'  => $this->params['user_name'],
					'{USER PASSWORD}' => $this->params['user_password'],
					'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
					'{BASE URL}'   => APPHP_BASE,
					'{YEAR}' 	   => date('Y')
				),
				$this->params['preferred_language']
			);
		}
		////////////////////////////////////////////////////////////
	}

	/**
	 * Draws login form on Front-End
	 * 		@param $draw
	 */
	public static function DrawLoginFormBlock($draw = true)
	{
		global $objLogin;		

		$username = '';
		$password = '';
		$remember_me_checked = '';
		
		// check if remember me cookies exists
		if(ModulesSettings::Get('customers', 'remember_me_allow') == 'yes'){
			$objLogin->CheckRememberMe($username, $password);
			if(!empty($username) && !empty($password)) $remember_me_checked = 'checked="checked"';
		}

		$output = draw_block_top(_AUTHENTICATION, '', 'maximized', false);
		$output .= '<form class="customer_login" action="index.php?customer=login" method="post">
			'.draw_hidden_field('submit_login', 'login', false).'
			'.draw_hidden_field('type', 'customer', false).'
			'.draw_token_field(false).'			
			<table border="0" cellspacing="1" cellpadding="1">
			<tr><td>'._USERNAME.':</td></tr>
			<tr><td><input type="text" style="width:130px" name="user_name" id="user_name" maxlength="50" autocomplete="off" value="'.$username.'" /></td></tr>
			<tr><td>'._PASSWORD.':</td></tr>
			<tr><td><input type="password" style="width:130px" name="password" id="password" maxlength="20" autocomplete="off" value="'.$password.'" /></td></tr>
			<tr><td valign="middle">';
		$output .= '<input class="form_button" type="submit" name="submit" value="'._BUTTON_LOGIN.'" /> ';
		if(ModulesSettings::Get('customers', 'remember_me_allow') == 'yes'){
			$output .= '<input class="form_checkbox" '.$remember_me_checked.' name="remember_me" value="1" type="checkbox" /> '._REMEMBER_ME.'<br>';
		}				
		$output .= '</td></tr>
			<tr><td></td></tr>';
			if(ModulesSettings::Get('customers', 'allow_registration') == 'yes') $output .= '<tr><td>'.prepare_permanent_link('index.php?customer=create_account', _CREATE_ACCOUNT, '', 'form_link').'</td></tr>';
			if(ModulesSettings::Get('customers', 'allow_reset_passwords') == 'yes') $output .= '<tr><td>'.prepare_permanent_link('index.php?customer=password_forgotten', _FORGOT_PASSWORD, '', 'form_link').'</td></tr>';
		$output .= '</table>
		</form>';
		$output .= draw_block_bottom(false);
		
		if($draw) echo $output;
		else return $output;				
	}	

	/**
	 * Send activation email
	 *		@param $email
	 */
	public static function Reactivate($email)
	{		
		global $objSettings;
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			self::$static_error = _OPERATION_BLOCKED;
			return false;
		}
		
		if(!empty($email)) {
			if(check_email_address($email)){			
				$sql = 'SELECT id, first_name, last_name, user_name, registration_code, preferred_language, is_active ';
				if(!PASSWORDS_ENCRYPTION){
					$sql .= ', user_password ';
				}else{
					if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
						$sql .= ', AES_DECRYPT(user_password, \''.PASSWORDS_ENCRYPT_KEY.'\') as user_password ';
					}else if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
						$sql .= ', \'\' as user_password ';
					}				
				}
				$sql .= 'FROM '.TABLE_CUSTOMERS.' WHERE email = \''.$email.'\'';
				$temp = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
				if(is_array($temp) && count($temp) > 0){
					if($temp['registration_code'] != '' && $temp['is_active'] == '0'){
						////////////////////////////////////////////////////////		
						if(!PASSWORDS_ENCRYPTION){
							$user_password = $temp['user_password'];
						}else{
							if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'aes'){
								$user_password = $temp['user_password'];
							}else if(strtolower(PASSWORDS_ENCRYPTION_TYPE) == 'md5'){
								$user_password = get_random_string(8);
								$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET user_password = \''.md5($user_password).'\' WHERE id = '.$temp['id'];
								database_void_query($sql);
							}				
						}

						send_email(
							$email,
							$objSettings->GetParameter('admin_email'),
							'new_account_created_confirm_by_email',
							array(
								'{FIRST NAME}' => $temp['first_name'],
								'{LAST NAME}'  => $temp['last_name'],
								'{USER NAME}'  => $temp['user_name'],
								'{USER PASSWORD}' => $user_password,
								'{REGISTRATION CODE}' => $temp['registration_code'],
								'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
								'{BASE URL}'   => APPHP_BASE,
								'{YEAR}' 	   => date('Y')
							),
							$temp['preferred_language']
						);
						////////////////////////////////////////////////////////
						return true;					
					}else{
						self::$static_error = _EMAILS_SENT_ERROR;
						return false;						
					}
				}else{
					self::$static_error = _EMAIL_NOT_EXISTS;
					return false;
				}				
			}else{
				self::$static_error = _EMAIL_IS_WRONG;
				return false;								
			}
		}else{
			self::$static_error = _EMAIL_EMPTY_ALERT;
			return false;
		}
		return true;
	}
	
	/**
	 * Sets plan listings for specific customer
	 * 		@param $customer_id
	 * 		@param $plan_id
	 * 		@param $operation
	 */
	public static function SetListingsForCustomer($customer_id = 0, $plan_id = 0, $listings_amount = 0, $operation = '')
	{
		$sql = 'UPDATE '.TABLE_CUSTOMERS.' ';
		if($operation == '+'){
			$sql .= 'SET plan'.(int)$plan_id.'_listings = plan'.(int)$plan_id.'_listings + '.(int)$listings_amount.' ';
		}else{
			$sql .= 'SET plan'.(int)$plan_id.'_listings = IF(plan'.(int)$plan_id.'_listings >= '.(int)$listings_amount.', plan'.(int)$plan_id.'_listings - '.(int)$listings_amount.', 0) ';
		}
		$sql .= 'WHERE id = '.(int)$customer_id;			
		database_void_query($sql);
	}	

	/**
	 * Set orders for specific customer
	 * 		@param $customer_id
	 * 		@param $operation
	 */
	public static function SetOrdersForCustomer($customer_id = 0, $operation = '')
	{
		$sql = 'UPDATE '.TABLE_CUSTOMERS.' ';
		if($operation == '+'){
			$sql .= 'SET orders_count = orders_count + 1 ';			
		}else{
			$sql .= 'SET orders_count = IF(orders_count > 0, orders_count - 1, 0) ';
		}
		$sql .= 'WHERE id = '.(int)$customer_id;
		database_void_query($sql);
	}

	/**
	 * Returns lisitngs of all plans for specific customer
	 * 		@param $customer_id
	 */
	public static function GetListingsForCustomer($customer_id = 0)
	{
		$result = array();
		$result_listings = array();
		$result_plans = array();

		$result_temp = AdvertisePlans::GetAllPlans();
		for($i = 0; $i < $result_temp[1]; $i++){
			$result_plans[$result_temp[0][$i]['id']] = $result_temp[0][$i]['plan_name'];
		}
		
		$sql = 'SELECT plan1_listings, plan2_listings, plan3_listings, plan4_listings FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$customer_id;
		$result_listings = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);		
		if($result_listings[1] > 0){
			if($result_listings[0]['plan1_listings'] > 0) $result[0] = array('id'=>'1', 'count'=>$result_listings[0]['plan1_listings'], 'name'=>(isset($result_plans['1']) ? $result_plans['1'] : ''));
			if($result_listings[0]['plan2_listings'] > 0) $result[1] = array('id'=>'2', 'count'=>$result_listings[0]['plan2_listings'], 'name'=>(isset($result_plans['2']) ? $result_plans['2'] : ''));
			if($result_listings[0]['plan3_listings'] > 0) $result[2] = array('id'=>'3', 'count'=>$result_listings[0]['plan3_listings'], 'name'=>(isset($result_plans['3']) ? $result_plans['3'] : ''));
			if($result_listings[0]['plan4_listings'] > 0) $result[3] = array('id'=>'4', 'count'=>$result_listings[0]['plan4_listings'], 'name'=>(isset($result_plans['4']) ? $result_plans['4'] : ''));
		}
		
		return $result;
	}
	
	/**
	 * Returns info about customer
	 * 		@param $customer_id
	 */
	public static function GetCustomerInfo($customer_id = 0)
	{
		$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$customer_id;
		return database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);		
	}
	
	/**
	 * Returns customers info 
	 * 		@param $where
	 */
	public static function GetAllCustomers($where = '')
	{
		$sql = 'SELECT *
				FROM '.TABLE_CUSTOMERS.'
				WHERE 1 = 1
				'.(!empty($where) ? ' AND '.$where : '');
		return database_query($sql, DATA_AND_ROWS, ALL_ROWS);			
	}
	
	
}
?>