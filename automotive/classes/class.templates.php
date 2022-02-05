<?php

class Automotive_Templates {
  private static $instance = null;
  public $templates        = array();

  public function __construct(){
    $this->templates['default'] = array(
      'id'   => 'default',
      'name' => __('Default', 'automotive'),
      'path' => 'default'
    );

    // run operations after_setup_theme so we can access all data properly
    add_action('after_setup_theme', array($this, 'required_files'), 15);

    // woocommerce exists, lets filter some template paths
    if(class_exists('woocommerce')){
      add_filter( 'wc_get_template_part', array($this, 'filter_wc_paths'), 10, 3 );
      add_filter( 'wc_get_template', array($this, 'filter_wc_get_template'), 10, 5 );
    }
  }

  public static function get_instance(){
    if(self::$instance === null){
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function required_files(){
    // always require a functions file
    $this->get_template_part('functions', false, true);
    $this->get_template_part('options', false, true);
  }

  public function filter_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {

    // var_dump([
    //   'located'       => $located,
    //   'template_name' => $template_name,
    //   'args'          => $args,
    //   'template_path' => $template_path
    // ]);
    //

    $template_path      = $this->get_current_template();
    $template_directory = get_template_directory();

    // using a child theme, check for template file
    $child_theme_template = trailingslashit(get_stylesheet_directory()) . 'woocommerce/' . $template_name;
    if(get_stylesheet_directory() !== $template_directory && file_exists($child_theme_template)){
        return $child_theme_template;
    }

    $default_path = trailingslashit($template_directory) . 'templates/default/theme/woocommerce/' . $template_name;

    // var_dump(file_exists($template_path['path'] . 'templates/theme/woocommerce/' . $template_name));
    // var_dump($template_path['path'] . 'templates/theme/woocommerce/' . $template_name);

    if(isset($template_path['path']) && file_exists($template_path['path'] . 'templates/theme/woocommerce/' . $template_name)){
      return $template_path['path'] . 'templates/theme/woocommerce/' . $template_name;
    } elseif(!isset($template_path['path']) && file_exists($default_path) ){
      return $default_path;
    } elseif($located) {
      return $located;
    }


    return $located;
  }

  public function filter_wc_paths($template, $slug, $name){
    $file_path          = $slug . '-' . $name . '.php';
    $template_path      = $this->get_current_template();
    $template_directory = get_template_directory();

    // using a child theme, check for template file
    $child_theme_template = trailingslashit(get_stylesheet_directory()) . 'woocommerce/' . $file_path;
    if(get_stylesheet_directory() !== $template_directory && file_exists($child_theme_template)){
        return $child_theme_template;
    }

    $default_path = trailingslashit($template_directory) . 'templates/default/theme/woocommerce/' . $file_path;
    $default_file_path = trailingslashit($template_directory) . 'templates/' . trailingslashit($template_path['path']) . 'theme/woocommerce/' . $file_path;

    if(isset($template_path['path']) && file_exists($default_file_path)){
      return $default_file_path;
    } elseif(!isset($template_path['path']) && file_exists($default_path) ){
      return $default_path;
    } elseif($name) {
      return WC()->plugin_path() . "/templates/{$slug}-{$name}.php";
    } else {
      return WC()->template_path() . "{$slug}.php";
    }
  }

  public function add_template($id, $args){
    $this->templates[$id] = $args;
  }

  public function get_current_template($value = false){
    // $site_template = automotive_theme_get_option('site_template', 'default');
    // var_dump([$site_template, $this->templates]);die;

    $site_template = get_option('automotive_theme_site_template', 'default');

    $site_template = (isset($this->templates[$site_template]) ? $this->templates[$site_template] : $this->templates['default']);


    if($value && isset($site_template[$value])){
      return $site_template[$value];
    }

    return $site_template;
  }

  public function template_exists($template){
    return (isset($this->templates[$template]));
  }

  public function get_template_part($file, $version = false, $force_required = false, $data = false){
    $version_path     = ($version && $this->template_exists($version) ? $version : $this->get_current_template('id'));
    $file_path_suffix = 'templates/' . $version_path . '/theme/' . $file . '.php';
    $check_file_paths = array();

    if(!$force_required){
      $check_file_paths[] = trailingslashit( get_stylesheet_directory() ) . $file_path_suffix;

      if(get_stylesheet_directory() !== get_template_directory()){
        $check_file_paths[] = trailingslashit( get_template_directory() ) . $file_path_suffix;
      }
    }

    // if the version isn't default we need the absolute plugin path
    if($version_path !== 'default'){
      $check_file_paths[] = $this->get_current_template('path') . 'templates/theme/' . $file . '.php';
    }

    // if template not found revert to defaults
    if(true){
      $check_file_paths[] = trailingslashit( get_template_directory() ) . 'templates/default/theme/' . $file . '.php';
    }

    // var_dump($check_file_paths);die;

    foreach($check_file_paths as $file_path){
      if(file_exists($file_path)){
        if(!empty($data)){
          extract($data);
        }

        include($file_path);

        break;
      }
    }
  }

  public function get_templates(){
    $templates = array();

    if(!empty($this->templates)){
      foreach($this->templates as $template_id => $template){
        $templates[$template_id] = $template['name'];
      }
    }

    return $templates;
  }

}

function Automotive_Templates(){
  return Automotive_Templates::get_instance();
}

function automotive_add_template($id, $args){
  Automotive_Templates()->add_template($id, $args);
}

function automotive_get_current_template($value = false){
  return Automotive_Templates()->get_current_template($value);
}

function automotive_get_templates(){
  return Automotive_Templates()->get_templates();
}

/* Used to grab template parts for each template variation */
function automotive_get_template_part($template_file, $load_template = false, $data = false){
  Automotive_Templates()->get_template_part($template_file, $load_template, false, $data);
}

function automotive_theme_get_part($part, $load_template = false, $data = false){
  automotive_get_template_part($part, $load_template, $data);
}

/* Used to grab template pages for each template variation */
function automotive_get_template_page($page, $load_template = ''){
  if(empty($load_template)){
    $load_template = automotive_get_current_template();
  }

  get_template_part('templates/' . $load_template . '/pages/' . $page);
}

/* Used to grab backend code for each template variation */
function automotive_require_once($file, $load_template = ''){
  if(empty($load_template)){
    $load_template = automotive_get_current_template();
  }

  require_once(trailingslashit(get_template_directory()) . 'templates/' . trailingslashit($load_template) . $file . '.php');
}
