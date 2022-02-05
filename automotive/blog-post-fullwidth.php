<?php
global $post, $Listing_Template;

$secondary_title = get_post_meta( $post->ID, "secondary_title", true );

$listing_display   = automotive_theme_get_option('listing_display', false);
$blog_post_details = automotive_theme_get_option('blog_post_details', 'not_set');

echo '<div class="col-12">';
if ( get_post_type() == "listings" && $listing_display ) {
  echo $Listing_Template->locate_template( "inventory_listing", array(
    "id"     => $post->ID,
    "layout" => "wide"
  ) );
} else { ?>
        <div class="blog-content margin-bottom-40<?php echo( is_sticky() ? " sticky_post" : "" ); ?>">
            <div class="blog-title">
                <h2<?php echo( empty( $secondary_title ) ? " class='margin-bottom-25'" : "" ); ?>><a
                            href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <?php echo( ! empty( $secondary_title ) ? "<strong class='margin-top-5 margin-bottom-25'>" . $secondary_title . "</strong>" : "" ); ?>
            </div>
    <?php if ( $blog_post_details ) { ?>
                <ul class="margin-top-10 margin-bottom-15 blog-content-details">
                    <li class="fa fa-calendar"><a href="#"><?php echo get_the_date(); ?></a></li>
                    <li class="fa fa-folder-open">
          <?php
          $categories      = get_the_category();
          $categories_list = $tooltip_cats = "";
          $cat_inc         = 0;

          if ( $categories ) {
            foreach ( $categories as $category ) {
              if ( $cat_inc < 4 ) {
                $categories_list .= "<a href='" . get_category_link( $category->term_id ) . "'>" . $category->cat_name . "</a>, ";
              } else {
                $tooltip_cats .= "<a href='" . get_category_link( $category->term_id ) . "'>" . $category->cat_name . "</a><br>";
              }

              $cat_inc ++;
            }
          }

          echo( isset( $categories_list ) && ! empty( $categories_list ) ? substr( $categories_list, 0, - 2 ) : "<span>" . __( "Not categorized", "automotive" ) . "</span>" );

          // if more than 5
          if ( ! empty( $tooltip_cats ) ) {
            echo ", <a class='' data-toggle=\"popover\" data-placement=\"top\" data-content=\"" . $tooltip_cats . "\" data-html=\"true\">" . __( "More Categories", "automotive" ) . "...</a>";
          }
          ?>
                    </li>
                    <li class="fa fa-user"><span
                                class="theme_font"><?php _e( "Posted by", "automotive" ); ?></span> <?php the_author_posts_link(); ?>
                    </li>
                    <li class="fa fa-comments"><?php comments_popup_link( __( 'No comments yet', 'automotive' ), __( '1 Comment', 'automotive' ), __( '% Comments', 'automotive' ) ); ?></li>
                </ul>
    <?php } ?>
            <div class="post-entry clearfix">
      <?php
      // blog thumbnail
      if ( has_post_thumbnail() ) {
        $featured_image_link = automotive_theme_get_option('featured_image_link', false);
        $large_image_url     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );

        echo '<div class="featured_blog_post_image"><a href="' . ($featured_image_link ? get_the_permalink() : $large_image_url[0]) . '" title="' . the_title_attribute( 'echo=0' ) . '" >';
        echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
        echo '</a></div>';
      } elseif ( get_post_type( $post ) == "listings" && function_exists( "auto_image" ) ) {
        $not_found_image = automotive_listing_get_option('not_found_image', false);
        $gallery_images = get_post_meta( $post->ID, "gallery_images", true );

        if ( ! empty( $gallery_images ) && ! empty( $gallery_images[0] ) ) {
          echo '<div class="featured_blog_post_image"><a href="' . get_permalink( $post->ID ) . '" title="' . the_title_attribute( 'echo=0' ) . '" >';
          echo auto_image( $gallery_images[0], 'thumbnail' );
          echo '</a></div>';
        } elseif ( empty( $gallery_images ) && isset( $not_found_image['url'] ) ) {
          echo '<div class="featured_blog_post_image"><a href="' . get_permalink( $post->ID ) . '" title="' . the_title_attribute( 'echo=0' ) . '" >';
          echo wp_get_attachment_image_src( $not_found_image['id'], 'thumbnail' );
          echo '</a></div>';
        }
      } ?>

      <?php //echo get_the_excerpt()
      $visual_composer_used = get_post_meta( $post->ID, "_wpb_vc_js_status", true );
      $post_content         = get_the_content();

      $stripp  = "<br><p><b><u><i><span><a><img>";
      $excerpt = get_the_excerpt();

      $has_mb = function_exists( "mb_substr" );

      if ( $visual_composer_used ) {
        $post_content = strip_tags( $post_content, $stripp );

        if ( ! empty( $excerpt ) ) {
          $post_content = ( $has_mb ? mb_ereg_replace( '\[[^\]]+\]', '', $excerpt ) : preg_replace( '/\[[^\]]+\]/', '', $excerpt ) );
        } else {
          $post_content = ( $has_mb ? mb_ereg_replace( '\[[^\]]+\]', '', $post_content ) : preg_replace( '/\[[^\]]+\]/', '', $post_content ) );
        }

        if ( $has_mb ) {
          $post_content = mb_substr( $post_content, 0, 1250, "utf-8" ) . " " . ( mb_strlen( $post_content ) > 1250 ? "[...]" : "" );
        } else {
          $post_content = substr( $post_content, 0, 1250 ) . " " . ( strlen( $post_content ) > 1250 ? "[...]" : "" );
        }
      } else {
        $post_content = $excerpt;
      }

      echo $post_content;//safe_html_cut( $post_content, 1250 );
      ?>

                <div class="clearfix"></div>

                <div class="blog-end row margin-top-20">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 read-more"><a
                                href="<?php echo get_permalink( $post->ID ); ?>"><?php _e( "Read More", "automotive" ); ?>
                            ...</a></div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right post-tags"><span
                                class="fa fa-tags tags">
        <?php
        $posttags = get_the_tags();
        $tags     = $tooltip_tags = "";
        $tag_inc  = 0;

        if ( $posttags ) {
          foreach ( $posttags as $tag ) {
            if ( $tag_inc < 4 ) {
              $tags .= "<a href='" . get_tag_link( $tag->term_id ) . "' title='" . $tag->name . " " . __( "Tag", "automotive" ) . "'>" . $tag->name . "</a>, ";
            } else {
              $tooltip_tags .= "<a href='" . get_tag_link( $tag->term_id ) . "' title='" . $tag->name . " " . __( "Tag", "automotive" ) . "'>" . $tag->name . "</a><br>";
            }

            $tag_inc ++;
          }
          echo substr( $tags, 0, - 2 );

          // if more than 5
          if ( ! empty( $tooltip_tags ) ) {
            echo ", <a class='' data-toggle=\"popover\" data-placement=\"top\" data-content=\"" . $tooltip_tags . "\" data-html=\"true\">" . __( "More Tags", "automotive" ) . "</a>";
          }
        }
        ?>
        </span></div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
  <?php
}
echo "</div>";
