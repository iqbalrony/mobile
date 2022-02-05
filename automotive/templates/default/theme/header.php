<?php global $post;

$header_image = get_post_meta( get_current_id(), "header_image", true );
$header_image = ( ! empty( $header_image ) ? wp_get_attachment_image_src( $header_image, "full" ) : "" );
$header_image = ( ! empty( $header_image ) ? $header_image[0] : "" );


$no_header         = get_post_meta( get_current_id(), "no_header", true );
$current_post_type = get_post_type();
$handle            = automotive_get_page_handle(); ?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <title><?php automotive_head_title(); ?></title>
	<?php
  do_action('automotive_theme_head_end');

	wp_head(); ?>
</head>
<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage">
<?php

$body_layout = automotive_theme_get_option('body_layout', 1);

if ( $body_layout != 1 ) {
	echo "<div class='boxed_layout" . ( $body_layout == 3 ? " margin" : "" ) . "'>";
}

// check if the header builder object exists, and if the setting is enabled.

if(automotive_theme_get_option('header_builder_enabled', false)){
  global $ThemeSuiteHeaderBuilder;

  if(isset($ThemeSuiteHeaderBuilder) && !empty($ThemeSuiteHeaderBuilder) && is_object($ThemeSuiteHeaderBuilder) && method_exists($ThemeSuiteHeaderBuilder, 'generate')){
  	$ThemeSuiteHeaderBuilder->generate('html');
  }
} else {
	// top toolbar classes
	$language_mobile = ( automotive_theme_get_option('toolbar_language_show_mobile', false) ? " li_mobile_show" : "" );
	$search_mobile   = ( automotive_theme_get_option('toolbar_search_show_mobile', false) ? " li_mobile_show" : "" );
	$phone_mobile    = ( automotive_theme_get_option('toolbar_phone_show_mobile', false) ? " li_mobile_show" : "" );
	$address_mobile  = ( automotive_theme_get_option('toolbar_address_show_mobile', false) ? " li_mobile_show" : "" );
	$logout_mobile   = ( automotive_theme_get_option('toolbar_logout_show_mobile', false) ? " li_mobile_show" : "" );

	// links
  $toolbar_login               = automotive_theme_get_option('toolbar_login', __( "Login", "automotive" ));
  $toolbar_login_link          = automotive_theme_get_option('toolbar_login_link', false);
  $toolbar_login_link_external = automotive_theme_get_option('toolbar_login_link_external', false);
	$login_link                  = ( $toolbar_login_link ? get_permalink( $toolbar_login_link ) : "#" );
	$login_link                  = ( $toolbar_login_link_external ? esc_url( $toolbar_login_link_external ) : $login_link );

  $toolbar_logout               = automotive_theme_get_option('toolbar_logout', __( "Logout", "automotive" ));
  $toolbar_logout_link          = automotive_theme_get_option('toolbar_logout_link', false);
  $toolbar_logout_link_external = automotive_theme_get_option('toolbar_logout_link_external', false);
	$logout_link                  = ( $toolbar_logout_link ? get_permalink( $toolbar_logout_link ) : wp_logout_url() );
	$logout_link                  = ( $toolbar_logout_link_external ? esc_url( $toolbar_logout_link_external ) : $logout_link );

  $toolbar_address_link          = automotive_theme_get_option('toolbar_address_link', false);
  $toolbar_address_link_external = automotive_theme_get_option('toolbar_address_link_external', false);
	$address_link                  = ( $toolbar_address_link ? get_permalink( $toolbar_address_link ) : "#" );
	$address_link                  = ( $toolbar_address_link_external ? esc_url( $toolbar_address_link_external ) : $address_link );

	$toolbar_mobile_classes = ( automotive_theme_get_option('toolbar_mobile_classes', true) ? 12 : 6 );
  $toolbar_languages      = automotive_theme_get_option('toolbar_languages', __( "Languages", "automotive" ) );

  $header_resize = automotive_theme_get_option('header_resize', true);
  $header_top    = automotive_theme_get_option('header_top', true);

	// header classes
	$header_classes = array(
    "clearfix",
    "affix-top",
		"original"
	);

	if($header_resize){
    $header_classes[] = 'no_resize';
	}

  if(!automotive_theme_get_option('sticky_header', true)) {
    $header_classes[] = 'no_fixed_header';
  }

	if($header_top){
    $header_classes[] = 'no_top_neg';
	}

	if(automotive_theme_get_option('header_resize_mobile', true)){
    $header_classes[] = 'no_header_resize_mobile';
	}

	// social icon data
	$toolbar_social_show        = automotive_theme_get_option('toolbar_social_show', false);
	$toolbar_social_show_mobile = automotive_theme_get_option('toolbar_social_show_mobile', false);
	$toolbar_social_position    = (automotive_theme_get_option('toolbar_social_position', false) ? 'left' : 'right');

  do_action('automotive_theme_header_start'); ?>
<!--Header Start-->
<header <?php echo ( $header_resize ? ' data-spy="affix" data-offset-top="1"' : '' ) . ' class="' . implode(" ", $header_classes) . '"'; ?> itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<?php if ( $header_top ) { ?>
        <div class="toolbar">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-<?php echo $toolbar_mobile_classes; ?> left_bar">
                        <ul class="left-none">
		                        <?php if ( ! is_user_logged_in() && automotive_theme_get_option('toolbar_login_show', false) ) { ?>
                              <li class="toolbar_login<?php echo (automotive_theme_get_option('toolbar_login_show_mobile', false) ? ' li_mobile_show' : ''); ?>">
                                <a href="<?php echo $login_link; ?>" <?php echo ( $toolbar_login_link ? "" : 'data-toggle="modal" data-target="#login_modal"' ); ?>>
                                  <i class="fa fa-user"></i> <?php echo $toolbar_login; ?>
                                </a>
                              </li>
              							<?php } ?>

                            <?php if ( is_user_logged_in() && automotive_theme_get_option('toolbar_logout_show', true) ) { ?>
                              <li class="toolbar_login<?php echo (automotive_theme_get_option('toolbar_logout_show_mobile', false) ? ' li_mobile_show' : ''); ?>">
                                <a href="<?php echo $logout_link; ?>">
                                  <i class="fa fa-user"></i> <?php echo $toolbar_logout; ?>
                                </a>
                              </li>
                            <?php } ?>

                            <?php if ( automotive_theme_get_option('woocommerce_cart', true) ) {
                                    $woocommerce_cart_link = automotive_theme_get_option('woocommerce_cart_link', false); ?>
                              <li class="toolbar_cart">
                                <a href="<?php echo( $woocommerce_cart_link ? get_permalink( $woocommerce_cart_link ) : "#" ); ?>">
                                  <i class="fa fa-shopping-cart"></i> <?php _e( "Cart", "automotive" ); ?>
                                </a>

	                              <?php woocommerce_shopping_cart(); ?>

                              </li>
                            <?php } ?>

                            <?php if ( automotive_theme_get_option('toolbar_language_show', true) ) { ?>
                              <li class="toolbar_language<?php echo ($language_mobile ? ' ' . $language_mobile : ''); ?>">
                                <a href="#">
                                  <i class="fa fa-globe"></i> <?php echo $toolbar_languages; ?>
                                </a>
			                           <?php automotive_languages_dropdown_menu(); ?>
                              </li>
                            <?php } ?>

                            <?php if ( automotive_theme_get_option('toolbar_search_show', true) ) { ?>
                              <li class="toolbar_search<?php echo ($search_mobile ? ' ' . $search_mobile : ''); ?>">
                                  <form method="GET" action="<?php echo home_url( '/' ); ?>" id="header_searchform">
                                      <button type="submit"><i class="fa fa-search"></i></button>
                                      <input type="search" placeholder="<?php echo automotive_theme_get_option('toolbar_search', __( "Search", "automotive" )); ?>" class="search_box" name="s" value="<?php echo get_search_query(); ?>">
                                  </form>
                              </li>
                            <?php } ?>
                        </ul>
												<?php if($toolbar_social_position == 'left' && ($toolbar_social_show || $toolbar_social_show_mobile)){
													automotive_social_icons($toolbar_social_show_mobile ? 'li_mobile_show' . (!$toolbar_social_show ? ' hide_desktop' : '') : '');
												} ?>
                    </div>
                    <div class="col-md-6 col-sm-6 col-<?php echo $toolbar_mobile_classes; ?> ">
                        <?php if($toolbar_social_position == 'right' && ($toolbar_social_show || $toolbar_social_show_mobile)){
                          automotive_social_icons($toolbar_social_show_mobile ? 'li_mobile_show' . (!$toolbar_social_show ? ' hide_desktop' : '') : '');
                        } ?>
                        <ul class="right-none pull-right company_info">
                          <?php if ( automotive_theme_get_option('toolbar_phone_show', true) ) { ?>
                                <li class="toolbar_phone<?php echo ($phone_mobile ? ' ' . $phone_mobile : ''); ?>">
                                  <a href="<?php echo esc_url(automotive_theme_get_option('toolbar_phone_link', '#')); ?>">
                                    <i class="fa fa-phone"></i> <?php echo automotive_theme_get_option('toolbar_phone', '1-800-567-0123'); ?>
                                  </a>
                                </li>
                              <?php } ?>

                              <?php if ( automotive_theme_get_option('toolbar_address_show', true) ) { ?>
                                <li class="address toolbar_address<?php echo ($address_mobile ? ' ' . $address_mobile : ''); ?>">
                                  <a href="<?php echo $address_link; ?>">
                                    <i class="fa fa-map-marker"></i> <?php echo automotive_theme_get_option('toolbar_address', '107 Sunset Blvd., Beverly Hills, CA  90210'); ?>
                                  </a>
                                </li>
                              <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>

			<?php if ( automotive_theme_get_option('toolbar_shadow') ) { ?>
                <div class="toolbar_shadow"></div>
			<?php } ?>
        </div>
	<?php } ?>

	<?php global $lwp_options; ?>
  <div class="bottom-header">
      <div class="container">
        <div class="row">
          <nav class="col-12 navbar navbar-default">
            <div class="navbar-header">
              <?php do_action('automotive_theme_hamburger_toggle'); ?>

              <?php do_action('automotive_theme_header_logo'); ?>
            </div>

            <div class="navbar-navigation">
              <div class="navbar-toggleable-sm">
                <?php do_action('automotive_theme_header_menu'); ?>
              </div>
            </div>
            <!-- /.navbar-collapse -->
          </nav>
        </div>
        <!-- /.container-fluid -->
      </div>

		<?php if ( automotive_theme_get_option('header_shadow') ) { ?>
      <div class="header_shadow"></div>
		<?php } ?>

    </div>
</header>
<!--Header End-->
<?php } ?>

<div class="clearfix"></div>

<?php
do_action('automotive_theme_header_end');

// if slideshow on homepage
$action         = ( is_404() || ( function_exists( "is_shop" ) && is_shop() || is_search() ) ? "" : get_post_meta( get_current_id(), "action_toggle", true ) );
$page_slideshow = ( is_404() || ( function_exists( "is_shop" ) && is_shop() || is_search() ) ? "" : get_post_meta( get_current_id(), "page_slideshow", true ) );

if ( isset( $page_slideshow ) && ! empty( $page_slideshow ) && $page_slideshow != "none" && function_exists( "putRevSlider" ) ) {
	echo "<div class='header_rev_slider_container'>";
	putRevSlider( $page_slideshow );
	echo "</div>";
} else {
	// if is search page
	if ( is_search() || is_category() || is_tag() || is_404() || ( function_exists( "is_product_category" ) && is_product_category() ) || function_exists( "is_product_tag" ) && is_product_tag() || function_exists( "is_shop" ) && is_shop() || ( get_option( 'show_on_front' ) == "posts" && is_home() ) ) {
    $header_page_image = automotive_theme_get_option($handle . '_page_image', false);
		$header_image        = ( $header_page_image ? $header_page_image['url'] : "" );
	}

	// listing default header image
  $default_header_listing_image = automotive_theme_get_option('default_header_listing_image', false);

	if ( empty( $header_image ) && $default_header_listing_image && $current_post_type == 'listings' ) {
		$header_image = $default_header_listing_image['url'];
	}

	// if no header image grab the default
  $default_header_image = automotive_theme_get_option('default_header_image', false);

	if ( empty( $header_image ) && $default_header_image ) {
		$header_image = $default_header_image['url'];
	}

	// no header
	if ( isset( $no_header ) && $no_header != "no_header" ) { ?>

        <section id="secondary-banner"
                 class="<?php echo( $action == "on" ? "action_on" : "" ); ?>"<?php echo( isset( $header_image ) && ! empty( $header_image ) ? " style='background-image: url(" . $header_image . ");'" : "" ); ?>>
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12 title-column">
						<?php
						$page_info  = automotive_get_page_info();
						$title      = $page_info['title'];
						$desc       = $page_info['desc'];
						$breadcrumb = $page_info['breadcrumb'];

						$title_heading_tag = (automotive_theme_get_option('blog_post_heading', false) ? "h2" : "h1");

						echo "<" . $title_heading_tag . " class=\"main-heading\">" . $title . "</" . $title_heading_tag . ">";
						echo "<h4 class=\"secondary-heading\">" . $desc . "</h4>"; ?>

                    </div>
                    <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 breadcrumb-column">
											<?php
											if(automotive_theme_get_option('breadcrumb_functionality', true)){
												echo the_breadcrumb( ! empty( $breadcrumb ) ? $breadcrumb : ""  );
											}
											?>
                    </div>
                </div>
            </div>
        </section>
        <!--#secondary-banner ends-->
	<?php } ?>

<?php }

$no_header_shadow = get_post_meta( get_current_id(), "no_header_shadow", true );

if ( ( ! isset( $no_header_shadow ) || ( isset( $no_header_shadow ) && $no_header_shadow != "no_header_shadow" ) ) && isset( $action ) && $action != "on" ) {
	echo '<div class="message-shadow"></div>';
}

action_area( $action, $no_header );

?>

<section class="content<?php echo( isset( $no_header ) && $no_header == "no_header" ? " push_down" : "" ); ?>">

	<?php
	$is_woocommerce_enabled   = function_exists( "is_woocommerce" );
	$is_woocommerce_fullwidth = automotive_theme_get_option('woocommerce_fullwidth', true);
	$container_class          = "container" . ( $is_woocommerce_enabled && ( is_shop() || is_product_category() || is_product_tag() || is_product() ) && $is_woocommerce_fullwidth ? "-fluid" : "" );

	?>

    <div class="<?php echo sanitize_html_class( $container_class ) . ( ! $is_woocommerce_fullwidth && $is_woocommerce_enabled && is_woocommerce() ? " woocommerce-regular" : "" ); ?>">
