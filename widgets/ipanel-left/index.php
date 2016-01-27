<?php

$host = isset($_GET['host']) ? urldecode(base64_decode($_GET['host'])) : '';
$key = isset($_GET['key']) ? base64_decode($_GET['key']) : '';

$host_not_decoded = isset($_GET['host']) ? $_GET['host'] : '';
$key_not_decoded = isset($_GET['key']) ? $_GET['key'] : '';

$basedir = '../../';

require_once($basedir.'include/base.inc.php');
if($key != INSTALLATION_KEY) exit(0);

//require_once($basedir.'include/shared.inc.php');
require_once($basedir.'include/settings.inc.php');
require_once($basedir.'include/functions.database.inc.php');
require_once($basedir.'include/functions.common.inc.php');
require_once($basedir.'include/functions.html.inc.php');
require_once($basedir.'include/functions.validation.inc.php');

require_once($basedir.'include/classes/Session.class.php');
require_once($basedir.'include/classes/Login.class.php');
require_once($basedir.'include/classes/MicroGrid.class.php');
require_once($basedir.'include/classes/Modules.class.php');
require_once($basedir.'include/classes/ModulesSettings.class.php');
require_once($basedir.'include/classes/Application.class.php');

require_once($basedir.'include/classes/Languages.class.php');
require_once($basedir.'include/classes/Categories.class.php');

require_once($basedir.'include/classes/Listings.class.php');
require_once($basedir.'include/classes/ListingsCategories.class.php');
require_once($basedir.'include/classes/ListingsLocations.class.php');
require_once($basedir.'include/classes/ListingsSubLocations.class.php');

require_once($basedir.'include/classes/Inquiries.class.php');


define('APPHP_BASE', get_base_url());
@date_default_timezone_set(TIME_ZONE);

// setup connection
//------------------------------------------------------------------------------
$database_connection = @mysql_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD) or die(((SITE_MODE == 'development') ? mysql_error() : 'Fatal Error: Please check database connection parameters!'));
@mysql_select_db(DATABASE_NAME, $database_connection) or die(((SITE_MODE == 'development') ? mysql_error() : 'Fatal Error: Please check your database exists!'));

Modules::Init();
ModulesSettings::Init();

require_once($basedir.'include/messages.en.inc.php');

$objSession = new Session();
$objLogin = new Login();

error_reporting(E_ALL);
ini_set('display_errors', 'On');

/////////////////////////////////////////////////////////////////////////////////////////////////

if(Modules::IsModuleInstalled('inquiries')){
   
    //echo $_SERVER['REQUEST_METHOD'];
    //print_r($_POST);
   
	$act = isset($_POST['act']) ? prepare_input($_POST['act']) : (isset($_GET['act']) ? prepare_input($_GET['act']) : '');
	$inquiry_sent = (int)Session::Get('inquiry_sent');
	$maximum_inquiries = 10;
	$msg = '';

	$params = array();
	
	$params['listing_id'] 	 = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : '0';
	$params['business_name'] = isset($_POST['business_name']) ? prepare_input($_POST['business_name']) : '';
	$params['inquiry_type']  = '0';

	$params['inquiry_category']      = isset($_POST['inquiry_category']) ? prepare_input($_POST['inquiry_category']) : '';
	$params['visitor_description']   = isset($_POST['visitor_description']) ? trim(prepare_input($_POST['visitor_description'])) : '';
    $params['visitor_name']  		 = isset($_POST['visitor_name']) ? prepare_input($_POST['visitor_name']) : '';
	$params['visitor_email'] 		 = isset($_POST['visitor_email']) ? prepare_input($_POST['visitor_email']) : '';
	$params['visitor_phone'] 		 = isset($_POST['visitor_phone']) ? prepare_input($_POST['visitor_phone']) : '';

	$home_visitor_sub_locations = isset($_POST['home_visitor_sub_locations']) ? prepare_input($_POST['home_visitor_sub_locations']) : '';;
	$hvsl_parts = explode('-', $home_visitor_sub_locations);
	if(count($hvsl_parts) == 2){
		$params['visitor_locations'] 	 = isset($hvsl_parts[0]) ? (int)$hvsl_parts[0] : '';
		$params['visitor_sub_locations'] = isset($hvsl_parts[1]) ? (int)$hvsl_parts[1] : '';
	}else{
		$params['visitor_locations'] 	 = isset($_POST['visitor_locations']) ? prepare_input($_POST['visitor_locations']) : '';
		$params['visitor_sub_locations'] = isset($_POST['visitor_sub_locations']) ? prepare_input($_POST['visitor_sub_locations']) : '';
	}
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
						header('location: '.$host.'widgets/ipanel-left/index.php?host='.$host_not_decoded.'&key='.$key_not_decoded.'&act=inquiry_sent');
						exit;
                    }else{
						//echo mysql_error();
						$msg = draw_important_message(_TRY_LATER, false);
					}									                    
				}				
            }            
		}else{
			$msg = draw_important_message(str_replace('_COUNT_', $maximum_inquiries, _MAXIMUM_ALLOWED_INQUIRIES_PER_SESSION), false);
		}
	}else if($act == 'location_reload'){
		// currently do nothing
    }else if($act == 'inquiry_sent'){
        $msg = draw_success_message(_INQUIRY_SENT_SUCCESS_MSG, false);    
	}
    
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Inquiry Form</title>
    <link href="<?php echo $host; ?>templates/default/css/style.css" type="text/css" rel="stylesheet" />    
    <script type="text/javascript" src="<?php echo $host; ?>js/main.js"></script>
    <script type="text/javascript" src="<?php echo $host; ?>js/jquery-1.6.3.min.js"></script>
    <base href="<?php echo $host; ?>" />
    <style>
        body { text-align:left; }
</style>
</head>    
<body>
    <?php
        
        $inquiry_category = isset($params['inquiry_category']) ? prepare_input($params['inquiry_category']) : '';
        $category_info = Categories::GetCategoryInfo($inquiry_category);
        $category_name = isset($category_info['name']) ? _SEND_INQUIRY_TO.' '.$category_info['name'] : _SEND_INQUIRY;
        $business_name = isset($params['business_name']) ? $params['business_name'] : '';

        $params['widget'] = true;
        $params['widget_host'] = $host_not_decoded;
        $params['widget_key'] = $key_not_decoded;        
        
    	if(!empty($msg)){
            echo $msg;
        }else{
            draw_title_bar(_SEND_INQUIRY);    
        }
        
        Inquiries::DrawInquiryForm($params);	
    ?>
    
</body>
</html>
<?php
}
?>