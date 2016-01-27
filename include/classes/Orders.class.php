<?php

/**
 *	Orders MicroGrid Class (for BusinessDirectory ONLY)
 *  -------------- 
 *	Written by  : ApPHP
 *  Updated	    : 06.07.2011
 *	Written by  : ApPHP
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             SendOrderEmail           
 *	__destruct              
 *	DrawOrderInvoice
 *	DrawOrderDescription    
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	BeforeDeleteRecord
 *	AfterDeleteRecord
 *	CleanCreditCardInfo
 *	UpdatePaymentDate
 *	SendInvoice
 *	
 **/


class Orders extends MicroGrid {
	
	protected $debug = false;
	
	private $page;
	private $customer_id;
	private $order_number;
	private $order_status;
	private $order_customer_id;
	private $order_listings;
	private $order_advertise_plan_id;
	private $currency_format;
	
	private $collect_credit_card;
	private $currencyFormat;
	
	//==========================================================================
    // Class Constructor
	// 		@param $customer_id
	//==========================================================================
	function __construct($customer_id = '')
	{
		global $objLogin;
		
		$this->SetRunningTime();
		
		$this->params = array();		
		if(isset($_POST['status']))   		   $this->params['status'] = prepare_input($_POST['status']);
		if(isset($_POST['status_changed']))    $this->params['status_changed'] = prepare_input($_POST['status_changed']);
		if(isset($_POST['additional_info']))   $this->params['additional_info'] = prepare_input($_POST['additional_info']);
		
		$this->currencyFormat = get_currency_format();		  

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
		$rid = MicroGrid::GetParameter('rid');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ORDERS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->order_number = '';
		$this->order_status = '';
		$this->order_customer_id = '';
		$this->order_listings = '';
		$this->order_advertise_plan_id = '';
		$arr_statuses          = array('0'=>_PREPARING, '1'=>_PENDING, '2'=>_PAID, '3'=>_COMPLETED, '4'=>_REFUNDED);
		$arr_statuses_edit     = array('1'=>_PENDING, '2'=>_PAID, '3'=>_COMPLETED, '4'=>_REFUNDED);
		$arr_statuses_edit_cut = array('1'=>_PENDING, '2'=>_PAID, '3'=>_COMPLETED);
		$arr_statuses_refund   = array('4'=>_REFUNDED);
		$arr_statuses_customer_edit = array('4'=>'');

		if($customer_id != ''){
			$this->customer_id = $customer_id;
			$this->page = 'customer=my_orders';
			$this->actions   = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>false);
		}else{			
			$this->customer_id = '';
			$this->page = 'admin=mod_payments_orders';
			$this->actions   = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>(($objLogin->IsLoggedInAs('owner')) ? true : false));
		}
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->formActionURL = 'index.php?'.$this->page;

		$this->allowLanguages = false;
		$this->languageId  	= ''; // ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE 1=1';
		if($customer_id != ''){
			$this->WHERE_CLAUSE = 'WHERE '.$this->tableName.'.status != 0 AND '.$this->tableName.'.customer_id = '.(int)$customer_id;
		}
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.id DESC'; // ORDER BY date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 30;

		$this->isSortingAllowed = true;

		$datetime_format = get_datetime_format();
		$date_format = get_date_format();
		$date_format_settings = get_date_format('view', true);
			
		$this->currency_format = get_currency_format();
		$pre_currency_symbol = ((Application::Get('currency_symbol_place') == 'left') ? Application::Get('currency_symbol') : '');
		$post_currency_symbol = ((Application::Get('currency_symbol_place') == 'right') ? Application::Get('currency_symbol') : '');

		$this->collect_credit_card = ModulesSettings::Get('payments', 'online_collect_credit_card');

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_ORDER_NUMBER => array('table'=>$this->tableName, 'field'=>'order_number', 'type'=>'text', 'sign'=>'like%', 'width'=>'70px'),
			_DATE 	      => array('table'=>$this->tableName, 'field'=>'payment_date', 'type'=>'calendar', 'date_format'=>$date_format_settings, 'sign'=>'like%', 'width'=>'80px', 'visible'=>true),
		);
		if($this->customer_id == ''){
			$this->arrFilteringFields[_CUSTOMER] = array('table'=>TABLE_CUSTOMERS, 'field'=>'user_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'70px');
		}
		$this->arrFilteringFields[_STATUS] = array('table'=>$this->tableName, 'field'=>'status', 'type'=>'dropdownlist', 'source'=>$arr_statuses_edit, 'sign'=>'=', 'width'=>'');


		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.order_number,
								'.$this->tableName.'.order_description,
								'.$this->tableName.'.order_price,
								'.$this->tableName.'.total_price,
								CONCAT('.TABLE_CURRENCIES.'.symbol, "", '.$this->tableName.'.total_price) as mod_total_price,
								'.$this->tableName.'.currency,
								'.$this->tableName.'.advertise_plan_id,
								'.$this->tableName.'.listings_amount,
								'.$this->tableName.'.customer_id,
								'.$this->tableName.'.transaction_number,
								'.$this->tableName.'.created_date,
								'.$this->tableName.'.payment_date,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								'.$this->tableName.'.status,
								'.$this->tableName.'.status_changed,
								CASE
									WHEN '.$this->tableName.'.payment_type = 0 THEN "'._ONLINE_ORDER.'"
									WHEN '.$this->tableName.'.payment_type = 1 THEN "'._PAYPAL.'"
									WHEN '.$this->tableName.'.payment_type = 2 THEN "2CO"
									WHEN '.$this->tableName.'.payment_type = 3 THEN "Authorize.Net"
									ELSE "'._UNKNOWN.'"
								END as m_payment_type,
								CASE
									WHEN '.$this->tableName.'.payment_method = 0 THEN "'._PAYMENT_COMPANY_ACCOUNT.'"
									WHEN '.$this->tableName.'.payment_method = 1 THEN "'._CREDIT_CARD.'"
									WHEN '.$this->tableName.'.payment_method = 2 THEN "E-Check"
									ELSE "'._UNKNOWN.'"
								END as m_payment_method,
								CASE
									WHEN '.$this->tableName.'.status = 0 THEN "<span style=color:#960000>'._PREPARING.'</span>"
									WHEN '.$this->tableName.'.status = 1 THEN "<span style=color:#FF9966>'._PENDING.'</span>"
									WHEN '.$this->tableName.'.status = 2 THEN "<span style=color:#336699>'._PAID.'</span>"
									WHEN '.$this->tableName.'.status = 3 THEN "<span style=color:#009600>'._COMPLETED.'</span>"
									WHEN '.$this->tableName.'.status = 4 THEN "<span style=color:#969600>'._REFUNDED.'</span>"
									ELSE "'._UNKNOWN.'"
								END as m_status,
								'.TABLE_CUSTOMERS.'.user_name as customer_name,
								'.TABLE_CURRENCIES.'.symbol,
								CONCAT("<a href=\"javascript:void(\'description\')\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'description\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._DESCRIPTION.'", " ]</a>") as link_order_description,								
								IF('.$this->tableName.'.status >= 2, CONCAT("<a href=\"javascript:void(\'invoice\')\" onclick=\"javascript:__mgDoPostBack(\''.$this->tableName.'\', \'invoice\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\')\">[ ", "'._INVOICE.'", " ]</a>"), "<span class=lightgray>'._INVOICE.'</span>") as link_order_invoice,
								IF('.$this->tableName.'.status = 0 OR '.$this->tableName.'.status = 1, CONCAT("<a href=\"javascript:void(0);\" title=\"Delete\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_ORDERS.'\', \'delete\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._DELETE_WORD.' ]</a>"), "<span class=lightgray>'._DELETE_WORD.'</span>") as link_order_delete,
								IF('.$this->tableName.'.status != 0, CONCAT("<a href=\"javascript:void(0);\" title=\"'._EDIT_WORD.'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_ORDERS.'\', \'edit\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._EDIT_WORD.' ]</a>"), "<span class=lightgray>'._EDIT_WORD.'</span>") as link_admin_order_edit,
								'.TABLE_ADVERTISE_PLANS_DESCRIPTION.'.name as plan_name,
								'.TABLE_CUSTOMERS.'.b_country
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
								LEFT OUTER JOIN '.TABLE_ADVERTISE_PLANS_DESCRIPTION.' ON ('.$this->tableName.'.advertise_plan_id = '.TABLE_ADVERTISE_PLANS_DESCRIPTION.'.advertise_plan_id AND language_id = "'.Application::Get('lang').'")
							';		

		// define view mode fields
		if($this->customer_id != ''){
			$this->arrViewModeFields = array(
				'order_number'    => array('title'=>_ORDER_NUMBER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'created_date'    => array('title'=>_DATE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>'', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'plan_name'       => array('title'=>_PLAN, 'header_tooltip'=>_ADVERTISE_PLAN, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'listings_amount' => array('title'=>_LISTINGS, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'mod_total_price'     => array('title'=>_TOTAL_PRICE, 'type'=>'label', 'align'=>'right', 'width'=>'', 'header'=>'', 'maxlength'=>'', 'sort_by'=>'total_price', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currency_format.'|2'),
				//'symbol'          => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'm_status' 		  => array('title'=>_STATUS, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_order_description' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_order_invoice'     => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_order_delete'      => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
			);			
		}else{
			$this->arrViewModeFields = array(
				'order_number'    => array('title'=>_ORDER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'created_date'    => array('title'=>_DATE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>'', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'customer_name'   => array('title'=>_CUSTOMER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'b_country'       => array('title'=>_COUNTRY, 'type'=>'label', 'align'=>'center', 'width'=>'', 'height'=>'', 'maxlength'=>''),
				'plan_name'       => array('title'=>_PLAN, 'header_tooltip'=>_ADVERTISE_PLAN, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'listings_amount' => array('title'=>_LISTINGS, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'm_payment_type'  => array('title'=>_METHOD, 'header_tooltip'=>_PAYMENT_METHOD, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'total_price'     => array('title'=>_TOTAL_PRICE, 'type'=>'label', 'align'=>'right', 'width'=>'', 'header'=>'', 'maxlength'=>'', 'sort_by'=>'total_price', 'sort_type'=>'numeric', 'format'=>'currency', 'format_parameter'=>$this->currency_format.'|2'),
				'symbol'          => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'm_status' 		  => array('title'=>_STATUS, 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_order_description' => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_order_invoice'     => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
				'link_admin_order_edit'  => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'', 'header'=>'', 'maxlength'=>''),
			);						
		}
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(

		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.order_number,
								'.$this->tableName.'.order_number as order_number_view,
								'.$this->tableName.'.order_description,
								'.$this->tableName.'.order_price,
								'.$this->tableName.'.vat_fee,
								'.$this->tableName.'.total_price,
								'.$this->tableName.'.currency,
								'.$this->tableName.'.advertise_plan_id,
								'.$this->tableName.'.listings_amount,
								'.$this->tableName.'.customer_id,								
								'.$this->tableName.'.cc_type,
								'.$this->tableName.'.cc_holder_name,
								IF(
									LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")) = 4,
									CONCAT("...", AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'"), " ('._CLEANED.')"),
									AES_DECRYPT('.$this->tableName.'.cc_number, "'.PASSWORDS_ENCRYPT_KEY.'")
								) as m_cc_number,								
								'.$this->tableName.'.cc_cvv_code,
								'.$this->tableName.'.cc_expires_month,
								'.$this->tableName.'.cc_expires_year,
								IF('.$this->tableName.'.cc_expires_month != "", CONCAT('.$this->tableName.'.cc_expires_month, "/", '.$this->tableName.'.cc_expires_year), "") as m_cc_expires_date,
								'.$this->tableName.'.transaction_number,
								'.$this->tableName.'.payment_date,
								'.$this->tableName.'.payment_type,
								'.$this->tableName.'.payment_method,
								'.$this->tableName.'.status,
								'.$this->tableName.'.status_changed,
								'.$this->tableName.'.additional_info
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
								LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
							';		

		if($this->customer_id != ''){
			$WHERE_CLAUSE = 'WHERE '.$this->tableName.'.status = 3 AND
								   '.$this->tableName.'.customer_id = '.$this->customer_id.' AND
			                       '.$this->tableName.'.id = _RID_';
		}else{
			$WHERE_CLAUSE = 'WHERE '.$this->tableName.'.id = _RID_';
		}
		$this->EDIT_MODE_SQL = $this->EDIT_MODE_SQL.$WHERE_CLAUSE;

		// prepare trigger
		$sql = 'SELECT
		            status,
					IF(TRIM(cc_number) = \'\' OR LENGTH(AES_DECRYPT(cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')) <= 4, \'hide\', \'show\') as cc_number_trigger
				FROM '.$this->tableName.' WHERE id = '.(int)$rid;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY, FETCH_ASSOC);
		if($result[1] > 0){
			$cc_number_trigger = $result[0]['cc_number_trigger'];
			$status_trigger = $result[0]['status'];
		}else{
			$cc_number_trigger = 'hide';
			$status_trigger = '0';
		}		

		// define edit mode fields
		if($customer_id != ''){
			$this->arrEditModeFields = array(
				'order_number_view' => array('title'=>_ORDER_NUMBER, 'type'=>'label'),
				'status_changed'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
				'status'  		    => array('title'=>_STATUS, 'type'=>'enum', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'source'=>$arr_statuses_customer_edit),
				'order_number'      => array('title'=>'', 'type'=>'hidden',   'required'=>false, 'default'=>''),
				'customer_id'       => array('title'=>'', 'type'=>'hidden',   'required'=>false, 'default'=>''),
			);
		}else{
			$status_readonly = ($status_trigger == '6') ? true : false;
			if($status_trigger >= '2' && $status_trigger <= '6'){
				$ind = $status_trigger;
				while($ind--) unset($arr_statuses_edit[$ind]);
				$status_source = $arr_statuses_edit;
			}else{
				$status_source = $arr_statuses_edit_cut;
			}			
			
			$this->arrEditModeFields = array(
				'order_number_view' => array('title'=>_ORDER_NUMBER, 'type'=>'label'),
				'status_changed'    => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
				'status'  		    => array('title'=>_STATUS, 'type'=>'enum', 'width'=>'210px', 'required'=>true, 'readonly'=>$status_readonly, 'source'=>$status_source, 'javascript_event'=>''),
				'order_number'      => array('title'=>'', 'type'=>'hidden',   'required'=>false, 'default'=>''),
				'customer_id'       => array('title'=>'', 'type'=>'hidden',   'required'=>false, 'default'=>''),

				'cc_type' 			=> array('title'=>_CREDIT_CARD_TYPE, 'type'=>'label'),
				'cc_holder_name' 	=> array('title'=>_CREDIT_CARD_HOLDER_NAME, 'type'=>'label'),
				'm_cc_number' 		=> array('title'=>_CREDIT_CARD_NUMBER, 'type'=>'label', 'post_html'=>(($cc_number_trigger == 'show') ? '&nbsp;[ <a href="javascript:void(0);" onclick="if(confirm(\''._PERFORM_OPERATION_COMMON_ALERT.'\')) __mgDoPostBack(\''.$this->tableName.'\',\'clean_credit_card\',\''.$rid.'\')">'._REMOVE.'</a> ]' : '')),
				'm_cc_expires_date' => array('title'=>_EXPIRES, 'type'=>'label'),
				'cc_cvv_code' 		=> array('title'=>_CVV_CODE, 'type'=>'label'),
				'additional_info' 	=> array('title'=>_ADDITIONAL_INFO, 'type'=>'textarea', 'width'=>'390px', 'header'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'unique'=>false),
			);
		}
		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------		
		$this->DETAILS_MODE_SQL = $this->VIEW_MODE_SQL.$WHERE_CLAUSE;
		$this->arrDetailsModeFields = array(

			'order_number'  	 => array('title'=>_ORDER, 'type'=>'label'),
			'order_description'  => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'order_price'  		 => array('title'=>_ORDER_PRICE, 'type'=>'label'),
			'vat_fee'       	 => array('title'=>_VAT, 'type'=>'label'),
			'total_price'  		 => array('title'=>_TOTAL_PRICE, 'type'=>'label'),
			'currency'  		 => array('title'=>_CURRENCY, 'type'=>'label'),
			'listings_amount'    => array('title'=>_LISTINGS, 'type'=>'label'),
			'customer_name'      => array('title'=>_CUSTOMER, 'type'=>'label'),
			'transaction_number' => array('title'=>_TRANSACTION, 'type'=>'label'),
			'payment_date'  	 => array('title'=>_DATE, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
			'm_payment_type'     => array('title'=>_PAYED_BY, 'type'=>'label'),
			'm_payment_method'   => array('title'=>_PAYMENT_METHOD, 'type'=>'label'),
			//'coupon_number'  	 => array('title'=>'', 'type'=>'label'),
			//'discount_campaign_id' => array('title'=>'', 'type'=>'label'),
			'm_status'  	     => array('title'=>_STATUS, 'type'=>'label'),
			'status_changed'     => array('title'=>_STATUS_CHANGED, 'type'=>'label', 'format'=>'date', 'format_parameter'=>$datetime_format),
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
	 *	Draws order invoice
	 * 		@param $rid
	 * 		@param $draw
	 */
	public function DrawOrderInvoice($rid, $text_only = false, $draw = true)
	{
		global $objSiteDescription;
		global $objSettings;
		
		$oid = isset($rid) ? (int)$rid : '0';
		$language_id = Languages::GetDefaultLang();
		$output = '';
		$content = '';
		
		$sql = 'SELECT
					'.$this->tableName.'.*,
					CASE
						WHEN '.$this->tableName.'.payment_type = 0 THEN \''._ONLINE_ORDER.'\'
						WHEN '.$this->tableName.'.payment_type = 1 THEN \''._PAYPAL.'\'
						WHEN '.$this->tableName.'.payment_type = 2 THEN \'2CO\'
						WHEN '.$this->tableName.'.payment_type = 3 THEN \'Authorize.Net\'
						ELSE \''._UNKNOWN.'\'
					END as m_payment_type,
					CASE
						WHEN '.$this->tableName.'.payment_method = 0 THEN \''._PAYMENT_COMPANY_ACCOUNT.'\'
						WHEN '.$this->tableName.'.payment_method = 1 THEN \''._CREDIT_CARD.'\'
						WHEN '.$this->tableName.'.payment_method = 2 THEN \'E-Check\'
						ELSE \''._UNKNOWN.'\'
					END as m_payment_method,
					CASE
						WHEN '.$this->tableName.'.status = 0 THEN \'<span style=color:#960000>'._PREPARING.'</span>\'
						WHEN '.$this->tableName.'.status = 1 THEN \'<span style=color:#FF9966>'._PENDING.'</span>\'
						WHEN '.$this->tableName.'.status = 2 THEN \'<span style=color:#336699>'._PAID.'</span>\'
						WHEN '.$this->tableName.'.status = 3 THEN \'<span style=color:#009600>'._COMPLETED.'</span>\'
						ELSE \''._UNKNOWN.'\'
					END as m_status,
					IF('.$this->tableName.'.status_changed = \'0000-00-00 00:00:00\', \'\', '.$this->tableName.'.status_changed) as status_changed,
					cust.user_name as customer_name,
					'.$this->tableName.'.order_price,
					'.$this->tableName.'.vat_fee,
					'.$this->tableName.'.total_price,
					cur.symbol,
					cur.symbol_placement,
					cust.first_name,
					cust.last_name,					
					cust.email as customer_email,
					cust.company as customer_company,
					cust.b_address,
					cust.b_address_2,
					cust.b_city,
					cust.b_state,
					cust.b_zipcode, 
					cntr.name as country_name
				FROM '.$this->tableName.'
					LEFT OUTER JOIN '.TABLE_CURRENCIES.' cur ON '.$this->tableName.'.currency = cur.code
					LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cust ON '.$this->tableName.'.customer_id = cust.id
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' cntr ON cust.b_country = cntr.abbrv 
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;

				if($this->customer_id != ''){
					$sql .=  ' AND '.$this->tableName.'.customer_id = '.(int)$this->customer_id;
				}
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY, FETCH_ASSOC);
		if($result[1] > 0){

			$part = '<table width="100%" dir="'.Application::Get('lang_dir').'" border="0">';
			if($text_only && ModulesSettings::Get('payments', 'mode') == 'TEST MODE'){
				$part .= '<tr><td colspan="2"><div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div></td></tr>';
			}
			$part .= '<tr>';
			$part .= '<td valign="top">
						<h3>'._CUSTOMER_DETAILS.'</h3>
						'._FIRST_NAME.': '.$result[0]['first_name'].'<br />
						'._LAST_NAME.': '.$result[0]['last_name'].'<br />
						'._EMAIL_ADDRESS.': '.$result[0]['customer_email'].'<br />
						'._COMPANY.': '.$result[0]['customer_company'].'<br />
						'._ADDRESS.': '.$result[0]['b_address'].' '.$result[0]['b_address_2'].'<br />
						'.$result[0]['b_city'].' '.$result[0]['b_state'].'<br />
						'.$result[0]['country_name'].' '.$result[0]['b_zipcode'].'						
					</td>
					<td valign="top" align="right">
						<h3>'._COMPANY.': '.$objSiteDescription->GetParameter('header_text').'</h3>
						'._EMAIL_ADDRESS.': '.$objSettings->GetParameter('admin_email').'<br />
						'._DATE_CREATED.': '.format_datetime($result[0]['payment_date']).'<br />
					</td>
				</tr>
				</table><br />';
				
			$plan_info = AdvertisePlans::GetPlanInfo($result[0]['advertise_plan_id']);
			$part .= '<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border:1px solid #d1d2d3">';
			$part .= '<tr style="background-color:#e1e2e3;font-weight:bold;font-size:13px;"><th align="left" colspan="2">&nbsp;<b>'._ORDER_DETAILS.'</b></th></tr>';
			$part .= '<tr><td width="25%">'._ORDER.': </td><td>'.$result[0]['order_number'].'</td></tr>';
			$part .= '<tr><td>'._DESCRIPTION.': </td><td>'.$result[0]['order_description'].'</td></tr>';
			$part .= '<tr><td nowrap="nowrap">'._ADVERTISE_PLAN.': </td><td>'.((isset($plan_info[0]['plan_name'])) ? $plan_info[0]['plan_name'] : '').'</td></tr>';
			$part .= '<tr><td>'._LISTINGS.': </td><td>'.$result[0]['listings_amount'].'</td></tr>';
			$part .= '<tr><td>'._TRANSACTION.': </td><td>'.$result[0]['transaction_number'].'</td></tr>';
			$part .= '<tr><td>'._ORDER_DATE.': </td><td>'.format_datetime($result[0]['created_date']).'</td></tr>';
			$part .= '<tr><td>'._PAYED_BY.': </td><td>'.$result[0]['m_payment_type'].'</td></tr>';
			$part .= '<tr><td>'._PAYMENT_METHOD.': </td><td>'.$result[0]['m_payment_method'].'</td></tr>';
			$part .= '<tr><td>'._ORDER_PRICE.': </td><td>'.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).'</td></tr>';
			$part .= '<tr><td>'._VAT.': </td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).' ('.$result[0]['vat_percent'].'%)</td></tr>';
			$part .= '<tr><td>'._TOTAL.': </td><td>'.Currencies::PriceFormat($result[0]['total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).'</td></tr>';
			//if($result[0]['campaign_name'] != '') $part .= '<tr><td>'._DISCOUNT_CAMPAIGN.': </td><td>'.$result[0]['campaign_name'].' ('.$result[0]['discount_percent'].'%)</td></tr>';
			$part .= '</table><br />';									

			$content = @file_get_contents('html/templates/invoice.tpl');
			if($content){
				$content = str_replace('_TOP_PART_', $part, $content);
				$content = str_replace('_YOUR_COMPANY_NAME_', $objSiteDescription->GetParameter('header_text'), $content);
				$content = str_replace('_ADMIN_EMAIL_', $objSettings->GetParameter('admin_email'), $content);
			}
		}
		$output .= '<div id="divInvoiceContent">'.$content.'</div>';
		if(!$text_only){
			$output .= '<table width="100%" border="0">';
			$output .= '<tr><td colspan="2">&nbsp;</tr>';
			$output .= '<tr>';
			$output .= '  <td colspan="2" align="left"><input type="button" class="mgrid_button" name="btnBack" value="'._BUTTON_BACK.'" onclick="javascript:window.location.href=\'index.php?'.$this->page.'\';"></td>';
			$output .= '</tr>';			
			$output .= '</table>';
		}
		
		if($draw) echo $output;
		else return $output;
	}
	
	/**
	 *	Draws order description	
	 * 		@param $rid
	 */
	public function DrawOrderDescription($rid)
	{
		$output = '';
		$oid = isset($rid) ? (int)$rid : '0';
		$language_id = Languages::GetDefaultLang();
	
		$sql = 'SELECT
					'.$this->tableName.'.'.$this->primaryKey.',
					'.$this->tableName.'.order_number,
					'.$this->tableName.'.order_description,
					'.$this->tableName.'.order_price,
					'.$this->tableName.'.vat_percent,
					'.$this->tableName.'.vat_fee,
					'.$this->tableName.'.total_price,
					'.$this->tableName.'.additional_info,
					'.$this->tableName.'.currency,
					'.$this->tableName.'.advertise_plan_id,
					'.$this->tableName.'.listings_amount,
					'.$this->tableName.'.customer_id,
					'.$this->tableName.'.cc_type,
					'.$this->tableName.'.cc_holder_name,
					IF(
						LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')) = 4,
						CONCAT(\'...\', AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')),
						AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')
					) as cc_number,								
					CONCAT(\'...\', SUBSTRING(AES_DECRYPT(cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\'), -4)) as cc_number_for_customer,								
					IF(
						LENGTH(AES_DECRYPT('.$this->tableName.'.cc_number, \''.PASSWORDS_ENCRYPT_KEY.'\')) = 4,
						\' ('._CLEANED.')\',
						\'\'
					) as cc_number_cleaned,								
					'.$this->tableName.'.cc_expires_month,
					'.$this->tableName.'.cc_expires_year,
					'.$this->tableName.'.cc_cvv_code, 
					'.$this->tableName.'.transaction_number,
					'.$this->tableName.'.created_date,
					'.$this->tableName.'.payment_date,
					'.$this->tableName.'.payment_type,
					'.$this->tableName.'.payment_method,
					CASE
						WHEN '.$this->tableName.'.payment_type = 0 THEN "'._ONLINE_ORDER.'"
						WHEN '.$this->tableName.'.payment_type = 1 THEN "'._PAYPAL.'"
						WHEN '.$this->tableName.'.payment_type = 2 THEN "2CO"
						WHEN '.$this->tableName.'.payment_type = 3 THEN "Authorize.Net"
						ELSE \''._UNKNOWN.'\'
					END as m_payment_type,
					CASE
						WHEN '.$this->tableName.'.payment_method = 0 THEN "'._PAYMENT_COMPANY_ACCOUNT.'"
						WHEN '.$this->tableName.'.payment_method = 1 THEN "'._CREDIT_CARD.'"
						WHEN '.$this->tableName.'.payment_method = 2 THEN "E-Check"
						ELSE \''._UNKNOWN.'\'
					END as m_payment_method,
					CASE
						WHEN '.$this->tableName.'.status = 0 THEN "<span style=color:#960000>'._PREPARING.'</span>"
						WHEN '.$this->tableName.'.status = 1 THEN "<span style=color:#FF9966>'._PENDING.'</span>"
						WHEN '.$this->tableName.'.status = 2 THEN "<span style=color:#336699>'._PAID.'</span>"
						WHEN '.$this->tableName.'.status = 3 THEN "<span style=color:#009600>'._COMPLETED.'</span>"
						ELSE \''._UNKNOWN.'\'
					END as m_status,
					IF('.$this->tableName.'.status_changed = "0000-00-00 00:00:00", "", '.$this->tableName.'.status_changed) as status_changed,
					cust.user_name as customer_name,
					cur.symbol,
					cur.symbol_placement
				FROM '.$this->tableName.'
					LEFT OUTER JOIN '.TABLE_CURRENCIES.' cur ON '.$this->tableName.'.currency = cur.code
					LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cust ON '.$this->tableName.'.customer_id = cust.id
				WHERE
					'.$this->tableName.'.'.$this->primaryKey.' = '.(int)$oid;
				if($this->customer_id != ''){
					$sql .=  ' AND '.$this->tableName.'.customer_id = '.(int)$this->customer_id;
				}

				//camp.campaign_name,
				//camp.discount_percent 
				//LEFT OUTER JOIN ".TABLE_CAMPAIGNS." camp ON ".$this->tableName.".discount_campaign_id = camp.id
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY, FETCH_ASSOC);
		$output .= '<div id="divDescriptionContent">';
		if($result[1] > 0){
			$plan_info = AdvertisePlans::GetPlanInfo($result[0]['advertise_plan_id']);
			
			$output .= '<table width="100%" dir="'.Application::Get('lang_dir').'" border="0">';
			$output .= '<tr>
							<td width="20%"><b>'._ORDER.' #: </b></td><td width="30%">'.$result[0]['order_number'].'</td>
							<td><b>'._STATUS.': </b></td><td>'.$result[0]['m_status'].'</td>
						</tr>';
			$output .= '<tr>
							<td><b>'._DESCRIPTION.': </b></td><td>'.$result[0]['order_description'].'</td>
							<td><b>'._STATUS_CHANGED.': </b></td><td>'.format_datetime($result[0]['status_changed']).'</td>
						</tr>';
			$output .= '<tr>
							<td><b>'._ADVERTISE_PLAN.': </b></td><td>'.((isset($plan_info[0]['plan_name'])) ? $plan_info[0]['plan_name'] : '').'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._LISTINGS.': </b></td><td>'.$result[0]['listings_amount'].'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._ORDER_DATE.': </b></td><td>'.format_datetime($result[0]['created_date']).'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._PAYED_BY.': </b></td><td>'.$result[0]['m_payment_type'].'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._PAYMENT_METHOD.': </b></td><td>'.$result[0]['m_payment_method'].'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._TRANSACTION.' #: </b></td><td>'.$result[0]['transaction_number'].'</td>
							<td colspan="2"></td>
						</tr>';
			$output .= '<tr>
							<td><b>'._ORDER_PRICE.': </b></td><td>'.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).'</td>
							<td colspan="2" rowspan="4" valign="top">
								<b>'._ADDITIONAL_INFO.'</b>:<br />
								'.(($result[0]['additional_info'] != '') ? $result[0]['additional_info'] : '--').'
							</td>							
						</tr>';
			$output .= '<tr><td><b>'._VAT.': </b></td><td>'.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).' ('.$result[0]['vat_percent'].'%)</td></tr>';
			$output .= '<tr><td><b>'._TOTAL_PRICE.': </b></td><td>'.Currencies::PriceFormat($result[0]['total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currency_format).'</td></tr>';
			//if($result[0]['campaign_name'] != '') $output .= '<tr><td><b>'._DISCOUNT_CAMPAIGN.': </b></td><td>'.$result[0]['campaign_name'].' ('.$result[0]['discount_percent'].'%)</td><td colspan='2'></td></tr>';
			if($this->customer_id == '') $output .= '<tr><td><b>'._CUSTOMER.': </b></td><td>'.$result[0]['customer_name'].'</td><td colspan="2"></td></tr>';
			if($result[0]['payment_type'] == '0'){
				// always show cc info, even if collecting is not requieed
				// $this->collect_credit_card == 'yes' 
				$output .= '<tr><td colspan="4"></td></tr>';
				$output .= '<tr><td><b>'._CREDIT_CARD_TYPE.': </b></td><td>'.$result[0]['cc_type'].'</td></tr>';
				$output .= '<tr><td><b>'._CREDIT_CARD_HOLDER_NAME.': </b></td><td>'.$result[0]['cc_holder_name'].'</td></tr>';
				if($this->customer_id == ''){
					$output .= '<tr><td><b>'._CREDIT_CARD_NUMBER.': </b></td><td>'.$result[0]['cc_number'].$result[0]['cc_number_cleaned'].'</td></tr>';
					$output .= '<tr><td><b>'._EXPIRES.': </b></td><td>'.(($result[0]['cc_expires_month'] != '') ? $result[0]['cc_expires_month'].'/'.$result[0]['cc_expires_year'] : '').'</td></tr>';
					$output .= '<tr><td><b>'._CVV_CODE.': </b></td><td>'.$result[0]['cc_cvv_code'].'</td></tr>';				
				}else{
					$output .= '<tr><td><b>'._CREDIT_CARD_NUMBER.': </b></td><td>'.$result[0]['cc_number_for_customer'].'</td></tr>';
				}
			}
			$output .= '<tr><td colspan="4">&nbsp;</tr>';
			$output .= '</table>';			
		}
		
		$output .= '</div>';
		
		$output .= '<table width="100%" border="0">';
		$output .= '<tr><td colspan="2">&nbsp;</tr>';
		$output .= '<tr>';
		$output .= '  <td colspan="2" align="left"><input type="button" class="mgrid_button" name="btnBack" value="'._BUTTON_BACK.'" onclick="javascript:window.location.href=\'index.php?'.$this->page.'\';"></td>';
		$output .= '</tr>';			
		$output .= '</table>';
		
		echo $output;
	}

	/**
	 *	Before-Update record
	 */
	public function BeforeUpdateRecord()
	{
		global $objLogin;
		
	 	// $this->curRecordId;
		if($objLogin->IsLoggedInAsAdmin()){			
			$result = $this->GetInfoByID($this->curRecordId);
			
			$this->order_customer_id = isset($result['customer_id']) ? (int)$result['customer_id'] : '';		
			$this->order_status = isset($result['status']) ? (int)$result['status'] : '';		
			$this->order_listings = isset($result['listings_amount']) ? (int)$result['listings_amount'] : '';		
			$this->order_advertise_plan_id = isset($result['advertise_plan_id']) ? (int)$result['advertise_plan_id'] : '';		
		}
	   	return true;
	}

	/**
	 *	After-Ipdate record
	 */
	public function AfterUpdateRecord()
	{
		global $objLogin;
		
		$sql = '';

		// $this->curRecordId - currently updated record
		if($objLogin->IsLoggedInAsAdmin()){
			if($this->order_status == '1' && in_array($this->params['status'], array('2', '3'))){
				// update orders/listings count for customer if it changed from pending -> paid/completed
				Customers::SetOrdersForCustomer($this->order_customer_id, '+');
				Customers::SetListingsForCustomer($this->order_customer_id, $this->order_advertise_plan_id, $this->order_listings, '+');
			}else if(in_array($this->order_status, array('1', '2', '3')) && $this->params['status'] == '4'){
				// update orders/listings count for customer if it changed from pending/paid/completed -> refunded
				Customers::SetListingsForCustomer($this->order_customer_id, $this->order_advertise_plan_id, $this->order_listings, '-');
			}
		}
		if(!empty($sql)) database_void_query($sql);
	}

	/**
	 *	Before-Deleting record
	 */
	public function BeforeDeleteRecord()
	{
	   // update products count field
	   $oid = MicroGrid::GetParameter('rid');
	   $sql = 'SELECT order_number, status, customer_id, advertise_plan_id, listings_amount FROM '.TABLE_ORDERS.' WHERE id = '.(int)$oid;		
	   $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY, FETCH_ASSOC);
	   if($result[1] > 0){
		   $this->order_number = $result[0]['order_number'];
		   $this->order_status = $result[0]['status'];
		   $this->order_customer_id = $result[0]['customer_id'];
		   $this->order_listings = $result[0]['listings_amount'];
		   $this->order_advertise_plan_id = $result[0]['advertise_plan_id'];
		   return true;
	   }
	   return false;
	}
	
	/**
	 *	After-Delete record
	 */
	public function AfterDeleteRecord()
	{
	    // $this->curRecordId - currently deleted record
		if($this->order_status == 2 || $this->order_status == 3 || $this->order_status == 4){
			// update customer orders/listings amount
			Customers::SetOrdersForCustomer($this->order_customer_id, '-');
			if($this->order_status == 2 || $this->order_status == 3){
				Customers::SetListingsForCustomer($this->order_customer_id, $this->order_advertise_plan_id, $this->order_listings, '-');	
			}
		}
	}
	
	/**
	 *	Cleans credit card info
	 * 		@param $rid
	 */
	public function CleanCreditCardInfo($rid)
	{
		$sql = 'UPDATE '.$this->tableName.'
				SET
					cc_number = AES_ENCRYPT(SUBSTRING(AES_DECRYPT(cc_number, "'.PASSWORDS_ENCRYPT_KEY.'"), -4), "'.PASSWORDS_ENCRYPT_KEY.'"),
					cc_cvv_code = "",
					cc_expires_month = "",
					cc_expires_year = ""
				WHERE '.$this->primaryKey.'='.(int)$rid;
		return database_void_query($sql);		
	}
	
	/**
	 *	Update Payment Date
	 * 		@param $rid
	 */
	public function UpdatePaymentDate($rid)
	{
		$sql = 'UPDATE '.$this->tableName.'
				SET payment_date = "'.date('Y-m-d H:i:s').'"
				WHERE
					'.$this->primaryKey.'='.(int)$rid.' AND 
					(status = 2 OR status = 3 OR status = 4) AND
					(payment_date = "" OR payment_date = "0000-00-00")';
		database_void_query($sql);		
	}
	
	/**
	 * Send invoice to customer
	 * 		@param $rid
	 */
	public function SendInvoice($rid)
	{
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}
		
        global $objSettings;
		
		$sql = 'SELECT
					c.email,
					c.preferred_language
				FROM '.TABLE_ORDERS.' o
					INNER JOIN '.TABLE_CUSTOMERS.' c ON o.customer_id = c.id
				WHERE 1=1 OR o.id = '.(int)$rid;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$recipient = $result[0]['email'];
			$preferred_language = $result[0]['preferred_language'];
			$sender    = $objSettings->GetParameter('admin_email');			
			$subject   = _INVOICE.' #'.$rid;
			$body      = $this->DrawOrderInvoice($rid, true, false);
			//$body      = str_replace('<br />', '', $body);
			
			send_email_wo_template(
				$recipient,
				$sender,
				$subject,
				$body,
				$preferred_language
			);
			return true;
		}
        $this->error = _EMAILS_SENT_ERROR;
		return false;		
	}

	/**
	 * Sends order mail
	 * 		@param $order_number
	 * 		@param $order_type
	 * 		@param $customer_id
	 */
	public static function SendOrderEmail($order_number, $order_type = 'accepted', $customer_id = '')
	{		
		global $objSettings;
		
		$currencyFormat = get_currency_format();
		$order_details = '';
		
		// send email to customer
		$sql = 'SELECT 
					o.*,
					CASE
						WHEN o.payment_type = 0 THEN "'._ONLINE_ORDER.'"
						WHEN o.payment_type = 1 THEN "'._PAYPAL.'"
						WHEN o.payment_type = 2 THEN "2CO"
						WHEN o.payment_type = 3 THEN "Authorize.Net"
						ELSE "'._UNKNOWN.'"
					END as m_payment_type,
					CASE
						WHEN o.payment_method = 0 THEN "'._PAYMENT_COMPANY_ACCOUNT.'"
						WHEN o.payment_method = 1 THEN "'._CREDIT_CARD.'"
						WHEN o.payment_method = 2 THEN "E-Check"
						ELSE "'._UNKNOWN.'"
					END as m_payment_method,			
					CASE
						WHEN o.status = 0 THEN "<span style=color:#960000>'._PREPARING.'</span>"
						WHEN o.status = 1 THEN "<span style=color:#FF9966>'._PENDING.'</span>"
						WHEN o.status = 2 THEN "<span style=color:#336699>'._PAID.'</span>"
						WHEN o.status = 3 THEN "<span style=color:#009600>'._COMPLETED.'</span>"
						WHEN o.status = 4 THEN "<span style=color:#969600>'._REFUNDED.'</span>"
						ELSE "'._UNKNOWN.'"
					END as m_status,			
					c.first_name,
					c.last_name,
					c.user_name as customer_name,
					c.preferred_language,
					c.email,
					c.b_address,
					c.b_address_2,
					c.b_city,
					c.b_state,
					count.name as b_country,
					c.b_zipcode, 
					c.phone,
					c.fax,
					cur.symbol,
					cur.symbol_placement
		FROM '.TABLE_ORDERS.' o
			LEFT OUTER JOIN '.TABLE_CURRENCIES.' cur ON o.currency = cur.code
			LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON o.customer_id = c.id
			LEFT OUTER JOIN '.TABLE_COUNTRIES.' count ON c.b_country = count.abbrv 
		WHERE
			o.customer_id = '.(int)$customer_id.' AND
			o.order_number = "'.$order_number.'"';
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){					
			$plan_info = AdvertisePlans::GetPlanInfo($result[0]['advertise_plan_id']);
			
			if(ModulesSettings::Get('payments', 'mode') == 'TEST MODE'){
				$order_details .= '<div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div><br />';	
			}			
			
			// Personal Info
			$order_details .= '<b>'._PERSONAL_INFORMATION.':</b><br />';
			$order_details .= _FIRST_NAME.' : '.$result[0]['first_name'].'<br />';
			$order_details .= _LAST_NAME.' : '.$result[0]['last_name'].'<br />';
			$order_details .= _EMAIL_ADDRESS.' : '.$result[0]['email'].'<br />';
			$order_details .= '<br />';

			// Billing Info
			$order_details .= '<b>'._BILLING_INFORMATION.':</b><br />';
			$order_details .= _ADDRESS.': '.$result[0]['b_address'].'<br />';
			$order_details .= _ADDRESS_2.': '.$result[0]['b_address_2'].'<br />';
			$order_details .= _CITY.': '.$result[0]['b_city'].'<br />';
			$order_details .= _STATE_PROVINCE.': '.$result[0]['b_state'].'<br />';						
			$order_details .= _COUNTRY.': '.$result[0]['b_country'].'<br />';
			$order_details .= _ZIP_CODE.': '.$result[0]['b_zipcode'].'<br />';
			if(!empty($result[0]['phone'])) $order_details .= _PHONE.' : '.$result[0]['phone'].'<br />';
			if(!empty($result[0]['fax'])) $order_details .= _FAX.' : '.$result[0]['fax'].'<br />';			
			$order_details .= '<br />';
			
			// Order Details
			$order_details .= '<b>'._ORDER_DETAILS.':</b><br />';
			$order_details .= _ORDER_DESCRIPTION.': '.$result[0]['order_description'].'<br />';
			$order_details .= _ADVERTISE_PLAN.': '.((isset($plan_info[0]['plan_name'])) ? $plan_info[0]['plan_name'] : '').'<br />';
			$order_details .= _LISTINGS_COUNT.': '.$result[0]['listings_amount'].'<br />';
			$order_details .= _CURRENCY.': '.$result[0]['currency'].'<br />';
			$order_details .= _CREATED_DATE.': '.format_datetime($result[0]['created_date']).'<br />';
			$order_details .= _PAYMENT_DATE.': '.format_datetime($result[0]['payment_date']).'<br />';
			$order_details .= _PAYMENT_TYPE.': '.$result[0]['m_payment_type'].'<br />';
			$order_details .= _PAYMENT_METHOD.': '.$result[0]['m_payment_method'].'<br />';
			//$order_details .= (($result[0]['campaign_name'] != '') ? _DISCOUNT_CAMPAIGN.': '.$result[0]['campaign_name'].' ('.$result[0]['discount_percent'].'%)' : '').'<br />';
			$order_details .= _ORDER_PRICE.': '.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $currencyFormat).'<br />';
			$order_details .= _VAT.': '.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $currencyFormat).' ('.$result[0]['vat_percent'].'%)'.'<br />';
			$order_details .= _TOTAL_PRICE.': '.Currencies::PriceFormat($result[0]['total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $currencyFormat).'<br />';
			//$order_details .= _ADDITIONAL_INFO.': '.nl2br($result[0]['additional_info']).'<br /><br />';
				
			$send_order_copy_to_admin =  ModulesSettings::Get('payments', 'send_order_copy_to_admin');
			////////////////////////////////////////////////////////////
			$sender = $objSettings->GetParameter('admin_email');
			$recipient = $result[0]['email'];

			if($order_type == 'completed'){
				// exit if email was already sent
				if($result[0]['email_sent'] == '1') return true;				
				$email_template = 'order_paid';
				$admin_copy_subject = 'Client order has been paid (admin copy)';				
			}else{
				$email_template = 'order_accepted_online';
				$admin_copy_subject = 'Client has placed online order (admin copy)';
			}
			
			////////////////////////////////////////////////////////////
			send_email(
				$recipient,
				$sender,
				$email_template,
				array(
					'{FIRST NAME}' => $result[0]['first_name'],
					'{LAST NAME}'  => $result[0]['last_name'],
					'{ORDER NUMBER}'  => $order_number,
					'{ORDER DETAILS}' => $order_details
				),
				$result[0]['preferred_language'],
				(($send_order_copy_to_admin == 'yes') ? $sender : ''),
				(($send_order_copy_to_admin == 'yes') ? $admin_copy_subject : '')
			);
			////////////////////////////////////////////////////////////

			if($order_type == 'completed'){
				$sql = 'UPDATE '.TABLE_ORDERS.' SET email_sent = 1 WHERE order_number = \''.$order_number.'\'';
				database_void_query($sql);
			}

			////////////////////////////////////////////////////////////
			return true;
		}else{
			///echo $sql;
			///echo mysql_error();
		}
		return false;
	}	
	
}
?>