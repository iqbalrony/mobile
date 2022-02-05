<?php

//********************************************
//	Custom meta boxes
//***********************************************************
if(!function_exists("automotive_theme_add_custom_boxes")){
	function automotive_theme_add_custom_boxes(){
		$post_types = get_post_types();//array("post", "page");

		foreach($post_types as $post_type){
			add_meta_box( "page_options", __("Page Options", "automotive"), "automotive_theme_page_options", $post_type, "side", "core", null );

      if(Automotive_Post_Meta()->has_secondary_title()){
        add_meta_box( "secondary_title", __("Secondary Title", "automotive"), "automotive_theme_secondary_title", $post_type, "advanced", "high", null);
      }
		}
	}
}
add_action( 'add_meta_boxes', 'automotive_theme_add_custom_boxes' );

function automotive_theme_page_options(){
  Automotive_Post_Meta()->display_post_meta_fields();
}
//automotive_theme_get_part('meta_boxes');




if(!function_exists("automotive_theme_title_add_after_editor")){
	function automotive_theme_title_add_after_editor(){
		global $post, $wp_meta_boxes;

		do_meta_boxes(get_current_screen(), 'advanced', $post);

		$post_types = get_post_types();

		foreach($post_types as $post_type){
			unset($wp_meta_boxes[$post_type]['advanced']);
		}
	}
}

add_action("edit_form_after_title", "automotive_theme_title_add_after_editor");

if(!function_exists("automotive_theme_secondary_title")){
	function automotive_theme_secondary_title(){
		global $post;

		$secondary_title = get_post_meta($post->ID, "secondary_title", true);

		echo "<input type='text' value='" . esc_attr($secondary_title) . "' name='secondary_title' style='width:100%;'/>";
	}
}
