<?php 
/**
 * Initialise VideoEncrypt Watermark
 *
 * @class       VideoEncrypt_Watermark_Actions
 * @author      VibeThemes
 * @category    Admin
 * @package     videoencrypt-watermark/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VideoEncrypt_Watermark_Actions{


    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VideoEncrypt_Watermark_Actions();
        return self::$instance;
    }

    private function __construct(){
        add_action('wp_ajax_send_email_videoencrypt',array($this,'send_email_videoencrypt'));
    }


    function send_email_videoencrypt(){
		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'videoencrypt_create_account')){
             echo esc_html('<div class="error notice is-dismissible"><p>'.__('Security check Failed. Contact Administrator.','videoencrypt-watermark').'</p></div>');
             die();
        }

        $email = sanitize_email($_POST['email']);

        $args = array(
            'method'      => 'POST',
            'timeout'     => 120 ,
            'body' => array( 'email' => $email ,'security'=>'vibe_rocks'),
        );
        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/send_videoencrypt_watermark_try_email',$args );
        
        if(!empty($result) && !is_wp_error($result)){
            echo wp_remote_retrieve_body( $result );
        }
        die();
	}
    
}
VideoEncrypt_Watermark_Actions::init();