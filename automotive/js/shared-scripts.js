jQuery(document).ready(function($){

  // back to top
  if ($(".back_to_top").length) {
    $(".back_to_top").click(function () {
      $("html, body").animate({scrollTop: 0}, "slow");
      return false;
    });

    $(window).scroll(function () {
      var height = $(window).scrollTop();

      if (height > 300) {
        $(".back_to_top").fadeIn();
      } else {
        $(".back_to_top").fadeOut();
      }
    });
  }

  // social likes
  if ($('.social-likes.blog_social').length) {
    $('.social-likes.blog_social').socialLikes({
      zeroes: 'yes'
    });
  }

});