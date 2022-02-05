<?php     get_header();

$sidebar            = automotive_theme_get_option('fourohfour_page_sidebar_position', '');
$default_sidebar    = automotive_theme_get_option('fourohfour_page_sidebar', '');
$classes            = content_classes($sidebar);

$content_class      = $classes[0];
$sidebar_class      = (isset($classes[1]) && !empty($classes[1]) ? $classes[1] : "");

$fourohfour_page_content = automotive_theme_get_option('fourohfour_page_content', false);
$page_content       = ($fourohfour_page_content ? get_post($fourohfour_page_content) : "");

// this is to help site admins fix 404 issues with listings
if( is_user_logged_in() && (current_user_can('editor') || current_user_can('administrator')) ) {
  echo "<div class='margin-top-20'>";
  echo do_shortcode('[alert type="3" close="No"]' . sprintf( __("If you are trying to view a listing but are seeing this page you need re-save your permalinks by going under %sSettings >> Permalinks%s and re-saving the existing settings", "automotive"), '<a href="' . admin_url('options-permalink.php') . '" target="_blank"><b>', '</b></a>') . '[/alert]') . "<div class='clearfix'></div>";
  echo '<i style="font-size: 12px;">(' . __('This message is only visible to site administrators') . ')</i><br><br><br>';
  echo "</div>";
} ?>

<div class="inner-page row wp_page<?php echo (isset($sidebar) && !empty($sidebar) ? " is_sidebar" : " no_sidebar"); ?>">
    <?php
    if(!empty($page_content)){
      echo '<div class="page-content post-entry' . (!empty($content_class) ? " " . $content_class : "") . '">';
      echo do_shortcode($page_content->post_content);
      echo '<div class="clearfix"></div>';
      echo '</div>';
    } else { ?>
      <div class="error-message<?php echo (!empty($content_class) ? " " . $content_class : ""); ?>">
          <h2 class="error padding-10 margin-bottom-30 padding-top-none"><i class="fa fa-exclamation-circle exclamation margin-right-50"></i>404</h2>
          <em><?php _e("File not found", "automotive"); ?>.</em>
          <div class="clearfix"></div>
      </div>
    <?php } ?>

    <?php // sidebar
        if(isset($sidebar) && !empty($sidebar) && $sidebar != "none" && isset($default_sidebar) && !empty($default_sidebar)){
            echo "<div class='" . $sidebar_class . " sidebar-widget side-content'>";
            dynamic_sidebar($default_sidebar);
            echo "</div>";
        }
    ?>
  <!-- </div> -->
</div>
<!--container ends-->

<?php get_footer(); ?>
