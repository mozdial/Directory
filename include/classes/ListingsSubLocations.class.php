<?php

/**
 *	Class ListingsSubLocations (for Business Directory ONLY)
 *  -------------- 
 *  Description : encapsulates listings locations properties
 *  Updated	    : 14.12.2012
 *	Written by  : ApPHP
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				GetAllSubLocations 
 *	__destruct              DrawAllSubLocations 
 *	
 **/

class ListingsSubLocations extends MicroGrid{
	
	protected $debug = false;
	

	//==========================================================================
    // Class Constructor
	//		@param $lid
	//==========================================================================
	function __construct($lid)
	{		
		parent::__construct();

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))   $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['location_id']))   $this->params['location_id'] = prepare_input($_POST['location_id']);
		
		// $this->params['language_id'] 	  = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_LISTINGS_SUB_LOCATIONS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_listings_sub_locations&lid='.(int)$lid;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		//$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = 'WHERE location_id = '.(int)$lid;
		$this->ORDER_CLAUSE = 'ORDER BY name ASC'; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 100;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;

		$arr_default_types = array('0'=>_NO, '1'=>_YES);
		$arr_activity_types = array('0'=>_NO, '1'=>_YES);				
		// define filtering fields
		$this->arrFilteringFields = array(
			_NAME => array('table'=>$this->tableName, 'field'=>'name', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
		);

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'name'        => array('title'=>_NAME, 	'type'=>'textbox', 'width'=>'240px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text'),
			'location_id' => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$lid),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.name
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'name'  => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'240px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text'),
			'location_id' => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$lid),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name' => array('title'=>_NAME, 'type'=>'label'),
		);
		
	}

	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 *	Get all sub locations array
	 *		@param $location_id
	 *		@param $order - order clause
	 */
	public static function GetAllSubLocations($location_id = 0, $order = 'name ASC')
	{
		// Build ORDER BY clause
		$order_clause = (!empty($order)) ? 'ORDER BY '.$order : '';
	
		$sql = 'SELECT id, location_id, name
				FROM '.TABLE_LISTINGS_SUB_LOCATIONS.'
				WHERE location_id = '.(int)$location_id.' '.$order_clause;			
		
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Draw all locations
	 *		@param $location_id
	 *		@param $params
	 *		@param $draw
	 */
	public static function DrawAllSubLocations($location_id = '', $params = array(), $draw = true)
	{
		$tag_name = isset($params['tag_name']) ? $params['tag_name'] : 'l_sub_locaton';
		$tag_id = isset($params['tag_id']) ? $params['tag_id'] : 'id_l_sub_locaton';
		$selected_value = isset($params['selected_value']) ? $params['selected_value'] : '';
		$javascript_event = isset($params['javascript_event']) ? ' '.$params['javascript_event'] : '';
		
		$output  = '<select name="'.$tag_name.'" id="'.$tag_id.'"'.$javascript_event.'>';
		$output .= '<option value="">-- '._ALL_SUB_LOCATIONS.' --</option>';		
		$locations = ListingsSubLocations::GetAllSubLocations($location_id, 'name ASC');
		for($i=0; $i < $locations[1]; $i++){
			if($selected_value == $locations[0][$i]['id']){
				$selected_state = 'selected="selected"';
			}else{
				$selected_state = '';
			}			
			$output .= '<option '.$selected_state.' value="'.$locations[0][$i]['id'].'">'.$locations[0][$i]['name'].'</option>';
		}
		$output .= '</select>';
		
		if($draw) echo $output;
		else return $output;		
	}
	
}
?>