<?php get_header();

$queried_id  = get_queried_object_id();
$sidebar     = get_post_meta($queried_id, "sidebar_area", true);
$sidebar_pos = get_post_meta($queried_id, "sidebar", true );
$classes     = content_classes($sidebar_pos);

$content_class = $classes[0];
$sidebar_class = (isset($classes[1]) && !empty($classes[1]) ? $classes[1] : ""); ?>

    <div class="inner-page wp_page<?php echo (isset($sidebar_pos) && !empty($sidebar_pos) ? " is_sidebar" : " no_sidebar"); ?> row blog-container">
       	<div class="<?php echo $content_class; ?> page-content">

			<?php get_template_part('loop'); ?>

	    </div>

        <?php // sidebar
			if(isset($sidebar_pos) && !empty($sidebar_pos) && $sidebar_pos != "none"){
				echo "<div class='" . $sidebar_class . " sidebar-widget side-content'>";
				dynamic_sidebar($sidebar);
				echo "</div>";
			}
		?>
    </div>

<?php get_footer(); ?>
