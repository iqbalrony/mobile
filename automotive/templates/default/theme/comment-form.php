<?php global $user_identity;

$commenter     = wp_get_current_commenter();
$req           = get_option( 'require_name_email' );
$aria_req      = ( $req ? " aria-required='true'" : '' );
$required_text = "*";

$current_user = wp_get_current_user();

$args = array(
  'id_form'           => 'commentform',
  'id_submit'         => 'submit',
  'title_reply'       => __( 'Leave comments', 'automotive' ),
  'title_reply_to'    => __( 'Leave a reply to %s', 'automotive' ),
  'cancel_reply_link' => __( 'Cancel Reply', 'automotive' ),
  'label_submit'      => __( 'Submit Comment', 'automotive' ),

  'comment_field' => '<textarea class="form-control" placeholder="' . __( 'Your comments', 'automotive' ) . '" rows="7" name="comment" id="comment"></textarea>',

  'must_log_in' => '<p class="must-log-in">' .
                   sprintf(
                     __( 'You must be <a href="%s">logged in</a> to post a comment.', 'automotive' ),
                     wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
                   ) . '</p>',

  'logged_in_as' => '<p class="logged-in-as">' .
                    sprintf(
                      __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'automotive' ),
                      admin_url( 'profile.php' ),
                      $user_identity,
                      wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) )
                    ) . '</p>',

  'comment_notes_before' => '<p class="comment-notes">' .
                            __( 'Your email address will not be published.', 'automotive' ) . ( $req ? $required_text : '' ) .
                            '</p>',

  'comment_notes_after' => '<p class="form-allowed-tags">' .
                           sprintf(
                             __( '<br><br>You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s', 'automotive' ),
                             ' <code>' . allowed_tags() . '</code>'
                           ) . '</p>',

  'fields' => apply_filters( 'comment_form_default_fields', array(

      'author' =>
        '<input type="text" class="form-control" placeholder="' . __( "Name (Required)", "automotive" ) . '" name="author">',

      'email' =>
        '<input type="text" class="form-control" placeholder="' . __( "Email (Required)", "automotive" ) . '" autocomplete="off" name="email">',

      'url' =>
        '<input type="text" class="form-control" placeholder="' . __( "Website", "automotive" ) . '" name="url">'
    )
  ),
);

echo "<div class='leave-comments clearfix' id='respond'>";
comment_form( $args, get_current_id() );
echo "</div>";