<?php

/**
 *	InquiriesReplies Class
 *  --------------
 *	Description : encapsulates methods and properties for inquiries replies
 *	Written by  : ApPHP
 *  Updated	    : 23.12.2012
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 			PRIVATE:
 * 	------------------	  	---------------     			---------------
 *	__construct             GetInquiryRepliesForCustomer
 *	__destruct
 *	AfterInsertRecord
 *	
 *	
 **/


class InquiriesReplies extends MicroGrid {
	
	protected $debug = false;
	
	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//		@param $inq_id
	//==========================================================================
	function __construct($inq_id, $customer_id = 0)
	{		
		parent::__construct();
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['inquiry_id']))  $this->params['inquiry_id'] = prepare_input($_POST['inquiry_id']);
		if(isset($_POST['customer_id'])) $this->params['customer_id'] = prepare_input($_POST['customer_id']);
		if(isset($_POST['date_added']))  $this->params['date_added'] = prepare_input($_POST['date_added']);
		if(isset($_POST['message']))     $this->params['message'] = prepare_input($_POST['message']);

		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}else if(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}else if (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		///$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_INQUIRIES_REPLIES; // TABLE_NAME
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = ((!empty($customer_id)) ? 'index.php?customer=inquiries_reply' : 'index.php?admin=mod_inquiries_reply').'&inq_id='.(int)$inq_id;
		$this->actions      = array('add'=>false, 'edit'=>false, 'details'=>true, 'delete'=>false);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ''; // ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE ir.inquiry_id = '.(int)$inq_id.(!empty($customer_id) ? ' AND customer_id = '.(int)$customer_id : '');
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = 'ORDER BY ir.date_added DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_3'  => array('table'=>'', 'field'=>'', 'type'=>'calendar', 'date_format'=>'dd/mm/yyyy|mm/dd/yyyy|yyyy/mm/dd', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
		);

		///$this->isAggregateAllowed = false;
		///// define aggregate fields for View Mode
		///$this->arrAggregateFields = array(
		///	'field1' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///	'field2' => array('function'=>'AVG', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///);

		///$date_format = get_date_format('view');
		///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
		///$date_format_edit = get_date_format('edit');
		$datetime_format = get_datetime_format();
		///$time_format = get_time_format(); /* by default 1st param - shows seconds */
		///$currency_format = get_currency_format();

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		/// $this->arrTranslations = $this->PrepareTranslateFields(
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		/// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		/// $sql_translation_description = $this->PrepareTranslateSql(
		///	TABLE_XXX_DESCRIPTION,
		///	'gallery_album_id',
		///	array('field1', 'field2')
		/// );
		///////////////////////////////////////////////////////////////////////////////			

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags, nl2br, readonly_text
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT ir.'.$this->primaryKey.',
									ir.inquiry_id,
									ir.customer_id,
									ir.message,
									ir.date_added,
									CONCAT(c.first_name, \' \', c.last_name) as customer_name
								FROM '.$this->tableName.' ir
									INNER JOIN '.TABLE_CUSTOMERS.' c ON ir.customer_id = c.id
								';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'customer_name'  => array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'date_added'     => array('title'=>_DATE_ADDED, 'type'=>'label', 'align'=>'left', 'width'=>'200px', 'header'=>'', 'maxlength'=>'', 'format'=>'date', 'format_parameter'=>$datetime_format),
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
			'message'       => array('title'=>_MESSAGE, 'type'=>'textarea', 'width'=>'510px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2048', 'default'=>'', 'height'=>'200px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			'customer_id'   => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$customer_id),
			'inquiry_id'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$inq_id),
			'date_added'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
		);

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
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.inquiry_id,
								'.$this->tableName.'.customer_id,
								'.$this->tableName.'.message,
								'.$this->tableName.'.date_added,
								CONCAT(c.first_name, \' \', c.last_name) as customer_name
							FROM '.$this->tableName.'
								INNER JOIN '.TABLE_CUSTOMERS.' c ON '.$this->tableName.'.customer_id = c.id
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'customer_name'  => array('title'=>_CUSTOMER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'date_added'     => array('title'=>_DATE_ADDED, 'type'=>'date',  'required'=>true, 'readonly'=>true, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>$datetime_format, 'min_year'=>'90', 'max_year'=>'10'),
			'message'        => array('title'=>_MESSAGE, 'type'=>'textarea', 'width'=>'510px', 'required'=>false, 'readonly'=>true, 'maxlength'=>'', 'default'=>'', 'height'=>'200px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'customer_name'  => array('title'=>_CUSTOMER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'date_added'     => array('title'=>_DATE_ADDED, 'type'=>'datetime', 'format'=>'date', 'format_parameter'=>$datetime_format, 'visible'=>true),
			'message'        => array('title'=>_MESSAGE, 'type'=>'html', 'visible'=>true),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'', 'readonly'=>false),
		/// 	  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'maxlength'=>'', 'maxlength'=>'512', 'validation_maxlength'=>'512', 'readonly'=>false)
		/// )
		/// );
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
	/**
	 * After addition record
	 */
	public function AfterInsertRecord()
	{
		global $objSettings, $objLogin;
		
		$maximum_replies = ModulesSettings::Get('inquiries', 'maximum_replies');
		// increase by 1 a number of replies and update is_active field
		$sql = 'UPDATE '.TABLE_INQUIRIES.'
				SET replies_count = replies_count + IF(replies_count < '.(int)$maximum_replies.', 1, 0),
					is_active = IF(replies_count > ('.(int)$maximum_replies.' - 1), 0, 1)
				WHERE id = '.(int)$this->params['inquiry_id'];
		database_void_query($sql);

		$objInquiries = Inquiries::Instance();
		$inquiry_info = $objInquiries->GetInfoByID($this->params['inquiry_id']);
		$visitor_email = isset($inquiry_info['email']) ? $inquiry_info['email'] : '';
		$visitor_name = isset($inquiry_info['name']) ? $inquiry_info['name'] : '';
		$reply_details  = _MESSAGE.':';
		$reply_details .= '<br>-----------<br>';
		$reply_details .= $this->params['message'];
		$reply_details .= '<br><br>';
		$reply_details .= _CUSTOMER_DETAILS.':';
		$reply_details .= '<br>-----------<br>';
		$reply_details .= _FIRST_NAME.': '.$objLogin->GetLoggedFirstName().'<br>';
		$reply_details .= _LAST_NAME.': '.$objLogin->GetLoggedLastName().'<br>';
		$reply_details .= _EMAIL.': '.$objLogin->GetLoggedEmail().'<br>';
		
		
		// send inquiry reply to visitor
		send_email(
			$visitor_email,
			$objSettings->GetParameter('admin_email'),
			'inquiry_reply',
			array(
				'{FIRST NAME}' => '',
				'{LAST NAME}'  => $visitor_name,
				'{REPLY DETAILS}'  => $reply_details,
				'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
				'{BASE URL}'   => APPHP_BASE,
			)
		);

	}

	/**
	 * Returns a number of inquiry replies for customer
	 * 		@param $inquiry_id
	 * 		@param $customer_id
	 */
	public static function GetInquiryRepliesForCustomer($inquiry_id, $customer_id)
	{
		$sql = 'SELECT ir.*
				FROM '.TABLE_INQUIRIES_REPLIES.' ir
					INNER JOIN '.TABLE_CUSTOMERS.' c ON ir.customer_id = c.id
				WHERE
					ir.inquiry_id = '.(int)$inquiry_id.' AND
					ir.customer_id = '.(int)$customer_id.' 
				';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result[1];
	}
	
	
}
?>