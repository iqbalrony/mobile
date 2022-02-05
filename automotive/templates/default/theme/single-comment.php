<?php
extract( $args, EXTR_SKIP );

if ( 'div' == $args['style'] ) {
  $tag       = 'div';
  $add_below = 'comment';
} else {
  $tag       = 'li';
  $add_below = 'div-comment';
} ?>
    <li>
    <div class="comment-profile clearfix margin-top-30 div-comment-<?php echo $comment->comment_ID; ?>" id="div-comment-<?php echo $comment->comment_ID; ?>">
        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 threadauthor">
          <?php if ( $args['avatar_size'] != 0 ) {
            echo get_avatar( $comment, 180 );
          } ?>
        </div>
        <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
            <div class="comment-data">
                <div class="comment-author clearfix"><strong><?php echo get_comment_author_link(); ?></strong>|
                  <small><?php printf( __( '%1$s at %2$s', 'automotive' ), get_comment_date(), get_comment_time() ) ?></small>
                  <span class="pull-right">
                    <?php
                    comment_reply_link( array_merge( $args, array(
                      'add_below' => $add_below,
                      'depth'     => $depth,
                      'max_depth' => $args['max_depth']
                    ) ) );

                    edit_comment_link( __( '(Edit)', 'automotive' ), ' ', '' ); ?>
                  </span>
                </div>

                <div class="comment-text">
                  <?php comment_text(); ?>
                </div>
            </div>
        </div>
    </div>
