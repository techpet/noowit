<?php

/*
Plugin Name: NooWit
Plugin URI: http://techpet.github.com/noowit
Description: Plugin for posting to personal NooWit account
Version: 0.5
Author: Koutsaftikis Ioannis
Author URI: http://www.techpet.gr
*/

/**
 * Copyright (c) 2011-2012 Embridea, Ltd. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */
 

 
 class NooWit {
	public function __construct()
	{
		add_action( 'admin_menu', array($this, 'noowit_plugin_menu') );
		
		$options = get_option('noowit_options');
		
		if ($options['authentication_token'] != ""){
		
			add_action('wp_ajax_noowit_confirm', array($this, 'noowit_confirm_callback') );
		
		
		
			if ($options['autopublish'] == "1"){
				add_action( 'admin_footer', array($this, 'noowit_default_publish') );
			}
			else{
				add_action( 'post_submitbox_misc_actions', array($this, 'noowit_button') );
			}
		
		}

	}
	
	
	
	function noowit_plugin_menu() {
		add_options_page( 'Noowit Options', 'Noowit', 'manage_options', 'my-unique-identifier', array($this, 'noowit_plugin_options') );
	}
	
	
	
	function noowit_plugin_options() {
		
		
		
		$options = get_option('noowit_options');
        
        	if (isset($_POST['form_submit'])){
			    
			$options['authentication_token'] = $_POST['authentication_token'];
			
			$options['autopublish']    = isset($_POST['autopublish'])     ? $_POST['autopublish']    : '';
			    
			echo '<div class="updated fade"><p>' . __('Settings Saved', 'cpt') . '</p></div>';

			update_option('noowit_options', $options);
			//update_option('CPT_configured', 'TRUE');
			   
		}
	
	
	
	
	
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		
		//register_setting('reading','eg_setting_name');
		
		?>
		
		<div class="wrap">
		<?php echo screen_icon(); ?>
		<h2><?php _e('NooWit Settings', 'cpt') ?></h2>
		
		
		<h2><img title="NooWit Logo" src="<?php echo  plugins_url( 'img/splash_logo.png' , __FILE__ );?>"/></h2>
		
		<form id="form_data" name="form" method="post"> 
		
		
		
		
		
		
		
		 <table class="form-table">
                    <tbody>
                    
                    
                    	<tr valign="top">
                            <th scope="row" style="text-align: right;"><label><?php _e('Your NooWit authentication token:', 'cpt') ?></label></th>
                            <td>
                                <label for="authentication_token">
                                <input type="text" value="<?php echo $options['authentication_token']; ?>" name="authentication_token"/>                                                
                            </td>
                        </tr>
                    
                    
                                                    
                        <tr valign="top">
                            <th scope="row" style="text-align: right;"><label><?php _e('Auto Publish', 'cpt') ?></label></th>
                            <td>
                                <label for="autopublish">
                                <input type="checkbox" <?php if ($options['autopublish'] == "1") {echo 'checked="checked"';} ?> value="1" name="autopublish">
                                <?php _e('If checked, the plug-in will automatically publish post to my NooWit account when I hit the "Publish" button', 'cpt') ?>.</label>
                            </td>
                        </tr>
                        
                        
                    </tbody>
                </table>






		<p class="submit">
                    <input type="submit" name="Submit" class="button-primary" value="<?php 
                    _e('Save Settings', 'cpt') ?>">
               </p>
            
                <input type="hidden" name="form_submit" value="true" />

		
		
		
		</form>
		
		
		
		
		
		
		
		
		
		<?php
	}
	
	
	
	
	
	
	
	function noowit_default_publish(){
	
		$this->noowit_confirm("publish");
			
	}
	
	
	
	function noowit_button(){
		
		global $post;
		
		$published = get_post_meta($post->ID,"noowit_published", true);
		
		?>
	
		
		<div id="major-publishing-actions" style="overflow:hidden">
		<div id="publishing-action">
		<input type="button" accesskey="p" tabindex="5" value="Publish to NooWit" class="button-primary" id="noowit_button" name="noowit" <?php if ($published == 1){echo "disabled";} ?> />
		</div>
		</div>
		
		<?php
		wp_enqueue_script('jquery');
		
		
		$this->noowit_confirm("noowit_button");
		
	}
	
	
	
	
	function noowit_confirm($button_name){
	
		
		wp_enqueue_script('jquery');
				
		
		?>
		
		
		<script type="text/javascript">
		jQuery("#<?php echo $button_name; ?>").click(function(){
		
		
			 
			
			var values = {};
			jQuery.each(jQuery('#post').serializeArray(), function(i, field) {
			
			    if (field.name == 'post_category[]'){
			    	values[field.name] = values[field.name]+','+field.value;
			    }else{
			    	values[field.name] = field.value;
			    }
			});
			
			//console.log(values);
			
			var content = values['content'];
			
			if (tinymce.activeEditor){
				content = tinymce.activeEditor.getContent();
			}
			
			
			var data = {
				action: 'noowit_confirm',
				id: values['post_ID'],
				title: values['post_title'],
				content: content,
				categories: values['post_category[]']
				
			}
			

			/*console.log(values['post_title']);
			console.log(values['post_ID']);
			console.log(values['post_category[]']);*/
			
			
			jQuery.post(ajaxurl, data, function(response) {
				//console.log(response);
				if (response == "0"){
					alert('Oops! Something went wrong...');
					
				}else{					
					alert('Post successuly published to your NooWit Magazine!');
					jQuery('#noowit_button').attr('disabled','disabled');
				}
				
			//	alert('Got this from the server: ' + response);
			});
			//var response = confirm("You are publishing the following to your NooWit account:<b/r> Title: "+values['post_title']+"<br/> Is it OK?");
			
			
		});
		
		
		</script>
		
		
		
		
		<?php
	
	
	}
	
	
	
	
	
	
	
	
	function noowit_confirm_callback(){
		$post_id =  intval($_POST['id']);
		$category_ids =  $_POST['categories'];
		$post_title = $_POST['title'];
		$content = $_POST['content'];
		
		
		$category_ids_array = explode(',' , $category_ids);
		
		$cat_id = $category_ids_array[2];
		
		$cat = get_category($cat_id);
		$cat_name = $cat->name;
		
		$options = get_option('noowit_options');
		
		$token = $options['authentication_token'];
		
		
		//Create oauth request for posting to NooWit
		require(plugin_dir_path( __FILE__ ). 'RestRequest.inc.php');


		$args = array();
		$args["title"] = addslashes($post_title);
		$args["content"] = addslashes($content);
		$args["category"] = addslashes($cat_name);

		$request = new RestRequest('http://www.noowit.com/token_auth/article?token='.$token, 'POST',$args); 
		$request->execute(); 
		
		$success = "0";
		$response_info = $request->getResponseInfo();
		if ($response_info['http_code'] == 200){
			
			$success = "1";
			//echo "success: ".$success;
			add_post_meta($post_id,"noowit_published",1);
			
		}

		echo $success;
		
		die();
	
	
	}
	
	
	
	
	
	
	
	
	
	
}


$noowit = new NooWit();

 
