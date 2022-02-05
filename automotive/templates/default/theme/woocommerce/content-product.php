<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$woo_shop_layout = automotive_theme_get_option('woo_shop_layout', true);
$product_layout  = ($woo_shop_layout ? "1" : "2");

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( sanitize_html_class("product_style_" . $product_layout ) ); ?>>
	<?php
	/**
	 * woocommerce_before_shop_loop_item hook.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );


	/**
	 * woocommerce_before_shop_loop_item_title hook.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */

	echo "<div class='woocommerce-image-wrapper'>";

	echo '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';
	do_action( 'woocommerce_before_shop_loop_item_title' );
	echo "</a>";

	if($product_layout == "2"){
	    echo "<div class='woocommerce-product-back'>";

	    echo "<div class='woocommerce-product-back-align'>";
		echo '<a href="' . get_the_permalink() . '">';
		do_action( 'woocommerce_shop_loop_item_title' );
		echo "</a>";

		if(function_exists("woocommerce_template_loop_price")){
			woocommerce_template_loop_price();
		}

		echo "<div class='woo-view-buttons'>";
		echo '<a href="' . get_the_permalink() . '" class="view-item-button"><i class="fa fa-eye"></i></a>';

		do_action('woocommerce_after_shop_loop_item_add_to_cart');
		echo "</div>";

		do_action( 'woocommerce_after_shop_loop_item_title' );

		echo "</div>";

		echo "</div>";

		echo "</div>"; //.woocommerce-image-wrapper
    }

	/**
	 * woocommerce_shop_loop_item_title hook.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	if($product_layout == "1") {
		do_action('woocommerce_after_shop_loop_item_add_to_cart');

		echo "</div>"; //.woocommerce-image-wrapper

		echo "<div class='woocommerce-title-price-area'>";
		echo '<a href="' . get_the_permalink() . '">';
		do_action( 'woocommerce_shop_loop_item_title' );
		echo "</a>";

		/**
		 * woocommerce_after_shop_loop_item_title hook.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );
		echo "<div class='clearfix'></div>";
		echo "</div>";

		$secondary_title = get_post_meta( $product->get_id(), "secondary_title", true );
		echo( ! empty( $secondary_title ) ? "<div class='woocommerce-loop-product__secondary_title'>" . $secondary_title . "</div>" : "" );
	}

	if( ! $product->is_in_stock() ){
		echo "<p class=\"stock out-of-stock\">" . __('Out of stock') . "</p>";
	}

	/**
	 * woocommerce_after_shop_loop_item hook.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
