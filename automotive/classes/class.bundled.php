<?php

class TS_Bundled_Updates {
  var $current_plugin = false;
  var $plugins        = array();

  public function __construct($plugins){
    if(!empty($plugins)){
      if( !function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      }

      foreach($plugins as $plugin){
        if(file_exists(trailingslashit(WP_PLUGIN_DIR) . $plugin)){
          $plugin_info = get_plugin_data( trailingslashit(WP_PLUGIN_DIR) . $plugin );

          $current_plugin          = new stdClass;
          $current_plugin->file    = $plugin;
          $current_plugin->name    = $plugin_info['Name'];
          $current_plugin->version = $plugin_info['Version'];

          $this->plugins[$plugin] = $current_plugin;

          // update messages must be added per-plugin
          add_action( 'in_plugin_update_message-' . $plugin, array($this, 'prefix_upgrade_message'), 1);
          add_action( 'in_plugin_update_message-' . $plugin, array($this, 'suffix_upgrade_message'), 99);
        }
      }
    }

    add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_update'), 1 );
    add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_for_update'), 999 );
    add_filter( "upgrader_package_options", array($this, "get_zip_download"), 999 );

    if(function_exists('vc_manager') && apply_filters('automotive_disable_wpb_updater', true)){
      vc_manager()->disableUpdater(true);
    }
  }


  function get_zip_download( $options ){
    $theme_details  = get_option('envato-market-ts');
    $package        = $options['package'];

    if(isset($options['hook_extra']) && isset($options['hook_extra']['plugin']) && isset($this->plugins[$options['hook_extra']['plugin']])){

      $theme_details = get_option('envato-market-ts');
      $post_vars     = array(
        'action' => 'get_download_link',
        'plugin' => str_replace('.php', '', $options['hook_extra']['plugin'])
      );

      if(isset($theme_details['theme']['purchase_code'])){
        $post_vars['purchase_code'] = $theme_details['theme']['purchase_code'];
        $post_vars['bearer']        = $theme_details['oauth']['themesuite']['access_token'];
      }

      $json = wp_remote_get( $this->bundled_url($post_vars) );

      if ( !is_wp_error($json) ) {
        $result = json_decode( $json['body'] );

        if(isset($result->download_url) && !empty($result->download_url)){
          $options['package'] = $result->download_url;
        }
      }
    }

    return $options;
  }

  public function plugin_update_exists($plugin_to_check){
    $return = false;

    foreach($this->plugins as $current_file => $current_plugin){
      if($plugin_to_check == $current_plugin->name){
        $return = true;

        break;
      }
    }

    return $return;
  }

  // ran on the plugins page
  public function prefix_upgrade_message(){
  	echo '<span class="previous-bundle-message" style="display: none;">';
  }

  public function suffix_upgrade_message(){
    echo '</span>'; // close opening tag that hides existing update messages

    if(!$this->has_oauth()){
      echo '<span class="ts-update-message"><br><br>';
      echo sprintf( esc_html__('You must first login with your Envato account under %sTheme Options >> Update Settings%s to enable plugin updates for bundled plugins.', 'automotive'), '<a href="' . admin_url('admin.php?page=automotive_wp&tab=15') . '">', '</a>');
      echo '</span>';
    }
  }

  public function bundled_url($url_vars = array()){
    return 'htt'.'ps:/'.'/fi'.'les.them'.'esuite.com/bundl'.'ed_plugins/in'.'dex.php?' . http_build_query($url_vars);
  }

  public function get_remote_version($current_file){
    $bundled_version          = false;
    $transient_remote_version = 'ts_bundled_' . $current_file;

    if ( false === ( $bundled_version = get_transient( $transient_remote_version ) ) ) {
      $json = wp_remote_get( $this->bundled_url( array(
        'action' => 'get_version',
        'plugin' => str_replace('.php', '', $current_file)
      )));

      if( !is_wp_error($json) ){
        $result = json_decode( $json['body'] );

        if(isset($result->status) && $result->status){
          $bundled_version = $result->version;
        }
      }

      // Put the results in a transient. Expire after 12 hours.
      set_transient( $transient_remote_version, $bundled_version, DAY_IN_SECONDS );
    }

    return $bundled_version;
  }

  public function has_oauth(){
    $theme_details = get_option('envato-market-ts');

    if(isset($theme_details['theme']['purchase_code']))
      return true;

    return false;
  }

  public function check_for_update( $transient ) {

    if(!empty($this->plugins)){
      foreach($this->plugins as $current_file => $current_plugin){
        // if ( isset( $transient->response[ $current_file ] ) ) {
    		// 	return $transient;
    		// }

        if(!isset($transient->response) || !isset($transient->response[ $current_file ]) || $transient->response[ $current_file ]->package !== true){

          $remote_version = $this->get_remote_version($current_file);

        	// Get the remote version
          if ( $remote_version && version_compare( $current_plugin->version, $remote_version, '<' ) ) {
            $slug_parts = explode("/", $current_file);

            $obj              = new stdClass();
        		$obj->slug        = $slug_parts[0];
        		$obj->new_version = $remote_version;
        		$obj->url         = '';
        		$obj->package     = $this->has_oauth();
        		$obj->name        = $current_plugin->name;

        		$transient->response[ $current_file ] = $obj;
          }
        }
      }

      return $transient;
    }
  }

}