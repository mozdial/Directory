<?php

/**
 *	Class ListingsLocations (for Business Directory ONLY)
 *  -------------- 
 *  Description : encapsulates listings locations properties
 *  Updated	    : 12.12.2012
 *	Written by  : ApPHP
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				GetAllLocations
 *	__destruct              DrawAllLocations
 *	AfterDeleteRecord
 *	
 **/

class ListingsLocations extends MicroGrid{
	
	protected $debug = false;
	

	//==========================================================================
    // Class Constructor
	//		@param $id
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))   $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['is_active']))   $this->params['is_active'] = (int)$_POST['is_active'];
		
		// $this->params['language_id'] 	  = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_LISTINGS_LOCATIONS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_listings_locations';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		//$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY name ASC'; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 30;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;

		$arr_default_types = array('0'=>_NO, '1'=>_YES);
		$arr_activity_types = array('0'=>_NO, '1'=>_YES);				
		// define filtering fields
		$this->arrFilteringFields = array(
			_NAME   => array('table'=>$this->tableName, 'field'=>'name', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
			_ACTIVE => array('table'=>$this->tableName, 'field'=>'is_active', 'type'=>'dropdownlist', 'source'=>$arr_activity_types, 'sign'=>'=', 'width'=>'90px', 'visible'=>true),
		);

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name,
									CONCAT("<a href=index.php?admin=mod_listings_sub_locations&lid=", '.$this->tableName.'.'.$this->primaryKey.',
										">[ ", "'._SUB_LOCATIONS.' ]</a> (",
										(SELECT COUNT(*) FROM '.TABLE_LISTINGS_SUB_LOCATIONS.' sl WHERE sl.location_id = '.$this->tableName.'.'.$this->primaryKey.'),
										")") as link_sub_locations,
									IF(is_active, \'<span class=yes>'._YES.'</span>\', \'<span class=no>'._NO.'</span>\') as mod_is_active
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  		     => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
			'mod_is_active'      => array('title'=>_ACTIVE, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'height'=>'', 'maxlength'=>''),
			'link_sub_locations' => array('title'=>'', 'type'=>'label', 'align'=>'left', 'width'=>'160px', 'maxlength'=>'', 'visible'=>true),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'name'  	     => array('title'=>_NAME, 	'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text'),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',    'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>'')
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// Validation Type: alpha|numeric|float|alpha_numeric|text|email
		// Validation Sub-Type: positive (for numeric and float)
		// Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.name,
								'.$this->tableName.'.is_active,
								IF(is_active, \'<span class=yes>'._YES.'</span>\', \'<span class=no>'._NO.'</span>\') as mod_is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'name'  	     => array('title'=>_NAME,         'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'default'=>'', 'validation_type'=>'text'),
			'is_active'      => array('title'=>_ACTIVE,       'type'=>'enum',     'required'=>true, 'width'=>'90px', 'readonly'=>false, 'default'=>'1', 'source'=>$arr_activity_types, 'unique'=>false, 'javascript_event'=>'')
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'  	     => array('title'=>_NAME, 'type'=>'label'),
			'mod_is_active'  => array('title'=>_ACTIVE, 'type'=>'label')
		);

	}


	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 *	Get all locations array
	 *		@param $order - order clause
	 */
	public static function GetAllLocations($order = 'name ASC')
	{
		// Build ORDER BY clause
		$order_clause = (!empty($order)) ? 'ORDER BY '.$order : '';
	
		$sql = 'SELECT id, name, is_active
				FROM '.TABLE_LISTINGS_LOCATIONS.'
				WHERE is_active = 1 '.$order_clause;			
		
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 *	Draw all locations
	 *		@param $params
	 *		@param $draw
	 */
	public static function DrawAllLocations($params = array(), $draw = true)	
	{
		$tag_name = isset($params['tag_name']) ? $params['tag_name'] : 'l_locaton';
		$tag_id = isset($params['tag_id']) ? $params['tag_id'] : 'id_l_locaton';
		$selected_value = isset($params['selected_value']) ? $params['selected_value'] : '';
		$javascript_event = isset($params['javascript_event']) ? ' '.$params['javascript_event'] : '';
		
		$output  = '<select name="'.$tag_name.'" id="'.$tag_id.'"'.$javascript_event.'>';
		$output .= '<option value="">-- '._ALL_LOCATIONS.' --</option>';		
		$locations = ListingsLocations::GetAllLocations('name ASC');
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


	//==========================================================================
    // MicroGrid Methods
	//==========================================================================	
	/**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
		//remove sub-locations
		$sql = 'DELETE FROM '.TABLE_LISTINGS_SUB_LOCATIONS.' WHERE location_id = '.(int)$this->curRecordId;
		return database_void_query($sql) ? true : false;
	}

}
?>