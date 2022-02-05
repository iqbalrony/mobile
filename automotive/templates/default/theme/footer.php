</div>
</section>
<div class="clearfix"></div>

<?php

do_action('automotive_theme_footer_start');

$toolbar_login_show  = automotive_theme_get_option('toolbar_login_show', true);
$footer_text         = automotive_theme_get_option('footer_text', '');
$footer_widgets      = automotive_theme_get_option('footer_widgets', true);
$footer_logo         = automotive_theme_get_option('footer_logo', true);
$footer_logo_image   = automotive_theme_get_option('footer_logo_image', false);
$logo_image          = automotive_theme_get_option('logo_image', false);
$logo_text           = automotive_theme_get_option('logo_text', '');
$logo_text_secondary = automotive_theme_get_option('logo_text_secondary', '');
$footer_icons        = automotive_theme_get_option('footer_icons', true);
$footer_menu         = automotive_theme_get_option('footer_menu', true);
$body_layout         = automotive_theme_get_option('body_layout', 1);
$footer_copyright    = automotive_theme_get_option('footer_copyright', true);
?>

<!--Footer Start-->
<?php
wp_reset_postdata();

global $post;

$footer_area = (is_singular() && isset($post->ID) ? get_post_meta( $post->ID, "footer_area", true ) : "");
$footer_area = (isset($footer_area) && !empty($footer_area) ? $footer_area : "default-footer");

if($footer_area != "no-footer" && $footer_widgets){ ?>
<footer itemscope="itemscope" itemtype="https://schema.org/WPFooter" >
    <div class="container">
        <div class="row">
            <?php dynamic_sidebar($footer_area); ?>
        </div>
    </div>
</footer>
<?php } ?>

<div class="clearfix"></div>
<section class="copyright-wrap <?php echo (isset($footer_area) && $footer_area == "no-footer" ? "no_footer" : "footer_area"); ?>">
<div class="container">
    <div class="row">
        <?php if(isset($footer_area) && $footer_area == "no-footer"){ ?>
        <div class="col-lg-12">
            <div class="logo-footer margin-bottom-15 md-margin-bottom-15 sm-margin-bottom-10 xs-margin-bottom-15">
                <?php if($footer_logo){ ?>
                    <?php if(isset($footer_logo_image['url']) && !empty($footer_logo_image['url'])){
                        echo "<img src='" . $footer_logo_image['url'] . "' alt='logo'>";
                    } else { ?>
                        <?php if(isset($logo_image['url']) && !empty($logo_image['url'])){ ?>
                        <img src='<?php echo $logo_image['url']; ?>' alt='logo'>
                        <?php } else { ?>
                        <div class="logo-footer"><a href="<?php echo home_url(); ?>">
                            <h2><?php echo (isset($logo_text) && !empty($logo_text) ? $logo_text : ""); ?></h2>
                            <span><?php echo (isset($logo_text_secondary) && !empty($logo_text_secondary) ? $logo_text_secondary : ""); ?></span></a>
                        </div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <?php if($footer_copyright){ ?>
                    <div class="footer_copyright_text"><?php do_action('automotive_theme_footer_text'); ?></div>
                <?php } ?>
            </div>
        </div>
        <?php } else { ?>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <?php if($footer_logo){ ?>
                <?php
              if(isset($footer_logo_image['url']) && !empty($footer_logo_image['url'])){
                echo "<div itemscope itemtype=\"http://schema.org/Organization\">";
                        echo "<a itemprop=\"url\" href=\"" . home_url() . "\"><img itemprop=\"logo\" src='" . $footer_logo_image['url'] . "' alt='logo'></a>";
                echo "</div>";
                    } else { ?>
                        <?php if(isset($logo_image['url']) && !empty($logo_image['url'])){ ?>
                        <img src='<?php echo $logo_image['url']; ?>' alt='logo'>
                        <?php } else { ?>
                        <div class="logo-footer"><a href="<?php echo home_url(); ?>">
                            <h2><?php echo (isset($logo_text) && !empty($logo_text) ? $logo_text : ""); ?></h2>
                            <span><?php echo (isset($logo_text_secondary) && !empty($logo_text_secondary) ? $logo_text_secondary : ""); ?></span></a>
                        </div>
                        <?php } ?>
                <?php } ?>
            <?php } ?>

            <?php if($footer_copyright){ ?>
                <div><?php do_action('automotive_theme_footer_text'); ?></div>
            <?php } ?>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <?php
            if($footer_icons) {
              automotive_social_icons('col-lg-12');
            }

            if($footer_menu) {
              $footer_menu_location = (!is_user_logged_in() || (is_user_logged_in() && !has_nav_menu( "logged-in-footer-menu" )) ? "footer-menu" : "logged-in-footer-menu");

              wp_nav_menu(
                array(
                  'theme_location'  => $footer_menu_location,
                  'menu_class'      => 'f-nav',
                  'container_class' => 'col-lg-12'
                )
              );
            } ?>
        </div>
        <?php } ?>
    </div>
</div>
</section>

<?php
do_action('automotive_theme_footer_end');

if($body_layout && $body_layout != 1){
  echo "</div>";
}

wp_footer();
?>
</body>
</html>
