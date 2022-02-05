<?php get_header();

    $sidebar            = automotive_theme_get_option('category_page_sidebar_position', false);
    $default_sidebar    = automotive_theme_get_option('category_page_sidebar', false);
    $classes            = content_classes($sidebar);

    $content_class      = $classes[0];
    $sidebar_class      = (isset($classes[1]) && !empty($classes[1]) ? $classes[1] : ""); ?>

	<div class="container">
        <div class="inner-page row<?php echo (isset($sidebar) && !empty($sidebar) ? " is_sidebar" : " no_sidebar"); ?>">
            <div class="page-content<?php echo (!empty($content_class) ? " " . $content_class : ""); ?> padding-left-none padding-right-none">

				<?php get_template_part('loop'); ?>

			</div>

            <?php // sidebar
                if(isset($sidebar) && !empty($sidebar) && $sidebar != "none" && isset($default_sidebar) && !empty($default_sidebar)){
                    echo "<div class='" . $sidebar_class . " sidebar-widget side-content'>";
                    dynamic_sidebar($default_sidebar);
                    echo "</div>";
                }
            ?>
        </div>
    </div>

<?php get_footer(); ?>
