<?php

    /**
     * @package     Redux Framework
     * @subpackage  Accordion field
     * @author      Kevin Provance (kprovance)
     * @version     1.0.1
     */

// Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

// Don't duplicate me!
    if ( ! class_exists( 'ReduxFramework_envato_auth' ) ) {

        /**
         * Main ReduxFramework_multi_media class
         *
         * @since       1.0.0
         */
        class ReduxFramework_envato_auth {
            public $envato_username = 'themesuite';
            protected $market_slug  = 'envato-market-ts';
            protected $oauth_script = 'http://themesuite.com/verification/server-script.php';

            /**
             * Class Constructor. Defines the args for the extions class
             *
             * @since       1.0.0
             * @access      public
             *
             * @param       array $field  Field sections.
             * @param       array $value  Values.
             * @param       array $parent Parent object.
             *
             * @return      void
             */
            public function __construct( $field = array(), $value = '', $parent ) {

                // Set required variables
                $this->parent = $parent;
                $this->field  = $field;
                $this->value  = $value;

                // Set extension dir & url
                if ( empty( $this->extension_dir ) ) {
                    $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
                    // $this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
                    $this->extension_url = trailingslashit(get_template_directory_uri()) . "ReduxFramework/extensions/envato_auth/envato_auth/";
                }

                $this->envato_auth();
            }

            public function envato_auth(){
              if ( ! empty( $_POST['oauth_session'] ) && ! empty( $_POST['bounce_nonce'] ) && ! empty( $_POST['deactivation_code'] ) && wp_verify_nonce( $_POST['bounce_nonce'], 'envato_oauth_bounce_' . $this->envato_username ) ) {

                update_option( "themesuite_deactivation_token", $_POST['deactivation_code'] );

                // request the token from our bounce url.
                $oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
                if ( ! $oauth_nonce ) {
                  // this is our 'private key' that is used to request a token from our api bounce server.
                  // only hosts with this key are allowed to request a token and a refresh token
                  // the first time this key is used, it is set and locked on the server.
                  $oauth_nonce = wp_create_nonce( 'envato_oauth_nonce_' . $this->envato_username );
                  update_option( 'envato_oauth_' . $this->envato_username, $oauth_nonce );
                }
                $response = wp_remote_post( $this->oauth_script, array(
                    'method'      => 'POST',
                    'timeout'     => 15,
                    'redirection' => 1,
                    'httpversion' => '1.0',
                    'blocking'    => true,
                    'headers'     => array(),
                    'body'        => array(
                      'oauth_session' => $_POST['oauth_session'],
                      'oauth_nonce'   => $oauth_nonce,
                      'get_token'     => 'yes'
                    ),
                    'cookies'     => array(),
                  )
                );

                if ( is_wp_error( $response ) ) {
                  $error_message = $response->get_error_message();
                  $class         = 'error';
                  echo "<div class=\"$class\"><p>" . sprintf( esc_html__( 'Something went wrong while trying to retrieve oauth token: %s', 'landscaping' ), $error_message ) . '</p></div>';
                } else {
                  $token  = @json_decode( wp_remote_retrieve_body( $response ), true );
                  $result = false;
                  if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
                    $token['oauth_session'] = $_POST['oauth_session'];
                    $result                 = $this->_manage_oauth_token( $token );

                    // refresh plugin updates value so users can get download links
                    delete_option('_site_transient_update_plugins');
                  }
                  if ( $result !== true ) {
                    echo 'Failed to get oAuth token. Please go back and try again';
                    exit;
                  }
                }

              }
            }

            /**
             * Field Render Function.
             * Takes the vars and outputs the HTML for the field in the settings
             *
             * @since       1.0.0
             * @access      public
             * @return      void
             */
            public function render() {
                $defaults    = array(
                    'position'  => '',
                    'style'     => '',
                    'class'     => '',
                    'title'     => '',
                    'subtitle'  => '',
                    'open'      => '',
                    'open-icon' => 'el-plus',
                    'close-icon' => 'el-minus'
                );
                $this->field = wp_parse_args( $this->field, $defaults );

                $guid = uniqid();

                $field_id = $this->field['id'];
                $dev_mode = $this->parent->args['dev_mode'];
                $opt_name = $this->parent->args['opt_name'];
                $dev_tag  = '';

                $option = get_option($this->market_slug);

                if ( isset( $option['theme']['id'] ) ) {
                  $reset_envato_url = wp_nonce_url(admin_url('admin.php?page=automotive_wp&reset_envato_account'), 'reset_envato_account');

                  echo "<p style='margin-top:0;'>" . __("Envato Account Connected", "automotive") . "</p>";
                  echo "<p><a href='" . esc_url($reset_envato_url) . "'>Reset</a></p>";
                } else {
                  echo "<a href='" . $this->get_oauth_login_url( admin_url('admin.php?page=automotive_wp&tab=15') ) . "' class='button-primary'>" . __("Login with Envato", "automotive") . "</a>";
                }
            }

            public function get_oauth_login_url( $return ) {
              if(is_child_theme()){
                $my_theme = wp_get_theme(get_template());
              } else {
                $my_theme = wp_get_theme();
              }

        			return 'https://themesuite.com/verification/server-script.php?bounce_nonce=' . wp_create_nonce( 'envato_oauth_bounce_' . $this->envato_username ) . '&wp_return=' . urlencode( $return ) . '&theme=' . urlencode( $my_theme->get( 'Name' ) ) . '&version=' . urlencode( $my_theme->get( 'Version' ) ) . '&site=' . urlencode( site_url() );
        		}

            /**
             * Enqueue Function.
             * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
             *
             * @since       1.0.0
             * @access      public
             * @return      void
             */
            public function enqueue() {
                $extension = ReduxFramework_extension_envato_auth::getInstance();
            }

            public function api() {
        			return Automotive_Envato_Market_API::instance();
        		}

            private function _array_merge_recursive_distinct( $array1, $array2 ) {
        			$merged = $array1;
        			foreach ( $array2 as $key => &$value ) {
        				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
        					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
        				} else {
        					$merged [ $key ] = $value;
        				}
        			}

        			return $merged;
        		}

            private static $_current_manage_token = false;

            private function _manage_oauth_token( $token ) {
        			$my_theme = wp_get_theme();

        			if ( is_array( $token ) && ! empty( $token['access_token'] ) ) {
        				if ( self::$_current_manage_token == $token['access_token'] ) {
        					return false; // stop loops when refresh auth fails.
        				}
        				self::$_current_manage_token = $token['access_token'];
        				// yes! we have an access token. store this in our options so we can get a list of items using it.
        				$option = get_option( 'envato_setup_wizard', array() );
        				if ( ! is_array( $option ) ) {
        					$option = array();
        				}
        				// check if token is expired.
        				if ( empty( $token['expires'] ) ) {
        					$token['expires'] = time() + 3600;
        				}
        				if ( $token['expires'] < time() + 120 && ! empty( $token['oauth_session'] ) ) {
        					// time to renew this token!
        					$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
        					$response    = wp_remote_post( $this->oauth_script, array(
        							'method'      => 'POST',
        							'timeout'     => 10,
        							'redirection' => 1,
        							'httpversion' => '1.0',
        							'blocking'    => true,
        							'headers'     => array(),
        							'body'        => array(
        								'oauth_session' => $token['oauth_session'],
        								'oauth_nonce'   => $oauth_nonce,
        								'refresh_token' => 'yes',
        								'url'           => home_url(),
        								'theme'         => $my_theme->get( 'Name' ),
        								'version'       => $my_theme->get( 'Version' ),
        							),
        							'cookies'     => array(),
        						)
        					);
        					if ( is_wp_error( $response ) ) {
        						$error_message = $response->get_error_message();
        						echo "Something went wrong while trying to retrieve oauth token: $error_message";
        					} else {
        						$new_token = @json_decode( wp_remote_retrieve_body( $response ), true );
        						$result    = false;
        						if ( is_array( $new_token ) && ! empty( $new_token['new_token'] ) ) {
        							$token['access_token'] = $new_token['new_token'];
        							$token['expires']      = time() + 3600;
        						}
        					}
        				}
        				// use this token to get a list of purchased items
        				// add this to our items array.
        				$response                    = $this->api()->request( 'https://api.envato.com/v3/market/buyer/purchases', array(
        					'headers' => array(
        						'Authorization' => 'Bearer ' . $token['access_token'],
        					),
        				) );
        				self::$_current_manage_token = false;

        				if ( is_array( $response ) && is_array( $response['purchases'] ) ) {

        					// up to here, add to items array

                  $parent_theme_data = false;

                  if(is_child_theme()){
                    $parent_theme_data = wp_get_theme(get_template());
                  }

        					foreach ( $response['purchases'] as $purchase ) {
        						// check if this item already exists in the items array.

        						if (
                      isset( $purchase['item']['wordpress_theme_metadata']['theme_name'] ) &&
                      (
                        (
                          $purchase['item']['wordpress_theme_metadata']['theme_name'] == $my_theme->get( 'Name' ) ||
                          $purchase['item']['wordpress_theme_metadata']['theme_name'] . " Child Theme" == $my_theme->get( 'Name' )
                        ) ||
                        (
                          $parent_theme_data !== false &&
                          $purchase['item']['wordpress_theme_metadata']['theme_name'] == $parent_theme_data->get( 'Name' )
                        )
                      )
                    ) {

        							$option['theme'] = array(
        								'id'            => '' . $purchase['item']['id'],
        								// item id needs to be a string for market download to work correctly.
        								'name'          => $purchase['item']['name'],
        								'oauth'         => $this->envato_username,
        								'type'          => ! empty( $purchase['item']['wordpress_theme_metadata'] ) ? 'theme' : 'plugin',
        								'purchase_code' => ! empty( $purchase['code'] ) ? $purchase['code'] : ''
        							);
        						}

        					}
        				} else {
        					return false;
        				}
        				if ( ! isset( $option['oauth'] ) ) {
        					$option['oauth'] = array();
        				}
        				// store our 1 hour long token here. we can refresh this token when it comes time to use it again (i.e. during an update)
        				$option['oauth'][ $this->envato_username ] = $token;
        				update_option( 'envato_setup_wizard', $option );

        				$envato_options = get_option( $this->market_slug, array() );
        				$envato_options = $this->_array_merge_recursive_distinct( $envato_options, $option );
        				update_option( $this->market_slug, $envato_options );

        				return true;
        			} else {
        				return false;
        			}
        		}

        }
    }