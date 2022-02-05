<?php

class Automotive_CSS_Composer {
    private static $instance    = null;
    public $css_data            = array();
    public $color_scheme        = array();
    public $color_scheme_groups = array();

    public function __construct(){
      $this->color_scheme_groups = array(
        'Global' 						=> __( 'Set the global color settings that will display across the website.', 'automotive' ),
        'Toolbar'						=> __( 'Set the colors for the top toolbar (above the header).', 'automotive' ),
        'Header'        		=> __( 'Set header colors.', 'automotive' ),
        'Sticky Header'  		=> __( 'Set header colors that will display in the sticky header on scroll.', 'automotive' ),
        'Mobile Header'			=> __( 'Set mobile header colors.', 'automotive' ),
        'Secondary Header' 	=> __( 'Set secondary header colors.', 'automotive' ),
        'Body'          		=> __( 'Set body colors.', 'automotive' ),
        'Inventory'					=> __( 'Set the inventory element colors.', 'automotive' ),
        'Footer'        		=> __( 'Set footer colors.', 'automotive' ),
        'Bottom Footer' 		=> __( 'Set bottom footer colors.', 'automotive' )
      );
    }

    public static function get_instance(){
      if(self::$instance === null){
        self::$instance = new self();
      }

      return self::$instance;
    }

    public function add_color_scheme($id, $title, $color, $alpha, $selectors, $mode, $group){
      $this->color_scheme[$id] = array(
        'id'       => $id,
        'title'    => $title,
        'color'    => $color,
        'alpha'    => $alpha,
        'selector' => $selectors,
        'mode'     => $mode,
        'group'    => $group
      );
    }

    public function remove_color_scheme($id){
      if(isset($this->color_scheme[$id])){
        unset($this->color_scheme[$id]);
      }
    }

    public function get_color_scheme($keep_keys = false){
      return ($keep_keys ? $this->color_scheme : array_values($this->color_scheme));
    }

    public function add_color_scheme_group($scheme, $description){
      $this->color_scheme_groups[$scheme] = $description;
    }

    public function remove_color_scheme_group($scheme){
      if(isset($this->color_scheme_groups[$scheme])){
        unset($this->color_scheme_groups[$scheme]);
      }

      // unset any removed color scheme options (redux still shows them)
      if(!empty($this->color_scheme)){
        foreach($this->color_scheme as $color_scheme_id => $color_scheme){
          if($color_scheme['group'] === $scheme){
            unset($this->color_scheme[$color_scheme_id]);
          }
        }
      }
    }

    public function get_color_scheme_groups(){
      return $this->color_scheme_groups;
    }

    public function update_color_scheme($id, $value_key, $new_value = false, $selector_value = false){
      if(isset($this->color_scheme[$id])){

        if(is_array($value_key) && !$new_value){
          foreach($value_key as $single_value_key => $single_value_value){
            // default to auto-add selectors, otherwise use a separate call.
            if($single_value_key === 'selector'){
              $this->update_color_scheme($id, $single_value_key, 'add', $single_value_value);
            } else {
              $this->update_color_scheme($id, $single_value_key, $single_value_value);
            }
          }
        } elseif($value_key == 'selector' && $selector_value){ // selectors need special updating
          $action = $new_value;

          if($action === 'add'){
            $this->color_scheme[$id]['selector'] .= (!empty($this->color_scheme[$id]['selector']) ? ", " : "") . $selector_value;
          } elseif($action === 'remove'){
            $this->color_scheme[$id]['selector'] = str_replace($selector_value .", ", "", $this->color_scheme[$id]['selector']);
          }

        } elseif($value_key === 'color'){
          // update both color and RGBA values
          $this->color_scheme[$id]['color'] = $new_value;
          $this->color_scheme[$id]['rgba']  = $this->hex2rgba($new_value, $this->color_scheme[$id]['alpha']);

        } elseif($value_key === 'alpha'){
          $this->color_scheme[$id]['alpha'] = $new_value;

          // now we should update the colors
          $this->color_scheme[$id]['rgba']  = $this->hex2rgba($this->color_scheme[$id]['color'], $new_value);
        } elseif($value_key !== 'id') { // we don't want to change ids

        } elseif($value_key === 'property') {

          // if($action === 'add'){
          //   echo '<pre>';
          //   print_r($this->color_scheme[$id]);
          //   echo '</pre>';
          //   die;
          // }

        }

      }
    }

    public function hex2rgba($color, $opacity = false) {
      $default = 'rgb(0,0,0)';

      //Return default if no color provided
      if(empty($color)){
        return $default;
      }

      //Sanitize $color if "#" is provided
      if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
      }

      //Check if color has 6 or 3 characters and get values
      if (strlen($color) == 6) {
          $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
      } elseif ( strlen( $color ) == 3 ) {
          $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
      } else {
          return $default;
      }

      //Convert hexadec to rgb
      $rgb = array_map('hexdec', $hex);

      //Check if opacity is set(rgba or rgb)
      if($opacity){
        if(abs($opacity) > 1)
          $opacity = 1.0;
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
      } else {
        $output = 'rgb('.implode(",",$rgb).')';
      }

      //Return rgb(a) color string
      return $output;
    }

    // this allows selectors to be in array or string and it will be properly converted to our composer format
    public function normalize_selector_prop_input($selector_input){
      $normalized = array();

      if(is_array($selector_input)){
        $normalized = $selector_input;
      } else {

        if(strpos($selector_input, ',')){
          $exploded_selectors = explode(',', $selector_input);
          $cleaned_selectors = array_map('trim', $exploded_selectors);

          $normalized = $cleaned_selectors;
        } else {
          $normalized = array($selector_input);
        }
      }

      return array_flip($normalized);
    }

    public function add_selector($id, $selector_and_props, $value = false){
      if(!isset($this->css_data[$id])){
        $this->css_data[$id] = array(
          'sap'   => array(),
          'value' => null
        );
      }

      foreach($selector_and_props as $sap_id => $single_selector_and_props){
        $normal_selectors = $this->normalize_selector_prop_input($single_selector_and_props['selectors']);
        $normal_props     = $this->normalize_selector_prop_input($single_selector_and_props['props']);

        $this->css_data[$id]['sap'][] = array(
          'selectors' => $normal_selectors,
          'props'     => $normal_props
        );
      }

      if($value){
        $this->css_data[$id]['value'] = $value;
      }
    }

    public function update_selector($id, $selector_and_props_to_add){
      if(isset($this->css_data[$id]) && !empty($this->css_data[$id]['sap'])){

        // D($selector_and_props_to_add);
        // D($this->css_data[$id]['sap']);

        // when updating selectors we need to check the amount of props for a match first,
        // if it doesn't then we add a new selector rule
        foreach($this->css_data[$id]['sap'] as $sap_id => $selectors_and_props){
          $existing_props     = $selectors_and_props['props'];
          $existing_selectors = $selectors_and_props['selectors'];

          foreach($selector_and_props_to_add as $to_add_id => $to_add_selectors_and_props){
            $new_props     = (isset($to_add_selectors_and_props['props']) ? $this->normalize_selector_prop_input($to_add_selectors_and_props['props']) : array());
            $new_selectors = (isset($to_add_selectors_and_props['selectors']) ? $this->normalize_selector_prop_input($to_add_selectors_and_props['selectors']) : array());

            // var_dump($existing_props == $new_props);
            // D($new_props);
            // D($existing_props);
            // D($new_selectors);

            if($existing_props == $new_props){
              $this->css_data[$id]['sap'][$sap_id]['selectors'] = array_merge($this->css_data[$id]['sap'][$sap_id]['selectors'], $new_selectors);

              // D($this->css_data[$id]['sap']);

              unset($selector_and_props_to_add[$to_add_id]);
            }

          }

        }

        // now check for any unadded rules then add them since they didn't match
        //
        // maybe in a later update we can split the properties into more easily searchable
        // chunks so if a selector contains 1 used property and 1 unused property it would
        // merge with the existing property selectors then add unused ones?
        // if(!empty($selector_and_props_to_add)){
        //   D($selector_and_props_to_add);
        //
        //
        //   die;
        // }

      } else {
        $this->add_selector($id, $selector_and_props_to_add);
      }

    }

    public function remove_selector($id, $remove_selectors){

      // var_dump($id);
      // var_dump($remove_selectors);

      if(isset($this->css_data[$id])){
        if(!empty($this->css_data[$id]['sap'])){
          $selector_and_props = $this->css_data[$id]['sap'];

          foreach($selector_and_props as $sap_id => $all_selectors){
            $single_selectors = $all_selectors['selectors'];
            $single_props     = $all_selectors['props'];

            // now we go through the remove selectors to find any matches
            if(!empty($remove_selectors)){
              foreach($remove_selectors as $remove_selector){
                $has_certain_props = is_array($remove_selector);

                if(!$has_certain_props){

                  if(isset($this->css_data[$id]['sap'][$sap_id]['selectors'][$remove_selector])){
                    // D($this->css_data[$id]['sap'][$sap_id]['selectors']);
                    //
                    // var_dump($remove_selector);
                    unset($this->css_data[$id]['sap'][$sap_id]['selectors'][$remove_selector]);
                    //
                    // D($this->css_data[$id]['sap'][$sap_id]['selectors']);

                  }
                } else {

                  foreach($remove_selector as $single_remove_selector){

                    if(isset($this->css_data[$id]['sap'][$sap_id]['props'][$single_remove_selector])){
                      unset($this->css_data[$id]['sap'][$sap_id]['props'][$single_remove_selector]);
                    }

                  }

                }
              }

              // if any sap has no selectors remove it
              if(empty($this->css_data[$id]['sap'][$sap_id]['selectors'])){
                unset($this->css_data[$id]['sap'][$sap_id]);
              }
            }

            // $single_selector = $single_selector_and_props['selectors'];
          }
        }
      }

    }

    public function delete_selectors_and_props($id){
      if(isset($this->css_data[$id])){
        unset($this->css_data[$id]);
      }
    }

    public function number_only($value){
      return preg_replace( '/\D/', '', $value);
    }

    public function css_strip_whitespace( $css ) {
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

    public function compose_css(){
      $composed_css = '';
      $css_data     = (!empty($this->css_data) ? $this->css_data : false);

      if($css_data){

        foreach($css_data as $id => $single_css_data){
          $sap   = (!empty($single_css_data['sap']) ? $single_css_data['sap'] : false);
          $value = (!empty($single_css_data['value']) ? $single_css_data['value'] : '');

          // now go through each set of selectors and properties
          if($sap){
            foreach($sap as $sap_id => $selectors_and_props){
              $final_selector = implode(',', array_keys($selectors_and_props['selectors']));

              $composed_css .= $final_selector . '{';

              if(!empty($selectors_and_props['props'])){
                foreach(array_keys($selectors_and_props['props']) as $property){
                  $composed_css .= $property . ':' . $value . ';';
                }
              }

              $composed_css .= '}';
            }
          }
        }
      }

      return $this->css_strip_whitespace($composed_css);
    }
}

function Automotive_CSS_Composer(){
  return Automotive_CSS_Composer::get_instance();
}
