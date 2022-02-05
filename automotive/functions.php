<?php

// translation my (friend||pengyou||vriend||ven||ami||freund||dost||amico||jingu||prijatel||amigo||arkadas)
load_theme_textdomain( 'automotive', get_template_directory() . '/languages' );

// disable redux notices.
$GLOBALS['redux_notice_check'] = true;

function automotive_template_change_redux($new_options, $changed_options){	// $changed_options only old contains values that were changed
	$auto_template_option_key = 'automotive_wp_template_';

	// site template has changed, lets store this template options in a backup to restore if
	// the user switches back so they don't lose existing options.
	if(isset($changed_options['site_template'])){
		$old_site_template = $changed_options['site_template'];
		$store_options 	   = $new_options;

		$store_options['site_template'] = $old_site_template;

		$site_options_saved = update_option($auto_template_option_key . $old_site_template, $store_options);

		// old site options saved, we can check for existing options now, if not then just
		// switch over the option name to finish the switch.
		$new_site_template = $new_options['site_template'];

		if($site_options_saved){
			$new_existing_options = get_option($auto_template_option_key . $new_site_template);

			if($new_existing_options){
				update_option('automotive_wp', $new_existing_options);
			}
		}

		update_option('automotive_theme_site_template', $new_site_template);

		// var_dump([$new_site_template]);
		// die;
	}
}
add_action("redux/options/automotive_wp/saved", "automotive_template_change_redux", 10, 2);

if(!function_exists('automotive_theme_get_option')){
	function automotive_theme_get_option($option, $default_value = null){
		global $awp_options;

		return (isset($awp_options[$option]) && (!empty($awp_options[$option]) || $awp_options[$option] === false || $awp_options[$option] == "0") ? $awp_options[$option] : $default_value);
	}
}

if(!function_exists('automotive_listing_get_option')){
	function automotive_listing_get_option($option, $default_value = null){
		global $lwp_options;

		return (isset($lwp_options[$option]) && (!empty($lwp_options[$option]) || $lwp_options[$option] === false || $lwp_options[$option] == "0") ? $lwp_options[$option] : $default_value);
	}
}

// bootstrap 4 notice if plugin is down level
function automotive_plugin_down_level__notice() {
    if(defined("AUTOMOTIVE_VERSION") && version_compare(AUTOMOTIVE_VERSION, "12.3", "<")) { ?>
        <div class="notice notice-warning">
            <p><?php _e( 'We recently upgraded our Automotive Theme & Plugin to use Bootstrap 4 but it looks like the Automotive Listings plugin is behind, please update this to at least version 12.3 for maximum compatibility.', 'automotive' ); ?></p>
        </div>
	    <?php
    }
}
add_action( 'admin_notices', 'automotive_plugin_down_level__notice' );

if ( ! function_exists( 'auto_theme_register_custom_extension_loader' ) ) :
	function auto_theme_register_custom_extension_loader( $ReduxFramework ) {
		$path = dirname( __FILE__ ) . '/ReduxFramework/extensions/';

		if ( is_dir( $path ) ) {
			$folders = scandir( $path, 1 );
			if ( ! empty( $folders ) ) {
				foreach ( $folders as $folder ) {
					if ( $folder === '.' or $folder === '..' or ! is_dir( $path . $folder ) ) {
						continue;
					}
					$extension_class = 'ReduxFramework_Extension_' . $folder;
					if ( ! class_exists( $extension_class ) ) {
						// In case you wanted override your override, hah.
						$class_file = $path . $folder . '/extension_' . $folder . '.php';
						$class_file = apply_filters( 'redux/extension/' . $ReduxFramework->args['opt_name'] . '/' . $folder, $class_file );
						if ( $class_file ) {
							require_once( $class_file );
						}
					}
					if ( ! isset( $ReduxFramework->extensions[ $folder ] ) ) {
						$ReduxFramework->extensions[ $folder ] = new $extension_class( $ReduxFramework );
					}
				}
			}
		}
	}

	add_action( "redux/extensions/automotive_wp/before", 'auto_theme_register_custom_extension_loader', 0 );
endif;

if ( ! class_exists( 'ReduxFramework' ) && file_exists( get_template_directory() . '/ReduxFramework/ReduxCore/framework.php' ) ) {
	require_once( get_template_directory() . '/ReduxFramework/ReduxCore/framework.php' );
}
if ( ! isset( $redux_demo ) && file_exists( get_template_directory() . '/ReduxFramework/options/options.php' ) ) {
	require_once( get_template_directory() . '/ReduxFramework/options/options.php' );
}

$awp_options = get_option( "automotive_wp" );

add_filter( 'widget_text', 'do_shortcode' );

if ( ! isset( $content_width ) ) {
	$content_width = 1170;
}


function automotive_after_theme_setup() {
	// Add Menu Support
	add_theme_support( 'menus' );

	// Add Thumbnail Theme Support
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'large_featured_image', 1170, 250, true );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'woocommerce' );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );

	remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'automotive_after_theme_setup', 11 );



/* Bundled Plugin Updater */
$custom_plugin_updater_disabled = automotive_theme_get_option('disable_bundled_plugin_updater', false);

if($custom_plugin_updater_disabled){
	add_filter('automotive_disable_wpb_updater', '__return_false');
}

function sample_admin_notice__success() {
	$page = (isset($_GET['page']) && !empty($_GET['page']) ? $_GET['page'] : false);

	if($page && ($page == 'vc-general' || $page == 'vc-roles' || $page == 'vc-automapper' ) ) {
		$custom_plugin_updater_disabled = automotive_theme_get_option('disable_bundled_plugin_updater', false);

		if(!$custom_plugin_updater_disabled){ ?>
	    <div class="notice notice-info is-dismissible">
	        <p><?php echo sprintf( __( 'If you purchased a license for WPBakery Page Builder separately from our theme license you can enable the in plugin updater by toggling the setting %sTheme Options >> Update Settings >> Disable Bundled Plugin Updates%s', 'automotive' ), '<strong>', '</strong>'); ?></p>
	    </div>
	    <?php
		}
	}
}
add_action( 'admin_notices', 'sample_admin_notice__success' );

// disable visual composer nagging
function automotive_disable_vc_notifications() {
	if(apply_filters('automotive_disable_wpb_updater', true)){
		vc_set_as_theme();
	}
}

add_action( 'vc_before_init', 'automotive_disable_vc_notifications' );

// social icons
global $social_options;

$social_icons = $social_options = array(
	"facebook"  => "Facebook",
	"twitter"   => "Twitter",
	"youtube"   => "Youtube",
	"vimeo"     => "Vimeo",
	"linkedin"  => "Linkedin",
	"rss"       => "RSS",
	"flickr"    => "Flickr",
	"skype"     => "Skype",
	"snapchat"  => "Snapchat",
	"google"    => "Google",
	"pinterest" => "Pinterest",
	"instagram" => "Instagram",
	"yelp"      => "Yelp"
);

//********************************************
//	Include Files
//***********************************************************
require_once( get_template_directory() . "/classes/class.css_composer.php");
require_once( get_template_directory() . "/classes/class.templates.php");
require_once( get_template_directory() . "/classes/class.post_meta.php");
require_once( get_template_directory() . "/meta_boxes.php" );
require_once( get_template_directory() . "/save.php" );
// require_once( get_template_directory() . "/ajax-functions.php" );
require_once( get_template_directory() . "/classes/envato_api.class.php" );
require_once( get_template_directory() . "/classes/class.bundled.php" );

require_once( get_template_directory() . "/classes/class-tgm-plugin-activation.php" );
require_once( get_template_directory() . "/included_plugins.php" );
require_once( get_template_directory() . "/update-notifier.php" );

require_once( get_template_directory() . "/envato_setup/envato_setup.php" );

function automotive_theme_editor_styles() {
	add_editor_style( get_template_directory() . '/css/style.css' );
  add_editor_style( get_template_directory() . '/css/all.min.css' );
	add_editor_style( get_template_directory() . '/css/v4-shims.min.css' );
}

add_action( 'init', 'automotive_theme_editor_styles' );

new TS_Bundled_Updates(array(
  'js_composer/js_composer.php',
  'revslider/revslider.php'
));

// update_option('revslider-valid', true);

//********************************************
//  Modify Main Query
//***********************************************************
function auto_change_filter( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ( $query->is_search || $query->is_author ) {
			$post_types = array( 'post', 'listings', 'page' );

			if ( function_exists( "is_woocommerce" ) ) {
				$post_types[] = "product";
			}

      if( isset($_GET['post_type']) && $_GET['post_type'] == 'product' ){
        $query->set('post_type', 'product');
      } else {
    		$query->set( 'post_type', $post_types );
      }

			$query->set( 'post_status', 'publish' );
		}
	}

  // hide sold vehicles
  if($query->is_search){
    global $lwp_options;

    $existing_meta_query = $query->get('meta_query');

    if(empty($existing_meta_query)){
      $existing_meta_query = array();
    }

    if(isset($lwp_options['inventory_no_sold']) && empty($lwp_options['inventory_no_sold']) && !is_admin()){
      $existing_meta_query[] = array(
        'relation' => 'OR',
        array(
          'key'   => 'car_sold',
          'value' => '2',
        ),
        array(
          'key'     => 'car_sold',
          'compare' => 'NOT EXISTS'
        )
      );
    }

    $query->set('meta_query', $existing_meta_query);
  }

  return $query;
}

add_action( 'pre_get_posts', 'auto_change_filter' );

//********************************************
//  Functions
//***********************************************************

if ( ! function_exists( "get_current_id" ) ) {
	function get_current_id() {
		if ( function_exists( "is_shop" ) && is_shop() ) {
			return get_option( "woocommerce_shop_page_id" );
		} else {
			return get_queried_object_id();
		}
	}
}

function suppress_filters_all( $query ) {
	$query->set( 'suppress_filters', false );

	return $query;
}

add_filter( 'pre_get_posts', 'suppress_filters_all' );

function automotive_color_options_change(){
  $automotive_temp_color_options = get_option('automotive_temp_color_options');

  if(!$automotive_temp_color_options){
    global $awp_options;

    $save_options = array('primary_color', 'css_link_color', 'css_footer_link_color');
    $temp_options = array();

    foreach($save_options as $option){
      if(isset($awp_options[$option]) && !empty($awp_options[$option])){
        $temp_options[$option] = $awp_options[$option];
      }
    }

    update_option('automotive_temp_color_options', $temp_options);
  }
}
add_action('init', 'automotive_color_options_change', 0);

if ( ! function_exists( "automotive_head_title" ) ) {
	function automotive_head_title() {
		if ( defined( "WPSEO_VERSION" ) ) {
			wp_title( '' );
		} else {
			// if ( is_home() ) {
			// 	echo bloginfo( "name" );
			// 	echo " | ";
			// 	echo bloginfo( "description" );
			// } else {
				echo wp_title( " | ", false, 'right' );
				echo bloginfo( "name" );
			// }
		}
	}
}

/*************************************************************************************
 *  Automatic Theme Update
 *************************************************************************************/
function automotive_themes_update_version( $updates ) {
  $theme_details = get_option('envato-market-ts');

  if(isset($theme_details['theme'])){
      $xml = automotive_get_latest_theme_version(86400);

      if(is_child_theme()){
    		global $new_theme_dir_name;

    		$theme_data = wp_get_theme( (isset($new_theme_dir_name) && !empty($new_theme_dir_name) ? $new_theme_dir_name : 'automotive') );
    	} else {
    		$theme_data = wp_get_theme();
    	}

      if(isset($xml->latest) && isset($theme_data['Version']) && version_compare($theme_data['Version'], $xml->latest) == -1) {
        $update = array(
          "url"         => "http://support.themesuite.com/version/changelog.php?t=automotive-wp",
          "new_version" => (string)$xml->latest,
          "package"     => "automotive_themesuite_package_url"
        );

        $updates->response['automotive'] = $update;
      }
  }

	return $updates;
}
add_filter( "pre_set_site_transient_update_themes", "automotive_themes_update_version" );

function automotive_themeforest_download_url( $options ){
  $theme_details  = get_option('envato-market-ts');
  $package        = $options['package'];

  if(isset($theme_details['theme']) && $package == "automotive_themesuite_package_url"){
    $Envato_Market = Automotive_Envato_Market_API::instance();
    $download_url = $Envato_Market->download($theme_details['theme']['id']);

    if($download_url){
      $options['package'] = $download_url;
    }
  }

  return $options;
}
add_filter( "upgrader_package_options", "automotive_themeforest_download_url" );


// admin
if ( ! function_exists( "admin_scripts" ) ) {
	function admin_scripts( $hook_suffix ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-effects-core' );
		wp_enqueue_script( 'jquery-ui-size' );
		wp_enqueue_script( 'iris' );


		if ( is_singular( 'listings' ) || $hook_suffix == "edit.php" || $hook_suffix == "post.php" || $hook_suffix == "post-new.php" ) {
			wp_register_script( 'admin_script', get_template_directory_uri() . "/js/admin.js" );
			wp_enqueue_script( 'admin_script' );

			wp_register_script( 'google-maps', "https://maps.googleapis.com/maps/api/js?key" );
			wp_enqueue_script( 'google-maps' );

			wp_register_script( 'jquery-admin', get_template_directory_uri() . '/js/jquery.admin.js' );
			wp_localize_script( 'jquery-admin', 'ajax_variables', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_enqueue_script( 'jquery-admin' );

		}
	}
}

function automotive_str_to_px( $input ) {
	return preg_replace( '/[^0-9]/', '', $input ) . "px";
}

//********************************************
//	Contact Form
//***********************************************************
if(!function_exists("automotive_send_contact_form")){
	function automotive_send_contact_form(){
		$has_recaptcha  = automotive_listing_get_option('recaptcha_enabled', false);
		$to_Email       = automotive_theme_get_option('contact_email', get_bloginfo('admin_email'));  //Replace with recipient email address
	  $subject        = __('Message from contact form', 'automotive');

	    //check $_POST vars are set, exit if any missing
	    if(!isset($_POST["userName"]) || !isset($_POST["userEmail"]) || !isset($_POST["userMessage"])) {
	        die();
	    }

	    //Sanitize input data using PHP filter_var().
	    $user_Name        = filter_var($_POST["userName"], FILTER_SANITIZE_STRING);
	    $user_Email       = filter_var($_POST["userEmail"], FILTER_SANITIZE_EMAIL);
	    $user_Message     = stripslashes($_POST["userMessage"]);

	    header('Content-type: application/json');
	    $return = array(
	        "message" => "",
	        "success" => "yes"
	    );

	    //additional php validation
	    if(strlen($user_Name) < 4) {
	        $return['message'] = __("Name is too short or empty.", "automotive");
	        $return['success'] = "no";
	    }

	    if(!filter_var($user_Email, FILTER_VALIDATE_EMAIL)) {
	        $return['message'] = __("Please enter a valid email.", "automotive");
	        $return['success'] = "no";
	    }

	    if(strlen($user_Message) < 5) {
	        $return['message'] = __("Too short message! Please enter something.", "automotive");
	        $return['success'] = "no";
	    }

			if(function_exists("automotive_recaptcha_check_request") && $has_recaptcha ){
				$recaptcha_check = automotive_recaptcha_check_request(isset($_POST['challenge']) ? $_POST['challenge'] : '');

				if(!isset($recaptcha_check->success) || (!$recaptcha_check->success) ){
					$return['message'] = __("reCAPTCHA is invalid, please try again.", "automotive");
					$return['success'] = "no";
				}
			}

	    //proceed with PHP email.
	    $headers = array();
	    $headers[] = 'From: ' . $user_Name . ' <' . $user_Email . '>';

	    if($return['success'] == "yes") {

	        $sentMail = @wp_mail($to_Email, $subject, __("Email", "automotive") . ": " . $user_Email . "\n " . __("Message", "automotive") . ": " . $user_Message . "\n\n" . __("Name", "automotive") . ": " . $user_Name, $headers);

	        if(!$sentMail)  {
	            $return['message'] = __("Could not send mail.", "automotive");
	            $return['success'] = "no";
	        } else {
	            $return['message'] = __('Hi ', 'automotive') . $user_Name . '. ' . __('Your email has been delivered.', 'automotive');
	        }
	    }

	    echo json_encode($return);

		die;
	}
}

add_action("wp_ajax_send_contact_form", "automotive_send_contact_form");
add_action("wp_ajax_nopriv_send_contact_form", "automotive_send_contact_form");


//********************************************
//  Ajax Login
//***********************************************************
function ajax_login(){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nonce    = $_POST['nonce'];
    $remember = (isset($_POST['remember_me']) && !empty($_POST['remember_me']) ? $_POST['remember_me'] : "");

    if ( wp_verify_nonce( $nonce, 'ajax_login_none' ) && !empty($username) && !empty($password) ) {
        $creds = array();

        $creds['user_login']    = sanitize_text_field($username);
        $creds['user_password'] = sanitize_text_field($password);
        $creds['remember_me']   = sanitize_text_field(($remember == "yes" ? true : false));

        $user = wp_signon( $creds, false );

        if ( ! is_wp_error($user) ) {
            echo "success";
        }
    }

    die;
}

add_action("wp_ajax_ajax_login", "ajax_login");
add_action("wp_ajax_nopriv_ajax_login", "ajax_login");

/**
 * Displays a CSS string of properties to be used with dynamic CSS
 * @param  array $data       All CSS data, key is CSS property and value itself
 * @param  array $show_props An array of which values to show
 * @return string            CSS string
 */
function automotive_css_properties($data, $show_props){
  $return = '';

  if(!empty($show_props) && !empty($data)){
    foreach($show_props as $show_prop){
      if(is_string($show_prop) && isset($data[$show_prop]) && !empty($data[$show_prop])){
        $prop_value = ($show_prop == 'font-size' || $show_prop == 'line-height' ? automotive_str_to_px($data[$show_prop]) : $data[$show_prop]);

        $return .= $show_prop . ': ' . $prop_value . '; ';
      }
    }
  }

  return $return;
}

function automotive_generate_custom_css(){
	$theme_custom_css = automotive_get_auto_custom_css();

	// $theme_custom_css = Automotive_CSS_Composer()->compose_css();
	$user_custom_css  = automotive_theme_get_option('custom_css', false);

	if($user_custom_css){
		$theme_custom_css .= "\n\n" . automotive_css_strip_whitespace($user_custom_css);
	}

	return $theme_custom_css;
}

// save styling if the user just updated

/**
 * Hooks into Redux theme options save and generates global styling saved in an option
 */
function automotive_after_theme_options_saved($new_options){
	global $awp_options;

	if($new_options){
		$awp_options = $new_options;
	}

	automotive_init_css_styling();// rerun to grab new color values

	// store the global css
	$theme_custom_css = apply_filters('automotive_theme_custom_css_styling', '');
	// $theme_custom_css = (empty($theme_custom_css) ? automotive_generate_custom_css() : $theme_custom_css);
	// $theme_custom_css = automotive_generate_custom_css();

  update_option('automotive_theme_global_css', $theme_custom_css);

  // echo (!$updated_css_option && isset($_REQUEST['debug']) ? '<strong>Global CSS Wasn\'t Saved</strong>' : '');
}
add_action('redux/options/automotive_wp/saved', 'automotive_after_theme_options_saved', 999, 1);

function automotive_init_css_styling(){
	global $awp_options;

	$theme_color_scheme = automotive_theme_get_option('theme_color_scheme', array());

	// if(!empty($theme_color_scheme)){
		if(automotive_get_current_template('id') === 'default'){
			$primary_color                       = (isset($theme_color_scheme['primary-color']['rgba']) ? $theme_color_scheme['primary-color']['rgba'] : 'rgba(199,8,27,0)');
			$primary_color_hex                   = (isset($theme_color_scheme['primary-color']['color']) ? $theme_color_scheme['primary-color']['color'] : '#c7081b');

			$primary_color_selectors             = array("a", "a:hover", "a:focus", ".auto-primary-color", ".firstcharacter", ".list-info span.text-red", ".car-block-wrap h4 a", ".welcome-wrap h4", ".small-block:hover h4", ".small-block:hover a i", ".recent-vehicles .scroller_title", ".flip .card .back i.button_icon:hover:before", ".about-us h3", ".blog-container h3", ".blog-post h3", ".services h3", ".list_faq ul li.active a", ".list_faq ul li a:hover", ".right_faq .side-widget h3", ".side-content .side-blog strong", ".side-content .list ul li span", ".main_pricing h3 b", "#features ul li .fa-li", ".left_inventory h2", ".left_inventory h2", ".featured-service h2", ".featured-service h2 strong", ".detail-service h2", ".detail-service h2 strong", ".find_team h2", ".find_team h2", ".find_team h2", ".our_inventory h4", ".our_inventory span", ".year_wrapper span", ".right_site_job .project_details ul li i", ".read-more a", ".comment-data .comment-author a", ".find_map h2", ".information_head h3", ".address ul li span.compayWeb_color", ".comparison-container .car-detail .option-tick-list ul li:before", ".detail-service .details h5:before", ".services .right-content ul li:before", ".alternate-font", ".left_inventory h3", ".no_footer .logo-footer a span", ".page-content .small-block:hover h4", ".pricing_table .main_pricing .inside span.amt", ".pricing_table .main_pricing .inside span.sub1", ".wp_page .page-content h2", ".detail-service .details h5 i", "body ul.shortcode.type-checkboxes li i", ".comments h3#comments-number", "body.woocommerce div.product p.price", ".flipping-card .back i.button_icon:hover::before");
			$primary_background_selectors        = array(".auto-primary-bg-color", ".pagination>li>a:hover", ".pagination>li>span:hover", ".pagination>li>a:focus", ".pagination>li>span:focus", ".woocommerce .cart .button", ".woocommerce nav.woocommerce-pagination ul li a:hover", ".woocommerce nav.woocommerce-pagination ul li a:focus", ".progressbar .progress .progress-bar-danger", ".bottom-header .navbar-default .navbar-nav>.active>a", ".bottom-header .navbar-default .navbar-nav>.active>a:hover", ".bottom-header .navbar-default .navbar-nav>.active>a:focus", ".bottom-header .navbar-default .navbar-nav> li> a:hover", "header .nav .open>a", "header .nav .open>a:hover", "header .nav .open>a:focus", "header .navbar-default .navbar-nav>.open>a", "header .navbar-default .navbar-nav>.open>a:hover", "header .navbar-default .navbar-nav>.open>a:focus", ".dropdown-menu>li>a:hover", ".dropdown-menu>li>a:focus", ".dropdown-menu>.active>a", ".dropdown-menu>.active>a:hover", ".dropdown-menu>.active>a:focus", ".navbar-default .navbar-nav .open .dropdown-menu>.active>a", ".navbar-default .navbar-nav .open .dropdown-menu>.active>a:hover", ".car-block:hover .car-block-bottom", ".controls .left-arrow:hover", ".controls .right-arrow:hover", ".back_to_top:hover", ".flipping-card .side.back", ".description-accordion .panel-title a:after", ".comparison-container .comparison-header", ".featured-service .featured:hover", ".featured-service .featured .caption", ".flexslider2 .flex-direction-nav li a:hover", ".default-btn", ".default-btn:hover", ".default-btn:focus", ".form-element input[type=submit]", ".side-content form input[type=submit]", ".side-content form input[type=submit]:hover", "input[type='reset']", "input[type='reset']:hover", "input[type='submit']", "input[type='button']", "input[type='submit']:hover", "input[type='button']:hover", ".btn-inventory", ".btn-inventory:hover", ".comparison-footer input[type='submit']", ".comparison-footer input[type='button']", ".comparison-footer input[type='submit']:active", ".comparison-footer input[type='button']:active", ".leave-comments form input[type=submit]", ".leave-comments form input[type=submit]:active", ".choose-list ul li:before", ".current_page_parent", "a.button-link", "button.navbar-toggler", "button.navbar-toggler:hover", "button.navbar-toggler:focus");
			$primary_background_color_selectors  = array("#wp-calendar td#today", "body ul.shortcode li .red_box", "button", ".pricing_table .pricing-header", ".page-content .automotive-featured-panel:hover", "button:hover", ".arrow1 a:hover", ".arrow2 a:hover", ".arrow3 a:hover", ".woocommerce a.button.alt:hover", ".woocommerce button.button.alt:hover", ".woocommerce input.button.alt:hover", ".woocommerce #respond input#submit.alt:hover", ".woocommerce #content input.button.alt:hover", ".woocommerce-page a.button.alt:hover", ".woocommerce-page button.button.alt:hover", ".woocommerce-page input.button.alt:hover", ".woocommerce-page #respond input#submit.alt:hover", ".woocommerce-page #content input.button.alt:hover", ".woocommerce a.button:hover", ".woocommerce button.button:hover", ".woocommerce input.button:hover", ".woocommerce #respond input#submit:hover", ".woocommerce #content input.button:hover", ".woocommerce-page a.button:hover", ".woocommerce-page button.button:hover", ".woocommerce-page input.button:hover", ".woocommerce-page #respond input#submit:hover", ".woocommerce-page #content input.button:hover", ".woocommerce button.button.alt.disabled", ".woocommerce-page button.button.alt.disabled", ".woocommerce button.button.alt.disabled:hover", ".woocommerce #respond input#submit", ".woocommerce a.button", ".woocommerce button.button", ".woocommerce input.button");
			$primary_border_left_color_selectors = array(".post-entry blockquote");
			$primary_border_left_right_selectors = array(".angled_badge.theme_color:before");
			$primary_border_color_selectors      = array(".woocommerce div.product .woocommerce-tabs ul.tabs li.active");

			Automotive_CSS_Composer()->add_selector(
				'primary_color',
				array(
					array(
						'selectors' => $primary_color_selectors,
						'props'     => 'color'
					),
					array(
						'selectors' => $primary_background_selectors,
						'props'     => array('background', 'background-color')
					),
					array(
						'selectors' => $primary_background_color_selectors,
						'props'			=> 'background-color'
					),
					array(
						'selectors' => $primary_border_left_color_selectors,
						'props'			=> 'border-left-color'
					),
					array(
						'selectors' => $primary_border_color_selectors,
						'props'     => 'border-color'
					)
				),
				$primary_color
			);
		}

		Automotive_CSS_Composer()->add_color_scheme('primary-color', __('Primary Color', 'automotive'),  (isset($automotive_temp_color_options['primary_color']) && !empty($automotive_temp_color_options['primary_color']) ? $automotive_temp_color_options['primary_color'] : '#c7081b'), 1, 'header, .dropdown .dropdown-menu li.dropdown .dropdown-menu, header .navbar-nav.pull-right>li>.dropdown-menu, header .navbar-nav>li>.dropdown-menu', 'background-color', 'Global');
		Automotive_CSS_Composer()->add_color_scheme('global-link', __('Link', 'automotive'), (isset($automotive_temp_color_options['css_link_color']) && isset($automotive_temp_color_options['css_link_color']['regular']) && !empty($automotive_temp_color_options['css_link_color']['regular']) ? $automotive_temp_color_options['css_link_color']['regular'] : '#c7081b'), 1, 'a', 'color', 'Global');
		Automotive_CSS_Composer()->add_color_scheme('global-link-hover', __('Link Hover', 'automotive'), (isset($automotive_temp_color_options['css_link_color']) && isset($automotive_temp_color_options['css_link_color']['hover']) && !empty($automotive_temp_color_options['css_link_color']['hover']) ? $automotive_temp_color_options['css_link_color']['hover'] : '#c7081b'), 1, 'a:hover', 'color', 'Global');
		Automotive_CSS_Composer()->add_color_scheme('global-link-active', __('Link Active', 'automotive'), (isset($automotive_temp_color_options['css_link_color']) && isset($automotive_temp_color_options['css_link_color']['active']) && !empty($automotive_temp_color_options['css_link_color']['active']) ? $automotive_temp_color_options['css_link_color']['active'] : '#c7081b'), 1, 'a:active', 'color', 'Global');
		Automotive_CSS_Composer()->add_color_scheme('site-header', __('Header Background', 'automotive'), '#000000', .65, 'header, .dropdown .dropdown-menu li.dropdown .dropdown-menu, header .navbar-nav.pull-right>li>.dropdown-menu, header .navbar-nav>li>.dropdown-menu', 'background-color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('dropdown-menu-background', __('Dropdown Background', 'automotive'), '#000000', .65, '.dropdown .dropdown-menu li.dropdown .dropdown-menu, header .navbar-nav.pull-right>li>.dropdown-menu, header .navbar-nav>li>.dropdown-menu', 'background-color', 'Header');

		Automotive_CSS_Composer()->add_color_scheme('toolbar-background', __('Toolbar Background', 'automotive'), '#000000', .2, '.toolbar', 'background-color', 'Toolbar');
		Automotive_CSS_Composer()->add_color_scheme('toolbar-color', __('Toolbar Text', 'automotive'), '#929596', 1, '.toolbar ul li a, .toolbar .search_box, header .toolbar button', 'color', 'Toolbar');
		Automotive_CSS_Composer()->add_color_scheme('toolbar-color-hover', __('Toolbar Hover Text', 'automotive'), '#FFF', 1, '.left-none li:hover a, .right-none li:hover a, .left-none li:hover input, .left-none li:hover i.fa, .right-none li:hover i.fa', 'color', 'Toolbar');

		Automotive_CSS_Composer()->add_color_scheme('header-menu-color', __('Header Menu Text', 'automotive'), '#FFFFFF', 1, '.bottom-header .navbar-default .navbar-nav>.active>a, header .bottom-header .navbar-default .navbar-nav>li>a, .navbar .navbar-nav li .dropdown-menu>li>a, .dropdown .dropdown-menu li.dropdown .dropdown-menu>li>a, body .navbar-default .navbar-nav .open .dropdown-menu>li>a', 'color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('header-menu-hover-color', __('Header Menu Hover Text', 'automotive'), '#FFFFFF', 1, 'header .bottom-header .navbar-default .navbar-nav>li:hover>a, .navbar .navbar-nav li .dropdown-menu>li:hover>a, .dropdown .dropdown-menu li.dropdown .dropdown-menu>li:hover>a, body .navbar-default .navbar-nav .open .dropdown-menu>li:hover>a', 'color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('header-menu-hover', __('Header Menu Hover Item', 'automotive'), '#c7081b', 1, '.bottom-header .navbar-default .navbar-nav> li:hover> a, .bottom-header .navbar-default .navbar-nav>.active>a:hover, .dropdown-menu>li>a:hover, .dropdown-menu>li.active>a:hover', 'background,background-color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('header-menu-active-hover-color', __('Header Menu Active Hover Text', 'automotive'), '#FFFFFF', 1, 'header .bottom-header .navbar-default .navbar-nav .active:hover>a, header .bottom-header .navbar-default .navbar-nav .dropdown-menu a.active:hover', 'color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('header-menu-active', __('Header Menu Active Item', 'automotive'), '#c7081b', 1, 'header .bottom-header .navbar-default .navbar-nav>.active>a, .dropdown-menu>.active>a', 'background,background-color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('header-menu-active-text-color', __('Header Menu Active Item Text', 'automotive'), '#FFF', 1, 'header .bottom-header .navbar-default .navbar-nav>.active>a, header .bottom-header .navbar-default .navbar-nav .dropdown-menu a.active', 'color', 'Header');
		Automotive_CSS_Composer()->add_color_scheme('mobile-site-header-bg', __('Mobile Header Background', 'automotive'), '#000000', .65, 'header, .dropdown .dropdown-menu li.dropdown .dropdown-menu, header .navbar-nav.pull-right>li>.dropdown-menu, header .navbar-nav>li>.dropdown-menu', 'background-color', 'Header');

		Automotive_CSS_Composer()->add_color_scheme('sticky-site-header', __('Header Background', 'automotive'), '#000000', .65, 'header.affix, header.affix .dropdown .dropdown-menu li.dropdown .dropdown-menu, header.affix .navbar-nav.pull-right>li>.dropdown-menu, header.affix .navbar-nav>li>.dropdown-menu', 'background-color', 'Sticky Header');
		Automotive_CSS_Composer()->add_color_scheme('sticky-header-menu-color', __('Header Menu Text', 'automotive'), '#FFFFFF', 1, 'header.affix .bottom-header .navbar-default .navbar-nav>.active>a, header.affix .bottom-header .navbar-default .navbar-nav>li>a, header.affix .navbar .navbar-nav li .dropdown-menu>li>a, header.affix .dropdown .dropdown-menu li.dropdown .dropdown-menu>li>a, body header.affix .navbar-default .navbar-nav .open .dropdown-menu>li>a', 'color', 'Sticky Header');
		Automotive_CSS_Composer()->add_color_scheme('sticky-header-menu-hover-color', __('Header Menu Hover Text', 'automotive'), '#FFFFFF', 1, 'header.affix .bottom-header .navbar-default .navbar-nav>li:hover>a, header.affix .navbar .navbar-nav li .dropdown-menu>li:hover>a, header.affix .dropdown .dropdown-menu li.dropdown .dropdown-menu>li:hover>a, body header.affix .navbar-default .navbar-nav .open .dropdown-menu>li:hover>a', 'color', 'Sticky Header');
		Automotive_CSS_Composer()->add_color_scheme('sticky-header-menu-active-text-color', __('Header Menu Active Item Text', 'automotive'), '#FFF', 1, 'header.affix .bottom-header .navbar-default .navbar-nav>.active>a', 'color', 'Sticky Header');
		Automotive_CSS_Composer()->add_color_scheme('mobile-site-sticky-header-bg', __('Mobile Header Background', 'automotive'), '#000000', .65, 'header.affix, header.affix .dropdown .dropdown-menu li.dropdown .dropdown-menu, header.affix .navbar-nav.pull-right > li > .dropdown-menu, header.affix .navbar-nav > li > .dropdown-menu', 'background-color', 'Sticky Header');

		Automotive_CSS_Composer()->add_color_scheme('hamburger-icon-button', __('Mobile "Hamburger" Menu Button', 'automotive'), 'transparent', 1, 'button.navbar-toggler.collapsed', 'background-color', 'Mobile Header');
		Automotive_CSS_Composer()->add_color_scheme('hamburger-icon-button-active', __('Mobile "Hamburger" Menu Active/Hover Button', 'automotive'), '#c7081b', 1, 'button.navbar-toggler.collapsed:hover, button.navbar-toggler, button.navbar-toggler:hover, button.navbar-toggler:focus', 'background-color', 'Mobile Header');
		Automotive_CSS_Composer()->add_color_scheme('hamburger-svg-icon', __('Mobile "Hamburger" Menu Icon Bars', 'automotive'), '#FFFFFF', .5, 'button.navbar-toggler.collapsed .navbar-toggler-icon', 'background-image', 'Mobile Header');
		Automotive_CSS_Composer()->add_color_scheme('hamburger-svg-icon-active', __('Mobile "Hamburger" Menu Active Icon Bars', 'automotive'), '#FFFFFF', 1, 'button.navbar-toggler .navbar-toggler-icon, button.navbar-toggler:hover .navbar-toggler-icon', 'background-image', 'Mobile Header');

		Automotive_CSS_Composer()->add_color_scheme('secondary-background', __('Secondary Background', 'automotive'), '#000000', 1, '#secondary-banner', 'background-color', 'Secondary Header');
		Automotive_CSS_Composer()->add_color_scheme('secondary-text', __('Secondary Text', 'automotive'), '#FFFFFF', 1, '#secondary-banner, #secondary-banner .main-heading, #secondary-banner .secondary-heading-heading', 'color', 'Secondary Header');
		Automotive_CSS_Composer()->add_color_scheme('secondary-text-shadow', __('Secondary Text Shadow', 'automotive'), '#000', 1, '#secondary-banner', 'text-shadow', 'Secondary Header');
		Automotive_CSS_Composer()->add_color_scheme('breadcrumb', __('Breadcrumb Text', 'automotive'), '#FFFFFF', 1, '.breadcrumb li, .breadcrumb li a, .breadcrumb>li+li:before', 'color', 'Secondary Header');

		Automotive_CSS_Composer()->add_color_scheme('body-background', __('Body Background', 'automotive'), '#FFFFFF', 1, 'section.content, .car-block-wrap, .welcome-wrap', 'background-color', 'Body');
		Automotive_CSS_Composer()->add_color_scheme('body-background-input', __('Body Background Input', 'automotive'), '#FFFFFF', 1, 'body input, body select, body textarea, body input[type=text], body textarea[name=message], body input[type=email], input.form-control, input[type=search], .side-content .financing_calculator table tr td input.number', 'background-color', 'Body');
		Automotive_CSS_Composer()->add_color_scheme('body-color-input', __('Body Input Text', 'automotive'), '#2D2D2D', 1, 'body input, body select, body textarea, input.form-control, select.form-control, textarea.form-control, input[type=search], .side-content .financing_calculator table tr td input.number', 'color', 'Body');
		Automotive_CSS_Composer()->add_color_scheme('body-button-bg', __('Button Background', 'automotive'), $primary_color_hex, 1, '.default-btn, button, input[type="reset"], input[type="button"], input[type="submit"], a.button-link, .form-element input[type="submit"],  .side-content form input[type="submit"]', 'background-color', 'Body');
		Automotive_CSS_Composer()->add_color_scheme('body-button-bg-hover', __('Button Hover Background', 'automotive'), $primary_color_hex, 1, '.default-btn:hover, button:hover, input[type="reset"]:hover, input[type="button"]:hover, input[type="submit"]:hover, a.button-link:hover, .form-element input[type="submit"]:hover,  .side-content form input[type="submit"]:hover', 'background-color', 'Body');
		Automotive_CSS_Composer()->add_color_scheme('body-button-font-color', __('Button Font Color', 'automotive'), '#FFFFFF', 1, '.default-btn, button, input[type="reset"], input[type="button"], input[type="submit"], a.button-link, .form-element input[type="submit"], .default-btn:hover, button:hover, input[type="reset"]:hover, input[type="button"]:hover, input[type="submit"]:hover, a.button-link:hover, .form-element input[type="submit"]:hover,  .side-content form input[type="submit"]', 'color', 'Body');

		Automotive_CSS_Composer()->add_color_scheme('inventory-background-input', __('Inventory Dropdown Background', 'automotive'), '#F7F7F7', 1, '.sbHolder, .sbOptions, .sbOptions li:hover', 'background-color', 'Inventory');
		Automotive_CSS_Composer()->add_color_scheme('inventory-color-input', __('Inventory Dropdown Text', 'automotive'), '#333', 1, '.sbHolder, .sbOptions, a.sbSelector:link, a.sbSelector:visited, a.sbSelector:hover, .sbOptions a:link, .sbOptions a:visited', 'color', 'Inventory');

		Automotive_CSS_Composer()->add_color_scheme('footer-background', __('Footer Background', 'automotive'), '#3D3D3D', 1, 'footer', 'background-color', 'Footer');
		Automotive_CSS_Composer()->add_color_scheme('footer-text', __('Footer Text', 'automotive'), '#FFFFFF', 1, 'footer, footer p, footer .textwidget, footer p, footer li, footer table', 'color', 'Footer');
		Automotive_CSS_Composer()->add_color_scheme('footer-link', __('Link', 'automotive'), (isset($automotive_temp_color_options['css_footer_link_color']) && isset($automotive_temp_color_options['css_footer_link_color']['regular']) && !empty($automotive_temp_color_options['css_footer_link_color']['regular']) ? $automotive_temp_color_options['css_footer_link_color']['regular'] : '#BEBEBE'), 1, 'footer a', 'color', 'Footer');

		Automotive_CSS_Composer()->add_color_scheme('footer-link-hover', __('Link Hover', 'automotive'), (isset($automotive_temp_color_options['css_footer_link_color']) && isset($automotive_temp_color_options['css_footer_link_color']['hover']) && !empty($automotive_temp_color_options['css_footer_link_color']['hover']) ? $automotive_temp_color_options['css_footer_link_color']['hover'] : '#999'), 1, 'footer a:hover', 'color', 'Footer');
		Automotive_CSS_Composer()->add_color_scheme('footer-link-active', __('Link Active', 'automotive'), (isset($automotive_temp_color_options['css_footer_link_color']) && isset($automotive_temp_color_options['css_footer_link_color']['active']) && !empty($automotive_temp_color_options['css_footer_link_color']['active']) ? $automotive_temp_color_options['css_footer_link_color']['active'] : '#999'), 1, 'footer a:active', 'color', 'Footer');

		Automotive_CSS_Composer()->add_color_scheme('bottom-footer-background', __('Bottom Footer Background', 'automotive'), '#2F2F2F', 1, '.copyright-wrap', 'background-color', 'Bottom Footer');

		Automotive_CSS_Composer()->add_color_scheme('bottom-footer-text', __('Bottom Footer Text', 'automotive'), '#FFFFFF', 1, '.copyright-wrap, .copyright-wrap p', 'color', 'Bottom Footer');
		Automotive_CSS_Composer()->add_color_scheme('bottom-footer-link', __('Bottom Footer Link', 'automotive'), '#999999', 1, '.copyright-wrap a', 'color', 'Bottom Footer');
		Automotive_CSS_Composer()->add_color_scheme('bottom-footer-link-hover', __('Bottom Footer Link Hover', 'automotive'), '#636363', 1, '.copyright-wrap a:hover', 'color', 'Bottom Footer');
		Automotive_CSS_Composer()->add_color_scheme('bottom-footer-link-active', __('Bottom Footer Link Active', 'automotive'), '#636363', 1, '.copyright-wrap a:active', 'color', 'Bottom Footer');

		// var_dump(['test', 'add_color_scheme', $primary_color]);
		// D($awp_options['theme_color_scheme']);
	// }
}
add_action('init', 'automotive_init_css_styling', 5);

if ( ! function_exists( "automotive_get_auto_custom_css" ) ) {
	function automotive_get_auto_custom_css() {
		global $awp_options;

		$custom_css = "";
/*
		if ( isset( $awp_options['theme_color_scheme']['primary-color']['rgba'] ) && !empty($awp_options['theme_color_scheme']['primary-color']['rgba']) ) {
      $primary_color = $awp_options['theme_color_scheme']['primary-color']['rgba'];

      $primary_color_selectors                = array("a", "a:hover", "a:focus", ".firstcharacter", ".list-info span.text-red", ".car-block-wrap h4 a", ".welcome-wrap h4", ".small-block:hover h4", ".small-block:hover a i", ".recent-vehicles .scroller_title", ".flip .card .back i.button_icon:hover:before", ".about-us h3", ".blog-container h3", ".blog-post h3", ".side-content h3", ".services h3", ".list_faq ul li.active a", ".list_faq ul li a:hover", ".right_faq .side-widget h3", ".side-content .side-blog strong", ".side-content .list ul li span", ".main_pricing h3 b", "#features ul li .fa-li", ".left_inventory h2", ".side-content .list h3", ".side-content .financing_calculator h3", ".left_inventory h2", ".side-content .list h3", ".side-content .financing_calculator h3", ".featured-service h2", ".featured-service h2 strong", ".detail-service h2", ".detail-service h2 strong", ".find_team h2", ".find_team h2", ".find_team h2", ".our_inventory h4", ".our_inventory span", ".year_wrapper span", ".right_site_job .project_details ul li i", ".read-more a", ".comment-data .comment-author a", ".find_map h2", ".information_head h3", ".address ul li span.compayWeb_color", ".comparison-container .car-detail .option-tick-list ul li:before", ".detail-service .details h5:before", ".services .right-content ul li:before", ".alternate-font", ".left_inventory h3", ".no_footer .logo-footer a span", ".page-content h3", ".page-content h4", ".page-content .small-block:hover h4", ".pricing_table .main_pricing .inside span.amt", ".pricing_table .main_pricing .inside span.sub1", ".wp_page .page-content h2", ".detail-service .details h5 i", "body ul.shortcode.type-checkboxes li i", ".comments h3#comments-number", "body.woocommerce div.product p.price");
      $primary_background_selectors           = array(".pagination>li>a:hover", ".pagination>li>span:hover", ".pagination>li>a:focus", ".pagination>li>span:focus", ".woocommerce nav.woocommerce-pagination ul li a:hover", ".woocommerce nav.woocommerce-pagination ul li a:focus", ".progressbar .progress .progress-bar-danger", ".bottom-header .navbar-default .navbar-nav>.active>a", ".bottom-header .navbar-default .navbar-nav>.active>a:hover", ".bottom-header .navbar-default .navbar-nav>.active>a:focus", ".bottom-header .navbar-default .navbar-nav> li> a:hover", "header .nav .open>a", "header .nav .open>a:hover", "header .nav .open>a:focus", "header .navbar-default .navbar-nav>.open>a", "header .navbar-default .navbar-nav>.open>a:hover", "header .navbar-default .navbar-nav>.open>a:focus", ".dropdown-menu>li>a:hover", ".dropdown-menu>li>a:focus", ".dropdown-menu>.active>a", ".dropdown-menu>.active>a:hover", ".dropdown-menu>.active>a:focus", ".navbar-default .navbar-nav .open .dropdown-menu>.active>a", ".navbar-default .navbar-nav .open .dropdown-menu>.active>a:hover", ".car-block:hover .car-block-bottom", ".controls .left-arrow:hover", ".controls .right-arrow:hover", ".back_to_top:hover", ".flipping-card .side.back", ".description-accordion .panel-title a:after", ".comparison-container .comparison-header", ".featured-service .featured:hover", ".featured-service .featured .caption", ".flexslider2 .flex-direction-nav li a:hover", ".default-btn", ".default-btn:hover", ".default-btn:focus", ".form-element input[type=submit]", ".side-content form input[type=submit]", ".side-content form input[type=submit]:hover", "input[type='reset']", "input[type='reset']:hover", "input[type='submit']", "input[type='button']", "input[type='submit']:hover", "input[type='button']:hover", ".btn-inventory", ".btn-inventory:hover", ".comparison-footer input[type='submit']", ".comparison-footer input[type='button']", ".comparison-footer input[type='submit']:active", ".comparison-footer input[type='button']:active", ".leave-comments form input[type=submit]", ".leave-comments form input[type=submit]:active", ".choose-list ul li:before", ".current_page_parent", "a.button-link", "button.navbar-toggler", "button.navbar-toggler:hover", "button.navbar-toggler:focus");
      $primary_background_color_selectors     = array("#wp-calendar td#today", "body ul.shortcode li .red_box", "button", ".pricing_table .pricing-header", ".page-content .automotive-featured-panel:hover", "button:hover", ".arrow1 a:hover", ".arrow2 a:hover", ".arrow3 a:hover", ".woocommerce a.button.alt:hover", ".woocommerce button.button.alt:hover", ".woocommerce input.button.alt:hover", ".woocommerce #respond input#submit.alt:hover", ".woocommerce #content input.button.alt:hover", ".woocommerce-page a.button.alt:hover", ".woocommerce-page button.button.alt:hover", ".woocommerce-page input.button.alt:hover", ".woocommerce-page #respond input#submit.alt:hover", ".woocommerce-page #content input.button.alt:hover", ".woocommerce a.button:hover", ".woocommerce button.button:hover", ".woocommerce input.button:hover", ".woocommerce #respond input#submit:hover", ".woocommerce #content input.button:hover", ".woocommerce-page a.button:hover", ".woocommerce-page button.button:hover", ".woocommerce-page input.button:hover", ".woocommerce-page #respond input#submit:hover", ".woocommerce-page #content input.button:hover", ".woocommerce button.button.alt.disabled", ".woocommerce-page button.button.alt.disabled", ".woocommerce button.button.alt.disabled:hover", ".woocommerce #respond input#submit", ".woocommerce a.button", ".woocommerce button.button", ".woocommerce input.button");
      $primary_border_left_color_selectors    = array(".post-entry blockquote");
      $primary_border_left_right_selectors    = array(".angled_badge.theme_color:before");
      $primary_border_color_selectors         = array(".woocommerce div.product .woocommerce-tabs ul.tabs li.active");


      $custom_css .= implode($primary_color_selectors, ",") . " { color: " . $primary_color . "; }";
      $custom_css .= implode($primary_background_selectors, ",") . " { background: " . $primary_color . "; background-color: " . $primary_color . "; }";
      $custom_css .= implode($primary_background_color_selectors, ",") . " { background-color: " . $primary_color . "; }";
      $custom_css .= implode($primary_border_left_color_selectors, ",") . " { border-left-color: " . $primary_color . "; }";
      // $custom_css .= implode($primary_border_left_right_selectors, ",") . " { border-left-color: " . $primary_color . "; border-right-color: " . $primary_color . "; }";
      // $custom_css .= implode($primary_border_left_right_selectors, ",") . " { border-color: " . $primary_color . "  rgba(0, 0, 0, 0); }";
      // $custom_css .= implode($primary_border_left_right_selectors, ",") . " { border-color: " . $primary_color . "; }";
		}
*/

		// heading font customizations
		$headings = array(
			"h1" => "",
			"h2" => ".wp_page .page-content h2",
			"h3" => ".side-content .financing_calculator h3, .side-content .list h3",
			"h4" => "",
			"h5" => ".detail-service .details h5",
			"h6" => ""
		);

		foreach ( $headings as $heading => $other_selectors ) {
			$heading_font = ( isset( $awp_options[ $heading . '_font' ] ) && ! empty( $awp_options[ $heading . '_font' ] ) ? $awp_options[ $heading . '_font' ] : "" );

			if ( ! empty( $heading_font ) ) {
        $custom_css .= $heading . ", .page-content " . $heading . ( ! empty( $other_selectors ) ? ", " . $other_selectors : "" ) . ' { ' . automotive_css_properties($heading_font, array('font-family', 'font-size', 'color', 'line-height', 'font-weight')) . ' } ';
			}
		}

		$custom_css .= Automotive_CSS_Composer()->compose_css();

		// D($awp_options);die;

		// echo ($custom_css);die;

		// custom font
		if ( isset( $awp_options['body_font'] ) && ! empty( $awp_options['body_font']['font-family'] ) ) {
			$body_font = $awp_options['body_font'];

      $body_font_selectors = array("body", "p", "table", "ul", "li", ".theme_font", ".textwidget", ".recent-vehicles p", ".post-entry table", ".icon_address p", ".list_faq ul li a", ".list-info p", ".blog-list span", ".blog-content strong", ".post-entry", ".pricing_table .category_pricing ul li", ".inventory-heading em", "body ul.shortcode.type-checkboxes li", ".about-us p", ".blog-container p", ".blog-post p", ".address ul li strong", ".address ul li span");

      $body_font_color  = array(".small-block h4", ".page-content .small-block h4", ".small-block a", ".page-template-404 .error", ".content h2.error", ".content h2.error i.exclamation", ".blog-list h4", ".page-content .blog-list h4", ".panel-heading .panel-title>a", ".wp_page .page-content h2", ".featured-service .featured h5", ".detail-service .details h5", ".name_post h4", ".page-content .name_post h4", ".portfolioContainer .box>div>span", ".blog-content .page-content ul li", ".comments > ul >li", ".blog-content .page-content ul li a", ".portfolioContainer .mix .box a", ".project_wrapper h4.related_project_head", ".post-entry span.tags a", ".post-entry span.tags", ".side-content .list ul li", ".wp_page .page-content h2 a", ".blog-content .post-entry h5", ".blog-content h2", ".address ul li i", ".address ul li strong", ".address ul li span", ".icon_address p i", ".listing-view ul.ribbon-item li a", ".select-wrapper span.sort-by", ".inventory-heading h2", ".inventory-heading span", ".inventory-heading .text-right h2", ".woocommerce div.product .product_title", ".woocommerce #content div.product .product_title", ".woocommerce-page div.product .product_title", ".woocommerce-page #content div.product .product_title", ".woocommerce ul.products li.product .price", ".woocommerce-page ul.products li.product .price", ".woocommerce-page div.product p.price", ".woocommerce div.product p.price", ".woocommerce div.product .product_title", ".woocommerce #content div.product .product_title", ".woocommerce-page div.product .product_title", ".woocommerce-page #content div.product .product_title", ".parallax_parent .parallax_scroll h4");
      $body_font_family = array(".recent-vehicles .scroller_title");

      $custom_css .= implode(",", $body_font_selectors) . " {" . automotive_css_properties($body_font, array('font-family', 'font-size', 'color', 'line-height', 'font-weight')) . "}";
      $custom_css .= implode(",", $body_font_color) . " { color: " . $body_font['color'] ."; }";
			$custom_css .= implode(",", $body_font_family) . " { font-family: " . $body_font['font-family'] . "; }";

      if(isset($awp_options['alternate_font']) && !empty($awp_options['alternate_font'])){
  			$alternate_font = $awp_options['alternate_font'];

        $custom_css .= ".alternate-font {" . automotive_css_properties($alternate_font, array('font-family', 'font-size', 'color', 'line-height', 'font-weight')) . "}";
      }
		}

		// logo fonts
		if ( isset( $awp_options['logo_top_font'] ) || isset( $awp_options['logo_bottom_font'] ) ) {
			$logo_top_font    = $awp_options['logo_top_font'];
			$logo_bottom_font = $awp_options['logo_bottom_font'];

      $top_font_selectors    = array("header .bottom-header .navbar-default .navbar-brand .logo .primary_text", ".no_footer .logo-footer a h2", ".logo-footer a h2");
      $bottom_font_selectors = array("header .bottom-header .navbar-default .navbar-brand .logo .secondary_text", ".no_footer .logo-footer a span", ".logo-footer a span");

			// D($logo_top_font);

      $custom_css .= implode(",", $top_font_selectors) . " { " . automotive_css_properties($logo_top_font, array('font-family', 'font-size', 'color', 'line-height')) . "}";
			$custom_css .= implode(",", $bottom_font_selectors) . " { " . automotive_css_properties($logo_bottom_font, array('font-family', 'font-size', 'color', 'line-height')) . "}";

			// var_dump(implode($top_font_selectors, ",") . " { " . automotive_css_properties($logo_top_font, array('font-family', 'font-size', 'color', 'line-height')) . "}");
			// var_dump(implode($bottom_font_selectors, ",") . " { " . automotive_css_properties($logo_bottom_font, array('font-family', 'font-size', 'color', 'line-height')) . "}");
			// echo "<br><br>";

		}

		// scroll logo fonts
		if ( isset( $awp_options['logo_top_font_scroll'] ) || isset( $awp_options['logo_bottom_font_scroll'] ) ) {
			$logo_scroll_top_font    = $awp_options['logo_top_font_scroll'];
			$logo_scroll_bottom_font = $awp_options['logo_bottom_font_scroll'];

      $logo_scroll_top_selectors    = array("header.affix .bottom-header .navbar-default .navbar-brand .logo .primary_text");
      $logo_scroll_bottom_selectors = array("header.affix .bottom-header .navbar-default .navbar-brand .logo .secondary_text");

      $custom_css .= implode(", ", $logo_scroll_top_selectors) . " { margin-bottom:0;" . automotive_css_properties($logo_scroll_top_font, array('font-size', 'line-height')) . "}";
      $custom_css .= implode(", ", $logo_scroll_bottom_selectors) . " { " . automotive_css_properties($logo_scroll_bottom_selectors, array('font-size', 'line-height')) . "}";
		}

		// boxed and boxed margin background
		if (
      isset( $awp_options['boxed_background'] ) && ! empty( $awp_options['boxed_background'] ) &&
      isset( $awp_options['body_layout'] ) && $awp_options['body_layout'] > 1
    ) {
			$background_options = array(
				"background-color",
				"background-image",
				"background-repeat",
				"background-position",
				"background-size",
				"background-attachment"
			);

      // alter the value for background image
      if(isset($awp_options['boxed_background']['background-image']) && !empty($awp_options['boxed_background']['background-image'])){
        $awp_options['boxed_background']['background-image'] = 'url(' . $awp_options['boxed_background']['background-image'] . ')';
      }

      $custom_css .= 'body { ' . automotive_css_properties($awp_options['boxed_background'], $background_options) . ' }';
		}

		// main menu font
		if ( isset( $awp_options['main_menu_font'] ) && ! empty( $awp_options['main_menu_font'] ) ) {
			$main_menu_font = $awp_options['main_menu_font'];

      $custom_css .= '.menu-main-menu-container ul li { ' . automotive_css_properties($main_menu_font, array('font-size', 'font-weight')) .' }';
      $custom_css .= implode(",", array('.menu-main-menu-container ul li', 'body header .bottom-header .navbar-default .navbar-nav>li>a')) . ' { ' . automotive_css_properties($main_menu_font, array('font-family', 'font-size', 'font-weight')) .' }';
		}

		// main dropdown menu font
		if ( isset( $awp_options['main_dropdown_font'] ) && ! empty( $awp_options['main_dropdown_font'] ) ) {
			$main_dropdown_font = $awp_options['main_dropdown_font'];

      if(isset($main_dropdown_font['font-size'])){
        $main_dropdown_font['line-height'] = $main_dropdown_font['font-size'];
      }

      $custom_css .= implode(',', array('.navbar .navbar-nav li .dropdown-menu>li>a', '.dropdown .dropdown-menu li.dropdown .dropdown-menu>li>a')) . '{ ' . automotive_css_properties($main_dropdown_font, array('font-family', 'font-weight', 'font-size', 'line-height')) . ' }';
		}

		// slideshow push
		if ( isset( $awp_options['push_mobile_slideshow_down'] ) && $awp_options['push_mobile_slideshow_down'] == 1 ) {
			$amount = ( isset( $awp_options['mobile_slideshow_down_amount'] ) && ! empty( $awp_options['mobile_slideshow_down_amount'] ) ? preg_replace( '/\D/', '', $awp_options['mobile_slideshow_down_amount'] ) : 98 );

			$custom_css .= "@media only screen and (max-width: 767px){
                  body .header_rev_slider_container {
                    margin-top:" . $amount . "px !important;
                } }";
		}

		// remove image borders
		if ( isset( $awp_options['images_border'] ) && $awp_options['images_border'] == 0 ) {
			$custom_css .= "body .page-content img, body .entry-content img {
			    border: 0;
			}";
		}

		// logo styling
		if ( isset( $awp_options['logo_customization'] ) && ! empty( $awp_options['logo_customization'] ) ) {
			$custom_logo_css = "";

			if ( isset( $awp_options['logo_dimensions'] ) && ! empty( $awp_options['logo_dimensions'] ) ) {
				$height = preg_replace( '/\D/', '', $awp_options['logo_dimensions']['height'] );
				$width  = preg_replace( '/\D/', '', $awp_options['logo_dimensions']['width'] );

				$units  = ( isset( $awp_options['logo_dimensions']['units'] ) && ! empty( $awp_options['logo_dimensions']['units'] ) ? $awp_options['logo_dimensions']['units'] : "px" );

				$custom_logo_css .= "
		    height: " . $height . $units . ";
		    width: " . $width . $units . ";";
			}

			if ( isset( $awp_options['logo_margin'] ) && ! empty( $awp_options['logo_margin'] ) ) {
				$margin_top    = preg_replace( '/\D/', '', $awp_options['logo_margin']['margin-top'] );
				$margin_right  = preg_replace( '/\D/', '', $awp_options['logo_margin']['margin-right'] );
				$margin_bottom = preg_replace( '/\D/', '', $awp_options['logo_margin']['margin-bottom'] );
				$margin_left   = preg_replace( '/\D/', '', $awp_options['logo_margin']['margin-left'] );
				$units         = ( isset( $awp_options['logo_margin']['units'] ) && ! empty( $awp_options['logo_margin']['units'] ) ? $awp_options['logo_margin']['units'] : "px" );

				$custom_logo_css .= "
		    margin-top: " . $margin_top . $units . ";
		    margin-right: " . $margin_right . $units . ";
		    margin-bottom: " . $margin_bottom . $units . ";
		    margin-left: " . $margin_left . $units . ";";
			}

      $custom_css .= "header .navbar-brand img.main_logo { " . $custom_logo_css . " }";
		}

		// mobile toolbar items for mobile
		$mobile_element_css = "";
		$mobile_elements    = array(
			"login"    => "",
			"language" => "",
			"cart"     => "",
			"search"   => "",
			"phone"    => "",
			"address"  => ""
		);


		foreach ( $mobile_elements as $mobile_element => $mobile_selector ) {
			$show_element = ( isset( $awp_options[ "toolbar_" . $mobile_element . "_show_mobile" ] ) && ! empty( $awp_options[ "toolbar_" . $mobile_element . "_show_mobile" ] ) ? true : false );

			if ( ! $show_element ) {
				$mobile_element_css .= "header .toolbar .row ul li.toolbar_" . $mobile_element . " { display: none; } ";
			}
		}

		// custom menu breakpoint
		$responsiveness = automotive_theme_get_option('responsiveness', true);

    if(isset($awp_options['main_menu_breakpoint']) && !empty($awp_options['main_menu_breakpoint']) && $responsiveness){
		    $main_menu_breakpoint = abs($awp_options['main_menu_breakpoint']);

		    $custom_css .= '@media(min-width: ' . ($main_menu_breakpoint + 1) . 'px){ .navbar-toggler { display: none; } } ';
		    $custom_css .= '@media(max-width: ' . $main_menu_breakpoint . 'px){ .navbar-header {
        float: none;
      }
      .navbar-toggle, .navbar-toggler .navbar-toggler-icon {
          display: block;
      }
      .navbar-collapse {
          border-top: 1px solid transparent;
          box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
      }
      .navbar-collapse.collapse {
          display: none!important;
      }
      .navbar-collapse.collapse.show {
          display: block!important;
      }
      .navbar-nav {
          float: none!important;
          margin: 7.5px -15px;
      }
      .navbar-nav>li {
          float: none;
      }
      .navbar-nav>li>a {
          padding-top: 10px;
          padding-bottom: 10px;
      }

      .navbar-navigation .mobile_dropdown_menu {
          display: block;
      }

      .navbar-navigation .fullsize_menu {
          display: none;
      }

      .navbar-navigation .mobile-menu-main-menu-container {
          width: 100%;
      }

      header .navbar-header {
          flex: 0 0 100%;
      }

      header .navbar-navigation {
          flex: 0 0 100%;
      }

      header .bottom-header .navbar-default {
          flex-wrap: wrap;
      }

      body header .bottom-header .navbar-default .navbar-nav.mobile_dropdown_menu>li>a {
          font-size: 14px;
          padding: 4px 11px;
      }

      header.affix .container .navbar .navbar-nav.mobile_dropdown_menu li a {
          font-size: 14px;
          line-height: 31px;
          padding: 4px 11px;
      } }';
    }

		if ( ! empty( $mobile_element_css ) ) {
			$custom_css .= "@media (max-width: " . ( isset( $awp_options['toolbar_mobile_breakpoint'] ) && ! empty( $awp_options['toolbar_mobile_breakpoint'] ) ? (int) $awp_options['toolbar_mobile_breakpoint'] : 768 ) . "px){ " . $mobile_element_css . " } ";
		}

		if ( isset( $awp_options['button_border_radius'] ) && ! empty( $awp_options['button_border_radius'] ) ) {
			$custom_css .= "button, button.btn, a.button-link, a.button, .button, button.button, #respond .form-submit input { border-radius: 0 !important; }";
		}

		// Theme Scheme CSS
    // echo '<pre>';
    // print_r($awp_options['theme_color_scheme']);
    // die;

		if ( isset( $awp_options['theme_color_scheme'] ) && ! empty( $awp_options['theme_color_scheme'] ) ) {
			$scheme     = ( isset( $awp_options['theme_color_scheme']['color_scheme_name'] ) && ! empty( $awp_options['theme_color_scheme']['color_scheme_name'] ) ? $awp_options['theme_color_scheme']['color_scheme_name'] : "Theme Styling" );

			if ( ! empty( $awp_options['theme_color_scheme'] ) ) {
        if(isset($awp_options['theme_color_scheme']['primary-color'])){
          unset($awp_options['theme_color_scheme']['primary-color']);
        }

				foreach ( $awp_options['theme_color_scheme'] as $key => $item ) {
          if($key == "mobile-site-header-bg" || $key == "mobile-site-sticky-header-bg"){

            $custom_css .= "@media (max-width: 768px){ " . $item['selector'] . " { background-color:" . $item['rgba'] . "} }";

          } elseif ($key == "hamburger-svg-icon" || $key == "hamburger-svg-icon-active"){

						$custom_css .= $item['selector'] . '{ ' . $item['mode'] . ':url("data:image/svg+xml;charset=utf8,%3Csvg viewBox=\'0 0 32 32\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath stroke=\'' . $item['rgba'] . '\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' d=\'M4 8h24M4 16h24M4 24h24\'/%3E%3C/svg%3E"); }';

					} elseif ( is_array( $item ) && ! empty( $item['selector'] ) && ! empty( $item['mode'] ) ) {
						$custom_css .= $item['selector'] . " { ";

						if ( strstr( $item['mode'], "," ) ) {
							$modes = explode( ",", $item['mode'] );

							if ( ! empty( $modes ) ) {
								foreach ( $modes as $mode ) {
									$custom_css .= $mode . ": " . $item['rgba'] . ";
                                    ";
								}
							}
						} else {
							$item['rgba'] = ( empty( $item['rgba'] ) ? "transparent" : $item['rgba'] );

							//text-shadow
							$item['rgba'] = ( $item['mode'] == "text-shadow" ? "0 1px 0 " . $item['rgba'] : $item['rgba'] );

							$custom_css .= $item['mode'] . ": " . $item['rgba'] . ";";
						}

						$custom_css .= "}";

					}
				}
			}

			if ( isset( $awp_options['theme_color_scheme']['header-menu-hover']['color'] ) && ! empty( $awp_options['theme_color_scheme']['header-menu-hover']['color'] ) ) {
				$custom_css .= 'body header .navbar-default .navbar-nav .open .dropdown-menu>li>a:focus { background-color: ' . $awp_options['theme_color_scheme']['header-menu-hover']['color'] . '; }';
			}
		}

		if ( isset( $awp_options['dropdown_menu_shadow'] ) && $awp_options['dropdown_menu_shadow'] == false ) {
			$custom_css .= "header .navbar-nav>li>.dropdown-menu { box-shadow: none; }";
		}

		if ( isset( $awp_options['header_image_stretch'] ) && ! empty( $awp_options['header_image_stretch'] ) ) {
			$custom_css .= "section#secondary-banner { background-size: cover; } ";
		}

		$user_custom_css  = $awp_options['custom_css'];//automotive_theme_get_option('custom_css', false);

		if($user_custom_css){
			$custom_css .= "\n\n" . automotive_css_strip_whitespace($user_custom_css);
		}

		return automotive_css_strip_whitespace( $custom_css );
	}
}
add_filter('automotive_theme_custom_css_styling', 'automotive_get_auto_custom_css');

function automotive_css_strip_whitespace( $css ) {
	$replace = array(
		"#/\*.*?\*/#s" => "",  // Strip C style comments.
		"#\s\s+#"      => " ", // Strip excess whitespace.
	);
	$search  = array_keys( $replace );
	$css     = preg_replace( $search, $replace, $css );

	$replace = array(
		": "  => ":",
		"; "  => ";",
		" {"  => "{",
		" }"  => "}",
		", "  => ",",
		"{ "  => "{",
		";}"  => "}", // Strip optional semicolons.
		",\n" => ",", // Don't wrap multiple selectors.
		"\n}" => "}", // Don't wrap closing braces.
		"} "  => "}\n", // Put each rule on it's own line.
	);
	$search  = array_keys( $replace );
	$css     = str_replace( $search, $replace, $css );

	return trim( $css );
}

if ( ! function_exists( "D" ) ) {
	function D( $vars ) {
		echo "<pre>";
		print_r( $vars );
		echo "</pre>";
	}
}

if ( ! function_exists( "auto_image_id" ) ) {
	function auto_image_id( $image_url ) {
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );

		return ( isset( $attachment[0] ) && ! empty( $attachment[0] ) ? $attachment[0] : "" );
	}
}

if ( ! function_exists( "automotive_google_analytics_code" ) ) {
	function automotive_google_analytics_code( $location ) {
		global $awp_options;
		$saved_location = ( isset( $awp_options['tracking_code_position'] ) && ! empty( $awp_options['tracking_code_position'] ) ? $awp_options['tracking_code_position'] : "" );

		if ( ! empty( $awp_options['google_analytics'] ) ) {
			if ( $location == "head" && $saved_location == 1 ) {
				echo "<script type='text/javascript'>";
				echo $awp_options['google_analytics'];
				echo "</script>";

			} elseif ( $location == "body" && empty( $saved_location ) ) {
				echo "<script type='text/javascript'>";
				echo $awp_options['google_analytics'];
				echo "</script>";
			}

		}
	}
}
add_action( 'admin_enqueue_scripts', 'admin_scripts' );

// Load conditional scripts
if ( ! function_exists( "automotive_conditional_scripts" ) ) {
	function automotive_conditional_scripts() {
		if ( is_singular() && comments_open() && ( get_option( 'thread_comments' ) == 1 ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}

if ( ! function_exists( "automotive_languages_dropdown_menu" ) ) {
	function automotive_languages_dropdown_menu() {
		global $awp_options;

		if ( function_exists( "icl_get_home_url" ) && isset( $awp_options['languages_dropdown'] ) && $awp_options['languages_dropdown'] == 1 ) {
			$languages = apply_filters( 'wpml_active_languages', null, '' );

			if ( ! empty( $languages ) ) {
				echo "<ul class='languages'>";
				foreach ( $languages as $l ) {
					echo "<li>";
					if ( ! $l['active'] ) {
						echo '<a href="' . $l['url'] . '">';
					}
					echo '<img src="' . $l['country_flag_url'] . '" height="12" alt="' . $l['language_code'] . '" width="18" />' . apply_filters( 'wpml_display_language_names', '', $l['native_name'], $l['translated_name'] );
					if ( ! $l['active'] ) {
						echo '</a>';
					}
					echo '</li>';
				}
				echo "</ul>";
			}
		}
	}
}

if ( ! function_exists( "woocommerce_shopping_cart" ) ) {
	function woocommerce_shopping_cart() {
		echo "<ul class='cart_dropdown'>";

		if ( function_exists( "is_woocommerce" ) ) {
			global $woocommerce;

			echo "<li>" . sprintf( _n( '%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes' ), $woocommerce->cart->cart_contents_count ) . ", " . __( "Total of", "automotive" ) . " " . $woocommerce->cart->get_cart_total() . " <span class='padding-horizontal-5'>|</span> <a href='" . wc_get_cart_url () . "'>" . __( "Checkout", "automotive" ) . "</a></li>";
		} else {
			echo "<li>" . __( "Please enable WooCommerce", "automotive" ) . "</li>";
		}

    echo "</ul>";
	}
}

if ( ! function_exists( "auto_woocommerce_menu_basket" ) ) {
	function auto_woocommerce_menu_basket() {
		$return = "";

		$custom_basket = apply_filters('automotive_custom_woocommerce_menu_basket', '');

		if(!empty($custom_basket)){
			return $custom_basket;
		}

		if ( function_exists( "is_woocommerce" ) ) {
			$cart_contents = WC()->cart->get_cart();

			ob_start();
			?>

            <div class="woocommerce-menu-basket">
				<?php

				if ( ! empty( $cart_contents ) ) {
					echo "<ul>";
					foreach ( $cart_contents as $cart_key => $cart_item ) {
						$item_id       = $cart_item['data']->get_id();
						$item_name     = $cart_item['data']->get_title();
						$item_quantity = $cart_item['quantity'];
						$item_price    = $cart_item['data']->get_price_html();
						$item_image    = wp_get_attachment_image_src( get_post_thumbnail_id( $item_id ), 'thumbnail' );

						$remove_url = wc_get_cart_remove_url( $cart_key );
						?>

                        <li>
                            <a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>">
                                <img src="<?php echo esc_url( $item_image[0] ); ?>">
                            </a>

                            <div class="product-item-details">
                                <a href="<?php echo esc_url( $remove_url ); ?>">
                                    <div class="item-remove"><i class="fa fa-times"></i></div>
                                </a>

                                <a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>">
                                    <div class="item-name"><?php echo esc_html( $item_name ); ?>
                                        <span>&times; <?php echo (int) $item_quantity; ?></span></div>
                                    <div class="item-price"><?php echo $item_price; ?></div>
                                </a>
                            </div>

                            <div class="clearfix"></div>
                        </li>

						<?php
					}
					echo "</ul>";
				}

				?>

                <div class="subtotal">
					<?php _e( "Subtotal", "automotive" ); ?>: <?php echo WC()->cart->get_cart_subtotal(); ?>
                </div>

                <div class="proceed">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'checkout' ) ); ?>"
                       class="button-link"><?php _e( "Proceed to Checkout", "automotive" ); ?></a>
                </div>

                <div class="view-cart">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"><?php _e( "View Cart", "automotive" ); ?></a>
                </div>
            </div>

			<?php

			$return = ob_get_clean();
		}

		return $return;
	}
}

// blog post
if ( ! function_exists( "blog_post" ) ) {
	function blog_post() {

	}
}

function auto_get_first_image( $post_id ) {
	$image = '';

	if ( get_post_status( $post_id ) == 'publish' ) {
		if ( has_post_thumbnail( $post_id ) ) {
			$image = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
		} else {
			$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_post_field( 'post_content', $post_id ), $matches );
			$image  = ( isset( $matches[1][0] ) && ! empty( $matches[1][0] ) ? $matches[1][0] : "" );
		}
	}

	return $image;
}

// Register Navigation
if ( ! function_exists( "register_automotive_menu" ) ) {
	function register_automotive_menu() {
		register_nav_menus( array(
			'header-menu' => __( 'Header Menu', 'automotive' ),
			'footer-menu' => __( 'Footer Menu', 'automotive' ),
			'mobile-menu' => __( 'Mobile Menu', 'automotive' ),

			'logged-in-header-menu' => __( 'Logged-in Header Menu', 'automotive' ),
			'logged-in-footer-menu' => __( 'Logged-in Footer Menu', 'automotive' ),
			'logged-in-mobile-menu' => __( 'Logged-in Mobile Menu', 'automotive' ),
		) );
	}
}

if ( ! function_exists( "browser_body_class" ) ) {
	function browser_body_class( $classes ) {
		global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

		if ( $is_lynx ) {
			$classes[] = 'lynx';
		} elseif ( $is_gecko ) {
			$classes[] = 'gecko';
		} elseif ( $is_opera ) {
			$classes[] = 'opera';
		} elseif ( $is_NS4 ) {
			$classes[] = 'ns4';
		} elseif ( $is_safari ) {
			$classes[] = 'safari';
		} elseif ( $is_chrome ) {
			$classes[] = 'chrome';
		} elseif ( $is_IE ) {
			$classes[] = 'ie';
		} else {
			$classes[] = 'unknown';
		}

		if ( $is_iphone ) {
			$classes[] = 'iphone';
		}

		return $classes;
	}
}
add_filter( 'body_class', 'browser_body_class' );

function automotive_theme_options_normalize($options){
	if(!empty($options)){
		foreach($options as $option_id => $option){
			if(!empty($option['fields'])){
				$options[$option_id]['fields'] = array_values($options[$option_id]['fields']);
			}
		}
	}

	return array_values($options);
}
add_filter('automotive_theme_options', 'automotive_theme_options_normalize', 999);
// If Dynamic Sidebar Exists
if ( function_exists( 'register_sidebar' ) ) {
	function automotive_sidebars() {
		global $awp_options;

		// custom footers
		if ( ! empty( $awp_options['footer_widget_spots'] ) ) {
			foreach ( $awp_options['footer_widget_spots'] as $footer ) {
				// Define Sidebar Widget Area $i
				if ( ! empty( $footer ) ) {
					register_sidebar( array(
						'name'          => $footer,
						'id'            => 'footer-widget-' . str_replace( " ", "-", strtolower( $footer ) ),
						'before_widget' => '<div id="%1$s" class="%2$s widget">',
						'after_widget'  => '</div>',
						'before_title'  => '<h4>',
						'after_title'   => '</h4>'
					) );
				}
			}
		}

		// custom sidebars
		if ( ! empty( $awp_options['custom_sidebars'] ) ) {
			foreach ( $awp_options['custom_sidebars'] as $sidebar ) {
				// Define Sidebar Widget Area $i
				if ( ! empty( $sidebar ) ) {
					$safe_name = str_replace( " ", "-", strtolower( $sidebar ) );

					register_sidebar( array(
						'name'          => $sidebar,
						'id'            => $safe_name,
						'before_widget' => '<div id="%1$s" class="side-widget widget padding-bottom-60 list col-xs-12 %2$s">',
						'after_widget'  => '</div>',
						'before_title'  => '<h3 class="side-widget-title margin-bottom-25">',
						'after_title'   => '</h3>'
					) );
				}
			}
		}

		// Define Sidebar Widget Area 5
		register_sidebar( array(
			'name'          => __( 'Default Footer', 'automotive' ),
			'id'            => 'default-footer',
			'before_widget' => '<div class="list col-xs-12">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>'
		) );

		// Define Sidebar Widget Area 5
		register_sidebar( array(
			'name'          => __( 'Blog Sidebar', 'automotive' ),
			'id'            => 'blog-sidebar',
			'before_widget' => '<div class="side-widget padding-bottom-60 list col-xs-12">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="side-widget-title margin-bottom-25">',
			'after_title'   => '</h3>'
		) );
	}

	add_action( "widgets_init", "automotive_sidebars" );
}

global $_automotive_footer_widget_counter;

$_automotive_footer_widget_counter = 0;

if ( ! function_exists( "automotive_bottom_sidebar_params" ) ) {
	function automotive_bottom_sidebar_params( $params ) {
		global $_automotive_footer_widget_counter;

		$sidebar_id = $params[0]['id'];

		if ( strpos( $sidebar_id, 'footer-widget' ) === 0 || $sidebar_id == 'default-footer' ) {

			$total_widgets   = wp_get_sidebars_widgets();
			$sidebar_widgets = count( $total_widgets[ $sidebar_id ] );

			if ( defined("ICL_LANGUAGE_CODE") ) {
				$all_language_active_widgets = 0;

				if ( ! empty( $total_widgets[ $sidebar_id ] ) ) {
					foreach ( $total_widgets[ $sidebar_id ] as $widget ) {
						$widget_parts = explode( "-", $widget );
						$widget_id    = end( $widget_parts );
						$widget_name  = "widget_" . str_replace( "-" . $widget_id, "", $widget );

						$widget_details = get_option( $widget_name );

						if ( isset( $widget_details[ $widget_id ] ) ) {
							if ( ! isset( $widget_details[ $widget_id ]['wpml_language'] ) || ( isset( $widget_details[ $widget_id ]['wpml_language'] ) && ( $widget_details[ $widget_id ]['wpml_language'] == ICL_LANGUAGE_CODE || $widget_details[ $widget_id ]['wpml_language'] == "all" ) ) ) {
								$all_language_active_widgets ++;
							}
						}
					}
				}

				$sidebar_widgets = $all_language_active_widgets;
			}


			// add padding
			foreach ( $total_widgets[ $sidebar_id ] as $key => $name ) {
				if ( $params[0]['widget_id'] == $name ) {
					$current_index = $key;
				}
			}

			// for single item stuff
			$md_sm = ( $sidebar_widgets == 1 ? 12 : 6 );
			$new_class = apply_filters('automotive_theme_footer_widget', "col-lg-" . floor( 12 / $sidebar_widgets ) . " col-md-" . $md_sm . " col-sm-12 col-xs-12", $_automotive_footer_widget_counter);

			$params[0]['before_widget'] = str_replace( 'class="', "class=\"" . $new_class . " ", $params[0]['before_widget'] );

			$_automotive_footer_widget_counter = $_automotive_footer_widget_counter+1;
		}

		return $params;
	}
}
add_filter( 'dynamic_sidebar_params', 'automotive_bottom_sidebar_params' );

// Pagination for paged posts, Page 1, Page 2, Page 3, with Next and Previous Links, No plugin
if ( ! function_exists( "automotive_pagination" ) ) {
	function automotive_pagination( $current_query = '' ) {
		wp_reset_query();
		global $wp_query;

		if ( is_page_template( "blog-template.php" ) ) {
			query_posts( array(
				'posts_per_page' => get_option( 'posts_per_page' ),
				'paged'          => get_query_var( 'paged' )
			) );
		}

		$big   = 999999999; // need an unlikely integer
		$pages = paginate_links( array(
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'    => '?paged=%#%',
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'total'     => $wp_query->max_num_pages,
			'type'      => 'array',
			'prev_next' => true,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
		) );

		if ( is_array( $pages ) ) {
			$paged = ( get_query_var( 'paged' ) == 0 ) ? 1 : get_query_var( 'paged' );
			echo '<ul class="pagination">';
			foreach ( $pages as $page ) {
				echo "<li>" . $page . "</li>\n";
			}
			echo '</ul>';
		}
	}
}

// Custom Comments Callback
if ( ! function_exists( "automotive_comments" ) ) {
	function automotive_comments( $comment, $args, $depth ) {
		// global $_temp_comment
		// $args = $args;
		automotive_theme_get_part('single-comment', false, array(
			'comment' => $comment,
			'args'    => $args,
			'depth'   => $depth
		));
	}
}

if ( ! function_exists( "automotive_commentform" ) ) {
	function automotive_commentform() {
		automotive_theme_get_part('comment-form');
	}
}

if ( ! function_exists( "get_page_title_and_desc" ) ) {
	function get_page_title_and_desc() {
		global $post, $awp_options;

		if ( is_404() ) {
			$desc  = __( "That being said, we will give you an amazing deal for the trouble", "automotive" );
			$title = __( "Error 404: File not found.", "automotive" );
		} else {
			if ( function_exists( "is_woocommerce" ) && ( is_shop() || is_checkout() || is_account_page() ) ) {
				if ( is_shop() ) {
					$page_id = get_option( 'woocommerce_shop_page_id' );
				} elseif ( is_checkout() ) {
					$page_id = get_option( 'woocommerce_pay_page_id' );
				} elseif ( is_account_page() ) {
					$page_id = get_option( 'woocommerce_myaccount_page_id' );
				} elseif ( is_account_page() ) {
					$page_id = get_option( 'woocommerce_edit_address_page_id' );
				} elseif ( is_account_page() ) {
					$page_id = get_option( 'woocommerce_view_order_page_id' );
				}

				$page = get_post( $page_id );

				$desc  = get_post_meta( $page->ID, "secondary_title", true );
				$title = get_the_title( $page->ID );
			} elseif ( function_exists( "is_product" ) && is_product() ) {
				$desc  = get_post_meta( get_queried_object_id(), "secondary_title", true );
				$title = get_the_title( get_queried_object_id() );
			} elseif ( function_exists( "is_product_category" ) && ( is_product_category() || is_product_tag() ) ) {
				global $wp_query;

				$cat   = $wp_query->get_queried_object();
				$desc  = $cat->description;
				$title = $cat->name;
			} elseif ( is_page() ) {
				global $post;

				$desc  = get_post_meta( $post->ID, "secondary_title", true );
				$title = get_the_title( $post->ID );
			} elseif ( is_home() ) {
				$id = get_option( 'page_for_posts' );

				$secondary_title = get_post_meta( $id, "secondary_title", true );

				$desc    = ( isset( $secondary_title ) && ! empty( $secondary_title ) ? $secondary_title : get_bloginfo( 'description' ) );
				$title   = ( $id == 0 ? get_bloginfo( 'name' ) : get_the_title( $id ) );
				$classes = "blog_page";
			} elseif ( is_category() ) {
				$cat = get_category( get_query_var( 'cat' ), false );

				$desc  = "";
				$title = __( "Category Archive", "automotive" ) . ": " . $cat->name;
			} elseif ( is_author() ) {
				$desc  = "";
				$title = __( "Author Archive", "automotive" ) . ": " . get_the_author();
			} elseif ( is_tag() ) {
				$desc  = "";
				$title = __( "Tag Archive", "automotive" ) . ": " . single_tag_title( "", false );
			} elseif ( is_search() ) {
				$desc  = "";
				$title = __( "Search term", "automotive" ) . ": " . get_search_query();
			} elseif ( is_singular( "listings" ) ) {
				global $lwp_options;

				$desc  = ( isset( $lwp_options['inventory_secondary_title'] ) && ! empty( $lwp_options['inventory_secondary_title'] ) ? $lwp_options['inventory_secondary_title'] : "" );
				$title = ( isset( $lwp_options['inventory_primary_title'] ) && ! empty( $lwp_options['inventory_primary_title'] ) ? $lwp_options['inventory_primary_title'] : "" );
			} elseif ( is_singular( "listings_portfolio" ) ) {
				$desc  = get_post_meta( $post->ID, "secondary_title", true );
				$title = get_the_title( $post->ID );
			} elseif ( is_single() ) {
				$desc  = ( isset( $awp_options['blog_secondary_title'] ) && ! empty( $awp_options['blog_secondary_title'] ) ? $awp_options['blog_secondary_title'] : __( "Latest Industry News", "automotive" ) );
				$title = ( isset( $awp_options['blog_primary_title'] ) && ! empty( $awp_options['blog_primary_title'] ) ? $awp_options['blog_primary_title'] : __( "Blog", "automotive" ) );
			} else {
				global $post;

        if(isset($post->ID)){
  				$desc  = get_post_meta( $post->ID, "secondary_title", true );
  				$title = get_the_title( $post->ID );
        } else {
          $desc = '';
          $title = '';
        }
			}
		}

		return array( $title, $desc );
	}
}

function safe_html_cut( $text, $max_length ) {
	$tags   = array();
	$result = "";

	$is_open          = false;
	$grab_open        = false;
	$is_close         = false;
	$in_double_quotes = false;
	$in_single_quotes = false;
	$tag              = "";

	$i        = 0;
	$stripped = 0;

	$stripped_text = strip_tags( $text );

	while ( $i < strlen( $text ) && $stripped < strlen( $stripped_text ) && $stripped < $max_length ) {
		$symbol = $text[$i];
		$result .= $symbol;

		switch ( $symbol ) {
			case '<':
				$is_open   = true;
				$grab_open = true;
				break;

			case '"':
				if ( $in_double_quotes ) {
					$in_double_quotes = false;
				} else {
					$in_double_quotes = true;
				}

				break;

			case "'":
				if ( $in_single_quotes ) {
					$in_single_quotes = false;
				} else {
					$in_single_quotes = true;
				}

				break;

			case '/':
				if ( $is_open && ! $in_double_quotes && ! $in_single_quotes ) {
					$is_close  = true;
					$is_open   = false;
					$grab_open = false;
				}

				break;

			case ' ':
				if ( $is_open ) {
					$grab_open = false;
				} else {
					$stripped ++;
				}

				break;

			case '>':
				if ( $is_open ) {
					$is_open   = false;
					$grab_open = false;
					array_push( $tags, $tag );
					$tag = "";
				} else if ( $is_close ) {
					$is_close = false;
					array_pop( $tags );
					$tag = "";
				}

				break;

			default:
				if ( $grab_open || $is_close ) {
					$tag .= $symbol;
				}

				if ( ! $is_open && ! $is_close ) {
					$stripped ++;
				}
		}

		$i ++;
	}

	while ( $tags ) {
		$result .= "</" . array_pop( $tags ) . ">";
	}

	return $result;
}

if(!function_exists("get_the_breadcrumbs")){
  function automotive_get_the_breadcrumbs( $last_text = '' ){
    $return          = array();
    $position_i      = 1;
		$character_limit = 75;
    $breadcrumb_text = '';

    // if we custom set the last breadcrumb text
		if ( ! empty( $last_text ) ) {
			$breadcrumb_text = ( automotive_strlen( $last_text ) > $character_limit ? automotive_substr( $last_text, 0, $character_limit ) . "..." : $last_text );
		}

		if ( ! is_front_page() ) {
			global $post, $awp_options, $lwp_options;

				$return[] = array('url' => home_url(), 'text' => __( "Home", "automotive" ) );

				if ( isset( $post ) && ! empty( $post ) && $post->post_parent ) {
					$parent_post = get_post( $post->post_parent );

					$return[] = array('url' => get_permalink( $post->post_parent ), 'text' => $parent_post->post_title);
				}

				if ( is_404() || is_page_template( "404.php" ) ) {
					$return[] = array('url' => '#', 'text' => ( ! empty( $last_text ) ? $last_text : "404" ) );
				} elseif ( is_search() ) {
					$return[] = array('url' => '#', 'text' => ( !empty( $breadcrumb_text ) ? $breadcrumb_text : __( "Search", "automotive" ) . ": " . get_search_query() ) );
				} elseif ( is_single() ) {
					if ( is_singular( 'listings' ) ) {

						if ( isset( $lwp_options['inventory_page'] ) && ! empty( $lwp_options['inventory_page'] ) ) {
							$inventory_page_id = apply_filters( "wpml_object_id", $lwp_options['inventory_page'], "page", true );

							$inventory_link  = get_permalink( $inventory_page_id );
							$inventory_title = get_the_title( $inventory_page_id );

							$return[] = array('url' => $inventory_link, 'text' => $inventory_title);
						}

					} elseif ( is_singular( "listings_portfolio" ) ) {
						$cats = wp_get_object_terms( $post->ID, "project-type" );

						if ( ! empty( $cats ) ) {
							foreach ( $cats as $cat ) {
								$return[] =  array('url' => get_category_link( $cat->term_id ), 'text' => $cat->name);
							}
						}
					} elseif ( function_exists( "is_product" ) && is_product() ) {
						$shop_id = get_option( 'woocommerce_shop_page_id' );
						$page    = get_post( $shop_id );

						$return[] = array('url' => get_permalink( $shop_id ), 'text' => get_the_title( $page->ID ) );
					} else {
						$breadcrumb_style = ( isset( $awp_options['breadcrumb_style'] ) && ! empty( $awp_options['breadcrumb_style'] ) ? $awp_options['breadcrumb_style'] : "" );

						if ( $breadcrumb_style == 0 ) {
							$cats = wp_get_post_categories( $post->ID );
							if ( ! empty( $cats ) ) {
								foreach ( $cats as $cat ) {
									$cat      = get_category( $cat );
									$return[] = array('url' => get_permalink( $cat->term_id ), 'text' => $cat->name);
								}
							}
						} else {
							$posts_page = get_option( 'page_for_posts' );

							if ( isset( $posts_page ) && ! empty( $posts_page ) ) {
								$return[] = array('url' => get_permalink( $posts_page ), 'text' => get_the_title( $posts_page ) );
							}
						}
					}

					$return[] = array('url' => '#', 'text' => ( automotive_strlen( get_the_title() ) > $character_limit ? automotive_substr( get_the_title(), 0, $character_limit ) . "..." : get_the_title() ) );
				} elseif ( is_archive() ) {
					if ( is_category() && ! isset( $breadcrumb_text ) ) {
						$return[] = array('url' => '#', 'text' => ( isset( $breadcrumb_text ) ? $breadcrumb_text : __( "Category Archives", "automotive" ) ) );
						$text       = single_cat_title( '', false );

					} elseif ( is_tag() && ! isset( $breadcrumb_text ) ) {
						$return[] = array('url' => '#', 'text' => ( isset( $breadcrumb_text ) ? $breadcrumb_text : __( "Tag Archives", "automotive" ) ) );
						$text       = single_tag_title( '', false );

					} elseif ( is_author() ) {
						the_post();

						$text = sprintf( __( 'Author Archives: %s', 'automotive' ), get_the_author() );

						rewind_posts();

					} elseif ( is_day() ) {
						$return[] = array('url' => '#', 'text' => __( "Daily Archives", "automotive" ) );
						$text       = get_the_date();

					} elseif ( is_month() ) {
						$return[] = array('url' => '#', 'text' => __( "Monthly Archives", "automotive" ) );
						$text       = get_the_date( 'F Y' );

					} elseif ( is_year() ) {
						$return[] = array('url' => '#', 'text' => __( "Yearly Archives", "automotive" ) );
						$text       = get_the_date( 'Y' );

					} elseif ( function_exists( "is_shop" ) && is_shop() ) {
						$text = get_the_title( get_option( 'woocommerce_shop_page_id' ) );
					} elseif ( function_exists( "is_product_category" ) && ( is_product_category() || is_product_tag() ) ) {
						global $wp_query;

						$cat  = $wp_query->get_queried_object();
						$text = $cat->name;
					} else {
						$text = __( 'Archives', 'automotive' );

					}

					if ( ! empty( $last_text ) ) {
						$return[] = array('url' => '#', 'text' => ( automotive_strlen( $last_text ) > $character_limit ? automotive_substr( $last_text, 0, $character_limit ) . "..." : $last_text ) );
					} else {
						$return[] = array('url' => '#', 'text' => ( automotive_strlen( $text ) > $character_limit ? automotive_substr( $text, 0, $character_limit ) . "..." : $text ) );
					}

				} else {
					$title = get_the_title( get_queried_object_id() );

					if ( ! empty( $last_text ) ) {
						$return[] = array('url' => '#', 'text' => ( automotive_strlen( $last_text ) > $character_limit ? automotive_substr( $last_text, 0, $character_limit ) . "..." : $last_text ) );
					} else {
						$return[] = array('url' => '#', 'text' => ( automotive_strlen( $title ) > $character_limit ? automotive_substr( $title, 0, $character_limit ) . "..." : $title ) );
					}
				}
      }

      return $return;
  }
}

if(!function_exists('automotive_strlen')){
  function automotive_strlen($str){
    return (function_exists('mb_strlen') ? mb_strlen($str) : strlen($str));
  }
}

if(!function_exists('automotive_substr')){
  function automotive_substr($str, $start, $length = null){
    if($length){
      return (function_exists('mb_substr') ? mb_substr($str, $start, $length) : substr($str, $start, $length));
    } else {
      return (function_exists('mb_substr') ? mb_substr($str, $start) : substr($str, $start));
    }
  }
}

function automotive_theme_add_breadcrumb_ld_json($jsons){
  $breadcrumbs         = array();
  $current_breadcrumbs = automotive_get_the_breadcrumbs();

  if(!empty($current_breadcrumbs) ){
    $breadcrumb_i = 1;

    foreach($current_breadcrumbs as $single_breadcrumb){
      $breadcrumbs[] = array(
        '@type'    => 'ListItem',
        'position' => $breadcrumb_i,
        'item'     => array(
          '@id'  => esc_url($single_breadcrumb['url']),
          'name' => sanitize_text_field($single_breadcrumb['text'])
        )
      );

      $breadcrumb_i++;
    }

    $jsons['breadcrumb'] = array(
      '@context'        => 'http://schema.org',
      '@type'           => 'BreadcrumbList',
      'itemListElement' => $breadcrumbs
    );
  }

  return $jsons;
}
add_filter('automotive_theme_json_ld', 'automotive_theme_add_breadcrumb_ld_json');

function automotive_get_page_handle(){
  $handle = "";
  // determine handle
  if ( is_search() ) {
  	$handle = "search";
  } elseif ( is_tag() ) {
  	$handle = "tag";
  } elseif ( is_category() ) {
  	$handle = "category";
  } elseif ( is_404() ) {
  	$handle = "fourohfour";
  } elseif ( function_exists( "is_product_category" ) && is_product_category() ) {
  	$handle = "woo_category";
  } elseif ( function_exists( "is_product_tag" ) && is_product_tag() ) {
  	$handle = "woo_tag";
  } elseif ( function_exists( "is_shop" ) && is_shop() ) {
  	$handle = "woo_shop";
  } elseif ( ( get_option( 'show_on_front' ) == "posts" && is_home() ) ) {
  	$handle = "homepage_blog";
  }

  return $handle;
}

function automotive_get_page_info(){
  global $awp_options;

  $handle     = automotive_get_page_handle();
  $title      = '';
  $desc       = '';
  $breadcrumb = '';

  if ( is_search() || is_tag() || is_category() || is_404() || ( function_exists( "is_product_category" ) && is_product_category() ) || function_exists( "is_product_tag" ) && is_product_tag() || function_exists( "is_shop" ) && is_shop() || ( get_option( 'show_on_front' ) == "posts" && is_home() ) ) {
    $title      = ( isset( $awp_options[ $handle . '_page_title' ] ) && ! empty( $awp_options[ $handle . '_page_title' ] ) ? $awp_options[ $handle . '_page_title' ] : "" );
    $desc       = ( isset( $awp_options[ $handle . '_page_secondary_title' ] ) && ! empty( $awp_options[ $handle . '_page_secondary_title' ] ) ? $awp_options[ $handle . '_page_secondary_title' ] : "" );
    $breadcrumb = ( isset( $awp_options[ $handle . '_page_breadcrumb' ] ) && ! empty( $awp_options[ $handle . '_page_breadcrumb' ] ) ? $awp_options[ $handle . '_page_breadcrumb' ] : "" );

    // determine if variable
    $query = "{query}";
    if ( is_search() ) {
      $title      = ( strstr( $title, $query ) ? str_replace( $query, get_search_query(), $title ) : $title );
      $desc       = ( strstr( $desc, $query ) ? str_replace( $query, get_search_query(), $desc ) : $desc );
      $breadcrumb = ( strstr( $breadcrumb, $query ) ? str_replace( $query, get_search_query(), $breadcrumb ) : $breadcrumb );
    } elseif ( is_tag() ) {
      $tag        = single_tag_title( "", false );
      $title      = ( strstr( $title, $query ) ? str_replace( $query, $tag, $title ) : $title );
      $desc       = ( strstr( $desc, $query ) ? str_replace( $query, $tag, $desc ) : $desc );
      $breadcrumb = ( strstr( $breadcrumb, $query ) ? str_replace( $query, $tag, $breadcrumb ) : $breadcrumb );
    } elseif ( is_category() ) {
      $category   = single_cat_title( "", false );
      $title      = ( strstr( $title, $query ) ? str_replace( $query, $category, $title ) : $title );
      $desc       = ( strstr( $desc, $query ) ? str_replace( $query, $category, $desc ) : $desc );
      $breadcrumb = ( strstr( $breadcrumb, $query ) ? str_replace( $query, $category, $breadcrumb ) : $breadcrumb );
    } elseif ( ( function_exists( "is_product_category" ) && is_product_category() ) || ( function_exists( "is_product_tag" ) && is_product_tag() ) || ( function_exists( "is_shop" ) && is_shop() ) ) {
      $category   = single_term_title( '', false );
      $title      = ( strstr( $title, $query ) ? str_replace( $query, $category, $title ) : $title );
      $desc       = ( strstr( $desc, $query ) ? str_replace( $query, $category, $desc ) : $desc );
      $breadcrumb = ( strstr( $breadcrumb, $query ) ? str_replace( $query, $category, $breadcrumb ) : $breadcrumb );
    }
  } else {
    $titles = get_page_title_and_desc();
    $title  = $titles[0];
    $desc   = $titles[1];
  }

  return apply_filters('automotive_page_header_info', array(
    'title'      => $title,
    'desc'       => $desc,
    'breadcrumb' => $breadcrumb
  ) );
}

//********************************************
//	The breadcrumb
//***********************************************************
if ( ! function_exists( "the_breadcrumb" ) ) {
	function the_breadcrumb( $last_text = false ) {
    $character_limit = 75;

    if ( ! is_front_page() ) {
      global $post, $awp_options, $lwp_options;

      if ( function_exists( "is_woocommerce" ) && is_woocommerce() ) {
        woocommerce_breadcrumb( array(
          "wrap_before" => "<nav class='breadcrumb woocommerce_breadcrumb'>",
          "wrap_after"  => "</nav>",
          "delimiter"   => "&nbsp;&nbsp;/&nbsp;&nbsp;"
        )
      );
    } else {
      $breadcrumb_text = $last_text;

      if ( $last_text ) {
        $breadcrumb_text = ( mb_strlen( $last_text ) > $character_limit ? mb_substr( $last_text, 0, $character_limit ) . "..." : $last_text );
      }

      $breadcrumb          = '';
      $current_breadcrumbs = automotive_get_the_breadcrumbs($breadcrumb_text);

      if(!empty($current_breadcrumbs)){
        $breadcrumb .= "<ul class='breadcrumb'>";

        foreach($current_breadcrumbs as $single_breadcrumb) {
          $breadcrumb .= '<li><a href="' . esc_url($single_breadcrumb['url']) . '"><span>' . esc_html($single_breadcrumb['text']) . '</span></a></li>';
        }

        $breadcrumb .= "</ul>";
      }

      // $breadcrumb  = "<ul class='breadcrumb' itemscope itemtype=\"http://schema.org/BreadcrumbList\">";
      // $breadcrumb .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . home_url() . '" itemprop="item" position="' . esc_attr($position_i) . '"><span itemprop="name">' . __( "Home", "automotive" ) . '</span></a></li>';
      //
      // if ( isset( $post ) && ! empty( $post ) && trim( $post->post_parent ) != "" && $post->post_parent != 0 ) {
      //   $parent_post = get_post( $post->post_parent );
      //   $breadcrumb  .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . get_permalink( $post->post_parent ) . "' itemprop=\"item\"><span itemprop=\"name\">" . $parent_post->post_title . "</span></a></li>";
      // }
      //
      // if ( is_404() || is_page_template( "404.php" ) ) {
      //   $breadcrumb .= " <li class='current_crumb' itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><span itemprop=\"name\">" . ( isset( $last_text ) && ! empty( $last_text ) ? $last_text : "404" ) . "</span></li>";
      // } elseif ( is_search() ) {
      //   $breadcrumb .= " <li class='current_crumb' itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><span itemprop=\"name\">" . ( isset( $breadcrumb_text ) ? $breadcrumb_text : __( "Search", "automotive" ) . ": " . get_search_query() ) . "</span></li>";
      // } elseif ( is_single() ) {
      //   if ( is_singular( 'listings' ) ) {
      //
      //     if ( isset( $lwp_options['inventory_page'] ) && ! empty( $lwp_options['inventory_page'] ) ) {
      //       $inventory_page_id = apply_filters( "wpml_object_id", $lwp_options['inventory_page'], "page", true );
      //
      //       $inventory_link  = get_permalink( $inventory_page_id );
      //       $inventory_title = get_the_title( $inventory_page_id );
      //
      //       $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . $inventory_link . "' itemprop=\"item\"><span itemprop=\"name\">" . $inventory_title . "</span></a></li>";
      //     }
      //
      //   } elseif ( is_singular( "listings_portfolio" ) ) {
      //     $cats = wp_get_object_terms( $post->ID, "project-type" );
      //     if ( ! empty( $cats ) ) {
      //       foreach ( $cats as $cat ) {
      //         $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . get_category_link( $cat->term_id ) . "' itemprop=\"item\"><span itemprop=\"name\">" . $cat->name . "</span></a></li>";
      //       }
      //     }
      //   } elseif ( function_exists( "is_product" ) && is_product() ) {
      //     $shop_id = get_option( 'woocommerce_shop_page_id' );
      //     $page    = get_post( $shop_id );
      //
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . get_permalink( $shop_id ) . "' itemprop=\"item\"><span itemprop=\"name\">" . get_the_title( $page->ID ) . "</span></a></li>";
      //   } else {
      //     $breadcrumb_style = ( isset( $awp_options['breadcrumb_style'] ) && ! empty( $awp_options['breadcrumb_style'] ) ? $awp_options['breadcrumb_style'] : "" );
      //
      //     if ( $breadcrumb_style == 0 ) {
      //       $cats = wp_get_post_categories( $post->ID );
      //       if ( ! empty( $cats ) ) {
      //         foreach ( $cats as $cat ) {
      //           $cat        = get_category( $cat );
      //           $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . get_permalink( $cat->term_id ) . "' itemprop=\"item\"><span itemprop=\"name\">" . $cat->name . "</span></a></li>";
      //         }
      //       }
      //     } else {
      //       $posts_page = get_option( 'page_for_posts' );
      //
      //       if ( isset( $posts_page ) && ! empty( $posts_page ) ) {
      //         $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='" . get_permalink( $posts_page ) . "' itemprop=\"item\"><span itemprop=\"name\">" . get_the_title( $posts_page ) . "</span></a></li>";
      //       }
      //     }
      //   }
      //
      //   $breadcrumb .= " <li class='current_crumb' itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><span itemprop=\"name\">" . ( mb_strlen( get_the_title() ) > $character_limit ? mb_substr( get_the_title(), 0, $character_limit ) . "..." : get_the_title() ) . "</span></li>";
      // } elseif ( is_archive() ) {
      //   if ( is_category() && ! isset( $breadcrumb_text ) ) {
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='#' itemprop=\"item\"><span itemprop=\"name\">" . ( isset( $breadcrumb_text ) ? $breadcrumb_text : __( "Category Archives", "automotive" ) ) . "</span></a></li>";
      //     $text       = single_cat_title( '', false );
      //
      //   } elseif ( is_tag() && ! isset( $breadcrumb_text ) ) {
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='#' itemprop=\"item\"><span itemprop=\"name\">" . ( isset( $breadcrumb_text ) ? $breadcrumb_text : __( "Tag Archives", "automotive" ) ) . "</span></a></li>";
      //     $text       = single_tag_title( '', false );
      //
      //   } elseif ( is_author() ) {
      //     the_post();
      //     $text = sprintf( __( 'Author Archives: %s', 'automotive' ), get_the_author() );
      //     rewind_posts();
      //
      //   } elseif ( is_day() ) {
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='#' itemprop=\"item\"><span itemprop=\"name\">" . __( "Daily Archives", "automotive" ) . "</span></a></li>";
      //     $text       = get_the_date();
      //
      //   } elseif ( is_month() ) {
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='#' itemprop=\"item\"><span itemprop=\"name\">" . __( "Monthly Archives", "automotive" ) . "</span></a></li>";
      //     $text       = get_the_date( 'F Y' );
      //
      //   } elseif ( is_year() ) {
      //     $breadcrumb .= "<li itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><a href='#' itemprop=\"item\"><span itemprop=\"name\">" . __( "Yearly Archives", "automotive" ) . "</span></a></li>";
      //     $text       = get_the_date( 'Y' );
      //
      //   } elseif ( function_exists( "is_shop" ) && is_shop() ) {
      //     $text = get_the_title( get_option( 'woocommerce_shop_page_id' ) );
      //   } elseif ( function_exists( "is_product_category" ) && ( is_product_category() || is_product_tag() ) ) {
      //     global $wp_query;
      //
      //     $cat  = $wp_query->get_queried_object();
      //     $text = $cat->name;
      //   } else {
      //     $text = __( 'Archives', 'automotive' );
      //
      //   }
      //
      //   $breadcrumb .= " <li class='current_crumb' itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><span itemprop=\"name\">";
      //     if ( isset( $last_text ) && ! empty( $last_text ) ) {
      //       $breadcrumb .= ( mb_strlen( $last_text ) > $character_limit ? mb_substr( $last_text, 0, $character_limit ) . "..." : $last_text );
      //     } else {
      //       $breadcrumb .= ( mb_strlen( $text ) > $character_limit ? mb_substr( $text, 0, $character_limit ) . "..." : $text );
      //     }
      //     $breadcrumb .= "</span></li>";
      //   } else {
      //     $title = get_the_title( get_queried_object_id() );
      //
      //     $breadcrumb .= " <li class='current_crumb' itemprop=\"itemListElement\" itemscope itemtype=\"http://schema.org/ListItem\"><span itemprop=\"name\">";
      //       if ( isset( $last_text ) && ! empty( $last_text ) ) {
      //         $breadcrumb .= ( mb_strlen( $last_text ) > $character_limit ? mb_substr( $last_text, 0, $character_limit ) . "..." : $last_text );
      //       } else {
      //         $breadcrumb .= ( mb_strlen( $title ) > $character_limit ? mb_substr( $title, 0, $character_limit ) . "..." : $title );
      //       }
      //       $breadcrumb .= "</span></li>";
      //     }
			// 	$breadcrumb .= "</ul>";

				echo $breadcrumb;
			}
		}
	}
}

if ( ! function_exists( "random_string" ) ) {
	function random_string( $length = 10 ) {
		$characters   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $randomString;
	}
}

if ( ! function_exists( "get_table_prefix" ) ) {
	function get_table_prefix() {
		global $wpdb;

		return $wpdb->prefix;
	}
}

//********************************************
//	Editor Styles
//***********************************************************
if ( ! function_exists( "theme_editor_styles" ) ) {
	function theme_editor_styles() {
		add_editor_style( "css/wp.css" );
		add_editor_style( "css/bootstrap.min.css" );
		add_editor_style( "css/style.css" );
		add_editor_style( "css/all.min.css" );
		add_editor_style( "css/custom.css" );
	}
}

add_action( 'init', 'theme_editor_styles' );

function automotive_theme_editor_dynamic_styles( $mceInit ) {
	$theme_color_scheme = automotive_theme_get_option('theme_color_scheme', array());
	$global_link        = (isset($theme_color_scheme['global-link']['rgba']) ? $theme_color_scheme['global-link']['rgba'] : 'rgba(199,8,27,0)');
	$global_link_hover  = (isset($theme_color_scheme['global-link-hover']['rgba']) ? $theme_color_scheme['global-link-hover']['rgba'] : 'rgba(199,8,27,0)');
	$global_link_active = (isset($theme_color_scheme['global-link-active']['rgba']) ? $theme_color_scheme['global-link-active']['rgba'] : 'rgba(199,8,27,0)');

	$styles  = 'a { color: ' . $global_link . '} ';
	$styles .= 'a:hover { color: ' . $global_link_hover . '} ';
	$styles .= 'a:active { color: ' . $global_link_active . '} ';

	if ( isset( $mceInit['content_style'] ) ) {
	    $mceInit['content_style'] .= ' ' . $styles . ' ';
	} else {
	    $mceInit['content_style'] = $styles . ' ';
	}

	return $mceInit;
}
add_filter('tiny_mce_before_init','automotive_theme_editor_dynamic_styles');


//********************************************
//  Actions + Filters
//***********************************************************
add_action( 'wp_print_scripts', 'automotive_conditional_scripts' );
//add_action( 'wp_enqueue_scripts', 'automotive_styles' );
//add_action( 'wp_enqueue_scripts', 'automotive_scripts' );
add_action( 'init', 'register_automotive_menu' );
add_action( 'init', 'automotive_pagination' );



//********************************************
//	Header Actions
//***********************************************************
function automotive_theme_google_analytics_head(){
	automotive_google_analytics_code("head");
}
add_action('automotive_theme_head_end', 'automotive_theme_google_analytics_head');

function automotive_theme_header_json_ld(){
	$automotive_json_schema = apply_filters('automotive_theme_json_ld', array());

	if(!empty($automotive_json_schema) && is_array($automotive_json_schema)){
		echo '<script type="application/ld+json">';
		echo wp_json_encode(array_values($automotive_json_schema));
		echo '</script>';
	}
}
add_action('automotive_theme_head_end', 'automotive_theme_header_json_ld');

function automotive_theme_facebook_share_img(){
	if ( get_post_type() == "listings" && ! defined( "WPSEO_VERSION" ) ) {
		$gallery_images = get_post_meta( get_current_id(), "gallery_images", true );

		if ( isset( $gallery_images[0] ) && ! empty( $gallery_images[0] ) ) {
			$image = wp_get_attachment_image_src( $gallery_images[0], 'thumb' );

			echo '<meta property="og:image" content="' . $image[0] . '" />';
		}
	}
}
add_action('automotive_theme_head_end', 'automotive_theme_facebook_share_img');

function automotive_theme_meta_tags() {
	// exception for Yoast SEO
	if ( ! defined( "WPSEO_VERSION" ) && ! defined("AIOSEOP_VERSION") ) {
		$listing_seo_string = get_option( "listing_seo_string" );
		if ( is_singular( "listings" ) && ! empty( $listing_seo_string ) ) {
			$listing_seo_string = convert_seo_string( $listing_seo_string ); ?>
    	<meta name="description" content="<?php echo $listing_seo_string; ?>">
		<?php } else { ?>
    	<meta name="description" content="<?php bloginfo( 'description' ); ?>">
		<?php }
	}
}
add_action('automotive_theme_head_end', 'automotive_theme_meta_tags');

function automotive_theme_favicon(){
	$favicon = automotive_theme_get_option('favicon', false);

	if ( ! empty( $favicon['url'] ) ) {
		echo '<link href="' . esc_attr($favicon['url']) . '" rel="shortcut icon">';
	}
}

function automotive_theme_head_meta(){ ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<?php }
add_action('automotive_theme_head_end', 'automotive_theme_head_meta');


function automotive_theme_logo(){
	$logo_link = automotive_theme_get_option('logo_link', true);

	echo ( $logo_link ? '<a class="navbar-brand" href="' . home_url() . '">' : '<a class="navbar-brand">' ); ?>
		<span class="logo">
				<?php
				$wpml_language_logos = automotive_theme_get_option('wpml_language_logos', false);
				$logo_image          = automotive_theme_get_option('logo_image', false);
				$pdf_logo            = automotive_listing_get_option('pdf_logo', false);

				//wpml multiple logos
				if ( $wpml_language_logos && defined( "ICL_LANGUAGE_CODE" ) ) {
					$wpml_logo_image = automotive_theme_get_option( 'logo_image_' . ICL_LANGUAGE_CODE, false ); ?>
						<img src='<?php echo $wpml_logo_image['url']; ?>'
								 class='main_logo' alt='logo'>
						<img src="<?php echo ( $pdf_logo ? $pdf_logo['url'] : $wpml_logo_image['url'] ); ?>"
								 class="pdf_print_logo">
				<?php } elseif ( isset( $logo_image['url'] ) && ! empty( $logo_image['url'] ) ) { ?>
						<img src='<?php echo $logo_image['url']; ?>'
								 class='main_logo' alt='logo'>
						<img src="<?php echo ( $pdf_logo ? $pdf_logo['url'] : $logo_image['url'] ); ?>"
								 class="pdf_print_logo">
				<?php } else { ?>
						<span class="primary_text"><?php echo automotive_theme_get_option('logo_text', ''); ?></span>
						<span class="secondary_text"><?php echo automotive_theme_get_option('logo_text_secondary', ''); ?></span>
				<?php } ?>
		</span>
	<?php echo( $logo_link ? '</a>' : '</a>' );
}
add_action('automotive_theme_header_logo', 'automotive_theme_logo');


function automotive_theme_footer_text(){
	$footer_text = automotive_theme_get_option('footer_text', 'Powered by {wp-link}. Built with {theme-link}.');

	if($footer_text){
	  $wp_link       = "<a href='http://www.wordpress.org'>WordPress</a>";
	  $theme_link    = "<a href='https://www.themesuite.com'>Automotive</a>";
	  $loginout_link = wp_loginout("", false);
	  $blog_title    = get_bloginfo('name');
	  $blog_link     = site_url();
	  $the_year      = date("Y");

	  $search      = array("{wp-link}", "{theme-link}", "{loginout-link}", "{blog-title}", "{blog-link}", "{the-year}");
	  $replace     = array($wp_link, $theme_link, $loginout_link, $blog_title, $blog_link, $the_year);
	  $footer_text = str_replace($search, $replace, $footer_text);
	}

	$footer_text = wpautop( do_shortcode($footer_text) );

	echo $footer_text;
}
add_action('automotive_theme_footer_text', 'automotive_theme_footer_text');


//********************************************
//	Footer Actions
//***********************************************************
function automotive_theme_back_to_top(){
	$back_to_top = automotive_theme_get_option('back_to_top', true);

	if($back_to_top) { ?>
	<div class="back_to_top">
	  <img src="<?php echo get_template_directory_uri(); ?>/images/arrow-up.png" alt="<?php _e('Back to top', 'automotive'); ?>" />
	</div>
	<?php }
}
add_action('automotive_theme_footer_end', 'automotive_theme_back_to_top');

function automotive_theme_custom_js(){
	$custom_js           = automotive_theme_get_option('custom_js', false);

	if($custom_js){ ?>
	<script type="text/javascript">
	    (function($) {
	        "use strict";
	        jQuery(document).ready( function($){
						<?php echo $custom_js; ?>
	        });
	    })(jQuery);
	</script>
<?php }
}
add_action('automotive_theme_footer_end', 'automotive_theme_custom_js');

function automotive_theme_google_analytics_body(){
	automotive_google_analytics_code("body");
}
add_action('automotive_theme_footer_end', 'automotive_theme_google_analytics_body');

class bs4Navwalker extends Walker_Nav_Menu
{
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"dropdown-menu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		/**
		 * Filter the CSS class(es) applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array  $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

		// New
		$class_names .= ' nav-item';

		if (in_array('menu-item-has-children', $classes)) {
			$class_names .= ' dropdown';
		}

		if (in_array('current-menu-item', $classes) || in_array( 'current-menu-ancestor', $classes )) {
			$class_names .= ' active';
		}

		if($item->object_id == get_option('page_for_posts')){
			$class_names .= ' blog-page';
		}

		if ($item->title == 'woocommerce-cart') {
			$class_names .= ' woocommerce-cart-menu-item';
        }
		//

		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		// print_r($class_names);

		/**
		 * Filter the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		// New
		if ($depth === 0) {
			$output .= $indent . '<li' . $id . $class_names .'>';
		}
		//

		// $output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
		$atts['class']  = '';

		// New
		if ($depth === 0) {
			$atts['class'] = 'nav-link';
		}

		if (/*$depth === 0 && */in_array('menu-item-has-children', $classes)) {
			$atts['class']       .= ' dropdown-toggle';
			$atts['data-toggle']  = 'dropdown';
		}

		if ($depth > 0) {
			$atts['class'] = 'dropdown-item ' . implode(' ', $item->classes);


//			if(strstr($item['classes'], 'menu-item-has-children')){
//				$atts['class'] .= ' dropdown';
//			}
		}

		if (in_array('current-menu-item', $item->classes)) {
			$atts['class'] .= ' active';
		}
		// print_r($item);
		//

		/**
		 * Filter the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title  Title attribute.
		 *     @type string $target Target attribute.
		 *     @type string $rel    The rel attribute.
		 *     @type string $href   The href attribute.
		 * }
		 * @param object $item  The current menu item.
		 * @param array  $args  An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;

        if($depth > 0){
            $deep_class = '';

            if(strstr($atts['class'], 'active') || in_array( 'current-menu-ancestor', $classes )){
                $deep_class .= ' active';
            }

            if(in_array('menu-item-has-children', $classes)){
                $deep_class .= ' dropdown';
            }

            $item_output .= '<li' . (isset($deep_class) && !empty($deep_class) ? ' class="' . trim($deep_class) . '"' : '') . '>';
        }

		$item_output .= '<a' . $attributes . '>';
		if ( $item->title == "woocommerce-cart" && function_exists( "is_woocommerce" ) ) {
			$cart_count = WC()->cart->get_cart_contents_count();

			$item_output .= $args->link_before . "<i class='fa fa-shopping-bag'></i><span class='woocommerce-total-cart-count" . ( $cart_count === 0 ? " empty" : "" ) . "'>" . esc_html( $cart_count ) . "</span>" . $args->link_after;
			$item_output .= ( isset($args->has_children) && $args->has_children && 0 === $depth ) ? ' <b class="caret"></b></a>' : '</a>';

			$item_output .= auto_woocommerce_menu_basket();
		} else {
			/** This filter is documented in wp-includes/post-template.php */
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		}
		$item_output .= '</a>';
		$item_output .= $args->after;

		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of {@see wp_nav_menu()} arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_el( &$output, $item, $depth = 0, $args = array() ) {
//		if (isset($args->has_children)) {
			$output .= "</li>\n";
//		}
	}
}

//********************************************
//	Action Area
//***********************************************************
if ( ! function_exists( "action_area" ) ) {
	function action_area( $action, $no_header = false ) {
		global $post;

		if ( isset( $post ) && ! empty( $post ) && isset( $action ) && $action == "on" ) {
			$action_text        = get_post_meta( $post->ID, "action_text", true );
			$action_button_text = get_post_meta( $post->ID, "action_button_text", true );
			$action_link        = get_post_meta( $post->ID, "action_link", true );
			$action_class       = get_post_meta( $post->ID, "action_class", true );

			$heading_class = ( ! empty( $action_button_text ) ? "col-lg-9 col-md-8 col-sm-8 col-xs-12 xs-padding-left-15" : "col-lg-12 col-md-12 col-sm-12 col-xs-12 xs-padding-left-15" ); ?>

            <section
                    class="message-wrap<?php echo( $no_header && $no_header == "no_header" ? " no-header-push" : "" ); ?>">
                <div class="container">
                    <div class="row">
                        <h2 class="<?php echo $heading_class; ?>"><?php echo $action_text; ?></h2>
						<?php if ( ! empty( $action_button_text ) ) { ?>
                            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12 xs-padding-right-15"><a
                                        href="<?php echo $action_link; ?>"
                                        class="default-btn pull-right action_button<?php echo( isset( $action_class ) && ! empty( $action_class ) ? " " . $action_class : "" ); ?>"><?php echo( $action_button_text ); ?></a>
                            </div>
						<?php } ?>
                    </div>
                </div>
                <div class="message-shadow"></div>
            </section>
			<?php
		}
	}
}

//********************************************
//	Add custom active class
//***********************************************************
add_filter( 'nav_menu_css_class', 'add_menu_active_class', 10, 2 );

if ( ! function_exists( "add_menu_active_class" ) ) {
	function add_menu_active_class( $classes = array(), $menu_item = false ) {
		if ( in_array( 'current-menu-parent', $classes ) ) {
			$classes[] = 'active';
		}

		return $classes;
	}
}

//********************************************
//	Social Icons
//***********************************************************
if( ! function_exists('automotive_social_icons') ) {
  function automotive_social_icons($classes = '', $echo = true){
    if(!$echo){
      ob_start();
    } ?>
    <div<?php echo (!empty($classes) ? ' class="' . ($classes) . '"' : ''); ?> itemscope itemtype="http://schema.org/Organization">
      <link itemprop="url" href="<?php echo home_url(); ?>">
        <ul class="social clearfix">
            <?php
						$social_network_links = automotive_theme_get_option('social_network_links', array());

            if(!empty($social_network_links['enabled'])){
                unset($social_network_links['enabled']['placebo']);

                foreach($social_network_links['enabled'] as $index => $social){
									$link = automotive_theme_get_option(strtolower($social) . '_url', '');

                  echo '<li><a itemprop="sameAs" class="' . strtolower($social) . '" href="' . esc_url($link) . '" target="_blank"></a></li>';
                }
            } ?>
        </ul>
        <div class="clearfix"></div>
    </div>
    <?php

    if(!$echo){
      return ob_get_clean();
    }
  }
}

if ( ! function_exists( "automotive_redux_custom_css_styling" ) ) {
	function automotive_redux_custom_css_styling() {
		// wp_register_style( 'redux-font-awesome', get_template_directory_uri() . '/css/all.min.css', array() );
		// wp_enqueue_style( 'redux-font-awesome' );
    //
		// wp_register_style( 'redux-font-awesome-shim', get_template_directory_uri() . '/css/v4-shims.min.css', array() );
		// wp_enqueue_style( 'redux-font-awesome-shim' );

		wp_register_style( 'theme-admin', get_template_directory_uri() . '/css/admin.css', array() );
		wp_enqueue_style( 'theme-admin' );
	}
}
add_action( 'redux/page/automotive_wp/enqueue', 'automotive_redux_custom_css_styling' );
add_action( 'admin_enqueue_scripts', 'automotive_redux_custom_css_styling' );

function automotive_theme_global_styling(){
	$theme_details = wp_get_theme();
	$theme_version = $theme_details->get( 'Version' );
	$css_dir       = get_template_directory_uri() . "/css/";
	$js_dir        = get_template_directory_uri() . "/js/";

	wp_enqueue_style('automotive-shared', $css_dir . 'shared-styling.css', array(), $theme_version, 'all');

	wp_enqueue_script( "automotive-shared", $js_dir . 'shared-scripts.js', array(), $theme_version, 'all' );

	if(is_child_theme()){
		wp_enqueue_style( "automotive-child", get_stylesheet_directory_uri() . '/style.css', array(), $theme_version, 'all' );
	}
}
add_action('wp_enqueue_scripts', 'automotive_theme_global_styling');

//********************************************
//	Sidebar classes
//***********************************************************
if ( ! function_exists( "content_classes" ) ) {
	function content_classes( $sidebar, $small_sidebar = false, $small_sidebar_width = 2 ) {
		$content_num = ( ! $small_sidebar ? 9 : ( 12 - $small_sidebar_width ) );
		$sidebar_num = ( ! $small_sidebar ? 3 : $small_sidebar_width );

		// determine classes
		if ( $sidebar == "left" ) {
			$return = array(
				"col-lg-" . $content_num . (!is_rtl() ? " push-lg-" . $sidebar_num : "") . " col-md-12 col-sm-12 col-xs-12",
				"col-lg-" . $sidebar_num . (!is_rtl() ? " order-first pull-lg-" . $content_num : "") . " col-md-12 col-sm-12 col-xs-12 sidebar_left"
			);
		} else if ( $sidebar == "right" ) {
			$return = array(
				"col-xl-" . $content_num . (is_rtl() ? " pull-lg-" . $sidebar_num : "") . " col-lg-" . $content_num . " col-md-12 col-sm-12 col-xs-12",
				"col-xl-" . $sidebar_num . (is_rtl() ? " push-lg-" . $content_num : "") . " col-lg-" . $sidebar_num . " col-md-12 col-sm-12 col-xs-12 sidebar_right"
			);
		} else {
			$return = array( "col-lg-12 col-md-12 col-sm-12 col-xs-12" );
		}

		// 0 = content class
		// 1 = sidebar class

		return $return;
	}
}

//********************************************
//  Woocommerce stuffz
//***********************************************************
function woocommerce_remove_breadcrumb() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}

add_action( 'woocommerce_before_main_content', 'woocommerce_remove_breadcrumb' );

function woocommerce_custom_breadcrumb() {
	woocommerce_breadcrumb();
}

add_action( 'woo_custom_breadcrumb', 'woocommerce_custom_breadcrumb' );

function woo_hide_page_title() {
	return false;
}

add_filter( 'woocommerce_show_page_title', 'woo_hide_page_title' );

function auto_loop_columns() {
	global $awp_options;

	return ( isset( $awp_options['woocommerce_fullwidth'] ) && ! empty( $awp_options['woocommerce_fullwidth'] ) ? 5 : 4 );
}

add_filter( 'loop_shop_columns', 'auto_loop_columns', 999 );

function auto_loop_shop_per_page( $cols ) {
  return ( isset( $awp_options['woocommerce_fullwidth'] ) && ! empty( $awp_options['woocommerce_fullwidth'] ) ? 15 : 16 );
}

add_filter( 'loop_shop_per_page', 'auto_loop_shop_per_page' );

function auto_woocommerce_price_html( $price, $product ) {
	return preg_replace( '@(<del>.*?</del>).*?(<ins>.*?</ins>)@misx', '$2 $1', $price );
}

if ( ! automotive_theme_get_option('woo_price_switch', false) ) {
	add_filter( 'woocommerce_get_price_html', 'auto_woocommerce_price_html', 100, 2 );
}

//woocommerce_loop_add_to_cart_args
function auto_add_to_cart_class( $args ) {

	$args['class'] .= " add_to_cart_";

	return $args;
}

add_filter( "woocommerce_loop_add_to_cart_args", "auto_add_to_cart_class" );

function auto_add_coupon_icon_woo( $message ) {
	return "<i class='fa fa-tag'></i> " . $message;
}

add_filter( "woocommerce_checkout_coupon_message", "auto_add_coupon_icon_woo" );

function auto_add_login_icon_woo( $message ) {
	return "<i class='fa fa-user'></i> " . $message;
}

add_filter( "woocommerce_checkout_login_message", "auto_add_login_icon_woo" );

function auto_wc_add_to_cart_message_html( $message, $product_id ) {
	reset( $product_id );
	$a_product_id = key( $product_id );

	$product_id_img = wp_get_attachment_image_src( get_post_thumbnail_id( $a_product_id ), 'thumbnail' );

	$message = "";
	$message .= "<img src='" . $product_id_img[0] . "'>";
	$message .= "<div><div class='product_title'>" . get_the_title( $a_product_id ) . "</div><div class='added_text'>" . esc_html__( "Has been added to your cart!", "automotive" ) . "</div></div>";
	$message .= "<a href=\"" . esc_url( wc_get_page_permalink( 'cart' ) ) . "\" class=\"button wc-forward\">" . esc_html__( "View cart", "automotive" ) . "</a>";

	return $message;
}

add_filter( "wc_add_to_cart_message_html", "auto_wc_add_to_cart_message_html", 10, 2 );


function auto_adjust_related_products_num( $args ) {
	$args['posts_per_page'] = 6;

	return $args;
}

add_filter( "woocommerce_output_related_products_args", "auto_adjust_related_products_num" );


if ( ! function_exists( "auto_is_page_edit" ) ) {
	function auto_is_edit_page( $new_edit = null ) {
		global $pagenow;

		if ( ! is_admin() ) {
			return false;
		}

		if ( $new_edit == "edit" ) {
			return in_array( $pagenow, array( 'post.php', ) );
		} elseif ( $new_edit == "new" ) //check for new post page
		{
			return in_array( $pagenow, array( 'post-new.php' ) );
		} else {
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}
}

if ( ! function_exists( 'auto_loop_4_columns' ) ) {
	function auto_loop_4_columns() {
		return 4;
	}
}

function auto_change_woo_pagination_arrows( $args ) {
	$args['prev_text'] = "<i class=\"fa fa-angle-left\"></i>";
	$args['next_text'] = "<i class=\"fa fa-angle-right\"></i>";

	return $args;
}


add_filter( "woocommerce_pagination_args", "auto_change_woo_pagination_arrows" );

function automotive_cart_update_fragment( $fragments ) {
	$fragments['div.woocommerce-menu-basket'] = auto_woocommerce_menu_basket() . "<script>if(typeof jQuery.fn.mCustomScrollbar !== 'undefined'){ jQuery(\".woocommerce-menu-basket ul\").mCustomScrollbar('destroy'); jQuery(\".woocommerce-menu-basket ul\").mCustomScrollbar({
            scrollInertia: 0,
            mouseWheelPixels: 500,
            scrollEasing: 'linear'
        }); }</script>";

	$cart_count = WC()->cart->get_cart_contents_count();

	$fragments['span.woocommerce-total-cart-count'] = "<span class='woocommerce-total-cart-count" . ( $cart_count == "0" ? " empty" : "" ) . "'>" . $cart_count . "</span>";

	return $fragments;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'automotive_cart_update_fragment', 10, 1 );


function auto_change_woo_shop_image_size( $size ) {
	return "shop_catalog";
}

add_filter( "single_product_archive_thumbnail_size", "auto_change_woo_shop_image_size" );

//********************************************
//  Visual Composer Templates
//***********************************************************
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'automotive/index.php' ) ) {
	add_filter( 'vc_load_default_templates', 'vc_contact_template' );
	add_filter( 'vc_load_default_templates', 'vc_about_template' );
	add_filter( 'vc_load_default_templates', 'vc_faq_template' );
	add_filter( 'vc_load_default_templates', 'vc_our_team' );
	add_filter( 'vc_load_default_templates', 'vc_services_template' );
	add_filter( 'vc_load_default_templates', 'vc_pricing_tables' );
	add_filter( 'vc_load_default_templates', 'vc_homepage_template' );


	function vc_contact_template( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] Contact Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row][vc_column width="1/1"][vc_column_text]
    <h3>FIND US ON THE MAP</h3>
    [/vc_column_text][auto_google_map longitude="-79.38" latitude="43.65" zoom="7" height="390"][/vc_column][/vc_row][vc_row][vc_column width="1/2"][vc_column_text]
    <h3>CONTACT INFORMATION</h3>
    [/vc_column_text][auto_contact_information company="Company Name" address="1234 Street Name
    City Name, AB 12345
    United States" phone="1-800-123-4567" email="sales@company.com" web="www.company.com"][vc_column_text]
    <h3>BUSINESS HOURS</h3>
    [/vc_column_text][hours_table title="Sales Department" mon="8:00am - 5:00pm" tue="8:00am - 9:00pm" wed="8:00am - 5:00pm" thu="8:00am - 9:00pm" fri="8:00am - 6:00pm" sat="9:00am - 5:00pm" sun="Closed"][hours_table title="Service Department" mon="8:00am - 5:00pm" tue="8:00am - 9:00pm" wed="8:00am - 5:00pm" thu="8:00am - 9:00pm" fri="8:00am - 6:00pm" sat="9:00am - 5:00pm" sun="Closed"][hours_table title="Parts Department" mon="8:00am - 5:00pm" tue="8:00am - 9:00pm" wed="8:00am - 5:00pm" thu="8:00am - 9:00pm" fri="8:00am - 6:00pm" sat="9:00am - 5:00pm" sun="Closed"][/vc_column][vc_column width="1/2"][vc_column_text]
    <h3>CONTACT FORM</h3>
    [/vc_column_text][auto_contact_form name="Name (Required)" email="Email (Required)" message="Message" button="Send Message"][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_about_template( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] About Us Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row css=".vc_custom_1410354226977{margin-bottom: 60px !important;}"][vc_column width="8/12"][vc_column_text]
    <h3>OUR MISSION IS SIMPLE</h3>
    [/vc_column_text][vc_column_text][dropcaps]C[/dropcaps]obem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.

    <img class="alignleft wp-image-1370 size-full" src="http://dev.themesuite.com/automotive/wp-content/uploads/2014/09/img-display.jpg" alt="img-display" width="370" height="192" />Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, eta rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis. Lorem ipsum dolor sit amet,

    Consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Et Donec pretium quis sem quam felis, ultricies nec, pellentesque eu, aenean massa et a pretium quis, sem. Cobem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.[/vc_column_text][/vc_column][vc_column width="4/12"][vc_column_text]
    <h3>WHAT WE SPECIALIZE IN</h3>
    [/vc_column_text][progress_bar color="#c7081b" filled="100%"]WordPress[/progress_bar][progress_bar color="#c7081b" filled="90%"]HTML / CSS[/progress_bar][progress_bar color="#c7081b" filled="80%"]PHP[/progress_bar][progress_bar color="#c7081b" filled="70%"]Javascript[/progress_bar][progress_bar color="#c7081b" filled="60%"]Photoshop[/progress_bar][progress_bar color="#c7081b" filled="50%"]MySQL[/progress_bar][progress_bar color="#c7081b" filled="40%"]jQuery[/progress_bar][progress_bar color="#c7081b" filled="30%"]Joomla[/progress_bar][progress_bar color="#c7081b" filled="20%"]XML[/progress_bar][/vc_column][/vc_row][vc_row css=".vc_custom_1410354195826{margin-bottom: 60px !important;}"][vc_column width="4/12"][vc_column_text]
    <h3>WHY CHOOSE US?</h3>
    [/vc_column_text][list style="arrows"][list_item]Integrated inventory management system[/list_item][list_item]Fully responsive and ready for all mobile devices[/list_item][list_item]Simple to use and extremely easy to customize[/list_item][list_item]Search engine optimized out of the box (SEO ready)[/list_item][list_item]Includes a license for Revolution Slider ($15 value)[/list_item][list_item]Tons of shortcodes for easy and functional add-ons[/list_item][list_item]Completely backed by our dedicated support staff[/list_item][list_item]Fully featured Option Panel for quick &amp; easy setup[/list_item][/list][/vc_column][vc_column width="3/12"][vc_column_text]
    <h3>TESTIMONIALS</h3>
    [/vc_column_text][testimonials slide="horizontal" speed="500"][testimonial_quote name="Theodore Isaac Rubin"]Happiness does not come from doing easy work but from the afterglow of satisfaction that comes after the achievement of a difficult task that demanded our best.[/testimonial_quote][testimonial_quote name="Theodore Isaac Rubin"]Happiness does not come from doing easy work but from the afterglow of satisfaction that comes after the achievement of a difficult task that demanded our best.[/testimonial_quote][/testimonials][/vc_column][vc_column width="5/12"][vc_column_text]
    <h3>LATEST AUTOMOTIVE NEWS</h3>
    [/vc_column_text][recent_posts_scroller number="2" speed="500" foo="3"][/vc_column][/vc_row][vc_row css=".vc_custom_1410354200578{margin-bottom: 60px !important;}"][vc_column width="1/1"][vc_column_text]
    <h3>SOME OF OUR FEATURED BRANDS</h3>
    [/vc_column_text][featured_brands][brand_logo img="1425" hoverimg="1424"][/brand_logo][brand_logo img="1421" hoverimg="1420"][/brand_logo][brand_logo img="1427" hoverimg="1426"][/brand_logo][brand_logo img="1423" hoverimg="1422"][/brand_logo][brand_logo img="1431" hoverimg="1430"][/brand_logo][brand_logo img="1429" hoverimg="1428"][/brand_logo][brand_logo img="1425" hoverimg="1424"][/brand_logo][brand_logo img="1421" hoverimg="1420"][/brand_logo][brand_logo img="1427" hoverimg="1426"][/brand_logo][brand_logo img="1423" hoverimg="1422"][/brand_logo][brand_logo img="1431" hoverimg="1430"][/brand_logo][brand_logo img="1429" hoverimg="1428"][/brand_logo][/featured_brands][/vc_column][/vc_row][vc_row el_class="fullwidth_element bottom_element"][vc_column width="1/1"][auto_google_map longitude="-79.38" latitude="43.65" zoom="8" height="390" map_style="JTVCJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJsYW5kc2NhcGUlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJ0cmFuc2l0JTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIycG9pJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIyd2F0ZXIlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJyb2FkJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMuaWNvbiUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmh1ZSUyMiUzQSUyMiUyM0YwRjBGMCUyMiU3RCUyQyU3QiUyMnNhdHVyYXRpb24lMjIlM0EtMTAwJTdEJTJDJTdCJTIyZ2FtbWElMjIlM0EyLjE1JTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMTIlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscy50ZXh0LmZpbGwlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9uJTIyJTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMjQlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmdlb21ldHJ5JTIyJTJDJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmxpZ2h0bmVzcyUyMiUzQTU3JTdEJTVEJTdEJTVE"][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_faq_template( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] FAQ Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row][vc_column width="1/1"][faq categories="Electrical,Engine,Mechanical,Navigation,Sunroof,Stereo,Wiring" sort_text="Sort FAQ by:"][toggle title="Nam sollicitudin neque eu nibh pharetra mollis mauris in nisi rhoncus?" categories="Electrical,Navigational,Wiring"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Onvallis odio nulla vulputate orci ut libero suscipit condimentum nunc nibh?" categories="Engine,Sunroof,Wiring"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Porta auctor adipiscing massa maecenas sem mi, vestibulum id lectus non?" categories="Electrical,Mechanical,Wiring"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Mauris in nisi elit maecenas at metus rhoncus, facilisis tellus at, quis felis pretium orci?" categories="Mechanical,Sunroof,Navigational"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Donec adipiscing tincidunt rutrum iaculis sapien nec porta ment yehu?" categories="Navigational,Stereo,Sunroof"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa maecenas sem mi?" categories="Engine,Navigational,Stereo"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Duis facilisis dapibus enim, ac venenatis nibh mattis in cras eu condimentum lacus?" categories="Engine,Navigational,Navigational"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Quisque posuere tincidunt convallis ut viverra neque non diam tempor, id tinciunt mauris cursus?" categories="Electrical,Sunroof,Wiring"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][toggle title="Suscipit mattis viverra cras sit amet odio sit amet dui aliquam tempus a ultrices felis?" categories="Mechanical,Engine,Stereo"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi nibh libero,
    consequat sit amet nisl vitae, suscipit gravida mi. Nam auctor viverra
    sodales. Quisque posuere tincidunt convallis. Ut viverra neque non diam
    tempor, id tincidunt mauris cursus. Donec suscipit mattis viverra. Cras sit
    amet odio sit amet dui aliquam tempus a ultrices felis. Proin sed imperdiet
    ipsum, ultrices posuere leo.

    Duis facilisis dapibus enim, ac venenatis nibh mattis in. Cras eu
    condimentum lacus, ac ultricies leo. Nunc sodales ipsum a suscipit.

    Mauris tincidunt rutrum auctor. <a href="#">Vivamus a nunc ac augue scelerisque dapibus</a> ut sed augue. Pellentesque fermentum orci in
    velit pharetra, non lobortis sapien suscipit. Aenean sem nulla, dignissim et bibendum et, consequat in nibh.

    Nam sollicitudin neque eu nibh pharetra mollis. Mauris in nisi elit. Maecenas at metus rhoncus, facilisis tellus at, pretium orci.
    Vivamus consectetur sem eget neque dignissim, sit amet sodales urna mattis. Vivamus ut semper dolor. Suspendisse tempus,
    dolor vel eleifend vestibulum, nulla eros elementum ligula, ac bibendum mi ipsum quis felis. Donec adipiscing iaculis sapien
    nec porta. Aliquam tellus leo, posuere ut magna porta, auctor adipiscing massa. Maecenas sem mi, vestibulum id lectus non,
    placerat rhoncus dui.[/toggle][/faq][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_our_team( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] Our Team Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row css=".vc_custom_1410358708453{margin-bottom: 10px !important;}"][vc_column width="1/1"][vc_column_text]
<h3>MEET THE MANAGEMENT</h3>
[/vc_column_text][/vc_column][/vc_row][vc_row css=".vc_custom_1410359107639{margin-bottom: 70px !important;}"][vc_column width="1/3"][person name="William Dean" position="Chief Executive Officer / CEO" phone="1-800-123-4567 - Extension 114" cell_phone="1-902-361-7714" email="william@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" flickr="#" google="#" img="109" hoverimg="108"]Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor aenean massa. Cum sociis numquasa mode tempora posuere feugiat.[/person][/vc_column][vc_column width="1/3"][person name="Leah Jennings" position="Chief Financial Officer / CEO" phone="1-800-123-4567 - Extension 107" cell_phone="1-902-342-0864" email="leah@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" img="111" hoverimg="110"]Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor aenean massa. Cum sociis numquasa mode tempora posuere feugiat.[/person][/vc_column][vc_column width="1/3"][person name="Zachary Hale" position="Lead Sales Manager" phone="1-800-123-4567 - Extension 119" cell_phone="1-902-832-3702" email="zachary@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" google="#" img="113" hoverimg="112"]Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor aenean massa. Cum sociis numquasa mode tempora posuere feugiat.[/person][/vc_column][/vc_row][vc_row css=".vc_custom_1410358730115{margin-bottom: 10px !important;}"][vc_column width="1/1"][vc_column_text]
<h3>MEET OUR SALES TEAM</h3>
[/vc_column_text][/vc_column][/vc_row][vc_row css=".vc_custom_1410359126831{margin-bottom: 70px !important;}"][vc_column width="1/4"][person name="Luca Sanderson" position="Sales Representative" phone="1-800-123-4567 - Extension 105" cell_phone="1-902-544-4415" email="luca@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" google="#" img="115" hoverimg="114"]Lorem ipsum dolor sit amet, paleotousia consectetuer adipiscing elit. Aenean com.[/person][/vc_column][vc_column width="1/4"][person name="Abby Myers" position="Sales Representative" phone="1-800-123-4567 - Extension 123" cell_phone="1-902-361-7267" email="abby@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" google="#" flickr="#" img="117" hoverimg="116"]Lorem ipsum dolor sit amet, paleotousia consectetuer adipiscing elit. Aenean com.[/person][/vc_column][vc_column width="1/4"][person name="Connor Wyatt" position="Sales Representative" phone="1-800-123-4567 - Extension 111" cell_phone="1-902-544-4415" email="connor@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" flickr="#" img="119" hoverimg="118"]Lorem ipsum dolor sit amet, paleotousia consectetuer adipiscing elit. Aenean com.[/person][/vc_column][vc_column width="1/4"][person name="Sarah Thomas" position="Sales Representative" phone="1-800-123-4567 - Extension 108" cell_phone="1-902-544-4415" email="sarah@automotivetemplate.com" facebook="#" twitter="#" linkedin="#" img="121" hoverimg="120"]Lorem ipsum dolor sit amet, paleotousia consectetuer adipiscing elit. Aenean com.[/person][/vc_column][/vc_row][vc_row css=".vc_custom_1410447322744{margin-bottom: 60px !important;}"][vc_column width="1/1"][vc_column_text]
<h4 style="color: #c7081b; font-size: 24px;"><span style="font-weight: 800;">SEARCH</span> OUR INVENTORY</h4>
[/vc_column_text][search_inventory_box][/vc_column][/vc_row][vc_row el_class="fullwidth_element bottom_element"][vc_column width="1/1"][auto_google_map height="390" map_style="JTVCJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJsYW5kc2NhcGUlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJ0cmFuc2l0JTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIycG9pJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIyd2F0ZXIlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJyb2FkJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMuaWNvbiUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmh1ZSUyMiUzQSUyMiUyM0YwRjBGMCUyMiU3RCUyQyU3QiUyMnNhdHVyYXRpb24lMjIlM0EtMTAwJTdEJTJDJTdCJTIyZ2FtbWElMjIlM0EyLjE1JTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMTIlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscy50ZXh0LmZpbGwlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9uJTIyJTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMjQlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmdlb21ldHJ5JTIyJTJDJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmxpZ2h0bmVzcyUyMiUzQTU3JTdEJTVEJTdEJTVE"][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_pricing_tables( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] Pricing Tables Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row el_class="margin-bottom-none"][vc_column width="1/1"][vc_column_text]
<div class="pricing_dept margin-bottom-50">
<h2 class="margin-bottom-25"><span style="color: #2d2d2d;">Choose the pricing option that best suits your business</span></h2>
Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin condimentum felis ut ultrices congue. Quisque in lacus condimentum, fringilla nisi commodo, faucibus velit. Integer fermentum mauris adipiscing faucibus tristique. Praesent iaculis sed tellus quis porta. Nulla porta tincidunt libero. Ut nec purus ut lectus convallis pellentesque ac non enim. Etiam suscipit eleifend tincidunt. Praesent volutpat, tortor ac molestie imperdiet, nisi quam imperdiet elit, id dapibus lacus felis sed massa. Cras ultrices enim in sagittis posuere. Vestibulum ac ipsum vitae lectus pretium vestibulum ac rutrum felis. Donec consequat lacus eu mi porta ornare. Duis eget velit ac felis sollicitudin sagittis.

</div>
<div class="pricing_wrapper">
<h3 class="margin-top-20 margin-bottom-30"><span style="color: #2d2d2d;">3 Column Pricing Layout</span></h3>
</div>
[/vc_column_text][/vc_column][/vc_row][vc_row][vc_column width="1/3"][pricing_table title="Standard" price="299.99" often="mo" button="Order Now"][pricing_option]Manual Transmission[/pricing_option][pricing_option]4 Cylinder Engine[/pricing_option][pricing_option]60 MPG[/pricing_option][pricing_option]6 Seats[/pricing_option][pricing_option]3 Year Warranty[/pricing_option][/pricing_table][/vc_column][vc_column width="1/3"][pricing_table title="Professional" price="399.99" often="mo" button="Order Now"][pricing_option]Manual Transmission[/pricing_option][pricing_option]6 Cylinder Engine[/pricing_option][pricing_option]45 MPG[/pricing_option][pricing_option]5 Seats[/pricing_option][pricing_option]4 Year Warranty[/pricing_option][/pricing_table][/vc_column][vc_column width="1/3"][pricing_table title="Premium" price="499.99" often="mo" button="Order Now"][pricing_option]Automatic Transmission[/pricing_option][pricing_option]8 Cylinder Engine[/pricing_option][pricing_option]30 MPG[/pricing_option][pricing_option]4 Seats[/pricing_option][pricing_option]5 Year Warranty[/pricing_option][/pricing_table][/vc_column][/vc_row][vc_row][vc_column width="1/1"][vc_column_text]
<div class="pricing_wrapper">
<h3 class="margin-bottom-none margin-top-30"><span style="color: #2d2d2d;">4 Column Pricing Layout</span></h3>
</div>
[/vc_column_text][/vc_column][/vc_row][vc_row][vc_column width="1/4"][pricing_table title="Standard" price="299.99" often="mo" button="Order Now"][pricing_option]Manual Transmission[/pricing_option][pricing_option]4 Cylinder Engine[/pricing_option][pricing_option]60 MPG[/pricing_option][pricing_option]6 Seats[/pricing_option][pricing_option]3 Year Warranty[/pricing_option][/pricing_table][/vc_column][vc_column width="1/4"][pricing_table title="Professional" price="399.99" often="mo" button="Order Now"][pricing_option]Manual Transmission[/pricing_option][pricing_option]6 Cylinder Engine[/pricing_option][pricing_option]45 MPG[/pricing_option][pricing_option]5 Seats[/pricing_option][pricing_option]4 Year Warranty[/pricing_option][/pricing_table][/vc_column][vc_column width="1/4"][pricing_table title="Premium" price="499.99" often="mo" button="Order Now"][pricing_option]Automatic Transmission[/pricing_option][pricing_option]8 Cylinder Engine[/pricing_option][pricing_option]30 MPG[/pricing_option][pricing_option]4 Seats[/pricing_option][pricing_option]5 Year Warranty[/pricing_option][/pricing_table][/vc_column][vc_column width="1/4"][pricing_table title="Platinum" price="599.99" often="mo" button="Order Now"][pricing_option]Automatic Transmission[/pricing_option][pricing_option]12 Cylinder Engine[/pricing_option][pricing_option]15 MPG[/pricing_option][pricing_option]2 Seats[/pricing_option][pricing_option]7 Year Warranty[/pricing_option][/pricing_table][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_services_template( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] Service Page', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row css=".vc_custom_1410361334750{margin-bottom: 70px !important;}"][vc_column width="2/3"][vc_column_text]
    <h3>WHAT CAN WE DO FOR YOU?</h3>
    [/vc_column_text][vc_column_text][dropcaps]R[/dropcaps]obem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.

    Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis.[/vc_column_text][/vc_column][vc_column width="1/3"][vc_column_text]
    <h3>THINGS TO CONSIDER</h3>
    [/vc_column_text][list style="checkboxes"][list_item]Fully responsive and ready for all mobile devices[/list_item][list_item]Integrated inventory management system[/list_item][list_item]Simple option panel and very easy to customize[/list_item][list_item]Search engine optimization (SEO) is 100% built-in[/list_item][list_item]Revolution Slider is included for product marketing[/list_item][list_item]Tons of shortcodes for quick and easy add-ons[/list_item][list_item]Fully backed by our dedicated support team[/list_item][/list][/vc_column][/vc_row][vc_row][vc_column width="1/1"][vc_column_text]
    <h2 class="margin-top-none" style="letter-spacing: -1.5px;"><span style="color: #c7081b;">Highlight Your <strong>Featured Services</strong></span></h2>
    [/vc_column_text][/vc_column][/vc_row][vc_row][vc_column width="1/4"][featured_panel title="Mobile Enhanced" icon="1463" hover_icon="1462"]Sed ut perspiciatis unde om natus error sit volup atem aperiam, eaque ipsa quae[/featured_panel][/vc_column][vc_column width="1/4"][featured_panel title="Platform Tested" icon="1465" hover_icon="1464"]Sed ut perspiciatis unde om natus error sit volup atem aperiam, eaque ipsa quae[/featured_panel][/vc_column][vc_column width="1/4"][featured_panel title="Social Ready" icon="1467" hover_icon="1466"]Sed ut perspiciatis unde om natus error sit volup atem aperiam, eaque ipsa quae[/featured_panel][/vc_column][vc_column width="1/4"][featured_panel title="Video Integration" icon="1469" hover_icon="1468"]Sed ut perspiciatis unde om natus error sit volup atem aperiam, eaque ipsa quae[/featured_panel][/vc_column][/vc_row][vc_row el_class="fullwidth_element" css=".vc_custom_1410362510858{margin-bottom: 30px !important;}"][vc_column width="1/1"][parallax_section title="Dealership Statistics" velocity="-.3" offset="0" image="1475" overlay_color="rgba(255,255,255,0.65)" text_color="#2d2d2d"]
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 margin-vertical-60 xs-margin-vertical-20"><i class="fa fa-car"></i><span class="animate_number margin-vertical-15"><span class="number">2,000</span>
    </span>Cars Sold</div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 margin-vertical-60 xs-margin-vertical-20"><i class="fa fa-shopping-cart"></i><span class="animate_number margin-vertical-15">$<span class="number">750,000</span>
    </span>Amount Sold</div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 margin-vertical-60 xs-margin-vertical-20"><i class="fa fa-users"></i><span class="animate_number margin-vertical-15"><span class="number">100</span>%
    </span>Customer Satisfaction</div>
    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 margin-vertical-60 xs-margin-vertical-20"><i class="fa fa-tint"></i><span class="animate_number margin-vertical-15"><span class="number">3,600</span>
    </span>Oil Changes</div>
    [/parallax_section][/vc_column][/vc_row][vc_row css=".vc_custom_1410362518410{margin-bottom: 20px !important;}"][vc_column width="1/1"][vc_column_text]
    <h2 class="margin-top-none" style="letter-spacing: -1.5px;"><span style="color: #c7081b;">Easily Layout Your <strong>Detailed Services</strong></span></h2>
    [/vc_column_text][/vc_column][/vc_row][vc_row][vc_column width="1/3"][detailed_panel title="Highly Customizable" icon="fa fa-wrench"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][vc_column width="1/3"][detailed_panel title="Award Winning" icon="fa fa-trophy"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][vc_column width="1/3"][detailed_panel title="Music To Your Ears" icon="fa fa-music"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][/vc_row][vc_row css=".vc_custom_1410366970608{margin-bottom: 70px !important;}"][vc_column width="1/3"][detailed_panel title="Easy To Work With" icon="fa fa-coffee"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][vc_column width="1/3"][detailed_panel title="Ultra Responsive" icon="fa fa-truck"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][vc_column width="1/3"][detailed_panel title="Flexible Framework" icon="fa fa-cog"]Sociis natoque penatibus et magnis dis parturient etah montes, nascetur ridiculus mus. Donec quam felis, A ultricies nec, pellentesque eu, pretium quis, sem. Cum sociis natoque penatibus et magnis dis parturient nas.[/detailed_panel][/vc_column][/vc_row][vc_row][vc_column width="1/1"][vc_column_text]
    <h3>SOME OF OUR FEATURED BRANDS</h3>
    [/vc_column_text][featured_brands][brand_logo img="1425" hoverimg="1424"][/brand_logo][brand_logo img="1421" hoverimg="1420"][/brand_logo][brand_logo img="1427" hoverimg="1426"][/brand_logo][brand_logo img="1423" hoverimg="1422"][/brand_logo][brand_logo img="1431" hoverimg="1430"][/brand_logo][brand_logo img="1429" hoverimg="1428"][/brand_logo][brand_logo img="1425" hoverimg="1424"][/brand_logo][brand_logo img="1421" hoverimg="1420"][/brand_logo][brand_logo img="1427" hoverimg="1426"][/brand_logo][brand_logo img="1423" hoverimg="1422"][/brand_logo][brand_logo img="1431" hoverimg="1430"][/brand_logo][brand_logo img="1429" hoverimg="1428"][/brand_logo][/featured_brands][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}

	function vc_homepage_template( $data ) {
		$template            = array();
		$template['name']    = __( '[Automotive] Homepage', 'automotive' );
		$template['content'] = <<<CONTENT
            [vc_row el_class="padding-bottom-40 margin-bottom-none"][vc_column width="1/3"][flipping_card image="316" larger_img="315" title="Race Ready" link="url:http%3A%2F%2Fdev.themesuite.com%2Fautomotive%2F%3Fpage_id%3D36|title:About%20Us|"][vc_column_text css=".vc_custom_1410531577112{margin-top: 20px !important;}"]
<h3 class="margin-bottom-10">FACTORY READY FOR TRACK DAY</h3>
<p class="margin-bottom-none">Sea veniam lucilius neglegentur ad, an per sumo volum voluptatibus. Qui cu everti repudiare. Eam ut cibo nobis aperiam, elit qualisque at cum. Possit antiopam id est. Illud delicata ea mel, sed novum mucius id. Nullam qua.</p>
[/vc_column_text][/vc_column][vc_column width="1/3"][flipping_card image="788" larger_img="787" title="Family Oriented" link="url:http%3A%2F%2Fdev.themesuite.com%2Fautomotive%2F%3Fpage_id%3D36|title:About%20Us|"][vc_column_text css=".vc_custom_1410531618564{margin-top: 20px !important;}"]
<h3 class="margin-bottom-10">A SPORT UTILITY FOR THE FAMILY</h3>
<p class="margin-bottom-none">Cum ut tractatos imperdiet, no tamquam facilisi qui. Eum tibique consectetuer in, an legimus referrentur vis, vocent deseruisse ex mel. Sed te idque graecis. Vel ne libris dolores, in mel graece dolorum.</p>
[/vc_column_text][/vc_column][vc_column width="1/3"][flipping_card image="790" larger_img="789" title="Race Ready" link="url:http%3A%2F%2Fdev.themesuite.com%2Fautomotive%2F%3Fpage_id%3D36|title:About%20Us|"][vc_column_text css=".vc_custom_1410531639450{margin-top: 20px !important;}"]
<h3 class="margin-bottom-10">MAKE AN EXECUTIVE STATEMENT</h3>
<p class="margin-bottom-none">Te inermis cotidieque cum, sed ea utroque atomorum sadipscing. Qui id oratio everti scaevola, vim ea augue ponderum vituperatoribus, quo adhuc abhorreant omittantur ad. No his fierent perpetua consequat, et nis.</p>
[/vc_column_text][/vc_column][/vc_row][vc_row el_class="fullwidth_element margin-top-30 padding-bottom-40 margin-bottom-none"][vc_column width="1/1"][parallax_section velocity="-.3" offset="0" image="99" overlay_color="rgba(240,240,240,0.95)" text_color="#2d2d2d"][vc_row_inner el_class="margin-bottom-60"][vc_column_inner width="1/4"][featured_icon_box title="Results Driven" icon="fa fa-bar-chart-o"]Sed ut perspiciatis unde om nis natus error sit volup atem accusant dolorem que laudantium. Totam aperiam, eaque ipsa quae ai.[/featured_icon_box][/vc_column_inner][vc_column_inner width="1/4"][featured_icon_box title="Proven Technology" icon="fa fa-road"]Sed ut perspiciatis unde om nis natus error sit volup atem accusant dolorem que laudantium. Totam aperiam, eaque ipsa quae ai.[/featured_icon_box][/vc_column_inner][vc_column_inner width="1/4"][featured_icon_box title="Winning Culture" icon="fa fa-flag-checkered"]Sed ut perspiciatis unde om nis natus error sit volup atem accusant dolorem que laudantium. Totam aperiam, eaque ipsa quae ai.[/featured_icon_box][/vc_column_inner][vc_column_inner width="1/4"][featured_icon_box title="Top Performance" icon="fa fa-dashboard"]Sed ut perspiciatis unde om nis natus error sit volup atem accusant dolorem que laudantium. Totam aperiam, eaque ipsa quae ai.[/featured_icon_box][/vc_column_inner][/vc_row_inner][/parallax_section][/vc_column][/vc_row][vc_row el_class="margin-top-30 padding-bottom-40 margin-bottom-none"][vc_column width="1/2"][vc_column_text]
<h4 class="margin-top-none">[bolded]WELCOME[/bolded] TO YOUR NEW WEBSITE</h4>
[/vc_column_text][vc_column_text el_class="padding-bottom-40"]Lorem ipsum dolor sit amet, falli tollit cetero te eos. Ea ullum liber aperiri mi, impetus ate philosophia ad duo, quem regione ne ius. Vis quis lobortis dissentias ex, in du aft philosophia, malis necessitatibus no mei. Volumus sensibus qui ex, eum duis doming ad. Modo liberavisse eu mel, no viris prompta sit. Pro labore sadipscing et. Ne peax egat usu te mel <span class="alternate-font">vivendo scriptorem</span>. Pro labore sadipscing et. Ne pertinax egat usu te mel vivendo scriptorem.

Cum ut tractatos imperdiet, no tamquam facilisi qui. Eum tibique onsectetuer in, an referrentur vis, vocent deseruisse ex mel. Sed te <span class="alternate-font">idque graecis</span>. Vel ne libris dolores, mel graece mel vivendo scriptorem dolorum.[/vc_column_text][/vc_column][vc_column width="1/2"][vc_column_text]
<h4 class="margin-top-none">[bolded]SEARCH[/bolded] OUR INVENTORY</h4>
[/vc_column_text][search_inventory_box column_1="Year,Make,Model,Body Style" column_2="Mileage,Transmission,Price,Search" min_max="Year,Mileage,Price" page_id="url:http%3A%2F%2Fdev.themesuite.com%2Fautomotive%2F%3Fpage_id%3D191|title:Wide%20Fullwidth|"][/vc_column][/vc_row][vc_row css=".vc_custom_1410982144071{margin-bottom: 0px !important;}" el_class="margin-top-30 padding-bottom-40 margin-bottom-none"][vc_column width="1/1"][vehicle_scroller title="Recent Vehicles" description="Browse through the vast selection of vehicles that have recently been added to our inventory." sort="Newest"][/vc_column][/vc_row][vc_row el_class="fullwidth_element margin-top-30 padding-bottom-40 margin-bottom-none"][vc_column width="1/1"][auto_google_map height="390" map_style="JTVCJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJsYW5kc2NhcGUlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJ0cmFuc2l0JTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIycG9pJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9mZiUyMiU3RCU1RCU3RCUyQyU3QiUyMmZlYXR1cmVUeXBlJTIyJTNBJTIyd2F0ZXIlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscyUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyZmVhdHVyZVR5cGUlMjIlM0ElMjJyb2FkJTIyJTJDJTIyZWxlbWVudFR5cGUlMjIlM0ElMjJsYWJlbHMuaWNvbiUyMiUyQyUyMnN0eWxlcnMlMjIlM0ElNUIlN0IlMjJ2aXNpYmlsaXR5JTIyJTNBJTIyb2ZmJTIyJTdEJTVEJTdEJTJDJTdCJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmh1ZSUyMiUzQSUyMiUyM0YwRjBGMCUyMiU3RCUyQyU3QiUyMnNhdHVyYXRpb24lMjIlM0EtMTAwJTdEJTJDJTdCJTIyZ2FtbWElMjIlM0EyLjE1JTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMTIlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmxhYmVscy50ZXh0LmZpbGwlMjIlMkMlMjJzdHlsZXJzJTIyJTNBJTVCJTdCJTIydmlzaWJpbGl0eSUyMiUzQSUyMm9uJTIyJTdEJTJDJTdCJTIybGlnaHRuZXNzJTIyJTNBMjQlN0QlNUQlN0QlMkMlN0IlMjJmZWF0dXJlVHlwZSUyMiUzQSUyMnJvYWQlMjIlMkMlMjJlbGVtZW50VHlwZSUyMiUzQSUyMmdlb21ldHJ5JTIyJTJDJTIyc3R5bGVycyUyMiUzQSU1QiU3QiUyMmxpZ2h0bmVzcyUyMiUzQTU3JTdEJTVEJTdEJTVE" longitude="-79.38" latitude="43.65" zoom="12" scrolling="false"][/vc_column][/vc_row][vc_row el_class="margin-top-30 padding-bottom-40 margin-bottom-none"][vc_column width="1/2" offset="vc_col-lg-2 vc_col-md-2 vc_col-xs-12"][icon_title title="Financing." icon="fa fa-tag"][/vc_column][vc_column width="1/2" offset="vc_col-lg-2 vc_col-md-2 vc_col-xs-12"][icon_title title="Warranty." icon="fa fa-cogs"][/vc_column][vc_column width="1/1" offset="vc_col-lg-4 vc_col-md-4 vc_col-xs-12" el_class="text-center"][vc_column_text]
<div class="small-block">
<h4 class="margin-bottom-25 margin-top-none">What are our Hours of Operation?</h4>
</div>
[/vc_column_text][hours_table title="Sales Department" mon="8:00am - 5:00pm" tue="8:00am - 9:00pm" wed="8:00am - 5:00pm" thu="8:00am - 9:00pm" fri="8:00am - 6:00pm" sat="9:00am - 5:00pm" sun="Closed"][hours_table title="Service Department" mon="8:00am - 5:00pm" tue="8:00am - 9:00pm" wed="8:00am - 5:00pm" thu="8:00am - 9:00pm" fri="8:00am - 6:00pm" sat="9:00am - 5:00pm" sun="Closed"][/vc_column][vc_column width="1/2" offset="vc_col-lg-2 vc_col-md-2 vc_col-xs-12"][icon_title title="About Us." icon="fa fa-users"][/vc_column][vc_column width="1/2" offset="vc_col-lg-2 vc_col-md-2 vc_col-xs-12"][icon_title title="Find Us." icon="fa fa-map-marker"][/vc_column][/vc_row][vc_row el_class="fullwidth_element bottom_element margin-top-30"][vc_column width="1/1"][parallax_section velocity="-.3" offset="-300" image="100" overlay_color="rgba(0,0,0,0.65)" text_color="#ffffff"][vc_row_inner][vc_column_inner width="1/4"][animated_numbers icon="fa fa-car" number="2000" alignment="center"][vc_column_text]
<p style="text-align: center;"><span style="color: #ffffff;">Cars Sold</span></p>
[/vc_column_text][/vc_column_inner][vc_column_inner width="1/4"][animated_numbers icon="fa fa-money" number="750000" before_number="$" alignment="center"][vc_column_text]
<p style="text-align: center;"><span style="color: #ffffff;">Amount Sold</span></p>
[/vc_column_text][/vc_column_inner][vc_column_inner width="1/4"][animated_numbers icon="fa fa-users" number="100" after_number="%" alignment="center"][vc_column_text]
<p style="text-align: center;"><span style="color: #ffffff;">Customer Satisfaction</span></p>
[/vc_column_text][/vc_column_inner][vc_column_inner width="1/4"][animated_numbers icon="fa fa-tint" number="3600" alignment="center"][vc_column_text]
<p style="text-align: center;"><span style="color: #ffffff;">Oil Changes</span></p>
[/vc_column_text][/vc_column_inner][/vc_row_inner][/parallax_section][/vc_column][/vc_row]
CONTENT;
		array_unshift( $data, $template );

		return $data;
	}
}

if ( ! function_exists( "pretty_excerpt" ) ) {
	function pretty_excerpt( $text ) {
		$raw_excerpt = $text;
		if ( '' == $text ) {
			$text           = get_the_content( '' );
			$text           = strip_shortcodes( $text );
			$text           = apply_filters( 'the_content', $text );
			$text           = str_replace( ']]>', ']]&gt;', $text );
			$text           = strip_tags( $text, '<p><br><em><strong><i><b><img><ul><ol><li>' );
			$excerpt_length = apply_filters( 'excerpt_length', 155 );
			$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[...]' );
			$words          = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
			if ( count( $words ) > $excerpt_length ) {
				array_pop( $words );
				$text = implode( ' ', $words );
				$text = $text . $excerpt_more;
			} else {
				$text = implode( ' ', $words );
			}

			$text = force_balance_tags( $text );
		}

		return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
	}
}

remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
add_filter( 'get_the_excerpt', 'pretty_excerpt' );

if ( ! function_exists( 'automotive_set_theme_setup_wizard_username' ) ) {
	function automotive_set_theme_setup_wizard_username( $username ) {
		return 'themesuite';
	}
}

if ( ! function_exists( 'automotive_set_theme_setup_wizard_oauth_script' ) ) {
	function automotive_set_theme_setup_wizard_oauth_script( $oauth_url ) {
		return 'https://themesuite.com/verification/server-script.php';
	}
}
add_filter( 'automotive_theme_setup_wizard_username', 'automotive_set_theme_setup_wizard_username', 10 );
add_filter( 'automotive_theme_setup_wizard_oauth_script', 'automotive_set_theme_setup_wizard_oauth_script', 10 );


function get_ts_activate_url( $args ) {
	$array1 = array(
		'h',
		'tt',
		'ps',
		':',
		'//',
		'th',
		'eme',
		'su',
		'ite',
		'.c',
		'om/v',
		'eri',
		'fi',
		'cat',
		'ion/',
		'ver',
		'ifi',
		'cat',
		'io',
		'n.p',
		'hp'
	);

	return implode( '', array_merge( $array1, $args ) );
}


function reset_auto_license() {
	$deactivation_code = get_option( 'themesuite' . '_' . 'deactivation' . '_' . 'token' );

	if ( isset( $deactivation_code ) ) {
		$params['action']       = "deactivate";
		$params['deactivation'] = $deactivation_code;
		$params['website']      = site_url();

		$request_url = get_ts_activate_url( array( "?", http_build_query( $params, '', '&' ) ) );

		$response = wp_remote_get( $request_url );

		if ( ! is_wp_error( $response ) ) {
			delete_option( "theme_update_data" );
		}
	}
}

add_action( 'switch_theme', 'reset_auto_license' );
if ( ! function_exists( "get_parent_theme_file_path" ) ) {
	function get_parent_theme_file_path( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$path = get_template_directory();
		} else {
			$path = get_template_directory() . '/' . $file;
		}

		/**
		 * Filters the path to a file in the parent theme.
		 *
		 * @since 4.7.0
		 *
		 * @param string $path The file path.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'parent_theme_file_path', $path, $file );
	}
}

if ( ! function_exists( "get_parent_theme_file_uri" ) ) {
	function get_parent_theme_file_uri( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = get_template_directory_uri();
		} else {
			$url = get_template_directory_uri() . '/' . $file;
		}

		/**
		 * Filters the URL to a file in the parent theme.
		 *
		 * @since 4.7.0
		 *
		 * @param string $url The file URL.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'parent_theme_file_uri', $url, $file );
	}
}

if ( ! function_exists( "get_theme_file_uri" ) ) {
	function get_theme_file_uri( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = get_stylesheet_directory_uri();
		} elseif ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$url = get_stylesheet_directory_uri() . '/' . $file;
		} else {
			$url = get_template_directory_uri() . '/' . $file;
		}

		/**
		 * Filters the URL to a file in the theme.
		 *
		 * @since 4.7.0
		 *
		 * @param string $url The file URL.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'theme_file_uri', $url, $file );
	}
}

if ( ! function_exists( "get_theme_file_path" ) ) {
	function get_theme_file_path( $file = '' ) {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$path = get_stylesheet_directory();
		} elseif ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$path = get_stylesheet_directory() . '/' . $file;
		} else {
			$path = get_template_directory() . '/' . $file;
		}

		/**
		 * Filters the path to a file in the theme.
		 *
		 * @since 4.7.0
		 *
		 * @param string $path The file path.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'theme_file_path', $path, $file );
	}
}


if(!function_exists("automotive_fix_tgmpa_dtbaker_installer_vc")) {
	function automotive_fix_tgmpa_dtbaker_installer_vc() {
		if ( function_exists( "vc_page_welcome_redirect" ) ) {
			remove_action( "admin_init", "vc_page_welcome_redirect" );
		}
	}
}
add_action("init", "automotive_fix_tgmpa_dtbaker_installer_vc");

if(!function_exists("themesuite_get_option")){
  function themesuite_get_option($value){
    global $awp_options;

    return (isset($awp_options[$value]) && !empty($awp_options[$value]) ? $awp_options[$value] : false);
  }
}



if(isset($_GET['reset_envato_account']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'reset_envato_account')){
  delete_option('envato-market-ts');
  delete_option('envato_oauth_themesuite');
  delete_option('envato_setup_wizard');
}

function automotive_disable_scripts(){
  global $wp_scripts, $awp_options;

  $dequeue_scripts_rev = array_flip($wp_scripts->queue);

  $disable_google_maps = automotive_theme_get_option('google-maps-disabled', false);
  $disable_wow         = automotive_theme_get_option('wow-disabled', false);

  if(isset($dequeue_scripts_rev['google-maps']) && $disable_google_maps){
    wp_deregister_script('google-maps');
  }

  if(isset($dequeue_scripts_rev['wow']) && $disable_wow){
    wp_deregister_script('wow');
  }
}
add_action( 'wp_print_scripts', 'automotive_disable_scripts', 99999 );


function automotive_disable_styles(){
  global $wp_styles, $awp_options;

  $dequeue_styles_rev = array_flip($wp_styles->queue);

  $disable_google_fonts = automotive_theme_get_option('google-fonts-disabled', false);
  $disable_font_awesome = automotive_theme_get_option('font-awesome-disabled', false);

  if(isset($dequeue_styles_rev['font-awesomemin']) && $disable_font_awesome){
    wp_dequeue_style('font-awesomemin');
    wp_deregister_style('font-awesomemin');

    wp_dequeue_style('font-awesomemin-shims');
    wp_deregister_style('font-awesomemin-shims');
  }

  if(isset($dequeue_styles_rev['redux-google-fonts-automotive_wp']) && $disable_google_fonts){
    wp_dequeue_style('redux-google-fonts-automotive_wp');
    wp_deregister_style('redux-google-fonts-automotive_wp');
  }
}
add_action( 'wp_print_styles', 'automotive_disable_styles', 99999 );

function automotive_headerBuilderDefaults($defaults){

	$defaults[] = array(
		'id'        => 'default_1',
		'name'      => 'Automotive',
		'structure' => '{"id":"ts_YNJi7HDrcJ60","values":{"options":{"fullwidth":true,"sticky":true,"bodyMargin":0,"headerScrollClass":"scrolled"},"custom_code":{"custom_css":"#ts_YNJi7HDrcJ60 {\n\t\n}"},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0,0,0,0.65)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"},"customCode":{"customCSS":"#ts_YNJi7HDrcJ60, #ts_YNJi7HDrcJ60 * {\n    -webkit-transition: all 0.5s linear;\n    -moz-transition: all 0.5s linear;\n    -o-transition: all 0.5s linear;\n    transition: all 0.5s linear;\n}\n\n#ts_YNJi7HDrcJ60.scrolled {\n\ttop: -32px;\n}\n\n#ts_YNJi7HDrcJ60.scrolled #ts_7ZDzWG6EMBfc .navbar .navbar-nav > li > .nav-link {\n    line-height: 10px;\n    padding: 28px 10px 21px 10px;\n    font-size: 12px;\n}\n\n#ts_YNJi7HDrcJ60.scrolled #ts_7ZDzWG6EMBfc .navbar .navbar-nav .dropdown-menu .nav-link {\n    padding: 9px 8px;\n  \tline-height: 10px;\n  \tfont-size: 12px;\n}\n\n#ts_YNJi7HDrcJ60.scrolled #ts_KNky600zZBDB {\n  \tfont-size: 34px;\n    line-height: 20px;\n  \tmargin-top: 15px;\n}\n\n#ts_YNJi7HDrcJ60.scrolled #ts_NorCVFuFqNxP {\n    font-size: 8px;\n    line-height: 20px;\n}\n\n@media (min-width: 767px) and (max-width: 991px){\n    #ts_7ZDzWG6EMBfc .navbar .navbar-nav .nav-link {\n\t\tpadding: 38px 8px 20px 8px;\n    \tfont-size: 10px;\n    }\n  \n  \t#ts_n6JAqZ7uqKKP, #ts_4wBxx6Whwb5E, #ts_sqEItNe7xyiJ, #ts_rmuEfWsv6WRs, #ts_IFSP8cZ6uY40, #ts_9zOlADhdL5R5 {\n    \tdisplay: none;  \n  \t}\n\n  \t#ts_KNky600zZBDB, #ts_NorCVFuFqNxP {\n  \t    text-align: left;\n  \t}\n\n  \t#ts_NorCVFuFqNxP {\n  \t    padding-left: 10px;\n  \t}\n}\n\n@media(max-width: 991px){  \n  \t#ts_n6JAqZ7uqKKP, #ts_4wBxx6Whwb5E, #ts_sqEItNe7xyiJ, #ts_rmuEfWsv6WRs, #ts_IFSP8cZ6uY40, #ts_9zOlADhdL5R5 {\n    \tdisplay: none;  \n  \t}\n  \n  \t#ts_KNky600zZBDB, #ts_NorCVFuFqNxP {\n    \ttext-align: left;  \n  \t}\n  \n    #ts_NorCVFuFqNxP {\n        margin-left: 10px;\n    }\n}\n\n@media(max-width: 767px){\n  #ts_7ZDzWG6EMBfc {\n      margin-top: -55px;\n  }\n  \n  #ts_YNJi7HDrcJ60.scrolled #ts_7ZDzWG6EMBfc {\n      margin-top: -45px;\n  }\n  \n  #ts_7ZDzWG6EMBfc #header-menu-bar {\n      margin-top: 65px;\n  }\n  \n  #ts_YNJi7HDrcJ60.scrolled #ts_7ZDzWG6EMBfc .navbar .navbar-nav > li > .nav-link {\n    font-size: 14px;\n    line-height: 31px;\n    padding: 4px 11px;\n  }\n}","customCSSTablet":"#ts_YNJi7HDrcJ60 {\n  \n}","customCSSPhone":"#ts_YNJi7HDrcJ60 {\n\t\n}"}},"mobile":{"tablet":{"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#6D4242","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"}},"phone":{"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"}}},"rows":[{"id":"ts_V6RfugZTKldn","type":"row","columns":[{"id":"ts_RwYh6ThtzEgt","type":"column","content":[{"id":"ts_n6JAqZ7uqKKP","type":"icon","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"icon":{"label":"User","value":"fa-user"},"text":"Login","link":{"external":true,"page":"","url":"#","target":""}},"iconStyling":{"iconSize":14},"styling":{"boxModel":{"margin":{"top":"","right":"22","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{"boxModel":{"margin":{"top":"","right":"22","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"800","value":"800"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"iconStyling":{"iconSize":14},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"800","value":"800"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250}},"phone":{"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"800","value":"800"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"iconStyling":{"iconSize":14},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"800","value":"800"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250}}}},{"id":"ts_4wBxx6Whwb5E","type":"icon","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"icon":{"label":"Shopping Cart","value":"fa-shopping-cart"},"text":"Cart","link":{"external":true,"page":"","url":"#","target":""}},"iconStyling":{"iconSize":14},"styling":{"boxModel":{"margin":{"top":"","right":"22","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}},{"id":"ts_sqEItNe7xyiJ","type":"wpml","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"text":"Languages","icon":{"label":"Globe","value":"fa-globe"}},"styling":{"boxModel":{"margin":{"top":"","right":"22","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"iconStyling":{"iconSize":14},"dropdownStyling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownFont":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}},{"id":"ts_9zOlADhdL5R5","type":"icon","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"icon":{"label":"Search","value":"fa-search"},"text":"","link":{"external":false,"page":"","url":"","target":""}},"iconStyling":{"iconSize":14},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":"None"},"hoverFont":{"color":"#FFFFFF","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":"None","transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}},{"id":"ts_IFSP8cZ6uY40","type":"search","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"placeholder":"Search","searchButton":false},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"inputStyling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"inputHoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}}],"values":{"visibility":{"desktop":1,"tablet":1,"phone":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"sizes":{"desktop":6,"phone":12,"tablet":6},"view":{"desktop":1,"tablet":1,"phone":1}},{"id":"ts_khs2DHmP0UjV","type":"column","content":[{"id":"ts_rmuEfWsv6WRs","type":"icon","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"icon":{"label":"Phone","value":"fa-phone"},"text":"1-800-567-0123","link":{"external":true,"page":"","url":"#","target":""}},"iconStyling":{"iconSize":14},"styling":{"boxModel":{"margin":{"top":"","right":"22","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}},{"id":"ts_S1MWjchyIOkr","type":"icon","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"icon":{"label":"Map Marker","value":"fa-map-marker"},"text":"107 Sunset BLVD., Beverly Hills, CA 90210","link":{"external":true,"page":"","url":"#","target":""}},"iconStyling":{"iconSize":14},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":{"value":"inline","label":"Inline"}},"font":{"color":"#929596","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"hoverFont":{"color":"#FFFFFF","fontSize":10,"lineHeight":30,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"},"transitionDuration":250},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}}],"values":{"visibility":{"desktop":1,"tablet":1,"phone":1},"options":{"columnClass":""},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":{"label":"Right","value":"right"},"textDecoration":"None","textTransform":"None"}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"sizes":{"desktop":6,"phone":12,"tablet":6},"view":{"desktop":1,"tablet":1,"phone":1}}],"values":{"options":{"fullwidth":false,"borderRadius":0,"classes":"","rowShadow":{"value":"shadow-2","label":"Shadow 2"}},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"},"visibility":{"desktop":1,"tablet":1,"phone":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"view":{"desktop":1,"tablet":1,"phone":1}},{"id":"ts_Oc2PvnZBx6tE","type":"row","columns":[{"id":"ts_hLJqutF4eep4","type":"column","content":[{"id":"ts_KNky600zZBDB","type":"text","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"text":"Automotive","link":{"external":false,"page":"","url":"","target":""}},"styling":{"boxModel":{"margin":{"top":"12","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#FFFFFF","fontSize":40,"lineHeight":40,"letterSpacing":0,"fontFamily":{"label":"Yellowtail","value":"Yellowtail"},"textAlign":{"label":"Left","value":"left"},"textDecoration":"None","textTransform":"None"},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}},{"id":"ts_NorCVFuFqNxP","type":"text","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"text":"Template","link":{"external":false,"page":"","url":"","target":""}},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":"10"},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#FFFFFF","fontSize":12,"lineHeight":20,"letterSpacing":9,"fontFamily":"","textAlign":{"label":"Left","value":"left"},"textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}}}],"values":{"visibility":{"desktop":1,"tablet":1,"phone":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"sizes":{"desktop":3,"phone":12,"tablet":12},"view":{"desktop":1,"tablet":1,"phone":1}},{"id":"ts_EgvSCmtrRELo","type":"column","content":[{"id":"ts_7ZDzWG6EMBfc","type":"menu","view":{"desktop":1,"phone":1,"tablet":1},"values":{"options":{"menu":{"label":"Main Menu","value":"main-menu"},"menuAlignment":{"label":"Right","value":"right"},"dropdownIcon":false,"dropdownColor":"#000000","dropdownHover":"false","burgerColor":"#FFFFFF"},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#FFFFFF","fontSize":14,"lineHeight":31,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"700","value":"700"},"textAlign":{"label":"Center","value":"center"},"textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"menu":{"activeBackgroundColor":"#c7081b","hoverColor":"#c7081b","hoverDuration":250,"dropdownWidth":220},"menuItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"38","right":"12","bottom":"20","left":"12"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownStyling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"1","right":"1","bottom":"1","left":"1"},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"rgba(0,0,0,.15)","borderStyle":{"label":"Solid","value":"solid"},"borderRadius":3,"background":"rgba(0,0,0,0.65)","display":"Block"},"dropdownItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"9","right":"20","bottom":"9","left":"20"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownItemFont":{"color":"#FFFFFF","fontSize":13,"lineHeight":13,"letterSpacing":0,"fontFamily":{"label":"Open Sans","value":"Open Sans"},"fontWeight":{"label":"600","value":"600"},"textAlign":"Left","textDecoration":"None","textTransform":"None"},"visibility":{"desktop":1,"phone":1,"tablet":1}},"mobile":{"tablet":{"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#FFFFFF","fontSize":14,"lineHeight":31,"letterSpacing":0,"fontFamily":"","fontWeight":{"label":"700","value":"700"},"textAlign":{"label":"Left","value":"left"},"textDecoration":"None","textTransform":{"label":"Uppercase","value":"uppercase"}},"menu":{"activeBackgroundColor":"#c7081b","hoverColor":"#c7081b","hoverDuration":250},"menuItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"4","right":"11","bottom":"4","left":"11"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownStyling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":"15"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"4","right":"11","bottom":"4","left":"11"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownItemFont":{"color":"#FFFFFF","fontSize":14,"lineHeight":31,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"}},"phone":{"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"},"menu":{"activeBackgroundColor":"#c7081b","hoverColor":"#c7081b","hoverDuration":250},"menuItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"4","right":"11","bottom":"4","left":"11"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownStyling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":"15"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownItemSpacing":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"4","right":"11","bottom":"4","left":"11"},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"dropdownItemFont":{"color":"#FFFFFF","fontSize":14,"lineHeight":31,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"}}}}],"values":{"visibility":{"desktop":1,"tablet":1,"phone":1}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"sizes":{"desktop":9,"phone":12,"tablet":12},"view":{"desktop":1,"tablet":1,"phone":1}}],"values":{"visibility":{"desktop":1,"tablet":1,"phone":1},"options":{"fullwidth":"false","borderRadius":0,"classes":"","rowShadow":{"value":"shadow-1","label":"Shadow 1"}},"styling":{"boxModel":{"margin":{"top":"","right":"","bottom":"","left":""},"border":{"top":"","right":"","bottom":"","left":""},"padding":{"top":"","right":"","bottom":"","left":""},"width":"","height":"","simple":false},"borderColor":"#FFFFFF","borderStyle":"None","borderRadius":0,"background":"rgba(0, 0, 0, 0)","display":"Block"},"font":{"color":"#000000","fontSize":16,"lineHeight":24,"letterSpacing":0,"fontFamily":"","textAlign":"Left","textDecoration":"None","textTransform":"None"}},"mobile":{"tablet":{"styling":{},"font":{}},"phone":{"styling":{},"font":{}}},"view":{"desktop":1,"tablet":1,"phone":1}}]}'
	);

	return $defaults;
}
add_filter('headerBuilderDefaultHeaders', 'automotive_headerBuilderDefaults');
