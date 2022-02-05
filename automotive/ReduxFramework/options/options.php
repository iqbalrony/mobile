<?php
if ( ! class_exists( "ReduxFramework" ) ) {
	return;
}

if ( ! class_exists( "Redux_Framework_automotive_wp_theme" ) ) {
	class Redux_Framework_automotive_wp_theme {

		public $args = array();
		public $sections = array();
		public $theme;
		public $ReduxFramework;

		public function __construct() {
			// add_action( 'after_setup_theme', array( $this, 'loadConfig' ), 10 );
			add_action( 'init', array( $this, 'loadConfig' ), 20 );

			$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );

		}

		public function loadConfig() {
			global $social_options;

			$is_woocommerce_active         = function_exists( "is_woocommerce" );
			$automotive_temp_color_options = get_option('automotive_temp_color_options');
			$automotive_templates          = automotive_get_templates();

			// optional_required_field used for header builder

			$general_settings   = array(
				'title'  => __( 'General Settings', 'automotive' ),
				'fields' => array(
					'site_template' => array(
						'id'      => 'site_template',
						'type'    => 'select',
						'title'   => __( 'Site Template', 'automotive' ),
						'desc'    => __( 'Choose which template will be used for the site. Once the setting has been saved you can refresh the page for the new options.', 'automotive' ),
						'options' => $automotive_templates,
						'default' => 'default'
					),
					'favicon' => array(
						'desc'  => __( 'Image to display beside the url bar', 'automotive' ),
						'id'    => 'favicon',
						'type'  => 'media',
						'title' => __( 'Favicon', 'automotive' ),
						'url'   => true,
					),
					'body_layout' => array(
						'title'   => __( 'Body Layout', 'automotive' ),
						'desc'    => __( 'Choose which layout the body will have', 'automotive' ),
						'type'    => 'button_set',
						'id'      => 'body_layout',
						'options' => array(
							'1' => __( 'Fullwidth', 'automotive' ),
							'2' => __( 'Boxed', 'automotive' ),
							'3' => __( 'Boxed Margin', 'automotive' )
						),
						'default' => 1
					),
					'default_sidebar' => array(
						'title'   => __( 'Default Sidebar Option', 'automotive' ),
						'desc'    => __( 'Choose what the default sidebar option will be when creating a new page', 'automotive' ),
						'type'    => 'button_set',
						'id'      => 'default_sidebar',
						'options' => array(
							'none'  => __( 'None', 'automotive' ),
							'left'  => __( 'Left', 'automotive' ),
							'right' => __( 'Right', 'automotive' )
						),
						'default' => 'none'
					),
					'boxed_background' => array(
						'id'       => 'boxed_background',
						'type'     => 'background',
						'title'    => __( 'Boxed Background', 'automotive' ),
						'desc'     => __( 'Sets the background image for boxed layouts', 'automotive' ),
						'required' => array( 'body_layout', '>', 1 ),
					),
					'social_share_buttons' => array(
						'desc'    => __( 'Enable or disable the social share buttons at the end of each blog post.', 'automotive' ),
						'type'    => 'switch',
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'id'      => 'social_share_buttons',
						'title'   => __( 'Social Share Buttons', 'automotive' ),
						'default' => '1',
					),
					'featured_image_blog' => array(
						'desc'    => __( 'Enable or disable the featured image showing above the blog post page.', 'automotive' ),
						'type'    => 'switch',
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'id'      => 'featured_image_blog',
						'title'   => __( 'Featured Image', 'automotive' ),
						'default' => '0',
					),
					'images_border' => array(
						'desc'    => __( 'Enable or disable the border added on images.', 'automotive' ),
						'type'    => 'switch',
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'id'      => 'images_border',
						'title'   => __( 'Image Border', 'automotive' ),
						'default' => '1',
					),
					'google_analytics' => array(
						'desc'  => __( 'Paste your Google Analytics (or other) tracking code here. This will be added into the footer or header based on which you select afterwards. <br><br> Please <b>do not</b> include the &lt;script&gt; tags.<br><br><a href="https://support.themesuite.com/kb/faq.php?id=14" target="_blank">How to setup the new gtag tracking code.</a>', 'automotive' ),
						'id'    => 'google_analytics',
						'type'  => 'ace_editor',
						'title' => __( 'Tracking Code', 'automotive' ),
						'theme' => 'chrome'
					),
					'tracking_code_position' => array(
						'desc' => __( 'Place code before &lt;/head&gt; or &lt;/body&gt;', 'automotive' ),
						'id'   => 'tracking_code_position',
						'on'   => '&lt;/' . __( 'head', 'automotive' ) . '&gt;',
						'off'  => '&lt;/' . __( 'body', 'automotive' ) . '&gt;',
						'type' => 'switch',
					),
					'custom_sidebars' => array(
						'id'      => 'custom_sidebars',
						'type'    => 'multi_text',
						'title'   => __( 'Custom Sidebars', 'listings' ),
						'desc'    => __( 'These sidebars can be chosen in the page options while editing a page.', 'automotive' ),
						'default' => array(
							__( 'Sidebar 1', 'listings' )
						)
					),
					'responsiveness' => array(
						'desc'    => __( 'Enable or disable the responsiveness of the theme.<br>Note: You may need to disable responsiveness in the WPBakery Page Builder settings as well.', 'automotive' ),
						'type'    => 'switch',
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'id'      => 'responsiveness',
						'title'   => __( 'Responsiveness', 'automotive' ),
						'default' => '1',
					),
					'retina' => array(
						'desc'    => __( 'Enable or disable the retina images.', 'automotive' ),
						'type'    => 'switch',
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'id'      => 'retina',
						'title'   => __( 'Retina', 'automotive' ),
						'default' => '1',
					),
				 'back_to_top' => array(
					 'desc'    => __( 'Enable or disable the back to top.', 'automotive' ),
					 'type'    => 'switch',
					 'on'      => __( 'Enabled', 'automotive' ),
					 'off'     => __( 'Disabled', 'automotive' ),
					 'id'      => 'back_to_top',
					 'title'   => __( 'Back to Top', 'automotive' ),
					 'default' => true,
				 ),
					'settings-disable-scripts-section-start' => array(
			       'id'       => 'settings1-section-start',
			       'type'     => 'section',
			       'title'    => __('Disable Scripts', 'redux-framework-demo'),
			       'indent'   => true
				   ),

 					 'google-maps-disabled' => array(
						'id'      => 'google-maps-disabled',
						'type'    => 'switch',
						'title'   => __( 'Google Maps', 'automotive' ),
 						'on'      => __( 'Disable', 'automotive' ),
 						'off'     => __( 'Enable', 'automotive' ),
 						'default' => false,
 					),

				 'google-fonts-disabled' => array(
					 'id'      => 'google-fonts-disabled',
					 'type'    => 'switch',
					 'title'   => __( 'Google Fonts', 'automotive' ),
					 'on'      => __( 'Disable', 'automotive' ),
					 'off'     => __( 'Enable', 'automotive' ),
					 'default' => false,
				 ),

				 'google-fonts-disabled' => array(
					 'id'      => 'font-awesome-disabled',
					 'type'    => 'switch',
					 'title'   => __( 'Font Awesome', 'automotive' ),
					 'on'      => __( 'Disable', 'automotive' ),
					 'off'     => __( 'Enable', 'automotive' ),
					 'default' => false,
				 ),

				 'wow-disabled' => array(
					 'id'      => 'wow-disabled',
					 'type'    => 'switch',
					 'title'   => __( 'Wow animations', 'automotive' ),
					 'on'      => __( 'Disable', 'automotive' ),
					 'off'     => __( 'Enable', 'automotive' ),
					 'default' => false,
				 ),

					 'settings-disable-scripts-section-end' => array(
 			       'id'       => 'settings1-section-end',
 			       'type'     => 'section',
						 'indent'   => false
					 ),
				),
				'icon'   => 'el-icon-cog',
			);

			// remove the site template option if
			if(count($automotive_templates) == 1){
				unset($general_settings['fields']['site_template']);
			}

			$header_settings    = array(
				'title'  => __( 'Header Settings', 'automotive' ),
				'fields' => array(
					'logo_text' => array(
						'title'   => __( 'Logo ', 'automotive' ),
						'desc'    => __( 'Main logo text', 'automotive' ),
						'type'    => 'text',
						'id'      => 'logo_text',
						'default' => __( 'Automotive', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_text_secondary'  => array(
						'desc'    => __( 'Text displayed under the logo text', 'automotive' ),
						'type'    => 'text',
						'id'      => 'logo_text_secondary',
						'default' => __( 'Template', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_image' => array(
						'desc' => 'For best results make the image 270px x 65px. This setting <strong>will</strong> take precedence over the above one.',
						'type' => 'media',
						'id'   => 'logo_image',
						'url'  => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_customization' => array(
						'id'      => 'logo_customization',
						'type'    => 'switch',
						'title'   => __( 'Logo Image Control', 'automotive' ),
						'desc'    => __( 'If enabled you can control the logo width and height as well as the spacing around the logo.', 'automotive' ),
						'default' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_dimensions' => array(
						'id'       => 'logo_dimensions',
						'type'     => 'dimensions',
						'units'    => array( 'em', 'px', '%' ),
						'title'    => __( 'Logo Dimensions', 'automotive' ),
						'desc'     => __( 'Adjust the logo dimensions if you are using an image.', 'automotive' ),
						'default'  => array(
							'width'  => '65',
							'height' => '65'
						),
						'required' => array( 'logo_customization', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_margin' => array(
						'id'             => 'logo_margin',
						'type'           => 'spacing',
						'mode'           => 'margin',
						'units'          => array( 'em', 'px', '%' ),
						'units_extended' => 'false',
						'title'          => __( 'Logo Margin', 'automotive' ),
						'desc'           => __( 'Adjust the margin on the logo if you are using an image.', 'automotive' ),
						'default'        => array(
							'margin-top'    => '0px',
							'margin-right'  => '0px',
							'margin-bottom' => '0px',
							'margin-left'   => '0px',
							'units'         => 'px',
						),
						'required'       => array( 'logo_customization', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'logo_link' => array(
						'id'      => 'logo_link',
						'type'    => 'switch',
						'title'   => __( "Link logo to home", 'automotive' ),
						'default' => true,
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'default_header_image' => array(
						'id'    => 'default_header_image',
						'type'  => 'media',
						'title' => __( 'Default Header Image', 'automotive' ),
						'desc'  => __( 'This image will be shown if no header image is found.', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'default_header_listing_image' => array(
						'id'    => 'default_header_listing_image',
						'type'  => 'media',
						'title' => __( 'Default Listing Header Image', 'automotive' ),
						'desc'  => __( 'This image will be shown if no header image is found on the listings.', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'header_image_stretch' => array(
						'id'      => 'header_image_stretch',
						'type'    => 'switch',
						'title'   => __( "Stretch Header Image", 'automotive' ),
						'desc'    => __( "If enabled this will stretch the header image rather than repeat it.", "automotive" ),
						'default' => false,
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'no_header_area_default' => array(
						'id'      => 'no_header_area_default',
						'type'    => 'switch',
						'title'   => __( 'No Header Area Default', 'automotive' ),
						'desc'    => __( 'This will check off the "No header area" when creating new page. Note: any existing pages can have the "No header area" option disabled even with this option enabled.', 'automotive' ),
						'default' => false,
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_shadow' => array(
						'id'      => 'toolbar_shadow',
						'type'    => 'switch',
						'title'   => __( "Toolbar Shadow", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'header_shadow' => array(
						'id'      => 'header_shadow',
						'type'    => 'switch',
						'title'   => __( "Header Shadow", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_mobile_breakpoint' => array(
						'id'      => 'toolbar_mobile_breakpoint',
						'type'    => 'text',
						'title'   => __( 'Toolbar Items Mobile Breakpoint', 'automotive' ),
						'desc'    => __( 'Choose at what resolution the mobile toolbar item settings below take place', 'automotive' ),
						'default' => 768,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_mobile_classes' => array(
						'id'      => 'toolbar_mobile_classes',
						'type'    => 'switch',
						'title'   => __( "Toolbar Mobile Style", 'automotive' ),
						'desc'    => __( 'This lets you control if the top toolbar will break into 2 rows or a single row depending on how many toolbar items you want displayed.', 'automotive' ),
						'default' => true,
						'on'      => '1 Row',
						'off'     => '2 Rows',
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'section-toolbar-login-start' => array(
						'id'       => 'section-toolbar-login-start',
						'type'     => 'section',
						'title'    => __( 'Toolbar Login Options', 'automotive' ),
						'subtitle' => __( 'Customize the login link in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_login_show' => array(
						'id'      => 'toolbar_login_show',
						'type'    => 'switch',
						'title'   => __( "Show login", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_login_show_mobile' => array(
						'id'      => 'toolbar_login_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show login on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_login' => array(
						'id'       => 'toolbar_login',
						'type'     => 'text',
						'title'    => __( "Login", 'automotive' ),
						'default'  => __( 'Login', 'automotive' ),
						'required' => array( 'toolbar_login_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_login_link' => array(
						'id'       => 'toolbar_login_link',
						'type'     => 'select',
						'title'    => __( "Login Internal Link", 'automotive' ),
						'data'     => 'pages',
						'required' => array( 'toolbar_login_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_login_link_external' => array(
						'id'       => 'toolbar_login_link_external',
						'type'     => 'text',
						'title'    => __( "Login External Link", 'automotive' ),
						'required' => array( 'toolbar_login_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'section-toolbar-login-end' => array(
						'type'   => 'section',
						'id'     => 'section-toolbar-login-end',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),


					'toolbar-logout-section-start' => array(
						'title'    => __( 'Toolbar Logout Options', 'automotive' ),
						'type'     => 'section',
						'subtitle' => __( 'Customize the logout link in the top toolbar section, only shown when a user is logged in and replaces the above login link.', 'automotive' ),
						'id'       => 'toolbar-logout-section-start',
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),

					'toolbar_logout_show' => array(
						'id'      => 'toolbar_logout_show',
						'type'    => 'switch',
						'title'   => __( "Show logout", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_logout_show_mobile' => array(
						'id'      => 'toolbar_logout_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show logout on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_logout' => array(
						'id'       => 'toolbar_logout',
						'type'     => 'text',
						'title'    => __( "Logout", 'automotive' ),
						'default'  => __( 'Logout', 'automotive' ),
						'required' => array( 'toolbar_logout_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_logout_link' => array(
						'id'       => 'toolbar_logout_link',
						'type'     => 'select',
						'title'    => __( "Logout Internal Link", 'automotive' ),
						'data'     => 'pages',
						'required' => array( 'toolbar_logout_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_logout_link_external' => array(
						'id'       => 'toolbar_logout_link_external',
						'type'     => 'text',
						'title'    => __( "Logout External Link", 'automotive' ),
						'required' => array( 'toolbar_logout_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),

					'toolbar-logout-section-end' => array(
						'id'     => 'toolbar-logout-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),

					'toolbar-language-section-start' => array(
						'id'       => 'toolbar-language-section-start',
						'type'     => 'section',
						'title'    => __( 'Toolbar Language Options', 'automotive' ),
						'subtitle' => __( 'Customize the language link in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_language_show' => array(
						'id'      => 'toolbar_language_show',
						'type'    => 'switch',
						'title'   => __( "Show languages", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_language_show_mobile' => array(
						'id'      => 'toolbar_language_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show languages on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_languages' => array(
						'id'       => 'toolbar_languages',
						'type'     => 'text',
						'title'    => __( "Languages", 'automotive' ),
						'default'  => __( 'Languages', 'automotive' ),
						'required' => array( 'toolbar_language_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-language-section-end' => array(
						'id'     => 'toolbar-language-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-search-section-start' => array(
						'id'       => 'toolbar-search-section-start',
						'type'     => 'section',
						'title'    => __( 'Toolbar Search Options', 'automotive' ),
						'subtitle' => __( 'Customize the search area in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_search_show' => array(
						'id'      => 'toolbar_search_show',
						'type'    => 'switch',
						'title'   => __( "Show search", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_search_show_mobile' => array(
						'id'      => 'toolbar_search_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show search on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_search' => array(
						'id'       => 'toolbar_search',
						'type'     => 'text',
						'title'    => __( "Search", 'automotive' ),
						'default'  => __( 'Search', 'automotive' ),
						'required' => array( 'toolbar_search_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-search-section-end' => array(
						'id'     => 'toolbar-search-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-phone-section-start' => array(
						'id'       => 'toolbar-phone-section-start',
						'type'     => 'section',
						'title'    => __( 'Toolbar Phone Options', 'automotive' ),
						'subtitle' => __( 'Customize the phone number link in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_phone_show' => array(
						'id'      => 'toolbar_phone_show',
						'type'    => 'switch',
						'title'   => __( "Show phone", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_phone_show_mobile' => array(
						'id'      => 'toolbar_phone_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show phone on mobile", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_phone' => array(
						'id'       => 'toolbar_phone',
						'type'     => 'text',
						'title'    => __( "Phone", 'automotive' ),
						'default'  => __( 'Phone', 'automotive' ),
						'required' => array( 'toolbar_phone_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_phone_link' => array(
						'id'       => 'toolbar_phone_link',
						'type'     => 'text',
						'title'    => __( "Phone Link", 'automotive' ),
						'desc'     => __( "To link this as a phone number simply enter \"tel:\" with your phone number after (no spaces, only numbers)" ),
						'required' => array( 'toolbar_phone_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-phone-section-end' => array(
						'id'     => 'toolbar-phone-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-address-section-start' => array(
						'id'       => 'toolbar-address-section-start',
						'type'     => 'section',
						'title'    => __( 'Toolbar Address Options', 'automotive' ),
						'subtitle' => __( 'Customize the address link in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_address_show' => array(
						'id'      => 'toolbar_address_show',
						'type'    => 'switch',
						'title'   => __( "Show address", 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_address_show_mobile' => array(
						'id'      => 'toolbar_address_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show address on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_address' => array(
						'id'       => 'toolbar_address',
						'type'     => 'text',
						'title'    => __( "Address", 'automotive' ),
						'default'  => __( 'Address', 'automotive' ),
						'required' => array( 'toolbar_address_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_address_link' => array(
						'id'       => 'toolbar_address_link',
						'type'     => 'select',
						'title'    => __( "Address Internal Link", 'automotive' ),
						'data'     => 'pages',
						'required' => array( 'toolbar_address_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_address_link_external' => array(
						'id'       => 'toolbar_address_link_external',
						'type'     => 'text',
						'title'    => __( "Address External Link", 'automotive' ),
						'required' => array( 'toolbar_address_show', 'equals', 1 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-address-section-end' => array(
						'id'     => 'toolbar-address-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),

					'toolbar-social-section-start' => array(
						'id'       => 'toolbar-social-section-start',
						'type'     => 'section',
						'title'    => __('Toolbar Social Icon Options', 'automotive'),
						'subtitle' => __( 'Customize the social icons in the top toolbar section.', 'automotive' ),
						'indent'   => true,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_social_show' => array(
						'id'      => 'toolbar_social_show',
						'type'    => 'switch',
						'title'   => __( "Show social icons", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_social_show_mobile' => array(
						'id'      => 'toolbar_social_show_mobile',
						'type'    => 'switch',
						'title'   => __( "Show social icons on mobile", 'automotive' ),
						'default' => false,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar_social_position' => array(
						'id'      => 'toolbar_social_position',
						'type'    => 'switch',
						'title'   => __( "Social Icon Display", 'automotive' ),
						'subtitle' => __("Choose which side of the toolbar the social icons will show up on"),
						'default' => false,
						'on'      => __( 'Left', 'automotive' ),
						'off'     => __( 'Right', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'toolbar-social-section-end' => array(
						'id'     => 'toolbar-social-section-end',
						'type'   => 'section',
						'indent' => false,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),

					'header_top' => array(
						'id'      => 'header_top',
						'type'    => 'switch',
						'title'   => __( 'Top toolbar display', 'automotive' ),
						'desc'    => __( 'Show or hide the top header area.', 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'header_resize' => array(
						'id'      => 'header_resize',
						'type'    => 'switch',
						'title'   => __( 'Header Resize', 'automotive' ),
						'desc'    => __( 'If on the header will resize after scrolling, or else it will stay the same size.', 'automotive' ),
						'default' => 1,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'sticky_header' => array(
						'id'       => 'sticky_header',
						'type'     => 'switch',
						'title'    => __( "Sticky Header", 'automotive' ),
						'desc'     => __('If disabled this will disable the header being stuck to the top of the users viewport while scrolling down the page.'),
						'default'  => 1,
						'required' => array( 'header_resize', 'equals', 0 ),
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'header_resize_mobile' => array(
						'id'      => 'header_resize_mobile',
						'type'    => 'switch',
						'title'   => __( 'Header Mobile Resize', 'automotive' ),
						'desc'    => __( 'If this option is off it will not resize the header if the user is using a mobile device.', 'automotive' ),
						'default' => 1,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
					'push_mobile_slideshow_down' => array(
						'id'      => 'push_mobile_slideshow_down',
						'type'    => 'switch',
						'title'   => __( 'Push Slider Under Header on Mobile', 'automotive' ),
						'desc'    => __( 'If this option is on it will push the slider underneath the header so users on mobile can see the full slideshow.', 'automotive' ),
						'default' => 0,
					),
					'mobile_slideshow_down_amount' => array(
						'id'       => 'mobile_slideshow_down_amount',
						'type'     => 'text',
						'title'    => __( "Amount to push down slider (px)", 'automotive' ),
						'validate' => 'numeric',
						'default'  => __( '98', 'automotive' ),
						'required' => array( 'push_mobile_slideshow_down', 'equals', 1 )
					),
					'breadcrumb_functionality' => array(
						'id'      => 'breadcrumb_functionality',
						'type'    => 'switch',
						'desc'    => __( 'Enable or disable the breadcrumb functionality.', 'automotive' ),
						'title'   => __( 'Breadcrumbs', 'automotive' ),
						'default' => 1,
						'on'      => __( "Enabled", "listings" ),
						'off'     => __( "Disabled", "listings" )
					),
					'breadcrumb_style' => array(
						'id'       => 'breadcrumb_style',
						'type'     => 'switch',
						'title'    => __( 'Breadcrumb Style', 'automotive' ),
						'desc'     => __( 'If blog is chosen it will show blog link in breadcrumbs, otherwise it will show all the categories the post/page is tagged in.', 'automotive' ),
						'default'  => 1,
						'on'       => __( "Blog", "listings" ),
						'off'      => __( "Categories", "listings" ),
						'required' => array( 'breadcrumb_functionality', 'equals', 1 )
					),
					'languages_dropdown' => array(
						'id'      => 'languages_dropdown',
						'type'    => 'switch',
						'title'   => sprintf( __( 'Languages (WPML is %s)', 'automotive' ), ( function_exists( "icl_get_home_url" ) ? 'Active' : 'Not Active' ) ),
						'desc'    => __( 'Display a dropdown of available languages in the header. Only works with WPML', 'automotive' ),
						'default' => 1,
						'optional_required_field' => array('header_builder_enabled', '=', '0')
					),
				),
				'icon'   => 'fa fa-header',
			);
			$footer_settings    = array(
				'title'  => __( 'Footer Settings', 'automotive' ),
				'fields' => array(
					'footer_widget_spots' => array(
						'desc'     => __( 'You can create different footer widget areas for different pages.', 'automotive' ),
						'id'       => 'footer_widget_spots',
						'type'     => 'multi_text',
						'add_text' => __( 'Add another footer', 'automotive' ),
						'title'    => __( 'Multiple Footer areas', 'automotive' ),
					),
					'footer_logo' => array(
						'desc'    => __( 'Show or hide the footer logo.', 'automotive' ),
						'id'      => 'footer_logo',
						'type'    => 'switch',
						'title'   => __( 'Footer Logo', 'automotive' ),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
					'footer_logo_image' => array(
						'desc'  => 'If a logo here isn\'t set it will default to the one from Header Settings.',
						'type'  => 'media',
						'id'    => 'footer_logo_image',
						'url'   => true,
						'title' => 'Footer Logo',
						'required' => array('footer_logo', 'equals', 1)
					),
					'footer_text' => array(
						'desc'    => __( 'You can use the following shortcodes in your footer text', 'automotive' ) . ': {wp-link} {theme-link} {loginout-link} {blog-title} {blog-link} {the-year}',
						'id'      => 'footer_text',
						'type'    => 'editor',
						'title'   => __( 'Footer Text', 'automotive' ),
						'default' => 'Powered by {wp-link}. Built with {theme-link}.',
					),
					'footer_icons' => array(
						'desc'    => __( 'Show or hide the footer icons.', 'automotive' ),
						'id'      => 'footer_icons',
						'type'    => 'switch',
						'title'   => __( 'Footer Icons', 'automotive' ),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
					'footer_menu' => array(
						'desc'    => __( 'Show or hide the footer menu.', 'automotive' ),
						'id'      => 'footer_menu',
						'type'    => 'switch',
						'title'   => __( 'Footer Menu', 'automotive' ),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
					'footer_widgets' => array(
						'desc'    => __( 'Show or hide the footer widgets.', 'automotive' ),
						'id'      => 'footer_widgets',
						'type'    => 'switch',
						'title'   => __( 'Footer Widgets', 'automotive' ),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
					'footer_copyright' => array(
						'desc'    => __( 'Show or hide the footer copyright.', 'automotive' ),
						'id'      => 'footer_copyright',
						'type'    => 'switch',
						'title'   => __( 'Footer Copyright', 'automotive' ),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
				),
				'icon'   => 'fa fa-list-alt'
			);
			$social_settings    = array(
				'title'  => __( 'Social Settings', 'automotive' ),
				'fields' => array(
					'social_network_links' => array(
						'id'      => 'social_network_links',
						'type'    => 'sorter',
						'title'   => __( 'Footer Social Icons', 'automotive' ),
						'desc'    => __( 'Choose which social networks are displayed and edit where they link to.', 'automotive' ),
						'options' => array(
							'enabled'  => $social_options,
							'disabled' => array()
						)
					),
				),
				'icon'   => 'fa fa-share-alt',
			);
			$contact_settings   = array(
				'title'  => __( 'Contact Settings', 'automotive' ),
				'fields' => array(
					'contact_email' => array(
						'id'      => 'contact_email',
						'type'    => 'text',
						'title'   => __( 'Contact Email', 'automotive' ),
						'desc'    => __( 'This email will be used to forward the contact form mail to it.', 'automotive' ),
						'default' => get_option( 'admin_email' )
					),
					'gdpr_form' => array(
						'id'      => 'gdpr_form',
						'type'    => 'switch',
						'title'   => __( 'GDPR Compliance', 'automotive' ),
						'desc'    => __( 'If enabled this will display a GDPR checkbox on the contact form.', 'automotive' ),
						'default' => false,
						'on'      => __( "Enabled", "automotive" ),
						'off'     => __( "Disabled", "automotive" )
					),
					'gdpr_form_text' => array(
						'id'       => 'gdpr_form_text',
						'type'     => 'text',
						'title'    => __( 'GDPR Compliance Text', 'automotive' ),
						'desc'     => __( 'This message will be displayed next to a checkbox on the contact form.', 'automotive' ),
						'default'  => 'You agree by submitting this form that you are sending us your data',
						'required' => array( 'gdpr_form', 'equals', 1 )
					)
				),
				'icon'   => 'fa fa-envelope',
			);
			$styling_settings   = array(
				'title'  => __( 'Custom Styling', 'automotive' ),
				'icon'   => 'fa fa-pencil-square-o',
				'fields' => array(
					'theme_color_scheme' => array(
						'id'       => 'theme_color_scheme',
						'type'     => 'color_scheme',
						'title'    => __( 'Color Scheme', 'automotive' ),
						'subtitle' => __( 'Save and load color schemes', 'automotive' ),
						'desc'     => '',
						'output'   => false,
						'compiler' => false,
						'simple'   => false,
						'options'  => array(
							'show_input'             => true,
							'show_initial'           => true,
							'show_alpha'             => true,
							'show_palette'           => true,
							'show_palette_only'      => false,
							'show_selection_palette' => true,
							'max_palette_size'       => 10,
							'allow_empty'            => true,
							'clickout_fires_change'  => false,
							'choose_text'            => __( 'Choose', 'automotive' ),
							'cancel_text'            => __( 'Cancel', 'automotive' ),
							'show_buttons'           => true,
							'use_extended_classes'   => true,
							'palette'                => null,  // show default
						),
						'groups'  => Automotive_CSS_Composer()->get_color_scheme_groups(),
						'default' => Automotive_CSS_Composer()->get_color_scheme()
					),
					'body_font' => array(
						'id'         => 'body_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the body font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'Body Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'units'       => 'px',
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '14',
							'line-height' => '24',
							'color'       => '#2D2D2D'
						),
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'logo_top_font' => array(
						'id'      => 'logo_top_font',
						'type'    => 'typography',
						'desc'    => __( 'Set the top logo font using Google\'s web font service.', 'automotive' ),
						'title'   => __( 'Top Logo Font', 'automotive' ),
						'default' => array(
							'units'       => 'px',
							'font-family' => 'Yellowtail',
							'font-weight' => '400',
							'font-size'   => '40',
							'line-height' => '20',
							'color'       => '#FFF'
						),
						'subsets' => true
					),
					'alternate_font' => array(
						'id'      => 'alternate_font',
						'type'    => 'typography',
						'desc'    => __( 'Set the alternate font using Google\'s web font service.', 'automotive' ),
						'title'   => __( 'Alternate Font', 'automotive' ),
						'default' => array(
							'units'       => 'px',
							'font-family' => 'Yellowtail',
							'font-weight' => '400',
							'font-size'   => '45',
							'line-height' => '30',
							'color'       => '#c7081b'
						),
						'subsets' => true
					),
					'logo_top_font_scroll' => array(
						'id'          => 'logo_top_font_scroll',
						'type'        => 'typography',
						'desc'        => __( 'Set the top logo font size when the user has scrolled and the header has shrunk.', 'automotive' ),
						'title'       => __( 'Top Logo Scroll Font', 'automotive' ),
						'default'     => array(
							'units'       => 'px',
							'font-size'   => '34',
							'line-height' => '20'
						),
						'subsets'     => false,
						'font-style'  => false,
						'font-weight' => false,
						'font-family' => false,
						'text-align'  => false,
						'color'       => false
					),
					'logo_bottom_font' => array(
						'id'      => 'logo_bottom_font',
						'type'    => 'typography',
						'desc'    => __( 'Set the bottom logo font using Google\'s web font service.', 'automotive' ),
						'title'   => __( 'Bottom Logo Font', 'automotive' ),
						'default' => array(
							'units'       => 'px',
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '12',
							'line-height' => '20',
							'color'       => '#FFF'
						),
						'subsets' => true
					),
					'logo_bottom_font_scroll' => array(
						'id'          => 'logo_bottom_font_scroll',
						'type'        => 'typography',
						'desc'        => __( 'Set the bottom logo font size when the user has scrolled and the header has shrunk.', 'automotive' ),
						'title'       => __( 'Bottom Logo Scroll Font', 'automotive' ),
						'default'     => array(
							'units'       => 'px',
							'font-size'   => '8',
							'line-height' => '20'
						),
						'subsets'     => false,
						'font-style'  => false,
						'font-weight' => false,
						'font-family' => false,
						'text-align'  => false,
						'color'       => false
					),
					'main_menu_font' => array(
						'id'          => 'main_menu_font',
						'type'        => 'typography',
						'desc'        => __( 'Set the main menu font using Google\'s web font service.', 'automotive' ),
						'title'       => __( 'Main Menu Font', 'automotive' ),
						'fonts'       => array(),
						'default'     => array(
							'units'       => 'px',
							'font-family' => 'Open Sans',
							'font-weight' => '700',
							'font-size'   => '14',
							'color'       => '#FFF'
						),
						'all_styles'  => true,
						'subsets'     => true,
						'text-align'  => false,
						'line-height' => false,
						'color'       => false
					),
					'main_dropdown_font' => array(
						'id'          => 'main_dropdown_font',
						'type'        => 'typography',
						'desc'        => __( 'Set the main menu dropdown fonts using Google\'s web font service.', 'automotive' ),
						'title'       => __( 'Main Menu Dropdown Font', 'automotive' ),
						'fonts'       => array(),
						'default'     => array(
							'units'       => 'px',
							'font-family' => 'Open Sans',
							'font-weight' => '600',
							'font-size'   => '13',
							'color'       => '#FFF'
						),
						'all_styles'  => true,
						'subsets'     => true,
						'text-align'  => false,
						'line-height' => false,
						'color'       => false
					),
					'main_menu_breakpoint' => array(
						'id'       => 'main_menu_breakpoint',
						'type'     => 'text',
						'desc'     => __( "This controls the mobile break point for the mobile menu button in pixels (default is 767).", "automotive" ),
						'validate' => 'numeric',
						'title'    => __( "Mobile Menu Breakpoint", "automotive" ),
						'default'  => 768
					),
					'button_border_radius' => array(
						'desc'    => __( 'Adjust how the corners appear on buttons in the theme.', 'automotive' ),
						'type'    => 'switch',
						'off'     => __( 'Rounded', 'automotive' ),
						'on'      => __( 'Straight', 'automotive' ),
						'id'      => 'button_border_radius',
						'title'   => __( 'Button Corners', 'automotive' ),
						'default' => false,
					),
					'dropdown_menu_shadow' => array(
						'desc'    => __( 'Enable or disable the shadow around the dropdown menus.', 'automotive' ),
						'type'    => 'switch',
						'off'     => __( 'Off', 'automotive' ),
						'on'      => __( 'On', 'automotive' ),
						'id'      => 'dropdown_menu_shadow',
						'title'   => __( 'Dropdown menu shadow', 'automotive' ),
						'default' => true
					),
					'external_css_styles' => array(
						'id'         => 'external_css_styles',
						'type'       => 'multi_text',
						'title'      => __( 'External CSS Styles', 'automotive' ),
						'validate'   => 'url',
						'desc'       => __( 'Link external CSS styles from other sites to be loaded on the frontend.', 'automotive' ),
						'show_empty' => false
					),
					'custom_css' => array(
						'desc'  => __( 'Quickly add some custom CSS to your theme.', 'automotive' ),
						'id'    => 'custom_css',
						'type'  => 'ace_editor',
						'title' => __( 'Custom CSS', 'automotive' ),
						'mode'  => 'css',
						'theme' => 'chrome'
					),
					'external_js_scripts' => array(
						'id'         => 'external_js_scripts',
						'type'       => 'multi_text',
						'title'      => __( 'External JS Scripts', 'automotive' ),
						'validate'   => 'url',
						'desc'       => __( 'Link external JS scripts from other sites to be loaded on the frontend.', 'automotive' ),
						'show_empty' => false
					),
					'custom_js' => array(
						'desc'  => __( 'Quickly add some custom JS to your theme. <br><br> Please <b>do not</b> include the &lt;script&gt; tags.', 'automotive' ),
						'id'    => 'custom_js',
						'type'  => 'ace_editor',
						'title' => __( 'Custom JS', 'automotive' ),
						'mode'  => 'javascript',
						'theme' => 'chrome'
					),
					'heading_accordion' => array(
						'id'       => 'heading_accordion',
						'type'     => 'accordion',
						'title'    => __( 'Heading Font Styles', 'automotive' ),
						'subtitle' => __( 'Adjust the H1 - H6 font styles', 'automotive' ),
						'position' => 'start',
					),
					'h1_font' => array(
						'id'         => 'h1_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H1 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H1 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '72',
							'line-height' => '80',
							'color'       => '#2D2D2D'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'h2_font' => array(
						'id'         => 'h2_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H2 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H2 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '600',
							'font-size'   => '32',
							'line-height' => '32',
							'color'       => '#2D2D2D'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'h3_font' => array(
						'id'         => 'h3_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H3 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H3 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '800',
							'font-size'   => '22',
							'line-height' => '22',
							'color'       => '#C7081B'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'h4_font' => array(
						'id'         => 'h4_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H4 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H4 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '24',
							'line-height' => '26',
							'color'       => '#C7081B'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'h5_font' => array(
						'id'         => 'h5_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H5 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H5 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '20',
							'line-height' => '22',
							'color'       => '#2D2D2D'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'h6_font' => array(
						'id'         => 'h6_font',
						'type'       => 'typography',
						'desc'       => __( 'Set the H6 font using Google\'s web font service.', 'automotive' ),
						'title'      => __( 'H6 Font', 'automotive' ),
						'fonts'      => array(),
						'default'    => array(
							'font-family' => 'Open Sans',
							'font-weight' => '400',
							'font-size'   => '16',
							'line-height' => '17',
							'color'       => '#2D2D2D'
						),
						'units'      => 'px',
						'all_styles' => true,
						'subsets'    => true,
						'text-align' => false
					),
					'heading_accordion_end' => array(
						'id'       => 'heading_accordion_end',
						'type'     => 'accordion',
						'position' => 'end'
					),
				),
			);

			$page_settings  						= array(
				'title' => __( 'Page Settings', 'automotive' ),
				'icon'  => 'fa fa-file-text-o'
			);
			$page_404_settings  				= array(
				'title'      => __( '404 Page', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'fourohfour_page_image' => array(
						'id'    => 'fourohfour_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'fourohfour_page_title' => array(
						'id'      => 'fourohfour_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'default' => __( 'Error 404: File not found.', 'automotive' )
					),
					'fourohfour_page_secondary_title' => array(
						'id'      => 'fourohfour_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'default' => __( 'That being said, we will give you an amazing deal for the trouble.', 'automotive' )
					),
					'fourohfour_page_breadcrumb' => array(
						'id'      => 'fourohfour_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'default' => '404'
					),
					'fourohfour_page_alert' => array(
						'id'      => 'fourohfour_page_alert',
						'type'    => 'switch',
						'title'   => __( "Permalink Message", 'automotive' ),
						'desc'    => __("This message only displays on 404 pages for administrators explaining how to regenerate permalinks if your listings aren't displaying properly."),
						'default' => true,
						'on'      => __( "Show", "automotive" ),
						'off'     => __( "Hide", "automotive" )
					),
					'fourohfour_page_sidebar' => array(
						'id'      => 'fourohfour_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'fourohfour_page_sidebar_position' => array(
						'id'      => 'fourohfour_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					),
					'fourohfour_page_content' => array(
						'id'      => 'fourohfour_page_content',
						'type'    => 'select',
						'title'   => __( "Page Content", 'automotive' ),
						'default' => '',
						'data'    => 'pages',
						'desc'    => __("Customize the 404 page content", "automotive")
					)
				),
			);
			$page_blog_settings 				= array(
				'title'      => __( 'Blog', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'blog_primary_title' => array(
						'id'    => 'blog_primary_title',
						'type'  => 'text',
						'desc'  => __( 'This title shows up in the header section on all blog postings and the blog page.', 'automotive' ),
						'title' => __( 'Blog Listing Titles', 'automotive' ),
					),
					'blog_secondary_title' => array(
						'id'   => 'blog_secondary_title',
						'type' => 'text',
						'desc' => __( 'This secondary title displays under the previous title in the header on blog pages.', 'automotive' ),
					),
					'blog_post_details' => array(
						'id'      => 'blog_post_details',
						'type'    => 'switch',
						'title'   => __( 'Blog Post Details', 'automotive' ),
						'default' => true,
						'on'      => __( 'Show', 'automotive' ),
						'off'     => __( 'Hide', 'automotive' )
					),
					'blog_post_heading' => array(
						'id'      => 'blog_post_heading',
						'desc'    => __( 'Toggle the blog post heading type to an h1 tag on single blog posts.', 'automotive' ),
						'type'    => 'switch',
						'title'   => __( 'Blog Post H1', 'automotive' ),
						'default' => false,
						'on'      => __( "Enable", "automotive" ),
						'off'     => __( "Disable", "automotive" )
					),
				),
			);
			$page_blog_search_settings 	= array(
				'title'      => __( 'Blog Search', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'post_layout' => array(
						'id'      => 'post_layout',
						'title'   => __( 'Post Layout', 'automotive' ),
						'desc'    => __( 'Choose which layout the blog posts will have', 'automotive' ),
						'type'    => 'button_set',
						'id'      => 'post_layout',
						'options' => array(
							'fullwidth' => __( 'Fullwidth', 'automotive' ),
							'boxed' => __( 'Boxed', 'automotive' ),
						),
						'default' => 'fullwidth'
					),
					'listing_display' => array(
						'id'      => 'listing_display',
						'type'    => 'switch',
						'title'   => __( 'Listing Display', 'automotive' ),
						'desc'    => __( 'If enabled this will show listings as they appear on inventory pages while searching the blog otherwise it will display in a blog post preview layout.', 'automotive' ),
						'default' => false,
						'on'      => __( 'Enable', 'automotive' ),
						'off'     => __( 'Disable', 'automotive' )
					),
					'featured_image_link' => array(
						'id'      => 'featured_image_link',
						'type'    => 'switch',
						'title'   => __( 'Featured Image Link', 'automotive' ),
						'default' => false,
						'on'      => __( 'Post', 'automotive' ),
						'off'     => __( 'Image', 'automotive' )
					),
				),
			);
			$page_category_settings 		= array(
				'title'      => __( 'Category Page', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'category_page_image' => array(
						'id'    => 'category_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'category_page_title' => array(
						'id'      => 'category_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of category term', 'automotive' ),
						'default' => 'Category: {query}'
					),
					'category_page_secondary_title' => array(
						'id'      => 'category_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of category term', 'automotive' ),
						'default' => 'Posts related to {query}'
					),
					'category_page_breadcrumb' => array(
						'id'      => 'category_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of category term', 'automotive' ),
						'default' => 'Category: {query}'
					),
					'category_page_sidebar' => array(
						'id'      => 'category_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'category_page_sidebar_position' => array(
						'id'      => 'category_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					)
				),
			);

			$page_search_settings    		= array(
				'title'      => __( 'Search Page', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'search_page_image' => array(
						'id'    => 'search_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'search_page_title' => array(
						'id'      => 'search_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of search term', 'automotive' ),
						'default' => 'Search'
					),
					'search_page_secondary_title' => array(
						'id'      => 'search_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of search term', 'automotive' ),
						'default' => 'Search results for: {query}'
					),
					'search_page_breadcrumb' => array(
						'id'      => 'search_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of search term', 'automotive' ),
						'default' => 'Search results: {query}'
					),
					'search_page_sidebar' => array(
						'id'      => 'search_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'search_page_sidebar_position' => array(
						'id'      => 'search_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					)
				),
			);

			$page_tag_settings       		= array(
				'title'      => __( 'Tag Page', 'automotive' ),
				'subsection' => true,
				'fields'     => array(
					'tag_page_image' => array(
						'id'    => 'tag_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'tag_page_title' => array(
						'id'      => 'tag_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => 'Tag: {query}'
					),
					'tag_page_secondary_title' => array(
						'id'      => 'tag_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => 'Posts related to {query}'
					),
					'tag_page_breadcrumb' => array(
						'id'      => 'tag_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => 'Tag: {query}'
					),
					'tag_page_sidebar' => array(
						'id'      => 'tag_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'tag_page_sidebar_position' => array(
						'id'      => 'tag_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					)
				),
			);

			$page_woocommerce_settings 	= array(
				'title'      => __( 'WooCommerce', 'automotive' ),
				'subsection' => true,
				'fields'     => array(

					'info_success_woocommerce' => array(
						'id'    => 'info_success_woocommerce',
						'type'  => 'info',
						'style' => ( $is_woocommerce_active ? 'success' : 'critical' ),
						'title' => sprintf( __( 'WooCommerce is %s', 'automotive' ), ( $is_woocommerce_active ? __( 'Active', 'automotive' ) : __( 'Not Active', 'automotive' ) ) ),
						'desc'  => ( ! $is_woocommerce_active ? sprintf( __( 'For these settings to take effect you must install and activate %sWooCommerce%s', 'automotive' ), "<a href='https://en-ca.wordpress.org/plugins/woocommerce/' target='_blank'>", "</a>" ) : "" )
					),

					'woocommerce_fullwidth' => array(
						'desc'    => __( 'If this is enabled it will display WooCommerce pages using a fullwidth layout.', 'automotive' ),
						'id'      => 'woocommerce_fullwidth',
						'type'    => 'switch',
						'title'   => __( 'WooCommerce Fullwidth Layout', 'automotive' ),
						'default' => 1,
					),
					'woocommerce_cart' => array(
						'desc'    => __( 'If this is enabled it will display a cart icon beside the Login label', 'automotive' ),
						'id'      => 'woocommerce_cart',
						'type'    => 'switch',
						'title'   => __( 'WooCommerce Toolbar Cart', 'automotive' ),
						'default' => 1,
					),
					'woocommerce_cart_link' => array(
						'id'       => 'woocommerce_cart_link',
						'type'     => 'select',
						'title'    => __( "WooCommerce Toolbar Cart Link", 'automotive' ),
						'data'     => 'pages',
						'required' => array( 'woocommerce_cart', 'equals', 1 )
					),
					'woocommerce_menu_cart' => array(
						'desc'    => __( 'If this is enabled it will include the scripts required for the cart dropdown in the main menu. ', 'automotive' ),
						'id'      => 'woocommerce_menu_cart',
						'type'    => 'switch',
						'title'   => __( 'WooCommerce Menu Cart', 'automotive' ),
						'default' => false,
					),

					'woocommerce-cart-info' => array(
						'id'       => 'woocommerce-cart-info',
						'type'     => 'info',
						'style'    => 'warning',
						'title'    => __( "To add the cart dropdown", "automotive" ),
						'desc'     => __( "Under Appearance >> Menus add a menu item to your cart with the navigation label \"woocommerce-cart\"" ),
						'required' => array( 'woocommerce_menu_cart', 'equals', '1' )
					),

					'woo_shop_layout' => array(
						'id'      => 'woo_shop_layout',
						'type'    => 'switch',
						'title'   => __( 'WooCommerce Product Layout', 'automotive' ),
						'default' => true,
						'on'      => __( 'Style 1', 'automotive' ),
						'off'     => __( 'Style 2', 'automotive' )
					),

					'woo_dropdown_categories' => array(
						'id'      => 'woo_dropdown_categories',
						'type'    => 'switch',
						'title'   => __( 'WooCommerce Dropdown Categories', 'automotive' ),
						'desc'    => __( 'If enabled this will change the WooCommerce categories widget lists to an icon dropdown.', 'automotive' ),
						'default' => false,
						'on'      => __( 'On', 'automotive' ),
						'off'     => __( 'Off', 'automotive' )
					),

					'section-woo-cat-start' => array(
						'title'  => __( 'WooCommerce Category', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-cat-start',
						'indent' => true
					),

					'woo_category_page_image' => array(
						'id'    => 'woo_category_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'woo_category_page_title' => array(
						'id'      => 'woo_category_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => '{query}'
					),
					'woo_category_page_secondary_title' => array(
						'id'      => 'woo_category_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => ''
					),
					'woo_category_page_breadcrumb' => array(
						'id'      => 'woo_category_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => '{query}'
					),
					'woo_category_page_sidebar' => array(
						'id'      => 'woo_category_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'woo_category_page_sidebar_position' => array(
						'id'      => 'woo_category_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					),
					'section-woo-cat-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-cat-end',
						'indent' => false
					),

					'section-woo-tag-start' => array(
						'title'  => __( 'WooCommerce Tag', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-tag-start',
						'indent' => true
					),
					'woo_tag_page_image' => array(
						'id'    => 'woo_tag_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'woo_tag_page_title' => array(
						'id'      => 'woo_tag_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => '{query}'
					),
					'woo_tag_page_secondary_title' => array(
						'id'      => 'woo_tag_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => ''
					),
					'woo_tag_page_breadcrumb' => array(
						'id'      => 'woo_tag_page_breadcrumb',
						'type'    => 'text',
						'title'   => __( "Breadcrumb", 'automotive' ),
						'desc'    => __( 'You are able to use {query} in place the of tag term', 'automotive' ),
						'default' => '{query}'
					),
					'woo_tag_page_sidebar' => array(
						'id'      => 'woo_tag_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'woo_tag_page_sidebar_position' => array(
						'id'      => 'woo_tag_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					),
					'section-woo-tag-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-tag-end',
						'indent' => false
					),

					'section-woo-shop-start' => array(
						'title'  => __( 'WooCommerce Shop', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-shop-start',
						'indent' => true
					),
					'woo_shop_page_image' => array(
						'id'    => 'woo_shop_page_image',
						'type'  => 'media',
						'title' => __( "Header Image", 'automotive' )
					),
					'woo_shop_page_title' => array(
						'id'      => 'woo_shop_page_title',
						'type'    => 'text',
						'title'   => __( "Main Title", 'automotive' ),
						'default' => 'Shop'
					),
					'woo_shop_page_secondary_title' => array(
						'id'      => 'woo_shop_page_secondary_title',
						'type'    => 'text',
						'title'   => __( "Secondary Title", 'automotive' ),
						'default' => ''
					),
					'woo_shop_page_sidebar' => array(
						'id'      => 'woo_shop_page_sidebar',
						'type'    => 'select',
						'title'   => __( "Sidebar", 'automotive' ),
						'default' => '',
						'data'    => 'sidebar'
					),
					'woo_shop_page_sidebar_position' => array(
						'id'      => 'woo_shop_page_sidebar_position',
						'type'    => 'select',
						'title'   => __( "Sidebar Position", 'automotive' ),
						'default' => '',
						'options' => array(
							"left"  => __( "Left", "automotive" ),
							"right" => __( "Right", "automotive" )
						)
					),
					'section-woo-shop-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-shop-end',
						'indent' => false
					),

					'section-woo-cart-start' => array(
						'title'  => __( 'WooCommerce Cart', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-cart-start',
						'indent' => true
					),
					'woo_shop_cart_empty_title' => array(
						'id'      => 'woo_shop_cart_empty_title',
						'type'    => 'text',
						'title'   => __( "Empty Cart Title", 'automotive' ),
						'default' => 'Your Cart Is Currently Empty'
					),
					'woo_shop_cart_empty_secondary_title' => array(
						'id'      => 'woo_shop_cart_empty_secondary_title',
						'type'    => 'text',
						'title'   => __( "Empty Cart Secondary Title", 'automotive' ),
						'default' => 'Start your shop at Automotive today!'
					),

					'section-woo-cart-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-cart-end',
						'indent' => false
					),

					'section-woo-product-start' => array(
						'title'  => __( 'WooCommerce Single Product', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-product-start',
						'indent' => true
					),

					'woo_price_switch' => array(
						'id'      => 'woo_price_switch',
						'title'   => 'Discount Price Display',
						'desc'    => __( 'Choose whether the discounted price should be displayed before or after the current price.', 'automotive' ),
						'type'    => 'switch',
						'default' => false,
						'on'      => __( 'Before', 'automotive' ),
						'off'     => __( 'After', 'automotive' )
					),

					'woo_product_desc_related' => array(
						'id'      => 'woo_product_desc_related',
						'type'    => 'image_select',
						'title'   => __( 'Description and Related Size', 'automotive' ),
						'default' => 'half',
						'options' => array(
							'full' => array(
								'img' => trailingslashit( get_template_directory_uri() ) . 'images/woocommerce-full.png',
							),
							'half' => array(
								'img' => trailingslashit( get_template_directory_uri() ) . 'images/woocommerce-half.png'
							)
						)
					),

					'section-woo-product-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-product-end',
						'indent' => false
					),


					'section-woo-checkout-start' => array(
						'title'  => __( 'WooCommerce Checkout', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-woo-checkout-start',
						'indent' => true
					),

					'woo_checkout_overview_width' => array(
						'id'      => 'woo_checkout_overview_width',
						'title'   => 'Checkout Order Overview Size',
						'desc'    => __( 'Control the display of the "Your Order" area when checking out.', 'automotive' ),
						'type'    => 'switch',
						'default' => false,
						'on'      => __( 'Fullwidth', 'automotive' ),
						'off'     => __( 'Sidebar', 'automotive' )
					),

					'section-woo-checkout-end' => array(
						'type'   => 'section',
						'id'     => 'section-woo-checkout-end',
						'indent' => false
					),
				),
			);

			$microdata_settings = array(
				'title'  => __( 'Microdata Settings', 'automotive' ),
				'fields' => array(
					'section-microdata-start' => array(
						'title'  => __( 'Blog Post', 'automotive' ),
						'type'   => 'section',
						'id'     => 'section-microdata-start',
						'indent' => true
					),

					'blog_post_publisher' => array(
						'id'      => 'blog_post_publisher',
						'type'    => 'text',
						'title'   => __( "Publisher", 'automotive' ),
						'default' => ''
					),

					'blog_post_publisher_logo' => array(
						'id'    => 'blog_post_publisher_logo',
						'type'  => 'media',
						'title' => __( "Publisher Logo", 'automotive' ),
						'desc'  => __( 'Logos should have a wide aspect ratio, not a square icon and should be no wider than 600px and no taller than 60px.', 'automotive' )
					),

					'section-microdata-end' => array(
						'type'   => 'section',
						'id'     => 'section-microdata-end',
						'indent' => false
					),
				)
			);

			$update_settings    = array(
				'title'  => __( 'Update Settings', 'automotive' ),
				'fields' => array(
					'themeforest_envato_auth' => array(
						'id'   => 'themeforest_envato_auth',
						'type' => 'envato_auth',
						'title' => 'Themeforest Automatic Updates'
					),
					array(
						'id'      => 'disable_bundled_plugin_updater',
						'type'    => 'switch',
						'title'   => 'Disable Bundled Plugin Updates',
						'desc'    => __( 'If you have purchased your own license for the bundled plugins you will need to enable this setting.', 'automotive' ),
						'default' => false,
						'on'      => __( 'Enabled', 'automotive' ),
						'off'     => __( 'Disabled', 'automotive' )
					),
				),
			);
			$import_settings    = array(
				'title'  => __( "Import / Export", "automotive" ),
				'class'  => 'custom_import',
				'fields' => array(
					'opt-import-export' => array(
						'id'         => 'opt-import-export',
						'type'       => 'import_export',
						'title'      => __( 'Import Export', 'automotive' ),
						'subtitle'   => __( 'Save and restore your Redux options', 'automotive' ),
						'full_width' => true,
					),
				),
				'icon'   => 'el-icon-refresh'
			);

			// redux requires index based keys but associate is easier to work with
			// dealing with wp filters, so we just reset the keys before redux reads them
			$sections = apply_filters('automotive_theme_options', array(
				'theme_settings'   => apply_filters('automotive_theme_general_settings', $general_settings),
				'header_settings'  => apply_filters('automotive_theme_header_settings', $header_settings),
				'footer_settings'  => apply_filters('automotive_theme_footer_settings', $footer_settings),
				'social_settings'  => apply_filters('automotive_theme_social_settings', $social_settings),
				'contact_settings' => apply_filters('automotive_theme_contact_settings', $contact_settings),
				'styling_settings' => apply_filters('automotive_theme_styling_settings', $styling_settings),
				'page_settings'    => apply_filters('automotive_theme_page_settings', $page_settings),

				'404_settings'           => apply_filters('automotive_theme_page_404_settings', $page_404_settings),
				'blog_settings'          => apply_filters('automotive_theme_page_blog_settings', $page_blog_settings),
				'blog_search_settings'   => apply_filters('automotive_theme_page_blog_search_settings', $page_blog_search_settings),
				'page_category_settings' => apply_filters('automotive_theme_page_category_settings', $page_category_settings),
				'page_search_settings'   => apply_filters('automotive_theme_page_search_settings', $page_search_settings),
				'page_tag_settings'      => apply_filters('automotive_theme_page_tag_settings', $page_tag_settings),
				'woocommerce_settings'   => apply_filters('automotive_theme_page_woocommerce_settings', $page_woocommerce_settings),

				'microdata_settings' => apply_filters('automotive_theme_microdata_settings', $microdata_settings),
				'update_settings'    => apply_filters('automotive_theme_update_settings', $update_settings),
				'import_settings'    => apply_filters('automotive_theme_import_settings', $import_settings)
			) );

			if ( defined( "ICL_LANGUAGE_CODE" ) ) {
				array_splice( $sections[1]['fields'], 2, 0, array(
					array(
						'id'    => 'wpml_language_logos',
						'type'  => 'switch',
						'title' => __( 'WPML Language Logos', 'listings' ),
						'desc'  => __( 'Use different logos for each language in WPML', 'listings' )
					)
				) );

				// add required to existing logo
				$sections[1]['fields'][3]['required'] = array( 'wpml_language_logos', 'equals', 0 );

				// now add the logo for each languages
				$all_languages = apply_filters( "wpml_active_languages", "", array(
					"skip_missing" => 0,
					"orderby"      => "id"
				) );

				if ( ! empty( $all_languages ) ) {
					foreach ( $all_languages as $lang_code => $lang ) {
						array_splice( $sections[1]['fields'], 4, 0, array(
							array(
								'desc'     => 'For best results make the image 270px x 65px. This setting <strong>will</strong> take precedence over the above one.',
								'type'     => 'media',
								'id'       => 'logo_image_' . $lang_code,
								'url'      => true,
								'title'    => __( 'Header Logo Image', 'listings' ) . ' ' . $lang['translated_name'],
								'required' => array( 'wpml_language_logos', 'equals', '1' )
							)
						) );
					}
				}
			}

			// add social network urls
			foreach ( $social_options as $label ) {
				$sections[3]['fields'][] = array(
					'id'    => strtolower( $label ) . '_url',
					'type'  => 'text',
					'title' => ucwords( $label ) . ' URL',
				);
			}

			// disable if available
			if ( defined( "AUTOMOTIVE_VERSION" ) && version_compare( AUTOMOTIVE_VERSION, "5.6" ) != - 1 ) {
				$sections[0]['fields'][] = array(
					'desc'    => __( 'Enable or disable the listing features of the plugin, useful if you only wish to use Automotive plugin for the widgets and shortcodes.', 'automotive' ),
					'type'    => 'custom_button',
					'on'      => __( 'Enabled', 'automotive' ),
					'off'     => __( 'Disabled', 'automotive' ),
					'id'      => 'plugin_listings',
					'title'   => __( 'Listing Features Deactivation', 'automotive' ),
					'default' => '1',
				);
			}

			// Change your opt_name to match where you want the data saved.
			$args = array(
				"opt_name"      => "automotive_wp",
				"menu_title"    => __( "Theme Options", 'automotive' ),
				"page_slug"     => "automotive_wp",
				'dev_mode'      => false,
				"footer_credit" => "Automotive by Theme Suite",
				"share_icons"   => array(
					array(
						'url'   => 'https://www.facebook.com/themesuite/',
						'title' => __( 'Like us on Facebook', 'automotive' ),
						'icon'  => 'fa fa-facebook-official'
					),
					array(
						'url'   => 'https://twitter.com/themesuite',
						'title' => __( 'Follow us on Twitter', 'automotive' ),
						'icon'  => 'fa fa-twitter'
					)
				)
			);

			// Use this section if this is for a theme. Replace with plugin specific data if it is for a plugin.
			$theme                   = wp_get_theme();
			$args["display_name"]    = $theme->get( "Name" );
			$args["display_version"] = $theme->get( "Version" );

			$ReduxFramework = new ReduxFramework( $sections, $args );
		}

	}

	new Redux_Framework_automotive_wp_theme();
}
