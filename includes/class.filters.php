<?php
/**
 * Filters for VideoEncrypt Watermark
 *
 * @class       Wplms_VideoTube_Watermark_Filters
 * @author      VibeThemes
 * @category    Admin
 * @package     WPLMS-VideoTube/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_VideoTube_Watermark_Filters{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_VideoTube_Watermark_Filters();
        return self::$instance;
    }

	private function __construct(){
		
	}

	


}
Wplms_VideoTube_Watermark_Filters::init();