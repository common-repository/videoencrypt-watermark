<?php
/*
Plugin Name: Videoencrypt Watermark
Plugin URI: https://videoencrypt.com
Description: Watermark your videos the WordPress Way
Author: VibeThemes
Version: 1.0
Author URI: https://vibethemes.com
Text Domain: videoencrypt-watermark
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit;
if( !defined('VIDEOENCRYPT_WATERMARK_VERSION')){
    define( 'VIDEOENCRYPT_WATERMARK_VERSION', '1.0' ); 
}

if ( ! defined( 'VIDEOENCRYPT_WATERMARK_API_NAMESPACE' ) ){
    define( 'VIDEOENCRYPT_WATERMARK_API_NAMESPACE', 'videotube/v1' );
}

if ( ! defined( 'VIDEOENCRYPT_WATERMARK_URL' ) ){
    define('VIDEOENCRYPT_WATERMARK_URL','https://videoencrypt.com');
}
if ( ! defined( 'VIDEOENCRYPT_WATERMARKED_FOLDER_NAME' ) ){
    define('VIDEOENCRYPT_WATERMARKED_FOLDER_NAME','videoencrypt_watermarked_videos');
}
if ( ! defined( 'VIDEOENCRYPT_WATERMARKED_OPTION' ) ){
    define('VIDEOENCRYPT_WATERMARKED_OPTION','videoencrypt_watermark_settings');
}



include_once 'includes/class.init.php';
include_once 'includes/class.functions.php';
include_once 'includes/class.filters.php';
include_once 'includes/class.watermark.php';
include_once 'includes/class.settings.php';
include_once 'includes/class.actions.php';



add_action('plugins_loaded','videoencrypt_watermark_translations');
function videoencrypt_watermark_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'videoencrypt-watermark');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'videoencrypt-watermark', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'videoencrypt-watermark', $mofile_global );
    } else {
        load_textdomain( 'videoencrypt-watermark', $mofile_local );
    }  
}
