<?php
/**
* @project ApPHP Business Directory
* @copyright (c) 2011 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('inquiries')){

	$act = isset($_POST['act']) ? prepare_input($_POST['act']) : '';
	$inquiry_sent = (int)Session::Get('inquiry_sent');
	$maximum_inquiries = 10;
	$msg = '';
	
	$params = array();

	$params['listing_id'] 	 = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : '0';
	$params['business_name'] = isset($_POST['business_name']) ? prepare_input($_POST['business_name']) : '';
	$params['inquiry_type']  = isset($_POST['inquiry_type']) ? (int)$_POST['inquiry_type'] : '0';

	$params['inquiry_category']      = isset($_POST['inquiry_category']) ? prepare_input($_POST['inquiry_category']) : '';
	$params['visitor_description']   = isset($_POST['visitor_description']) ? trim(prepare_input($_POST['visitor_description'])) : '';
    $params['visitor_name']  		 = isset($_POST['visitor_name']) ? prepare_input($_POST['visitor_name']) : '';
	$params['visitor_email'] 		 = isset($_POST['visitor_email']) ? prepare_input($_POST['visitor_email']) : '';
	$params['visitor_phone'] 		 = isset($_POST['visitor_phone']) ? prepare_input($_POST['visitor_phone']) : '';
	$params['visitor_locations'] 	 = isset($_POST['visitor_locations']) ? prepare_input($_POST['visitor_locations']) : '';
	$params['visitor_sub_locations'] = isset($_POST['visitor_sub_locations']) ? prepare_input($_POST['visitor_sub_locations']) : '';
	$params['visitor_availability']  = isset($_POST['visitor_availability']) ? prepare_input($_POST['visitor_availability']) : '';
	$params['visitor_availability']  = isset($_POST['visitor_availability']) ? prepare_input($_POST['visitor_availability']) : '';
	$params['visitor_preferred_contact'] = isset($_POST['visitor_preferred_contact']) ? prepare_input($_POST['visitor_preferred_contact']) : '';
	$params['focus_field'] = '';
    
    if($act == 'send'){
		if($inquiry_sent < $maximum_inquiries){
			if($params['inquiry_type'] == '0' && $params['inquiry_category'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._CATEGORY.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'inquiry_category';
			}else if($params['visitor_description'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_description';
			}else if($params['visitor_name'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._NAME.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_name';
			}else if($params['visitor_email'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._EMAIL.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_email';
			}else if($params['visitor_email'] != '' && !check_email_address($params['visitor_email'])){
				$msg = draw_important_message(_EMAIL_VALID_ALERT, false);
				$params['focus_field'] = 'visitor_email';
			}else if($params['visitor_phone'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._PHONE.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_phone';
			}else if($params['visitor_locations'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._LOCATION.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_locations';
			}else if($params['visitor_sub_locations'] == ''){
				$msg = draw_important_message(str_replace('_FIELD_', '<b>'._SUB_LOCATION.'</b>', _FIELD_CANNOT_BE_EMPTY), false);
				$params['focus_field'] = 'visitor_sub_locations';
			}else{
				
				// direct inquiry
				if($params['inquiry_type'] == '1'){
					$objListing = Listings::Instance($params['listing_id']);
					$plan_info = AdvertisePlans::GetPlanInfo($objListing->GetField('advertise_plan_id'));
					if($plan_info[0]['inquiry_button'] != '1'){
						$msg = draw_important_message(_DIRECT_INQUIRY_NOT_ALLOWED, false);
					}					
					if($objListing->GetField('customer_id') == $objLogin->GetLoggedID()){
						$msg = draw_important_message(_INQUIRY_TO_YOURSELF_PROHIBITED,  false);
					}
				}				
				
				if(empty($msg)){
					$sql = 'INSERT INTO '.TABLE_INQUIRIES.' (
							inquiry_type,
							category_id,
							listing_id,
							name,
							email,
							phone,
							location_id,
							sub_location_id,
							availability,
							preferred_contact,
							description,
							date_created,
							replies_count,
							is_active
						) VALUES (
							'.(int)$params['inquiry_type'].',
							'.(int)$params['inquiry_category'].',
							'.(int)$params['listing_id'].',
							\''.encode_text($params['visitor_name']).'\',
							\''.encode_text($params['visitor_email']).'\',
							\''.encode_text($params['visitor_phone']).'\',
							'.(int)$params['visitor_locations'].',
							'.(int)$params['visitor_sub_locations'].',
							'.(int)$params['visitor_availability'].',
							'.(int)$params['visitor_preferred_contact'].',
							\''.encode_text($params['visitor_description']).'\',
							\''.date('Y-m-d H:i:s').'\',
							0,
							1
						)
					';
					if(database_void_query($sql) > 0){
						
						$inquiry_id = mysql_insert_id();
						
						if($params['inquiry_type'] == '0'){
							$where_clause  = 'l.id IN (SELECT listing_id FROM '.TABLE_LISTINGS_CATEGORIES.' lc WHERE category_id = '.(int)$params['inquiry_category'].') AND ';
							$where_clause .= 'l.listing_location_id = '.(int)$params['visitor_locations'].' AND ';
							$where_clause .= 'l.listing_sub_location_id = '.(int)$params['visitor_sub_locations'];
						}else{
							$where_clause = 'l.id = '.(int)$params['listing_id'];
						}

						$sql_insert = '';
						$arr_customers = Listings::GetCustomerInfoByListing($where_clause);

						for($i=0; $i<$arr_customers[1]; $i++){
							
							$send_email = true;
							// save inquires history (for standard inquires only)
							if($params['inquiry_type'] == '0'){
								if($arr_customers[0][$i]['inquiries_allowed'] == '-1' || $arr_customers[0][$i]['inquiries_sent'] < $arr_customers[0][$i]['inquiries_allowed']){
									$sql_insert .= ($sql_insert == '') ? 'INSERT INTO '.TABLE_INQUIRIES_HISTORY.'(inquiry_id, customer_id, listing_id, date_added) VALUES ' : ',';
									$sql_insert .= '('.(int)$inquiry_id.', '.(int)$arr_customers[0][$i]['customer_id'].', '.(int)$params['listing_id'].', \''.date('Y-m-d H:i:s').'\')';
								}else{
									$send_email = false;	
								}								
							}
							
							if($send_email){
								send_email(
									$arr_customers[0][$i]['email'],
									$objSettings->GetParameter('admin_email'),
									'inquiry_new',
									array(
										'{FIRST NAME}' => $arr_customers[0][$i]['first_name'],
										'{LAST NAME}'  => $arr_customers[0][$i]['last_name'],
										'{WEB SITE}'   => $_SERVER['SERVER_NAME'],
										'{BASE URL}'   => APPHP_BASE,
									)
								);								
							}							
						}
						// insert data into inquiries history table
						if($sql_insert != '') database_void_query($sql_insert);
						
						Session::Set('inquiry_sent', $inquiry_sent+1);					
						header('location: index.php?page=inquiry_send');
						exit;
					}else{
						///echo mysql_error();
						$msg = draw_important_message(_TRY_LATER, false);
					}									
				}				
			}
		}else{
			$msg = draw_important_message(str_replace('_COUNT_', $maximum_inquiries, _MAXIMUM_ALLOWED_INQUIRIES_PER_SESSION), false);
		}
	}else if($act == 'location_reload'){
		// currently do nothing		
	}

}
	
?>