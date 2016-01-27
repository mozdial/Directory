<?php
/**
* @project ApPHP Business Directory
* @copyright (c) 2011 ApPHP
* @author ApPHP <info@apphp.com>
* @license http://www.gnu.org/licenses/
*/

@session_start();

//------------------------------------------------------------------------------
require_once('shared.inc.php');
require_once('settings.inc.php');
require_once('functions.database.inc.php');
require_once('functions.common.inc.php');
require_once('functions.html.inc.php');
require_once('functions.validation.inc.php');

define('APPHP_BASE', get_base_url());

// setup connection
//------------------------------------------------------------------------------
$database_connection = @mysql_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
@mysql_select_db(DATABASE_NAME, $database_connection);
if(mysql_error()){  
    $error_content = @file_get_contents('html/site_error.html');
    if(!empty($error_content)){
        $error_content = str_ireplace(
            array('{HEADER_TEXT}', '{ERROR_SIGNATURE}', '{ERROR_MESSAGE}'),
            array('System Fatal Error', 'Database connection', (SITE_MODE == 'development') ? 'Reporting details:<br>'.mysql_error() : 'Please check your database connection parameters!'),
            $error_content
        );                    
    }else{
        $error_content = 'System Fatal Error: '.mysql_error();
    }
    echo $error_content;
    exit;
}
// set collation
set_collation();
// set group_concat max length
set_group_concat_max_length();
/// set sql_mode to empty if you have Mixing of GROUP columns SQL issue
///set_sql_mode();

// autoloading classes
//------------------------------------------------------------------------------
function __autoload($class_name){
	if($class_name == 'PHPMailer'){
		require_once('modules/phpmailer/class.phpmailer.php');
	}else{
		require_once('classes/'.$class_name.'.class.php');	
	}	
}

if(defined('APPHP_CONNECT') && APPHP_CONNECT == 'direct'){	
	include_once('messages.inc.php');
	
	// Set time zone
	//------------------------------------------------------------------------------
	@date_default_timezone_set(TIME_ZONE);
	
	$objSession  = new Session();
	$objLogin    = new Login();
	$objSettings = new Settings();
	Modules::Init();
	ModulesSettings::Init();
	
}else{
	// set timezone
	//------------------------------------------------------------------------------
	Settings::SetTimeZone();
	
	// create main objects
	//------------------------------------------------------------------------------
	$objSession 		= new Session();
	$objLogin 			= new Login();
	$objSettings 		= new Settings();
	$objSiteDescription = new SiteDescription();
	Modules::Init();
	ModulesSettings::Init();
	Application::Init();
	Languages::Init();
	
	// include files for administrator use only
	//------------------------------------------------------------------------------
	if($objLogin->IsLoggedInAsAdmin()){
		include_once('functions.admin.inc.php');
	}
	
	// include language file
	//------------------------------------------------------------------------------
	if(!defined('APPHP_LANG_INCLUDED')){
		if(get_os_name() == 'windows'){
			$lang_file_path = str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']).'include/messages.'.Application::Get('lang').'.inc.php';
		}else{
			$lang_file_path = 'include/messages.'.Application::Get('lang').'.inc.php';
		}
		if(file_exists($lang_file_path)){
			include_once($lang_file_path);
		}else if(file_exists('include/messages.inc.php')){
			include_once('include/messages.inc.php');
		}
	}	
}

?>