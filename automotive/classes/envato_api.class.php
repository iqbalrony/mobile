<?php
/**
 * Envato API class.
 *
 * @package Envato_Market
 */

if ( ! class_exists( 'Automotive_Envato_Market_API' ) ) :

	/**
	 * Creates the Envato API connection.
	 *
	 * @class Automotive_Envato_Market_API
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Automotive_Envato_Market_API {

		/**
		 * The single class instance.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var object
		 */
		private static $_instance = null;

		/**
		 * The Envato API personal token.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $token;

		private $market_slug     = "envato-market-ts";
		private $envato_username = "themesuite";
		private $oauth_script    = "http://themesuite.com/verification/server-script.php";

		/**
		 * Main Automotive_Envato_Market_API Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Automotive_Envato_Market_API()
		 * @uses Automotive_Envato_Market_API::init_globals() Setup class globals.
		 * @uses Automotive_Envato_Market_API::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Automotive_Envato_Market_API.
		 * @codeCoverageIgnore
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::$_instance->init_globals();
				//self::$_instance->init_actions();
			}
			return self::$_instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Automotive_Envato_Market_API::instance()
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function __construct() {
			/* We do nothing here! */
		}

		/**
		 * You cannot clone this class.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'landscaping' ), '1.0.0' );
		}

		/**
		 * You cannot unserialize instances of this class.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'landscaping' ), '1.0.0' );
		}

		/**
		 * Premium themes.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var array
		 */
		private static $themes = array();

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.0.0
		 */
		/*public function init_actions() {
			// Inject theme updates into the response array.
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_themes' ) );
			add_filter( 'pre_set_transient_update_themes', array( $this, 'update_themes' ) );
		}*/

		/**
		 * Setup the class globals.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		public function init_globals() {
			// Envato API token.
			$options        = get_option($this->market_slug);
			$token_options  = (isset($options['oauth'][$this->envato_username]) && !empty($options['oauth'][$this->envato_username]) ? $options['oauth'][$this->envato_username] : "");

			$this->token    = (isset($token_options['access_token']) && !empty($token_options['access_token']) ? $token_options['access_token'] : "");

			// D($token_options);
			//
			// var_dump(time());
			// var_dump($token_options['expires'] < time() + 120);
			//
			// die;

			if ( !empty($token_options) && $token_options['expires'] < time() + 120 && ! empty( $token_options['oauth_session'] ) ) {
				// time to renew this token!
				if(is_child_theme()){
					$my_theme = wp_get_theme(get_template());
				} else {
					$my_theme = wp_get_theme();
				}

				$oauth_nonce = get_option( 'envato_oauth_' . $this->envato_username );
				$response    = wp_remote_post( $this->oauth_script, array(
						'method'      => 'POST',
						'timeout'     => 10,
						'redirection' => 1,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(
							'oauth_session' => $token_options['oauth_session'],
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

					if ( is_array( $new_token ) && ! empty( $new_token['new_token'] ) ) {
						$options['oauth'][$this->envato_username]['access_token'] = $this->token = $new_token['new_token'];
						$options['oauth'][$this->envato_username]['expires']      = time() + 3600;

						update_option($this->market_slug, $options);
					} else {
						// token got dropped, hmm
						delete_option('envato_oauth_' . $this->envato_username);
						delete_option($this->market_slug);
						delete_option('envato_setup_wizard');
					}
				}
			}
		}

		/**
		 * Query the Envato API.
		 *
		 * @uses wp_remote_get() To perform an HTTP request.
		 *
		 * @since 1.0.0
		 *
		 * @param  string $url API request URL, including the request method, parameters, & file type.
		 * @param  array  $args The arguments passed to `wp_remote_get`.
		 * @return array  The HTTP response.
		 */
		public function request( $url, $args = array() ) {
			$defaults = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->token,
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405',
				),
				'timeout' => 20,
			);
			$args = wp_parse_args( $args, $defaults );

			$token = trim( str_replace( 'Bearer', '', $args['headers']['Authorization'] ) );
			if ( empty( $token ) ) {
				return new WP_Error( 'api_token_error', __( 'An API token is required.', 'landscaping' ) );
			}

			// Make an API request.
			$response = wp_remote_get( esc_url_raw( $url ), $args );

			// Check the response code.
			$response_code    = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );

			if ( 200 !== $response_code && ! empty( $response_message ) ) {
				return new WP_Error( $response_code, $response_message );
			} elseif ( 200 !== $response_code ) {
				return new WP_Error( $response_code, __( 'An unknown API error occurred.', 'landscaping' ) );
			} else {
				$return = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( null === $return ) {
					return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'landscaping' ) );
				}
				return $return;
			}
		}

		/**
		 * Deferred item download URL.
		 *
		 * @since 1.0.0
		 *
		 * @param int $id The item ID.
		 * @return string.
		 */
		public function deferred_download( $id ) {
			if ( empty( $id ) ) {
				return '';
			}

			$args = array(
				'deferred_download' => true,
				'item_id' => $id,
			);
			return add_query_arg( $args, esc_url( envato_market()->get_page_url() ) );
		}

		/**
		 * Get the item download.
		 *
		 * @since 1.0.0
		 *
		 * @param  int   $id The item ID.
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return bool|array The HTTP response.
		 */
		public function download( $id, $args = array() ) {
			if ( empty( $id ) ) {
				return false;
			}

			$url = 'https://api.envato.com/v2/market/buyer/download?item_id=' . $id . '&shorten_url=true';
			$response = $this->request( $url, $args );

			// @todo Find out which errors could be returned & handle them in the UI.
			if ( is_wp_error( $response ) || empty( $response ) || ! empty( $response['error'] ) ) {
				return false;
			}

			if ( ! empty( $response['wordpress_theme'] ) ) {
				return $response['wordpress_theme'];
			}

			if ( ! empty( $response['wordpress_plugin'] ) ) {
				return $response['wordpress_plugin'];
			}

			return false;
		}

		/**
		 * Get an item by ID and type.
		 *
		 * @since 1.0.0
		 *
		 * @param  int   $id The item ID.
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function item( $id, $args = array() ) {
			$url = 'https://api.envato.com/v2/market/catalog/item?id=' . $id;
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) ) {
				return false;
			}

			if ( ! empty( $response['wordpress_theme_metadata'] ) ) {
				return $this->normalize_theme( $response );
			}

			if ( ! empty( $response['wordpress_plugin_metadata'] ) ) {
				return $this->normalize_plugin( $response );
			}

			return false;
		}

		/**
		 * Get the list of available themes.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function themes( $args = array() ) {
			$themes = array();

			$url = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-themes';
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) || empty( $response['results'] ) ) {
				return $themes;
			}

			foreach ( $response['results'] as $theme ) {
				$themes[] = $this->normalize_theme( $theme['item'] );
			}

			return $themes;
		}

		/**
		 * Normalize a theme.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $theme An array of API request values.
		 * @return array A normalized array of values.
		 */
		public function normalize_theme( $theme ) {
			return array(
				'id' => $theme['id'],
				'name' => ( ! empty( $theme['wordpress_theme_metadata']['theme_name'] ) ? $theme['wordpress_theme_metadata']['theme_name'] : '' ),
				'author' => ( ! empty( $theme['wordpress_theme_metadata']['author_name'] ) ? $theme['wordpress_theme_metadata']['author_name'] : '' ),
				'version' => ( ! empty( $theme['wordpress_theme_metadata']['version'] ) ? $theme['wordpress_theme_metadata']['version'] : '' ),
				'description' => self::remove_non_unicode( $theme['wordpress_theme_metadata']['description'] ),
				'url' => ( ! empty( $theme['url'] ) ? $theme['url'] : '' ),
				'author_url' => ( ! empty( $theme['author_url'] ) ? $theme['author_url'] : '' ),
				'thumbnail_url' => ( ! empty( $theme['thumbnail_url'] ) ? $theme['thumbnail_url'] : '' ),
				'rating' => ( ! empty( $theme['rating'] ) ? $theme['rating'] : '' ),
			);
		}

		/**
		 * Get the list of available plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function plugins( $args = array() ) {
			$plugins = array();

			$url = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-plugins';
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) || empty( $response['results'] ) ) {
				return $plugins;
			}

			foreach ( $response['results'] as $plugin ) {
				$plugins[] = $this->normalize_plugin( $plugin['item'] );
			}

			return $plugins;
		}

		/**
		 * Normalize a plugin.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $plugin An array of API request values.
		 * @return array A normalized array of values.
		 */
		public function normalize_plugin( $plugin ) {
			$requires = null;
			$tested = null;
			$versions = array();

			// Set the required and tested WordPress version numbers.
			foreach ( $plugin['attributes'] as $k => $v ) {
				if ( 'compatible-software' === $v['name'] ) {
					foreach ( $v['value'] as $version ) {
						$versions[] = str_replace( 'WordPress ', '', trim( $version ) );
					}
					if ( ! empty( $versions ) ) {
						$requires = $versions[ count( $versions ) - 1 ];
						$tested = $versions[0];
					}
					break;
				}
			}

			return array(
				'id' => $plugin['id'],
				'name' => ( ! empty( $plugin['wordpress_plugin_metadata']['plugin_name'] ) ? $plugin['wordpress_plugin_metadata']['plugin_name'] : '' ),
				'author' => ( ! empty( $plugin['wordpress_plugin_metadata']['author'] ) ? $plugin['wordpress_plugin_metadata']['author'] : '' ),
				'version' => ( ! empty( $plugin['wordpress_plugin_metadata']['version'] ) ? $plugin['wordpress_plugin_metadata']['version'] : '' ),
				'description' => self::remove_non_unicode( $plugin['wordpress_plugin_metadata']['description'] ),
				'url' => ( ! empty( $plugin['url'] ) ? $plugin['url'] : '' ),
				'author_url' => ( ! empty( $plugin['author_url'] ) ? $plugin['author_url'] : '' ),
				'thumbnail_url' => ( ! empty( $plugin['thumbnail_url'] ) ? $plugin['thumbnail_url'] : '' ),
				'landscape_url' => ( ! empty( $plugin['previews']['landscape_preview']['landscape_url'] ) ? $plugin['previews']['landscape_preview']['landscape_url'] : '' ),
				'requires' => $requires,
				'tested' => $tested,
				'number_of_sales' => ( ! empty( $plugin['number_of_sales'] ) ? $plugin['number_of_sales'] : '' ),
				'updated_at' => ( ! empty( $plugin['updated_at'] ) ? $plugin['updated_at'] : '' ),
				'rating' => ( ! empty( $plugin['rating'] ) ? $plugin['rating'] : '' ),
			);
		}

		/**
		 * Normalizes a string to do a value check against.
		 *
		 * Strip all HTML tags including script and style & then decode the
		 * HTML entities so `&amp;` will equal `&` in the value check and
		 * finally lower case the entire string. This is required becuase some
		 * themes & plugins add a link to the Author field or ambersands to the
		 * names, or change the case of their files or names, which will not match
		 * the saved value in the database causing a false negative.
		 *
		 * @since 1.0.0
		 *
		 * @param string $string The string to normalize.
		 * @return string
		 */
		public function normalize( $string ) {
			return strtolower( html_entity_decode( wp_strip_all_tags( $string ) ) );
		}

		/**
		 * Remove all non unicode characters in a string
		 *
		 * @since 1.0.0
		 *
		 * @param string $retval The string to fix.
		 * @return string
		 */
		static private function remove_non_unicode( $retval ) {
			return preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $retval );
		}
	}

endif;
