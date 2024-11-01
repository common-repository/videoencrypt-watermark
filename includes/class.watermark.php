<?php
/**
 * Initialise Videoencrypt_Watermark
 *
 * @class       Videoencrypt_Watermark_Watermarkor
 * @author      VibeThemes,Anshuman
 * @category    Admin
 * @package     WPLMS-VideoTube/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class Videoencrypt_Watermark_Watermarkor{


    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Videoencrypt_Watermark_Watermarkor();
        return self::$instance;
    }

    private function __construct(){
        $this->privacy_options = apply_filters('videotube_privacy_options_array',array(
                '' => _x('Select Privacy','','videoencrypt-watermark'),
                'all_users' => _x('All users','','videoencrypt-watermark'),
                'logged_in_users' => _x('Logged in Users','','videoencrypt-watermark'),
                'selected_users' => _x('Selected Users','','videoencrypt-watermark')
            ));
        if(function_exists('bp_course_is_member')){
            $this->privacy_options['course_students'] =  _x('Course Students','','videoencrypt-watermark');
        }
        
        
        add_action('print_media_templates',array($this,'print_media_templates'));
        
        add_action('wp_ajax_fetch_media_url',array($this,'fetch_media_url'));

        add_action('wp_ajax_download_watermarked_files',array($this,'download_watermarked_files'));
        add_action('wp_ajax_videotube_watermark_check_video_status',array($this,'videotube_watermark_check_video_status'));
        add_action('wp_ajax_videotube_watermark_ready_chunks_array',array($this,'videotube_watermark_ready_chunks_array'));
        
        add_action('wp_ajax_videowatermark_send_chunks',array($this,'videowatermark_send_chunks'));
        add_action('wp_ajax_videowatermark_download_file',array($this,'videowatermark_download_file'));
        add_action('wp_ajax_end_videowatermark_download_file',array($this,'end_videowatermark_download_file'));

        add_filter( 'wp_prepare_attachment_for_js', array( $this, 'add_watermark_button' ), 99, 2);
        
        add_action( 'admin_enqueue_scripts', array( $this, 'watermark_enqueue_scripts' ) ,100);
        // Load js and css on pages with Media Uploader - WP Enqueue Media.
        add_action( 'wp_enqueue_media', array( $this, 'watermark_enqueue_scripts' ) ,100);



    }

    function get_option(){
        $this->videotube_settings = sanitize_option(VIDEOENCRYPT_WATERMARKED_OPTION,get_option(VIDEOENCRYPT_WATERMARKED_OPTION));
    }

    function videowatermark_download_file(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security')){
             echo __('Security check Failed. Contact Administrator.','videoencrypt-watermark');
             die();
        }
        $video_id =  is_numeric($_POST['video_id'])?sanitize_text_field($_POST['video_id']):0;
        
            
        if(!empty($_POST['file'])){
            $file_url = esc_url_raw($_POST['file']);
            if(!empty($video_id) && !empty($file_url)){
                
                $video = wp_get_attachment_url($video_id);
                $video_path = $this->url_to_string($video);
                $user_id = get_current_user_id();
                $upload_dir_base = wp_upload_dir();
                $uploads_base_url =$upload_dir_base['baseurl'];
                $folderurl = $uploads_base_url."/".VIDEOENCRYPT_WATERMARKED_FOLDER_NAME."/".$user_id."/".$video_path;
                $folderpath = $upload_dir_base['basedir']."/".VIDEOENCRYPT_WATERMARKED_FOLDER_NAME."/".$user_id."/".$video_path;
                if(function_exists('is_dir') && !is_dir($folderpath)){
                    if(function_exists('mkdir')) 
                        mkdir($folderpath, 0755, true) || chmod($folderpath, 0755);
                }
                if(!empty($_POST['first_call'])){
                    if ($dhr = opendir($folderpath)){
                        while (($_file = readdir($dhr)) !== false){
                            @unlink($folderpath.'/'.$_file);
                        }
                        closedir($dhr); 
                    }
                }
                $uploaded = $this->_fetch_remote_file( $file_url,$folderpath);

                if (  empty( $uploaded ) || is_wp_error( $uploaded ) ) {
                    // todo: error
                    $data =  array(
                        'status' => 0,
                        'message'=>_x('File could not be downloaded from videowatermark.com server ','','videotube-watermarker')
                    );
                }else{
                    //edit the m3u8 file for path to key 
                    //just chhange the key file path ts not needed .

                    $data =  array(
                        'status' => 1,
                        
                        'message'=>_x('file downloaded','','videotube-watermarker')
                    );
                }
            }else{
                $data =array(
                    'status'=>0,
                    'message'=>_x('Security error ! Could not find file url','','videoencrypt-watermark'),
                );
            }
        }else{
            $data =array(
                'status'=>0,
                'message'=>_x('Could not find file url','','videoencrypt-watermark'),
            );
        }
        echo json_encode($data);
        die();
    }

    function end_videowatermark_download_file(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security')){
             echo __('Security check Failed. Contact Administrator.','videoencrypt-watermark');
             die();
        }
        $video_id =  is_numeric($_POST['video_id'])?sanitize_text_field($_POST['video_id']):0;
        if(!empty($video_id)){
            $video = wp_get_attachment_url($video_id);
            $video_path = $this->url_to_string($video);
            $user_id = get_current_user_id();
            $upload_dir_base = wp_upload_dir();
            $uploads_base_url =$upload_dir_base['baseurl'];
            $folderurl = $uploads_base_url."/".VIDEOENCRYPT_WATERMARKED_FOLDER_NAME."/".$user_id."/".$video_path;
            $folderpath = $upload_dir_base['basedir']."/".VIDEOENCRYPT_WATERMARKED_FOLDER_NAME."/".$user_id."/".$video_path;
            $keyfile_contents_array = [];
            $new_m3u8_contents = '';
            $new_m3u8_contents_array = array();
            $counter = 0;
           
            if ($dh = opendir($folderpath)){
                
                while (($file = readdir($dh)) !== false){

                    


                    //read all m3u8 files and combine them
                    if(strpos($file, '-watermarked.mp4')){
                        $watermarked_file = $folderpath."/".$file;
                        /*-----------------------
                        ADDING FILE TO MEDIA LIBRARY
                        ----------------------*/
                        $post_file_name  = basename( $watermarked_file );
                        
                        $attachment = array(
                                        'guid'           => $watermarked_file, 
                                        'post_mime_type' => 'video/mp4',
                                        'post_title'     => preg_replace( '/\.[^.]+$/', '',$post_file_name ),
                                        'post_content'   => '',
                                        'post_status'    => 'inherit'
                                        );   
                                

                        $attachment_id = wp_insert_attachment($attachment,$watermarked_file);
                        if(!empty($attachment_id)  ){
                            


                            require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
                            require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
                            require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
                            $attachment_url = wp_get_attachment_url($attachment_id);              
                            $update_value = wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $filePath ) );
                            update_post_meta($video_id,'watermark_status','watermarked');
                            update_post_meta($video_id,'watermarked_file_id', $attachment_id);
                            update_post_meta($attachment_id,'watermarked_file',1);

                            
                            $data =  array(
                                'status' => 1,
                                'message'=>_x('File downloaded from videowatermark.com server ','','videotube-watermarker'),
                                'new_video' =>  $attachment_id
                            );
                        }
                    }//end if(strpos($file, '-watermarked.mp4')){
                }
            }
        }
        echo json_encode($data);
        die();
    }

    function download_watermarked_files(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','videoencrypt-watermark');
            die();
        }
        $video_id =  is_numeric($_POST['video'])?sanitize_text_field($_POST['video']):0;
        if(!empty($video_id)){
            $video = wp_get_attachment_url($video_id);
            $files = get_post_meta($video_id,'watermarked_files',true);
            $files_array = [];
            if(!empty($files) && count($files)){
                $security = wp_create_nonce('wplms_videotube_en_security');

                foreach ($files as $key => $url) {
                    $first_call= $last_call= 0;
                    if($key == 0){
                        $first_call= 1 ;
                    }
                    if($key == (count($files) - 1)){
                        $last_call= 1 ;
                    }
                    $files_array[] = array(

                                'action'=>'videowatermark_download_file',
                                'security'=>$security,
                                'video_id'=>$video_id,
                                'first_call'=>$first_call,
                                'last_call'=>$last_call,
                                'file'=>$url
                                
                    );
                }

                $data =  array(
                        'status' => 1,
                        'files'=>$files_array,
                    );





            }else{
                $data =  array(
                            'status' => 0,
                            'message'=>_x('Encryped Files not found found for this video!','','videotube-watermarker')
                        );
            }
        }else{
            $data =  array(
                        'status' => 0,
                        'message'=>_x('Video id not defined','','videotube-watermarker')
                    );
        }
        echo json_encode($data);
        die();
    }

    function videowatermark_send_chunks(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security')){
             echo __('Security check Failed. Contact Administrator.','videoencrypt-watermark');
             die();
        }
        $video_id = is_numeric($_POST['video_id'])?sanitize_text_field($_POST['video_id']):0;
        $start = is_numeric($_POST['start'])?sanitize_text_field($_POST['start']):'';
        $chunk = is_numeric($_POST['chunk'])?sanitize_text_field($_POST['chunk']):'';

        $first_chunk  = sanitize_text_field($_POST['first_chunk']);
        $last_chunk  = sanitize_text_field($_POST['last_chunk']);
        $chunk_number  = sanitize_text_field($_POST['chunk_number']);
        $videotube_api_key = '';
        $this->get_option();
        if(!empty($this->videotube_settings) && !empty($this->videotube_settings['videotube_api_key'])){
            $videotube_api_key = $this->videotube_settings['videotube_api_key'];
        }
        if(!empty($video_id) && !empty($chunk)){
            $path = get_attached_file($video_id);
            $file_size = filesize($path);
            if(file_exists($path)){
                
                $contents = $this->videotube_read_file($path,$start,$chunk);
                $url = wp_get_attachment_url($video_id);
                //http://localhost/wplms/wp-json/videotube/v1/video/watermark
                
                $args = array(
                    'method'      => 'POST',
                    'timeout'     => apply_filters('watermark_vidtube_video_request_timeout',120),
                    
                    'body' => array(
                        'video'=>$url, 
                        'videotube_api_key'=>$videotube_api_key,
                        'contents'=>base64_encode($contents),
                        'file_size'=>$file_size,
                        'first_chunk'=>$first_chunk,
                        'last_chunk'=>$last_chunk,
                        'chunk_number'=>$chunk_number,
                        'video_length'=>sanitize_text_field($_POST['video_length']),
                        'video_height'=>sanitize_text_field($_POST['video_height']),
                        'video_width'=>sanitize_text_field($_POST['video_width']),
                        'watermark_logo' => $this->videotube_settings['videoencrypt_watermark_logo'],
                        'watermark_position' => (!empty($this->videotube_settings['videoencrypt_watermark_postition'])?$this->videotube_settings['videoencrypt_watermark_postition']:'bottomright'),

                        'watermark_logo_padding' => (!empty($this->videotube_settings['videoencrypt_watermark_logo_padding'])?$this->videotube_settings['videoencrypt_watermark_logo_padding']:'10'),
                        
                    ),
                );
                $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/video/watermarkuploadchunked',$args );
               
                if(!empty($result) && !is_wp_error($result)){

                    
                    $result_body = json_decode(wp_remote_retrieve_body( $result ),true);

                    if(!empty($result_body) && $result_body['status']){
                        if(!empty($last_chunk)){
                            if(!empty($result_body['key']) && $result_body['status']){
                                $data = array(
                                    'status' => 1,
                                    'key'=>$result_body['key'],
                                    'message'=>_x('Video uploaded on server.Ready to watermark.','','videoencrypt-watermark'),
                                );
                                
                                update_post_meta($video_id,'watermark_status','processing');
                                $data = $result_body;
                            }else{
                                $data = array(
                                    'status' => 0,
                                    'message'=>(!empty($result_body['message'])?$result_body['message']:_x('There was some error in uploading the video ','','videoencrypt-watermark')),
                                );
                            }
                        }
                        $data = $result_body;
                    }else{
                        
                        $data = array(
                            'status' => 0,
                            'message'=>(!empty($result_body['message'])?$result_body['message']:_x('There was some error in uploading the video ','','videoencrypt-watermark')),
                        );
                    }
                }else{
                    
                    $data = array(
                        'status' => 0,
                        'message'=>(!empty($result_body['message'])?$result_body['message']:_x('There was some error in uploading the video ','','videoencrypt-watermark')),
                    );
                }
            }
        }
        echo json_encode($data);
        die();
    }

    function videotube_watermark_ready_chunks_array(){

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security')){
             _e('Security check Failed. Contact Administrator.','videoencrypt-watermark');
             die();
        }
        $video_id = is_numeric($_POST['video'])?sanitize_text_field($_POST['video']):0;
        if(!empty($video_id)){
            $path = get_attached_file($video_id);
            $url = wp_get_attachment_url($video_id);
            if(file_exists($path)){
                $file_size = filesize( $path );
                $chunks_array = [];
                $post_size = $this->get_post_size();
                
                $start_value = 0;

                $parts = ceil(intval($file_size)/intval($post_size));
                $new_parts = 0;
                if(!empty($parts)){
                    $security = wp_create_nonce('wplms_videotube_en_security');
                    while($new_parts < $parts){
                        $first_chunk = 0;
                        $last_chunk = 0;
                        if($new_parts == 0){
                            $start_value = 0;
                            $first_chunk = 1;
                        }else{
                            $start_value =  $new_parts*$post_size;
                            if($new_parts == ($parts-1)){
                                $last_chunk = 1;
                            }
                        }
                        if($parts == 1){
                            $first_chunk= 1;
                            $last_chunk= 1;
                        }
                        $chunks_array[] = [
                            'action'=>'videowatermark_send_chunks',
                            'security'=>$security,
                            'video_id'=>$video_id,
                            'video'=>$url,
                            'start' => $start_value,
                            'chunk'=>$post_size,
                            'filesize'=>$file_size,
                            'first_chunk'=>$first_chunk,
                            'last_chunk'=>$last_chunk,
                            'chunk_number'=>$new_parts,
                            'video_length'=>sanitize_text_field($_POST['video_length']),
                            'video_height'=>sanitize_text_field($_POST['video_height']),
                            'video_width'=>sanitize_text_field($_POST['video_width']),
                        ];
                        $new_parts++;

                    }  
                }
            }
        }
        echo json_encode($chunks_array);
        die();
    }

    function videotube_watermark_check_video_status(){

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security')){
             _e('Security check Failed. Contact Administrator.','videoencrypt-watermark');
             die();
        }
        $data = array();
        $video_id = is_numeric($_POST['video'])?sanitize_text_field($_POST['video']):0;
        $video_url = sanitize_url($_POST['url']);
        if(!empty($video_id) && !empty($video_url)){
            $check_status = $this->check_video_status($video_url);
           
            if(!empty($check_status)){
                if(!empty($check_status['files'])){
                    $data = array(
                        'status' => true,
                        'video_status' => 'completed',
                        'files' => $check_status['files']
                    );
                    update_post_meta($video_id,'watermark_status','download_now');
                    update_post_meta($video_id,'watermarked_files',$check_status['files']);
                }else{

                    //check video was failed:
                    if(!empty($check_status['video_failed'])){
                        $data = array(
                            'status' => false,
                            'video_status' => $check_status['video_failed'],
                            'message' => (!empty($check_status['message'])?$check_status['message']:_x('There was some error in checking video status!','','videoencrypt-watermark'))
                        );
                        delete_post_meta($video_id,'watermarked_file_id');
                        delete_post_meta($video_id,'watermarked_files');
                        delete_post_meta($video_id,'watermark_status');
                    }else{
                       $data = array(
                            'status' => false,
                            'video_status' => $check_status['status'],
                            'message' => (!empty($check_status['message'])?$check_status['message']:_x('There was some error in checking video status!','','videoencrypt-watermark'))
                        ); 
                    }
                    
                }
            }else{
                //delete_post_meta($video_id,'watermarked_file_id');
                //delete_post_meta($video_id,'watermarked_files');
                //delete_post_meta($video_id,'watermark_status');
                if(!empty($check_status['status'])){
                    $data = array(
                        'status' => false,
                        'message' => _x('There was some error in checking video status!','','videoencrypt-watermark')
                    );
                }
                
            }
        }else{
            $data = array(
                    'status' => false,
                    'message' => _x('Video url or id could not be found!','','videoencrypt-watermark')
                );
        }
        echo json_encode($data);
        die();
    }

    function watermark_enqueue_scripts(){
        if ( wp_script_is( 'watermark-backbone-extension', 'enqueued' ) ) {
            return;
        }
        wp_enqueue_script(
            'watermark-backbone-extension',
            plugins_url('../assets/js/media.js',__FILE__),
            array(
                'jquery',
                'media-editor', // Used in image filters.
                'media-views',
                'media-grid',
                'wp-util',
                'wp-api',
            ),
            VIDEOENCRYPT_WATERMARK_VERSION,
            true
        );

        wp_localize_script(
            'watermark-backbone-extension',
            'watermark_vars',
            array(
                'strings' => array(
                    'stats_label' => esc_html__( 'Upload', 'videoencrypt-watermark' ),
                    
                    
                ),
                'nonce'   => array(
                    'get_watermark_status' => wp_create_nonce( 'get_watermark_status' ),
                ),
            )
        );
        wp_enqueue_style(
            'watermark-backbone-extension-css',
            plugins_url('../assets/css/watermarker.css',__FILE__),
            array(),
            VIDEOENCRYPT_WATERMARK_VERSION
        );
    }

    function check_status($attachment){
        $status = 'not-watermarked';
        if ( ! isset( $attachment->ID ) ) {
            return $status;
        }
        $check_status = get_post_meta($attachment->ID,'watermark_status',true);
        if(!empty($check_status)){
            $status = $check_status;
        }
        return $status;
    }

    function show_watermark_status($attachment){
        $return = '';
        if ( ! isset( $attachment->ID ) ) {
            return;
        }
        $status = $this->check_status($attachment);
        if(isset($status)){
            switch ($status) {
                /*case 'not-uploaded':
                    $return = '<a class="wplms_watermark button button-primary" href="javascript:void();">'._x('Watermark','videoencrypt-watermark').'</a>';
                    break;  
                case 'uploaded':
                   $return = '<a class="wplms_watermark" href="javascript:void();">'._x('Watermark','videoencrypt-watermark').'</a>';
                    break; 
                
                case 'not-downloaded':
                    $return = '';
                    break;
                case 'downloaded':
                    $return = '';
                    break;*/
                case 'not-watermarked':
                    $return = '<a class="wplms_watermark button button-primary" href="javascript:void(0);" data-id="'.$attachment->ID.'" data-url="'.$attachment->guid.'">'._x('Upload for watermarking','videoencrypt-watermark').'</a>';
                    break; 
                case 'processing' :
                //show progress bar
                    $return = '<a class="videotube_watermark_check_video_status button button-primary" href="javascript:void(0);" data-id="'.$attachment->ID.'"  data-url="'.$attachment->guid.'">'._x('Check Status','videoencrypt-watermark').'</a>';
                    break;
                case 'download_now' :
                //download now button
                    $return = '<a class="download_watermarked_files button button-primary" href="javascript:void(0);" data-id="'.$attachment->ID.'"  data-url="'.$attachment->guid.'">'._x('Download now','videoencrypt-watermark').'</a>';
                    break;    

                case 'watermarked':
                    
                    $watermarked_video = get_post_meta($attachment->ID,'watermarked_file_id',true);
                    if(!empty($watermarked_video) &&  get_permalink( $watermarked_video )){
                        $return = '<p>'._x('Already watermarked','videoencrypt-watermark').'</p>';
                        $return .= '<br><a  href="javascript:void(0);" class="show_watermarking_details">'._x('See details','videoencrypt-watermark').'</a>';
                        $return .= '<table class="video_watermarking_details">

                        <tr><td>'._x('watermarked file edit link','videoencrypt-watermark').'</td><td><a href="'.get_edit_post_link($watermarked_video ).'">'._x('Edit','videoencrypt-watermark').'</a></td><tr>
                        <tr><td>'._x('watermarked file attachment page','videoencrypt-watermark').'</td><td><a target="_blank" href="'.get_permalink( $watermarked_video ).'">'.get_permalink( $watermarked_video ).'</a></td><tr>';
                        
                    }else{
                        //delete the meta watermarked_file_id
                        delete_post_meta($attachment->ID,'watermarked_file_id');
                        delete_post_meta($attachment->ID,'watermark_status');
                        delete_post_meta($attachment->ID,'watermarked_files');
                        $return = '<a class="wplms_watermark button button-primary" href="javascript:void(0);" data-id="'.$attachment->ID.'" data-url="'.$attachment->guid.'">'._x('Watermark','videoencrypt-watermark').'</a>';
                    }
                    
                    break;
                default:
                    $return = '';
                    break;
            }
        }
        return $return;  
    }

    function add_watermark_button($response, $attachment) {
        if ( ! isset( $attachment->ID ) ) {
            return $response;
        }
        if(!in_array($attachment->post_mime_type, $this->get_video_allowed_mime_types()))
            return $response;
        $watermarked_file =get_post_meta($attachment->ID,'watermarked_file',true);
        $encrypted_file =get_post_meta($attachment->ID,'encrypted_file',true);
        if(!empty($encrypted_file) && $encrypted_file == 1){
            return $response;
        }else{
            if(!empty($watermarked_file) && $watermarked_file == 1){

                $response['watermark'] = _x('Watermarked File','','videoencrypt-watermark');
            }else{
                $response['watermark'] = $this->show_watermark_status($attachment);
            }
        }
        

        return $response;
    }

    function url_to_string($url){
        
        $url = basename($url);
        $url_arr = explode('.',$url);
        $url = $url_arr[0];
        
        return $url;
    }

    function _fetch_remote_file( $url,$path ) {
     
        // extract the file name and extension from the url
        $file_name  = basename( $url );
        $upload     = false;
        $new_file = trailingslashit($path) . "$file_name";
        $ifp = @ fopen( $new_file, 'wb' );
        if ( ! $ifp ) {

                return array( 'error' => sprintf( __( 'Could not write file %s' ), $new_file ) );
        }

        @fwrite( $ifp, '' );
        fclose( $ifp );
        clearstatcache();
        $upload =true;
        // Set correct file permissions
        $stat  = @ stat( dirname( $new_file ) );
        $perms = $stat['mode'] & 0007777;
        $perms = $perms & 0000666;
        @ chmod( $new_file, $perms );
        clearstatcache();

        // Compute the URL

        if ($upload ) {
            // get placeholder file in the upload dir with a unique, sanitized filename
            


            $max_size = (int) apply_filters( 'import_attachment_size_limit', 0 );
            $videotube_curl_timelimit =apply_filters('videotube_curl_timelimit',120);  
            $response = wp_remote_get( $url ,array('timeout' => $videotube_curl_timelimit));
            if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {

                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                $headers = $response['headers'];
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents( $new_file, $response['body'] );
                //
            } else {
                // required to download file failed.
                @unlink( $new_file );

                return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond' ) );
            }

            $filesize = filesize( $new_file );

            if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
                @unlink( $new_file );

                return new WP_Error( 'import_file_error', esc_html__( 'Remote file is incorrect size' ) );
            }

            if ( 0 == $filesize ) {
                @unlink( $new_file);

                return new WP_Error( 'import_file_error', esc_html__( 'Zero size file downloaded' ) );
            }

            if ( ! empty( $max_size ) && $filesize > $max_size ) {
                @unlink( $new_file );

                return new WP_Error( 'import_file_error', sprintf( esc_html__( 'Remote file is too large, limit is %s' ), size_format( $max_size ) ) );
            }
        }


        return $new_file;
    }
    

    function watermark_vidtube_video(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','videoencrypt-watermark');
            die();
        }
        $videotube_api_key = '';
        $this->get_option();
        if(!empty($this->videotube_settings) && !empty($this->videotube_settings['videotube_api_key'])){
            $videotube_api_key = $this->videotube_settings['videotube_api_key'];
        }
        if(!empty($_POST['key'])){
            if(is_numeric($_POST['video'])){
                $attachment_id =  sanitize_text_field($_POST['video']);
                $url = wp_get_attachment_url($attachment_id);
                //http://localhost/wplms/wp-json/videotube/v1/video/watermark
                
                $args = array(
                    'method'      => 'POST',
                    'timeout'     => apply_filters('watermark_vidtube_video_request_timeout',120),
                    'httpversion' => '1.1',
                    
                    'body' => array('video'=>$url, 'key' => sanitize_text_field($_POST['key']) ,'videotube_api_key'=>$videotube_api_key),
                );
                
                $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/video/watermark',$args );
                $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
                
                if(!empty($result_body['files'])){
                    $data = array(
                        'status' => 1,
                        'files'=>$result_body['files'],
                        'message'=>_x('Video watermarked on server','','videoencrypt-watermark'),
                    );
                }else{

                    $data = array(
                        'status' => 0,
                        'message'=>((!empty($result_body) && !empty($result_body['message']))?$result_body['message']:_x('There was some error','','videoencrypt-watermark')),
                    );
                }
            }
        }
        echo json_encode($data);
        die();
    }

    function fetch_media_url(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_videotube_en_security') || !is_user_logged_in()){
            _e('Security check Failed. Contact Administrator.','videoencrypt-watermark');
            die();
        }
        $videotube_api_key = '';
        $this->get_option();
        if(!empty($this->videotube_settings) && !empty($this->videotube_settings['videotube_api_key'])){
            $videotube_api_key = $this->videotube_settings['videotube_api_key'];
        }
        $attachment_id =  sanitize_text_field($_POST['video']);
        if(is_numeric($attachment_id )){
            $url = wp_get_attachment_url($attachment_id);
            $videotube_curl_timelimit =apply_filters('videotube_curl_timelimit',120);
            $args = array(
                'method'      => 'POST',
                'timeout'     => $videotube_curl_timelimit ,
                
                'body' => array( 'video' => $url ,'videotube_api_key'=>$videotube_api_key),
            );


            //http://localhost/wplms/wp-json/videotube/v1/video/upload
            $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/video/upload',$args );

            if(!empty($result) && !is_wp_error($result)){
                $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
                if(!empty($result_body['key']) && $result_body['status']){
                    $data = array(
                        'status' => 1,
                        'key'=>$result_body['key'],
                        'message'=>_x('Video uploaded on server.Ready to watermark.','','videoencrypt-watermark'),
                    );
                    update_post_meta($attachment_id,'watermark_status','processing');
                }else{

                    $data = array(
                        'status' => 0,
                        'message'=>(!empty($result_body['message'])?$result_body['message']:_x('There was some error in uploading the video ','','videoencrypt-watermark')),
                    );
                }
            }else{

                $data = array(
                    'status' => 0,
                    'message'=>(!empty($result_body['message'])?$result_body['message']:_x('There was some error in uploading the video ','','videoencrypt-watermark')),
                );
            }
                
        }
        echo json_encode($data);
        die();
    }

    function print_media_templates(){
        
        $data = array(
            'security'=>wp_create_nonce('videotube_security'),
            'privacy_options' =>$this->privacy_options,
            'ajax_url' => admin_url( 'admin-ajax.php' ) ,
            'translations'=>array(
                'more_chars'=> __( 'Please enter more characters','videoencrypt-watermark'),
                'select_courses' => __( 'Select Course','videoencrypt-watermark'),
                'watermark'=>_x('Upload for watermarking','','videoencrypt-watermark'),
                'watermarked'=>_x('Downloaded','','videoencrypt-watermark'),
                'selected_items'=>_x('Please select a video to watermark','','videoencrypt-watermark'),
                'upload_error' => _x('There was some error appeared on server while uploading video.Please try after some time','','videoencrypt-watermark'),
                'watermark_error' => _x('There was some error appeard on server  while watermarking video.Please try after some time','','videoencrypt-watermark'),
                'set_privacy_label' => _x('Set privacy of video','','videoencrypt-watermark'),
                'set_privacy' => _x('Set privacy','','videoencrypt-watermark'),
                'enter_names' =>  __('Enter Student Usernames/Emails, separated by comma','videoencrypt-watermark'),
                'privacy_set' =>  __('Privacy set','videoencrypt-watermark'),
                'video_uploaded' =>  __('Video uploaded! Added to queue for watermarking','videoencrypt-watermark'),
                'check_status' => _x('Check Status','','videoencrypt-watermark'),
                'status_error' =>  _x('There was some error appeared on server while fetching video status.Please try after some time','','videoencrypt-watermark'),
                'download_now' => _x('Download now','','videoencrypt-watermark'),
                'uploading' => _x('Uploading','','videoencrypt-watermark'),
                'uploaded' => _x('Uploaded','','videoencrypt-watermark'),
            ),
        );
       
        $data = apply_filters('wplms_videotube_watermark_after_data',$data);

        

        wp_enqueue_script('wplms_videotube_watermark_js',plugins_url('../assets/js/videotube_watermark.js',__FILE__),array('jquery'),VIDEOENCRYPT_WATERMARK_VERSION,true);


        wp_localize_script('wplms_videotube_watermark_js','wplms_videotube_watermark_data',$data);
        wp_nonce_field('wplms_videotube_en_security','wplms_videotube_en_security');
        
    }

    function get_video_mimetypes(){
        return apply_filters('videotube_watermarker_get_video_mimetypes',array(
            'mp4'=>array('video/mp4'),
            'm4v'=>array('video/mp4'),


            'mov'=>array('video/quicktime'),
            'wmv'=>array('video/x-ms-wmv'),
            'avi'=>array('video/avi'),
            'mpg'=>array('video/mpeg'),
        ));
    }

    function get_video_allowed_mime_types(){
        $mime_types = array();
        if(!empty($this->get_video_mimetypes())){
            foreach ($this->get_video_mimetypes() as $key => $mimes) {
                $mime_types =  array_merge($mime_types, $mimes);
            }
        }
        
        return $mime_types;
    }

    function check_video_status($url){
        //make api hit and get the video status 
        $videotube_curl_timelimit =apply_filters('videotube_curl_timelimit',120);
        $videotube_api_key = '';
        $this->get_option();
        if(!empty($this->videotube_settings) && !empty($this->videotube_settings['videotube_api_key'])){
            $videotube_api_key = $this->videotube_settings['videotube_api_key'];
        }
        $args = array(
            'method'      => 'POST',
            'timeout'     => $videotube_curl_timelimit ,
            
            'body' => array( 'video' => $url ,'videotube_api_key'=>$videotube_api_key),
        );


        //http://localhost/wplms/wp-json/videotube/v1/video/upload
        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/video/watermark_status',$args );
        if(!empty($result) && !is_wp_error($result)){

            $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
            return $result_body;
        }
        return false;
    }



    function videotube_read_file($file,$start_point,$chunksize){
        $return = '';
        if ($fileo = fopen($file, 'rb')) {
            if(!empty($start_point)){
                fseek($fileo,$start_point);
            }
            if(feof($fileo)){
                return 0;
            }
            $return = fread($fileo, $chunksize);
            ob_flush(); 
            flush(); 
            fclose($fileo);
        }
            
        return $return;
    }


    function videotube_write_into_file($newfile,$contents){
        if(!file_exists($newfile)){
           if ($fileo = fopen($newfile, 'wb')) {
                fwrite($fileo, $contents);
            } 
        }else{
           if ($fileo = fopen($newfile, 'ab')) {
                fwrite($fileo, $contents);
            } 
        }
        
        fclose($fileo);
    }

    function get_post_size(){
        $post_size = apply_filters('videotube_post_size_settings',((1*1024*1024)-1024));
        if ( function_exists( 'ini_get' )){
            $post_size = ini_get('post_max_size') ;
            $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
        
            $post_size = intval($post_size);
            $post_size = (($post_size*1024*1024)-1024);//taking 1Kb less due to insecurity disorder of mine
        }
        return (3*1024*1024)-1024; //for testing purpose
        //return $post_size;
        
    }

}
add_action('init',function (){
    Videoencrypt_Watermark_Watermarkor::init();
});