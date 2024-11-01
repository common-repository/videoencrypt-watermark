<?php
/**
 * Initialise VideoEncrypt Watermark
 *
 * @class       Wplms_videotube_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     WPLMS-VideoTube/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_VideoTube_Watermark_Init{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_VideoTube_Watermark_Init();
        return self::$instance;
    }

	private function __construct(){
		
		add_action('admin_enqueue_scripts',array($this,'enqueue_media_scripts')); 
	}

	function enqueue_media_scripts() {
		$screen = get_current_screen();
		if($screen->base == 'media_page_videoencrypt-watermark'){
			wp_enqueue_media();	
		}
	}
	

}
Wplms_VideoTube_Watermark_Init::init();