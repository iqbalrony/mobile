<?php

/*********************************************
//	Automotive Post Meta Class
//***********************************************************

  Used to alter the post meta options when using different site variations

*/
class Automotive_Post_Meta {
  private static $instance    = null;
  public $fields              = array();
  public $post                = 0;
  public $has_secondary_title = true;

  public function __construct(){
    $this->fields = array(
      "header_image" => array(
        "type"  => "header_image",
        "title" => esc_html__("Header Image", "automotive")
      ),
      "no_header_area" => array(
        "type"   => "no_header_area",
        "title"  => esc_html__("No Header Area", "automotive"),
        "single" => true
      ),
      "no_header_shadow" => array(
        "type"   => "no_header_shadow",
        "title"  => esc_html__("No Header Shadow", "automotive"),
        "single" => true
      ),
      "sidebar" => array(
        "type"  => "sidebar",
        "title" => esc_html__("Sidebar", "automotive")
      ),
      "footer_area" => array(
        "type"  => "footer_area",
        "title" => esc_html__("Footer Area", "automotive")
      ),
      "call_to_action" => array(
        "type"  => "call_to_action",
        "title" => false
      ),
      "slideshow" => array(
        "type"  => "slideshow",
        "title" => esc_html__("Slideshow", "automotive")
      )
    );

    add_action('save_post', array($this, 'save_post_meta'), 10, 3);
  }

  public function load_post_data(){
    global $post;

    $this->post = $post;
  }

  public function get_id(){
    return $this->post->ID;
  }

  public function get_fields(){
    return apply_filters('automotive_theme_post_meta', $this->fields);
  }

  public function remove_field($field_s){
    if(!is_array($field_s)){
      $field_s = array($field_s);
    }

    if(!empty($field_s)){
      foreach($field_s as $field_s_single){
        if(isset($this->fields[$field_s_single])){
          unset($this->fields[$field_s_single]);
        }
      }
    }
  }

  public function has_field($field){
    return isset($this->fields[$field]);
  }

  public function has_secondary_title(){
    return $this->has_secondary_title;
  }

  public function enable_secondary_title(){
    $this->has_secondary_title = true;
  }

  public function disable_secondary_title(){
    $this->has_secondary_title = false;
  }

  public function display_post_meta_fields(){
    $this->load_post_data();

    $fields = $this->get_fields();

    if(!empty($fields)){
      $last_field = end($fields);

      foreach($fields as $field_id => $field){
        $field_type  = $field['type'];
        $field_title = (isset($field['title']) ? $field['title'] : '');

        echo '<div>';

        // echo (isset($field['single']) && $field['single'] ? '<label>' : '');
        echo ($field['title'] && (!isset($field['single']) || !$field['single']) ? $this->title($field_title) : '');

        if($field_type === 'sidebar'){
          $this->show_sidebar_meta();
        } elseif($field_type === 'header_image'){
          $this->show_header_image_meta();
        } elseif($field_type === 'no_header_area'){
          echo '<p><b><label>' . esc_html($field_title) . ' ' . $this->show_no_header_area_meta() . '</label></b></p>';
        } elseif($field_type === 'no_header_shadow'){
          // $this->show_no_header_shadow_meta();
          echo '<p><b><label>' . esc_html($field_title) . ' ' . $this->show_no_header_shadow_meta() . '</label></b></p>';
        } elseif($field_type === 'footer_area'){
          $this->show_footer_area_meta();
        } elseif($field_type === 'call_to_action'){
          $this->show_call_to_action_meta();
        } elseif($field_type === 'slideshow'){
          $this->show_slideshow_meta();
        } else {
          // display custom form
          echo apply_filters('automotive_theme_post_meta_' . $field_id, '');
        }

        // echo (isset($field['single']) && $field['single'] ? '</label>' : '');

        // don't show the hr for last element
        if($last_field !== $field){
          echo '<hr>';
        }

        echo '</div>';
      }
    }
  }

  public function title($title){
    echo '<p><b>' . esc_html($title) . '</b></p>';
  }

  public function show_slideshow_meta(){
    if(is_plugin_active( 'revslider/revslider.php' )){ ?>
  		<select name="page_slideshow" style="width:100%;">
  			<?php
  			global $wpdb;

  			$default_slideshow = get_post_meta($this->get_id(), "page_slideshow", true);

  			// Get Revolution Sliders
  			$rev_sliders         = array();
  			$rev_sliders['none'] = esc_html__("No Slideshow", "automotive");

  			$rev_sliders_query = $wpdb->get_results("SELECT title, alias FROM " . $wpdb->prefix . "revslider_sliders");

  			if(!empty($rev_sliders_query)){
  				foreach($rev_sliders_query as $slider){
  					$rev_sliders[$slider->alias] = stripslashes($slider->title);
  				}
  			}

  			foreach($rev_sliders as $alias => $slider){
  				echo "<option value='" . esc_attr($alias) . "' " . selected($default_slideshow, $alias, false) . ">" . esc_html($slider) . "</option>\n";
  			} ?>
  		</select>
  	<?php
  	}
  }

  public function show_call_to_action_meta(){
  	$action_toggle      = get_post_meta($this->get_id(), "action_toggle", true);
  	$action_text        = get_post_meta($this->get_id(), "action_text", true);
  	$action_button_text = sanitize_text_field( get_post_meta($this->get_id(), "action_button_text", true) );
  	$action_link        = get_post_meta($this->get_id(), "action_link", true);
  	$action_class       = sanitize_text_field( get_post_meta($this->get_id(), "action_class", true) );

    ?>
    <p><b><?php esc_html_e("Call To Action", "automotive"); ?></b> <input type='checkbox' class='call_to_action' name='call' value='action' <?php echo ($action_toggle == "on" ? " checked='checked'" : ""); ?> /></p>

    <div class='call_to_action_form'<?php echo ($action_toggle == "on" ? " style='display: block;'" : " style='display: none;'"); ?>>
    	<table border='0'>
        	<tr><td><?php esc_html_e("Text", "automotive"); ?>: </td><td><input type='text' name='action_text' value="<?php echo htmlspecialchars($action_text); ?>" /></td></tr>
            <tr><td><?php esc_html_e("Button Text", "automotive"); ?>: </td><td><input type='text' name='action_button_text' value='<?php echo esc_attr($action_button_text); ?>' /></td></tr>
            <tr><td><?php esc_html_e("Button Link", "automotive"); ?>: </td><td><input type='text' name='action_link' value='<?php echo esc_attr( esc_url( $action_link) ); ?>' /></td></tr>
            <tr><td><?php esc_html_e("Button Class", "automotive"); ?>: </td><td><input type='text' name='action_class' value='<?php echo esc_attr($action_class); ?>' /></td></tr>
        </table>
    </div>
    <?php
  }

  public function show_footer_area_meta(){
    $default_footer = get_post_meta( $this->get_id(), "footer_area", true );
    $footer_areas   = automotive_theme_get_option('footer_widget_spots', array()); ?>
    <select name="footer_area" style="width:100%;">
      <?php
        echo "<option value='default-footer'" . selected($default_footer, "default-footer", false) . ">" . esc_html__("Default Footer", "automotive") . "</option>";
        echo "<option value='no-footer'" . selected($default_footer, "no-footer", false) . ">" . esc_html__("No Footer", "automotive") . "</option>";

        if(!empty($footer_areas)){
          foreach($footer_areas as $area){
            echo "<option value='" . esc_attr($area) . "' " . selected($default_footer, $area, false) . ">" . esc_html($area) . "</option>\n";
          }
        } ?>
    </select>
    <?php
  }

  public function show_no_header_shadow_meta(){
    $no_header_shadow = get_post_meta($this->get_id(), "no_header_shadow", true);
    $selected         = (!empty($no_header_shadow) && $no_header_shadow == "no_header_shadow");

    return '<input type="checkbox" value="no_header_shadow" name="no_header_shadow"' . ($selected ? " checked='checked'" : "") . '>';
  }

  public function show_no_header_area_meta(){
    $no_header              = get_post_meta($this->get_id(), "no_header", true);
    $no_header_area_default = automotive_theme_get_option('no_header_area_default', false);
    $selected               = (!empty($no_header) && $no_header == "no_header") || ($no_header_area_default == 1 && auto_is_edit_page('new'));

    return '<input type="checkbox" value="no_header" name="no_header"' . ($selected ? " checked='checked'" : "") . '>';
  }

  public function show_header_image_meta(){
    $header_image = get_post_meta($this->get_id(), "header_image", true);

    ?><button class="choose_image button button-primary" data-uploader-title="<?php esc_html_e("Select a header image", "automotive"); ?>" data-uploader-button-text="<?php _e("Select Image", "automotive"); ?>"><?php _e("Choose Header Image", "automotive"); ?></button>
    <input type="hidden" class="header_image_input" name="header_image" value="<?php echo trim(esc_attr($header_image)); ?>" <?php echo (!empty($header_image) ? "data-id='" . auto_image_id($header_image) . "'" : ""); ?>>

    <div class="header_preview_area">
    	<?php
      if(isset($header_image) && !empty($header_image)){
        $full   = wp_get_attachment_image_src($header_image, "full");
        $medium = wp_get_attachment_image_src($header_image, "medium");

  			echo "<a href='" . esc_url($full[0]) . "' target='_blank'><img src='" . esc_url($medium[0]) . "' style='width: 100%; margin-top: 8px;'></a>";
  			echo "<i class='fa fa-times remove_header_image'></i>";
  		} ?>
    </div><?php
  }

  public function show_sidebar_meta(){
    $sidebar         = get_post_meta($this->get_id(), "sidebar", true);
    $custom_sidebars = automotive_theme_get_option('custom_sidebars', array());
    $default_sidebar = get_post_meta( $this->get_id(), "sidebar_area", true );

    // default sidebar for new pages
    if(empty($sidebar)){
      $sidebar = automotive_theme_get_option('default_sidebar', '');
    }

    ?><select name="sidebar_area" style="width:100%;">
      <option><?php esc_html_e("No Sidebar", "automotive"); ?></option>
      <?php
      if(get_post_type($this->get_id()) == "listings"){
        $default_sidebar = (!$default_sidebar ? "single_listing_sidebar" : $default_sidebar);

        echo "<option value='single_listing_sidebar'" . selected($default_sidebar, "single_listing_sidebar", false) . ">" . esc_html__("Single Listing Sidebar", "automotive") . "</option>";
      }

      echo "<option value='blog-sidebar'" . selected($default_sidebar, "blog-sidebar", false) . ">" . esc_html__("Blog Sidebar", "automotive") . "</option>";

      if(!empty($custom_sidebars)){
        foreach($custom_sidebars as $area){
          echo "<option value='" . esc_attr($area) . "' " . selected($default_sidebar, str_replace(" ", "-", strtolower($area)), false) . ">" . esc_html($area) . "</option>\n";
        }
      } ?>
    </select>

    <?php echo $this->title(esc_html__("Sidebar Position", "automotive")); ?>

    <select name="sidebar">
    	<option value='none'><?php esc_html_e("None", "automotive"); ?></option>
        <option value='left' <?php selected($sidebar, "left"); ?>><?php esc_html_e("Left", "automotive"); ?></option>
        <option value='right' <?php selected($sidebar, "right"); ?>><?php esc_html_e("Right", "automotive"); ?></option>
    </select><?php
  }

  public function save_post_meta($post_id, $post, $update){
    $post_types = get_post_types();

  	if(in_array(get_post_type(), $post_types)){

      // save the secondary title
      if($this->has_secondary_title()){
        $secondary_title = (isset($_POST['secondary_title']) && !empty($_POST['secondary_title']) ? $_POST['secondary_title'] : "");

    		update_post_meta((int)$post_id, "secondary_title", (string)esc_html($secondary_title));
      }

      // save the sidebar data
      if($this->has_field('sidebar')){
        $sidebar      = (isset($_POST['sidebar']) && !empty($_POST['sidebar']) ? $_POST['sidebar']                                                       : "");
        $sidebar_area = (isset($_POST['sidebar_area']) && !empty($_POST['sidebar_area']) ? str_replace( " ", "-", strtolower( $_POST['sidebar_area'] ) ) : "");

    		if(!empty($sidebar)){
    			update_post_meta((int)$post_id, "sidebar", (string)$sidebar);
    		}

    		if(!empty($sidebar_area)){
    			update_post_meta((int)$post_id, "sidebar_area", (string)$sidebar_area);
    		}
      }

      // save header image meta
      if($this->has_field('header_image')){
        $header_image = (isset($_POST['header_image']) && !empty($_POST['header_image']) ? $_POST['header_image'] : "");

    		update_post_meta((int)$post_id, "header_image", (string)$header_image);
      }

      // save the footer area meta
      if($this->has_field('footer_area')){
        $footer_area = (isset($_POST['footer_area']) && !empty($_POST['footer_area']) ? $_POST['footer_area'] : "");

    		if(!empty($footer_area)){
    			update_post_meta((int)$post_id, "footer_area", (string)$footer_area);
    		}
      }

      // save the slider meta
      if($this->has_field('slideshow')){
        $page_slideshow = (isset($_POST['page_slideshow']) && !empty($_POST['page_slideshow']) ? $_POST['page_slideshow'] : "");

    		if(!empty($page_slideshow)){
    			update_post_meta((int)$post_id, "page_slideshow", (string)$page_slideshow);
    		}
      }

      // save the header area meta
      if($this->has_field('no_header_area')){
        $no_header = (isset($_POST['no_header']) && !empty($_POST['no_header']) ? $_POST['no_header'] : "");

    		update_post_meta((int)$post_id, "no_header", (string)$no_header);
      }

      if($this->has_field('no_header_shadow')){
        $no_header_shadow   = (isset($_POST['no_header_shadow']) &&  !empty($_POST['no_header_shadow']) ? $_POST['no_header_shadow'] : "");

    		update_post_meta((int)$post_id, "no_header_shadow", (string)$no_header_shadow);
      }

      // save the call to action meta
      if($this->has_field('call_to_action')){
        $action_text        = (isset($_POST['action_text']) && !empty($_POST['action_text']) ? $_POST['action_text'] : "");
    		$action_button_text = (isset($_POST['action_button_text']) && !empty($_POST['action_button_text']) ? $_POST['action_button_text'] : "");
    		$action_link        = (isset($_POST['action_link']) && !empty($_POST['action_link']) ? $_POST['action_link'] : "");
    		$action_class       = (isset($_POST['action_class']) && !empty($_POST['action_class']) ? $_POST['action_class'] : "");

    		if(isset($_POST['call']) && $_POST['call'] == "action"){
    			update_post_meta((int)$post_id, "action_toggle", "on");
    			update_post_meta((int)$post_id, "action_text", (string)$action_text);
    			update_post_meta((int)$post_id, "action_button_text", (string)$action_button_text);
    			update_post_meta((int)$post_id, "action_link", (string)$action_link);
    			update_post_meta((int)$post_id, "action_class", (string)$action_class);
    		} else {
    			update_post_meta((int)$post_id, "action_toggle", "off");
    		}
      }

      // save any custom post meta
      do_action('automotive_theme_save_post_meta', $post_id, $post, $update);
  	}
  }


  public static function get_instance(){
    if(self::$instance === null){
      self::$instance = new self();
    }

    return self::$instance;
  }
}


function Automotive_Post_Meta(){
  return Automotive_Post_Meta::get_instance();
}

Automotive_Post_Meta();
