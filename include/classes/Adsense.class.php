<?php

/**
 *	Class Adsense
 *  -------------- 
 *  Description : encapsulates AdSense properties
 *	Written by  : ApPHP
 *  Updated	    : 19.12.2011
 *	Version     : 1.0.1
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				GetVerticalBanerCode
 *	__destruct              GetHorizontalBanerCode
 *	
 *  1.0.1
 *      - 
 *      - 
 *      -
 *      -
 *      -      
 *	
 **/

class Adsense {
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{

	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Returns vertical banner code
	 */
	static public function GetVerticalBanerCode()
	{		
		if(Modules::IsModuleInstalled('adsense')){
			$activation = strtolower(ModulesSettings::Get('adsense', 'adsense_code_activation'));
			if($activation == 'vertical' || $activation == 'all'){
				return ModulesSettings::Get('adsense', 'adsense_code_vertical');
			}			
		}        
		return '';
	}

	/**
	 * Returns horizontal banner code
	 */
	static public function GetHorizontalBanerCode()
	{
		if(Modules::IsModuleInstalled('adsense')){
			$activation = strtolower(ModulesSettings::Get('adsense', 'adsense_code_activation'));
			if($activation == 'horizontal' || $activation == 'all'){
				return ModulesSettings::Get('adsense', 'adsense_code_horizontal');
			}			
		}        
		return '';
	}
}

?>