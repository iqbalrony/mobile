<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'envato_theme_setup_wizard' ) ) :
	function envato_theme_setup_wizard() {

		if ( class_exists( 'Envato_Theme_Setup_Wizard' ) ) {
			class dtbwp_Envato_Theme_Setup_Wizard extends Envato_Theme_Setup_Wizard {

				/**
				 * Holds the current instance of the theme manager
				 *
				 * @since 1.1.3
				 * @var Envato_Theme_Setup_Wizard
				 */
				private static $instance = null;

				/**
				 * @since 1.1.3
				 *
				 * @return Envato_Theme_Setup_Wizard
				 */
				public static function get_instance() {
					if ( ! self::$instance ) {
						self::$instance = new self;
					}

					return self::$instance;
				}

				public function init_actions() {
					if ( apply_filters( $this->theme_name . '_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
						add_filter( $this->theme_name . '_theme_setup_wizard_content', array(
							$this,
							'theme_setup_wizard_content'
						) );
						add_filter( $this->theme_name . '_theme_setup_wizard_steps', array(
							$this,
							'theme_setup_wizard_steps'
						) );
					}
					parent::init_actions();
				}

				public function theme_setup_wizard_steps( $steps ) {
					//unset($steps['design']); // this removes the "logo" step
					return $steps;
				}

				public function theme_setup_wizard_content( $content ) {
					if ( $this->is_possible_upgrade() ) {
						array_unshift_assoc( $content, 'upgrade', array(
							'title'            => __( 'Upgrade', 'landscaping' ),
							'description'      => __( 'Upgrade Content and Settings', 'landscaping' ),
							'pending'          => __( 'Pending.', 'landscaping' ),
							'installing'       => __( 'Installing Updates.', 'landscaping' ),
							'success'          => __( 'Success.', 'landscaping' ),
							'install_callback' => array( $this, '_content_install_updates' ),
							'checked'          => 1
						) );
					}

					return $content;
				}

				public function get_default_theme_style() {
					return false;
				}

				public function is_possible_upgrade() {
					$widget = get_option( 'widget_text' );
					if ( is_array( $widget ) ) {
						foreach ( $widget as $item ) {
							if ( isset( $item['dtbwp_widget_bg'] ) ) {
								return true;
							}
						}
					}
					// check if shop page is already installed?
					$shoppage = get_page_by_title( 'Shop' );
					if ( $shoppage || get_option( 'page_on_front', false ) ) {
						return true;
					}

					return false;
				}

				public function _content_install_updates() {
					return true;

				}

			}

			dtbwp_Envato_Theme_Setup_Wizard::get_instance();
		} else {
			// log error?
		}
	}
endif;