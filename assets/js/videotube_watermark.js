jQuery(document).ready(function ($){
	jQuery('body').delegate('.show_watermarking_details','click',function (){
		jQuery(this).parent().find('.video_watermarking_details').toggle(100);
	});



	jQuery('body').delegate('.wplms_watermark','click',function (){

		var $this =  jQuery(this);

		if($this.hasClass('disabled'))
			return false;  

		$this.addClass('disabled');

		var video = $this.data('id');
		var video_url = $this.data('url');
		var video_length = 0;
		var video_height = 0;
		var video_width = 0;
		var button_label = $this.text();
		$this.text(wplms_videotube_watermark_data.translations.uploading);

		var videodom = document.querySelector('video');
		if(videodom){
			var onDurationChange = function(){
		        if(videodom.readyState){
		        	video_length = videodom.duration;
		        	video_height = videodom.videoHeight;
		        	video_width = videodom.videoWidth;
		        }
		    };
		    videodom.addEventListener('durationchange', onDurationChange);
		    onDurationChange();
		}
	    

		if(!video){
			alert(wplms_videotube_watermark_data.translations.selected_items);
			return false;
		}

		$.ajax({
          	type: "POST",
          	url: ajaxurl,
          	dataType: "json",
          	data: { action: 'videotube_watermark_ready_chunks_array', 
                  security:jQuery('#wplms_videotube_en_security').val(),
                  video: video,
                  video_length:video_length,
                  video_height:video_height,
                  video_width:video_width

                },
          	cache: false,
          	success: function (json) {
            	$this.parent().append('<div class="watermark-file-'+video+' watermark-file-vid" ><span class="file_vid_progressbar"><small></small></span><i class="prog_percent"></i></div>');
            	jQuery('.watermark-file-'+video+' .file_vid_progressbar small').css('width','2%');
				jQuery('.watermark-file-'+video+' .prog_percent').text('2%');
            	var defferred = [];
            	var current = 0;
            	$.each(json,function(i,item){
            		defferred.push(item);
            	});
            	recursive_step(current,defferred,$this);
            	//$.each() RUN loop on json and increment progress bar
            	
          	}
        });

        function recursive_step(current,defferred,$this){
        	var def_data = defferred[current];
		    if(current < defferred.length){
		    	
		        $.ajax({
		            type: "POST",
		            url: ajaxurl,
		            data: defferred[current],
		            dataType: "json",
		            cache: false,
		            success: function(myson){ 
		            	if(myson){
		            		if(myson.status){
		            			current++;
				                var width = 10 + 90*current/defferred.length;

				                jQuery('.watermark-file-'+def_data.video_id+' .file_vid_progressbar small').css('width',width+'%');
								jQuery('.watermark-file-'+def_data.video_id+' .prog_percent').text(width.toFixed(2)+'%');

				                if(defferred.length == current){
				                	
				                    $('body').trigger('watermark_end_recursive_chunks',[{video:def_data,json:myson,element:$this}]);
				                }else{
				                    recursive_step(current,defferred,$this);
				                }
		            		}else{
		            			if(myson.message){

		            				alert(myson.message);
		            				current == defferred.length;

		            				$('body').trigger('watermark_end_recursive_chunks_error',[{video:def_data,json:myson,element:$this}]);
		            				return false;
		            			}
		            		}
		            	}
		                
		            }
		        });
		    }else{
		    	$('body').trigger('watermark_end_recursive_chunks',[{video:def_data}]);
		    }
		}//End of function

		$('body').on('watermark_end_recursive_chunks',function(e, data){
			if(data.json && data.json.status && data.json.key){
				
				var video = data.video.video_id;
				var video_url = data.video.video;
				jQuery('.watermark-file-'+video+' .file_vid_progressbar').remove();
				jQuery('.watermark-file-'+video+' .prog_percent').remove();
	    		jQuery('.watermark-file-'+video).append('<span>'+wplms_videotube_watermark_data.translations.video_uploaded+'</span>');
	    		if(data.element){
	    			data.element.text(wplms_videotube_watermark_data.translations.uploaded);
	    		}
	    		if(!(jQuery('.watermark-file-'+video+' .videotube_watermark_check_video_status') && jQuery('.watermark-file-'+video+' .videotube_watermark_check_video_status').length)){
		        	jQuery('.watermark-file-'+video).append('<a class="videotube_watermark_check_video_status button button-primary" href="javascript:void(0);" data-id="'+video+'"  data-url="'+video_url+'">'+wplms_videotube_watermark_data.translations.check_status+'</a>');
	    		}
			}
			
    	});

		$('body').on('watermark_end_recursive_chunks_error',function(e, data){
    		jQuery('.watermark-file-'+video).remove();
    		if(data.json && data.json.message){
    			var message = data.json.message;
    		}
    		if(data.element){
    			data.element.removeClass('disabled');
    			data.element.text(wplms_videotube_watermark_data.translations.watermark);
    		}
    		alert((message?message:wplms_videotube_watermark_data.translations.upload_error));
    	});
	});

	jQuery('body').delegate('.videotube_watermark_check_video_status','click',function (){
		var $this = jQuery(this);
		$this.attr('disabled','disabled');
		jQuery.ajax({
          	type: "POST",
          	url: ajaxurl,
          	dataType: 'json',
          	data: { action: 'videotube_watermark_check_video_status',
                  security:jQuery('#wplms_videotube_en_security').val(),
                  video: $this.data('id'),
                  url:$this.data('url'),
            },
          	cache: false,
          	success: function (json) {
          		if(json){
          			if(json.status && json.files && json.files.length){
          				if(!($this.parent().find('.download_watermarked_files') && $this.parent().find('.download_watermarked_files').length)){
          					$this.parent().append('<a class="download_watermarked_files button button-primary" href="javascript:void(0);" data-id="'+$this.data('id')+'"  data-url="'+$this.data('url')+'">'+wplms_videotube_watermark_data.translations.download_now+'</a>');
          				}
          				
          				$this.remove();

          			}else{
          				if(json.message){
          					alert((json.message?json.message:wplms_videotube_watermark_data.translations.status_error));
          				}
          			}
          		}
          		$this.removeAttr('disabled');
          	}
        });
	});

	jQuery('body').delegate('.download_watermarked_files','click',function (){
		var $this = jQuery(this);
		var video = $this.data('id');
		if($this[0].hasAttribute('disabled'))
			return false;
		$this.attr('disabled','disabled');
		jQuery.ajax({
          	type: "POST",
          	url: ajaxurl,
          	dataType: 'json',
          	data: { action: 'download_watermarked_files',
                  security:jQuery('#wplms_videotube_en_security').val(),
                  video: $this.data('id'),
                  url:$this.data('url'),
            },
          	cache: false,
          	success: function (json) {
          		if(json.status && json.files){
            			$this.parent().append('<div class="watermark-file-'+video+' watermark-file-vid" ><span class="file_vid_progressbar"><small></small></span><i class="prog_percent"></i></div>');
		            	jQuery('.watermark-file-'+video+' .file_vid_progressbar small').css('width','2%');
						jQuery('.watermark-file-'+video+' .prog_percent').text('2%');
						var defferred1 = [];
		            	var current1 = 0;
		            	$.each(json.files,function(i,item1){
		            		defferred1.push(item1);
		            	});
		            	recursive_step1(current1,defferred1,$this);
            	
					
            	}else{
            		
            		alert((json.message?json.message:wplms_videotube_watermark_data.translations.upload_error));
            		$this.removeAttr('disabled');
					return false;
            	}
            	
          	}
        });
	});


	function recursive_step1(current,defferred,$this){
    	var def_data = defferred[current];
	    if(current < defferred.length){
	    	
	        $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            data: defferred[current],
	            dataType: "json",
	            cache: false,
	            success: function(myson){ 
	            	if(myson){
	            		if(myson.status){
	            			current++;
			                var width = 10 + 90*current/defferred.length;
			                if(width){
			                	jQuery('.watermark-file-'+def_data.video_id+' .file_vid_progressbar small').css('width',width+'%');
								jQuery('.watermark-file-'+def_data.video_id+' .prog_percent').text(width.toFixed(2)+'%');
			                }
			                

			                if(defferred.length == current){
			                	
			                    $('body').trigger('watermark_end_recursive_download_file',[{video:def_data,json:myson,element:$this}]);
			                }else{
			                    recursive_step1(current,defferred,$this);
			                }
	            		}else{
	            			if(myson.message){

	            				alert(myson.message);
	            				current == defferred.length;

	            				$('body').trigger('watermark_end_recursive_download_file_error',[{video:def_data,json:myson,element:$this}]);
	            				return false;
	            			}
	            		}
	            	}
	            }
	        });
	    }else{
	    	$('body').trigger('watermark_end_recursive_download_file',[{video:def_data,element:$this}]);
	    }
	}//End of function

	$('body').on('watermark_end_recursive_download_file',function(e, data){
		if(data.json && data.json.status){
			var video = data.video.video_id;
			var video_url = data.video.video;
			var $this = data.element;
			jQuery.ajax({
	          	type: "POST",
	          	url: ajaxurl,
	          	dataType: 'json',
	          	data: { action: 'end_videowatermark_download_file',
	                  security:jQuery('#wplms_videotube_en_security').val(),
	                  video_id: video,
	                  url:video_url,
	            },
	          	cache: false,
	          	success: function (json) {
	          		if(json){
	          			if(json.status && json.new_video){
	          				
	          				$this.text(wplms_videotube_watermark_data.translations.watermarked);
							if(!($this.parent().find('.privacy_wrapper') && $this.parent().find('.privacy_wrapper').length)){
								var select_html = '';
								select_html = '<select class="watermarked_video_privacy">';
								for (var k in wplms_videotube_watermark_data.privacy_options) {
									select_html += '<option value="'+k+'">'+wplms_videotube_watermark_data.privacy_options[k]+'</option>';
								}
								select_html += '</select>';
								
								jQuery('body').trigger('videowatermark_privacy_details_appended');
							}

	          			}else{
	          				if(json.message){
	          					alert((json.message?json.message:wplms_videotube_watermark_data.translations.status_error));
	          				}
	          			}
	          		}
	          	}
	        });
		}
		
	});

	$('body').on('watermark_end_recursive_download_file_error',function(e, data){
		jQuery('.watermark-file-'+video).remove();
		if(data.element){
			data.element.removeClass('disabled');
			data.element.text(wplms_videotube_watermark_data.translations.watermark);
		}
	});

	jQuery('body').delegate('.watermarked_video_privacy','change',function (){
		var $this = jQuery(this);
		var privacy  = $this.val();

		if(privacy){
			if(privacy=='course_students'){
				$this.parent().find('.course_select_div').show();
				$this.parent().find('.selected_users_div').hide();
			}else if(privacy=='selected_users'){
				$this.parent().find('.selected_users_div').show();
				$this.parent().find('.course_select_div').hide();
			}else{
				$this.parent().find('.course_select_div').hide();
				$this.parent().find('.selected_users_div').hide();
			}
		}
	});

	jQuery('body').on('videowatermark_privacy_details_appended',function (){
		$('.selectusers_videotube').each(function(){

	    	var $this = $(this);
		    $this.select2({
		        minimumInputLength: 4,
		        placeholder: $(this).attr('data-placeholder'),
		        closeOnSelect: true,
		        language: {
		          inputTooShort: function() {
		            return wplms_videotube_watermark_data.translations.more_chars;
		          }
		        },
		        dropdownParent: $this.closest('.selected_users_div'),
		        ajax: {
		            url: wplms_videotube_watermark_data.ajax_url,
		            type: "POST",
		            dataType: 'json',
		            delay: 250,
		            data: function(term){ 
		                    return  {   action: 'select_users_videotube', 
		                                security: wplms_videotube_watermark_data.security,
		                                q: term,
		                            }
		            },
		            processResults: function (data) {
		            	
		                return {
		                    results: data
		                };
		            },       
		            cache:true  
		        },
		        templateResult: function(data){
		            return '<img width="32" src="'+data.image+'">'+data.text;
		        },
		        templateSelection: function(data){
		            return '<img width="32" src="'+data.image+'">'+data.text;
		        },
		        escapeMarkup: function (m) {
		            return m;
		        }
		    });
	  	});
		$('.selectcoursecpt_videotube').each(function(){
	       
	        var $this = jQuery(this);
	        var cpt = $this.attr('data-cpt');
	        var placeholder = $this.attr('data-placeholder');
	        $this.select2({
	            minimumInputLength: 4,
	            placeholder: placeholder,
	            closeOnSelect: true,
	            allowClear: true,
	            dropdownParent: $this.closest('.course_select_div'),
	            ajax: {
	                url: ajaxurl,
	                type: "POST",
	                dataType: 'json',
	                delay: 250,
	                data: function(term){ 
	                        return  {   action: 'get_admin_select_cpt_videotube', 
	                                    security: wplms_videotube_watermark_data.security,
	                                    cpt: cpt,
	                                    q: term,
	                                }
	                },
	                processResults: function (data) {
	                    return {
	                        results: data
	                    };
	                },       
	                cache:false  
	            },
	        });
	    });

	    


	});
	
});