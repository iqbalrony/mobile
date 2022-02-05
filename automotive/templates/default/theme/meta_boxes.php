<?php
//********************************************
//	Custom meta boxes
//***********************************************************
if(!function_exists("automotive_theme_add_custom_boxes")){
	function automotive_theme_add_custom_boxes(){
		$post_types = get_post_types();//array("post", "page");

		foreach($post_types as $post_type){
			add_meta_box( "secondary_title", __("Secondary Title", "automotive"), "automotive_theme_secondary_title", $post_type, "advanced", "high", null);
			add_meta_box( "page_options", __("Page Options", "automotive"), "automotive_theme_page_options", $post_type, "side", "core", null );
		}
	}
}
add_action( 'add_meta_boxes', 'automotive_theme_add_custom_boxes' );

function automotive_theme_page_options(){
	global $post;

	// $sidebar            = get_post_meta($post->ID, "sidebar", true);
	// $header_image       = get_post_meta($post->ID, "header_image", true);
	// $no_header          = get_post_meta($post->ID, "no_header", true);
	// $no_header_shadow   = get_post_meta($post->ID, "no_header_shadow", true);
	// $action_toggle      = get_post_meta($post->ID, "action_toggle", true);
	// $action_text        = get_post_meta($post->ID, "action_text", true);
	// $action_button_text = sanitize_text_field( get_post_meta($post->ID, "action_button_text", true) );
	// $action_link        = esc_url( get_post_meta($post->ID, "action_link", true) );
	// $action_class       = sanitize_text_field( get_post_meta($post->ID, "action_class", true) );

	// $no_header_area_default = automotive_theme_get_option('no_header_area_default', false);
	// $custom_sidebars        = automotive_theme_get_option('custom_sidebars', array());
	// $footer_areas           = automotive_theme_get_option('footer_widget_spots', array());

  // default sidebar for new pages
  // if(empty($sidebar)){
  //     $sidebar = automotive_theme_get_option('default_sidebar', '');
  // }

	// $default_sidebar = get_post_meta( $post->ID, "sidebar_area", true );
	// $default_footer  = get_post_meta( $post->ID, "footer_area", true );
	?>

    <?php if(is_plugin_active( 'revslider/revslider.php' )){ ?>
	    <hr>

	    <p><b><?php _e("Slideshow", "automotive"); ?></b></p>
		<select name="page_slideshow" style="width:100%;">
			<?php
			global $wpdb;

			$default_slideshow = get_post_meta($post->ID, "page_slideshow", true);

			// Get Revolution Sliders
			$rev_sliders = array();
			$rev_sliders['none'] = "No Slideshow";

			$rev_sliders_query = $wpdb->get_results("SELECT title, alias FROM " . $wpdb->prefix . "revslider_sliders");

			if(!empty($rev_sliders_query)){
				foreach($rev_sliders_query as $slider){
					$rev_sliders[$slider->alias] = stripslashes($slider->title);
				}
			}

			foreach($rev_sliders as $alias => $slider){
				echo "<option value='" . $alias . "' " . selected($default_slideshow, $alias, false) . ">" . $slider . "</option>\n";
			} ?>
		</select>
	<?php
	}
}


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
