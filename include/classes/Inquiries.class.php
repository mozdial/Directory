<?php

/***
 *	Inquiries class 
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 13.12.2012
 *  Usage       : Business directory OINLY
 *
 *	PUBLIC:				  	 STATIC:				 	PRIVATE:
 * 	------------------	  	 ---------------     	---------------
 *	__construct              DrawTopGuideBlock
 *	__destruct               DrawInquiryForm
 *	CustomerRelatedToInquire DrawInquiryDirectForm
 *	                         DrawLastInquiriesBlock
 *	                         DrawInquirySubForm (private)
 *	                         GetAllInquiries
 *	                         Instance
 *	                         RemoveOld
 *
 **/

class Inquiries extends MicroGrid {
	
	protected $debug = false;
	
	//----------------------------------
	private static $instance;
	
	private $sqlFieldDatetimeFormat = '';
	private static $arr_availabilities = array('0'=>_ANYTIME, '1'=>_MORNING, '2'=>_LUNCH, '3'=>_AFTERNOON, '4'=>_EVENING, '5'=>_WEEKEND);
	private static $arr_preferred_contacts = array('0'=>_BY_PHONE_OR_EMAIL, '1'=>_BY_PHONE, '2'=>_VIA_EMAIL);
	private static $arr_inquiry_types = array('0'=>_STANDARD, '1'=>_DIRECT);

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objSettings, $objLogin;

		$this->params = array();		
		## for standard fields
		if(isset($_POST['name'])) $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['email'])) $this->params['email'] = prepare_input($_POST['email']);
		if(isset($_POST['phone'])) $this->params['phone'] = prepare_input($_POST['phone']);
		if(isset($_POST['availability'])) $this->params['availability'] = prepare_input($_POST['availability']);
		if(isset($_POST['preferred_contact'])) $this->params['preferred_contact'] = prepare_input($_POST['preferred_contact']);
		if(isset($_POST['description'])) $this->params['description'] = prepare_input($_POST['description']);
		if(isset($_POST['date_created'])) $this->params['date_created'] = prepare_input($_POST['date_created']);
		if(isset($_POST['replies_count'])) $this->params['replies_count'] = prepare_input($_POST['replies_count']);
		
	
		## for checkboxes 
		$this->params['is_active'] = isset($_POST['is_active']) ? prepare_input($_POST['is_active']) : '0';

		## for images
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = $_POST['icon'];
		//}else if(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}else if (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		$this->params['language_id'] = '';//MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_INQUIRIES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?'.($objLogin->IsLoggedInAsAdmin() ? 'admin=mod_inquiries_management' : 'customer=inquiries');
		$this->actions      = array(
			'add'     => false,
			'edit'    => ($objLogin->IsLoggedInAs('owner') ? true : false),
			'details' => true,
			'delete'  => ($objLogin->IsLoggedInAs('owner') ? true : false)
		);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId = '';
		
		$visible = $objLogin->IsLoggedInAsCustomer() ? false : true;
		
		if($objLogin->IsLoggedInAsCustomer()){
			$this->WHERE_CLAUSE = 'WHERE ((
				i.inquiry_type = 1 AND
				i.listing_id IN (SELECT l.id FROM '.TABLE_LISTINGS.' l WHERE l.customer_id = '.(int)$objLogin->GetLoggedId().')
			) OR (
				i.inquiry_type = 0 AND
				i.id IN (SELECT ih.inquiry_id FROM '.TABLE_INQUIRIES_HISTORY.' ih WHERE ih.customer_id = '.(int)$objLogin->GetLoggedId().')
			))';
		}else{
			$this->WHERE_CLAUSE = '';	
		}
		$this->ORDER_CLAUSE = 'ORDER BY i.date_created DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_NAME         => array('table'=>'i', 'field'=>'name', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px', 'visible'=>true),
			_DATE_CREATED => array('table'=>'i', 'field'=>'date_created', 'type'=>'calendar', 'date_format'=>$objSettings->GetParameter('date_format'), 'sign'=>'like%', 'width'=>'85px', 'visible'=>true),
		);
		
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
		}
		$this->SetLocale(Application::Get('lc_time_name'));			

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		//$replies_count = $objLogin->IsLoggedInAsCustomer() ? 1 : ModulesSettings::Get('inquiries', 'maximum_replies');
		
		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT i.'.$this->primaryKey.',
									i.inquiry_type,
									i.name,									
									i.email,
									i.phone,
									i.location_id,
									i.sub_location_id,
									i.availability,
									i.preferred_contact,
									i.description,
									i.is_active,
									DATE_FORMAT(i.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
									i.replies_count,';
									if($objLogin->IsLoggedInAsCustomer()){
										$this->VIEW_MODE_SQL .= 'IF(											
											(
												SELECT ir.id
												FROM '.TABLE_INQUIRIES_REPLIES.' ir
													INNER JOIN '.TABLE_CUSTOMERS.' c ON ir.customer_id = c.id
												WHERE 
													ir.inquiry_id = i.id AND
													ir.customer_id = '.(int)$objLogin->GetLoggedId().' 
												LIMIT 0, 1 
											) IS NULL,
											IF(i.is_active = 0, "'._CLOSED.'", CONCAT("<a href=index.php?customer=inquiries_reply&inq_id=", i.'.$this->primaryKey.', "&act=add>[ '._REPLY.' ]</a>")),
											CONCAT("<a href=index.php?customer=inquiries_reply&inq_id=", i.'.$this->primaryKey.', "> '._ANSWERED.' </a>")
										) as link_reply_customer,
										"" as link_reply_admin,';				
									}else{
										$this->VIEW_MODE_SQL .= '
											"" as link_reply_customer,
											CONCAT("<a href=index.php?admin=mod_inquiries_reply&inq_id=", i.'.$this->primaryKey.', ">", IF((i.is_active = 1), "[ '._ACTIVE.' ]", "'._CLOSED.'"), "</a> (", i.replies_count, ")") as link_reply_admin,
										';
									}
									$this->VIEW_MODE_SQL .= 'CONCAT(ll.name, " / ", lsl.name) as location_name,
									lcd.name as category_name
								FROM '.$this->tableName.' i
									LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' lcd ON i.category_id = lcd.category_id AND lcd.language_id = \''.Application::Get('lang').'\'
									LEFT OUTER JOIN '.TABLE_LISTINGS_LOCATIONS.' ll ON i.location_id = ll.id
									LEFT OUTER JOIN '.TABLE_LISTINGS_SUB_LOCATIONS.' lsl ON i.sub_location_id = lsl.id
								';
								
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'          => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'email' 	    => array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'width'=>'', 'maxlength'=>'36', 'visible'=>$visible),
			//'phone' 		=> array('title'=>_PHONE,	 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'category_name' => array('title'=>_CATEGORY,	'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'nowrap', 'visible'=>'', 'maxlength'=>''),
			'location_name' => array('title'=>_LOCATION,	'align'=>'left', 'width'=>'130px', 'sortable'=>true, 'nowrap'=>'nowrap', 'visible'=>'', 'maxlength'=>''),
			//'sub_location_name' => array('title'=>_SUB_LOCATION,	'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),			
			'date_created'  => array('title'=>_DATE_CREATED, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'nowrap', 'visible'=>'', 'height'=>'', 'maxlength'=>''),
			'inquiry_type'  => array('title'=>_TYPE, 'type'=>'enum', 'align'=>'center', 'width'=>'70px', 'source'=>self::$arr_inquiry_types, 'visible'=>true),

			'link_reply_customer' => array('title'=>_REPLIES, 'type'=>'label', 'align'=>'center', 'width'=>'65px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'visible'=>$objLogin->IsLoggedInAsCustomer()),
			'link_reply_admin'    => array('title'=>_REPLIES, 'type'=>'label', 'align'=>'center', 'width'=>'65px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'', 'visible'=>$objLogin->IsLoggedInAsAdmin()),

			//'replies_count' => array('title'=>_REPLIES, 'type'=>'label', 'align'=>'center', 'width'=>'60px', 'visible'=>$visible),
			'is_active'     => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255 ....
		//   Ex.: 'validation_maxlength'=>'255'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								i.inquiry_type,
								i.category_id,
								cd.name as category_name,
								i.listing_id,
								ld.business_name as listing_name,
								i.name,
								i.email,
								i.phone,
								i.availability,
								i.preferred_contact,
								i.description,
								DATE_FORMAT(i.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
								i.replies_count,
								i.is_active,
								ll.name as location_name,
								lsl.name as sub_location_name
							FROM '.$this->tableName.' i
								LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON i.category_id = cd.category_id AND cd.language_id = \''.Application::Get('lang').'\'
								LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON i.listing_id = ld.listing_id AND ld.language_id = \''.Application::Get('lang').'\'
								LEFT OUTER JOIN '.TABLE_LISTINGS_LOCATIONS.' ll ON i.location_id = ll.id
								LEFT OUTER JOIN '.TABLE_LISTINGS_SUB_LOCATIONS.' lsl ON i.sub_location_id = lsl.id
							WHERE i.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'separator_contact_information'   =>array(
				'separator_info' => array('legend'=>_CONTACT_INFORMATION, 'columns'=>'0'),
				'name'		=> array('title'=>_NAME, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
				'email'		=> array('title'=>_EMAIL_ADDRESS, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'70', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>$visible),
				'phone'		=> array('title'=>_PHONE, 'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'20', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
				'location_name'     => array('title'=>_LOCATION, 'type'=>'label'),
				'sub_location_name' => array('title'=>_SUB_LOCATION, 'type'=>'label'),
				'availability'      => array('title'=>_I_AM_AVAILABLE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>self::$arr_availabilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'preferred_contact' => array('title'=>_PREFERRED_TO_BE_CONTACTED, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>self::$arr_preferred_contacts, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			),
			'separator_inquiry_details'   =>array(
				'separator_info' => array('legend'=>_INQUIRY_DETAILS, 'columns'=>'0'),
				'description'   => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'2048', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'validation_maxlength'=>'2048', 'unique'=>false),	
				'inquiry_type'  => array('title'=>_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>true, 'default'=>'', 'source'=>self::$arr_inquiry_types, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'category_name'	=> array('title'=>_CATEGORY, 'type'=>'label'),
				'listing_name'	=> array('title'=>_LISTING, 'type'=>'label'),
				'date_created'	=> array('title'=>_DATE_CREATED, 'type'=>'label'),
				'replies_count'	=> array('title'=>_REPLIES, 'type'=>'label'),
				'is_active'     => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),		
			)
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'separator_contact_information'   =>array(
				'separator_info' => array('legend'=>_CONTACT_INFORMATION, 'columns'=>'0'),
				'name'			=> array('title'=>_NAME, 'type'=>'label'),
				'email'			=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label', 'visible'=>$visible),
				'phone'			=> array('title'=>_PHONE, 'type'=>'label', 'visible'=>$visible),
				'location_name'     => array('title'=>_LOCATION, 'type'=>'label'),
				'sub_location_name' => array('title'=>_SUB_LOCATION, 'type'=>'label'),
				'availability'  => array('title'=>_I_AM_AVAILABLE, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>self::$arr_availabilities, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'preferred_contact' => array('title'=>_PREFERRED_TO_BE_CONTACTED, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>self::$arr_preferred_contacts, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			),
			'separator_inquiry_details'   =>array(
				'separator_info' => array('legend'=>_INQUIRY_DETAILS, 'columns'=>'0'),
				'description'   => array('title'=>_DESCRIPTION, 'type'=>'html'),	
				'inquiry_type'  => array('title'=>_TYPE, 'type'=>'enum', 'source'=>self::$arr_inquiry_types, 'visible'=>$visible),
				'category_name'	=> array('title'=>_CATEGORY, 'type'=>'label', 'visible'=>$visible),
				'listing_name'	=> array('title'=>_LISTING, 'type'=>'label', 'visible'=>$visible),
				'date_created'	=> array('title'=>_DATE_CREATED, 'type'=>'label'),
				'replies_count'	=> array('title'=>_REPLIES, 'type'=>'label', 'visible'=>$visible),
				'is_active'     => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			)
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
	 *	Return instance of the class
	 */
	public static function Instance()
	{
		if(self::$instance == null) self::$instance = new Inquiries();
		return self::$instance;
	}       

	/**
	 *	Draws top guide block (3 steps)
	 *		@param $draw
	 */
	public static function DrawTopGuideBlock($draw = true)
	{
		$page = Application::Get('page');
		$output = '';
		
		$inquiry_category = isset($_POST['inquiry_category']) ? $_POST['inquiry_category'] : '';
		
		if(Modules::IsModuleInstalled('inquiries') &&
		   in_array($page, array('home', 'inquiry_send', 'inquiry_form', 'inquiry_send')) &&
		   Application::Get('customer') == ''){

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
	
			$output .= '
			<table id="guide-block">
			<tr>
				<td width="33%">
					<div class="steps step1'.(($page == 'inquiry_form') ? ' active' : '').'">
						<div class="header">'._STEP.' 1</div>
						<div class="content">'._STEP_1_DESCRIPTION.'</div>
					</div>
				</td>
				<td width="34%">
					<div class="steps step2'.(($page == 'inquiry_send') ? ' active' : '').'">
						<div class="header">'._STEP.' 2</div>
						<div class="content">'._STEP_2_DESCRIPTION.'</div>
					</div>
				</td>
				<td width="33%">
					<div class="steps step3">
						<div class="header">'._STEP.' 3</div>
						<div class="content">'._STEP_3_DESCRIPTION.'</div>
					</div>
				</td>
			</tr>';
			
			if($page == 'home'){
				$output .= '<tr>
				<td colspan="3">
					<div class="footer">
						<div class="content">
							<form name="frmGideBlock" action="index.php?page=inquiry_form" method="post">
							'.draw_token_field(false).'
							'._WHAT_DO_YOU_NEED.' &nbsp; ';				
				
							$output .= '<select name="inquiry_category">';
							$output .= '<option value="">-- '._SELECT.' --</option>';
							foreach($arr_categories as $key => $val){
								$output .= '<option value="'.$key.'"'.(($inquiry_category == $key) ? ' selected="selected"' : '').'>'.$val.'</option>';
							}
							$output .= '</select>';
					
							$output .= '&nbsp; <input type="submit" class="form_button"  value="'._SUBMIT.'">
							</form>
						</div>
					</div>
				</td>
				</tr>';
			}
			$output .= '</table>';
		}
		
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 *	Draws inquiry form
	 *		@param $params
	 *		@param $draw
	 */
	public static function DrawInquiryForm($params, $draw = true)
	{
		$output = '';
		
		$inquiry_category = isset($params['inquiry_category']) ? prepare_input($params['inquiry_category']) : '';
		$widget = isset($params['widget']) ? prepare_input($params['widget']) : '';
		$widget_host = isset($params['widget_host']) ? prepare_input($params['widget_host']) : '';
		$widget_key = isset($params['widget_key']) ? prepare_input($params['widget_key']) : '';

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
		if($widget){
			$output .= '<form id="frmInquiryForm" action="'.APPHP_BASE.'index.php?host='.$widget_host.'&key='.$widget_key.'" method="post">';	
		}else{
			$output .= '<form id="frmInquiryForm" action="index.php?page=inquiry_form" method="post">';	
		}				
		$output .= draw_token_field(false);
		$output .= draw_hidden_field('act', 'send', false, 'id_act');
		$output .= draw_hidden_field('inquiry_type', '0', false);
		
		$output .= '<div class="inquiry_wrapper">';
		
		$output .= _WHAT_DO_YOU_NEED.'<br>';
		$output .= '<select id="inquiry_category" name="inquiry_category">';
		$output .= '<option value="">-- '._SELECT.' --</option>';
		foreach($arr_categories as $key => $val){
			$output .= '<option value="'.$key.'"'.(($inquiry_category == $key) ? ' selected="selected"' : '').'>'.$val.'</option>';
		}
		$output .= '</select><br><br>';		
		
		$output .= self::DrawInquirySubForm($params, false);		
		$output .= '</div>';
		
		$output .= '</form>';
		
		if($draw) echo $output;
		else return $output;
		
	}
	
	/**
	 *	Draws inquiry direct form
	 *		@param $params
	 *		@param $draw
	 */
	public static function DrawInquiryDirectForm($params, $draw = true)
	{
		$listing_id = isset($params['listing_id']) ? $params['listing_id'] : '';
		$business_name = isset($params['business_name']) ? $params['business_name'] : '';
		$output = '';
		
		//print_r($_POST);
		
		$output .= '<form id="frmInquiryForm" action="index.php?page=inquiry_form" method="post">';
		$output .= draw_token_field(false);
		$output .= draw_hidden_field('act', 'send', false, 'id_act');
		$output .= draw_hidden_field('business_name', $business_name, false);
		$output .= draw_hidden_field('listing_id', $listing_id, false);
		$output .= draw_hidden_field('inquiry_type', '1', false);
		
		$output .= '<div class="inquiry_wrapper">';
		
		$output .= '<h3>'.$business_name.'</h3>';		
		
		$output .= self::DrawInquirySubForm($params, false);
		
		$output .= '</div>';
		$output .= '</form>';
			
		if($draw) echo $output;
		else return $output;
	}


	/**
	 *	Draws inquiry sub form
	 *		@param $params
	 *		@param $draw
	 */
	private static function DrawInquirySubForm($params, $draw = true)
	{
		$output = '';

		$visitor_locations = isset($params['visitor_locations']) ? $params['visitor_locations'] : '';
		$visitor_sub_locations = isset($params['visitor_sub_locations']) ? $params['visitor_sub_locations'] : '';
		$visitor_description = isset($params['visitor_description']) ? $params['visitor_description'] : '';
		$visitor_availability = isset($params['visitor_availability']) ? $params['visitor_availability'] : '';
		$visitor_preferred_contact = isset($params['visitor_preferred_contact']) ? $params['visitor_preferred_contact'] : '';
		$inquiry_type = isset($params['inquiry_type']) ? $params['inquiry_type'] : '0';

		$locations = ListingsLocations::DrawAllLocations(array('tag_name'=>'visitor_locations', 'tag_id'=>'id_visitor_locations', 'selected_value'=>$visitor_locations, 'javascript_event'=>'onchange="jQuery(\'#id_act\').val(\'location_reload\');jQuery(\'#frmInquiryForm\').submit();"'), false).' &nbsp;';
		$sub_locations = ListingsSubLocations::DrawAllSubLocations($visitor_locations, array('tag_name'=>'visitor_sub_locations', 'tag_id'=>'id_visitor_sub_locations', 'selected_value'=>$visitor_sub_locations), false);
		
		$output .= _DESCRIBE_WHAT_YOU_NEED.'<br>';
		$output .= '<textarea id="id_visitor_description" name="visitor_description" maxlength="2048">'.$visitor_description.'</textarea><br><br>';
		$output .= '<b>'._CONTACT_INFORMATION.'</b> <br>';
		$output .= '<div class="left_panel">
					<label>'._NAME.':</label> <input id="id_visitor_name" name="visitor_name" type="text" maxlength="50" autocomplete="off" value="'.(isset($params['visitor_name']) ? $params['visitor_name'] : '').'" /><br>
					<label>'._EMAIL.':</label> <input id="id_visitor_email" name="visitor_email" type="text" maxlength="70" autocomplete="off" value="'.(isset($params['visitor_email']) ? $params['visitor_email'] : '').'" /><br>
					<label>'._PHONE.':</label> <input id="id_visitor_phone" name="visitor_phone" type="text" maxlength="20" autocomplete="off" value="'.(isset($params['visitor_phone']) ? $params['visitor_phone'] : '').'" />
					</div>';
		$output .= '<div class="right_panel">
					<label>'._LOCATION.':</label> '.$locations.'<br>
					<label>'._SUB_LOCATION.':</label> '.$sub_locations.'<br>
					
					<label>'._I_AM_AVAILABLE.':</label> ';					
					$output .= '<select id="id_visitor_availability" name="visitor_availability">';
					foreach(self::$arr_availabilities as $key => $val){
						$output .= '<option value="'.$key.'"'.(($visitor_availability == $key) ? ' selected="selected"' : '').'>'.$val.'</option>';
					}
					$output .= '</select><br>';
					
					$output .= '<label>'._PREFERRED_TO_BE_CONTACTED.':</label> ';
					$output .= '<select id="id_visitor_preferred_contact" name="visitor_preferred_contact">';
					foreach(self::$arr_preferred_contacts as $key => $val){
						$output .= '<option value="'.$key.'"'.(($visitor_preferred_contact == $key) ? ' selected="selected"' : '').'>'.$val.'</option>';
					}
					$output .= '</select>';
					
					$output .= '
				</div>					
				<div style="margin:10px auto;text-align:center;">
					'.str_replace('_COUNT_', ($inquiry_type == 1) ? '1' : ModulesSettings::Get('inquiries', 'maximum_replies'), _INQUIRY_FORM_DISCLAIMER).'<br><br>
					<input type="submit" class="form_button" name="" value="'._SUBMIT.'">
				</div>';

		if(isset($params['focus_field']) && !empty($params['focus_field'])){
			$output .= '<script type="text/javascript">appSetFocus("id_'.$params['focus_field'].'");</script>';
		}
		
		if($draw) echo $output;
		else return $output;
		
	}
	
	/**
	 * Draws last inquiries side block 
	 * 		@param $draw
	 */
	public static function DrawLastInquiriesBlock($draw = false)
	{
		
		$output = draw_block_top(_INCOMING_JOBS, '', 'maximized', false);
		$max_inquiries = 10;
		
		$datetime_format = get_datetime_format();

		$result = self::GetAllInquiries('', 'i.date_created DESC', $max_inquiries);
		if($result[1] > 0){
			$output .= '<ul class="incoming_jobs">';
			for($i=0; $i < $result[1] && ($i < $max_inquiries); $i++){
				$output .= '<li>';
				$output .= (($result[0][$i]['inquiry_type'] == '1') ? $result[0][$i]['business_name'] : $result[0][$i]['category_name']).'<br>';
				$output .= '<div class="location">'.$result[0][$i]['location_name'].' <div class="date_created">'.date($datetime_format, strtotime($result[0][$i]['date_created'])).'</div></div>';
				$output .= '</li>';
			}
			$output .= '</ul>';			
		}else{
			$output .= _NO_INCOMMING_JOBS_YET;
		}
		$output .= draw_block_bottom(false);		

		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 * Returns inquiries array
	 * 		@param $where_clause
	 * 		@param $order_clause
	 * 		@param $limit
	 */
	public static function GetAllInquiries($where_clause = '', $order_clause = 'RAND() ASC', $limit = '')
	{
		$output = array('0'=>array(), '1'=>'0');
		
		$sql = 'SELECT
					i.*,									
					cd.name as category_name,
					ld.business_name as business_name,
					ll.name as location_name
				FROM '.TABLE_INQUIRIES.' i
					INNER JOIN '.TABLE_LISTINGS_LOCATIONS.' ll ON i.location_id = ll.id
					LEFT OUTER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd ON i.category_id = cd.category_id AND cd.language_id = \''.Application::Get('lang').'\'
					LEFT OUTER JOIN '.TABLE_LISTINGS_DESCRIPTION.' ld ON i.listing_id = ld.listing_id AND ld.language_id = \''.Application::Get('lang').'\'
				WHERE 1=1
					'.(($where_clause != '') ? ' AND '.$where_clause : '').'
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
	 * Checks whether customer is related to inquiry
	 * 		@param $inquiry_id
	 */
	public function CustomerRelatedToInquire($inquiry_id = 0)
	{
		$sql = $this->VIEW_MODE_SQL.' '.$this->WHERE_CLAUSE.' AND i.id = '.(int)$inquiry_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return ($result[1] > 0) ? true : false;
	}
	
	/**
	 * Remove very old inquiries
	 */
	static public function RemoveOld()
	{
		$keep_history_days = ModulesSettings::Get('inquiries', 'keep_history_days');
		
		$sql = 'DELETE FROM '.TABLE_INQUIRIES_REPLIES.'
				WHERE DATEDIFF(\''.date('Y-m-d H:i:s').'\', '.TABLE_INQUIRIES_REPLIES.'.date_added) > '.(int)$keep_history_days;
		database_void_query($sql);
		
		$sql = 'DELETE FROM '.TABLE_INQUIRIES_HISTORY.'
				WHERE DATEDIFF(\''.date('Y-m-d H:i:s').'\', '.TABLE_INQUIRIES_HISTORY.'.date_added) > '.(int)$keep_history_days;
		database_void_query($sql);

		$sql = 'DELETE FROM '.TABLE_INQUIRIES.'
				WHERE DATEDIFF(\''.date('Y-m-d H:i:s').'\', '.TABLE_INQUIRIES.'.date_created) > '.(int)$keep_history_days;
		database_void_query($sql);
	}	
}

?>