<?php get_header();

global $post;

if ( is_product_category() ) {
	$sidebar         = automotive_theme_get_option('woo_category_page_sidebar_position', false);
	$default_sidebar = automotive_theme_get_option('woo_category_page_sidebar', false);
} elseif ( is_product_tag() ) {
	$sidebar         = automotive_theme_get_option('woo_tag_page_sidebar_position', false);
	$default_sidebar = automotive_theme_get_option('woo_tag_page_sidebar', false);
} elseif ( is_shop() ) {
	$sidebar         = automotive_theme_get_option('woo_shop_page_sidebar_position', false);
	$default_sidebar = automotive_theme_get_option('woo_shop_page_sidebar', false);
} else {
	$sidebar         = get_post_meta( get_current_id(), "sidebar", true );
	$default_sidebar = get_post_meta( $post->ID, "sidebar_area", true );
}

$is_woocommerce_fullwidth = automotive_theme_get_option('woocommerce_fullwidth', true);

if ( isset( $sidebar ) && ! empty( $sidebar ) ) {

	if(!$is_woocommerce_fullwidth) {
		add_filter( 'loop_shop_columns', 'auto_loop_4_columns' );
	}
}

$classes = content_classes( $sidebar, true, ($is_woocommerce_fullwidth ? 2 : 3) );

$content_class = ( isset( $classes[0] ) && ! empty( $classes[0] ) ? $classes[0] : "" );
$sidebar_class = ( isset( $classes[1] ) && ! empty( $classes[1] ) ? $classes[1] : "" ); ?>

    <div class="inner-page row wp_page<?php echo( isset( $sidebar ) && ! empty( $sidebar ) ? " is_sidebar" : " no_sidebar" ); ?>">

			<div class="col-xl-12 col-lg-12 row">

				<?php do_action('woocommerce_before_main_content'); ?>

				<?php if(is_shop() || is_product_category()){ ?>
					<div class="woocommerce-shop-before-row">
							<p class="woocommerce-result-count">
									<?php
									global $wp_query;
									
									$paged    = max( 1, $wp_query->get( 'paged' ) );
									$per_page = $wp_query->get( 'posts_per_page' );
									$total    = $wp_query->found_posts;
									$first    = ( $per_page * $paged ) - $per_page + 1;
									$last     = min( $total, $wp_query->get( 'posts_per_page' ) * $paged );

									if ( $total <= $per_page || - 1 === $per_page ) {
											/* translators: %d: total results */
											printf( _n( 'Single result', '%d results', $total, 'automotive' ), $total );
									} else {
											/* translators: 1: first result 2: last result 3: total results */
											printf( _nx( 'Single result', '%1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'automotive' ), $first, $last, $total );
									}
									?>
							</p>

							<div>
									<?php
									global $wp_query;

									$is_products_showing = woocommerce_products_will_display();

									if( !$is_products_showing ) {
											echo "<div style='visibility: hidden;'>";
									}

									$orderby                 = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
									$show_default_orderby    = 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
									$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
											'menu_order' => __( 'Default sorting', 'automotive' ),
											'popularity' => __( 'Sort by popularity', 'automotive' ),
											'rating'     => __( 'Sort by average rating', 'automotive' ),
											'date'       => __( 'Sort by newness', 'automotive' ),
											'price'      => __( 'Sort by price: low to high', 'automotive' ),
											'price-desc' => __( 'Sort by price: high to low', 'automotive' ),
									) );

									if ( ! $show_default_orderby ) {
											unset( $catalog_orderby_options['menu_order'] );
									}

									if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
											unset( $catalog_orderby_options['rating'] );
									}

									wc_get_template( 'loop/orderby.php', array(
											'catalog_orderby_options' => $catalog_orderby_options,
											'orderby'                 => $orderby,
											'show_default_orderby'    => $show_default_orderby
									) );


									if( !$is_products_showing ) {
											echo "</div>";
									}
									?>
							</div>

							<div class="clearfix"></div>
					</div>
					<?php } ?>
				</div>

        <div class="col-xl-12 col-lg-12 row">
            <div class="pull-right"><?php do_action( 'currency_switcher', array( 'format' => '%name% (%symbol%)' ) ); ?></div>

            <div class="clearfix"></div>

            <div id="post-<?php echo get_current_id(); ?>" <?php echo "class='" . $content_class . " page-content post-entry'"; ?>>

    					<?php woocommerce_content(); ?>

            </div>

    		<?php // sidebar
    		if ( isset( $sidebar ) && ! empty( $sidebar ) && $sidebar != "none" ) {
    			echo "<div class='" . $sidebar_class . " sidebar-widget side-content'>";
    			dynamic_sidebar( $default_sidebar );
    			echo "</div>";
    		}
    		?>
        </div>

				<?php do_action('woocommerce_after_main_content'); ?>
    </div>

<?php get_footer(); ?>
