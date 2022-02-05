<?php

function automotive_theme_hamburger_toggle(){ ?>
	<button class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#automotive-header-menu" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button><?php
}
add_action('automotive_theme_hamburger_toggle', 'automotive_theme_hamburger_toggle');

function automotive_theme_header_menu(){ ?>
	<div class="collapse navbar-collapse" id="automotive-header-menu" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
		<?php
		// bootstrap 4 menu
		if ( has_nav_menu( "header-menu" ) ) {
			$header_menu_location = ( ! is_user_logged_in() || ( is_user_logged_in() && ! has_nav_menu( "logged-in-header-menu" ) ) ? "header-menu" : "logged-in-header-menu" );
			$mobile_menu_location = ( ! is_user_logged_in() || ( is_user_logged_in() && ! has_nav_menu( "logged-in-mobile-menu" ) ) ? "mobile-menu" : "logged-in-mobile-menu" );

			wp_nav_menu(
				array(
					'theme_location'  => $header_menu_location,
					'fallback_cb'     => 'bs4navwalker::fallback',
					'walker'          => new bs4navwalker(),
					'menu_class'      => 'nav navbar-nav pull-right fullsize_menu',
					'container_class' => 'menu-main-menu-container'
				)
			);

			// mobile menu
			wp_nav_menu(
				array(
					'theme_location'  => $mobile_menu_location,
					'fallback_cb'     => 'bs4navwalker::fallback',
					'walker'          => new bs4navwalker(),
					'menu_class'      => 'nav navbar-nav pull-right mobile_dropdown_menu',
					'container_class' => 'mobile-menu-main-menu-container'
				)
			);
		} else {
			echo "<ul class=\"nav navbar-nav pull-right\"><li class=\"active\"><a href=\"" . home_url() . "\">" . __( "Home", "automotive" ) . "</a></li></ul>";
		} ?>
	</div><?php
}
add_action('automotive_theme_header_menu', 'automotive_theme_header_menu');

if ( ! function_exists( "automotive_styles" ) ) {
	function automotive_styles() {
		if ( $GLOBALS['pagenow'] != 'wp-login.php' && ! is_admin() ) {
			global $awp_options;

			$responsiveness = automotive_theme_get_option('responsiveness', true);

			$theme_details = wp_get_theme();
			$theme_version = $theme_details->get( 'Version' );

			$css_dir   = get_template_directory_uri() . "/css/";
			$css_files = array(
				"bootstrap"             => "bootstrap.min",
        "font-awesomemin"       => "all.min",
				"font-awesomemin-shims" => "v4-shims.min",
				"flexslider"            => "flexslider",
				"jqueryfancybox"        => "jquery.fancybox",
				"style"                 => "style",
				"ts"                    => "ts",
				"mobile"                => "mobile",
				"wp"                    => "wp",
				"social-likes"          => "social-likes"
			);

			if ( !$responsiveness ) {
				$css_files[] = "disable-responsive";

				unset($css_files['mobile']);
			}

			// rtl
			if ( is_rtl() ) {
        $css_files['style']  = "style-rtl";
        $css_files['mobile'] = "mobile-rtl";
        $css_files['ts']     = "ts-rtl";
			}


			foreach ( $css_files as $file_handle => $file ) {
				wp_register_style( $file_handle, $css_dir . $file . '.css', array(), $theme_version, 'all' );
				wp_enqueue_style( $file_handle );
			}

			if ( isset( $awp_options['woocommerce_menu_cart'] ) && ! empty( $awp_options['woocommerce_menu_cart'] ) ) {
				wp_register_style( "content-scroller", $css_dir . 'jquery.mCustomScrollbar.min.css', array(), $theme_version, 'all' );
				wp_enqueue_style( "content-scroller" );
			}

			// child theme
			if ( is_child_theme() ) {
				wp_enqueue_style( "child-style", get_stylesheet_uri() );
			}

			// external styles
			if ( isset( $awp_options['external_css_styles'] ) && ! empty( $awp_options['external_css_styles'] ) ) {
				$i = 1;
				foreach ( $awp_options['external_css_styles'] as $style_url ) {
					if ( filter_var( $style_url, FILTER_VALIDATE_URL ) ) {
						wp_enqueue_style( "auto-external-" . $i, $style_url );
						$i ++;
					}
				}
			}

			// external scripts
			if ( isset( $awp_options['external_js_scripts'] ) && ! empty( $awp_options['external_js_scripts'] ) ) {
				$i = 1;
				foreach ( $awp_options['external_js_scripts'] as $js_url ) {
					if ( filter_var( $js_url, FILTER_VALIDATE_URL ) ) {
						wp_enqueue_script( "auto-external-" . $i, $js_url );
						$i ++;
					}
				}
			}

			// custom styling
      $custom_css = get_option('automotive_theme_global_css');

			if(empty($custom_css)){
				$custom_css = automotive_generate_custom_css();
			}

      wp_add_inline_style( 'style', $custom_css );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'automotive_styles' );

//********************************************
//	Alter the body class if we need
//***********************************************************
function automotive_default_alter_body_class( $classes ) {
	// disable responsiveness
	if ( ! automotive_theme_get_option('responsiveness', true) ) {
		$classes[] = 'no_responsive';
	}

	// woocommerce dropdown functionality
	if( automotive_theme_get_option('woo_dropdown_categories', false) ){
		$classes[] = 'woo-auto-dropdowns';
	}

	return $classes;
}
add_filter( 'body_class', 'automotive_default_alter_body_class' );

if ( ! function_exists( "automotive_scripts" ) ) {
	function automotive_scripts() {
		global $awp_options;

		$theme_details = wp_get_theme();
		$theme_version = $theme_details->get( 'Version' );

    $needs_popper  = (is_single() || is_page() || is_archive() || is_author() || is_category() || is_tag() || is_home());

    $bootstrap_deps = array('jquery');

    if($needs_popper){
      $bootstrap_deps[] = 'popper';
    }

		wp_enqueue_script( 'jquery' );

		$js_dir = get_template_directory_uri() . "/js/";

		if ( isset( $awp_options['retina'] ) && ! empty( $awp_options['retina'] ) ) {
			wp_register_script( "retina", $js_dir . 'retina.js', array( 'jquery' ), $theme_version, true );
			wp_enqueue_script( "retina" );
		}

		if ( isset( $awp_options['post_layout'] ) && $awp_options['post_layout'] == 'boxed' ) {
      wp_register_script( "isotope", $js_dir . 'jquery.isotope.js', array('jquery'), $theme_version, true );
      wp_enqueue_script( "isotope" );
    }

		wp_register_script( "tether", $js_dir . 'tether.min.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( "tether" );

    // popper js
    if( $needs_popper ){
  		wp_register_script( "popper", $js_dir . 'popper.min.js', array( 'jquery' ), $theme_version, true );
  		wp_enqueue_script( "popper" );
    }

		wp_register_script( "bootstrap", $js_dir . 'bootstrap.js', $bootstrap_deps, $theme_version, true );
		wp_enqueue_script( "bootstrap" );

		wp_register_script( "lazy-load", $js_dir . 'jquery.lazy.min.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( "lazy-load" );

		wp_register_script( "wow", $js_dir . 'wow.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( "wow" );

		wp_register_script( "main", $js_dir . 'main.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( "main" );

    if ( ! is_singular( 'listings' ) ) {
  		wp_register_script( "fancybox", $js_dir . 'jquery.fancybox.js', array( 'jquery' ), $theme_version, true );
  		wp_enqueue_script( "fancybox" );
    }

		wp_register_script( "social-likes", $js_dir . 'social-likes.min.js', array( 'jquery' ), $theme_version, true );

		if ( is_singular( array( 'listings', 'post' ) ) ) {
			wp_enqueue_script( "social-likes" );
		}

		if ( function_exists( "is_product" ) && is_product() ) {
			wp_register_script( "flexslider", $js_dir . 'jquery.flexslider.js', array( 'jquery' ), $theme_version, true );
			wp_enqueue_script( "flexslider" );
		}

		if ( isset( $awp_options['woocommerce_menu_cart'] ) && ! empty( $awp_options['woocommerce_menu_cart'] ) ) {
			wp_register_script( "content-scroller", $js_dir . 'jquery.mCustomScrollbar.min.js', array(), $theme_version, 'all' );
			wp_enqueue_script( "content-scroller" );
		}

		wp_localize_script( 'main', 'ajax_variables', array(
			'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
			'template_url'                 => get_template_directory_uri(),
			"disable_header_resize"        => (isset($awp_options['header_resize']) && !$awp_options['header_resize'] ? true : false),
			"disable_mobile_header_resize" => (isset($awp_options['header_resize_mobile']) && !$awp_options['header_resize_mobile'] ? true : false)
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'automotive_scripts' );


function automotive_theme_footer_login(){
	$toolbar_login_show  = automotive_theme_get_option('toolbar_login_show', true);

	if($toolbar_login_show){ ?>
	<div class="modal fade" id="login_modal" data-backdrop="static" data-keyboard="true" tabindex="-1">
		<div class="vertical-alignment-helper">
	    <div class="modal-dialog vertical-align-center">
        <div class="modal-content">
          <div class="modal-body">
            <form method="POST" id="automotive_login_form" action="<?php echo home_url(); ?>">
              <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e("Close", "automotive"); ?></span></button>

              <h4><?php _e("Login to access different features", "automotive"); ?></h4>

              <input type="text" placeholder="<?php _e("Username", "automotive"); ?>" class="username_input margin-right-10 margin-vertical-10">
              <input type="password" placeholder="<?php _e("Password", "automotive"); ?>" class="password_input margin-right-10 margin-vertical-10"> <i class="fa fa-refresh fa-spin login_loading"></i>

              <div class="clearfix"></div>

              <input type="checkbox" name="remember_me" value="yes" id="remember_me"> <label for="remember_me" class="margin-bottom-10"><?php _e("Remember Me", "automotive"); ?></label><br>

              <input type="submit" class="ajax_login md-button" data-nonce="<?php echo wp_create_nonce("ajax_login_none"); ?>" value="<?php _e("Login", "automotive"); ?>">
						</form>
          </div>
        </div>
	    </div>
		</div>
	</div>
	<?php }
}
add_action('automotive_theme_footer_start', 'automotive_theme_footer_login');

function automotive_theme_sidebars(){


	// Define Sidebar Widget Area 5
	register_sidebar( array(
		'name'          => __( 'Default Footer', 'automotive' ),
		'id'            => 'default-footer',
		'before_widget' => '<div id="%1$s" class="list col-xs-12 %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>'
	) );

	// Define Sidebar Widget Area 5
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'automotive' ),
		'id'            => 'blog-sidebar',
		'before_widget' => '<div id="%1$s" class="side-widget widget padding-bottom-60 list col-xs-12 %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="side-widget-title margin-bottom-25">',
		'after_title'   => '</h3>'
	) );
}
add_action( "widgets_init", "automotive_theme_sidebars" );


remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item_add_to_cart', 'woocommerce_template_loop_add_to_cart' );

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );




//woocommerce_back_info_product_2
$product_layout = ( automotive_theme_get_option('woo_shop_layout', true) ? "1" : "2" );

if ( $product_layout == "2" ) {
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
}
