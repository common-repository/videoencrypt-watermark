<?php
/**
 * Settings in Admin
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	videotube/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Videoencrypt_Watermark_Settings{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Videoencrypt_Watermark_Settings();
        return self::$instance;
    }

	private function __construct(){

		$this->videos_stats = array();
		$this->settings = get_option(VIDEOENCRYPT_WATERMARKED_OPTION);

		add_submenu_page('upload.php',__('Videoencrypt Watermark','videoencrypt-watermark'),__('WaterMark Videos','videoencrypt-watermark'),'manage_options','videoencrypt-watermark',array($this,'main_settings'),9);

		add_action('admin_enqueue_scripts',array($this,'set_videoencrypt_icon'));

	}

	function set_videoencrypt_icon(){
		?>
		<style type="text/css">
			li#toplevel_page_videoencrypt-watermark a .wp-menu-image.dashicons-before img{
			    width: 25px;
			    padding-top:5px;
			}
			.vewm_settings_wrapper {
			    display:flex;
			    flex-direction:row;
			    align-items: center;
			    margin: 30px 30px 30px 0;
			}


			@media(max-width:768px){
			    .vewm_settings_wrapper {
			        flex-direction:column;
			    }
			}

			.watermark_settings div.top {
			    display:flex;
			    flex-direction:row;
			    align-items: center;
			    justify-content: space-between;
			    margin:1rem;
			}
			.watermark_settings div.top label+span{
			     opacity:0;
			     transition:0.3s all ;
			}
			.watermark_settings div.top:hover label+span{
			    opacity:1;
			}
			.watermark_settings div.top label{
			    display:block;
			    font-size:16px;
			    font-weight:700;    
			}

	        .videoenc_message{
	        	display:none;
	        }
	        .videoenc_email_wrapper{display:none;}
	        .videoencrypt_create_account img{
			    width: 50px;
			    padding: 10px;
			}
			.videoencrypt_create_account{
			    display:flex;
			    flex-direction:row;
			    align-items: center;
			    justify-content: start;
			    width: auto;
			}

	        .videotube_tab_content_wrapper {
			    padding: 15px;
			    display: block;
			    margin-top: -8.1px;
			    border-top: 1px solid rgba(0,0,0,0.1);
			}

			.videotube_tabs_list ul.nav-tab-wrapper {border-bottom:none;}

			ul.nav-tab-wrapper .nav-tab.active{
			    border-bottom:none;
			}
			.videotube_tab_content_wrapperr {
			    padding: 1rem;
			    border-top: 1px solid rgba(0,0,0,0.1);
			    margin-top: -0.47rem;
			}
			ul.nav-tab-wrapper .nav-tab{
			    border-bottom:1px solid rgba(0,0,0,0.1);
			}
	        .videotube_tab_content_wrapper .videotube_tab_content{display:none;}
			.videotube_tab_content_wrapper .videotube_tab_content.active{display:block;}
			.videotube_tabs_list ul{list-style:none;}
			.videotube_tabs_list ul li{
			  
			    cursor:pointer;
			}
			.videotube_tab_content_wrapper {
			    padding: 15px;
			}
			.videotube_tab_content ol li{
			    display:grid;
			    grid-template-columns:40% 50%;
			    align-items:center;
			}
			.videotube_tab_content ol li span:nth-child(2) {
			    justify-self: center;
			}

	        img#picsrc {
			    width: auto !important;
			}
			.watermark_preview .logo.topleft{
			    position:absolute;
			    top:5px;
			    left:5px;
			}
			.watermark_preview .logo.topright{
			    position:absolute;
			    top:5px;
			    right:5px;
			}
			.watermark_preview .logo.bottomright{
			    position:absolute;
			    right:5px;
			    bottom:5px;
			}
			.watermark_preview .logo.bottomleft{
			    position:absolute;
			    left:5px;
			    bottom:5px;
			}
			.watermark_preview .logo.center {
			    position:absolute;
			    top:50%;
			    left:50%;
			    transform:translate(-50%,-50%);
			}
			.watermark_preview{
			    position:relative;
			    
			}
		</style>
		<?php
	}

	function get_settings(){
		if ( function_exists( 'wp_max_upload_size') ) {
			$size =  wp_max_upload_size() ; 
 			$size = $size/1048576;
		}else{
			$size = 1048576;
		}
 			
		return apply_filters('Videotube_Watermark_Settings',array(
			
			
			array(
				'label' => __( 'Videoencrypt api key', 'videoencrypt-watermark' ),
				'name' => 'videotube_api_key',
				'type' => 'text',
				'desc' => __( 'Put here your Videoencrypt api key for encryption ', 'videoencrypt-watermark' ),
			),
			array(
				'label' => __( 'Upload Watermark logo', 'videoencrypt-watermark' ),
				'name' => 'videoencrypt_watermark_logo',
				'type' => 'upload',
				'desc' => __( 'Upload Watermark logo here to be appearing on the video.<br> (Ideal Size of image would be 50px X 50px)', 'videoencrypt-watermark' ),
			),
			array(
				'label' => __( 'Set position of Watermark logo', 'videoencrypt-watermark' ),
				'name' => 'videoencrypt_watermark_postition',
				'type' => 'select',
				'desc' => __( 'Select the position to which the logo should appear on the video ', 'videoencrypt-watermark' ),
				'options' => array(
					'center' => _x('Center','','videoencrypt-watermark'),
					'topright' => _x('Top Right','','videoencrypt-watermark'),
					'topleft' => _x('Top Left','','videoencrypt-watermark'),
					'bottomright' => _x('Bottom Right','','videoencrypt-watermark'),
					'bottomleft' => _x('Bottom Left','','videoencrypt-watermark'),

				)
			),
			array(
				'label' => __( 'Logo padding', 'videoencrypt-watermark' ),
				'name' => 'videoencrypt_watermark_logo_padding',
				'type' => 'number',
				'desc' => __( 'Set the padding of the logo', 'videoencrypt-watermark' ),
				'std' => 5,
			),

			
		));	
	}


	function get_main_tabs(){
		$tabs = array(
			'settings'=>array(
				'label' => _x('Settings','','videoencrypt-watermark'),
				'content_callback'=>'settings',
			),
			'account'=>array(
				'label' => _x('Account','','videoencrypt-watermark'),
				'content_callback'=>'videowatermark_account',
			),
			'extras'=>array(
				'label' => _x('Extras','','videoencrypt-watermark'),
				'content_callback'=>'videowatermark_extras',
			),
		);

		return apply_filters('videoencrypt_watermark_tabs',$tabs);
	}

	function get_tabs(){
		$tabs = array(
			'queued_videos'=>array(
				'label' => _x('Currently processing on videoencrypt','','videoencrypt-watermark'),
				'content_callback'=>'queued_videos',
			),
			'completed_video'=>array(
				'label' => _x('Completed on videoencrypt','','videoencrypt-watermark'),
				'content_callback'=>'completed_video',
			),
			'failed_video'=>array(
				'label' => _x('Failed on videoencrypt','','videoencrypt-watermark'),
				'content_callback'=>'failed_video',
			),
		);

		return apply_filters('videotube_tabs',$tabs);
	}

	function failed_video($api_key){
		if(empty($this->videos_stats)){
			$args = array(
	            'method'      => 'POST',
	            'timeout'     => 120,
	            
	            'body' => array('videotube_api_key'=>$api_key),
	        );
	        
	        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/video/watermark_videos',$args );
	        $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
	        $this->videos_stats = $result_body;
		}
		if(!empty($this->videos_stats) && !empty($this->videos_stats['videos']['failed'])){
        		
    		echo '<ol class="videotube_failed_videos">';
    		
    		foreach ($this->videos_stats['videos']['failed'] as  $video) {
    			$video_id = attachment_url_to_postid($video);
    			if(!empty($video_id)){
    				$encrypted_video = get_post_meta($video_id,'encrypted_file_id',true);
    			}
    			
    				echo '<li>
	    			<span >'.get_the_title($video_id).'</span>
	    			<span>

	    			<a target="_blank" class="dashicons 
dashicons-visibility" title="Orignal" href="'.admin_url('upload.php?item='.$video_id).'" ></a> 
	    			</span>
	    			</li>';
    			
    		}
    		echo '</ol>';
    	}else{
    		echo _x('No data Available','','videoencrypt-watermark');
    	}
	}

	function queued_videos($api_key){
		if(empty($this->videos_stats)){
			$args = array(
	            'method'      => 'POST',
	            'timeout'     => 120,
	            
	            'body' => array('videotube_api_key'=>$api_key),
	        );
	        
	        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/video/watermark_videos',$args );
	        $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
	        $this->videos_stats = $result_body;
    	}
        if(!empty($this->videos_stats) ){
        	if(!empty($this->videos_stats['videos']['processing'])){
        		
        		echo '<ol class="videotube_processing_videos">';
        		
        		foreach ($this->videos_stats['videos']['processing'] as  $video) {
        			$video_id = attachment_url_to_postid($video);
        			
        			
        			echo '<li>
        			<span >'.get_the_title($video_id).'</span>
        			<span>
        			<a target="_blank" class="dashicons 
dashicons-visibility" title="original" href="'.admin_url('upload.php?item='.$video_id).'" title="Orignal"></a>
        			</span>
        			</li>';
        		}
        			
        		echo '</ol>';
        	}else{
	    		echo _x('No data Available','','videoencrypt-watermark');
	    	}
        	
        }else{
    		echo _x('No data Available','','videoencrypt-watermark');
    	}

	}

	function completed_video($api_key){

		if(empty($this->videos_stats)){
			$args = array(
	            'method'      => 'POST',
	            'timeout'     => 120,
	            
	            'body' => array('videotube_api_key'=>$api_key),
	        );
	        
	        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/video/watermark_videos',$args );
	        $result_body = json_decode(wp_remote_retrieve_body( $result ),true);

	        $this->videos_stats = $result_body;
		}
		if(!empty($this->videos_stats) && !empty($this->videos_stats['videos']['completed'])){
        		
    		echo '<ol class="videotube_completed_videos">';
    		
    		foreach ($this->videos_stats['videos']['completed'] as  $video) {
    			$video_id = attachment_url_to_postid($video);
    			if(!empty($video_id)){
    				$encrypted_video = get_post_meta($video_id,'encrypted_file_id',true);
    			}
    			if(!empty($encrypted_video)){
    				echo '<li>
	    			<span >'.get_the_title($video_id).'</span>
	    			<span>

	    			<a target="_blank" class="dashicons 
dashicons-visibility" title="Orignal" href="'.admin_url('upload.php?item='.$video_id).'" ></a> | 
	    			<a target="_blank" class="dashicons 
dashicons-visibility" href="'.get_permalink($encrypted_video).'" title="Encrypted Video"></a>
	    			<a target="_blank" class="dashicons 
dashicons-edit" href="'.get_edit_post_link($encrypted_video).'" title="Edit encrypted Video"></a>
	    			</span>
	    			</li>';
    			}
    			
    		}
    		echo '</ol>';
    	}else{
    		echo _x('No data Available','','videoencrypt-watermark');
    	}
	}

	function main_settings(){
		$ctab = (isset($_GET) && !empty($_GET['tab']))?sanitize_text_field($_GET['tab']):'settings';
		echo '<div class="videotube_tabs_wrapper">';
        echo '<div class="videotube_tabs_list main_videotube_tabs_list"><ul class="nav-tab-wrapper">';
        foreach ($this->get_main_tabs() as $k => $tab) {
        	$active = '';
        	if($ctab == $k){
        		$active = 'active';
        	}
        	echo '<li id="'.$k.'" class="nav-tab '.$active.'"><span ><a href="?page=videoencrypt-watermark&tab='.$k.'">'.$tab['label'].'</a></span></li>';
        }
        
        echo '</ul></div>';

        echo '<div class="videotube_tab_content_wrapperr">';
    	echo '<div class="videotube_tab_content " >';
		$str = $this->get_main_tabs()[$ctab]['content_callback'];

        if(method_exists($this, $str)){
			$this->$str($api_key);
		}elseif(function_exists($str)){
			$str($api_key);
		}
    	echo '</div>';
        echo '</div>';

        ?>
        <script>
        	jQuery(document).ready(function($){
				$('.videotube_tabs_list:not(.main_videotube_tabs_list) li').on('click',function (){
					var $this = $(this);
					var current_tab = $this.attr('id');
					$this.addClass('active');
					$this.addClass('nav-tab-active');
					
					$this.closest('.videotube_tabs_wrapper').find('.videotube_tab_content_wrapper').find('.'+current_tab).addClass('active');
					$this.closest('.videotube_tabs_wrapper').find('.videotube_tabs_list li').each(function(){
						var $_this = $(this);
						if($_this.attr('id') != current_tab){
							$_this.removeClass('active');
							$_this.removeClass('nav-tab-active');
							$this.closest('.videotube_tabs_wrapper').find('.videotube_tab_content_wrapper').find('.'+$_this.attr('id')).removeClass('active');
						}
	    			});
				});
				jQuery('.videotube_tabs_list:not(.main_videotube_tabs_list) li:first').trigger('click');
        	});
        </script>
        <?php
	}

	function settings(){
		wp_enqueue_script('jquery');
	  	wp_enqueue_script('media-upload');
	  	wp_enqueue_script('thickbox');
	  	wp_enqueue_script('jquery-ui-slider');
	  	
		$this->save();

		$this->show_create_account_notice();

		echo '<form method="post">';
		wp_nonce_field('Videotube_Watermark_Settings');   
		

		$settings = $this->get_settings();
		?>
		<div class="vewm_settings_wrapper">
			<div class="watermark_preview" style="width: 640px; height: 360px; background-size: contain;background: url('<?php echo plugins_url('../assets/images/poster.jpeg',__FILE__)?>')">
	        </div>
	        <div class="watermark_settings">
				<?php $this->generate_form($settings); ?>
			</div>
		</div>

		<?php
		echo '<input type="submit" name="save_Videotube_Watermark_Settings" class="button button-primary" value="'.__('Save Settings','videoencrypt-watermark').'" />';
		echo '</form>';
		
	}

	function videowatermark_extras(){
		$this->show_create_account_notice();
		?>
		<div class="vewm_extras_wrapper">
			<div class="vewm_extras_image">
				<h2>How Watermarking Works?</h2>
			</div>
			<img src="<?php echo plugins_url('../assets/images/videoencrypthowvideowatermarkingworks.svg',__FILE__)?>" />
			<hr />
			<p>For questions contact us at <a href="mailto:info@videoencrypt.com">info@videoencrypt.com</a> or <a href="mailto:vibethemes@gmail.com">vibethemes@gmail.com</a></p>
		</div>
		<style>
		.vewm_extras_image{display:flex;align-items:start;}
		.vewm_extras_image img{
		    margin-right:30px;
		    margin-bottom:30px;
		}
		</style>
		<?php
	}

	function videowatermark_account(){
		$this->show_create_account_notice();
		$api_key = '';
        if(!empty($this->settings) && !empty($this->settings['videotube_api_key'])){
        	$api_key =$this->settings['videotube_api_key'];
        }
        if(!empty($api_key)){

	        echo '<div class="videotube_tabs_wrapper">';

	        $args = array(
	            'method'      => 'POST',
	            'timeout'     => 120,
	            
	            'body' => array('videotube_api_key'=>$api_key),
	        );
	        
	        $result = wp_remote_post( VIDEOENCRYPT_WATERMARK_URL.'/wp-json/'.VIDEOENCRYPT_WATERMARK_API_NAMESPACE.'/user/watermark_upload_quota/',$args );
	        $result_body = json_decode(wp_remote_retrieve_body( $result ),true);
            
            if(!empty($result_body['quota'])){
                echo '<div class="upload_quota_indication"><label><h2>'._x('Watermark Upload quota on videoencrypt.com server','','videotube').'</label><label>'.round($result_body['quota']/1024,2).' Mbs</label></h2></div>

                <style>
                .upload_quota_indication label{
                	margin:20px;
                }
                
                </style>
                ';
            }else{
            	if(!empty($result_body['message'])){
            		echo $result_body['message'];
            	}
            }

	        echo '<div class="videotube_tabs_list"><ul class="nav-tab-wrapper">';
	        foreach ($this->get_tabs() as $k => $tab) {
	        	echo '<li id="'.$k.'" class="nav-tab"><span >'.$tab['label'].'</span></li>';
	        }
	        
	        echo '</ul></div>';
	        echo '<div class="videotube_tab_content_wrapper">';
	        foreach ($this->get_tabs() as $k => $tab) {
	        	echo '<div class="videotube_tab_content '.$k.'" data-tab-key="'.$k.'">';
	        		$str = $tab['content_callback'];
	        		if(method_exists($this, $tab['content_callback'])){
	        			$this->$str($api_key);
	        		}elseif(function_exists($tab['content_callback'])){
	        			$str($api_key);
	        		}
	        		
	        	echo '</div>';
	        }

            
            
            echo '</div></div>';
        }else{
        	echo '<div class="message error">'._x('Please configure videoencrypt api key !','','videoencrypt-watermark').'</div>';
        }

        
	}

	function show_create_account_notice(){
		//show create an account button
		if(empty($this->settings) || empty($this->settings['videotube_api_key'])){
			wp_nonce_field('videoencrypt_create_account','videoencrypt_create_account');
			?>
			<div class="notice notice-warning is-dismissible videoencrypt_create_account">
			    <img src="<?php echo plugins_url('../assets/images/videoencryptwatermark.svg',__FILE__)?>">
			    <div class="">
				    <span><?php echo _x('Dont have an account on videoencrypt.com? Dont worry, get 50MB free watermarking space on videoencrypt.com!','','videoencrypt-watermark');?></span>
				    <button class="button-primary create_acccount_button"><?php echo _x('Create an account','','videoencrypt-watermark')?></button>
				    <div class="videoenc_email_wrapper">
				    	<input type="email" class="videoenc_email" placeholder="<?php echo _x('Enter Email','','videoencrypt-watermark')?>">
				    	<button class="button-primary videoenc_send_email"><?php echo _x('Get Link','','videoencrypt-watermark')?></button>
				    	<div class="videoenc_message message notice">

				    	</div>
				    </div>
				</div>
			</div>
			<?php
		}
		
		?>
		
        <script>
    		
    		jQuery(document).ready(function ($){

    			$('.create_acccount_button').on('click',function(event){
    				event.preventDefault();
    				$('.videoenc_email_wrapper').toggle();
    			});


    			$('.videoenc_send_email').on('click',function(event){
    				event.preventDefault();
    				var $this = $(this);
    				var text = $this.text();
    				$this.text('.....');
    				var email = $('.videoenc_email').val();
    				var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    				if(re.test(String(email).toLowerCase())){
    					//send ajax bro:
    					$.ajax({
				          	type: "POST",
				          	url: ajaxurl,
				          	dataType: "json",
				          	data: { action: 'send_email_videoencrypt', 
				                  security:jQuery('#videoencrypt_create_account').val(),
				                  email: email,
				                },
				          	cache: false,
				          	success: function (json) {
				          		$this.text(text);
				          		if(json){
				          			$('.videoenc_message').show(500);
				          			$('.videoenc_message').html(json.message);
				          		}
				          	}
				        });
    				}else{
    					alert('<?php echo _x('Please enter a valid email!','','videoencrypt-watermark')?>');
    				}
    			});


    			
    		});
    	</script>
        <?php
	}

	function generate_form($settings){
		
		foreach($settings as $setting ){
			echo '<div class="top">';
			switch($setting['type']){
				case 'textarea':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><textarea name="'.$setting['name'].'" style="max-width: 560px; height: 240px;border:1px solid #DDD;">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'</textarea>';
					echo '</div>';
				break;
				case 'select':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><select name="'.$setting['name'].'" class="logofield logoposition">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '</div>';
				break;
				case 'checkbox':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
					echo '</div>';
				break;
				case 'number':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><input class="logofield" type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" />';
					echo '</div>';
				break;
				case 'text':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" />';
					echo '</div>';
				break;
				case 'upload':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp">'.$this->image_uploader_field($setting);
					echo '</div>';
					 
				break;
				case 'range':
					echo '<div scope="row" class="titledesc"><label>'.$setting['label'].'</label><span>'.$setting['desc'].'</span></div>';
					echo '<div class="forminp"><input class="position_slider" min="0" max="500" type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" /><input min="0" max="500" type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:$setting['std']).'" />';
					echo '</div>';
				break;
				
			}
			echo '</div>';
		}	
	}


	function save(){
		

		if(!isset($_POST['save_Videotube_Watermark_Settings']))
			return;

		if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'Videotube_Watermark_Settings') ){
		     echo '<div class="error notice is-dismissible"><p>'.__('Security check Failed. Contact Administrator.','videoencrypt-watermark').'</p></div>';
		}

		$settings = $this->get_settings();
		foreach($settings as $setting){
			if(isset($_POST[$setting['name']])){
				$this->settings[$setting['name']] = sanitize_text_field($_POST[$setting['name']]);
			}else if($setting['type'] == 'checkbox' && isset($this->settings[$setting['name']])){
				unset($this->settings[$setting['name']]);
			}
		}

		update_option(VIDEOENCRYPT_WATERMARKED_OPTION,$this->settings);
		echo '<div class="updated notice is-dismissible"><p>'.__('Settings Saved.','videoencrypt-watermark').'</p></div>';
	}

	function image_uploader_field($setting) {
		ob_start();
		if(!empty($this->settings) && !empty($this->settings['videoencrypt_watermark_logo'])){
			$url = $this->settings['videoencrypt_watermark_logo'];
		}
		 ?>
		<input id="<?php echo $setting['name'];?>" name="<?php echo $setting['name'];?>" type="text"
		         value="<?php echo $url;?>" style="width:200px;display:block" class="logofield logoimagefield"/>
		<input id="my_upl_button" type="button" class="button-primary" value="Upload Image" /><br/>
		<img src="<?php echo $url;?>" style="width:200px;" id="picsrc" />
		<script>
			jQuery(document).ready( function($) {
				var media_uploader='';
				jQuery('#my_upl_button').click(function() {

					if ( media_uploader ) {
				      media_uploader.open();
				      return;
				    }
					media_uploader = wp.media.frames.media_uploader = wp.media({
				        title: "<?php _e('Select Watermark Logo','videoencrypt-watermark'); ?>",
				        library: {
				            type: 'image',
				            query: false
				        },
				        button: {
				            text: "<?php _e('Set Watermark Image','videoencrypt-watermark'); ?>",
				        },
				        multiple: false
				    });

				    // Create a callback when the uploader is called
				    media_uploader.on( 'select', function() {
				        var selection = media_uploader.state().get('selection');
				            selection.map( function( attachment ) {
				            attachment = attachment.toJSON();
				            
				            var url_image='';
				            if( attachment.sizes){
				                if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
				                else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
				                else url_image=attachment.sizes.full.url;
				            }
				            
				            jQuery("#<?php echo $setting['name'];?>").val(url_image);		
				            jQuery("#picsrc").attr('src',url_image);		
			            	jQuery('.logofield').trigger('change');
				         });

				    });
				    // Open the uploader
				    media_uploader.open();
			    }); // End on click


			    jQuery('.rangeform').on('change',function(){
			    	var $this = jQuery(this);
			    	$this.closest('td').find('input').val($this.val());
			    	jQuery('.logofield').trigger('change');
			    });	


			    jQuery('.logofield').on('change',function(){
			    	if(jQuery('.watermark_preview .logo')){
			        	jQuery('.watermark_preview .logo').remove();
			        }
			        var padding = 5;
			        var padding_html = '';
			        var position = jQuery('.logoposition').val();
			        if(position != 'center'){
			        	switch(position){
			        		case 'bottomleft':
				        		if(jQuery('input[name="videoencrypt_watermark_logo_padding"]')){
						        	padding = jQuery('input[name="videoencrypt_watermark_logo_padding"]').val();

						        }

						        padding_html = 'bottom:'+padding+'px;left:'+padding+'px';
			        		break;
			        		case 'bottomright':
			        			if(jQuery('input[name="videoencrypt_watermark_logo_padding"]')){
						        	padding = jQuery('input[name="videoencrypt_watermark_logo_padding"]').val();

						        }

						        padding_html = 'bottom:'+padding+'px;right:'+padding+'px';
			        		break;
			        		case 'topleft':
			        			if(jQuery('input[name="videoencrypt_watermark_logo_padding"]')){
						        	padding = jQuery('input[name="videoencrypt_watermark_logo_padding"]').val();

						        }

						        padding_html = 'top:'+padding+'px;left:'+padding+'px';
			        		break;
			        		case 'topright':
			        			if(jQuery('input[name="videoencrypt_watermark_logo_padding"]')){
						        	padding = jQuery('input[name="videoencrypt_watermark_logo_padding"]').val();

						        }

						        padding_html = 'top:'+padding+'px;right:'+padding+'px';
			        		break;
			        		default:

			        		break;

			        	}
			        	
			        }

			        
			        jQuery('.watermark_preview').append('<div class="logo '+jQuery('.logoposition').val()+'" style="'+padding_html+'"><img src="'+jQuery('.logoimagefield').val()+'" ></div>');
			    });
			});
		</script>
		<?php
		$html = ob_get_clean();
		return $html;
	}
}

add_action('admin_menu',function(){
	Videoencrypt_Watermark_Settings::init();
},100);

